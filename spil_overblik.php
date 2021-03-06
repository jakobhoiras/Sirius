<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();


if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

$current_game = $_GET['cg'];
$_SESSION['cg'] = $current_game;

$mysql = new Mysql_spil();
$res = $mysql -> get_status();
if ($res[0] == ''){
	$map_name='Not chosen';
}
else{
	$map_name = $res[0][0][0];
}
if ($res[1] == ''){
	$n_zoner=0;
}
else{
	$n_zoner = sizeof($res[1]);
}
if ($res[2] == ''){
	$n_ruter=0;
}
else{
	$n_ruter = sizeof($res[2]);
}
if ($res[2] == ''){
	$n_rute_length=0;
}
else{
	$n=0;
	for ($i=1; $i<sizeof($res[2][0]); $i++){
		if ($res[2][0][$i] != NULL){
			$n += 1;
		}
	}
	$n_rute_length = $n;
}
if ($res[3] == ''){
	$n_hold=0;
}
else{
	$n_hold = sizeof($res[3]);
}
if ($res[3][sizeof($res[3])-1] == NULL){
	$yn='nej';
}
else{
	$yn = 'yes';
}
if ($res[4] == ''){
	$n_opgaver = 0;
}
else{
	$n_opgaver = sizeof($res[4]);
}
if ($res[5] == false){
	$yn2='no';
}
else{
	$yn2 = 'yes';
}
if ($res[6] == ''){
    $time = 0;
}
else{
    $time = $res[6][0][0];
}
if( $_POST && !empty($_POST['time'])) {
    $mysql->update_half_time($_POST['time']);
    $time = $_POST['time'];
}
if ($res[7][0][0] == 0){
    $divs = 'Ingen divisioner';
}
else{
    $divs = $res[7][0][0];
}

$yn3 = 'no';
$yn4 = 'no';

if( $_POST && !empty($_POST['zip']) ) {
	require 'zipper.php';
	$zip = new zipper();
    $zip -> createZip();
	$yn4 = 'yes';
}

?>

<html lang="da">
    <head>
        <title>
            Game menu
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
	<body>
        <div style="width:100%; float:left; ">
            <button id="back" type="button" onclick=change_page('start_admin')>Start menu</button>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
        <div style="width:800px; margin-left:auto; margin-right:auto;">
        <div style="width:38%; float:left;">
			<h1>
				<p>Actions</p>
			</h1>
			<form method="post">
				<input type="button" value="Import Assignments" onclick=change_page("importer_opgaver") /><br>
				<input type="button" value="Import maps" onclick=change_page("importer_kort") /><br>
				<input type="button" value="Zones" onclick=change_page("draw_zone") /><br>
				<input type="button" value="Rutes" onclick=change_page("create_rutes") /><br>
				<input type="button" value="Teams" onclick=change_page("teams_divisions") /><br>
                <input type="button" value="Display setup" onclick=change_page("set_overview_screen") /><br>
                <input type="button" value="Map display" onclick=change_page("screen_options") /><br>
                <input type="button" value="Consultant panel" onclick=change_page("consultant_panel") /><br>
				<input type="button" value="Distribute rutes" onclick="fordel_ruter(<?php echo $n_ruter . ',' . $n_hold; ?>)" /><br>
				<input type="button" value="Distribute Assignments" onclick="fordel_opgaver(<?php echo $n_opgaver . ',' . $n_rute_length . ',' . $n_hold; ?>)" /><br>
                <input type="button" value="Generate QR-codes" onclick=create_qr_codes() /><br>
				<p id="res"></p>
			</form>
			<form method="post" action="">
                <input type="submit" value="Zip assignments" name="zip"/>
            </form>
            <form method="get" action="<?php echo 'Games/' . $_SESSION['cg'] . '/json/qr_codes.zip' ?>">
                <button type="submit">Download QR-codes</button>
            </form>
		</div>
		<div style="width:38%; float:left;">
			<h1>
				<p>Status</p>
			</h1>
			<p>Map:  <?php echo $map_name; ?> </p>
			<p>Assignments: <?php echo $n_opgaver; ?></p>
			<p>Zones: <?php echo $n_zoner; ?></p>
			<p>Rutes: <?php echo $n_ruter; ?></p>
			<p>Rute length: <?php echo $n_rute_length; ?></p>
			<p>Teams: <?php echo $n_hold; ?></p>
            <p>Divisions: <?php echo $divs; ?></p>
			<p id="fordel_ruter">Rutes distributed: <?php echo $yn; ?></p>
			<p id="fordel_opgaver">Assignment distributed: <?php echo $yn2; ?></p>
            <p id="qr_gen">QR-codes generated: <?php echo $yn3; ?></p>
			<p id="ass_zip">Assignment zipped: <?php echo $yn4; ?></p>
		</div>
        <div style="width:24%; float:left">
			<h1>
				<p>Time</p>
			</h1>
            <p>Time per period: <?php echo $time; ?>min</p>
            <form method="post" action="">
            <p style="text-align:center">
                <label for="time" style="text-decoration:underline;">Time in minutes: </label>
                <input type="text" name="time" />
            </p>
            <p style="text-align:center">
                <input type="submit" value="submit" name="submit"/>
            </p>
	        </form>
		</div>
        </div>
	</body>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ( page_name + ".php");
    }

	function fordel_ruter(n_ruter, n_hold){
		if (n_ruter != 2* n_hold){
			document.getElementById("res").innerHTML = "The number of rutes has to be twice the number of teams!";
		}
		else{
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
		    	if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		        	xmlhttp.responseText.split(" ");  
					document.getElementById("fordel_ruter").innerHTML = 'Rutes distributed: yes'; 
					document.getElementById("res").innerHTML = "";         
		    	}
			}
			xmlhttp.open("GET","fordel_ruter.php",true);
			xmlhttp.send();
		}
	}

	function fordel_opgaver(n_opgaver, n_rute_length, n_hold){
		if (n_opgaver != 2*3*n_rute_length){
			document.getElementById("res").innerHTML = "The number of assignments has be 6 times the rute length";
		}
		else{
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
		    	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
					document.getElementById("fordel_opgaver").innerHTML = 'Assignments distributed: yes'; 
					document.getElementById("res").innerHTML = "";         
		    	}
			}
			xmlhttp.open("GET","fordel_opgaver.php?rute_length=" + n_rute_length + "&n_hold=" + n_hold, true);
			xmlhttp.send();
		}
	}

    function create_qr_codes(){
        var xmlhttp = new XMLHttpRequest();
	    xmlhttp.onreadystatechange=function() {
	    	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
                if (xmlhttp.responseText == true){  
				    document.getElementById("qr_gen").innerHTML = 'QR-codes generated: yes'; 
				    document.getElementById("res").innerHTML = "";
                }      
	       	}
		}
		xmlhttp.open("GET","make_qr_json.php", true);
		xmlhttp.send();
    }
</script>
