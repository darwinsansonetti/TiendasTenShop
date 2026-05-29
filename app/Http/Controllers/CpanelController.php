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

use App\Models\DivisaValor;

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
        // // 🚀 Aquí: usar fechas del request si existen
        // $fechaInicio = $request->input('fecha_inicio')
        //     ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
        //     : null;

        // $fechaFin = $request->input('fecha_fin')
        //     ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
        //     : null;

        // Obtener fecha fin (puede venir del request o usar fecha actual)
        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : now()->endOfDay();
        
        // Calcular fecha inicio = 30 días hacia atrás de fecha fin
        $fechaInicio = (clone $fechaFin)->subDays(30)->startOfDay();

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

        // Obtener sucursal activa
        $sucursalId = session('sucursal_id');
        
        // // ✅ Verificar si ya se ejecutó la automatización en los últimos 90 días
        // $diasGracia = 30;
        // $fechaLimite = now()->subDays($diasGracia);
        $fechaUltimaEjecucion = null;
        $ejecucionReciente = false;
        $ultimoReporte = null; 

        if ($sucursalId && $sucursalId != 0) {
            $ultimoCambio = DB::table('ProductoSucursal')
                ->where('SucursalId', $sucursalId)
                // ->where('FechaBajaPrecio', '>', $fechaLimite)
                ->where('FechaBajaPrecio', '>', $fechaInicio)
                ->max('FechaBajaPrecio');
            
            if ($ultimoCambio) {
                $ejecucionReciente = true;
                $fechaUltimaEjecucion = Carbon::parse($ultimoCambio)->format('d/m/Y H:i:s');
            }

            $ultimoReporteDB = DB::table('automatizacion_reportes')
                ->where('sucursal_id', $sucursalId)
                ->orderBy('fecha_ejecucion', 'desc')
                ->first();
            
            if ($ultimoReporteDB) {
                // Decodificar los datos JSON
                $datos = json_decode($ultimoReporteDB->datos, true);
                
                // Construir objeto completo para el frontend
                $ultimoReporte = [
                    'sucursal_nombre' => $ultimoReporteDB->sucursal_nombre,
                    'total_analizados' => $ultimoReporteDB->total_analizados,
                    'productos_afectados' => $ultimoReporteDB->productos_afectados,
                    'productos_mantenidos' => $ultimoReporteDB->productos_mantenidos,
                    'productos_saltados_reproceso' => $ultimoReporteDB->productos_saltados,
                    'dias_gracia' => $datos['dias_gracia'] ?? 30,
                    'categorias' => $datos['categorias'] ?? [],
                    'detalles' => $datos['detalles'] ?? [],
                    'detalles_mantenidos' => $datos['detalles_mantenidos'] ?? [],
                    'detalles_saltados' => $datos['detalles_saltados'] ?? []
                ];
            }
        }

        // Obtener productos
        $indices = GeneralHelper::ObtenerAutomaticamenteProductosBajaDemanda($filtroFecha, $sucursalId);

        return view('cpanel.resumen.baja_demanda', compact('indices', 'ejecucionReciente', 'fechaUltimaEjecucion', 'ultimoReporte'));
    } 

    public function ejecutarAutomatizacion(Request $request)
    {
        try {
            \Log::info('========== INICIO AUTOMATIZACIÓN ==========', [
                'fecha_ejecucion' => now()->toDateTimeString()
            ]);

            $tasaBcv = DivisaValor::orderBy('ID', 'desc')->first();
            $tasaValor = $tasaBcv->Valor;

            $tasaParalelo = DB::table('Paralelo')->orderByDesc('id')->first();
            $paralelo = $tasaParalelo->valor;

            set_time_limit(600);
            
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'sucursal_id' => 'required|integer|in:3,4,5,7'
            ]);

            $fechaFin = $request->input('fecha_fin')
                ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
                : now()->endOfDay();
            
            $fechaInicio = (clone $fechaFin)->subDays(30)->startOfDay();

            \Log::info('Parámetros de ejecución', [
                'fecha_fin' => $fechaFin->toDateTimeString(),
                'fecha_inicio' => $fechaInicio->toDateTimeString(),
                'sucursal_id' => $request->input('sucursal_id')
            ]);

            $diasGracia = 30;

            $filtroFecha = new ParametrosFiltroFecha(
                null,
                null,
                null,
                false,
                $fechaInicio,
                $fechaFin
            );

            $sucursalId = $request->input('sucursal_id');
            
            $nombreSucursal = DB::table('Sucursales')
                ->where('Id', $sucursalId)
                ->value('Nombre') ?? 'Sucursal ' . $sucursalId;

            $bajaDemanda = GeneralHelper::ObtenerAutomaticamenteProductosBajaDemanda($filtroFecha, $sucursalId);
            
            $totalProductos = $bajaDemanda->detalles->count();
            
            \Log::info('Productos obtenidos desde helper', [
                'total_productos' => $totalProductos,
                'sucursal' => $nombreSucursal
            ]);
            
            if ($totalProductos == 0) {
                return response()->json([
                    'success' => true,
                    'mensaje' => "No hay productos con baja demanda en {$nombreSucursal}",
                    'total_analizados' => 0,
                    'productos_afectados' => 0
                ]);
            }
            
            // Precargar datos para optimizar
            $productosIds = $bajaDemanda->detalles->pluck('producto_id')->toArray();
            
            $fechasBajaPrecio = DB::table('ProductoSucursal')
                ->where('SucursalId', $sucursalId)
                ->whereIn('ProductoId', $productosIds)
                ->select('ProductoId', 'FechaBajaPrecio')
                ->get()
                ->keyBy('ProductoId');
            
            $costos = DB::table('Productos')
                ->whereIn('ID', $productosIds)
                ->select('ID', 'CostoDivisa')
                ->get()
                ->keyBy('ID');
            
            $loteSize = 50;
            $totalLotes = ceil($totalProductos / $loteSize);
            
            $productosProcesados = [];
            $productosConError = [];
            $productosPrecioMantenido = [];
            $productosSaltadosPorReproceso = [];
            
            // Contadores solo para productos ACTUALIZADOS
            $contadorCategorias = [
                'rotacionLenta' => 0,
                'riesgoEstancamiento' => 0,
                'mercanciaCritica' => 0,
                'remateTotal' => 0,
                'superRemateTotal' => 0,
                'nuevaColeccion' => 0,
                'preciosMantenidos' => 0,
                'saltadosReproceso' => 0
            ];
            
            $productosArray = $bajaDemanda->detalles->values();
            
            for ($lote = 0; $lote < $totalLotes; $lote++) {
                $inicio = $lote * $loteSize;
                $loteProductos = $productosArray->slice($inicio, $loteSize);
                
                \Log::info('=== PROCESANDO LOTE ' . ($lote + 1) . ' ===', [
                    'cantidad_productos' => $loteProductos->count()
                ]);
                
                foreach ($loteProductos as $index => $item) {
                    $productoId = $item->producto_id;
                    $codigo = $item->producto['codigo'];
                    $descripcion = $item->producto['descripcion'];
                    $existencia = $item->producto['existencia'];
                    
                    $costoDivisa = $costos[$productoId]->CostoDivisa ?? 0;
                    $pvpActual = $item->producto['pvp_divisa'] ?? 0;
                    
                    // ============================================
                    // LOG 1: DATOS BÁSICOS DEL PRODUCTO
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('========== PRODUCTO ESPECÍFICO LA1603 ==========', [
                            'codigo' => $codigo,
                            'productoId' => $productoId,
                            'costoDivisa' => $costoDivisa,
                            'pvpActual' => $pvpActual,
                            'existencia' => $existencia
                        ]);
                    }
                    
                    // Verificar reproceso
                    $fechaUltimoCambio = null;
                    if (isset($fechasBajaPrecio[$productoId])) {
                        $fechaUltimoCambio = $fechasBajaPrecio[$productoId]->FechaBajaPrecio;
                    }
                    
                    if ($fechaUltimoCambio && Carbon::parse($fechaUltimoCambio) > $fechaInicio) {
                        $contadorCategorias['saltadosReproceso']++;
                        $productosSaltadosPorReproceso[] = [
                            'id' => $productoId,
                            'codigo' => $codigo,
                            'descripcion' => $descripcion,
                            'fecha_ultimo_cambio' => Carbon::parse($fechaUltimoCambio)->format('Y-m-d H:i:s')
                        ];
                        continue;
                    }
                    
                    // Calcular antigüedad
                    $fechaCreacionOriginal = $item->producto['fecha_creacion'];
                    $fechaActualizacion = $item->producto['fecha_actualizacion'] ?? null;

                    $fechaReferencia = $fechaCreacionOriginal;
                    if ($fechaActualizacion && $fechaActualizacion > $fechaCreacionOriginal) {
                        $fechaReferencia = $fechaActualizacion;
                    }

                    $mesesAntiguedad = now()->diffInMonths($fechaReferencia);
                    
                    // ============================================
                    // LOG 2: CÁLCULO DE ANTIGÜEDAD
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('Antigüedad LA1603', [
                            'fechaCreacionOriginal' => $fechaCreacionOriginal,
                            'fechaActualizacion' => $fechaActualizacion,
                            'fechaReferencia' => $fechaReferencia,
                            'mesesAntiguedad' => $mesesAntiguedad,
                            'now' => now()->toDateTimeString()
                        ]);
                    }
                    
                    // Determinar porcentaje y categoría
                    $porcentajeDescuento = 0;
                    $categoria = '';
                    $porcentajeAplica = null;
                    
                    if ($mesesAntiguedad >= 2 && $mesesAntiguedad < 5) {
                        $porcentajeAplica = 100;
                        $categoria = 'Rotacion Lenta';
                    } elseif ($mesesAntiguedad >= 5 && $mesesAntiguedad < 8) {
                        $porcentajeAplica = 75;
                        $categoria = 'Riesgo Estancamiento';
                    } elseif ($mesesAntiguedad >= 8 && $mesesAntiguedad < 12) {
                        $porcentajeAplica = 0;
                        $categoria = 'Mercancia Critica';
                    } elseif ($mesesAntiguedad >= 12 && $mesesAntiguedad < 19) {
                        $porcentajeAplica = -30;
                        $categoria = 'Remate Total';
                    } elseif ($mesesAntiguedad >= 19) {
                        $porcentajeAplica = -60;
                        $categoria = 'Super Remate Total';
                    } else {
                        $contadorCategorias['nuevaColeccion']++;
                        continue;
                    }
                    
                    // ============================================
                    // LOG 3: PORCENTAJE Y CATEGORÍA ASIGNADA
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('Categoría asignada LA1603', [
                            'categoria' => $categoria,
                            'porcentajeAplica' => $porcentajeAplica,
                            'rango_meses' => $mesesAntiguedad >= 12 && $mesesAntiguedad < 19 ? '12-19' : 'otro'
                        ]);
                    }
                    
                    $gananciaActual = $pvpActual - $costoDivisa;
                    
                    // ============================================
                    // LOG 4: GANANCIA ACTUAL
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('Ganancia LA1603', [
                            'gananciaActual' => $gananciaActual,
                            'gananciaActual_menor_igual_0' => ($gananciaActual <= 0),
                            'pvpActual_mayor_0' => ($pvpActual > 0),
                            'porcentajeAplica_mayor_igual_0' => ($porcentajeAplica >= 0),
                            'condicion_mantenidos' => ($gananciaActual <= 0 && $pvpActual > 0 && $porcentajeAplica >= 0)
                        ]);
                    }
                    
                    // REGLA: Productos con pérdida (solo se mantienen si NO es remate)
                    if ($gananciaActual <= 0 && $pvpActual > 0 && $porcentajeAplica >= 0) {
                        $contadorCategorias['preciosMantenidos']++;
                        $productosPrecioMantenido[] = [
                            'id' => $productoId,
                            'codigo' => $codigo,
                            'descripcion' => $descripcion,
                            'costo' => round($costoDivisa, 2),
                            'pvp_actual' => round($pvpActual, 2),
                            'ganancia' => round($gananciaActual, 2),
                            'categoria' => $categoria,
                            'razon' => 'Producto en perdida o sin ganancia'
                        ];
                        
                        // ============================================
                        // LOG 5: PRODUCTO MANTENIDO
                        // ============================================
                        if ($codigo == 'LA1603') {
                            \Log::warning('LA1603 fue MANTENIDO (no se actualizará)', [
                                'categoria' => $categoria,
                                'porcentajeAplica' => $porcentajeAplica,
                                'gananciaActual' => $gananciaActual
                            ]);
                        }
                        
                        continue;
                    }
                    
                    // Calcular nuevo precio
                    $nuevoPvp = $costoDivisa * (1 + $porcentajeAplica / 100);

                    if ($pvpActual > 0) {
                        $porcentajeDescuento = (($pvpActual - $nuevoPvp) / $pvpActual) * 100;
                        $porcentajeDescuento = round($porcentajeDescuento, 2);
                    } else {
                        $porcentajeDescuento = 999;
                    }

                    $nuevoPvp = round($nuevoPvp, 2);
                    
                    // ============================================
                    // LOG 6: NUEVO PRECIO CALCULADO
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('Nuevo precio calculado LA1603', [
                            'nuevoPvp' => $nuevoPvp,
                            'porcentajeDescuento' => $porcentajeDescuento,
                            'condicion_mantener' => ($pvpActual > 0 && $porcentajeAplica >= 0 && $pvpActual <= $nuevoPvp)
                        ]);
                    }
                    
                    // Validar si mantener precio (cuando sube)
                    if ($pvpActual > 0 && $porcentajeAplica >= 0) {
                        if ($pvpActual <= $nuevoPvp) {
                            $contadorCategorias['preciosMantenidos']++;
                            $productosPrecioMantenido[] = [
                                'id' => $productoId,
                                'codigo' => $codigo,
                                'descripcion' => $descripcion,
                                'costo' => round($costoDivisa, 2),
                                'pvp_actual' => round($pvpActual, 2),
                                'ganancia' => round($gananciaActual, 2),
                                'categoria' => $categoria,
                                'razon' => 'Precio actual menor o igual al propuesto'
                            ];
                            continue;
                        }
                    }
                    
                    // ============================================
                    // LOG 7: PRODUCTO A ACTUALIZAR
                    // ============================================
                    if ($codigo == 'LA1603') {
                        \Log::info('LA1603 será ACTUALIZADO', [
                            'pvp_actual' => $pvpActual,
                            'nuevo_pvp' => $nuevoPvp,
                            'categoria' => $categoria,
                            'porcentajeAplica' => $porcentajeAplica
                        ]);
                    }
                    
                    // ACTUALIZAR PRODUCTO
                    try {
                        $existe = DB::table('ProductoSucursal')
                            ->where('ProductoId', $productoId)
                            ->where('SucursalId', $sucursalId)
                            ->exists();
                        
                        if (!$existe) {
                            continue;
                        }
                        
                        $actualizado = DB::table('ProductoSucursal')
                            ->where('ProductoId', $productoId)
                            ->where('SucursalId', $sucursalId)
                            ->update([
                                'PvpAnterior' => $pvpActual,
                                'NuevoPvp' => $nuevoPvp,
                                'PvpDivisa' => $nuevoPvp,
                                'FechaNuevoPrecio' => now(),
                                'FechaBajaPrecio' => now()
                            ]);

                        if ($actualizado) {
                            switch ($categoria) {
                                case 'Rotacion Lenta':
                                    $contadorCategorias['rotacionLenta']++;
                                    break;
                                case 'Riesgo Estancamiento':
                                    $contadorCategorias['riesgoEstancamiento']++;
                                    break;
                                case 'Mercancia Critica':
                                    $contadorCategorias['mercanciaCritica']++;
                                    break;
                                case 'Remate Total':
                                    $contadorCategorias['remateTotal']++;
                                    break;
                                case 'Super Remate Total':
                                    $contadorCategorias['superRemateTotal']++;
                                    break;
                            }
                            
                            $precioAnteriorConvertido = 0;
                            $nuevoPrecioConvertido = 0;
                            
                            if ($paralelo > 0 && $tasaValor > 0) {
                                if ($pvpActual > 0) {
                                    $precioAnteriorConvertido = round(($pvpActual * $paralelo) / $tasaValor, 2);
                                }
                                $nuevoPrecioConvertido = round(($nuevoPvp * $paralelo) / $tasaValor, 2);
                                
                                // LOG para verificar
                                \Log::info('Conversión aplicada', [
                                    'codigo' => $codigo,
                                    'nuevoPvp_usd' => $nuevoPvp,
                                    'paralelo' => $paralelo,
                                    'tasaValor' => $tasaValor,
                                    'nuevoPrecioConvertido' => $nuevoPrecioConvertido
                                ]);
                            } else {
                                $precioAnteriorConvertido = round($pvpActual, 2);
                                $nuevoPrecioConvertido = round($nuevoPvp, 2);
                                
                                // LOG para verificar
                                \Log::info('Sin conversión (usando USD)', [
                                    'codigo' => $codigo,
                                    'nuevoPvp_usd' => $nuevoPvp,
                                    'nuevoPrecioConvertido' => $nuevoPrecioConvertido,
                                    'paralelo' => $paralelo,
                                    'tasaValor' => $tasaValor
                                ]);
                            }

                            $productosProcesados[] = [
                                'id' => $productoId,
                                'codigo' => $codigo,
                                'descripcion' => $descripcion,
                                'categoria' => $categoria,
                                'antiguedad_meses' => round($mesesAntiguedad, 1),
                                'precio_anterior' => $precioAnteriorConvertido,
                                'nuevo_precio' => $nuevoPrecioConvertido,
                                'porcentaje_descuento' => $porcentajeDescuento,
                                'costo' => round($costoDivisa, 2),
                                'existencia' => $existencia,
                                'reduccion' => round($precioAnteriorConvertido - $nuevoPrecioConvertido, 2),
                                'tasa_bcv' => $tasaValor,
                                'tasa_paralelo' => $paralelo
                            ];
                        }
                        
                    } catch (\Exception $e) {
                        \Log::error('Error al actualizar producto', [
                            'codigo' => $codigo,
                            'error' => $e->getMessage()
                        ]);
                        
                        $productosConError[] = [
                            'id' => $productoId,
                            'error' => $e->getMessage()
                        ];
                    }
                }
                
                unset($loteProductos);
                gc_collect_cycles();
            }

            // Calcular total de actualizados
            $totalActualizados = $contadorCategorias['rotacionLenta'] 
                + $contadorCategorias['riesgoEstancamiento']
                + $contadorCategorias['mercanciaCritica']
                + $contadorCategorias['remateTotal']
                + $contadorCategorias['superRemateTotal'];

            \Log::info('========== RESUMEN FINAL AUTOMATIZACIÓN ==========', [
                'total_analizados' => $totalProductos,
                'productos_actualizados' => $totalActualizados,
                'productos_mantenidos' => $contadorCategorias['preciosMantenidos'],
                'productos_saltados_reproceso' => $contadorCategorias['saltadosReproceso'],
                'desglose_categorias' => [
                    'rotacionLenta' => $contadorCategorias['rotacionLenta'],
                    'riesgoEstancamiento' => $contadorCategorias['riesgoEstancamiento'],
                    'mercanciaCritica' => $contadorCategorias['mercanciaCritica'],
                    'remateTotal' => $contadorCategorias['remateTotal'],
                    'superRemateTotal' => $contadorCategorias['superRemateTotal']
                ]
            ]);

            // Guardar reporte
            $reporteData = [
                'sucursal_id' => $sucursalId,
                'sucursal_nombre' => $nombreSucursal,
                'fecha_ejecucion' => now(),
                'total_analizados' => $totalProductos,
                'productos_afectados' => $totalActualizados,
                'productos_mantenidos' => $contadorCategorias['preciosMantenidos'],
                'productos_saltados' => $contadorCategorias['saltadosReproceso'],
                'datos' => json_encode([
                    'categorias' => $contadorCategorias,
                    'detalles' => $productosProcesados,
                    'detalles_mantenidos' => $productosPrecioMantenido,
                    'detalles_saltados' => $productosSaltadosPorReproceso,
                    'dias_gracia' => $diasGracia
                ]),
                'created_at' => now(),
            ];
            
            DB::table('automatizacion_reportes')->insert($reporteData);
            
            return response()->json([
                'success' => true,
                'mensaje' => "Se actualizaron " . $totalActualizados . " productos en {$nombreSucursal}",
                'sucursal_id' => $sucursalId,
                'sucursal_nombre' => $nombreSucursal,
                'total_analizados' => $totalProductos,
                'productos_afectados' => $totalActualizados,
                'productos_mantenidos' => $contadorCategorias['preciosMantenidos'],
                'productos_saltados_reproceso' => $contadorCategorias['saltadosReproceso'],
                'dias_gracia' => $diasGracia,
                'categorias' => $contadorCategorias,
                'detalles' => $productosProcesados,
                'detalles_mantenidos' => $productosPrecioMantenido,
                'detalles_saltados' => $productosSaltadosPorReproceso,
                'errores' => $productosConError,
                'reporte_guardado' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('ERROR GENERAL EN AUTOMATIZACIÓN', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Alta Demanda
    public function alta_demanda(Request $request)
    {       
        // Obtener fecha fin
        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : now()->endOfDay();
        
        // Calcular fecha inicio = 30 días hacia atrás
        $fechaInicio = (clone $fechaFin)->subDays(30)->startOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false, $fechaInicio, $fechaFin
        );

        $sucursalId = session('sucursal_id');
        
        // ✅ Verificar si ya se ejecutó la automatización de ALTA DEMANDA en los últimos 30 días
        $diasGracia = 30;
        $fechaLimite = now()->subDays($diasGracia);
        $fechaUltimaEjecucion = null;
        $ejecucionReciente = false;
        $ultimoReporte = null; 

        if ($sucursalId && $sucursalId != 0) {
            // Buscar última subida de precio
            $ultimoCambio = DB::table('ProductoSucursal')
                ->where('SucursalId', $sucursalId)
                ->where('FechaSubePrecio', '>', $fechaLimite)
                ->max('FechaSubePrecio');
            
            if ($ultimoCambio) {
                $ejecucionReciente = true;
                $fechaUltimaEjecucion = Carbon::parse($ultimoCambio)->format('d/m/Y H:i:s');
            }

            // Obtener último reporte de automatización de alta demanda
            $ultimoReporteDB = DB::table('automatizacion_reportes_alta')
                ->where('sucursal_id', $sucursalId)
                ->orderBy('fecha_ejecucion', 'desc')
                ->first();
            
            if ($ultimoReporteDB) {
                $datos = json_decode($ultimoReporteDB->datos, true);
                
                $ultimoReporte = [
                    'sucursal_nombre' => $ultimoReporteDB->sucursal_nombre,
                    'total_analizados' => $ultimoReporteDB->total_analizados,
                    'productos_afectados' => $ultimoReporteDB->productos_afectados,
                    'productos_mantenidos' => $ultimoReporteDB->productos_mantenidos,
                    'productos_saltados_reproceso' => $ultimoReporteDB->productos_saltados,
                    'dias_gracia' => $datos['dias_gracia'] ?? 30,
                    'categorias' => $datos['categorias'] ?? [],
                    'detalles' => $datos['detalles'] ?? [],
                    'detalles_mantenidos' => $datos['detalles_mantenidos'] ?? [],
                    'detalles_saltados' => $datos['detalles_saltados'] ?? []
                ];
            }
        }

        $productosAltaDemanda = GeneralHelper::ObtenerProductosAltaDemanda($filtroFecha, $sucursalId, false);

        session([
            'menu_active' => 'Informes - Resumen',
            'submenu_active' => 'Alta Demanda'
        ]);

        return view('cpanel.resumen.alta_demanda', compact('productosAltaDemanda', 'ejecucionReciente', 'fechaUltimaEjecucion', 'ultimoReporte'));
    }

    public function ejecutarSubidaPrecios(Request $request)
    {
        try {
            // Obtener tasas
            $tasaBcv = DivisaValor::orderBy('ID', 'desc')->first();
            $tasaValor = $tasaBcv->Valor;
            $tasaParalelo = DB::table('Paralelo')->orderByDesc('id')->first();
            $paralelo = $tasaParalelo->valor;

            // Aumentar tiempo de ejecución
            set_time_limit(600);
            
            // Validar datos
            $request->validate([
                'sucursal_id' => 'required|integer|in:3,4,5,7',
                'fecha_fin' => 'required|date'
            ]);

            $sucursalId = $request->input('sucursal_id');
            
            // Obtener nombre de la sucursal
            $nombreSucursal = DB::table('Sucursales')
                ->where('Id', $sucursalId)
                ->value('Nombre') ?? 'Sucursal ' . $sucursalId;

            // Obtener fecha fin
            $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
            $fechaInicio = (clone $fechaFin)->subDays(30)->startOfDay();

            // Días de gracia para no reprocesar (30 días)
            $diasGracia = 30;
            $fechaLimiteReproceso = now()->subDays($diasGracia);

            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false, $fechaInicio, $fechaFin
            );

            // Obtener productos con alta demanda
            $productosAltaDemanda = GeneralHelper::ObtenerProductosAltaDemanda($filtroFecha, $sucursalId, true);
            
            $totalProductos = $productosAltaDemanda->count();
            
            if ($totalProductos == 0) {
                return response()->json([
                    'success' => true,
                    'mensaje' => "No hay productos con alta demanda en {$nombreSucursal}",
                    'total_analizados' => 0,
                    'productos_afectados' => 0
                ]);
            }
            
            $productosProcesados = [];
            $productosConError = [];
            $productosPrecioMantenido = [];
            $productosSaltadosPorReproceso = [];
            
            $contadorCategorias = [
                'altaDemanda' => 0,      // 5 estrellas
                'buenaDemanda' => 0,     // 4 estrellas
                'demandaMedia' => 0,     // 3 estrellas
                'preciosMantenidos' => 0,
                'saltadosReproceso' => 0
            ];
            
            foreach ($productosAltaDemanda as $item) {
                $productoId = $item['id'];
                $codigo = $item['codigo'];
                $descripcion = $item['descripcion'];
                $existencia = $item['existencia'];
                $costoDivisa = $item['costo'];
                $pvpActual = $item['pvp_actual'];
                $porcentajeSubida = $item['porcentaje_subida'];
                $categoria = $item['categoria'];
                $estrellas = $item['estrellas'];
                
                // ✅ Verificar si ya fue procesado recientemente (FechaSubePrecio)
                $fechaUltimoCambio = DB::table('ProductoSucursal')
                    ->where('ProductoId', $productoId)
                    ->where('SucursalId', $sucursalId)
                    ->value('FechaSubePrecio');
                
                if ($fechaUltimoCambio && Carbon::parse($fechaUltimoCambio) > $fechaLimiteReproceso) {
                    $contadorCategorias['saltadosReproceso']++;
                    $productosSaltadosPorReproceso[] = [
                        'id' => $productoId,
                        'codigo' => $codigo,
                        'descripcion' => $descripcion,
                        'fecha_ultimo_cambio' => Carbon::parse($fechaUltimoCambio)->format('Y-m-d H:i:s')
                    ];
                    continue;
                }
                
                // ✅ Calcular utilidad actual (no subir si está en pérdida)
                $utilidadActual = $pvpActual - $costoDivisa;
                if ($utilidadActual <= 0) {
                    $contadorCategorias['preciosMantenidos']++;
                    $productosPrecioMantenido[] = [
                        'id' => $productoId,
                        'codigo' => $codigo,
                        'descripcion' => $descripcion,
                        'costo' => round($costoDivisa, 2),
                        'pvp_actual' => round($pvpActual, 2),
                        'ganancia' => round($utilidadActual, 2),
                        'razon' => 'Producto en pérdida o sin ganancia'
                    ];
                    continue;
                }
                
                // ✅ Calcular nuevo precio
                $nuevoPvp = $pvpActual * (1 + $porcentajeSubida / 100);
                $nuevoPvp = round($nuevoPvp, 2);
                
                // Contar por categoría
                if ($estrellas >= 5) {
                    $contadorCategorias['altaDemanda']++;
                } elseif ($estrellas >= 4) {
                    $contadorCategorias['buenaDemanda']++;
                } else {
                    $contadorCategorias['demandaMedia']++;
                }
                
                try {
                    $existe = DB::table('ProductoSucursal')
                        ->where('ProductoId', $productoId)
                        ->where('SucursalId', $sucursalId)
                        ->exists();
                    
                    if (!$existe) {
                        continue;
                    }
                    
                    // ✅ Actualizar precio (SUBIR)
                    $actualizado = DB::table('ProductoSucursal')
                        ->where('ProductoId', $productoId)
                        ->where('SucursalId', $sucursalId)
                        ->update([
                            'PvpAnterior' => $pvpActual,
                            'NuevoPvp' => $nuevoPvp,
                            'PvpDivisa' => $nuevoPvp,
                            'FechaNuevoPrecio' => now(),
                            'FechaSubePrecio' => now()
                        ]);
                    
                    if ($actualizado) {
                        // ✅ Convertir precios con tasas
                        $precioAnteriorConvertido = 0;
                        $nuevoPrecioConvertido = 0;
                        
                        if ($paralelo > 0 && $tasaValor > 0) {
                            $precioAnteriorConvertido = round(($pvpActual * $paralelo) / $tasaValor, 2);
                            $nuevoPrecioConvertido = round(($nuevoPvp * $paralelo) / $tasaValor, 2);
                        } else {
                            $precioAnteriorConvertido = round($pvpActual, 2);
                            $nuevoPrecioConvertido = round($nuevoPvp, 2);
                        }
                        
                        $productosProcesados[] = [
                            'id' => $productoId,
                            'codigo' => $codigo,
                            'descripcion' => $descripcion,
                            'categoria' => $categoria,
                            'precio_anterior' => $precioAnteriorConvertido,
                            'nuevo_precio' => $nuevoPrecioConvertido,
                            'porcentaje_subida' => $porcentajeSubida,
                            'costo' => round($costoDivisa, 2),
                            'existencia' => $existencia,
                            'estrellas' => $estrellas,
                            'tasa_bcv' => $tasaValor,
                            'tasa_paralelo' => $paralelo
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $productosConError[] = [
                        'id' => $productoId,
                        'error' => $e->getMessage()
                    ];
                    \Log::error("[ERROR Subida] Producto {$productoId}: " . $e->getMessage());
                }
            }
            
            // ============================================
            // GUARDAR REPORTE EN LA BASE DE DATOS
            // ============================================
            $reporteData = [
                'sucursal_id' => $sucursalId,
                'sucursal_nombre' => $nombreSucursal,
                'fecha_ejecucion' => now(),
                'total_analizados' => $totalProductos,
                'productos_afectados' => count($productosProcesados),
                'productos_mantenidos' => $contadorCategorias['preciosMantenidos'],
                'productos_saltados' => $contadorCategorias['saltadosReproceso'],
                'datos' => json_encode([
                    'categorias' => $contadorCategorias,
                    'detalles' => $productosProcesados,
                    'detalles_mantenidos' => $productosPrecioMantenido,
                    'detalles_saltados' => $productosSaltadosPorReproceso,
                    'dias_gracia' => $diasGracia
                ]),
                'created_at' => now(),
            ];
            
            DB::table('automatizacion_reportes_alta')->insert($reporteData);
            
            return response()->json([
                'success' => true,
                'mensaje' => "Se actualizaron " . count($productosProcesados) . " productos en {$nombreSucursal}",
                'sucursal_id' => $sucursalId,
                'sucursal_nombre' => $nombreSucursal,
                'total_analizados' => $totalProductos,
                'productos_afectados' => count($productosProcesados),
                'productos_mantenidos' => $contadorCategorias['preciosMantenidos'],
                'productos_saltados_reproceso' => $contadorCategorias['saltadosReproceso'],
                'dias_gracia' => $diasGracia,
                'categorias' => $contadorCategorias,
                'detalles' => $productosProcesados,
                'detalles_mantenidos' => $productosPrecioMantenido,
                'detalles_saltados' => $productosSaltadosPorReproceso,
                'errores' => $productosConError
            ]);
            
        } catch (\Exception $e) {
            \Log::error('ERROR GENERAL en subida precios: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}