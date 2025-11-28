<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaVendedor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'VentasVendedor';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'VentaVendedorId',
        'VentaId',
        'ProductoId',
        'UsuarioId',
        'Cantidad',
        'Costo',
        'CostoDivisa',
        'PrecioVenta',
        'MontoDivisa'
    ];

    protected $casts = [
        'Cantidad' => 'integer',
        'Costo' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'UsuarioId');
    }
}