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
use Illuminate\Support\Facades\Log;

use App\Helpers\FileHelper;

use Illuminate\Support\Facades\DB;


class BancosController extends Controller
{
    public function mostrarBancos(Request $request)
    {
        try {
            Log::info('INICIO - BancosController::mostrarBancos');

            // ================================================
            // 1. ASIGNAR MENU ACTIVO
            // ================================================
            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Bancos'
            ]);

            // ================================================
            // 2. OBTENER LISTA DE BANCOS
            // ================================================
            $bancos = $this->buscarBancos();

            Log::info('Bancos cargados exitosamente', [
                'cantidad' => $bancos->count()
            ]);

            return view('cpanel.entidades_bancarias.bancos', [
                'bancos' => $bancos
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::mostrarBancos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de bancos');
        }
    }

    /**
     * Buscar todos los bancos
     * Equivalente a: public async Task<List<BancoDTO>> BuscarBancos()
     */
    private function buscarBancos()
    {
        try {
            $bancos = DB::connection('sqlsrv')
                ->table('Bancos')
                ->orderBy('Nombre', 'asc')
                ->select([
                    'ID',
                    'Nombre',
                    'EsActivo',
                    'Logo'
                ])
                ->get();

            $bancos->transform(function ($item) {
                $item->EstatusTexto = $item->EsActivo == 1 ? 'Activo' : 'Inactivo';
                $item->EstatusBadge = $item->EsActivo == 1 ? 'success' : 'danger';
                
                // ✅ Ruta correcta: assets/img/bancos/
                $item->LogoUrl = $item->Logo 
                    ? asset('assets/img/bancos/' . $item->Logo) 
                    : asset('assets/img/bancos/sinbanca.png');
                
                return $item;
            });

            return $bancos;

        } catch (\Exception $e) {
            Log::error('Error en buscarBancos: ' . $e->getMessage());
            return collect();
        }
    }

    public function crearBanco(Request $request)
    {
        try {
            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Bancos'
            ]);

            return view('cpanel.entidades_bancarias.crear_banco');

        } catch (\Exception $e) {
            Log::error('Error en BancosController::crearBanco: ' . $e->getMessage());
            return redirect()->route('cpanel.bancos.index')->with('error', 'Error al cargar el formulario');
        }
    }

    /**
     * Guardar un nuevo banco
     */
    public function guardarBanco(Request $request)
    {
        try {
            Log::info('INICIO - BancosController::guardarBanco', $request->all());

            // ================================================
            // 1. VALIDAR DATOS
            // ================================================
            $request->validate([
                'nombre' => 'required|string|max:100',
                'es_activo' => 'required|in:0,1',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
            ]);

            // ================================================
            // 2. PROCESAR LOGO
            // ================================================
            $logoName = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $logoName = 'banco_' . time() . '.' . $extension;
                
                $destinationPath = public_path('assets/img/bancos/');
                
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                $file->move($destinationPath, $logoName);
            }

            // ================================================
            // 3. INSERTAR EN BASE DE DATOS
            // ================================================
            $id = DB::connection('sqlsrv')->table('Bancos')->insertGetId([
                'Nombre' => $request->nombre,
                'EsActivo' => $request->es_activo,
                'Logo' => $logoName
            ]);

            Log::info('Banco creado exitosamente', [
                'id' => $id,
                'nombre' => $request->nombre,
                'logo' => $logoName
            ]);

            return redirect()->route('cpanel.bancos.index')
                ->with('success', 'Banco creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete los datos necesarios');
        } catch (\Exception $e) {
            Log::error('Error en BancosController::guardarBanco: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el banco: ' . $e->getMessage());
        }
    }

    public function detalleBanco($id)
    {
        try {
            Log::info('INICIO - BancosController::detalleBanco', ['id' => $id]);

            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Bancos'
            ]);

            $banco = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('ID', $id)
                ->first();

            if (!$banco) {
                return redirect()->route('cpanel.bancos.index')
                    ->with('error', 'Banco no encontrado');
            }

            // Formatear datos
            $banco->EstatusTexto = $banco->EsActivo == 1 ? 'Activo' : 'Inactivo';
            $banco->EstatusBadge = $banco->EsActivo == 1 ? 'success' : 'danger';
            $banco->LogoUrl = $banco->Logo 
                ? asset('assets/img/bancos/' . $banco->Logo) 
                : asset('assets/img/bancos/sinbanca.png');

            return view('cpanel.entidades_bancarias.detalle_banco', [
                'banco' => $banco
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::detalleBanco: ' . $e->getMessage());
            return redirect()->route('cpanel.bancos.index')
                ->with('error', 'Error al cargar el detalle del banco');
        }
    }

    /**
     * Mostrar formulario para editar banco
     */
    public function editarBanco($id)
    {
        try {
            Log::info('INICIO - BancosController::editarBanco', ['id' => $id]);

            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Bancos'
            ]);

            $banco = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('ID', $id)
                ->first();

            if (!$banco) {
                return redirect()->route('cpanel.bancos.index')
                    ->with('error', 'Banco no encontrado');
            }

            $banco->LogoUrl = $banco->Logo 
                ? asset('assets/img/bancos/' . $banco->Logo) 
                : asset('assets/img/bancos/sinbanca.png');

            return view('cpanel.entidades_bancarias.editar_banco', [
                'banco' => $banco
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::editarBanco: ' . $e->getMessage());
            return redirect()->route('cpanel.bancos.index')
                ->with('error', 'Error al cargar el formulario de edición');
        }
    }

    /**
     * Actualizar un banco
     */
    public function actualizarBanco(Request $request, $id)
    {
        try {
            Log::info('INICIO - BancosController::actualizarBanco', [
                'id' => $id,
                'data' => $request->all()
            ]);

            // ================================================
            // 1. VALIDAR DATOS
            // ================================================
            $request->validate([
                'nombre' => 'required|string|max:100',
                'es_activo' => 'required|in:0,1',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048'
            ]);

            // ================================================
            // 2. VERIFICAR QUE EL BANCO EXISTA
            // ================================================
            $banco = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('ID', $id)
                ->first();

            if (!$banco) {
                return redirect()->back()
                    ->with('error', 'Banco no encontrado');
            }

            // ================================================
            // 3. PROCESAR LOGO
            // ================================================
            $logoName = $banco->Logo;
            
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($banco->Logo && file_exists(public_path('assets/img/bancos/' . $banco->Logo))) {
                    unlink(public_path('assets/img/bancos/' . $banco->Logo));
                }
                
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $logoName = 'banco_' . time() . '.' . $extension;
                
                $destinationPath = public_path('assets/img/bancos/');
                
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                $file->move($destinationPath, $logoName);
            }

            // ================================================
            // 4. ACTUALIZAR EN BASE DE DATOS
            // ================================================
            DB::connection('sqlsrv')->table('Bancos')
                ->where('ID', $id)
                ->update([
                    'Nombre' => $request->nombre,
                    'EsActivo' => $request->es_activo,
                    'Logo' => $logoName
                ]);

            Log::info('Banco actualizado exitosamente', [
                'id' => $id,
                'nombre' => $request->nombre
            ]);

            return redirect()->route('cpanel.bancos.index')
                ->with('success', 'Banco actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete los datos necesarios');
        } catch (\Exception $e) {
            Log::error('Error en BancosController::actualizarBanco: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el banco: ' . $e->getMessage());
        }
    }

    public function mostrarPuntos(Request $request)
    {
        try {
            Log::info('INICIO - BancosController::mostrarPuntos');

            // ================================================
            // 1. ASIGNAR MENU ACTIVO
            // Equivalente a: AsignarMenuActivo(MenuActivo.CONFIGURAC)
            // ================================================
            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Puntos de Ventas'
            ]);

            // ================================================
            // 2. OBTENER LISTA DE PUNTOS DE VENTA
            // Equivalente a: List<PuntoDeVentaDTO> _listaPdv = await _pdvService.BuscarListaPDV()
            // ================================================
            $puntos = $this->buscarListaPDV();

            Log::info('Puntos de venta cargados exitosamente', [
                'cantidad' => $puntos->count()
            ]);

            return view('cpanel.entidades_bancarias.puntos', [
                'puntos' => $puntos
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::mostrarPuntos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de puntos de venta');
        }
    }

    /**
     * Buscar lista de puntos de venta
     * Equivalente a: public async Task<List<PuntoDeVentaDTO>> BuscarListaPDV()
     */
    private function buscarListaPDV()
    {
        try {
            $puntos = DB::connection('sqlsrv')
                ->table('PuntosDeVenta as pdv')
                ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                ->leftJoin('Sucursales as s', 'pdv.SucursalId', '=', 's.ID')
                ->where('pdv.EsActivo', 1)
                ->select([
                    'pdv.PuntoDeVentaId',
                    'pdv.BancoId',
                    'pdv.SucursalId',
                    'pdv.Serial',
                    'pdv.Descripcion',
                    'pdv.Codigo',
                    'pdv.EsActivo',
                    'b.Nombre as banco_nombre',
                    'b.Logo as banco_logo',
                    's.Nombre as sucursal_nombre'
                ])
                ->orderBy('pdv.Descripcion', 'asc')
                ->get();

            // Formatear datos
            $puntos->transform(function ($item) {
                $item->EstatusTexto = $item->EsActivo == 1 ? 'Activo' : 'Inactivo';
                $item->EstatusBadge = $item->EsActivo == 1 ? 'success' : 'danger';
                
                $item->LogoUrl = $item->banco_logo 
                    ? asset('assets/img/bancos/' . $item->banco_logo) 
                    : asset('assets/img/bancos/banco_default.png');
                
                return $item;
            });

            return $puntos;

        } catch (\Exception $e) {
            Log::error('Error en buscarListaPDV: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Mostrar formulario para crear punto de venta
     */
    public function crearPunto(Request $request)
    {
        try {
            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Puntos de Ventas'
            ]);

            // Obtener lista de bancos para el select
            $bancos = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('EsActivo', 1)
                ->orderBy('Nombre', 'asc')
                ->select('ID', 'Nombre')
                ->get();

            // Obtener lista de sucursales para el select
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->orderBy('Nombre', 'asc')
                ->select('ID', 'Nombre')
                ->get();

            return view('cpanel.entidades_bancarias.crear_punto', [
                'bancos' => $bancos,
                'sucursales' => $sucursales
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::crearPunto: ' . $e->getMessage());
            return redirect()->route('cpanel.puntos.index')->with('error', 'Error al cargar el formulario');
        }
    }

    /**
     * Guardar un nuevo punto de venta
     */
    public function guardarPunto(Request $request)
    {
        try {
            Log::info('INICIO - BancosController::guardarPunto', $request->all());

            $request->validate([
                'banco_id' => 'required|exists:Bancos,ID',
                'sucursal_id' => 'required|exists:Sucursales,ID',
                'serial' => 'nullable|string|max:50',
                'descripcion' => 'required|string|max:200',
                'codigo' => 'nullable|integer',
                'es_activo' => 'required|in:0,1'
            ]);

            $id = DB::connection('sqlsrv')->table('PuntosDeVenta')->insertGetId([
                'BancoId' => $request->banco_id,
                'SucursalId' => $request->sucursal_id,
                'Serial' => $request->serial,
                'Descripcion' => $request->descripcion,
                'Codigo' => $request->codigo,
                'EsActivo' => $request->es_activo
            ]);

            Log::info('Punto de venta creado exitosamente', [
                'id' => $id,
                'descripcion' => $request->descripcion
            ]);

            return redirect()->route('cpanel.puntos.index')
                ->with('success', 'Punto de venta creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete los datos necesarios');
        } catch (\Exception $e) {
            Log::error('Error en BancosController::guardarPunto: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el punto de venta: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de un punto de venta
     */
    public function detallePunto($id)
    {
        try {
            Log::info('INICIO - BancosController::detallePunto', ['id' => $id]);

            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Puntos de Ventas'
            ]);

            $punto = DB::connection('sqlsrv')
                ->table('PuntosDeVenta as pdv')
                ->leftJoin('Bancos as b', 'pdv.BancoId', '=', 'b.ID')
                ->leftJoin('Sucursales as s', 'pdv.SucursalId', '=', 's.ID')
                ->where('pdv.PuntoDeVentaId', $id)
                ->select([
                    'pdv.*',
                    'b.Nombre as banco_nombre',
                    'b.Logo as banco_logo',
                    's.Nombre as sucursal_nombre'
                ])
                ->first();

            if (!$punto) {
                return redirect()->route('cpanel.puntos.index')
                    ->with('error', 'Punto de venta no encontrado');
            }

            // Formatear datos
            $punto->EstatusTexto = $punto->EsActivo == 1 ? 'Activo' : 'Inactivo';
            $punto->EstatusBadge = $punto->EsActivo == 1 ? 'success' : 'danger';
            $punto->LogoUrl = $punto->banco_logo 
                ? asset('assets/img/bancos/' . $punto->banco_logo) 
                : asset('assets/img/bancos/banco_default.png');

            return view('cpanel.entidades_bancarias.detalle_punto', [
                'punto' => $punto
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::detallePunto: ' . $e->getMessage());
            return redirect()->route('cpanel.puntos.index')
                ->with('error', 'Error al cargar el detalle del punto de venta');
        }
    }

    /**
     * Mostrar formulario para editar punto de venta
     */
    public function editarPunto($id)
    {
        try {
            Log::info('INICIO - BancosController::editarPunto', ['id' => $id]);

            session([
                'menu_active' => 'Entidades Bancarias',
                'submenu_active' => 'Puntos de Ventas'
            ]);

            $punto = DB::connection('sqlsrv')
                ->table('PuntosDeVenta')
                ->where('PuntoDeVentaId', $id)
                ->first();

            if (!$punto) {
                return redirect()->route('cpanel.puntos.index')
                    ->with('error', 'Punto de venta no encontrado');
            }

            // Obtener lista de bancos para el select
            $bancos = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('EsActivo', 1)
                ->orderBy('Nombre', 'asc')
                ->select('ID', 'Nombre')
                ->get();

            // Obtener lista de sucursales para el select
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->orderBy('Nombre', 'asc')
                ->select('ID', 'Nombre')
                ->get();

            return view('cpanel.entidades_bancarias.editar_punto', [
                'punto' => $punto,
                'bancos' => $bancos,
                'sucursales' => $sucursales
            ]);

        } catch (\Exception $e) {
            Log::error('Error en BancosController::editarPunto: ' . $e->getMessage());
            return redirect()->route('cpanel.puntos.index')
                ->with('error', 'Error al cargar el formulario de edición');
        }
    }

    /**
     * Actualizar un punto de venta
     */
    public function actualizarPunto(Request $request, $id)
    {
        try {
            Log::info('INICIO - BancosController::actualizarPunto', [
                'id' => $id,
                'data' => $request->all()
            ]);

            $request->validate([
                'banco_id' => 'required|exists:Bancos,ID',
                'sucursal_id' => 'required|exists:Sucursales,ID',
                'serial' => 'nullable|string|max:50',
                'descripcion' => 'required|string|max:200',
                'codigo' => 'nullable|integer',
                'es_activo' => 'required|in:0,1'
            ]);

            $punto = DB::connection('sqlsrv')
                ->table('PuntosDeVenta')
                ->where('PuntoDeVentaId', $id)
                ->first();

            if (!$punto) {
                return redirect()->back()
                    ->with('error', 'Punto de venta no encontrado');
            }

            DB::connection('sqlsrv')->table('PuntosDeVenta')
                ->where('PuntoDeVentaId', $id)
                ->update([
                    'BancoId' => $request->banco_id,
                    'SucursalId' => $request->sucursal_id,
                    'Serial' => $request->serial,
                    'Descripcion' => $request->descripcion,
                    'Codigo' => $request->codigo,
                    'EsActivo' => $request->es_activo
                ]);

            Log::info('Punto de venta actualizado exitosamente', [
                'id' => $id,
                'descripcion' => $request->descripcion
            ]);

            return redirect()->route('cpanel.puntos.index')
                ->with('success', 'Punto de venta actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete los datos necesarios');
        } catch (\Exception $e) {
            Log::error('Error en BancosController::actualizarPunto: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el punto de venta: ' . $e->getMessage());
        }
    }
}