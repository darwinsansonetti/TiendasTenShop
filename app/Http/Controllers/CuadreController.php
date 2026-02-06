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
use App\Models\PagoPuntoDeVenta;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Enums\EnumTipoFiltroFecha; 
use Illuminate\Support\Facades\Validator;

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
            $cierreDiario = VentasHelper::buscarListadoAuditoriasNew($filtroFecha, $sucursalId, 999);
        }

        $totalDivisa = $cierreDiario->sum('EfectivoDivisas');
        $totalEfectivoBs = $cierreDiario->sum('EfectivoBs');
        $totalPagoMovil = $cierreDiario->sum('PagoMovilBs');
        $totalPuntoVenta = $cierreDiario->sum('PuntoDeVentaBs');
        $totalTransferencias = $cierreDiario->sum('TransferenciaBs');
        $totalSistemaBs = $cierreDiario->sum('VentaSistema');
        $totalEgresosBs = $cierreDiario->sum('EgresoBs');
        $totalBiopago = $cierreDiario->sum('Biopago');
        $totalEgresosDivisa = $cierreDiario->sum('EgresoDivisas');

        $totalIngresoBs = $totalEfectivoBs
                    + $totalPagoMovil
                    + $totalPuntoVenta
                    + $totalTransferencias
                    + $totalBiopago;

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
            'totalBiopago' => $totalBiopago,
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
        $cierreDiario->CasheaBsaDivisa = $divisaValor > 0 ? number_format($cierreDiario->CasheaBs / $divisaValor, 2, '.', '') : '0.00';
        $cierreDiario->BiopagoBsaDivisa = $divisaValor > 0 ? number_format($cierreDiario->Biopago / $divisaValor, 2, '.', '') : '0.00';

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
        $totalCasheaBs = $cierreDiario->CasheaBs;
        $totalBiopagoBs = $cierreDiario->Biopago;

        $totalIngresoBs = $totalEfectivoBs
                    + $totalPagoMovil
                    + $totalPuntoVenta
                    + $totalTransferencias
                    + $totalCasheaBs
                    + $totalBiopagoBs;

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
            'totalCasheaBs' => $totalCasheaBs,
            'totalBiopagoBs' => $totalBiopagoBs,
            'totalIngresoBs' => $totalIngresoBs,
            'totalBs' => $totalBs,
            'totalGeneralDivisa' => $totalGeneralDivisa,
            'diferencia' => $diferencia,
        ]);
    }

    // Regsitro Cierre Diario
    public function listar_registro_cierre(Request $request)
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
            'submenu_active' => 'Registrar Cierre'
        ]);

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');
        $sucursalNombre = session('sucursal_nombre');

        $cierreDiario = collect();

        $cierreDiario = VentasHelper::buscarListadoAuditoriasNew($filtroFecha, $sucursalId, 1);
        $cierreDiario = $cierreDiario->sortByDesc('Fecha');

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
        return view('cpanel.cuadre.listado_cierre_diario', [
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

    // Verificar cierre diario
    public function verificarCierre(Request $request)
    {
        try {
            $request->validate([
                'sucursal_id' => 'required|integer|exists:sucursales,id'
            ]);

            $sucursalId = $request->sucursal_id;
            
            // Usa tu Helper para obtener la fecha de HOY en Venezuela
            $filtroHoy = new ParametrosFiltroFecha(
                tipoFiltroFecha: EnumTipoFiltroFecha::Hoy
            );
            
            // Fecha formateada para comparación
            $fechaHoy = $filtroHoy->fechaInicio->format('Y-m-d');
            
            $existeCierre = CierreDiario::where('SucursalId', $sucursalId)
                ->whereDate('Fecha', $fechaHoy)
                ->first();

            if ($existeCierre) {
                // Esta Pendiente por Contabilizar o Cerrar
                if($existeCierre->Estatus == 0 || $existeCierre->Estatus == 1){
                    return response()->json([
                        'existe' => true,
                        'mensaje' => 'Ya existe un cierre diario para la sucursal en la fecha ' . 
                                $filtroHoy->fechaInicio->format('d/m/Y') . 
                                '. Su estatus es PENDIENTE.'
                    ]);
                }else{
                    // Esta Contabilizado
                    if($existeCierre->Estatus == 3){
                        return response()->json([
                                'existe' => true,
                                'mensaje' => 'Ya existe un cierre diario para la sucursal en la fecha ' . 
                                        $filtroHoy->fechaInicio->format('d/m/Y') . 
                                        '. Su estatus es CONTABILIZADO.'
                            ]);
                    }else{
                        // Esta CERRADA
                        if($existeCierre->Estatus == 4){
                            return response()->json([
                                    'existe' => true,
                                    'mensaje' => 'Ya existe un cierre diario para la sucursal en la fecha ' . 
                                            $filtroHoy->fechaInicio->format('d/m/Y') . 
                                            '. Su estatus es CERRADA.'
                                ]);
                        }else{
                            // Esta EN AUDITORIA
                            if($existeCierre->Estatus == 2){
                                return response()->json([
                                        'existe' => true,
                                        'mensaje' => 'Ya existe un cierre diario para la sucursal en la fecha ' . 
                                                $filtroHoy->fechaInicio->format('d/m/Y') . 
                                                '. Se encuentra EN AUDITORIA.'
                                    ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'existe' => false,
                'mensaje' => '✅ Puede proceder con la creación del cierre diario para ' . 
                           $filtroHoy->fechaInicio->format('d/m/Y')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Datos inválidos: ' . implode(', ', $e->errors())
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error en verificarCierre: ' . $e->getMessage());
            
            return response()->json([
                'error' => true,
                'mensaje' => 'Error del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function crear(Request $request)
    {
        try {
            // Validar sesión
            $sucursalId = session('sucursal_id');

            if (!$sucursalId) {
                return redirect()->route('cpanel.dashboard')
                    ->with('error', 'Debe seleccionar una sucursal primero.');
            }

            // Obtener tasa
            $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());
            $tasaBCV = $tasa['DivisaValor']['Valor'] ?? 0;

            // Usa tu Helper para obtener la fecha de HOY en Venezuela
            $filtroHoy = new ParametrosFiltroFecha(
                tipoFiltroFecha: EnumTipoFiltroFecha::Hoy
            );
            
            // Fecha formateada para comparación
            $fechaHoy = $filtroHoy->fechaInicio->format('Y-m-d');
            
            // Buscar si ya existe un cierre
            $cierreExistente = CierreDiario::where('SucursalId', $sucursalId)
                ->whereDate('Fecha', $fechaHoy)
                ->first();
            
            // INICIALIZAR las variables ANTES del if-else
            $cierreDiarioDTO = null;
            $cierreId = null;
            
            if ($cierreExistente) {
                // Si YA EXISTE, usar ese
                $cierreId = $cierreExistente->CierreDiarioId;
                \Log::info('Usando cierre existente', [
                    'cierre_id' => $cierreId,
                    'sucursal_id' => $sucursalId
                ]);
                
            } else {
                // Si NO EXISTE, crear nuevo
                $cierreDiarioDTO = VentasHelper::generarCierreDiario($sucursalId);
                
                if (!$cierreDiarioDTO) {
                    throw new \Exception('No se pudo generar el cierre diario');
                }
                
                $cierreId = $cierreDiarioDTO->CierreDiarioId;
                
                \Log::info('Nuevo cierre creado', [
                    'cierre_id' => $cierreId,
                    'sucursal_id' => $sucursalId
                ]);
            }
            
            // Obtener el modelo completo con relaciones (siempre por ID)
            $cierreCompleto = CierreDiario::with(['divisaValor', 'pagosPuntoDeVenta', 'pagosPuntoDeVenta.puntoDeVenta', 'pagosPuntoDeVenta.puntoDeVenta.banco'])
                ->find($cierreId);
                
            if (!$cierreCompleto) {
                throw new \Exception('No se encontró el cierre diario con ID: ' . $cierreId);
            }

            // Preparar datos para la vista
            $datosVista = [
                'sucursal' => [
                    'id' => $sucursalId,
                    'nombre' => session('sucursal_nombre', 'Sucursal')
                ],
                'fecha' => [
                    'actual' => $filtroHoy->fechaInicio,
                    'formateada' => $filtroHoy->fechaInicio->format('d/m/Y'),
                    'iso' => $filtroHoy->fechaInicio->format('Y-m-d')
                ],
                'tasa_bcv' => $tasaBCV,
                'hora_inicio' => now('America/Caracas')->format('H:i:s'),
                'usuario' => auth()->user()->name ?? 'Usuario',
                'cierre' => $cierreCompleto,
                'cierre_dto' => $cierreDiarioDTO, // Ahora siempre definida (aunque sea null)
                'cierre_id' => $cierreId
            ];

            // \Log::info('Mostrando Gastos', ['Gastos' => $gastos]);

            return view('cpanel.cuadre.crear', compact('datosVista'));

        } catch (\Exception $e) {
            \Log::error('Error en crear cierre diario: ' . $e->getMessage(), [
                'sucursal_id' => $sucursalId ?? 'no-definido',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al iniciar el cierre diario: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request, $id)
    {
        // Buscar el cierre diario
        try {
            $cierre = CierreDiario::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cierre diario no encontrado.'
            ], 404);
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'numero_zeta' => 'nullable|string|max:50',
            'venta_sistema' => 'required|numeric|min:0',
            'efectivo_bs' => 'required|numeric|min:0',
            'transferencia_bs' => 'required|numeric|min:0',
            'pago_movil_bs' => 'required|numeric|min:0',
            'efectivo_divisas' => 'required|numeric|min:0',
            'zelle_divisas' => 'required|numeric|min:0',
            'cashea_bs' => 'required|numeric|min:0',
            'biopago_bs' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:500',
            'puntos_venta' => 'nullable|array',
            
            'puntos_venta.*.id' => 'nullable|integer|exists:PagosPuntoDeVenta,PagoPuntoDeVentaId',
            'puntos_venta.*.punto_venta_id' => 'required|integer|exists:PuntosDeVenta,PuntoDeVentaId',
            'puntos_venta.*.monto' => 'required|numeric|min:0',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric' => 'El campo :attribute debe ser un número válido.',
            'min' => 'El campo :attribute debe ser mayor o igual a :min.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'puntos_venta.*.monto.required' => 'El monto para cada punto de venta es obligatorio.',
            'puntos_venta.*.monto.numeric' => 'El monto para cada punto de venta debe ser un número válido.',
            'puntos_venta.*.monto.min' => 'El monto para cada punto de venta no puede ser negativo.',
        ]);
        
        // Si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, corrige los errores en el formulario.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar si es finalizar (valor 3)
        $esFinalizar = $request->has('es_finalizar') && $request->input('es_finalizar') == '3';
        
        // Usar transacción para asegurar integridad de datos
        DB::beginTransaction();
        
        try {
            // Preparar datos para el cierre diario
            $datosCierre = [
                'Fecha' => $request->fecha,
                'NumeroZeta' => $request->numero_zeta,
                'VentaSistema' => $request->venta_sistema,
                'EfectivoBs' => $request->efectivo_bs,
                'TransferenciaBs' => $request->transferencia_bs,
                'PagoMovilBs' => $request->pago_movil_bs,
                'EfectivoDivisas' => $request->efectivo_divisas,
                'ZelleDivisas' => $request->zelle_divisas,
                'CasheaBs' => $request->cashea_bs,
                'Biopago' => $request->biopago_bs,
                'Observacion' => $request->observacion,
                'Estatus' => 1,
            ];

            // Si es finalizar (valor 3), cambiar estatus
            if ($esFinalizar) {
                $datosCierre['Estatus'] = 3; // O el valor que uses para "Finalizado"
            }
            
            // Actualizar el cierre diario
            $cierre->update($datosCierre);
            
            // Actualizar pagos de puntos de venta - SIN USAR DB::raw
            if ($request->has('puntos_venta') && is_array($request->puntos_venta)) {
                foreach ($request->puntos_venta as $pagoData) {
                    if (isset($pagoData['id']) && $pagoData['id']) {
                        $pagoPuntoVenta = PagoPuntoDeVenta::find($pagoData['id']);
                        if ($pagoPuntoVenta && $pagoPuntoVenta->CierreDiarioId == $cierre->CierreDiarioId) {
                            // Actualizar directamente sin cast complicado
                            $pagoPuntoVenta->Monto = (float) $pagoData['monto'];
                            $pagoPuntoVenta->save();
                        }
                    }
                }
            }
            
            // Confirmar transacción
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $esFinalizar ? 'Cierre finalizado correctamente.' : 'Cambios guardados correctamente.',
                'es_finalizar' => $esFinalizar,
                'data' => [
                    'cierre_id' => $cierre->CierreDiarioId,
                ],
                'redirect' => route('cpanel.cuadre.registrar_cierre')
            ], 200);
            
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar los cambios.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function editar(CierreDiario $cierreDiario)
    {
        try {
            // Validar sesión
            $sucursalId = session('sucursal_id');

            // Obtener tasa
            $tasa = GeneralHelper::obtenerTasaCambioDiaria($cierreDiario->Fecha);
            $tasaBCV = $tasa['DivisaValor']['Valor'] ?? 0;

            // Usa tu Helper para obtener la fecha de HOY en Venezuela
            $filtroHoy = new ParametrosFiltroFecha(
                tipoFiltroFecha: EnumTipoFiltroFecha::Hoy
            );
            
            // INICIALIZAR las variables ANTES del if-else
            $cierreDiarioDTO = null;
            $cierreId = $cierreDiario->CierreDiarioId;
            
            // Obtener el modelo completo con relaciones (siempre por ID)
            $cierreCompleto = CierreDiario::with(['divisaValor', 'pagosPuntoDeVenta', 'pagosPuntoDeVenta.puntoDeVenta', 'pagosPuntoDeVenta.puntoDeVenta.banco'])
                ->find($cierreId);
                
            if (!$cierreCompleto) {
                throw new \Exception('No se encontró el cierre diario con ID: ' . $cierreId);
            }

        // dd($cierreCompleto);

            // Obtenemos los gastos para mostrarlos
            $gastos = VentasHelper::obtenerGastosPorCierre($cierreId);


            // Preparar datos para la vista
            $datosVista = [
                'sucursal' => [
                    'id' => $sucursalId,
                    'nombre' => session('sucursal_nombre', 'Sucursal')
                ],
                'fecha' => [
                    'actual' => $cierreDiario->Fecha,
                    'formateada' => $cierreDiario->Fecha->format('d/m/Y'),
                    'iso' => $cierreDiario->Fecha->format('Y-m-d')
                ],
                'tasa_bcv' => $tasaBCV,
                'hora_inicio' => now('America/Caracas')->format('H:i:s'),
                'usuario' => auth()->user()->name ?? 'Usuario',
                'cierre' => $cierreCompleto,
                'cierre_dto' => $cierreDiarioDTO, // Ahora siempre definida (aunque sea null)
                'cierre_id' => $cierreId,
                'gastos' => $gastos
            ];

            return view('cpanel.cuadre.crear', compact('datosVista'));

        } catch (\Exception $e) {
            
            return redirect()->back()
                ->with('error', 'Error al iniciar el cierre diario: ' . $e->getMessage());
        }
    }

    public function guardar_gasto(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cierre_diario_id' => 'required|exists:CierreDiario,CierreDiarioId',
                'descripcion' => 'required|string|max:255',
                'monto_bsf' => 'required|numeric|min:0',
                'monto_usd' => 'required|numeric|min:0',
                'forma_pago' => 'required|integer|min:0|max:6',
                'observacion_gasto' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cierre = CierreDiario::findOrFail($request->cierre_diario_id);

            DB::transaction(function () use ($request, $cierre) {
                VentasHelper::guardarGastosDiariosSucursal(
                    $cierre,
                    $request->only([
                        'descripcion',
                        'forma_pago',
                        'monto_bsf',
                        'monto_usd',
                        'observacion_gasto'
                    ]),
                    false
                );
            });

            // Obtenemos los gastos para mostrarlos
            $gastos = VentasHelper::obtenerGastosPorCierre($cierre->CierreDiarioId);

            return response()->json([
                'success' => true,
                'message' => 'La transacción se ha guardado exitosamente',
                'gastos' => $gastos
            ]);
            
        } catch (\Exception $e) {
            // Registrar el error
            Log::error('Error guardando gasto diario: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar el gasto',
            ], 500);
        }
    }
    
    public function listar_gastos($cierreId)
    {
        try {
            $gastos = GastoDiario::where('CierreDiarioId', $cierreId)
                ->orderBy('Fecha', 'desc')
                ->get();
            
            // Calcular totales
            $totalUSD = $gastos->sum('MontoUSD');
            $totalBsF = $gastos->sum('MontoBsF');
            
            return response()->json([
                'success' => true,
                'gastos' => $gastos->map(function($gasto) {
                    return [
                        'id' => $gasto->GastoDiarioId,
                        'descripcion' => $gasto->Descripcion,
                        'monto_usd' => $gasto->MontoUSD,
                        'monto_bsf' => $gasto->MontoBsF,
                        'forma_pago' => $gasto->FormaPago,
                        'observacion' => $gasto->Observacion,
                        'fecha' => $gasto->Fecha->format('d/m/Y H:i')
                    ];
                }),
                'totales' => [
                    'total_usd' => $totalUSD,
                    'total_bsf' => $totalBsF
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error listando gastos:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los gastos'
            ], 500);
        }
    }
    
    public function eliminar_gasto($gastoId)
    {
        try {
            $gasto = GastoDiario::find($gastoId);
            
            if (!$gasto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gasto no encontrado'
                ], 404);
            }
            
            $cierreId = $gasto->CierreDiarioId;
            $gasto->delete();
            
            // Actualizar totales en el cierre diario
            $this->actualizarTotalesCierre($cierreId);
            
            return response()->json([
                'success' => true,
                'message' => 'Gasto eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error eliminando gasto:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el gasto'
            ], 500);
        }
    }
    
    private function actualizarTotalesCierre($cierreId)
    {
        try {
            $totalGastosUSD = GastoDiario::where('CierreDiarioId', $cierreId)->sum('MontoUSD');
            $totalGastosBsF = GastoDiario::where('CierreDiarioId', $cierreId)->sum('MontoBsF');
            
            $cierre = CierreDiario::find($cierreId);
            if ($cierre) {
                $cierre->update([
                    'TotalGastosUSD' => $totalGastosUSD,
                    'TotalGastosBsF' => $totalGastosBsF
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error actualizando totales del cierre:', ['error' => $e->getMessage()]);
        }
    }
}
