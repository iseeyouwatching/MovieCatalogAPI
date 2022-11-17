<?php

	function learnFullInformationAboutMovie($movieId): array
	{
		global $link;

		$infoAboutMovie = $link->query("SELECT * FROM movies WHERE movie_id='$movieId'");
		$reviews = $link->query("SELECT * FROM reviews WHERE movie_id='$movieId'");
		$genreIdFromMovieId = $link->query("SELECT genre_id FROM movie_genre WHERE movie_id='$movieId'");
		$allGenres = array();
		foreach ($genreIdFromMovieId as $row) {
			$genreID = $row['genre_id'];
			$genres = $link->query("SELECT genre_id, name FROM genres WHERE genre_id='$genreID'")->fetch_assoc();
			$allGenres[] = array(
				'id' => $genres['genre_id'],
				'name' => $genres['name']
			);
		}
		$movieInfo = [];
		foreach ($infoAboutMovie as $row) {
			$movieInfo = array(
				'id' => $row['movie_id'],
				'name' => $row['name'],
				'poster' => $row['poster'],
				'year' => intval($row['year']),
				'country' => $row['country'],
				'genres' => [],
				'reviews' => [],
				'time' => intval($row['time']),
				'tagline' => $row['tagline'],
				'description' => $row['description'],
				'director' => $row['director'],
				'budget' => intval($row['budget']),
				'fees' => intval($row['fees']),
				'ageLimit' => intval($row['age_limit'])
			);
			$movieInfo['genres'] = $allGenres;
			foreach ($reviews as $review) {
				$review_id = $review['review_id'];
				$user_id_from_review_id = $link->query("SELECT user_id FROM reviews WHERE review_id='$review_id'")->fetch_assoc();
				$user_id = $user_id_from_review_id['user_id'];
				$user_info = $link->query("SELECT user_id, username, avatarLink FROM users WHERE user_id='$user_id'")->fetch_assoc();
				$info_about_user = array(
					'userId' => $user_info['user_id'],
					'nickName' => $user_info['username'],
					'avatar' => $user_info['avatarLink']
				);
				$movieInfo['reviews'][] = array(
					'id' => $review['review_id'],
					'rating' => intval($review['rating']),
					'reviewText' => $review['review_text'],
					'isAnonymous' => filter_var($review['is_anonymous'], FILTER_VALIDATE_BOOLEAN),
					'createDateTime' => $review['create_datetime'],
					'author' => $info_about_user
				);
			}
		}
		return $movieInfo;
	}