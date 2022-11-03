<?php

	function validateStringNotLess($str = '', $length): bool {
		if (strlen($str) >= $length) {
			return true;
		}
		else {
			return false;
		}
	}

	function validateStringDoesnotContainsSpecialSymbols($str = ''): bool {
		$sourceUsername = $str;
		if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $str) || strpos($str, '/') || strpos($str, ' ') || $sourceUsername !== stripslashes($str) || preg_match('/[А-Яа-яЁё]/u', $str)) {
			return false;
		}
		else {
			return true;
		}
	}