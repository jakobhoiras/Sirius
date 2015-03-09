<?php

require 'mysql_json.php';
require 'json_creater.php';
require 'Mysql.php';

$json = new json();
$mysql = new mysql_json();
$teamID = $mysql -> get_teamID();
$Mysql = new Mysql_spil();
$games = $Mysql->get_games();
# find the game name and set the vairable
for ($i = 0; $i < sizeof($games); $i++) {
  	if ($games[$i][1] === $_SESSION['cg']) {
	    $gameID = $games[$i][0];
    }
}

for ($i=0; $i<sizeof($teamID); $i++){
	$array = array(
					"url"=>'http://www.matkonsw.com/sirius/getGame.php',
					"gameId"=>$gameID,
					"teamId"=>$teamID[$i][0],
				  );
	$path = 'Games/' . $_SESSION['cg'] . "/json/identify_team_" . $teamID[$i][0];
	$json -> createJson($array,$path);
}

include 'create_qr_codes.php';

echo create_qr_codes();
