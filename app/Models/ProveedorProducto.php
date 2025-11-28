<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProveedorProducto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ProveedorProducto';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ProveedorId',
        'ProductoId'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}