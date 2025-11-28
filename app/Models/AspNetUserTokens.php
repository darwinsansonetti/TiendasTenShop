<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspNetUserTokens extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetUserTokens';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'UserId',
        'LoginProvider',
        'Name',
        'Value'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }
}