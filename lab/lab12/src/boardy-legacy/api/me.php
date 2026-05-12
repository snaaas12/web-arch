<?php
// api/me.php

// Те же настройки сессии, что и в login.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Общий секрет с FastAPI
$secret_key = 'boardysecretkey';

// Функция генерации JWT
function generate_jwt($user_id, $user_name, $secret_key) {
    // Header
    $header = rtrim(strtr(base64_encode(json_encode([
        'alg' => 'HS256', 
        'typ' => 'JWT'
    ])), '+/', '-_'), '=');
    
    // Payload
    $payload = rtrim(strtr(base64_encode(json_encode([
        'user_id' => (int)$user_id,
        'name' => $user_name,
        'exp' => time() + 3600
    ])), '+/', '-_'), '=');
    
    // Signature
    $signature = rtrim(strtr(base64_encode(
        hash_hmac('sha256', "$header.$payload", $secret_key, true)
    ), '+/', '-_'), '=');
    
    return "$header.$payload.$signature";
}

// Проверяем авторизацию
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Генерируем JWT
$jwt = generate_jwt($_SESSION['user_id'], $_SESSION['user_name'], $secret_key);

// Возвращаем токен
header('Content-Type: application/json');
echo json_encode(['token' => $jwt]);
?>
