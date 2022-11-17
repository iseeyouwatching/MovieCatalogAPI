<?php

	require_once "check_birthdate.php";

	function isValidRegistrationUserData($login, $name, $password, $email, $date): bool
	{
		$validationErrors = [];
		if (!validateStringNotLess($login, 5) && !is_null($login) && $login != "") {
			$validationErrors[] = ["Login" => "Login must be at least 5 characters"];
		}
		if (!validateStringDoesNotContainsSpecialSymbols($login) && !is_null($login) && $login != "") {
			$validationErrors[] = ["Login" => "Login '" . $login. "' is invalid, can only contains letters or digits"];
		}
		if ($login == "") {
			$validationErrors[] = ["Login" => "The Login field is required"];
		}
		if ($name == "") {
			$validationErrors[] = ["Name" => "The Name field is required"];
		}
		if (!validateStringNotLess($password, 6) && !is_null($password) && $password != "") {
			$validationErrors[] = ["Password" => "Password must be at least 6 characters"];
		}
		if ($password == "") {
			$validationErrors[] = ["Password" => "The Password field is required"];
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !is_null($email) && $email != "") {
			$validationErrors[] = ["Email" => "Invalid Email address"];
		}
		if ($email == "") {
			$validationErrors[] = ["Email" => "The Email field is required"];
		}
		if ($validationErrors) {
			$messageResult = array(
				'message' => 'User Registration Failed',
				'errors' => []
			);
			$messageResult['errors'] = $validationErrors;
			setHTTPStatus('400', $messageResult);
			return false;
		}
		return true;
	}

	function isValidUpdateUserData($name, $birthdate, $email, $gender): bool
	{
		$validationErrors = [];
		if ($email == "") {
			$validationErrors[] = ["Email" => "The Email field is required"];
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !is_null($email) && $email != "") {
			$validationErrors[] = ["Email" => "Invalid Email address"];
		}
		if ($name == "") {
			$validationErrors[] = ["Name" => "The Name field is required"];
		}
		if ($birthdate == "") {
			$validationErrors[] = ["Birthdate" => "The Birthdate field is required"];
		}
		if ((($gender != 0 && $gender != 1) || !is_int($gender)) && $gender != "") {
			$validationErrors[] = ["Gender" => "We have only two genders"];
		}
		if ($gender == "") {
			$validationErrors[] = ["Gender" => "The Gender field is required"];
		}
		if ($validationErrors) {
			$messageResult = array(
				'message' => 'User Data Update Failed',
				'errors' => []
			);
			$messageResult['errors'] = $validationErrors;
			setHTTPStatus('400', $messageResult);
			return false;
		}
		else {
			return true;
		}
	}

