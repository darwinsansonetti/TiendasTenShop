<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BovedaController extends Controller
{
    private $denominacionesDivisa = [1, 2, 5, 10, 20, 50, 100];
    private $denominacionesBs = [1, 2, 5, 10, 20, 50, 100, 200, 500];
    
    private $tiposOtros = [
        1 => 'Biopago',
        2 => 'Transferencia',
        3 => 'Cashea',
        4 => 'Zelle'
    ];

    // /**
    //  * Mostrar listado de bóvedas con filtros
    //  */
    // public function index(Request $request)
    // {
    //     try {
    //         session([
    //             'menu_active' => 'Bóveda',
    //             'submenu_active' => 'Cierre Diario Bóveda'
    //         ]);

    //         // ================================================
    //         // FILTROS
    //         // ================================================
    //         $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
    //         $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
    //         $estatus = $request->input('estatus', 'todos');
    //         $conciliacion = $request->input('conciliacion', 'todos');

    //         // ================================================
    //         // QUERY CON FILTROS
    //         // ================================================
    //         $query = DB::connection('sqlsrv')
    //             ->table('Boveda as b')
    //             ->leftJoin('Sucursales as s', 'b.SucursalId', '=', 's.ID')
    //             ->leftJoin('DivisaValor as dv', 'b.DivisaValorId', '=', 'dv.ID')
    //             ->whereDate('b.Fecha', '>=', $fechaInicio)
    //             ->whereDate('b.Fecha', '<=', $fechaFin)
    //             ->orderBy('b.Fecha', 'desc')
    //             ->orderBy('b.BovedaId', 'desc')
    //             ->select([
    //                 'b.*',
    //                 's.Nombre as sucursal_nombre',
    //                 'dv.Valor as tasa_cambio'
    //             ]);

    //         // Filtro por estatus
    //         if ($estatus !== 'todos' && $estatus !== '') {
    //             $query->where('b.Estatus', (int) $estatus);
    //         }

    //         // Filtro por conciliación
    //         if ($conciliacion !== 'todos' && $conciliacion !== '') {
    //             $query->where('b.EstatusConciliacion', (int) $conciliacion);
    //         }

    //         $bovedas = $query->get();

    //         // ================================================
    //         // FORMATEAR DATOS
    //         // ================================================
    //         $bovedas->transform(function ($item) {
    //             $item->EstatusTexto = $item->Estatus == 0 ? 'Abierto' : 'Cerrado';
    //             $item->EstatusBadge = $item->Estatus == 0 ? 'success' : 'secondary';
    //             $item->FechaFormateada = $item->Fecha ? Carbon::parse($item->Fecha)->format('d/m/Y') : 'N/A';
                
    //             $conciliacionTexto = [
    //                 0 => 'Pendiente',
    //                 1 => 'Conciliado',
    //                 2 => 'Con Diferencia'
    //             ];
    //             $conciliacionBadge = [
    //                 0 => 'warning',
    //                 1 => 'success',
    //                 2 => 'danger'
    //             ];
    //             $item->ConciliacionTexto = $conciliacionTexto[$item->EstatusConciliacion ?? 0] ?? 'Pendiente';
    //             $item->ConciliacionBadge = $conciliacionBadge[$item->EstatusConciliacion ?? 0] ?? 'warning';
                
    //             return $item;
    //         });

    //         // ================================================
    //         // ESTADÍSTICAS DEL RANGO
    //         // ================================================
    //         $totalBovedas = $bovedas->count();
    //         $totalAbiertas = $bovedas->where('Estatus', 0)->count();
    //         $totalCerradas = $bovedas->where('Estatus', 1)->count();
    //         $totalConciliadas = $bovedas->where('EstatusConciliacion', 1)->count();
    //         $totalConDiferencia = $bovedas->where('EstatusConciliacion', 2)->count();

    //         return view('cpanel.boveda.index', [
    //             'bovedas' => $bovedas,
    //             'fechaInicio' => $fechaInicio,
    //             'fechaFin' => $fechaFin,
    //             'estatusFiltro' => $estatus,
    //             'conciliacionFiltro' => $conciliacion,
    //             'totalBovedas' => $totalBovedas,
    //             'totalAbiertas' => $totalAbiertas,
    //             'totalCerradas' => $totalCerradas,
    //             'totalConciliadas' => $totalConciliadas,
    //             'totalConDiferencia' => $totalConDiferencia
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error en BovedaController::index: ' . $e->getMessage());
    //         return back()->with('error', 'Error al cargar el listado de bóvedas');
    //     }
    // }

    public function index(Request $request)
    {
        try {
            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Cierre Diario Bóveda'
            ]);

            // ================================================
            // FILTROS DE FECHA
            // ================================================
            $fechaMinima = '2026-09-01';

            $defaultInicio = Carbon::now('America/Caracas')->startOfMonth()->format('Y-m-d');
            if ($defaultInicio < $fechaMinima) {
                $defaultInicio = $fechaMinima;
            }

            $fechaInicio = $request->input('fecha_inicio', $defaultInicio);
            $fechaFin    = $request->input('fecha_fin', Carbon::now('America/Caracas')->format('Y-m-d'));

            if ($fechaInicio < $fechaMinima) {
                $fechaInicio = $fechaMinima;
            }

            // ================================================
            // CIERRES DIARIOS DEL RANGO (agrupados por fecha)
            // ================================================
            $cierresRaw = DB::connection('sqlsrv')
                ->table('CierreDiario as cd')
                ->leftJoin('Sucursales as s', 'cd.SucursalId', '=', 's.ID')
                ->leftJoin('DivisaValor as dv', 'cd.DivisaValorId', '=', 'dv.ID')
                ->whereDate('cd.Fecha', '>=', $fechaInicio)
                ->whereDate('cd.Fecha', '<=', $fechaFin)
                ->where('cd.Estatus', 4) // 4 = Finalizado
                ->orderBy('cd.Fecha', 'desc')
                ->orderBy('s.Nombre')
                ->select([
                    'cd.CierreDiarioId',
                    'cd.Fecha',
                    'cd.SucursalId',
                    'cd.VentaSistema',
                    'cd.EfectivoBs',
                    'cd.EfectivoDivisas',
                    'cd.ZelleDivisas',
                    'cd.PagoMovilBs',
                    'cd.TransferenciaBs',
                    'cd.CasheaBs',
                    'cd.Biopago',
                    'cd.NumeroZeta',
                    'cd.DivisaValorId',
                    's.Nombre as sucursal_nombre',
                    'dv.Valor as tasa_cambio'
                ])
                ->get();

            // Agrupar por fecha
            $cierresPorFecha = $cierresRaw->groupBy(function ($item) {
                return Carbon::parse($item->Fecha)->format('Y-m-d');
            });

            // ================================================
            // BÓVEDAS DEL RANGO (indexadas por fecha)
            // ================================================
            $bovedasPorFecha = DB::connection('sqlsrv')
                ->table('Boveda')
                ->whereDate('Fecha', '>=', $fechaInicio)
                ->whereDate('Fecha', '<=', $fechaFin)
                ->select(['BovedaId', 'Fecha', 'Estatus', 'EstatusConciliacion'])
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->Fecha)->format('Y-m-d');
                });

            // ================================================
            // CONSTRUIR LISTADO AGRUPADO POR FECHA
            // ================================================
            $listado = collect();

            foreach ($cierresPorFecha as $fecha => $cierres) {

                $boveda = $bovedasPorFecha->get($fecha);

                // Totales del día (suma de todas las sucursales)
                $ventaSistemaTotal   = $cierres->sum('VentaSistema');
                $efectivoBsTotal     = $cierres->sum('EfectivoBs');
                $efectivoDivisasTotal = $cierres->sum('EfectivoDivisas');

                // Lista de sucursales del día
                $sucursales = $cierres->pluck('sucursal_nombre')->filter()->values();

                // Estado de la bóveda
                if ($boveda) {
                    if ($boveda->Estatus == 0) {
                        $estadoBoveda = 'Con Bóveda (Abierta)';
                        $estadoBadge = 'warning';
                        $accion = 'retomar';
                    } else {
                        $estadoBoveda = 'Con Bóveda (Finalizada)';
                        $estadoBadge = 'success';
                        $accion = 'ver';
                    }
                    $bovedaId = $boveda->BovedaId;
                } else {
                    $estadoBoveda = 'Sin Bóveda';
                    $estadoBadge = 'secondary';
                    $accion = 'crear';
                    $bovedaId = null;
                }

                $listado->push((object) [
                    'Fecha'                 => $fecha,
                    'FechaFormateada'       => Carbon::parse($fecha)->format('d/m/Y'),
                    'CantidadSucursales'    => $cierres->count(),
                    'Sucursales'            => $sucursales,
                    'VentaSistemaTotal'     => $ventaSistemaTotal,
                    'EfectivoBsTotal'       => $efectivoBsTotal,
                    'EfectivoDivisasTotal'  => $efectivoDivisasTotal,
                    'BovedaId'              => $bovedaId,
                    'EstadoBoveda'          => $estadoBoveda,
                    'EstadoBadge'           => $estadoBadge,
                    'Accion'                => $accion,
                ]);
            }

            // ================================================
            // ESTADÍSTICAS GENERALES
            // ================================================
            $totalDias            = $listado->count();
            $totalDiasSinBoveda   = $listado->where('Accion', 'crear')->count();
            $totalDiasConAbierta  = $listado->where('Accion', 'retomar')->count();
            $totalDiasConFinal    = $listado->where('Accion', 'ver')->count();
            $totalCierres         = $cierresRaw->count();

            return view('cpanel.boveda.index', [
                'listado'              => $listado,
                'fechaInicio'          => $fechaInicio,
                'fechaFin'             => $fechaFin,
                'totalDias'            => $totalDias,
                'totalDiasSinBoveda'   => $totalDiasSinBoveda,
                'totalDiasConAbierta'  => $totalDiasConAbierta,
                'totalDiasConFinal'    => $totalDiasConFinal,
                'totalCierres'         => $totalCierres,
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar el listado: ' . $e->getMessage());
        }
    }

    // /**
    //  * Mostrar formulario para crear bóveda
    //  */
    // public function crear(Request $request)
    // {
    //     try {
    //         session([
    //             'menu_active' => 'Bóveda',
    //             'submenu_active' => 'Cierre Diario Bóveda'
    //         ]);

    //         $sucursalId = $this->obtenerOficinaPrincipal();
    //         $tasa = $this->obtenerTasaCambioActual();

    //         // Verificar si ya existe bóveda abierta hoy
    //         $bovedaExistente = DB::connection('sqlsrv')
    //             ->table('Boveda')
    //             ->where('SucursalId', $sucursalId)
    //             ->where('Fecha', Carbon::now()->format('Y-m-d'))
    //             ->where('Estatus', 0)
    //             ->first();

    //         if ($bovedaExistente) {
    //             return redirect()->route('cpanel.boveda.detalle', $bovedaExistente->BovedaId)
    //                 ->with('info', 'Ya existe un cierre de bóveda abierto para hoy');
    //         }

    //         $fechaCierre = Carbon::now()->subDay()->format('Y-m-d');
            
    //         $cierresDiarios = DB::connection('sqlsrv')
    //             ->table('CierreDiario as cd')
    //             ->leftJoin('Sucursales as s', 'cd.SucursalId', '=', 's.ID')
    //             ->where('cd.Fecha', $fechaCierre)
    //             ->select(['cd.*', 's.Nombre as sucursal_nombre'])
    //             ->orderBy('s.Nombre')
    //             ->get();

    //         if ($cierresDiarios->isEmpty()) {
    //             return redirect()->route('cpanel.boveda.index')
    //                 ->with('error', 'No hay cierres diarios del día anterior (' . Carbon::parse($fechaCierre)->format('d/m/Y') . ') para conciliar');
    //         }

    //         $datosPorSucursal = [];

    //         foreach ($cierresDiarios as $cierre) {
    //             // Puntos de venta de la sucursal con sus montos del cierre (en Bs.)
    //             $puntosVenta = DB::connection('sqlsrv')
    //                 ->table('PagosPuntoDeVenta as ppv')
    //                 ->join('PuntosDeVenta as pdv', 'ppv.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
    //                 ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
    //                 ->where('ppv.CierreDiarioId', $cierre->CierreDiarioId)
    //                 ->select([
    //                     'pdv.PuntoDeVentaId',
    //                     'pdv.Descripcion as pdv_descripcion',
    //                     'pdv.Codigo as pdv_codigo',
    //                     'b.Nombre as banco_nombre',
    //                     'ppv.Monto as monto_sistema' // Este monto está en Bs.
    //                 ])
    //                 ->get();

    //             // Otros conceptos de la sucursal
    //             $otrosConceptos = collect();
    //             if (($cierre->Biopago ?? 0) > 0) {
    //                 $otrosConceptos->push((object) [
    //                     'Tipo' => 1,
    //                     'TipoNombre' => 'Biopago',
    //                     'Moneda' => 'Bs',
    //                     'MontoSistema' => $cierre->Biopago
    //                 ]);
    //             }
    //             if (($cierre->TransferenciaBs ?? 0) > 0) {
    //                 $otrosConceptos->push((object) [
    //                     'Tipo' => 2,
    //                     'TipoNombre' => 'Transferencia',
    //                     'Moneda' => 'Bs',
    //                     'MontoSistema' => $cierre->TransferenciaBs
    //                 ]);
    //             }
    //             if (($cierre->CasheaBs ?? 0) > 0) {
    //                 $otrosConceptos->push((object) [
    //                     'Tipo' => 3,
    //                     'TipoNombre' => 'Cashea',
    //                     'Moneda' => 'Bs',
    //                     'MontoSistema' => $cierre->CasheaBs
    //                 ]);
    //             }
    //             if (($cierre->ZelleDivisas ?? 0) > 0) {
    //                 $otrosConceptos->push((object) [
    //                     'Tipo' => 4,
    //                     'TipoNombre' => 'Zelle',
    //                     'Moneda' => 'USD',
    //                     'MontoSistema' => $cierre->ZelleDivisas
    //                 ]);
    //             }

    //             $datosPorSucursal[] = (object) [
    //                 'SucursalId' => $cierre->SucursalId,
    //                 'SucursalNombre' => $cierre->sucursal_nombre,
    //                 'CierreDiarioId' => $cierre->CierreDiarioId,
    //                 // Efectivo del cierre
    //                 'EfectivoDivisas' => $cierre->EfectivoDivisas ?? 0,
    //                 'EfectivoBs' => $cierre->EfectivoBs ?? 0,
    //                 // Totales del cierre
    //                 'PuntoDeVentaDivisas' => $cierre->PuntoDeVentaDivisas ?? 0,
    //                 'Biopago' => $cierre->Biopago ?? 0,
    //                 'TransferenciaBs' => $cierre->TransferenciaBs ?? 0,
    //                 'CasheaBs' => $cierre->CasheaBs ?? 0,
    //                 'ZelleDivisas' => $cierre->ZelleDivisas ?? 0,
    //                 // Colecciones
    //                 'PuntosVenta' => $puntosVenta,
    //                 'OtrosConceptos' => $otrosConceptos
    //             ];
    //         }

    //         return view('cpanel.boveda.crear', [
    //             'datosPorSucursal' => collect($datosPorSucursal),
    //             'denominacionesDivisa' => $this->denominacionesDivisa,
    //             'denominacionesBs' => $this->denominacionesBs,
    //             'sucursalId' => $sucursalId,
    //             'tasaCambio' => $tasa ? $tasa->Valor : 0,
    //             'fechaCierre' => $fechaCierre
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error en BovedaController::crear: ' . $e->getMessage());
    //         return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
    //     }
    // }

    public function crear(Request $request)
    {
        try {

            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Cierre Diario Bóveda'
            ]);

            // 🔹 Recibir la fecha del cierre diario desde el listado
            $fechaCierre = $request->input('fecha_cierre');

            if (!$fechaCierre) {
                return redirect()->route('cpanel.boveda.index')
                    ->with('error', 'Debe seleccionar una fecha de cierre diario');
            }

            // Normalizar fecha
            try {
                $fechaCierre = Carbon::parse($fechaCierre)->format('Y-m-d');
            } catch (\Exception $e) {
                return redirect()->route('cpanel.boveda.index')
                    ->with('error', 'Fecha de cierre inválida');
            }

            // Verificar si ya existe bóveda para ese día
            $bovedaExistente = DB::connection('sqlsrv')
                ->table('Boveda')
                ->where('Fecha', $fechaCierre)
                ->first();

            if ($bovedaExistente) {
                return redirect()->route('cpanel.boveda.detalle', $bovedaExistente->BovedaId)
                    ->with('info', 'Ya existe una bóveda para el día ' 
                        . Carbon::parse($fechaCierre)->format('d/m/Y'));
            }

            $sucursalId = $this->obtenerOficinaPrincipal();
            $tasa = $this->obtenerTasaCambioActual();

            // 🔹 Buscar TODOS los cierres diarios de esa fecha (todas las sucursales)
            $cierresDiarios = DB::connection('sqlsrv')
                ->table('CierreDiario as cd')
                ->leftJoin('Sucursales as s', 'cd.SucursalId', '=', 's.ID')
                ->whereDate('cd.Fecha', $fechaCierre)
                ->where('cd.Estatus', 4) // 4 = Finalizado
                ->select(['cd.*', 's.Nombre as sucursal_nombre'])
                ->orderBy('s.Nombre')
                ->get();

            if ($cierresDiarios->isEmpty()) {
                return redirect()->route('cpanel.boveda.index')
                    ->with('error', 'No hay cierres diarios finalizados para la fecha ' 
                        . Carbon::parse($fechaCierre)->format('d/m/Y'));
            }

            $datosPorSucursal = [];

            foreach ($cierresDiarios as $cierre) {
                // Puntos de venta de la sucursal con sus montos del cierre (en Bs.)
                $puntosVenta = DB::connection('sqlsrv')
                    ->table('PagosPuntoDeVenta as ppv')
                    ->join('PuntosDeVenta as pdv', 'ppv.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
                    ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                    ->where('ppv.CierreDiarioId', $cierre->CierreDiarioId)
                    ->select([
                        'pdv.PuntoDeVentaId',
                        'pdv.Descripcion as pdv_descripcion',
                        'pdv.Codigo as pdv_codigo',
                        'b.Nombre as banco_nombre',
                        'ppv.Monto as monto_sistema'
                    ])
                    ->get();

                // Otros conceptos de la sucursal
                $otrosConceptos = collect();
                if (($cierre->Biopago ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 1,
                        'TipoNombre' => 'Biopago',
                        'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->Biopago
                    ]);
                }
                if (($cierre->TransferenciaBs ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 2,
                        'TipoNombre' => 'Transferencia',
                        'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->TransferenciaBs
                    ]);
                }
                if (($cierre->CasheaBs ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 3,
                        'TipoNombre' => 'Cashea',
                        'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->CasheaBs
                    ]);
                }
                if (($cierre->ZelleDivisas ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 4,
                        'TipoNombre' => 'Zelle',
                        'Moneda' => 'USD',
                        'MontoSistema' => $cierre->ZelleDivisas
                    ]);
                }

                $datosPorSucursal[] = (object) [
                    'SucursalId' => $cierre->SucursalId,
                    'SucursalNombre' => $cierre->sucursal_nombre,
                    'CierreDiarioId' => $cierre->CierreDiarioId,
                    'EfectivoDivisas' => $cierre->EfectivoDivisas ?? 0,
                    'EfectivoBs' => $cierre->EfectivoBs ?? 0,
                    'PuntoDeVentaDivisas' => $cierre->PuntoDeVentaDivisas ?? 0,
                    'Biopago' => $cierre->Biopago ?? 0,
                    'TransferenciaBs' => $cierre->TransferenciaBs ?? 0,
                    'CasheaBs' => $cierre->CasheaBs ?? 0,
                    'ZelleDivisas' => $cierre->ZelleDivisas ?? 0,
                    'PuntosVenta' => $puntosVenta,
                    'OtrosConceptos' => $otrosConceptos
                ];
            }

            return view('cpanel.boveda.crear', [
                'datosPorSucursal'      => collect($datosPorSucursal),
                'denominacionesDivisa'  => $this->denominacionesDivisa,
                'denominacionesBs'      => $this->denominacionesBs,
                'sucursalId'            => $sucursalId,
                'tasaCambio'            => $tasa ? $tasa->Valor : 0,
                'fechaCierre'           => $fechaCierre,
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Guardar una nueva bóveda con conciliación
     */
    public function guardar(Request $request)
    {
        try {

            $request->validate([
                'fecha' => 'required|date',
                'observacion' => 'nullable|string|max:500',
                'denominaciones' => 'nullable|array',
                'conciliacion_efectivo' => 'nullable|array',
                'conciliacion_pdv' => 'nullable|array',
                'conciliacion_otros' => 'nullable|array'
            ]);

            // 🔹 Normalizar fecha del cierre diario
            $fechaCierre = Carbon::parse($request->fecha)->format('Y-m-d');

            // 🔹 Validar que no exista bóveda para esa fecha
            $bovedaExistente = DB::connection('sqlsrv')
                ->table('Boveda')
                ->whereDate('Fecha', $fechaCierre)
                ->first();

            if ($bovedaExistente) {
                return redirect()->route('cpanel.boveda.detalle', $bovedaExistente->BovedaId)
                    ->with('info', 'Ya existe una bóveda para el día ' . Carbon::parse($fechaCierre)->format('d/m/Y'));
            }

            DB::connection('sqlsrv')->beginTransaction();

            try {
                $sucursalId = $this->obtenerOficinaPrincipal();
                $usuarioId = auth()->user()->id ?? null;
                $tasa = $this->obtenerTasaCambioActual();

                // 1. Insertar bóveda con la FECHA DEL CIERRE DIARIO
                $bovedaId = DB::connection('sqlsrv')->table('Boveda')->insertGetId([
                    'SucursalId' => $sucursalId,
                    'DivisaValorId' => $tasa ? $tasa->ID : null,
                    'Fecha' => $fechaCierre,
                    'Estatus' => 0,
                    'EstatusConciliacion' => 0,
                    'Observacion' => $request->observacion,
                    'FechaCreacion' => Carbon::now(),
                    'UsuarioCreacion' => $usuarioId
                ]);

                // 2. Denominaciones
                if ($request->has('denominaciones')) {
                    foreach ($request->denominaciones as $sucId => $denominaciones) {
                        // Divisas
                        if (isset($denominaciones['divisa'])) {
                            foreach ($denominaciones['divisa'] as $item) {
                                if (($item['cantidad'] ?? 0) > 0) {
                                    DB::connection('sqlsrv')->table('BovedaDenominacionDivisa')->insert([
                                        'BovedaId' => $bovedaId,
                                        'SucursalId' => $sucId,
                                        'Denominacion' => $item['denominacion'],
                                        'Cantidad' => $item['cantidad'],
                                        'MontoTotal' => $item['denominacion'] * $item['cantidad'],
                                        'FechaCreacion' => Carbon::now()
                                    ]);
                                }
                            }
                        }
                        // Bolívares
                        if (isset($denominaciones['bs'])) {
                            foreach ($denominaciones['bs'] as $item) {
                                if (($item['cantidad'] ?? 0) > 0) {
                                    DB::connection('sqlsrv')->table('BovedaDenominacionBs')->insert([
                                        'BovedaId' => $bovedaId,
                                        'SucursalId' => $sucId,
                                        'Denominacion' => $item['denominacion'],
                                        'Cantidad' => $item['cantidad'],
                                        'MontoTotal' => $item['denominacion'] * $item['cantidad'],
                                        'FechaCreacion' => Carbon::now()
                                    ]);
                                }
                            }
                        }
                    }
                }

                $tieneDiferencias = false;

                // 3. Conciliación EFECTIVO
                if ($request->has('conciliacion_efectivo')) {
                    foreach ($request->conciliacion_efectivo as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionEfectivo')->insert([
                            'BovedaId' => $bovedaId,
                            'SucursalId' => $item['sucursal_id'],
                            'CierreDiarioId' => $item['cierre_diario_id'] ?? null,
                            'Tipo' => $item['tipo'],
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 4. Conciliación PDV
                if ($request->has('conciliacion_pdv')) {
                    foreach ($request->conciliacion_pdv as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionPDV')->insert([
                            'BovedaId' => $bovedaId,
                            'SucursalId' => $item['sucursal_id'],
                            'PuntoDeVentaId' => $item['punto_venta_id'],
                            'CierreDiarioId' => $item['cierre_diario_id'] ?? null,
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'FechaDeposito' => $fechaCierre,
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 5. Conciliación Otros
                if ($request->has('conciliacion_otros')) {
                    foreach ($request->conciliacion_otros as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionOtros')->insert([
                            'BovedaId' => $bovedaId,
                            'SucursalId' => $item['sucursal_id'],
                            'Tipo' => $item['tipo'],
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'FechaDeposito' => $fechaCierre,
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 6. Actualizar estatus de conciliación
                $estatusConciliacion = $tieneDiferencias ? 2 : 1;
                DB::connection('sqlsrv')->table('Boveda')
                    ->where('BovedaId', $bovedaId)
                    ->update(['EstatusConciliacion' => $estatusConciliacion]);

                DB::connection('sqlsrv')->commit();

                return redirect()->route('cpanel.boveda.detalle', $bovedaId)
                    ->with('success', 'Cierre Diario Bóveda creado exitosamente');

            } catch (\Exception $e) {
                DB::connection('sqlsrv')->rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput()->with('error', 'Complete los datos necesarios');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle con conciliación
     */
    public function detalle($id)
    {
        try {
            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Cierre Diario Bóveda'
            ]);

            $boveda = DB::connection('sqlsrv')
                ->table('Boveda as b')
                ->leftJoin('Sucursales as s', 'b.SucursalId', '=', 's.ID')
                ->leftJoin('DivisaValor as dv', 'b.DivisaValorId', '=', 'dv.ID')
                ->where('b.BovedaId', $id)
                ->select(['b.*', 's.Nombre as sucursal_nombre', 'dv.Valor as tasa_cambio'])
                ->first();

            if (!$boveda) {
                return redirect()->route('cpanel.boveda.index')->with('error', 'Bóveda no encontrada');
            }

            // Denominaciones
            $denominacionesDivisa = DB::connection('sqlsrv')
                ->table('BovedaDenominacionDivisa as bdd')
                ->leftJoin('Sucursales as s', 'bdd.SucursalId', '=', 's.ID')
                ->where('bdd.BovedaId', $id)
                ->select(['bdd.*', 's.Nombre as sucursal_nombre'])
                ->get();

            $denominacionesBs = DB::connection('sqlsrv')
                ->table('BovedaDenominacionBs as bdb')
                ->leftJoin('Sucursales as s', 'bdb.SucursalId', '=', 's.ID')
                ->where('bdb.BovedaId', $id)
                ->select(['bdb.*', 's.Nombre as sucursal_nombre'])
                ->get();

            // Conciliación PDV
            $conciliacionPDV = DB::connection('sqlsrv')
                ->table('BovedaConciliacionPDV as bcp')
                ->leftJoin('Sucursales as s', 'bcp.SucursalId', '=', 's.ID')
                ->leftJoin('PuntosDeVenta as pdv', 'bcp.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
                ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                ->where('bcp.BovedaId', $id)
                ->select([
                    'bcp.*',
                    's.Nombre as sucursal_nombre',
                    'pdv.Descripcion as pdv_descripcion',
                    'pdv.Codigo as pdv_codigo',
                    'b.Nombre as banco_nombre'
                ])
                ->get();

            // Conciliación Otros
            $conciliacionOtros = DB::connection('sqlsrv')
                ->table('BovedaConciliacionOtros as bco')
                ->leftJoin('Sucursales as s', 'bco.SucursalId', '=', 's.ID')
                ->where('bco.BovedaId', $id)
                ->select(['bco.*', 's.Nombre as sucursal_nombre'])
                ->get();

            // Préstamos
            $prestamos = DB::connection('sqlsrv')
                ->table('BovedaPrestamo as bp')
                ->leftJoin('Sucursales as s', 'bp.SucursalId', '=', 's.ID')
                ->where('bp.BovedaId', $id)
                ->select(['bp.*', 's.Nombre as sucursal_nombre'])
                ->get();

            // Formatear
            $boveda->EstatusTexto = $boveda->Estatus == 0 ? 'Abierto' : 'Cerrado';
            $boveda->EstatusBadge = $boveda->Estatus == 0 ? 'success' : 'secondary';
            $boveda->FechaFormateada = $boveda->Fecha ? Carbon::parse($boveda->Fecha)->format('d/m/Y') : 'N/A';

            // Totales
            $totalDivisa = $denominacionesDivisa->sum('MontoTotal');
            $totalBs = $denominacionesBs->sum('MontoTotal');
            $totalPDVSistema = $conciliacionPDV->sum('MontoSistema');
            $totalPDVDepositado = $conciliacionPDV->sum('MontoDepositado');
            $diferenciaPDV = $totalPDVSistema - $totalPDVDepositado;
            $totalOtrosSistema = $conciliacionOtros->sum('MontoSistema');
            $totalOtrosDepositado = $conciliacionOtros->sum('MontoDepositado');
            $diferenciaOtros = $totalOtrosSistema - $totalOtrosDepositado;
            $prestamosPendientes = $prestamos->where('Estatus', 0)->sum('MontoDivisa');

            // Conciliación general
            $tieneDiferencias = ($conciliacionPDV->where('TieneDiferencia', 1)->count() > 0) || 
                               ($conciliacionOtros->where('TieneDiferencia', 1)->count() > 0);

            $conciliacionTexto = [0 => 'Pendiente', 1 => 'Conciliado', 2 => 'Con Diferencia'];
            $conciliacionBadge = [0 => 'warning', 1 => 'success', 2 => 'danger'];

            return view('cpanel.boveda.detalle', [
                'boveda' => $boveda,
                'denominacionesDivisa' => $denominacionesDivisa,
                'denominacionesBs' => $denominacionesBs,
                'conciliacionPDV' => $conciliacionPDV,
                'conciliacionOtros' => $conciliacionOtros,
                'prestamos' => $prestamos,
                'totalDivisa' => $totalDivisa,
                'totalBs' => $totalBs,
                'totalPDVSistema' => $totalPDVSistema,
                'totalPDVDepositado' => $totalPDVDepositado,
                'diferenciaPDV' => $diferenciaPDV,
                'totalOtrosSistema' => $totalOtrosSistema,
                'totalOtrosDepositado' => $totalOtrosDepositado,
                'diferenciaOtros' => $diferenciaOtros,
                'prestamosPendientes' => $prestamosPendientes,
                'tieneDiferencias' => $tieneDiferencias,
                'puedeCerrar' => $boveda->Estatus == 0,
                'conciliacionTexto' => $conciliacionTexto,
                'conciliacionBadge' => $conciliacionBadge,
                'tiposOtros' => $this->tiposOtros
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar el detalle');
        }
    }

    /**
     * Cerrar bóveda
     */
    public function cerrar($id)
    {
        try {
            $boveda = DB::connection('sqlsrv')->table('Boveda')->where('BovedaId', $id)->first();

            if (!$boveda) {
                return response()->json(['success' => false, 'message' => 'Bóveda no encontrada'], 404);
            }

            if ($boveda->Estatus == 1) {
                return response()->json(['success' => false, 'message' => 'La bóveda ya está cerrada'], 400);
            }

            $prestamosPendientes = DB::connection('sqlsrv')
                ->table('BovedaPrestamo')->where('BovedaId', $id)->where('Estatus', 0)->count();

            if ($prestamosPendientes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cerrar porque hay préstamos pendientes'
                ], 400);
            }

            $usuarioId = auth()->user()->id ?? null;

            DB::connection('sqlsrv')->table('Boveda')->where('BovedaId', $id)->update([
                'Estatus' => 1,
                'FechaCierre' => Carbon::now(),
                'UsuarioCierre' => $usuarioId
            ]);

            return response()->json(['success' => true, 'message' => 'Bóveda cerrada exitosamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener tasa de cambio actual
     */
    private function obtenerTasaCambioActual()
    {
        return DB::connection('sqlsrv')->table('DivisaValor')->orderBy('ID', 'desc')->first();
    }

    private function obtenerOficinaPrincipal()
    {
        try {
            $oficina = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 0) // Oficina Principal
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->first();

            if (!$oficina) {
                throw new \Exception('No se encontró la Oficina Principal (Tipo = 0)');
            }

            return $oficina->ID;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Mostrar formulario para editar una bóveda abierta
     */
    public function editar($id)
    {
        try {

            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Cierre Diario Bóveda'
            ]);

            // Verificar que la bóveda existe y está abierta
            $boveda = DB::connection('sqlsrv')
                ->table('Boveda')
                ->where('BovedaId', $id)
                ->where('Estatus', 0)
                ->first();

            if (!$boveda) {
                return redirect()->route('cpanel.boveda.index')
                    ->with('error', 'La bóveda no existe o ya está cerrada');
            }

            // 🔍 DIAGNÓSTICO: ver cómo se parsea la fecha
            $fechaParseada = Carbon::parse($boveda->Fecha);
            $fechaCierre = $fechaParseada->format('Y-m-d');

            // 🔍 Contar cierres por distintas formas de filtro
            $countWhereDate = DB::connection('sqlsrv')
                ->table('CierreDiario')
                ->whereDate('Fecha', $fechaCierre)
                ->count();

            $countWhere = DB::connection('sqlsrv')
                ->table('CierreDiario')
                ->where('Fecha', $fechaCierre)
                ->count();

            $countWhereBetween = DB::connection('sqlsrv')
                ->table('CierreDiario')
                ->whereBetween('Fecha', [$fechaCierre . ' 00:00:00', $fechaCierre . ' 23:59:59'])
                ->count();

            $cierresDiarios = DB::connection('sqlsrv')
                ->table('CierreDiario as cd')
                ->leftJoin('Sucursales as s', 'cd.SucursalId', '=', 's.ID')
                ->whereDate('cd.Fecha', $fechaCierre)
                ->where('cd.Estatus', 4)
                ->select([
                    'cd.CierreDiarioId',
                    'cd.SucursalId',
                    'cd.Fecha',
                    'cd.Estatus',
                    'cd.EfectivoDivisas',
                    'cd.EfectivoBs',
                    'cd.Biopago',
                    'cd.TransferenciaBs',
                    'cd.CasheaBs',
                    'cd.ZelleDivisas',
                    's.Nombre as sucursal_nombre'
                ])
                ->orderBy('s.Nombre')
                ->get();

            // 🔍 DIAGNÓSTICO: verificar qué sucursales están guardadas en la bóveda
            $sucursalesEnBoveda = DB::connection('sqlsrv')
                ->table('BovedaConciliacionEfectivo')
                ->where('BovedaId', $id)
                ->distinct()
                ->pluck('SucursalId')
                ->toArray();

            $sucursalesEnBovedaPDV = DB::connection('sqlsrv')
                ->table('BovedaConciliacionPDV')
                ->where('BovedaId', $id)
                ->distinct()
                ->pluck('SucursalId')
                ->toArray();

            $sucursalesEnBovedaOtros = DB::connection('sqlsrv')
                ->table('BovedaConciliacionOtros')
                ->where('BovedaId', $id)
                ->distinct()
                ->pluck('SucursalId')
                ->toArray();

            // Denominaciones ya guardadas
            // $denominacionesDivisaGuardadas = DB::connection('sqlsrv')
            //     ->table('BovedaDenominacionDivisa')
            //     ->where('BovedaId', $id)
            //     ->get()
            //     ->keyBy(function($item) {
            //         return $item->SucursalId . '_' . $item->Denominacion;
            //     });

            $denominacionesDivisaGuardadas = DB::connection('sqlsrv')
                ->table('BovedaDenominacionDivisa')
                ->where('BovedaId', $id)
                ->get()
                ->keyBy(function($item) {
                    // Normalizar a 2 decimales para que coincida con el array
                    return $item->SucursalId . '_' . number_format((float) $item->Denominacion, 2, '.', '');
                });

            $denominacionesBsGuardadas = DB::connection('sqlsrv')
                ->table('BovedaDenominacionBs')
                ->where('BovedaId', $id)
                ->get()
                ->keyBy(function($item) {
                    return $item->SucursalId . '_' . $item->Denominacion;
                });

            // Conciliaciones ya guardadas
            $conciliacionPDVGuardada = DB::connection('sqlsrv')
                ->table('BovedaConciliacionPDV')
                ->where('BovedaId', $id)
                ->get()
                ->keyBy(function($item) {
                    return $item->SucursalId . '_' . $item->PuntoDeVentaId;
                });

            $conciliacionOtrosGuardada = DB::connection('sqlsrv')
                ->table('BovedaConciliacionOtros')
                ->where('BovedaId', $id)
                ->get()
                ->keyBy(function($item) {
                    return $item->SucursalId . '_' . $item->Tipo;
                });

            $conciliacionEfectivoGuardada = DB::connection('sqlsrv')
                ->table('BovedaConciliacionEfectivo')
                ->where('BovedaId', $id)
                ->get()
                ->keyBy(function($item) {
                    return $item->SucursalId . '_' . $item->Tipo;
                });

            // Armar datos por sucursal
            $datosPorSucursal = [];

            foreach ($cierresDiarios as $cierre) {
                $puntosVenta = DB::connection('sqlsrv')
                    ->table('PagosPuntoDeVenta as ppv')
                    ->join('PuntosDeVenta as pdv', 'ppv.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
                    ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                    ->where('ppv.CierreDiarioId', $cierre->CierreDiarioId)
                    ->select([
                        'pdv.PuntoDeVentaId',
                        'pdv.Descripcion as pdv_descripcion',
                        'pdv.Codigo as pdv_codigo',
                        'b.Nombre as banco_nombre',
                        'ppv.Monto as monto_sistema'
                    ])
                    ->get();

                $otrosConceptos = collect();
                if (($cierre->Biopago ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 1, 'TipoNombre' => 'Biopago', 'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->Biopago
                    ]);
                }
                if (($cierre->TransferenciaBs ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 2, 'TipoNombre' => 'Transferencia', 'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->TransferenciaBs
                    ]);
                }
                if (($cierre->CasheaBs ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 3, 'TipoNombre' => 'Cashea', 'Moneda' => 'Bs',
                        'MontoSistema' => $cierre->CasheaBs
                    ]);
                }
                if (($cierre->ZelleDivisas ?? 0) > 0) {
                    $otrosConceptos->push((object) [
                        'Tipo' => 4, 'TipoNombre' => 'Zelle', 'Moneda' => 'USD',
                        'MontoSistema' => $cierre->ZelleDivisas
                    ]);
                }

                $datosPorSucursal[] = (object) [
                    'SucursalId' => $cierre->SucursalId,
                    'SucursalNombre' => $cierre->sucursal_nombre,
                    'CierreDiarioId' => $cierre->CierreDiarioId,
                    'EfectivoDivisas' => $cierre->EfectivoDivisas ?? 0,
                    'EfectivoBs' => $cierre->EfectivoBs ?? 0,
                    'PuntosVenta' => $puntosVenta,
                    'OtrosConceptos' => $otrosConceptos
                ];
            }

            $tasa = $this->obtenerTasaCambioActual();

            return view('cpanel.boveda.editar', [
                'boveda' => $boveda,
                'datosPorSucursal' => collect($datosPorSucursal),
                'denominacionesDivisa' => $this->denominacionesDivisa,
                'denominacionesBs' => $this->denominacionesBs,
                'denominacionesDivisaGuardadas' => $denominacionesDivisaGuardadas,
                'denominacionesBsGuardadas' => $denominacionesBsGuardadas,
                'conciliacionPDVGuardada' => $conciliacionPDVGuardada,
                'conciliacionOtrosGuardada' => $conciliacionOtrosGuardada,
                'conciliacionEfectivoGuardada' => $conciliacionEfectivoGuardada,
                'tasaCambio' => $tasa ? $tasa->Valor : 0,
                'fechaCierre' => $fechaCierre
            ]);

        } catch (\Exception $e) {
            return redirect()->route('cpanel.boveda.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request, $id)
    {
        try {

            $boveda = DB::connection('sqlsrv')
                ->table('Boveda')
                ->where('BovedaId', $id)
                ->where('Estatus', 0)
                ->first();

            if (!$boveda) {
                return redirect()->route('cpanel.boveda.index')
                    ->with('error', 'La bóveda no existe o ya está cerrada');
            }

            // 🔹 Normalizar fecha para FechaDeposito
            $fechaDeposito = Carbon::parse($boveda->Fecha)->format('Y-m-d');

            DB::connection('sqlsrv')->beginTransaction();

            try {
                // 1. Actualizar observación
                DB::connection('sqlsrv')->table('Boveda')
                    ->where('BovedaId', $id)
                    ->update(['Observacion' => $request->observacion]);

                // 2. Eliminar denominaciones anteriores
                DB::connection('sqlsrv')->table('BovedaDenominacionDivisa')->where('BovedaId', $id)->delete();
                DB::connection('sqlsrv')->table('BovedaDenominacionBs')->where('BovedaId', $id)->delete();

                // 3. Eliminar conciliaciones anteriores
                DB::connection('sqlsrv')->table('BovedaConciliacionPDV')->where('BovedaId', $id)->delete();
                DB::connection('sqlsrv')->table('BovedaConciliacionOtros')->where('BovedaId', $id)->delete();
                DB::connection('sqlsrv')->table('BovedaConciliacionEfectivo')->where('BovedaId', $id)->delete();

                // 4. Reinsertar denominaciones
                if ($request->has('denominaciones')) {
                    foreach ($request->denominaciones as $sucId => $denominaciones) {
                        if (isset($denominaciones['divisa'])) {
                            foreach ($denominaciones['divisa'] as $item) {
                                if (($item['cantidad'] ?? 0) > 0) {
                                    DB::connection('sqlsrv')->table('BovedaDenominacionDivisa')->insert([
                                        'BovedaId' => $id,
                                        'SucursalId' => $sucId,
                                        'Denominacion' => $item['denominacion'],
                                        'Cantidad' => $item['cantidad'],
                                        'MontoTotal' => $item['denominacion'] * $item['cantidad'],
                                        'FechaCreacion' => Carbon::now()
                                    ]);
                                }
                            }
                        }
                        if (isset($denominaciones['bs'])) {
                            foreach ($denominaciones['bs'] as $item) {
                                if (($item['cantidad'] ?? 0) > 0) {
                                    DB::connection('sqlsrv')->table('BovedaDenominacionBs')->insert([
                                        'BovedaId' => $id,
                                        'SucursalId' => $sucId,
                                        'Denominacion' => $item['denominacion'],
                                        'Cantidad' => $item['cantidad'],
                                        'MontoTotal' => $item['denominacion'] * $item['cantidad'],
                                        'FechaCreacion' => Carbon::now()
                                    ]);
                                }
                            }
                        }
                    }
                }

                $tieneDiferencias = false;

                // 5. Conciliación Efectivo
                if ($request->has('conciliacion_efectivo')) {
                    foreach ($request->conciliacion_efectivo as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionEfectivo')->insert([
                            'BovedaId' => $id,
                            'SucursalId' => $item['sucursal_id'],
                            'CierreDiarioId' => $item['cierre_diario_id'] ?? null,
                            'Tipo' => $item['tipo'],
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 6. Conciliación PDV
                if ($request->has('conciliacion_pdv')) {
                    foreach ($request->conciliacion_pdv as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionPDV')->insert([
                            'BovedaId' => $id,
                            'SucursalId' => $item['sucursal_id'],
                            'PuntoDeVentaId' => $item['punto_venta_id'],
                            'CierreDiarioId' => $item['cierre_diario_id'] ?? null,
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'FechaDeposito' => $fechaDeposito,   // 🔹 normalizado
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 7. Conciliación Otros
                if ($request->has('conciliacion_otros')) {
                    foreach ($request->conciliacion_otros as $item) {
                        $montoSistema = (float) ($item['monto_sistema'] ?? 0);
                        $montoDepositado = (float) ($item['monto_depositado'] ?? 0);
                        $diferencia = $montoSistema - $montoDepositado;
                        $tieneDiferencia = abs($diferencia) > 0.01;
                        
                        if ($tieneDiferencia) $tieneDiferencias = true;

                        DB::connection('sqlsrv')->table('BovedaConciliacionOtros')->insert([
                            'BovedaId' => $id,
                            'SucursalId' => $item['sucursal_id'],
                            'Tipo' => $item['tipo'],
                            'MontoSistema' => $montoSistema,
                            'MontoDepositado' => $montoDepositado,
                            'Diferencia' => $diferencia,
                            'FechaDeposito' => $fechaDeposito,   // 🔹 normalizado
                            'Observacion' => $item['observacion'] ?? null,
                            'TieneDiferencia' => $tieneDiferencia ? 1 : 0,
                            'FechaCreacion' => Carbon::now()
                        ]);
                    }
                }

                // 8. Actualizar estatus de conciliación
                $estatusConciliacion = $tieneDiferencias ? 2 : 1;
                DB::connection('sqlsrv')->table('Boveda')
                    ->where('BovedaId', $id)
                    ->update(['EstatusConciliacion' => $estatusConciliacion]);

                DB::connection('sqlsrv')->commit();

                return redirect()->route('cpanel.boveda.detalle', $id)
                    ->with('success', 'Cierre Diario Bóveda actualizado exitosamente');

            } catch (\Exception $e) {
                DB::connection('sqlsrv')->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::actualizar: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function diferencias(Request $request)
    {
        try {

            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Diferencias Bóveda'
            ]);

            // Obtener todas las bóvedas con diferencias
            $bovedasConDiferencias = DB::connection('sqlsrv')
                ->table('Boveda as b')
                ->leftJoin('Sucursales as s', 'b.SucursalId', '=', 's.ID')
                ->where('b.EstatusConciliacion', 2) // Con diferencia
                ->orderBy('b.Fecha', 'desc')
                ->select(['b.*', 's.Nombre as sucursal_nombre'])
                ->get();

            // Para cada bóveda, obtener el detalle de las diferencias
            foreach ($bovedasConDiferencias as $boveda) {
                $boveda->diferenciasPDV = DB::connection('sqlsrv')
                    ->table('BovedaConciliacionPDV as bcp')
                    ->leftJoin('PuntosDeVenta as pdv', 'bcp.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
                    ->leftJoin('Sucursales as s', 'bcp.SucursalId', '=', 's.ID')
                    ->where('bcp.BovedaId', $boveda->BovedaId)
                    ->where('bcp.TieneDiferencia', 1)
                    ->select([
                        'bcp.*',
                        'pdv.Descripcion as pdv_descripcion',
                        'pdv.Codigo as pdv_codigo',
                        's.Nombre as sucursal_nombre'
                    ])
                    ->get();

                $boveda->diferenciasOtros = DB::connection('sqlsrv')
                    ->table('BovedaConciliacionOtros as bco')
                    ->leftJoin('Sucursales as s', 'bco.SucursalId', '=', 's.ID')
                    ->where('bco.BovedaId', $boveda->BovedaId)
                    ->where('bco.TieneDiferencia', 1)
                    ->select(['bco.*', 's.Nombre as sucursal_nombre'])
                    ->get();

                $boveda->diferenciasEfectivo = DB::connection('sqlsrv')
                    ->table('BovedaConciliacionEfectivo as bce')
                    ->leftJoin('Sucursales as s', 'bce.SucursalId', '=', 's.ID')
                    ->where('bce.BovedaId', $boveda->BovedaId)
                    ->where('bce.TieneDiferencia', 1)
                    ->select(['bce.*', 's.Nombre as sucursal_nombre'])
                    ->get();
            }

            return view('cpanel.boveda.diferencias', [
                'bovedasConDiferencias' => $bovedasConDiferencias
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::diferencias: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las diferencias: ' . $e->getMessage());
        }
    }

    /**
     * Listado de préstamos a sucursales con filtros
     */
    public function prestamos(Request $request)
    {
        try {

            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Préstamo a Sucursal'
            ]);

            // ================================================
            // FILTROS
            // ================================================
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
            $estatus = $request->input('estatus', 'todos');
            $sucursalId = $request->input('sucursal_id', '');
            $tipoMoneda = $request->input('tipo_moneda', 'todos');

            // ================================================
            // QUERY CON FILTROS
            // ================================================
            $query = DB::connection('sqlsrv')
                ->table('BovedaPrestamo as bp')
                ->leftJoin('Sucursales as s', 'bp.SucursalId', '=', 's.ID')
                ->whereDate('bp.FechaPrestamo', '>=', $fechaInicio)
                ->whereDate('bp.FechaPrestamo', '<=', $fechaFin)
                ->orderBy('bp.FechaPrestamo', 'desc')
                ->orderBy('bp.BovedaPrestamoId', 'desc')
                ->select([
                    'bp.*',
                    's.Nombre as sucursal_nombre'
                ]);

            if ($estatus !== 'todos' && $estatus !== '') {
                $query->where('bp.Estatus', (int) $estatus);
            }

            if (!empty($sucursalId)) {
                $query->where('bp.SucursalId', $sucursalId);
            }

            if ($tipoMoneda !== 'todos' && $tipoMoneda !== '') {
                $query->where('bp.TipoMoneda', (int) $tipoMoneda);
            }

            $prestamos = $query->get();

            $estatusTexto = [0 => 'Pendiente', 1 => 'Devuelto', 2 => 'Anulado'];
            $estatusBadge = [0 => 'warning', 1 => 'success', 2 => 'danger'];
            $monedaTexto = [0 => 'Divisas (USD)', 1 => 'Bolívares (Bs.)'];

            $prestamos->transform(function ($item) use ($estatusTexto, $estatusBadge, $monedaTexto) {
                $item->EstatusTexto = $estatusTexto[$item->Estatus] ?? 'Desconocido';
                $item->EstatusBadge = $estatusBadge[$item->Estatus] ?? 'secondary';
                $item->MonedaTexto = $monedaTexto[$item->TipoMoneda ?? 0] ?? 'Divisas';
                $item->FechaFormateada = $item->FechaPrestamo ? Carbon::parse($item->FechaPrestamo)->format('d/m/Y') : 'N/A';
                return $item;
            });

            // Sucursales para filtro
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            // ================================================
            // TOTALES DEL RANGO FILTRADO
            // ================================================
            $totales = [
                'total_prestamos' => $prestamos->count(),
                'prestamos_pendientes' => $prestamos->where('Estatus', 0)->count(),
                'prestamos_devueltos' => $prestamos->where('Estatus', 1)->count(),
                'total_prestado_divisa' => $prestamos->where('TipoMoneda', 0)->sum('MontoDivisa'),
                'total_prestado_bs' => $prestamos->where('TipoMoneda', 1)->sum('MontoBs'),
                'total_pendiente_divisa' => $prestamos->where('Estatus', 0)->where('TipoMoneda', 0)->sum('SaldoPendiente'),
                'total_pendiente_bs' => $prestamos->where('Estatus', 0)->where('TipoMoneda', 1)->sum('SaldoPendiente'),
            ];

            return view('cpanel.boveda.prestamos', [
                'prestamos' => $prestamos,
                'sucursales' => $sucursales,
                'totales' => $totales,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'estatusFiltro' => $estatus,
                'sucursalFiltro' => $sucursalId,
                'tipoMonedaFiltro' => $tipoMoneda
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::prestamos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los préstamos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para crear un nuevo préstamo
     */
    public function crearPrestamo(Request $request)
    {
        try {
            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Préstamo a Sucursal'
            ]);

            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->where('Tipo', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            $tasa = $this->obtenerTasaCambioActual();

            return view('cpanel.boveda.crear_prestamo', [
                'sucursales' => $sucursales,
                'denominacionesDivisa' => $this->denominacionesDivisa,
                'denominacionesBs' => $this->denominacionesBs,
                'tasaCambio' => $tasa ? $tasa->Valor : 0
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::crearPrestamo: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Guardar un nuevo préstamo
     */
    public function guardarPrestamo(Request $request)
    {
        try {

            $request->validate([
                'sucursal_id' => 'required|exists:Sucursales,ID',
                'fecha_prestamo' => 'required|date',
                'tipo_moneda' => 'required|in:0,1',
                'monto' => 'required|numeric|min:0.01',
                'observacion' => 'nullable|string|max:500',
                'denominaciones' => 'nullable|array'
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            try {
                $tasa = $this->obtenerTasaCambioActual();
                $tipoMoneda = (int) $request->tipo_moneda;
                $monto = (float) $request->monto;

                if ($tipoMoneda == 0) {
                    // Divisas
                    $montoDivisa = $monto;
                    $montoBs = $monto * ($tasa->Valor ?? 0);
                } else {
                    // Bolívares
                    $montoBs = $monto;
                    $montoDivisa = ($tasa->Valor ?? 0) > 0 ? $monto / $tasa->Valor : 0;
                }

                // Insertar préstamo
                $prestamoId = DB::connection('sqlsrv')->table('BovedaPrestamo')->insertGetId([
                    'BovedaId' => null,
                    'SucursalId' => $request->sucursal_id,
                    'MontoDivisa' => $montoDivisa,
                    'MontoBs' => $montoBs,
                    'TasaCambio' => $tasa->Valor ?? 0,
                    'TipoMoneda' => $tipoMoneda,
                    'TipoMovimiento' => 1, // 1 = Préstamo
                    'Estatus' => 0, // Pendiente
                    'FechaPrestamo' => Carbon::parse($request->fecha_prestamo),
                    'FechaEntrega' => Carbon::parse($request->fecha_prestamo),
                    'SaldoPendiente' => $monto,
                    'Observacion' => $request->observacion,
                    'UsuarioCreacion' => auth()->user()->id ?? null,
                    'FechaCreacion' => Carbon::now()
                ]);

                // Insertar denominaciones
                if ($request->has('denominaciones')) {
                    foreach ($request->denominaciones as $item) {
                        if (($item['cantidad'] ?? 0) > 0) {
                            DB::connection('sqlsrv')->table('BovedaPrestamoDenominacion')->insert([
                                'BovedaPrestamoId' => $prestamoId,
                                'Denominacion' => $item['denominacion'],
                                'Cantidad' => $item['cantidad'],
                                'MontoTotal' => $item['denominacion'] * $item['cantidad'],
                                'FechaCreacion' => Carbon::now()
                            ]);
                        }
                    }
                }

                DB::connection('sqlsrv')->commit();

                return redirect()->route('cpanel.boveda.prestamos')
                    ->with('success', 'Préstamo registrado exitosamente');

            } catch (\Exception $e) {
                DB::connection('sqlsrv')->rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput()->with('error', 'Complete los datos necesarios');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de un préstamo
     */
    public function detallePrestamo($id)
    {
        try {
            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Préstamo a Sucursal'
            ]);

            $prestamo = DB::connection('sqlsrv')
                ->table('BovedaPrestamo as bp')
                ->leftJoin('Sucursales as s', 'bp.SucursalId', '=', 's.ID')
                ->where('bp.BovedaPrestamoId', $id)
                ->select(['bp.*', 's.Nombre as sucursal_nombre'])
                ->first();

            if (!$prestamo) {
                return redirect()->route('cpanel.boveda.prestamos')
                    ->with('error', 'Préstamo no encontrado');
            }

            $denominaciones = DB::connection('sqlsrv')
                ->table('BovedaPrestamoDenominacion')
                ->where('BovedaPrestamoId', $id)
                ->orderBy('Denominacion', 'desc')
                ->get();

            $estatusTexto = [0 => 'Pendiente', 1 => 'Devuelto', 2 => 'Anulado'];
            $estatusBadge = [0 => 'warning', 1 => 'success', 2 => 'danger'];
            $monedaTexto = [0 => 'Divisas (USD)', 1 => 'Bolívares (Bs.)'];
            $monedaSimbolo = [0 => '$', 1 => 'Bs.'];

            $prestamo->EstatusTexto = $estatusTexto[$prestamo->Estatus] ?? 'Desconocido';
            $prestamo->EstatusBadge = $estatusBadge[$prestamo->Estatus] ?? 'secondary';
            $prestamo->MonedaTexto = $monedaTexto[$prestamo->TipoMoneda ?? 0] ?? 'Divisas';
            $prestamo->MonedaSimbolo = $monedaSimbolo[$prestamo->TipoMoneda ?? 0] ?? '$';
            $prestamo->FechaFormateada = $prestamo->FechaPrestamo ? Carbon::parse($prestamo->FechaPrestamo)->format('d/m/Y') : 'N/A';
            $prestamo->FechaEntregaFormateada = $prestamo->FechaEntrega ? Carbon::parse($prestamo->FechaEntrega)->format('d/m/Y') : 'N/A';
            $prestamo->FechaDevolucionFormateada = $prestamo->FechaDevolucion ? Carbon::parse($prestamo->FechaDevolucion)->format('d/m/Y') : 'N/A';

            // Total denominaciones
            $totalDenominaciones = $denominaciones->sum('MontoTotal');

            return view('cpanel.boveda.detalle_prestamo', [
                'prestamo' => $prestamo,
                'denominaciones' => $denominaciones,
                'totalDenominaciones' => $totalDenominaciones
            ]);

        } catch (\Exception $e) {
            return redirect()->route('cpanel.boveda.prestamos')
                ->with('error', 'Error al cargar el detalle');
        }
    }

    /**
     * Devolver un préstamo
     */
    public function devolverPrestamo($id)
    {
        try {

            $prestamo = DB::connection('sqlsrv')
                ->table('BovedaPrestamo')
                ->where('BovedaPrestamoId', $id)
                ->first();

            if (!$prestamo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Préstamo no encontrado'
                ], 404);
            }

            if ($prestamo->Estatus == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este préstamo ya fue devuelto'
                ], 400);
            }

            DB::connection('sqlsrv')->table('BovedaPrestamo')
                ->where('BovedaPrestamoId', $id)
                ->update([
                    'Estatus' => 1,
                    'FechaDevolucion' => Carbon::now(),
                    'SaldoPendiente' => 0
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Préstamo marcado como devuelto exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::devolverPrestamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al devolver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista consolidada de la Bóveda (todos los totales) con filtros de fecha
     */
    public function consolidado(Request $request)
    {
        try {

            session([
                'menu_active' => 'Bóveda',
                'submenu_active' => 'Consolidado Bóveda'
            ]);

            // ================================================
            // FILTROS DE FECHA
            // ================================================
            $fechaMinima = '2026-09-01';

            $defaultInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            if ($defaultInicio < $fechaMinima) {
                $defaultInicio = $fechaMinima;
            }

            $fechaInicio = $request->input('fecha_inicio', $defaultInicio);
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            if ($fechaInicio < $fechaMinima) {
                $fechaInicio = $fechaMinima;
            }

            $sucursalId = $request->input('sucursal_id', '');

            // ================================================
            // FILTRAR BÓVEDAS POR FECHA Y SUCURSAL
            // ================================================
            $bovedasFiltradas = DB::connection('sqlsrv')
                ->table('Boveda')
                ->whereDate('Fecha', '>=', $fechaInicio)
                ->whereDate('Fecha', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('SucursalId', $sucursalId))
                ->pluck('BovedaId')
                ->toArray();

            // Sucursales para filtro
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            // Si no hay bóvedas en el rango, devolver vacío
            if (empty($bovedasFiltradas)) {
                return view('cpanel.boveda.consolidado', [
                    'divisasPorDenominacion' => collect(),
                    'bsPorDenominacion' => collect(),
                    'puntosVentaTotales' => collect(),
                    'otrosTotales' => collect(),
                    'efectivoTotales' => collect(),
                    'prestamosPendientes' => collect(),
                    'totalDivisas' => 0,
                    'totalBs' => 0,
                    'totalBsUSD' => 0,
                    'totalDivisasContadas' => 0,
                    'totalBsContados' => 0,
                    'totalBsContadosUSD' => 0,
                    'totalPdvSistema' => 0,
                    'totalPdvSistemaUSD' => 0,
                    'totalPdvDepositado' => 0,
                    'totalPdvDepositadoUSD' => 0,
                    'totalPrestamosPendientesUSD' => 0,
                    'totalPrestamosPendientesBs' => 0,
                    'disponibleDivisas' => 0,
                    'disponibleBs' => 0,
                    'disponibleBsUSD' => 0,
                    'totalConsolidadoBs' => 0,
                    'totalConsolidadoBsUSD' => 0,
                    'totalOtrosBs' => 0,
                    'totalOtrosBsUSD' => 0,
                    'totalEfectivoBs' => 0,
                    'totalEfectivoBsUSD' => 0,
                    'tasaValor' => 0,
                    'historialBovedas' => collect(),
                    'fechaInicio' => $fechaInicio,
                    'fechaFin' => $fechaFin,
                    'sucursalId' => $sucursalId,
                    'sucursales' => $sucursales,
                    'gastosPeriodo' => collect(),
                    'gastosPorCategoria' => collect(),
                    'totalGastosUSD' => 0,
                    'totalGastosBs' => 0,
                    'totalConsolidadoBsFinal' => 0,
                    'totalConsolidadoBsUSDFinal' => 0,
                ]);
            }

            // ================================================
            // 1. DIVISAS POR DENOMINACIÓN (CONTADO - PRESTADO)
            // ================================================
            $divisasContadas = DB::connection('sqlsrv')
                ->table('BovedaDenominacionDivisa')
                ->whereIn('BovedaId', $bovedasFiltradas)
                ->select(
                    'Denominacion',
                    DB::raw('SUM(Cantidad) as CantidadContada'),
                    DB::raw('SUM(MontoTotal) as MontoContado')
                )
                ->groupBy('Denominacion')
                ->get()
                ->keyBy('Denominacion');

            $divisasPrestadas = DB::connection('sqlsrv')
                ->table('BovedaPrestamoDenominacion as bpd')
                ->join('BovedaPrestamo as bp', 'bpd.BovedaPrestamoId', '=', 'bp.BovedaPrestamoId')
                ->where('bp.Estatus', 0)
                ->where('bp.TipoMoneda', 0)
                ->whereDate('bp.FechaPrestamo', '>=', $fechaInicio)
                ->whereDate('bp.FechaPrestamo', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('bp.SucursalId', $sucursalId))
                ->select(
                    'bpd.Denominacion',
                    DB::raw('SUM(bpd.Cantidad) as CantidadPrestada'),
                    DB::raw('SUM(bpd.MontoTotal) as MontoPrestado')
                )
                ->groupBy('bpd.Denominacion')
                ->get()
                ->keyBy('Denominacion');

            $divisasPorDenominacion = $divisasContadas->map(function ($item, $denominacion) use ($divisasPrestadas) {
                $prestado = $divisasPrestadas->get($denominacion);
                $cantPrestada  = $prestado->CantidadPrestada ?? 0;
                $montoPrestado = $prestado->MontoPrestado ?? 0;

                $item->CantidadPrestada = $cantPrestada;
                $item->MontoPrestado    = $montoPrestado;
                $item->CantidadNeta     = $item->CantidadContada - $cantPrestada;
                $item->MontoNeto        = $item->MontoContado - $montoPrestado;

                // Divisas ya están en USD → USD = mismo monto
                $item->MontoContadoUSD = $item->MontoContado;
                $item->MontoNetoUSD    = $item->MontoNeto;

                // Retrocompatibilidad con la vista actual
                $item->TotalCantidad = $item->CantidadContada;
                $item->TotalMonto    = $item->MontoContado;

                return $item;
            })->values();

            // Denominaciones que solo tienen préstamo (no conteo)
            $denominacionesExtra = $divisasPrestadas->keys()->diff($divisasContadas->keys());
            foreach ($denominacionesExtra as $den) {
                $prestado = $divisasPrestadas->get($den);
                $divisasPorDenominacion->push((object)[
                    'Denominacion'      => $den,
                    'CantidadContada'   => 0,
                    'MontoContado'      => 0,
                    'MontoContadoUSD'   => 0,
                    'CantidadPrestada'  => $prestado->CantidadPrestada,
                    'MontoPrestado'     => $prestado->MontoPrestado,
                    'CantidadNeta'      => -$prestado->CantidadPrestada,
                    'MontoNeto'         => -$prestado->MontoPrestado,
                    'MontoNetoUSD'      => -$prestado->MontoPrestado,
                    'TotalCantidad'     => 0,
                    'TotalMonto'        => 0,
                ]);
            }

            $divisasPorDenominacion = $divisasPorDenominacion->sortByDesc('Denominacion')->values();

            // ================================================
            // 2. BOLÍVARES POR DENOMINACIÓN (CONTADO - PRESTADO + CONVERSIÓN USD)
            // ================================================
            $bsContados = DB::connection('sqlsrv')
                ->table('BovedaDenominacionBs as bdb')
                ->join('Boveda as bo', 'bdb.BovedaId', '=', 'bo.BovedaId')
                ->leftJoin('DivisaValor as dv', 'bo.DivisaValorId', '=', 'dv.ID')
                ->whereIn('bdb.BovedaId', $bovedasFiltradas)
                ->select(
                    'bdb.Denominacion',
                    DB::raw('SUM(bdb.Cantidad) as CantidadContada'),
                    DB::raw('SUM(bdb.MontoTotal) as MontoContado'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bdb.MontoTotal / dv.Valor ELSE 0 END) as MontoContadoUSD')
                )
                ->groupBy('bdb.Denominacion')
                ->get()
                ->keyBy('Denominacion');

            $bsPrestados = DB::connection('sqlsrv')
                ->table('BovedaPrestamoDenominacion as bpd')
                ->join('BovedaPrestamo as bp', 'bpd.BovedaPrestamoId', '=', 'bp.BovedaPrestamoId')
                ->where('bp.Estatus', 0)
                ->where('bp.TipoMoneda', 1)
                ->whereDate('bp.FechaPrestamo', '>=', $fechaInicio)
                ->whereDate('bp.FechaPrestamo', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('bp.SucursalId', $sucursalId))
                ->select(
                    'bpd.Denominacion',
                    DB::raw('SUM(bpd.Cantidad) as CantidadPrestada'),
                    DB::raw('SUM(bpd.MontoTotal) as MontoPrestado')
                )
                ->groupBy('bpd.Denominacion')
                ->get()
                ->keyBy('Denominacion');

            $bsPrestadosUSD = DB::connection('sqlsrv')
                ->table('BovedaPrestamoDenominacion as bpd')
                ->join('BovedaPrestamo as bp', 'bpd.BovedaPrestamoId', '=', 'bp.BovedaPrestamoId')
                ->where('bp.Estatus', 0)
                ->where('bp.TipoMoneda', 1)
                ->whereDate('bp.FechaPrestamo', '>=', $fechaInicio)
                ->whereDate('bp.FechaPrestamo', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('bp.SucursalId', $sucursalId))
                ->select(
                    'bpd.Denominacion',
                    DB::raw('SUM(bp.MontoDivisa) as MontoPrestadoUSD')
                )
                ->groupBy('bpd.Denominacion')
                ->get()
                ->keyBy('Denominacion');

            $bsPorDenominacion = $bsContados->map(function ($item, $denominacion) use ($bsPrestados, $bsPrestadosUSD) {
                $prestado    = $bsPrestados->get($denominacion);
                $prestadoUSD = $bsPrestadosUSD->get($denominacion);

                $cantPrestada     = $prestado->CantidadPrestada ?? 0;
                $montoPrestado    = $prestado->MontoPrestado ?? 0;
                $montoPrestadoUSD = $prestadoUSD->MontoPrestadoUSD ?? 0;

                $item->CantidadPrestada = $cantPrestada;
                $item->MontoPrestado    = $montoPrestado;
                $item->MontoPrestadoUSD = $montoPrestadoUSD;
                $item->CantidadNeta     = $item->CantidadContada - $cantPrestada;
                $item->MontoNeto        = $item->MontoContado - $montoPrestado;
                $item->MontoNetoUSD     = $item->MontoContadoUSD - $montoPrestadoUSD;

                $item->TotalCantidad = $item->CantidadContada;
                $item->TotalMonto    = $item->MontoContado;

                return $item;
            })->values();

            $denominacionesExtraBs = $bsPrestados->keys()->diff($bsContados->keys());
            foreach ($denominacionesExtraBs as $den) {
                $prestado    = $bsPrestados->get($den);
                $prestadoUSD = $bsPrestadosUSD->get($den);

                $montoPrestadoUSD = $prestadoUSD->MontoPrestadoUSD ?? 0;

                $bsPorDenominacion->push((object)[
                    'Denominacion'      => $den,
                    'CantidadContada'   => 0,
                    'MontoContado'      => 0,
                    'MontoContadoUSD'   => 0,
                    'CantidadPrestada'  => $prestado->CantidadPrestada,
                    'MontoPrestado'     => $prestado->MontoPrestado,
                    'MontoPrestadoUSD'  => $montoPrestadoUSD,
                    'CantidadNeta'      => -$prestado->CantidadPrestada,
                    'MontoNeto'         => -$prestado->MontoPrestado,
                    'MontoNetoUSD'      => -$montoPrestadoUSD,
                    'TotalCantidad'     => 0,
                    'TotalMonto'        => 0,
                ]);
            }

            $bsPorDenominacion = $bsPorDenominacion->sortByDesc('Denominacion')->values();

            // ================================================
            // 3. TOTALES POR PUNTO DE VENTA (Bs + estimado USD)
            // ================================================
            $puntosVentaTotales = DB::connection('sqlsrv')
                ->table('BovedaConciliacionPDV as bcp')
                ->join('PuntosDeVenta as pdv', 'bcp.PuntoDeVentaId', '=', 'pdv.PuntoDeVentaId')
                ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                ->leftJoin('Sucursales as s', 'pdv.SucursalId', '=', 's.ID')
                ->join('Boveda as bo', 'bcp.BovedaId', '=', 'bo.BovedaId')
                ->leftJoin('DivisaValor as dv', 'bo.DivisaValorId', '=', 'dv.ID')
                ->whereIn('bcp.BovedaId', $bovedasFiltradas)
                ->select(
                    'pdv.PuntoDeVentaId',
                    'pdv.Descripcion as pdv_descripcion',
                    'pdv.Codigo as pdv_codigo',
                    'b.Nombre as banco_nombre',
                    's.Nombre as sucursal_nombre',
                    DB::raw('SUM(bcp.MontoSistema) as TotalSistema'),
                    DB::raw('SUM(bcp.MontoDepositado) as TotalDepositado'),
                    DB::raw('SUM(bcp.Diferencia) as TotalDiferencia'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bcp.MontoSistema / dv.Valor ELSE 0 END) as TotalSistemaUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bcp.MontoDepositado / dv.Valor ELSE 0 END) as TotalDepositadoUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bcp.Diferencia / dv.Valor ELSE 0 END) as TotalDiferenciaUSD')
                )
                ->groupBy(
                    'pdv.PuntoDeVentaId',
                    'pdv.Descripcion',
                    'pdv.Codigo',
                    'b.Nombre',
                    's.Nombre'
                )
                ->orderBy('s.Nombre')
                ->orderBy('pdv.Descripcion')
                ->get();

            // ================================================
            // 4. TOTALES POR OTROS CONCEPTOS (Bs + estimado USD)
            // ================================================
            $tiposOtros = [
                1 => 'Biopago',
                2 => 'Transferencia',
                3 => 'Cashea',
                4 => 'Zelle'
            ];

            $otrosTotales = DB::connection('sqlsrv')
                ->table('BovedaConciliacionOtros as bco')
                ->join('Boveda as bo', 'bco.BovedaId', '=', 'bo.BovedaId')
                ->leftJoin('DivisaValor as dv', 'bo.DivisaValorId', '=', 'dv.ID')
                ->whereIn('bco.BovedaId', $bovedasFiltradas)
                ->select(
                    'bco.Tipo',
                    DB::raw('SUM(bco.MontoSistema) as TotalSistema'),
                    DB::raw('SUM(bco.MontoDepositado) as TotalDepositado'),
                    DB::raw('SUM(bco.Diferencia) as TotalDiferencia'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bco.MontoSistema / dv.Valor ELSE 0 END) as TotalSistemaUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bco.MontoDepositado / dv.Valor ELSE 0 END) as TotalDepositadoUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bco.Diferencia / dv.Valor ELSE 0 END) as TotalDiferenciaUSD')
                )
                ->groupBy('bco.Tipo')
                ->get()
                ->map(function ($item) use ($tiposOtros) {
                    $item->TipoNombre = $tiposOtros[$item->Tipo] ?? 'Otro';
                    $item->Moneda     = $item->Tipo == 4 ? 'USD' : 'Bs';
                    $item->Simbolo    = $item->Tipo == 4 ? '$' : 'Bs.';

                    // Zelle ya está en USD → USD = mismo monto
                    if ($item->Tipo == 4) {
                        $item->TotalSistemaUSD    = $item->TotalSistema;
                        $item->TotalDepositadoUSD = $item->TotalDepositado;
                        $item->TotalDiferenciaUSD = $item->TotalDiferencia;
                    }
                    return $item;
                });

            // ================================================
            // 5. TOTALES DE EFECTIVO (Bs + estimado USD)
            // ================================================
            $efectivoTotales = DB::connection('sqlsrv')
                ->table('BovedaConciliacionEfectivo as bce')
                ->join('Boveda as bo', 'bce.BovedaId', '=', 'bo.BovedaId')
                ->leftJoin('DivisaValor as dv', 'bo.DivisaValorId', '=', 'dv.ID')
                ->whereIn('bce.BovedaId', $bovedasFiltradas)
                ->select(
                    'bce.Tipo',
                    DB::raw('SUM(bce.MontoSistema) as TotalSistema'),
                    DB::raw('SUM(bce.MontoDepositado) as TotalDepositado'),
                    DB::raw('SUM(bce.Diferencia) as TotalDiferencia'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bce.MontoSistema / dv.Valor ELSE 0 END) as TotalSistemaUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bce.MontoDepositado / dv.Valor ELSE 0 END) as TotalDepositadoUSD'),
                    DB::raw('SUM(CASE WHEN ISNULL(dv.Valor, 0) > 0 THEN bce.Diferencia / dv.Valor ELSE 0 END) as TotalDiferenciaUSD')
                )
                ->groupBy('bce.Tipo')
                ->get()
                ->map(function ($item) {
                    $item->TipoNombre = $item->Tipo == 1 ? 'Divisas' : 'Bolívares';
                    $item->Moneda     = $item->Tipo == 1 ? 'USD' : 'Bs';
                    $item->Simbolo    = $item->Tipo == 1 ? '$' : 'Bs.';

                    // Divisas ya están en USD → USD = mismo monto
                    if ($item->Tipo == 1) {
                        $item->TotalSistemaUSD    = $item->TotalSistema;
                        $item->TotalDepositadoUSD = $item->TotalDepositado;
                        $item->TotalDiferenciaUSD = $item->TotalDiferencia;
                    }
                    return $item;
                });

            // ================================================
            // 6. PRÉSTAMOS PENDIENTES POR SUCURSAL (SIN CONVERSIÓN)
            // ================================================
            $prestamosPendientes = DB::connection('sqlsrv')
                ->table('BovedaPrestamo as bp')
                ->join('Sucursales as s', 'bp.SucursalId', '=', 's.ID')
                ->where('bp.Estatus', 0)
                ->whereDate('bp.FechaPrestamo', '>=', $fechaInicio)
                ->whereDate('bp.FechaPrestamo', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('bp.SucursalId', $sucursalId))
                ->select(
                    's.ID as SucursalId',
                    's.Nombre as sucursal_nombre',
                    DB::raw('COUNT(bp.BovedaPrestamoId) as CantidadPrestamos'),
                    DB::raw('SUM(CASE WHEN bp.TipoMoneda = 0 THEN bp.SaldoPendiente ELSE 0 END) as TotalPendienteUSD'),
                    DB::raw('SUM(CASE WHEN bp.TipoMoneda = 1 THEN bp.SaldoPendiente ELSE 0 END) as TotalPendienteBs')
                )
                ->groupBy('s.ID', 's.Nombre')
                ->orderBy('s.Nombre')
                ->get();

            // ================================================
            // 7. TOTALES GENERALES
            // ================================================
            // Divisas (ya en USD)
            $totalDivisasContadas = $divisasPorDenominacion->sum('MontoContado');
            $totalDivisas         = $divisasPorDenominacion->sum('MontoNeto');

            // Bolívares (Bs + USD)
            $totalBsContados    = $bsPorDenominacion->sum('MontoContado');
            $totalBsContadosUSD = $bsPorDenominacion->sum('MontoContadoUSD');
            $totalBs            = $bsPorDenominacion->sum('MontoNeto');
            $totalBsUSD         = $bsPorDenominacion->sum('MontoNetoUSD');

            // Puntos de venta
            $totalPdvSistema       = $puntosVentaTotales->sum('TotalSistema');
            $totalPdvSistemaUSD    = $puntosVentaTotales->sum('TotalSistemaUSD');
            $totalPdvDepositado    = $puntosVentaTotales->sum('TotalDepositado');
            $totalPdvDepositadoUSD = $puntosVentaTotales->sum('TotalDepositadoUSD');

            // Préstamos
            $totalPrestamosPendientesUSD = $prestamosPendientes->sum('TotalPendienteUSD');
            $totalPrestamosPendientesBs  = $prestamosPendientes->sum('TotalPendienteBs');

            // Disponible
            $disponibleDivisas = $totalDivisas;
            $disponibleBs      = $totalBs;
            $disponibleBsUSD   = $totalBsUSD;

            // ================================================
            // 7.1 TOTALES ESPECÍFICOS PARA "CONSOLIDADO EN BS"
            // ================================================
            // Otros: solo los que están en Bs (Biopago, Transferencia, Cashea)
            $totalOtrosBs    = $otrosTotales->where('Moneda', 'Bs')->sum('TotalSistema');
            $totalOtrosBsUSD = $otrosTotales->where('Moneda', 'Bs')->sum('TotalSistemaUSD');

            // Efectivo: solo el tipo 2 (Bolívares)
            $efectivoBsRow      = $efectivoTotales->where('Tipo', 2)->first();
            $totalEfectivoBs    = $efectivoBsRow->TotalSistema ?? 0;
            $totalEfectivoBsUSD = $efectivoBsRow->TotalSistemaUSD ?? 0;

            // Total consolidado en Bs = físico neto + PDV + Otros Bs + Efectivo Bs
            $totalConsolidadoBs    = $disponibleBs + $totalPdvSistema + $totalOtrosBs + $totalEfectivoBs;
            $totalConsolidadoBsUSD = $disponibleBsUSD + $totalPdvSistemaUSD + $totalOtrosBsUSD + $totalEfectivoBsUSD;

            $tasa = $this->obtenerTasaCambioActual();
            $tasaValor = $tasa ? $tasa->Valor : 0;

            // ================================================
            // 7.2 GASTOS Y PAGOS A PROVEEDORES DEL PERÍODO
            //     Tipo 0 = Pago Proveedor Mercancía
            //     Tipo 2 = Gasto
            //     Tipo 3 = Gasto de Caja
            //     Tipo 5 = Pago Proveedor Servicio
            // ================================================
            $tiposSalidas = [0, 2, 3, 5];

            $gastosPeriodo = DB::connection('sqlsrv')
                ->table('Transacciones as t')
                ->leftJoin('Sucursales as s', 't.SucursalId', '=', 's.ID')
                ->whereIn('t.Tipo', $tiposSalidas)
                ->whereDate('t.Fecha', '>=', $fechaInicio)
                ->whereDate('t.Fecha', '<=', $fechaFin)
                ->select(
                    't.ID',
                    't.Fecha',
                    't.Descripcion',
                    't.Nombre as CategoriaNombre',
                    't.CategoriaId',
                    't.Tipo',
                    't.MontoDivisaAbonado',
                    't.MontoAbonado',
                    't.TasaDeCambio',
                    't.NumeroOperacion',
                    't.SucursalId',
                    's.Nombre as SucursalNombre'
                )
                ->orderBy('t.Fecha', 'desc')
                ->get();

            // Mapeo de tipos a etiquetas legibles
            $tiposDescripcion = [
                0 => 'Pago Proveedor Mercancía',
                2 => 'Gastos',
                3 => 'Gastos de Caja',
                5 => 'Pago Proveedor Servicio',
            ];

            // Totales
            $totalGastosUSD = $gastosPeriodo->sum(fn($g) => (float) ($g->MontoDivisaAbonado ?? 0));
            $totalGastosBs  = $gastosPeriodo->sum(fn($g) => (float) ($g->MontoAbonado ?? 0));

            // Agrupados por categoría (usa Nombre si existe, sino el tipo)
            $gastosPorCategoria = $gastosPeriodo
                ->groupBy(function ($g) use ($tiposDescripcion) {
                    // Si tiene Nombre (categoría de gasto), usarlo
                    if (!empty($g->CategoriaNombre)) {
                        return $g->CategoriaNombre;
                    }
                    // Si no, usar el nombre del tipo
                    return $tiposDescripcion[$g->Tipo] ?? 'Otro';
                })
                ->map(function ($items, $categoria) {
                    return (object) [
                        'Categoria' => $categoria,
                        'Cantidad'  => $items->count(),
                        'TotalUSD'  => $items->sum(fn($g) => (float) ($g->MontoDivisaAbonado ?? 0)),
                        'TotalBs'   => $items->sum(fn($g) => (float) ($g->MontoAbonado ?? 0)),
                    ];
                })
                ->sortByDesc('TotalUSD')
                ->values();

            // ================================================
            // 7.3 AJUSTAR EL TOTAL CONSOLIDADO RESTANDO SALIDAS
            // ================================================
            $totalConsolidadoBsFinal    = $totalConsolidadoBs - $totalGastosBs;
            $totalConsolidadoBsUSDFinal = $totalConsolidadoBsUSD - $totalGastosUSD;

            // ================================================
            // 8. HISTORIAL DE BÓVEDAS (FILTRADO)
            // ================================================
            $historialBovedas = DB::connection('sqlsrv')
                ->table('Boveda as b')
                ->leftJoin('Sucursales as s', 'b.SucursalId', '=', 's.ID')
                ->whereDate('b.Fecha', '>=', $fechaInicio)
                ->whereDate('b.Fecha', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('b.SucursalId', $sucursalId))
                ->orderBy('b.Fecha', 'desc')
                ->orderBy('b.BovedaId', 'desc')
                ->limit(10)
                ->select(['b.*', 's.Nombre as sucursal_nombre'])
                ->get()
                ->map(function ($item) {
                    $item->EstatusTexto = $item->Estatus == 0 ? 'Abierto' : 'Cerrado';
                    $item->EstatusBadge = $item->Estatus == 0 ? 'success' : 'secondary';
                    $item->ConciliacionTexto = $item->EstatusConciliacion == 1 ? 'Conciliado' :
                                            ($item->EstatusConciliacion == 2 ? 'Con Diferencia' : 'Pendiente');
                    $item->ConciliacionBadge = $item->EstatusConciliacion == 1 ? 'success' :
                                            ($item->EstatusConciliacion == 2 ? 'danger' : 'warning');
                    $item->FechaFormateada = $item->Fecha ? \Carbon\Carbon::parse($item->Fecha)->format('d/m/Y') : 'N/A';
                    return $item;
                });

            return view('cpanel.boveda.consolidado', [
                'divisasPorDenominacion' => $divisasPorDenominacion,
                'bsPorDenominacion' => $bsPorDenominacion,
                'puntosVentaTotales' => $puntosVentaTotales,
                'otrosTotales' => $otrosTotales,
                'efectivoTotales' => $efectivoTotales,
                'prestamosPendientes' => $prestamosPendientes,
                'totalDivisas' => $totalDivisas,
                'totalBs' => $totalBs,
                'totalBsUSD' => $totalBsUSD,
                'totalDivisasContadas' => $totalDivisasContadas,
                'totalBsContados' => $totalBsContados,
                'totalBsContadosUSD' => $totalBsContadosUSD,
                'totalPdvSistema' => $totalPdvSistema,
                'totalPdvSistemaUSD' => $totalPdvSistemaUSD,
                'totalPdvDepositado' => $totalPdvDepositado,
                'totalPdvDepositadoUSD' => $totalPdvDepositadoUSD,
                'totalPrestamosPendientesUSD' => $totalPrestamosPendientesUSD,
                'totalPrestamosPendientesBs' => $totalPrestamosPendientesBs,
                'disponibleDivisas' => $disponibleDivisas,
                'disponibleBs' => $disponibleBs,
                'disponibleBsUSD' => $disponibleBsUSD,
                'totalConsolidadoBs' => $totalConsolidadoBs,
                'totalConsolidadoBsUSD' => $totalConsolidadoBsUSD,
                'totalOtrosBs' => $totalOtrosBs,
                'totalOtrosBsUSD' => $totalOtrosBsUSD,
                'totalEfectivoBs' => $totalEfectivoBs,
                'totalEfectivoBsUSD' => $totalEfectivoBsUSD,
                'tasaValor' => $tasaValor,
                'historialBovedas' => $historialBovedas,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'sucursalId' => $sucursalId,
                'sucursales' => $sucursales,
                'gastosPeriodo' => $gastosPeriodo,
                'gastosPorCategoria' => $gastosPorCategoria,
                'totalGastosUSD' => $totalGastosUSD,
                'totalGastosBs' => $totalGastosBs,
                'totalConsolidadoBsFinal' => $totalConsolidadoBsFinal,
                'totalConsolidadoBsUSDFinal' => $totalConsolidadoBsUSDFinal,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BovedaController::consolidado: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el consolidado: ' . $e->getMessage());
        }
    }
}