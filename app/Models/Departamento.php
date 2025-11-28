<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Departamentos';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'Departamento',
        'Notas'
    ];

    public $timestamps = false;

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'DepartamentoId', 'Id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'DepartamentoId', 'Id');
    }
}