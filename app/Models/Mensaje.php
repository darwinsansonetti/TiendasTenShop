<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Mensajes';
    protected $primaryKey = 'MensajeId';

    protected $fillable = [
        'MensajeId',
        'Fecha',
        'Mensaje'
    ];

    protected $casts = [
        'Fecha' => 'datetime'
    ];

    public $timestamps = false;
}