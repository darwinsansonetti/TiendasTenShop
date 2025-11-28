<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionProveedor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesProveedor';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ProveedorId',
        'TransaccionId',
        'FacturaId'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'FacturaId', 'ID');
    }
}