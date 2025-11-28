<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisa extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Divisas';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'EsActiva',
        'EsPrincipal',
        'Simbolo',
        'Nombre'
    ];

    protected $casts = [
        'EsActiva' => 'boolean',
        'EsPrincipal' => 'boolean'
    ];

    public $timestamps = false;

    public function valores(): HasMany
    {
        return $this->hasMany(DivisaValor::class, 'DivisaId');
    }

    public function transacciones(): HasMany
    {
        return $this->hasMany(Transaccion::class, 'DivisaId');
    }
}