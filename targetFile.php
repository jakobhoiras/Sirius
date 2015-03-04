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
        # find the game name and set the vairable
        for ($i = 0; $i < sizeof($games); $i++) {
            if ($games[$i][0] === $gameId) {
                $gameName = $games[$i][1];
                $_SESSION['cg'] = $gameName;
            }
        }
        # get the list of shuffled assigments for all teams
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Teams_assignments";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($assigns = $result->fetch_all()) {
                $stmt->close();
                $nZones = (sizeof($assigns[0])-1)/3;
            }
        }

        $rutes = $Mysql->get_rutes(); #contents of Rutes table
        $zones = $Mysql->get_zones(); #contents of Zones table
        $team = $Mysql->get_teams(); #contents of Teams table
        # find ruteIDs for each team
        for ($i = 0; $i < sizeof($team); $i++) {
            if ($team[$i][0] === $teamId) {
                $ruteId1 = $team[$i][1];
                $ruteId2 = $team[$i][2];
            }
        }
        
        $target_array = array();
        for ($i=0; $i < $nZones; $i++){
            array_push($target_array, ($this->makeTarget($zones, $assigns, $rutes, $teamId, $i+1, $ruteId1, $ruteId2)));
        }

        echo json_encode($target_array);
    }

    function makeTarget($zones, $assigns, $rutes, $teamId, $count, $ruteId1, $ruteId2) {
        ### FIRST HALF
        # finds the first of three indexes for the assignments in teams_assignments table; three assignments for each zone!
        $startAssign1 = (($count * 3) - 2);  
        
        # Finds the zoneID 
        for ($i = 0; $i < sizeof($rutes); $i++) {
            if ($rutes[$i][0] === $ruteId1) {
            
                $zoneId = $rutes[$i][$count];
            }
        }
        # Finds the coordinates
        for ($i = 0; $i < sizeof($zones); $i++) {
            if ($zones[$i][0] === $zoneId) {
                
                $tlat = $zones[$i][1];
                $tlong = $zones[$i][2];
                $tAcceptRange = $zones[$i][3];
            }
        }
        # Finds the assignments
        for ($i = 0; $i < sizeof($assigns); $i++) {
            if ($assigns[$i][0] === $teamId) {
            
                $q1 = $assigns[$i][$startAssign];
                $q2 = $assigns[$i][$startAssign + 1];
                $q3 = $assigns[$i][$startAssign + 2];
            }
        }
        # json-object for first half
        $target1 = array(
            "tLat" => $tlat, # latitude
            "tLong" => $tlong, #longitude
            "tName" => $zoneId, # zoneID
            "tAcceptRange" => $tAcceptRange, #Zone radius 
            "tQuestions" => 3, # number of assignments per zone; fixed to 3 for now!
            "tQuestion" => array($q1,$q2,$q3); #array with questions 
        );
        ### SECOND HALF
        $startAssign2 = ((3*$nZones)/2 + ($count * 3) - 2);
        # Finds the zoneID 
        for ($i = 0; $i < sizeof($rutes); $i++) {
            if ($rutes[$i][0] === $ruteId2) {
            
                $zoneId = $rutes[$i][$count];
            }
        }
        # Finds the coordinates
        for ($i = 0; $i < sizeof($zones); $i++) {
            if ($zones[$i][0] === $zoneId) {
                
                $tlat = $zones[$i][1];
                $tlong = $zones[$i][2];
                $tAcceptRange = $zones[$i][3];
            }
        }
        # Finds the assignments
        for ($i = 0; $i < sizeof($assigns); $i++) {
            if ($assigns[$i][0] === $teamId) {
            
                $q1 = $assigns[$i][$startAssign2];
                $q2 = $assigns[$i][$startAssign2 + 1];
                $q3 = $assigns[$i][$startAssign2 + 2];
            }
        }
        # json-object for first half
        $target1 = array(
            "tLat" => $tlat,
            "tLong" => $tlong,
            "tName" => $zoneId,
            "tAcceptRange" => $tAcceptRange, #Zone radius 
            "tQuestions" => 3, # number of assignments per zone; fixed to 3 for now!
            "tQuestion" => array($q1,$q2,$q3); #array with questions 
        );
        return array($target1,$target2);
    }

}

if (isset($_POST['gameId'], $_POST['teamId'])) {
    $func = new targetFile();
    $func->makeTargets();
}
