<?php

/**
 * Testes — Estratégias de desconto (volume, fidelidade e cadeia encadeada).
 * Execute com: php crecli.php test
 */

use Crescent\Utils\Tests;
use App\Contracts\Services\ContractCalculatorService;

require_once __DIR__ . '/helpers.php';


Tests::describe('VolumeDiscountStrategy — desconto por volume', function () {

    Tests::it('não aplica desconto no limite exato de itens (threshold = 3)', function () {
        $calc   = new ContractCalculatorService(makeVolumeStrategy(0.10, 3));
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 100.00),
            makeItem(2, 1, 100.00),
            makeItem(3, 1, 100.00),
        ]));
        Tests::expect($result['monthly_total'])->toBe(300.0);
        Tests::expect(count($result['discounts']))->toBe(0);
    });

    Tests::it('aplica 10% ao superar o limite de itens distintos', function () {
        $calc   = new ContractCalculatorService(makeVolumeStrategy(0.10, 3));
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 100.00),
            makeItem(2, 1, 100.00),
            makeItem(3, 1, 100.00),
            makeItem(4, 1, 100.00), // 4 > 3 → desconto
        ]));
        Tests::expect($result['base_total'])->toBe(400.0);
        Tests::expect($result['monthly_total'])->toBe(360.0);
        Tests::expect(count($result['discounts']))->toBe(1);
        Tests::expect($result['discounts'][0]['label'])->toBe('Desconto por Volume');
        Tests::expect($result['discounts'][0]['amount'])->toBe(40.0);
    });

    Tests::it('aplica desconto quando quantidade acumulada supera threshold*2', function () {
        // 1 item, qty=7 > 3*2=6 → desconto
        $calc   = new ContractCalculatorService(makeVolumeStrategy(0.10, 3));
        $result = $calc->calculate(makeContract([makeItem(1, 7, 100.00)]));
        Tests::expect($result['base_total'])->toBe(700.0);
        Tests::expect($result['monthly_total'])->toBe(630.0);
    });

    Tests::it('não aplica desconto quando strategy está inativa', function () {
        $calc   = new ContractCalculatorService(makeVolumeStrategy(0.10, 3, false));
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 100.00),
            makeItem(2, 1, 100.00),
            makeItem(3, 1, 100.00),
            makeItem(4, 1, 100.00),
        ]));
        Tests::expect($result['monthly_total'])->toBe(400.0);
        Tests::expect(count($result['discounts']))->toBe(0);
    });

    Tests::it('aplica taxa configurável de 20% com threshold=2', function () {
        $calc   = new ContractCalculatorService(makeVolumeStrategy(0.20, 2));
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 200.00),
            makeItem(2, 1, 200.00),
            makeItem(3, 1, 200.00), // 3 > 2 → 20%
        ]));
        Tests::expect($result['base_total'])->toBe(600.0);
        Tests::expect($result['monthly_total'])->toBe(480.0);
    });
});


Tests::describe('LoyaltyDiscountStrategy — desconto por fidelidade', function () {

    Tests::it('não aplica desconto para contrato recente (6 meses < 12)', function () {
        $calc   = new ContractCalculatorService(makeLoyaltyStrategy(0.05, 12));
        $result = $calc->calculate(makeContract([makeItem(1, 1, 1000.00)], monthsAgo(6)));
        Tests::expect($result['monthly_total'])->toBe(1000.0);
        Tests::expect(count($result['discounts']))->toBe(0);
    });

    Tests::it('aplica 5% para contrato com 13 meses', function () {
        $calc   = new ContractCalculatorService(makeLoyaltyStrategy(0.05, 12));
        $result = $calc->calculate(makeContract([makeItem(1, 1, 1000.00)], monthsAgo(13)));
        Tests::expect($result['base_total'])->toBe(1000.0);
        Tests::expect($result['monthly_total'])->toBe(950.0);
        Tests::expect($result['discounts'][0]['amount'])->toBe(50.0);
    });

    Tests::it('aplica desconto exatamente no limite de 12 meses', function () {
        $calc   = new ContractCalculatorService(makeLoyaltyStrategy(0.05, 12));
        $result = $calc->calculate(makeContract([makeItem(1, 1, 200.00)], monthsAgo(12)));
        Tests::expect($result['monthly_total'])->toBe(190.0);
    });

    Tests::it('não aplica desconto quando strategy está inativa', function () {
        $calc   = new ContractCalculatorService(makeLoyaltyStrategy(0.05, 12, false));
        $result = $calc->calculate(makeContract([makeItem(1, 1, 1000.00)], monthsAgo(24)));
        Tests::expect($result['monthly_total'])->toBe(1000.0);
    });

    Tests::it('não aplica desconto quando start_date está vazio', function () {
        $calc   = new ContractCalculatorService(makeLoyaltyStrategy(0.05, 12));
        $result = $calc->calculate(['items' => [makeItem(1, 1, 500.00)], 'start_date' => '']);
        Tests::expect($result['monthly_total'])->toBe(500.0);
    });
});


Tests::describe('ContractCalculatorService — cadeia de estratégias', function () {

    Tests::it('aplica volume e fidelidade em sequência (base=800 → -10% → -5% = 684)', function () {
        $calc   = new ContractCalculatorService(
            makeVolumeStrategy(0.10, 3),
            makeLoyaltyStrategy(0.05, 12)
        );
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 200.00),
            makeItem(2, 1, 200.00),
            makeItem(3, 1, 200.00),
            makeItem(4, 1, 200.00), // 4 > 3 → volume
        ], monthsAgo(15)));          // 15 meses → fidelidade
        Tests::expect($result['base_total'])->toBe(800.0);
        Tests::expect($result['monthly_total'])->toBe(684.0);
        Tests::expect(count($result['discounts']))->toBe(2);
    });

    Tests::it('retorna dois itens em discounts quando ambas as estratégias disparam', function () {
        $calc   = new ContractCalculatorService(
            makeVolumeStrategy(0.10, 1),
            makeLoyaltyStrategy(0.10, 1)
        );
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 100.00),
            makeItem(2, 1, 100.00), // 2 > 1 → volume
        ], monthsAgo(2)));           // 2 >= 1 → fidelidade
        Tests::expect(count($result['discounts']))->toBe(2);
    });

    Tests::it('total final nunca é negativo mesmo com taxas altíssimas', function () {
        $calc   = new ContractCalculatorService(
            makeVolumeStrategy(0.99, 1),
            makeLoyaltyStrategy(0.99, 1)
        );
        $result = $calc->calculate(makeContract([
            makeItem(1, 2, 10.00),
            makeItem(2, 1, 10.00),
        ], monthsAgo(2)));
        Tests::expect($result['monthly_total'])->toBeGreaterThan(-0.01);
    });

    Tests::it('discounts fica vazio quando nenhuma condição é atendida', function () {
        $calc   = new ContractCalculatorService(
            makeVolumeStrategy(0.10, 10),
            makeLoyaltyStrategy(0.05, 24)
        );
        $result = $calc->calculate(makeContract([makeItem(1, 1, 500.00)], monthsAgo(3)));
        Tests::expect($result['discounts'])->toBeArray();
        Tests::expect(count($result['discounts']))->toBe(0);
        Tests::expect($result['monthly_total'])->toBe($result['base_total']);
    });
});

Tests::run();
