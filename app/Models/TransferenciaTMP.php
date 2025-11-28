<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaTMP extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransferenciasTMP';
    protected $primaryKey = 'TransferenciaId';

    protected $fillable = [
        'TransferenciaId',
        'Numero',
        'Fecha',
        'SucursalOrigenId',
        'Estatus',
        'Tipo',
        'Observacion'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalOrigenId', 'ID');
    }
}