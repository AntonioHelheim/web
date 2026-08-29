<?php
// Configuración de conexión a la base de datos.
// Ajusta estos valores a tu entorno (XAMPP/MAMP/hosting).
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'pokeweb';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// Todas las respuestas de la API son JSON.
header('Content-Type: application/json; charset=utf-8');

// Sesión compartida entre todos los endpoints (login, save, load...).
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        respond(['error' => 'No has iniciado sesión'], 401);
    }
    return (int) $_SESSION['user_id'];
}

// Catálogo de especies espejo de js/data.js. Vive también en PHP porque las
// batallas PvP se calculan en el servidor (autoridad única de daño/turnos),
// así que el servidor necesita poder generar/validar monstruos por su cuenta.
function species_catalog(): array
{
    return [
        'mon_fire'  => ['name' => 'Flamlet', 'color' => 0xd94f2b, 'hp' => 22, 'atk' => 12, 'def' => 8],
        'mon_water' => ['name' => 'Aquabub', 'color' => 0x3a6ea5, 'hp' => 24, 'atk' => 9, 'def' => 12],
        'mon_grass' => ['name' => 'Leafkin', 'color' => 0x4c9a2a, 'hp' => 23, 'atk' => 10, 'def' => 11],
    ];
}

function random_monster(): array
{
    $catalog = species_catalog();
    $keys = array_keys($catalog);
    $key = $keys[array_rand($keys)];
    $base = $catalog[$key];
    return [
        'speciesKey' => $key,
        'name' => $base['name'],
        'color' => $base['color'],
        'maxHp' => $base['hp'],
        'hp' => $base['hp'],
        'atk' => $base['atk'],
        'def' => $base['def'],
    ];
}

// Usa el primer monstruo del equipo guardado del jugador si tiene uno con
// vida; si no, genera uno al azar. La copia usada en la batalla PvP es un
// snapshot independiente: perder un duelo no daña tu equipo guardado.
function monster_for_user(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT party_json FROM saves WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if ($row) {
        $party = json_decode($row['party_json'], true);
        if (is_array($party) && count($party) > 0 && ($party[0]['hp'] ?? 0) > 0) {
            return $party[0];
        }
    }
    return random_monster();
}
