<?php

	require_once "database_connection.php";
	require_once "jwt.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch($method) {
			case "POST":
				switch ($urlList[2]) {
					case 'register':
						$username = $requestData->body->userName;
						$user = $link->query("SELECT user_id FROM users WHERE username='$username'")->fetch_assoc();
						if (!$user) {
							$name = $requestData->body->name;
							$password = hash("sha1", $requestData->body->password);
							$email = $requestData->body->email;
							$birthdate = $requestData->body->birthDate;
							$gender = $requestData->body->gender;
							$userInsertResult = $link->query("INSERT INTO users(user_id, nickname, username, name, password, email, birthdate, gender) 
															VALUES(UUID(),'$username', '$username', '$name', '$password', '$email', '$birthdate', '$gender')");
							if (!$userInsertResult) {
								echo "too bad";
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
						}
						else {
							echo "user exist";
						}
						break;
					case 'login':
						$username = $requestData->body->username;
						$password = hash("sha1", $requestData->body->password);
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
							echo "400: input data incorrect";
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
				if ($urlList[2] == "profile" && count($urlList) == 3) {
					$token = substr(getallheaders()['Authorization'], 7);
					$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
					if (!isExpired($token) && $isLogoutToken == null) {
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT * FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
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
					}
					else {
						setHTTPStatus('401', 'Token not specified or not valid');
					}
				}
				else {
					setHTTPStatus('404', 'Missing resource is requested');
				}
				break;
			case "PUT":
				if ($urlList[2] == "profile"  && count($urlList) == 3) {
					$token = substr(getallheaders()['Authorization'], 7);
					$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
					if (!isExpired($token) && $isLogoutToken == null) {
						$isValidated = true;
						$validationErrors = [];
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
						$userID = $user['user_id'];
						$email = $requestData->body->email;
						if ($email == "") {
							$isValidated = false;
							$validationErrors[] = ["Email" => "The Email field is required"];
						}
						if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !is_null($email) && $email != "") {
							$isValidated = false;
							$validationErrors[] = ["Email" => "Invalid Email address"];
						}
						$avatarLink = $requestData->body->avatarLink;
						$name = $requestData->body->name;
						if ($name == "") {
							$isValidated = false;
							$validationErrors[] = ["Name" => "The Name field is required"];
						}
						$birthdate = $requestData->body->birthDate;
						$gender = $requestData->body->gender;
						if ($gender != 0 && $gender != 1) {
							$isValidated = false;
							$validationErrors[] = ["Gender" => "We have only two genders"];
						}
						if (!$isValidated) {
							$messageResult = array(
								'message' => 'User Registration Failed',
								'errors' => []
							);
							$messageResult['errors'] = $validationErrors;
							setHTTPStatus('400', $messageResult);
							return;
						}
						$userUpdateResult = $link->query("UPDATE users SET email='$email', avatarLink='$avatarLink', name='$name', birthdate='$birthdate', gender='$gender' WHERE user_id='$userID'");
					}
					else {
						setHTTPStatus('401', 'Token not specified or not valid');
					}
				}
				else {
					setHTTPStatus('404', 'Missing resource is requested');
				}
				break;
			default:
				echo "404";
				break;
		}
	}
