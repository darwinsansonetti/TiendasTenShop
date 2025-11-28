<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoNoVendible extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'MotivosNoVendible';
    protected $primaryKey = 'MotivosNoVendibleId';

    protected $fillable = [
        'MotivosNoVendibleId',
        'Descripcion',
        'EsRebajaInventario'
    ];

    protected $casts = [
        'EsRebajaInventario' => 'boolean'
    ];

    public $timestamps = false;

    public function recepcionesNoVendibles(): HasMany
    {
        return $this->hasMany(RecepcionNoVendible::class, 'MotivoNoVendibleId');
    }
}