<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();


$mysql = new Mysql_spil();
$table = $mysql->get_games();

?>

<html lang="da">
    <head>
		<link rel="stylesheet" href="style.css" type="text/css" />
        <title>
            Choose Game
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:10%">
            <button id="back" type="button" onclick=back_to_start('start_admin')>Back to start</button>
        </div>
        <div style="width:400px; height:400px; margin-left:auto; margin-right:auto; overflow:auto">
            <table id="games" style="margin-left:auto; margin-right:auto">
                <caption>Saved games</caption>
                <tr>
                    <th>Game name</th>
                    <th>Customer</th>
                </tr>
				<?php for ($i=0; $i<sizeof($table); $i++){
					echo '<tr id="row"' . $i . "; onclick=pick_row($i)>
							<td>" . $table[$i][1] . "</td>
							<td>" . $table[$i][2] . "</td>
						  </tr>";
				} ?>
			</table>
            <button type="button" onclick=change_page('spil_overblik')>Choose game</button>
        </div>
        
    </body>
</html>
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
                if (i % 2 == 0){
                	rows[i].style.background = "#fff";
				}
				else{
					rows[i].style.background = "#eee";
				}
                rows[i].setAttribute("value","np");
            }
        }
        if (rows[j+1].getAttribute("value") != "edit"){
            rows[j+1].style.background='blue';
            rows[j+1].setAttribute("value","p");
        }
    }

    function back_to_start(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
</script>
