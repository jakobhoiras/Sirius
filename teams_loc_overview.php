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
            init_zone_map(map_info[0]);
            get_zones_from_db(); // updates zone table on page initialization
            add_zones(); // add zones to map on page initialization
            add_bases();
            //get_bases_from_db();
            get_base()
			setTimeout(function() {map.zoomToExtent(zoneLayer.getDataExtent());},1250);
        }
    }
    xmlhttp.open("GET","get_map_name.php",true);
    xmlhttp.send();
	setSize(50);
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
    drawControls['zone'].handler.setOptions({radius: radius, angle: 0});
    drawControls['base'].handler.setOptions({radius: radius, angle: 0});
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
    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:1000px; height:700px; margin-left:auto; margin-right:auto;">
    <div style="width:60%; height:70%; float:left" id="map"></div>
    <div style="width:40%; height:70%; float:left">
        <div id="zones_table" style="width:70%; height:100%; margin-left:auto; margin-right:auto; overflow:auto"></div>
    </div>
    <div style="width:20%; height:40%; float:left;">
        <p id="zoom"></p> 
        <!--p id="demo">test</p-->
        <ul id="controlToggle">
            <li>
                <input type="radio" name="type" value="none" id="noneToggle"
                       onclick="toggleControl(this)" checked="checked" />
                <label for="noneToggle">navigate</label>
            </li>
            <li>
                <input type="radio" name="type" value="zone" id="zoneToggle" onclick="toggleControl(this)" />
                <label for="zoneToggle">add zone</label>
            </li>
            <li>
                <input type="radio" name="type" value="base" id="baseToggle" onclick="toggleControl(this);" />
                <label for="baseToggle">add base</label>
            </li>
            <li>
                <input type="radio" name="type" value="del_zone" id="delzToggle" onclick="toggleControl(this);" />
                <label for="delzToggle">delete zone</label>
            </li>
            <li>
                <input type="radio" name="type" value="del_base" id="delbToggle" onclick="toggleControl(this);" />
                <label for="delbToggle">delete base</label>
            </li>
        </ul>
        </div>
        <div style="width:30%; height:40%; float:left; ">
        <p style="float:left">radius of zone or base:<p>
        <select name="size" onchange="setSize(parseFloat(this.value))" id="size" style="float:left">
            <option value="10">10m</option>
            <option value="20">20m</option>
            <option value="30">30m</option>
            <option value="40">40m</option>
            <option value="50" selected="selected">50m</option>
            <option value="100">100m</option>
            <option value="200">200m</option>
            <option value="300">300m</option>
        </select>
        <br/>
        <p style="float:left">add zone with GPS coord.:</p>
        <br/>
        <p style="position:absolute; margin-top:25px;">lat:</p><input type="text" name="lat" id="lat" size="3" style="position:absolute; margin-top:25px; margin-left:-180px">
        <br/>        
        <p style="position:absolute; margin-top:45px;">lon:</p><input type="text" name="lon" id="lon" size="3" style="position:absolute; margin-top:45px; margin-left:-180px">
        <br/>
        <button type="button" onclick="add_zone()" style="position:absolute; margin-left:150px;">submit</button>
    </div>
    <div style="width:30%; height:30%; float:left; background:grey">
        <div style="width:100%; height:10%;"> 
            <p style="text-align:center">Show Perimeter</p>
        </div>
        <div style="width:80%; height:40%; margin-left:auto; margin-right:auto; ">
            <p style="text-align:center">
                <label for="min">min:</label>
                <input type="text" name="min" id="min" size="3" style="">
                
            </p>
            <p style="text-align:center">
                <label for="min">max:</label>
                <input type="text" name="max" id="max" size="3" style="">
            </p>
            <p style="text-align:center">
                <button type="button" onclick="show_perim()" style="">show</button>
                <button type="button" onclick="hide_perim()" id="hide" disabled>hide</button>
            </p>
        </div>  
    </div>
    </div>
</body>
</html>
