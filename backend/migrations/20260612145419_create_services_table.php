<?php

/**
 * Migration: create_services_table
 * Gerado em: 2026-06-12 14:54:19
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `create_services_table` (
                `id_service`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`            VARCHAR(150) NOT NULL,
                `base_value_month` DECIMAL(10,2) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_service`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `create_services_table`');
    }
};