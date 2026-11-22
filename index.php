<?php
    ob_start();
    require_once 'init.php';
    /** @var string $current_lang */
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars((string)$current_lang, ENT_QUOTES, 'UTF-8'); ?>" dir="ltr">
<head>
    <title>SMS Tools</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css?v=<?php echo htmlspecialchars((string)filemtime('style2.css'), ENT_QUOTES, 'UTF-8'); ?>">

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
                $safe_listid = htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8');
                $safe_phone  = htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8');

                if ($listid) {
                    echo "<a href='index.php?cmd=receivers&listid={$safe_listid}&phone={$safe_phone}'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                } else {
                    echo "<a href='index.php'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                }
            ?>
        </header>

        <nav id="topmenu">
            <ul>
                <?php
                    // I <nav id="topmenu"> i index.php:
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        if (empty($_SESSION['must_change_password'])) {
                            echo "<li><a href='index.php?cmd=receivers&listid=" . htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8') . "&phone=" . htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8') . "' class='menu'>" . htmlspecialchars(__('nav_receivers'), ENT_QUOTES, 'UTF-8') . "</a></li>";
                            echo "<li><a href='index.php?cmd=receiverforminsert&listid=" . htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8') . "&phone=" . htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8') . "' class='menu'>" . htmlspecialchars(__('nav_new_receiver'), ENT_QUOTES, 'UTF-8') . "</a></li>";
                            echo "<li><a href='index.php?cmd=sms2allform&listid=" . htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8') . "&phone=" . htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8') . "' class='menu'>" . htmlspecialchars(__('nav_send_sms'), ENT_QUOTES, 'UTF-8') . "</a></li>";
                            echo "<li><a href='index.php?cmd=smsstatus&listid=" . htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8') . "&phone=" . htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8') . "' class='menu'>" . htmlspecialchars(__('nav_sms_log'), ENT_QUOTES, 'UTF-8') . "</a></li>";
                        }
                        echo "<li class='logout'><a href='logoff.php'>" . htmlspecialchars(__('nav_logout'), ENT_QUOTES, 'UTF-8') . "</a></li>";
                    }

                    // Preserve current page and query parameters when switching language
                    $query_da = http_build_query(array_merge($_GET, ['lang' => 'da']));
                    $query_en = http_build_query(array_merge($_GET, ['lang' => 'en']));
                ?>
                <li style="margin-left: auto; display: flex; align-items: center;">
                    <a href="index.php?<?php echo htmlspecialchars($query_da, ENT_QUOTES, 'UTF-8'); ?>" style="<?php echo $current_lang === 'da' ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.75;'; ?>">DA</a>
                    <a href="index.php?<?php echo htmlspecialchars($query_en, ENT_QUOTES, 'UTF-8'); ?>" style="<?php echo $current_lang === 'en' ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.75;'; ?>">EN</a>
                </li>
            </ul>
        </nav>

        <main id="main-content">
            <div class="container">
                <div class="content-card">
                    <?php
                        $routes = [
                            'login'              => 'login.php',
                            'changepassword'     => 'changepassword.php',
                            'receivers'          => 'receivers2.php',
                            'receiverformupdate' => 'receiverformupdate2.php',
                            'receiverforminsert' => 'receiverforminsert2.php',
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
