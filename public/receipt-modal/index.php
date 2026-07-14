<?php
	require_once __DIR__.'/../../app/Core/init.php';

    use App\Core\Database;

	$id = $_GET['id'];

    $database = new Database();
    $conn = $database->getConnection();
	require  __DIR__ . '/../../app/Views/receipt/receipt_modal.php';
?>