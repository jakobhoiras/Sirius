<?php
require 'Mysql.php';


$screen = $_GET['screen'];
$group = $_GET['group'];
$mysql = new Mysql_spil();
$zones = $mysql -> get_zones();
$teams = $mysql -> get_teams();
$rutes = $mysql -> get_rutes();
$teams_state = $mysql -> get_teams_state($_SESSION['cg']);
$progress = $mysql -> get_game_progress($_SESSION['cg']);

$overset = $mysql->get_overview_settings();
$n_teams = sizeof($teams);
$teams_per_screen = $n_teams / ($overset[0][0]);
$teams_per_group = $n_teams / ($overset[0][0] * $overset[0][1]);
$start_team = $teams_per_screen*($screen-1) + $teams_per_group*($group-1);
$end_team = $teams_per_screen*$screen - $teams_per_group*($overset[0][1]-$group);
$active_zones = array($zones,array(),array());
for ($i=$start_team; $i<$end_team; $i++){
    $ruteID = $teams[$i][1];
    for ($j=0; $j<sizeof($rutes); $j++){
        if ($rutes[$j][0] == $ruteID){
            for ($k=0; $k<sizeof($teams_state); $k++){
                if($teams_state[$k][0] == $teams[$i][0]){
                    if($progress == 'start' or $progress == 'waiting first half' or $progress == 'first half'){
                        $zoneID = $rutes[$j][$teams_state[$k][3]];
                    }
                    else{
                        $zoneID = $rutes[$j][$teams_state[$k][4]];
                        
                    }
                    array_push($active_zones[1], $teams[$i][0]);
                    array_push($active_zones[2], $zoneID);
                }
            }
        }
    }
    
}

die(json_encode($active_zones));

?>
