<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    use HasFactory;

    protected $table = 'Galeria';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'UrlFoto',
        'EsActivo'
    ];

    protected $casts = [
        'EsActivo' => 'boolean',
    ];
}