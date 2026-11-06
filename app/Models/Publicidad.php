<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publicidad extends Model
{
    use HasFactory;

    protected $table = 'Publicidad';
    protected $primaryKey = 'Id';
    
    // 🚀 DESACTIVAMOS LOS TIMESTAMPS AUTOMÁTICOS
    public $timestamps = false;

    protected $fillable = [
        'UrlFoto',
        'EsActivo',
        'FechaIni',
        'FechaFin'
    ];

    protected $casts = [
        'EsActivo' => 'boolean',
        'FechaIni' => 'datetime',
        'FechaFin' => 'datetime',
    ];
}