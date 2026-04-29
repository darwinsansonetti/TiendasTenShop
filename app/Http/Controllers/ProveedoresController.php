<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;
use App\Models\Proveedor;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

use App\Helpers\FileHelper;

class ProveedoresController extends Controller
{
    public function listado_proveedores_mercancia(Request $request)
    {
        try {
            // Tipo Mercancia = 0
            $tipo = 0;
            
            // Obtener listado de proveedores
            $proveedoresMercancia = GeneralHelper::BuscarListadoProveedores($tipo);
            
            // Configurar menú activo
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Listado Proveedores'
            ]);
            
            return view('cpanel.proveedores.listado_mercancia', compact('proveedoresMercancia'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_proveedores_mercancia: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de proveedores: ' . $e->getMessage());
        }
    }

    public function crearProveedor()
    {
        try {
            // Obtener lista de bancos para el select
            $bancos = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('EsActivo', 1)
                ->orderBy('Nombre', 'asc')
                ->get();
            
            session([
                'menu_active' => 'Proveedores',
                'submenu_active' => 'Mercancia'
            ]);
            
            return view('cpanel.proveedores.crear_proveedor', compact('bancos'));
            
        } catch (\Exception $e) {
            \Log::error('Error en crearProveedor: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }
    
    public function guardarProveedor(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'Tipo' => 'required|in:0,1',
                'Nombre' => 'required|string|max:150',
                'RifCedula' => 'nullable|string|max:50',
                'Direccion' => 'nullable|string|max:500',
                'TelefonoMovil' => 'required|string|max:20',
                'TelefonoFijo' => 'nullable|string|max:20',
                'CorreoElectronico' => 'nullable|email|max:100',
                'FechaCreacion' => 'required|date',
                'Estatus' => 'required|in:0,1',
                'NumeroDeCuenta' => 'nullable|string|max:50',
                'BancoId' => 'nullable|integer',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            // Verificar si el proveedor ya existe
            $existe = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('Nombre', $request->Nombre)
                ->first();
            
            if ($existe) {
                return back()->with('error', 'Ya existe un proveedor con este nombre')->withInput();
            }
            
            // Guardar logo si se subió
            $urlImagen = null;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $nombreLogo = time() . '_' . str_replace(' ', '_', $request->Nombre) . '.' . $logo->getClientOriginalExtension();
                
                // Guardar en storage/app/public/images/proveedores/
                $logo->storeAs('public/images/proveedores', $nombreLogo);
                
                // Guardar solo el nombre del archivo en la BD
                $urlImagen = $nombreLogo;
            }
            
            // Insertar nuevo proveedor
            $proveedorId = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->insertGetId([
                    'Nombre' => $request->Nombre,
                    'Rif_Cedula' => $request->RifCedula,
                    'Direccion' => $request->Direccion,
                    'TelefonoMovil' => $request->TelefonoMovil,
                    'TelefonoFijo' => $request->TelefonoFijo,
                    'CorreoElectronico' => $request->CorreoElectronico,
                    'FechaCreacion' => Carbon::parse($request->FechaCreacion),
                    'Tipo' => $request->Tipo,
                    'Estatus' => 0,
                    'NumeroDeCuenta' => $request->NumeroDeCuenta,
                    'BancoId' => $request->BancoId,
                    'UrlImagen' => $urlImagen,
                    'SucursalId' => 0
                ]);
            
            // Redirigir según el tipo
            if ($request->Tipo == 0) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('success', 'Proveedor de mercancía creado correctamente');
            } else {
                return redirect()->route('cpanel.proveedores.servicios')
                    ->with('success', 'Proveedor de servicios creado correctamente');
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al guardar proveedor: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar el proveedor: ' . $e->getMessage())->withInput();
        }
    }
    
    public function editarProveedor($id)
    {
        try {
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Proveedor no encontrado');
            }
            
            // Obtener lista de bancos
            $bancos = DB::connection('sqlsrv')
                ->table('Bancos')
                ->where('EsActivo', 1)
                ->orderBy('Nombre', 'asc')
                ->get();
            
            // Obtener imagen del proveedor
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/proveedores/',
                $proveedor->UrlImagen,
                'assets/img/adminlte/img/proveedor_default.png'
            );
            
            session([
                'menu_active' => 'Proveedores',
                'submenu_active' => 'Mercancia'
            ]);
            
            return view('cpanel.proveedores.editar_proveedor', compact('proveedor', 'bancos', 'imgSrc'));
            
        } catch (\Exception $e) {
            \Log::error('Error en editarProveedor: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar proveedor
     */
    public function actualizarProveedor(Request $request, $id)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'Tipo' => 'required|in:0,1',
                'Nombre' => 'required|string|max:150',
                'RifCedula' => 'nullable|string|max:50',
                'Direccion' => 'nullable|string|max:500',
                'TelefonoMovil' => 'required|string|max:20',
                'TelefonoFijo' => 'nullable|string|max:20',
                'CorreoElectronico' => 'nullable|email|max:100',
                'FechaCreacion' => 'required|date',
                'Estatus' => 'required|in:0,1',
                'NumeroDeCuenta' => 'nullable|string|max:50',
                'BancoId' => 'nullable|integer',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            // Verificar si el proveedor existe
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return back()->with('error', 'Proveedor no encontrado');
            }
            
            // Verificar nombre duplicado (excluyendo el actual)
            $existe = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('Nombre', $request->Nombre)
                ->where('ProveedorId', '!=', $id)
                ->first();
            
            if ($existe) {
                return back()->with('error', 'Ya existe otro proveedor con este nombre')->withInput();
            }
            
            // Guardar nuevo logo si se subió
            $urlImagen = $proveedor->UrlImagen;
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($urlImagen) {
                    $rutaAnterior = storage_path('app/public/images/proveedores/' . $urlImagen);
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                $logo = $request->file('logo');
                $nombreLogo = time() . '_' . str_replace(' ', '_', $request->Nombre) . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('public/images/proveedores', $nombreLogo);
                $urlImagen = $nombreLogo;
            }
            
            // Actualizar proveedor
            DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->update([
                    'Nombre' => $request->Nombre,
                    'Rif_Cedula' => $request->RifCedula,
                    'Direccion' => $request->Direccion,
                    'TelefonoMovil' => $request->TelefonoMovil,
                    'TelefonoFijo' => $request->TelefonoFijo,
                    'CorreoElectronico' => $request->CorreoElectronico,
                    'FechaCreacion' => Carbon::parse($request->FechaCreacion),
                    'Tipo' => $request->Tipo,
                    'Estatus' => $request->Estatus,
                    'NumeroDeCuenta' => $request->NumeroDeCuenta,
                    'BancoId' => $request->BancoId,
                    'UrlImagen' => $urlImagen
                ]);
            
            // Redirigir según el tipo
            if ($request->Tipo == 0) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('success', 'Proveedor actualizado correctamente');
            } else {
                return redirect()->route('cpanel.proveedores.servicios')
                    ->with('success', 'Proveedor actualizado correctamente');
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Eliminar proveedor
     */
    public function eliminarProveedor(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ]);
            }
            
            // Solo cambiar el estatus a 0 (Inactivo) - NO eliminar físicamente
            DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->update([
                    'Estatus' => 1
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Proveedor desactivado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al desactivar proveedor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar el proveedor: ' . $e->getMessage()
            ]);
        }
    }
    
    public function detalleProveedor($id)
    {
        try {
            if (!$id) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Debe indicar un código de proveedor');
            }
            
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'No se pudo encontrar un proveedor');
            }
            
            // Obtener imagen
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/proveedores/',
                $proveedor->UrlImagen,
                'assets/img/adminlte/img/proveedor_default.png'
            );
            
            // ============================================
            // FACTURAS VIGENTES (En Proceso + Recibiendo + Recibida)
            // ============================================
            $facturasVigentes = $this->buscarFacturasActivas($id);
            
            // ============================================
            // PAGOS VIGENTES
            // ============================================
            $transaccionesVigentes = $this->buscarPagosVigentesProveedores($id);
            
            // ============================================
            // PRODUCTOS (si es Mercancía)
            // ============================================
            $productos = null;
            if ($proveedor->Tipo == 0) {
                $sucursalId = session('sucursal_id'); // Ajusta según tu lógica
                $productos = $this->buscarProductosDelProveedor($sucursalId, $id);
            }
            
            // ============================================
            // ESTADO DE CUENTA DEL PROVEEDOR
            // ============================================
            $estadoCuenta = $this->generarEstadoCuentaProveedor($id, $facturasVigentes);

            // ============================================
            // BANCO
            // ============================================
            $banco = null;
            if ($proveedor->BancoId) {
                $banco = DB::connection('sqlsrv')
                    ->table('Bancos')
                    ->where('Id', $proveedor->BancoId)
                    ->first();
            }

            // BALANCE DE FACTURAS
            $balanceFacturas = new \stdClass();
            $balanceFacturas->totalFacturas = $facturasVigentes->sum('MontoDivisa');
            $balanceFacturas->totalPagado = $facturasVigentes->sum('total_pagado');
            $balanceFacturas->saldoPendiente = $balanceFacturas->totalFacturas - $balanceFacturas->totalPagado;
            $balanceFacturas->porcentajePagado = $balanceFacturas->totalFacturas > 0 
                ? round(($balanceFacturas->totalPagado * 100) / $balanceFacturas->totalFacturas, 2) 
                : 0;
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Listado Proveedores'
            ]);
            
            return view('cpanel.proveedores.detalle_proveedor', compact(
                'proveedor',
                'imgSrc',
                'facturasVigentes',
                'transaccionesVigentes',
                'productos',
                'banco',
                'estadoCuenta',
                'balanceFacturas'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detalleProveedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.listado')
                ->with('error', 'Error al cargar el detalle: ' . $e->getMessage());
        }
    }
    
    private function generarEstadoCuentaProveedor($proveedorId, $facturasVigentes)
    {
        try {
            $operaciones = collect();
            
            foreach ($facturasVigentes as $factura) {
                // 1. Agregar la factura como operación (cargo)
                $operaciones->push((object)[
                    'fecha' => Carbon::parse($factura->FechaCreacion),
                    'descripcion' => "Emisión de Factura: {$factura->Numero}",
                    'referencia' => $factura->Numero,
                    'monto_divisa' => $factura->MontoDivisa,
                    'monto_bs' => $factura->MontoBs,
                    'pago_divisa' => 0,
                    'pago_bs' => 0,
                    'tipo' => 'factura'
                ]);
                
                // 2. Agregar los pagos de la factura (abonos)
                $pagos = DB::connection('sqlsrv')
                    ->table('TransaccionesProveedor as tp')
                    ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                    ->where('tp.FacturaId', $factura->ID)
                    ->select([
                        't.Fecha',
                        't.Descripcion',
                        't.NumeroOperacion as referencia',
                        't.MontoDivisaAbonado as monto_divisa',
                        't.MontoAbonado as monto_bs'
                    ])
                    ->get();
                
                foreach ($pagos as $pago) {
                    $operaciones->push((object)[
                        'fecha' => Carbon::parse($pago->Fecha),
                        'descripcion' => $pago->Descripcion ?? 'Pago de factura',
                        'referencia' => $pago->referencia,
                        'monto_divisa' => 0,
                        'monto_bs' => 0,
                        'pago_divisa' => $pago->monto_divisa,
                        'pago_bs' => $pago->monto_bs,
                        'tipo' => 'pago'
                    ]);
                }
            }
            
            // Ordenar por fecha (ascendente)
            $operaciones = $operaciones->sortBy('fecha')->values();
            
            // Calcular saldo corrido
            $saldoDivisa = 0;
            $saldoBs = 0;
            
            foreach ($operaciones as $operacion) {
                $saldoDivisa += ($operacion->monto_divisa ?? 0) - ($operacion->pago_divisa ?? 0);
                $saldoBs += ($operacion->monto_bs ?? 0) - ($operacion->pago_bs ?? 0);
                
                $operacion->saldo_divisa = $saldoDivisa;
                $operacion->saldo_bs = $saldoBs;
            }
            
            // Revertir para mostrar orden cronológico descendente (más reciente primero)
            $operaciones = $operaciones->reverse()->values();
            
            // Calcular totales
            $totalFacturas = $facturasVigentes->sum('MontoDivisa');
            $totalPagos = $facturasVigentes->sum('total_pagado');
            $saldoPendiente = $totalFacturas - $totalPagos;
            $porcentajePagado = $totalFacturas > 0 ? round(($totalPagos * 100) / $totalFacturas, 2) : 0;
            
            return [
                'operaciones' => $operaciones,
                'totales' => [
                    'facturas' => $totalFacturas,
                    'pagos' => $totalPagos,
                    'saldo' => $saldoPendiente,
                    'porcentaje_pagado' => $porcentajePagado
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error en generarEstadoCuentaProveedor: ' . $e->getMessage());
            return null;
        }
    }

    private function buscarFacturasActivas($proveedorId)
    {
        try {
            $facturas = collect();
            
            // 1. Facturas EN PROCESO (Estatus = 1)
            $enProceso = $this->buscarListadoFacturas($proveedorId, 1);
            if ($enProceso && $enProceso->count() > 0) {
                $facturas = $facturas->concat($enProceso);
            }
            
            // 2. Facturas RECIBIENDO (Estatus = 2)
            $recibiendo = $this->buscarListadoFacturas($proveedorId, 2);
            if ($recibiendo && $recibiendo->count() > 0) {
                $facturas = $facturas->concat($recibiendo);
            }
            
            // 3. Facturas RECIBIDA (Estatus = 4)
            $recibida = $this->buscarListadoFacturas($proveedorId, 4);
            if ($recibida && $recibida->count() > 0) {
                $facturas = $facturas->concat($recibida);
            }
            
            return $facturas;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarFacturasActivas: ' . $e->getMessage());
            return collect();
        }
    }
    
    private function buscarListadoFacturas($proveedorId, $estatus)
    {
        try {
            $facturas = DB::connection('sqlsrv')
                ->table('Facturas as f')
                ->leftJoin('Proveedores as p', 'f.ProveedorId', '=', 'p.ProveedorId')
                ->leftJoin('Sucursales as s', 'f.SucursalId', '=', 's.ID')
                ->leftJoin('FacturaDetalles as fd', 'f.ID', '=', 'fd.FacturaId')
                ->where('f.ProveedorId', $proveedorId)
                ->where('f.Estatus', $estatus)
                ->groupBy(
                    'f.ID', 'f.ProveedorId', 'f.Numero', 'f.Serie', 'f.FechaCreacion',
                    'f.FechaDespacho', 'f.FechaCierre', 'f.Estatus', 'f.ContenedorId',
                    'f.Traspaso', 'f.PorcentajeCosto', 'f.PorcentajeDescuento',
                    'f.MontoDescuento', 'f.EsCargarFleteEnFactura', 'f.Tipo',
                    'f.SucursalId', 'f.DivisaValorId', 'f.MontoDivisa', 'f.MontoBs',
                    'f.Descripcion', 'f.TasaDeCambio', 'f.MonedaPrincipal',
                    'p.Nombre', 's.Nombre'
                )
                ->orderBy('f.FechaCreacion', 'asc')
                ->select([
                    'f.ID',
                    'f.ProveedorId',
                    'f.Numero',
                    'f.Serie',
                    'f.FechaCreacion',
                    'f.FechaDespacho',
                    'f.FechaCierre',
                    'f.Estatus',
                    'f.ContenedorId',
                    'f.Traspaso',
                    'f.PorcentajeCosto',
                    'f.PorcentajeDescuento',
                    'f.MontoDescuento',
                    'f.EsCargarFleteEnFactura',
                    'f.Tipo',
                    'f.SucursalId',
                    'f.DivisaValorId',
                    // MontoDivisa = detalles + traspaso
                    DB::raw('COALESCE(SUM(fd.CantidadEmitida * fd.CostoDivisa), 0) + COALESCE(f.Traspaso, 0) as MontoDivisa'),
                    DB::raw('COALESCE(SUM(fd.CantidadEmitida * fd.CostoBs), 0) as MontoBs'),
                    'f.Descripcion',
                    'f.TasaDeCambio',
                    'f.MonedaPrincipal',
                    'p.Nombre as proveedor_nombre',
                    's.Nombre as sucursal_nombre'
                ])
                ->get();
            
            // Calcular pagos por factura y saldo pendiente
            foreach ($facturas as $factura) {
                // Sumar pagos desde TransaccionesProveedor
                $pagos = DB::connection('sqlsrv')
                    ->table('TransaccionesProveedor as tp')
                    ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                    ->where('tp.FacturaId', $factura->ID)
                    ->sum('t.MontoDivisaAbonado');
                
                $factura->total_pagado = $pagos ?? 0;
                // Saldo pendiente = MontoDivisa (incluye traspaso) - pagos
                $factura->saldo_pendiente = max(0, ($factura->MontoDivisa ?? 0) - ($factura->total_pagado ?? 0));
            }
            
            return $facturas;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarListadoFacturas: ' . $e->getMessage());
            return collect();
        }
    }

    private function buscarPagosVigentesProveedores($proveedorId)
    {
        try {
            // Primero, obtener facturas activas del proveedor
            $facturasVigentes = $this->buscarFacturasActivas($proveedorId);
            
            if (!$facturasVigentes || $facturasVigentes->count() == 0) {
                return collect();
            }
            
            $facturasIds = $facturasVigentes->pluck('ID')->toArray();
            
            // Buscar transacciones asociadas a esas facturas
            $transacciones = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor as tp')
                ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                ->whereIn('tp.FacturaId', $facturasIds)
                ->where('tp.ProveedorId', $proveedorId)
                ->orderBy('t.Fecha', 'desc')
                ->select([
                    't.ID as TransaccionId',
                    't.Fecha',
                    't.MontoDivisaAbonado as MontoDivisa',
                    't.MontoAbonado as MontoBs',
                    't.TasaDeCambio as Tasa',
                    't.Estatus',
                    't.Descripcion',
                    't.NumeroOperacion',
                    't.Observacion'
                ])
                ->get();
            
            return $transacciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarPagosVigentesProveedores: ' . $e->getMessage());
            return collect();
        }
    }

    private function buscarAbonosFactura($facturaId)
    {
        try {
            $pagos = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor as tp')
                ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                ->where('tp.FacturaId', $facturaId)
                ->orderBy('t.Fecha', 'desc')
                ->select([
                    't.ID as TransaccionId',
                    't.Fecha',
                    't.MontoDivisaAbonado as MontoDivisa',
                    't.MontoAbonado as MontoBs',
                    't.TasaDeCambio as Tasa',
                    't.Estatus',
                    't.Descripcion',
                    't.NumeroOperacion'
                ])
                ->get();
            
            return $pagos;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarAbonosFactura: ' . $e->getMessage());
            return collect();
        }
    }

    private function buscarProductosDelProveedor($sucursalId, $proveedorId)
    {
        try {
            $query = DB::connection('sqlsrv')
                ->table('Productos as p')
                ->join('ProveedorProducto as pp', 'p.ID', '=', 'pp.ProductoId')
                ->where('pp.ProveedorId', $proveedorId);
            
            // Si es una sucursal específica (no 0), filtrar por sucursal
            if ($sucursalId != 0) {
                $query->leftJoin('ProductoSucursal as ps', function($join) use ($sucursalId) {
                    $join->on('p.ID', '=', 'ps.ProductoId')
                        ->where('ps.SucursalId', $sucursalId);
                });
            } else {
                // Todas las sucursales
                $query->leftJoin('ProductoSucursal as ps', 'p.ID', '=', 'ps.ProductoId');
            }
            
            $productos = $query->select([
                    'p.ID',
                    'p.Codigo',
                    'p.Descripcion as Nombre',
                    'p.CostoDivisa',
                    'p.CostoBs',
                    'p.UrlFoto',
                    'ps.PvpDivisa',
                    'ps.PvpBs',
                    'ps.Existencia',
                    'ps.SucursalId'
                ])
                ->orderBy('p.Descripcion', 'asc')
                ->get();
            
            // Agrupar por producto si hay múltiples sucursales
            if ($sucursalId == 0) {
                $productos = $productos->groupBy('ID')->map(function($grupo) {
                    $primerProducto = $grupo->first();
                    $primerProducto->Sucursales = $grupo->map(function($item) {
                        return [
                            'sucursal_id' => $item->SucursalId,
                            'pvp_divisa' => $item->PvpDivisa,
                            'pvp_bs' => $item->PvpBs,
                            'existencia' => $item->Existencia
                        ];
                    })->toArray();
                    return $primerProducto;
                })->values();
            }
            
            return $productos;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarProductosDelProveedor: ' . $e->getMessage());
            return collect();
        }
    }

    public function registrarPagosIndex($modo = 'pagos')
    {
        try {
            // Tipo Mercancia = 0
            $tipo = 0;
            
            // Obtener listado de proveedores
            $proveedoresMercancia = GeneralHelper::BuscarListadoProveedores($tipo);
            
            // Configurar menú activo según el modo
            $submenuActivo = ($modo == 'pagos') ? 'Registrar Pagos' : 'Registrar Facturas';
            
            session([
                'menu_active' => 'Compras',
                'submenu_active' => $submenuActivo
            ]);
            
            return view('cpanel.proveedores.registrar_pagos', compact('proveedoresMercancia', 'modo'));
            
        } catch (\Exception $e) {
            \Log::error('Error en registrarPagosIndex: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar proveedores: ' . $e->getMessage());
        }
    }

    public function getFacturasPendientes($id)
    {
        // try {
        //     $proveedor = Proveedor::findOrFail($id);
            
        //     $facturas = $proveedor->facturas()
        //         ->where('Estatus', 4) // Facturas Recibidas
        //         ->with(['transacciones' => function($query) {
        //             $query->where('Estatus', 2); // Solo pagos pagados
        //         }])
        //         ->get()
        //         ->map(function($factura) {
        //             $totalPagado = $factura->transacciones->sum('MontoDivisa');
        //             $factura->saldo_pendiente = $factura->MontoDivisa - $totalPagado;
        //             return $factura;
        //         })
        //         ->filter(function($factura) {
        //             return $factura->saldo_pendiente > 0;
        //         })
        //         ->values();
            
        //     return response()->json([
        //         'success' => true,
        //         'facturas' => $facturas
        //     ]);
            
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage()
        //     ], 500);
        // }
    }

    public function registrarPago(Request $request)
    {
        // try {
        //     $request->validate([
        //         'proveedor_id' => 'required|exists:Proveedores,ProveedorId',
        //         'factura_id' => 'required|exists:Facturas,FacturaId',
        //         'monto_divisa' => 'required|numeric|min:0.01',
        //         'fecha_pago' => 'required|date',
        //         'tasa' => 'required|numeric',
        //         'monto_bs' => 'required|numeric',
        //         'numero_operacion' => 'nullable|string',
        //         'descripcion' => 'nullable|string'
        //     ]);
            
        //     // Verificar que el pago no supere el saldo pendiente
        //     $factura = Factura::findOrFail($request->factura_id);
        //     $totalPagado = $factura->transacciones()->where('Estatus', 2)->sum('MontoDivisa');
        //     $saldoPendiente = $factura->MontoDivisa - $totalPagado;
            
        //     if ($request->monto_divisa > $saldoPendiente) {
        //         return back()->with('error', 'El monto excede el saldo pendiente de la factura');
        //     }
            
        //     // Crear la transacción de pago
        //     $transaccion = Transaccion::create([
        //         'ProveedorId' => $request->proveedor_id,
        //         'FacturaId' => $request->factura_id,
        //         'MontoDivisa' => $request->monto_divisa,
        //         'MontoBs' => $request->monto_bs,
        //         'Tasa' => $request->tasa,
        //         'Fecha' => $request->fecha_pago,
        //         'NumeroOperacion' => $request->numero_operacion,
        //         'Descripcion' => $request->descripcion,
        //         'Estatus' => 2, // Pagada
        //         'Tipo' => 1, // Pago a proveedor
        //         'FechaCreacion' => now()
        //     ]);
            
        //     return redirect()->route('cpanel.proveedor.mercancia.registrar_pagos')
        //         ->with('success', 'Pago registrado exitosamente');
            
        // } catch (\Exception $e) {
        //     \Log::error('Error al registrar pago: ' . $e->getMessage());
        //     return back()->with('error', 'Error al registrar pago: ' . $e->getMessage());
        // }
    }

    public function pagarProveedor($id)
    {
        try {
            if (!$id) {
                return redirect()->route('cpanel.proveedor.mercancia.registrar_pagos')
                    ->with('error', 'Debe indicar un código de proveedor');
            }
            
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return redirect()->route('cpanel.proveedor.mercancia.registrar_pagos')
                    ->with('error', 'No se pudo encontrar un proveedor');
            }
            
            // Obtener imagen
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/proveedores/',
                $proveedor->UrlImagen,
                'assets/img/adminlte/img/proveedor_default.png'
            );
            
            // ============================================
            // FACTURAS VIGENTES (En Proceso + Recibiendo + Recibida)
            // ============================================
            $facturasVigentes = $this->buscarFacturasActivas($id);

            // BALANCE DE FACTURAS
            $balanceFacturas = new \stdClass();
            $balanceFacturas->totalFacturas = $facturasVigentes->sum('MontoDivisa');
            $balanceFacturas->totalPagado = $facturasVigentes->sum('total_pagado');
            $balanceFacturas->saldoPendiente = $balanceFacturas->totalFacturas - $balanceFacturas->totalPagado;
            $balanceFacturas->porcentajePagado = $balanceFacturas->totalFacturas > 0 
                ? round(($balanceFacturas->totalPagado * 100) / $balanceFacturas->totalFacturas, 2) 
                : 0;
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Registrar Pagos'
            ]);
            
            return view('cpanel.proveedores.pagar_proveedor', compact(
                'proveedor',
                'imgSrc',
                'facturasVigentes',
                'balanceFacturas'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en pagarProveedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.registrar_pagos')
                ->with('error', 'Error al cargar informacion proveedor: ' . $e->getMessage());
        }
    }
}