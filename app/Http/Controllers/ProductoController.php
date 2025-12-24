<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoSucursal;
use App\Models\Producto;

use App\DTO\ProductoDTO;
use App\DTO\SucursalDTO;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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
}