<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SucursalController extends Controller
{
    // Seleccionar una sucursal
    public function seleccionar($id)
    {
        // Guardamos en sesión la sucursal seleccionada
        session(['sucursal_id' => $id]);

        // Redireccionamos al dashboard
        return redirect()->route('cpanel.dashboard')
            ->with('success', 'Sucursal seleccionada correctamente.');
    }
}