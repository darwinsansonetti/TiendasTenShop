<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AspNetUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string'
    //     ]);

    //     // Buscar usuario por email
    //     $user = AspNetUser::where('Email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'El email no está registrado'
    //         ]);
    //     }

    //     // Verificar la contraseña usando Hash::check con PasswordV2
    //     if (!$user->Password || !Hash::check($request->password, $user->Password)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Contraseña incorrecta'
    //         ]);
    //     }

    //     // Iniciar sesión
    //     Auth::login($user);

    //     // Retornar mensaje sin redirigir
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Bienvenido ' . $user->NombreCompleto
    //     ]);
    // }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string'
        ]);

        try {
            // 1. Buscar usuario por email
            $user = AspNetUser::where('Email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'El email no está registrado'
                ]);
            }

            // 2. Verificar si el usuario está activo
            if (isset($user->EsActivo) && $user->EsActivo == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario está inactivo. Contacte al administrador.'
                ]);
            }

            // 3. Verificar el campo Password
            if (empty($user->Password)) {
                // Primera vez: no tiene password en Laravel
                // Se guarda el hash del password que ingresó
                $user->Password = Hash::make($request->password);
                $user->save();

            } else {
                // Ya tiene password: validar
                if (!Hash::check($request->password, $user->Password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Contraseña incorrecta'
                    ]);
                }
            }

            // 4. Obtener los roles del usuario
            $roles = DB::connection('sqlsrv')
                ->table('AspNetUserRoles as ur')
                ->join('AspNetRoles as r', 'ur.RoleId', '=', 'r.Id')
                ->where('ur.UserId', $user->Id)
                ->pluck('r.Name')
                ->map(fn($r) => trim($r))
                ->toArray();

            // 5. Determinar si es MASTER
            $esMaster = in_array('MASTER', $roles);

            // 6. Iniciar sesión
            Auth::login($user);

            // 7. Determinar redirect
            $redirectUrl = $esMaster
                ? route('cpanel.dashboard')      // MASTER → dashboard
                : route('pago.movil.publico');   // Otros → formulario de verificación

            return response()->json([
                'success'   => true,
                'message'   => 'Bienvenido ' . $user->NombreCompleto,
                'es_master' => $esMaster,
                'roles'     => $roles,
                'redirect'  => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Error login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión. Intente nuevamente.'
            ]);
        }
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
