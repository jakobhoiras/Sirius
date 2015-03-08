<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
//require 'Mysql_create_game.php';
//$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['game_name']) && !empty($_POST['company']) ) {
    //$_SESSION['spil_navn'] = $_POST['spil_navn'];
    $response = $mysql->create_game($_POST['game_name'],$_POST['company']);
    //header('location: spil_GUI.php');
}

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

?>

<!DOCTYPE html>

<html lang="da">
    <head>

        <title>Create user</title>

        <meta name="keywords" content="stuff" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

    </head>

    <body>
        <div style="width:100%">
            <button id="back" type="button" onclick=change_page('start_admin')>Start menu</button>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" />
            </form>
        </div>
        <div style="margin-left:auto; margin-right:auto; margin-top:150px; width:400px;">
        <form method="post">
            Username: <input type="text" name="user"/><br>
            Password: <input type="text" name="pwd"/><br>
            Game: <input type="text" name="game"/><br>
            Skal dette være en admin bruger?: <input type="checkbox" name="perm" value="yes"/><br>
            <input type="submit" value="Opret bruger" name="submit1"/>
        </form>
        </div>
    </body>
</html>	

<script>
    function change_page(page_name) {
        window.location.href = ( page_name + ".php");
    }
</script>
<?php
if (isset($_POST['submit1'])) {
    $membership->create_User();
}
?>
