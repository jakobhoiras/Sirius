<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
$n_divs = $mysql->get_divs()[0][0];
$n_teams_temp = $mysql->get_teams();
$n_teams = sizeof($n_teams_temp);
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

function populate($n_teams, $n_divs){
    $factors = factor($n_teams);
    echo "<select name=\"selectDivs\">";
    echo "<option value=\"0\">ingen divisioner</option>";
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



<html>
    <head>
        <title>
            Divisions
        </title>
    </head>
    <body>
        <div style="width:580px; margin-left:auto; margin-right:auto">
            <p>number of teams: <?php echo $n_teams; ?></p>
            <p>number of divisions: <?php echo $n_divs; ?></p>
            <p>number of teams per division: <?php echo $n_teams_divs; ?></p>
            <form method="post" id="set_div">
                <div>
                    <label for="choices">division options:</label>
                    <p id="choices">
                        <?php populate($n_teams, $n_divs); ?>
                    </p>
                    <input type="submit" name="submit" id="submit" value="Set">
                </div>
            </form>
        </div>
    </body>
</html>
