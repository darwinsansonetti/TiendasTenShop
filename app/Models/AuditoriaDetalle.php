<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'AuditoriaDetalles';
    protected $primaryKey = 'AuditoriaDetalleId';

    protected $fillable = [
        'AuditoriaDetalleId',
        'AuditoriaId',
        'RecepcionDetalleId',
        'Detalle',
        'Accion'
    ];

    protected $casts = [
        'Accion' => 'integer'
    ];

    public $timestamps = false;

    public function auditoria(): BelongsTo
    {
        return $this->belongsTo(Auditoria::class, 'AuditoriaId');
    }
}