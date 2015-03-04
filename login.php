<?php

require_once 'Membership.php';
$membership = new Membership();
//ffsdfd
if(isset($_GET['status']) && $_GET['status'] == 'loggedout'){
	$membership->log_User_Out();
}
// did the user enter username and password?
if( $_POST && !empty($_POST['username']) && !empty($_POST['pwd']) ) {
	$response = $membership->validate_User($_POST['username'], $_POST['pwd']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login screen</title>
<link rel="shortcut icon" href="icon.ico">
</head>

<body>

<div style="background:green;width:400px;border-style:solid;margin-right:auto;margin-left:auto; margin-top:150px">
	<form method="post" action="">
	<h2 style="text-align:center">Login <small>enter your credentials</small></h2>

	<p style="text-align:center">
		<label for="name">Username: </label>
		<input type="text" name="username" />
	</p>

	<p style="text-align:center">
		<label for="pwd">Password: </label>
		<input type="password" name="pwd" />
	</p>

	<p style="text-align:center">
		<input type="submit" value="Login" name="submit"/>
	</p>
	</form>
	<?php if(isset($response)) echo "<h4 class='alert'>" . $response . "</h4>" ?>
</div>
</body>
</html>
