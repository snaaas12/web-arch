<?php
// oauth-callback-debug.php - исправленная версия
session_start();

// Включим вывод всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== OAuth Callback Debug ===\n\n";

$client_id = 'Iv23lid3A4sOqyfao16Z';  // ЗАМЕНИТЕ
$client_secret = 'ed70a3bbd814675ea7e43b0e24866700cb5b4cee';  // ЗАМЕНИТЕ

echo "1. GET parameters:\n";
print_r($_GET);
echo "\n";

echo "2. Session state: " . ($_SESSION['oauth_state'] ?? 'not set') . "\n\n";

// Проверка state (временно отключим для отладки)
// if (($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
//     die('State mismatch');
// }

echo "3. Exchanging code for access_token...\n";
echo "   URL: https://github.com/login/oauth/access_token\n";

// ПРАВИЛЬНЫЙ запрос к GitHub
$post_fields = http_build_query([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $_GET['code'],
]);

echo "   Post fields: $post_fields\n\n";

$ch = curl_init();

// ВАЖНО: правильный URL
curl_setopt($ch, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Временно для отладки
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$info = curl_getinfo($ch);

curl_close($ch);

echo "HTTP Code: $http_code\n";
if ($curl_error) {
    echo "CURL Error: $curl_error\n";
}
echo "Response: $response\n\n";

// Если получили HTML вместо JSON
if (strpos($response, '<html') !== false) {
    echo "ERROR: Got HTML instead of JSON. Possible SSL or redirect issue.\n";
    echo "First 500 chars of response:\n" . substr($response, 0, 500) . "\n\n";
}

$data = json_decode($response, true);
$access_token = $data['access_token'] ?? null;

if (!$access_token) {
    echo "ERROR: Failed to get access token!\n";
    echo "Full response: ";
    print_r($data);
    
    // Пробуем альтернативный метод
    echo "\n\n4. Trying alternative method (without http_build_query)...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $_GET['code'],
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response2 = curl_exec($ch);
    $http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code2\n";
    echo "Response: $response2\n\n";
    
    $data2 = json_decode($response2, true);
    $access_token = $data2['access_token'] ?? null;
    
    if (!$access_token) {
        echo "Both methods failed!\n";
        exit;
    }
}

echo "5. Access token obtained: " . substr($access_token, 0, 20) . "...\n\n";

echo "6. Fetching user profile...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'User-Agent: Boardy-Debug'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$profile_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $profile_response\n\n";

$profile = json_decode($profile_response, true);

if (!isset($profile['id'])) {
    echo "ERROR: Failed to get user profile!\n";
    if (isset($profile['message'])) {
        echo "GitHub message: " . $profile['message'] . "\n";
    }
    exit;
}

echo "7. User profile obtained:\n";
echo "   ID: " . $profile['id'] . "\n";
echo "   Login: " . $profile['login'] . "\n";
echo "   Name: " . ($profile['name'] ?? 'not set') . "\n\n";

// Подключение к БД
try {
    $pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare('SELECT id, name FROM users WHERE github_id = ?');
    $stmt->execute([(string)$profile['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "8. Creating new user...\n";
        $stmt = $pdo->prepare('INSERT INTO users (name, github_id) VALUES (?, ?)');
        $stmt->execute([$profile['login'], (string)$profile['id']]);
        $user = [
            'id' => $pdo->lastInsertId(),
            'name' => $profile['login']
        ];
        echo "   User created with ID: " . $user['id'] . "\n";
    } else {
        echo "8. User found with ID: " . $user['id'] . "\n";
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    echo "9. Session created!\n";
    echo "   user_id: " . $_SESSION['user_id'] . "\n";
    echo "   user_name: " . $_SESSION['user_name'] . "\n\n";
    
    echo "SUCCESS! Redirecting to /messages.php...\n";
    echo "</pre>";
    
    echo '<meta http-equiv="refresh" content="3;url=/messages.php">';
    echo '<a href="/messages.php">Click here if not redirected</a>';
    exit;
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit;
}
?>

