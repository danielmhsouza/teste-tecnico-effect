<?php

/**
 * Migration: create_clients_table
 * Gerado em: 2026-06-12 14:53:55
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `clients` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`       VARCHAR(150) NOT NULL,
                `document`   VARCHAR(20)  NOT NULL,
                `email`      VARCHAR(150) NOT NULL,
                `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_clients_document` (`document`),
                UNIQUE KEY `uq_clients_email`    (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `clients`');
    }
};