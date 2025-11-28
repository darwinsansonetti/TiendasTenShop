<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Ventas';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Fecha',
        'SucursalId',
        'Estatus',
        'Saldo'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'Saldo' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(VentaProducto::class, 'VentaId', 'ID');
    }
}