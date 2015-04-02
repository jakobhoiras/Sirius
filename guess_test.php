<?php 

require 'Mysql.php';

$mysql = new Mysql_spil();
$mysql -> make_guess('MatkonTest',2,5);
