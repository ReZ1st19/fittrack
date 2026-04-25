<?php
/**
 * Класс для защиты от CSRF-атак
 */
class CSRF {
    
    /**
     * Генерация CSRF-токена и сохранение в сессии
     * @return string
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Получение текущего CSRF-токена
     * @return string|null
     */
    public static function getToken() {
        return $_SESSION['csrf_token'] ?? null;
    }
    
    /**
     * Проверка CSRF-токена
     * @param string $token Токен для проверки
     * @return bool
     */
    public static function verifyToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Очистка CSRF-токена (например, после выхода)
     */
    public static function clearToken() {
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * Генерация HTML-поля с CSRF-токеном
     * @return string
     */
    public static function tokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Проверка CSRF-токена из POST-запроса, с ошибкой при несовпадении
     * @return bool
     */
    public static function check() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!self::verifyToken($token)) {
                http_response_code(403);
                die('Ошибка безопасности: недействительный CSRF-токен.');
            }
        }
        return true;
    }
}