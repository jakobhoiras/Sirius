<?php
require_once 'constants.php';
session_start();
class Mysql_assignment {

    private $conn;

    function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or
                die('there was a problem connecting to the database.');
    }

    function create_assignment($name) {
        $query = "SELECT name FROM Assignments.Assignments WHERE name = ?";

        $formIn = $_POST['formIn'];
        $formOut = $_POST['formIn'];

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $un);
            $stmt->execute();
            $result = $stmt->get_result();
            $result2 = $result->fetch_array(MYSQLI_NUM);
            echo strtolower($result2[0]);

            if (strtolower($result2[0]) === strtolower($name)) {
                return 1;
            } else {
                $query = 'INSERT INTO Assignments.Assignments(name) VALUES' . '(?)';
                $name2 = strtolower($name);
                if ($stmt = $this->conn->prepare($query)) {
                    
                    $stmt->bind_param('s', $name2);
                    $stmt->execute();
                    $stmt->close();
                    return 0;
                }
            }
        }
    }
    
    function delete_assignment($name) {
        $query = "Delete FROM Assignments.Assignments WHERE name = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $name);
            $stmt->execute();
        }
    }

	function get_assignments(){
		$query = "SELECT name FROM Assignments.Assignments";
		if ($stmt = $this->conn->prepare($query)) {
        	$stmt->execute();
            $result = $stmt->get_result();
            if($assignments = $result->fetch_all()){
                $stmt->close();
				return $assignments;
            }
        }
	}

	function get_imported_assignments(){
		$query = "SELECT name FROM GAME_" . $_SESSION['cg'] . ".Assignments";
		if ($stmt = $this->conn->prepare($query)) {
        	$stmt->execute();
            $result = $stmt->get_result();
            if($assignments = $result->fetch_all()){
                $stmt->close();
				return $assignments;
            }
        }
	}
}


