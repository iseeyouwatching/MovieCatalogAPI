<?php

	function checkEmailDuplicates($email): bool {
		global $link;
		$messageResult = array(
			'message' => 'User Registration Failed',
			'error' => []
		);
		if ($link->error == "Duplicate entry '" . $email . "' for key 'users.email'") {
			$messageResult['error'] = "Email '" . $email . "' is already taken";
			setHTTPStatus('409', $messageResult);
			return true;
		}
		return false;
	}
