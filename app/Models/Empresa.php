<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Empresa';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'NumeroFiscal',
        'Nombre',
        'Resena',
        'Direccion',
        'Objetivo',
        'Mision',
        'Vision',
        'FechaFundada',
        'Website',
        'Telefono1',
        'Telefono2',
        'Facebook',
        'Instagram',
        'Twitter',
        'Linkedin',
        'Propietario',
        'Pais'
    ];

    protected $casts = [
        'FechaFundada' => 'datetime'
    ];

    public $timestamps = false;
}