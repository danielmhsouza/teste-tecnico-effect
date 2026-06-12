<?php

namespace App\Clients\Services;

use App\Clients\Models\ClientsModel;

class ClientsService
{
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function sanitizeDocument(string $doc): string
    {
        return preg_replace('/\D/', '', $doc);
    }

    public static function validateDocument(string $doc): bool
    {
        $clean = self::sanitizeDocument($doc);
        if (strlen($clean) === 11) {
            return self::validateCpf($clean);
        }
        if (strlen($clean) === 14) {
            return self::validateCnpj($clean);
        }
        return false;
    }

    private static function validateCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $rem = ($sum * 10) % 11;
        if ($rem === 10 || $rem === 11) {
            $rem = 0;
        }
        if ($rem !== (int) $cpf[9]) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $rem = ($sum * 10) % 11;
        if ($rem === 10 || $rem === 11) {
            $rem = 0;
        }
        return $rem === (int) $cpf[10];
    }

    private static function validateCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }

        $calcDigit = function (string $cnpj, int $len): int {
            $weights = $len === 12
                ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
                : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            $sum = 0;
            for ($i = 0; $i < $len; $i++) {
                $sum += (int) $cnpj[$i] * $weights[$i];
            }
            $rem = $sum % 11;
            return $rem < 2 ? 0 : 11 - $rem;
        };

        if ($calcDigit($cnpj, 12) !== (int) $cnpj[12]) {
            return false;
        }
        if ($calcDigit($cnpj, 13) !== (int) $cnpj[13]) {
            return false;
        }
        return true;
    }

    public static function paginate(int $page, int $perPage): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;
        $total   = (int) ClientsModel::count();

        $rows = ClientsModel::paginate($perPage, $offset);

        return [
            'data' => $rows,
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
            ],
        ];
    }

    public static function create(array $body): array
    {
        $errors = self::validate($body, false);
        if ($errors) {
            return ['errors' => $errors];
        }

        $id = ClientsModel::insert([
            'name'     => trim($body['name']),
            'document' => self::sanitizeDocument($body['document']),
            'email'    => strtolower(trim($body['email'])),
            'status'   => $body['status'] ?? 'active',
        ]);

        return ['data' => ClientsModel::find((int) $id)];
    }

    public static function update(int $id, array $body): array
    {
        $client = ClientsModel::find($id);
        if (!$client) {
            return ['notFound' => true];
        }

        $errors = self::validate($body, true);
        if ($errors) {
            return ['errors' => $errors];
        }

        $fields = [];
        if (isset($body['name']))     $fields['name']     = trim($body['name']);
        if (isset($body['document'])) $fields['document'] = self::sanitizeDocument($body['document']);
        if (isset($body['email']))    $fields['email']    = strtolower(trim($body['email']));
        if (isset($body['status']))   $fields['status']   = $body['status'];

        if (!empty($fields)) {
            ClientsModel::update($id, $fields);
        }

        return ['data' => ClientsModel::find($id)];
    }

    public static function delete(int $id): array
    {
        if (!ClientsModel::find($id)) {
            return ['notFound' => true];
        }
        ClientsModel::delete($id);
        return ['deleted' => true];
    }

    private static function validate(array $body, bool $isUpdate): array
    {
        $errors = [];

        if (!$isUpdate && empty($body['name'])) {
            $errors[] = 'O campo name é obrigatório.';
        }

        if (!$isUpdate && empty($body['email'])) {
            $errors[] = 'O campo email é obrigatório.';
        } elseif (!empty($body['email']) && !self::validateEmail($body['email'])) {
            $errors[] = 'Email inválido.';
        }

        if (!$isUpdate && empty($body['document'])) {
            $errors[] = 'O campo document é obrigatório.';
        } elseif (!empty($body['document']) && !self::validateDocument($body['document'])) {
            $errors[] = 'CPF ou CNPJ inválido.';
        }

        if (isset($body['status']) && !in_array($body['status'], ['active', 'inactive'], true)) {
            $errors[] = "Status inválido. Use 'active' ou 'inactive'.";
        }

        return $errors;
    }
}
