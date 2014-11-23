<?php
/*require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
require 'Mysql_create_game.php';
$mysql = new Mysql_spil();
*/

require 'Mysql_opgave.php';

$mysql = new Mysql_assignment();
$table = $mysql->get_assignments();
$table2 = $mysql->get_imported_assignments();

?>

<html lang="da">
    <head>
        <title>
            Importer kort
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:500px; height:400px; margin-left:auto; margin-right:auto;">
			<div style="width:50%; height:100%; float:left; overflow:auto">
				<div style="width:100%; height:90%; float:left; overflow:auto">
					<table id="db" style="margin-left:auto; margin-right:auto">
				        <caption>Opgave database</caption>
						<?php 
						for ($i=0; $i<sizeof($table); $i++){
							echo '<tr id="row"' . $i . "; onclick=pick_row($i," . '"db"' . ")>
									<td>" . $table[$i][0] . "</td>
								  </tr>";
						} ?>
					</table>
				</div>
				<div style="width:20%; height:10%; margin-left:auto; margin-right:auto;">
					<button style="" type="button" onclick="import()">Import</button>
				</div>
			</div>
			<div style="width:50%; height:100%; float:left; overflow:auto">
				<div style="width:100%; height:90%; float:left; overflow:auto">
					<table id="import" style="margin-left:auto; margin-right:auto">
				        <caption>Importerede opgaver</caption>
						<?php 
						for ($i=0; $i<sizeof($table2); $i++){
							echo '<tr id="row_s"' . $i . "; onclick=pick_row($i," . '"import"' . ")>
									<td>" . $table2[$i][0] . "</td>
								  </tr>";
						} ?>
					</table>
				</div>
				<div style="width:20%; height:10%; margin-left:auto; margin-right:auto;">
					<button style="" type="button" onclick="delete_chosen()">Delete</button>
				</div>
			</div>
        </div>
    </body>
</html>

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

    /*function change_page(page_name) {
        var table = document.getElementById("games");
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") == "p"){
                var current_game = rows[i].cells[0].innerHTML;
            }
        }
        window.location.href = ("http://localhost/sirius/" + page_name + ".php?cg=" + current_game);
    }*/

    function pick_row(j, table_name) {
        var rows = document.getElementById(table_name).rows;
        var rows_db = document.getElementById("db").rows;
        var rows_im = document.getElementById("import").rows;
        for (i=0; i<rows_db.length; i++){
            if(rows_db[i].getAttribute("value") != "edit"){
                rows_db[i].style.background = "white";
                rows_db[i].setAttribute("value","np");
            }
        }
        for (i=0; i<rows_im.length; i++){
            if(rows_im[i].getAttribute("value") != "edit"){
                rows_im[i].style.background = "white";
                rows_im[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "edit"){
            rows[j].style.background='blue';
            rows[j].setAttribute("value","p");
        }
    }
</script>
