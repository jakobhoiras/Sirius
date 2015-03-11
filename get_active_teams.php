<?php 

function get_active_teams(){
	$screen = $_POST['screen'];
	$group = $_POST['group'];
	$mysql = new Mysql_spil();
	$zones = $mysql -> get_zones();
	$teams = $mysql -> get_teams();
	$rutes = $mysql -> get_rutes();
	$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
	$progress = $mysql -> get_game_progress($_SESSION['cg']);

	$overset = $mysql->get_overview_settings();
	$n_teams = sizeof($teams);
	$teams_per_screen = $n_teams / ($overset[0][0]);
	$teams_per_group = $n_teams / ($overset[0][0] * $overset[0][1]);
	$start_team = $teams_per_screen*($screen-1) + $teams_per_group*($group-1);
	$end_team = $teams_per_screen*$screen - $teams_per_group*($overset[0][1]-$group);
	$active_teams = array();
	for ($i=$start_team; $i<$end_team; $i++){
		array_push($active_teams, $teams[$i][0]);
	}
	return $active_teams;
}

?>
