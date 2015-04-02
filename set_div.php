<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();


if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

$mysql = new Mysql_spil();
$n_divs = $mysql->get_divs()[0][0];
$teams = $mysql->get_teams();
$n_teams = sizeof($teams);
if($n_divs != 0){
    $n_teams_divs = $n_teams / $n_divs;
}
else{
    $n_teams_divs=0;
}
if( $_POST && !empty($_POST['submit'])) {
    $mysql->update_division_settings($_POST['selectDivs']);
    $n_divs=$_POST['selectDivs'];
    if($n_divs != 0){
    $n_teams_divs = $n_teams / $n_divs;
    }
    else{
        $n_teams_divs=0;
    }   
}
$yn = 'yes';
for ($i=0; $i<$n_teams; $i++){
    if ($teams[$i][7] == NULL){
       $yn = 'no';
    }
}

if( isset($_POST['distribute'])){
    $teamIDs = array();
    for ($i=0; $i<$n_teams; $i++){
        array_push($teamIDs,$teams[$i][0]);
    }
    shuffle($teamIDs);
    $div_names = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    for($i=0; $i<$n_divs; $i++){
        for($j=$i*($n_teams/$n_divs); $j<($i+1)*($n_teams/$n_divs); $j++){
            $mysql->set_team_div($teamIDs[$j],$div_names[$i]);
            $mysql->init_team_score($teamIDs[$j],$div_names[$i]);
        }
    }
    $yn = 'yes';
}

function populate($n_teams, $n_divs){
    $factors = factor($n_teams);
    echo "<select name=\"selectDivs\">";
    echo "<option value=\"1\">1 division</option>";
    for ($i=0; $i<sizeof($factors)-1; $i++) {
        echo "<option value=\"" . $factors[$i] . "\">" . $factors[$i] . " divisioner med " . $n_teams/$factors[$i]  . " hold</option>";
    }
    echo "</select>";
}


function factor($n){
    $factors = array();
	for ($i=2; $i<401; $i++){
        if( $n % $i == 0){
            array_push($factors,$i);
        }
    }
    return $factors;
}

?>

<script>

function change_page(page_name) {
   window.location.href = ( page_name + ".php?cg=" + <?php echo json_encode($_SESSION['cg']) ?>);
}

</script>


<html>
    <head>
        <title>
            Divisions
        </title>
    </head>
    <body>
        <div style="width:100%; padding-bottom:5px">
            <button id="back" type="button" onclick=change_page('spil_overblik')>Game menu</button>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
        <div style="width:580px; margin-left:auto; margin-right:auto">
            <p>number of teams: <?php echo $n_teams; ?></p>
            <p>number of divisions: <?php echo $n_divs; ?></p>
            <p>number of teams per division: <?php echo $n_teams_divs; ?></p>
            <p>teams distributed to divisions: <?php echo $yn; ?></p>
            <form method="post" id="set_div">
                <div>
                    <label for="choices">division options:</label>
                    <p id="choices">
                        <?php populate($n_teams, $n_divs); ?>
                    </p>
                    <input type="submit" name="submit" id="submit" value="Set">
                </div>
            </form>
            <form method="post" id="set_div2">
                <div>
                    <input type="submit" name="distribute" id="distribute" value="distribute">
                </div>
            </form>
        </div>
    </body>
</html>
