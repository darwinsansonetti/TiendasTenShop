<?php
// app/Models/TransferenciaTotalizadaView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferenciaTotalizadaView extends BaseModel
{
    use HasFactory;

    protected $table = 'TransferenciaTotalizadaView';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'TransferenciaId' => 'integer',
        'SucursalOrigenId' => 'integer',
        'SucursalDestinoId' => 'integer',
        'Estatus' => 'integer',
        'Tipo' => 'integer',
        'CantidadEmitida' => 'decimal:2',
        'CantidadRecibida' => 'decimal:2',
        'CantidadDisponible' => 'decimal:2',
        'CantidadItems' => 'integer',
    ];
}