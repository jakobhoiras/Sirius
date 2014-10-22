<?php

require_once 'constants.php';

class Mysql_spil {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }

    function save_map($mapID){
        $query = "INSERT INTO Maps.Maps(MapID) 
                  VALUES (?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $mapID);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function delete_rute($rute) {
        // deletes a row from zones table. 2nd and 3rd for-loop are for handling cases with rouding errors
        $query =  "DELETE FROM test_spil.Rutes WHERE zoneID1 = ? AND zoneID2 = ? AND zoneID3 = ? AND zoneID4 = ? AND zoneID5 = ?";           
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('iiiii', $rute[0], $rute[1], $rute[2], $rute[3], $rute[4]);
		    $stmt->execute();
            $stmt->close();
            return;
        }
    }
    
     function save_rute($rute) {
        if(sizeof($rute) == 1){
            $query = "INSERT INTO test_spil.Rutes(zoneID1) 
                      VALUES (?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('i', $rute[0]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 2){
            $query = "INSERT INTO test_spil.Rutes(zoneID1, zoneID2) 
                      VALUES (?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('ii', $rute[0],$rute[1]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 3){
            $query = "INSERT INTO test_spil.Rutes(zoneID1, zoneID2, zoneID3) 
                      VALUES (?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iii', $rute[0],$rute[1],$rute[2]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 4){
            $query = "INSERT INTO test_spil.Rutes(zoneID1, zoneID2, zoneID3, zoneID4) 
                      VALUES (?,?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiii', $rute[0],$rute[1],$rute[2],$rute[3]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        else if(sizeof($rute) == 5){
            $query = "INSERT INTO test_spil.Rutes(zoneID1, zoneID2, zoneID3, zoneID4, zoneID5) 
                      VALUES (?,?,?,?,?)";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('iiiii', $rute[0],$rute[1],$rute[2],$rute[3],$rute[4]);
			    $stmt->execute();
                $stmt->close();
            }
        }
        
    }

    function get_rutes(){
        $query = "SELECT * FROM test_spil.Rutes";
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
        $query = "SELECT * FROM test_spil.Zones";
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
        $query = "INSERT INTO test_spil.Zones (GPSx,GPSy,radius) 
                  VALUES (?,?,?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('sss', $GPSx, $GPSy, $radius);
			$stmt->execute();
            $stmt->close();
        }
    }

    function get_bases(){
        $query = "SELECT * FROM test_spil.Base";
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
        $query = "SELECT * FROM test_spil.Base";
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
        $query = "INSERT INTO test_spil.Base (GPSx,GPSy,radius) 
                  VALUES (?,?,?)";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('sss', $GPSx, $GPSy, $radius);
			$stmt->execute();
            $stmt->close();
        }
    }

    function update_zone_ID($zoneID, $assID) {
        $query = "UPDATE test_spil.Zones SET assignmentID =? 
                  WHERE zoneID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $assID, $zoneID);
			$stmt->execute();
            $stmt->close();
        }
    }

    function get_zone_link($zoneID){
        $query = "SELECT assignmentID FROM test_spil.Zones WHERE zoneID = ?";
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
            $query = "SELECT zoneID,GPSx,GPSy,radius FROM test_spil.Zones";
        }
        if ($action == 'delete_base'){  
            $query = "SELECT baseID,GPSx,GPSy,radius FROM test_spil.Base";
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
                        $query =  "DELETE FROM test_spil.Zones WHERE GPSx = ? AND GPSy = ?";
                    }
                    else if ($action == 'delete_base'){ 
                        $query =  "DELETE FROM test_spil.Base WHERE GPSx = ? AND GPSy = ?";
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
                        $query =  "DELETE FROM test_spil.Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){ 
                        $query =  "DELETE FROM test_spil.Base WHERE baseID = ?";
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
                        $query =  "DELETE FROM test_spil.Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){
                        $query =  "DELETE FROM test_spil.Base WHERE baseID = ?";
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
                        $query =  "DELETE FROM test_spil.Zones WHERE zoneID = ?";
                    }
                    else if ($action == 'delete_base'){
                        $query =  "DELETE FROM test_spil.Base WHERE baseID = ?";
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
    function display_games() {
        $query = "SELECT navn FROM Spil.spil";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($game_names = $result->fetch_all()){
                $stmt->close();
                return $game_names;
            }
        } 
    }
    
    function create_game($game_name, $company) {
        // Creates the new game database
        $query = "CREATE DATABASE " . (string)$game_name;        
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for assignments
        $query = "CREATE TABLE " . (string)$game_name . ".Assignments (
            assignmentID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(assignmentID), 
            type VARCHAR(20), 
            text_in VARCHAR(20), 
            text_out VARCHAR(20), 
            ref_to_pic_in VARCHAR(20), 
            ref_to_pic_out VARCHAR(20), 
            answer1 VARCHAR(20),
            answer2 VARCHAR(20),
            answer3 VARCHAR(20),
            answer4 VARCHAR(20),
            answer5 VARCHAR(20),
            correct_answer VARCHAR(20) )";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for zones
        $query = "CREATE TABLE " . (string)$game_name . ".Zones (
            zoneID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(zoneID), 
            GPSx FLOAT(20), 
            GPSy FLOAT(20), 
            radius INT(6), 
            assignmentID INT(5))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for rutes
        $query = "CREATE TABLE " . (string)$game_name . ".Rutes (
            ruteID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(ruteID), 
            zoneID1 INT(5), 
            zoneID2 INT(5), 
            zoneID3 INT(5),
            zoneID4 INT(5),
            zoneID5 INT(5), 
            length FLOAT(5))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for teams
        $query = "CREATE TABLE " . (string)$game_name . ".Teams (
            teamID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(teamID), 
            ruteID INT(5), 
            name1 VARCHAR(30), 
            name2 VARCHAR(30),
            name3 VARCHAR(30),
            name4 VARCHAR(30))";
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
        if(mkdir((string)$game_name)){}
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
