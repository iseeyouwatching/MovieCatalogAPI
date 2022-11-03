<?php

	require_once "database_connection.php";
	require_once "jwt.php";
	require_once "helpers/validation.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch($method) {
			case "POST":
				switch ($urlList[2]) {
					case 'register':
						$isValidated = true;
						$validationErrors = [];
						$username = $requestData->body->userName;
						if (!validateStringNotLess($username, 5) && !is_null($username) && $username != "") {
							$isValidated = false;
							$validationErrors[] = ["Login" => "Login must be at least 5 characters"];
						}
						if (!validateStringDoesnotContainsSpecialSymbols($username) && !is_null($username) && $username == "") {
							$isValidated = false;
							$validationErrors[] = ["Login" => "Login '" . $username . "' is invalid, can only contains letters or digits"];
						}
						if ($username == "") {
							$isValidated = false;
							$validationErrors[] = ["Login" => "The Login field is required"];
						}
						$name = $requestData->body->name;
						if ($name == "") {
							$isValidated = false;
							$validationErrors[] = ["Name" => "The Name field is required"];
						}
						if (!validateStringNotLess($requestData->body->password, 6) && !is_null($requestData->body->password) && $requestData->body->password != "") {
							$isValidated = false;
							$validationErrors[] = ["Password" => "Password must be at least 6 characters"];
						}
						if ($requestData->body->password == "") {
							$isValidated = false;
							$validationErrors[] = ["Password" => "The Password field is required"];
						}
						$password = hash("sha1", $requestData->body->password);
						$email = $requestData->body->email;
						if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !is_null($email) && $email != "") {
							$isValidated = false;
							$validationErrors[] = ["Email" => "Invalid Email address"];
						}
						if ($email == "") {
							$isValidated = false;
							$validationErrors[] = ["Email" => "The Email field is required"];
						}
						$birthdate = $requestData->body->birthDate;
						$gender = $requestData->body->gender;
						if (!$isValidated) {
							$messageResult = array(
								'message' => 'User Registration Failed',
								'errors' => []
							);
							$messageResult['errors'] = $validationErrors;
							setHTTPStatus('400', $messageResult);
							return;
						}
						$userInsertResult = $link->query("INSERT INTO users(user_id,  username, name, password, email, birthdate, gender) 
															VALUES(UUID(), '$username', '$name', '$password', '$email', '$birthdate', '$gender')");
						if (!$userInsertResult) {
							$messageResult = array(
								'message' => 'User Registration Failed',
								'error' => []
							);
							if ($link->error == "Duplicate entry '" . $username . "' for key 'users.username'") {
								$messageResult['error'] = "Login '" . $username . "' is already taken";
								setHTTPStatus('409', $messageResult);
								return;
							}
							if ($link->error == "Duplicate entry '" . $email . "' for key 'users.email'") {
								$messageResult['error'] = "Email '" . $email . "' is already taken";
								setHTTPStatus('409', $messageResult);
								return;
							}
						}
						else {
							$payload = [
								'unique_name' => $username,
								'email' => $email,
							];

							$secret = bin2hex(random_bytes(32));;
							$token = generateToken($payload, $secret);

							echo json_encode(['token' => $token]);
						}
						break;
					case 'login':
						$username = $requestData->body->username;
						$password = hash("sha1", $requestData->body->password);
						if (is_null($username) && is_null($requestData->body->password)) {
							setHTTPStatus('500');
							return;
						}
						$user = $link->query("SELECT email FROM users WHERE username='$username' AND password='$password'")->fetch_assoc();
						if ($user) {
							$payload = [
								'unique_name' => $username,
								'email' => $user['email'],
							];

							$secret = bin2hex(random_bytes(32));;
							$token = generateToken($payload, $secret);

							echo json_encode(['token' => $token]);
						}
						else {
							setHTTPStatus('400', 'Login failed');
						}
						break;
					case 'logout':
						$token = substr(getallheaders()['Authorization'], 7);
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
						$userID = $user['user_id'];
						$tokenInsertResult = $link->query("INSERT INTO tokens(token_id, value, user_id) VALUES(UUID(), '$token','$userID')");

						if ($tokenInsertResult) {
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
					$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
					if (!isExpired($token) && $isLogoutToken == null) {
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
						if ($user) {
							$userID = $user['user_id'];
							$user = $link->query("SELECT * FROM users WHERE user_id='$userID'")->fetch_assoc();
							$result = array(
								'id' => $user['user_id'],
								'nickName'=> $user['username'],
								'email' => $user['email'],
								'avatarLink'=> $user['avatarLink'],
								'name' => $user['name'],
								'birthDate' => $user['birthdate'],
								'gender' => intval($user['gender'])
							);
							echo json_encode($result);
						} else {
							echo "400: input data incorrect";
						}
					}
					else {
						echo "401: unauthorized";
					}
				}
				else {
					echo "404";
				}
				break;
			case "PUT":
				if ($urlList[2] == "profile") {
					$token = substr(getallheaders()['Authorization'], 7);
					$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
					if (!isExpired($token) && $isLogoutToken == null) {
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
						if ($user) {
							$userID = $user['user_id'];
							$email = $requestData->body->email;
							$avatarLink = $requestData->body->avatarLink;
							$name = $requestData->body->name;
							$birthdate = $requestData->body->birthDate;
							$gender = $requestData->body->gender;
							$userUpdateResult = $link->query("UPDATE users SET email='$email', avatarLink='$avatarLink', name='$name', birthdate='$birthdate', gender='$gender' WHERE user_id='$userID'");
							if (!$userUpdateResult) {
								echo "400: bad request";
							}
						}
						else {
							echo "400: input data incorrect";
						}
					}
					else {
						echo "401: unauthorized";
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
