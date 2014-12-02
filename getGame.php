<?php

require_once 'mysql.php';

function createJson($teamId, $gameId) {

    $gameCoords = getGameCoords($gameId);

    $array = array(
        "targetFile" => "http//:t-a-g.dk/games/" . $gameId . "/targetFile.php",
        "mapFile" => "http://t-a-g.dk/games/" . $gameId . "/mapFile.zip",
        "centerLat" => $gameCoords[0],
        "centerLong" => $gameCoords[1],
        "minLat" => $gameCoords[2],
        "minLong" => $gameCoords[3],
        "maxLat" => $gameCoords[4],
        "maxLong" => $gameCoords[5],
        "questionFile" => "http://t-a-g.dk/games/" . $gameId . "/questionfile.zip",
        "answerFile" => "http://t-a-g.dk/games/" . $gameId . "/answerfile.zip",
        "postCoords" => "http://t-a-g.dk/action/postCoords.php",
        "getCoords" => "http://t-a-g.dk/action/getCoords.php",
        "postAnswer" => "http://t-a-g.dk/action/postAnswer.php"
    );

    header('Content-Type: application/json');
    $array2 = json_encode($array);
    echo $array2;
}

function getGameCoords($id) {

    $gameCoords = array();
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
    array_push($gameCoords, $centerLat, $centerLong, $minLat, $minLong, $maxLat, $maxLong);
    return $gameCoords;
}

if (isset($_POST['teamId'], $_POST['gameId'])) {
    $teamId = $_POST['teamId'];
    $gameId = $_POST['gameId'];
    createJson($teamId, $gameId);
}