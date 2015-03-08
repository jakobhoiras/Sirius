<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}

?>

<script>
function change_page(page_name) {
        window.location.href = ("http://localhost/sirius/" + page_name + ".php?cg=" + <?php echo json_encode($_SESSION['cg']) ?>);
}
</script>


<html lang="da">
    <head>
        <title>
            Consultant panel
        </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
    </head>
    <div style="width:100%; padding-bottom:5px">
        <button id="back" type="button" onclick=change_page('spil_overblik')>Game menu</button>
        <form method="post" style="display:inline">
            <input type="submit" value="Log out" style="float:right" name="logout" /><br>
        </form>
    </div>
	<body>
        <div style="width:800px; margin-left:auto; margin-right:auto;">
		<div style="width:38%; margin-left:auto; margin-right:auto">
				<input id="time_btn" type="button" value="time control" onclick=change_page("consultant_time")><br/>
                <input id="score_btn" type="button" value="score overview" onclick=change_page("consultant_score")><br/>    
                <input id="asgn_btn" type="button" value="assignments" onclick=change_page("consultant_assignments")>
		</div>
        </div>
	</body>
</html>

 

<!-- // -->
