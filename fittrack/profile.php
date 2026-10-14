<?php
require_once 'functions.php';
require_login();

$userId = $_SESSION['user_id'];
$user = current_user();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    check_csrf();
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $error = 'Email не может быть пустым';
    } else {
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);

        if ($stmt->fetch()) {
            $error = 'Этот email уже занят';
        } else {
            if ($password !== '') {
                if (strlen($password) < 8) {
                    $error = 'Пароль минимум 8 символов';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $hash, $userId]);
                    $message = 'Профиль обновлён';
                }
            } else {
                $stmt = db()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $userId]);
                $message = 'Профиль обновлён';
            }
        }
    }
    $user = current_user();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
    check_csrf();
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed)) {
            $error = 'Разрешены только JPG, PNG, GIF, WEBP';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Файл больше 2 МБ';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/avatars/';

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                if ($user['avatar'] && file_exists(__DIR__ . '/' . $user['avatar'])) {
                    @unlink(__DIR__ . '/' . $user['avatar']);
                }
                $avatarPath = 'uploads/avatars/' . $filename;
                $stmt = db()->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$avatarPath, $userId]);
                $message = 'Аватар обновлён';
                $user = current_user();
            } else {
                $error = 'Не удалось сохранить файл';
            }
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Профиль</title>
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
        <a href="profile.php" class="active"><i class="fas fa-user"></i> Профиль</a>
        <a href="settings.php"><i class="fas fa-cog"></i> Настройки</a>
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
    <h1 class="page-title">Профиль</h1>
    <p class="page-subtitle">Управляйте своими данными</p>

    <?php if ($error): ?><div class="error-box"><?= $error ?></div><?php endif; ?>
    <?php if ($message): ?><div class="success-box"><?= $message ?></div><?php endif; ?>

    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="avatar-wrapper">
                <?php if ($user['avatar']): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>?t=<?= time() ?>" alt="Аватар">
                <?php else: ?>
                    <i class="fas fa-user-circle profile-avatar"></i>
                <?php endif; ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['name'] ?: $user['username']) ?></div>
            <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="upload_avatar">
                <div class="form-group">
                    <label>Сменить аватар</label>
                    <input type="file" name="avatar" accept="image/*">
                </div>
                <button type="submit" class="btn btn-secondary btn-block">Загрузить</button>
            </form>
        </div>

        <div class="card">
            <h3 class="card-title">Личные данные</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Имя (отображаемое)</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Введите имя">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Новый пароль (оставьте пустым)</label>
                    <input type="password" name="password" placeholder="Минимум 8 символов">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Сохранить изменения</button>
            </form>

            <a href="?logout=1" class="btn btn-danger btn-block" style="margin-top:12px;">Выйти из аккаунта</a>
        </div>
    </div>
</main>

</body>
</html>