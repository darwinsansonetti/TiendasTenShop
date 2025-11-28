<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspNetUserLogins extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetUserLogins';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'LoginProvider',
        'ProviderKey',
        'ProviderDisplayName',
        'UserId'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id');
    }
}