<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


// submit.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Защита страницы - только для авторизованных
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$success = '';

$pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body'] ?? '');
    
    if (empty($body)) {
        $error = 'Текст поста не может быть пустым';
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts (body, author_id) VALUES (?, ?)');
        $stmt->execute([$body, $_SESSION['user_id']]);
        $success = 'Пост успешно опубликован!';
    }
}
?>

<?php include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main>
    <div class="card">
        <h1>Добавить пост</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="error" style="background: #efe; color: #2c6e2c;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Текст поста</label>
                <textarea name="body" rows="5" required><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
            </div>
            
            <button type="submit">Опубликовать</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/partials/foot.php'; ?>
