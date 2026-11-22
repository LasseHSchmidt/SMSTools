<?php
require_once 'init.php';

// 1. Logic: Handle SMS dispatch (POST)
$feedback = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    verify_csrf();
    
    $message = $_POST['message'] ?? '';
    // Always use $listid from the authenticated session
    $current_listid = (int)$listid;

    if (!empty($message) && $current_listid > 0) {
        $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
        if (!$mysqli) {
            die("Connection error: " . mysqli_connect_error());
        }
        mysqli_set_charset($mysqli, "utf8mb4");

        // Safely fetch recipients
        $stmt = $mysqli->prepare("SELECT ShortName, CellPhone FROM $TReceivers WHERE ListId = ? AND CellPhone <> ''");
        $stmt->bind_param("i", $current_listid);
        $stmt->execute();
        $results = $stmt->get_result();

        $default_cc = preg_replace('/[^0-9]/', '', (string)(getenv('DEFAULT_COUNTRY_CODE') ?: ''));
        $recipients = [];

        while ($line = $results->fetch_assoc()) {
            $raw_phone = trim($line["CellPhone"]);

            // Convert leading '00' international prefix to '+'
            if (strpos($raw_phone, '00') === 0) {
                $raw_phone = '+' . substr($raw_phone, 2);
            }

            $has_plus = (strpos($raw_phone, '+') === 0);
            $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);

            // Only prepend DEFAULT_COUNTRY_CODE if no '+' was given and length suggests a local number (e.g. 8 digits)
            if (!$has_plus && !empty($default_cc) && strlen($clean_phone) === 8) {
                $clean_phone = $default_cc . $clean_phone;
            }

            // Valid international MSISDN is typically 7 to 15 digits
            if (strlen($clean_phone) >= 7 && strlen($clean_phone) <= 15) {
                $recipients[] = ['msisdn' => (int)$clean_phone];
            }
        }
        $stmt->close();

        if (count($recipients) > 0) {
            // Prepare JSON payload for GatewayAPI
            $json = [
                'sender' => (string)$fromphone,
                'message' => $message,
                'recipients' => $recipients
            ];

            // Send via cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_USERPWD, $api_token . ":");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $reply = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $response_data = json_decode($reply);

            if ($http_code == 200 && isset($response_data->usage)) {
                $cost = $response_data->usage->total_cost;
                $currency = $response_data->usage->currency ?? 'DKK';
                $count = count($recipients);
                $msg_ids = isset($response_data->ids) ? implode(', ', $response_data->ids) : '';
                
                $feedback = "<div class='success-msg'>" . __(
                    'sms_success',
                    $count,
                    htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)$cost, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string)$currency, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($msg_ids, ENT_QUOTES, 'UTF-8')
                ) . "</div>";

                // Log the sent SMS in the database
                $log_stmt = $mysqli->prepare("INSERT INTO $TSendSMS (ListId, CellPhone, Message, Status, Date) VALUES (?, ?, ?, ?, NOW())");
                $status_text = "$currency $cost";
                $all_numbers = $count . " " . __('log_recipients');
                $log_stmt->bind_param("isss", $current_listid, $all_numbers, $message, $status_text);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $feedback = "<div class='error-msg'>" . htmlspecialchars(__('sms_api_error', $http_code), ENT_QUOTES, 'UTF-8') . htmlspecialchars((string)$reply, ENT_QUOTES, 'UTF-8') . "</div>";
            }
        } else {
            $feedback = "<div class='error-msg'>" . htmlspecialchars(__('sms_no_recipients'), ENT_QUOTES, 'UTF-8') . "</div>";
        }
        mysqli_close($mysqli);
    }
}
?>

<div class="sms-container">
    <h2><?php echo htmlspecialchars(__('sms_title'), ENT_QUOTES, 'UTF-8'); ?></h2>

    <?php echo $feedback; ?>

    <form action="" method="post" id="smsForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8'); ?>" />

        <div class="form-group">
            <label for="message"><?php echo htmlspecialchars(__('sms_label_msg', (string)$fromphone), ENT_QUOTES, 'UTF-8'); ?></label>
            <textarea id="message" name="message" rows="6" maxlength="300" 
                placeholder="<?php echo htmlspecialchars(__('sms_ph_msg'), ENT_QUOTES, 'UTF-8'); ?>" required
                oninput="updateCount(this)"></textarea>
            <div class="char-count">
                <span id="chars">0</span> / 300 <?php echo htmlspecialchars(__('sms_chars'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

        <button type="submit" name="ok" class="send-btn" id="submitBtn" 
            onclick="this.innerText=<?php echo htmlspecialchars(json_encode(__('sms_btn_sending')), ENT_QUOTES, 'UTF-8'); ?>; this.style.opacity='0.7';">
            <?php echo htmlspecialchars(__('sms_btn_send'), ENT_QUOTES, 'UTF-8'); ?>
        </button>
    </form>
</div>

<script>
function updateCount(textarea) {
    var count = textarea.value.length;
    document.getElementById('chars').innerText = count;
    
    // Highlight character count in red when approaching the limit
    if (count >= 280) {
        document.getElementById('chars').style.color = "#dc2626";
    } else {
        document.getElementById('chars').style.color = "#6b7280";
    }
}
</script>
