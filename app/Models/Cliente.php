<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Clientes';
    protected $primaryKey = 'ClienteId';

    protected $fillable = [
        'ClienteId',
        'Rif_Cedula',
        'Nombre',
        'Direccion',
        'TelefonoMovil',
        'TelefonoFijo',
        'CorreoElectronico',
        'Fecha',
        'Estatus',
        'Tipo',
        'UrlImagen'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function facturaciones(): HasMany
    {
        return $this->hasMany(Facturacion::class, 'ClienteId');
    }
}