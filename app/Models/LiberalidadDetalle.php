<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiberalidadDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'LiberalidadDetalles';
    protected $primaryKey = 'LiberalidadDetalleId';

    protected $fillable = [
        'LiberalidadDetalleId',
        'LiberalidadId',
        'EmpleadoId',
        'UsuarioId',
        'OtraLiberalidad',
        'SaldoFavor',
        'MontoLiberalidad',
        'Pago',
        'TotalPagado',
        'Unidades',
        'Venta',
        'AbonoPrestamo',
        'DeudaPrestamo',
        'Estatus',
        'EsVendedor',
        'Motivo'
    ];

    protected $casts = [
        'OtraLiberalidad' => 'decimal:2',
        'SaldoFavor' => 'decimal:2',
        'MontoLiberalidad' => 'decimal:2',
        'Pago' => 'decimal:2',
        'TotalPagado' => 'decimal:2',
        'Venta' => 'decimal:2',
        'AbonoPrestamo' => 'decimal:2',
        'DeudaPrestamo' => 'decimal:2',
        'EsVendedor' => 'boolean',
        'Estatus' => 'integer'
    ];

    public $timestamps = false;

    public function liberalidad(): BelongsTo
    {
        return $this->belongsTo(Liberalidad::class, 'LiberalidadId');
    }
}