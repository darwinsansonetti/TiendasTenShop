<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionCierreDiario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesCierreDiario';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'CierreDiarioId',
        'TransaccionId'
    ];

    public $timestamps = false;

    public function cierreDiario(): BelongsTo
    {
        return $this->belongsTo(CierreDiario::class, 'CierreDiarioId');
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }
}