<?php

require 'Mysql.php';

if (isset($_POST['gameId'], $_POST['teamId'])) {
    $gameId = $_POST['gameId'];
    $teamId = $_POST['teamId'];
    
    $Mysql = new Mysql_spil();
    $games = $Mysql->get_games();

    for ($i = 0; $i < sizeof($games); $i++) {
        if ($games[$i][0] === $gameId) {
            $gameName = $games[$i][1];
        }
    }
    $progress = $Mysql -> get_game_progress($gameName);
    $time = $Mysql -> get_time($gameName);
    if ( $progress == 'start' or $progress == 'waiting first half' or $progress == 'waiting second half' ){
        return 0;
    }
    else if ( $progress == 'first half'){
        if ( $time[0][5] == 0){
            return time() - $time[0][1];
        }
        else{
            $offset = $Mysql -> get_time_offset($gameName, 'first');
            return time() - $time[0][1] - $offset;
        }
    }
    else if ( $progress == 'second half'){
        if ( $time[0][6] == 0){
            return time() - $time[0][3];
        }
        else{
            $offset = $Mysql -> get_time_offset($gameName, 'second');
            return time() - $time[0][3] - $offset;
        }
    }
