<?php
require_once 'constants.php';
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
                $query = 'INSERT INTO Assignments.Assignments(Name) VALUES' . '(?)';
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

}


