<?php
require_once 'config.php';

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('Ошибка CSRF-токена');
        }
    }
}

function sanitize($value) {
    $value = str_replace(chr(0), '', $value);
    $value = strip_tags($value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($value);
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function current_user() {
    if (!is_logged_in()) return null;
    $stmt = db()->prepare("SELECT id, username, name, email, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function user_goals() {
    if (!is_logged_in()) return ['steps_goal' => 10000, 'distance_goal' => 10, 'calories_goal' => 500];
    $stmt = db()->prepare("SELECT * FROM goals WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $goals = $stmt->fetch();
    return $goals ?: ['steps_goal' => 10000, 'distance_goal' => 10, 'calories_goal' => 500];
}

function get_stats($userId, $period, $date) {
    switch ($period) {
        case 'week':
            $sql = "SELECT 
                        COALESCE(SUM(distance_km), 0) AS distance,
                        COALESCE(SUM(duration_min), 0) AS duration,
                        COALESCE(SUM(steps), 0) AS steps,
                        COALESCE(SUM(calories), 0) AS calories
                    FROM activities
                    WHERE user_id = ? AND YEARWEEK(activity_date, 1) = YEARWEEK(?, 1)";
            $params = [$userId, $date];
            break;
        case 'month':
            $sql = "SELECT 
                        COALESCE(SUM(distance_km), 0) AS distance,
                        COALESCE(SUM(duration_min), 0) AS duration,
                        COALESCE(SUM(steps), 0) AS steps,
                        COALESCE(SUM(calories), 0) AS calories
                    FROM activities
                    WHERE user_id = ? 
                      AND YEAR(activity_date) = YEAR(?) 
                      AND MONTH(activity_date) = MONTH(?)";
            $params = [$userId, $date, $date];
            break;
        default:
            $sql = "SELECT 
                        COALESCE(SUM(distance_km), 0) AS distance,
                        COALESCE(SUM(duration_min), 0) AS duration,
                        COALESCE(SUM(steps), 0) AS steps,
                        COALESCE(SUM(calories), 0) AS calories
                    FROM activities
                    WHERE user_id = ? AND activity_date = ?";
            $params = [$userId, $date];
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function goals_for_period($goals, $period, $date) {
    $dateObj = new DateTime($date);
    if ($period === 'day') {
        return [
            'steps' => (int)$goals['steps_goal'],
            'distance' => (float)$goals['distance_goal'],
            'calories' => (int)$goals['calories_goal']
        ];
    }
    if ($period === 'week') {
        return [
            'steps' => (int)$goals['steps_goal'] * 7,
            'distance' => (float)$goals['distance_goal'] * 7,
            'calories' => (int)$goals['calories_goal'] * 7
        ];
    }
    $daysInMonth = (int)$dateObj->format('t');
    return [
        'steps' => (int)$goals['steps_goal'] * $daysInMonth,
        'distance' => (float)$goals['distance_goal'] * $daysInMonth,
        'calories' => (int)$goals['calories_goal'] * $daysInMonth
    ];
}