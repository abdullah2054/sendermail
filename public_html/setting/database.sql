-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 21 Eyl 2024, 21:31:54
-- Sunucu sürümü: 10.11.8-MariaDB-cll-lve
-- PHP Sürümü: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `u929469444_phplist`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin`
--

CREATE TABLE `admin` (
  `mail` varchar(250) NOT NULL DEFAULT '',
  `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `admin`
--



-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `EmailAddresses`
--

CREATE TABLE `EmailAddresses` (
  `id` int(11) NOT NULL,
  `email` varchar(250) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `EmailAddresses`

--
-- Tablo için tablo yapısı `EmailDrafts`
--

CREATE TABLE `EmailDrafts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
CREATE TABLE `EmailGroups` (
  `id` int(11) NOT NULL,
  `email_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

--
-- Tablo için tablo yapısı `Groups`
--

CREATE TABLE `Groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Tablo için tablo yapısı `Newsletters`
--

CREATE TABLE `Newsletters` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `status` varchar(5) NOT NULL DEFAULT '0',
  `use_smtp` varchar(250) NOT NULL DEFAULT '0',
  `smtp_host` varchar(250) NOT NULL,
  `smtp_port` varchar(250) NOT NULL,
  `smtp_username` varchar(250) NOT NULL,
  `smtp_password` varchar(250) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `user_mail` varchar(250) NOT NULL,
  `mail_limit` int(11) DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo için tablo yapısı `Tasks`
--

CREATE TABLE `Tasks` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `newsletter_id` int(150) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(5) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Tablo için indeksler `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Tablo için indeksler `EmailAddresses`
--
ALTER TABLE `EmailAddresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id` (`id`,`email`,`name`,`date`,`status`);

--
-- Tablo için indeksler `EmailDrafts`
--
ALTER TABLE `EmailDrafts`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `EmailGroups`
--
ALTER TABLE `EmailGroups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_id` (`email_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Tablo için indeksler `Groups`
--
ALTER TABLE `Groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_name` (`group_name`);

--
-- Tablo için indeksler `Newsletters`
--
ALTER TABLE `Newsletters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `Tasks`
--
ALTER TABLE `Tasks`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `EmailAddresses`
--
ALTER TABLE `EmailAddresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46472;

--
-- Tablo için AUTO_INCREMENT değeri `EmailDrafts`
--
ALTER TABLE `EmailDrafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `EmailGroups`
--
ALTER TABLE `EmailGroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50001;

--
-- Tablo için AUTO_INCREMENT değeri `Groups`
--
ALTER TABLE `Groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `Newsletters`
--
ALTER TABLE `Newsletters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Tablo için AUTO_INCREMENT değeri `Tasks`
--
ALTER TABLE `Tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46075;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `EmailGroups`
--
ALTER TABLE `EmailGroups`
  ADD CONSTRAINT `EmailGroups_ibfk_1` FOREIGN KEY (`email_id`) REFERENCES `EmailAddresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `EmailGroups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
--
