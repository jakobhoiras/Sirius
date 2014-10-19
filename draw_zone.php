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
        eventListeners: {
        featureover: function(e) { // style change when hovering zone
            if (e.feature.attributes["type"] != "perim"){
                e.feature.style = {fillColor: "blue", fillOpacity: 0.4, strokeColor: "blue",label: e.feature.style.label, fontSize: 10};
                e.feature.layer.drawFeature(e.feature);
            }
        },
        featureout: function(e) { // style change when not hovering zone
            if (e.feature.attributes["type"] != "perim"){
                e.feature.style = {fillColor: "red", fillOpacity: 0.4, strokeColor: "red",label: e.feature.style.label, fontSize: 10};
                e.feature.layer.drawFeature(e.feature);
                if (e.feature.attributes["type"]=="set"){
                    e.feature.style = {fillColor: "green", fillOpacity: 0.4, strokeColor: "green",label: e.feature.style.label, fontSize: 10};
                    e.feature.layer.drawFeature(e.feature);
                }
            }
        }
    }
    });
    // zonelayer functionality
    var layerListeners = {
        featureclick: function(e) { // action when zone is clicked. the zone is removed if the delete control is set
            if (delete_z == 1 && e.feature.geometry.getVertices().length == 50) {
                if (e.feature.attributes["type"] != "perim"){
                    area_map_units = e.feature.geometry.getArea(); 
                    radius_map_units = Math.sqrt(area_map_units / Math.PI); 
                    var radius_in_m = Math.ceil(radius_map_units * Math.cos(lat*(Math.PI/180)));
                    var center = e.feature.geometry.getCentroid().transform(map.projection, map.displayProjection);
                    update_zones_in_db(center.x, center.y, radius_in_m, 'delete_zone');         
                    zoneLayer.removeFeatures( [ e.feature ] );
                    get_zones_from_db();
                    return false;
                }
            }
            else if (delete_b == 1 && e.feature.geometry.getVertices().length == 4) {
                area_map_units = e.feature.geometry.getArea();
                side_map_units = Math.sqrt(area_map_units);
                radius_map_units = Math.sqrt(2 * Math.pow(side_map_units,2)) / 2
                var radius_in_m = Math.ceil(radius_map_units * Math.cos(lat*(Math.PI/180)));
                radius_in_m = 10 * Math.round(radius_in_m / 10.0);
                var center = e.feature.geometry.getCentroid().transform(map.projection, map.displayProjection);
                update_zones_in_db(center.x, center.y, radius_in_m, 'delete_base');         
                baseLayer.removeFeatures( [ e.feature ] );
                get_bases_from_db();
                return false;
            }
        },
        featureadded: function(e) { // Checks if a zone is linked to an assignment and sets atrributes accordingly
            if (typeof(e.feature.attributes["type"])=="undefined"){
                e.feature.attributes["type"]="notset";
            }
            if (typeof(e.feature.attributes["ID"])=="undefined"){
                var zones;
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange=function() {
                    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                        zones = xmlhttp.responseText.split(" ");
                        var zones_num = zones.length;
                        e.feature.attributes["ID"]=zones[zones_num-6];
                    }
                }
                xmlhttp.open("GET","get_zones.php",true);
                xmlhttp.send();
            }
            //document.getElementById("zoom").innerHTML = 'Zoom Level: ' + e.feature.layer;
            if (base_layer == 1 && e.feature.layer.features.length > 1){
                return alert("you now have " + e.feature.layer.features.length + " base(s).");
            }
        }
    };

    // adds the layers for points marking zones and base
    zoneLayer = new OpenLayers.Layer.Vector("Zone Layer", {eventListeners: layerListeners});
    map.addLayers([zoneLayer]);
    baseLayer = new OpenLayers.Layer.Vector("Base Layer", {eventListeners: layerListeners});
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
    drawControls = { 
        zone: new OpenLayers.Control.DrawFeature(zoneLayer, OpenLayers.Handler.RegularPolygon, { 
            handlerOptions: {
                sides: 50
            }
        }),
        base: new OpenLayers.Control.DrawFeature(baseLayer, OpenLayers.Handler.RegularPolygon, { 
            handlerOptions: {
                sides: 4
            }
        })
    };
    // updates database and interface table when a zone is created
    drawControls['zone'].handler.callbacks.create = function(data) {
        area_map_units = data.getArea();
        radius_map_units = Math.sqrt(area_map_units / Math.PI); 
        var radius_in_m = Math.ceil(radius_map_units * Math.cos(lat*(Math.PI/180)));
        var center = data.getCentroid().transform(map.projection, map.displayProjection);
        update_zones_in_db(center.x, center.y, radius_in_m, 'save');
        get_zones_from_db();
    };

    drawControls['base'].handler.callbacks.create = function(data) {
        area_map_units = data.getArea();
        side_map_units = Math.sqrt(area_map_units);
        radius_map_units = Math.sqrt(2 * Math.pow(side_map_units,2)) / 2
        var radius_in_m = Math.ceil(radius_map_units * Math.cos(lat*(Math.PI/180)));
        radius_in_m = 10 * Math.round(radius_in_m / 10.0);
        var center = data.getCentroid().transform(map.projection, map.displayProjection);
        update_zones_in_db(center.x, center.y, radius_in_m, 'saveBase');
        get_bases_from_db();
    }

    for(var key in drawControls) { // adds the controls to the map object
        map.addControl(drawControls[key]);
    }

    // controls for switching maps/layers
 	var switcherControl = new OpenLayers.Control.LayerSwitcher();
	map.addControl(switcherControl);
	switcherControl.maximizeControl();
            
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
    document.getElementById('noneToggle').checked = true; // sets control to map navigation

    get_zones_from_db(); // updates zone table on page initialization
    add_zones(); // add zones to map on page initialization
    add_bases();
    get_bases_from_db();
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


// sets the radius of the zones
// corrects the distance to match the mercator projection (not exact)
function setSize(radius_in_m) {
    var radius = radius_in_m/Math.cos(lat*(Math.PI/180));
    drawControls['zone'].handler.setOptions({radius: radius, angle: 0});
    drawControls['base'].handler.setOptions({radius: radius, angle: 0});
}

// for adding a zone with GPS coordinates
function add_zone(){
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

// updates the database with a new zone
function update_zones_in_db(centerX, centerY, radius, a) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //document.getElementById("zoom").innerHTML=xmlhttp.responseText;
        }
    }
    if (a == 'save' || a == 'delete_zone' || a == 'saveBase' || a == 'delete_base'){
        xmlhttp.open("GET","update_zones.php?x=" + centerX.toFixed(6) + "&y=" + centerY.toFixed(6) + "&r=" + radius + '&a=' + a,true);
        xmlhttp.send();
    } 
}
// retrieves the zones from the database. includes a recall to the function if nothing has changed.
function get_zones_from_db(){
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

function get_bases_from_db(){
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

// is currently not used
function get_zone_link_from_db(zoneID){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var res = xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET","get_zone_link.php?zID=" + zoneID ,true);
    xmlhttp.send();
    return res;
}
// for linking zones to assignments
function submit_assign(){
    var zoneID = document.getElementById("zoneID").innerHTML.split(" ")[1];
    var assID = document.getElementById("assID").value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //document.getElementById("zoom").innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET","update_zones.php?x=" + zoneID + "&y=" + assID + "&r=1&a=link",true);
    xmlhttp.send();
    get_zones_from_db();
    zoneLayer.removeAllFeatures();
    add_zones();
}
// for controlling style and graphics when chosing a zone in the table
function pick_row(i, assID){
    document.getElementById("row" + i).style.background = "blue";
    document.getElementById("row" + i).setAttribute("value", 'p');
    document.getElementById("zoneID").innerHTML = "Zone " + (i) + " links to ";
    document.getElementById("assID").setAttribute("value", assID);
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
                zoneLayer.features[j].layer.redraw();
            }
        }
    }
    row_picked = i;
}
// for controlling style and graphics when hovering a zone in the table
function temppick_row(i){
    document.getElementById("row" + i).style.background = "blue";
}
// for controlling style and graphics when dehovering a zone in the table
function unpick_row(i){
    if (document.getElementById("row" + i).getAttribute("value") == 'np'){
        document.getElementById("row" + i).style.background = "white";
    }
}


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

    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:50%; height:70%; float:left" id="map"></div>
    <div style="width:50%; height:70%; float:left">
        <div id="zones_table"></div>
    </div>
    <div style="width:20%; height:40%; float:left">
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
        <div style="width:30%; height:40%; float:left">
        <p style="float:left">radius of zone or base:<p>
        <select name="size" onchange="setSize(parseFloat(this.value))" id="size" style="float:left">
            <option value="" selected="selected">variable</option>
            <option value="10">10m</option>
            <option value="20">20m</option>
            <option value="30">30m</option>
            <option value="40">40m</option>
            <option value="50">50m</option>
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
    <div style="width:100%; float:left;">
        <p id="base#"># of bases: 0</p>
        <button type="button" onclick="add_rute()">add rute</button>
        <button type="button" onclick="add_zone_to_rute()">add zone</button><input type="text" name="addToRute" id="addToRute" size="3">
        <table id="table" style="border: 1px solid black"><table>
        
    </div>
        

</body>
 
</html>
