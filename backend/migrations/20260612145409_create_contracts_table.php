<?php

/**
 * Migration: create_contracts_table
 * Gerado em: 2026-06-12 14:54:09
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `create_contracts_table` (
                `id_contract`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_client`           INT UNSIGNED NOT NULL,
                `data_start`          DATE NOT NULL,
                `data_end`            DATE NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`id_client`) REFERENCES `create_clients_table`(`id_client`) ON DELETE CASCADE,
                PRIMARY KEY (`id_contract`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `create_contracts_table`');
    }
};