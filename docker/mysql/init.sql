-- URL Shortener Database Schema

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Create login_user table
CREATE TABLE IF NOT EXISTS `login_user` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `oauth_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oauth_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oauth_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oauth_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oauth_first_time` datetime DEFAULT NULL,
  `oauth_last_login` datetime DEFAULT NULL,
  `api_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_user_oauth_type_oauth_id_unique` (`oauth_type`, `oauth_id`),
  UNIQUE KEY `login_user_api_token_unique` (`api_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create url_shortener table
CREATE TABLE IF NOT EXISTS `url_shortener` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lu_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `original_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_url` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` text COLLATE utf8mb4_unicode_ci,
  `og_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashtag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gacode_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fbpixel_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_medium` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_term` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_content` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_shortener_short_url_unique` (`short_url`),
  KEY `url_shortener_lu_id_index` (`lu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create click_log table
CREATE TABLE IF NOT EXISTS `click_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `short_url` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referral_url` text COLLATE utf8mb4_unicode_ci,
  `referral` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `click_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `click_log_us_id_index` (`us_id`),
  KEY `click_log_short_url_index` (`short_url`),
  CONSTRAINT `click_log_us_id_foreign` FOREIGN KEY (`us_id`) REFERENCES `url_shortener` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create hash_tags table
CREATE TABLE IF NOT EXISTS `hash_tags` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash_tags_us_id_index` (`us_id`),
  CONSTRAINT `hash_tags_us_id_foreign` FOREIGN KEY (`us_id`) REFERENCES `url_shortener` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create failed_jobs table for queue
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
