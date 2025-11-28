<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaDetalleTMP extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransferenciaDetallesTMP';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'TransferenciaDetalleId',
        'TransferenciaId',
        'ProductoId',
        'SucursalId',
        'CantidadEmitida',
        'CantidadRecibida',
        'CantidadDisponible'
    ];

    protected $casts = [
        'CantidadEmitida' => 'decimal:2',
        'CantidadRecibida' => 'decimal:2',
        'CantidadDisponible' => 'decimal:2'
    ];

    public $timestamps = false;

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(Transferencia::class, 'TransferenciaId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}