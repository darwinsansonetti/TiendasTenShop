<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionUsuario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'TransaccionesUsuario';
    protected $primaryKey = 'TransaccionId';
    public $incrementing = false;

    protected $fillable = [
        'UsuarioId',
        'TransaccionId',
        'PrestamoId',
        'LiberalidadId'
    ];

    public $timestamps = false;

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'UsuarioId');
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'TransaccionId', 'ID');
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'PrestamoId');
    }

    public function liberalidad(): BelongsTo
    {
        return $this->belongsTo(Liberalidad::class, 'LiberalidadId');
    }
}