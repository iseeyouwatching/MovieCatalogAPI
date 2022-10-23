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

			}
		}
		else {
			echo "bad request";
		}
	}