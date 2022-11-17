<?php

	require_once "helpers/movies/film_overview.php";
	require_once "helpers/movies/full_information_about_movie.php";

	function route($method, $urlList): void {
		global $link;
		if ($method == "GET") {
			if (count($urlList) == 3) {
				$pageInfo = array('pageSize' => 6);
				$pageNumber = $urlList[2];
				if ($pageNumber <= 0) {
					setHTTPStatus('404', "Page '$pageNumber' does not exist");
					return;
				}
				$moviesCount = $link->query("SELECT COUNT(*) AS totalNumberOfFilms FROM movies")->fetch_assoc();
				$pageInfo['pageCount'] = ceil($moviesCount['totalNumberOfFilms'] / $pageInfo['pageSize']);
				if ($pageNumber > $pageInfo['pageCount']) {
					setHTTPStatus('404', "Page '$pageNumber' does not exist");
					return;
				}
				$pageInfo['currentPage'] = intval($urlList[2]);
				echo json_encode(collectingInformationAboutMovie($pageInfo));
			}
			else if (count($urlList) == 4 && $urlList[2] == "details") {
				$movieId = $urlList[3];
				$checkIsMovieExist = $link->query("SELECT * FROM movies WHERE movie_id='$movieId'")->fetch_assoc();
				if (!$checkIsMovieExist) {
					setHTTPStatus('404', "There is no movie with this '$movieId' identifier");
					return;
				}
				echo json_encode(learnFullInformationAboutMovie($movieId));
			}
			else {
				setHTTPStatus('404', 'Missing resource is requested');
			}
		}
		else {
			setHTTPStatus('405', "Method '$method' not allowed");
		}
	}