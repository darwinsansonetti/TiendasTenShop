<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Alertas';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'FechaCreacion',
        'TipoRepeticion',
        'Frecuencia',
        'FechaSiguiente',
        'Estatus',
        'Tipo'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'FechaSiguiente' => 'datetime',
        'TipoRepeticion' => 'integer',
        'Frecuencia' => 'integer',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;
}