<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPrecio extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ProductosPrecio';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'Id',
        'ProductoId',
        'PvpBs',
        'PvDivisa',
        'FechaInicial',
        'FechaFinal',
        'EsVigente'
    ];

    protected $casts = [
        'PvpBs' => 'decimal:2',
        'PvDivisa' => 'decimal:2',
        'FechaInicial' => 'date',
        'FechaFinal' => 'string',
        'EsVigente' => 'boolean'
    ];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}