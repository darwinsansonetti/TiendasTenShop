<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Menu';
    protected $primaryKey = 'MenuId';

    protected $fillable = [
        'MenuId',
        'NumeroGrupo',
        'GrupoOpcion',
        'NumeroOpcion',
        'Opcion',
        'Claim',
        'EsClaim'
    ];

    protected $casts = [
        'NumeroGrupo' => 'integer',
        'NumeroOpcion' => 'integer',
        'EsClaim' => 'boolean'
    ];

    public $timestamps = false;
}