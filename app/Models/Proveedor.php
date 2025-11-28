<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Proveedores';
    protected $primaryKey = 'ProveedorId';

    protected $fillable = [
        'ProveedorId',
        'Rif_Cedula',
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
        'FacturaID',
        'SucursalId',
        'NumeroDeCuenta',
        'BancoId'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'ProveedorId');
    }
}