<?php

require 'Mysql.php';

if (isset($_POST['gameId'], $_POST['teamId'])) {
    $gameId = $_POST['gameId'];
    $teamId = $_POST['teamId'];
    $Mysql = new Mysql_spil();
    $games = $Mysql->get_games();
    for ($i = 0; $i < sizeof($games); $i++) {
        if ($games[$i][0] == $gameId) {
            $gameName = $games[$i][1];
        }
    }
    $state = $Mysql->get_state_all($gameName, $teamId);
    $progress = $Mysql->get_game_progress($gameName);
    if ($state[0] == 'ready'){
        $answer = 'waiting';
    }
    else if ($state[0] == 'open'){
        if ($progress == 'waiting first half' or $progress == 'waiting second half'){
            $answer = 'waiting';
        }
        else if ($progress == 'first half' or $progress == 'second half'){
            if ($state[1] == 'OUT'){
                $answer = 'running'; 
            }
            else{
                $answer = 'waiting';
            }
        }
    }
    else if ($state[0] == 'stop'){
        $answer = 'ended';
    }
    else if ($state[0] == 'pause'){
        $answer = 'pause'; 
    }
    //$out = array(   "gameState" => $state[0],
    //               "teamState" => $state[1]);
    
    
    header('Content-Type: application/json');
    echo $answer;
  
}
