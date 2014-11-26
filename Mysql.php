<?php

require_once 'constants.php';
session_start();

class Mysql_spil {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }
	
	function check_if_game_exists($game_name){
		$query = "SELECT game_name FROM Games.Games WHERE game_name=?";
		if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $game_name);
		    $stmt->execute();
			$result = $stmt->get_result();
			if($result = $result->fetch_all()){
                $stmt->close();
			}
			if ( sizeof($result) != 0){
				return 'The game already exists. Pick another name';
			}
			else{
				return 'success';
			}
        }
	}
		

	function check_if_Teams_assignments_exists(){
		$query =  "SELECT 1 from GAME_" . $_SESSION['cg']. ".Teams_assignments";           
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
			return true;
        }
		else{
			return false;
		}
	}

	function drop_teams_assignments_table(){
		$query =  "DROP TABLE GAME_" . $_SESSION['cg']. ".Teams_assignments";           
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }
	}

	function get_assignments(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Assignments";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 

	function save_assignment($name) {
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Assignments (name) 
                  VALUES (?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $name);
		    $stmt->execute();
            $stmt->close();
        }
    }   

	function save_teams_assignments($teamID, $ass){

		$query = "INSERT INTO GAME_" . $_SESSION['cg'] . ".Teams_assignments	(teamID";
		for ($i=0; $i<sizeof($ass); $i++){
			$query .= ", opgaveID" . (string)($i + 1);
		}
		$query .= ")";
		$query .= " VALUES (" . $teamID;
		for ($i=0; $i<sizeof($ass); $i++){
			$query .= ", " . "'" . $ass[$i][0] . "'";
		}
		$query .= ");";
		echo $query;
		if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }
		else{ echo "Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error; }
	}

	function create_teams_assignments_table($rute_length){
		$query = "CREATE TABLE GAME_" . $_SESSION['cg'] . ".Teams_assignments(
            teamID INT";
		for ($i=0; $i<2*3*$rute_length; $i++){
			$query .= ",opgaveID" . (string)($i + 1) . "  VARCHAR(250)";
		}
		$query .= ")";
		echo $query;
		if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }
	}
	
	function link_teams_and_rutes(){
		$query = "SELECT ruteID FROM GAME_" . $_SESSION['cg'] . ".Rutes";
		if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
			$result = $stmt->get_result();
            if($rutes = $result->fetch_all()){
                $stmt->close();
            }
        }

		$query = "SELECT teamID FROM GAME_" . $_SESSION['cg'] . ".Teams";
		if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
			$result = $stmt->get_result();
            if($teams = $result->fetch_all()){
                $stmt->close();
            }
        }

		for ($i=0; $i<sizeof($rutes); $i++){
			if ($i<sizeof($rutes)/2){
				$query = "UPDATE GAME_" . $_SESSION['cg'] . ".Teams 
					  	  SET GAME_" . $_SESSION['cg'] . ".Teams.ruteID1 = ? 
					  	  WHERE GAME_" . $_SESSION['cg'] . ".Teams.teamID = ?";
				if ($stmt = $this->conn->prepare($query)){
					$stmt->bind_param('ii', $rutes[$i][0], $teams[$i][0]);
		    		$stmt->execute();
					$stmt->close(); 
				}
			}
			else{
				$query = "UPDATE GAME_" . $_SESSION['cg'] . ".Teams 
					  	  SET GAME_" . $_SESSION['cg'] . ".Teams.ruteID2 = ? 
					  	  WHERE GAME_" . $_SESSION['cg'] . ".Teams.teamID = ?";
				if ($stmt = $this->conn->prepare($query)){
					$stmt->bind_param('ii', $rutes[$i][0], $teams[$i-sizeof($rutes)/2][0]);
		    		$stmt->execute();
					$stmt->close(); 
				}
			}
		}
	}

	function get_status(){
		$map = $this->get_map();
		$zones = $this->get_zones();
		$rutes = $this->get_rutes();
		$teams = $this->get_teams();
		$ass = $this->get_assignments();
		$team_ass = $this->check_if_Teams_assignments_exists();
		return array($map, $zones, $rutes, $teams, $ass, $team_ass);
	}

    function save_map_link($map_name) {
        $query =  "TRUNCATE TABLE GAME_" . $_SESSION['cg']. ".Map";           
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Map(mapID) 
                  VALUES (?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $map_name);
		    $stmt->execute();
            $stmt->close();
        }
    }  

    function get_map(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Map";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 

    function get_games(){
        $query = "SELECT * FROM Games.Games";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 

    function edit_team($name1, $name2, $name3, $name4, $old_name1, $old_name2, $old_name3, $old_name4) {
        $query =  "UPDATE GAME_" . $_SESSION['cg']. ".Teams SET name1=?, name2=?, name3=?, name4=?
                   WHERE name1=? AND name2=? AND name3=? AND name4=?";           
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ssssssss', $name1, $name2, $name3, $name4,$old_name1,$old_name2,$old_name3,$old_name4);
		    $stmt->execute();
            $stmt->close();
            return;
        }
    }

    function delete_team($teamID) {
        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Teams WHERE teamID = ?";           
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $stmt->close();
            return;
        }
    }

    function get_teams(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg']. ".Teams";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 

    function save_team($name1, $name2, $name3, $name4) {
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Teams(name1, name2, name3, name4) 
                  VALUES (?, ?, ?, ?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ssss', $name1, $name2, $name3, $name4);
		    $stmt->execute();
            $stmt->close();
        }
    }   

    function get_maps(){
        $query = "SELECT * FROM Maps.Maps";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 

    function save_map($mapID, $GPSx, $GPSy){
        $query = "INSERT INTO Maps.Maps(MapID, GPSx, GPSy) 
                  VALUES (?, ?, ?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('sss', $mapID, $GPSx, $GPSy);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function delete_rute($rute) {
        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Rutes WHERE zoneID1 = ? AND zoneID2 = ? AND zoneID3 = ? AND zoneID4 = ? AND zoneID5 = ?";           
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('iiiii', $rute[0], $rute[1], $rute[2], $rute[3], $rute[4]);
		    $stmt->execute();
            $stmt->close();
            return;
        }
    }

    function edit_rute($rute, $old_rute) {
        if(sizeof($old_rute) == 1){
            $query = "UPDATE GAME_" . $_SESSION['cg']. ".Rutes SET zoneID1=?
                   WHERE zoneID1=?;";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('ii', $rute[0],$old_rute[0]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($old_rute) == 2){
            $query = "UPDATE GAME_" . $_SESSION['cg']. ".Rutes SET zoneID1=?, zoneID2=?
                   WHERE zoneID1=? AND zoneID2=?;";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiii', $rute[0],$rute[1], $old_rute[0],$old_rute[1]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($old_rute) == 3){
            $query = "UPDATE GAME_" . $_SESSION['cg']. ".Rutes SET zoneID1=?, zoneID2=?, zoneID3=?
                   WHERE zoneID1=? AND zoneID2=? AND zoneID3=?;";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiiiii', $rute[0],$rute[1],$rute[2], $old_rute[0],$old_rute[1],$old_rute[2]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($old_rute) == 4){
            $query = "UPDATE GAME_" . $_SESSION['cg']. ".Rutes SET zoneID1=?, zoneID2=?, zoneID3=?, zoneID4=?
                   WHERE zoneID1=? AND zoneID2=? AND zoneID3=? AND zoneID4=?;";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiiiiiii', $rute[0],$rute[1],$rute[2],$rute[3],$old_rute[0],$old_rute[1],$old_rute[2],$old_rute[3]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($old_rute) == 5){
            $query = "UPDATE GAME_" . $_SESSION['cg']. ".Rutes SET zoneID1=?, zoneID2=?, zoneID3=?, zoneID4=?, zoneID5=?
                   WHERE zoneID1=? AND zoneID2=? AND zoneID3=? AND zoneID4=? AND zoneID5=?;";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiiiiiiiii', $rute[0],$rute[1],$rute[2],$rute[3],$rute[4], $old_rute[0],$old_rute[1],$old_rute[2],$old_rute[3],$old_rute[4]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        
    }
    
     function save_rute($rute) {
        if(sizeof($rute) == 1){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Rutes(zoneID1) 
                      VALUES (?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('i', $rute[0]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 2){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Rutes(zoneID1, zoneID2) 
                      VALUES (?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('ii', $rute[0],$rute[1]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 3){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Rutes(zoneID1, zoneID2, zoneID3) 
                      VALUES (?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iii', $rute[0],$rute[1],$rute[2]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 4){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Rutes(zoneID1, zoneID2, zoneID3, zoneID4) 
                      VALUES (?,?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiii', $rute[0],$rute[1],$rute[2],$rute[3]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 5){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Rutes(zoneID1, zoneID2, zoneID3, zoneID4, zoneID5) 
                      VALUES (?,?,?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiiii', $rute[0],$rute[1],$rute[2],$rute[3],$rute[4]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        
    }

    function get_rutes(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg']. ".Rutes";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    } 
    
    // FUNCTIONS FOR DRAW ZONES PAGE
    function get_zones(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg']. ".Zones";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }   

    function save_zones($GPSx, $GPSy, $radius) {
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Zones (GPSx,GPSy,radius) 
                  VALUES (?,?,?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('sss', $GPSx, $GPSy, $radius);
			$stmt->execute();
            $stmt->close();
        }
    }

    function get_bases(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg']. ".Base";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }  

    function get_number_of_bases(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg']. ".Base";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return sizeof($table);
            }
        } 
    }   

    function save_base($GPSx, $GPSy, $radius) {
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Base (GPSx,GPSy,radius) 
                  VALUES (?,?,?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('sss', $GPSx, $GPSy, $radius);
			$stmt->execute();
            $stmt->close();
        }
    }

    function update_zone_ID($zoneID, $assID) {
        $query = "UPDATE GAME_" . $_SESSION['cg']. ".Zones SET assignmentID =? 
                  WHERE zoneID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $assID, $zoneID);
			$stmt->execute();
            $stmt->close();
        }
    }

    function get_zone_link($zoneID){
        $query = "SELECT assignmentID FROM GAME_" . $_SESSION['cg']. ".Zones WHERE zoneID = ?";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($assID = $result->fetch_all()){
                $stmt->close();
                echo $assID;
            }
        } 
    }  

    function delete_zones($GPSx, $GPSy, $action) {
        // deletes a row from zones table. 2nd and 3rd for-loop are for handling cases with rouding errors
        if ($action == 'delete_zone'){  
            $query = "SELECT zoneID,GPSx,GPSy,radius FROM GAME_" . $_SESSION['cg']. ".Zones";
        }
        if ($action == 'delete_base'){  
            $query = "SELECT baseID,GPSx,GPSy,radius FROM GAME_" . $_SESSION['cg']. ".Base";
        } 
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
            }
        }
            $zones_num = sizeof($table);
            for ($i=0; $i<$zones_num; $i++){
                if($table[$i][1]==$GPSx and $table[$i][2]==$GPSy){
                    if ($action == 'delete_zone'){ 
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Zones WHERE GPSx = ? AND GPSy = ?";
                    }
                    else if ($action == 'delete_base'){ 
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Base WHERE GPSx = ? AND GPSy = ?";
                    }
                    if ($stmt = $this->conn->prepare($query)){
                        $stmt->bind_param('ss', $GPSx, $GPSy);
			            $stmt->execute();
                        $stmt->close();
                        return;
                    }
                }
            }
            for ($i=0; $i<$zones_num; $i++){
                if ($table[$i][1]==$GPSx and $table[$i][2]!=$GPSy){
                    $dist_list = array();
                    for ($j=0; $j<$zones_num; $j++){
                        array_push($dist_list, abs($GPSy - $table[$j][2]));
                    }
                    $index = array_keys($dist_list,min($dist_list));
                    if ($action == 'delete_zone'){ 
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){ 
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Base WHERE baseID = ?";
                    } 
                    if ($stmt = $this->conn->prepare($query)){
                        $stmt->bind_param('i', $table[$index[0]][0]);
			            $stmt->execute();
                        $stmt->close();
                        return;
                    }
                }            
            }

            for ($i=0; $i<$zones_num; $i++){
                if ($table[$i][1]!=$GPSx and $table[$i][2]==$GPSy){
                    $dist_list = array();
                    for ($j=0; $j<$zones_num; $j++){
                        array_push($dist_list, abs($GPSx - $table[$j][1]));
                    }
                    $index = array_keys($dist_list,min($dist_list));
                    if ($action == 'delete_zone'){
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Base WHERE baseID = ?";
                    }
                    if ($stmt = $this->conn->prepare($query)){
                        $stmt->bind_param('i', $table[$index[0]][0]);
			            $stmt->execute();
                        $stmt->close();
                        return;
                    }
                }            
            }

            for ($i=0; $i<$zones_num; $i++){
                if ($table[$i][1]!=$GPSx and $table[$i][2]!=$GPSy){
                    $dist_list = array();
                    for ($j=0; $j<$zones_num; $j++){
                        array_push($dist_list, abs($GPSx - $table[$j][1]) + abs($GPSy - $table[$j][2]));
                    }
                    $index = array_keys($dist_list,min($dist_list));
                    if ($action == 'delete_zone'){
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){
                        $query =  "DELETE FROM GAME_" . $_SESSION['cg']. ".Base WHERE baseID = ?";
                    }
                    if ($stmt = $this->conn->prepare($query)){
                        $stmt->bind_param('i', $table[$index[0]][0]);
			            $stmt->execute();
                        $stmt->close();
                        return;
                    }
                }            
            }
    }
    // OTHER FUNCTIONS
    /*function display_games() {
        $query = "SELECT navn FROM Spil.spil";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($game_names = $result->fetch_all()){
                $stmt->close();
                return $game_names;
            }
        } 
    }*/
    
    function create_game($game_name, $company) {
        // Creates the new game database
        $query = "CREATE DATABASE GAME_" . (string)$game_name;        
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for assignments
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Assignments (
            name VARCHAR(250))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for base
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Base (
            baseID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(baseID), 
            GPSx FLOAT(20), 
            GPSy FLOAT(20), 
            radius INT(6))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for zones
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Zones (
            zoneID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(zoneID), 
            GPSx FLOAT(20), 
            GPSy FLOAT(20), 
            radius INT(6))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for rutes
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Rutes (
            ruteID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(ruteID), 
            zoneID1 INT(5) DEFAULT 0, 
            zoneID2 INT(5) DEFAULT 0, 
            zoneID3 INT(5) DEFAULT 0,
            zoneID4 INT(5) DEFAULT 0,
            zoneID5 INT(5) DEFAULT 0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for teams
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Teams (
            teamID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(teamID), 
            ruteID1 INT(5),
			ruteID2 INT(5), 
            name1 VARCHAR(30), 
            name2 VARCHAR(30),
            name3 VARCHAR(30),
            name4 VARCHAR(30))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for Map
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Map (
            mapID VARCHAR(30) NOT NULL, PRIMARY KEY(mapID))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Inserts entry into games database
        $query = "INSERT INTO Games.Games (gameID, game_name, company) 
                  VALUES (?,?,?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('iss', $gameID, $game_name, $company);
			$stmt->execute();
            $stmt->close();
        }
        if(mkdir((string)$game_name) and mkdir((string)$game_name . '/json')){}
        else{die('error');}
    }
    
    function opret_opgave($type, $database_navn) {
        $query = "INSERT INTO " . (string)$database_navn . ".opgaver (type) 
                  VALUES (?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $type);
			$stmt->execute();
            $stmt->close();
        }
    }
}

class Mysql_login {
	
	private $conn;
	
	function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
									die('there was a problem connecting to the database.');
	}

	function verify_Username_and_Pass($un, $pwd) {
	
		$query = "SELECT permission
							FROM users
							WHERE username = ? AND password = ?
							LIMIT 1";
       
		if ($stmt = $this->conn->prepare($query)){
			$stmt->bind_param('ss', $un, $pwd);
			$stmt->execute();
			
			$result = $stmt->get_result();

			if ($myrow = $result->fetch_assoc()){
				$stmt->close();
				return [true, $myrow['permission']];
    	    }
		}
	}
}

class Mysql_create_user {
     private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or
                die('there was a problem connecting to the database.');
    }

    //$perm = admin || user
    function create_user($un, $pwd, $perm) {

        $query = "SELECT * FROM users";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->execute();
            $result = $stmt->get_result();
			if($result = $result->fetch_all()){
            	$stmt->close();
            }
			$already_exists = false;
			for ($i=0; $i<sizeof($result); $i++){
            	if ($result[$i][0] == $un) {
                	echo "Bruger findes allerede";
					$already_exists = true;
            	} 
			}
			if ($already_exists == false) {
                $query = 'INSERT INTO users (Username,Password,Permission) VALUES (?,?,?)';

                if ($stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param('sss', $un, $pwd, $perm);
                    $stmt->execute();
                    $stmt->close();
                    echo "brugeren er nu oprettet";
                }
            }
        }
    }
}
class Mysql_assignment {
     private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }
    
    function create_assignment ($typeIn,$typeOut,$multiNr,$name) {
        $query = "SELECT name FROM Assignments.Assignments WHERE name = ?";
        
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $un);
            $stmt->execute();
            $result = strtolower($stmt->get_result());
            if($result===strtolower($name)) {
                return 1;
            } else {
                $query = 'INSERT INTO Assignments.Assignments (name)';
                return 0;
            }
        }           
    }
}
#function insert_Kunde($navn1, $navn2, $navn3){
#
#		$query = "INSERT
#							INTO kunder (navn1, navn2, navn3)
#							VALUES (?,?,?)";
#
#		if ($stmt = $this->conn->prepare($query)){
#			$stmt->bind_param('sss',$navn1, $navn2, $navn3);
#			$stmt->execute();
#			$stmt->close();
#			return true;
#		}
#	}
#
#	function get_Kunde($navn) {
#		$words = explode(" ", $navn);
#
#		$query = "SELECT *
#							FROM kunder
#							WHERE navn1 = ? OR navn2 = ? OR navn3 = ?";
#		
#		if ($stmt = $this->conn->prepare($query)){
#			$found = array(10000);
#			for ($i=0; $i<count($words); $i++){
#				$navn = $words[$i];
#				$stmt->bind_param('sss', $navn, $navn, $navn);
#		
#				$stmt->execute();
#			
#				$result = $stmt->get_result();
#				while ($myrow = $result->fetch_assoc()){
#					$key = array_search($myrow['id'], $found);
#					if($found[$key] != $myrow['id']){
#						$res[] = array ( true, $myrow['navn1'], $myrow['navn2'], $myrow['navn3']) ;
#					}
#					$found[] = $myrow['id'];
#				}
#			}
#			$stmt->close();
#			if(isset($res)){
#				return $res;
#			} 
#			else {
#				return false;
#			}
#		}
#	}
#}
