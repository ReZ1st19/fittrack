<?php
/**
 * Страница входа в приложение FitTrack
 */

session_start();
require_once DIR . '/../includes/auth.php';
require_once DIR . '/../includes/csrf.php';

// Если пользователь уже авторизован, перенаправляем на дашборд
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF-токена
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Пожалуйста, обновите страницу.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($login) || empty($password)) {
            $error = 'Пожалуйста, заполните все поля';
        } else {
            $user = authenticateUser($login, $password);
            if ($user) {
                if ($user['is_active']) {
                    loginUser($user);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Ваш аккаунт заблокирован';
                }
            } else {
                $error = 'Неверный логин/email или пароль';
            }
        }
    }
}

// Проверка сообщения после регистрации
if (isset($_GET['registered'])) {
    $success = 'Регистрация успешна! Теперь вы можете войти.';
}

// Генерация CSRF-токена для формы
$csrfToken = CSRF::generateToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Вход</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-walking"></i>
                <h1>FitTrack</h1>
                <p class="auth-subtitle">Отслеживание физической активности</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <?php echo CSRF::tokenField(); ?>
                
                <div class="form-group">
                    <label for="login">
                        <i class="fas fa-user"></i> Логин или Email
                    </label>
                    <input type="text" id="login" name="login" 
                           placeholder="Введите логин или email" required
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Пароль
                    </label>
                    <input type="password" id="password" name="password" 
                           placeholder="Введите пароль" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>
            
            <div class="auth-links">
                <a href="forgot-password.php" class="link-forgot">
                    <i class="fas fa-key"></i> Забыли пароль?
                </a>
                <a href="register.php" class="link-register">
                    <i class="fas fa-user-plus"></i> Регистрация
                </a>
                </div>
            
            <div class="demo-info">
                <p><i class="fas fa-info-circle"></i> Демо-доступ:</p>
                <p>Логин: <code>demo</code> / Пароль: <code>123456</code></p>
            </div>
        </div>
    </div>
</body>
</html>