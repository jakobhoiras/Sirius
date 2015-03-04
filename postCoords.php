<?php

require_once 'Mysql.php';
require_once 'get_distance.php';

class postCoords {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function postCoord() {

        if (isset($_POST['teamId'], $_POST['gameId'], $_POST['lat'], $_POST['long'], $_POST['timestamp'])) {

            $mysql = new Mysql_spil();
            $games = $mysql->get_games();

            $gameId = $_POST['gameId'];
            $teamId = $_POST['teamId'];
            $lat = $_POST['lat'];
            $long = $_POST['long'];
            $time = $_POST['timestamp'];

            for ($i = 0; $i < sizeof($games); $i++) {
                if ($games[$i][0] == $gameId) {
                    $gameName = $games[$i][1];
                }
            }
         
            $query = 'INSERT INTO GAME_' . $gameName . '.Team_pos_' . $teamId . '(lon, lat, time) VALUES (?, ?, ?)';

            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param('ddi', $lat, $long, $time);
                $stmt->execute();
                $stmt->close();
            }
            $progress = $mysql -> get_game_progress($gameName);
            $team_state = $mysql -> get_team_state($gameName, $teamId);
            if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
                $current_cp = $team_state[0][3];
            } else{
                $current_cp = $team_state[0][4];
            }
            $coords = $mysql -> get_coords($gameName, $teamID);
            $lon1 = $coords[0][1];
            $lat1 = $coords[0][2];
            $rute = $mysql -> get_rute($gameName, $teamID);
            if ( $current_cp < 6 ){
                $zoneID = $rute[0][$current_cp];
                if ( $zoneID != 0){
                    $zone = $mysql -> get_zone($gameName, $zoneID);
                    $radius = $zone[0][3];
                    $lat2 = $zone[0][1];
                    $lon2 = $zone[0][2];
                    if (Vincenty_Distance($lat1,$lon1,$lat2,$lon2) < $radius){
                        $mysql -> team_found_zone($gameName, $teamID);
                    }
                }
            }          
        }
    }
}

$func = new postCoords();
$func->postCoord();
