<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Pagos';
    protected $primaryKey = 'PagoId';

    protected $fillable = [
        'PagoId',
        'Fecha',
        'TasaCambioId',
        'MontoBs',
        'MontoDivisa',
        'TipoPago',
        'Estatus',
        'Descripcion',
        'Observacion',
        'Cedula',
        'Nombre',
        'SucursalId',
        'UrlComprobante'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'MontoBs' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'TipoPago' => 'integer',
        'Estatus' => 'integer'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}