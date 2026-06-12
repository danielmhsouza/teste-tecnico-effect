<?php

/**
 * Testes — Validações de clientes (e-mail, CPF, CNPJ, sanitização).
 * Execute com: php crecli.php test
 */

use Crescent\Utils\Tests;
use App\Clients\Services\ClientsService;

require_once __DIR__ . '/helpers.php';


Tests::describe('ClientsService — validação de e-mail', function () {

    Tests::it('aceita e-mail válido simples', function () {
        Tests::expect(ClientsService::validateEmail('joao@example.com'))->toBeTrue();
    });

    Tests::it('aceita e-mail com subdomínio', function () {
        Tests::expect(ClientsService::validateEmail('user@mail.empresa.com.br'))->toBeTrue();
    });

    Tests::it('rejeita e-mail sem @', function () {
        Tests::expect(ClientsService::validateEmail('joaoemail.com'))->toBeFalse();
    });

    Tests::it('rejeita e-mail sem domínio', function () {
        Tests::expect(ClientsService::validateEmail('joao@'))->toBeFalse();
    });

    Tests::it('rejeita e-mail sem local-part', function () {
        Tests::expect(ClientsService::validateEmail('@example.com'))->toBeFalse();
    });

    Tests::it('rejeita string vazia', function () {
        Tests::expect(ClientsService::validateEmail(''))->toBeFalse();
    });
});


Tests::describe('ClientsService — sanitização de documento', function () {

    Tests::it('remove pontos, traços e barras do CPF formatado', function () {
        Tests::expect(ClientsService::sanitizeDocument('529.982.247-25'))->toBe('52998224725');
    });

    Tests::it('remove formatação de CNPJ', function () {
        Tests::expect(ClientsService::sanitizeDocument('11.222.333/0001-81'))->toBe('11222333000181');
    });

    Tests::it('não altera string já limpa', function () {
        Tests::expect(ClientsService::sanitizeDocument('52998224725'))->toBe('52998224725');
    });
});


Tests::describe('ClientsService — validação de CPF', function () {

    Tests::it('aceita CPF válido (529.982.247-25)', function () {
        Tests::expect(ClientsService::validateDocument('529.982.247-25'))->toBeTrue();
    });

    Tests::it('aceita CPF válido sem formatação', function () {
        Tests::expect(ClientsService::validateDocument('52998224725'))->toBeTrue();
    });

    Tests::it('rejeita CPF com dígito verificador errado', function () {
        Tests::expect(ClientsService::validateDocument('52998224726'))->toBeFalse();
    });

    Tests::it('rejeita sequência de dígitos iguais (111.111.111-11)', function () {
        Tests::expect(ClientsService::validateDocument('11111111111'))->toBeFalse();
    });

    Tests::it('rejeita CPF com tamanho errado (10 dígitos)', function () {
        Tests::expect(ClientsService::validateDocument('5299822472'))->toBeFalse();
    });

    Tests::it('rejeita CPF com tamanho errado (12 dígitos)', function () {
        Tests::expect(ClientsService::validateDocument('529982247250'))->toBeFalse();
    });
});


Tests::describe('ClientsService — validação de CNPJ', function () {

    Tests::it('aceita CNPJ válido (11.222.333/0001-81)', function () {
        Tests::expect(ClientsService::validateDocument('11.222.333/0001-81'))->toBeTrue();
    });

    Tests::it('aceita CNPJ válido sem formatação', function () {
        Tests::expect(ClientsService::validateDocument('11222333000181'))->toBeTrue();
    });

    Tests::it('rejeita CNPJ com dígito verificador errado', function () {
        Tests::expect(ClientsService::validateDocument('11222333000182'))->toBeFalse();
    });

    Tests::it('rejeita sequência de dígitos iguais (00.000.000/0000-00)', function () {
        Tests::expect(ClientsService::validateDocument('00000000000000'))->toBeFalse();
    });

    Tests::it('rejeita CNPJ com tamanho errado (13 dígitos)', function () {
        Tests::expect(ClientsService::validateDocument('1122233300018'))->toBeFalse();
    });
});

Tests::run();
