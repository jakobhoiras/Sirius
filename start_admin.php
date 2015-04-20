<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();

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
    <form method="post" style="display:inline">
        <input type="submit" value="Log out" style="float:right" name="logout" /><br>
    </form>
    <div style="margin-left:auto; margin-right:auto; margin-top:150px">
    <form method="post" style="text-align:center">
        <input type="button" value="Create assignment" onclick="change_page('opret_opgave_type')"/><br>
        <input type="button" value="Create game" onclick="change_page('opret_spil')"/><br>
        <input type="button" value="Create user login" onclick="change_page('opret_bruger')"/><br>
        <input type="button" value="Create map" onclick="change_page('osm_gem_kort_JTD')"/><br>
        <input type="button" value="Choose game" onclick="change_page('eksisterende_spil')"/><br>
    </form>
    </div>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ( page_name + ".php");
    }
</script>
