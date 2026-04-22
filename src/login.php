<?php
// login.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$error = '';

$pdo = new PDO('mysql:host=localhost;dbname=boardy;charset=utf8', 'boardy', '12boardy');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Неверный email или пароль';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: /messages.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}
?>

<?php include __DIR__ . '/partials/head.php'; ?>
<?php include __DIR__ . '/partials/nav.php'; ?>

<main>
    <div class="card">
        <h1>Вход</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Войти</button>
        </form>
        
        <hr>
        
        <p>Нет аккаунта? <a href="/register.php" class="link">Зарегистрируйтесь</a></p>
    </div>
</main>

<?php include __DIR__ . '/partials/foot.php'; ?>
