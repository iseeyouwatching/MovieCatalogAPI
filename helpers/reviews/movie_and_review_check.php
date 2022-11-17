<?php

	function checkIfTheUserHasReview($movieID, $userID): bool {
		global $link;
		$movieCheck = $link->query("SELECT review_id FROM reviews WHERE user_id='$userID' AND movie_id='$movieID'")->fetch_assoc();
		if (!$movieCheck) {
			setHTTPStatus('404','The user has no review on this movie');
			return false;
		}
		return true;
	}

	function checkIfTheUserCanChangeReview($reviewID, $userID): bool {
		global $link;
		$reviewCheck = $link->query("SELECT review_id FROM reviews WHERE review_id='$reviewID' AND user_id='$userID'")->fetch_assoc();
		if (!$reviewCheck) {
			setHTTPStatus('403','User cannot change foreign review');
			return false;
		}
		return true;
	}

