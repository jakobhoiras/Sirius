<?php

require 'Mysql.php';

class getCoords {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function getCoord() {

        if (isset($_POST['teamId'], $_POST['gameId'])) {

            $Mysql = new Mysql_spil();
            $games = $Mysql->get_games();

            $gameId = $_POST['gameId'];
            $teamId = $_POST['teamId'];

            for ($i = 0; $i < sizeof($games); $i++) {
                if ($games[$i][0] == $gameId) {
                    $gameName = $games[$i][1];
                }
            }
            $query = "SELECT * FROM GAME_" . $gameName . ".Team_pos_" . $teamId . " ORDER BY count DESC LIMIT 1";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->execute();
                $result = $stmt->get_result();
                if ($coords = $result->fetch_all()) {
                    $stmt->close();
                }
            }

            $array = array(
                "latitude" => $coords[0][2] . "",
                "longitude" => $coords[0][1] . "",
                "timestamp" => $coords[0][3] . ""
            );
			header('Content-Type: application/json');
            echo json_encode($array);
        }
    }

}

$func = new getCoords();
$func->getCoord();


