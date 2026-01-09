<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Divisa;
use App\Models\DivisaValor;
use App\Models\Mensaje;
use App\Models\Producto;

use App\Helpers\ParametrosFiltroFecha;

use App\Models\VentaDiariaTotalizada;
use App\Models\VentaVendedoresTotalizada;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use App\DTO\ComparacionSucursalesDTO;
use App\DTO\ComparacionSucursalesDetalleDTO;

use App\DTO\IndiceDeRotacionDTO;
use App\DTO\IndiceDeRotacionDetallesDTO;

use App\DTO\ComparacionSinVentaDTO;
use App\DTO\ComparacionSinVentaDetallesDTO;
use App\DTO\ProductoDTO;

use Illuminate\Support\Facades\Auth;

class VentasHelper
{
    public static function BuscarListadoVentasDiarias(ParametrosFiltroFecha $filtro, ?int $sucursalId) 
    {
        $user = Auth::user()->load('sucursal');

        // Instanciar servicios (puedes pasarlos por constructor si usas IoC)
        $ventasService = new VentasService();

        // Si es una Tienda == 1, Si es 0 Es "OFICINA PRINCIPAL"
        if($user && $user->sucursal->Tipo == 1){

            // Si la Sucursal esta Activa
            if($user->sucursal->EsActiva == 1){
                // ================================
                // VENTAS DIARIAS
                // ================================
                $balanceSucursal['Ventas'] =
                    $ventasService->obtenerListadoVentasDiarias(
                        $filtro,
                        $sucursalId,
                        false
                    );

            }
        }else{
            // ================================
            // VENTAS DIARIAS
            // ================================
            $balanceSucursal['Ventas'] =
                $ventasService->obtenerListadoVentasDiarias(
                    $filtro,
                    $sucursalId,
                    false
                );
        }

        // dd($balanceSucursal['Ventas']);

        $balanceSucursal['FechaInicio'] = $filtro->fechaInicio->startOfDay();
        $balanceSucursal['FechaFin'] = $filtro->fechaFin->startOfDay();

        return $balanceSucursal;
    }
}