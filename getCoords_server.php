<?php



//Gets and echos the present coordinate (lon,lat,time) from the server for a given teamID and GameID 

require_once 'Mysql.php';

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
            echo $coords[0][1] . ' ' . $coords[0][2] . ' ' . $coords[0][3]; 
        }
    }

}

$func = new getCoords();
$func->getCoord();


