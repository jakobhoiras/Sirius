<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}


?>

<html>
<head>
    <title>OSM save map</title>
	<link rel="stylesheet" href="style.css" type="text/css" />
    <!-- bring in the OpenLayers javascript library
         (here we bring it from the remote site, but you could
         easily serve up this javascript yourself) -->
    <script src="OpenLayers/OpenLayers.js"></script>
 
    <!-- bring in the OpenStreetMap OpenLayers layers.
         Using this hosted file will make sure we are kept up
         to date with any necessary changes -->
    <script src="Openmap.js"></script>
 
    <script type="text/javascript">
// Start position for the map (hardcoded here for simplicity)
var lat=55.77060;
var lon=12.50575;
var zoom=13;
var map;
var maps_db;
var mapBounds = new OpenLayers.Bounds(12.3937091643, 55.7398667756, 12.6078492329, 55.8264812224);
var mapMinZoom = 13;
var mapMaxZoom = 17;
OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
var emptyTileURL = "http://www.maptiler.org/img/none.png";
var old_resp = 0;
var singlesiteLayer = 0;
//Initialise the 'map' object
function init() {
    map = new OpenLayers.Map ("map", {
                controls:[
                    new OpenLayers.Control.Navigation(),
                    new OpenLayers.Control.PanZoomBar(),
                    new OpenLayers.Control.Permalink(),
                    new OpenLayers.Control.ScaleLine({geodesic: true}),
                    new OpenLayers.Control.Permalink('permalink'),
                    new OpenLayers.Control.MousePosition(),                    
                    new OpenLayers.Control.Attribution()],
                maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
                maxResolution: 156543.0339,
                numZoomLevels: 19,
                units: 'm',
                projection: new OpenLayers.Projection("EPSG:900913"),
                displayProjection: new OpenLayers.Projection("EPSG:4326")
            } );
 
    layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
    map.addLayer(layerMapnik); 
 
    // This is the layer that uses the locally stored tiles
    /*var overviewLayer = new OpenLayers.Layer.OSM("Overview of all maps", "tiles/all13/${z}/${x}/${y}.png", {numZoomLevels: 19, alpha: true});
    map.addLayer(overviewLayer);*/
    
    singlesiteLayer = new OpenLayers.Layer.OSM("single site map", "tiles/lyngby/${z}/${x}/${y}.png", {numZoomLevels: 19, alpha: true});
    map.addLayer(singlesiteLayer);


    // This is the layer that uses the locally stored tiles
    var satelliteLayer = new OpenLayers.Layer.TMS("Satellite map", "tiles/lyngby/", {numZoomLevels: 19, alpha: true, isBaseLayer: false, layername: '.', type: 'png',serviceVersion: '.', getURL: getURL, visibility: 0});
    map.addLayer(satelliteLayer);
    if (OpenLayers.Util.alphaHack() == false) {
        satelliteLayer.setOpacity(0.7);
    }
 
    var switcherControl = new OpenLayers.Control.LayerSwitcher();
    map.addControl(switcherControl);
    switcherControl.maximizeControl();

    if( ! map.getCenter() ){
        var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
        map.setCenter (lonLat, zoom);
            
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
    }

    map.events.register("zoomend", map, zoomChanged);
    function zoomChanged(){
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
    }

    get_maps_from_db();
}

function getURL(bounds) {
    bounds = this.adjustBounds(bounds);
    var res = this.getServerResolution();
    var x = Math.round((bounds.left - this.tileOrigin.lon) / (res * this.tileSize.w));
    var y = Math.round((bounds.bottom - this.tileOrigin.lat) / (res * this.tileSize.h));
    var z = map.getZoom()
    if (this.map.baseLayer.CLASS_NAME === 'OpenLayers.Layer.Bing') {
        z+=1;
    }
    var path = this.serviceVersion + "/" + this.layername + "/" + z + "/" + x + "/" + y + "." + this.type; 
    var url = this.url;
    if (OpenLayers.Util.isArray(url)) {
        url = this.selectUrl(path, url);
    }
    if (z >= mapMinZoom && z <= mapMaxZoom) {
        return url + path;
    } 
    else {
        return emptyTileURL;
    }
} 

function Save_map(){
    var zoomLevel = map.getZoom();
    var mapCenter = map.getCenter().transform(map.projection, map.displayProjection);
    var newLon = mapCenter.lon;
    var newLat = mapCenter.lat;
    var mapID = document.getElementById("map_name").value;
    console.log(maps_db);
    maps_db.push(mapID);
    maps_db.push(newLon.toFixed(8));
    maps_db.push(newLat.toFixed(8));
    console.log(maps_db);
    document.getElementById("save_btn").disabled = true;
    var table = document.getElementById("maps");
    table.insertRow(-1).insertCell(0).innerHTML = mapID;
    add_pick_coloring(table.rows.length-1);
    document.getElementById("wait").innerHTML="please wait while the map is being downloaded. This can take a while.";
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("wait").innerHTML = xmlhttp.responseText;
            document.getElementById("save_btn").disabled = false;
            document.getElementById("map_name").value = '';
        }
    }
	xmlhttp.open("GET","autorun2.php?lon=" + newLon.toFixed(8) + '&lat=' + newLat.toFixed(8) + '&map_name=' + mapID,true);
    //xmlhttp.open("GET","autorun.php?zoomLevel=" + zoomLevel + '&lon=' + newLon.toFixed(8) + '&lat=' + newLat.toFixed(8) + '&mapID=' + mapID,true);
    xmlhttp.send();
}

function get_maps_from_db(){
    // retrieves the maps from the database. includes a recall to the function if nothing has changed.
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            display_maps_from_db(xmlhttp.responseText.split(" "));
        }
    }
    xmlhttp.open("GET","display_maps_table.php",true);
    xmlhttp.send();
}

function display_maps_from_db(maps){
    maps_db = maps;
    var table = document.getElementById("maps");
    for (var i=0; i<maps.length/3; i++){
        var row = table.insertRow(i);
        add_pick_coloring(i);
        if (maps[i*3] != 0){    
            var cell = row.insertCell(0);
            cell.innerHTML = maps[i*3];
        }
    }
}

function add_pick_coloring(i,p){
    var table = document.getElementById("maps");
    table.rows[i].onclick = function() {pick_map(table, j=i);};
}

function pick_map(table, j) {
	//j = j || "none";
    // upon cliking on a rute the coloring of the table is changed accordingly.
    // The zones belonging to the rute is colored orange on the map and finally lines are drawn.
    // If a rute was already chosen the old zones are returned to their original color
    var rows = table.rows;
    for (i=0; i<rows.length; i++){
        if (i % 2 == 0){
                	rows[i].style.background = "#fff";
				}
				else{
					rows[i].style.background = "#eee";
				}
        rows[i].setAttribute("value","np");
    }
    if (rows[j].getAttribute("value") != "edit"){
        rows[j].style.background='blue';
        rows[j].setAttribute("value","p");
        var newLon = maps_db[j*3+1];
        var newLat = maps_db[j*3+2];
        singlesiteLayer.url = "tiles/" + maps_db[j*3] + "/${z}/${x}/${y}.png";
        var lonLat = new OpenLayers.LonLat(newLon, newLat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
        map.setCenter (lonLat, zoom);
    }
}

function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }

    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
    <div style="width:100%; padding-bottom:5px;">
        <button id="back" type="button" onclick=change_page('start_admin')>Start menu</button>
        <form method="post" style="display:inline">
            <input type="submit" value="Log out" style="float:right" name="logout" /><br>
        </form>
    </div>
    <div style="width:60%; height:70%; float:left" id="map"></div>
    <div style="width:40%; height:70%; float:left; overflow:auto"> 
        <table id="maps" style="width:50%; margin-left:auto; margin-right:auto">
            <caption>Maps</caption> 
        </table>
    </div>
    <div style="width:100%; height:9%; float:left">
        <p id="zoom" style="float:left"></p>
    </div>
    <div style="width:100%; height:8%; float:left"> 
        <p style="float:left;">Map name: </p> 
        <input id="map_name" size=10 type="text" style="float:left; margin-top:12px; margin-left:8px">
        <button id="save_btn" type="button" onclick="Save_map()" style="float:left; margin-top:12px">Save map</button> 
        <p id="wait" style="float:left; margin-left:16px"></p>
    </div>
</body>
 
</html>
