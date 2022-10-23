<?php

	require_once "database_connection.php";

	function route($method, $urlList, $requestData) {
		global $link;
		if ($method == "GET") {
			if ($urlList[2] != "details") {
				$page_number = $urlList[2];
				$infoAboutMovies = $link->query("SELECT * FROM movies WHERE page_number='$page_number'");
				$pageInfo = array(
					'pageSize' => 6,
					'pageCount' => 5,
					'currentPage' => intval($urlList[2])
				);
				$result = array(
					'pageInfo' => [],
					'movies' => []
				);
				$result['pageInfo'] = $pageInfo;
				foreach ($infoAboutMovies as $row) {
					$movie_id = $row['movie_id'];
					$reviews = $link->query("SELECT review_id, movie_id, rating FROM reviews WHERE movie_id='$movie_id'");
					$movieInfo = array(
						'id' => $row['movie_id'],
						'name' => $row['name'],
						'poster' => $row['poster'],
						'year' => intval($row['year']),
						'country' => $row['year'],
						'genres' => [],
						'reviews' => []
					);
					$genreFromMovieId = $link->query("SELECT genre_id FROM movie_genre WHERE movie_id='$movie_id'");
					foreach ($genreFromMovieId as $row) {
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
			}
			else {
				$movie_id = $urlList[3];
				$infoAboutMovies = $link->query("SELECT * FROM movies WHERE movie_id='$movie_id'");
				$reviews = $link->query("SELECT * FROM reviews WHERE movie_id='$movie_id'");
				$genreFromMovieId = $link->query("SELECT genre_id FROM movie_genre WHERE movie_id='$movie_id'");
				$allGenres = array();
				foreach ($genreFromMovieId as $row) {
					$genreID = $row['genre_id'];
					$genres = $link->query("SELECT genre_id, name FROM genres WHERE genre_id='$genreID'")->fetch_assoc();
					$allGenres[] = array(
						'id' => $genres['genre_id'],
						'name' => $genres['name']
					);
				}
				$movieInfo = [];
				foreach ($infoAboutMovies as $row) {
					$movieInfo = array(
						'id' => $row['movie_id'],
						'name' => $row['name'],
						'poster' => $row['poster'],
						'year' => intval($row['year']),
						'country' => $row['year'],
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
				$result = $movieInfo;
				echo json_encode($result);
			}
		}
		else {
			echo "bad request";
		}
	}