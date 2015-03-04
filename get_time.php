<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$time = $mysql -> get_time($_SESSION['cg']);

if ($_GET['func'] == 'first_half'){
    echo $time[0][1]*1000;
}

if ($_GET['func'] == 'second_half'){
    echo $time[0][3]*1000;
}
