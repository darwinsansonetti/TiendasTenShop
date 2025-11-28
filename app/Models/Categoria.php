<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Categoria';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'Id',
        'DepartamentoId',
        'Categoria',
        'Notas'
    ];

    public $timestamps = false;

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'DepartamentoId', 'Id');
    }

    public function familias(): HasMany
    {
        return $this->hasMany(Familia::class, 'CategoriaId', 'Id');
    }
}