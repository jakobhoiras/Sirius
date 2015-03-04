<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$state = $mysql -> get_state($_SESSION['cg'], 1);

echo $state[0];
