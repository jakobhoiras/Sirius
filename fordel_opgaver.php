<?php
require 'Mysql.php';

$rute_length = $_GET['rute_length'];
$n_teams = $_GET['n_hold'];

$mysql = new Mysql_spil();
$mysql -> drop_teams_assignments_table();
$mysql -> create_teams_assignments_table($_GET['rute_length']);

$ass = $mysql -> get_assignments();
$teams = $mysql -> get_teams();

for ($i=0; $i<sizeof($teams); $i++){
	$ass_new = $ass;
	shuffle($ass_new);
	$mysql -> save_teams_assignments($teams[$i][0], $ass_new);
}

?>
