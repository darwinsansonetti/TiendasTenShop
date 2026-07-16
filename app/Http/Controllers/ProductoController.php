<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoSucursal;
use App\Models\Producto;
use App\Models\ProductosSucursalView;

use App\DTO\ProductoDTO;
use App\DTO\SucursalDTO;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Helpers\GeneralHelper;

use App\Helpers\FileHelper;

use Illuminate\Support\Facades\DB;


class ProductoController extends Controller
{
    public function actualizarPVP(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|integer|exists:Productos,ID',
            'sucursal_id' => 'required|integer',
            'nuevo_pvp' => 'required|numeric|min:0.01',
        ]);

        // Actualizar solo el registro correspondiente
        $actualizado = ProductoSucursal::where('ProductoId', $request->producto_id)
                                    ->where('SucursalId', $request->sucursal_id)
                                    ->update([
                                            'PvpAnterior' => DB::raw('PvpDivisa'),
                                            'NuevoPvp' => $request->nuevo_pvp,
                                            'FechaNuevoPrecio' => now(),
                                            'Tipo' => 0
                                    ]);

        if (!$actualizado) {
            return response()->json([
                'success' => false,
                'message' => 'Producto en sucursal no encontrado o no se pudo actualizar'
            ]);
        }

        // Obtener el registro actualizado
        $productoSucursal = ProductoSucursal::where('ProductoId', $request->producto_id)
                                            ->where('SucursalId', $request->sucursal_id)
                                            ->first();

        if (!$productoSucursal) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el registro actualizado'
            ]);
        }

        $producto = Producto::find($request->producto_id);

        $resultado = [
            'producto_id' => $producto->ID,
            'codigo' => $producto->Codigo,
            'descripcion' => $producto->Descripcion,
            'pvp_actual' => $productoSucursal->NuevoPvp,
            'pvp_anterior' => $productoSucursal->PvpAnterior,
            'costo_divisa' => $producto->CostoDivisa, // <--- AGREGAR
            'margen_nuevo_precio' => $producto->CostoDivisa != 0 
                                    ? round((($productoSucursal->NuevoPvp * 100) / $producto->CostoDivisa) - 100, 2) 
                                    : 0,
            'utilidad_nuevo_pvp' => $productoSucursal->NuevoPvp - $producto->CostoDivisa,
            'fecha_nuevo_precio' => $productoSucursal->FechaNuevoPrecio->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data' => $resultado
        ]);
    }

    // Ver detalle del producto
    public function show($id)
    {
        // Buscar el producto en la tabla Productos
        $producto = Producto::findOrFail($id);

        // Buscar las sucursales
        $listaSucursales = GeneralHelper::buscarSucursales(1);

        // Si es 'todas las sucursales', recorrer la lista de sucursales y buscar los productos
        foreach ($listaSucursales as $sucursal) {
            // Buscar el producto en la sucursal específica
            $productoSucursalItem = ProductosSucursalView::where('ID', $id)
                                                        ->where('SucursalId', $sucursal->ID)
                                                        ->where('Estatus', 1)
                                                        ->first();

            // Solo agregar si el producto está disponible en esa sucursal
            if ($productoSucursalItem) {
                // Obtener el nombre de la sucursal desde la tabla Sucursales
                $sucursalNombre = \App\Models\Sucursal::where('ID', $sucursal->ID)->value('Nombre');

                // Agregar a la lista con nombre de sucursal
                $productoSucursal[] = [
                    'producto' => $productoSucursalItem, 
                    'sucursal_nombre' => $sucursalNombre
                ];
            }
        }

        // dd($productoSucursal);

        // Pasamos tanto la información del producto como la información de sucursales
        return view('cpanel.productos.detalle', compact('producto', 'productoSucursal'));
    }

    // Vista para cargar productos por sucursal
    public function mostrarListaProductos()
    {
        // Configurar menú activo
        session([
            'menu_active' => 'Productos',
            'submenu_active' => 'Listado por sucursal'
        ]);

        // Obtener sucursal de la sesión
        $sucursalId = session('sucursal_id');
        $sucursalNombre = session('sucursal_nombre', 'Sin sucursal');

        // Obtener lista de sucursales
        $sucursales = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->orderBy('Nombre')
            ->get();

        // Obtener productos solo si hay sucursal seleccionada
        $productos = collect();

        if ($sucursalId) {
            $productos = DB::connection('sqlsrv')
                ->table('ProductoSucursal as ps')
                ->leftJoin('Productos as p', 'ps.ProductoId', '=', 'p.ID')
                ->where('ps.SucursalId', $sucursalId)
                ->where('ps.Estatus', 1)
                ->where('ps.Existencia', '>', 0)  // ✅ SOLO productos con existencia > 0
                ->select([
                    'ps.ProductoId',
                    'ps.SucursalId',
                    'ps.PvpBs',
                    'ps.PvpDivisa',
                    'ps.Existencia',
                    'ps.FechaIngreso',
                    'ps.FechaUltimaVenta',
                    'p.Codigo',
                    'p.Referencia',
                    'p.Descripcion as producto_nombre',
                    'p.CostoDivisa',
                    'p.CostoBs',
                    'p.UrlFoto',
                    'p.Estatus as producto_estatus'
                ])
                ->orderBy('p.Codigo')
                ->get();

            // Formatear datos
            $productos->transform(function ($item) {
                $item->UrlFoto = $item->UrlFoto 
                    ? FileHelper::getOrDownloadFile('images/items/thumbs/', $item->UrlFoto, 'assets/img/adminlte/img/produc_default.jfif')
                    : 'assets/img/adminlte/img/produc_default.jfif';
                $item->estatus_texto = $item->producto_estatus == 1 ? 'Activo' : 'Inactivo';
                $item->estatus_clase = $item->producto_estatus == 1 ? 'success' : 'danger';
                
                // Formatear fechas
                $item->FechaIngreso = $item->FechaIngreso ? \Carbon\Carbon::parse($item->FechaIngreso)->format('d/m/Y') : 'N/A';
                $item->FechaUltimaVenta = $item->FechaUltimaVenta ? \Carbon\Carbon::parse($item->FechaUltimaVenta)->format('d/m/Y') : 'N/A';
                
                // Usar FechaUltimaVenta como fecha de actualización
                $item->FechaActualizacion = $item->FechaUltimaVenta && $item->FechaUltimaVenta != 'N/A' 
                    ? $item->FechaUltimaVenta 
                    : $item->FechaIngreso;
                
                return $item;
            });
        }

        return view('cpanel.productos.listado_sucursal', compact('sucursales', 'productos', 'sucursalId', 'sucursalNombre'));
    }
}