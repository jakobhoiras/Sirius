function add_zones(){
    // for (re)adding all zones in the database to the map
    zoneLayer.removeAllFeatures();
    var zones;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            zones = xmlhttp.responseText.split(" ");
            var zones_num = zones.length;
            for (var i=0; i<(zones_num-1)/4; i++){
                var lon_new = zones[i*4+1]; // lon of the center of the zone
                var lat_new = zones[i*4+2]; // lat of the center of the zone
                var radius_in_m = zones[i*4+3];
                var lonLat = new OpenLayers.LonLat(lon_new,lat_new).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()); //transform from map units(conventional) to map projection units(stupid)
                var point = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
                var radius = radius_in_m/Math.cos(lat*(Math.PI/180)); //transform from map units(conventional) to map projection units(stupid). this not an exact conversion.
                var mycircle = OpenLayers.Geometry.Polygon.createRegularPolygon(point,radius,50,0);
                var featurecircle = new OpenLayers.Feature.Vector(mycircle);
                if (0 == 0){
                    // sets the color of the zone to red if it is not linked to an assignment
                    featurecircle.style = {fillColor: "red", fillOpacity: 0.4, strokeColor:"red",label: zones[i*4], fontSize: 10};
                    featurecircle.attributes["type"]="notset";
                    }
                else{
                    // sets the color of the zone to green if it is linked to an assignment
                    featurecircle.style = {fillColor: "green", fillOpacity: 0.4, strokeColor:"green",label: zones[i*5], fontSize: 10};
                    featurecircle.attributes["type"]="set";
                }
                featurecircle.attributes["ID"]=zones[i*4]; // set an id attribute for use in later manipulation
                zoneLayer.addFeatures([featurecircle]); // adds the zone to the layer
            }        
        }
    }
    xmlhttp.open("GET","get_zones.php",true);
    xmlhttp.send();
}

function add_bases(){
    // for (re)adding all zones in the database to the map
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
                featurecircle.attributes["ID"]="base";
                baseLayer.addFeatures([featurecircle]); 
            }        
        }
    }
    xmlhttp.open("GET","get_bases.php",true);
    xmlhttp.send();
}

function get_base(){
    // gets the base information from the database
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            base = xmlhttp.responseText.split(" ");         
        }
    }
    xmlhttp.open("GET","get_bases.php",true);
    xmlhttp.send(); 
}
