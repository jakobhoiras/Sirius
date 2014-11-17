<?php
require 'Mysql.php';

$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['name1']) && !empty($_POST['name2']) && !empty($_POST['name3']) && !empty($_POST['name4'])) {
    $mysql->save_team($_POST['name1'],$_POST['name2'],$_POST['name3'],$_POST['name4']);
}

?>



<html>
    <head>
        <title>
            Create teams
        </title>

<script type="text/javascript">

var teams;
var old_edit_names = [0,0,0,0];
function init(){
    get_teams('disp');
}

    function get_teams(a){
        // retrieves the zones from the database. includes a recall to the function if nothing has changed.
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                teams = xmlhttp.responseText.split(" ");
                if (a == 'disp'){
                    var number_of_teams = (teams.length-1)/6;
                    var table = document.getElementById("teams");
                    for (var i=0; i<number_of_teams; i++){
                        var row = table.insertRow(-1);
                        row.setAttribute("value","np");
                        add_pick_coloring(i, teams);
                        var cell = row.insertCell(0);
                        cell.innerHTML = "team " + (teams[i*6]);
                    }
                }
            }
        }
        xmlhttp.open("GET","display_teams.php",true);
        xmlhttp.send();
    }

    function add_pick_coloring(i, teams){
        var table = document.getElementById("teams");
        table.rows[i].onclick = function() {pick_rute(table, j=i, teams)};
    }

    function pick_rute(table, j='none', teams) {
        // upon cliking on a rute the coloring of the table is changed accordingly.
        // The zones belonging to the rute is colored orange on the map and finally lines are drawn.
        // If a rute was already chosen the old zones are returned to their original color
        var rows = table.rows;
        for (i=0; i<rows.length; i++){
            if(rows[i].getAttribute("value") != "edit"){
                rows[i].style.background = "white";
                rows[i].setAttribute("value","np");
            }
        }
        if (rows[j].getAttribute("value") != "edit"){
            rows[j].style.background='blue';
            rows[j].setAttribute("value","p");
        }
        show_team(teams, j);
    }
    
    function show_team(teams, j){
        var name1 = teams[j*6+2];
        var name2 = teams[j*6+3];
        var name3 = teams[j*6+4];
        var name4 = teams[j*6+5];
        document.getElementById("name1").innerHTML = name1;
        document.getElementById("name2").innerHTML = name2;
        document.getElementById("name3").innerHTML = name3;
        document.getElementById("name4").innerHTML = name4;
    }

    function delete_chosen(){
        // removes a team from the list (table)
        var table = document.getElementById("teams");
        var team = [];
        for (i=0; i<table.rows.length; i++){
            if (table.rows[i].getAttribute("value") == "p"){
                var teamID = parseInt(table.rows[i].cells[0].innerHTML.split("").reverse().join(""));
                delete_team_from_db(teamID, 'delete_team');
                table.deleteRow(i);
            }
        }
    }

function delete_team_from_db(teamID,a){
    // retrieves the zones from the database. includes a recall to the function if nothing has changed.
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    if (a == 'edit' || a == 'delete_team'){
        xmlhttp.open("GET","update_team.php?teamID=" + teamID + '&a=' + a,true);
        xmlhttp.send();
    }
}

function edit_chosen(){
    // for chosing a rute to edit. 
    get_teams('not_disp');
    var table = document.getElementById("teams");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "edit"){
            table.rows[i].style.background = "white";
            table.rows[i].setAttribute("value","np");
        }
        if (table.rows[i].getAttribute("value") == "p"){
            for (j=0; j<4; j++){
                document.getElementById("edit_name" + (j+1)).value = teams[i*6+j+2];
                old_edit_names[j] = teams[i*6+j+2];
            }
            table.rows[i].style.background = "green";
            table.rows[i].setAttribute("value","edit");
        }
    }
}

function save_edit(){
    // for re-saving a rute after edit 
    var edit_name1 = document.getElementById("edit_name1").value;
    var edit_name2 = document.getElementById("edit_name2").value;
    var edit_name3 = document.getElementById("edit_name3").value;
    var edit_name4 = document.getElementById("edit_name4").value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        }
    }
    a='edit'
    xmlhttp.open("GET","update_team.php?name1=" + edit_name1 + "&name2=" + edit_name2 + "&name3=" + edit_name3 + "&name4=" + edit_name4 + "&old_name1=" + old_edit_names[0] + "&old_name2=" + old_edit_names[1] + "&old_name3=" + old_edit_names[2] + "&old_name4=" + old_edit_names[3] + '&a=' + a,true);
    xmlhttp.send();
    var table = document.getElementById("teams");
    for (i=0; i<table.rows.length; i++){
        if (table.rows[i].getAttribute("value") == "edit"){
            table.rows[i].setAttribute("value","np");
            table.rows[i].style.background = "white";
        }
    }
    remove_old_content();
    document.getElementById("edit_name1").value = "";
    document.getElementById("edit_name2").value = "";
    document.getElementById("edit_name3").value = "";
    document.getElementById("edit_name4").value = "";
    setTimeout(function(){get_teams('disp')},500);
}

function remove_old_content(){
    var table = document.getElementById("teams");
    var len = table.rows.length;
    for (var i=0; i<len; i++){
        table.deleteRow(0);
    }
}

</script>
        
    </head>
    <body onload=init();>
        <div style="width:580px; margin-left:auto; margin-right:auto">
            <div style="width:320px; float:left;">
            <form method="post" action="">
            <h2 style="text-decoration:underline; text-align:center">Add new team</h2>
	        <h3 style="text-align:center">start inde</h3>
            <p>
                <label for="name">name 1: </label>
                <input type="text" name="name1" />
            </p>
            <p>
                <label for="name">name 2: </label>
                <input type="text" name="name2" />
            </p>
            <h3 style="text-align:center">start ude</h3>
            <p>
                <label for="name">name 3: </label>
                <input type="text" name="name3" />
            </p>
            <p>
                <label for="name">name 4: </label>
                <input type="text" name="name4" />
            </p>
            <p style="text-align:center">
                <input type="submit" value="submit" name="submit" onclick="get_teams()"/>
            </p>
	        </form>
            </div>
            <div style="width:250px; height:373px; float:left; overflow:auto">
                <div style="height:90%">
                    <table id="teams" style="margin-left:auto; margin-right:auto;">
                        <caption>Teams</caption> 
                    </table>
                </div>
                <div style="margin-left:auto; margin-right:auto; width:55%; ">
                    <input type="submit" value="edit" name="submit" onclick="edit_chosen()"/>
                    <input type="submit" value="delete" name="submit" onclick="delete_chosen()"/>
                </div>
            </div> 
            <div style="width:250px; float:left;">
                <h3 style="text-align:center">start inde</h3>
                    <p id="name1" style="text-align:center">pick a team<p>
                    <p id="name2" style="text-align:center">pick a team<p>
                <h3 style="text-align:center">start ude</h3>
                    <p id="name3" style="text-align:center">pick a team<p>
                    <p id="name4" style="text-align:center">pick a team<p>
            </div>  
            <div style="width:320px; border-style:solid; float:left;">
                <h2 style="text-decoration:underline; text-align:center">Edit team</h2>
	            <h3 style="text-align:center">start inde</h3>
                <p>
                    <label for="edit_name1">name 1: </label>
                    <input type="text" name="edit_name1" id="edit_name1"/>
                </p>
                <p>
                    <label for="edit_name2">name 2: </label>
                    <input type="text" name="edit_name2" id="edit_name2"/>
                </p>
                <h3 style="text-align:center">start ude</h3>
                <p>
                    <label for="edit_name3">name 3: </label>
                    <input type="text" name="edit_name3" id="edit_name3"/>
                </p>
                <p>
                    <label for="edit_name4">name 4: </label>
                    <input type="text" name="edit_name4" id="edit_name4"/>
                </p>
                <p style="text-align:center">
                    <input type="submit" value="submit" name="submit" onclick="save_edit()"/>
                </p>
            </div> 
        </div>
    </body>
</html>
