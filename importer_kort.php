<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();


if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}


$mysql = new Mysql_spil();
$table = $mysql->get_maps();

?>

<html lang="da">
	<link rel="stylesheet" href="style.css" type="text/css" />
    <head>
        <title>
            Import map
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <body>
        <div style="width:100%">
            <button id="back" type="button" onclick=change_page('spil_overblik')>Game menu</button>
            <form method="post" style="display:inline">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </div>
        <div style="width:400px; height:400px; margin-left:auto; margin-right:auto; overflow:auto">
            <table id="games" style="margin-left:auto; margin-right:auto">
                <caption>Saved maps</caption>
				<?php 
				for ($i=0; $i<sizeof($table); $i++){
					echo '<tr id="row"' . $i . "; onclick=pick_row($i)>
							<td>" . $table[$i][0] . "</td>
						  </tr>";
				} ?>
			</table>
            <button id="choose" type="button">Choose map</button>
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

    function change_page(page_name) {
        window.location.href = ( page_name + ".php?cg=" + <?php echo json_encode($_SESSION['cg']) ?>);
    }

    function pick_row(j) {
        var table = document.getElementById("games");
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") != "chosen"){
				if (i % 2 == 0){
                	rows[i].style.background = "#fff";
				}
				else{
					rows[i].style.background = "#eee";
				}
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
