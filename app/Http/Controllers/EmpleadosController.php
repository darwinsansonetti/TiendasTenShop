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
use App\Models\Sucursal;
use App\Models\Usuario;
use App\DTOs\CierreDiarioPeriodoDTO;
use App\Models\CierreDiario;
use App\Models\PagoPuntoDeVenta;
use App\Models\ValorizacionInventario;
use App\Models\Transaccion;
use App\Models\CierreOfp;
use App\DTO\EDCOficinaPrincipalDTO;
use App\DTO\TransferenciaDTO;
use App\DTO\TransferenciaDetalleDTO;
use App\DTO\ProductoDTO;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

class EmpleadosController extends Controller
{
    public function listado_empleados(Request $request)
    {
        $now = Carbon::now('America/Caracas');
        
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio') 
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : $now->copy()->subDay()->startOfDay();

        $fechaFin = $request->input('fecha_fin') 
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : $now->copy()->subDay()->endOfDay();

        // $fechaInicio = Carbon::parse('2026-03-15', 'America/Caracas');
        // $fechaFin = Carbon::parse('2026-03-15', 'America/Caracas');

        $filtroFecha = new ParametrosFiltroFecha(
            null,   // tipoFiltroFecha
            null,   // mesSeleccionado
            null,   // año
            false,  // añoAnterior
            $fechaInicio,
            $fechaFin
        );

        $sucursalId = session('sucursal_id', 0);
        // Si es 0, convertirlo a null
        $sucursalId = $sucursalId ?: null;

        // Obtener ranking de vendedores
        $rankingVendedor = GeneralHelper::ObtenerRankingVendedoresSinAgrupar($filtroFecha, null, $sucursalId);

        // dd($rankingVendedor);

        session([
            'menu_active' => 'Empleados',
            'submenu_active' => 'Ventas Diarias'
        ]);

        return view('cpanel.empleados.listado_ventas_empleados', [
            'rankingVendedor' => $rankingVendedor,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
        ]);
    }

    public function detallesVenta($id, $vti)
    {
        try {
            // Buscar información del vendedor
            $vendedor = Usuario::where('UsuarioId', $id)
                ->where('EsActivo', 1)
                ->first();
            
            if (!$vendedor) {
                return back()->with('error', 'Vendedor no encontrado');
            }
            
            // Obtener los detalles de la venta (productos)
            $detallesVenta = DB::table('VentaVendedoresView')
                ->select([
                    'ID',
                    'ID as VentaId',
                    'Fecha',
                    'SucursalId',
                    'ProductoId',
                    'Cantidad',
                    'PrecioVenta',
                    'MontoDivisa',
                    'UsuarioId',
                    'CostoDivisa',
                    'Costo',
                    'Existencia'
                ])
                ->where('UsuarioId', $id)
                ->where('ID', $vti)
                ->orderBy('ProductoId')
                ->get();
            
            // Obtener IDs de productos para cargar TODOS sus datos
            $productoIds = $detallesVenta->pluck('ProductoId')->unique()->toArray();
            
            // Cargar TODA la información de productos (no solo nombre y código)
            $productos = Producto::whereIn('ID', $productoIds)
                ->get()
                ->keyBy('ID');
            
            // Enriquecer detalles con TODOS los datos del producto
            $detallesVenta->transform(function ($item) use ($productos) {
                $producto = $productos->get($item->ProductoId);
                
                if ($producto) {
                    // Asignar TODOS los campos del producto
                    $item->ProductoNombre = $producto->Nombre;
                    $item->ProductoCodigo = $producto->Codigo;
                    $item->ProductoDescripcion = $producto->Descripcion ?? '';
                    $item->ProductoUrlFoto = $producto->UrlFoto ?? '';
                    $item->ProductoCategoria = $producto->Categoria ?? '';
                    $item->ProductoMarca = $producto->Marca ?? '';
                    $item->ProductoModelo = $producto->Modelo ?? '';
                    $item->ProductoProveedorId = $producto->ProveedorId ?? null;
                    $item->ProductoEstatus = $producto->Estatus ?? 1;
                    $item->ProductoFechaRegistro = $producto->FechaRegistro ?? null;
                    $item->ProductoPrecioCompra = $producto->PrecioCompra ?? 0;
                    $item->ProductoPrecioVenta = $producto->PrecioVenta ?? 0;
                    $item->ProductoStock = $producto->Stock ?? 0;
                    $item->ProductoStockMinimo = $producto->StockMinimo ?? 0;
                    $item->ProductoUbicacion = $producto->Ubicacion ?? '';
                } else {
                    // Valores por defecto si no se encuentra el producto
                    $item->ProductoNombre = 'Producto no encontrado';
                    $item->ProductoCodigo = 'N/A';
                    $item->ProductoDescripcion = '';
                    $item->ProductoUrlFoto = '';
                    $item->ProductoCategoria = '';
                    $item->ProductoMarca = '';
                    $item->ProductoModelo = '';
                    $item->ProductoProveedorId = null;
                    $item->ProductoEstatus = 0;
                    $item->ProductoFechaRegistro = null;
                    $item->ProductoPrecioCompra = 0;
                    $item->ProductoPrecioVenta = 0;
                    $item->ProductoStock = 0;
                    $item->ProductoStockMinimo = 0;
                    $item->ProductoUbicacion = '';
                }
                
                // Calcular subtotales (estos ya los tenías)
                $item->SubtotalDivisa = $item->Cantidad * $item->PrecioVenta;
                $item->SubtotalBs = $item->SubtotalDivisa * ($item->TasaDeCambio ?? 1);
                
                return $item;
            });

            // Calcular totales de la venta
            $totales = (object) [
                'totalCantidad' => $detallesVenta->sum('Cantidad'),
                'totalDivisa' => $detallesVenta->sum('MontoDivisa'),
                'totalBs' => $detallesVenta->sum(function ($item) {
                    return $item->Cantidad * $item->PrecioVenta * ($item->TasaDeCambio ?? 1);
                })
            ];
            
            // Obtener información de la sucursal
            $sucursal = null;
            if ($vendedor->SucursalId) {
                $sucursal = Sucursal::find($vendedor->SucursalId);
            }
            
            // Puedes hacer un dd para verificar que ahora tienes todos los campos
            // dd($detallesVenta->first());
            
            return view('cpanel.empleados.detalles_venta', compact(
                'vendedor',
                'detallesVenta',
                'totales',
                'sucursal',
                'vti'
            ));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
        }
    }
}