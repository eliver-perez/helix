<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$index = array_search('receipt', $segments);

	$action = $segments[$index + 1] ?? null;
	$id     = $segments[$index + 1] ?? null;

	if (is_uuid($action)) {
		$id = $action;
		$view = APP_PATH . '/Views/receipt/index.php';
	}

	require APP_PATH . '/Views/layout/main.php';