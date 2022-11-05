<?php

	function setHTTPStatus($status = "200", $message = null) {

		switch ($status) {
			default:
			case "200":
				$status = "HTTP/1.0 200 OK";
				break;
			case "401":
				$status = "HTTP/1.0 401 Unauthorized";
				break;
			case "404":
				$status = "HTTP/1.0 404 Not Found";
				break;
			case "405":
				$status = "HTTP/1.0 405 Method Not Allowed";
				break;
		}
		header($status);
		if (!is_null($message)) {
			echo json_encode(['message' => $message]);
		}
	}
