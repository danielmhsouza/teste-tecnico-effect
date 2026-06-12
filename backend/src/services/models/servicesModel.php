<?php

namespace App\Services\Models;

use Crescent\Core\Model;

class ServicesModel extends Model
{
    protected static string $table      = 'services';
    protected static string $primaryKey = 'id';

    public static function paginate(int $limit, int $offset): array
    {
        return static::query(
            'SELECT * FROM services ORDER BY name ASC LIMIT ? OFFSET ?',
            [$limit, $offset]
        ) ?: [];
    }
}
