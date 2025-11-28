<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaMetaGanador extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'VentaMetaGanadores';
    protected $primaryKey = 'VentaMetaId';

    protected $fillable = [
        'VentaMetaId',
        'PrimerLugarId',
        'SegundoLugarId',
        'TercerLugarId'
    ];

    public $timestamps = false;

    public function ventaMeta(): BelongsTo
    {
        return $this->belongsTo(VentaMeta::class, 'VentaMetaId', 'VentasMetaId');
    }
}