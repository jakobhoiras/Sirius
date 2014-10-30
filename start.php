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

<html>
    <head>
        <title>
            Forside
        </title>
    </head>
    <h1>
        <p>opgaver du kan foretage dig:</p>
    </h1>
    <form method="post">
        <input type="button" value="opret opgave" onclick="change_page('opret_opgave_type')"/><br>
        <input type="button" value="opret spil" onclick="change_page('opret_spil')"/><br>
        <input type="button" value="opret bruger" onclick="change_page('opret_bruger')"/><br>
    </form>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>