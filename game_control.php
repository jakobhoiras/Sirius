<?php

require 'Mysql.php';

//session_start();
class game_control{

    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }

    function open_game($progress){
		$mysql = new Mysql_spil();
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Game_state
					  	  SET GAME_" . $_SESSION['cg'] . ".Game_state.state = 'open'";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }

        $teams_state = $mysql -> get_teams_state($_SESSION['cg']);
		
        $count = 0;
        for ($i=0; $i<sizeof($teams_state); $i++){
            if ($teams_state[$i][1] == 'OUT'){
                $count += 1;
				if ($progress == 'first'){
                	$state = 'first half';
				} else {
					$state = 'second half';
				}  
            } else {
				if ($progress == 'first'){
                	$state = 'waiting first half';
				} else {
					$state = 'waiting second half';
				}
			}
			$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], $state);
        }
        if ($count == 0){
            if ($progress == 'first'){
                $mysql -> update_game_progress($_SESSION['cg'], 'waiting first half');
            }
            else if ($progress == 'second'){
                $mysql -> update_game_progress($_SESSION['cg'], 'waiting second half');
            }
            return 'waiting for someone to leave the zone!';
        }
        else{
            $start_time = time();
            if ($progress == 'first'){
                $mysql -> update_start_time_first_half($_SESSION['cg'], $start_time);
                $mysql -> update_game_progress($_SESSION['cg'], 'first half');
            }
            else if ($progress == 'second'){
                $mysql -> update_start_time_second_half($_SESSION['cg'], $start_time);
                $mysql -> update_game_progress($_SESSION['cg'], 'second half');
            }  
            return 'the game is running';
        }
    }

    function pause_game(){
        $half = $this->get_half();
        if ( $half == 'first'){
            $query = "SELECT n_pause FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        else if ( $half == 'second'){
            $query = "SELECT n_pause2 FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($n_pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }
        if ( $half == 'first'){
            $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Time
		   	          SET GAME_" . $_SESSION['cg'] . ".Time.n_pause = ?";
        }
        else if ( $half == 'second'){
            $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Time
		   	          SET GAME_" . $_SESSION['cg'] . ".Time.n_pause2 = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $n_pause = ($n_pause[0][0]+1);
            $stmt->bind_param('i', $n_pause);
			$stmt->execute();
            $stmt->close();
        }
        $start = time();
        if ( $half == 'first'){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Pause (start) 
                      VALUES (?)";
        }
        else if ( $half == 'second'){
            $query = "INSERT INTO GAME_" . $_SESSION['cg']. ".Pause2 (start) 
                      VALUES (?)";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $start);
			$stmt->execute();
            $stmt->close();
        }

		$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
		for ($i=0; $i<sizeof($teams_state); $i++){
			$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'pause');
		}

        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Game_state
					  	  SET GAME_" . $_SESSION['cg'] . ".Game_state.state = 'pause'";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
            return 1;
        }
        else{
            return 0;
        }
        
    }

    function restart_game(){
        $half = $this->get_half();
        if ( $half == 'first'){
            $query = "SELECT n_pause FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        else if ( $half == 'second'){
            $query = "SELECT n_pause2 FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($n_pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }
        $start = time();
        if ( $half == 'first'){
            $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Pause
		   	          SET GAME_" . $_SESSION['cg'] . ".Pause.end = ?
                      WHERE GAME_" . $_SESSION['cg'] . ".Pause.id = ?";
        }
        else if ( $half == 'second'){
            $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Pause2
		   	          SET GAME_" . $_SESSION['cg'] . ".Pause2.end = ?
                      WHERE GAME_" . $_SESSION['cg'] . ".Pause2.id = ?";
        }
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('ii', $start, $n_pause[0][0]);
			$stmt->execute();
            $stmt->close();
        }
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Game_state
					  	  SET GAME_" . $_SESSION['cg'] . ".Game_state.state = 'open'";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
        }
        if ( $half == 'first'){
            $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Pause";
        }
        else if ( $half == 'second'){
            $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Pause2";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }

		$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
		$progress = $mysql -> get_game_progress($_SESSION['cg']);
		for ($i=0; $i<sizeof($teams_state); $i++){
			if ($progress == 'waiting first half'){
				$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'waiting first half');
			}
			else if ($progress == 'first half'){
				if ($teams_state[$i][1] == 'OUT'){
					$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'first half');
				} else {
					if ($teams_state[$i][5] == 0) {
						$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'waiting first half');
					} else {
						$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'start second half');
					}
				}
			}
			if ($progress == 'waiting second half'){
				$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'waiting second half');
			}
			else if ($progress == 'second half'){
				if ($teams_state[$i][1] == 'OUT'){
					$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'second half');
				} else {
					if ($teams_state[$i][6] == 0) {
						$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'waiting second half');
					} else {
						$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'ended');
					}
				}
			}
		}

        $diff=0;
        for($i=0; $i<$n_pause[0][0]; $i++){
            $diff += $pause[$i][2] - $pause[$i][1];
        }
        return ($diff . ' ' . $half);
    }

    function stop_first_half(){
        $time = time();
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Time
		   	      SET GAME_" . $_SESSION['cg'] . ".Time.end1 = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $time);
			$stmt->execute();
            $stmt->close();
        }
        $mysql = new Mysql_spil();
		$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
		for ($i=0; $i<sizeof($teams_state); $i++){
			$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'start second half');
		}
        $mysql -> update_game_progress($_SESSION['cg'], 'start second half');
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Game_state
					  	  SET GAME_" . $_SESSION['cg'] . ".Game_state.state = 'ready'";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
            return 1;
        }
        else{
            return 0;
        }
    }

    function stop_second_half(){
        $time = time();
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Time
		   	      SET GAME_" . $_SESSION['cg'] . ".Time.end2 = ?";
        if ($stmt = $this->conn->prepare($query)){
            $stmt->bind_param('i', $time);
			$stmt->execute();
            $stmt->close();
        }
        $mysql = new Mysql_spil();
		$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
		for ($i=0; $i<sizeof($teams_state); $i++){
			$mysql -> update_team_state($_SESSION['cg'], $teams_state[$i][0], 'ended');
		}
        $mysql -> update_game_progress($_SESSION['cg'], 'ended');
        $query = "UPDATE GAME_" . $_SESSION['cg'] . ".Game_state
					  	  SET GAME_" . $_SESSION['cg'] . ".Game_state.state = 'stop'";
        if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
            $stmt->close();
            return 1;
        }
        else{
            return 0;
        }
    }
    
    function teams_state($half){
        $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Teams_state WHERE GAME_" . $_SESSION['cg'] . ".Teams_state.IO='OUT'";
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if (mysqli_num_rows($result) == 0){
                $stmt->close();
                return 0;
            }
            else{
                $stmt-> close();
                return 1;
            }
        }
    }

    function get_half(){
        $mysql = new Mysql_spil();
        $progress = $mysql -> get_game_progress($_SESSION['cg']);
        if ($progress == 'first half' or $progress == 'waiting first half'){
            return 'first';
        }
        else if ($progress == 'second half' or $progress == 'waiting second half'){
            return 'second';
        }
    }

    function get_sp(){
        $mysql = new Mysql_spil();
        $state = $mysql -> get_state();
        $progress = $mysql -> get_game_progress($_SESSION['cg']);
        return implode(" ", array($state, $progress));
    }

    function get_time_offset(){
        $half = $this->get_half();
        if ( $half == 'first'){
            $query = "SELECT n_pause FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        else if ( $half == 'second'){
            $query = "SELECT n_pause2 FROM GAME_" . $_SESSION['cg'] . ".Time";
        }
        if ($stmt = $this->conn->prepare($query)){
			$stmt->execute();
            $result = $stmt->get_result();
            if($n_pause = $result->fetch_all()){
                $stmt->close();
                
            }
        }
        if ( $half == 'first'){
            $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Pause";
        }
        else if ( $half == 'second'){
            $query = "SELECT * FROM GAME_" . $_SESSION['cg'] . ".Pause2";
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

}

$gc = new game_control();
if ($_GET['func'] == 'sp'){
    $res = $gc -> get_sp();
    echo $res;
} 
else if ($_GET['func'] == 'get_offset'){
    $res = $gc -> get_time_offset();
    echo $res;
} 
else if ($_GET['func'] == 'start_first_half'){
    $res = $gc -> open_game('first');
    echo $res;
}
else if ($_GET['func'] == 'start_second_half'){
    $res = $gc -> open_game('second');
    echo $res;
}
else if ($_GET['func'] == 'pause_game'){
    $res = $gc -> pause_game();
    echo $res;
}
else if ($_GET['func'] == 'stop_first_half'){
    $res = $gc -> stop_first_half();
    echo $res;
}
else if ($_GET['func'] == 'stop_second_half'){
    $res = $gc -> stop_second_half();
    echo $res;
}
else if ($_GET['func'] == 'teams_state1'){
    $res = $gc -> teams_state('first');
    echo $res;
}
else if ($_GET['func'] == 'teams_state2'){
    $res = $gc -> teams_state('second');
    echo $res;
}
else if ($_GET['func'] == 'restart_game'){
    $res = $gc -> restart_game();
    echo $res;
}
else if ($_GET['func'] == 'get_half'){
    $res = $gc -> get_half();
    echo $res;
} 
