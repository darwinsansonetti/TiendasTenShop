<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoEquivalencia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ProductosEquivalencia';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'IdExterno',
        'ProductoId'
    ];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }
}