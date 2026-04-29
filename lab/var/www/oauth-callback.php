<?php
// oauth-callback.php
session_start();

$client_id = 'Ov23lixL8oQw5U5EPZDH';
$client_secret = '63ea7d7e161f627a5b2bb541d1292d983a250cf2';

// ✅ ВКЛЮЧАЕМ ПРОВЕРКУ STATE (CSRF защита)
if (($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
    die('Invalid state — possible CSRF attack');
}

// Обмен code на access_token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $_GET['code'],
    ]),
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$access_token = $data['access_token'] ?? null;

if (!$access_token) {
    die('Failed to get access token: ' . print_r($data, true));
}

// Запрос профиля
$ch = curl_init('https://api.github.com/user');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token,
        'User-Agent: Boardy'
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$profile = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($profile['id'])) {
    die('Failed to get user profile');
}

// Подключение к БД
$pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Найти или создать пользователя
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE github_id = ?');
$stmt->execute([(string)$profile['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $stmt = $pdo->prepare('INSERT INTO users (name, github_id) VALUES (?, ?)');
    $stmt->execute([$profile['login'], (string)$profile['id']]);
    $user = [
        'id' => $pdo->lastInsertId(),
        'name' => $profile['login']
    ];
}

// Создаём сессию
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];

// Редирект
header('Location: /messages.php');
exit;
?>
