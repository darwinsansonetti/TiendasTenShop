<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DivisaValor;
use Illuminate\Support\Facades\DB;

class DivisasController extends Controller
{
    // Guardar o Actualizar Tasa del Dia
    public function guardarTasa(Request $request)
    {
        $request->validate([
            'valor'           => 'required|numeric|min:0.01',
            'valor_paralelo'  => 'required|numeric|min:0.01',
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

        $paralelo = DB::table('Paralelo')
            ->whereDate('fecha', now())
            ->orderByDesc('id')
            ->first();

        if ($paralelo) {
            DB::table('Paralelo')
                ->where('id', $paralelo->id)
                ->update([
                    'valor' => $request->valor_paralelo
                ]);
        } else {
            DB::table('Paralelo')->insert([
                'valor' => $request->valor_paralelo,
                'fecha' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'id'      => $valor->Id,
            'message' => 'Tasa del día guardada correctamente'
        ]);
    }
}