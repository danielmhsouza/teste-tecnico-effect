<?php

/**
 * Migration: create_services_table
 * Gerado em: 2026-06-12 14:54:19
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `services` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`               VARCHAR(150) NOT NULL,
                `base_monthly_value` DECIMAL(10,2) NOT NULL,
                `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `services`');
    }
};