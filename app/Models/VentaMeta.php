<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VentaMeta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'VentasMetas';
    protected $primaryKey = 'VentasMetaId';

    protected $fillable = [
        'VentasMetaId',
        'FechaInicio',
        'FechaFin',
        'SucursalId',
        'EsActiva',
        'Descripcion',
        'PrimerPremio',
        'MontoMinimoPrimero',
        'SegundoPremio',
        'MontoMinimoSegundo',
        'TercerPremio',
        'MontoMinimoTerecero'
    ];

    protected $casts = [
        'FechaInicio' => 'datetime',
        'FechaFin' => 'datetime',
        'EsActiva' => 'boolean',
        'MontoMinimoPrimero' => 'decimal:2',
        'MontoMinimoSegundo' => 'decimal:2',
        'MontoMinimoTerecero' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function ganadores(): HasOne
    {
        return $this->hasOne(VentaMetaGanador::class, 'VentaMetaId', 'VentasMetaId');
    }
}