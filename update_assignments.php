<?php
require 'Mysql.php';

$name = $_GET['a'];

$mysql = new Mysql_spil();
$mysql->save_assignment($name);

?>
