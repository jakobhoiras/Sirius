<?php

require 'mysql_json.php';
require 'json_creater.php';

$json = new json();
$mysql = new mysql_json();
$teamID = $mysql -> get_teamID();

for ($i=0; $i<sizeof($teamID); $i++){
	$array = array(
                      "targetFile": "http://t-a-g.dk/games/" . $_SESSION['cg'] . "/targetFile.php"
                      "mapFile": "http://t-a-g.dk/games/" . $_SESSION['cg'] . "/mapFile.zip",
                      "centerLat": "55.7259",
                      "centerLong": "12.4433",
                      "minLat": "55.7080",
                      "minLong": "12.4046",
                      "maxLat": "55.7409",
                      "maxLong": "12.4766",
                      "questionFile": "http://t-a-g.dk/games/" . $_SESSION['cg'] . "/questionfile.zip",
                      "answerFile": "http://t-a-g.dk/games/" . $_SESSION['cg'] . "/answerfile.zip",
                      "postCoords": "http://t-a-g.dk/action/postCoords.php",
                      "getCoords": "http://t-a-g.dk/action/getCoords.php",
                      "postAnswer": "http://t-a-g.dk/action/postAnswer.php"
				  );
	$path = $_SESSION['cg'] . "/json/identify_team_" . $teamID[$i][0];
	$json -> createJson($array,$path);
}

include 'create_qr_codes.php';

echo create_qr_codes();
