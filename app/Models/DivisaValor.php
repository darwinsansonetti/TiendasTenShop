<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisaValor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'DivisaValor';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'DivisaId',
        'Valor',
        'Fecha'
    ];

    protected $casts = [
        'Valor' => 'decimal:2',
        'Fecha' => 'datetime'
    ];

    public $timestamps = false;

    public function divisa(): BelongsTo
    {
        return $this->belongsTo(Divisa::class, 'DivisaId');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'DivisaValorId');
    }

    public function cierresDiario(): HasMany
    {
        return $this->hasMany(CierreDiario::class, 'DivisaValorId');
    }
}