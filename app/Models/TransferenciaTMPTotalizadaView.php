<?php
// app/Models/TransferenciaTMPTotalizadaView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferenciaTMPTotalizadaView extends BaseModel
{
    use HasFactory;

    protected $table = 'TransferenciaTMPTotalizadaView';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'TransferenciaId' => 'integer',
        'SucursalOrigenId' => 'integer',
        'Estatus' => 'integer',
        'Tipo' => 'integer',
        'CantidadEmitida' => 'decimal:2',
        'CantidadRecibida' => 'decimal:2',
        'CantidadDisponible' => 'decimal:2',
        'CantidadItems' => 'integer',
    ];
}