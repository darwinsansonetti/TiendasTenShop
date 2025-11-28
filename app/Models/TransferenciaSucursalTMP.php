<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaSucursalTMP extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransferenciasSucursalesTMP';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'TransferenciaId',
        'SucursalId',
        'Estatus'
    ];

    protected $casts = [
        'Estatus' => 'integer'
    ];

    public $timestamps = false;

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(Transferencia::class, 'TransferenciaId');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}