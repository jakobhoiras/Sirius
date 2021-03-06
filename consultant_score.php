<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();

$mysql = new Mysql_spil();
$score = $mysql->get_points_all_teams("", "first");
$divs = ($mysql -> get_divs());
$n_divs = $divs[0][0];
$div_names = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

require 'calculate_distance.php';

?>

<script>

function change_page(page_name) {
        window.location.href = (page_name + ".php");
}

</script>

<html>
<head>
    <title>consultant score</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
    <!-- bring in the OpenLayers javascript library
         (here we bring it from the remote site, but you could
         easily serve up this javascript yourself) -->
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body>
    <div style="width:100%; padding-bottom:5px">
        <button id="back" type="button" onclick=change_page('consultant_panel')>Consultant menu</button>
        <form method="post" style="display:inline">
            <input type="submit" value="Log out" style="float:right" name="logout" /><br>
        </form>
    </div>
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:1000px; height:1200px; margin-left:auto; margin-right:auto;">
        <div style="width:90%; height:30%; float:left">
            <div style="width:100%; height:100%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="score" style="margin-left:auto; margin-right:auto;">
                    <caption> Teams score</caption>
                    <tr>
                        <th>rank</th>
                        <th>team</th>
                        <th>div</th>
                        <th>1. cp</th>
                        <th>1. asgn. p</th>
                        <th>1. penalty</th>
                        <th>1. p</th>
                        <th>2. cp</th>
                        <th>2. asgn. p</th>
                        <th>2. penalty</th>
                        <th>2. p</th>
                        <th>total points</th>
                    </tr>
                    <?php 
                        $team_score = array();
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
                            array_push($team_score, array($team, $div, $points1, $points2, $cp1, $asgnp1, $cp2, $asgnp2, 
                                       $penalty1, $penalty2, $totp)); 
                        }
                        foreach ($team_score as $key => $row) {
                            $points[$key]  = $row[10];
                        }
                        array_multisort($points, SORT_DESC, $team_score);
                        for ($i=0; $i<sizeof($team_score); $i++){
                            $rank = $i + 1;
                            $team = $team_score[$i][0];
                            $div = $team_score[$i][1];
                            $points1 = $team_score[$i][2];
                            $points2 = $team_score[$i][3];
                            $cp1 = $team_score[$i][4];
                            $asgnp1 = $team_score[$i][5];
                            $cp2 = $team_score[$i][6];
                            $asgnp2 = $team_score[$i][7];
                            $penalty1 = $team_score[$i][8];
                            $penalty2 = $team_score[$i][9];
                            $totp = $team_score[$i][10];
                            echo "<tr>
                                    <td>$rank</td>
                                    <td>$team</td>
                                    <td>$div</td>
                                    <td>$cp1</td>
                                    <td>$asgnp1</td>
                                    <td>-$penalty1</td>
                                    <td>$points1</td>
                                    <td>$cp2</td>
                                    <td>$asgnp2</td>
                                    <td>-$penalty2</td>
                                    <td>$points2</td>
                                    <td>$totp</td>
                                  </tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
        <div style="width:90%; height:30%; float:left">
            <div style="width:100%; height:100%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="score" style="margin-left:auto; margin-right:auto;">
                    <caption>Division score</caption>
                    <tr>
                        <th>rank</th>
                        <th>div</th>
                        <th>1. cp</th>
                        <th>1. asgn. p</th>
                        <th>1. penalty</th>
                        <th>1. p</th>
                        <th>2. cp</th>
                        <th>2. asgn. p</th>
                        <th>2. penalty</th>
                        <th>2. p</th>
                        <th>total points</th>
                    </tr>
                    <?php  
                        $div_score = array();
                        for ($i=0; $i<$n_divs; $i++){
                            array_push($div_score, array($div_names[$i],0,0,0,0,0,0,0,0,0));
                            for ($j=0; $j<sizeof($team_score); $j++){
                                if ($div_names[$i] == $team_score[$j][1]){
                                   $div_score[$i][1] += $team_score[$j][2];
                                   $div_score[$i][2] += $team_score[$j][3];
                                   $div_score[$i][3] += $team_score[$j][4];
                                   $div_score[$i][4] += $team_score[$j][6];
                                   $div_score[$i][5] += $team_score[$j][10];
                                   $div_score[$i][6] += $team_score[$j][8];
                                   $div_score[$i][7] += $team_score[$j][9];
                                   $div_score[$i][8] += $team_score[$j][5];
                                   $div_score[$i][9] += $team_score[$j][7];
        
                                }
                            }
                        }
                        
                        foreach ($div_score as $key => $row) {
                            $points_div[$key]  = $row[5];
                        }
                        array_multisort($points_div, SORT_DESC, $div_score);
                        for ($i=0; $i<sizeof($div_score); $i++){
                            $rank = $i + 1;
                            $div = $div_score[$i][0];
                            $points1 = $div_score[$i][1];
                            $points2 = $div_score[$i][2];
                            $cp1 = $div_score[$i][3];
                            $asgnp1 = $div_score[$i][8];
                            $cp2 = $div_score[$i][4];
                            $asgnp2 = $div_score[$i][9];
                            $totp = $div_score[$i][5];
                            $penalty1 = $div_score[$i][6];
                            $penalty2 = $div_score[$i][7];
                            echo "<tr>
                                    <td>$rank</td>
                                    <td>$div</td>
                                    <td>$cp1</td>
                                    <td>$asgnp1</td>
                                    <td>-$penalty1</td>
                                    <td>$points1</td>
                                    <td>$cp2</td>
                                    <td>$asgnp2</td>
                                    <td>-$penalty2</td>
                                    <td>$points2</td>
                                    <td>$totp</td>
                                  </tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
        <div style="width:90%; height:30%; float:left">
            <div style="width:100%; height:100%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="score" style="margin-left:auto; margin-right:auto;">
                    <caption> Teams improvement</caption>
                    <tr>
                        <th>rank</th>
                        <th>team</th>
                        <th>div</th>
                        <th>1. p</th>
                        <th>2. p</th>
                        <th>% diff</th>
                        <th>total points</th>
                    </tr>
                    <?php 
                        $team_score = array();
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
                            array_push($team_score, array($team, $div, $points1, $points2, $cp1, $asgnp1, $cp2, $asgnp2, 
                                       $penalty1, $penalty2, $totp)); 
                        }
                        foreach ($team_score as $key => $row) {
                            $points[$key]  = $row[10];
                        }
                        array_multisort($points, SORT_DESC, $team_score);
                        for ($i=0; $i<sizeof($team_score); $i++){
                            $rank = $i + 1;
                            $team = $team_score[$i][0];
                            $div = $team_score[$i][1];
                            $points1 = $team_score[$i][2];
                            $points2 = $team_score[$i][3];
                            if ($points1 !=0){
                                $diff = (($points2 / $points1)-1)*100;
                            } else{
                                $diff = '-';
                            }
                            $totp = $team_score[$i][10];
                            echo "<tr>
                                    <td>$rank</td>
                                    <td>$team</td>
                                    <td>$div</td>
                                    <td>$points1</td>
                                    <td>$points2</td>
                                    <td>$diff</td>
                                    <td>$totp</td>
                                  </tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
        <div style="width:90%; height:30%; float:left">
            <div style="width:100%; height:100%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="score" style="margin-left:auto; margin-right:auto;">
                    <caption> Team distance </caption>
                    <tr>
                        <th>team</th>
                        <th>1. half</th>
                        <th>2. half</th>
                        <th>total</th>
                        <th>+/- average</th>
                    </tr>
                    <?php 
                        $res = get_distance_array();
                        for ($i=0; $i<sizeof($res); $i++){
                            $team = $res[$i][0];
                            //$div = $score[$i][1];
                            $dist1 = round($res[$i][1]);
                            $dist2 = round($res[$i][2]);
                            $tot_dist = round($res[$i][3]);
                            $av_dist = round($res[$i][4]);
                            echo "<tr>
                                    <td>$team</td>
                                    <td>$dist1</td>
                                    <td>$dist2</td>
                                    <td>$tot_dist</td>
                                    <td>$av_dist</td>
                                  </tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
