<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama', 'username', 'password', 'role', 'line', 'is_active', 'departemen', 'plant'];
    protected $useTimestamps    = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
}
