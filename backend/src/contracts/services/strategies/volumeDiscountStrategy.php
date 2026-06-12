<?php

namespace App\Contracts\Services\Strategies;

use App\Contracts\Services\ContractDiscountStrategyInterface;
use App\Strategies\Models\StrategiesModel;

/**
 * Progressive volume discount (DB-driven).
 *
 * Applies a discount when the contract has more than `threshold_value`
 * distinct service items OR the cumulative quantity exceeds `threshold_value * 2`.
 * Rate and threshold are loaded from the `discount_strategies` table.
 */
class VolumeDiscountStrategy implements ContractDiscountStrategyInterface
{
    private static ?array $config = null;

    private function config(): array
    {
        if (self::$config === null) {
            self::$config = StrategiesModel::findWhere(['name' => 'volume'])
                ?? ['label' => 'Desconto por Volume', 'discount_rate' => 0.10, 'threshold_value' => 3, 'is_active' => 1];
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

        $items     = $contract['items'] ?? [];
        $itemCount = count($items);
        $totalQty  = (int) array_sum(array_column($items, 'quantity'));
        $threshold = (int) $cfg['threshold_value'];

        if ($itemCount > $threshold || $totalQty > ($threshold * 2)) {
            return round($currentTotal * (1 - (float) $cfg['discount_rate']), 2);
        }

        return $currentTotal;
    }
}
