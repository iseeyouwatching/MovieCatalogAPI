<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "POST":
				$token = substr(getallheaders()['Authorization'], 7);
				$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
				$userID = $userFromToken['user_id'];
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
					}
					else {
						echo "success";
					}
				}
				else {
					echo "review already exist";
				}
				break;
			case "PUT":
				$token = substr(getallheaders()['Authorization'], 7);
				$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
				$userID = $userFromToken['user_id'];
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
				break;
			case "DELETE":
				$token = substr(getallheaders()['Authorization'], 7);
				$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
				$userID = $userFromToken['user_id'];
				$movieID = $urlList[2];
				$reviewID = $urlList[4];
				$reviewDeleteResult = $link->query("DELETE FROM reviews WHERE user_id='$userID' AND movie_id='$movieID' AND review_id='$reviewID'");
				if (!$reviewDeleteResult) {
					echo "bad";
				}
				break;
			default:
				echo "404";
				break;
		}
	}
