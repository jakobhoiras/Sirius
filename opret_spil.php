<?php
/*require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();*/
require 'Mysql.php';
$mysql = new Mysql_spil();
$res='n';
if( $_POST && !empty($_POST['game_name']) && !empty($_POST['company']) ) {
	$res = $mysql -> check_if_game_exists($_POST['game_name']);
	if($res == 'success'){
    	$response = $mysql->create_game($_POST['game_name'],$_POST['company']);
	}
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
            <input type="button" value="Back to start" onclick="change_page('start')"/><br>





            game name:<br>
            <input type="text" name="game_name"><br/>
            company:<br>
            <input type="text" name="company"><br/>
            <input type="submit" value="Create game">
        </form>
		<p><?php if($res != 'n'){echo $res;}else{echo "";} ?></p>
    </body>
</html>	

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>


