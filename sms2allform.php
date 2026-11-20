<?php
require_once 'init.php';

// 1. Logik: Håndter afsendelse af SMS (POST)
$feedback = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    
    $message = $_POST['message'] ?? '';
    $current_listid = $_POST['listid'] ?? $listid;

    if (!empty($message) && $current_listid) {
        $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
        mysqli_set_charset($mysqli, "utf8mb4");
        if (!$mysqli) {
            die("Forbindelsesfejl: " . mysqli_connect_error());
        }

        // Hent modtagere sikkert
        $stmt = $mysqli->prepare("SELECT ShortName, CellPhone FROM $TReceivers WHERE ListId = ? AND CellPhone <> ''");
        $stmt->bind_param("i", $current_listid);
        $stmt->execute();
        $results = $stmt->get_result();

        $recipients = [];
        while ($line = $results->fetch_assoc()) {
            // Fjern mellemrum/tegn og tilføj dansk landekode (45) hvis nummeret er 8 cifre
            $clean_phone = preg_replace('/[^0-9]/', '', $line["CellPhone"]);
            if (strlen($clean_phone) === 8) {
                $clean_phone = '45' . $clean_phone;
            }
            if (!empty($clean_phone)) {
                $recipients[] = ['msisdn' => (int)$clean_phone];
            }
        }
        $stmt->close();

        if (count($recipients) > 0) {
            // Forbered JSON til GatewayAPI
            $json = [
                'sender' => (string)$fromphone,
                'message' => $message,
                'recipients' => $recipients
            ];

            // cURL afsendelse
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
                $feedback = "<div class='success-msg'>Succes! $count SMSer sendt fra <strong>$fromphone</strong>. Pris: $cost $currency (ID: $msg_ids)</div>";

                // Log afsendelsen i databasen
                $log_stmt = $mysqli->prepare("INSERT INTO $TSendSMS (ListId, CellPhone, Message, Status, Date) VALUES (?, ?, ?, ?, NOW())");
                $status_text = "$currency $cost";
                $all_numbers = $count . " modt.";
                $log_stmt->bind_param("isss", $current_listid, $all_numbers, $message, $status_text);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $feedback = "<div class='error-msg'>Fejl fra GatewayAPI (HTTP $http_code): " . htmlspecialchars($reply) . "</div>";
            }
        } else {
            $feedback = "<div class='error-msg'>Ingen modtagere fundet på denne liste.</div>";
        }
        mysqli_close($mysqli);
    }
}
?>

<div class="sms-container">
    <h2>Send SMS til alle</h2>

    <?php echo $feedback; ?>

    <form action="" method="post" id="smsForm">
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars($listid); ?>" />

        <div class="form-group">
            <label for="message">Din besked (Afsender: <?php echo htmlspecialchars($fromphone); ?>)</label>
            <textarea id="message" name="message" rows="6" maxlength="300" 
                placeholder="Skriv din besked her..." required
                oninput="updateCount(this)"></textarea>
            <div class="char-count">
                <span id="chars">0</span> / 300 tegn
            </div>
        </div>

        <button type="submit" name="ok" class="send-btn" id="submitBtn" 
            onclick="this.innerText='Sender... Vent venligst'; this.style.opacity='0.7';">
            Send SMS nu
        </button>
    </form>
</div>

<script>
function updateCount(textarea) {
    var count = textarea.value.length;
    document.getElementById('chars').innerText = count;
    
    if (count >= 280) {
        document.getElementById('chars').style.color = "#dc2626";
    } else {
        document.getElementById('chars').style.color = "#6b7280";
    }
}
</script>
