<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UxE extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'UxE';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'Simbolo',
        'Descripcion',
        'Unidades'
    ];

    protected $casts = [
        'Unidades' => 'integer'
    ];

    public $timestamps = false;
}