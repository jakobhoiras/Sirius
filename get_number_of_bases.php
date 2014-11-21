<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
echo $mysql->get_number_of_bases();

?>
