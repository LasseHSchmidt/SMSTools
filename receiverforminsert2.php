<?php
    require_once 'init.php';
    // This file must be included via index.php so $listid and $fromphone are available

// 1. Logic: Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    
    // Connect using database settings from init.php / Config
    $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    mysqli_set_charset($con, "utf8mb4");

    if (!$con) {
        die("Connection error: " . mysqli_connect_error());
    }

    // Sanitize and prepare input data
    $fullname   = $_POST['fullname'] ?? '';
    $shortname  = $_POST['shortname'] ?? '';
    $cellphone  = $_POST['cellphone'] ?? '';
    $phone      = !empty($_POST['phone']) ? $_POST['phone'] : 0;
    $email      = $_POST['email'] ?? '';
    $birthday   = !empty($_POST['birthday']) ? $_POST['birthday'] : "0000-00-00";

    // Prepared statement to prevent SQL injection
    $query = "INSERT INTO $TReceivers (ListId, FullName, ShortName, CellPhone, Phone, Email, BirthDay) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    
    // "issssss" = integer, string, string, string, string, string, string
    $stmt->bind_param("issssss", $listid, $fullname, $shortname, $cellphone, $phone, $email, $birthday);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);

        // Redirect back to the contact list on success
        $to = "index.php?cmd=receivers&listid=$listid&phone=$fromphone";
        header('Location: ' . $to);
        exit();
    } else {
        $error = "Error inserting record: " . $stmt->error;
        $stmt->close();
        mysqli_close($con);
    }
}

// 2. View: Contact creation form
?>

<div class="form-container">
    <h2><?php echo htmlspecialchars(__('insert_title')); ?></h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars($listid); ?>" />
        
        <div class="form-group">
            <label for="fullname"><?php echo htmlspecialchars(__('label_fullname')); ?></label>
            <input type="text" id="fullname" name="fullname" placeholder="<?php echo htmlspecialchars(__('ph_fullname')); ?>" required onblur="copyFirstName()" />
        </div>

        <div class="form-group">
            <label for="shortname"><?php echo htmlspecialchars(__('label_shortname')); ?></label>
            <input type="text" id="shortname" name="shortname" placeholder="<?php echo htmlspecialchars(__('ph_shortname')); ?>" />
        </div>

        <div class="form-group">
            <label for="cellphone"><?php echo htmlspecialchars(__('label_cellphone')); ?></label>
            <input type="text" 
                    id="cellphone" 
                    name="cellphone" 
                    placeholder="88888888" 
                    required 
                    maxlength="8" 
                    pattern="[0-9]{8}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
        </div>

        <input type="hidden" name="phone" />
        <input type="hidden" name="email" />
        <input type="hidden" name="birthday" />

        <button type="submit" name="ok" class="submit-btn"><?php echo htmlspecialchars(__('btn_save_contact')); ?></button>
    </form>
</div>

<script>
function copyFirstName() {
    var fullNameInput = document.getElementById('fullname');
    var shortNameInput = document.getElementById('shortname');

    // Auto-fill shortname only if it is currently empty
    if (shortNameInput.value.trim() === "") {
        var name = fullNameInput.value.trim();
        if (name !== "") {
            // Split the string by spaces and extract the first element
            var firstName = name.split(' ')[0];
            shortNameInput.value = firstName;
        }
    }
}
</script>