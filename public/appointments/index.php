<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$index = array_search('appointments', $segments);

	$action = $segments[$index + 1] ?? null;
	$id     = $segments[$index + 1] ?? null;
	$next   = $segments[$index + 2] ?? null;

	// default
	$view = APP_PATH . '/Views/appointments/index.php';

	if (is_numeric($action)) {
		$id = $action;
		$view = APP_PATH . '/Views/appointments/view.php';

	} elseif ($action === 'add') {
		$view = APP_PATH . '/Views/appointments/add.php';

	} elseif ($action === 'edit' && is_numeric($next)) {
		$id = $next;
		$view = APP_PATH . '/Views/appointments/edit.php';
	}

	require APP_PATH . '/Views/layout/main.php';