<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transferencia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Transferencias';
    protected $primaryKey = 'TransferenciaId';

    protected $fillable = [
        'TransferenciaId',
        'Numero',
        'Fecha',
        'SucursalOrigenId',
        'SucursalDestinoId',
        'Estatus',
        'Tipo',
        'Observacion',
        'Saldo'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer',
        'Saldo' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalOrigenId', 'ID');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalDestinoId', 'ID');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransferenciaDetalle::class, 'TransferenciaId');
    }
}