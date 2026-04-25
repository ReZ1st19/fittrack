<?php
/**
 * Конфигурация подключения к базе данных
 */

// Настройки для Open Server (по умолчанию)
define('DB_HOST', 'localhost');
define('DB_NAME', 'fittrack_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // На Open Server пароль пустой по умолчанию
define('DB_CHARSET', 'utf8mb4');

/**
 * Получение соединения с БД через PDO
 * @return PDO
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Ошибка подключения к БД: " . $e->getMessage());
        }
    }
    
    return $db;
}