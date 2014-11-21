<?php

require_once 'constants.php';

class Mysql_spil {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
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
}

