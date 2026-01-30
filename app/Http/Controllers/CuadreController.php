<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\DivisaValor;
use App\Models\Venta;
use App\Models\VentaProducto;
use App\Models\VentaVendedor;
use App\Models\Producto;
use App\Models\ProductoSucursal;
use App\Models\Usuario;
use App\DTOs\CierreDiarioPeriodoDTO;
use App\Models\CierreDiario;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CuadreController extends Controller
{   

    // Resumen diario
    public function resumen_diario(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        // $fechaInicio = Carbon::parse('2026-01-01')->startOfDay();
        // $fechaFin = Carbon::parse('2026-01-05')->startOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // Asignacion al menu
        session([
            'menu_active' => 'Cuadre de Caja',
            'submenu_active' => 'Resumen Diario'
        ]);

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');
        $sucursalNombre = session('sucursal_nombre');

        $cierreDiario = collect();

        if ($sucursalId != 0) {
            // Llamamos al helper que construye los cierres diarios
            // $cierreDiario = VentasHelper::buscarListadoAuditorias($cierreDiario, $filtroFecha, $sucursalId);
            $cierreDiario = VentasHelper::buscarListadoAuditoriasNew($filtroFecha, $sucursalId);
        }

        $totalDivisa = $cierreDiario->sum('EfectivoDivisas');
        $totalEfectivoBs = $cierreDiario->sum('EfectivoBs');
        $totalPagoMovil = $cierreDiario->sum('PagoMovilBs');
        $totalPuntoVenta = $cierreDiario->sum('PuntoDeVentaBs');
        $totalTransferencias = $cierreDiario->sum('TransferenciaBs');
        $totalSistemaBs = $cierreDiario->sum('VentaSistema');
        $totalEgresosBs = $cierreDiario->sum('EgresoBs');
        $totalEgresosDivisa = $cierreDiario->sum('EgresoDivisas');

        $totalIngresoBs = $totalEfectivoBs
                    + $totalPagoMovil
                    + $totalPuntoVenta
                    + $totalTransferencias;

        $totalBs = $totalIngresoBs - $totalEgresosBs;
        $totalGeneralDivisa = $totalDivisa - $totalEgresosDivisa;
        $diferencia = $totalBs - $totalSistemaBs;

        // dd($cierreDiario);

        // Pasar todo a la vista
        return view('cpanel.cuadre.resumen_diario', [
            'cierreDiario' => $cierreDiario,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'sucursalId' => $sucursalId,
            'totalDivisa' => $totalDivisa,
            'totalEfectivoBs' => $totalEfectivoBs,
            'totalPagoMovil' => $totalPagoMovil,
            'totalPuntoVenta' => $totalPuntoVenta,
            'totalTransferencias' => $totalTransferencias,
            'totalSistemaBs' => $totalSistemaBs,
            'totalBs' => $totalBs,
            'totalGeneralDivisa' => $totalGeneralDivisa,
            'diferencia' => $diferencia,
        ]);
    }

    public function detalle(CierreDiario $cierreDiario)
    {
        // Cargar las relaciones necesarias
        // $cierreDiario->load(['sucursal', 'pagosPuntoDeVenta', 'divisaValor']);

        $cierreDiario->load([
            'sucursal',
            'divisaValor',
            'pagosPuntoDeVenta.puntoDeVenta',
            'pagosPuntoDeVenta.puntoDeVenta.banco',
        ]);

        // Realizar cálculos
        $totalPDV = $cierreDiario->pagosPuntoDeVenta->sum('Monto');
        $cierreDiario->PuntoDeVentaBs = number_format($totalPDV, 2, '.', '');

        // Valor de la divisa (si no tiene valor, asumimos 1)
        $divisaValor = $cierreDiario->divisaValor->Valor ?? 1;
        $cierreDiario->DivisaValor = number_format($divisaValor, 2, '.', '');

        // Conversión a divisa y formateo como string
        $cierreDiario->EfectivoBsaDivisa = $divisaValor > 0 ? number_format($cierreDiario->EfectivoBs / $divisaValor, 2, '.', '') : '0.00';
        $cierreDiario->PagoMovilBsaDivisa = $divisaValor > 0 ? number_format($cierreDiario->PagoMovilBs / $divisaValor, 2, '.', '') : '0.00';
        $cierreDiario->TransferenciaBsaDivisa = $divisaValor > 0 ? number_format($cierreDiario->TransferenciaBs / $divisaValor, 2, '.', '') : '0.00';
        $cierreDiario->PuntoDeVentaBsaDivisa = $divisaValor > 0 ? number_format($totalPDV / $divisaValor, 2, '.', '') : '0.00';

        // Agregar el nombre de la sucursal
        $cierreDiario->SucursalNombre = $cierreDiario->sucursal->Nombre ?? 'Sin Sucursal';

        $totalDivisa = $cierreDiario->EfectivoDivisas;
        $totalEfectivoBs = $cierreDiario->EfectivoBs;
        $totalPagoMovil = $cierreDiario->PagoMovilBs;
        $totalPuntoVenta = $cierreDiario->PuntoDeVentaBs;
        $totalTransferencias = $cierreDiario->TransferenciaBs;
        $totalEgresosBs = $cierreDiario->EgresoBs;
        $totalEgresosDivisa = $cierreDiario->EgresoDivisas;
        $totalSistemaBs = $cierreDiario->VentaSistema;

        $totalIngresoBs = $totalEfectivoBs
                    + $totalPagoMovil
                    + $totalPuntoVenta
                    + $totalTransferencias;

        $totalBs = $totalIngresoBs - $totalEgresosBs;
        $totalGeneralDivisa = $totalDivisa - $totalEgresosDivisa;
        $diferencia = $totalBs - $totalSistemaBs;

        // Puedes usar dd() para depurar si lo necesitas
        // dd($cierreDiario);

        // $pagos = $cierreDiario->pagosPuntoDeVenta;

        // dd($pagos);

        // Pasar todo a la vista
        return view('cpanel.cuadre.detalle', [
            'cierreDiario' => $cierreDiario,
            'totalDivisa' => $totalDivisa,
            'totalEfectivoBs' => $totalEfectivoBs,
            'totalPagoMovil' => $totalPagoMovil,
            'totalPuntoVenta' => $totalPuntoVenta,
            'totalTransferencias' => $totalTransferencias,
            'totalIngresoBs' => $totalIngresoBs,
            'totalBs' => $totalBs,
            'totalGeneralDivisa' => $totalGeneralDivisa,
            'diferencia' => $diferencia,
        ]);
    }
}
