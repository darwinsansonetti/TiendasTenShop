<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaProducto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'VentaProductos';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'VentaId',
        'ProductoId',
        'Cantidad',
        'PrecioVenta',
        'MontoDivisa',
        'TicketId'
    ];

    protected $casts = [
        'Cantidad' => 'integer',
        'PrecioVenta' => 'decimal:2',
        'MontoDivisa' => 'decimal:2'
    ];

    public $timestamps = false;

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'VentaId', 'ID');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}