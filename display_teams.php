<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_teams();

//$res = '';
//for ($i=0; $i<sizeof($table); $i++){
//$res = $res . implode(" ", $table[$i]) . ' ';
//}

echo json_encode($table);


?>
