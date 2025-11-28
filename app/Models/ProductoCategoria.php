<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoCategoria extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ProductosCategoria';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'ProductoId',
        'DepartamentoId',
        'CategoriaId',
        'FamiliaId'
    ];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'DepartamentoId', 'Id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'CategoriaId', 'Id');
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class, 'FamiliaId', 'Id');
    }
}