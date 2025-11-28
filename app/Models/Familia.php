<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Familia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Familia';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'Id',
        'DepartamentoId',
        'CategoriaId',
        'NombreFamilia',
        'Notas'
    ];

    public $timestamps = false;

    // Relaciones
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'DepartamentoId', 'Id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'CategoriaId', 'Id');
    }
}