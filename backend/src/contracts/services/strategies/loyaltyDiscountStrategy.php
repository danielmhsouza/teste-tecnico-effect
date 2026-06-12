<?php

namespace App\Contracts\Services\Strategies;

use App\Contracts\Services\ContractDiscountStrategyInterface;
use App\Strategies\Models\StrategiesModel;

/**
 * Loyalty discount (DB-driven).
 *
 * Applies a discount for contracts active for at least `threshold_value` months.
 * Rate and threshold are loaded from the `discount_strategies` table.
 */
class LoyaltyDiscountStrategy implements ContractDiscountStrategyInterface
{
    private static ?array $config = null;

    private function config(): array
    {
        if (self::$config === null) {
            self::$config = StrategiesModel::findWhere(['name' => 'loyalty'])
                ?? ['label' => 'Desconto por Fidelidade', 'discount_rate' => 0.05, 'threshold_value' => 12, 'is_active' => 1];
        }
        return self::$config;
    }

    public function getLabel(): string
    {
        return $this->config()['label'];
    }

    public function apply(array $contract, float $currentTotal): float
    {
        $cfg = $this->config();

        if (!(bool) ($cfg['is_active'] ?? 1)) {
            return $currentTotal;
        }

        if (empty($contract['start_date'])) {
            return $currentTotal;
        }

        $startDate = new \DateTimeImmutable($contract['start_date']);
        $diff      = $startDate->diff(new \DateTimeImmutable());
        $months    = ($diff->y * 12) + $diff->m;

        if ($months >= (int) $cfg['threshold_value']) {
            return round($currentTotal * (1 - (float) $cfg['discount_rate']), 2);
        }

        return $currentTotal;
    }
}
