<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Paises';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id',
        'CodigoTelefonico',
        'Nombre'
    ];

    public $timestamps = false;

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'PaisId');
    }
}