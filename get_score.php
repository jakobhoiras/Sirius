<?php

require_once 'Mysql.php';

$screen = $_GET['screen'];
$group = $_GET['group'];
$mysql = new Mysql_spil();
$progress = $mysql -> get_game_progress($_SESSION['cg']);
if ($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
    $half = 'first';
} else{
    $half = 'second';
}
$score = $mysql->get_points_all_teams("", $half);
$score_sorted = $mysql->get_points_all_teams("sort", $half);
$overset = $mysql->get_overview_settings();
$teams = $mysql->get_teams();
$n_teams = sizeof($teams);
$divs = $mysql -> get_divs();
$n_divs = $divs[0][0];
$div_names = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$teams_per_screen = $n_teams / ($overset[0][0]);
$teams_per_group = $n_teams / ($overset[0][0] * $overset[0][1]);
if($score != ""){
    $team_sorted = array();
    for ($i=0; $i<sizeof($score); $i++){
        $team = $score[$i][0];
        $div = $score[$i][1];
        $points1 = $score[$i][3];
        $points2 = $score[$i][5];
        $cp1 = $score[$i][2]*3;
        $asgnp1 = $points1 - $cp1;
        $cp2 = $score[$i][4]*3;
        $asgnp2 = $points2 - $cp2;
        $penalty = $mysql -> get_penalty($score,$i);
        $penalty1 = $penalty[0];
        $penalty2 = $penalty[1];
        $points1 -= $penalty1;
        $points2 -= $penalty2;
        $totp = $points1 + $points2;
        array_push($team_sorted, array($team, $div, $points1, $points2, $cp1, $asgnp1, $cp2, $asgnp2, 
                                       $penalty1, $penalty2, $totp)); 
    }              
    foreach ($team_sorted as $key => $row) {
        $points_sorted[$key]  = $row[10];
    }
    array_multisort($points_sorted, SORT_DESC, $team_sorted);
    //echo json_encode($team_sorted);
    if ($_GET['a'] == "teams"){
        $start_team = $teams_per_screen*($screen-1) + $teams_per_group*($group-1);
        $end_team = $teams_per_screen*$screen - $teams_per_group*($overset[0][1]-$group);
        $team_score = array();
        for ($i=$start_team; $i<$end_team; $i++){
            $team = $score[$i][0];
            $div = $score[$i][1];
            $points1 = $score[$i][3];
            $points2 = $score[$i][5];
            $cp1 = $score[$i][2]*3;
            $asgnp1 = $points1 - $cp1;
            $cp2 = $score[$i][4]*3;
            $asgnp2 = $points2 - $cp2;
            $penalty = $mysql -> get_penalty($score,$i);
            $penalty1 = $penalty[0];
            $penalty2 = $penalty[1];
            $points1 -= $penalty1;
            $points2 -= $penalty2;
            $totp = $points1 + $points2;
            array_push($team_score, array($team, $div, $points1, $points2, $cp1, $asgnp1, $cp2, $asgnp2, 
                                          $penalty1, $penalty2, $totp)); 
        }                
        foreach ($team_score as $key => $row) {
            $points[$key]  = $row[10];
        }
        array_multisort($points, SORT_DESC, $team_score);
        $ranks = array();
        for ($i=$start_team; $i<$end_team; $i++){
            $rank = get_rank($i,$score,$team_sorted);
            array_push($ranks,$rank);
        }
        sort($ranks);
        echo json_encode(array($team_score,$half,$ranks));

    } else{
        
        $div_score = array();
        for ($i=0; $i<$n_divs; $i++){
            array_push($div_score, array($div_names[$i],0,0,0,0,0,0,0,0,0));
            for ($j=0; $j<sizeof($team_sorted); $j++){
                if ($div_names[$i] == $team_sorted[$j][1]){
                    $div_score[$i][1] += $team_sorted[$j][2];
                    $div_score[$i][2] += $team_sorted[$j][3];
                    $div_score[$i][3] += $team_sorted[$j][4];
                    $div_score[$i][4] += $team_sorted[$j][6];
                    $div_score[$i][5] += $team_sorted[$j][10];
                    $div_score[$i][6] += $team_sorted[$j][8];
                    $div_score[$i][7] += $team_sorted[$j][9];
                    $div_score[$i][8] += $team_sorted[$j][5];
                    $div_score[$i][9] += $team_sorted[$j][7];
                }
            }
        }
        foreach ($div_score as $key => $row) {
            $points_div[$key]  = $row[5];
        }
        array_multisort($points_div, SORT_DESC, $div_score);
        echo json_encode(array($div_score,$half));
    }
}

function get_rank($i,$score,$score_sorted){
    for ($j=0; $j<sizeof($score_sorted); $j++){
        if ($score[$i][0] == $score_sorted[$j][0]){
            return $j+1;
        }
    }
}
