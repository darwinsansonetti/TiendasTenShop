<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductoModel extends Model
{
    /**
     * Nombre de la tabla (igual que en .NET)
     */
    protected $table = 'Productos';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'ID';

    /**
     * Indica si los IDs son autoincrementales
     */
    public $incrementing = true;

    /**
     * Tipo de la clave primaria
     */
    protected $keyType = 'int';

    /**
     * Indica si el modelo tiene timestamps
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa
     */
    protected $fillable = [
        'Codigo',
        'CodigoBarra',
        'Referencia',
        'Descripcion',
        'CostoBs',
        'CostoDivisa',
        'UrlFoto',
        'FechaActualizacion',
        'FechaCreacion',
        'Estatus',
        'EsProveedorAsignado'
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected $casts = [
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'FechaActualizacion' => 'datetime:Y-m-d',
        'FechaCreacion' => 'datetime:Y-m-d',
        'Estatus' => 'integer',
        'EsProveedorAsignado' => 'boolean'
    ];

    /**
     * Mutador para CodigoBarra (igual que en .NET)
     */
    protected function codigoBarra(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => empty(trim($value ?? '')) ? '0' : $value,
            set: fn ($value) => $value
        );
    }

    /**
     * Mutador para Referencia (igual que en .NET)
     */
    protected function referencia(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => empty(trim($value ?? '')) ? 'N/A' : $value,
            set: fn ($value) => $value
        );
    }

    /**
     * RELACIONES
     */

    /**
     * Relación con RecepcionesDetalles
     */
    public function recepcionesDetalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class, 'ProductoId', 'ID');
    }

    /**
     * Relación con ProveedorProducto
     */
    public function proveedorProducto(): HasMany
    {
        return $this->hasMany(ProveedorProducto::class, 'ProductoId', 'ID');
    }

    /**
     * Relación con PrestamoDetalles
     */
    public function prestamosDetalles(): HasMany
    {
        return $this->hasMany(PrestamoDetalles::class, 'ProductoId', 'ID');
    }

    /**
     * Relación con TransferenciaDetalles (comentada en .NET)
     */
    // public function transferenciaDetalles(): HasMany
    // {
    //     return $this->hasMany(TransferenciaDetalles::class, 'ProductoId', 'ID');
    // }

    /**
     * MÉTODOS PERSONALIZADOS
     */

    /**
     * Obtener el costo en Bs formateado
     */
    public function getCostoBsFormateadoAttribute(): string
    {
        return number_format($this->CostoBs, 2, ',', '.');
    }

    /**
     * Obtener el costo en Divisa formateado
     */
    public function getCostoDivisaFormateadoAttribute(): string
    {
        return number_format($this->CostoDivisa, 2, ',', '.');
    }

    /**
     * Verificar si el producto está activo
     */
    public function getEstaActivoAttribute(): bool
    {
        return $this->Estatus == 1; // Ajusta según tu lógica de estatus
    }

    /**
     * Obtener la fecha de actualización formateada
     */
    public function getFechaActualizacionFormateadaAttribute(): ?string
    {
        return $this->FechaActualizacion?->format('Y-m-d');
    }

    /**
     * Obtener la fecha de creación formateada
     */
    public function getFechaCreacionFormateadaAttribute(): ?string
    {
        return $this->FechaCreacion?->format('Y-m-d');
    }

    /**
     * SCOPES
     */

    /**
     * Scope para productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('Estatus', 1); // Ajusta según tu lógica
    }

    /**
     * Scope para productos con proveedor asignado
     */
    public function scopeConProveedorAsignado($query)
    {
        return $query->where('EsProveedorAsignado', true);
    }

    /**
     * Scope para buscar por código
     */
    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('Codigo', 'LIKE', "%{$codigo}%");
    }

    /**
     * Scope para buscar por descripción
     */
    public function scopePorDescripcion($query, $descripcion)
    {
        return $query->where('Descripcion', 'LIKE', "%{$descripcion}%");
    }

    /**
     * Scope para productos creados entre fechas
     */
    public function scopeCreadosEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('FechaCreacion', [$fechaInicio, $fechaFin]);
    }
}