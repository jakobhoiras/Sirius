<?php

require_once 'Mysql.php';

if (isset($_POST['teamId'], $_POST['gameId'], $_POST['found'])){
	$gameId = $_POST['gameId'];
	$teamID = $_POST['teamId'];
	$satLock = $_POST['found'];
	$mysql = new Mysql_spil();
    $games = $mysql->get_games();
	for ($i = 0; $i < sizeof($games); $i++) {
	    if ($games[$i][0] == $gameId) {
	        $gameName = $games[$i][1];
        }
    }
	$mysql -> sat_lock_changed($gameName, $teamID, $satLock);
}
