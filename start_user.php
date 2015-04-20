<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_User();
$membership->check_Active();
//require 'Mysql_create_game.php';
//$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}
?>

<html>
    <head>
        <title>
            General options
        </title>
    </head>
        <div style="width:100%">
            <form method="post">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
        <div style="margin-left:auto; margin-right:auto; margin-top:150px"> 
            <form method="post" style="text-align:center">
                <input type="button" value="Create assignments" onclick="change_page('opret_opgave_type')"/><br>
                <input type="button" value="Screens" onclick="change_page('screen_options')"/><br>
            </form>
        </div>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ( page_name + ".php");
    }
</script>
