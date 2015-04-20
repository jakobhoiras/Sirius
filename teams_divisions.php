<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();

$mysql = new Mysql_spil();
$n_divs = $mysql->get_divs()[0][0];
$teams = $mysql->get_teams();
$n_teams = sizeof($teams);

function populate($n_teams, $n_divs){
    $factors = factor($n_teams);
    echo "<select name=\"selectDivs\">";
    echo "<option value=\"1\">1 division with all teams</option>";
    for ($i=0; $i<sizeof($factors)-1; $i++) {
        echo "<option value=\"" . $factors[$i] . "\">" . $factors[$i] . " divisions with " . $n_teams/$factors[$i]  . " teams</option>";
    }
    echo "</select>";
}

function populate_div($n_divs){
	$div_names = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y');
    echo "<select name=\"selectTeamDiv\" id=\"selectTeamDiv\">";
    for ($i=0; $i<$n_divs; $i++) {
        echo "<option value=\"" . $div_names[$i] . "\">" . $div_names[$i] . "</option>";
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

if( $_POST && !empty($_POST['NTeams'])) {
	$mysql->save_teams($_POST['NTeams']);
	$mysql->set_teams_div_and_init_score($_POST['NTeams'],1);
	$mysql->update_division_settings(1);
	header("location: teams_divisions.php");
}


if( $_POST && !empty($_POST['submit'])) {
    $mysql->update_division_settings($_POST['selectDivs']);
    $n_divs=$_POST['selectDivs'];
	$mysql->set_teams_div_and_init_score($n_teams,$n_divs);
	header("location: teams_divisions.php");
}

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

?>



<html>
    <head>
        <title>
            Create teams
        </title>

    <link rel="stylesheet" href="style.css" type="text/css" />
	
	<script>
var row_picked = -1;

function change_page(page_name) {
   window.location.href = ( page_name + ".php?cg=" + <?php echo json_encode($_SESSION['cg']) ?>);
}

function send_div_change(){
	var select = document.getElementById("selectTeamDiv");
	var selectedValue = select.options[select.selectedIndex].value;
	document.getElementById("row" + row_picked).cells[1].innerHTML = selectedValue;
	/*var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    xmlhttp.open("GET","update_div.php?div=" + selectedValue + "&teamID=" + i ,true);
    xmlhttp.send();*/
}

function pick_row(i){
    // for controlling style and graphics when chosing a zone in the table
    document.getElementById("row" + i).style.background = "blue";
    document.getElementById("row" + i).setAttribute("value", 'p');
    var n_teams = document.getElementById("teamDivTable").rows.length;
    if (row_picked != i && row_picked != -1){
        document.getElementById("row" + row_picked).style.background = "white";
        document.getElementById("row" + row_picked).setAttribute("value", 'np');
    }
    row_picked = i;
}

function temppick_row(i){
    // for controlling style and graphics when hovering a zone in the table
    document.getElementById("row" + i).style.background = "blue";
}

function unpick_row(i){
    // for controlling style and graphics when dehovering a zone in the table
    if (document.getElementById("row" + i).getAttribute("value") == 'np'){
		if (i % 2 == 0){
         	document.getElementById("row" + i).style.background = "#fff";
		}
		else{
			document.getElementById("row" + i).style.background = "#eee";
		}
    }
}
	</script>

    </head>
    <body>

        <div style="width:100%; padding-bottom:5px; display:inline">
            <?php
                if ($_SESSION['status'] == 'authorized_admin'){
                    echo '<button id="back" type="button" onclick=change_page("spil_overblik") >Game menu</button>';
                } else{
                    echo '<button id="back" type="button" onclick=change_page("start_user") style="display:inline" >Start menu</button>';
                }
            ?>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
		<div style="float:left; width:100%; height:20%">
		    <div style="width:80%; margin-left:auto; margin-right:auto;	">
				<div style="float:left; width:50%; height:100%; text-align:center">		        
					<form method="post">
                		<input type="input" placeholder="Number of teams" name="NTeams" />
            		</form>
					<form method="post">
                		<div>
                    		<p id="choices" style="display:inline">
                       			<?php populate($n_teams, $n_divs); ?>
                    		</p>
                   			<input type="submit" name="submit" id="submit" value="Set" style="display:inline" />
                		</div>
            		</form>
				</div>
				<div style="float:left;width:50%;height:100%; text-align:center">
					<?php echo "<p> Number of teams: $n_teams </p>" ?>
					<?php echo "<p> Number of divisions: $n_divs </p>" ?>
				</div>
		    </div>
		</div>

		<div style="float:left; width:100%; height:50%;">
			<div style="width:20%;height:100%; margin-left:auto; margin-right:auto; overflow:auto">
                <!--div style="text-align:center">
                  	<p id="choices" style="display:inline">
                   		<?php populate_div($n_divs); ?>
                   	</p>
                	<button type="button" id="teamDiv" style="display:inline" onclick="send_div_change()">Set</button>
                </div-->

				<?php 
					echo "<table id='teamDivTable' style='border-collapse:collapse; margin-right:auto; margin-left:auto; text-align:center'>";
					//echo "<caption>List of zones:</caption>";
					echo "<tr>";
					echo "<th>team</th>";
					echo "<th>division</th>";
					echo "</tr>";

					for ($i=0; $i<$n_teams; $i++){
						echo "<tr value='np' id='row" . $i . "' onclick=pick_row($i) onmouseover=temppick_row($i) onmouseout=unpick_row($i)>";
						echo "<td>" . $teams[$i][0] . "</td>";
						echo "<td>" . $teams[$i][7] . "</td>";
						echo "</tr>";
					}
					echo "</table>"; 

				?>
			</div>
		</div>
    </body>
</html>

