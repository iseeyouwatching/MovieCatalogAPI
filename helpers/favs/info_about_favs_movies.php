<?php

	function informationAboutFavsMovies($token): array
	{
		global $link;

		$usernameFromToken = getPayload($token)['unique_name'];
		$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
		$userID = $user['user_id'];
		$movieIdFromFavouriteMovies = $link->query("SELECT movie_id FROM favourite_movies WHERE user_id='$userID'");
		$result = [];
		foreach ($movieIdFromFavouriteMovies as $row) {
			$movieID = $row['movie_id'];
			$movies = $link->query("SELECT * FROM movies WHERE movie_id='$movieID'")->fetch_assoc();
			$movieInfo = array(
				'id' => $movies['movie_id'],
				'name' => $movies['name'],
				'poster' => $movies['poster'],
				'year' => intval($movies['year']),
				'country' => $movies['country'],
				'genres' => [],
				'reviews' => []
			);
			$reviews = $link->query("SELECT review_id, rating FROM reviews WHERE movie_id='$movieID'");
			$genreIdFromMovieId = $link->query("SELECT genre_id FROM movie_genre WHERE movie_id='$movieID'");
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
		return $result;
	}

