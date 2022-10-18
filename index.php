<?php

	global $link;

	require_once "database_connection.php";
	require_once "get_functions.php";

	header("Content-type: application/json");

	$url = isset($_GET['q']) ? $_GET['q'] : '';
	$url = rtrim($url, '/');
	$urlList = explode('/', $url);

	$router = $urlList[1];
	$requestData = getData(getMethod());
	$method = getMethod();


	if (file_exists(realpath(dirname(__FILE__)). '/api/' . $router . '.php')) {
		include_once 'api/' . $router . '.php';
		route($method, $urlList, $requestData);
	}
	else {
		echo "NOPE 404";
	}


