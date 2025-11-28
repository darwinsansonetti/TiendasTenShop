<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransferenciaDetalles';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'TransferenciaDetalleId',
        'TransferenciaId',
        'ProductoId',
        'CantidadEmitida',
        'CantidadRecibida'
    ];

    protected $casts = [
        'CantidadEmitida' => 'decimal:2',
        'CantidadRecibida' => 'decimal:2'
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
}