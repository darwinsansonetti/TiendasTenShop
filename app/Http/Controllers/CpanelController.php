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

        // $indices = GeneralHelper::ObtenerSinVentaSucursales($filtroFecha, $sucursalId);
        $indices = GeneralHelper::ObtenerAutomaticamenteProductosBajaDemanda($filtroFecha, $sucursalId);

        // dd($indices->detalles->first()); //SucursalNombre

        return view('cpanel.resumen.baja_demanda', compact('indices'));
    } 

    public function ejecutarAutomatizacion(Request $request)
    {
        try {
            // Aumentar tiempo de ejecución
            set_time_limit(600);
            
            // Validar datos
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'sucursal_id' => 'required|integer|in:3,4,5,7' // Obligatorio y solo sucursales válidas
            ]);

            // Usar últimos 2 meses como período fijo
            $fechaInicio = now()->subMonths(2)->startOfDay();
            $fechaFin = now()->endOfDay();

            \Log::info('========== INICIO AUTOMATIZACION ==========');
            \Log::info('Fecha proceso: ' . now()->format('Y-m-d H:i:s'));
            \Log::info('Periodo analisis: ' . $fechaInicio->format('Y-m-d') . ' al ' . $fechaFin->format('Y-m-d'));

            // Configurar días de gracia para no reprocesar
            $diasGracia = 90;
            $fechaLimiteReproceso = now()->subDays($diasGracia);
            \Log::info("No se reprocesaran productos actualizados en los ultimos {$diasGracia} dias");

            $filtroFecha = new ParametrosFiltroFecha(
                null,
                null,
                null,
                false,
                $fechaInicio,
                $fechaFin
            );

            $sucursalId = $request->input('sucursal_id');
            
            // Obtener nombre de la sucursal
            $nombreSucursal = DB::table('Sucursales')
                ->where('Id', $sucursalId)
                ->value('Nombre') ?? 'Sucursal ' . $sucursalId;
            
            \Log::info("Procesando sucursal: {$nombreSucursal} (ID: {$sucursalId})");

            // Obtener productos con baja demanda para UNA sucursal
            $bajaDemanda = GeneralHelper::ObtenerAutomaticamenteProductosBajaDemanda($filtroFecha, $sucursalId);
            
            $totalProductos = $bajaDemanda->detalles->count();
            \Log::info('Total productos encontrados: ' . $totalProductos);
            
            if ($totalProductos == 0) {
                return response()->json([
                    'success' => true,
                    'mensaje' => "No hay productos con baja demanda en {$nombreSucursal}",
                    'total_analizados' => 0,
                    'productos_afectados' => 0
                ]);
            }
            
            // Procesar en lotes de 50 productos
            $loteSize = 50;
            $totalLotes = ceil($totalProductos / $loteSize);
            
            \Log::info("Procesando en {$totalLotes} lotes de {$loteSize} productos");
            
            $productosProcesados = [];
            $productosConError = [];
            $productosPrecioMantenido = [];
            $productosSaltadosPorReproceso = [];
            
            $contadorCategorias = [
                'rotacionLenta' => 0,
                'riesgoEstancamiento' => 0,
                'mercanciaCritica' => 0,
                'remateTotal' => 0,
                'nuevaColeccion' => 0,
                'preciosMantenidos' => 0,
                'saltadosReproceso' => 0
            ];
            
            $productosArray = $bajaDemanda->detalles->values();
            
            for ($lote = 0; $lote < $totalLotes; $lote++) {
                $inicio = $lote * $loteSize;
                $loteProductos = $productosArray->slice($inicio, $loteSize);
                
                \Log::info("Procesando lote " . ($lote + 1) . " de {$totalLotes}");
                
                foreach ($loteProductos as $index => $item) {
                    $productoId = $item->producto_id;
                    $codigo = $item->producto['codigo'];
                    $descripcion = $item->producto['descripcion'];
                    $fechaCreacion = $item->producto['fecha_creacion'];
                    $existencia = $item->producto['existencia'];
                    
                    // Obtener el costo desde la tabla Productos
                    $costoDivisa = DB::table('Productos')
                        ->where('ID', $productoId)
                        ->value('CostoDivisa') ?? 0;
                    
                    $pvpActual = $item->producto['pvp_divisa'] ?? 0;
                    
                    // Verificar si ya fue procesado recientemente
                    $fechaUltimoCambio = DB::table('ProductoSucursal')
                        ->where('ProductoId', $productoId)
                        ->where('SucursalId', $sucursalId)
                        ->value('FechaNuevoPrecio');
                    
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
                    
                    // Calcular antigüedad en meses
                    $mesesAntiguedad = now()->diffInMonths($fechaCreacion);
                    
                    // Determinar porcentaje de descuento según antigüedad
                    $porcentajeDescuento = 0;
                    $categoria = '';
                    
                    if ($mesesAntiguedad >= 2 && $mesesAntiguedad < 5) {
                        $porcentajeDescuento = 20;
                        $categoria = 'Rotacion Lenta';
                        $contadorCategorias['rotacionLenta']++;
                    } elseif ($mesesAntiguedad >= 5 && $mesesAntiguedad < 8) {
                        $porcentajeDescuento = 30;
                        $categoria = 'Riesgo Estancamiento';
                        $contadorCategorias['riesgoEstancamiento']++;
                    } elseif ($mesesAntiguedad >= 8 && $mesesAntiguedad < 12) {
                        $porcentajeDescuento = 50;
                        $categoria = 'Mercancia Critica';
                        $contadorCategorias['mercanciaCritica']++;
                    } elseif ($mesesAntiguedad >= 12) {
                        $porcentajeDescuento = 100;
                        $categoria = 'Remate Total';
                        $contadorCategorias['remateTotal']++;
                    } else {
                        $contadorCategorias['nuevaColeccion']++;
                        continue;
                    }
                    
                    // Calcular ganancia actual
                    $gananciaActual = $pvpActual - $costoDivisa;
                    
                    // ============================================
                    // REGLA CORREGIDA: Productos con pérdida o sin ganancia
                    // ============================================
                    if ($gananciaActual <= 0) {
                        // NO hacer nada, mantener el precio actual
                        $contadorCategorias['preciosMantenidos']++;
                        
                        \Log::info("[PRECIO MANTENIDO] Producto ID: {$productoId}, Sucursal: {$sucursalId}");
                        \Log::info("   Codigo: {$codigo}");
                        \Log::info("   Costo: $" . number_format($costoDivisa, 2));
                        \Log::info("   PVP Actual: $" . number_format($pvpActual, 2));
                        \Log::info("   Ganancia: $" . number_format($gananciaActual, 2) . " (Perdida o sin ganancia)");
                        \Log::info("   -> Se MANTIENE el precio actual: $" . number_format($pvpActual, 2));
                        
                        $productosPrecioMantenido[] = [
                            'id' => $productoId,
                            'codigo' => $codigo,
                            'descripcion' => $descripcion,
                            'costo' => round($costoDivisa, 2),
                            'pvp_actual' => round($pvpActual, 2),
                            'ganancia' => round($gananciaActual, 2),
                            'razon' => 'Producto en perdida o sin ganancia'
                        ];
                        
                        continue; // Saltar al siguiente producto, NO actualizar
                    }
                    
                    // ============================================
                    // Producto con ganancia positiva: aplicar descuento
                    // ============================================
                    $reduccion = $gananciaActual * ($porcentajeDescuento / 100);
                    $nuevoPvp = $pvpActual - $reduccion;
                    
                    // Asegurar que el nuevo precio no sea menor al costo
                    if ($nuevoPvp < $costoDivisa) {
                        $nuevoPvp = $costoDivisa;
                    }
                    $nuevoPvp = round($nuevoPvp, 2);
                    
                    // Si el precio actual ya es menor o igual al propuesto, mantener
                    if ($pvpActual <= $nuevoPvp) {
                        $contadorCategorias['preciosMantenidos']++;
                        \Log::info("[PRECIO MANTENIDO] {$codigo} - Actual: \${$pvpActual} <= Propuesto: \${$nuevoPvp}");
                        continue;
                    }
                    
                    // Log para productos que se van a actualizar
                    if ($lote == 0 && $index < 10) {
                        \Log::info("[ACTUALIZANDO] Producto ID: {$productoId}, Sucursal: {$sucursalId}");
                        \Log::info("   Codigo: {$codigo}");
                        \Log::info("   Costo: $" . number_format($costoDivisa, 2));
                        \Log::info("   Precio Actual: $" . number_format($pvpActual, 2));
                        \Log::info("   Ganancia: $" . number_format($gananciaActual, 2));
                        \Log::info("   Antiguedad: {$mesesAntiguedad} meses, Categoria: {$categoria}");
                        \Log::info("   Descuento aplicado: {$porcentajeDescuento}%");
                        \Log::info("   Reduccion: $" . number_format($reduccion, 2));
                        \Log::info("   -> Nuevo Precio: $" . number_format($nuevoPvp, 2));
                    }
                    
                    try {
                        $existe = DB::table('ProductoSucursal')
                            ->where('ProductoId', $productoId)
                            ->where('SucursalId', $sucursalId)
                            ->exists();
                        
                        if (!$existe) {
                            \Log::warning("[ADVERTENCIA] No existe registro para ProductoId: {$productoId}");
                            continue;
                        }
                        
                        $actualizado = DB::table('ProductoSucursal')
                            ->where('ProductoId', $productoId)
                            ->where('SucursalId', $sucursalId)
                            ->update([
                                'PvpAnterior' => $pvpActual,
                                'NuevoPvp' => $nuevoPvp,
                                'PvpDivisa' => $nuevoPvp,
                                'FechaNuevoPrecio' => now()
                            ]);
                        
                        if ($actualizado) {
                            $productosProcesados[] = [
                                'id' => $productoId,
                                'codigo' => $codigo,
                                'descripcion' => $descripcion,
                                'categoria' => $categoria,
                                'antiguedad_meses' => round($mesesAntiguedad, 1),
                                'precio_anterior' => round($pvpActual, 2),
                                'nuevo_precio' => round($nuevoPvp, 2),
                                'porcentaje_descuento' => $porcentajeDescuento,
                                'costo' => round($costoDivisa, 2),
                                'existencia' => $existencia,
                                'reduccion' => round($pvpActual - $nuevoPvp, 2)
                            ];
                        }
                        
                    } catch (\Exception $e) {
                        $productosConError[] = [
                            'id' => $productoId,
                            'error' => $e->getMessage()
                        ];
                        \Log::error("[ERROR] Producto {$productoId}: " . $e->getMessage());
                    }
                }
                
                unset($loteProductos);
                gc_collect_cycles();
                
                \Log::info("Lote " . ($lote + 1) . " completado. Actualizados: " . count($productosProcesados));
            }
            
            // ============================================
            // REGISTRAR RESUMEN FINAL EN LOG
            // ============================================
            \Log::info('========== RESUMEN FINAL AUTOMATIZACION ==========');
            \Log::info("Sucursal: {$nombreSucursal} (ID: {$sucursalId})");
            \Log::info('Estadisticas:');
            \Log::info("   |- Total productos encontrados: " . $totalProductos);
            \Log::info("   |- Nueva Coleccion (sin cambios): " . $contadorCategorias['nuevaColeccion']);
            \Log::info("   |- Saltados por reproceso: " . $contadorCategorias['saltadosReproceso']);
            \Log::info("   |- Precios mantenidos (perdida o ya menor): " . $contadorCategorias['preciosMantenidos']);
            \Log::info("   |- Rotacion Lenta (20%): " . $contadorCategorias['rotacionLenta']);
            \Log::info("   |- Riesgo Estancamiento (30%): " . $contadorCategorias['riesgoEstancamiento']);
            \Log::info("   |- Mercancia Critica (50%): " . $contadorCategorias['mercanciaCritica']);
            \Log::info("   |- Remate Total (100%): " . $contadorCategorias['remateTotal']);
            \Log::info("   '- Total productos actualizados: " . count($productosProcesados));
            
            \Log::info('========== FIN AUTOMATIZACION ==========');
            
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
                'detalles' => $productosProcesados,           // 432 productos actualizados
                'detalles_mantenidos' => $productosPrecioMantenido,  // ← 148 productos mantenidos
                'detalles_saltados' => $productosSaltadosPorReproceso, // ← productos saltados (si hay)
                'errores' => $productosConError
            ]);
            
        } catch (\Exception $e) {
            \Log::error('ERROR GENERAL en automatizacion: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}