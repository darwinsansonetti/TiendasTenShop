<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionGasto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesGastos';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'GastoId',
        'TransaccionId'
    ];

    public $timestamps = false;

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }
}