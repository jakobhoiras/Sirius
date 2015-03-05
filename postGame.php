<?php
require_once 'postGameFun.php';
$postGame = New postGameFun();
$array = $postGame->getRute();
?>
<html lang="da">
    <head>

        <link rel="stylesheet" type="text/css" href="opgave.css">
        <title>Opsætning - opret spil</title>

        <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

        <script src='vincenty.js'></script>
       
</head>
    <body>
        <div></div>
        <span id="element"></span>
        <div></div>
        <script>
            var array = "<?php echo $array; ?>";
            var result = getDistance(array);
        </script>
    </body>
</html>

