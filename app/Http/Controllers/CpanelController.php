<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;

class CpanelController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user(); 

        // Obtener Tasa del Dia desde Helpers
        $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());

        // 1 = TipoSucursal.Tienda, 0 = Todas
        $listaSucursales = GeneralHelper::buscarSucursales(0);

        // Tomamos la Sucursal seleccionada
        $sucursalId = session('sucursal_id', 0);

        // Inicializamos variable para mostrar nombre de la sucursal
        $sucursalNombre = "";

        if ($sucursalId != 0) {
            // Filtrar la lista para encontrar la sucursal por ID
            $sucursalSeleccionada = $listaSucursales->firstWhere('ID', $sucursalId);

            // Asignamos el nombre si existe
            $sucursalNombre = $sucursalSeleccionada ? $sucursalSeleccionada->Nombre : "";
        } else {
            // Cuando ID = 0 (Todas las sucursales)
            $sucursalNombre = "Todas las sucursales";
        }

        // Guardamos en sesión para poder usarlo en cualquier parte
        session(['sucursal_nombre' => $sucursalNombre]);

        // Mostrar mensaje si no hay tasa
        if (!$tasa || !$tasa['DivisaValor']) {
            session()->flash('warning', 'No se ha registrado la tasa del día. Por favor, actualícela.');
        }

        return view('cpanel.dashboard', compact(
            'user', 
            'tasa', 
            'listaSucursales'
        ));
    }
}