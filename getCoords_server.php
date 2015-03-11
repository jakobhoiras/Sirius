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

        if (isset($_POST['gameId'], $_POST['screen'], $_POST['group'])) {

            $mysql = new Mysql_spil();
            $games = $mysql->get_games();

            $gameId = $_POST['gameId'];
			require 'get_active_teams.php';
            $active_teams = get_active_teams();
            for ($i = 0; $i < sizeof($games); $i++) {
                if ($games[$i][0] == $gameId) {
                    $gameName = $games[$i][1];
                }
            }
			$coords_ar = array();
			for ($i = 0; $i < sizeof($active_teams); $i++){
		        $query = "SELECT * FROM GAME_" . $gameName . ".Team_pos_" . $active_teams[$i] . " ORDER BY count DESC LIMIT 1";
		        if ($stmt = $this->conn->prepare($query)) {
		            $stmt->execute();
		            $result = $stmt->get_result();
		            if ($coords = $result->fetch_all()) {
		                $stmt->close();
		            }
		        }
		        array_push($coords_ar,array($coords[0][1],$coords[0][2], $coords[0][3],$active_teams[$i]));
			} 
			echo json_encode($coords_ar);
        }
    }

}

$func = new getCoords();
$func->getCoord();


