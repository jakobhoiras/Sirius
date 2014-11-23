<?php
/*require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();
*/

require 'Mysql.php';

$mysql = new Mysql_spil();
$table = $mysql->get_maps();

echo '<html lang="da">
    <head>
        <title>
            Importer kort
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:400px; height:400px; margin-left:auto; margin-right:auto; overflow:auto">
            <table id="games" style="margin-left:auto; margin-right:auto">
                <caption>Gemte kort</caption>';
for ($i=0; $i<sizeof($table); $i++){
    echo '<tr id="row"' . $i . "; onclick=pick_row($i)>
            <td>" . $table[$i][0] . "</td>
          </tr>";
}
echo '</table>
        </div>
        <button id="choose" type="button">vælg spil</button>
    </body>
</html>';

?>


<script>
    function set_map(map_name, j){
        var table = document.getElementById("games");
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") != "edit"){
                rows[i].style.background = "white";
                rows[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "edit"){
            rows[j].style.background='green';
            rows[j].setAttribute("value","chosen");
        }
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            }
        }
        xmlhttp.open("GET","set_map.php?map_name=" + rows[j].cells[0].innerHTML,true);
        xmlhttp.send();
    }

    function change_page(page_name) {
        var table = document.getElementById("games");
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") == "p"){
                var current_game = rows[i].cells[0].innerHTML;
            }
        }
        window.location.href = ("http://localhost/sirius/" + page_name + ".php?cg=" + current_game);
    }

    function pick_row(j) {
        var table = document.getElementById("games");
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") != "chosen"){
                rows[i].style.background = "white";
                rows[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "chosen"){
            rows[j].style.background='blue';
            rows[j].setAttribute("value","p");
        }
        var button = document.getElementById("choose");
        button.onclick = function(){set_map(rows[j].cells[0].innerHTML, j)};
    }
</script>
