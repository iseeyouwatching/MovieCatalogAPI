<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "DELETE":
				$movieId = $urlList[2];
				$deleteMovieFromFavourites = $link->query("DELETE FROM favourite_movies WHERE movie_id='$movieId'");
				if ($deleteMovieFromFavourites) {
					echo "200: success";
				}
				else {
					echo "bad";
				}
				break;
			default:
				echo "404";
				break;
		}
	}