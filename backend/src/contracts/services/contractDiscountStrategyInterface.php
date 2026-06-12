<?php

namespace App\Contracts\Services;

interface ContractDiscountStrategyInterface
{
    /**
     * Apply a discount/surcharge rule.
     *
     * @param  array $contract     Contract data array (must include 'items' and 'start_date')
     * @param  float $currentTotal Running total before this strategy is applied
     * @return float               Adjusted total after applying the rule
     */
    public function apply(array $contract, float $currentTotal): float;

    /**
     * Human-readable label for this strategy (used in discount breakdown).
     */
    public function getLabel(): string;
}
