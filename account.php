<?php
// start the session
session_start();
include './templates/header.php';

//Page is only accessible if user is logged in
if( isset($_SESSION['loggedin']) ){

	//because the user is logged in, user name is stored in session variable
	$username = $_SESSION['username'];

	//check if the user has just pressed the return book button, if so
	if( isset($_POST['retBook']) && isset($_POST['chkreturn'])){
		//loop through each checkbox's value (which holds book ids)
		foreach($_POST['chkreturn'] as $returned){
			//and remove that record from the transaction table
			try{
				$sql ="DELETE FROM `transactions` WHERE `bid` LIKE :bid AND `username` LIKE :username";
				$query = $db->prepare( $sql );
				$query->bindParam(':username', $username, PDO::PARAM_STR);
				$query->bindParam(':bid', $returned, PDO::PARAM_INT);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}

	}

	//check if the user has just pressed the checkout book button
	if( isset($_POST['chkOutBook']) && isset($_POST['chkout'])){
		//loop through each checkbox's value (which holds book ids)
		foreach($_POST['chkout'] as $checkout){
			//and add that record into transaction table 			
			try{
				$sql ="INSERT INTO `transactions` (`bid`, `username`) VALUES (:bid, :username)";
				$query = $db->prepare( $sql );
				$query->bindParam(':username', $username, PDO::PARAM_STR);
				$query->bindParam(':bid', $checkout, PDO::PARAM_INT);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}


	}

	//grab all the records from the user table, join it to patrons table, join that to the transactions table, then join that to the books table. This gives us all books taken out by a user, plus that user's first and last name, and library card
	try{
		$sql ="SELECT * FROM `users` INNER JOIN `patrons` ON (`users`.`username` = `patrons`.`username`) LEFT JOIN `transactions` ON (`transactions`.`username` = `users`.`username`) LEFT JOIN `books` ON (`transactions`.`bid` = `books`.`bid`) WHERE `users`.`username` LIKE :username";
		$query = $db->prepare( $sql );
		$query->bindParam(':username', $username, PDO::PARAM_STR);
		$query->execute();
		$result = $query->fetchAll( PDO::FETCH_ASSOC );
	} catch( PDOException $err ) { 

		echo $err->getMessage();

	}



	//if the user is not an admin, the My Account page shows more options, because this is the page where they do most of their interaction with the library
	if(!$_SESSION['is_admin']){
		echo "<h1>Welcome, " . $result[0]['firstname'] . "</h1><br />
		Your card number is " .  $result[0]['card_number'] . "<br />
		You have the following books out on loan:<br />

		<form method='POST' action='account.php'>";

		//$y is a counter to count how many books. This loops through every record in $result (i.e. all the books the user has taken out as listed in the transactions table) and echoes out the details of that book. It will append a checkbox to the end of the line and give it the value of the book id. This lets the user select multiple books to return to the library
		$y = 0;
		for($x = 0 ; $x < sizeof($result); $x++){
			if($result[$x]['title'] != null){
				echo $result[$x]['publication_type'] . ": <b>" . $result[$x]['title'] . "</b> by " . $result[$x]['author'] . "<input type='checkbox' name='chkreturn[]' value='". $result[$x]['bid'] ."'><br />";
				$y++;
			}
		}
	echo "$y results<br />";
?>

	<input type='submit' value='Return Book' name="retBook" />
	</form>

<?php

		
		echo "<h1>Books to check out</h1>
		<form method='POST' action='account.php'>";
		//select all the records in the books table which do not exist in the transactions table, i.e. books that have not been checked out by any user	
		try{
			$result = $db->query("SELECT * FROM `books` WHERE NOT EXISTS (SELECT `bid` FROM `transactions` WHERE `books`.`bid` = `transactions`.`bid`)");
			//loop through all the results, echo out details of the books, and add a checkbox at the end allowing the user to checkout multiple books at once
			while( $row = $result->fetch() ) {
				echo $row['publication_type'] . ": <b>" . $row['title'] . "</b> by " . $row['author'] . "<input type='checkbox' name='chkout[]' value='". $row['bid'] ."'><br />";
			}
		} catch( PDOException $err ) { 

			echo $err->getMessage();

		}
		echo "<input type='submit' value='Check Out' name='chkOutBook' /></form>";

		

	} //end check to see if user is an admin or not. All users, admin and non admin alike, will see the following code

	?>

		<h1>Change password</h1>
		<form method='POST' action='account.php'>
			<label for='password'>Password:</label>
			<input type='password' id='password' name='password' />
			<input type='submit' value='Change Password' name='chgPwd' />
		</form>

	<?
		//if the change password form above has been submitted
		if(isset($_POST['chgPwd'])){
			try{
				$username = $_SESSION['username'];
				$password = sha1($_POST['password']);
				//update the password to the password that the user t
				$sql ="UPDATE `users` SET `password`=:password WHERE `username`=:username";
				$query = $db->prepare( $sql );
				$query->bindParam(':password', $password, PDO::PARAM_STR);
				$query->bindParam(':username', $username, PDO::PARAM_STR);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}

} else {
	echo "<script>window.location.replace('index.php')</script>";
}


include './templates/footer.php';
?>
