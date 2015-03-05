<?php

require_once 'Mysql.php';

class postGameFun {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function getRute() {

        $Mysql = new Mysql_spil();
        $games = $Mysql->get_games();
        $rutes = array();

        for ($i = 0; $i < count($games); $i++) {
            if ($games[$i][3] === 1) {
                $gameName = $games[$i][1];
            }
        }
        $_SESSION['cg'] = $gameName;
        $teams = $Mysql->get_teams();
        for ($i = 0; $i < count($teams); $i++) {
            $id = $teams[$i][0];
            $query = "SELECT * FROM Game_" . $gameName . ".Team_pos_" . $id;
            $rute = array();
            $rute[0] = array($id);
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->execute();
                $result = $stmt->get_result();
                if ($coords = $result->fetch_all()) {
                    $stmt->close();
                }
                for ($j = 0; $j < count($coords); $j++) {
                array_push($rute, $coords[$j]);
                }
            }
            array_push($rutes, $rute);
        }
        $rutes2 = json_encode($rutes);
        return $rutes2;
    }

}
