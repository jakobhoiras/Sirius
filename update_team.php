<?php
require 'Mysql.php';


$action = $_GET['a'];

//$rute_ar = explode(",", $rute);


if ($action == 'edit') {
    $name1 = $_GET['name1'];    
    $name2 = $_GET['name2'];
    $name3 = $_GET['name3'];
    $name4 = $_GET['name4'];
    $old_name1 = $_GET['old_name1'];
    $old_name2 = $_GET['old_name2'];
    $old_name3 = $_GET['old_name3'];
    $old_name4 = $_GET['old_name4'];
    $mysql = new Mysql_spil();
    $mysql->edit_team($name1,$name2,$name3,$name4,$old_name1,$old_name2,$old_name3,$old_name4);
}
else if ($action == 'delete_team'){
    $teamID = $_GET['teamID'];
    $mysql = new Mysql_spil();
    $mysql->delete_team($teamID);
}

?>
