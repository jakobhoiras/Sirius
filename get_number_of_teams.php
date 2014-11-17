<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_teams();

$number_of_teams = sizeof($table);
echo $number_of_teams;


?>
