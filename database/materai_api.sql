-- ============================================================
--  SeriCheck — Sistem Verifikasi Materai Proposal
--  Database: materai_api
--  MySQL / MariaDB
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `materai_api`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `materai_api`;

-- ============================================================
-- 1. USERS
--    Pengguna internal yang bisa login ke dashboard
-- ============================================================
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL,
  `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
  `password`          VARCHAR(255)    NOT NULL,
  `remember_token`    VARCHAR(100)    NULL DEFAULT NULL,
  `created_at`        TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. API CLIENTS
--    Setiap web/aplikasi yang ingin menggunakan API
--    wajib terdaftar di sini dan punya api_key unik
-- ============================================================
CREATE TABLE `api_clients` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`        VARCHAR(255)    NOT NULL COMMENT 'Nama aplikasi/web, contoh: Web Fakultas Teknik',
  `api_key`     VARCHAR(64)     NOT NULL COMMENT 'Token unik yang dikirim di header X-API-Key',
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `keterangan`  TEXT            NULL     COMMENT 'Deskripsi tambahan tentang client ini',
  `last_used_at` TIMESTAMP      NULL DEFAULT NULL COMMENT 'Waktu terakhir API key ini digunakan',
  `created_at`  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_clients_api_key_unique` (`api_key`),
  KEY `api_clients_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. MATERAI
--    Data utama hasil upload & verifikasi materai
-- ============================================================
CREATE TABLE `materai` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_client_id`  BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Siapa yang upload (dari client mana)',
  `nomor_seri`     VARCHAR(255)    NULL DEFAULT NULL,
  `jenis`          ENUM('fisik','elektronik') NULL DEFAULT NULL,
  `nama_file`      VARCHAR(255)    NOT NULL,
  `path_file`      VARCHAR(255)    NOT NULL,
  `status`         ENUM('unik','duplikat','tidak_terbaca') NULL DEFAULT NULL,

  -- Relasi duplikat: menunjuk ke materai PERTAMA yang punya nomor seri ini
  `duplikat_dari_id` BIGINT UNSIGNED NULL DEFAULT NULL
    COMMENT 'ID materai pertama (yang asli) jika ini adalah duplikat',

  -- Hitung berapa kali nomor seri ini muncul lagi setelah pertama
  `duplikat_count`   INT UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Jumlah duplikat yang ditemukan (hanya diisi di materai pertama)',

  `confidence`     TINYINT UNSIGNED NULL DEFAULT NULL COMMENT 'Confidence OCR 0-100',
  `raw_text`       TEXT            NULL DEFAULT NULL COMMENT 'Raw text hasil OCR',
  `created_at`     TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`     TIMESTAMP       NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `materai_nomor_seri_index`      (`nomor_seri`),
  KEY `materai_status_index`          (`status`),
  KEY `materai_api_client_id_index`   (`api_client_id`),
  KEY `materai_duplikat_dari_id_index`(`duplikat_dari_id`),

  CONSTRAINT `fk_materai_api_client`
    FOREIGN KEY (`api_client_id`)
    REFERENCES `api_clients` (`id`)
    ON DELETE SET NULL,

  CONSTRAINT `fk_materai_duplikat`
    FOREIGN KEY (`duplikat_dari_id`)
    REFERENCES `materai` (`id`)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. API LOGS
--    Catat setiap request yang masuk ke API
--    (berguna untuk audit & debug)
-- ============================================================
CREATE TABLE `api_logs` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `method`        VARCHAR(10)     NOT NULL COMMENT 'GET, POST, dll',
  `endpoint`      VARCHAR(255)    NOT NULL,
  `status_code`   SMALLINT        NOT NULL,
  `ip_address`    VARCHAR(45)     NULL DEFAULT NULL,
  `request_body`  TEXT            NULL DEFAULT NULL COMMENT 'Body request (tanpa file biner)',
  `response_body` TEXT            NULL DEFAULT NULL COMMENT 'Body response JSON',
  `duration_ms`   INT             NULL DEFAULT NULL COMMENT 'Durasi proses dalam milidetik',
  `created_at`    TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_logs_api_client_id_index` (`api_client_id`),
  KEY `api_logs_created_at_index`    (`created_at`),
  CONSTRAINT `fk_api_logs_client`
    FOREIGN KEY (`api_client_id`)
    REFERENCES `api_clients` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PERSONAL ACCESS TOKENS (Sanctum)
--    Untuk login user dashboard
-- ============================================================
CREATE TABLE `personal_access_tokens` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255)    NOT NULL,
  `tokenable_id`   BIGINT UNSIGNED NOT NULL,
  `name`           VARCHAR(255)    NOT NULL,
  `token`          VARCHAR(64)     NOT NULL,
  `abilities`      TEXT            NULL,
  `last_used_at`   TIMESTAMP       NULL,
  `expires_at`     TIMESTAMP       NULL,
  `created_at`     TIMESTAMP       NULL,
  `updated_at`     TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index`
      (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. CACHE & JOBS (Laravel default)
-- ============================================================
CREATE TABLE `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` INT(11)      NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)    NOT NULL,
  `payload`      LONGTEXT        NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED    NULL,
  `available_at` INT UNSIGNED    NOT NULL,
  `created_at`   INT UNSIGNED    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA AWAL (Seeder)
-- ============================================================

-- User admin
INSERT INTO `users` (`name`, `email`, `password`, `created_at`, `updated_at`) VALUES
('Admin', 'admin@sericheck.id', '$2y$12$r6880sdUp19qqyEt5m0mCu.Ag/M62QLiwXid7BdMZ19dxgZ.4u1ue', NOW(), NOW());
-- password: password

-- Contoh API clients yang terdaftar
INSERT INTO `api_clients` (`nama`, `api_key`, `is_active`, `keterangan`, `created_at`, `updated_at`) VALUES
('Web Fakultas Teknik',    'sk_ft_a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4', 1, 'Sistem pengumpulan proposal skripsi Fak. Teknik',       NOW(), NOW()),
('Web Fakultas Ekonomi',   'sk_fe_b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5', 1, 'Sistem pengumpulan proposal PKM Fak. Ekonomi',          NOW(), NOW()),
('Sistem BAAK Pusat',      'sk_baak_c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6', 1, 'Dashboard verifikasi terpusat BAAK',                    NOW(), NOW()),
('Aplikasi Mobile KKN',    'sk_kkn_d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1', 0, 'Nonaktif — sedang dalam pengembangan',                  NOW(), NOW());

COMMIT;
