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
use Illuminate\Support\Facades\Http;


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
                // ✅ Procesar logo con FileHelper
                $item->LogoUrl = FileHelper::getOrDownloadFile(
                    'assets/img/bancos/',                           // Carpeta
                    $item->Logo ?? '',                              // Archivo
                    'assets/img/bancos/banco_default.png'           // Default
                );
                
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
            // 2. PROCESAR LOGO CON DETECCIÓN DE ENTORNO
            // ================================================
            $logoName = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $logoName = 'banco_' . time() . '.' . $extension;

                // DETECTAR ENTORNO
                $environment = app()->environment();

                if ($environment === 'production') {
                    // ✅ PRODUCCIÓN - SmarterASP (usa public/)
                    $folder = 'assets/img/bancos/';
                    $physicalPath = base_path('public/' . $folder);

                    if (!is_dir($physicalPath)) {
                        mkdir($physicalPath, 0777, true);
                    }

                    $file->move($physicalPath, $logoName);

                    Log::info('Logo guardado en producción', [
                        'path' => $physicalPath,
                        'filename' => $logoName
                    ]);

                } else {
                    // ✅ LOCAL - storage/app/public/
                    $folder = 'assets/img/bancos/';
                    $storagePath = 'public/' . $folder;

                    if (!Storage::exists($storagePath)) {
                        Storage::makeDirectory($storagePath, 0755, true);
                    }

                    Storage::putFileAs($storagePath, $file, $logoName);

                    Log::info('Logo guardado en local (storage)', [
                        'path' => $storagePath,
                        'filename' => $logoName
                    ]);
                }
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
            $banco->LogoUrl = FileHelper::getOrDownloadFile(
                    'assets/img/bancos/',                           // Carpeta
                    $banco->Logo ?? '',                              // Archivo
                    'assets/img/bancos/banco_default.png'           // Default
                );

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

            $banco->LogoUrl = FileHelper::getOrDownloadFile(
                    'assets/img/bancos/',                           // Carpeta
                    $banco->Logo ?? '',                              // Archivo
                    'assets/img/bancos/banco_default.png'           // Default
                );

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
            // 3. PROCESAR LOGO CON DETECCIÓN DE ENTORNO
            // ================================================
            $logoName = $banco->Logo;

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $logoName = 'banco_' . time() . '.' . $extension;

                $environment = app()->environment();

                if ($environment === 'production') {
                    // ✅ PRODUCCIÓN
                    $folder = 'assets/img/bancos/';
                    $physicalPath = base_path('public/' . $folder);

                    if (!is_dir($physicalPath)) {
                        mkdir($physicalPath, 0777, true);
                    }

                    // Eliminar logo anterior
                    if ($banco->Logo) {
                        $oldFilePath = $physicalPath . $banco->Logo;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }

                    $file->move($physicalPath, $logoName);

                    Log::info('Logo actualizado en producción', [
                        'path' => $physicalPath,
                        'filename' => $logoName
                    ]);

                } else {
                    // ✅ LOCAL - storage/app/public/
                    $folder = 'assets/img/bancos/';
                    $storagePath = 'public/' . $folder;

                    if (!Storage::exists($storagePath)) {
                        Storage::makeDirectory($storagePath, 0755, true);
                    }

                    // Eliminar logo anterior
                    if ($banco->Logo) {
                        $oldFile = $storagePath . $banco->Logo;
                        if (Storage::exists($oldFile)) {
                            Storage::delete($oldFile);
                        }
                    }

                    Storage::putFileAs($storagePath, $file, $logoName);

                    Log::info('Logo actualizado en local (storage)', [
                        'path' => $storagePath,
                        'filename' => $logoName
                    ]);
                }
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
                'nombre' => $request->nombre,
                'logo' => $logoName
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

    public function verifyPagoMovil(Request $request)
    {
        try {
            // ================================================
            // 1. ASIGNAR MENU ACTIVO
            // ================================================
            session([
                'menu_active'    => 'Entidades Bancarias',
                'submenu_active' => 'Pago Movil'
            ]);

            // ================================================
            // 2. FILTROS DE FECHA
            // ================================================
            $fechaInicio = $request->input('fecha_inicio', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaFin    = $request->input('fecha_fin',    \Carbon\Carbon::now()->format('Y-m-d'));
            $sucursalId  = $request->input('sucursal_id', '');

            // ================================================
            // 3. CONSULTA DE PAGOS VERIFICADOS
            // ================================================
            $query = DB::connection('sqlsrv')
                ->table('PagoMovil as pm')
                ->join('Sucursales as s', 'pm.SucursalId', '=', 's.ID')
                ->leftJoin('SucursalPagoMovil as spm', 'pm.SucursalPagoMovilId', '=', 'spm.SucursalPagoMovilId')
                ->whereDate('pm.FechaPago', '>=', $fechaInicio)
                ->whereDate('pm.FechaPago', '<=', $fechaFin)
                ->when($sucursalId != '', fn($q) => $q->where('pm.SucursalId', $sucursalId))
                ->orderByDesc('pm.FechaVerificacion')
                ->orderByDesc('pm.PagoMovilId')
                ->select(
                    'pm.PagoMovilId',
                    'pm.SucursalId',
                    'pm.SucursalPagoMovilId',
                    'pm.CedulaPagador',
                    'pm.TelefonoPagador',
                    'pm.TelefonoDestino',
                    'pm.Referencia',
                    'pm.FechaPago',
                    'pm.Importe',
                    'pm.BancoOrigen',
                    'pm.BancoOrigenNombre',
                    'pm.StatusBdv',
                    'pm.MensajeBdv',
                    'pm.FechaVerificacion',
                    's.Nombre as sucursal_nombre',
                    'spm.Alias as alias_pago_movil'
                );

            $pagos = $query->get()->map(function ($p) {
                $p->FechaPagoFormateada = $p->FechaPago
                    ? \Carbon\Carbon::parse($p->FechaPago)->format('d/m/Y')
                    : '—';

                $p->FechaVerificacionFormateada = $p->FechaVerificacion
                    ? \Carbon\Carbon::parse($p->FechaVerificacion)->format('d/m/Y H:i')
                    : '—';

                return $p;
            });

            // ================================================
            // 4. SUCURSALES PARA EL FILTRO
            // ================================================
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 1)
                ->where('EsActiva', 1)
                ->orderBy('Nombre')
                ->select('ID', 'Nombre')
                ->get();

            // ================================================
            // 5. TOTALES RÁPIDOS (opcional, para las tarjetas)
            // ================================================
            $totalPagos   = $pagos->count();
            $totalMonto   = $pagos->sum('Importe');

            return view('cpanel.entidades_bancarias.verify_pago_movil', [
                'pagos'         => $pagos,
                'sucursales'    => $sucursales,
                'fechaInicio'   => $fechaInicio,
                'fechaFin'      => $fechaFin,
                'sucursalId'    => $sucursalId,
                'totalPagos'    => $totalPagos,
                'totalMonto'    => $totalMonto,
            ]);

        } catch (\Exception $e) {
            Log::error('Error verifyPagoMovil: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar vista pago movil');
        }
    }

    /**
     * Formulario para verificar un pago móvil.
     */
    public function formVerificarPagoMovil()
    {
        session([
            'menu_active'    => 'Entidades Bancarias',
            'submenu_active' => 'Pago Movil'
        ]);

        $configuraciones = DB::connection('sqlsrv')
            ->table('SucursalPagoMovil as spm')
            ->join('Sucursales as s', 'spm.SucursalId', '=', 's.ID')
            ->where('spm.Activo', 1)
            ->where('s.Tipo', 1)
            ->where('s.EsActiva', 1)
            ->orderBy('s.Nombre')
            ->orderBy('spm.Alias')
            ->select(
                'spm.SucursalPagoMovilId',
                'spm.SucursalId',
                'spm.Alias',
                'spm.Rif',
                'spm.Telefono',
                's.Nombre as sucursal_nombre'
            )
            ->get();

        return view('cpanel.entidades_bancarias.form_verificar_pago_movil', [
            'configuraciones' => $configuraciones,
        ]);
    }

    /**
     * Consulta el API del BDV y, si es exitoso, guarda el pago.
     */
    public function consultarPagoMovil(Request $request)
    {
        $request->validate([
            'SucursalPagoMovilId' => 'required|integer',
            'cedulaPagador'       => ['required', 'string', 'max:20', 'regex:/^[VEP][0-9]+$/'],
            'telefonoPagador'     => 'required|string|max:20',
            'referencia'          => 'required|string|max:30',
            'fechaPago'           => 'required|date_format:Y-m-d',
            'importe'             => 'required|numeric|min:0.01',
            'bancoOrigen'         => 'required|string|max:4',
        ]);

        try {
            // 1. Obtener la configuración (API Key, RIF, Teléfono, Endpoint)
            $config = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $request->SucursalPagoMovilId)
                ->where('Activo', 1)
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'La configuración de Pago Móvil no existe o está inactiva.',
                ], 422);
            }

            // 2. Validar duplicado (misma ref + fecha + banco + sucursal)
            $existe = DB::connection('sqlsrv')
                ->table('PagoMovil')
                ->where('SucursalId', $config->SucursalId)
                ->where('Referencia', trim($request->referencia))
                ->where('FechaPago', $request->fechaPago)
                ->where('BancoOrigen', trim($request->bancoOrigen))
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago ya fue verificado y registrado anteriormente.',
                ], 409);
            }

            // 3. Consultar API BDV
            $endpoint = $config->Endpoint
                ?: 'https://bdvconciliacion.banvenez.com:443/getMovement';

            $payload = [
                'cedulaPagador'   => strtoupper(trim($request->cedulaPagador)),
                'telefonoPagador' => trim($request->telefonoPagador),
                'telefonoDestino' => trim($config->Telefono),
                'referencia'      => trim($request->referencia),
                'fechaPago'       => $request->fechaPago,
                'importe'         => number_format((float) $request->importe, 2, '.', ''),
                'bancoOrigen'     => trim($request->bancoOrigen),
            ];

            $response = Http::timeout(20)
                ->withHeaders([
                    'X-API-Key'    => trim($config->ApiKey),
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post($endpoint, $payload);

            if ($response->failed()) {
                Log::error('BDV Conciliación: Error HTTP', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'config' => $config->SucursalPagoMovilId,
                ]);
                return response()->json([
                    'success' => false,
                    'code'    => $response->status(),
                    'message' => 'Error de comunicación con el banco (HTTP ' . $response->status() . ').',
                    'data'    => null,
                ], 200);
            }

            // 4. Evaluar respuesta del banco
            $resultado = $response->json();
            $code = $resultado['code'] ?? 0;

            $exitoso = ($code === 1000);
            $resultado['success'] = $exitoso;

            // Mensaje amigable según el código del banco
            if ($exitoso) {
                $resultado['mensaje_amigable'] = 'Pago verificado correctamente.';
            } else {
                $resultado['mensaje_amigable'] = match ($code) {
                    1010 => str_contains($resultado['message'] ?? '', 'null')
                                ? 'Faltan datos obligatorios en la solicitud.'
                                : 'No se encontró ningún pago con los datos ingresados. Verifica la referencia, fecha, monto, cédula y banco.',
                    400  => 'El formato de la solicitud no es válido. Contacta al administrador.',
                    default => $resultado['message'] ?? 'No se pudo verificar el pago.',
                };
            }

            // 5. Si fue exitoso, guardar en la tabla
            if ($exitoso) {
                DB::connection('sqlsrv')->table('PagoMovil')->insert([
                    'SucursalPagoMovilId' => $config->SucursalPagoMovilId,
                    'SucursalId'          => $config->SucursalId,
                    'CedulaPagador'       => strtoupper(trim($request->cedulaPagador)),
                    'TelefonoPagador'     => trim($request->telefonoPagador),
                    'TelefonoDestino'     => $config->Telefono,
                    'Referencia'          => trim($request->referencia),
                    'FechaPago'           => $request->fechaPago,
                    'Importe'             => (float) $request->importe,
                    'BancoOrigen'         => trim($request->bancoOrigen),
                    'BancoOrigenNombre'   => $this->nombreBanco(trim($request->bancoOrigen)),
                    'StatusBdv'           => $resultado['data']['status'] ?? '1000',
                    'MensajeBdv'          => $resultado['data']['reason'] ?? $resultado['message'] ?? null,
                    'FechaVerificacion'   => now(),
                    'UsuarioVerifico'     => session('usuario_id') ?? null,
                ]);
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('Error consultarPagoMovil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detalle de un pago verificado.
     */
    public function detallePagoMovil($id)
    {
        session([
            'menu_active'    => 'Entidades Bancarias',
            'submenu_active' => 'Pago Movil'
        ]);

        $pago = DB::connection('sqlsrv')
            ->table('PagoMovil as pm')
            ->join('Sucursales as s', 'pm.SucursalId', '=', 's.ID')
            ->leftJoin('SucursalPagoMovil as spm', 'pm.SucursalPagoMovilId', '=', 'spm.SucursalPagoMovilId')
            ->where('pm.PagoMovilId', $id)
            ->select(
                'pm.*',
                's.Nombre as sucursal_nombre',
                'spm.Alias as alias_pago_movil',
                'spm.Rif as config_rif'
            )
            ->first();

        if (!$pago) {
            return redirect()
                ->route('cpanel.pago.movil.index')
                ->with('error', 'Pago móvil no encontrado.');
        }

        $pago->FechaPagoFormateada = $pago->FechaPago
            ? \Carbon\Carbon::parse($pago->FechaPago)->format('d/m/Y')
            : '—';

        $pago->FechaVerificacionFormateada = $pago->FechaVerificacion
            ? \Carbon\Carbon::parse($pago->FechaVerificacion)->format('d/m/Y H:i:s')
            : '—';

        return view('cpanel.entidades_bancarias.detalle_pago_movil', [
            'pago' => $pago,
        ]);
    }

    /**
     * Helper: nombre del banco por código SUDEBAN.
     */
    private function nombreBanco($codigo)
    {
        $bancos = [
            '0102' => 'Banco de Venezuela',
            '0104' => 'Venezolano de Crédito',
            '0105' => 'Mercantil',
            '0108' => 'Provincial',
            '0114' => 'Bancaribe',
            '0115' => 'Banco Exterior',
            '0128' => 'Banco Caroní',
            '0134' => 'Banesco',
            '0137' => 'Sofitasa',
            '0138' => 'Banco Plaza',
            '0146' => 'Bangente',
            '0151' => 'BFC Banco Fondo Común',
            '0156' => '100% Banco',
            '0157' => 'DelSur',
            '0163' => 'Banco del Tesoro',
            '0166' => 'Banco Agrícola de Venezuela',
            '0168' => 'Bancrecer',
            '0169' => 'Mi Banco',
            '0171' => 'Banco Activo',
            '0172' => 'Bancamiga',
            '0174' => 'Banplus',
            '0175' => 'Banco Bicentenario',
            '0177' => 'Banfanb',
            '0191' => 'BNC',
        ];
        return $bancos[$codigo] ?? 'Desconocido';
    }

    /**
     * Listado de configuraciones Pago Móvil.
     */
    public function configuracionPagoMovil(Request $request)
    {
        try {
            session([
                'menu_active'    => 'Entidades Bancarias',
                'submenu_active' => 'Configuracion - Pago Movil'
            ]);

            $query = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil as spm')
                ->join('Sucursales as s', 'spm.SucursalId', '=', 's.ID')
                ->select(
                    'spm.SucursalPagoMovilId',
                    'spm.SucursalId',
                    'spm.Alias',
                    'spm.Rif',
                    'spm.Telefono',
                    'spm.ApiKey',
                    'spm.Endpoint',
                    'spm.Activo',
                    'spm.FechaCreacion',
                    's.Nombre as sucursal_nombre'
                )
                ->orderByDesc('spm.SucursalPagoMovilId');

            // Filtros
            if ($request->filled('sucursal_id')) {
                $query->where('spm.SucursalId', $request->sucursal_id);
            }
            if ($request->filled('activo') && $request->activo !== '') {
                $query->where('spm.Activo', (int) $request->activo);
            }

            $configuraciones = $query->get();

            // Sucursales tipo tienda activas (para filtro y para el form)
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 1)
                ->where('EsActiva', 1)
                ->orderBy('Nombre')
                ->select('ID', 'Nombre')
                ->get();

            return view('cpanel.entidades_bancarias.configuracion_pago_movil', [
                'configuraciones' => $configuraciones,
                'sucursales'      => $sucursales,
                'sucursalId'      => $request->sucursal_id,
                'activo'          => $request->activo,
            ]);

        } catch (\Exception $e) {
            Log::error('Error configuracionPagoMovil: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar vista Configuracion - Pago Movil');
        }
    }

    /**
     * Formulario de creación.
     */
    public function crearConfiguracionPagoMovil()
    {
        session([
            'menu_active'    => 'Entidades Bancarias',
            'submenu_active' => 'Configuracion - Pago Movil'
        ]);

        $sucursales = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('Tipo', 1)
            ->where('EsActiva', 1)
            ->orderBy('Nombre')
            ->select('ID', 'Nombre')
            ->get();

        return view('cpanel.entidades_bancarias.configuracion_pago_movil_form', [
            'configuracion' => null,
            'sucursales'    => $sucursales,
        ]);
    }

    /**
     * Guardar nueva configuración.
     */
    public function guardarConfiguracionPagoMovil(Request $request)
    {
        $request->validate([
            'SucursalId' => 'required|integer',
            'Alias'      => 'required|string|max:100',
            'Rif'        => 'required|string|max:20',
            'Telefono'   => 'required|string|max:20',
            'ApiKey'     => 'required|string|max:100',
            'Endpoint'   => 'nullable|string|max:200',
        ]);

        try {
            // Validar que la sucursal exista, sea Tipo 1 y Activa
            $sucursalValida = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $request->SucursalId)
                ->where('Tipo', 1)
                ->where('EsActiva', 1)
                ->exists();

            if (!$sucursalValida) {
                return back()->withInput()
                    ->with('error', 'La sucursal seleccionada no es válida.');
            }

            // Validar duplicado SucursalId + Telefono
            $existe = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalId', $request->SucursalId)
                ->where('Telefono', trim($request->Telefono))
                ->exists();

            if ($existe) {
                return back()->withInput()
                    ->with('error', 'Ya existe una configuración con ese teléfono en la sucursal seleccionada.');
            }

            DB::connection('sqlsrv')->table('SucursalPagoMovil')->insert([
                'SucursalId'      => $request->SucursalId,
                'Alias'           => $request->Alias,
                'Rif'             => strtoupper(trim($request->Rif)),
                'Telefono'        => trim($request->Telefono),
                'ApiKey'          => trim($request->ApiKey),
                'Endpoint'        => $request->Endpoint ?: null,
                'Activo'          => 1,
                'FechaCreacion'   => now(),
                'UsuarioCreacion' => session('usuario_id') ?? null,
            ]);

            return redirect()
                ->route('cpanel.configuracion.pago.movil')
                ->with('success', 'Configuración creada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error guardarConfiguracionPagoMovil: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de edición.
     */
    public function editarConfiguracionPagoMovil($id)
    {
        session([
            'menu_active'    => 'Entidades Bancarias',
            'submenu_active' => 'Configuracion - Pago Movil'
        ]);

        $configuracion = DB::connection('sqlsrv')
            ->table('SucursalPagoMovil')
            ->where('SucursalPagoMovilId', $id)
            ->first();

        if (!$configuracion) {
            return redirect()
                ->route('cpanel.configuracion.pago.movil')
                ->with('error', 'Configuración no encontrada.');
        }

        $sucursales = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('Tipo', 1)
            ->where('EsActiva', 1)
            ->orderBy('Nombre')
            ->select('ID', 'Nombre')
            ->get();

        return view('cpanel.entidades_bancarias.configuracion_pago_movil_form', [
            'configuracion' => $configuracion,
            'sucursales'    => $sucursales,
        ]);
    }

    /**
     * Actualizar configuración.
     */
    public function actualizarConfiguracionPagoMovil(Request $request, $id)
    {
        $request->validate([
            'SucursalId' => 'required|integer',
            'Alias'      => 'required|string|max:100',
            'Rif'        => 'required|string|max:20',
            'Telefono'   => 'required|string|max:20',
            'ApiKey'     => 'required|string|max:100',
            'Endpoint'   => 'nullable|string|max:200',
        ]);

        try {
            $config = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->first();

            if (!$config) {
                return back()->with('error', 'Configuración no encontrada.');
            }

            $sucursalValida = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $request->SucursalId)
                ->where('Tipo', 1)
                ->where('EsActiva', 1)
                ->exists();

            if (!$sucursalValida) {
                return back()->withInput()
                    ->with('error', 'La sucursal seleccionada no es válida.');
            }

            $existe = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalId', $request->SucursalId)
                ->where('Telefono', trim($request->Telefono))
                ->where('SucursalPagoMovilId', '!=', $id)
                ->exists();

            if ($existe) {
                return back()->withInput()
                    ->with('error', 'Ya existe otra configuración con ese teléfono en esa sucursal.');
            }

            DB::connection('sqlsrv')->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->update([
                    'SucursalId'          => $request->SucursalId,
                    'Alias'               => $request->Alias,
                    'Rif'                 => strtoupper(trim($request->Rif)),
                    'Telefono'            => trim($request->Telefono),
                    'ApiKey'              => trim($request->ApiKey),
                    'Endpoint'            => $request->Endpoint ?: null,
                    'Activo'              => $request->has('Activo') ? (int) $request->Activo : $config->Activo,
                    'FechaModificacion'   => now(),
                    'UsuarioModificacion' => session('usuario_id') ?? null,
                ]);

            return redirect()
                ->route('cpanel.configuracion.pago.movil')
                ->with('success', 'Configuración actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error actualizarConfiguracionPagoMovil: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Activar / desactivar.
     */
    public function toggleConfiguracionPagoMovil($id)
    {
        try {
            $config = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->first();

            if (!$config) {
                return back()->with('error', 'Configuración no encontrada.');
            }

            DB::connection('sqlsrv')->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->update([
                    'Activo'              => $config->Activo ? 0 : 1,
                    'FechaModificacion'   => now(),
                    'UsuarioModificacion' => session('usuario_id') ?? null,
                ]);

            return redirect()
                ->route('cpanel.configuracion.pago.movil')
                ->with('success', 'Estado actualizado.');

        } catch (\Exception $e) {
            Log::error('Error toggleConfiguracionPagoMovil: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar estado.');
        }
    }

    /**
     * Eliminar configuración.
     */
    public function eliminarConfiguracionPagoMovil($id)
    {
        try {
            $config = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->first();

            if (!$config) {
                return back()->with('error', 'Configuración no encontrada.');
            }

            // (En Bloque 2 validaremos que no tenga pagos asociados)

            DB::connection('sqlsrv')->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $id)
                ->delete();

            return redirect()
                ->route('cpanel.configuracion.pago.movil')
                ->with('success', 'Configuración eliminada.');

        } catch (\Exception $e) {
            Log::error('Error eliminarConfiguracionPagoMovil: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Formulario público para verificar un pago móvil (sin layout, sin login).
     * TEMPORAL - Solo para pruebas.
     */
    public function formPublicoPagoMovil()
    {
        $configuraciones = DB::connection('sqlsrv')
            ->table('SucursalPagoMovil as spm')
            ->join('Sucursales as s', 'spm.SucursalId', '=', 's.ID')
            ->where('spm.Activo', 1)
            ->where('s.Tipo', 1)
            ->where('s.EsActiva', 1)
            ->orderBy('s.Nombre')
            ->orderBy('spm.Alias')
            ->select(
                'spm.SucursalPagoMovilId',
                'spm.SucursalId',
                'spm.Alias',
                'spm.Rif',
                'spm.Telefono',
                's.Nombre as sucursal_nombre'
            )
            ->get();

        return view('publico.pago_movil_form', [
            'configuraciones' => $configuraciones,
        ]);
    }

    /**
     * Consulta pública al API del BDV (sin guardar en BD).
     * TEMPORAL - Solo para pruebas.
     */
    public function consultarPagoMovilPublico(Request $request)
    {
        $request->validate([
            'SucursalPagoMovilId' => 'required|integer',
            'cedulaPagador'       => ['required', 'string', 'max:20', 'regex:/^[VEP][0-9]+$/'],
            'telefonoPagador'     => 'required|string|max:20',
            'referencia'          => 'required|string|max:30',
            'fechaPago'           => 'required|date_format:Y-m-d',
            'importe'             => 'required|numeric|min:0.01',
            'bancoOrigen'         => 'required|string|max:4',
        ]);

        $payload  = null;
        $endpoint = null;

        try {
            // 1. Obtener la configuración
            $config = DB::connection('sqlsrv')
                ->table('SucursalPagoMovil')
                ->where('SucursalPagoMovilId', $request->SucursalPagoMovilId)
                ->where('Activo', 1)
                ->first();

            if (!$config) {
                Log::warning('BDV [público]: Config no encontrada o inactiva', [
                    'SucursalPagoMovilId' => $request->SucursalPagoMovilId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'La configuración de Pago Móvil no existe o está inactiva.',
                ], 422);
            }

            // 2. Consultar API BDV
            $endpoint = $config->Endpoint
                ?: 'https://bdvconciliacion.banvenez.com:443/getMovement';

            $payload = [
                'cedulaPagador'   => strtoupper(trim($request->cedulaPagador)),
                'telefonoPagador' => trim($request->telefonoPagador),
                'telefonoDestino' => trim($config->Telefono),
                'referencia'      => trim($request->referencia),
                'fechaPago'       => $request->fechaPago,
                'importe'         => number_format((float) $request->importe, 2, '.', ''),
                'bancoOrigen'     => trim($request->bancoOrigen),
            ];

            Log::info('BDV [público]: Iniciando consulta', [
                'endpoint'  => $endpoint,
                'payload'   => $payload,
                'config_id' => $config->SucursalPagoMovilId,
            ]);

            $response = Http::timeout(20)
                ->withHeaders([
                    'X-API-Key'    => trim($config->ApiKey),
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post($endpoint, $payload);

            Log::info('BDV [público]: Respuesta recibida', [
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('BDV [público]: Error HTTP', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'config' => $config->SucursalPagoMovilId,
                ]);
                return response()->json([
                    'success' => false,
                    'code'    => $response->status(),
                    'message' => 'Error de comunicación con el banco (HTTP ' . $response->status() . ').',
                    'data'    => null,
                ], 200);
            }

            // 3. Evaluar respuesta
            $resultado = $response->json();
            $code = $resultado['code'] ?? 0;
            $exitoso = ($code === 1000);
            $resultado['success'] = $exitoso;

            if ($exitoso) {
                $resultado['mensaje_amigable'] = 'Pago verificado correctamente.';
            } else {
                $resultado['mensaje_amigable'] = match ($code) {
                    1010 => str_contains($resultado['message'] ?? '', 'null')
                                ? 'Faltan datos obligatorios en la solicitud.'
                                : 'No se encontró ningún pago con los datos ingresados.',
                    400  => 'El formato de la solicitud no es válido.',
                    default => $resultado['message'] ?? 'No se pudo verificar el pago.',
                };
            }

            // 4. Guardar si es exitoso
            if ($exitoso) {
                DB::connection('sqlsrv')->table('PagoMovil')->insert([
                    'SucursalPagoMovilId' => $config->SucursalPagoMovilId,
                    'SucursalId'          => $config->SucursalId,
                    'CedulaPagador'       => strtoupper(trim($request->cedulaPagador)),
                    'TelefonoPagador'     => trim($request->telefonoPagador),
                    'TelefonoDestino'     => $config->Telefono,
                    'Referencia'          => trim($request->referencia),
                    'FechaPago'           => $request->fechaPago,
                    'Importe'             => (float) $request->importe,
                    'BancoOrigen'         => trim($request->bancoOrigen),
                    'BancoOrigenNombre'   => $this->nombreBanco(trim($request->bancoOrigen)),
                    'StatusBdv'           => $resultado['data']['status'] ?? '1000',
                    'MensajeBdv'          => $resultado['data']['reason'] ?? $resultado['message'] ?? null,
                    'FechaVerificacion'   => now(),
                    'UsuarioVerifico'     => session('usuario_id') ?? null,
                ]);

                Log::info('BDV [público]: Pago guardado', [
                    'referencia' => $request->referencia,
                    'importe'    => $request->importe,
                ]);
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('BDV [público]: Excepción', [
                'message'  => $e->getMessage(),
                'code'     => $e->getCode(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
                'endpoint' => $endpoint,
                'payload'  => $payload,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el pago: ' . $e->getMessage(),
            ], 500);
        }
    }
}