<?php
// app/Models/ValorizacionInventario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValorizacionInventario extends BaseModel
{
    use HasFactory;

    protected $table = 'ValorizacionInventario';
    public $timestamps = false;
    
    protected $casts = [
        'Existencia' => 'integer',
        'Referencias' => 'integer',
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'PvpBs' => 'decimal:2',
        'PvpDivisa' => 'decimal:2',
        'SucursalId' => 'integer',
    ];
}