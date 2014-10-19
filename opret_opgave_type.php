<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['opret_opgave']) ) {
    $response = $mysql->opret_opgave($_POST['opret_opgave'], $_SESSION['spil_navn']);
    header('location: opgave_GUI.php');
}

?>

<!DOCTYPE html>

<html lang="da">
<head>

  <title>Opsætning - opret spil</title>

  <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf8">

  <link rel="stylesheet" type="text/css" href="styleGrøn.css" />

  <style type="text/css">
  </style>

</head>

<body>

<form method="post" action="">
    <select name="opret_opgave">
        <option value="volvo">Volvo</option>
        <option value="saab">Saab</option>
        <option value="opel">Opel</option>
        <option value="audi">Audi</option>
    </select>  
    <input type="submit" value="Opret opgave">
</form>

</body>
</html>	

		<!--	 -->
