<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Facturas';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'ProveedorId',
        'Numero',
        'Serie',
        'FechaCreacion',
        'FechaDespacho',
        'FechaCierre',
        'Estatus',
        'ContenedorId',
        'Traspaso',
        'PorcentajeCosto',
        'PorcentajeDescuento',
        'MontoDescuento',
        'EsCargarFleteEnFactura',
        'Tipo',
        'SucursalId',
        'DivisaValorId',
        'MontoDivisa',
        'MontoBs',
        'Descripcion',
        'TasaDeCambio',
        'MonedaPrincipal'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'FechaDespacho' => 'datetime',
        'FechaCierre' => 'datetime',
        'Estatus' => 'integer',
        'Traspaso' => 'decimal:2',
        'PorcentajeCosto' => 'decimal:2',
        'PorcentajeDescuento' => 'decimal:2',
        'MontoDescuento' => 'decimal:2',
        'EsCargarFleteEnFactura' => 'boolean',
        'Tipo' => 'integer',
        'MontoDivisa' => 'decimal:2',
        'MontoBs' => 'decimal:2',
        'TasaDeCambio' => 'decimal:2',
        'MonedaPrincipal' => 'integer'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'FacturaId', 'ID');
    }
}