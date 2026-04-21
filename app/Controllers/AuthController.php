<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Core\Request;
use App\Core\Response;
use App\Auth\Session;
use App\Core\Database;

use PDO;
use DateTimeImmutable;
use Throwable;

use function App\Helpers\getDeviceType;

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        $salt = env('HMAC_SALT');

        if(!$salt) {
            throw new \RuntimeException('HMAL_SALT no esta configurado en .env');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $conn = $database->getConnection();

        try {
            $usuario = trim((string)$request->input('usuario', ''));
            $password = (string)$request->input('password', '');
            $keep = (int)$request->input('keep_me_logged_in', 0) === 1;

            if ($usuario === '' || $password === '') {
                return $response->json([
                    'status' => 'VALIDATION_ERROR',
                    'message' => 'Usuario y password son obligatorios'
                ], 422);
            }

            $stmt = $conn->prepare("
                SELECT u.id,
                       u.nombre,
                       u.tipo_usuario AS tipo_usuario_id,
                       ut.tipo AS tipo_usuario,
                       u.usuario,
                       u.password_hash,
                       u.activo
                FROM usuarios u
                INNER JOIN usuarios_tipos ut
                    ON u.tipo_usuario = ut.id
                WHERE u.usuario = :usuario
                LIMIT 1
            ");
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $response->json(['status' => 'ERROR_AUTENTICACION'], 401);
            }

            if (!password_verify($password, $user['password_hash'])) {
                return $response->json(['status' => 'ERROR_AUTENTICACION'], 401);
            }

            if ((int)$user['activo'] !== 1) {
                return $response->json(['status' => 'FAIL_NOT_ACTIVE'], 403);
            }

            $conn->beginTransaction();

            session_regenerate_id(true);

            $tokenBin = random_bytes(32);
            $token = bin2hex($tokenBin);
            $tokenHash = hash_hmac('sha256', $tokenBin, $salt, true);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if ($userAgent !== null) {
                $userAgent = mb_substr($userAgent, 0, 255);
            }

            $dispositivo = getDeviceType($userAgent);
            if ($dispositivo !== null) {
                $dispositivo = mb_substr($dispositivo, 0, 255);
            }

            $ahora = new DateTimeImmutable();
            $_SESSION['HELIX_ERP_AUTH_TIME'] = $ahora->format('Y-m-d H:i:s');
            $expiraEn = $keep ? $ahora->modify('+30 days') : $ahora->modify('+8 hours');
            $_SESSION['HELIX_ERP_AUTH_EXPIRES'] = $expiraEn->format('Y-m-d H:i:s');

            $session_id = random_bytes(16);
            $_SESSION['HELIX_ERP_LAST_ACTIVITY'] = time();
            $_SESSION['HELIX_ERP_AUTH_TOKEN'] = $token;
            $_SESSION['HELIX_ERP_ID'] = (int)$user['id'];
            $_SESSION['HELIX_ERP_USER'] = $user['usuario'];
            $_SESSION['HELIX_ERP_NOMBRE'] = $user['nombre'];
            $_SESSION['HELIX_ERP_TIPO_ID'] = (int)$user['tipo_usuario_id'];
            $_SESSION['HELIX_ERP_TIPO'] = $user['tipo_usuario'];

            $stmt = $conn->prepare("
                INSERT INTO usuarios_sesiones
                (
                    id,
                    usuario,
                    token_aux,
                    token_hash,
                    f_registro,
                    ultima_actividad,
                    expira_en,
                    ip,
                    user_agent,
                    dispositivo
                )
                VALUES
                (
                    :id,
                    :usuario,
                    :token_aux,
                    :token_hash,
                    NOW(),
                    NOW(),
                    :expira_en,
                    :ip,
                    :user_agent,
                    :dispositivo
                )
            ");
            $stmt->bindValue(':id', $session_id, PDO::PARAM_LOB);
            $stmt->bindValue(':usuario', (int)$user['id'], PDO::PARAM_INT);
            $stmt->bindValue(':token_aux', $token, PDO::PARAM_STR);
            $stmt->bindValue(':token_hash', $tokenHash, PDO::PARAM_LOB);
            $stmt->bindValue(':expira_en', $expiraEn->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':ip', $ip);
            $stmt->bindValue(':user_agent', $userAgent);
            $stmt->bindValue(':dispositivo', $dispositivo);
            $stmt->execute();

            $stmt = $conn->prepare("
                UPDATE usuarios
                SET f_ultima_conexion = NOW()
                WHERE id = :uid
            ");
            $stmt->bindValue(':uid', (int)$user['id'], PDO::PARAM_INT);
            $stmt->execute();

            $conn->commit();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'id' => (int)$user['id'],
                    'nombre' => $user['nombre'],
                    'usuario' => $user['usuario'],
                    'tipo_usuario_id' => (int)$user['tipo_usuario_id'],
                    'tipo_usuario' => $user['tipo_usuario']
                ]
            ]);
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            session_unset();
            session_destroy();

            return $response->json([
                'status' => 'ERROR_SQL',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function logout(Request $request, Response $response) {
    //     $salt = env('HMAC_SALT');

    //     if(!$salt) {
    //         throw new \RuntimeException('HMAL_SALT no esta configurado en .env');
    //     }

    //     if (session_status() === PHP_SESSION_NONE) {
    //         session_start();
    //     }

    //     $database = new DatabaseConnection();
    //     $conn = $database->GetDatabaseConnector();

    //     try {
    //         if (!empty($_SESSION['HELIX_ERP_AUTH_TOKEN'])) {
    //             $tokenHex = $_SESSION['HELIX_ERP_AUTH_TOKEN'];

    //             if (ctype_xdigit($tokenHex) && strlen($tokenHex) === 64) {
    //                 $tokenBin = hex2bin($tokenHex);
    //                 $tokenHash = hash_hmac('sha256', $tokenBin, $salt, true);

    //                 $stmt = $conn->prepare("
    //                     DELETE FROM usuarios_sesiones
    //                     WHERE token_hash = :token_hash
    //                 ");
    //                 $stmt->bindValue(':token_hash', $tokenHash, PDO::PARAM_LOB);
    //                 $stmt->execute();
    //             }
    //         }

    //         $_SESSION = [];
    //         session_destroy();

    //         return $response->json(['status' => 'OK']);
    //     } catch (Throwable $e) {
    //         return $response->json([
    //             'status' => 'ERROR_LOGOUT',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function logout(Request $request, Response $response) {
        $session = new Session();
        $database = new Database();
        $conn = $database->getConnection();

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
    }

    public function me(Request $request, Response $response)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['HELIX_ERP_ID'])) {
            return $response->json([
                'status' => 'UNAUTHORIZED'
            ], 401);
        }

        return $response->json([
            'status' => 'OK',
            'data' => [
                'id' => $_SESSION['HELIX_ERP_ID'],
                'usuario' => $_SESSION['HELIX_ERP_USER'] ?? null,
                'nombre' => $_SESSION['HELIX_ERP_NOMBRE'] ?? null,
                'tipo_usuario_id' => $_SESSION['HELIX_ERP_TIPO_ID'] ?? null,
                'tipo_usuario' => $_SESSION['HELIX_ERP_TIPO'] ?? null,
                'auth_time' => $_SESSION['HELIX_ERP_AUTH_TIME'] ?? null,
            ]
        ]);
    }
}