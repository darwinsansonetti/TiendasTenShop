<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuntoDeVenta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PuntosDeVenta';
    protected $primaryKey = 'PuntoDeVentaId';

    protected $fillable = [
        'PuntoDeVentaId',
        'BancoId',
        'SucursalId',
        'Serial',
        'Descripcion',
        'Codigo',
        'EsActivo'
    ];

    protected $casts = [
        'Codigo' => 'integer',
        'EsActivo' => 'boolean'
    ];

    public $timestamps = false;

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'BancoId', 'ID');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoPuntoDeVenta::class, 'PuntoDeVentaId');
    }
}