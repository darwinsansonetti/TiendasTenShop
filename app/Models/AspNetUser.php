<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AspNetUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'sqlsrv';
    protected $table = 'AspNetUsers';
    protected $primaryKey = 'Id';
    protected $keyType = 'string';

    protected $fillable = [
        'Id',
        'UserName',
        'NormalizedUserName',
        'Email',
        'NormalizedEmail',
        'EmailConfirmed',
        'PasswordHash',
        'SecurityStamp',
        'ConcurrencyStamp',
        'PhoneNumber',
        'PhoneNumberConfirmed',
        'TwoFactorEnabled',
        'LockoutEnd',
        'LockoutEnabled',
        'AccessFailedCount',
        'VendedorId',
        'EsActivo',
        'NombreCompleto',
        'Direccion',
        'FechaCreacion',
        'FechaNacimiento',
        'SucursalId',
        'FotoPerfil',
        'ExternalId',
        'Password',
    ];

    protected $casts = [
        'EmailConfirmed' => 'boolean',
        'PhoneNumberConfirmed' => 'boolean',
        'TwoFactorEnabled' => 'boolean',
        'LockoutEnabled' => 'boolean',
        'AccessFailedCount' => 'integer',
        'EsActivo' => 'boolean',
        'FechaCreacion' => 'datetime',
        'FechaNacimiento' => 'datetime',
        'ExternalId' => 'integer'
    ];

    public $timestamps = false;

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}