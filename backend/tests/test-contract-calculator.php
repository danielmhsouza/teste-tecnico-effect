<?php

/**
 * Testes — Cálculo base do contrato (sem descontos).
 * Execute com: php crecli.php test
 */

use Crescent\Utils\Tests;
use App\Contracts\Services\ContractCalculatorService;

require_once __DIR__ . '/helpers.php';

Tests::describe('ContractCalculatorService — total base', function () {

    Tests::it('retorna zero para contrato sem itens', function () {
        $calc   = new ContractCalculatorService();
        $result = $calc->calculate(makeContract([]));
        Tests::expect($result['base_total'])->toBe(0.0);
        Tests::expect($result['monthly_total'])->toBe(0.0);
        Tests::expect($result['discounts'])->toBeArray();
    });

    Tests::it('calcula corretamente um único item', function () {
        $calc   = new ContractCalculatorService();
        $result = $calc->calculate(makeContract([makeItem(1, 2, 150.00)]));
        Tests::expect($result['base_total'])->toBe(300.0);
        Tests::expect($result['monthly_total'])->toBe(300.0);
    });

    Tests::it('soma múltiplos itens corretamente (100 + 150 + 150 = 400)', function () {
        $calc   = new ContractCalculatorService();
        $result = $calc->calculate(makeContract([
            makeItem(1, 1, 100.00),
            makeItem(2, 3,  50.00),
            makeItem(3, 2,  75.00),
        ]));
        Tests::expect($result['base_total'])->toBe(400.0);
    });

    Tests::it('nenhum desconto aplicado quando não há estratégias', function () {
        $calc   = new ContractCalculatorService();
        $result = $calc->calculate(makeContract([makeItem(1, 5, 200.00)]));
        Tests::expect(count($result['discounts']))->toBe(0);
        Tests::expect($result['monthly_total'])->toBe($result['base_total']);
    });

    Tests::it('arredonda corretamente valores com casas decimais', function () {
        $calc   = new ContractCalculatorService();
        $result = $calc->calculate(makeContract([makeItem(1, 3, 33.33)]));
        // 3 * 33.33 = 99.99
        Tests::expect($result['base_total'])->toBe(99.99);
    });
});

Tests::run();
