<?php
require 'Mysql.php';

$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['team_name']) ) {
    $mysql->save_team();
}
?>

<html>
    <head>
        <title>
            Create teams
        </title>
    </head>
    <body>
        <form method="post" action="">
	    <p>
		    <label for="name">Team name:</label>
		    <input type="text" name="team_name" />
            <input type="submit" value="submit" name="submit"/>
	    </p>
	    </form>
    
    </body>
</html>
