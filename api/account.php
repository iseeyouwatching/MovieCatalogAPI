<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		if ($method == "POST") {
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
				default:
					break;
			}
		}
		else {
			"404";
		}
	}
