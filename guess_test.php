<?php 

require 'Mysql.php';

$answer = "true";
$teamID = 1;
$assID = 3;
$gameName = 'Matkontest2';
$mysql = new Mysql_spil();
$mysql -> make_guess('Matkontest2',1,3);
if ($answer == "true"){
        $guesses = $mysql -> get_guesses($gameName, $teamID, $assID);
        if ($guesses == 1){
            $mysql -> update_team_score($gameName, $teamID, 3);
        }
        else if ($guesses == 2){
            $mysql -> update_team_score($gameName, $teamID, 2);
        }
        else if ($guesses == 3){
            $mysql -> update_team_score($gameName, $teamID, 1);
        }
        else if ($guesses > 3){
           $mysql -> update_team_score($gameName, $teamID, 0); 
        }
    }
