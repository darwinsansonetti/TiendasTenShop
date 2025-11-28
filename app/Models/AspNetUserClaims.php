<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspNetUserClaims extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetUserClaims';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'UserId',
        'ClaimType',
        'ClaimValue'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }
}