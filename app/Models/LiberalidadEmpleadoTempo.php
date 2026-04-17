<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiberalidadEmpleadoTempo extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'LiberalidadEmpleadoTempo';
    protected $primaryKey = 'Id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'Mes',
        'Anno',
        'EmpleadoId',
        'UsuarioId',
        'FechaCreacion',
        'MontoLiberalidadDivisa',
        'MontoLiberalidadBs',
        'MontoDescuentoDivisa',
        'MontoDescuentoBs',
        'DisponibleLiberalidadDivisa',
        'DisponibleLiberalidadBs',
        'Tasa'
    ];

    protected $casts = [
        'Mes' => 'integer',
        'Anno' => 'integer',
        'MontoLiberalidadDivisa' => 'decimal:2',
        'MontoLiberalidadBs' => 'decimal:2',
        'MontoDescuentoDivisa' => 'decimal:2',
        'MontoDescuentoBs' => 'decimal:2',
        'DisponibleLiberalidadDivisa' => 'decimal:2',
        'DisponibleLiberalidadBs' => 'decimal:2',
        'Tasa' => 'decimal:2',
        'FechaCreacion' => 'datetime'
    ];

    // Relación con AspNetUser (empleado de sistema)
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(AspNetUser::class, 'EmpleadoId', 'Id');
    }

    // Relación con Usuario (vendedor temporal)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioId', 'UsuarioId');
    }

    // Scope para filtrar por período
    public function scopePeriodo($query, $mes, $anio)
    {
        return $query->where('Mes', $mes)->where('Anno', $anio);
    }

    // Scope para filtrar por empleado (sistema)
    public function scopeEmpleado($query, $empleadoId)
    {
        return $query->where('EmpleadoId', $empleadoId);
    }

    // Scope para filtrar por usuario (temporal)
    public function scopeUsuario($query, $usuarioId)
    {
        return $query->where('UsuarioId', $usuarioId);
    }

    // Método para obtener el disponible en divisa
    public function getDisponibleDivisaAttribute()
    {
        return $this->DisponibleLiberalidadDivisa;
    }

    // Método para obtener el disponible en bolívares
    public function getDisponibleBsAttribute()
    {
        return $this->DisponibleLiberalidadBs;
    }

    // Método para verificar si tiene disponible
    public function tieneDisponible()
    {
        return $this->DisponibleLiberalidadDivisa > 0;
    }
}