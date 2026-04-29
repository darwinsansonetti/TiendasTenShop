<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proveedor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Proveedores';
    protected $primaryKey = 'ProveedorId';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'ProveedorId',
        'RifCedula',
        'Nombre',
        'Direccion',
        'TelefonoMovil',
        'TelefonoFijo',
        'CorreoElectronico',
        'FechaCreacion',
        'Estatus',
        'Tipo',
        'Facebook',
        'Twitter',
        'Instagram',
        'PaisId',
        'UrlImagen',
        'FacturaId',
        'SucursalId',
        'NumeroDeCuenta',
        'BancoId'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer',
        'PaisId' => 'integer',
        'SucursalId' => 'integer',
        'BancoId' => 'integer'
    ];

    public $timestamps = false;

    // ============================================
    // RELACIONES
    // ============================================
    
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'ProveedorId', 'ProveedorId');
    }
    
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'ProveedorId', 'ProveedorId');
    }
    
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class, 'ProveedorId', 'ProveedorId');
    }
    
    public function transacciones(): HasMany
    {
        return $this->hasMany(TransaccionProveedor::class, 'ProveedorId', 'ProveedorId');
    }
    
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'PaisId', 'Id');
    }
    
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
    
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'BancoId', 'Id');
    }
}