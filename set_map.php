<?php

require 'Mysql.php';
$mysql = new Mysql_spil();
$mysql -> save_map_link($_GET['map_name']);
?>
