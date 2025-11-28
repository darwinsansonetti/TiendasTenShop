<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'InventarioDetalle';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'InventarioDetalleId',
        'InventarioId',
        'ProductoId',
        'Existencia',
        'CostoDivisa',
        'CantidadContada',
        'Fecha',
        'EsUsuarioRestringido',
        'CantidadVendida',
        'CantidadCajaVacia',
        'CantidadPieSolo',
        'CantidadPieInvertido',
        'CantidadPiezaDanada'
    ];

    protected $casts = [
        'Existencia' => 'integer',
        'CostoDivisa' => 'decimal:2',
        'CantidadContada' => 'integer',
        'Fecha' => 'date',
        'EsUsuarioRestringido' => 'boolean',
        'CantidadVendida' => 'integer',
        'CantidadCajaVacia' => 'integer',
        'CantidadPieSolo' => 'integer',
        'CantidadPieInvertido' => 'integer',
        'CantidadPiezaDanada' => 'integer'
    ];

    public $timestamps = false;

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'InventarioId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}