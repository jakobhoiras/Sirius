<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_App();
$membership->check_Active();

if( $_POST && !empty($_POST['logout']) ) {
    $membership -> log_User_Out();
    header('location: login.php');
}
?>

<html>

<head>
</head>

<body>

<div style="width:100%; float:left; ">
	<form method="post" style="display:inline">
	    <input type="submit" value="Log out" style="float:right" name="logout" /><br>
    </form>
</div>

<div style="float:left; width:100%">
	<div style="text-align:center">
	<form method="get" action="<?php echo '../App/SiriusApp.apk' ?>" enctype="application/vnd.android.package-archive" style="margin-top:20%">
		<input type="submit" value="Download App" style="height:100px; width:160px; font-size:20px;"/>
	</form>
	</div>
</div>


</body>
</html>
