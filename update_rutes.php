<?php
require 'Mysql.php';

$rute = $_GET['rute'];
$action = $_GET['a'];

$rute_ar = explode(",", $rute);


if ($action == 'save') {
    $mysql = new Mysql_spil();
    $mysql->save_rute($rute_ar);
}
else if ($action == 'delete_rute'){
    $mysql = new Mysql_spil();
    $mysql->delete_rute($rute_ar);
}
else if ($action == 'edit'){
    $old_rute = $_GET['old_rute'];
    $old_rute_ar = explode(",", $old_rute);
    $mysql = new Mysql_spil();
    $mysql->edit_rute($rute_ar,$old_rute_ar);
}

?>
