<?php

require_once 'mysql.php';

class targetFile {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function makeTargets() {

        $gameId = $_POST['gameId'];
        $teamId = $_POST['teamId'];

        $Mysql = new Mysql_spil();
        $games = $Mysql->get_games();

        for ($i = 0; $i < sizeof($games); $i++) {
            if ($games[$i][0] === $gameId) {
                $gameName = $games[$i][1];
                $_SESSION['cg'] = $gameName;
            }
        }

        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Teams_assignments";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($assigns = $result->fetch_all()) {
                $stmt->close();
            }
        }

        $rutes = $Mysql->get_rutes();
        $zones = $Mysql->get_zones();
        $team = $Mysql->get_teams();

        for ($i = 0; $i < sizeof($team); $i++) {
            if ($team[$i][0] === $teamId) {
                $ruteId = $team[$i][1];
            }
        }

        $target0 = ($this->makeTarget($zones, $assigns, $rutes, $teamId, 1, $ruteId));
        $target1 = ($this->makeTarget($zones, $assigns, $rutes, $teamId, 2, $ruteId));
        $target2 = ($this->makeTarget($zones, $assigns, $rutes, $teamId, 3, $ruteId));
        $target3 = ($this->makeTarget($zones, $assigns, $rutes, $teamId, 4, $ruteId));
        $target4 = ($this->makeTarget($zones, $assigns, $rutes, $teamId, 5, $ruteId));

        $array = json_encode(array(
            "target[0]" => $target0,
            "target[1]" => $target1,
            "target[2]" => $target2,
            "target[3]" => $target3,
            "target[4]" => $target4,
        ));
        echo $array;
    }

    function makeTarget($zones, $assigns, $rutes, $teamId, $count, $ruteId) {

        $startAssign = (($count * 3) - 2);
        for ($i = 0; $i < sizeof($rutes); $i++) {
            if ($rutes[$i][0] === $ruteId) {
            
                $zoneId = $rutes[$i][$count];
            }
        }
        for ($i = 0; $i < sizeof($zones); $i++) {
            if ($zones[$i][0] === $zoneId) {
                
                $tlat = $zones[$i][1];
                $tlong = $zones[$i][2];
            }
        }
        for ($i = 0; $i < sizeof($assigns); $i++) {
            if ($assigns[$i][0] === $teamId) {
            
                $q1 = $assigns[$i][$startAssign];
                $q2 = $assigns[$i][$startAssign + 1];
                $q3 = $assigns[$i][$startAssign + 2];
            }
        }
        $target = array(
            "tLat" => $tlat,
            "tLong" => $tlong,
            "tName" => $zoneId,
            "tQuestion[0]" => $q1,
            "tQuestion[1]" => $q2,
            "tQuestion[2]" => $q3
        );
        return $target;
    }

}

if (isset($_POST['gameId'], $_POST['teamId'])) {
    $func = new targetFile();
    $func->makeTargets();
}
