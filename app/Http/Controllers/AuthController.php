<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AspNetUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Buscar usuario por email
        $user = AspNetUser::where('Email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'El email no está registrado'
            ]);
        }

        // Verificar la contraseña usando Hash::check con PasswordV2
        if (!$user->Password || !Hash::check($request->password, $user->Password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ]);
        }

        // Iniciar sesión
        Auth::login($user);

        // Retornar mensaje sin redirigir
        return response()->json([
            'success' => true,
            'message' => 'Bienvenido ' . $user->NombreCompleto
        ]);
    }

    // Recuperar password
    public function recoverPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = AspNetUser::where('Email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Este email no está registrado.'
            ]);
        }

        // // Generar password temporal
        // $tempPass = Str::random(10);

        // // Guardar usando hash Laravel en el campo PasswordV2
        // $user->Password = Hash::make($tempPass);
        // $user->save();

        // // Enviar email
        // try {
        //     Mail::raw("Su nueva contraseña temporal es: {$tempPass}", function ($message) use ($user) {
        //         $message->to($user->Email)
        //                 ->subject('Recuperación de contraseña');
        //     });
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'No se pudo enviar el correo. Verifique SMTP.'
        //     ]);
        // }

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado una nueva contraseña a su correo.'
        ]);
    }

    // Cerrar sesion
    public function logout(Request $request)
    {
        Auth::logout();

        // Elimina todas las variables de sesión
        $request->session()->invalidate();

        // Invalida la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landingpage.index');
    }
}
