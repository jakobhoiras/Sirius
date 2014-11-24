<?php

require_once 'constants.php';
session_start();

class mysql_json {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }

	function get_teamID(){
		$query = "SELECT teamID FROM GAME_" . $_SESSION['cg'] . ".Teams";
		if ($stmt = $this->conn->prepare($query)){
		    $stmt->execute();
			$result = $stmt->get_result();
			if($result = $result->fetch_all()){
				return $result;
                $stmt->close();
			}
        }
	}

}
