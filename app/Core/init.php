<?php
	use App\Auth\WebSession;
    use App\Core\Database;

    define('APP_PATH', dirname(__DIR__));

	require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Support/helpers.php';
	
	loadEnv(dirname(__DIR__) . '/../.env');
	$config = require __DIR__ . '/config.php';

	$session = new WebSession();
    $database = new Database();
    $conn = $database->getConnection();