<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoria extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Auditorias';
    protected $primaryKey = 'AuditoriaId';

    protected $fillable = [
        'AuditoriaId',
        'RecepcionId',
        'Fecha',
        'Numero',
        'Estatus',
        'EsRecepcionParcial',
        'Observacion'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'EsRecepcionParcial' => 'boolean'
    ];

    public $timestamps = false;

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(AuditoriaDetalle::class, 'AuditoriaId');
    }
}