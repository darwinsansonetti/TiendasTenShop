<?php

namespace App\DTO;

use Illuminate\Support\Collection;
use App\DTO\FacturaDTO;
// use App\DTO\ServicioDTO;
use App\DTO\ProductoDTO;
use App\DTO\TransaccionDTO;

class ProveedorDTO
{
    // Datos básicos
    public int $ProveedorId = 0;
    public string $RifCedula = '';
    public string $Nombre = '';
    public string $Direccion = '';
    public string $TelefonoMovil = '';
    public string $TelefonoFijo = '';
    public string $CorreoElectronico = '';
    public string $UrlImagen = '';
    public string $Facebook = '';
    public string $Twitter = '';
    public string $Instagram = '';
    public string $NumeroDeCuenta = '';
    
    // Relaciones
    public ?int $PaisId = null;
    public ?int $SucursalId = null;
    public ?int $BancoId = null;
    
    // Fechas y estados
    public ?string $FechaCreacion = null;
    public int $Estatus = 1; // 1 = Activo
    public int $Tipo = 0; // 0 = Mercancía
    
    // Colecciones
    public Collection $FacturasVigentes;
    public Collection $FacturasHistoricas;
    public Collection $Servicios;
    public Collection $Productos;
    public Collection $TransaccionesVigentes;
    public Collection $TransaccionesHistoricas;
    
    // Totales y cálculos
    public float $TotalDivisasFactura = 0;
    public float $TotalBsFacturas = 0;
    public float $PagosDivisasFacturas = 0;
    public float $PagosBolivaresFacturas = 0;
    public float $TotalDivisasServicio = 0;
    public float $SubTotal = 0;
    
    public function __construct()
    {
        $this->FacturasVigentes = collect();
        $this->FacturasHistoricas = collect();
        $this->Servicios = collect();
        $this->Productos = collect();
        $this->TransaccionesVigentes = collect();
        $this->TransaccionesHistoricas = collect();
    }
    
    // ============================================
    // PROPIEDADES CALCULADAS
    // ============================================
    
    public function getSaldoDivisasFacturas(): float
    {
        return abs($this->TotalDivisasFactura - $this->PagosDivisasFacturas);
    }
    
    public function getSaldoDivisasServicios(): float
    {
        return abs($this->TotalDivisasServicio - $this->PagosDivisasFacturas);
    }
    
    public function getPorcentajePagos(): float
    {
        if ($this->TotalDivisasFactura != 0) {
            return round(($this->PagosDivisasFacturas * 100) / $this->TotalDivisasFactura, 2);
        }
        return 0;
    }
    
    public function getPorcentajeSaldo(): float
    {
        if ($this->TotalDivisasFactura != 0) {
            $saldo = $this->getSaldoDivisasFacturas();
            return round(($saldo * 100) / $this->TotalDivisasFactura, 2);
        }
        return 0;
    }
    
    public function getCantidadItemsSinCosto(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $count = $this->Productos->filter(fn($p) => ($p->CostoDivisa ?? 0) == 0)->count();
            return number_format($count, 0, ',', '.');
        }
        return '0';
    }
    
    public function getCantidadItemsSinExistencia(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $count = $this->Productos->filter(fn($p) => ($p->Existencia ?? 0) == 0)->count();
            return number_format($count, 0, ',', '.');
        }
        return '0';
    }
    
    public function getCantidadItemsSinPrecio(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $count = $this->Productos->filter(fn($p) => ($p->PvpDivisa ?? 0) == 0)->count();
            return number_format($count, 0, ',', '.');
        }
        return '0';
    }
    
    public function getNumeroDeReferencias(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            return number_format($this->Productos->count(), 0, ',', '.');
        }
        return '0';
    }
    
    public function getUnidadesEnExistencia(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $total = $this->Productos->sum(fn($p) => $p->Existencia ?? 0);
            return number_format($total, 0, ',', '.');
        }
        return '0';
    }
    
    public function getValorizacionInventario(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $total = $this->Productos->sum(fn($p) => ($p->CostoDivisa ?? 0) * ($p->Existencia ?? 0));
            return number_format($total, 2, ',', '.');
        }
        return '0,00';
    }
    
    public function getUtilidadTotal(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $total = $this->Productos->sum(fn($p) => round(($p->UtilidadDivisaUnitario ?? 0) * ($p->Existencia ?? 0), 2));
            return number_format($total, 2, ',', '.');
        }
        return '0,00';
    }
    
    public function getPvpEstimado(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $total = $this->Productos->sum(fn($p) => ($p->PvpDivisa ?? 0) * ($p->Existencia ?? 0));
            return number_format($total, 2, ',', '.');
        }
        return '0,00';
    }
    
    public function getMargenEstimado(): string
    {
        if ($this->Productos && $this->Productos->count() > 0) {
            $productosConMargen = $this->Productos->filter(fn($p) => ($p->Margen ?? 0) != 0);
            $cantidad = $productosConMargen->count();
            
            if ($cantidad > 0) {
                $margenBruto = $productosConMargen->sum(fn($p) => $p->Margen ?? 0);
                $margenTotal = $margenBruto / $cantidad;
                return number_format($margenTotal, 2, ',', '.') . '%';
            }
        }
        return '0,00%';
    }
}