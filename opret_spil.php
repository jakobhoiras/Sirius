<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
//require 'Mysql.php';
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

        <title>Create game</title>

        <meta name="keywords" content="stuff" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

    </head>

    <body>
        <div style="width:10%">
            <button id="back" type="button" onclick=change_page('start_admin')>Back to start</button>
        </div>
        <div style="margin-left:auto; margin-right:auto; margin-top:150px; width:400px;">
        <form method="post" action="">
            Game name:<br>
            <input type="text" name="game_name"><br/>
            Company:<br>
            <input type="text" name="company"><br/>
            <input type="submit" value="Create game">
        </form>
		<p><?php if($res != 'n'){echo $res;}else{echo "";} ?></p>
        </div>
    </body>
</html>	

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>


