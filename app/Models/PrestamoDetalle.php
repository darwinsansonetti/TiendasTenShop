<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestamoDetalle extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'PrestamosDetalles';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'PrestamoId',
        'ProductoId',
        'Cantidad',
        'PvpBs',
        'PvpDivisa'
    ];

    protected $casts = [
        'Cantidad' => 'integer',
        'PvpBs' => 'decimal:2',
        'PvpDivisa' => 'decimal:2'
    ];

    public $timestamps = false;

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'PrestamoId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}