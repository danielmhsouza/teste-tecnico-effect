<?php

namespace App\Contracts\Models;

use Crescent\Core\Model;

class ContractsModel extends Model
{
    protected static string $table      = 'contracts';
    protected static string $primaryKey = 'id';

    public static function paginateWithClient(int $limit, int $offset): array
    {
        return static::query(
            'SELECT c.*, cl.name AS client_name, cl.email AS client_email, cl.document AS client_document
             FROM contracts c
             JOIN clients cl ON cl.id = c.client_id
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?',
            [$limit, $offset]
        ) ?: [];
    }

    public static function findWithClient(int $id): ?array
    {
        $rows = static::query(
            'SELECT c.*, cl.name AS client_name, cl.email AS client_email, cl.document AS client_document
             FROM contracts c
             JOIN clients cl ON cl.id = c.client_id
             WHERE c.id = ?',
            [$id]
        );

        return $rows[0] ?? null;
    }
}
