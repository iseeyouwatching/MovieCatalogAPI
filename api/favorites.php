<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "GET":
				$token = substr(getallheaders()['Authorization'], 7);
				$userFromToken = $link->query("SELECT user_id FROM tokens WHERE value='$token'")->fetch_assoc();
				$userId = $userFromToken['user_id'];
				$movieIdFromFavouriteMovies = $link->query("SELECT movie_id FROM favourite_movies WHERE user_id='$userId'");
				$result = [];
				foreach ($movieIdFromFavouriteMovies as $row) {
					$movieId = $row['movie_id'];
					$movies = $link->query("SELECT * FROM movies WHERE movie_id='$movieId'")->fetch_assoc();
					$movieInfo = array(
						'id' => $movies['movie_id'],
						'name' => $movies['name'],
						'poster' => $movies['poster'],
						'year' => intval($movies['year']),
						'country' => $movies['country'],
						'genres' => [],
						'reviews' => []
					);
					$reviews = $link->query("SELECT review_id, rating FROM reviews WHERE movie_id='$movieId'");
					$genreIdFromMovieId = $link->query("SELECT genre_id FROM movie_genre WHERE movie_id='$movieId'");
					foreach ($genreIdFromMovieId as $row) {
						$genreID = $row['genre_id'];
						$genres = $link->query("SELECT genre_id, name FROM genres WHERE genre_id='$genreID'")->fetch_assoc();
						$movieInfo['genres'][] = array(
							'id' => $genres['genre_id'],
							'name' => $genres['name']
						);
					}
					foreach ($reviews as $review) {
						$movieInfo['reviews'][] = [
							'id' => $review['review_id'],
							'rating' => intval($review['rating'])
						];
					}
					$result['movies'][] = $movieInfo;
				};
				echo json_encode($result);
				break;
			default:
				echo "404";
				break;
		}
	}