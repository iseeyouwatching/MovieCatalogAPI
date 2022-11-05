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
					$movieID = $urlList[2];
					if (!$review) {
						$isValidated = true;
						$validationErrors = [];
						$reviewText = $requestData->body->reviewText;
						$rating = $requestData->body->rating;
						if ($rating < 0 || $rating > 10 || !is_int($rating)) {
							$validationErrors[] = ["Rating" => 'The field Rating must be between 0 and 10'];
							$isValidated = false;
						}
						$isAnonymous = $requestData->body->isAnonymous;
						if (!is_bool($isAnonymous)) {
							$validationErrors[] = ["IsAnonymous" => 'The field IsAnonymous can only be true or false'];
							$isValidated = false;
						}
						if (!$isValidated) {
							$messageResult = array(
								'message' => 'Adding review is failed',
								'errors' => []
							);
							$messageResult['errors'] = $validationErrors;
							setHTTPStatus('400', $messageResult);
							return;
						}
						$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
						$nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z';
						$isAnonymous = (int)$isAnonymous;
						$reviewInsertResult = $link->query("INSERT INTO reviews(review_id, user_id, movie_id, review_text, rating, is_anonymous, create_datetime) 
														VALUES(UUID(), '$userID', '$movieID', '$reviewText', '$rating', '$isAnonymous', '$nowFormatted')");
					}
					else {
						$reviewID = $review['review_id'];
						setHTTPStatus('409', "User with this '$userID' identifier already had review with this identifier '$reviewID' for this movie");
					}
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			case "PUT":
				if (count($urlList) != 6) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$reviewID = $urlList[4];
					$reviewCheck = $link->query("SELECT review_id FROM reviews WHERE review_id='$reviewID' AND user_id='$userID'")->fetch_assoc();
					$movieCheck = $link->query("SELECT review_id FROM reviews WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
					if (!$movieCheck) {
						setHTTPStatus('404','The user has no review on this movie');
						return;
					}
					if (!$reviewCheck) {
						setHTTPStatus('403','User cannot change foreign review');
						return;
					}
					$isValidated = true;
					$validationErrors = [];
					$reviewText = $requestData->body->reviewText;
					$rating = $requestData->body->rating;
					if ($rating < 0 || $rating > 10 || !is_int($rating)) {
						$validationErrors[] = ["Rating" => 'The field Rating must be between 0 and 10'];
						$isValidated = false;
					}
					$isAnonymous = $requestData->body->isAnonymous;
					if (!is_bool($isAnonymous)) {
						$validationErrors[] = ["IsAnonymous" => 'The field IsAnonymous can only be true or false'];
						$isValidated = false;
					}
					if (!$isValidated) {
						$messageResult = array(
							'message' => 'Adding review is failed',
							'errors' => []
						);
						$messageResult['errors'] = $validationErrors;
						setHTTPStatus('400', $messageResult);
						return;
					}
					$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
					$nowFormatted = substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z';
					$isAnonymous = (int)$isAnonymous;
					$reviewUpdateResult = $link->query("UPDATE reviews SET review_text='$reviewText', rating='$rating', is_anonymous='$isAnonymous', create_datetime='$nowFormatted' 
               									WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
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
