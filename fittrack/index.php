<?php
require_once 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $login = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Вход</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= $currentTheme === 'dark' ? 'dark' : '' ?>">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-walking"></i>
            <h1>FitTrack</h1>
            <p>Мониторинг физической активности</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" placeholder="Введите логин" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Войти</button>
        </form>

        <div class="auth-links">
            <a href="register.php">Регистрация</a>
            <a href="reset.php">Забыли пароль?</a>
        </div>
    </div>
</div>

</body>
</html>