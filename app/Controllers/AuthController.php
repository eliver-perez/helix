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
                       u.usuario,
                       u.password_hash,
                       u.activo
                FROM usuarios u
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

            $stmt = $conn->prepare("
                SELECT
                uer.id,
                e.uuid empresa_id,
                e.empresa,
                TRIM(
                    CONCAT(
                        COALESCE(e.calle, ''), ' ',
                        COALESCE(e.num_ext, ''), ' ',
                        COALESCE(e.num_int, ''), ', ',
                        COALESCE(ce.colonia, ''), ' ',
                        COALESCE(e.cp, ''), ', ',
                        COALESCE(me.municipio, ''), ', ',
                        COALESCE(ee.estado, ''), ', ',
                        COALESCE(pe.pais, '')
                    )
                ) domicilio,
                ut.id tipo_usuario_id,
                ut.codigo tipo_usuario_codigo,
                ut.tipo tipo_usuario
                FROM usuarios_empresas_roles uer
                    INNER JOIN empresas e
                        ON uer.empresa = e.id
                    LEFT JOIN colonias ce
                        ON e.colonia = ce.id
                    LEFT JOIN municipios me
                        ON ce.municipio = me.id
                    LEFT JOIN estados ee
                        ON me.estado = ee.id
                    LEFT JOIN paises pe
                        ON ee.pais = pe.id
                    INNER JOIN usuarios_tipos ut
                        ON uer.tipo_usuario = ut.id
                WHERE uer.usuario = :usuario
                    AND uer.f_baja IS NULL
            ");

            $stmt->bindParam(':usuario', $user['id']);
            $stmt->execute();
            $data = $stmt->fetchAll();
            $tipos_usuario = array();
            foreach($data as $tu) {
                array_push($tipos_usuario, array('id'                   => $tu['id'],
                                                'empresa_id'            => $tu['empresa_id'],
                                                'empresa'               => $tu['empresa'],
                                                'domicilio'             => $tu['domicilio'],
                                                'tipo_usuario_id'       => $tu['tipo_usuario_id'],
                                                'tipo_usuario_codigo'   => $tu['tipo_usuario_codigo'],
                                                'tipo_usuario'          => $tu['tipo_usuario']));
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
            $_SESSION['HELIX_ERP_USER_ROLE'] = count($tipos_usuario) == 1 ? $tipos_usuario[0]['id'] : null;
            $_SESSION['HELIX_ERP_USER_TYPE_CD'] = count($tipos_usuario) == 1 ? $tipos_usuario[0]['tipo_usuario_codigo'] : null;
            $_SESSION['HELIX_ERP_USER_TYPE'] = count($tipos_usuario) == 1 ? $tipos_usuario[0]['tipo_usuario'] : null;

            $_SESSION['HELIX_ERP_AVAILABLE_ROLES'] = count($tipos_usuario) > 1 ? $tipos_usuario : null;

            $stmt = $conn->prepare("
                INSERT INTO usuarios_sesiones
                (
                    id,
                    usuario,
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
            // $stmt->bindValue(':token_aux', $token, PDO::PARAM_STR);
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
                    'usuario' => $user['usuario']
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
        $salt = env('HMAC_SALT');

        $session = new Session();
        $database = new Database();
        $conn = $database->getConnection();

        try {
            $conn->beginTransaction();
            $authentication_token_bin = hex2bin($session->getToken());
            $authentication_token_hash = hash('sha256', $authentication_token_bin . $salt, true);
            
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
            return $response->json(['status' => 'OK']);
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
                'auth_time' => $_SESSION['HELIX_ERP_AUTH_TIME'] ?? null,
            ]
        ]);
    }
}