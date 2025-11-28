<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EFMigrationsHistory extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = '__EFMigrationsHistory';
    protected $primaryKey = 'MigrationId';
    protected $keyType = 'string';

    protected $fillable = [
        'MigrationId',
        'ProductVersion'
    ];

    public $timestamps = false;
}