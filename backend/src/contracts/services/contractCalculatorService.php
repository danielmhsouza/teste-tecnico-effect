<?php

namespace App\Contracts\Services;

use App\Contracts\Services\Strategies\LoyaltyDiscountStrategy;
use App\Contracts\Services\Strategies\VolumeDiscountStrategy;

/**
 * Calculates the monthly total of a contract by running all registered
 * discount strategies in sequence (Strategy Pattern chain).
 */
class ContractCalculatorService
{
    /** @var ContractDiscountStrategyInterface[] */
    private array $strategies;

    public function __construct(ContractDiscountStrategyInterface ...$strategies)
    {
        $this->strategies = $strategies;
    }

    public static function create(): self
    {
        return new self(
            new VolumeDiscountStrategy(),
            new LoyaltyDiscountStrategy()
        );
    }

    public function calculate(array $contract): array
    {
        $baseTotal = 0.0;

        foreach ($contract['items'] ?? [] as $item) {
            $baseTotal += (float) $item['unit_value'] * (int) $item['quantity'];
        }

        $baseTotal = round($baseTotal, 2);
        $total     = $baseTotal;
        $discounts = [];

        foreach ($this->strategies as $strategy) {
            $newTotal = $strategy->apply($contract, $total);
            if ($newTotal < $total) {
                $discounts[] = [
                    'label'  => $strategy->getLabel(),
                    'amount' => round($total - $newTotal, 2),
                ];
            }
            $total = $newTotal;
        }

        return [
            'base_total'    => $baseTotal,
            'monthly_total' => round($total, 2),
            'discounts'     => $discounts,
        ];
    }
}
