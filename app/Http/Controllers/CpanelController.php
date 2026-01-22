<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CpanelController extends Controller
{
    // Acceso al Inicio o Dashboard
    public function dashboard(Request $request)
    {       
        $user = Auth::user(); 

        // Obtener Tasa del Día desde Helpers
        $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());

        // Obtener Tasa Paralelo
        $valorParalelo = DB::table('Paralelo')
            ->orderByDesc('id')
            ->first();

        $paralelo = $valorParalelo ? $valorParalelo->valor : 0;

        // // 🔹 Usar cache para la tasa diaria
        // $tasa = Cache::remember('tasa_diaria_' . now()->format('Y-m-d'), 3600, function() {
        //     return GeneralHelper::obtenerTasaCambioDiaria(now());
        // });

        // Obtener lista de sucursales
        $listaSucursales = GeneralHelper::buscarSucursales(1);

        // // 🔹 Cache para la lista de sucursales
        // $listaSucursales = Cache::remember('lista_sucursales', 3600, function() {
        //     return GeneralHelper::buscarSucursales(0);
        // });

        $sucursalId = session('sucursal_id', 0);
        $sucursalNombre = $sucursalId != 0 
            ? ($listaSucursales->firstWhere('ID', $sucursalId)->Nombre ?? "")
            : "Todas las sucursales";

        $productos = GeneralHelper::obtenerProductos(2);

        // // 🔹 Cache productos
        // $productos = Cache::remember('productos_2', 3600, function() {
        //     return GeneralHelper::obtenerProductos(2);
        // });

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

        // // 🔹 Ranking Sucursales cacheado por fechas
        // $rankingSucursalesKey = 'ranking_sucursales_' . md5($fechaInicio . '_' . $fechaFin);
        // $rankingSucursales = Cache::remember($rankingSucursalesKey, 10800, function() use ($filtroFecha) {
        //     return GeneralHelper::ObtenerRankingSucursales($filtroFecha);
        // });
        
        // Obtener las ventas diarias totalizadas de los 7 meses desde
        $graficaSucursalesMeses = GeneralHelper::ObtenerGraficaSucursales();

        // // 🔹 Gráfica Sucursales (últimos 7 meses) cacheada
        // $graficaSucursalesMeses = Cache::remember('grafica_sucursales_7meses', 10800, function() {
        //     return GeneralHelper::ObtenerGraficaSucursales();
        // });

        // Filtro del mes (si la vista envía 'mes' y 'anio')
        $filtroMesAnio = [
            'mes' => $request->input('mes') ? intval($request->input('mes')) : now()->month,
            'anio' => $request->input('anio') ? intval($request->input('anio')) : now()->year
        ];

        // Obtener ranking de tiendas segun su produccion en dolares en un mes especifico
        $graficaProduccionMes = GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);

        // // 🔹 Ranking de producción mensual cacheado
        // $graficaProduccionMesKey = 'produccion_mes_' . $filtroMesAnio['mes'] . '_' . $filtroMesAnio['anio'];
        // $graficaProduccionMes = Cache::remember($graficaProduccionMesKey, 10800, function() use ($filtroMesAnio) {
        //     return GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);
        // });

        // Obtener ranking de vendedores
        $rankingVendedor = GeneralHelper::ObtenerRankingVendedores($filtroFecha);

        // // 🔹 Ranking de vendedores cacheado por fechas
        // $rankingVendedorKey = 'ranking_vendedores_' . md5($fechaInicio . '_' . $fechaFin);
        // $rankingVendedor = Cache::remember($rankingVendedorKey, 10800, function() use ($filtroFecha) {
        //     return GeneralHelper::ObtenerRankingVendedores($filtroFecha);
        // });

        //dd($rankingVendedor);

        session(['sucursal_nombre' => $sucursalNombre]);
        session(['sucursal_id' => 0]);

        // Asignacion al menu
        session([
            'menu_active' => 'Inicio',
            'submenu_active' => null
        ]);

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
            'rankingVendedor',            
            'paralelo'
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

            // $rankingSucursales = GeneralHelper::ObtenerRankingSucursales($filtroFecha);

            // Clave única basada en ambas fechas
            $rankingSucursalesKey = 'ranking_sucursales_' . md5($fechaInicio . '_' . $fechaFin);

            // Cache por 1 hora (3600 seg)
            $rankingSucursales = Cache::remember($rankingSucursalesKey, 10800, function() use ($filtroFecha) {
                return GeneralHelper::ObtenerRankingSucursales($filtroFecha);
            });

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
            // $graficaProduccionMes = GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);

            // Creamos una llave única para el cache
            $produccionKey = 'produccion_sucursales_' . md5($mes . '_' . $anio);

            // Cacheamos por 6 horas (21600 seg)
            $graficaProduccionMes = Cache::remember($produccionKey, 21600, function () use ($filtroMesAnio) {
                return GeneralHelper::ObtenerProduccionSucursales($filtroMesAnio);
            });

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
            // $rankingVendedores = GeneralHelper::ObtenerRankingVendedores($filtroFecha);

            // Generamos una clave única para el cache, basada en las fechas
            $rankingVendedoresKey = 'ranking_vendedores_' . md5($fechaInicio . '_' . $fechaFin);

            // Cacheamos el resultado durante 1 hora (3600 segundos)
            $rankingVendedores = Cache::remember($rankingVendedoresKey, 3600, function() use ($filtroFecha) {
                return GeneralHelper::ObtenerRankingVendedores($filtroFecha);
            });

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

    // Resumen de ventas
    public function resumen_ventas(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // Generar datos de ventas cacheadas por fechas
        $ventasKey = 'ventas_resumen_' . md5($fechaInicio . '_' . $fechaFin);

        $ventas = GeneralHelper::generarDatosVentas($filtroFecha);
        // dd($ventas);
        
        // Cache::remember($ventasKey, 3600, function() use ($filtroFecha) {
        //     return GeneralHelper::generarDatosVentas($filtroFecha);
        // });

        $dtoVenta = new \App\DTO\VentasPeriodoDTO($ventas);

        // Ajusta valores globales si los obtienes en otra parte:
        $dtoVenta->UnidadesGlobalVendidas = $valorUnidadesGlobal ?? 0;
        $dtoVenta->MontoDivisasGlobal = $valorMontoDivisasGlobal ?? 0;
        $dtoVenta->MontoCostoGlobal = $valorMontoCostoGlobal ?? 0;

        // Para ver todo el contenido del DTO
        // dd($dtoVenta);
        // dd(
        //     $dtoVenta->getListaItemsTopTenPeriodo(),
        //     array_map(fn($i) => $i['Producto'], $dtoVenta->getListaItemsTopTenPeriodo())
        // );

        // // Total en divisas
        // $totalDivisa = $dtoVenta->listaVentasDiarias->sum('TotalDivisa');
        // dd($totalDivisa);

        // Top 10 Productos mas vendidos
        $topTen = $dtoVenta->getListaItemsTopTenPeriodo() ?? [];

        // Filtrar cualquier item cuyo Código sea "SALDO"
        $topTen = array_filter($topTen, function($item) {
            return ($item['Codigo'] ?? '') !== 'SALDO';
        });

        // Reindexar el array para evitar saltos en los índices
        $topTen = array_values($topTen);

        // dd($topTen);

        // Ventas por dia de la Semamna
        $ventasPorDiaSemana = collect($dtoVenta->listaVentasDiarias)
        ->groupBy(function($item) {
            // Carbon parse para obtener el nombre del día en español
            return \Carbon\Carbon::parse($item['Fecha'])->locale('es')->dayName;
        })
        ->map(function($items) {
            return $items->sum('TotalDivisa'); // suma total por día
        });        

        // Asignacion al menu
        session([
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Resumen de ventas'
        ]);

        return view('cpanel.resumen.resumen_ventas', [
            'ventas' => $dtoVenta,
            'topTen' => $topTen,
            'ventasCompleto' => $ventas,
            'ventasPorDiaSemana' => $ventasPorDiaSemana
        ]);
    }

    // Estados de cuentas
    public function estado_cuentas(Request $request)
    {       
        // 1️⃣ Leer mes y año del request (si vienen)
        $mes = $request->input('mes');   // Ej. 1-12
        $anio = $request->input('anio'); // Ej. 2025

        // $mes  = 10;   // noviembre
        // $anio = 2025;

        // 2️⃣ Si no vienen → usar mes y año actual
        if (!$mes || !$anio) {
            $mes = now('America/Caracas')->month;
            $anio = now('America/Caracas')->year;
        }

        // 3️⃣ Convertir el MES numérico al EnumMes
        //    TUS enums tienen valores 1..12 así que funciona perfecto.
        $mesEnum = \App\Enums\EnumMes::from((int)$mes);

        // 4️⃣ Generar el filtro EXACTO al estilo .NET
        $filtroFecha = new \App\Helpers\ParametrosFiltroFecha(
            null,           // tipoFiltroFecha
            $mesEnum,       // mesSeleccionado
            (int)$anio,     // año
            false,          // añoAnterior
            null,
            null
        );

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');

        // dd($sucursalId);

        // 6️⃣ Llamar al servicio igual que en .NET
        // $balanceSucursal = GeneralHelper::buscarValoresEstadoDeCuentaSucursal(
        //     $filtroFecha,
        //     $sucursalId
        // );

        // 6️⃣ Generar una clave de cache única basada en mes, año y sucursal
        $balanceSucursalKey = 'balance_sucursal_' . md5($mes . '_' . $anio . '_' . $sucursalId);

        // 7️⃣ Cachear el resultado por 1 hora (3600 segundos)
        $balanceSucursal = Cache::remember($balanceSucursalKey, 3600, function () use ($filtroFecha, $sucursalId) {
            return GeneralHelper::buscarValoresEstadoDeCuentaSucursal($filtroFecha, $sucursalId);
        });

        // 7️⃣ Agregar mes/año al resultado para la vista
        $balanceSucursal['Mes'] = $mes;
        $balanceSucursal['Anio'] = $anio;     

        // Asignacion al menu
        session([
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Estado de cuentas'
        ]);

        // dd($balanceSucursal);

        return view('cpanel.resumen.estado_cuentas', $balanceSucursal);
    }

    // Comparativa entre sucursales
    public function comparativa_sucursales(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // $comparacion = GeneralHelper::ObtenerComparacionSucursales($filtroFecha);

        // Generamos una clave única para el cache, basada en las fechas
        $comparacionKey = 'comparacion_sucursales_' . md5($fechaInicio . '_' . $fechaFin);

        // Cacheamos el resultado durante 1 hora (3600 segundos)
        $comparacion = Cache::remember($comparacionKey, 3600, function () use ($filtroFecha) {
            return GeneralHelper::ObtenerComparacionSucursales($filtroFecha);
        });

        // Asignacion al menu
        session([
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Comparativa'
        ]);

        return view('cpanel.resumen.comparativa_sucursales', [
            'fechaInicio' => $comparacion->fechaInicio,
            'fechaFin'    => $comparacion->fechaFin,
            'detalles'    => $comparacion->detalles,
        ]);
    }

    // Indice de Rotacion
    public function indice_rotacion(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');

        // dd($sucursalId);

        $indices = GeneralHelper::ObtenerIndiceRotacion($filtroFecha, $sucursalId);

        // // 🔑 Crear clave única para el cache:
        // $cacheKey = 'indice_rotacion_' 
        //     . ($fechaInicio ? $fechaInicio->format('Ymd') : 'null') . '_' 
        //     . ($fechaFin ? $fechaFin->format('Ymd') : 'null');

        // // 🧠 Cache por 5 minutos (ajusta si quieres)
        // $indices = Cache::remember($cacheKey, 3600, function () use ($filtroFecha, $sucursalId) {
        //     return GeneralHelper::ObtenerIndiceRotacion($filtroFecha, $sucursalId);
        // });

        // dd($indices);
        // dd($indices->detalles);

        session([
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Indice de Rotación'
        ]);

        return view('cpanel.resumen.indice_rotacion', compact('indices'));
    }

    // Productos poco vendidos
    public function baja_demanda(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

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
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Baja Demanda'
        ]);

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');

        $indices = GeneralHelper::ObtenerSinVentaSucursales($filtroFecha, $sucursalId);

        // dd($indices->detalles->first());

        return view('cpanel.resumen.baja_demanda', compact('indices'));
    } 
}