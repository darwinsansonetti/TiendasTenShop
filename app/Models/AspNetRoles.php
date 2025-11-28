<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspNetRoles extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AspNetRoles';
    protected $primaryKey = 'Id';
    protected $keyType = 'string';

    protected $fillable = [
        'Id',
        'Name',
        'NormalizedName',
        'ConcurrencyStamp'
    ];

    public $timestamps = false;
}