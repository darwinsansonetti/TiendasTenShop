<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Sucursales';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Nombre',
        'Direccion',
        'FechaCarga',
        'EsSeleccionado',
        'EsActiva',
        'Tipo',
        'SerialImpresora',
        'NumeroZeta'
    ];

    protected $casts = [
        'FechaCarga' => 'datetime',
        'EsSeleccionado' => 'boolean',
        'EsActiva' => 'boolean',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    // Relaciones
    public function productos(): HasMany
    {
        return $this->hasMany(ProductoSucursal::class, 'SucursalId', 'ID');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'SucursalId', 'ID');
    }
}