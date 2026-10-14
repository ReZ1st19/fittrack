<?php
require_once 'functions.php';
require_login();

$userId = $_SESSION['user_id'];
$user = current_user();
$goals = user_goals();

$calcResult = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_activity') {
    check_csrf();
    $date = $_POST['activity_date'] ?? date('Y-m-d');
    $distance = (float)($_POST['distance_km'] ?? 0);
    $minutes = (int)($_POST['duration_min'] ?? 0);

    if ($distance > 0 && $minutes > 0) {
        $steps = (int)($distance * 1312);
        $calories = (int)($minutes * 7);

        $stmt = db()->prepare("
            INSERT INTO activities (user_id, activity_date, distance_km, duration_min, steps, calories)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                distance_km = distance_km + VALUES(distance_km),
                duration_min = duration_min + VALUES(duration_min),
                steps = steps + VALUES(steps),
                calories = calories + VALUES(calories)
        ");
        $stmt->execute([$userId, $date, $distance, $minutes, $steps, $calories]);

        $calcResult = "Добавлено за $date: $steps шагов, $calories ккал";
    } else {
        $calcResult = 'Введите километры и время';
    }
}

$period = $_GET['period'] ?? 'day';
$date = $_GET['date'] ?? date('Y-m-d');

$stats = get_stats($userId, $period, $date);
$periodGoals = goals_for_period($goals, $period, $date);

$stepsPercent = min(($stats['steps'] / $periodGoals['steps']) * 100, 100);
$distPercent = min(($stats['distance'] / $periodGoals['distance']) * 100, 100);
$calPercent = min(($stats['calories'] / $periodGoals['calories']) * 100, 100);
$hours = round($stats['duration'] / 60, 1);

$periodTitles = ['day' => 'Статистика за день', 'week' => 'Статистика за неделю', 'month' => 'Статистика за месяц'];
$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>FitTrack — Дашборд</title>
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
        <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Главная</a>
        <a href="profile.php"><i class="fas fa-user"></i> Профиль</a>
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
    <h1 class="page-title">Дашборд</h1>
    <p class="page-subtitle">Отслеживайте свою физическую активность</p>

    <div class="period-tabs">
        <a href="?period=day&date=<?= urlencode($date) ?>" class="period-tab <?= $period === 'day' ? 'active' : '' ?>">День</a>
        <a href="?period=week&date=<?= urlencode($date) ?>" class="period-tab <?= $period === 'week' ? 'active' : '' ?>">Неделя</a>
        <a href="?period=month&date=<?= urlencode($date) ?>" class="period-tab <?= $period === 'month' ? 'active' : '' ?>">Месяц</a>
    </div>

    <?php if ($calcResult): ?>
        <div class="success-box"><?= $calcResult ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="card">
            <h3 class="card-title">Калькулятор активности</h3>
            <form method="GET" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label>Дата</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
                </div>
                <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
            </form>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="save_activity">
                <input type="hidden" name="activity_date" value="<?= htmlspecialchars($date) ?>">

                <div class="form-group">
                    <label>Пройдено километров</label>
                    <input type="number" name="distance_km" step="0.1" placeholder="Например: 5" required>
                </div>
                <div class="form-group">
                    <label>Время (минут)</label>
                    <input type="number" name="duration_min" placeholder="Например: 60" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Рассчитать и сохранить</button>
            </form>
        </div>

        <div>
            <h2 style="margin-bottom: 20px;"><?= $periodTitles[$period] ?></h2>

            <div class="stats-grid">
                <div class="metric-card">
                    <div class="metric-label">Шаги</div>
                    <div class="metric-value"><?= number_format($stats['steps'], 0, '.', ' ') ?></div>
                    <div class="metric-goal">из <?= number_format($periodGoals['steps'], 0, '.', ' ') ?></div>
                    <div class="progress-bar"><div class="progress-fill" style="width:<?= $stepsPercent ?>%"></div></div>
                    <div class="metric-percent" style="margin-top:8px;"><?= round($stepsPercent) ?>%</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Километры</div>
                    <div class="metric-value"><?= $stats['distance'] ?></div>
                    <div class="metric-goal">из <?= $periodGoals['distance'] ?> км</div>
                    <div class="progress-bar"><div class="progress-fill" style="width:<?= $distPercent ?>%"></div></div>
                    <div class="metric-percent" style="margin-top:8px;"><?= round($distPercent) ?>%</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Калории</div>
                    <div class="metric-value"><?= number_format($stats['calories'], 0, '.', ' ') ?></div>
                    <div class="metric-goal">из <?= number_format($periodGoals['calories'], 0, '.', ' ') ?></div>
                    <div class="progress-bar"><div class="progress-fill" style="width:<?= $calPercent ?>%"></div></div>
                    <div class="metric-percent" style="margin-top:8px;"><?= round($calPercent) ?>%</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Время</div>
                    <div class="metric-value"><?= $hours ?></div>
                    <div class="metric-goal">часов активности</div>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>