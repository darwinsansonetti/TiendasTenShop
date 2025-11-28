<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}