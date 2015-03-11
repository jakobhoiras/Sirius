<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$start_time = time()*1000;
//$res = $mysql->update_start_time_first_half($start_time);
$games = $mysql->get_games();
for ($i = 0; $i < sizeof($games); $i++) {
    if ($games[$i][1] === $_SESSION['cg']) {
        $gameID = $games[$i][0];
    }
}
?>

<script type="text/javascript">
var gameID = <?php echo $gameID; ?>;
var team1 = [[12.5038,55.7702],
             [12.5036,55.7696],
             [12.5030,55.7688],
             [12.5027,55.7682],
             [12.5023,55.7675],
             [12.5020,55.7670],
             [12.5017,55.7665]]
var team2 = [[12.5039,55.7702],
             [12.5033,55.7700],
             [12.5025,55.7697],
             [12.5016,55.7694],
             [12.5011,55.7691],
             [12.5009,55.7685],
             [12.5001,55.7687]]
var team3 = [[12.5040,55.7702],
             [12.5032,55.7695],
             [12.5019,55.7683],
             [12.5011,55.7674],
             [12.5004,55.7666],
             [12.4993,55.7656],
             [12.4985,55.7648]]
var team4 = [[12.5040,55.7702],
             [12.5024,55.7702],
             [12.5010,55.7701],
             [12.4999,55.7700],
             [12.4989,55.7700],
             [12.4978,55.7699],
             [12.4965,55.7699]]
var i1=0;
setInterval(function(){
    var time = Date.now();
    console.log(i1);
    lon = team1[i1][0];
    lat = team1[i1][1];
    send_coordinates(1,lat,lon,time);
    if (i1 == team1.length-1){
        i1=0;
    }else{
        i1 += 1;
    }
},2000);

var i2=0;
console.log(team2.length);
setInterval(function(){
    var time = Date.now();
    lon = team2[i2][0];
    lat = team2[i2][1];
    send_coordinates(4,lat,lon,time);
    if (i2 == team2.length-1){
        i2=0;
    }else{
        i2 += 1;
    }
},2000);

var i3=0;
setInterval(function(){
    var time = Date.now();
    lon = team3[i3][0];
    lat = team3[i3][1];
    send_coordinates(3,lat,lon,time);
    if (i3 == team3.length-1){
        i3=0;
    }else{
        i3 += 1;
    }
},2000);

var i4=0;
setInterval(function(){
    var time = Date.now();
    lon = team4[i4][0];
    lat = team4[i4][1];
    send_coordinates(5,lat,lon,time);
    if (i4 == team4.length-1){
        i4=0;
    }else{
        i4 += 1;
    }
},2000);


function send_coordinates(teamID,lat,lon,time){
    var http = new XMLHttpRequest();
    var url = "postCoords.php";
    var params = "gameId=" + gameID + "&teamId=" + teamID + "&lat=" + lat + "&long=" + lon + "&timestamp=" + time;
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http.setRequestHeader("Content-length", params.length);
    http.setRequestHeader("Connection", "close");

    http.onreadystatechange = function() {//Call a function when the state changes.
	    if(http.readyState == 4 && http.status == 200) {
	    }
    }
    http.send(params);
    


        /*var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            }
        }
        xmlhttp.open("POST","postCoords.php?gameId=" + gameID + "&teamId=" + teamID + "&lat=" + lat + "&long=" + lon + "&timestamp=" + time,true);
        xmlhttp.send();*/
}
</script>
