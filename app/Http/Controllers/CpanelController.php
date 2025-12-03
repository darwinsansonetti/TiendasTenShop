<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;

class CpanelController extends Controller
{
    public function dashboard(Request $request)
    {       
        $user = Auth::user(); 

        // Obtener Tasa del Día desde Helpers
        $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());

        // Obtener lista de sucursales
        $listaSucursales = GeneralHelper::buscarSucursales(0);

        $sucursalId = session('sucursal_id', 0);
        $sucursalNombre = $sucursalId != 0 
            ? ($listaSucursales->firstWhere('ID', $sucursalId)->Nombre ?? "")
            : "Todas las sucursales";

        $productos = GeneralHelper::obtenerProductos(2);

        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio') 
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin') 
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,   // tipoFiltroFecha
            null,   // mesSeleccionado
            null,   // año
            false,  // añoAnterior
            $fechaInicio,
            $fechaFin
        );

        // Obtener ranking según el filtro de fechas
        $rankingSucursales = GeneralHelper::ObtenerRankingSucursales($filtroFecha);
        
        // Obtener las ventas diarias totalizadas de los 7 meses desde
        $graficaSucursalesMeses = GeneralHelper::ObtenerGraficaSucursales();

        // Filtro del mes (si la vista envía 'mes' y 'anio')
        $filtroMesAnio = [
            'mes' => $request->input('mes') ? intval($request->input('mes')) : now()->month,
            'anio' => $request->input('anio') ? intval($request->input('anio')) : now()->year
        ];

        // Obtener ranking de tiendas segun su produccion en dolares en un mes especifico
        $graficaProduccionMes = GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);

        // Obtener ranking de vendedores
        $rankingVendedor = GeneralHelper::ObtenerRankingVendedores($filtroFecha);

        //dd($rankingVendedor);

        session(['sucursal_nombre' => $sucursalNombre]);

        if (!$tasa || !$tasa['DivisaValor']) {
            session()->flash('warning', 'No se ha registrado la tasa del día. Por favor, actualícela.');
        }

        return view('cpanel.dashboard', compact(
            'user', 
            'tasa', 
            'listaSucursales', 
            'productos',
            'rankingSucursales',
            'graficaSucursalesMeses',
            'graficaProduccionMes',
            'rankingVendedor'
        ));
    }

    // Metodo para obtener Ranking de sucursales por rango de fechas
    public function obtenerRankingSucursales(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            if (!$fechaInicio || !$fechaFin) {
                return response()->json(['error' => 'Fechas no válidas'], 400);
            }

            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            );

            $rankingSucursales = GeneralHelper::ObtenerRankingSucursales($filtroFecha);

            $html = view('cpanel.partials.ranking_sucursales', compact('rankingSucursales'))->render();

            return response()->json(['html' => $html]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // Obtener datos de gráfica Producción Mensual vía AJAX 
    public function obtenerProduccionMensual(Request $request)
    {
        try {
            // Recibimos monthYear = "2025-11"
            $monthYear = $request->input('monthYear');

            if (!$monthYear || !preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
                return response()->json(['error' => 'Parámetro monthYear inválido'], 400);
            }

            // Dividir año y mes
            [$anio, $mes] = explode('-', $monthYear);

            $filtroMesAnio = [
                'mes'  => intval($mes),
                'anio' => intval($anio)
            ];

            // Llamar al helper que ya tienes
            $graficaProduccionMes = GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);

            $categorias = $graficaProduccionMes->pluck('sucursal');
            $valores    = $graficaProduccionMes->pluck('produccion');

            return response()->json([
                'categorias' => $categorias,
                'valores'    => $valores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener raning de vendedores
    public function obtenerRankingVendedores(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            if (!$fechaInicio || !$fechaFin) {
                return response()->json(['error' => 'Fechas inválidas'], 400);
            }

            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            );

            // Usar GeneralHelper para obtener ranking
            $rankingVendedores = GeneralHelper::ObtenerRankingVendedores($filtroFecha);

            // Solo los 3 primeros
            $rankingVendedores = $rankingVendedores->take(3);

            // Retornar el partial renderizado como HTML
            $html = view('cpanel.partials.ranking_vendedores', compact('rankingVendedores'))->render();

            return response()->json(['html' => $html]);

        } catch (\Throwable $e) {
            // Devuelve el mensaje completo para depuración
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}