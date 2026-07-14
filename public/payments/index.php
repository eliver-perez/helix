<?php
	require_once __DIR__.'/../../app/Core/init.php';

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$segments = explode('/', $uri);

	$index = array_search('payments', $segments);

	$action = $segments[$index + 1] ?? null;
	$next   = $segments[$index + 2] ?? null;

	$id = null;

	$view = APP_PATH . '/Views/payments/index.php';

	if ($action === 'add') {
		$view = APP_PATH . '/Views/payments/add.php';
	} elseif ($action === 'edit' && is_uuid($next)) {
		$id = $next;
		$view = APP_PATH . '/Views/payments/edit.php';

	} elseif (is_uuid($action)) {
		$id = $action;
		$view = APP_PATH . '/Views/payments/view.php';
	}

	require APP_PATH . '/Views/layout/main.php';