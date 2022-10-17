<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		if ($method == "POST") {
			global $link;
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
		}
		else {
			"404";
		}
	}
