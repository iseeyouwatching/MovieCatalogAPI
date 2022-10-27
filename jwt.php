<?php
	function generateToken(array $payload, string $secret): string {

		$header = [
			'alg' => 'HS256',
			'typ' => 'JWT',
		];

		$now = new DateTime();
		$payload['nbf'] = $now->getTimestamp();
		$payload['exp'] = $now->getTimestamp() + 3600;
		$payload['iat'] = $now->getTimestamp();
		$payload['iss'] = "http://localhost/";
		$payload['aud'] = "http://localhost/";

		$base64Header = base64_encode(json_encode($header));
		$base64Payload = base64_encode(json_encode($payload));

		$base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
		$base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

		$secret = base64_encode($secret);
		$signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

		$base64Signature = base64_encode($signature);

		$signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

		return $base64Header . '.' . $base64Payload . '.' . $signature;
	}

	function getPayload(string $token) {
		$array = explode('.', $token);

		return json_decode(base64_decode($array[1]), true);
	}

	function isExpired(string $token): bool {
	$payload = getPayload($token);

	$now = new DateTime();

	return $payload['exp'] < $now->getTimestamp();
	}
