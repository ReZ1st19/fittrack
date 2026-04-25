<?php
/**
 * Страница восстановления пароля
 */

session_start();
require_once DIR . '/../includes/csrf.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности.';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // В реальном приложении здесь отправка email
            $message = 'Инструкции по восстановлению отправлены на указанный email (демо-режим)';
        } else {
            $error = 'Введите корректный email';
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
    <title>FitTrack - Восстановление пароля</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-walking"></i>
                <h1>FitTrack</h1>
                <p class="auth-subtitle">Восстановление пароля</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <?php echo CSRF::tokenField(); ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" 
                           placeholder="Введите email, указанный при регистрации" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Восстановить пароль
                </button>
            </form>
            
            <div class="auth-links">
                <a href="login.php" class="link-login">
                    <i class="fas fa-arrow-left"></i> Вернуться ко входу
                </a>
            </div>
        </div>
    </div>
</body>
</html>