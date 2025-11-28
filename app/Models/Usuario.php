<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Usuarios';
    protected $primaryKey = 'UsuarioId';
    protected $keyType = 'string';

    protected $fillable = [
        'UsuarioId',
        'VendedorId',
        'Email',
        'EsActivo',
        'PhoneNumber',
        'NombreCompleto',
        'Direccion',
        'FechaCreacion',
        'FechaNacimiento',
        'SucursalId',
        'FotoPerfil',
        'EsRegistrado'
    ];

    protected $casts = [
        'EsActivo' => 'boolean',
        'FechaCreacion' => 'datetime',
        'FechaNacimiento' => 'datetime',
        'EsRegistrado' => 'boolean'
    ];

    public $timestamps = false;

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class, 'UsuarioId', 'UsuarioId');
    }
}