<?php 

require 'Mysql_opgave.php';

$mysql = new Mysql_assignment();
$mysql -> delete_imported_assignment($_GET['ass']);

?>
