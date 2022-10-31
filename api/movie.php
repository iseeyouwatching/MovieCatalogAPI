<?php

	require_once "database_connection.php";
	require_once "jwt.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "POST":
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$review = $link->query("SELECT review_id FROM reviews WHERE user_id='$userID'")->fetch_assoc();
					if (!$review) {
						$reviewText = $requestData->body->reviewText;
						$rating = $requestData->body->rating;
						$isAnonymous = $requestData->body->rating;
						$movieID = $urlList[2];
						$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
						$nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z';
						$reviewInsertResult = $link->query("INSERT INTO reviews(review_id, user_id, movie_id, review_text, rating, is_anonymous, create_datetime) 
														VALUES(UUID(), '$userID', '$movieID', '$reviewText', '$rating', '$isAnonymous', '$nowFormatted')");
						if (!$reviewInsertResult) {
							echo "too bad";
						} else {
							echo "success";
						}
					}
					else {
						echo json_encode(['message' => "User has already had review for this movie"]);
					}
				}
				else {
					echo "401: unauthorized";
				}
				break;
			case "PUT":
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$reviewID = $urlList[4];
					$reviewText = $requestData->body->reviewText;
					$rating = $requestData->body->rating;
					$isAnonymous = $requestData->body->rating;
					$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
					$nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z';
					$reviewUpdateResult = $link->query("UPDATE reviews SET review_text='$reviewText', rating='$rating', is_anonymous='$isAnonymous', create_datetime='$nowFormatted' 
               									WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
					if (!$reviewUpdateResult) {
						echo "bad";
					}
				}
				else {
					echo "401: unauthorized";
				}
				break;
			case "DELETE":
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$reviewID = $urlList[4];
					$checkIsExist = $link->query("SELECT user_id, movie_id FROM reviews WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
					if ($checkIsExist) {
						$reviewDeleteResult = $link->query("DELETE FROM reviews WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
					}
					else {
						echo "404 not found";
					}
				}
				else {
					echo "401: unauthorized";
				}
				break;
			default:
				echo "404";
				break;
		}
	}
