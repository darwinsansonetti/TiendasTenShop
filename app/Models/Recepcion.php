<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recepcion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Recepciones';
    protected $primaryKey = 'RecepcionId';

    protected $fillable = [
        'RecepcionId',
        'ProveedorId',
        'Numero',
        'FechaCreacion',
        'FechaRecepcion',
        'Estatus',
        'EsConFactura',
        'SucursalDestinoId',
        'SucursalOrigenId',
        'TasaDeCambio',
        'Tipo'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'FechaRecepcion' => 'datetime',
        'Estatus' => 'integer',
        'EsConFactura' => 'boolean',
        'TasaDeCambio' => 'decimal:2',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalDestinoId', 'ID');
    }

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalOrigenId', 'ID');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class, 'RecepcionId');
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'RecepcionesFacturas', 'RecepcionId', 'FacturaId');
    }

    public function transferencias()
    {
        return $this->belongsToMany(Transferencia::class, 'RecepcionesTransferencias', 'RecepcionId', 'TransferenciaId');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'RecepcionId');
    }
}