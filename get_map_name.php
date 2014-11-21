<?php

require 'Mysql.php';
$mysql = new Mysql_spil();

$map_name = $mysql -> get_map();
$table = $mysql -> get_maps();

for ($i=0; $i<sizeof($table); $i++){
    if($table[$i][0]==$map_name[0][0]){
         echo implode(" ", $table[$i]);
    }
}
?>
