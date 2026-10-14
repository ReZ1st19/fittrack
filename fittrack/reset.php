<?php
require_once 'functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $message = 'Инструкция отправлена (демо-режим)';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Восстановление пароля</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-container">
        <h2>Восстановление пароля</h2>
        <p class="instruction-text">Введите ваш email или логин, и мы отправим инструкцию по восстановлению.</p>

        <?php if ($message): ?>
            <div class="success-box"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label>Email или логин</label>
                <input type="text" name="credential" placeholder="email@mail.com или логин" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Отправить инструкцию</button>
        </form>

        <div class="form-links" style="justify-content: center;">
            <a href="index.php">Вернуться ко входу</a>
        </div>
    </div>
</body>
<body class="<?= ($_SESSION['theme'] ?? 'light') === 'dark' ? 'dark' : '' ?>">
</html>