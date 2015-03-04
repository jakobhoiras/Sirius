<?php
require 'Mysql.php';

$_POST['gameId'] = 34;
$_POST['teamId'] = 1;

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

    $Mysql->change_team_state($gameName, $teamId);
    
}
