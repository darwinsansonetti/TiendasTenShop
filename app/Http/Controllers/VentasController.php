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

class VentasController extends Controller
{   

    // Ventas Diarias
    public function ventas_diarias(Request $request)
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
            'menu_active' => 'Análisis de Ventas',
            'submenu_active' => 'Ventas Diarias'
        ]);

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');

        $ventasDiariaPeriodo = VentasHelper::BuscarListadoVentasDiarias($filtroFecha, $sucursalId);

        return view('cpanel.ventas.ventas_diarias', [
            'ventas' => $ventasDiariaPeriodo['Ventas'],
            'fechaInicio' => $ventasDiariaPeriodo['FechaInicio'],
            'fechaFin' => $ventasDiariaPeriodo['FechaFin']
        ]);
    }

    // Eliminar Venta Diaria
    public function eliminar_venta(Request $request)
    {
        $id = $request->input('venta_id');

        try {
            $ventasService = new VentasService();
            $ok = $ventasService->borrarVentaDiaria($id);

            return response()->json([
                'ok' => $ok,
                'message' => $ok ? 'Venta eliminada' : 'Venta no encontrada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function detalleVenta($ventaId, $sucursalId)
    {
        try {
            // 1️⃣ Obtener la venta - USAR 'ID' en mayúsculas
            $venta = DB::table('Ventas')
                ->where('ID', $ventaId) // ← CORRECCIÓN: 'ID' no 'Id'
                ->first();

            if (!$venta) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Venta no encontrada'
                ], 404);
            }

            // 2️⃣ Si sucursal viene en 0 se usa la de la venta
            if ($sucursalId == 0) {
                // Intentar diferentes nombres de columna
                $sucursalId = $venta->SucursalId ?? $venta->sucursalId ?? $venta->ID_Sucursal ?? 0;
            }

            // 3️⃣ Obtener los productos vendidos - VERIFICAR NOMBRES DE COLUMNAS
            $detalles = DB::table('VentaProductos')
                ->where('VentaId', $ventaId) // ← 'VentaId' o 'VentaID'?
                ->get();

            if ($detalles->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'data' => []
                ]);
            }

            // Verificar estructura del primer detalle
            if ($detalles->isNotEmpty()) {
                $primerDetalle = (array)$detalles->first();
            }

            // Lista de productoIds - VERIFICAR NOMBRE DE COLUMNA
            $productoIds = $detalles->pluck('ProductoId')->toArray();
            if (empty($productoIds)) {
                // Intentar con diferentes nombres
                $productoIds = $detalles->pluck('ProductoID')->toArray();
            }

            // 4️⃣ BUSCAR PRODUCTOS EN LA VISTA ProductosSucursalView
            
            $productos = DB::table('ProductosSucursalView')
                ->whereIn('ID', $productoIds)
                ->where('SucursalId', $sucursalId)
                ->get()
                ->keyBy('ID');

            // Si no hay productos, intentar con nombre de columna diferente
            if ($productos->isEmpty()) {
                $productos = DB::table('ProductosSucursalView')
                    ->whereIn('ID', $productoIds)
                    ->where('SucursalId', $sucursalId)
                    ->get()
                    ->keyBy('ID');
                
            }

            // 5️⃣ Si no se encuentran productos en la vista, buscar en tabla maestra
            if ($productos->isEmpty() && $sucursalId != 0) {
                
                $productos = DB::table('Productos')
                    ->whereIn('ID', $productoIds)
                    ->select(
                        'ID',
                        'Codigo',
                        'Descripcion',
                        'CostoBs',
                        'CostoDivisa',
                        DB::raw('0 as Existencia'),
                        DB::raw($sucursalId . ' as SucursalId'),
                        DB::raw('0 as PvpBs'),
                        DB::raw('0 as PvpDivisa')
                    )
                    ->get()
                    ->keyBy('ID');
            }

            // 6️⃣ Unir resultados - MANEJAR DIFERENTES NOMBRES DE COLUMNAS
            $respuesta = $detalles->map(function ($d) use ($productos, $sucursalId) {
                // Obtener ProductoId del detalle (puede ser ProductoId o ProductoID)
                $productoId = $d->ProductoId ?? $d->ProductoID ?? $d->productoId ?? null;
                
                if (!$productoId) {
                    $producto = null;
                } else {
                    // Buscar producto (puede estar keyed por 'Id' o 'ID')
                    $producto = $productos[$productoId] ?? 
                            $productos->firstWhere('Id', $productoId) ??
                            $productos->firstWhere('ID', $productoId);
                }
                
                // Obtener precio (puede ser Precio o MontoDivisa)
                $precio = $d->Precio ?? $d->precio ?? 0;
                $montoDivisa = $d->MontoDivisa ?? $d->montoDivisa ?? $precio;
                
                $item = [
                    'id' => $d->Id ?? $d->ID,
                    'venta_id' => $d->VentaId ?? $d->VentaID,
                    'producto_id' => $productoId,
                    'cantidad' => $d->Cantidad ?? $d->cantidad ?? 0,
                    'precio' => $precio,
                    'monto_divisa' => $montoDivisa,
                ];
                
                if ($producto) {
                    // Obtener ID del producto (puede ser Id o ID)
                    $prodId = $producto->Id ?? $producto->ID;
                    $prodDesc = $producto->Descripcion ?? $producto->descripcion ?? '';
                    $prodCodigo = $producto->Codigo ?? $producto->codigo ?? '';
                    
                    // Datos del producto
                    $item['producto'] = [
                        'Id' => intval($prodId),
                        'Codigo' => $prodCodigo,
                        'Descripcion' => $prodDesc,
                        'CostoBs' => floatval($producto->CostoBs ?? $producto->costoBs ?? 0),
                        'CostoDivisa' => floatval($producto->CostoDivisa ?? $producto->costoDivisa ?? 0),
                        'Existencia' => intval($producto->Existencia ?? $producto->existencia ?? 0),
                        'SucursalId' => intval($producto->SucursalId ?? $producto->sucursalId ?? $sucursalId),
                        'PvpBs' => floatval($producto->PvpBs ?? $producto->pvpBs ?? 0),
                        'PvpDivisa' => floatval($producto->PvpDivisa ?? $producto->pvpDivisa ?? 0),
                    ];
                    
                    // Calcular valores básicos
                    $costoBs = floatval($producto->CostoBs ?? $producto->costoBs ?? 0);
                    $costoDivisa = floatval($producto->CostoDivisa ?? $producto->costoDivisa ?? 0);
                    $cantidad = $item['cantidad'];
                    
                    if ($cantidad > 0) {
                        $item['monto_unitario'] = round($precio / $cantidad, 2);
                        $item['monto_divisa_unitario'] = round($montoDivisa / $cantidad, 2);
                        
                        // Margen si hay costo en divisa
                        if ($costoDivisa > 0) {
                            $montoDivisaUnitario = $montoDivisa / $cantidad;
                            $margen = (($montoDivisaUnitario * 100) / $costoDivisa) - 100;
                            $item['margen'] = round($margen, 2);
                        } else {
                            $item['margen'] = 0;
                        }
                    } else {
                        $item['monto_unitario'] = 0;
                        $item['monto_divisa_unitario'] = 0;
                        $item['margen'] = 0;
                    }
                    
                    $item['costo_total_bs'] = round($cantidad * $costoBs, 2);
                    $item['costo_total_divisa'] = round($cantidad * $costoDivisa, 2);
                    $item['utilidad_bs'] = round($precio - ($cantidad * $costoBs), 2);
                    $item['utilidad_divisa'] = round($montoDivisa - ($cantidad * $costoDivisa), 2);
                    
                } else {
                    $item['producto'] = null;
                    $item['monto_unitario'] = 0;
                    $item['monto_divisa_unitario'] = 0;
                    $item['margen'] = 0;
                    $item['costo_total_bs'] = 0;
                    $item['costo_total_divisa'] = 0;
                    $item['utilidad_bs'] = 0;
                    $item['utilidad_divisa'] = 0;
                }
                
                return $item;
            });

            return response()->json([
                'ok' => true,
                'data' => $respuesta
            ]);

        } catch (\Throwable $e) {
            \Log::error('💥 ERROR en detalleVenta:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'ok' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
