<?php

/**
 * Migration: create_discount_strategies_table
 * Gerado em: 2026-06-12 20:00:00
 */

return new class {

    public function up(\PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS `discount_strategies` (
                `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `name`            VARCHAR(50)   NOT NULL,
                `label`           VARCHAR(150)  NOT NULL,
                `discount_rate`   DECIMAL(5,4)  NOT NULL COMMENT '0.10 = 10%',
                `threshold_value` DECIMAL(10,2) NOT NULL COMMENT 'Interpretado pela strategy correspondente',
                `threshold_type`  VARCHAR(50)   NOT NULL COMMENT 'items_count | months',
                `is_active`       TINYINT(1)    NOT NULL DEFAULT 1,
                `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_strategies_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL);

        // Seed das duas estratégias padrão
        $pdo->exec(<<<SQL
            INSERT IGNORE INTO `discount_strategies`
                (`name`, `label`, `discount_rate`, `threshold_value`, `threshold_type`)
            VALUES
                ('volume',  'Desconto por Volume',     0.1000,  3, 'items_count'),
                ('loyalty', 'Desconto por Fidelidade', 0.0500, 12, 'months');
        SQL);
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `discount_strategies`');
    }
};
