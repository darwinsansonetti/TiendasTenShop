<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facturacion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Facturacion';
    protected $primaryKey = 'FacturacionId';

    protected $fillable = [
        'FacturacionId',
        'ClienteId',
        'Numero',
        'Fecha',
        'Estatus',
        'TasaDeCambioId',
        'MontoDivisa',
        'Observacion',
        'SucursalId'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'MontoDivisa' => 'decimal:2'
    ];

    public $timestamps = false;

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'ClienteId');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}