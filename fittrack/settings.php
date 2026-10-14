<?php
require_once 'functions.php';
require_login();

$userId = $_SESSION['user_id'];
$user = current_user();
$goals = user_goals();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $steps = (int)($_POST['steps_goal'] ?? 10000);
    $distance = (float)($_POST['distance_goal'] ?? 10);
    $calories = (int)($_POST['calories_goal'] ?? 500);

    $stmt = db()->prepare("UPDATE goals SET steps_goal = ?, distance_goal = ?, calories_goal = ? WHERE user_id = ?");
    $stmt->execute([$steps, $distance, $calories, $userId]);

    $message = 'Цели сохранены';
    $goals = user_goals();
}

$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Настройки</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= $currentTheme === 'dark' ? 'dark' : '' ?>">

<header class="site-header">
    <a href="dashboard.php" class="site-logo">
        <i class="fas fa-walking"></i>
        <span>FitTrack</span>
    </a>
    <nav class="site-nav">
        <a href="dashboard.php"><i class="fas fa-home"></i> Главная</a>
        <a href="profile.php"><i class="fas fa-user"></i> Профиль</a>
        <a href="settings.php" class="active"><i class="fas fa-cog"></i> Настройки</a>
    </nav>
    <div class="user-menu">
        <span class="username"><?= htmlspecialchars($user['name'] ?: $user['username']) ?></span>
        <?php if ($user['avatar']): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>?t=<?= time() ?>" class="avatar-small" alt="">
        <?php else: ?>
            <div class="avatar-small-icon"><i class="fas fa-user"></i></div>
        <?php endif; ?>
    </div>
</header>

<main class="site-main">
    <h1 class="page-title">Настройки</h1>
    <p class="page-subtitle">Настройте интерфейс и цели</p>

    <?php if ($message): ?>
        <div class="success-box"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-box"><?= $error ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <div class="card">
            <h3 class="card-title">Тема оформления</h3>
            <div class="theme-switcher">
                <a href="?theme=light" class="theme-option <?= $currentTheme === 'light' ? 'active' : '' ?>">Светлая</a>
                <a href="?theme=dark" class="theme-option <?= $currentTheme === 'dark' ? 'active' : '' ?>">Тёмная</a>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Цели на сегодня</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="form-group">
                    <label>Шаги (цель)</label>
                    <input type="number" name="steps_goal" value="<?= (int)$goals['steps_goal'] ?>">
                </div>
                <div class="form-group">
                    <label>Дистанция (км)</label>
                    <input type="number" name="distance_goal" step="0.1" value="<?= (float)$goals['distance_goal'] ?>">
                </div>
                <div class="form-group">
                    <label>Калории (Kcal)</label>
                    <input type="number" name="calories_goal" value="<?= (int)$goals['calories_goal'] ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
                <a href="dashboard.php" class="btn btn-secondary btn-block">Отмена</a>
            </form>
        </div>
    </div>
</main>

</body>
</html>