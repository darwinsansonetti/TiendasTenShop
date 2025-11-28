<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Prestamos';
    protected $primaryKey = 'PrestamoId';

    protected $fillable = [
        'PrestamoId',
        'UsuarioId',
        'Fecha',
        'FechaCierre',
        'Observacion',
        'MontoBs',
        'MontoDivisa',
        'Estatus',
        'Tipo',
        'TasaCambioId',
        'SucursalId'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'FechaCierre' => 'datetime',
        'MontoBs' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'UsuarioId');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PrestamoDetalle::class, 'PrestamoId');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}