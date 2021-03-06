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
$overview_settings = $mysql->get_overview_settings();
$screens = $overview_settings[0][0];
$shifts = $overview_settings[0][1];
$frequency = $overview_settings[0][2];
$n_teams = sizeof($mysql->get_teams());
$teams_per_screen = $n_teams / $screens;
if ($shifts != 0){
    $teams_per_shift = $teams_per_screen / $shifts;
}
else{
    $teams_per_shift = $teams_per_screen;
}

if( isset($_POST['submit_screens'])) {
    $mysql->update_screens_setting($_POST['selectScreens']);
    $mysql->update_shifts_setting(0);
    $shifts = 0;
    $screens=$_POST['selectScreens'];
    $teams_per_screen = $n_teams / $screens;
    $teams_per_shift = $teams_per_screen;
}

if( isset($_POST['submit_shifts'])) {
    $mysql->update_shifts_setting($_POST['selectShifts']);
    $shifts=$_POST['selectShifts'];
    if ($shifts != 0){
        $teams_per_shift = $teams_per_screen / $shifts;
    }
    else{
        $teams_per_shift = $teams_per_screen;
    }
}

if( isset($_POST['submit_freq'])) {
    $mysql->update_freq_setting($_POST['selectFreq']);
    $frequency=$_POST['selectFreq'];
}

function populate_screens($n_teams){
    $factors = factor($n_teams);
    echo "<select name=\"selectScreens\">";
    echo "<option value=\"1\">1 screen with $n_teams teams</option>";
    for ($i=0; $i<sizeof($factors); $i++) {
        echo "<option value=\"" . $factors[$i] . "\">" . $factors[$i] . " screens with " . $n_teams/$factors[$i]  . " teams</option>";
    }
    echo "</select>";
}

function populate_shifts($teams_per_screen){
    $factors = factor($teams_per_screen);
    echo "<select name=\"selectShifts\">";
    echo "<option value=\"1\">1 group with $teams_per_screen teams per group</option>";
    for ($i=0; $i<sizeof($factors); $i++) {
        echo "<option value=\"" . $factors[$i] . "\">" . $factors[$i] . " groups with " . $teams_per_screen/$factors[$i]  . " teams per group</option>";
    }
    echo "</select>";
}

function populate_freq($shifts){
    echo "<select name=\"selectFreq\">";
    echo "<option value=\"0\">0 seconds between shifts</option>";
    for ($i=0; $i<=60; $i+=5) {
        echo "<option value=\"" . $i . "\">" . $i . " seconds between shifts</option>";
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
    <head lang="da">
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">
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
        <div style="width:500px; margin-left:auto; margin-right:auto">
            <p>Number of teams: <?php echo $n_teams; ?></p>
            <p>Number of screens: <?php echo $screens; ?></p>
            <p>Teams per screen: <?php echo $teams_per_screen; ?></p>
            <p>Number of groups per screen: <?php echo $shifts; ?></p>
            <p>Number of teams per group: <?php echo $teams_per_shift; ?></p>
            <p>Frequency of shifts: <?php echo $frequency . "s"; ?></p>
            <form method="post" id="set_screens">
                <div>
                    <label for="choices">screen options:</label>
                        <?php populate_screens($n_teams); ?>
                    <input type="submit" name="submit_screens" id="submit_screens" value="Set">
                </div>
            </form>
            <form method="post" id="set_shifts">
                <div>
                    <label for="choices">shifts options:</label>
                        <?php populate_shifts($teams_per_screen); ?>
                    <input type="submit" name="submit_shifts" id="submit_shifts" value="Set">
                </div>
            </form>
            <form method="post" id="set_freq">
                <div>
                    <label for="choices">frequency options:</label>
                        <?php populate_freq($shifts); ?>
                    <input type="submit" name="submit_freq" id="submit_freq" value="Set">
                </div>
            </form>
            
        </div>
    </body>
</html>
