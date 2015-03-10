<?php
require 'Mysql.php';

$t = time();
$map_name = $_GET["map_name"];
$lon = floatval($_GET["lon"]);
$lat = floatval($_GET["lat"]);
$center = array($lon,$lat);
$top = $lat + 0.09;
$bottom = $lat - 0.09;
$left = $lon - 0.18;
$right = $lon + 0.18;

$mysql = new Mysql_spil();
$mysql->save_map($map_name, $lon, $lat);

$output = shell_exec("bzcat ../denmark-latest.osm.bz2 | osmosis  --read-xml enableDateParsing=no file=-  --bounding-box top=$top left=$left bottom=$bottom right=$right --write-xml file=- | bzip2 > " . "../tiles/" . "$map_name.osm.bz2 2>&1");
//if ($output == NULL){
//    echo "<p>NULL<p>";
//}
//else{
//echo "<p>$output<p>";
//}
create_mscript_file($map_name);
chdir('Maperitive');
$output = shell_exec('sh Maperitive.sh ../osm_render_file.mscript 2>&1');
chdir('../../tiles');
require 'zipper.php';
$zip = new zipper();
ini_set('max_execution_time', 300);
ini_set('memory_limit', '350M');
$zip -> zip(getcwd() . "/../tiles/" . $map_name, getcwd() . "/../tiles/" . $map_name . '.zip');
/*if ($output == NULL){
    echo "<p>NULL<p>";
}
else{
echo "<p>$output<p>";
$t = time();
echo "<p>$t</p>";
}*/

function create_mscript_file($map_name){
	$myfile = fopen("osm_render_file.mscript", "w") or die("Unable to open file!");
        $txt = 'use-ruleset alias=default'."\n";
        fwrite($myfile, $txt);
        $txt = "load-source ../../tiles/$map_name.osm.bz2"."\n";
        fwrite($myfile, $txt);
        $txt = "generate-tiles minzoom=12 maxzoom=17 tilesdir=../../tiles/$map_name"."\n";
        fwrite($myfile, $txt);
        $txt = 'exit';
	fwrite($myfile, $txt);
    fclose($myfile); 
}


?>
