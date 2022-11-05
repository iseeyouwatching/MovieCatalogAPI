<?php

	require_once "database_connection.php";
	require_once "jwt.php";

	function route($method, $urlList, $requestData) {
		global $link;
		switch ($method) {
			case "GET":
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
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
					if (empty($result)) {
						echo json_encode(['movies' => $result]);
					}
					else {
						echo json_encode($result);
					}
				}
				else {
					echo "401: unauthorized";
				}
				break;
			case "POST":
				if (count($urlList) == 4) {
					$token = substr(getallheaders()['Authorization'], 7);
					$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
					if (!isExpired($token) && $isLogoutToken == null) {
						$usernameFromToken = getPayload($token)['unique_name'];
						$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
						$userID = $user['user_id'];
						$movieID = $urlList[2];
						$insertMovie = $link->query("INSERT INTO favourite_movies(user_id, movie_id) VALUES('$userID', '$movieID')");
						if (!$insertMovie) {
							setHTTPStatus('409', "The film with this '$movieID' identifier is already in the list of favorites at the user with this '$userID' identifier");
						}
					}
					else {
						setHTTPStatus('401', 'Token not specified or not valid');
					}
				}
				break;
			case "DELETE":
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (!isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					$checkIsExist = $link->query("SELECT user_id, movie_id FROM favourite_movies WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
					if ($checkIsExist) {
						$deleteMovieFromFavourites = $link->query("DELETE FROM favourite_movies WHERE movie_id='$movieID' AND user_id='$userID'");
						echo "200: success";
					}
					else {
						echo "Not-existing user favorite movie";
					}
				}
				else {
					echo "401: unauthorized";
				}
				break;
			default:
				echo "404";
				break;
		}
	}