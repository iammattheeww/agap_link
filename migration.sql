-- ─── AGAP-Link Database Migration ───────────────────────────────────────────
-- Run this against your agap_link database after restoring the base schema.

-- Table: announcements (ADMIN-3 / ADD-1)
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(255) NOT NULL,
  `content`         TEXT NOT NULL,
  `image_path`      VARCHAR(255) DEFAULT NULL,
  `created_by`      INT(11) NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`announcement_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `announcements_ibfk_1`
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lgu_users (ADMIN-5)
CREATE TABLE IF NOT EXISTS `lgu_users` (
  `lgu_user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `agency_id`   INT(11) NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `email`       VARCHAR(255) NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`lgu_user_id`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `lgu_users_ibfk_1`
    FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`agency_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample agencies (required for lgu_users FK — add only if agencies table is empty)
INSERT IGNORE INTO `agencies` (`name`, `type`) VALUES
  ('Brgy. Granada', 'Barangay'),
  ('Brgy. Alangilan', 'Barangay'),
  ('Singcang Airport', 'Government');
