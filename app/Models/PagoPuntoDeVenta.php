<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoPuntoDeVenta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PagosPuntoDeVenta';
    protected $primaryKey = 'PagoPuntoDeVentaId';

    protected $fillable = [
        'PagoPuntoDeVentaId',
        'CierreDiarioId',
        'PuntoDeVentaId',
        'Monto'
    ];

    protected $casts = [
        'Monto' => 'decimal:2'
    ];

    public $timestamps = false;

    public function cierreDiario(): BelongsTo
    {
        return $this->belongsTo(CierreDiario::class, 'CierreDiarioId');
    }

    public function puntoDeVenta(): BelongsTo
    {
        return $this->belongsTo(
            PuntoDeVenta::class,
            'PuntoDeVentaId',      // FK en PagosPuntoDeVenta
            'PuntoDeVentaId'       // PK en PuntosDeVenta
        );
    }
}