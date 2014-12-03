<?php

require 'Mysql.php';
$mysql = new Mysql_spil();
$map_name = $mysql -> get_map($_SESSION['cg']);
if ($map_name == ''){
    die('There is no map linked to this game. Go to "import map" in order to set a map!');
}

$res = $mysql->get_half_time();
$half_time = $res[0][0];

?>

<html>
<head>
    <title>Create zones</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
    <!-- bring in the OpenLayers javascript library
         (here we bring it from the remote site, but you could
         easily serve up this javascript yourself) -->
    <script src="OpenLayers/OpenLayers.js"></script>
 
    <!-- bring in the OpenStreetMap OpenLayers layers.
         Using this hosted file will make sure we are kept up
         to date with any necessary changes -->
    <script src="Openmap.js"></script>

    <script src="init_maps.js"></script>
    <script src="get_satellite_map.js"></script>
    <script src="get_zones_and_bases.js"></script>
 
    <script type="text/javascript">
// Start position for the map (hardcoded here for simplicity)
var start_time = new Date(<?php echo time()*1000 ?>);
var lat=55.398;
var lon=10.385;
var zoom=13;
var map;
var drawControls;
var mapMinZoom = 13;
var mapMaxZoom = 17;
OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
var emptyTileURL = "http://www.maptiler.org/img/none.png";
var delete_z=0;
var delete_b=0;
var zoneLayer;
var baseLayer;
var old_resp=0;
var row_picked=0;
var base_layer=0;
var base=0;
var point_base;
//Initialise the 'map' object
function init() {
    // creates the basic map object and the tile, zone and base Layers. 
    // Sets the map center and adds a event register for change in zoom level.
    // Also listeners for handling hovering, dehovering and clicking zones as well as
    // controls for drawing and deleting zones and bases are added
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var map_info = xmlhttp.responseText.split(" ");
            lat = map_info[2];
            lon = map_info[1];
            init_overview_map(map_info[0]);
            get_zones_from_db(); // updates zone table on page initialization
            add_zones(); // add zones to map on page initialization
            add_bases();
            //get_bases_from_db();
            get_base()
			setInterval(function() {map.zoomToExtent(zoneLayer.getDataExtent());},3000);
        }
    }
    xmlhttp.open("GET","get_map_name.php",true);
    xmlhttp.send();
	setSize(50);
    startInterval(<?php echo $half_time ?>);
    update_score_table();
    update_division_score_table();
    setInterval(function(){update_score_table();},30000);
    setInterval(function(){update_division_score_table();},30000);
}

function sortFunction(a, b) {
    if (a[2] === b[2]) {
        return 0;
    }
    else {
        return (a[2] > b[2]) ? -1 : 1;
    }
}

function onlyUnique(value, index, self) { 
    return self.indexOf(value) === index;
}

function update_division_score_table(){
    remove_score("div");
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var score = xmlhttp.responseText.split(" ");
            var number_of_teams = (score.length-1)/4;
            var table = document.getElementById("div_score");
            var div = [];
            var cp = [];
            var points = [];
            for (var i=0; i<number_of_teams; i++){
                div.push(score[i*4+1]);
                cp.push(score[i*4+2]);
                points.push(score[i*4+3]);
            }
            var unique_divs = div.filter(onlyUnique);
            var res = [];
            for (var j=0; j<unique_divs.length; j++){
                cp_tot = 0;
                points_tot = 0;
                for (var i=0; i<number_of_teams; i++){
                    if (div[i] == unique_divs[j]){
                        cp_tot += parseInt(cp[i]);
                        points_tot += parseInt(points[i]);
                    }
                }
                res.push([unique_divs[j],cp_tot,points_tot]);
            }
            res.sort(sortFunction);
            for (var i=0; i<res.length; i++){
                var row = table.insertRow(-1);
                var cell0 = row.insertCell(0);
                var cell1 = row.insertCell(1);
                var cell2 = row.insertCell(2);
                var cell3 = row.insertCell(3);
                cell0.innerHTML = i + 1;
                cell1.innerHTML = res[i][0];
                cell2.innerHTML = res[i][1];
                cell3.innerHTML = res[i][2];
            }
        }
    }
    xmlhttp.open("GET","get_score.php",true);
    xmlhttp.send();
}

function remove_score(a){
    if (a == "teams"){
        var table = document.getElementById("score");
    }
    else{
        var table = document.getElementById("div_score");
    }
    var number_of_rows = table.rows.length;
    for (var i=1; i<number_of_rows; i++){
        table.deleteRow(-1);
    }
}

function update_score_table(){
    remove_score("teams");
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var score = xmlhttp.responseText.split(" ");
            var number_of_teams = (score.length-1)/4;
            var table = document.getElementById("score");
            for (var i=0; i<number_of_teams; i++){
                var row = table.insertRow(-1);
                var cell0 = row.insertCell(0);
                var cell1 = row.insertCell(1);
                var cell2 = row.insertCell(2);
                var cell3 = row.insertCell(3);
                var cell4 = row.insertCell(4);
                cell0.innerHTML = i + 1;
                cell1.innerHTML = score[i*4];
                cell2.innerHTML = score[i*4+1];
                cell3.innerHTML = score[i*4+2];
                cell4.innerHTML = score[i*4+3];
            }
        }
    }
    xmlhttp.open("GET","get_score.php",true);
    xmlhttp.send();
}


// handles the map controls 
function toggleControl(element) {
    // handle controls and set variables for determining listeners action
    for(key in drawControls) {
        // activate new control and deactivate old control
        var control = drawControls[key];
        if(element.value == key && element.checked) {
            control.activate();
        } 
        else {
            control.deactivate();
        }
    }
    // variables for use in init_maps.js
    if(element.value == 'del_zone' && element.checked) {
        delete_z=1;
    }  
    else{
        delete_z=0;
    }
    if(element.value == 'del_base' && element.checked) {
        delete_b=1;
    }  
    else{
        delete_b=0;
    }
    if(element.value == 'base' && element.checked) {
        base_layer=1;
    }  
    else{
        base_layer=0;
    }
}

function setSize(radius_in_m) {
    // sets the radius of the zones
    // corrects the distance to match the mercator projection (not exact)
    var radius = radius_in_m/Math.cos(lat*(Math.PI/180));
    //drawControls['zone'].handler.setOptions({radius: radius, angle: 0});
    //drawControls['base'].handler.setOptions({radius: radius, angle: 0});
}

function add_zone(){
    // for adding a zone by specifying GPS coordinates
    var lat_new = document.getElementById('lat').value;
    var lon_new = document.getElementById('lon').value;
    var lonLat = new OpenLayers.LonLat(lon_new,lat_new).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
    var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
    var radius_in_m = document.getElementById('size').value;
    var radius = radius_in_m/Math.cos(lat*(Math.PI/180));
    var mycircle = OpenLayers.Geometry.Polygon.createRegularPolygon(point,radius,50,0);
    var featurecircle = new OpenLayers.Feature.Vector(mycircle);
    featurecircle.style = {fillColor: "red", fillOpacity: 0.4, strokeColor:"red"};
    update_zones_in_db(parseFloat(lon_new),parseFloat(lat_new),radius_in_m,'save');
    zoneLayer.addFeatures([featurecircle]);
    get_zones_from_db();
}

function update_zones_in_db(centerX, centerY, radius, a) {
    // delete or add a zone or a base
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    if (a == 'save' || a == 'delete_zone' || a == 'saveBase' || a == 'delete_base'){
        xmlhttp.open("GET","update_zones.php?x=" + centerX.toFixed(6) + "&y=" + centerY.toFixed(6) + "&r=" + radius + '&a=' + a,true);
        xmlhttp.send();
    } 
}

function get_zones_from_db(){
    // retrieves the zones from the database. includes a recall to the function if nothing has changed.
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("zones_table").innerHTML=xmlhttp.responseText;
            if (old_resp == xmlhttp.responseText){
                get_zones_from_db();            
            }
            else{
                old_resp = xmlhttp.responseText;
            }
        }
    }
    xmlhttp.open("GET","display_zones_table.php",true);
    xmlhttp.send();
}

/*function get_bases_from_db(){
   // gets the number of bases 
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("base#").innerHTML=xmlhttp.responseText;
            if (old_resp == xmlhttp.responseText){
                get_bases_from_db();            
            }
            else{
                old_resp = xmlhttp.responseText;
            }
        }
    }
    xmlhttp.open("GET","get_number_of_bases.php",true);
    xmlhttp.send();
}*/


function submit_assign(){
    // for linking zones to assignments. Should be extended so that multiple assignments can be linked to one zone. 
    var zoneID = document.getElementById("zoneID").innerHTML.split(" ")[1];
    var assID = document.getElementById("assID").value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    xmlhttp.open("GET","update_zones.php?x=" + zoneID + "&y=" + assID + "&r=1&a=link",true);
    xmlhttp.send();
    for (var i=0; i<zoneLayer.features.length; i++){
        // change the color of the linked zone
        if (zoneLayer.features[i].attributes["ID"] == zoneID && assID != 0){
            zoneLayer.features[i].attributes["type"] = 'set';
            zoneLayer.features[i].style.fillColor = 'green';
            zoneLayer.features[i].style.strokeColor = 'green';
            zoneLayer.drawFeature(zoneLayer.features[i]);
        }   
    }
    get_zones_from_db(); // updates the table.
}

function pick_row(i){
    // for controlling style and graphics when chosing a zone in the table
    document.getElementById("row" + i).style.background = "blue";
    document.getElementById("row" + i).setAttribute("value", 'p');
    var zones_num = zoneLayer.features.length;
    for (var j=0; j<zones_num; j++){
        if(zoneLayer.features[j].attributes["ID"]==i){
            zoneLayer.features[j].style = {fillColor: "blue", fillOpacity: 0.4, strokeColor: "blue",label: zoneLayer.features[j].style.label, fontSize: 10};
            zoneLayer.drawFeature(zoneLayer.features[j]);
        }
    }
    if (row_picked != i && row_picked != 0){
        document.getElementById("row" + row_picked).style.background = "white";
        document.getElementById("row" + row_picked).setAttribute("value", 'np');
        for (var j=0; j<zones_num; j++){
            if (zoneLayer.features[j].attributes["ID"]==row_picked){
                if (zoneLayer.features[j].attributes["type"]=='notset'){
                    zoneLayer.features[j].style = {fillColor: "red", fillOpacity: 0.4, strokeColor: "red",label: zoneLayer.features[j].style.label, fontSize: 10};
                }
                else if (zoneLayer.features[j].attributes["type"]=='set'){
                    zoneLayer.features[j].style = {fillColor: "green", fillOpacity: 0.4, strokeColor: "green",label: zoneLayer.features[j].style.label, fontSize: 10};
                }
                zoneLayer.drawFeature(zoneLayer.features[j]);
            }
        }
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

function show_perim(){
    var min_dist_start = document.getElementById("min").value;
    var max_dist_start = document.getElementById("max").value;
    var lon_base = base[1];
    var lat_base = base[2];
    var lonLat = new OpenLayers.LonLat(lon_base,lat_base).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
    point_base = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
    var radius_small = min_dist_start/Math.cos(lat*(Math.PI/180));
    var radius_big = max_dist_start/Math.cos(lat*(Math.PI/180));
    var mycircle_small = OpenLayers.Geometry.Polygon.createRegularPolygon(point_base,radius_small,50,0);
    var mycircle_big = OpenLayers.Geometry.Polygon.createRegularPolygon(point_base,radius_big,50,0);
    var featurecircle_small = new OpenLayers.Feature.Vector(mycircle_small);
    var featurecircle_big = new OpenLayers.Feature.Vector(mycircle_big);
    featurecircle_small.style = {fillOpacity: 0.0, strokeColor:"black", strokeDashstyle: 'dash'};
    featurecircle_big.style = {fillOpacity: 0.0, strokeColor:"black", strokeDashstyle: 'dash'};
    featurecircle_small.attributes["type"]="perim";
    featurecircle_big.attributes["type"]="perim";
    baseLayer.removeFeatures( baseLayer.getFeaturesByAttribute("type", "perim") );
    baseLayer.addFeatures([featurecircle_small,featurecircle_big]); // add the include perimiter
    document.getElementById("hide").disabled = false;
}

function hide_perim(){
    baseLayer.removeFeatures( baseLayer.getFeaturesByAttribute("type", "perim") );
    document.getElementById("hide").disabled = true;
}

function zoomChanged(){
        // updates the zoom level display
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
}

function startInterval(game_time){  
    setInterval('updateTime(' + game_time + ');', 100);  
}

function updateTime(game_time_min){
    var game_time_ms = game_time_min * 60000;
    var nowMS = Date.now();
    var minutes = (game_time_ms - (nowMS - start_time.getTime()))/(1000*60);
    var seconds = 60*(game_time_min-Math.floor(minutes)) - (game_time_ms - minutes*60000)/1000.0;
    var clock = document.getElementById('time');
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
    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:1000px; height:600px; margin-left:auto; margin-right:auto;">
        <div style="width:60%; height:100%; float:left" id="map"></div>
        <div style="width:40%; height:100%; float:left">
            <div style="width:60%;height:10%;margin-left:auto;margin-right:auto"><h1 id="time">Time: 60:00</h1></div>
            <div style="width:70%; height:60%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="score" style="margin-left:auto; margin-right:auto;">
                    <caption>Score</caption>
                    <tr>
                        <th>rank</th>
                        <th>team</th>
                        <th>div</th>
                        <th>cp</th>
                        <th>points</th>
                </table>
            </div>
            <div style="width:70%; height:30%; margin-left:auto; margin-right:auto; overflow:auto">
                <table id="div_score" style="margin-left:auto; margin-right:auto;">
                    <caption>Division Score</caption>
                    <tr>
                        <th>rank</th>
                        <th>div</th>
                        <th>cp</th>
                        <th>points</th>
                </table>
            </div>
        </div>
        <p id="zoom"></p>
    
    
    </div>
</body>
</html>
