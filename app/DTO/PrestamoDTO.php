<?php

namespace App\DTO;

use Carbon\Carbon;
use App\Enums\EnumPrestamo;
use App\Enums\EnumTipoPrestamo;

class PrestamoDTO
{
    // Propiedades básicas
    public int $prestamoId;
    public string $usuarioId;
    public ?object $usuario = null; // UsuarioRegistradoIdentity equivalente
    public Carbon $fecha;
    public ?Carbon $fechaCierre = null;
    public string $observacion = '';
    
    // Montos
    public float $montoDivisa = 0;
    public float $montoBs = 0;
    
    // Enums
    public int $estatus; // EnumPrestamo
    public int $tipo; // EnumTipoPrestamo
    
    // Relaciones
    public array $prestamosDetalles = []; // List<PrestamoDetallesDTO>
    public array $listaPagos = []; // List<TransaccionDTO>
    public ?object $pago = null; // TransaccionDTO
    public ?object $ultimoAbono = null; // TransaccionDTO
    public ?object $divisaValor = null; // DivisaValorDTO
    public ?object $productoSeleccionado = null; // ProductoDTO
    
    // IDs adicionales
    public int $sucursalId;
    public int $tasaCambioId;
    
    // Propiedades calculadas (getters en PHP)
    public function getTotalUnidadesAttribute(): int
    {
        if (!empty($this->prestamosDetalles)) {
            return collect($this->prestamosDetalles)->sum('cantidad');
        }
        return 0;
    }
    
    public function getTotalItemsAttribute(): int
    {
        return count($this->prestamosDetalles);
    }
    
    public function getTotalProductosBsAttribute(): float
    {
        if (empty($this->prestamosDetalles)) {
            return 0;
        }
        
        $total = collect($this->prestamosDetalles)->sum(function($detalle) {
            return ($detalle->pvpDivisa ?? 0) * ($detalle->cantidad ?? 0);
        });
        
        if ($this->divisaValor && isset($this->divisaValor->valor)) {
            return $total * $this->divisaValor->valor;
        }
        
        return collect($this->prestamosDetalles)->sum(function($detalle) {
            return ($detalle->pvpBs ?? 0) * ($detalle->cantidad ?? 0);
        });
    }
    
    public function getTotalProductosDivisasAttribute(): float
    {
        if (empty($this->prestamosDetalles)) {
            return 0;
        }
        
        return collect($this->prestamosDetalles)->sum(function($detalle) {
            return ($detalle->pvpDivisa ?? 0) * ($detalle->cantidad ?? 0);
        });
    }
    
    public function getTotalAbonadoBsAttribute(): float
    {
        if (empty($this->listaPagos)) {
            return 0;
        }
        
        $total = collect($this->listaPagos)->sum('montoDivisaAbonado');
        
        if ($this->divisaValor && isset($this->divisaValor->valor)) {
            return $total * $this->divisaValor->valor;
        }
        
        return collect($this->listaPagos)->sum('montoAbonado');
    }
    
    public function getTotalAbonadoDivisaAttribute(): float
    {
        if (empty($this->listaPagos)) {
            return 0;
        }
        
        return collect($this->listaPagos)->sum('montoDivisaAbonado');
    }
    
    public function getTotalPrestamoDivisaAttribute(): float
    {
        return $this->totalProductosDivisas + $this->montoDivisa;
    }
    
    public function getTotalPrestamoBsAttribute(): float
    {
        if ($this->divisaValor && isset($this->divisaValor->valor)) {
            return $this->totalPrestamoDivisa * $this->divisaValor->valor;
        }
        
        return $this->totalProductosBs + $this->montoBs;
    }
    
    public function getSaldoDivisaAttribute(): float
    {
        return $this->totalPrestamoDivisa - $this->totalAbonadoDivisa;
    }
    
    public function getSaldoBsAttribute(): float
    {
        if ($this->divisaValor && isset($this->divisaValor->valor)) {
            return ($this->totalPrestamoDivisa - $this->totalAbonadoDivisa) * $this->divisaValor->valor;
        }
        
        return $this->totalPrestamoBs - $this->totalAbonadoBs;
    }
    
    public function getTotalSaldoBsAttribute(): float
    {
        return $this->saldoBs;
    }
    
    public function getPorcentajeSaldoDivisaAttribute(): float
    {
        if ($this->totalPrestamoDivisa == 0) {
            return 0;
        }
        
        $porcentaje = ($this->saldoDivisa * 100) / $this->totalPrestamoDivisa;
        return round($porcentaje, 2);
    }
    
    public function getPorcentajeSaldoBsAttribute(): float
    {
        if ($this->totalPrestamoBs == 0) {
            return 0;
        }
        
        $porcentaje = ($this->saldoBs * 100) / $this->totalPrestamoBs;
        return round($porcentaje, 2);
    }
    
    public function getPorcentajePagosDivisasAttribute(): float
    {
        if ($this->totalPrestamoDivisa == 0) {
            return 0;
        }
        
        return round(($this->totalAbonadoDivisa * 100) / $this->totalPrestamoDivisa, 2);
    }
    
    public function getPorcentajePagosBsAttribute(): float
    {
        if ($this->totalPrestamoBs == 0) {
            return 0;
        }
        
        return round(($this->totalAbonadoBs * 100) / $this->totalPrestamoBs, 2);
    }
    
    public function getDiasEmisionAttribute(): int
    {
        return Carbon::now()->diffInDays($this->fecha);
    }
    
    // Constructor
    public function __construct(array $data = [])
    {
        $this->prestamoId = $data['prestamoId'] ?? 0;
        $this->usuarioId = $data['usuarioId'] ?? '';
        $this->fecha = isset($data['fecha']) ? Carbon::parse($data['fecha']) : Carbon::now();
        $this->fechaCierre = isset($data['fechaCierre']) ? Carbon::parse($data['fechaCierre']) : null;
        $this->observacion = $data['observacion'] ?? '';
        $this->montoDivisa = (float)($data['montoDivisa'] ?? 0);
        $this->montoBs = (float)($data['montoBs'] ?? 0);
        $this->estatus = $data['estatus'] ?? EnumPrestamo::NUEVO;
        $this->tipo = $data['tipo'] ?? 0;
        $this->sucursalId = $data['sucursalId'] ?? 0;
        $this->tasaCambioId = $data['tasaCambioId'] ?? 0;
        $this->prestamosDetalles = $data['prestamosDetalles'] ?? [];
        $this->listaPagos = $data['listaPagos'] ?? [];
    }
    
    // Método para formatear números (similar a DisplayFormat)
    public function formatearNumero($valor, $decimales = 2): string
    {
        return number_format($valor, $decimales, ',', '.');
    }
    
    // Método para formatear fecha (similar a DataType.Date)
    public function getFechaFormateadaAttribute(): string
    {
        return $this->fecha->format('Y-m-d');
    }
    
    // Método para convertir a array (útil para respuestas JSON)
    public function toArray(): array
    {
        return [
            'prestamoId' => $this->prestamoId,
            'usuarioId' => $this->usuarioId,
            'usuario' => $this->usuario,
            'fecha' => $this->fecha->format('Y-m-d'),
            'fechaCierre' => $this->fechaCierre?->format('Y-m-d'),
            'observacion' => $this->observacion,
            'montoDivisa' => $this->montoDivisa,
            'montoBs' => $this->montoBs,
            'estatus' => $this->estatus,
            'tipo' => $this->tipo,
            'sucursalId' => $this->sucursalId,
            'tasaCambioId' => $this->tasaCambioId,
            'divisaValor' => $this->divisaValor,
            'totalUnidades' => $this->totalUnidades,
            'totalItems' => $this->totalItems,
            'totalProductosBs' => $this->totalProductosBs,
            'totalProductosDivisas' => $this->totalProductosDivisas,
            'totalAbonadoBs' => $this->totalAbonadoBs,
            'totalAbonadoDivisa' => $this->totalAbonadoDivisa,
            'totalPrestamoBs' => $this->totalPrestamoBs,
            'totalPrestamoDivisa' => $this->totalPrestamoDivisa,
            'saldoBs' => $this->saldoBs,
            'saldoDivisa' => $this->saldoDivisa,
            'porcentajeSaldoBs' => $this->porcentajeSaldoBs,
            'porcentajeSaldoDivisa' => $this->porcentajeSaldoDivisa,
            'porcentajePagosBs' => $this->porcentajePagosBs,
            'porcentajePagosDivisas' => $this->porcentajePagosDivisas,
            'diasEmision' => $this->diasEmision,
            'prestamosDetalles' => $this->prestamosDetalles,
            'listaPagos' => $this->listaPagos,
        ];
    }
}