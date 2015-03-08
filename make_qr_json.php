<?php

require 'mysql_json.php';
require 'json_creater.php';

$json = new json();
$mysql = new mysql_json();
$teamID = $mysql -> get_teamID();

for ($i=0; $i<sizeof($teamID); $i++){
	$array = array(
					"url"=>'http://www.matkonsw.com/sirius/getGame.php',
					"gameID"=>$_SESSION['cg'],
					"teamID"=>$teamID[$i][0],
				  );
	$path = 'Games/' . $_SESSION['cg'] . "/json/identify_team_" . $teamID[$i][0];
	$json -> createJson($array,$path);
}

include 'create_qr_codes.php';

echo create_qr_codes();
