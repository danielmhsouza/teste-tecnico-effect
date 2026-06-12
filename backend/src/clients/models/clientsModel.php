<?php

namespace App\Clients\Models;

use Crescent\Core\Model;

class ClientsModel extends Model
{
    protected static string $table      = 'clients';
    protected static string $primaryKey = 'id';
}