<?php


"""
Receives gameID and teamID from tablet and creates a JSON-object with all the links needed for the tablet
"""
require_once 'mysql.php';

function createJson($teamId, $gameId) {

    $mapCoords = getMapCoords($gameId);
    $baseCoords = getBaseCoords($gameId);
    $time = getTime($gameId);

    $array = array(
        "gameId" => $gameId,
        "teamId" => $teamId,
        "targetFile" => "http//:t-a-g.dk/Games/" . $gameId . "/targetFile.php",
        "mapFile" => "http://t-a-g.dk/tiles/" . $mapCoords[6] . ".zip",
        "centerLat" => $mapCoords[0],
        "centerLong" => $mapCoords[1],
        "minLat" => $mapCoords[2],
        "minLong" => $mapCoords[3],
        "maxLat" => $mapCoords[4],
        "maxLong" => $mapCoords[5],
        "homeMinLat" => $baseCoords[2],
        "homeMinLong" => $baseCoords[3],
        "homeMaxLat" => $baseCoords[4],
        "homeMaxLong" => $baseCoords[5],
        "gameRunTime" => $time[0][0],
        "gameRounds" => 2,
        "postStateChange" => "http://t-a-g.dk/action/postStateChange.php",
        "getState" => "http://t-a-g.dk/action/getState.php",
        "questionFile" => "http://t-a-g.dk/Games/" . $gameId . "/questionfile.zip",
        "postCoords" => "http://t-a-g.dk/action/postCoords.php",
        "getCoords" => "http://t-a-g.dk/action/getCoords.php",
        "postAnswer" => "http://t-a-g.dk/action/postAnswer.php"
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
    $dLat = $dx/$R
    $dLon = $dx/($R*cos(pi()*$cenLat/180))
    $homeMinLat = $cenLat - $dLat;
    $homeMinLon = $cenLon - $dLon;
    $homeMaxLat = $cenLat + $dLat;
    $homeMaxLon = $cenLon + $dLon;

    array_push($baseCoords, $cenLat, $cenLon, $homeMinLat, $homeMinLon, $homeMaxLat, $homeMaxLon);
    return $baseCoords;
}

function getTime($id) {
    $Mysql = new Mysql_spil();
    $time = $Mysql->get_time();
    return $time;
}

if (isset($_POST['teamId'], $_POST['gameId'])) {
    $teamId = $_POST['teamId'];
    $gameId = $_POST['gameId'];
    createJson($teamId, $gameId);
}
