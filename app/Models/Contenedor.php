<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contenedor extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Contenedor';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'Nombre',
        'FechaCreacion',
        'FechaDespacho',
        'FechaRecepcion',
        'Flete',
        'Aduana',
        'NumeroOperacion',
        'Origen',
        'Estatus',
        'Observacion',
        'EsNumeroAutomatico'
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
        'FechaDespacho' => 'datetime',
        'FechaRecepcion' => 'datetime',
        'Flete' => 'decimal:2',
        'Aduana' => 'decimal:2',
        'Estatus' => 'integer',
        'EsNumeroAutomatico' => 'boolean'
    ];

    public $timestamps = false;

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'ContenedorId');
    }
}