<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesDetalles';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'TransaccionId',
        'Descripcion',
        'Fecha',
        'MontoGeneral',
        'MontoGeneralDivisa',
        'MontoImpuesto',
        'MontoImpuestoDivisa'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'MontoGeneral' => 'decimal:2',
        'MontoGeneralDivisa' => 'decimal:2',
        'MontoImpuesto' => 'decimal:2',
        'MontoImpuestoDivisa' => 'decimal:2'
    ];

    public $timestamps = false;

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }
}