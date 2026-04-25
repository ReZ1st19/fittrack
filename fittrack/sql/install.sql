-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0:3306
-- Время создания: Апр 25 2026 г., 11:10
-- Версия сервера: 8.0.45
-- Версия PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `fittrack_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `daily_activity`
--

CREATE TABLE `daily_activity` (
  `id` int NOT NULL COMMENT 'ID записи',
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `date` date NOT NULL COMMENT 'Дата активности',
  `total_steps` int NOT NULL DEFAULT '0' COMMENT 'Шаги за день',
  `total_distance_km` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT 'Дистанция (км)',
  `total_time_minutes` int NOT NULL DEFAULT '0' COMMENT 'Время активности (мин)',
  `total_calories` int NOT NULL DEFAULT '0' COMMENT 'Калории за день',
  `avg_speed_kmh` decimal(4,2) DEFAULT NULL COMMENT 'Средняя скорость',
  `max_heart_rate` int DEFAULT NULL COMMENT 'Макс. пульс',
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Последнее обновление'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Дневная активность';

-- --------------------------------------------------------

--
-- Структура таблицы `device_sync`
--

CREATE TABLE `device_sync` (
  `id` int NOT NULL COMMENT 'ID записи',
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID устройства',
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название устройства',
  `last_sync_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последняя синхронизация',
  `sync_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Токен синхронизации',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активно ли устройство'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Синхронизация устройств';

-- --------------------------------------------------------

--
-- Структура таблицы `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Токены восстановления пароля';

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL COMMENT 'ID пользователя',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Электронная почта',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Хешированный пароль',
  `login` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Логин для входа',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `last_login_at` datetime DEFAULT NULL COMMENT 'Последний вход',
  `phone` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Телефон',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активен ли аккаунт'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Пользователи приложения';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `login`, `created_at`, `last_login_at`, `phone`, `is_active`) VALUES
(1, 'demo@fittrack.ru', '$2y$10$YourHashHereReplaceWithRealBcryptHash', 'demo', '2026-04-25 10:50:19', NULL, '+79001234567', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `user_goals`
--

CREATE TABLE `user_goals` (
  `id` int NOT NULL COMMENT 'ID цели',
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `metric_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Тип метрики: steps, distance, time, calories',
  `period` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Период: daily, weekly, monthly',
  `target_value` decimal(10,2) NOT NULL COMMENT 'Целевое значение',
  `start_date` date NOT NULL COMMENT 'Дата начала',
  `end_date` date DEFAULT NULL COMMENT 'Дата окончания',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активна ли цель',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Цели пользователя';

-- --------------------------------------------------------

--
-- Структура таблицы `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int NOT NULL COMMENT 'ID настройки',
  `user_id` int NOT NULL COMMENT 'ID пользователя',
  `theme` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light' COMMENT 'Тема оформления: light/dark',
  `step_goal` int NOT NULL DEFAULT '10000' COMMENT 'Цель по шагам',
  `distance_goal_km` decimal(6,2) NOT NULL DEFAULT '10.00' COMMENT 'Цель по дистанции (км)',
  `time_goal_minutes` int NOT NULL DEFAULT '60' COMMENT 'Цель по времени (минуты)',
  `calorie_goal` int NOT NULL DEFAULT '500' COMMENT 'Цель по калориям',
  `weight_kg` decimal(5,2) DEFAULT NULL COMMENT 'Вес (кг)',
  `height_cm` int DEFAULT NULL COMMENT 'Рост (см)',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Настройки пользователя';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `daily_activity`
--
ALTER TABLE `daily_activity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_date` (`user_id`,`date`);

--
-- Индексы таблицы `device_sync`
--
ALTER TABLE `device_sync`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_device` (`user_id`,`device_id`);

--
-- Индексы таблицы `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_token` (`email`,`token`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD UNIQUE KEY `uk_login` (`login`);

--
-- Индексы таблицы `user_goals`
--
ALTER TABLE `user_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_goals` (`user_id`,`metric_type`,`period`,`is_active`);

--
-- Индексы таблицы `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `daily_activity`
--
ALTER TABLE `daily_activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID записи';

--
-- AUTO_INCREMENT для таблицы `device_sync`
--
ALTER TABLE `device_sync`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID записи';

--
-- AUTO_INCREMENT для таблицы `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID пользователя', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `user_goals`
--
ALTER TABLE `user_goals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID цели', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID настройки', AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `daily_activity`
--
ALTER TABLE `daily_activity`
  ADD CONSTRAINT `fk_daily_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `device_sync`
--
ALTER TABLE `device_sync`
  ADD CONSTRAINT `fk_device_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_goals`
--
ALTER TABLE `user_goals`
  ADD CONSTRAINT `fk_goals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
