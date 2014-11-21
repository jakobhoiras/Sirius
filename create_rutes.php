<?php

require 'Mysql.php';
$mysql = new Mysql_spil();
$map_name = $mysql -> get_map($_SESSION['cg']);
if ($map_name == ''){
    die('There is no map linked to this game. Go to "import map" in order to set a map!');
}
?>

<html>
<head>
    <title>OSM Local Tiles</title>
    <!--link rel="stylesheet" href="style.css" type="text/css" /-->
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
//Initialise the map and add zones and bases from the database
function init() {
    // creates the basic map object and the tile, zone and base Layers. 
    // Sets the map center and adds a event register for change in zoom level
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var map_info = xmlhttp.responseText.split(" ");
            lat = map_info[2];
            lon = map_info[1];
            init_rutes_map(map_info[0]);
            add_zones(); // add zones to map on page initialization
            add_bases(); // add bases to map on page initialization
            get_base(); // writes the base information to a global variable
            get_rutes_from_db();
        }
    }
    xmlhttp.open("GET","get_map_name.php",true);
    xmlhttp.send();
      

    add_zones(); // add zones to map on page initialization
    add_bases(); // add bases to map on page initialization
    get_base(); // writes the base information to a global variable
    get_rutes_from_db();
}

function zoomChanged(){
        // updates the zoom level display
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
    }


function suggest_rutes(num_zones, min_dist, max_dist, min_dist_start, max_dist_start){
    // suggests all possible rutes based on number of zones and min and max distance between each zone
    var zones;
    var rutes = [];
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var zones_num = zones.length;
            for (var rute_point=0; rute_point<num_zones; rute_point++){
                rutes_new = [];
                if (rute_point == 0){
                    // find the first zone
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
                    for (var zone=0; zone<(zones_num-1)/4; zone++){                    
                        var lon_zone = zones[zone*4+1];
                        var lat_zone = zones[zone*4+2];
                        var lonLat2 = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                        var point2 = new OpenLayers.Geometry.Point(lonLat2.lon, lonLat2.lat);
                        if (point_base.distanceTo(point2)*Math.cos(lat*(Math.PI/180)) < max_dist_start && point_base.distanceTo(point2)*Math.cos(lat*(Math.PI/180)) > min_dist_start){
                            rutes.push([[zones[zone*4],point2]]); //add zoneto rute if zone is with specified distance
                        }
                    }
                }
                else{
                    var rutes_num = rutes.length;
                    for (var zone_count=0; zone_count<rutes_num; zone_count++){
                        for (var zone=0; zone<(zones_num-1)/4; zone++){
                            var old_rute=0;
                            var lon_zone = zones[zone*4+1];
                            var lat_zone = zones[zone*4+2];
                            var lonLat = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                            var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                            if (rute_point != num_zones-1){
                                if (rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) < max_dist && rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) > min_dist){
                                    not_in_rute=true;
                                    for (var i=0; i<rutes[zone_count].length;i++){
                                        if (rutes[zone_count][i][0] == zones[zone*4]){
                                            not_in_rute=false;
                                        }
                                    }
                                    if (not_in_rute == true){
                                        old_rute = rutes[zone_count];
                                        new_rute = old_rute.concat([[zones[zone*4],point]]);
                                        rutes_new.push(new_rute);
                                    }
                                }
                            }
                            else if (rute_point == num_zones-1){
                                if (rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) < max_dist && rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) > min_dist){
                                    if (point_base.distanceTo(point)*Math.cos(lat*(Math.PI/180)) < max_dist && point_base.distanceTo(point)*Math.cos(lat*(Math.PI/180)) > min_dist){
                                        not_in_rute=true;
                                        for (var i=0; i<rutes[zone_count].length;i++){
                                            if (rutes[zone_count][i][0] == zones[zone*4]){
                                                not_in_rute=false;
                                            }
                                        }
                                        if (not_in_rute == true){
                                            old_rute = rutes[zone_count];
                                            new_rute = old_rute.concat([[zones[zone*4],point]]);
                                            rutes_new.push(new_rute);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    rutes = rutes_new;
                }
            }
         display_rutes(rutes);      
        }
    }
    xmlhttp.open("GET","get_zones.php",true);
    xmlhttp.send();
}

function display_rutes(rutes){
    remove_old_content();
    var table = document.getElementById("suggest_rutes");
    for (var i=0; i<rutes.length; i++){
        var row = table.insertRow(i);
        add_pick_coloring(i, 'su');
        for (var j=0; j<rutes[i].length; j++){    
            var cell = row.insertCell(j);
            cell.innerHTML = rutes[i][j][0];
        }
    }
}


function add_pick_coloring(i,p){
    if (p == 'ch'){
        var table = document.getElementById("chosen_rutes");
    }
    else if (p == 'su'){
        var table = document.getElementById("suggest_rutes");
    }
    table.rows[i].onclick = function() {pick_rute(table, j=i)};
}

function get_rutes_from_db(){
    // retrieves the zones from the database. includes a recall to the function if nothing has changed.
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            display_rutes_from_db(xmlhttp.responseText.split(" "));            
        }
    }
    xmlhttp.open("GET","display_rutes_table.php",true);
    xmlhttp.send();
}

function display_rutes_from_db(rutes){
    var table = document.getElementById("chosen_rutes");
    for (var i=0; i<rutes.length/5-1; i++){
        var row = table.insertRow(i);
        add_pick_coloring(i,'ch');
        for (var k=0; k<5; k++){
            if (rutes[i*5+k] != 0){    
                var cell = row.insertCell(k);
                cell.innerHTML = rutes[i*5+k];
            }
        }
    }
}

function move_to_chosen_rutes(){
    var table = document.getElementById("suggest_rutes");
    var table2 = document.getElementById("chosen_rutes");
    var len = table.rows.length;
    for (var i=0; i<len; i++){
        if (table.rows[i].getAttribute("value") == "p"){
            var row = table2.insertRow(-1);
            var rute=[];
            for (var j=0; j<table.rows[i].cells.length; j++){
                row.insertCell(-1).innerHTML = table.rows[i].cells[j].innerHTML;
                rute.push(table.rows[i].cells[j].innerHTML);
            }
            table.deleteRow(i);
            var len = table2.rows.length-1;
            row.onclick = function (){pick_rute(table2, j=len)};
            for (j=0; j<table.rows.length; j++){
                add_pick_coloring(j,'su');
            }
            add_rute_to_db(rute,'save');
            return true;
        }
    }
}

function pick_rute(table, j='none') {
        // upon cliking on a rute the coloring of the table is changed accordingly.
        // The zones belonging to the rute is colored orange on the map and finally lines are drawn.
        // If a rute was already chosen the old zones are returned to their original color
        var rows = table.rows;
        var rows_ch = document.getElementById("chosen_rutes").rows;
        var rows_su = document.getElementById("suggest_rutes").rows;
        for (i=0; i<rows_ch.length; i++){
            if(rows_ch[i].getAttribute("value") != "edit"){
                rows_ch[i].style.background = "white";
                rows_ch[i].setAttribute("value","np");
            }
        }
        for (i=0; i<rows_su.length; i++){
            if(rows_su[i].getAttribute("value") != "edit"){
                rows_su[i].style.background = "white";
                rows_su[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "edit"){
            rows[j].style.background='blue';
            rows[j].setAttribute("value","p");
        }
        for (var i=0; i<zoneLayer.features.length-1; i++){
            if (zoneLayer.features[i].style.fillColor == "orange"){
                if (zoneLayer.features[i].attributes["type"] == "notset"){
                    zoneLayer.features[i].style.fillColor="red";
                    zoneLayer.features[i].style.strokeColor="red";
                    zoneLayer.drawFeature(zoneLayer.features[i]);
                }
                else if (zoneLayer.features[i].attributes["type"] == "set"){
                    zoneLayer.features[i].style.fillColor="green";
                    zoneLayer.features[i].style.strokeColor="green";
                    zoneLayer.drawFeature(zoneLayer.features[i]);
                }
            }
        }
        for (var i=0; i<rows[j].cells.length; i++){
            var zone = zoneLayer.getFeaturesByAttribute("ID", rows[j].cells[i].innerHTML);
            zone[0].style.fillColor = "orange";
            zone[0].style.strokeColor = "orange";
            zone[0].layer.drawFeature(zone[0]);
        }
        draw_lines(rows[j]);
    }

function remove_old_content(table){
    var table = document.getElementById("suggest_rutes");
    var len = table.rows.length;
    for (var i=0; i<len; i++){
        table.deleteRow(0);
    }
}

function remove_lines(){
    // remove the lines connecting the zones of a given rute. Lines are drawn on the zoneLayer.
    for (var i=0; i<zoneLayer.features.length; i++){
        if (zoneLayer.features[i].attributes["type"] == "lines"){
            zoneLayer.removeFeatures(zoneLayer.features[i]);
        }
    }
}

function draw_lines(row){
    // draw the lines connecting the zones of a chosen rute. Lines are drawn on the zoneLayer.
    remove_lines(); // remove the old ones
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var points = []; // for carrying the points that are to be connected by lines
            var lon_base = base[1];
            var lat_base = base[2];
            var lonLat = new OpenLayers.LonLat(lon_base,lat_base).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
            point_base = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
            points.push(point_base);
            for (var j=0; j<row.cells.length; j++){
                // find the points belonging to the chosen rute.
                for (var zone=0; zone<zones.length; zone++){
                    if (zones[4*zone]==row.cells[j].innerHTML && row.cells[j].innerHTML != ""){
                        var lon_zone = zones[zone*4+1];
                        var lat_zone = zones[zone*4+2];
                        var lonLat = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                        var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                        points.push(point);
                     }
                }
            }
            points.push(point_base);
            var feature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(points)); // the actual lines feature (all lines are drawn as one object)
            feature.attributes["type"] = "lines";
            zoneLayer.addFeatures([feature]);
            show_dist(row, points); // updates the distance info of the chosen rute
        }
    }
    xmlhttp.open("GET","get_zones.php",true);
    xmlhttp.send();
}


function add_manual(){
    // function for manually adding a rute by specifying a set of zone ids. No validity checks are done.
    var zone1 = document.getElementById("add_zone1").value; // get the value of the entry
    var zone2 = document.getElementById("add_zone2").value;
    var zone3 = document.getElementById("add_zone3").value;
    var zone4 = document.getElementById("add_zone4").value;
    var zone5 = document.getElementById("add_zone5").value;
    zone_array = [zone1,zone2,zone3,zone4,zone5];
    var table = document.getElementById("chosen_rutes"); // get the table object from the UI
    var row = table.insertRow(-1); // add a new row at the end
    var len = table.rows.length-1;
    row.onclick = function() {pick_rute(table, j=len)};
    var rute = [];
    for (var i=0; i<5; i++){
        // puts the values into the table
        if (zone_array[i] != ''){
            var cell = row.insertCell(i);
            cell.innerHTML = zone_array[i];
            rute.push(zone_array[i]);
        }
    }
    add_rute_to_db(rute,'save');
    document.getElementById("add_zone1").value = ""; // reset the entry values to blank
    document.getElementById("add_zone2").value = "";
    document.getElementById("add_zone3").value = "";
    document.getElementById("add_zone4").value = "";
    document.getElementById("add_zone5").value = "";
}
 
function add_rute_to_db(rute, a) {
    // delete or add a zone or a base
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    if (a == 'save' || a == 'delete_rute'){
        xmlhttp.open("GET","update_rutes.php?rute=" + rute + '&a=' + a,true);
        xmlhttp.send();
    } 
}

function show_dist(rute, points){
    // calculates the distance between each zone (and total distance) and puts the result in a table
    table = document.getElementById("dist"); // get the table
    table.deleteTHead(0); // delete the old stuff
    if (table.rows.length > 0){
        table.deleteRow(0);
    }  
    var header = table.createTHead(); // create the new stuff
    var row = header.insertRow(0); // header row
    var row2 = table.insertRow(-1); // distance row
    for (var i=0; i<rute.cells.length; i++){
        if (i==0){
            // the distance from base to the first zone
            var cell = row.insertCell(-1).innerHTML = 'base - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
        else if(i==rute.cells.length-1){
            // distance between the second last and the last zone
            row.insertCell(-1).innerHTML = rute.cells[i-1].innerHTML + ' - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
            // distance between the last zone and the base
            row.insertCell(-1).innerHTML = rute.cells[i].innerHTML + ' - base';
            var dist = points[i+1].distanceTo(points[i+2]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
            // the total distance
            row.insertCell(-1).innerHTML = 'total distance';
            var dist=0;
            for (var i=0; i<points.length-1; i++){
                dist += points[i].distanceTo(points[i+1]);
            }
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
        else{
            // the distance between the middle zones
            row.insertCell(-1).innerHTML = rute.cells[i-1].innerHTML + ' - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
    }
}

function delete_chosen(){
    // removes a rute from the list (table)
    var table = document.getElementById("chosen_rutes");
    var rute = [];
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "p"){
            for(var j=0; j<table.rows[i].cells.length; j++){
                rute.push(table.rows[i].cells[j].innerHTML);
            }
            for (var j=0; j<5-table.rows[i].cells.length; j++){
                rute.push(0);
            }
            add_rute_to_db(rute, 'delete_rute');
            table.deleteRow(i);
        }
    }
}

function edit_chosen(){
    // for chosing a rute to edit. 
    var table = document.getElementById("chosen_rutes");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "edit"){
            table.rows[i].style.background = "white";
            table.rows[i].setAttribute("value","np");
        }
        if (table.rows[i].getAttribute("value") == "p"){
            for (j=0; j<table.rows[i].cells.length; j++){
                document.getElementById("edit_zone" + (j+1)).value = table.rows[i].cells[j].innerHTML;
            }
            table.rows[i].style.background = "green";
            table.rows[i].setAttribute("value","edit");
        }
    }
}

function save_edit(){
    // for re-saving a rute after edit 
    var zone1 = document.getElementById("edit_zone1").value;
    var zone2 = document.getElementById("edit_zone2").value;
    var zone3 = document.getElementById("edit_zone3").value;
    var zone4 = document.getElementById("edit_zone4").value;
    var zone5 = document.getElementById("edit_zone5").value;
    zone_array = [zone1,zone2,zone3,zone4,zone5];
    old_zone_array = [];
    var table = document.getElementById("chosen_rutes");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "edit"){
            for (j=0; j<5; j++){
                if (zone_array[j] != ''){
                    old_zone_array.push(table.rows[i].cells[j].innerHTML);
                    table.rows[i].cells[j].innerHTML = zone_array[j];
                }
            }
            table.rows[i].setAttribute("value","np");
            table.rows[i].style.background = "white";
        }
    }
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    a='edit'
    xmlhttp.open("GET","update_rutes.php?rute=" + zone_array + "&old_rute=" + old_zone_array + '&a=' + a,true);
    xmlhttp.send();
    document.getElementById("edit_zone1").value = "";
    document.getElementById("edit_zone2").value = "";
    document.getElementById("edit_zone3").value = "";
    document.getElementById("edit_zone4").value = "";
    document.getElementById("edit_zone5").value = "";
}

function get_suggest_rutes_param(){
    var zones_number = document.getElementById("#zones").value;
    var min_d = document.getElementById("min").value;
    var max_d = document.getElementById("max").value;
    suggest_rutes(zones_number,min_d,max_d,min_d,max_d);
}

    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <div style="width:21%; height:85%; float:left; overflow:auto">
        <table id="suggest_rutes">
            <caption>suggested rutes</caption> 
        </table>
        <button type="button" onclick="get_suggest_rutes_param()">suggest rutes</button>
        <button type="button" onclick="move_to_chosen_rutes()">----></button>
        <table id=suggest_param style="text-align:center">
            <tr>
                <td># zones</td>
                <td>min</td>
                <td>max</td>
            </tr>
            <tr>
                <td><input type="text" name="#zones" id="#zones" size="1"></td>
                <td><input type="text" name="min" id="min" size="1"></td>
                <td><input type="text" name="min" id="max" size="1"></td>
            </tr>
        </table>
    </div>

    <div style="width:57%; height:85%; float:left; margin-left:5px">
    <div style="width:100%; height:90%; float:left" id="map">
    </div>
        <p id="zoom" style="float:left"></p>
        <table id="dist" style="float:right; border:1px solid black; text-align:center"></table>
    </div>

    <div style="width:20%; height:85%; float:left; overflow:auto"> 
        <table id="chosen_rutes">
            <caption>Chosen rutes</caption> 
        </table>
        <button type="button" onclick="delete_chosen()">delete</button>
        <button type="button" onclick="edit_chosen()">edit</button>
    </div>


    <div style="width:50%; height:15%; float:left">
        
        <p style="text-decoration:underline">Add manually</p>
        <input type="text" name="add_zone1" id="add_zone1" size="1">
        <input type="text" name="add_zone2" id="add_zone2" size="1">
        <input type="text" name="add_zone3" id="add_zone3" size="1">
        <input type="text" name="add_zone4" id="add_zone4" size="1">
        <input type="text" name="add_zone5" id="add_zone5" size="1">
        <button type="button" onclick="add_manual()">add</button>
    </div>
    <div style="width:50%; height:15%; float:left">
        <p style="text-decoration:underline">Edit</p>
        <input type="text" name="edit_zone1" id="edit_zone1" size="1">
        <input type="text" name="edit_zone2" id="edit_zone2" size="1">
        <input type="text" name="edit_zone3" id="edit_zone3" size="1">
        <input type="text" name="edit_zone4" id="edit_zone4" size="1">
        <input type="text" name="edit_zone5" id="edit_zone5" size="1">
        <button type="button" onclick="save_edit()">save</button>
    </div>
    
        
</body>
</html>
