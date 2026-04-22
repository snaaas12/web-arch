<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// messages.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем посты с именами авторов
$stmt = $pdo->query('
    SELECT p.id, p.body, p.created_at,
           u.name AS author_name
    FROM posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main>
    <h1>Все посты</h1>
    
    <?php if (empty($posts)): ?>
        <div class="card">
            <p>Пока нет ни одного поста. Будьте первым!</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-author"><?= htmlspecialchars($post['author_name']) ?></div>
                <div class="post-body"><?= htmlspecialchars($post['body']) ?></div>
                <div class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/partials/foot.php'; ?>
