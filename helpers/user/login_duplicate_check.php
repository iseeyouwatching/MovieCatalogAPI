<?php

	function checkUsernameDuplicates($username): bool {
		global $link;
		$messageResult = array(
			'message' => 'User Registration Failed',
			'error' => []
		);
		if ($link->error == "Duplicate entry '" . $username . "' for key 'users.username'") {
			$messageResult['error'] = "Login '" . $username . "' is already taken";
			setHTTPStatus('409', $messageResult);
			return true;
		}
		return false;
	}
