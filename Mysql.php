<?php

require_once 'constants.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Mysql_spil {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }

    function get_zone($game_name, $zoneID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Zones
                  WHERE GAME_" . $game_name . ".Zones.zoneID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $zoneID);
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }

    function get_rute($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Rutes
                  WHERE GAME_" . $game_name . ".Rutes.teamID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }

    function get_all_coords($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Team_pos_" . $teamID;
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($coords = $result->fetch_all()) {
                $stmt->close();
                return $coords;
            }
        }
    }

    function get_coords($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Team_pos_" . $teamID . " ORDER BY count DESC LIMIT 1";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($coords = $result->fetch_all()) {
                $stmt->close();
                return $coords;
            }
        }
    }

    function get_penalty($score,$i){
        $progress = $this -> get_game_progress($_SESSION['cg']);
        $teams_state = $this -> get_teams_state($_SESSION['cg']);
        $time = $this -> get_time($_SESSION['cg']);
        for ($j=0; $j<sizeof($teams_state); $j++){
            if ($teams_state[$j][0] == $score[$i][0]){
                $end_time1 = $teams_state[$j][5];
                $end_time2 = $teams_state[$j][6];
                if ($end_time1 == 0){
                    $end_time1 = time();
                }
                if ($end_time2 == 0){
                    $end_time2 = time();
                }
            }
        } 
        $offset = $this -> get_time_offset($_SESSION['cg'], 'first');
        $time_diff = $end_time1 - $time[0][1] - $offset - $time[0][0]*60;
        if ( $time_diff <= 0 or $progress=='start' or $progress=='waiting first half'){
            $penalty1 = 0;
        } else{
            $penalty1 = pow(2,ceil($time_diff/60.));
        }
        $offset = $this -> get_time_offset($_SESSION['cg'], 'second');
        $time_diff = $end_time2 - $time[0][3] - $offset - $time[0][0]*60;
        if ($time_diff<=0 or $progress=='start' or $progress=='waiting first half' or $progress=='first half'
           or $progress == 'waiting second half'){
            $penalty2 = 0;
        }else{
            $penalty2 = pow(2,ceil($time_diff/60.));
        }
        return array($penalty1, $penalty2);
    }

    function team_found_zone($game_name, $teamID){
        $progress = $this -> get_game_progress($game_name);
        if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.points = points + 3 
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        } else {
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.points2 = points2 + 3 
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $stmt->close();
        }
        if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.check_points = check_points + 1 
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        } else{
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.check_points2 = check_points2 + 1 
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $stmt->close();
        }
        if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
            $query = "UPDATE GAME_" . $game_name . ".Teams_state
				      SET GAME_" . $game_name . ".Teams_state.current_cp = current_cp + 1 
                      WHERE GAME_" . $game_name . ".Team_state.teamID = ?";
        } else{
            $query = "UPDATE GAME_" . $game_name . ".Teams_state
				      SET GAME_" . $game_name . ".Teams_state.current_cp2 = current_cp2 + 1 
                      WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $stmt->close();
        }
    }
    
    function update_team_score($game_name, $teamID, $points){
        $progress = $this -> get_game_progress($game_name);
        if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.points = points + $points
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        } else{
            $query = "UPDATE GAME_" . $game_name . ".Score
				      SET GAME_" . $game_name . ".Score.points2 = points2 + $points
                      WHERE GAME_" . $game_name . ".Score.teamID = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function get_guesses($game_name, $teamID, $assID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Guesses 
                  WHERE teamID=? AND assID=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $teamID, $assID);
			$stmt->execute();
            $result = $stmt->get_result();
            if($guesses = $result->fetch_all()){
                $stmt->close();
                return $guesses[0][2];
            }
        } 
    }

    function make_guess($game_name, $teamID, $assID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Guesses 
                  WHERE teamID=? AND assID=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $teamID, $assID);
			$stmt->execute();
            $result = $stmt->get_result();
            if($guesses = $result->fetch_all()){
                $stmt->close();
            }
        } 
        if ($guesses==[]){
            $query = 'INSERT INTO GAME_' . $game_name . '.Guesses(teamID, assID) VALUES (?, ?)';
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param('ii', $teamID, $assID);
                $stmt->execute();
                $stmt->close();   
            }
        }
        else{
            $query = "UPDATE GAME_" . $game_name . ".Guesses
				  SET GAME_" . $game_name . ".Guesses.tries = tries + 1
                  WHERE GAME_" . $game_name . ".Guesses.teamID = ? AND GAME_" . $game_name . ".Guesses.assID = ?";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('ii', $teamID, $assID);
		        $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    function init_team_score($teamID, $div_name){
        $query = 'INSERT INTO GAME_' . $_SESSION['cg'] . '.Score(teamID, division) VALUES (?, ?)';
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('is', $teamID, $div_name);
            $stmt->execute();
            $stmt->close();   
        }
    }

    function set_team_div($teamID, $div_name){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Teams
				  SET GAME_" . $_SESSION['cg'] . ".Teams.division = ?
                  WHERE GAME_" . $_SESSION['cg'] . ".Teams.teamID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('si', $div_name, $teamID);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function get_time_offset($game_name, $half){
        if ( $half == 'first'){
            $query = "SELECT n_pause FROM GAME_" . $game_name . ".Time";
        }
        else if ( $half == 'second'){
            $query = "SELECT n_pause2 FROM GAME_" . $game_name . ".Time";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($n_pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }
        if ( $half == 'first'){
            $query = "SELECT * FROM GAME_" . $game_name . ".Pause";
        }
        else if ( $half == 'second'){
            $query = "SELECT * FROM GAME_" . $game_name . ".Pause2";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }
        $diff=0;
        for($i=0; $i<$n_pause[0][0]; $i++){
            $diff += $pause[$i][2] - $pause[$i][1];
        }
        return $diff;
    }

    function get_game_progress($game_name){
        $query = "SELECT * FROM GAME_" . $game_name . ".Game_progress";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->execute();
            $result = $stmt->get_result();
            if($progress = $result->fetch_all()){
                $stmt->close();
                return $progress[0][0];
            }
        }
    }
    
    function update_game_progress($game_name, $progress){
        $query = "UPDATE GAME_" . $game_name . ".Game_progress
			      SET GAME_" . $game_name . ".Game_progress.state = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('s', $progress);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function get_state(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Game_state";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->execute();
            $result = $stmt->get_result();
            if($game_state = $result->fetch_all()){
                $stmt->close();
                return $game_state[0][0];
            }
        }  
    }

    function get_game_state($game_name){
        $query = "SELECT * FROM GAME_" . $game_name . ".Game_state";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->execute();
            $result = $stmt->get_result();
            if($game_state = $result->fetch_all()){
                $stmt->close();
                return $game_state;
            }
        }  
    }

    function get_state_all($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $gameName . ".Game_state";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->execute();
            $result = $stmt->get_result();
            if($game_state = $result->fetch_all()){
                $stmt->close();
            }
        }  
        $query = "SELECT * FROM GAME_" . $game_name . ".Teams_state 
                  WHERE teamID=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
			$stmt->execute();
            $result = $stmt->get_result();
            if($team_state = $result->fetch_all()){
                $stmt->close();
            }
        } 
        return array($game_state[0][0],$team_state[0][1]);
    }
    
    function get_teams_state($game_name){
        $query = "SELECT * FROM GAME_" . $game_name . ".Teams_state";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $result = $stmt->get_result();
            if($team_state = $result->fetch_all()){
                $stmt->close();
                return $team_state;
            }
        } 
    }

    function get_team_state($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Teams_state
                  WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $result = $stmt->get_result();
            if($team_state = $result->fetch_all()){
                $stmt->close();
                return $team_state;
            }
        } 
    }

    function change_team_state($game_name, $teamID){
        $query = "SELECT * FROM GAME_" . $game_name . ".Teams_state 
                      WHERE teamID=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
		    $stmt->execute();
            $result = $stmt->get_result();
            if($team_state = $result->fetch_all()){
                $stmt->close();
            }
        } 

        $game_state = $this->get_game_state($game_name);

        if ($team_state[0][1] == 'IN'){
            $query = "UPDATE GAME_" . $game_name . ".Teams_state
					  	  SET GAME_" . $game_name . ".Teams_state.IO = 'OUT'
                          WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('i', $team_state[0][0]);
		        $stmt->execute();
                $stmt->close();
            }
            if ($game_state[0][0] == 'open'){
                $query = "UPDATE GAME_" . $game_name . ".Teams_state
					  	  SET GAME_" . $game_name . ".Teams_state.state = 'running'
                          WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
                if ($stmt = $this->conn->prepare($query)){
                    $stmt->bind_param('i', $team_state[0][0]);
		            $stmt->execute();
                    $stmt->close();
                }
            }
            $progress = $this -> get_game_progress($game_name);
            $start_time = time();
            if ($progress == 'waiting first half'){
                $this -> update_game_progress($game_name, 'first half');                
                $this -> update_start_time_first_half($game_name, $start_time);
            }
            else if ($progress == 'waiting second half'){
                $this -> update_game_progress($game_name, 'second half');                
                $this -> update_start_time_second_half($game_name, $start_time);  
            }
        }
        else if ($team_state[0][1] == 'OUT'){
            $query = "UPDATE GAME_" . $game_name . ".Teams_state
					  	  SET GAME_" . $game_name . ".Teams_state.IO = 'IN', GAME_" . $_SESSION['cg'] . ".Teams_state.state = 'waiting'
                        WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
            if ($stmt = $this->conn->prepare($query)){
                $stmt->bind_param('i', $team_state[0][0]);
		        $stmt->execute();
                $stmt->close();
            }
            if ($game_state[0][0] == 'open' or $game_state[0][0] == 'pause'){
                $progress = $this -> get_game_progress($game_name);
                if ($progress == 'first half'){
                    $query = "UPDATE GAME_" . $game_name . ".Teams_state
		    			  	  SET GAME_" . $game_name . ".Teams_state.end_half = ?
                              WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
                }
                else if ($progress == 'second half'){
                    $query = "UPDATE GAME_" . $game_name . ".Teams_state
		    			  	  SET GAME_" . $game_name . ".Teams_state.end_half2 = ?
                              WHERE GAME_" . $game_name . ".Teams_state.teamID = ?";
                }
                if ($progress == 'first half' or $progress == 'second half'){
                    if ($stmt = $this->conn->prepare($query)){
                        $time = time();
                        $stmt->bind_param('ii', $time, $team_state[0][0]);
		                $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }
    }

    function update_start_time_first_half($game_name, $time){
        $query = "UPDATE GAME_" . $game_name . ".Time 
					  	  SET GAME_" . $game_name . ".Time.start1 = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $time);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function update_start_time_second_half($game_name, $time){
        $query = "UPDATE GAME_" . $game_name . ".Time 
					  	  SET GAME_" . $game_name . ".Time.start2 = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $time);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function update_freq_setting($freq){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Overview_settings
					  	  SET GAME_" . $_SESSION['cg'] . ".Overview_settings.freq = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $freq);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function update_shifts_setting($shifts){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Overview_settings
					  	  SET GAME_" . $_SESSION['cg'] . ".Overview_settings.shifts = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $shifts);
		    $stmt->execute();
            $stmt->close();
        }
    }
    
    function update_screens_setting($screens){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Overview_settings
					  	  SET GAME_" . $_SESSION['cg'] . ".Overview_settings.screens = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $screens);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function get_overview_settings(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Overview_settings";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }

    function update_division_settings($n_divs){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Divisions 
					  	  SET GAME_" . $_SESSION['cg'] . ".Divisions.divs = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $n_divs);
		    $stmt->execute();
            $stmt->close();
        }
    }

    function get_divs(){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Divisions";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }
    

    function get_points_all_teams($sort, $half){
        if ($sort=='sort'){
            if ($half == 'first'){
                $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Score ORDER BY points DESC";
            } else{
                $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Score ORDER BY points2 DESC";
            }
        }else{
            $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Score";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
    }
    
    function update_half_time($time){
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Time 
					  	  SET GAME_" . $_SESSION['cg'] . ".Time.time = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $time);
		    $stmt->execute();
            $stmt->close();
        }
    }
    
    function get_time($game_name){
        $query = "SELECT * FROM GAME_" . $game_name . ".Time";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
                return $table;
            }
        } 
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
        $time = $this->get_time($_SESSION['cg']);
        $divs = $this->get_divs();
		return array($map, $zones, $rutes, $teams, $ass, $team_ass,$time,$divs);
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
        }
        $query =  "DROP TABLE GAME_" . $_SESSION['cg']. ".Team_pos_$teamID";           
        if ($stmt = $this->conn->prepare($query)){
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
        $query = "SELECT teamID FROM GAME_" . $_SESSION['cg']. ".Teams WHERE name1=? AND name2=? AND name3=? AND name4=?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ssss', $name1, $name2, $name3, $name4);
			$stmt->execute();
            $result = $stmt->get_result();
            if($table = $result->fetch_all()){
                $stmt->close();
            }
        }
        $teamID = $table[0][0];
        $query = "CREATE TABLE GAME_" . $_SESSION['cg'] . ".Team_pos_$teamID (
            count INT(7) NOT NULL AUTO_INCREMENT, PRIMARY KEY(count),
            lon FLOAT(15),
            lat FLOAT(15),
            time FLOAT(20))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Teams_state(teamID, IO, state) 
                  VALUES (?, 'IN', 'waiting')";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $teamID);
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

        // Creates table for Guesses
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Guesses (
            teamID INT(5) NOT NULL, PRIMARY KEY(Id),
            assID VARCHAR(250),
            tries INT(5) DEFAULT 1)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 

        // Creates table for Game state
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Game_state (
            state VARCHAR(30), PRIMARY KEY(state))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 

        // Creates table for Game progress
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Game_progress (
            state VARCHAR(30), PRIMARY KEY(state))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 

        // Inserts default state into Game state
        $query = "INSERT INTO GAME_". (string)$game_name . ".Game_state (state) 
                  VALUES ('ready')";
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
            name4 VARCHAR(30),
            division VARCHAR(5))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for teams state
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Teams_state (
            teamID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(teamID), 
            IO VARCHAR(5),
            state VARCHAR(10),
            current_cp INT(5) DEFAULT 1,
            current_cp2 INT(5) DEFAULT 1,
            end_half INT(20) DEFAULT 0,
            end_half2 INT(20) DEFAULT 0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Inserts default values into teams state
        #$query = "INSERT INTO GAME_". (string)$game_name . ".Teams_state (IO, state) 
        #          VALUES ('IN', 'waiting')";
        #if ($stmt = $this->conn->prepare($query)){
		#	$stmt->execute();
        #    $stmt->close();
        #}
        // Creates table for Map
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Map (
            mapID VARCHAR(30) NOT NULL, PRIMARY KEY(mapID))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        } 
        // Creates table for Score
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Score (
            teamID INT(5) NOT NULL, PRIMARY KEY(teamID),
            division VARCHAR(4),
            check_points INT(5) DEFAULT 0,
            points INT(5) DEFAULT 0,
            check_points2 INT(5) DEFAULT 0,
            points2 INT(5) DEFAULT 0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for Divisions
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Divisions (
            divs INT(5) NOT NULL, PRIMARY KEY(divs))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        //  s default time into Divisions table
        $query = "INSERT INTO GAME_". (string)$game_name . ".Divisions (divs) 
                  VALUES (0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for Time
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Time (
            time INT(4) NOT NULL, PRIMARY KEY(time),
            start1 INT(20),
            end1 INT(20),
            start2 INT(20),
            end2 INT(20),
            n_pause INT(20) DEFAULT 0,
            n_pause2 INT(20) DEFAULT 0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for Pause
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Pause (
            id INT(5) NOT NULL, PRIMARY KEY(id),
            start INT(20)
            end INT(20))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for Pause2
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Pause2 (
            id INT(5) NOT NULL, PRIMARY KEY(id),
            start INT(20)
            end INT(20))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Inserts default time into time table
        $query = "INSERT INTO GAME_". (string)$game_name . ".Time (time, start1, end1, start2, end2) 
                  VALUES (60, 0, 0, 0 ,0)";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Creates table for Overview_settings
        $query = "CREATE TABLE GAME_" . (string)$game_name . ".Overview_settings (
            screens INT(4) NOT NULL, PRIMARY KEY(screens),
            shifts INT(4),
            freq INT(5))";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $stmt->close();
        }
        // Inserts default time into Overview_settings table
        $query = "INSERT INTO GAME_". (string)$game_name . ".Overview_settings (screens, shifts, freq) 
                  VALUES (1, 1, 0)";
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
        if(mkdir('Games/' . (string)$game_name) and mkdir('Games/' . (string)$game_name . '/json')){}
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
	
		$query = "SELECT *
							FROM users
							WHERE username = ? AND password = ?
							LIMIT 1";
       
		if ($stmt = $this->conn->prepare($query)){
			$stmt->bind_param('ss', $un, $pwd);
			$stmt->execute();		
			$result = $stmt->get_result();
			if ($myrow = $result->fetch_assoc()){
				$stmt->close();
				return [true, $myrow];
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
    function create_user($un, $pwd, $perm, $game_name) {

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
                $query = 'INSERT INTO users (Username,Password,Permission,GameName) VALUES (?,?,?,?)';

                if ($stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param('ssss', $un, $pwd, $perm, $game_name);
                    $stmt->execute();
                    $stmt->close();
                    echo "brugeren er nu oprettet";
                }
            }
        }
    }
}
/*class Mysql_assignment {
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
}*/
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
