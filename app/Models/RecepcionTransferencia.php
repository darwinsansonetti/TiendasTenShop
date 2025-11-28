<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionTransferencia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'RecepcionesTransferencias';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'RecepcionId',
        'TransferenciaId'
    ];

    public $timestamps = false;

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(Transferencia::class, 'TransferenciaId');
    }
}