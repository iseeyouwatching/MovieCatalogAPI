<?php

	function checkIfExistMovie($movieID): bool
	{
		global $link;

		$movieCheck = $link->query("SELECT movie_id FROM movies WHERE movie_id='$movieID'")->fetch_assoc();
		if (!$movieCheck) {
			return false;
		}
		return true;
	}

