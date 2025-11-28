<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Liberalidad extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Liberalidad';
    protected $primaryKey = 'LiberalidadId';

    protected $fillable = [
        'LiberalidadId',
        'Mes',
        'Anno',
        'FechaInicio',
        'FechaFinal',
        'Estatus'
    ];

    protected $casts = [
        'FechaInicio' => 'date',
        'FechaFinal' => 'date',
        'Estatus' => 'integer'
    ];

    public $timestamps = false;

    public function detalles(): HasMany
    {
        return $this->hasMany(LiberalidadDetalle::class, 'LiberalidadId');
    }
}