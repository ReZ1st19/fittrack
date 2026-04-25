<?php
/**
 * Страница регистрации
 */

session_start();
require_once DIR . '/../includes/auth.php';
require_once DIR . '/../includes/csrf.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Пожалуйста, обновите страницу.';
    } else {
        $data = [
            'login' => trim($_POST['login'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // Валидация
        $errors = [];
        if (strlen($data['login']) < 3) {
            $errors[] = 'Логин должен быть не менее 3 символов';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный email';
        }
        if (strlen($data['password']) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов';
        }
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Пароли не совпадают';
        }
        
        if (empty($errors)) {
            $result = registerUser($data);
            if ($result['success']) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = $result['message'];
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

$csrfToken = CSRF::generateToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Регистрация</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-walking"></i>
                <h1>FitTrack</h1>
                <p class="auth-subtitle">Создание нового аккаунта</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <?php echo CSRF::tokenField(); ?>
                
                <div class="form-group">
                    <label for="login">
                        <i class="fas fa-user"></i> Логин
                    </label>
                    <input type="text" id="login" name="login" 
                           placeholder="Придумайте логин" required
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" 
                           placeholder="your@email.com" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Телефон
                    </label>
                    <input type="tel" id="phone" name="phone" 
                           placeholder="+79001234567"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Пароль
                    </label>
                    <input type="password" id="password" name="password" 
                           placeholder="Минимум 6 символов" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">
                        <i class="fas fa-lock"></i> Подтверждение пароля
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" 
                           placeholder="Повторите пароль" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Зарегистрироваться
                </button>
            </form>
            
            <div class="auth-links">
                <a href="login.php" class="link-login">
                    <i class="fas fa-sign-in-alt"></i> Уже есть аккаунт? Войти
                </a>
            </div>
        </div>
    </div>
</body>
</html>