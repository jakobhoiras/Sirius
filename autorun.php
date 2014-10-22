<?php
require 'Mysql.php';

$zoom = $_GET["zoomLevel"];
$center = $_GET["mapCenter"];
$mapID = $_GET["mapID"];

chdir('tiles');
mkdir($mapID);
chdir('..');
$mysql = new Mysql_spil();
$mysql->save_map($mapID);

$lon = floatval(substr($center,4,18));
$lat = floatval(substr($center,27,34));
$center = array($lon,$lat);
create_xml_file($zoom,$center,$mapID);
chdir('JTileDownloader/jar');
$output = shell_exec('java -jar jTileDownloader-0-6-1.jar dl=../../osm_get_file.xml 2>&1');

if ($output == NULL){
    echo "<p>NULL<p>";
}
else{
echo "<p>$output<p>";
}

function create_xml_file($zoom,$center, $mapID){
    if ($zoom < 13 or $zoom > 14){
        echo "<p>zoom level must be between 13 and 17(temporarily 14)! go back and try again<p>";    
    }
    else{
        $zooms = range($zoom,14);
        $bounds = get_boundaries($center,0.05);
        $myfile = fopen("osm_get_file.xml", "w") or die("Unable to open file!");
        $txt = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        fwrite($myfile, $txt);
        $txt = '<!DOCTYPE properties SYSTEM "http://java.sun.com/dtd/properties.dtd">'."\n";
        fwrite($myfile, $txt);
        $txt = '<properties>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="Type">BBoxLatLon</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="OutputLocation">../../tiles/' . $mapID . '</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="TileServer">http://a.tile.openstreetmap.org</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="MaxLon">'.$bounds[0].'</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="MinLon">'.$bounds[1].'</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="MaxLat">'.$bounds[2].'</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="MinLat">'.$bounds[3].'</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '<entry key="OutputZoomLevel">';
        foreach ($zooms as $zoom1){
            $txt = $txt . $zoom1 . ',';
        }
        $txt = substr($txt,0,-1) . '</entry>'."\n";
        fwrite($myfile, $txt);
        $txt = '</properties>'."\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }    
}


function get_boundaries($center,$dx){
    $maxLon = $center[0] + $dx;
    $minLon = $center[0] - $dx;
    $maxLat = $center[1] + $dx;
    $minLat = $center[1] - $dx;
    $ar = array($maxLon,$minLon,$maxLat,$minLat);
    return $ar;
}
?>
