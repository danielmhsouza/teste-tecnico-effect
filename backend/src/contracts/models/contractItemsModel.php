<?php

namespace App\Contracts\Models;

use Crescent\Core\Model;

class ContractItemsModel extends Model
{
    protected static string $table      = 'contract_items';
    protected static string $primaryKey = 'id';

    public static function findByContracts(array $contractIds): array
    {
        if (empty($contractIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($contractIds), '?'));
        return static::query(
            "SELECT ci.*, s.name AS service_name, s.base_monthly_value
             FROM contract_items ci
             JOIN services s ON s.id = ci.service_id
             WHERE ci.contract_id IN ($placeholders)",
            $contractIds
        ) ?: [];
    }
}
