<?php
    require_once 'init.php';
// Denne fil skal inkluderes i index.php, så $listid og $fromphone er tilgængelige via init.php

// 1. Logik: Håndter indsendelse af formular (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    
    // Forbindelse via konstanter fra din nye Config-struktur (hvis du har implementeret den)
    // Ellers bruger vi variablerne fra din nuværende init.php
    $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    mysqli_set_charset($con, "utf8mb4");

    if (!$con) {
        die("Forbindelsesfejl: " . mysqli_connect_error());
    }

    // Rens og forbered data
    $fullname   = $_POST['fullname'] ?? '';
    $shortname  = $_POST['shortname'] ?? '';
    $cellphone  = $_POST['cellphone'] ?? '';
    $phone      = !empty($_POST['phone']) ? $_POST['phone'] : 0;
    $email      = $_POST['email'] ?? '';
    $birthday   = !empty($_POST['birthday']) ? $_POST['birthday'] : "0000-00-00";

    // Prepared Statement for sikkerhed mod SQL Injection
    $query = "INSERT INTO $TReceivers (ListId, FullName, ShortName, CellPhone, Phone, Email, BirthDay) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    
    // "issssss" = integer, string, string, string, string, string, string
    $stmt->bind_param("issssss", $listid, $fullname, $shortname, $cellphone, $phone, $email, $birthday);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);

        // Redirect tilbage til oversigten ved succes
        $to = "index.php?cmd=receivers&listid=$listid&phone=$fromphone";
        header('Location: ' . $to);
        exit();
    } else {
        $error = "Der skete en fejl: " . $stmt->error;
        $stmt->close();
        mysqli_close($con);
    }
}

// 2. Visning: Selve formularen
?>

<div class="form-container">
    <h2>Opret ny kontakt</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars($listid); ?>" />
        
        <div class="form-group">
            <label for="fullname">Fuldt navn</label>
            <input type="text" id="fullname" name="fullname" placeholder="f.eks. Jensen Jensen" required onblur="copyFirstName()" />
        </div>

        <div class="form-group">
            <label for="shortname">Fornavn (Kaldenavn)</label>
            <input type="text" id="shortname" name="shortname" placeholder="f.eks. Jensen" />
        </div>

        <div class="form-group">
    <label for="cellphone">Mobilnummer (8 tal)</label>
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

        <button type="submit" name="ok" class="submit-btn">Gem kontakt</button>
    </form>
</div>

<script>
function copyFirstName() {
    var fullNameInput = document.getElementById('fullname');
    var shortNameInput = document.getElementById('shortname');

    // Kun hvis shortname er tomt, udfylder vi det automatisk
    if (shortNameInput.value.trim() === "") {
        var name = fullNameInput.value.trim();
        if (name !== "") {
            // Splitter strengen ved mellemrum og tager det første element
            var firstName = name.split(' ')[0];
            shortNameInput.value = firstName;
        }
    }
}
</script>