<?php

?>

<html>
<head>
    <title>OSM Local Tiles</title>
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
    // the basic map object
    map = new OpenLayers.Map ("map", {
        controls:[
            new OpenLayers.Control.Navigation(),
            new OpenLayers.Control.PanZoomBar(),
            new OpenLayers.Control.Permalink(),
            new OpenLayers.Control.ScaleLine({geodesic: true}),
            new OpenLayers.Control.Permalink('permalink'),
            new OpenLayers.Control.MousePosition(),                    
            new OpenLayers.Control.Attribution()
        ],
        maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
        maxResolution: 156543.0339,
        numZoomLevels: 19,
        units: 'm',
        projection: new OpenLayers.Projection("EPSG:900913"),
        displayProjection: new OpenLayers.Projection("EPSG:4326"),
    });
    // zonelayer functionality

    // adds the layers for points marking zones and base
    zoneLayer = new OpenLayers.Layer.Vector("Zone Layer");
    map.addLayers([zoneLayer]);
    baseLayer = new OpenLayers.Layer.Vector("Base Layer");
    map.addLayers([baseLayer]);
    // adds the map layer from mapnik
    var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
    map.addLayer(layerMapnik);
            
    // This is the layer that uses the locally stored OSM tiles 
    var newLayer = new OpenLayers.Layer.OSM("Local tiles", "tiles/odense/${z}/${x}/${y}.png", {numZoomLevels: 19, alpha: true});
    map.addLayer(newLayer);

    // This is the layer that uses the locally stored satellite tiles
    var newLayer2 = new OpenLayers.Layer.TMS("Local satellite tiles", "tiles/lyngby/", {numZoomLevels: 19, alpha: true, isBaseLayer: false, layername: '.', type: 'png',serviceVersion: '.', getURL: getURL,visibility: 0});
    map.addLayer(newLayer2);
    if (OpenLayers.Util.alphaHack() == false) {
        newLayer2.setOpacity(0.7);
    }
    // initialize the handler for drawing the zones and the base
    
    // moves the map to the correct position
    if( ! map.getCenter() ){
        var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
        map.setCenter (lonLat, zoom);
        // for displaying the current zoom level
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
    }
    // registers when zoom level is changed and updates zoom display
    map.events.register("zoomend", map, zoomChanged);
    function zoomChanged(){
        var zoomLevel = map.getZoom();
        document.getElementById("zoom").innerHTML = 'Zoom Level: ' + zoomLevel;
    }

    add_zones(); // add zones to map on page initialization
    add_bases();
    get_base();
    //rutes(4, 880, 1000, 880, 1000);
}

// picks the tiles for the satelitte map
function getURL(bounds) {
    bounds = this.adjustBounds(bounds);
    var res = this.getServerResolution();
    var x = Math.round((bounds.left - this.tileOrigin.lon) / (res * this.tileSize.w));
    var y = Math.round((bounds.bottom - this.tileOrigin.lat) / (res * this.tileSize.h));
    var z = map.getZoom();
    var path = this.serviceVersion + "/" + this.layername + "/" + z + "/" + x + "/" + y + "." + this.type; 
    var url = this.url;
    if (OpenLayers.Util.isArray(url)) {
        url = this.selectUrl(path, url);
    }
    if (z >= mapMinZoom && z <= mapMaxZoom) {
        document.getElementById("demo").innerHTML = x;
        return url + path;
    } 
    else {
        return emptyTileURL;
    }
} 
// handles the map controls 
function toggleControl(element) {
    for(key in drawControls) {
        var control = drawControls[key];
        if(element.value == key && element.checked) {
            control.activate();
        } 
        else {
            control.deactivate();
        }
    }
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

function get_base(){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            base = xmlhttp.responseText.split(" ");         
        }
    }
    xmlhttp.open("GET","get_bases.php",true);
    xmlhttp.send(); 
}
// sets the radius of the zones
// corrects the distance to match the mercator projection (not exact)
// for (re)adding all zones in the database to the map
function add_zones(){
    zoneLayer.removeAllFeatures();
    var zones;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var zones_num = zones.length;
            for (var i=0; i<(zones_num-1)/5; i++){
                var lon_new = zones[i*5+1];
                var lat_new = zones[i*5+2];
                var radius_in_m = zones[i*5+3];
                var lonLat = new OpenLayers.LonLat(lon_new,lat_new).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                var radius = radius_in_m/Math.cos(lat*(Math.PI/180));
                var mycircle = OpenLayers.Geometry.Polygon.createRegularPolygon(point,radius,50,0);
                var featurecircle = new OpenLayers.Feature.Vector(mycircle);
                if (zones[i*5+4] == 0){
                    featurecircle.style = {fillColor: "red", fillOpacity: 0.4, strokeColor:"red",label: zones[i*5], fontSize: 10};
                    featurecircle.attributes["type"]="notset";
                    }
                else{
                    featurecircle.style = {fillColor: "green", fillOpacity: 0.4, strokeColor:"green",label: zones[i*5], fontSize: 10};
                    featurecircle.attributes["type"]="set";
                }
                featurecircle.attributes["ID"]=zones[i*5];
                zoneLayer.addFeatures([featurecircle]);
            }        
        }
    }
    xmlhttp.open("GET","get_zones.php",true);
    xmlhttp.send();
}

function add_bases(){
    baseLayer.removeAllFeatures();
    var bases;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            bases = xmlhttp.responseText.split(" ");           
            var bases_num = bases.length;
            for (var i=0; i<(bases_num-1)/4; i++){ 
                var lon_new = bases[i*4+1];
                var lat_new = bases[i*4+2];
                var radius_in_m = bases[i*4+3];
                var lonLat = new OpenLayers.LonLat(lon_new,lat_new).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                var radius = radius_in_m/Math.cos(lat*(Math.PI/180));
                var mycircle = OpenLayers.Geometry.Polygon.createRegularPolygon(point,radius,4,0);
                var featurecircle = new OpenLayers.Feature.Vector(mycircle);
                featurecircle.style = {fillColor: "red", fillOpacity: 0.4, strokeColor:"red"};
                featurecircle.attributes["type"]="notset";
                baseLayer.addFeatures([featurecircle]); 
            }        
        }
    }
    xmlhttp.open("GET","get_bases.php",true);
    xmlhttp.send();
}

/*
function rutes(num_zones, min_dist, max_dist, min_dist_start, max_dist_start){
    console.log('inside');
    var zones;
    document.getElementById("zoom").innerHTML='rutes';
    var rutes = [];
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var zones_num = zones.length;
            for (var rute_point=0; rute_point<num_zones; rute_point++){
                rutes_new = [];
                if (rute_point == 0){
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
                    baseLayer.addFeatures([featurecircle_small,featurecircle_big]); 
                    for (var zone=0; zone<(zones_num-1)/5; zone++){                    
                        var lon_zone = zones[zone*5+1];
                        var lat_zone = zones[zone*5+2];
                        var lonLat2 = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                        var point2 = new OpenLayers.Geometry.Point(lonLat2.lon, lonLat2.lat);
                        if (point_base.distanceTo(point2)*Math.cos(lat*(Math.PI/180)) < max_dist_start && point_base.distanceTo(point2)*Math.cos(lat*(Math.PI/180)) > min_dist_start){
                            rutes.push([[zones[zone*5],point2]]);
                        }
                    }
                }
                else{
                    var rutes_num = rutes.length;
                    for (var zone_count=0; zone_count<rutes_num; zone_count++){
                        for (var zone=0; zone<(zones_num-1)/5; zone++){
                            var old_rute=0;
                            var lon_zone = zones[zone*5+1];
                            var lat_zone = zones[zone*5+2];
                            var lonLat = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                            var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                            if (rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) < max_dist && rutes[zone_count][rutes[zone_count].length-1][1].distanceTo(point)*Math.cos(lat*(Math.PI/180)) > min_dist){
                                not_in_rute=true;
                                for (var i=0; i<rutes[zone_count].length;i++){
                                    if (rutes[zone_count][i][0] == zones[zone*5]){
                                        not_in_rute=false;
                                    }
                                }
                                if (not_in_rute == true){
                                    old_rute = rutes[zone_count];
                                    new_rute = old_rute.concat([[zones[zone*5],point]]);
                                    rutes_new.push(new_rute);
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
    console.log(rutes);
    var table = document.getElementById("table");
    var header = table.createTHead();
    for (var i=0; i<rutes.length; i++){
        var row = header.insertRow(i);
        var dist = point_base.distanceTo(rutes[i][0][1]);
        for (var j=0; j<rutes[i].length; j++){    
            var cell = row.insertCell(j);
            cell.innerHTML = rutes[i][j][0];
            if (j != 0){
                dist += rutes[i][j-1][1].distanceTo(rutes[i][j][1]);
            }
            if (j == rutes[i].length-1){
                var cell = row.insertCell(j+1);
                cell.innerHTML = (dist*Math.cos(lat*(Math.PI/180))).toFixed(0);
            }
        }
    }
}

function add_rute(){
    //var header = table.createTHead();
    var table = document.getElementById("table");
    var row = table.insertRow(-1);
    for (var i=0; i<4; i++){
        var cell = row.insertCell(0);
        cell.innerHTML = 'not set';
    }
}

function add_zone_to_rute(){
    var ZoneID = document.getElementById("addToRute").value;
    var table = document.getElementById("table");
    for (var i = 0; i<table.rows.length; i++){
        for (var j = 0; j<table.rows[i].cells.length; j++){
            if (table.rows[i].cells[j].innerHTML == 'not set'){
                table.rows[i].cells[j].innerHTML = ZoneID;
                return true;
            }
        }
    }
}
*/

function remove_lines(){
    for (var i=0; i<zoneLayer.features.length; i++){
        if (zoneLayer.features[i].attributes["type"] == "lines"){
            zoneLayer.removeFeatures(zoneLayer.features[i]);
        }
    }
}

function draw_lines(row){
    remove_lines();
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var points = [];
            var lon_base = base[1];
            var lat_base = base[2];
            var lonLat = new OpenLayers.LonLat(lon_base,lat_base).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
            point_base = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
            points.push(point_base);
            for (var j=0; j<row.cells.length; j++){
                for (var zone=0; zone<zones.length; zone++){
                    if (zones[5*zone]==row.cells[j].innerHTML && row.cells[j].innerHTML != ""){
                        var lon_zone = zones[zone*5+1];
                        var lat_zone = zones[zone*5+2];
                        var lonLat = new OpenLayers.LonLat(lon_zone,lat_zone).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                        var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                        points.push(point);
                     }
                }
            }
            points.push(point_base);
            var feature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(points));
            feature.attributes["type"] = "lines";
            zoneLayer.addFeatures([feature]);
            show_dist(row, points);
        }
    }
    xmlhttp.open("GET","get_zones.php",true);
    xmlhttp.send();
}


function add_manual(){
    var zone1 = document.getElementById("add_zone1").value;
    var zone2 = document.getElementById("add_zone2").value;
    var zone3 = document.getElementById("add_zone3").value;
    var zone4 = document.getElementById("add_zone4").value;
    var zone5 = document.getElementById("add_zone5").value;
    zone_array = [zone1,zone2,zone3,zone4,zone5];
    var table = document.getElementById("chosen_rutes");
    var row = table.insertRow(-1);
    row.onclick = function() {
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") != "edit"){
                rows[i].style.background = "white";
                rows[i].setAttribute("value","np");
            }
        }
        if (row.getAttribute("value") != "edit"){
            row.style.background='blue';
            row.setAttribute("value","p");
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
        for (var i=0; i<row.cells.length; i++){
            var zone = zoneLayer.getFeaturesByAttribute("ID", row.cells[i].innerHTML);
            zone[0].style.fillColor = "orange";
            zone[0].style.strokeColor = "orange";
            zone[0].layer.drawFeature(zone[0]);
        }
        draw_lines(row);
    };
    for (var i=0; i<5; i++){
        if (zone_array[i] != ''){
            var cell = row.insertCell(i);
            cell.innerHTML = zone_array[i];      
        }
    }
    document.getElementById("add_zone1").value = "";
    document.getElementById("add_zone2").value = "";
    document.getElementById("add_zone3").value = "";
    document.getElementById("add_zone4").value = "";
    document.getElementById("add_zone5").value = "";
}
 
function show_dist(rute, points){
    table = document.getElementById("dist");
    table.deleteTHead(0);
    if (table.rows.length > 0){
        table.deleteRow(0);
    }  
    var header = table.createTHead();
    var row = header.insertRow(0);
    var row2 = table.insertRow(-1);
    for (var i=0; i<rute.cells.length; i++){
        if (i==0){
            var cell = row.insertCell(-1).innerHTML = 'base - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
        else if(i==rute.cells.length-1){
            row.insertCell(-1).innerHTML = rute.cells[i-1].innerHTML + ' - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
            row.insertCell(-1).innerHTML = rute.cells[i].innerHTML + ' - base';
            var dist = points[i+1].distanceTo(points[i+2]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
            row.insertCell(-1).innerHTML = 'total distance';
            var dist=0;
            for (var i=0; i<points.length-1; i++){
                dist += points[i].distanceTo(points[i+1]);
            }
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
        else{
            row.insertCell(-1).innerHTML = rute.cells[i-1].innerHTML + ' - ' + rute.cells[i].innerHTML;
            var dist = points[i].distanceTo(points[i+1]);
            row2.insertCell(-1).innerHTML = (dist*Math.cos(lat*(Math.PI/180))/1000).toFixed(2);
        }
    }
}

function delete_chosen(){
    var table = document.getElementById("chosen_rutes");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "p"){
            table.deleteRow(i);
        }
    }
}

function edit_chosen(){
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
    var zone1 = document.getElementById("edit_zone1").value;
    var zone2 = document.getElementById("edit_zone2").value;
    var zone3 = document.getElementById("edit_zone3").value;
    var zone4 = document.getElementById("edit_zone4").value;
    var zone5 = document.getElementById("edit_zone5").value;
    zone_array = [zone1,zone2,zone3,zone4,zone5];
    var table = document.getElementById("chosen_rutes");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "edit"){
            for (j=0; j<5; j++){
                if (zone_array[j] != ''){
                    table.rows[i].cells[j].innerHTML = zone_array[j];
                }
            }
            table.rows[i].setAttribute("value","np");
            table.rows[i].style.background = "white";
        }
    }
}
    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:50%; height:70%; float:left" id="map"></div>
    <div style="width:50%; height:70%; float:left">
        <table id="chosen_rutes">
            <caption>Chosen rutes</caption> 
        </table> 
        <button type="button" onclick="delete_chosen()">delete</button>
        <button type="button" onclick="edit_chosen()">edit</button>
        <p style="text-decoration:underline">Add manually</p>
        <input type="text" name="add_zone1" id="add_zone1" size="1">
        <input type="text" name="add_zone2" id="add_zone2" size="1">
        <input type="text" name="add_zone3" id="add_zone3" size="1">
        <input type="text" name="add_zone4" id="add_zone4" size="1">
        <input type="text" name="add_zone5" id="add_zone5" size="1">
        <button type="button" onclick="add_manual()">add</button>
        <p style="text-decoration:underline">Edit</p>
        <input type="text" name="edit_zone1" id="edit_zone1" size="1">
        <input type="text" name="edit_zone2" id="edit_zone2" size="1">
        <input type="text" name="edit_zone3" id="edit_zone3" size="1">
        <input type="text" name="edit_zone4" id="edit_zone4" size="1">
        <input type="text" name="edit_zone5" id="edit_zone5" size="1">
        <button type="button" onclick="save_edit()">save</button>
    </div>
    <div style="width:20%; height:40%; float:left">
        <p id="zoom"></p> 
        <!--p id="demo">test</p-->
        </div>
        <div style="width:30%; height:40%; float:left">
       
        </div>
    <div style="width:100%; float:left;">
        <table id="dist"></table>
        
    </div>
        

</body>
 
</html>
