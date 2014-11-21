<?php
require 'Mysql.php';

$zoom = $_GET["zoomLevel"];
$lon = $_GET["lon"];
$lat = $_GET["lat"];
$mapID = $_GET["mapID"];

if ($zoom != 13){
    echo "zoom level must be 13! go to zoom level 13 and try again"; 
   
}
else{
    chdir('tiles');
    mkdir($mapID);
    chdir('..');
    $center = array(floatval($lon),floatval($lat));

    $mysql = new Mysql_spil();
    $mysql->save_map($mapID, $lon, $lat);


    create_xml_file($zoom,$center,$mapID);
    chdir('JTileDownloader/jar');
    $output = shell_exec('java -jar jTileDownloader-0-6-1.jar dl=../../osm_get_file.xml 2>&1');
    /*chdir("../../tiles/$mapID/13");
    foreach ( scandir('.') as $src){
        if ($src != '.' and $src != '..'){
            if(file_exists("../../all13/13/$src")==False){
                chdir("../../all13/13/");
                mkdir($src);
                chdir("../../$mapID/13");
            }
            chdir($src);
            foreach ( scandir('.') as $src2){
                if ($src2 != '.' and $src2 != '..'){
                    copy($src2, "../../../all13/13/$src/$src2");
                }
            }
            chdir('..');
        }
    }
    chdir('../..');*/
    echo 'done';
}
/*if ($output == NULL){
    echo "<p>NULL<p>";
}
else{
echo "<p>$output<p>";
}*/

function create_xml_file($zoom,$center, $mapID){
        $zooms = range($zoom,15);
        $bounds = get_boundaries($center,0.03,0.01);
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


function get_boundaries($center,$dx,$dy){
    $maxLon = $center[0] + $dx;
    $minLon = $center[0] - $dx;
    $maxLat = $center[1] + $dy;
    $minLat = $center[1] - $dy;
    $ar = array($maxLon,$minLon,$maxLat,$minLat);
    return $ar;
}

?>
