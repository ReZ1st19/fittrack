<?php
/**
 * Функции аутентификации и работы с пользователями
 */

require_once DIR . '/../config/database.php';
require_once DIR . '/csrf.php';

/**
 * Проверка, авторизован ли пользователь
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Получение данных текущего пользователя
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.*, s.theme, s.step_goal, s.distance_goal_km, s.time_goal_minutes, s.calorie_goal
        FROM users u
        LEFT JOIN user_settings s ON u.id = s.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Проверка пароля пользователя
 * @param string $login Логин или email
 * @param string $password Пароль
 * @return array|false
 */
function authenticateUser($login, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR login = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Обновляем время последнего входа
        $updateStmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        return $user;
    }
    return false;
}

/**
 * Вход пользователя в систему
 * @param array $user Данные пользователя
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_login'] = $user['login'];
    $_SESSION['user_email'] = $user['email'];
    
    // Генерируем новый CSRF-токен при входе
    CSRF::generateToken();
}

/**
 * Выход пользователя
 */
function logoutUser() {
    CSRF::clearToken();
    session_destroy();
}

/**
 * Регистрация нового пользователя
 * @param array $data Данные формы
 * @return array [success, message, user_id]
 */
function registerUser($data) {
    $db = getDB();
    
    // Проверка существования email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
    }
    
    // Проверка существования логина
    $stmt = $db->prepare("SELECT id FROM users WHERE login = ?");
    $stmt->execute([$data['login']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Пользователь с таким логином уже существует'];
    }
    
    try {
        $db->beginTransaction();
        
        // Хеширование пароля
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Создание пользователя
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, login, phone, created_at, is_active) 
            VALUES (?, ?, ?, ?, NOW(), 1)
        ");
        $stmt->execute([
            $data['email'],
            $passwordHash,
            $data['login'],
            $data['phone'] ?? null
        ]);
        
        $userId = $db->lastInsertId();
        
        // Создание настроек по умолчанию
        $stmt = $db->prepare("
            INSERT INTO user_settings (user_id, theme, step_goal, distance_goal_km, time_goal_minutes, calorie_goal, updated_at)
            VALUES (?, 'light', 10000, 10.00, 60, 500, NOW())
        ");
        $stmt->execute([$userId]);
        
        $db->commit();
        
        return ['success' => true, 'message' => 'Регистрация успешна', 'user_id' => $userId];
        
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage()];
    }
}

/**
 * Обновление профиля пользователя
 * @param int $userId ID пользователя
 * @param array $data Данные для обновления
 * @return array
 */
function updateProfile($userId, $data) {
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        // Обновление основных данных
        $stmt = $db->prepare("UPDATE users SET login = ?, phone = ? WHERE id = ?
        ");
        $stmt->execute([$data['login'], $data['phone'], $userId]);
        
        // Обновление настроек
        $stmt = $db->prepare("
            UPDATE user_settings SET 
                theme = ?,
                step_goal = ?,
                distance_goal_km = ?,
                time_goal_minutes = ?,
                calorie_goal = ?,
                weight_kg = ?,
                height_cm = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([
            $data['theme'] ?? 'light',
            $data['step_goal'] ?? 10000,
            $data['distance_goal_km'] ?? 10.00,
            $data['time_goal_minutes'] ?? 60,
            $data['calorie_goal'] ?? 500,
            $data['weight_kg'] ?? null,
            $data['height_cm'] ?? null,
            $userId
        ]);
        
        $db->commit();
        
        // Обновляем данные в сессии
        $_SESSION['user_login'] = $data['login'];
        
        return ['success' => true, 'message' => 'Профиль обновлен'];
        
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
    }
}

/**
 * Получение статистики пользователя
 * @param int $userId ID пользователя
 * @param string $period day/week/month
 * @return array
 */
function getUserStats($userId, $period = 'day') {
    $db = getDB();
    
    switch ($period) {
        case 'week':
            $stmt = $db->prepare("
                SELECT 
                    SUM(total_steps) as steps,
                    SUM(total_distance_km) as distance,
                    SUM(total_time_minutes) as time_minutes,
                    SUM(total_calories) as calories,
                    AVG(total_steps) as avg_steps
                FROM daily_activity 
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            break;
        case 'month':
            $stmt = $db->prepare("
                SELECT 
                    SUM(total_steps) as steps,
                    SUM(total_distance_km) as distance,
                    SUM(total_time_minutes) as time_minutes,
                    SUM(total_calories) as calories,
                    AVG(total_steps) as avg_steps
                FROM daily_activity 
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            break;
        default: // day
            $stmt = $db->prepare("
                SELECT 
                    total_steps as steps,
                    total_distance_km as distance,
                    total_time_minutes as time_minutes,
                    total_calories as calories
                FROM daily_activity 
                WHERE user_id = ? AND date = CURDATE()
            ");
    }
    
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();
    
    // Если данных за сегодня нет, возвращаем нули
    if (!$stats && $period === 'day') {
        $stats = [
            'steps' => 0,
            'distance' => 0,
            'time_minutes' => 0,
            'calories' => 0
        ];
    }
    
    return $stats ?: [];
}