<?php 

require 'Mysql.php';
require_once 'get_distance.php';

function calculate_team_distance($teamID){
    $mysql = new Mysql_spil();
    // get team pos
    $coords = $mysql -> get_all_coords($_SESSION['cg'], $teamID);    
    // for pos i get Vincenty_distance(i)
    $dist = 0;
    for ($i=0; $i<sizeof($coords)-1; $i++){
        $lon1 = $coords[$i][1];
        $lat1 = $coords[$i][2];
        $lon2 = $coords[$i+1][1];
        $lat2 = $coords[$i+1][2];
        $dist += Vincenty_Distance($lat1, $lon1, $lat2, $lon2);
    }

    return $dist;
}
