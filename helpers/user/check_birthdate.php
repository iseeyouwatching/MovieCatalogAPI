<?php

	function checkBirthdate($date): bool {
		global $link;
		$messageResult = array(
			'message' => 'User Registration Failed',
			'error' => []
		);
		if ($link->errno == 1292) {
			$messageResult['error'] = "Incorrect datetime value: '$date'";
			setHTTPStatus('400', $messageResult);
			return true;
		}
		return false;
	}
