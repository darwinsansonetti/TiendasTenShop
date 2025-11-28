<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'FacturaDetalles';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'FacturaId',
        'ProductoId',
        'CantidadEmitida',
        'CantidadRecibida',
        'CantidadDisponible',
        'CostoBs',
        'CostoDivisa',
        'CostoUnitario',
        'CostoEmpaque',
        'UxE',
        'DescuentoPuntual'
    ];

    protected $casts = [
        'CantidadEmitida' => 'integer',
        'CantidadRecibida' => 'integer',
        'CantidadDisponible' => 'integer',
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'CostoUnitario' => 'decimal:2',
        'CostoEmpaque' => 'decimal:2',
        'DescuentoPuntual' => 'decimal:2'
    ];

    public $timestamps = false;

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'FacturaId', 'ID');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}