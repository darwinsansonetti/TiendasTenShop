<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaGasto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CategoriaGastos';
    protected $primaryKey = 'CategoriaId';

    protected $fillable = [
        'CategoriaId',
        'Nombre',
        'EsActivo'
    ];

    protected $casts = [
        'EsActivo' => 'boolean'
    ];

    public $timestamps = false;
}