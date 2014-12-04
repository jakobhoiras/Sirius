<?php

require_once 'Mysql.php';

class postCoords {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function postCoord() {

        if (isset($_POST['teamId'], $_POST['gameId'], $_POST['lat'], $_POST['long'], $_POST['timestamp'])) {

            $Mysql = new Mysql_spil();
            $games = $Mysql->get_games();

            $gameId = $_POST['gameId'];
            $teamId = $_POST['teamId'];
            $lat = $_POST['lat'];
            $long = $_POST['long'];
            $time = $_POST['timestamp'];

            for ($i = 0; $i < sizeof($games); $i++) {
                if ($games[$i][0] === $gameId) {
                    $gameName = $games[$i][1];
                }
            }

            $query = 'INSERT INTO Game_' . $gameName . '.Team_pos_' . $gameId . ' VALUES (?, ?, ?)';

            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param('ddi', $lat, $long, $time);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

$func = new postCoords();
$func->postCoord();
