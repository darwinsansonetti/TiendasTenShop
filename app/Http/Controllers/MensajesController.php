<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mensaje;

class MensajesController extends Controller
{
    public function enviarMensaje(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string'
        ]);

        // Crear objeto igual que en .NET
        $mensaje = new Mensaje();
        $mensaje->Fecha = now()->format('Y-m-d');
        $mensaje->Mensaje = $request->mensaje;
        $mensaje->save();

        return response()->json([
            'success' => true,
            'data' => [
                'MensajeId' => $mensaje->MensajeId,
                'Fecha'     => $mensaje->Fecha,
                'Mensaje'   => $mensaje->Mensaje
            ],
            'message' => 'Mensaje guardado correctamente'
        ]);
    }
}
