<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servicio extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Servicios';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'ProveedorId',
        'Numero',
        'Descripcion',
        'Monto',
        'MontoDivisa',
        'TasaDeCambio',
        'FechaCreacion',
        'Estatus',
        'SucursalId'
    ];

    protected $casts = [
        'Monto' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'TasaDeCambio' => 'decimal:2',
        'FechaCreacion' => 'datetime',
        'Estatus' => 'integer'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}