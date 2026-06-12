<?php

/**
 * Migration: create_items_contract_table
 * Gerado em: 2026-06-12 14:54:36
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `contract_items` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `contract_id` INT UNSIGNED NOT NULL,
                `service_id`  INT UNSIGNED NOT NULL,
                `quantity`    INT UNSIGNED NOT NULL DEFAULT 1,
                `unit_value`  DECIMAL(10,2) NOT NULL,
                `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_items_contract` FOREIGN KEY (`contract_id`)
                    REFERENCES `contracts`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_items_service` FOREIGN KEY (`service_id`)
                    REFERENCES `services`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `contract_items`');
    }
};