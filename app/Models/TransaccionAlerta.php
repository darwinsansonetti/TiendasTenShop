<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionAlerta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesAlerta';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'AlertaId',
        'TransaccionId'
    ];

    public $timestamps = false;

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class, 'AlertaId', 'ID');
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }
}