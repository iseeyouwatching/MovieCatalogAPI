<?php

	function collectingInformationAboutMovie($pageInfo): array
	{
		global $link;

		$result['pageInfo'] = $pageInfo;
		$numberOfRecords = $pageInfo['pageSize'];
		$from = $pageInfo['pageSize']*($pageInfo['currentPage'] - 1);
		$infoAboutMovies = $link->query("SELECT movie_id, name, poster, year, country FROM movies LIMIT $numberOfRecords OFFSET $from");
		foreach ($infoAboutMovies as $row) {
			$movieId = $row['movie_id'];
			$reviews = $link->query("SELECT review_id, movie_id, rating FROM reviews WHERE movie_id='$movieId'");
			$movieInfo = array(
				'id' => $row['movie_id'],
				'name' => $row['name'],
				'poster' => $row['poster'],
				'year' => intval($row['year']),
				'country' => $row['country'],
				'genres' => [],
				'reviews' => []
			);
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

		return $result;
	}

