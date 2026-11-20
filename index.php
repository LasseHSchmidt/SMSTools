<?php
    ob_start();
    require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang); ?>" dir="ltr">
<head>
    <title>sms.3490.dk V.2.0</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css?v=<?php echo filemtime('style2.css'); ?>">

    <script type="text/javascript">
        function limitText(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
            }
        }
    </script>
</head>

<body onload="if(document.getElementById('message')) document.getElementById('message').value=''">

    <div class="wrapper">
        <header id="banner">
            <?php 
                if ($listid) {
                    echo "<a href='index.php?cmd=receivers&listid=$listid&phone=$fromphone'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                } else {
                    echo "<a href='index.php'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                }
            ?>
        </header>

        <nav id="topmenu">
            <ul>
                <?php
                    // Show navigation links only when logged in
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        echo "<li><a href='index.php?cmd=receivers&listid=$listid&phone=$fromphone' class='menu'>" . __('nav_receivers') . "</a></li>";
                        echo "<li><a href='index.php?cmd=receiverforminsert&listid=$listid&phone=$fromphone' class='menu'>" . __('nav_new_receiver') . "</a></li>";
                        echo "<li><a href='index.php?cmd=sms2allform&listid=$listid&phone=$fromphone' class='menu'>" . __('nav_send_sms') . "</a></li>";
                        echo "<li><a href='index.php?cmd=smsstatus&listid=$listid&phone=$fromphone' class='menu'>" . __('nav_sms_log') . "</a></li>";
                        echo "<li class='logout'><a href='logoff.php'>" . __('nav_logout') . "</a></li>";
                    }

                    // Preserve current page and query parameters when switching language
                    $query_da = http_build_query(array_merge($_GET, ['lang' => 'da']));
                    $query_en = http_build_query(array_merge($_GET, ['lang' => 'en']));
                ?>
                <li style="margin-left: auto; display: flex; align-items: center;">
                    <a href="index.php?<?php echo htmlspecialchars($query_da); ?>" style="<?php echo $current_lang === 'da' ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.75;'; ?>">DA</a>
                    <a href="index.php?<?php echo htmlspecialchars($query_en); ?>" style="<?php echo $current_lang === 'en' ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.75;'; ?>">EN</a>
                </li>
            </ul>
        </nav>

        <main id="main-content">
            <div class="container">
                <div class="content-card">
                    <?php
                    $routes = [
                        'login'              => 'login.php',
                        'receivers'          => 'receivers2.php',
                        'receiverformupdate' => 'receiverformupdate2.php',
                        'receiverforminsert' => 'receiverforminsert2.php',
                        'sms2oneform'        => 'sms2oneform.php',
                        'sms2allform'        => 'sms2allform.php',
                        'smsstatus'          => 'smsstatus2.php',
                    ];
                    
                    $file = $routes[$cmd] ?? 'receivers2.php';
                    require_once $file;
                    ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>