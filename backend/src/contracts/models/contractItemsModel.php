<?php

namespace App\Contracts\Models;

use Crescent\Core\Model;

class ContractItemsModel extends Model
{
    protected static string $table      = 'contract_items';
    protected static string $primaryKey = 'id';
}
