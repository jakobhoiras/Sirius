<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_zones();

$zones_num = sizeof($table);
for ($i=0; $i<$zones_num; $i++){
    echo $table[$i][0] . " ";
    echo $table[$i][1] . " ";
    echo $table[$i][2] . " ";
    echo $table[$i][3] . " ";
}

?>
