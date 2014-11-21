<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_rutes();
$res = '';
for ($i=0; $i<sizeof($table); $i++){
$res = $res . implode(" ", array_slice($table[$i],1)) . ' ';
}

echo $res;

?>
