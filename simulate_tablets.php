<?php

require 'Mysql.php';

$mysql = new Mysql_spil();
$start_time = time()*1000;
$res = $mysql->update_start_time_first_half($start_time);
$games = $mysql->get_games();
for ($i = 0; $i < sizeof($games); $i++) {
    if ($games[$i][1] === $_SESSION['cg']) {
        $gameID = $games[$i][0];
    }
}
?>

<script type="text/javascript">
var gameID = <?php echo $gameID; ?>;
var team1 = [[56.99905,9.76651],
             [57.00005,9.76651],
             [57.00105,9.76651],
             [57.00205,9.76651],
             [57.00305,9.76651],
             [57.00405,9.76651],
             [57.00505,9.76651],
             [57.00605,9.76651]]
var team2 = [[56.99905,9.76651],
             [57.00005,9.76751],
             [57.00105,9.76851],
             [57.00205,9.76951],
             [57.00305,9.77051],
             [57.00405,9.77151],
             [57.00505,9.77251],
             [57.00605,9.77351]]
var team3 = [[56.99905,9.76651],
             [57.00005,9.76551],
             [57.00105,9.76451],
             [57.00205,9.76351],
             [57.00305,9.76251],
             [57.00405,9.76151],
             [57.00505,9.76051],
             [57.00605,9.75951]]
var team4 = [[56.99905,9.76651],
             [56.99805,9.76651],
             [56.99705,9.76651],
             [56.99605,9.76651],
             [56.99505,9.76651],
             [56.99405,9.76651],
             [56.99305,9.76651],
             [56.99205,9.76651]]
var i1=0;
setInterval(function(){
    var time = Date.now();
    console.log(i1);
    lat = team1[i1][0];
    lon = team1[i1][1];
    send_coordinates(1,lat,lon,time);
    if (i1 == team1.length-1){
        i1=0;
    }else{
        i1 += 1;
    }
},10000);

var i2=0;
console.log(team2.length);
setInterval(function(){
    var time = Date.now();
    lat = team2[i2][0];
    lon = team2[i2][1];
    send_coordinates(2,lat,lon,time);
    if (i2 == team2.length-1){
        i2=0;
    }else{
        i2 += 1;
    }
},10000);

var i3=0;
setInterval(function(){
    var time = Date.now();
    lat = team3[i3][0];
    lon = team3[i3][1];
    send_coordinates(3,lat,lon,time);
    if (i3 == team3.length-1){
        i3=0;
    }else{
        i3 += 1;
    }
},10000);

var i4=0;
setInterval(function(){
    var time = Date.now();
    lat = team4[i4][0];
    lon = team4[i4][1];
    send_coordinates(4,lat,lon,time);
    if (i4 == team4.length-1){
        i4=0;
    }else{
        i4 += 1;
    }
},10000);


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
