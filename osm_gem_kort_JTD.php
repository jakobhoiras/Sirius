<?php

?>

<html>
<head>
    <title>OSM Local Tiles</title>
    <link rel="stylesheet" href="default_style.css" type="text/css" />
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
        var zoom=16;
        var map;
              var mapBounds = new OpenLayers.Bounds( 12.3937091643, 55.7398667756, 12.6078492329, 55.8264812224);
              var mapMinZoom = 13;
              var mapMaxZoom = 17;
              OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
 
        var emptyTileURL = "http://www.maptiler.org/img/none.png";
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
            var newLayer = new OpenLayers.Layer.OSM("Local Tiles", "tiles/odense/${z}/${x}/${y}.png", {numZoomLevels: 19, alpha: true, isBaseLayer: false});
            map.addLayer(newLayer);
			// This is the end of the layer

            // This is the layer that uses the locally stored tiles
            var newLayer2 = new OpenLayers.Layer.TMS("Local Tiles2", "tiles/lyngby/", {numZoomLevels: 19, alpha: true, isBaseLayer: false, layername: '.', type: 'png',serviceVersion: '.', getURL: getURL});
            map.addLayer(newLayer2);
            if (OpenLayers.Util.alphaHack() == false) {
                      newLayer2.setOpacity(0.7);
                  }

			// This is the end of the layer
 
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
                      document.getElementById("demo").innerHTML = x;
                      return url + path;
                  } else {
                      return emptyTileURL;
                  }
              } 

        function Save_map(){
            var zoomLevel = map.getZoom();
            var mapCenter = map.getCenter().transform(map.projection, map.displayProjection);
            createCookie("zoom", zoomLevel, "10");
            createCookie("center", mapCenter, "10");
            window.location="autorun.php";
        }
         function createCookie(name, value, days) {
            var expires;
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            } 
            else {
                expires = "";
            }
            document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
        }
    </script>
</head>
 
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body onload="init();">
 
    <!-- define a DIV into which the map will appear. Make it take up the whole window -->
    <div style="width:100%; height:90%" id="map"></div>
    <div style="width:100%; height:10%">
        <p id="zoom" style="float:left"></p> 
        <p id="demo">test</p>
        <button type="button" onclick="Save_map()" style="float:right">Gem kort</button> 
    </div>
</body>
 
</html>
