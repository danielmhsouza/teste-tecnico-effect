<?php

/**
 * Migration: create_clients_table
 * Gerado em: 2026-06-12 14:53:55
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `create_clients_table` (
                `id_client`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name`             VARCHAR(150) NOT NULL,
                `cpf_cnpj`         VARCHAR(20) NOT NULL,
                `email`           VARCHAR(150) NOT NULL,
                `status`          Enum('active', 'inactive') NOT NULL DEFAULT 'active',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_client`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `create_clients_table`');
    }
};