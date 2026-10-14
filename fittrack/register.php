<?php
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || strlen($password) < 8) {
        $error = 'Заполните все поля, пароль минимум 8 символов';
    } else {
        $stmt = db()->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Пользователь уже существует';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare("INSERT INTO users (username, name, email, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $username, $email, $hash]);
            $userId = db()->lastInsertId();

            $stmt = db()->prepare("INSERT INTO goals (user_id, steps_goal, distance_goal, calories_goal) VALUES (?, 10000, 10, 500)");
            $stmt->execute([$userId]);

            $success = 'Регистрация успешна! Теперь можно войти.';
        }
    }
}

$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Регистрация</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= $currentTheme === 'dark' ? 'dark' : '' ?>">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-walking"></i>
            <h1>Регистрация</h1>
            <p>Создайте новый аккаунт</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-box"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" placeholder="Придумайте логин" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@mail.com" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Минимум 8 символов" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
        </form>

        <div class="auth-links" style="justify-content: center;">
            <a href="index.php">Уже есть аккаунт? Войти</a>
        </div>
    </div>
</div>

</body>
</html>