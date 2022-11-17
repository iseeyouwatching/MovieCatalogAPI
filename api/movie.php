<?php

	require_once "helpers/jwt.php";
	require_once "helpers/reviews/check_review_data.php";
	require_once "helpers/reviews/movie_and_review_check.php";

	function route($method, $urlList, $requestData): void {
		global $link;
		switch ($method) {
			case "POST":
				if (count($urlList) != 5) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$checkIsMovieExist = $link->query("SELECT * FROM movies WHERE movie_id='$movieID'")->fetch_assoc();
					if (!$checkIsMovieExist) {
						setHTTPStatus('404',"There is no movie with this '$movieID' identifier");
						return;
					}
					$review = $link->query("SELECT review_id FROM reviews WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
					if (!$review) {
						$reviewText = $requestData->body->reviewText;
						$rating = $requestData->body->rating;
						$isAnonymous = $requestData->body->isAnonymous;
						if (!isValidReviewData($rating, $isAnonymous, $reviewText)) {
							return;
						}
						$isAnonymous = (int)$isAnonymous;
						$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
						$nowFormatted = str_replace(["T", "Z"], " ", trim(substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'));
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
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$reviewID = $urlList[4];
					if (!checkIfTheUserHasReview($movieID, $userID) || !checkIfTheUserCanChangeReview($reviewID, $userID)) {
						return;
					}
					$reviewText = $requestData->body->reviewText;
					$rating = $requestData->body->rating;
					$isAnonymous = $requestData->body->isAnonymous;
					if (!isValidReviewData($rating, $isAnonymous, $reviewText)) {
						return;
					}
					$isAnonymous = (int)$isAnonymous;
					$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
					$nowFormatted = str_replace(["T", "Z"], " ", trim(substr($now->format('Y-m-d\TH:i:s.u'), 0, -3) . 'Z'));
					$reviewUpdateResult = $link->query("UPDATE reviews SET review_text='$reviewText', rating='$rating', is_anonymous='$isAnonymous', create_datetime='$nowFormatted' 
               									WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			case "DELETE":
				if (count($urlList) != 6) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$reviewID = $urlList[4];
					if (!checkIfTheUserHasReview($movieID, $userID) || !checkIfTheUserCanChangeReview($reviewID, $userID)) {
						return;
					}
					$reviewDeleteResult = $link->query("DELETE FROM reviews WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			default:
				setHTTPStatus('405', "Method '$method' not allowed");
				break;
		}
	}
