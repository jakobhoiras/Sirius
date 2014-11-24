<?php

require 'mysql_json.php';
require 'json_creater.php';

echo $_SESSION['cg'];
$json = new json();
$mysql = new mysql_json();
echo 'here';
$teamID = $mysql -> get_teamID();
echo $teamID[0][0];
for ($i=0; $i<sizeof($teamID); $i++){
	$array = array(
					"url"=>'http://t-a-g.dk/getGame.php',
					"gameID"=>$_SESSION['cg'],
					"teamID"=>$teamID[$i][0],
				  );
	$path = $_SESSION['cg'] . "/json/identify_team_" . $teamID[$i][0];
	$json -> createJson($array,$path);
}




