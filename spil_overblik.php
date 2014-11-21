<?php
/*require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();
*/

$current_game = $_GET['cg'];
session_start();
$_SESSION['cg'] = $current_game;
?>

<html>
    <head>
        <title>
            Spil menu
        </title>
    </head>
    <h1>
        <p></p>
    </h1>
    <form method="post">
        <input type="button" value="importer opgaver" onclick="change_page('importer_opgaver')"/><br>
        <input type="button" value="importer kort" onclick="change_page('importer_kort')"/><br>
        <input type="button" value="Zoner" onclick="change_page('draw_zone')"/><br>
        <input type="button" value="Ruter" onclick="change_page('create_rutes')"/><br>
        <input type="button" value="Hold" onclick="change_page('create_teams')"/><br>
    </form>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>
