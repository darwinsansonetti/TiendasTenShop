<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'RecepcionesDetalles';
    protected $primaryKey = 'RecepcionesDetallesId';

    protected $fillable = [
        'RecepcionesDetallesId',
        'RecepcionId',
        'ProductoId',
        'CantidadRecibida',
        'CantidadPedida',
        'CostoBs',
        'CostoDivisa',
        'CantidadPieSolo',
        'CantidadPieInvertido',
        'CantidadCajaVacia',
        'CantidadPiezaDanada'
    ];

    protected $casts = [
        'CantidadRecibida' => 'decimal:2',
        'CantidadPedida' => 'decimal:2',
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'CantidadPieSolo' => 'integer',
        'CantidadPieInvertido' => 'integer',
        'CantidadCajaVacia' => 'integer',
        'CantidadPiezaDanada' => 'integer'
    ];

    public $timestamps = false;

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}