<?php
// start the session
session_start();
include './templates/header.php';

if( isset($_SESSION['loggedin']) && $_SESSION['is_admin'] ){

	//check if the user has just pressed the delete book button, if so
	if( isset($_POST['chkdelete']) && isset($_POST['deleteBook'])){
		//loop through each checkbox's value (which holds book ids)
		foreach($_POST['chkdelete'] as $returned){
			//and remove that record from the books table
			try{
				$sql ="DELETE FROM `transactions` WHERE `bid` LIKE :bid";
				$query = $db->prepare( $sql );
				$query->bindParam(':bid', $_POST['bid'], PDO::PARAM_INT);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}

	}

	//if the user pressed the return book button and any return book checkbox has been selected
	if( isset($_POST['retbook']) && isset($_POST['returnBook'])){
		//loop through each checkbox's value (which holds book ids)
		foreach($_POST['retbook'] as $returned){
			//and remove that record from the transaction table
			$arr = explode("&", $returned);
			try{
				$sql ="DELETE FROM `transactions` WHERE `bid` LIKE :bid AND `username` LIKE :username";
				$query = $db->prepare( $sql );
				$query->bindParam(':bid', $arr[0], PDO::PARAM_INT);
				$query->bindParam(':username', $arr[1], PDO::PARAM_STR);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}

	}

	if( isset($_POST['deluser']) && isset($_POST['delUser'])){
		//loop through each checkbox's value (which holds book ids)
		foreach($_POST['deluser'] as $returned){
			//and remove that record from the transaction table
			try{
				$sql ="DELETE FROM `users` WHERE `username` LIKE :username";
				$query = $db->prepare( $sql );
				$query->bindParam(':username', $returned, PDO::PARAM_STR);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
			//also delete from transaction table
			try{
				$sql ="DELETE FROM `transactions` WHERE `username` LIKE :username";
				$query = $db->prepare( $sql );
				$query->bindParam(':username', $returned, PDO::PARAM_STR);
				$query->execute();
			} catch( PDOException $err ) { 

				echo $err->getMessage();

			}
		}

	}

	//if the user to add is an admin, and the adduser button has been checked and a username/password have been filled in
	if( isset($_POST['is_admin'])  && $_POST['is_admin'] == "is_admin" && isset($_POST['addUser']) && isset($_POST['username']) && isset($_POST['password']) ){
		//hash the password
		$password = sha1($_POST['password']);
		$is_admin = 1;
		try{
			//and insert into the users table ONLY
			$sql ="INSERT INTO `users` (`username`, `password`, `is_admin`) VALUES (:username, :password, :is_admin)";
			$query = $db->prepare( $sql );
			$query->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
			$query->bindParam(':password', $password, PDO::PARAM_STR);
			$query->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
			$query->execute();
		} catch( PDOException $err ) { 

			echo $err->getMessage();

		}
	}

	//if is_admin checkbox has NOT been selected, and the add user button has been clicked and username/password have been filled in
	if(isset($_POST['addUser']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['cardnumber'])){
		//hash the password
		$password = sha1($_POST['password']);
		$is_admin = 0;
		//user to be added is not an admin so add to the users table
		try{
			$sql ="INSERT INTO `users` (`username`, `password`, `is_admin`) VALUES (:username, :password, :is_admin)";
			$query = $db->prepare( $sql );
			$query->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
			$query->bindParam(':password', $password, PDO::PARAM_STR);
			$query->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
			$query->execute();
		} catch( PDOException $err ) { 

		echo $err->getMessage();

		}
		//and also add to the patrons table
		try{
			$sql ="INSERT INTO `patrons` (`username`, `firstname`, `lastname`, `card_number`) VALUES (:username, :firstname, :lastname, :cardnumber)";
			$query = $db->prepare( $sql );
			$query->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
			$query->bindParam(':firstname', $_POST['firstname'], PDO::PARAM_STR);
			$query->bindParam(':lastname', $_POST['lastname'], PDO::PARAM_STR);
			$query->bindParam(':cardnumber', $_POST['cardnumber'], PDO::PARAM_INT);
			$query->execute();
		} catch( PDOException $err ) { 

		echo $err->getMessage();

		}
	}

	//if all the requirements are met to add the book
	if( isset($_POST['addBook']) && isset($_POST['title']) && isset($_POST['author']) && isset($_POST['publication_type']) && isset($_POST['pages']) ){
		//loop through each checkbox's value (which holds book info)
		try{
			$sql ="INSERT INTO `books` (`title`, `author`, `pages`, `publication_type`) VALUES (:title, :author, :pages, :publication_type)";
			$query = $db->prepare( $sql );
			$query->bindParam(':title', $_POST['title'], PDO::PARAM_STR);
			$query->bindParam(':author', $_POST['author'], PDO::PARAM_STR);
			$query->bindParam(':pages', $_POST['pages'], PDO::PARAM_INT);
			$query->bindParam(':publication_type', $_POST['publication_type'], PDO::PARAM_INT);
			$query->execute();
		} catch( PDOException $err ) { 

			echo $err->getMessage();

		}
	}

	


	echo "<h1>Books currently signed out from the library</h1>
	<form method='POST' action='admin.php'>";
	//select from the users table, join it to the matching entries in the patrons table based on username, then join those results and transactions tables based on the username
	try{
		$result = $db->query("SELECT * FROM `users` LEFT JOIN `patrons` ON (`users`.`username` = `patrons`.`username`) LEFT JOIN `transactions` ON (`transactions`.`username` = `users`.`username`) LEFT JOIN `books` ON (`transactions`.`bid` = `books`.`bid`)");
		//loop through all the results, echo out details of the books, and add a checkbox at the end allowing the user to checkout multiple books at once
		$patron_prev = "0";
		$patron_current ="1";
		while( $row = $result->fetch() ) {

			$patron_prev = $row['username'];
			if($patron_current != $patron_prev && $patron_current != null){
				echo "<br>Patron " . $row['firstname'] . " " . $row['lastname'] . " (" . $row['username'] . " " .$row['card_number'] . ") has the following books out on loan:<br />";
				$patron_current = $row['username'];
			}
			if ($row['author'] != null){
				echo $row['publication_type'] . ": <b>" . $row['title'] . "</b> by " . $row['author'] . "<input type='checkbox' name='retbook[]' value='". $row['bid'] ."&" . $row['username'] ."''><br />";
			//} else if ($patron_current != $patron_prev && $row['author'] == null) {
			//	echo "No books currently out.";
			}
		}
		

	} catch( PDOException $err ) { 

		echo $err->getMessage();

	}

	echo "<input type='submit' value='Return Book' name='returnBook' /></form>";

	echo "<h1>Books currently available in the library</h1>
	<form method='POST' action='admin.php'>";
	//select all the records in the books table which do not exist in the transactions table, i.e. books that have not been checked out by any user	
	try{
		$result = $db->query("SELECT * FROM `books` WHERE NOT EXISTS (SELECT `bid` FROM `transactions` WHERE `books`.`bid` = `transactions`.`bid`)");
		//loop through all the results, echo out details of the books, and add a checkbox at the end allowing the user to checkout multiple books at once
		while( $row = $result->fetch() ) {
			echo $row['publication_type'] . ": <b>" . $row['title'] . "</b> by " . $row['author'] . "<input type='checkbox' name='chkdelete[]' value='". $row['bid'] . "'><br />";
		}
	} catch( PDOException $err ) { 

		echo $err->getMessage();

	}
	echo "<input type='submit' value='Remove Book Permanently From The Library' name='deleteBook' /></form>";


	?>
	<h1>Create new book</h1>
	<form method='POST' action='admin.php'>
	<label for='title'>Title:</label>
	<input id='title' name='title' /><br />
	<label for='author'>Author:</label>
	<input id='author' name='author' /><br />
	<label for='publication_type'>Publication Type:</label>
	<input id='publication_type' name='publication_type' /><br />
	<label for='pages'>Number of Pages:</label>
	<input id='pages' name='pages' /><br />
	<input type='submit' value='Add Book' name='addBook' /></form>
	

	<h1>Delete users</h1>
	<form method='POST' action='admin.php'>

	<?php
	
		try{
		$result = $db->query("SELECT * FROM `users` JOIN `patrons` ON (`users`.`username` = `patrons`.`username`)");
		//loop through all the results, echo out details of the users, and add a checkbox at the end allowing the librarian to delete multiple users at once
		while( $row = $result->fetch() ) {
			echo $row['firstname'] . " " . $row['lastname'] . " (" . $row['username'] . " " .$row['card_number'] . ")<input type='checkbox' name='deluser[]' value='". $row['username'] ."'><br />";
				$patron_current = $row['username'];
		}
		

		} catch( PDOException $err ) { 

			echo $err->getMessage();

		}

	echo "<input type='submit' value='Delete Users Permanently From The Library' name='delUser' /></form>";

	?>
	
	<h1>Create new users</h1>
	<form method='POST' action='admin.php'>
	<label for='username'>User Name:</label>
	<input id='username' name='username' /><br />
	<label for='password'>Password:</label>
	<input id='password' name='password' /><br />
	<label for='firstname'>First Name:</label>
	<input id='firstname' name='firstname' /><br />
	<label for='lastname'>Last Name:</label>
	<input id='lastname' name='lastname' /><br />
	<label for='cardnumber'>Library Card Number:</label>
	<input id='cardnumber' name='cardnumber' /><br />
	<label for='is_admin'>Is Admin?:</label>
	<input type='checkbox' name='is_admin' value='is_admin'><br />
	<input type='submit' value='Add User' name='addUser' /></form>

	<?php


} else {
	echo "<script>window.location.replace('index.php')</script>";
}


include './templates/footer.php';
?>