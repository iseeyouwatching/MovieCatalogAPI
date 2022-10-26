<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
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
			default:
				echo "404";
				break;
		}
	}
