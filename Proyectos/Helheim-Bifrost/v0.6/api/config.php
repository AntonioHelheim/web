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
        // Fuego: inspirados en dragones
        'fire_1' => ['name' => 'Chispodrilo', 'color' => 0xe0703a, 'hp' => 22, 'atk' => 12, 'def' => 7],
        'fire_2' => ['name' => 'Braseryx', 'color' => 0xd94f2b, 'hp' => 28, 'atk' => 16, 'def' => 11],
        'fire_3' => ['name' => 'Vulcanor', 'color' => 0xb83214, 'hp' => 35, 'atk' => 21, 'def' => 15],
        // Agua: inspirados en serpientes marinas
        'water_1' => ['name' => 'Marejino', 'color' => 0x5ea8c9, 'hp' => 24, 'atk' => 9, 'def' => 10],
        'water_2' => ['name' => 'Corrientauro', 'color' => 0x3a6ea5, 'hp' => 30, 'atk' => 13, 'def' => 14],
        'water_3' => ['name' => 'Abisalgo', 'color' => 0x1f4e79, 'hp' => 38, 'atk' => 17, 'def' => 19],
        // Planta: inspirados en insectos
        'grass_1' => ['name' => 'Brotalín', 'color' => 0x7cb342, 'hp' => 23, 'atk' => 10, 'def' => 9],
        'grass_2' => ['name' => 'Espigón', 'color' => 0x4c9a2a, 'hp' => 29, 'atk' => 14, 'def' => 13],
        'grass_3' => ['name' => 'Follascorpio', 'color' => 0x2e6b1f, 'hp' => 36, 'atk' => 18, 'def' => 17],
        // Electricidad: inspirados en equidnas
        'electric_1' => ['name' => 'Chispequín', 'color' => 0xe8c268, 'hp' => 21, 'atk' => 13, 'def' => 6],
        'electric_2' => ['name' => 'Voltígero', 'color' => 0xd4a017, 'hp' => 27, 'atk' => 17, 'def' => 10],
        'electric_3' => ['name' => 'Amperidna', 'color' => 0xb8860b, 'hp' => 33, 'atk' => 22, 'def' => 13],
        // Lucha: inspirados en artes marciales
        'fighting_1' => ['name' => 'Puñolet', 'color' => 0xa0522d, 'hp' => 24, 'atk' => 13, 'def' => 8],
        'fighting_2' => ['name' => 'Katáfaro', 'color' => 0x8b3a1a, 'hp' => 30, 'atk' => 17, 'def' => 12],
        'fighting_3' => ['name' => 'Granmaestro', 'color' => 0x6b2c12, 'hp' => 37, 'atk' => 22, 'def' => 16],
        // Volador: inspirados en aves
        'flying_1' => ['name' => 'Plumín', 'color' => 0xa8d8e8, 'hp' => 20, 'atk' => 11, 'def' => 7],
        'flying_2' => ['name' => 'Ventizarro', 'color' => 0x7ec4dd, 'hp' => 26, 'atk' => 15, 'def' => 10],
        'flying_3' => ['name' => 'Tormenpluma', 'color' => 0x4a90b8, 'hp' => 32, 'atk' => 19, 'def' => 13],
        // Oscuro: inspirados en gatos
        'dark_1' => ['name' => 'Sombrigato', 'color' => 0x5b4b8a, 'hp' => 22, 'atk' => 12, 'def' => 8],
        'dark_2' => ['name' => 'Penumbraz', 'color' => 0x3d2f5c, 'hp' => 28, 'atk' => 16, 'def' => 11],
        'dark_3' => ['name' => 'Eclipsino', 'color' => 0x241b38, 'hp' => 35, 'atk' => 20, 'def' => 15],
        // Diurno: inspirados en perros
        'day_1' => ['name' => 'Solete', 'color' => 0xf2c14e, 'hp' => 23, 'atk' => 11, 'def' => 9],
        'day_2' => ['name' => 'Auroraz', 'color' => 0xe8a33d, 'hp' => 29, 'atk' => 15, 'def' => 12],
        'day_3' => ['name' => 'Radialbo', 'color' => 0xd4822a, 'hp' => 36, 'atk' => 19, 'def' => 16],
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
