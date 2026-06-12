<?php

/**
 * Helpers compartilhados entre os testes do ERP.
 * Incluído automaticamente por cada arquivo de teste.
 */


if (!defined('APP_ROOT')) {
    define('APP_ROOT',      dirname(__DIR__));
    define('CRESCENT_ROOT', APP_ROOT . '/crescent');
    require CRESCENT_ROOT . '/utils/env.php';
    \Crescent\Utils\Env::load(APP_ROOT . '/.env');
}

require_once CRESCENT_ROOT . '/init.php';

use App\Contracts\Services\ContractDiscountStrategyInterface;


if (!function_exists('makeVolumeStrategy')) {
    /**
     * Strategy de volume com config fixa (sem acesso ao DB).
     * Desconto aplicado quando: itens distintos > threshold OU qty acumulada > threshold*2.
     */
    function makeVolumeStrategy(float $rate, int $threshold, bool $active = true): ContractDiscountStrategyInterface
    {
        return new class($rate, $threshold, $active) implements ContractDiscountStrategyInterface {
            public function __construct(
                private float $rate,
                private int   $threshold,
                private bool  $active
            ) {}

            public function getLabel(): string { return 'Desconto por Volume'; }

            public function apply(array $contract, float $currentTotal): float
            {
                if (!$this->active) return $currentTotal;
                $items    = $contract['items'] ?? [];
                $count    = count($items);
                $totalQty = (int) array_sum(array_column($items, 'quantity'));
                if ($count > $this->threshold || $totalQty > ($this->threshold * 2)) {
                    return round($currentTotal * (1 - $this->rate), 2);
                }
                return $currentTotal;
            }
        };
    }
}

if (!function_exists('makeLoyaltyStrategy')) {
    /**
     * Strategy de fidelidade com config fixa (sem acesso ao DB).
     * Desconto aplicado quando: meses de vigência >= minMonths.
     */
    function makeLoyaltyStrategy(float $rate, int $minMonths, bool $active = true): ContractDiscountStrategyInterface
    {
        return new class($rate, $minMonths, $active) implements ContractDiscountStrategyInterface {
            public function __construct(
                private float $rate,
                private int   $minMonths,
                private bool  $active
            ) {}

            public function getLabel(): string { return 'Desconto por Fidelidade'; }

            public function apply(array $contract, float $currentTotal): float
            {
                if (!$this->active) return $currentTotal;
                if (empty($contract['start_date'])) return $currentTotal;
                $start  = new \DateTimeImmutable($contract['start_date']);
                $diff   = $start->diff(new \DateTimeImmutable());
                $months = ($diff->y * 12) + $diff->m;
                if ($months >= $this->minMonths) {
                    return round($currentTotal * (1 - $this->rate), 2);
                }
                return $currentTotal;
            }
        };
    }
}

if (!function_exists('monthsAgo')) {
    /** Retorna uma data de N meses atrás no formato Y-m-d. */
    function monthsAgo(int $months): string
    {
        return (new \DateTimeImmutable())->modify("-{$months} months")->format('Y-m-d');
    }
}

if (!function_exists('makeContract')) {
    /** Contrato fictício para testes. */
    function makeContract(array $items, string $startDate = ''): array
    {
        return [
            'id'         => 0,
            'start_date' => $startDate ?: date('Y-m-d'),
            'status'     => 'active',
            'items'      => $items,
        ];
    }
}

if (!function_exists('makeItem')) {
    /** Item fictício de contrato. */
    function makeItem(int $serviceId, int $qty, float $unitValue): array
    {
        return ['service_id' => $serviceId, 'quantity' => $qty, 'unit_value' => $unitValue];
    }
}

if (!function_exists('in_array_contains')) {
    /** Verifica se algum item do array contém a substring dada. */
    function in_array_contains(array $arr, string $needle): bool
    {
        foreach ($arr as $msg) {
            if (str_contains($msg, $needle)) return true;
        }
        return false;
    }
}

if (!function_exists('validateStrategyInput')) {
    /** Replica a lógica de validação do StrategiesService sem acesso ao DB. */
    function validateStrategyInput(array $body): array
    {
        $errors = [];
        if (isset($body['discount_rate'])) {
            $rate = (float) $body['discount_rate'];
            if ($rate <= 0 || $rate > 1) {
                $errors[] = 'discount_rate deve ser um valor entre 0.01 e 1.00.';
            }
        }
        if (isset($body['threshold_value']) && (float) $body['threshold_value'] <= 0) {
            $errors[] = 'threshold_value deve ser maior que zero.';
        }
        return $errors;
    }
}
