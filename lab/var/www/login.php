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
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
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
    <div class="card" style="max-width: 400px; margin: 40px auto;">
        <h1 style="text-align: center; margin-bottom: 24px;">Вход</h1>

        <?php if ($error): ?>
            <div class="error" style="margin-bottom: 16px;"><?= htmlspecialchars($error) ?></div>
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

            <button type="submit" style="width: 100%; margin-bottom: 24px;">Войти</button>
        </form>

        <!-- Разделитель "или" -->
        <div style="
            text-align: center;
            margin-bottom: 24px;
            position: relative;
            border-top: 1px solid #dee2e6;
        ">
            <span style="
                background: white;
                padding: 0 10px;
                position: relative;
                top: -11px;
                color: #6c757d;
                font-size: 14px;
            ">или</span>
        </div>

        <!-- Кнопка входа через GitHub - во всю ширину -->
        <a href="/oauth-github.php" style="
            display: block;
            width: 100%;
            background: #24292e;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            border: none;
            box-sizing: border-box;
        ">
            <svg height="18" viewBox="0 0 16 16" width="18" style="display: inline; margin-right: 8px; vertical-align: text-bottom;">
                <path fill="white" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>
            </svg>
            Войти через GitHub
        </a>

        <p style="text-align: center; margin-top: 24px; margin-bottom: 0;">
            Нет аккаунта? <a href="/register.php" class="link">Зарегистрируйтесь</a>
        </p>
    </div>
</main>

<?php include __DIR__ . '/partials/foot.php'; ?>
