<?php

/**
 * Migration: create_items_contract_table
 * Gerado em: 2026-06-12 14:54:36
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `create_items_contract_table` (
                `id_item`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_contract`     INT UNSIGNED NOT NULL,
                `id_service`      INT UNSIGNED NOT NULL,
                `quantity`        INT UNSIGNED NOT NULL,
                `value_month`     DECIMAL(10,2) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`id_contract`) REFERENCES `create_contracts_table`(`id_contract`) ON DELETE CASCADE,
                FOREIGN KEY (`id_service`) REFERENCES `create_services_table`(`id_service`), 
                PRIMARY KEY (`id_item`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `create_items_contract_table`');
    }
};