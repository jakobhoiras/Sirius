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
$table = $mysql->get_games();

echo '<html lang="da">
    <head>
        <title>
            Vælg eksisterende spil
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:400px; height:400px; margin-left:auto; margin-right:auto; overflow:auto">
            <table id="games" style="margin-left:auto; margin-right:auto">
                <caption>Gemte spil</caption>
                <tr>
                    <th>spil navn</th>
                    <th>kunde</th>
                </tr>';
for ($i=0; $i<sizeof($table); $i++){
    echo '<tr id="row"' . $i . "; onclick=pick_row($i)>
            <td>" . $table[$i][1] . "</td>
            <td>" . $table[$i][2] . "</td>
          </tr>";
}
echo '</table>
        </div>
        <button type="button" onclick="change_page(' . "'" . 'spil_overblik' . "'" . ')">vælg spil</button>
    </body>
</html>';

?>


<script>
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
            if(rows[i].getAttribute("value") != "edit"){
                rows[i].style.background = "white";
                rows[i].setAttribute("value","np");
            }
        }
        if (rows[j+1].getAttribute("value") != "edit"){
            rows[j+1].style.background='blue';
            rows[j+1].setAttribute("value","p");
        }
    }
</script>
