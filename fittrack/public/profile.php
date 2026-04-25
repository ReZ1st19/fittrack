<?php
/**
 * Страница профиля и настроек
 */

session_start();
require_once DIR . '/../includes/auth.php';
require_once DIR . '/../includes/csrf.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$message = '';
$error = '';

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Пожалуйста, обновите страницу.';
    } else {
        $data = [
            'login' => trim($_POST['login'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'theme' => $_POST['theme'] ?? 'light',
            'step_goal' => (int)($_POST['step_goal'] ?? 10000),
            'distance_goal_km' => (float)($_POST['distance_goal_km'] ?? 10.00),
            'time_goal_minutes' => (int)($_POST['time_goal_minutes'] ?? 60),
            'calorie_goal' => (int)($_POST['calorie_goal'] ?? 500),
            'weight_kg' => !empty($_POST['weight_kg']) ? (float)$_POST['weight_kg'] : null,
            'height_cm' => !empty($_POST['height_cm']) ? (int)$_POST['height_cm'] : null
        ];
        
        $result = updateProfile($user['id'], $data);
        if ($result['success']) {
            $message = $result['message'];
            $user = getCurrentUser(); // Обновляем данные
        } else {
            $error = $result['message'];
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
    <title>FitTrack - Профиль</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="profile-page <?php echo $user['theme'] ?? 'light'; ?>">
    <div class="app-wrapper">
        <!-- Шапка -->
        <header class="app-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1>Профиль и настройки</h1>
            </div>
        </header>
        
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
        
        <form method="POST" action="" class="profile-form">
            <?php echo CSRF::tokenField(); ?>
            
            <!-- Личные данные -->
            <div class="form-section">
                <h2><i class="fas fa-user"></i> Личные данные</h2>
                
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" 
                           value="<?php echo htmlspecialchars($user['login']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small class="form-hint">Email нельзя изменить</small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Цели -->
            <div class="form-section">
                <h2><i class="fas fa-bullseye"></i> Дневные цели</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="step_goal">Шаги</label>
                        <input type="number" id="step_goal" name="step_goal" 
                               value="<?php echo $user['step_goal'] ?? 10000; ?>" min="100" step="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="distance_goal_km">Дистанция (км)</label>
                        <input type="number" id="distance_goal_km" name="distance_goal_km" 
                               value="<?php echo $user['distance_goal_km'] ?? 10.00; ?>" min="0.1" step="0.1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="time_goal_minutes">Время (минут)</label>
                        <input type="number" id="time_goal_minutes" name="time_goal_minutes" 
                               value="<?php echo $user['time_goal_minutes'] ?? 60; ?>" min="1" step="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="calorie_goal">Калории</label>
                        <input type="number" id="calorie_goal" name="calorie_goal" 
                               value="<?php echo $user['calorie_goal'] ?? 500; ?>" min="10" step="10">
                    </div>
                </div>
            </div>
            
            <!-- Физические параметры -->
            <div class="form-section">
                <h2><i class="fas fa-weight-scale"></i> Физические параметры</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="weight_kg">Вес (кг)</label>
                        <input type="number" id="weight_kg" name="weight_kg" 
                               value="<?php echo $user['weight_kg'] ?? ''; ?>" min="30" max="300" step="0.1">
                    </div>
                    
                    <div class="form-group">
                        <label for="height_cm">Рост (см)</label>
                        <input type="number" id="height_cm" name="height_cm" 
                               value="<?php echo $user['height_cm'] ?? ''; ?>" min="100" max="250">
                    </div>
                </div>
            </div>
            
            <!-- Тема оформления -->
            <div class="form-section">
                <h2><i class="fas fa-palette"></i> Оформление</h2>
                
                <div class="theme-selector">
                    <label class="theme-option <?php echo ($user['theme'] ?? 'light') === 'light' ? 'active' : ''; ?>">
                        <input type="radio" name="theme" value="light" 
                               <?php echo ($user['theme'] ?? 'light') === 'light' ? 'checked' : ''; ?>>
                        <i class="fas fa-sun"></i> Светлая тема
                    </label>
                    <label class="theme-option <?php echo ($user['theme'] ?? 'light') === 'dark' ? 'active' : ''; ?>">
                        <input type="radio" name="theme" value="dark"
                               <?php echo ($user['theme'] ?? 'light') === 'dark' ? 'checked' : ''; ?>>
                        <i class="fas fa-moon"></i> Тёмная тема
                    </label>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
        
        <!-- Кнопка выхода -->
        <div class="logout-section">
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Выйти из аккаунта
                </a>
        </div>
        
        <!-- Нижняя навигация -->
        <nav class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Главная</span>
            </a>
            <a href="profile.php" class="nav-item active">
                <i class="fas fa-user"></i>
                <span>Профиль</span>
            </a>
        </nav>
    </div>
</body>
</html>