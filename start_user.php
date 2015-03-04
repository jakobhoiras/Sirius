<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_User();
$membership->check_Active();
//require 'Mysql_create_game.php';
//$mysql = new Mysql_spil();

//if( $_POST && !empty($_POST['game_name']) && !empty($_POST['company']) ) {
    //$_SESSION['spil_navn'] = $_POST['spil_navn'];
//    $response = $mysql->create_game($_POST['game_name'],$_POST['company']);
    //header('location: spil_GUI.php');
//}
?>

<html>
    <head>
        <title>
            General options
        </title>
    </head>
        <div style="margin-left:auto; margin-right:auto; margin-top:150px"> 
            <form method="post" style="text-align:center">
                <input type="button" value="create assignments" onclick="change_page('opret_opgave_type')"/><br>
                <input type="button" value="create teams" onclick="change_page('create_teams')"/><br>
                <input type="button" value="Screens" onclick="change_page('screen_options')"/><br>
            </form>
        </div>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>
