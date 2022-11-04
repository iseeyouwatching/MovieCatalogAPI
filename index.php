<?php

	global $link;

	require_once "database_connection.php";
	require_once "get_functions.php";
	include_once "helpers/headers.php";

	header("Content-type: application/json");

	$url = $_GET['q'] ?? '';
	$url = rtrim($url, '/');
	$urlList = explode('/', $url);

	$router = $urlList[1];
	$requestData = getData(getMethod());
	$method = getMethod();


	if (file_exists(realpath(dirname(__FILE__)). '/' .$urlList[0] . '/' . $router . '.php')) {
		include_once 'api/' . $router . '.php';
		route($method, $urlList, $requestData);
	}
	else {
		setHTTPStatus('404', "There is no routing as '$urlList[0]/$router'");
	}


