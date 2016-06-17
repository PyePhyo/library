<?php
// start the session
session_start();
include './templates/header.php';

//if the user is already logged in, redirect to the My Account page
if( isset($_SESSION['loggedin']) ){
	echo "<script>window.location.replace('account.php')</script>";
} else {

	try{
		//if the user name and password have already been passed to the page via POST
		if ( isset($_POST['username']) && isset($_POST['password']) ){
			//assign both to variable (the hashed version of the password)
			$username = $_POST['username'];
			$password = sha1($_POST['password']);

			//grab the username and password of the user with username $username
			$sql ="SELECT * FROM `users` WHERE `username` LIKE :username AND `password` LIKE :password";
			$query = $db->prepare( $sql );
			$query->bindParam(':username', $username, PDO::PARAM_STR);
			$query->bindParam(':password', $password, PDO::PARAM_STR);
			$query->execute();
			$result = $query->fetch( PDO::FETCH_ASSOC );

			//and test the hash against the hash stored in the database. If true
			if ($password == $result['password']){
				//session variables are created to indicate that the user has been logged in,  whatever their admin status is, and to store their username (since this is used in so many places)
				$_SESSION['loggedin'] = true;
				$_SESSION['username'] = $username;	
				$_SESSION['is_admin'] = $result['is_admin'];


				//then redirect to the My Account Page
				echo "<script>window.location.replace('account.php')</script>";
			}
			
		}

	} catch( PDOException $err ) { 

		echo $err->getMessage();

	}
}


?>
<h1>Please Login</h1>
<form method='POST' action='index.php'>
	<label for='username'>Username:</label>
	<input id='username' name='username' />
	<label for='password'>Password:</label>
	<input type='password' id='password' name='password' />
	<input type='submit' value='GO!' />
</form>
<?php

include './templates/footer.php';
?>