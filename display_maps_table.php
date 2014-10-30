<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_maps();
$res = '';
for ($i=0; $i<sizeof($table); $i++){
$res = $res . implode(" ", $table[$i]) . ' ';
}
$res = substr($res, 0, -1);
echo $res;

?>
