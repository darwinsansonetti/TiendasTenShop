<?php
// app/Models/BalanceDeServicios.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BalanceDeServicios extends BaseModel
{
    use HasFactory;

    protected $table = 'BalanceDeServicios';
    public $timestamps = false;
    
    protected $casts = [
        'ProveedorId' => 'integer',
        'CantidadFacturas' => 'integer',
        'CantidadUnidadesCompradas' => 'integer',
        'MontoFacturas' => 'decimal:2',
        'MontoPagos' => 'decimal:2',
        'CantidadPagos' => 'integer',
        'CantidadItemsProveedor' => 'integer',
        'CantidadUnidadesExistencia' => 'integer',
        'CantidadItemsVendidos' => 'integer',
        'CantidadUnidadesVendidas' => 'integer',
        'ValorizacionInventario' => 'decimal:2',
        'PrecioVentaEstimado' => 'decimal:2',
    ];
}