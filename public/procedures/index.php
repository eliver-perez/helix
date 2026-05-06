<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$index = array_search('procedures', $segments);

	$action = $segments[$index + 1] ?? null;
	$id     = $segments[$index + 1] ?? null;
	$next   = $segments[$index + 2] ?? null;

	// default
	$view = APP_PATH . '/Views/procedures/index.php';

	require APP_PATH . '/Views/layout/main.php';