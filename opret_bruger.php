<!--?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['game_name']) && !empty($_POST['company']) ) {
    //$_SESSION['spil_navn'] = $_POST['spil_navn'];
    $response = $mysql->create_game($_POST['game_name'],$_POST['company']);
    //header('location: spil_GUI.php');
}

?-->

<!DOCTYPE html>

<html lang="da">
    <head>

        <title>Lav ny bruger</title>

        <meta name="keywords" content="stuff" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

    </head>

    <body>
        <form method="post" action="">
            <input type="button" value="Back to start" onclick="change_page('start')"/><br>
        </form>
        <h1> Opret bruger </h1>
        <form method="post">
            Brugernavn: <input type="text" name="user"/><br>
            Password: <input type="text" name="pwd"/><br>
            Skal dette være en admin bruger?: <input type="checkbox" name="perm" value="yes"/><br>
            <input type="submit" value="Opret bruger" name="submit1"/>
        </form>
    </body>
</html>	

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>
<?php
require 'Membership.php';
if (isset($_POST['submit1'])) {
    $member = new Membership();
    $member->create_User();
}
?>