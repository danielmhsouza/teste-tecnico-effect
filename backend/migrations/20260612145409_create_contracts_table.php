<?php

/**
 * Migration: create_contracts_table
 * Gerado em: 2026-06-12 14:54:09
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `contracts` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `client_id`  INT UNSIGNED NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date`   DATE NULL,
                `status`     ENUM('active','canceled') NOT NULL DEFAULT 'active',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_contracts_client` FOREIGN KEY (`client_id`)
                    REFERENCES `clients`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `contracts`');
    }
};