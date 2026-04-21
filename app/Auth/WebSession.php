<?php

declare(strict_types=1);

namespace App\Auth;

use App\Core\Database;
use PDO;

require_once __DIR__ . '/../Support/helpers.php';

class WebSession
{
    public ?int $id = null;
    public ?string $nombre = null;
    public ?string $usuario = null;
    public ?string $token = null;
    public ?int $tipo_id = null;
    public ?string $tipo = null;
    public bool $active = false;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['HELIX_ERP_ID']) || empty($_SESSION['HELIX_ERP_AUTH_TOKEN'])) {
            $this->destroySession();
        }

        $this->id = (int) $_SESSION['HELIX_ERP_ID'];
        $this->nombre = $_SESSION['HELIX_ERP_NOMBRE'] ?? null;
        $this->usuario = $_SESSION['HELIX_ERP_USER'] ?? null;
        $this->token = $_SESSION['HELIX_ERP_AUTH_TOKEN'] ?? null;
        $this->tipo_id = isset($_SESSION['HELIX_ERP_TIPO_ID']) ? (int) $_SESSION['HELIX_ERP_TIPO_ID'] : null;
        $this->tipo = $_SESSION['HELIX_ERP_TIPO'] ?? null;

        $_SESSION['HELIX_ERP_LAST_ACTIVITY'] = time();

        if (!$this->validateToken()) {
            die('destroy session');
            $this->destroySession();
        }

        $this->active = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipoId(): ?int
    {
        return $this->tipo_id;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function destroySession(): never
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: /helix/public/autenticacion/');
        exit;
    }

    private function validateToken(): bool
    {
        $token = $this->token;

        if (!$token || !ctype_xdigit($token) || strlen($token) !== 64) {
            return false;
        }

        $tokenBin = hex2bin($token);
        if ($tokenBin === false) {
            return false;
        }

        $salt = env('HMAC_SALT');
        if (!$salt) {
            return false;
        }

        $tokenHash = hash_hmac('sha256', $tokenBin, $salt, true);

        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("
            SELECT id, expira_en
            FROM usuarios_sesiones
            WHERE usuario = :usuario
              AND token_hash = :token
            LIMIT 1
        ");
        $stmt->bindValue(':usuario', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':token', $tokenHash, PDO::PARAM_LOB);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $stmt = $conn->prepare("
            UPDATE usuarios_sesiones
            SET ultima_actividad = NOW()
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_LOB);
        $stmt->execute();

        return true;
    }
}