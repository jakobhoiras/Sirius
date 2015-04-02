<?php
require 'Mysql.php';

if (isset($_POST['gameId'], $_POST['teamId'], $_POST['questionId'], $_POST['answer'], $_POST['currRunTime'])) {
    $gameId = $_POST['gameId'];
    $teamID = $_POST['teamId'];
    $assID = $_POST['questionId'];
    $answer = $_POST['answer'];
    $timestamp = $_POST['currRunTime'];
    
    $mysql = new Mysql_spil();
    $games = $mysql->get_games();

    for ($i = 0; $i < sizeof($games); $i++) {
        if ($games[$i][0] == $gameId) {
            $gameName = $games[$i][1];
        }
    }


    $mysql -> make_guess($gameName, intval($teamID), $assID);

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
}
