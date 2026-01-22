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

}