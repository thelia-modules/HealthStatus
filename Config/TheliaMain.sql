
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- health_keys
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `health_keys`;

CREATE TABLE `health_keys`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `admin_id` INTEGER NOT NULL,
    `secret_key` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `health_keys_fi_8e51ba` (`admin_id`),
    CONSTRAINT `health_keys_fk_8e51ba`
        FOREIGN KEY (`admin_id`)
        REFERENCES `admin` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
