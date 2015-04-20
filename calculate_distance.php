<?php 

require_once 'get_distance.php';

function calculate_team_distance($teamID){
    $mysql = new Mysql_spil();
    $time = $mysql -> get_time($_SESSION['cg']);
    $start1 = $time[0][1]*1000;
    $start2 = $time[0][3]*1000;
    $state = $mysql -> get_team_state($_SESSION['cg'], $teamID);
    $end1 = $state[0][5]*1000;
    $end2 = $state[0][6]*1000;
	if ($end1 == 0){
		$end1 = 10000000000000;
	}
	if ($end2 == 0){
		$end2 = 10000000000000;
	}
    // get team pos
    $coords = $mysql -> get_all_coords($_SESSION['cg'], $teamID);  
    // for pos i get Vincenty_distance(i)
    $dist1 = 0;
    $dist2 = 0;
    for ($i=0; $i<sizeof($coords)-1; $i++){
        if (intval($coords[$i][3]) > $start1 and intval($coords[$i+1][3]) < $end1){
            $lon1 = $coords[$i][1];
            $lat1 = $coords[$i][2];
            $lon2 = $coords[$i+1][1];
            $lat2 = $coords[$i+1][2];
            $dist1 += Vincenty_Distance($lat1, $lon1, $lat2, $lon2);
        }
        else if (intval($coords[$i][3]) > $start2 and intval($coords[$i+1][3]) < $end2) {
            $lon1 = $coords[$i][1];
            $lat1 = $coords[$i][2];
            $lon2 = $coords[$i+1][1];
            $lat2 = $coords[$i+1][2];
            $dist2 += Vincenty_Distance($lat1, $lon1, $lat2, $lon2);
        }
    }
    $dist_tot = $dist1 + $dist2;
    return array($teamID, $dist1, $dist2, $dist_tot);
}

function get_distance_array(){
    $mysql = new Mysql_spil();
    $teams = $mysql -> get_teams();
    $dist_ar = array();
    $tot_dist = 0;
    for ($i=0; $i < sizeof($teams); $i++){
        $tot_dist += calculate_team_distance($teams[$i][0])[3];
    }
    $av_dist = $tot_dist/sizeof($teams);
    for ($i=0; $i < sizeof($teams); $i++){
        $team_dists = calculate_team_distance($teams[$i][0]);
        array_push($team_dists,$team_dists[3]-$av_dist);
        array_push($dist_ar, $team_dists);
    }
    return $dist_ar;
}
