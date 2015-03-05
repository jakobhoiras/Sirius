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
$state = $mysql -> get_state();
$progress = $mysql -> get_game_progress($_SESSION['cg']);
$res = $mysql->get_time($_SESSION['cg']);
$time = $res;
if ($time[0][1] == 0){
    $time_disp1 = '-' . $time[0][0] . ':00:00';
}
else{
    $time_disp1 = clock('first', $mysql, $time);
}
if ($time[0][3] == 0){
    $time_disp2 = '-' . $time[0][0] . ':00:00';
}
else{
    $time_disp2 = clock('second', $mysql, $time);
}

function clock($half, $mysql, $time){
    $offset = ($mysql -> get_time_offset($_SESSION['cg'],$half));
    if ($half == 'first' and $time[0][2] != 0){
        $se = $time[0][2]-$time[0][1];
    }
    else if ($half == 'first' and $time[0][2] == 0){
        $se = time()-$time[0][1];
    }
    else if ($half == 'second' and $time[0][4] != 0){
        $se = $time[0][4]-$time[0][3];
    }
    else if ($half == 'second' and $time[0][4] == 0){
        $se = time()-$time[0][3];
    }
    $ctime = $se - $offset;
    $half_time = $time[0][0]*60;
    $rest_time = $half_time - $ctime;
    $min = ($rest_time)/(60);
    $sec = ($half_time-floor($min)*60) - ($half_time-$min*60);
    if ($min >= 0 and $sec >= 0){
        if ($min >= 10 and $sec >= 10){
           $time_disp1 = '-' . floor($min) . ':' . floor($sec); 
        }
        else if ($min >= 10 and $sec < 10){
           $time_disp1 = '-' . floor($min) . ':0' . floor($sec);
        }
        else if ($min < 10 and $sec >= 10){
           $time_disp1 = '-0' . floor($min) . ':' . floor($sec);
        }
        else{
            $time_disp1 = '-0' . floor($min) . ':0' . floor($sec);
        }
    }
    else{
        if(floor($sec)<1){
            $sec=60;
            $min -= 1;
        }
        if($sec<=51 && $min<=-10){ 
            $time_disp1 = floor(abs($min)) . ':' . (60 - floor($sec));
        }
        else if($min<=-10 && $sec>51){
            $time_disp1 = floor(abs($min)) . ':0' . (60 - floor($sec));
        }
        else if($sec<=51 && $min>=-10){
            $time_disp1 = '0' . floor(abs($min)) . ':' . (60 - floor($sec));
        }
        else{
            $time_disp1 = '0' . floor(abs($min)) . ':0' . (60 - floor($sec));
        }
    }
    return $time_disp1;
}

?>

<html lang="da">
    <head>
        <title>
            Consultant time panel
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
	<body onload="init();">
        <div style="width:100%; padding-bottom:5px">
            <button id="back" type="button" onclick=change_page('consultant_panel')>Consultant menu</button>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
        <div style="width:800px; margin-left:auto; margin-right:auto;">
		<div style="width:38%; float:left">
			<h1>
				<p>Options</p>
			</h1>
			<form method="post">
				<input id="start_first_half_btn" type="button" value="start 1. half" onclick=start_first_half() disabled><br>
                <input id="end_first_half_btn" type="button" value="end 1. half" onclick=end_first_half() disabled/><br>
                <input id="start_second_half_btn" type="button" value="start 2. half" onclick=start_second_half() disabled><br>
                <input id="end_second_half_btn" type="button" value="end 2. half" onclick=end_second_half() disabled/><br>
				<input id="pause_btn" type="button" value="pause" onclick=pause_game() disabled/><br>
				<input id="restart_btn" type="button" value="restart" onclick=restart_game() disabled/>
			</form>
		</div>
		<div style="width:38%; float:left">
			<h1>
				<p>Status</p>
			</h1>
			<p id="g_state">Game state:  <?php echo $state; ?> </p>
            <p id="g_time1">1. half:  <?php echo $time_disp1; ?> </p>
            <p id="g_time2">2. half:  <?php echo $time_disp2; ?> </p>
		</div>
        <div style="width:24%; float:left">
			
		</div>
        </div>
	</body>
</html>
 
<script>
var timerIDstartgame;
var start_time;
var timerIDCheck;
var timerIDupdate;

function init(){
    var state = <?php echo json_encode($state) ?>;
    var progress = <?php echo json_encode($progress) ?>;
    console.log(state);
    console.log(progress);
    if (state == 'pause'){
        document.getElementById("restart_btn").disabled = false;
    }
    else if (state == 'ready'){
        if (progress == 'start'){
            document.getElementById("start_first_half_btn").disabled = false;
        }
        else if (progress == 'waiting second half'){
            document.getElementById("start_second_half_btn").disabled = false;
        }
    }
    else if (state == 'open'){
        if (progress == 'waiting first half'){
            document.getElementById("end_first_half_btn").disabled = false;
            document.getElementById("pause_btn").disabled = false;
        }
        else if (progress == 'first half'){
            document.getElementById("end_first_half_btn").disabled = false;
            document.getElementById("pause_btn").disabled = false;
            var offset = <?php echo json_encode($mysql -> get_time_offset($_SESSION['cg'],'first')) ?>;
            start_clock(offset,'first');
        }
        else if (progress == 'waiting second half'){
            document.getElementById("start_second_half_btn").disabled = false;
        }
        else if (progress == 'second half'){
            document.getElementById("end_second_half_btn").disabled = false;
            document.getElementById("pause_btn").disabled = false;
            var offset = <?php echo json_encode($mysql -> get_time_offset($_SESSION['cg'],'second')) ?>;
            start_clock(offset,'second');
        }
    }
    else if (state == 'stop'){
        
    }
}

function start_first_half(){
    var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
                document.getElementById("g_state").innerHTML = xmlhttp.responseText; 
                if (xmlhttp.responseText == 'the game is running'){
                    start_clock(0,'first'); //start timer
                }   
                else{
                    timerIDstartgame = setInterval('check_teams_state("first")', 2000);
                }
           	}
        }
    xmlhttp.open("GET","game_control.php?func=start_first_half", true);
    xmlhttp.send();
    document.getElementById("start_first_half_btn").disabled = true;
    document.getElementById("end_first_half_btn").disabled = false;
    document.getElementById("start_second_half_btn").disabled = true;
    document.getElementById("end_second_half_btn").disabled = true;
    document.getElementById("restart_btn").disabled = true;
    document.getElementById("pause_btn").disabled = false;
}


function end_first_half(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
	    if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            if (xmlhttp.responseText == 1){  
		        document.getElementById("g_state").innerHTML = 'Game state: waiting for second half to start';
                //clearInterval(timerIDCheck);
                clearInterval(timerIDupdate); 
            }      
	    }
	}
	xmlhttp.open("GET","game_control.php?func=stop_first_half", true);
	xmlhttp.send();
    document.getElementById("start_first_half_btn").disabled = true;
    document.getElementById("end_first_half_btn").disabled = true;
    document.getElementById("start_second_half_btn").disabled = false;
    document.getElementById("end_second_half_btn").disabled = true;
    document.getElementById("restart_btn").disabled = true;
    document.getElementById("pause_btn").disabled = true;
}

function start_second_half(){
    var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
                document.getElementById("g_state").innerHTML = xmlhttp.responseText; 
                if (xmlhttp.responseText == 'the game is running'){
                    start_clock(0,'second'); //start timer
                }   
                else{
                    timerIDstartgame = setInterval('check_teams_state("second")', 2000);
                }
           	}
        }
    xmlhttp.open("GET","game_control.php?func=start_second_half", true);
    xmlhttp.send();
    document.getElementById("start_first_half_btn").disabled = true;
    document.getElementById("end_first_half_btn").disabled = true;
    document.getElementById("start_second_half_btn").disabled = true;
    document.getElementById("end_second_half_btn").disabled = false;
    document.getElementById("restart_btn").disabled = true;
    document.getElementById("pause_btn").disabled = false;
}

function end_second_half(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
	    if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            if (xmlhttp.responseText == 1){  
		        document.getElementById("g_state").innerHTML = 'Game state: game is over';
                //clearInterval(timerIDCheck);
                clearInterval(timerIDupdate); 
            }      
	    }
	}
	xmlhttp.open("GET","game_control.php?func=stop_second_half", true);
	xmlhttp.send();
    document.getElementById("start_first_half_btn").disabled = true;
    document.getElementById("end_first_half_btn").disabled = true;
    document.getElementById("start_second_half_btn").disabled = true;
    document.getElementById("end_second_half_btn").disabled = true;
    document.getElementById("restart_btn").disabled = true;
    document.getElementById("pause_btn").disabled = true;
}

function check_teams_state(half){
    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            if (xmlhttp.responseText == 1){
                document.getElementById("g_state").innerHTML = 'the game is running'
                clearInterval(timerIDstartgame);
                start_clock(0,half);
            }
            else{

            }
	    }
    }
    if (half == 'first'){
	    xmlhttp.open("GET","game_control.php?func=teams_state1", true);
    }
    else if (half == 'second'){
        xmlhttp.open("GET","game_control.php?func=teams_state2", true);
    }
	xmlhttp.send();  
}



function start_clock(offset, half){
    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            start_time = new Date(parseInt(xmlhttp.responseText)); // gemmer first half start tid
            console.log(xmlhttp.responseText);
            //timerIDCheck = setInterval('check_state()',10000); // tjekker om spillet er sat på pause eller halvegen er afsluttet hvert 10. sekund
            startInterval(<?php echo $time[0][0] ?>, offset, half); // skal kaldes når tiden skal startes   
	    }
    }
    if (half == 'first'){
	    xmlhttp.open("GET","get_time.php?func=first_half", true);
    }
    else if (half == 'second'){
        xmlhttp.open("GET","get_time.php?func=second_half", true);
    }
	xmlhttp.send();
}
    
/*function check_state(){
    var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            if (xmlhttp.responseText == 'pause'){
                // DO SOMETHING            
            } 
            else if (xmlhttp.responseText == ''){
                // DO SOMETHING
            }
	    }
    }
	xmlhttp.open("GET","get_state.php", true);
	xmlhttp.send();
}*/


function pause_game(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {  
            if (xmlhttp.responseText == 1){  
	    	    document.getElementById("g_state").innerHTML = 'Game state: pause'; 
                //clearInterval(timerIDCheck);
                clearInterval(timerIDupdate);
            }      
	   	}
	}
	xmlhttp.open("GET","game_control.php?func=pause_game", true);
	xmlhttp.send();
    document.getElementById("start_first_half_btn").disabled = true;
    document.getElementById("end_first_half_btn").disabled = true;
    document.getElementById("start_second_half_btn").disabled = true;
    document.getElementById("end_second_half_btn").disabled = true;
    document.getElementById("restart_btn").disabled = false;
    document.getElementById("pause_btn").disabled = true;
}    

var half_restart;
function restart_game(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {   
	        document.getElementById("g_state").innerHTML = 'Game state: the game is running'; 
            var table = xmlhttp.responseText.split(" ");
            var offset = table[0]*1000;
            half = table[1];
            start_clock(offset, half);
            document.getElementById("start_first_half_btn").disabled = true;
            if ( half == 'first'){
                document.getElementById("end_first_half_btn").disabled = false;
            }
            else if ( half == 'second'){
                document.getElementById("end_first_half_btn").disabled = true;
            }
            document.getElementById("start_second_half_btn").disabled = true;
            if ( half == 'first'){
                document.getElementById("end_second_half_btn").disabled = true;
            }
            else if ( half == 'second'){
                document.getElementById("end_second_half_btn").disabled = false;
            }
            document.getElementById("restart_btn").disabled = true;
            document.getElementById("pause_btn").disabled = false;         
	   	}
	}
	xmlhttp.open("GET","game_control.php?func=restart_game", true);
	xmlhttp.send();
}   

/*function find_half(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
	 	if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            half_restart = xmlhttp.responseText;
                 
	   	}
	}
	xmlhttp.open("GET","game_control.php?func=get_half", true);
	xmlhttp.send();
}  */ 

    
function startInterval(game_time, offset, half){  
    timerIDupdate = setInterval('updateTime(' + game_time + ',' + offset + ',"' + half + '");', 100);  
}

function updateTime(game_time_min, offset, half){
    var game_time_ms = game_time_min * 60000;
    var nowMS = Date.now();
    console.log(nowMS);
    console.log(start_time.getTime());
    console.log(offset);
    var minutes = (game_time_ms - (nowMS - start_time.getTime() - offset))/(1000*60);
    var seconds = 60*(game_time_min-Math.floor(minutes)) - (game_time_ms - minutes*60000)/1000.0;
    if (half == 'first'){
        var clock = document.getElementById('g_time1');
    }
    else if (half == 'second'){
        var clock = document.getElementById('g_time2');
    }
    if(clock){
        if(seconds>=0 && minutes>=0){ 
            if(seconds>=10 && minutes>=10){ 
                clock.innerHTML = 'Time: -' + Math.floor(minutes) + ':' + Math.floor(seconds);
            }
            else if(minutes>=10 && seconds<10){
                clock.innerHTML = 'Time: -' + Math.floor(minutes) + ':0' + Math.floor(seconds);
            }
            else if(seconds>=10 && minutes<10){
                clock.innerHTML = 'Time: -0' + Math.floor(minutes) + ':' + Math.floor(seconds);
            }
            else{
                clock.innerHTML = 'Time: -0' + Math.floor(minutes) + ':0' + Math.floor(seconds);
            }
        }
        else{
            if(Math.floor(seconds)<1){
                seconds=60;
                minutes -= 1;
            }
            if(seconds<=51 && minutes<=-10){ 
                clock.innerHTML = 'Time: ' + Math.floor(Math.abs(minutes)) + ':' + (60 - Math.floor(seconds));
            }
            else if(minutes<=-10 && seconds>51){
               clock.innerHTML = 'Time: ' + Math.floor(Math.abs(minutes)) + ':0' + (60 - Math.floor(seconds));
            }
            else if(seconds<=51 && minutes>=-10){
                clock.innerHTML = 'Time: 0' + Math.floor(Math.abs(minutes)) + ':' + (60 - Math.floor(seconds));
            }
            else{
                clock.innerHTML = 'Time: 0' + Math.floor(Math.abs(minutes)) + ':0' + (60 - Math.floor(seconds));
            }
        }
    }
} 

function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
}
</script>

<!-- // -->
