<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionFactura extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'RecepcionesFacturas';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'RecepcionId',
        'FacturaId'
    ];

    public $timestamps = false;

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'RecepcionId');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'FacturaId', 'ID');
    }
}