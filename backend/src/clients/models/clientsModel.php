<?php

namespace App\Clients\Models;

use Crescent\Core\Model;

class ClientsModel extends Model
{
    protected static string $table      = 'clients';
    protected static string $primaryKey = 'id';

    public static function paginate(int $limit, int $offset): array
    {
        return static::query(
            'SELECT * FROM clients ORDER BY name ASC LIMIT ? OFFSET ?',
            [$limit, $offset]
        ) ?: [];
    }
}
