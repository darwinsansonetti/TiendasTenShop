<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Helpers\GeneralHelper;

class SucursalController extends Controller
{
    // Seleccionar una sucursal
    // public function seleccionar($id)
    // {
    //     // Guardamos en sesión la sucursal seleccionada
    //     session(['sucursal_id' => $id]);

    //     // Redireccionamos al dashboard
    //     return redirect()->route('cpanel.dashboard')
    //         ->with('success', 'Sucursal seleccionada correctamente.');
    // }

    public function seleccionar($id)
    {
        // Guardar id en sesión
        session(['sucursal_id' => $id]);

        // Obtener lista de sucursales
        $listaSucursales = Cache::remember('lista_sucursales', 3600, function() {
            return GeneralHelper::buscarSucursales(0);
        });

        // Calcular nombre
        $sucursalNombre = $id != 0 
            ? ($listaSucursales->firstWhere('ID', $id)->Nombre ?? "")
            : "Todas las sucursales";

        session(['sucursal_nombre' => $sucursalNombre]);

        // Siempre devolver JSON si la petición viene de JS
        return response()->json([
            'success' => true,
            'sucursal_id' => $id,
            'sucursal_nombre' => $sucursalNombre,
            'message' => 'Sucursal seleccionada correctamente.'
        ]);
    }

}