<?php
//require_once 'Membership.php';
//$membership = New Membership();
//$membership->confirm_Admin();
//$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['game_name']) && !empty($_POST['company']) ) {
    //$_SESSION['spil_navn'] = $_POST['spil_navn'];
    $response = $mysql->create_game($_POST['game_name'],$_POST['company']);
    //header('location: spil_GUI.php');
}

?>

<!DOCTYPE html>

<html lang="da">
<head>

  <title>setup - create game</title>

  <meta name="keywords" content="stuff" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf8">

</head>

<body>

<form method="post" action="">
    game name: <input type="text" name="game_name"><br/>
    company: <input type="text" name="company"><br/>
    <input type="submit" value="Create game">
</form>

</body>
</html>	


