<?php
// app/Models/VentaDiariaTotalizada.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaDiariaTotalizada extends Model
{
    use HasFactory;

    protected $table = 'VentaDiariaTotalizada';
    protected $primaryKey = 'ID'; // Definimos la PK de la vista
    public $timestamps = false;

    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'TasaDeCambio' => 'decimal:2',
        'Cantidad' => 'integer',
        'CostoDivisa' => 'decimal:2',
        'TotalBs' => 'decimal:2',
        'TotalDivisa' => 'decimal:2',
        'ProveedorId' => 'integer',
        'Estatus' => 'integer',
        'Saldo' => 'decimal:2',
    ];

    /**
     * Relación con la tabla sucursal
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}
