<?php
/*require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();
*/

require 'Mysql.php';

$current_game = $_GET['cg'];
$_SESSION['cg'] = $current_game;

$mysql = new Mysql_spil();
$res = $mysql -> get_status();
if ($res[0] == ''){
	$map_name='ikke valgt';
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
	$yn = 'ja';
}
if ($res[4] == ''){
	$n_opgaver = 0;
}
else{
	$n_opgaver = sizeof($res[4]);
}
if ($res[5] == false){
	$yn2='nej';
}
else{
	$yn2 = 'ja';
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

?>

<html lang="da">
    <head>
        <title>
            Spil menu
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
	<body style="width:800px; margin-left:auto; margin-right:auto;">
		<div style="width:38%; float:left">
			<h1>
				<p>Muligheder</p>
			</h1>
			<form method="post">
				<input type="button" value="importer opgaver" onclick=change_page("importer_opgaver") /><br>
				<input type="button" value="importer kort" onclick=change_page("importer_kort") /><br>
				<input type="button" value="Zoner" onclick=change_page("draw_zone") /><br>
				<input type="button" value="Ruter" onclick=change_page("create_rutes") /><br>
				<input type="button" value="Hold" onclick=change_page("create_teams") /><br>
                <input type="button" value="Divisioner" onclick=change_page("set_div") /><br>
				<input type="button" value="Fordel ruter" onclick="fordel_ruter(<?php echo $n_ruter . ',' . $n_hold; ?>)" /><br>
				<input type="button" value="Fordel opgaver" onclick="fordel_opgaver(<?php echo $n_opgaver . ',' . $n_rute_length . ',' . $n_hold; ?>)" /><br>
				<p id="res"></p>
			</form>
		</div>
		<div style="width:38%; float:left">
			<h1>
				<p>Status</p>
			</h1>
			<p>Map:  <?php echo $map_name; ?> </p>
			<p>Opgaver: <?php echo $n_opgaver; ?></p>
			<p>Zoner: <?php echo $n_zoner; ?></p>
			<p>Ruter: <?php echo $n_ruter; ?></p>
			<p>Rute længde: <?php echo $n_rute_length; ?></p>
			<p>Hold: <?php echo $n_hold; ?></p>
            <p>Divisioner: <?php echo $divs; ?></p>
			<p id="fordel_ruter">Ruter fordelt: <?php echo $yn; ?></p>
			<p id="fordel_opgaver">Opgaver fordelt: <?php echo $yn2; ?></p>
		</div>
        <div style="width:24%; float:left">
			<h1>
				<p>Tid</p>
			</h1>
            <p>Tid pr. halveg: <?php echo $time; ?>min</p>
            <form method="post" action="">
            <p style="text-align:center">
                <label for="time" style="text-decoration:underline;">Angiv tid i min: </label>
                <input type="text" name="time" />
            </p>
            <p style="text-align:center">
                <input type="submit" value="submit" name="submit"/>
            </p>
	        </form>
		</div>
	</body>
</html>

<script>
    function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }

	function fordel_ruter(n_ruter, n_hold){
		if (n_ruter != 2* n_hold){
			document.getElementById("res").innerHTML = "Der skal være dobbelt så mange ruter som hold!";
		}
		else{
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
		    	if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		        	xmlhttp.responseText.split(" ");  
					document.getElementById("fordel_ruter").innerHTML = 'Ruter fordelt: ja'; 
					document.getElementById("res").innerHTML = "";         
		    	}
			}
			xmlhttp.open("GET","fordel_ruter.php",true);
			xmlhttp.send();
		}
	}

	function fordel_opgaver(n_opgaver, n_rute_length, n_hold){
		if (n_opgaver != 2*3*n_rute_length){
			document.getElementById("res").innerHTML = "6 x rute længden skal være lig med antallet opgaver!";
		}
		else{
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
		    	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
					document.getElementById("fordel_opgaver").innerHTML = 'Opgaver fordelt: ja'; 
					document.getElementById("res").innerHTML = "";         
		    	}
			}
			xmlhttp.open("GET","fordel_opgaver.php?rute_length=" + n_rute_length + "&n_hold=" + n_hold, true);
			xmlhttp.send();
		}
	}
</script>
