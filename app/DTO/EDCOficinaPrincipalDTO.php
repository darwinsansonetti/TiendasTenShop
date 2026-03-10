<?php

namespace App\DTO;

use Carbon\Carbon;

class EDCOficinaPrincipalDTO
{
    // Propiedades
    public $Fecha;
    public $SucursalId;
    public $Sucursal;
    public $VentasDiariaPeriodo;
    public $CierreDiario;
    public $CierreOFPPeriodo;
    public $EsDiaCerrado;
    public $EDCSucursales;
    public $FacturasMercancia;
    public $FacturasServicio;
    public $GastosSucursal;
    public $GastosCaja;
    public $PagosServicios;
    public $PagosFacturas;
    public $Prestamos;
    public $PagosPrestamos;

    // Constructor
    public function __construct()
    {
        $this->EsDiaCerrado = false;
        $this->EDCSucursales = [];
        $this->GastosSucursal = [];
        $this->GastosCaja = [];
        $this->PagosServicios = [];
        $this->PagosFacturas = [];
        $this->Prestamos = [];
        $this->PagosPrestamos = [];
    }

    // Propiedades calculadas (getters en PHP)
    public function getValorizacionAttribute()
    {
        if (!empty($this->EDCSucursales)) {
            $valorizacion = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->ValorizacionInventario) && $item->ValorizacionInventario) {
                    $valorizacion += $item->ValorizacionInventario->CostoDivisa ?? 0;
                }
            }
            return $valorizacion;
        }
        return 0;
    }

    public function getEgresosAttribute()
    {
        if (!empty($this->EDCSucursales)) {
            return collect($this->EDCSucursales)->sum(function($item) {
                return $item->EgresosDivisasPeriodo ?? 0;
            });
        }
        return 0;
    }

    public function getSaldoAttribute()
    {
        if (!empty($this->EDCSucursales)) {
            return collect($this->EDCSucursales)->sum(function($item) {
                return $item->SaldoSucursalPeriodo ?? 0;
            });
        }
        return 0;
    }

    public function getExistenciaAttribute()
    {
        if (!empty($this->EDCSucursales)) {
            $existencia = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->ValorizacionInventario) && $item->ValorizacionInventario) {
                    $existencia += $item->ValorizacionInventario->Existencia ?? 0;
                }
            }
            return $existencia;
        }
        return 0;
    }

    public function getReferenciasAttribute()
    {
        if (!empty($this->EDCSucursales)) {
            $referencias = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->ValorizacionInventario) && $item->ValorizacionInventario) {
                    $referencias += $item->ValorizacionInventario->Referencias ?? 0;
                }
            }
            return $referencias;
        }
        return 0;
    }

    public function getTotalVentasDivisaAttribute()
    {
        if ($this->VentasDiariaPeriodo) {
            return $this->VentasDiariaPeriodo->MontoDivisaTotalPeriodo ?? 0;
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->Ventas) && $item->Ventas) {
                    $total += $item->Ventas->MontoDivisaTotalPeriodo ?? 0;
                }
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalUnidadesVendidasAttribute()
    {
        if ($this->VentasDiariaPeriodo) {
            return $this->VentasDiariaPeriodo->UnidadesVendidas ?? 0;
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->Ventas) && $item->Ventas) {
                    $total += $item->Ventas->UnidadesVendidas ?? 0;
                }
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalGastosGeneralDivisaAttribute()
    {
        return $this->TotalGastosCajaDivisa + $this->TotalGastosSucursalDivisa;
    }

    public function getTotalGastosGeneralBsAttribute()
    {
        return $this->TotalGastosCajaBs + $this->TotalGastosSucursalBs;
    }

    public function getTotalPagosGeneralDivisaAttribute()
    {
        return $this->TotalPagoServiciosDivisa + $this->TotalPagoFacturasDivisa;
    }

    public function getTotalPagosGeneralBsAttribute()
    {
        return $this->TotalPagoServiciosBs + $this->TotalPagoFacturasBs;
    }

    public function getTotalGastosCajaDivisaAttribute()
    {
        if (!empty($this->GastosCaja)) {
            return collect($this->GastosCaja)->sum(function($item) {
                return $item->MontoDivisaAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalGastosCajaBsAttribute()
    {
        if (!empty($this->GastosCaja)) {
            return collect($this->GastosCaja)->sum(function($item) {
                return $item->MontoAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalGastosSucursalDivisaAttribute()
    {
        if (!empty($this->GastosSucursal)) {
            return collect($this->GastosSucursal)->sum(function($item) {
                return $item->MontoDivisaAbonado ?? 0;
            });
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                $total += $item->GastosDivisaPeriodo ?? 0;
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalGastosSucursalBsAttribute()
    {
        if (!empty($this->GastosSucursal)) {
            return collect($this->GastosSucursal)->sum(function($item) {
                return $item->MontoAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalVentasBsAttribute()
    {
        if ($this->VentasDiariaPeriodo) {
            return $this->VentasDiariaPeriodo->MontoBsPeriodo ?? 0;
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                if (isset($item->Ventas) && $item->Ventas) {
                    $total += $item->Ventas->MontoBsPeriodo ?? 0;
                }
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalCierreDivisaAttribute()
    {
        if ($this->CierreDiario && ($this->CierreDiario->TotalDivisaPeriodo ?? 0) > 0) {
            return $this->CierreDiario->TotalDivisaPeriodo;
        }
        return $this->TotalVentasDivisa;
    }

    public function getTotalCiereBsAttribute()
    {
        if ($this->CierreDiario && ($this->CierreDiario->TotalBsPeriodo ?? 0) > 0) {
            return $this->CierreDiario->TotalBsPeriodo;
        }
        return $this->TotalVentasBs;
    }

    public function getTotalPagoServiciosDivisaAttribute()
    {
        if (!empty($this->PagosServicios)) {
            return collect($this->PagosServicios)->sum(function($item) {
                return $item->MontoDivisaAbonado ?? 0;
            });
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                $total += $item->MontoPagosServiciosDivisa ?? 0;
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalPagoServiciosBsAttribute()
    {
        if (!empty($this->PagosServicios)) {
            return collect($this->PagosServicios)->sum(function($item) {
                return $item->MontoAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalPagoFacturasDivisaAttribute()
    {
        if (!empty($this->PagosFacturas)) {
            return collect($this->PagosFacturas)->sum(function($item) {
                return $item->MontoDivisaAbonado ?? 0;
            });
        }
        
        if (!empty($this->EDCSucursales)) {
            $total = 0;
            foreach ($this->EDCSucursales as $item) {
                $total += $item->MontoPagosMercanciaDivisa ?? 0;
            }
            return $total;
        }
        
        return 0;
    }

    public function getTotalPagoFacturasBsAttribute()
    {
        if (!empty($this->PagosFacturas)) {
            return collect($this->PagosFacturas)->sum(function($item) {
                return $item->MontoAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalPrestamosDivisaAttribute()
    {
        if (!empty($this->Prestamos)) {
            return collect($this->Prestamos)->sum(function($item) {
                return $item->MontoDivisa ?? 0;
            });
        }
        return 0;
    }

    public function getTotalPrestamosBsAttribute()
    {
        if (!empty($this->Prestamos)) {
            return collect($this->Prestamos)->sum(function($item) {
                return $item->MontoBs ?? 0;
            });
        }
        return 0;
    }

    public function getTotalAbonosDivisaAttribute()
    {
        if (!empty($this->PagosPrestamos)) {
            return collect($this->PagosPrestamos)->sum(function($item) {
                return $item->MontoDivisaAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalAbonosBsAttribute()
    {
        if (!empty($this->PagosPrestamos)) {
            return collect($this->PagosPrestamos)->sum(function($item) {
                return $item->MontoAbonado ?? 0;
            });
        }
        return 0;
    }

    public function getTotalFacturasMercanciaBsAttribute()
    {
        if ($this->FacturasMercancia) {
            return $this->FacturasMercancia->MontoBs ?? 0;
        }
        return 0;
    }

    public function getTotalFacturasMercanciaDivisaAttribute()
    {
        if ($this->FacturasMercancia) {
            return $this->FacturasMercancia->MontoDivisa ?? 0;
        }
        return 0;
    }

    public function getTotalFacturasServicioDivisaAttribute()
    {
        if ($this->FacturasServicio) {
            return $this->FacturasServicio->MontoDivisa ?? 0;
        }
        return 0;
    }

    public function getTotalFacturasServicioBsAttribute()
    {
        if ($this->FacturasServicio) {
            return $this->FacturasServicio->MontoBs ?? 0;
        }
        return 0;
    }

    public function getSaldoOperacionBsAttribute()
    {
        $total = ($this->TotalAbonosBs + $this->TotalCiereBs) -
                ($this->TotalGastosCajaBs +
                 $this->TotalGastosSucursalBs +
                 $this->TotalPagoFacturasBs + 
                 $this->TotalPagoServiciosBs +
                 $this->TotalPagosGeneralBs + 
                 $this->TotalPrestamosBs);

        return $total;
    }

    public function getSaldoOperacionDivisasAttribute()
    {
        $total = ($this->TotalAbonosDivisa + $this->TotalVentasDivisa) -
                ($this->TotalGastosCajaDivisa +
                 $this->TotalGastosSucursalDivisa +
                 $this->TotalPagosGeneralDivisa + 
                 $this->TotalPrestamosDivisa);

        return $total;
    }

    // Método para formatear fechas (similar a DataType.Date en .NET)
    public function getFechaFormateadaAttribute()
    {
        if ($this->Fecha) {
            return Carbon::parse($this->Fecha)->format('Y-m-d');
        }
        return null;
    }

    // Método para formatear números (similar a DisplayFormat)
    public function formatearNumero($valor, $decimales = 2)
    {
        if ($valor === null) return '0.00';
        return number_format($valor, $decimales, ',', '.');
    }
}