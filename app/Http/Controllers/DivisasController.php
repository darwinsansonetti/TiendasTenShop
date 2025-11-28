<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DivisaValor;

class DivisasController extends Controller
{
    // Guardar o Actualizar Tasa del Dia
    public function guardarTasa(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:0.01'
        ]);

        // La divisa por defecto (igual que en .NET)
        $divisaId = 1;

        // Buscar si ya existe tasa para hoy y para la divisa 1
        $valor = DivisaValor::whereDate('Fecha', now())
                            ->where('DivisaId', $divisaId)
                            ->first();

        if ($valor) {
            // Actualiza la tasa existente
            $valor->Valor = $request->valor;
            $valor->save();
        } else {
            // Crear nueva tasa
            $valor = DivisaValor::create([
                'DivisaId' => $divisaId,
                'Fecha'    => now(),
                'Valor'    => $request->valor
            ]);
        }

        return response()->json([
            'success' => true,
            'id'      => $valor->Id,
            'message' => 'Tasa del día guardada correctamente'
        ]);
    }
}