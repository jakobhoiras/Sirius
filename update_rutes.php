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

?>
