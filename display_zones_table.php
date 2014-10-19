<?php
require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_zones();

echo "<table style='border-collapse:collapse'>";
echo "<caption>List of zones:</caption>";
echo "<tr>";
echo "<th>zone #</th>";
echo "<th>GPS lon</th>";
echo "<th>GPS lat</th>";
echo "<th>radius</th>";
echo "<th>links to assignment</th>";
echo "</tr>";

$zones_num = sizeof($table);
for ($i=0; $i<$zones_num; $i++){
    if (isset($table[$i][0])){
        $zoneID = $table[$i][0];
    }
    else{
        $zoneID = 1;
    }
    echo "<tr value='np' id='row" . $zoneID . "' onclick=pick_row($zoneID," . $table[$i][4] . ") onmouseover=temppick_row($zoneID) onmouseout=unpick_row($zoneID)>";
    echo "<td>" . (string)$table[$i][0] . "</td>";
    echo "<td>" . $table[$i][1] . "</td>";
    echo "<td>" . $table[$i][2] . "</td>";
    echo "<td>" . $table[$i][3] . "</td>";
    echo "<td>" . $table[$i][4] . "</td>";
    echo "</tr>";
}
if(!isset($zoneID)){
    $zoneID = ' ';
}
echo "</table>";
echo "<p id='zoneID' style='position:absolute'>Zone " . $zoneID . " links to </p><input type='text' name='assID' id='assID' size='1' style='position:absolute;margin-left:135px;margin-top:10px' value='" . $table[0][4] . "'>";
echo "<button type='button' onclick='submit_assign()' style='position:absolute;margin-left:200px;margin-top:10px'>submit</button>"; 

?>
