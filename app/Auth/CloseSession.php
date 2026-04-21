<?php

    declare(strict_types=1);

    namespace App\Auth;

    use App\Core\Database;
    use PDO;
    
	$session = new WebSession();
	$database = new Database();
    $conn = $database->GetDatabaseConnector();

	try {
		$conn->beginTransaction();
		$authentication_token_bin = hex2bin($session->getToken());
		$authentication_token_hash = hash('sha256', $authentication_token_bin . $CFG->hmacsalt, true);
		
		$stmt = $conn->prepare('SELECT id, destruida_en FROM usuarios_sesiones WHERE usuario = :usuario AND token_hash = :token AND destruida_en IS NULL');
		$stmt->bindParam(':usuario', $id);
		$stmt->bindParam(':token', $authentication_token_hash);
		$stmt->execute();
		$data = $stmt->fetch();

		if($data != null && $data['destruida_en'] == null) {
			$stmt = $conn->prepare('UPDATE usuarios_sesiones SET destruida_en = NOW() WHERE id = :id');
			$stmt->bindParam(':id', $data['id']);
			$stmt->execute();
		}

		$conn->commit();
		
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header("Location: ".$CFG->protocol.$CFG->host.$CFG->root."autenticacion/");
	} catch(Exception $ex) {
		$conn->rollBack();
		echo json_encode(array('status' => 'FAIL'));
	}