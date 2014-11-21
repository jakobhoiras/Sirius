<?php
require 'Mysql.php';

$GPSx = floatval($_GET['x']);
$GPSy = floatval($_GET['y']);
$radius = floatval($_GET['r']);
$action = $_GET['a'];

if ($action == 'save') {
    $mysql = new Mysql_spil();
    $mysql->save_zones($GPSx, $GPSy, $radius);
}
else if ($action == 'delete_zone'){
    $mysql = new Mysql_spil();
    $mysql->delete_zones($GPSx, $GPSy, $action);
}
else if ($action == 'link'){
    $zoneID = intval($_GET['x']);
    $assID = intval($_GET['y']);
    $mysql = new Mysql_spil();
    $mysql->update_zone_ID($zoneID, $assID); 
}
else if ($action == 'saveBase'){
    $mysql = new Mysql_spil();
    $mysql->save_base($GPSx, $GPSy, $radius);
}
else if ($action == 'delete_base'){
    $mysql = new Mysql_spil();
    $mysql->delete_zones($GPSx, $GPSy, $action);
}

?>
