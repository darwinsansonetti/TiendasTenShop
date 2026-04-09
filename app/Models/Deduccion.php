<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduccion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Deducciones';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'MesDeduccion',
        'AnnoDeduccion',
        'FechaCreacion',
        'EmpleadoId',
        'UsuarioId',
        'TipoDeduccion',
        'MontoBs',
        'MontoDivisa',
        'Tasa',
        'EsPagado',
        'Motivo'
    ];

    protected $casts = [
        'ID' => 'integer',
        'MesDeduccion' => 'integer',
        'AnnoDeduccion' => 'integer',
        'FechaCreacion' => 'datetime',
        'MontoBs' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'Tasa' => 'decimal:2',
        'EsPagado' => 'boolean'
    ];

    // =============================================
    // RELACIONES
    // =============================================

    /**
     * Relación con AspNetUsers (usuario del sistema)
     */
    public function empleadoSistema(): BelongsTo
    {
        return $this->belongsTo(AspNetUser::class, 'EmpleadoId', 'Id');
    }

    /**
     * Relación con Usuarios (vendedor temporal)
     */
    public function vendedorTemporal(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'UsuarioId');
    }
}