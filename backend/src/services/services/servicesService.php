<?php

namespace App\Services\Services;

use App\Services\Models\ServicesModel;

class ServicesService
{
    // ─── Pagination ──────────────────────────────────────────────────────────

    public static function paginate(int $page, int $perPage): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;
        $total   = (int) ServicesModel::count();

        $rows = ServicesModel::query(
            'SELECT * FROM services ORDER BY name ASC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        return [
            'data' => $rows ?: [],
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
            ],
        ];
    }

    // ─── CRUD ────────────────────────────────────────────────────────────────

    public static function create(array $body): array
    {
        $errors = self::validate($body, false);
        if ($errors) {
            return ['errors' => $errors];
        }

        $id = ServicesModel::insert([
            'name'               => trim($body['name']),
            'base_monthly_value' => (float) $body['base_monthly_value'],
        ]);

        return ['data' => ServicesModel::find((int) $id)];
    }

    public static function update(int $id, array $body): array
    {
        if (!ServicesModel::find($id)) {
            return ['notFound' => true];
        }

        $errors = self::validate($body, true);
        if ($errors) {
            return ['errors' => $errors];
        }

        $fields = [];
        if (isset($body['name']))               $fields['name']               = trim($body['name']);
        if (isset($body['base_monthly_value'])) $fields['base_monthly_value'] = (float) $body['base_monthly_value'];

        if (!empty($fields)) {
            ServicesModel::update($id, $fields);
        }

        return ['data' => ServicesModel::find($id)];
    }

    public static function delete(int $id): array
    {
        if (!ServicesModel::find($id)) {
            return ['notFound' => true];
        }
        ServicesModel::delete($id);
        return ['deleted' => true];
    }

    // ─── Internal validation ─────────────────────────────────────────────────

    private static function validate(array $body, bool $isUpdate): array
    {
        $errors = [];

        if (!$isUpdate && empty($body['name'])) {
            $errors[] = 'O campo name é obrigatório.';
        }

        if (!$isUpdate && !isset($body['base_monthly_value'])) {
            $errors[] = 'O campo base_monthly_value é obrigatório.';
        } elseif (isset($body['base_monthly_value']) && (float) $body['base_monthly_value'] <= 0) {
            $errors[] = 'base_monthly_value deve ser maior que zero.';
        }

        return $errors;
    }
}
