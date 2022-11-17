<?php

	require_once "helpers/jwt.php";
	require_once "helpers/favs/info_about_favs_movies.php";
	require_once "helpers/favs/is_exist_movie.php";

	function route($method, $urlList): void {
		global $link;
		switch ($method) {
			case "GET":
				if (count($urlList) != 2) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$result = informationAboutFavsMovies($token);
					if (empty($result)) {
						echo json_encode(['movies' => informationAboutFavsMovies($token)]);
					}
					else {
						echo json_encode(informationAboutFavsMovies($token));
					}
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			case "POST":
				if (count($urlList) != 4) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					if (!checkIfExistMovie($movieID)) {
						setHTTPStatus('404', "Film with this '$movieID' identifier does not exist");
						return;
					}
					$insertMovie = $link->query("INSERT INTO favourite_movies(user_id, movie_id) VALUES('$userID', '$movieID')");
					if (!$insertMovie) {
						setHTTPStatus('409', "The film with this '$movieID' identifier is already in the list of favorites at the user with this '$userID' identifier");
					}
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			case "DELETE":
				if (count($urlList) != 4) {
					setHTTPStatus('404', 'Missing resource is requested');
					return;
				}
				$token = substr(getallheaders()['Authorization'], 7);
				$isLogoutToken = $link->query("SELECT user_id FROM tokens WHERE value LIKE '$token'")->fetch_assoc();
				if (isValid($token) && !isExpired($token) && $isLogoutToken == null) {
					$usernameFromToken = getPayload($token)['unique_name'];
					$user = $link->query("SELECT user_id FROM users WHERE username='$usernameFromToken'")->fetch_assoc();
					$userID = $user['user_id'];
					$movieID = $urlList[2];
					if (!checkIfExistMovie($movieID)) {
						setHTTPStatus('404', "Film with this '$movieID' identifier does not exist");
						return;
					}
					$checkIsExistInListOfFavs = $link->query("SELECT user_id, movie_id FROM favourite_movies WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
					if ($checkIsExistInListOfFavs) {
						$deleteMovieFromFavourites = $link->query("DELETE FROM favourite_movies WHERE movie_id='$movieID' AND user_id='$userID'");
					}
					else {
						setHTTPStatus('409', "The film with this '$movieID' identifier does not exist in the list of favorites at the user with this '$userID' identifier");
					}
				}
				else {
					setHTTPStatus('401', 'Token not specified or not valid');
				}
				break;
			default:
				setHTTPStatus('404', 'Missing resource is requested');
				break;
		}
	}