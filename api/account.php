<?php

	require_once "helpers/jwt.php";
	require_once "helpers/validation.php";
	require_once "helpers/user/check_user_data.php";
	require_once "helpers/user/email_duplicate_check.php";
	require_once "helpers/user/login_duplicate_check.php";

	function route($method, $urlList, $requestData): void {
		global $link;
		if (count($urlList) == 3) {
			switch ($method) {
				case "POST":
					switch ($urlList[2]) {
						case 'register':
							$username = $requestData->body->userName;
							$name = $requestData->body->name;
							$password = hash("sha1", $requestData->body->password);
							$email = $requestData->body->email;
							$birthdate = str_replace(["T", "Z"], " ", trim($requestData->body->birthDate));
							$gender = $requestData->body->gender;
							if (!isValidRegistrationUserData($username, $name, $requestData->body->password, $email, $birthdate)) {
								return;
							}
							$userInsertResult = $link->query("INSERT INTO users(user_id,  username, name, password, email, birthdate, gender, isAdmin) 
															VALUES(UUID(), '$username', '$name', '$password', '$email', '$birthdate', '$gender', false)");
							if (!$userInsertResult) {
								if (checkUsernameDuplicates($username) || checkEmailDuplicates($email) || checkBirthdate($birthdate)) {
									return;
								}
							}
							else {
								echo json_encode(['token' => generateToken($username, $email)]);
							}
							break;
						case 'login':
							$username = $requestData->body->username;
							$password = hash("sha1", $requestData->body->password);
							$user = $link->query("SELECT email FROM users WHERE username='$username'")->fetch_assoc();
							if ($user) {
								echo json_encode(['token' => generateToken($username, $user['email'])]);
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
							$checkToken = $link->query("SELECT token_id FROM tokens WHERE value='$token'")->fetch_assoc();
							if ($checkToken) {
								setHTTPStatus('409', "From this '$token' token has already logged out");
								return;
							}
							$tokenInsertResult = $link->query("INSERT INTO tokens(token_id, value, user_id) VALUES(UUID(), '$token','$userID')");
							if ($tokenInsertResult) {
								$messageResult = array('token' => $token, 'message' => 'Logged Out');
								setHTTPStatus('200', $messageResult);
							}
							break;
						default:
							break;
					}
					break;
				case "GET":
					if ($urlList[2] == "register" || $urlList[2] == "login" || $urlList[2] == "logout") {
						setHTTPStatus('405', "Method '$method' not allowed");
						break;
					}
					if ($urlList[2] == "profile") {
						$token = substr(getallheaders()['Authorization'], 7);
						$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
						if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
							$usernameFromToken = getPayload($token)['unique_name'];
							$user = $link->query("SELECT * FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
							$result = array(
								'id' => $user['user_id'],
								'nickName' => $user['username'],
								'email' => $user['email'],
								'avatarLink' => $user['avatarLink'],
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
					if ($urlList[2] == "register" || $urlList[2] == "login" || $urlList[2] == "logout") {
						setHTTPStatus('405', "Method '$method' not allowed");
						break;
					}
					if ($urlList[2] == "profile") {
						$token = substr(getallheaders()['Authorization'], 7);
						$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
						if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
							$usernameFromToken = getPayload($token)['unique_name'];
							$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
							$userID = $user['user_id'];
							$email = $requestData->body->email;
							$avatarLink = $requestData->body->avatarLink;
							$name = $requestData->body->name;
							$birthdate = str_replace(["T", "Z"], " ", trim($requestData->body->birthDate));
							$gender = $requestData->body->gender;
							if (!isValidUpdateUserData($name, $birthdate, $email, $gender)) {
								return;
							}
							$userUpdateResult = $link->query("UPDATE users SET email='$email', avatarLink='$avatarLink', name='$name', birthdate='$birthdate', gender='$gender' WHERE user_id='$userID'");
							if (!$userUpdateResult) {
								if (checkEmailDuplicates($email) || checkBirthdate($birthdate)) {
									return;
								}
							}
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
					setHTTPStatus('405', "Method '$method' not allowed");
					break;
			}
		}
		else {
			setHTTPStatus('404', 'Missing resource is requested');
		}
	}

