<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Issues';
    protected $primaryKey = 'IssueId';

    protected $fillable = [
        'IssueId',
        'Titulo',
        'Descripcion',
        'Fecha',
        'FechaCierre',
        'Estatus'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'FechaCierre' => 'datetime',
        'Estatus' => 'integer'
    ];

    public $timestamps = false;
}