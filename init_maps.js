function init_rutes_map(){ 
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
}


function init_zone_map(){
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
                
                e.feature.style = {fillColor: "blue", fillOpacity: 0.4, strokeColor: "blue",label: e.feature.attributes["ID"], fontSize: 10};
                e.feature.layer.drawFeature(e.feature);
            }
        },
        featureout: function(e) { // style change when not hovering zone
            if (e.feature.attributes["type"] != "perim"){
                e.feature.style = {fillColor: "red", fillOpacity: 0.4, strokeColor: "red",label: e.feature.attributes["ID"], fontSize: 10};
                e.feature.layer.drawFeature(e.feature);
                if (e.feature.attributes["type"]=="set"){
                    e.feature.style = {fillColor: "green", fillOpacity: 0.4, strokeColor: "green",label: e.feature.attributes["ID"], fontSize: 10};
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
}
