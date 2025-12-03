<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Productos';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Codigo',
        'CodigoBarra',
        'Referencia',
        'Descripcion',
        'CostoBs',
        'CostoDivisa',
        'UrlFoto',
        'FechaActualizacion',
        'FechaCreacion',
        'Estatus',
        'DepartamentoId',
        'EsProveedorAsignado'
    ];

    protected $casts = [
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'FechaActualizacion' => 'datetime',
        'FechaCreacion' => 'datetime',
        'Estatus' => 'integer',
        'EsProveedorAsignado' => 'boolean'
    ];

    public $timestamps = false;

    // Relaciones
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'DepartamentoId', 'Id');
    }

    public function precios(): HasMany
    {
        return $this->hasMany(ProductoPrecio::class, 'ProductoId', 'ID');
    }

    public function sucursales()
    {
        return $this->hasMany(ProductoSucursal::class, 'ProductoId', 'ID');
    }
}