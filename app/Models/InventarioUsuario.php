<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioUsuario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'InventarioUsuario';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'InventarioId',
        'InventarioDetalleId',
        'UsuarioTranscripcionId',
        'UsuarioConteoId'
    ];

    public $timestamps = false;

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'InventarioId');
    }

    public function inventarioDetalle(): BelongsTo
    {
        return $this->belongsTo(InventarioDetalle::class, 'InventarioDetalleId');
    }

    public function usuarioTranscripcion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioTranscripcionId', 'UsuarioId');
    }

    public function usuarioConteo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioConteoId', 'UsuarioId');
    }
}