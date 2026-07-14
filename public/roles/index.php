<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$index = array_search('roles', $segments);

	$action = $segments[$index + 1] ?? null;
	$next   = $segments[$index + 2] ?? null;

	$id = null;

	$view = APP_PATH . '/Views/roles/index.php';

	require APP_PATH . '/Views/layout/main.php';