<?php
    ob_start();
    require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="da" dir="ltr">
<head>
    <title>sms.3490.dk V.2.0</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css?v=<?php echo filemtime('style2.css'); ?>">

    <?php if ($cmd == "smsstatus"): ?>
        <?php endif; ?>

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
                if ($listid)
                {
                    print"<a href='index.php?cmd=receivers&listid=$listid&phone=$fromphone'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                }
                else
                {
                    print"<a href='index.php'><img src='banner2.jpg' alt='SMS Tools Banner'></a>"; 
                }
            ?>
        </header>

        <nav id="topmenu">
            <ul>
                <?php
                    // Vis kun menuen hvis vi er logget ind
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true)
                    {
                        print "<li><a href='index.php?cmd=receivers&listid=$listid&phone=$fromphone' class='menu'>Navneliste</a></li>  ";
                        print "<li><a href='index.php?cmd=receiverforminsert&listid=$listid&phone=$fromphone' class='menu'>Ny modtager</a></li> ";
                        print "<li><a href='index.php?cmd=sms2allform&listid=$listid&phone=$fromphone' class='menu'>Send SMS</a></li> ";
                        print "<li><a href='index.php?cmd=smsstatus&listid=$listid&phone=$fromphone' class='menu'>SMS log</a></li> ";
                        print "<li class='logout'><a href='logoff.php'>Log af</a></li>";
                    }
                ?>
            </ul>
        </nav>


        <main id="main-content">
            <div class="container">
                <div class="content-card">
                    <?php
                    $cmd = $_GET['cmd'] ?? 'receivers';
                    
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