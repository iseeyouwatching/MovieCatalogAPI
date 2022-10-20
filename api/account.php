<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch($method) {
			case "POST":
				switch ($urlList[2]) {
					case 'register':
						$username = $requestData->body->username;
						$user = $link->query("SELECT user_id FROM users WHERE username='$username'")->fetch_assoc();
						if (!$user) {
							$name = $requestData->body->name;
							$password = hash("sha1", $requestData->body->password);
							$email = $requestData->body->email;
							$birthdate = $requestData->body->birthdate;
							$gender = $requestData->body->gender;
							$userInsertResult = $link->query("INSERT INTO users(username, name, password, email, birthdate, gender) 
															VALUES('$username', '$name', '$password', '$email', '$birthdate', '$gender')");
							if (!$userInsertResult) {
								echo "too bad";
							}
							else {
								echo "success";
							}
						}
						else {
							echo "user exist";
						}
						break;
					case 'login':
						$username = $requestData->body->username;
						$password = hash("sha1", $requestData->body->password);
						$user = $link->query("SELECT user_id FROM users WHERE username='$username' AND password='$password'")->fetch_assoc();

						if ($user) {
							$token = bin2hex(random_bytes(32));
							$userID = $user['user_id'];
							$tokenInsertResult = $link->query("INSERT INTO tokens(value, user_id) VALUES('$token', '$userID')");

							if (!$tokenInsertResult) {
								echo "bad";
							}
							else {
								echo json_encode(['token' => $token]);
							}
						}
						else {
							echo "400: input data incorrect";
						}
						break;
					case 'logout':
						$token = substr(getallheaders()['Authorization'], 7);
						$tokenDeleteResult = $link->query("DELETE FROM tokens WHERE value='$token'");

						if ($tokenDeleteResult) {
							echo "200: success";
						}
						else {
							echo "bad";
						}
						break;
					default:
						break;
				}
				break;
			case "GET":
				if ($urlList[2] == "profile") {
					$token = substr(getallheaders()['Authorization'], 7);
					$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
					if ($userFromToken) {
						$userID = $userFromToken['user_id'];
						$user = $link->query("SELECT * FROM users WHERE user_id='$userID'")->fetch_assoc();
						echo json_encode($user);
					}
					else {
						echo "400: input data incorrect";
					}
				}
				else {
					echo "404";
				}
				break;
			case "PUT":
				if ($urlList[2] == "profile") {
					$token = substr(getallheaders()['Authorization'], 7);
					$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
					if ($userFromToken) {
						$userID = $userFromToken['user_id'];
						$user = $link->query("SELECT username, email, avatarLink, name, birthDate, gender FROM users WHERE user_id='$userID'")->fetch_assoc();
						$username = $requestData->body->username;
						$email = $requestData->body->email;
						$avatarLink = $requestData->body->avatarLink;
						$name = $requestData->body->name;
						$birthdate = $requestData->body->birthDate;
						$gender = $requestData->body->gender;
						$userUpdateResult = $link->query("UPDATE users SET username='$username', email='$email', avatarLink='$avatarLink', name='$name', birthdate='$birthdate', gender='$gender' WHERE user_id='$userID'");
						if (!$userUpdateResult) {
							echo "bad";
						}
					}
					else {
						echo "400: input data incorrect";
					}
				}
				else {
					echo "404";
				}
				break;
			default:
				echo "404";
				break;
		}
	}
