
<?php
// oauth-github.php - начало OAuth потока

session_start();

$client_id = 'Ov23lixL8oQw5U5EPZDH';

// Генерируем state для CSRF-защиты
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => 'http://192.168.72.131/oauth-callback.php',
    'scope' => 'read:user',
    'state' => $state,
]);

header("Location: https://github.com/login/oauth/authorize?$params");
exit;
?>
