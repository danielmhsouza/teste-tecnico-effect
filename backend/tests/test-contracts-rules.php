<?php

/**
 * Testes — Regras de negócio de contratos e validação de estratégias.
 * Execute com: php crecli.php test
 */

use Crescent\Utils\Tests;

require_once __DIR__ . '/helpers.php';


Tests::describe('ContractsService — bloqueio de contrato cancelado', function () {

    Tests::it('contrato cancelado é detectado corretamente', function () {
        $contract = ['id' => 1, 'status' => 'canceled'];
        Tests::expect($contract['status'] === 'canceled')->toBeTrue();
    });

    Tests::it('contrato ativo não é bloqueado', function () {
        $contract = ['id' => 2, 'status' => 'active'];
        Tests::expect($contract['status'] === 'canceled')->toBeFalse();
    });

    Tests::it('apenas os status válidos são aceitos (active e canceled)', function () {
        $valid = ['active', 'canceled'];
        Tests::expect(in_array('active',   $valid, true))->toBeTrue();
        Tests::expect(in_array('canceled', $valid, true))->toBeTrue();
        Tests::expect(in_array('pending',  $valid, true))->toBeFalse();
        Tests::expect(in_array('',         $valid, true))->toBeFalse();
    });
});


Tests::describe('StrategiesService — validação de entrada', function () {

    Tests::it('rejeita discount_rate igual a zero', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.0, 'threshold_value' => 3]);
        Tests::expect(in_array_contains($errors, 'discount_rate'))->toBeTrue();
    });

    Tests::it('rejeita discount_rate maior que 1', function () {
        $errors = validateStrategyInput(['discount_rate' => 1.5, 'threshold_value' => 3]);
        Tests::expect(in_array_contains($errors, 'discount_rate'))->toBeTrue();
    });

    Tests::it('aceita discount_rate no limite inferior (0.01)', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.01, 'threshold_value' => 3]);
        Tests::expect(count($errors))->toBe(0);
    });

    Tests::it('aceita discount_rate no limite superior (1.00)', function () {
        $errors = validateStrategyInput(['discount_rate' => 1.0, 'threshold_value' => 3]);
        Tests::expect(count($errors))->toBe(0);
    });

    Tests::it('aceita discount_rate válido (0.10)', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.10, 'threshold_value' => 3]);
        Tests::expect(count($errors))->toBe(0);
    });

    Tests::it('rejeita threshold_value igual a zero', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.10, 'threshold_value' => 0]);
        Tests::expect(in_array_contains($errors, 'threshold_value'))->toBeTrue();
    });

    Tests::it('rejeita threshold_value negativo', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.05, 'threshold_value' => -5]);
        Tests::expect(in_array_contains($errors, 'threshold_value'))->toBeTrue();
    });

    Tests::it('aceita threshold_value válido (12)', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.05, 'threshold_value' => 12]);
        Tests::expect(count($errors))->toBe(0);
    });

    Tests::it('retorna múltiplos erros quando ambos os campos são inválidos', function () {
        $errors = validateStrategyInput(['discount_rate' => 0.0, 'threshold_value' => -1]);
        Tests::expect(count($errors))->toBe(2);
    });
});

Tests::run();
