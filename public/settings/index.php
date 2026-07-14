<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$view = APP_PATH . '/Views/settings/index.php';

	require APP_PATH . '/Views/layout/main.php';