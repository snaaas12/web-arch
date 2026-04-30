<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// register.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,      // Для локальной разработки false, в продакшене true
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$error = '';

// Подключение к БД
$pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Валидация
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        // Проверка уникальности email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже зарегистрирован';
        } else {
            // Хеширование пароля и создание пользователя
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            
            $user_id = $pdo->lastInsertId();
            
            // Автологин
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            
            header('Location: /messages.php');
            exit;
        }
    }
}
?>

<?php include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main>
    <div class="card">
        <h1>Регистрация</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Пароль (мин. 6 символов)</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Зарегистрироваться</button>
        </form>
        
        <hr>
        
        <p>Уже есть аккаунт? <a href="/login.php" class="link">Войдите</a></p>
    </div>
</main>

<?php include __DIR__ . '/partials/foot.php'; ?>
