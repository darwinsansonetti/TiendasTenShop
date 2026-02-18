<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\TransaccionesGasto;

class Transaccion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Transacciones';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Descripcion',
        'MontoAbonado',
        'MontoDivisaAbonado',
        'NumeroOperacion',
        'DivisaId',
        'TasaDeCambio',
        'Tipo',
        'FormaDePago',
        'Estatus',
        'Fecha',
        'UrlComprobante',
        'SucursalOrigenId',
        'SucursalId',
        'Observacion',
        'Nombre',
        'Cedula',
        'CategoriaId'
    ];

    protected $casts = [
        'MontoAbonado' => 'decimal:2',
        'MontoDivisaAbonado' => 'decimal:2',
        'TasaDeCambio' => 'decimal:2',
        'Tipo' => 'integer',
        'FormaDePago' => 'integer',
        'Estatus' => 'integer',
        'Fecha' => 'datetime'
    ];

    public $timestamps = false;

    public function detalles(): HasMany
    {
        return $this->hasMany(TransaccionDetalle::class, 'TransaccionId', 'ID');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function transaccionesAbonos()
    {
        // Un gasto tiene muchos abonos
        return $this->hasMany(TransaccionGasto::class, 'GastoId', 'ID');
    }

    public function transaccionesRecepciones()
    {
        return $this->hasMany(TransaccionRecepcion::class, 'TransaccionId', 'ID');
    }

    public function recepciones()
    {
        return $this->belongsToMany(
            Recepciones::class,
            'TransaccionesRecepciones',  // Tabla pivot
            'TransaccionId',              // FK en pivot que apunta a Transaccion
            'RecepcionId'                 // FK en pivot que apunta a Recepciones
        )->withPivot('SucursalId');
    }

    public function transaccionesProveedor()
    {
        return $this->hasMany(TransaccionProveedor::class, 'TransaccionId', 'ID');
    }

    public function proveedores()
    {
        return $this->belongsToMany(
            Proveedor::class,
            'TransaccionesProveedor',  // Tabla pivot
            'TransaccionId',            // FK en pivot que apunta a Transaccion
            'ProveedorId'               // FK en pivot que apunta a Proveedor
        )->withPivot('MontoAbonado', 'FacturaId');
    }
}