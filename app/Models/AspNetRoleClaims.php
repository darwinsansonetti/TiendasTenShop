<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspNetRoleClaims extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetRoleClaims';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'RoleId',
        'ClaimType',
        'ClaimValue'
    ];

    public $timestamps = false;

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleId', 'Id');
    }
}