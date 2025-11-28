<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Bancos';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Nombre',
        'EsActivo'
    ];

    protected $casts = [
        'EsActivo' => 'boolean'
    ];

    public $timestamps = false;

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'BancoId');
    }

    public function puntosDeVenta(): HasMany
    {
        return $this->hasMany(PuntoDeVenta::class, 'BancoId');
    }
}