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
    $state = $Mysql->get_team_state($gameName, $teamId);
    //$progress = $Mysql->get_game_progress($gameName);
    
    
    header('Content-Type: application/json');
    echo $state[0][2];
  
}
