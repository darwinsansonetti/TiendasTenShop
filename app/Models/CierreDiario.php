<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreDiario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CierreDiario';
    protected $primaryKey = 'CierreDiarioId';

    protected $fillable = [
        'CierreDiarioId',
        'SucursalId',
        'DivisaValorId',
        'EfectivoBs',
        'EfectivoDivisas',
        'EgresoBs',
        'EgresoDivisas',
        'Estatus',
        'Fecha',
        'ImpuestoGeneral',
        'NumeroZeta',
        'MontoBaseGeneral',
        'MontoBaseExento',
        'MontoBaseGeneralDevoluciones',
        'MontoImpuestoGeneralDevoluciones',
        'MontoBaseExentoDevoluciones',
        'MontoBaseGeneralAuditado',
        'MontoBaseExentoAuditado',
        'ImpuestoGeneralAuditado',
        'MontoBaseGeneralDevolucionesAuditado',
        'MontoImpuestoGeneralDevolucionesAuditado',
        'MontoBaseExentoDevolucionesAuditado',
        'PagoMovilBs',
        'PuntoDeVentaDivisas',
        'SerialImpresora',
        'TotalFacturas',
        'TransferenciaBs',
        'TransferenciaDivisas',
        'Tipo',
        'VentaSistema',
        'ZelleDivisas',
        'Observacion'
    ];

    protected $casts = [
        'EfectivoBs' => 'decimal:2',
        'EfectivoDivisas' => 'decimal:2',
        'EgresoBs' => 'decimal:2',
        'EgresoDivisas' => 'decimal:2',
        'Estatus' => 'integer',
        'Fecha' => 'datetime',
        'ImpuestoGeneral' => 'decimal:2',
        'MontoBaseGeneral' => 'decimal:2',
        'MontoBaseExento' => 'decimal:2',
        'MontoBaseGeneralDevoluciones' => 'decimal:2',
        'MontoImpuestoGeneralDevoluciones' => 'decimal:2',
        'MontoBaseExentoDevoluciones' => 'decimal:2',
        'MontoBaseGeneralAuditado' => 'decimal:2',
        'MontoBaseExentoAuditado' => 'decimal:2',
        'ImpuestoGeneralAuditado' => 'decimal:2',
        'MontoBaseGeneralDevolucionesAuditado' => 'decimal:2',
        'MontoImpuestoGeneralDevolucionesAuditado' => 'decimal:2',
        'MontoBaseExentoDevolucionesAuditado' => 'decimal:2',
        'PagoMovilBs' => 'decimal:2',
        'PuntoDeVentaDivisas' => 'decimal:2',
        'TotalFacturas' => 'integer',
        'TransferenciaBs' => 'decimal:2',
        'TransferenciaDivisas' => 'decimal:2',
        'Tipo' => 'integer',
        'VentaSistema' => 'decimal:2',
        'ZelleDivisas' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function transacciones()
    {
        return $this->hasMany(TransaccionCierreDiario::class, 'CierreDiarioId');
    }

    public function pagosPuntoDeVenta(): HasMany
    {
        return $this->hasMany(PagoPuntoDeVenta::class, 'CierreDiarioId');
    }
}