<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspNetUserRoles extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetUserRoles';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'UserId',
        'RoleId'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleId', 'Id');
    }
}