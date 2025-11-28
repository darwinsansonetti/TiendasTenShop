<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionRecepcion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesRecepciones';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'SucursalId',
        'RecepcionId',
        'TransaccionId'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }
}