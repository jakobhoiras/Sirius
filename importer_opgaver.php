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
$table2_new = [];
for ($i=0; $i<sizeof($table2); $i++){
	array_push($table2_new, $table2[$i][0]);
}

?>

<html lang="da">
    <head>
		<link rel="stylesheet" href="style.css" type="text/css" />
        <title>
            Importer kort
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:500px; height:400px; margin-left:auto; margin-right:auto;">
			<div style="width:48%; height:100%; float:left; overflow:auto">
				<div style="width:100%; height:90%; float:left; overflow:auto">
					<table id="db" style="margin-left:auto; margin-right:auto">
				        <caption>Opgave database</caption>
						<?php 
						$j=0;
						for ($i=0; $i<sizeof($table); $i++){
							if (in_array($table[$i][0],$table2_new)==false){
								echo '<tr id="row"' . $j . "; onclick=pick_row($j," . '"db"' . ")>
										<td>" . $table[$i][0] . "</td>
									  </tr>";
								$j += 1;
							}
						} ?>
					</table>
				</div>
				<div style="width:20%; height:10%; margin-left:auto; margin-right:auto;">
					<button style="" type="button" onclick="import_ass()">Import</button>
				</div>
			</div>
			<div style="width:48%; height:100%; float:right; overflow:auto">
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

	function add_ass_to_db(a) {
    // delete or add a zone or a base
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    xmlhttp.open("GET","update_assignments.php?a=" + a,true);
    xmlhttp.send(); 
}	

    function import_ass(){
        var table = document.getElementById("db");
    	var table2 = document.getElementById("import");
    	var len = table.rows.length;
    	for (var i=0; i<len; i++){
		    if (table.rows[i].getAttribute("value") == "p"){
		        var row = table2.insertRow(-1);
		        for (var j=0; j<table.rows[i].cells.length; j++){
		            row.insertCell(-1).innerHTML = table.rows[i].cells[j].innerHTML;
		        }
		        table.deleteRow(i);
		        var len = table2.rows.length-1;
		        row.onclick = function (){pick_row(len, 'import')};
		        for (j=0; j<table.rows.length; j++){
		            add_pick_coloring(j,'db');
		        }
		        add_ass_to_db(row.cells[0].innerHTML);
		        return true;
		    }
		}
	}

	function add_pick_coloring(i,p){
		var table = document.getElementById(p);
		table.rows[i].onclick = function() {pick_row(i,  p)};
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
                if (i % 2 == 0){
                	rows_db[i].style.background = "#fff";
				}
				else{
					rows_db[i].style.background = "#eee";
				}
                rows_db[i].setAttribute("value","np");
            }
        }
        for (i=0; i<rows_im.length; i++){
            if(rows_im[i].getAttribute("value") != "edit"){
                if (i % 2 == 0){
                	rows_im[i].style.background = "#fff";
				}
				else{
					rows_im[i].style.background = "#eee";
				}
                rows_im[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "edit"){
            rows[j].style.background='blue';
            rows[j].setAttribute("value","p");
        }
    }

	/*function delete_chosen(){
    // removes a rute from the list (table)
    var table = document.getElementById("import");
    var rute = [];
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "p"){
            for(var j=0; j<table.rows[i].cells.length; j++){
                rute.push(table.rows[i].cells[j].innerHTML);
            }
            for (var j=0; j<5-table.rows[i].cells.length; j++){
                rute.push(0);
            }
			console.log(rute);
            add_rute_to_db(rute, 'delete_rute');
            table.deleteRow(i);
        }
    }
}*/
</script>