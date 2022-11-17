<?php

	function isValidReviewData($rating, $isAnonymous, $reviewText): bool
	{
		$validationErrors = [];
		if (($rating < 0 || $rating > 10 || !is_int($rating)) && $rating != "") {
			$validationErrors[] = ["Rating" => 'The field Rating must be between 0 and 10'];
		}
		if ($rating == "") {
			$validationErrors[] = ["Rating" => "The Rating field is required"];
		}
		if ($reviewText == "") {
			$validationErrors[] = ["ReviewText" => "The ReviewText field is required"];
		}
		if (($isAnonymous !== true && $isAnonymous !== false && $isAnonymous != "") || is_int($isAnonymous)) {
			$validationErrors[] = ["IsAnonymous" => 'The field IsAnonymous can only be true or false'];
		}
		if (is_null($isAnonymous)) {
			$validationErrors[] = ["IsAnonymous" => "The IsAnonymous field is required"];
		}
		if ($validationErrors) {
			$messageResult = array(
				'message' => 'Adding review is failed',
				'errors' => []
			);
			$messageResult['errors'] = $validationErrors;
			setHTTPStatus('400', $messageResult);
			return false;
		}
		return true;
	}