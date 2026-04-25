<?php
/**
 * Главный дашборд приложения FitTrack
 */

session_start();
require_once DIR . '/../includes/auth.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$period = $_GET['period'] ?? 'day';
$stats = getUserStats($user['id'], $period);

// Получение целей пользователя
$db = getDB();
$stmt = $db->prepare("
    SELECT * FROM user_goals 
    WHERE user_id = ? AND period = ? AND is_active = 1
");
$stmt->execute([$user['id'], 'daily']);
$goals = [];
while ($row = $stmt->fetch()) {
    $goals[$row['metric_type']] = $row['target_value'];
}

// Значения по умолчанию из настроек
$stepGoal = $goals['steps'] ?? $user['step_goal'] ?? 10000;
$distanceGoal = $goals['distance'] ?? $user['distance_goal_km'] ?? 10.00;
$timeGoal = $goals['time'] ?? $user['time_goal_minutes'] ?? 60;
$calorieGoal = $goals['calories'] ?? $user['calorie_goal'] ?? 500;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack - Дашборд</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dashboard-page <?php echo $user['theme'] ?? 'light'; ?>">
    <div class="app-wrapper">
        <!-- Шапка -->
        <header class="app-header">
            <div class="header-left">
                <i class="fas fa-walking"></i>
                <h1>FitTrack</h1>
            </div>
            <div class="header-right">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['login']); ?>
                </span>
                <a href="profile.php" class="btn-icon" title="Профиль">
                    <i class="fas fa-cog"></i>
                </a>
                <a href="logout.php" class="btn-icon" title="Выйти">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>
        
        <!-- Переключатель периодов -->
        <div class="period-switcher">
            <a href="?period=day" class="period-btn <?php echo $period === 'day' ? 'active' : ''; ?>">
                <i class="fas fa-sun"></i> Сегодня
            </a>
            <a href="?period=week" class="period-btn <?php echo $period === 'week' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-week"></i> Неделя
            </a>
            <a href="?period=month" class="period-btn <?php echo $period === 'month' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Месяц
            </a>
        </div>
        
        <!-- Основная статистика -->
        <div class="stats-grid">
            <!-- Шаги -->
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shoe-prints"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['steps'] ?? 0, 0, '', ' '); ?></span>
                    <span class="stat-label">шагов</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($stats['steps'] ?? 0) / $stepGoal * 100); ?>%"></div>
                    </div>
                    <span class="progress-text">
                        <?php echo number_format($stats['steps'] ?? 0, 0, '', ' '); ?> / <?php echo number_format($stepGoal, 0, '', ' '); ?>
                    </span>
                </div>
            </div>
            
            <!-- Дистанция -->
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-road"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['distance'] ?? 0, 1); ?></span>
                    <span class="stat-label">км</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($stats['distance'] ?? 0) / $distanceGoal * 100); ?>%"></div>
                    </div>
                    <span class="progress-text">
                        <?php echo number_format($stats['distance'] ?? 0, 1); ?> / <?php echo number_format($distanceGoal, 1); ?> км
                    </span>
                </div>
            </div>
            
            <!-- Время -->
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <?php 
                    $timeMinutes = $stats['time_minutes'] ?? 0;
                    $hours = floor($timeMinutes / 60);
                    $minutes = $timeMinutes % 60;
                    ?>
                    <span class="stat-value"><?php echo $hours; ?>:<?php echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?></span>
                    <span class="stat-label">часов</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, $timeMinutes / $timeGoal * 100); ?>%"></div>
                    </div>
                    <span class="progress-text">
                        <?php echo $timeMinutes; ?> / <?php echo $timeGoal; ?> мин
                    </span>
                </div>
            </div>
            
            <!-- Калории -->
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['calories'] ?? 0, 0); ?></span>
                    <span class="stat-label">ккал</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($stats['calories'] ?? 0) / $calorieGoal * 100); ?>%"></div>
                    </div>
                    <span class="progress-text">
                        <?php echo number_format($stats['calories'] ?? 0, 0); ?> / <?php echo number_format($calorieGoal, 0); ?> ккал
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Дополнительная информация -->
        <?php if ($period !== 'day' && isset($stats['avg_steps'])): ?>
        <div class="additional-stats">
            <div class="info-card">
                <i class="fas fa-chart-line"></i>
                <span>Среднее шагов в день: <strong><?php echo number_format($stats['avg_steps'] ?? 0, 0); ?></strong></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Нижняя навигация -->
        <nav class="bottom-nav">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Главная</span>
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Профиль</span>
            </a>
        </nav>
    </div>
</body>
</html>
