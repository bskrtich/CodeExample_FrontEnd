<?php


	// Ensures that numeric types returned from the database are the appropriate type since depending on the driver and settings fetchObject will return all fields as strings
	class User{
		function __construct(){
			if(isset($this->userID))
			$this->userID = (int) $this->userID;
		}
	}


	function getUserByID($userID){
		global $sqlCon;
		
		$query = 'SELECT userID, username ' .
			'FROM users ' .
			'WHERE userID = :userID';
			
		$prepared = $sqlCon->prepare($query);
		$prepared->bindValue(':userID', $userID);
		sqlQuery($prepared);
		
		$user = $prepared->fetchObject('User');
		
		if(!$user) return false;
		
		return $user;
	}

	
	// Individual API methods are currently defined below, feel free to reorganize this to something more sensible if the number of API calls gets large
	switch($method){
		case 'latestmsgs' :
			$sql = "SELECT * FROM messages JOIN subscriptions ON messages.user_id = subscriptions.following_user_id WHERE subscriptions.user_id = 1";
			
			$result = $sqlCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			apiResult($result);
			break;
		
		case 'newmsg' :
			$result = new stdClass();
			$result->newmsg = 'My New Message';
			
			apiResult($result);
			break;
			
		case 'GetUserInfo' :
			$user = getUserByID($userInfo->userID);
			
			if(!$user) apiError('NoUser', 'User does not exist with userID: ' . $userInfo->userID);
		
			$result = new stdClass();
			$result->user = $user;
			
			apiResult($result);
			break;
			
		case 'AddUser' :
			$username = requiredParameter('username', 'string');
			$password = optionalParameter('password', 'string', substr(sha1(microtime()), 0, 8));
			
			$salt = hash('sha512', str_shuffle($username . microtime() . rand(0, 9999999) . $password));
			$hashed = hash('sha512', $salt . $password);
		
			$query = 'INSERT IGNORE INTO users ' .
				'(username, password, salt) ' .
				'VALUES (:username, :password, :salt)';
				
			$prepared = $sqlCon->prepare($query);
			$prepared->bindValue(':username', $username);
			$prepared->bindValue(':password', $hashed);
			$prepared->bindValue(':salt', $salt);
			sqlQuery($prepared);

			apiResult();
			break;
			
		case 'users' :
			$sql = "SELECT id, username FROM users ORDER BY username";
			$result = $sqlCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			apiResult($result);
			break;
		default: 
			echo $request;
	}

?>