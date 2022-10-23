<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "POST":
				$token = substr(getallheaders()['Authorization'], 7);
				$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
				$userId = $userFromToken['user_id'];
				$movieId = $urlList[2];
				$insertMovie = $link->query("INSERT INTO favourite_movies(user_id, movie_id) VALUES('$userId', '$movieId')");
				if (!$insertMovie) {
					echo "already added";
				}
				else {
					echo "200: success";
				}
				break;
			default:
				echo "404";
				break;
		}
	}