<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionNoVendible extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'RecepcionesNoVendibles';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'RecepcionId',
        'ProductoId',
        'MotivoNoVendibleId',
        'Cantidad'
    ];

    protected $casts = [
        'Cantidad' => 'decimal:2'
    ];

    public $timestamps = false;

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }

    public function motivoNoVendible(): BelongsTo
    {
        return $this->belongsTo(MotivoNoVendible::class, 'MotivoNoVendibleId');
    }
}