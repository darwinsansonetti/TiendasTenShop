<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysDiagram extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'sysdiagrams';
    protected $primaryKey = 'diagram_id';

    protected $fillable = [
        'name',
        'principal_id',
        'diagram_id',
        'version',
        'definition'
    ];

    protected $casts = [
        'principal_id' => 'integer',
        'version' => 'integer'
    ];

    public $timestamps = false;
}