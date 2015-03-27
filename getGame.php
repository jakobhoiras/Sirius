<?php



//Receives gameID and teamID from tablet and creates a JSON-object with all the links needed for the tablet

require_once 'Mysql.php';
require 'targetFile.php';

function createJson($teamId, $gameId) {

    $mapCoords = getMapCoords($gameId);
    $baseCoords = getBaseCoords($gameId);
    $time = getTime($gameId);
	$targetfile = new targetFile();
	$targetfile -> create_target_file($gameId, $teamId);

	$Mysql = new Mysql_spil();
    $games = $Mysql->get_games();
    # find the game name and set the vairable
    for ($i = 0; $i < sizeof($games); $i++) {
    	if ($games[$i][0] === $gameId) {
           $gameName = $games[$i][1];
        }
    }
	
    $array = array(
        "gameId" => $gameId,
        "teamId" => $teamId,
        "targetFile" => "http://www.matkonsw.com/sirius/Games/" . $gameName . "/targetfile$teamId.json",
        "mapFile" => "http://www.matkonsw.com/tiles/" . $mapCoords[6] . ".zip",
        "centerLat" => $mapCoords[1],
        "centerLon" => $mapCoords[0],
        "minLat" => $mapCoords[3],
        "minLon" => $mapCoords[2],
        "maxLat" => $mapCoords[5],
        "maxLon" => $mapCoords[4],
		"homeLat" => $baseCoords[0],
		"homeLon" => $baseCoords[1],
        "homeMinLat" => $baseCoords[2],
        "homeMinLon" => $baseCoords[3],
        "homeMaxLat" => $baseCoords[4],
        "homeMaxLon" => $baseCoords[5],
		"homeRadius" => $baseCoords[6],
        "gameRunTime" => $time[0][0],
        "gameRounds" => 2,
        "postStateChange" => "http://www.matkonsw.com/sirius/postStateChange.php",
        "getState" => "http://www.matkonsw.com/sirius/getState.php",
		"getRunTime" => "http://www.matkonsw.com/sirius/getRunTime.php",
        "questionFile" => "http://www.matkonsw.com/sirius/Games/" . $gameName . "/questionfile.zip",
        "postCoords" => "http://www.matkonsw.com/sirius/postCoords.php",
        "getCoords" => "http://www.matkonsw.com/sirius/getCoords.php",
        "postAnswer" => "http://www.matkonsw.com/sirius/postAnswer.php",
		"foundZone" => "http://www.matkonsw.com/sirius/foundZone.php"
    );

    header('Content-Type: application/json');
    $array2 = json_encode($array);
    echo $array2;
}

function getMapCoords($id) {

    $mapCoords = array();
    $Mysql = new Mysql_spil();
    $games = $Mysql->get_games();

    for ($i = 0; $i < sizeof($games); $i++) {
        if ($games[$i][0] === $id) {
            $gameName = $games[$i][1];
        }
    }

    $_SESSION['cg'] = $gameName;
    $map = $Mysql->get_map();
    $mapId = $map[0][0];
    $maps = $Mysql->get_maps();

    for ($i = 0; $i < sizeof($maps); $i++) {
        if ($maps[$i][0] === $mapId) {
            $centerLat = $maps[$i][1];
            $centerLong = $maps[$i][2];
            $minLat = $maps[$i][1] - 0.09; 
            $maxLat = $maps[$i][1] + 0.09;
            $minLong = $maps[$i][2] - 0.18;
            $maxLong = $maps[$i][2] + 0.18;
        }
    }
    array_push($mapCoords, $centerLat, $centerLong, $minLat, $minLong, $maxLat, $maxLong, $mapId);
    return $mapCoords;
}

function getBaseCoords($id) {

    $baseCoords = array();
    $Mysql = new Mysql_spil();

    $base = $Mysql->get_bases();
    $R = 6378137;
    $cenLon = $base[0][1];
    $cenLat = $base[0][2];
    $radius = $base[0][3];
    $dx = $radius / sqrt(2);
    $dLat = $dx/$R;
    $dLon = $dx/($R*cos(pi()*$cenLat/180));
    $homeMinLat = $cenLat - $dLat * 180 / pi();
    $homeMinLon = $cenLon - $dLon * 180 / pi();
    $homeMaxLat = $cenLat + $dLat * 180 / pi();
    $homeMaxLon = $cenLon + $dLon * 180 / pi();

    array_push($baseCoords, $cenLat, $cenLon, $homeMinLat, $homeMinLon, $homeMaxLat, $homeMaxLon, $radius);
    return $baseCoords;
}

function getTime($id) {
    $Mysql = new Mysql_spil();
    $time = $Mysql->get_time($_SESSION['cg']);
    return $time;
}


if (isset($_POST['teamId'], $_POST['gameId'])) {
    $teamId = intval($_POST['teamId']);
    $gameId = intval($_POST['gameId']);
    createJson($teamId, $gameId);
}

?>
