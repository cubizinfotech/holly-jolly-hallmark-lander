CREATE TABLE `cozy_crew_submissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `favorite_star` VARCHAR(255) NOT NULL,
    `participate` ENUM('yes','no') NOT NULL,
    `plotlines` TEXT NOT NULL, -- JSON array (stringified) of selected plotlines
    `message` TEXT NULL,
    `ip_address` VARCHAR(45) NOT NULL, -- IPv4 or IPv6
    `user_agent` VARCHAR(1000) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
