<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
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
