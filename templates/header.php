<?php
// local 
/*
$db = new PDO('mysql:host=localhost:8889;dbname=library;charset=utf8', 'root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
// mobile.sheridan
*/$db = new PDO('mysql:host=localhost;dbname=bakhta;charset=utf8', 'username', 'password');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Library</title>
</head>
<body>
<nav>
<?php
	$fileName = explode( "/", $_SERVER['SCRIPT_FILENAME'] );
	$page = end($fileName);
	switch ($page) {
		case "index.php":
			echo "<b><a href='index.php'>Login</a></b> | <a href='checkout.php'>My Account</a> | <a href='admin.php'>Administration</a> | <a href='logout.php'>Logout</a>";
			break;
		case "account.php":
			echo "<a href='index.php'>Login</a> | <b><a href='account.php'>My Account</a></b> | <a href='admin.php'>Administration</a> | <a href='logout.php'>Logout</a>";
			break;
		case "logout.php":
			echo "<a href='index.php'>Login</a> | <a href='checkout.php'>Check out book</a> | <a href='admin.php'>Administration</a> | <b><a href='logout.php'>Logout</a></b>";
		break;
		case "admin.php":
			echo "<a href='index.php'>Login</a> | <a href='checkout.php'>Check out book</a> | <b><a href='admin.php'>Administration</a></b> | <a href='logout.php'>Logout</a>";
		break;

	} 
?>	
</nav>
<div>


