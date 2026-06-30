<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;
use App\Models\Proveedor;
use App\Models\DivisaValor;

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
                    'p.Referencia',
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

            // Obtener tasa de cambio actual
            $tasaBcv = DivisaValor::orderBy('ID', 'desc')->first();
            $tasaCambioActual = $tasaBcv->Valor;
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Registrar Pagos'
            ]);
            
            return view('cpanel.proveedores.pagar_proveedor', compact(
                'proveedor',
                'imgSrc',
                'facturasVigentes',
                'balanceFacturas',
                'tasaCambioActual'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en pagarProveedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.registrar_pagos')
                ->with('error', 'Error al cargar informacion proveedor: ' . $e->getMessage());
        }
    }

    public function facturaRegistroProveedor($id)
    {
        try {
            if (!$id) {
                return redirect()->route('cpanel.proveedor.mercancia.registrar_facturas')
                    ->with('error', 'Debe indicar un código de proveedor');
            }
            
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return redirect()->route('cpanel.proveedor.mercancia.registrar_facturas')
                    ->with('error', 'No se pudo encontrar un proveedor');
            }
            
            // Obtener imagen
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/proveedores/',
                $proveedor->UrlImagen ?? '',
                'assets/img/adminlte/img/proveedor_default.png'
            );
            
            // Obtener lista de contenedores (si es proveedor de mercancía)
            $contenedores = null;
            if ($proveedor->Tipo == 0) {
                $contenedores = DB::connection('sqlsrv')
                            ->table('Contenedor')
                            ->whereIn('Estatus', [0, 1, 2]) // 0=Nuevo, 1=EnTransito, 2=EnAduana
                            ->where('FechaRecepcion', '>=', now()->subMonths(6))
                            ->select('Id', 'Nombre')
                            ->orderBy('FechaRecepcion', 'desc')
                            ->get();
                
                if ($contenedores->isEmpty()) {
                    \Log::warning('No se encontraron contenedores activos');
                }
            }
            
            // Obtener tasa de cambio actual
            $tasaBcv = DivisaValor::orderBy('ID', 'desc')->first();
            $tasaCambioActual = $tasaBcv->Valor;
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Registrar Factura'
            ]);
            
            return view('cpanel.proveedores.registrar_factura', compact(
                'proveedor',
                'imgSrc',
                'contenedores',
                'tasaCambioActual'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en facturaRegistroProveedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.registrar_facturas')
                ->with('error', 'Error al cargar información: ' . $e->getMessage());
        }
    }

    public function detalle($id)
    {
        try {
            // Buscar factura seleccionada
            $facturaDTO = $this->buscarFacturaConDetalles($id);
            
            // Validar que exista la factura
            if (!$facturaDTO) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Factura no encontrada');
            }
            
            // Redirigir según el tipo de factura
            if ($facturaDTO->Tipo == 0) {

                // ✅ Obtener el contenedor y su porcentaje de gastos
                $porcentajeGastos = 0;
                if ($facturaDTO->ContenedorId && $facturaDTO->ContenedorId != 0) {
                    $contenedor = DB::connection('sqlsrv')
                        ->table('Contenedor')
                        ->where('Id', $facturaDTO->ContenedorId)
                        ->first();
                    
                    if ($contenedor) {
                        // Si el contenedor tiene PorcentajeGastos, usarlo
                        if (isset($contenedor->PorcentajeGastos) && $contenedor->PorcentajeGastos > 0) {
                            $porcentajeGastos = $contenedor->PorcentajeGastos;
                        } else {
                            // Si no, calcularlo con el SP
                            $porcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($facturaDTO->ContenedorId);
                        }
                    }
                }
                
                // ✅ Asignar el porcentaje de gastos al facturaDTO
                $facturaDTO->PorcentajeGastos = $porcentajeGastos;
                
                // Preparar variables para la vista (mapear de $facturaDTO a lo que espera la vista)
                $factura = $facturaDTO;  // ← Mapear a $factura
                
                // Obtener estado de la factura (texto y clase)
                $estados = [
                    1 => ['texto' => 'En Proceso', 'clase' => 'warning'],
                    2 => ['texto' => 'Recibiendo', 'clase' => 'info'],
                    4 => ['texto' => 'Recibida', 'clase' => 'success'],
                    0 => ['texto' => 'Anulada', 'clase' => 'danger']
                ];
                $estadoFactura = $estados[$facturaDTO->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'secondary'];
                
                // Detalles de productos
                $detalles = $facturaDTO->Detalles ?? collect([]);
                
                // Pagos
                $pagos = $facturaDTO->Pagos ?? collect([]);
                $totalPagado = $facturaDTO->TotalPagado ?? 0;
                
                return view('cpanel.proveedores.detalle_factura', compact(
                    'facturaDTO', 
                    'estadoFactura', 
                    'detalles', 
                    'pagos', 
                    'totalPagado'
                ));
                
            } else {
                return redirect()->route('cpanel.servicios.detalle', ['id' => $facturaDTO->ID]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error en detalle de factura: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el detalle de la factura');
        }
    }

    private function buscarFacturaConDetalles($facturaId)
    {
        // Obtener la factura específica
        $factura = DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ID', $facturaId)
            ->first();
        
        if (!$factura) {
            return null;
        }
        
        // Calcular MontoDivisa real (suma de productos + traspaso)
        $sumaProductos = DB::connection('sqlsrv')
            ->table('FacturaDetalles')
            ->where('FacturaId', $facturaId)
            ->sum(DB::raw('CantidadEmitida * CostoDivisa'));
        
        $montoReal = ($sumaProductos ?? 0) + ($factura->Traspaso ?? 0);
        $factura->MontoDivisaReal = $montoReal;
        
        // Calcular total pagado de esta factura
        $totalPagado = DB::connection('sqlsrv')
            ->table('TransaccionesProveedor as tp')
            ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
            ->where('tp.FacturaId', $facturaId)
            ->sum('t.MontoDivisaAbonado');
        
        $factura->total_pagado = $totalPagado ?? 0;
        $factura->saldo_pendiente = max(0, $montoReal - $factura->total_pagado);
        
        // Buscar pagos para la tabla
        $factura->Pagos = DB::connection('sqlsrv')
            ->table('TransaccionesProveedor as tp')
            ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
            ->where('tp.FacturaId', $facturaId)
            ->select([
                't.ID',
                't.NumeroOperacion',
                't.Fecha',
                't.MontoDivisaAbonado',
                't.TasaDeCambio as Tasa',
                't.Estatus',
                't.Descripcion'
            ])
            ->orderBy('t.Fecha', 'desc')
            ->get();
        
        // Buscar proveedor
        $proveedor = DB::connection('sqlsrv')
            ->table('Proveedores')
            ->where('ProveedorId', $factura->ProveedorId)
            ->first();
        
        $factura->proveedor_nombre = $proveedor->Nombre ?? 'N/A';
        $factura->proveedor_rif = $proveedor->Rif_Cedula ?? 'N/A';
        
        // Buscar sucursal
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('ID', $factura->SucursalId)
            ->first();
        
        $factura->sucursal_nombre = $sucursal->Nombre ?? 'N/A';
        $factura->sucursal_direccion = $sucursal->Direccion ?? '';
        
        // Si es factura de mercancía, buscar contenedor
        if ($factura->Tipo == 0 && $factura->ContenedorId) {
            $factura->Contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $factura->ContenedorId)
                ->first();
            
            $factura->PorcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($factura->ContenedorId);
            $factura->Flete = $factura->Contenedor->Flete ?? 0;
            $factura->Aduana = $factura->Contenedor->Aduana ?? 0;
        } else {
            $factura->Contenedor = null;
            $factura->PorcentajeGastos = 0;
            $factura->Flete = 0;
            $factura->Aduana = 0;
        }
        
        // Buscar detalles de la factura (productos)
        $factura->Detalles = $this->buscarDetallesFactura($facturaId);
        
        // Calcular totales
        $factura->Subtotal = $sumaProductos;
        $factura->CostoTraspaso = $factura->Traspaso ?? 0;
        $factura->TotalFactura = $montoReal;
        $factura->TotalPagado = $totalPagado;
        $factura->SaldoPendiente = $montoReal - $totalPagado;
        $factura->MontoGastos = ($montoReal * $factura->PorcentajeGastos) / 100;
        
        return $factura;
    }

    private function buscarDatosFactura($id)
    {
        // Obtener factura base con joins
        $factura = DB::connection('sqlsrv')
            ->table('Facturas as f')
            ->leftJoin('Proveedores as p', 'f.ProveedorId', '=', 'p.ProveedorId')
            ->leftJoin('Sucursales as s', 'f.SucursalId', '=', 's.ID')
            ->where('f.ID', $id)
            ->select([
                'f.*',
                'p.Nombre as proveedor_nombre',
                'p.Rif_Cedula as proveedor_rif',
                'p.TelefonoMovil as proveedor_telefono',
                'p.CorreoElectronico as proveedor_email',
                's.Nombre as sucursal_nombre',
                's.Direccion as sucursal_direccion'
            ])
            ->first();
        
        if (!$factura) {
            return null;
        }
        
        // Buscar pagos de la factura
        $factura->Pagos = DB::connection('sqlsrv')
            ->table('TransaccionesProveedor as tp')
            ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
            ->where('tp.FacturaId', $id)
            ->select([
                't.ID',
                't.NumeroOperacion',
                't.Fecha',
                't.MontoDivisaAbonado',
                't.TasaDeCambio as Tasa',
                't.Estatus',
                't.Descripcion'
            ])
            ->orderBy('t.Fecha', 'desc')
            ->get();
        
        // Calcular total pagado
        $factura->TotalPagado = $factura->Pagos->sum('MontoDivisaAbonado');
        $factura->SaldoPendiente = max(0, ($factura->MontoDivisa ?? 0) - $factura->TotalPagado);
        
        // Si es factura de mercancía, buscar contenedor y calcular gastos
        if ($factura->Tipo == 0 && $factura->ContenedorId) {
            $factura->Contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $factura->ContenedorId)
                ->first();
            
            // Calcular porcentaje de gastos
            $factura->PorcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($factura->ContenedorId);
            
            // Calcular Flete y Aduana
            $factura->Flete = $factura->Contenedor->Flete ?? 0;
            $factura->Aduana = $factura->Contenedor->Aduana ?? 0;
        } else {
            $factura->Contenedor = null;
            $factura->PorcentajeGastos = 0;
            $factura->Flete = 0;
            $factura->Aduana = 0;
        }
        
        // Costo traspaso
        $factura->CostoTraspaso = $factura->Traspaso ?? 0;
        
        // Buscar detalles de la factura (productos)
        $factura->Detalles = $this->buscarDetallesFactura($id);
        
        // Calcular subtotal
        $factura->Subtotal = $factura->Detalles->sum(function($detalle) {
            return ($detalle->CantidadEmitida ?? 0) * ($detalle->CostoDivisa ?? 0);
        });
        
        // Calcular Total Factura (Subtotal + Traspaso)
        $factura->TotalFactura = ($factura->Subtotal ?? 0) + ($factura->CostoTraspaso ?? 0);
        
        // Calcular porcentaje pagado
        if ($factura->TotalFactura > 0) {
            $factura->PorcentajePagado = ($factura->TotalPagado * 100) / $factura->TotalFactura;
        } else {
            $factura->PorcentajePagado = 0;
        }
        
        // Calcular monto de gastos
        $factura->MontoGastos = ($factura->TotalFactura * $factura->PorcentajeGastos) / 100;
        
        return $factura;
    }

    public function generarFactura(Request $request)
    {
        try {
            // Validar datos del formulario (ModelState.IsValid)
            $request->validate([
                'proveedor_id' => 'required|exists:Proveedores,ProveedorId',
                'contenedor_id' => 'nullable|exists:Contenedor,Id',
                'fecha_creacion' => 'required|date',
                'traspaso' => 'nullable|numeric|min:0',
                'estatus' => 'required|integer|in:0,1,2,3,4',
                'serie' => 'nullable|string|max:20',
                'es_cargar_flete' => 'nullable|boolean',
                'tipo' => 'required|integer'
            ]);
            
            // Obtener el proveedor
            $proveedorSession = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $request->proveedor_id)
                ->first();
            
            if (!$proveedorSession) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }
            
            // Generar lista de contenedores
            $this->generarListaDeContenedores();
            
            // Crear el objeto FacturaDTO (con Id = 0 para indicar que es nueva)
            $factura = new \stdClass();
            $factura->Id = 0;  // ← IMPORTANTE: indica que es nueva factura
            $factura->ProveedorId = $proveedorSession->ProveedorId;
            $factura->Numero = $request->numero ?? ('FAC' . date('YmdHi') . '-' . $request->proveedor_id);
            $factura->Serie = $request->serie;
            $factura->FechaCreacion = $request->fecha_creacion;
            $factura->Traspaso = $request->traspaso ?? 0;
            $factura->Estatus = $request->estatus;
            $factura->ContenedorId = ($request->contenedor_id && $request->contenedor_id != 0) ? $request->contenedor_id : null;
            $factura->EsCargarFleteEnFactura = $request->has('es_cargar_flete') ? 1 : 0;
            $factura->Tipo = $proveedorSession->Tipo;
            $factura->SucursalId = 0;  // Para que busque la oficina principal
            $factura->MontoDivisa = $request->traspaso ?? 0;
            $factura->MontoBs = 0;
            $factura->MonedaPrincipal = 0;
            $factura->TasaDeCambio = 0;
            $factura->Descripcion = null;
            
            // Verificar si la factura ya existe
            $existeFactura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ProveedorId', $proveedorSession->ProveedorId)
                ->where('Numero', $factura->Numero)
                ->exists();
            
            if ($existeFactura) {
                return redirect()->back()
                    ->with('error', 'La factura ya existe en sistema. No pueden existir dos facturas con igual número para un mismo proveedor')
                    ->withInput();
            }
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // 5. Guardar la factura (llama al método que guarda en BD)
            $facturaGuardada = $this->guardarFacturaEnBD($factura, $proveedorSession);
            
            // 6. Mostrar lista de contenedores de la factura
            $this->mostrarListaContenedoresFactura($facturaGuardada);
            
            // 7. Guardar factura activa en sesión
            $proveedorSession->FacturaActiva = $facturaGuardada;
            session(['proveedor_activo' => $proveedorSession]);
            
            DB::connection('sqlsrv')->commit();
            
            // 8. Redirigir a edición para agregar productos
            return redirect()->route('cpanel.facturas.editar', $facturaGuardada->ID)
                ->with('success', 'La factura se ha creado exitósamente');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al guardar factura: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al guardar la factura: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function guardarFacturaEnBD($factura, $proveedorSession)
    {
        // Determinar sucursal (si es 0, buscar oficina principal)
        $sucursalId = $factura->SucursalId ?? 0;
        if ($sucursalId == 0) {
            $sucursalId = $this->obtenerSucursalDefault();
        }
        
        // Insertar la factura
        $facturaId = DB::connection('sqlsrv')->table('Facturas')->insertGetId([
            'ProveedorId' => $factura->ProveedorId,
            'Numero' => $factura->Numero,
            'Serie' => $factura->Serie ?? null,
            'FechaCreacion' => $factura->FechaCreacion,
            'FechaDespacho' => null,
            'FechaCierre' => null,
            'Estatus' => $factura->Estatus,
            'ContenedorId' => $factura->ContenedorId ?? null,
            'Traspaso' => $factura->Traspaso ?? 0,
            'PorcentajeCosto' => null,
            'PorcentajeDescuento' => null,
            'MontoDescuento' => null,
            'EsCargarFleteEnFactura' => $factura->EsCargarFleteEnFactura ?? 0,
            'Tipo' => $factura->Tipo,
            'SucursalId' => $sucursalId,
            'DivisaValorId' => null,
            'MontoDivisa' => $factura->MontoDivisa ?? 0,
            'MontoBs' => 0,
            'Descripcion' => $factura->Descripcion ?? null,
            'TasaDeCambio' => $factura->TasaDeCambio ?? 0,
            'MonedaPrincipal' => 0
        ]);
        
        // Obtener la factura guardada
        $facturaGuardada = DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ID', $facturaId)
            ->first();
        
        // Calcular porcentaje de gastos si tiene contenedor
        if ($facturaGuardada->ContenedorId) {
            $facturaGuardada->PorcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($facturaGuardada->ContenedorId);
        }
        
        return $facturaGuardada;
    }

    private function generarListaDeContenedores()
    {
        $contenedores = DB::connection('sqlsrv')
            ->table('Contenedor')
            ->whereIn('Estatus', [0, 1, 2]) // Nuevo, EnTransito, EnAduana
            ->select('Id', 'Nombre')
            ->get();
        
        view()->share('contenedores', $contenedores);
        
        if ($contenedores->isEmpty()) {
            session(['mensaje_error' => 'No se han creado contenedores para el registro de las facturas']);
        }
    }

    private function obtenerProveedorPorId($id)
    {
        $proveedor = DB::connection('sqlsrv')
            ->table('Proveedores')
            ->where('ProveedorId', $id)
            ->first();
        
        if (!$proveedor) {
            throw new \Exception('Proveedor no encontrado');
        }
        
        return $proveedor;
    }

    private function facturaServiceGuardarFactura($factura)
    {
        // Determinar sucursal (si no tiene, buscar oficina principal)
        $sucursalId = $factura->SucursalId ?? $this->obtenerSucursalDefault();
        
        // Insertar la factura
        $facturaId = DB::connection('sqlsrv')->table('Facturas')->insertGetId([
            'ProveedorId' => $factura->ProveedorId,
            'Numero' => $factura->Numero,
            'Serie' => $factura->Serie ?? null,
            'FechaCreacion' => $factura->FechaCreacion,
            'FechaDespacho' => null,
            'FechaCierre' => null,
            'Estatus' => $factura->Estatus,
            'ContenedorId' => $factura->ContenedorId ?? null,
            'Traspaso' => $factura->Traspaso ?? 0,
            'PorcentajeCosto' => null,
            'PorcentajeDescuento' => null,
            'MontoDescuento' => null,
            'EsCargarFleteEnFactura' => $factura->EsCargarFleteEnFactura ?? 0,
            'Tipo' => $factura->Tipo,
            'SucursalId' => $sucursalId,
            'DivisaValorId' => null,
            'MontoDivisa' => 0,
            'MontoBs' => 0,
            'Descripcion' => $factura->Descripcion ?? null,
            'TasaDeCambio' => null,
            'MonedaPrincipal' => 0
        ]);
        
        // Obtener la factura guardada (como _facturaDTO = _mapper.Map<FacturaDTO>(_facturaModel))
        $facturaDTO = DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ID', $facturaId)
            ->first();
        
        // Buscar el proveedor (como _proveedorService.BuscarProveedor)
        $proveedor = $this->buscarProveedor($facturaDTO->ProveedorId);
        
        // Si es proveedor de Mercancía (Tipo == 0)
        if ($facturaDTO != null && $proveedor != null && $proveedor->Tipo == 0) {
            
            // Calcular porcentaje de gastos usando el SP (como uspObtenerPorcentajeGastosFlete)
            if ($facturaDTO->ContenedorId) {
                $porcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($facturaDTO->ContenedorId);
                $facturaDTO->PorcentajeGastos = $porcentajeGastos;
            } else {
                $facturaDTO->PorcentajeGastos = 0;
            }
            
            // Buscar contenedor si tiene y no está cargado (como contenedorService.BuscarContenedor)
            if ($facturaDTO->ContenedorId != 0 && !isset($facturaDTO->Contenedor)) {
                $facturaDTO->Contenedor = $this->buscarContenedor($facturaDTO->ContenedorId);
            }
            
            // Buscar detalles de la factura (productos)
            $facturaDTO->Detalles = $this->buscarDetallesFactura($facturaId);
        }
        
        return $facturaDTO;
    }

    private function uspObtenerPorcentajeGastosFlete($contenedorId)
    {
        // 1. Calcular TotalFacturas (suma de productos)
        $totalFacturas = DB::connection('sqlsrv')
            ->table('FacturaDetalles as fd')
            ->join('Facturas as f', 'fd.FacturaId', '=', 'f.ID')
            ->where('f.ContenedorId', $contenedorId)
            ->sum(DB::raw('fd.CantidadEmitida * fd.CostoDivisa'));
        
        // 2. Calcular TotalFlete (Aduana + Flete + SUM(Traspaso))
        $contenedor = DB::connection('sqlsrv')
            ->table('Contenedor')
            ->where('Id', $contenedorId)
            ->first();
        
        $totalTraspaso = DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ContenedorId', $contenedorId)
            ->sum('Traspaso');
        
        $totalFlete = ($contenedor->Aduana ?? 0) + ($contenedor->Flete ?? 0) + ($totalTraspaso ?? 0);
        
        // 3. Calcular porcentaje
        if ($totalFacturas > 0) {
            return ($totalFlete * 100) / $totalFacturas;
        }
        
        return 0;
    }

    private function buscarProveedor($proveedorId)
    {
        $proveedor = DB::connection('sqlsrv')
            ->table('Proveedores')
            ->where('ProveedorId', $proveedorId)
            ->first();
        
        return $proveedor;
    }

    private function mostrarListaContenedoresFactura($factura)
    {
        if ($factura) {
            $contenedores = $this->buscarContenedoresActivos();
            
            view()->share('contenedores', $contenedores);
            
            if ($factura->ContenedorId) {
                view()->share('contenedor_seleccionado', $factura->ContenedorId);
            }
        }
    }

    private function buscarContenedoresActivos()
    {
        // Contenedores con Estatus: 0(Nuevo), 1(EnTransito), 2(EnAduana)
        return DB::connection('sqlsrv')
            ->table('Contenedor')
            ->whereIn('Estatus', [0, 1, 2])
            ->select('Id', 'Nombre')
            ->orderBy('Estatus')
            ->orderBy('Nombre')
            ->get();
    }

    private function obtenerSucursalDefault()
    {
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('Nombre', 'LIKE', '%OFICINA PRINCIPAL%')
            ->orWhere('ID', 8)
            ->first();
        
        return $sucursal ? $sucursal->ID : 1;
    }

    private function buscarDetallesFactura($idFactura)
    {
        $detalles = DB::connection('sqlsrv')
            ->table('FacturaDetalles as fd')
            ->leftJoin('Productos as pr', 'fd.ProductoId', '=', 'pr.ID')
            ->where('fd.FacturaId', $idFactura)
            ->select([
                'fd.*',
                'pr.ID as producto_id',
                'pr.Codigo',
                'pr.Descripcion as producto_nombre',  // ← Cambiado de 'Nombre' a 'Descripcion'
                'pr.Referencia',
                'pr.CostoDivisa',
                'pr.CostoBs',
                'pr.UrlFoto',
                'pr.CodigoBarra',
                'pr.Estatus as producto_estatus'
            ])
            ->get();
        
        // Transformar cada detalle para que tenga un objeto Producto anidado (como en .NET)
        foreach ($detalles as $detalle) {
            $detalle->Producto = (object) [
                'ID' => $detalle->producto_id ?? null,
                'Codigo' => $detalle->Codigo ?? null,
                'CodigoBarra' => $detalle->CodigoBarra ?? null,
                'Descripcion' => $detalle->producto_nombre ?? null,
                'Referencia' => $detalle->Referencia ?? null,
                'CostoDivisa' => $detalle->CostoDivisa ?? 0,
                'CostoBs' => $detalle->CostoBs ?? 0,
                'UrlFoto' => $detalle->UrlFoto ?? null,
                'Estatus' => $detalle->producto_estatus ?? 1
            ];
            
            // Calcular subtotales
            $detalle->SubtotalDivisa = ($detalle->CantidadEmitida ?? 0) * ($detalle->CostoDivisa ?? 0);
            $detalle->SubtotalBs = ($detalle->CantidadEmitida ?? 0) * ($detalle->CostoBs ?? 0);
        }
        
        return $detalles;
    }

    private function buscarContenedor($id)
    {
        $contenedor = DB::connection('sqlsrv')
            ->table('Contenedor')
            ->where('Id', $id)
            ->first();
        
        if ($contenedor) {
            // Calcular total de gastos del contenedor
            $contenedor->TotalGastos = ($contenedor->Aduana ?? 0) + ($contenedor->Flete ?? 0);
            
            // Buscar traspasos asociados a este contenedor
            $totalTraspaso = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ContenedorId', $id)
                ->sum('Traspaso');
            
            $contenedor->TotalTraspaso = $totalTraspaso ?? 0;
            $contenedor->TotalGeneral = $contenedor->TotalGastos + $contenedor->TotalTraspaso;
        }
        
        return $contenedor;
    }

    private function calcularPorcentajeGastosManual($contenedorId)
    {
        try {
            // Calcular TotalFacturas (suma de CantidadEmitida * CostoDivisa)
            $totalFacturas = DB::connection('sqlsrv')
                ->table('FacturaDetalles as fd')
                ->join('Facturas as f', 'fd.FacturaId', '=', 'f.ID')
                ->where('f.ContenedorId', $contenedorId)
                ->sum(DB::raw('fd.CantidadEmitida * fd.CostoDivisa'));
            
            // Calcular TotalFlete (Aduana + Flete + SUM(Traspaso))
            $contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $contenedorId)
                ->select('Aduana', 'Flete')
                ->first();
            
            $totalTraspaso = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ContenedorId', $contenedorId)
                ->sum('Traspaso');
            
            $totalFlete = ($contenedor->Aduana ?? 0) + ($contenedor->Flete ?? 0) + ($totalTraspaso ?? 0);
            
            // Calcular porcentaje
            if ($totalFacturas > 0) {
                return ($totalFlete * 100) / $totalFacturas;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Error en cálculo manual de porcentaje: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function editar($id)
    {
        try {
            // Buscar factura seleccionada
            // $facturaDTO = $this->buscarDatosFactura($id);
            $facturaDTO = $this->buscarFacturaConDetalles($id);

            // Validar que exista la factura
            if (!$facturaDTO) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Factura no encontrada');
            }
            
            // Redirigir según el tipo de factura
            if ($facturaDTO->Tipo == 0) {

                // ✅ Obtener el contenedor y su porcentaje de gastos
                $porcentajeGastos = 0;
                if ($facturaDTO->ContenedorId && $facturaDTO->ContenedorId != 0) {
                    $contenedor = DB::connection('sqlsrv')
                        ->table('Contenedor')
                        ->where('Id', $facturaDTO->ContenedorId)
                        ->first();
                    
                    if ($contenedor) {
                        // Si el contenedor tiene PorcentajeGastos, usarlo
                        if (isset($contenedor->PorcentajeGastos) && $contenedor->PorcentajeGastos > 0) {
                            $porcentajeGastos = $contenedor->PorcentajeGastos;
                        } else {
                            // Si no, calcularlo con el SP
                            $porcentajeGastos = $this->uspObtenerPorcentajeGastosFlete($facturaDTO->ContenedorId);
                        }
                    }
                }
                
                // ✅ Asignar el porcentaje de gastos al facturaDTO
                $facturaDTO->PorcentajeGastos = $porcentajeGastos;
                
                // Preparar variables para la vista (mapear de $facturaDTO a lo que espera la vista)
                $factura = $facturaDTO;  // ← Mapear a $factura
                
                // Obtener estado de la factura (texto y clase)
                $estados = [
                    1 => ['texto' => 'En Proceso', 'clase' => 'warning'],
                    2 => ['texto' => 'Recibiendo', 'clase' => 'info'],
                    4 => ['texto' => 'Recibida', 'clase' => 'success'],
                    0 => ['texto' => 'Anulada', 'clase' => 'danger']
                ];
                $estadoFactura = $estados[$facturaDTO->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'secondary'];
                
                // Detalles de productos
                $detalles = $facturaDTO->Detalles ?? collect([]);
                
                // Pagos
                $pagos = $facturaDTO->Pagos ?? collect([]);
                $totalPagado = $facturaDTO->TotalPagado ?? 0;

                // Obtener lista de contenedores para el select
                $contenedores = DB::connection('sqlsrv')
                    ->table('Contenedor')
                    ->whereIn('Estatus', [0, 1, 2])
                    ->select('Id', 'Nombre')
                    ->get();
                
                return view('cpanel.proveedores.editar_factura_new', compact(
                    'facturaDTO', 
                    'estadoFactura', 
                    'detalles', 
                    'pagos', 
                    'totalPagado',
                    'contenedores'
                ));
                
            } else {
                return redirect()->route('cpanel.servicios.detalle', ['id' => $facturaDTO->ID]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error en detalle de factura: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el detalle de la factura');
        }
    }

    public function buscarProductoProveedor(Request $request)
    {
        try {
            $codigo = $request->input('codigo');
            $proveedorId = $request->input('proveedor_id');
            $facturaId = $request->input('factura_id'); // ✅ Nuevo parámetro
            
            \Log::info('buscarProductoProveedor - Código: ' . $codigo . ', Proveedor: ' . $proveedorId . ', Factura: ' . $facturaId);
            
            if (empty($codigo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ingrese un código de producto'
                ]);
            }
            
            $producto = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('Codigo', '=', $codigo)
                ->first();
            
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            // Verificar si está asignado al proveedor
            $asignado = DB::connection('sqlsrv')
                ->table('ProveedorProducto')
                ->where('ProductoId', $producto->ID)
                ->where('ProveedorId', $proveedorId)
                ->exists();
            
            if (!$asignado) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no está asignado a este proveedor'
                ]);
            }
            
            // ✅ Buscar si el producto ya está en la factura
            $detalleExistente = DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $facturaId)
                ->where('ProductoId', $producto->ID)
                ->first();
            
            $cantidadEnFactura = $detalleExistente ? ($detalleExistente->CantidadEmitida ?? 0) : 0;
            
            // Cantidad disponible del producto (puedes ajustar según tu lógica)
            $cantidadDisponible = $producto->Cantidad ?? 0;
            
            return response()->json([
                'success' => true,
                'producto' => [
                    'ID' => $producto->ID,
                    'Codigo' => $producto->Codigo,
                    'Descripcion' => $producto->Descripcion,
                    'CostoDivisa' => $producto->CostoDivisa,
                    'CantidadDisponible' => $cantidadDisponible,
                    'CantidadEnFactura' => $cantidadEnFactura
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarProductoProveedor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Agregar o actualizar un producto en la factura
     */
    public function agregarProductoFactura(Request $request, $facturaId)
    {
        try {
            // Validar datos
            $request->validate([
                'producto_id' => 'required|exists:Productos,ID',
                'cantidad' => 'required|numeric|min:0.01',
                'costo' => 'required|numeric|min:0',
                'empaque' => 'nullable|string'
            ]);
            
            $productoId = $request->producto_id;
            $cantidad = $request->cantidad;
            $costo = $request->costo;
            $uxe = $request->empaque == '12' ? 12 : 1;
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // Verificar si el producto ya existe en la factura
            $detalleExistente = DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $facturaId)
                ->where('ProductoId', $productoId)
                ->first();
            
            if ($detalleExistente) {
                // Actualizar producto existente - SOLO las columnas que existen
                DB::connection('sqlsrv')
                    ->table('FacturaDetalles')
                    ->where('FacturaId', $facturaId)
                    ->where('ProductoId', $productoId)
                    ->update([
                        'CantidadEmitida' => $cantidad,
                        'CostoDivisa' => $costo,
                        'UxE' => $uxe
                    ]);
                
                $mensaje = 'Producto actualizado correctamente';
            } else {
                // Insertar nuevo producto
                DB::connection('sqlsrv')
                    ->table('FacturaDetalles')
                    ->insert([
                        'FacturaId' => $facturaId,
                        'ProductoId' => $productoId,
                        'CantidadEmitida' => $cantidad,
                        'CantidadRecibida' => 0,
                        'CantidadDisponible' => 0,
                        'CostoDivisa' => $costo,
                        'CostoBs' => $costo * ($request->tasa_cambio ?? 40),
                        'CostoUnitario' => 0,
                        'CostoEmpaque' => null,
                        'UxE' => $uxe,
                        'DescuentoPuntual' => null
                    ]);
                
                $mensaje = 'Producto agregado correctamente';
            }
            
            // Actualizar el MontoDivisa de la factura
            $totalFactura = DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $facturaId)
                ->sum(DB::raw('CantidadEmitida * CostoDivisa'));
            
            DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $facturaId)
                ->update(['MontoDivisa' => $totalFactura]);
            
            DB::connection('sqlsrv')->commit();
            
            // Obtener los detalles actualizados usando el método existente
            $detalles = $this->buscarDetallesFactura($facturaId);
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'detalles' => $detalles,
                'total_factura' => $totalFactura,
                'factura_id' => $facturaId
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al agregar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el producto: ' . $e->getMessage()
            ]);
        }
    }

    public function guardarProductosFactura(Request $request, $id)
    {
        try {
            $productos = $request->input('productos');
            
            if (empty($productos)) {
                return response()->json(['success' => false, 'message' => 'No hay productos para guardar']);
            }
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // Eliminar productos existentes de la factura
            DB::connection('sqlsrv')->table('FacturaDetalles')
                ->where('FacturaId', $id)
                ->delete();
            
            // Insertar nuevos productos
            foreach ($productos as $producto) {
                // Buscar el ID del producto por código
                $productoModel = DB::connection('sqlsrv')
                    ->table('Productos')
                    ->where('Codigo', $producto['codigo'])
                    ->first();
                
                if ($productoModel) {
                    DB::connection('sqlsrv')->table('FacturaDetalles')->insert([
                        'FacturaId' => $id,
                        'ProductoId' => $productoModel->ID,
                        'CantidadEmitida' => $producto['cantidad'],
                        'CostoDivisa' => $producto['costo'],
                        'CostoBs' => $producto['costo'] * ($request->tasa_cambio ?? 40),
                        'Empaque' => $producto['empaque'] == 12 ? 'Docena' : 'Unidad'
                    ]);
                }
            }
            
            // Actualizar el MontoDivisa de la factura
            $totalFactura = DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $id)
                ->sum(DB::raw('CantidadEmitida * CostoDivisa'));
            
            DB::connection('sqlsrv')->table('Facturas')
                ->where('ID', $id)
                ->update(['MontoDivisa' => $totalFactura]);
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json(['success' => true, 'message' => 'Productos guardados correctamente']);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al guardar productos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Actualizar factura
     */
    public function actualizar(Request $request, $id)
    {
        try {
            $request->validate([
                // 'numero' => 'required|string|max:50',
                // 'serie' => 'nullable|string|max:20',
                'fecha_creacion' => 'required|date',
                // 'fecha_despacho' => 'nullable|date',
                'contenedor_id' => 'nullable|exists:Contenedor,Id',
                'traspaso' => 'nullable|numeric|min:0',
                'es_cargar_flete' => 'nullable|boolean',
                'estatus' => 'required|integer|in:0,1,2,3,4',
                // 'descripcion' => 'nullable|string'
            ]);
            
            // Verificar que la factura existe y está en estado editable
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $id)
                ->first();
            
            if (!$factura || $factura->Estatus != 1) {
                return redirect()->back()->with('error', 'No se puede editar esta factura');
            }
            
            // Actualizar factura
            DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $id)
                ->update([
                    // 'Numero' => $request->numero,
                    // 'Serie' => $request->serie,
                    'FechaCreacion' => $request->fecha_creacion,
                    // 'FechaDespacho' => $request->fecha_despacho,
                    'ContenedorId' => ($request->contenedor_id && $request->contenedor_id != 0) ? $request->contenedor_id : null,
                    'Traspaso' => $request->traspaso ?? 0,
                    'EsCargarFleteEnFactura' => $request->has('es_cargar_flete') ? 1 : 0,
                    'Estatus' => $request->estatus,
                    // 'Descripcion' => $request->descripcion
                ]);
            
            return redirect()->route('cpanel.facturas.detalle', $id)
                ->with('success', 'Factura actualizada correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar factura: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la factura: ' . $e->getMessage());
        }
    }

    private function crearDetallesFacturasDesdeExcel($rutaArchivo, $facturaId)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaArchivo);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $estatusArchivo = 0; // ESTATUS_INICIO
            $listaDetalles = [];
            
            foreach ($rows as $row) {
                $col0 = trim($row[0] ?? '');  // Código
                $col1 = trim($row[1] ?? '');  // Referencia
                $col2 = trim($row[2] ?? '');  // Descripción
                $col3 = trim($row[3] ?? '');  // Cantidad (empaques)
                $col4 = trim($row[4] ?? '');  // UxE
                $col5 = trim($row[5] ?? '');  // Costo por empaque (columna F)
                
                if ($estatusArchivo == 0) {
                    if (strtolower($col0) == 'entrada de factura') {
                        $estatusArchivo = 1;
                    }
                } elseif ($estatusArchivo == 1) {
                    if (strtolower($col0) == 'codigo' || strtolower($col0) == 'código') {
                        $estatusArchivo = 2;
                    }
                } elseif ($estatusArchivo == 2) {
                    if (!empty($col0) && !empty($col2) && !empty($col3) && !empty($col5)) {
                        $cantidad = floatval($col3);
                        $uxe = intval($col4) ?: 1;
                        $costoPorEmpaque = floatval($col5);  // ✅ 6 o 20
                        $costoTotal = $cantidad * $costoPorEmpaque;  // 30 o 240
                        
                        $listaDetalles[] = [
                            'Codigo' => $col0,
                            'Referencia' => $col1,
                            'Descripcion' => $col2,
                            'Cantidad' => $cantidad,
                            'UxE' => $uxe,
                            'Costo' => $costoTotal,              // 30 o 240 (para mostrar)
                            'CostoPorEmpaque' => $costoPorEmpaque  // ✅ 6 o 20 (para guardar)
                        ];
                    }
                }
            }
            
            return $listaDetalles;
            
        } catch (\Exception $e) {
            \Log::error('Error al leer Excel: ' . $e->getMessage());
            return [];
        }
    }

    public function guardarExcelFactura(Request $request, $facturaId)
    {
        try {
            $productos = $request->input('productos');
            
            if (empty($productos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos para guardar'
                ]);
            }
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Procesar y guardar productos en tabla Productos
            $productosProcesados = $this->guardarNuevoProductosDeFactura($productos, $facturaId);
            
            // 2. Guardar o actualizar cada detalle en FacturaDetalles
            foreach ($productosProcesados as $producto) {
                $this->guardarOActualizarDetalleFactura($producto, $facturaId);
            }
            
            DB::connection('sqlsrv')->commit();
            
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $facturaId)
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'La factura se ha actualizado exitósamente',
                'proveedor_id' => $factura->ProveedorId
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al guardar Excel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    // private function guardarNuevoProductosDeFactura($productos, $facturaId)
    // {
    //     $productosConId = [];
    //     $proveedorId = null;
    //     $porcentajeGastos = 0;
        
    //     // Obtener el proveedor de la factura
    //     $factura = DB::connection('sqlsrv')
    //         ->table('Facturas')
    //         ->where('ID', $facturaId)
    //         ->first();
        
    //     if ($factura) {
    //         $proveedorId = $factura->ProveedorId;

    //         // Obtener el porcentaje de gastos del contenedor
    //         if ($factura->ContenedorId) {
    //             $contenedor = DB::connection('sqlsrv')
    //                 ->table('Contenedor')
    //                 ->where('Id', $factura->ContenedorId)
    //                 ->first();
                
    //             if ($contenedor) {
    //                 $porcentajeGastos = $contenedor->PorcentajeGastos ?? 0;
                    
    //                 // Log para debug
    //                 \Log::info('Porcentaje de gastos obtenido', [
    //                     'factura_id' => $facturaId,
    //                     'contenedor_id' => $factura->ContenedorId,
    //                     'porcentaje_gastos' => $porcentajeGastos
    //                 ]);
    //             }
    //         }
    //     }
        
    //     // Obtener sucursal de tipo Almacén (si existe en tu BD)
    //     $sucursalAlmacen = $this->buscarSucursalAlmacen();
    //     $sucursalId = $sucursalAlmacen ? $sucursalAlmacen->ID : null;
        
    //     foreach ($productos as $producto) {
    //         // Buscar producto por código
    //         $productoModel = DB::connection('sqlsrv')
    //             ->table('Productos')
    //             ->where('Codigo', $producto['codigo'])
    //             ->first();
            
    //         $productoId = null;

    //         // Calcular costo con gastos
    //         $costoUnitario = $producto['costo_unitario'] ?? 0;
    //         $costoConGastos = $costoUnitario * (1 + ($porcentajeGastos / 100));
            
    //         // Redondear a 2 decimales para moneda
    //         $costoConGastos = round($costoConGastos, 2);
            
    //         \Log::info('Calculando costo con gastos', [
    //             'codigo' => $producto['codigo'],
    //             'costo_unitario' => $costoUnitario,
    //             'porcentaje_gastos' => $porcentajeGastos,
    //             'costo_final' => $costoConGastos
    //         ]);
            
    //         if (!$productoModel) {
    //             // Crear nuevo producto
    //             $productoId = DB::connection('sqlsrv')->table('Productos')->insertGetId([
    //                 'Codigo' => $producto['codigo'],
    //                 'Descripcion' => $producto['descripcion'],
    //                 'Referencia' => $producto['referencia'] ?? '',
    //                 //'CostoDivisa' => $producto['costo_unitario'],
    //                 'CostoDivisa' => $costoConGastos, // ✅ Costo unitario + % gastos
    //                 // 'CostoBs' => $producto['costo'] * 40,
    //                 'Estatus' => 1,
    //                 'EsProveedorAsignado' => 1,
    //                 'FechaCreacion' => now(),
    //                 'FechaActualizacion' => now()
    //             ]);
    //         } else {
    //             // Actualizar producto existente
    //             DB::connection('sqlsrv')
    //                 ->table('Productos')
    //                 ->where('ID', $productoModel->ID)
    //                 ->update([
    //                     'Descripcion' => $producto['descripcion'],
    //                     'Referencia' => $producto['referencia'] ?? '',
    //                     // 'CostoDivisa' => $producto['costo_unitario'],
    //                     'CostoDivisa' => $costoConGastos, // ✅ Costo unitario + % gastos
    //                     'CostoBs' => 0,
    //                     'EsProveedorAsignado' => 1,
    //                     'FechaActualizacion' => now()
    //                 ]);
    //             $productoId = $productoModel->ID;
    //         }
            
    //         // Guardar en almacén (ProductoSucursal) - si existe la tabla
    //         if ($sucursalId) {
    //             $existeAlmacen = DB::connection('sqlsrv')
    //                 ->table('ProductoSucursal')
    //                 ->where('SucursalId', $sucursalId)
    //                 ->where('ProductoId', $productoId)
    //                 ->exists();
                
    //             if (!$existeAlmacen) {
    //                 DB::connection('sqlsrv')->table('ProductoSucursal')->insert([
    //                     'SucursalId' => $sucursalId,
    //                     'ProductoId' => $productoId,
    //                     'PvpBs' => 0,
    //                     'PvpDivisa' => 0,
    //                     'Estatus' => 1,
    //                     'Existencia' => 0,
    //                     'FechaIngreso' => now(),
    //                     'FechaUltimaVenta' => null
    //                 ]);
    //             }
    //         }
            
    //         // Guardar asociación proveedor-producto
    //         if ($proveedorId) {
    //             $existeAsociacion = DB::connection('sqlsrv')
    //                 ->table('ProveedorProducto')
    //                 ->where('ProveedorId', $proveedorId)
    //                 ->where('ProductoId', $productoId)
    //                 ->exists();
                
    //             if (!$existeAsociacion) {
    //                 DB::connection('sqlsrv')->table('ProveedorProducto')->insert([
    //                     'ProveedorId' => $proveedorId,
    //                     'ProductoId' => $productoId
    //                 ]);
    //             }
    //         }
            
    //         $productosConId[] = [
    //             'producto_id' => $productoId,
    //             'codigo' => $producto['codigo'],
    //             'descripcion' => $producto['descripcion'],
    //             'referencia' => $producto['referencia'] ?? '',  // ✅ Agregar referencia
    //             'costo' => $producto['costo'],
    //             'costo_unitario' => $producto['costo_unitario'] ?? 0,  // ✅ AGREGAR
    //             'costo_excel' => $producto['costo_excel'] ?? 0,
    //             'cantidad' => $producto['cantidad'],
    //             'empaque' => $producto['empaque']
    //         ];
    //     }
        
    //     return $productosConId;
    // }

    private function guardarNuevoProductosDeFactura($productos, $facturaId)
    {
        $productosConId = [];
        $proveedorId = null;
        $porcentajeGastos = 0;
        
        // Obtener el proveedor de la factura
        $factura = DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ID', $facturaId)
            ->first();
        
        if ($factura) {
            $proveedorId = $factura->ProveedorId;

            // Obtener el porcentaje de gastos del contenedor
            if ($factura->ContenedorId) {
                $contenedor = DB::connection('sqlsrv')
                    ->table('Contenedor')
                    ->where('Id', $factura->ContenedorId)
                    ->first();
                
                if ($contenedor) {
                    $porcentajeGastos = $contenedor->PorcentajeGastos ?? 0;
                    
                    \Log::info('Porcentaje de gastos obtenido', [
                        'factura_id' => $facturaId,
                        'contenedor_id' => $factura->ContenedorId,
                        'porcentaje_gastos' => $porcentajeGastos
                    ]);
                }
            }
        }
        
        // Obtener sucursal de tipo Almacén
        $sucursalAlmacen = $this->buscarSucursalAlmacen();
        $sucursalId = $sucursalAlmacen ? $sucursalAlmacen->ID : null;
        
        // --- PASO 1: Obtener TODOS los códigos de productos del Excel ---
        $codigos = array_column($productos, 'codigo');
        
        // --- PASO 2: Obtener TODOS los productos existentes en UNA sola consulta ---
        $productosExistentes = DB::connection('sqlsrv')
            ->table('Productos')
            ->whereIn('Codigo', $codigos)
            ->get()
            ->keyBy('Codigo'); // Indexar por código para acceso rápido
        
        \Log::info('Productos existentes encontrados', [
            'total_excel' => count($productos),
            'existentes' => $productosExistentes->count()
        ]);
        
        // Preparar datos para inserciones/actualizaciones masivas
        $productosNuevos = [];
        $productosActualizar = [];
        $idsProductosExistentes = [];
        
        foreach ($productos as $producto) {
            $costoUnitario = $producto['costo_unitario'] ?? 0;
            $costoConGastos = round($costoUnitario * (1 + ($porcentajeGastos / 100)), 2);
            
            $productoData = [
                'Codigo' => $producto['codigo'],
                'Descripcion' => $producto['descripcion'],
                'Referencia' => $producto['referencia'] ?? '',
                'CostoDivisa' => $costoConGastos,
                'CostoBs' => 0,
                'Estatus' => 1,
                'EsProveedorAsignado' => 1,
                'FechaActualizacion' => now()
            ];
            
            // Verificar si el producto ya existe
            if (isset($productosExistentes[$producto['codigo']])) {
                // Producto existe - guardar para actualización masiva
                $productoId = $productosExistentes[$producto['codigo']]->ID;
                $idsProductosExistentes[] = $productoId;
                
                $productosActualizar[] = [
                    'ID' => $productoId,
                    'data' => $productoData
                ];
            } else {
                // Producto nuevo - preparar para inserción masiva
                $productoData['FechaCreacion'] = now();
                $productosNuevos[] = $productoData;
                $productoId = null;
            }
            
            // Guardar en array de resultados (el ID se asignará después)
            $productosConId[] = [
                'producto_id' => $productoId ?? null,
                'codigo' => $producto['codigo'],
                'descripcion' => $producto['descripcion'],
                'referencia' => $producto['referencia'] ?? '',
                'costo' => $producto['costo'],
                'costo_unitario' => $costoUnitario,
                'costo_con_gastos' => $costoConGastos,
                'costo_excel' => $producto['costo_excel'] ?? 0,
                'cantidad' => $producto['cantidad'],
                'empaque' => $producto['empaque']
            ];
        }
        
        // --- PASO 3: Insertar TODOS los productos nuevos en UNA sola operación ---
        if (!empty($productosNuevos)) {
            \Log::info('Insertando productos nuevos', ['cantidad' => count($productosNuevos)]);
            
            // Insertar en lotes de 50 para no sobrecargar
            foreach (array_chunk($productosNuevos, 50) as $chunk) {
                DB::connection('sqlsrv')->table('Productos')->insert($chunk);
            }
            
            // Obtener los IDs de los productos recién insertados
            $codigosNuevos = array_column($productosNuevos, 'Codigo');
            $productosInsertados = DB::connection('sqlsrv')
                ->table('Productos')
                ->whereIn('Codigo', $codigosNuevos)
                ->get()
                ->keyBy('Codigo');
            
            // Actualizar los IDs en el array de resultados
            foreach ($productosConId as &$item) {
                if (isset($productosInsertados[$item['codigo']])) {
                    $item['producto_id'] = $productosInsertados[$item['codigo']]->ID;
                    $idsProductosExistentes[] = $item['producto_id'];
                }
            }
            unset($item); // Romper la referencia
        }
        
        // --- PASO 4: Actualizar TODOS los productos existentes ---
        if (!empty($productosActualizar)) {
            \Log::info('Actualizando productos existentes', ['cantidad' => count($productosActualizar)]);
            
            foreach ($productosActualizar as $item) {
                DB::connection('sqlsrv')
                    ->table('Productos')
                    ->where('ID', $item['ID'])
                    ->update($item['data']);
            }
        }
        
        // Recolectar todos los IDs de productos (nuevos y existentes)
        $todosLosIds = array_column($productosConId, 'producto_id');
        $todosLosIds = array_filter($todosLosIds);
        
        // --- PASO 5: Guardar en almacén (ProductoSucursal) en UNA sola operación ---
        if ($sucursalId && !empty($todosLosIds)) {
            // Obtener los IDs que ya existen en ProductoSucursal
            $existentesEnAlmacen = DB::connection('sqlsrv')
                ->table('ProductoSucursal')
                ->where('SucursalId', $sucursalId)
                ->whereIn('ProductoId', $todosLosIds)
                ->pluck('ProductoId')
                ->toArray();
            
            $nuevosEnAlmacen = array_diff($todosLosIds, $existentesEnAlmacen);
            
            if (!empty($nuevosEnAlmacen)) {
                \Log::info('Insertando en ProductoSucursal', ['cantidad' => count($nuevosEnAlmacen)]);
                
                $productoSucursalData = array_map(function($productoId) use ($sucursalId) {
                    return [
                        'SucursalId' => $sucursalId,
                        'ProductoId' => $productoId,
                        'PvpBs' => 0,
                        'PvpDivisa' => 0,
                        'Estatus' => 1,
                        'Existencia' => 0,
                        'FechaIngreso' => now(),
                        'FechaUltimaVenta' => null
                    ];
                }, $nuevosEnAlmacen);
                
                // Insertar en lotes de 50
                foreach (array_chunk($productoSucursalData, 50) as $chunk) {
                    DB::connection('sqlsrv')->table('ProductoSucursal')->insert($chunk);
                }
            }
        }
        
        // --- PASO 6: Guardar asociaciones proveedor-producto en UNA sola operación ---
        if ($proveedorId && !empty($todosLosIds)) {
            // Obtener las asociaciones que ya existen
            $existentesProveedor = DB::connection('sqlsrv')
                ->table('ProveedorProducto')
                ->where('ProveedorId', $proveedorId)
                ->whereIn('ProductoId', $todosLosIds)
                ->pluck('ProductoId')
                ->toArray();
            
            $nuevosProveedor = array_diff($todosLosIds, $existentesProveedor);
            
            if (!empty($nuevosProveedor)) {
                \Log::info('Insertando en ProveedorProducto', ['cantidad' => count($nuevosProveedor)]);
                
                $proveedorProductoData = array_map(function($productoId) use ($proveedorId) {
                    return [
                        'ProveedorId' => $proveedorId,
                        'ProductoId' => $productoId
                    ];
                }, $nuevosProveedor);
                
                // Insertar en lotes de 50
                foreach (array_chunk($proveedorProductoData, 50) as $chunk) {
                    DB::connection('sqlsrv')->table('ProveedorProducto')->insert($chunk);
                }
            }
        }
        
        \Log::info('Productos procesados exitosamente', [
            'total' => count($productosConId),
            'nuevos' => count($productosNuevos),
            'actualizados' => count($productosActualizar)
        ]);
        
        return $productosConId;
    }

    private function buscarSucursalAlmacen()
    {
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('Tipo', 2) 
            ->first();
        
        return $sucursal;
    }

    private function guardarOActualizarDetalleFactura($producto, $facturaId)
    {
        \Log::info('=== guardarOActualizarDetalleFactura INICIO ===');
        \Log::info('Producto recibido:', $producto);
        
        // Obtener valores
        $cantidadUnidades = floatval($producto['cantidad'] ?? 0);
        $costoUnitario = floatval($producto['costo_unitario'] ?? 0);
        $costoExcel = floatval($producto['costo_excel'] ?? 0);  // ✅ Valor del Excel
        $empaque = $producto['empaque'] ?? 1;
        
        \Log::info('Valores extraídos:', [
            'cantidadUnidades' => $cantidadUnidades,
            'costoUnitario' => $costoUnitario,
            'costoExcel' => $costoExcel,
            'empaque' => $empaque
        ]);
        
        // Calcular UxE
        $uxe = 1;
        if (is_numeric($empaque)) {
            $uxe = intval($empaque);
        } elseif (is_string($empaque)) {
            if (strtolower($empaque) == 'docena') {
                $uxe = 12;
            } elseif (strtolower($empaque) == 'unidad') {
                $uxe = 1;
            } else {
                preg_match('/\d+/', $empaque, $matches);
                $uxe = !empty($matches) ? intval($matches[0]) : 1;
            }
        }
        
        // Calcular cantidad de empaques
        $cantidadEmpaques = $cantidadUnidades / $uxe;
        
        // ✅ CostoDivisa = costo_excel (valor directo del Excel)
        $costoDivisa = $costoExcel;
        
        // CantidadDisponible = cantidad de unidades totales
        $cantidadDisponible = $cantidadUnidades;
        
        \Log::info('Valores calculados para guardar:', [
            'cantidadEmpaques' => $cantidadEmpaques,
            'costoDivisa' => $costoDivisa,
            'uxe' => $uxe,
            'cantidadDisponible' => $cantidadDisponible
        ]);
        
        $detalleExistente = DB::connection('sqlsrv')
            ->table('FacturaDetalles')
            ->where('FacturaId', $facturaId)
            ->where('ProductoId', $producto['producto_id'])
            ->first();
        
        \Log::info('detalleExistente:', ['existe' => $detalleExistente ? 'Sí' : 'No']);
        
        if ($detalleExistente) {
            \Log::info('ACTUALIZANDO detalle existente:', [
                'FacturaId' => $facturaId,
                'ProductoId' => $producto['producto_id'],
                'CantidadEmitida' => $cantidadEmpaques,
                'CostoDivisa' => $costoDivisa,
                'UxE' => $uxe,
                'CantidadRecibida' => 0
            ]);
            
            DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $facturaId)
                ->where('ProductoId', $producto['producto_id'])
                ->update([
                    'CantidadEmitida' => $cantidadEmpaques,
                    'CostoDivisa' => $costoDivisa,
                    'UxE' => $uxe,
                    'CantidadRecibida' => 0
                ]);
        } else {
            \Log::info('INSERTANDO nuevo detalle:', [
                'FacturaId' => $facturaId,
                'ProductoId' => $producto['producto_id'],
                'CantidadEmitida' => $cantidadEmpaques,
                'CantidadRecibida' => 0,
                'CantidadDisponible' => 0,
                'CostoDivisa' => $costoDivisa,
                'UxE' => $uxe
            ]);
            
            DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->insert([
                    'FacturaId' => $facturaId,
                    'ProductoId' => $producto['producto_id'],
                    'CantidadEmitida' => $cantidadEmpaques,
                    'CantidadRecibida' => 0,
                    'CantidadDisponible' => 0,
                    'CostoDivisa' => $costoDivisa,
                    'CostoBs' => 0,
                    'UxE' => $uxe
                ]);
        }
        
        \Log::info('=== guardarOActualizarDetalleFactura FIN ===');
    }








    private function guardarDetalleFactura($producto, $facturaId, $esEdicion = false)
    {
        $detalleExistente = DB::connection('sqlsrv')
            ->table('FacturaDetalles')
            ->where('FacturaId', $facturaId)
            ->where('ProductoId', $producto['producto_id'])
            ->first();
        
        $uxe = ($producto['empaque'] == 'Docena' || $producto['empaque'] == 12) ? 12 : 1;
        
        if ($detalleExistente && $esEdicion) {
            // UPDATE (solo si es edición)
            DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $facturaId)
                ->where('ProductoId', $producto['producto_id'])
                ->update([
                    'CantidadEmitida' => $producto['cantidad'],
                    'CostoDivisa' => $producto['costo'],
                    'UxE' => $uxe
                ]);
        } else if (!$detalleExistente) {
            // INSERT (nuevo producto)
            DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->insert([
                    'FacturaId' => $facturaId,
                    'ProductoId' => $producto['producto_id'],
                    'CantidadEmitida' => $producto['cantidad'],
                    'CantidadRecibida' => 0,
                    'CantidadDisponible' => 0,
                    'CostoDivisa' => $producto['costo'],
                    'CostoBs' => $producto['costo'] * 40,
                    'UxE' => $uxe
                ]);
        }
    }

    public function uploadProductosFactura(Request $request, $facturaId)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:5120'
            ]);
            
            $archivo = $request->file('excel_file');
            
            // Guardar el archivo temporalmente
            $nombreArchivo = 'temp_' . time() . '.xlsx';
            $rutaCompleta = $archivo->getPathName(); // Usar ruta temporal
            
            // Crear los detalles desde el Excel (sin guardar en BD)
            $detallesFactura = $this->crearDetallesFacturasDesdeExcel($rutaCompleta, $facturaId);
            
            if (empty($detallesFactura)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron leer productos del archivo'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Se encontraron " . count($detallesFactura) . " productos.",
                'detalles' => $detallesFactura
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al subir Excel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar el archivo: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar una factura (AJAX)
     */
    public function eliminar($id)
    {
        try {
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $id)
                ->first();
            
            if (!$factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }
            
            // Validar si se puede eliminar
            if ($factura->Estatus != 1 && $factura->saldo_pendiente > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta factura porque tiene pagos asociados o no está en estado "En Proceso"'
                ], 400);
            }
            
            // Verificar si tiene pagos asociados
            $tienePagos = DB::connection('sqlsrv')
                ->table('PagosFacturas')
                ->where('FacturaId', $id)
                ->exists();
            
            if ($tienePagos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la factura porque tiene pagos registrados'
                ], 400);
            }
            
            // Realizar eliminación lógica (actualizar estatus)
            DB::connection('sqlsrv')->table('Facturas')
                ->where('ID', $id)
                ->update([
                    'Estatus' => 0, 
                    'FechaEliminacion' => now(),
                    'EliminadoPor' => auth()->user()->id ?? null
                ]);
            
            Log::info('Factura eliminada', [
                'factura_id' => $id, 
                'factura_numero' => $factura->Numero,
                'usuario' => auth()->user()->id ?? null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar factura: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validación directa
            $validated = $request->validate([
                'proveedor_id' => 'required|exists:Proveedores,ProveedorId',
                'fecha' => 'required|date',
                'descripcion' => 'required|string|max:500',
                'tasa_cambio' => 'required|numeric|min:0',
                'monto_divisa' => 'required|numeric|min:0.01',
                'monto_bs' => 'required|numeric|min:0',
                'forma_pago' => 'required|integer',
                'numero_operacion' => 'nullable|string|max:100',
                'estatus' => 'required|integer',
                'tipo_transaccion' => 'required|integer',
                'sucursal_id' => 'required|integer',
                'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);
            
            DB::connection('sqlsrv')->beginTransaction();

            $proveedor = $this->buscarDatosTransaccionProveedor($request->proveedor_id);
            
            if (!$proveedor) {
                return response()->json(['success' => false, 'message' => 'Proveedor no encontrado'], 404);
            }
            
            // 2. Crear objeto Pago con los datos del formulario
            $pago = $proveedor->pago;
            $pago->Descripcion = $request->descripcion;
            $pago->MontoDivisaAbonado = $request->monto_divisa;
            $pago->MontoAbonado = $request->monto_bs;
            $pago->FormaDePago = $request->forma_pago;
            $pago->Fecha = $request->fecha;
            $pago->TasaDeCambio = $request->tasa_cambio;
            $pago->SucursalId = $request->sucursal_id;
            
            // Subir comprobante si existe
            if ($request->hasFile('comprobante')) {
                $comprobante = $request->file('comprobante');
                $extension = $comprobante->getClientOriginalExtension();
                
                // Generar nombre como en .NET: PAG{yyyyMMddhhmm}-{proveedorId}.extensión
                $fileName = 'PAG' . date('YmdHi') . '-' . $request->proveedor_id . '.' . $extension;
                
                // Guardar en storage/app/public/images/comprobantes/
                $path = $comprobante->storeAs('images/comprobantes', $fileName, 'public');
                
                // Guardar SOLO el nombre del archivo (como en .NET)
                $pago->UrlComprobante = $fileName;
            }
            
            // 3. Asignar Tipo y Estatus
            $pago->Tipo = 0;     // PagoMercancia
            $pago->Estatus = 2;  // Pagada
            
            // 4. Guardar transacción (como GuardarTransaccionDeProveedor)
            $pagoGuardado = $this->guardarTransaccionDeProveedor($pago, $request->proveedor_id);
            
            // 5. Si es proveedor de mercancía, refrescar facturas vigentes
            if ($proveedor->Tipo == 0) { // 0 = Mercancía
                $facturasVigentes = $this->buscarFacturasActivas($request->proveedor_id);
            }
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'La transacción se guardó con éxito',
                'transaccion_id' => $pagoGuardado->Id,
                'numero_operacion' => $pagoGuardado->NumeroOperacion
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function buscarDatosTransaccionProveedor($proveedorId)
    {
        // 1. Buscar datos básicos del proveedor
        $proveedor = DB::connection('sqlsrv')
            ->table('Proveedores')
            ->where('ProveedorId', $proveedorId)
            ->first();
        
        if (!$proveedor) {
            return null;
        }
        
        // 2. Buscar facturas vigentes
        $facturasVigentes = $this->buscarFacturasActivas($proveedorId);
        
        // 3. Obtener tasa de cambio actual
        $tasaActual = DB::connection('sqlsrv')
            ->table('DivisaValor')
            ->orderBy('ID', 'desc')
            ->first();
        
        $tasaCambio = $tasaActual->Valor ?? 0;
        
        // 4. Inicializar objeto Pago vacío
        $pago = new \stdClass();
        $pago->Id = 0;
        $pago->Descripcion = '';
        $pago->MontoAbonado = 0;
        $pago->MontoDivisaAbonado = 0;
        $pago->NumeroOperacion = '';
        $pago->FormaDePago = null;
        $pago->TasaDeCambio = $tasaCambio;
        $pago->Fecha = date('Y-m-d');
        $pago->Estatus = 2;  // Pagada
        $pago->Tipo = 0;     // PagoMercancia
        $pago->SucursalId = session('sucursal_id', 1);
        $pago->UrlComprobante = null;
        
        // 5. Retornar proveedor con el pago inicializado
        $proveedor->pago = $pago;
        $proveedor->facturas_vigentes = $facturasVigentes;
        $proveedor->tasa_cambio = $tasaCambio;
        
        return $proveedor;
    }

    private function guardarTransaccionDeProveedor($pago, $proveedorId)
    {
        // 1. Generar número de operación (como en .NET)
        $numeroOperacion = 'PAG' . date('YmdHi') . '-' . $proveedorId;
        
        // 2. Verificar si ya existe una transacción con ese número
        $existe = DB::connection('sqlsrv')
            ->table('Transacciones')
            ->where('NumeroOperacion', $numeroOperacion)
            ->exists();
        
        if ($existe) {
            throw new \Exception('La transacción ya existe en sistema');
        }
        
        // 3. Obtener facturas vigentes con saldo pendiente
        $facturas = $this->buscarFacturasActivas($proveedorId);
        
        // 4. Distribuir pago entre facturas (como en .NET)
        $montoRestante = $pago->MontoDivisaAbonado;
        $montoBsRestante = $pago->MontoAbonado;
        $transaccionesCreadas = [];
        
        foreach ($facturas as $index => $factura) {
            
            if ($montoRestante <= 0) {
                break;
            }
            
            if ($factura->saldo_pendiente <= 0) {
                continue;
            }
            
            $montoAPagar = 0;
            $montoAPagarBs = 0;
            $cerrarFactura = false;
            
            if ($montoRestante >= $factura->saldo_pendiente) {
                // Paga factura completa
                $montoAPagar = $factura->saldo_pendiente;
                $montoAPagarBs = $factura->saldo_pendiente_bs ?? ($factura->saldo_pendiente * $pago->TasaDeCambio);
                $montoRestante -= $montoAPagar;
                $montoBsRestante -= $montoAPagarBs;
                $cerrarFactura = true;
            } else {
                // Pago parcial
                $montoAPagar = $montoRestante;
                $montoAPagarBs = $montoBsRestante;
                $montoRestante = 0;
                $montoBsRestante = 0;
            }
            
            // Obtener la sucursal de la factura (como en .NET)
            $sucursalId = $factura->SucursalId ?? 8;
            
            // Crear descripción automática (como en .NET)
            $descripcionAuto = 'Auto.' . ($pago->Descripcion ?? 'Pago registrado');
            
            // Crear transacción - SOLO con las columnas que existen
            $transaccionId = DB::connection('sqlsrv')->table('Transacciones')->insertGetId([
                'Descripcion' => $descripcionAuto,
                'MontoAbonado' => $montoAPagarBs,
                'MontoDivisaAbonado' => $montoAPagar,
                'NumeroOperacion' => $numeroOperacion,
                'DivisaId' => null,
                'TasaDeCambio' => $pago->TasaDeCambio,
                'Tipo' => 0, // 0 = PagoMercancia
                'FormaDePago' => $pago->FormaDePago,
                'Estatus' => 2, // 2 = Pagada
                'Fecha' => $pago->Fecha,
                'UrlComprobante' => $pago->UrlComprobante,
                'SucursalOrigenId' => $sucursalId,
                'SucursalId' => $sucursalId,
                'Observacion' => $descripcionAuto,
                'Nombre' => '',
                'Cedula' => '',
                'CategoriaId' => 0
            ]);
            
            // Guardar relación en TransaccionesProveedor
            DB::connection('sqlsrv')->table('TransaccionesProveedor')->insert([
                'ProveedorId' => $proveedorId,
                'TransaccionId' => $transaccionId,
                'FacturaId' => $factura->ID
            ]);
            
            // Cerrar factura si se pagó completa (como en .NET)
            if ($cerrarFactura) {
                DB::connection('sqlsrv')->table('Facturas')
                    ->where('ID', $factura->ID)
                    ->update(['Estatus' => 5]); // 5 = Pagada/Cerrada
            }
            
            $transaccionesCreadas[] = $transaccionId;
        }
        
        // Actualizar el objeto pago con el primer ID generado
        $pago->Id = $transaccionesCreadas[0] ?? 0;
        $pago->NumeroOperacion = $numeroOperacion;
        
        return $pago;
    }

    /**
     * Mostrar detalle de un pago
     */
    public function detallePago($id)
    {
        try {
            // Buscar el pago en Transacciones
            $pago = DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->first();
            
            if (!$pago) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Pago no encontrado');
            }
            
            // Buscar la relación con proveedor y factura
            $relacion = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            if ($relacion) {
                // Buscar datos del proveedor
                $proveedor = DB::connection('sqlsrv')
                    ->table('Proveedores')
                    ->where('ProveedorId', $relacion->ProveedorId)
                    ->first();
                
                // Buscar la factura relacionada
                $factura = DB::connection('sqlsrv')
                    ->table('Facturas')
                    ->where('ID', $relacion->FacturaId)
                    ->first();
            }
            
            // Mapear estatus del pago
            $estatusMap = [
                1 => ['texto' => 'Pendiente', 'clase' => 'warning'],
                2 => ['texto' => 'Pagada', 'clase' => 'success'],
                4 => ['texto' => 'Cerrada', 'clase' => 'secondary'],
                5 => ['texto' => 'Anulada', 'clase' => 'danger']
            ];
            
            $estatusPago = $estatusMap[$pago->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'secondary'];
            
            // Mapear forma de pago
            $formaPagoMap = [
                0 => 'Efectivo',
                1 => 'Transferencia',
                2 => 'Tarjeta',
                3 => 'Cheque',
                4 => 'Otros'
            ];
            
            $formaPagoTexto = $formaPagoMap[$pago->FormaDePago] ?? 'Desconocido';
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Listado Proveedores'
            ]);
            
            return view('cpanel.pagos.detalle_pago', compact(
                'pago',
                'relacion',
                'proveedor',
                'factura',
                'estatusPago',
                'formaPagoTexto'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en detallePago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el detalle del pago: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición de pago (solo comprobante)
     */
    public function editarPago($id)
    {
        try {
            // Buscar el pago en Transacciones
            $pago = DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->first();
            
            if (!$pago) {
                return redirect()->route('cpanel.proveedor.mercancia.listado')
                    ->with('error', 'Pago no encontrado');
            }
            
            // Buscar la relación con proveedor
            $relacion = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            if ($relacion) {
                $proveedor = DB::connection('sqlsrv')
                    ->table('Proveedores')
                    ->where('ProveedorId', $relacion->ProveedorId)
                    ->first();
            }
            
            // Mapeo de forma de pago
            $formaPagoMap = [
                0 => 'Efectivo',
                1 => 'Transferencia',
                2 => 'Tarjeta',
                3 => 'Cheque',
                4 => 'Otros'
            ];
            
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Listado Proveedores'
            ]);
            
            return view('cpanel.pagos.editar_pago', compact('pago', 'relacion', 'proveedor', 'formaPagoMap'));
            
        } catch (\Exception $e) {
            Log::error('Error en editarPago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar pago (solo comprobante)
     */
    public function actualizarPago(Request $request, $id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // Validar datos
            $request->validate([
                'monto_divisa' => 'required|numeric|min:0.01',
                'tasa_cambio' => 'required|numeric|min:0',
                'fecha' => 'required|date',
                'descripcion' => 'nullable|string|max:500',
                'numero_operacion' => 'nullable|string|max:100',
                'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);
            
            // 1. Buscar el pago original
            $pagoOriginal = DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->first();
            
            if (!$pagoOriginal) {
                throw new \Exception('Pago no encontrado');
            }
            
            // 2. Buscar la relación original
            $relacionOriginal = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            if (!$relacionOriginal) {
                throw new \Exception('Relación del pago no encontrada');
            }
            
            $proveedorId = $relacionOriginal->ProveedorId;
            
            // 3. ELIMINAR la transacción original y su relación (para recrearla)
            DB::connection('sqlsrv')->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->delete();
            
            DB::connection('sqlsrv')->table('Transacciones')
                ->where('ID', $id)
                ->delete();
            
            // 4. Subir nuevo comprobante si existe, si no conservar el anterior
            $urlComprobante = null;
            if ($request->hasFile('comprobante')) {
                $comprobante = $request->file('comprobante');
                $extension = $comprobante->getClientOriginalExtension();
                $fileName = 'PAG' . date('YmdHi') . '-' . $proveedorId . '.' . $extension;
                $comprobante->storeAs('images/comprobantes', $fileName, 'public');
                $urlComprobante = $fileName;
            } else {
                $urlComprobante = $pagoOriginal->UrlComprobante;
            }
            
            // 5. Calcular el nuevo monto en Bs
            $montoBs = $request->monto_divisa * $request->tasa_cambio;
            
            // 6. Crear nuevo objeto pago con los datos actualizados
            $pago = new \stdClass();
            $pago->Id = 0;
            $pago->Descripcion = $request->descripcion ?? $pagoOriginal->Descripcion;
            $pago->MontoDivisaAbonado = $request->monto_divisa;
            $pago->MontoAbonado = $montoBs;
            $pago->NumeroOperacion = $request->numero_operacion ?? $pagoOriginal->NumeroOperacion;
            $pago->TasaDeCambio = $request->tasa_cambio;
            $pago->FormaDePago = $pagoOriginal->FormaDePago;
            $pago->Fecha = $request->fecha;
            $pago->Estatus = $pagoOriginal->Estatus;
            $pago->Tipo = $pagoOriginal->Tipo;
            $pago->UrlComprobante = $urlComprobante;
            
            // 7. Obtener facturas vigentes del proveedor
            $facturasVigentes = $this->buscarFacturasActivas($proveedorId);
            
            // 8. Distribuir el nuevo monto entre las facturas
            $montoRestante = $pago->MontoDivisaAbonado;
            $transaccionCreada = null;
            $facturasAfectadas = [];
            
            foreach ($facturasVigentes as $factura) {
                if ($montoRestante <= 0) break;
                
                if ($factura->saldo_pendiente <= 0) continue;
                
                $montoAPagar = 0;
                $cerrarFactura = false;
                
                if ($montoRestante >= $factura->saldo_pendiente) {
                    $montoAPagar = $factura->saldo_pendiente;
                    $montoRestante -= $montoAPagar;
                    $cerrarFactura = true;
                } else {
                    $montoAPagar = $montoRestante;
                    $montoRestante = 0;
                }
                
                $descripcionAuto = 'Auto.' . ($pago->Descripcion ?? 'Pago registrado');
                $sucursalId = $factura->SucursalId ?? 8;
                
                // Crear nueva transacción
                $transaccionId = DB::connection('sqlsrv')->table('Transacciones')->insertGetId([
                    'Descripcion' => $descripcionAuto,
                    'MontoAbonado' => $montoAPagar * $pago->TasaDeCambio,
                    'MontoDivisaAbonado' => $montoAPagar,
                    'NumeroOperacion' => $pago->NumeroOperacion,
                    'DivisaId' => null,
                    'TasaDeCambio' => $pago->TasaDeCambio,
                    'Tipo' => $pago->Tipo,
                    'FormaDePago' => $pago->FormaDePago,
                    'Estatus' => $pago->Estatus,
                    'Fecha' => $pago->Fecha,
                    'UrlComprobante' => $pago->UrlComprobante,
                    'SucursalOrigenId' => $sucursalId,
                    'SucursalId' => $sucursalId,
                    'Observacion' => $descripcionAuto,
                    'Nombre' => '',
                    'Cedula' => '',
                    'CategoriaId' => 0
                ]);
                
                // Guardar relación
                DB::connection('sqlsrv')->table('TransaccionesProveedor')->insert([
                    'ProveedorId' => $proveedorId,
                    'TransaccionId' => $transaccionId,
                    'FacturaId' => $factura->ID
                ]);
                
                $facturasAfectadas[] = [
                    'factura_id' => $factura->ID,
                    'factura_numero' => $factura->Numero,
                    'monto_pagado' => $montoAPagar,
                    'factura_cerrada' => $cerrarFactura
                ];
                
                $transaccionCreada = $transaccionId;
            }
            
            DB::connection('sqlsrv')->commit();
            
            return redirect()->route('cpanel.pagos.detalle', $transaccionCreada)
                ->with('success', 'Pago actualizado correctamente. Se redistribuyó $' . number_format($request->monto_divisa, 2) . ' entre las facturas pendientes.');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('Error en actualizarPago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un pago
     */
    public function eliminarPago($id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Buscar la relación en TransaccionesProveedor
            $relacion = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            if (!$relacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la relación del pago'
                ], 404);
            }
            
            $proveedorId = $relacion->ProveedorId;
            
            // 2. Eliminar la relación en TransaccionesProveedor
            DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->delete();
            
            // 3. Eliminar la transacción en Transacciones
            DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->delete();
            
            DB::connection('sqlsrv')->commit();
            
            // Obtener información de la factura afectada para el mensaje
            $factura = null;
            if ($relacion->FacturaId) {
                $factura = DB::connection('sqlsrv')
                    ->table('Facturas')
                    ->where('ID', $relacion->FacturaId)
                    ->first();
            }
            
            Log::info('Pago eliminado correctamente', [
                'transaccion_id' => $id,
                'proveedor_id' => $proveedorId,
                'factura_id' => $relacion->FacturaId
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'El pago ha sido eliminado correctamente',
                'proveedor_id' => $proveedorId
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function imprimirRecibo($id)
    {
        try {
            // Buscar el pago
            $pago = DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->first();
            
            if (!$pago) {
                return redirect()->back()->with('error', 'Pago no encontrado');
            }
            
            // Buscar la relación con proveedor y factura
            $relacion = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            if (!$relacion) {
                return redirect()->back()->with('error', 'Relación del pago no encontrada');
            }
            
            // Buscar datos del proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $relacion->ProveedorId)
                ->first();
            
            // Buscar la factura asociada
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $relacion->FacturaId)
                ->first();
            
            // Buscar sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $pago->SucursalId ?? 8)
                ->first();
            
            // Forma de pago
            $formaPagoMap = [
                0 => 'Efectivo',
                1 => 'Transferencia',
                2 => 'Tarjeta de Crédito',
                3 => 'Cheque',
                4 => 'Otros'
            ];
            
            $formaPagoTexto = $formaPagoMap[$pago->FormaDePago] ?? 'Desconocido';
            
            // Número de comprobante
            $numeroComprobante = $pago->NumeroOperacion ?? 'N/A';
            
            // Montos
            $montoDivisa = $pago->MontoDivisaAbonado ?? 0;
            $montoBs = $pago->MontoAbonado ?? 0;
            $tasaCambio = $pago->TasaDeCambio ?? 0;
            
            // Obtener imagen del comprobante si existe
            $comprobanteSrc = null;
            if ($pago->UrlComprobante) {
                $comprobanteSrc = FileHelper::getOrDownloadFile(
                    'images/comprobantes/',
                    $pago->UrlComprobante,
                    null
                );
            }
            
            // Configurar para vista de impresión
            return view('cpanel.pagos.recibo_pago', compact(
                'pago',
                'proveedor',
                'factura',
                'sucursal',
                'formaPagoTexto',
                'numeroComprobante',
                'montoDivisa',
                'montoBs',
                'tasaCambio',
                'comprobanteSrc'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en imprimirRecibo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    public function verComprobante($id)
    {
        try {
            // Buscar el pago
            $pago = DB::connection('sqlsrv')
                ->table('Transacciones')
                ->where('ID', $id)
                ->first();
            
            if (!$pago) {
                return redirect()->back()->with('error', 'Pago no encontrado');
            }
            
            if (!$pago->UrlComprobante) {
                return redirect()->back()->with('error', 'No hay comprobante asociado a este pago');
            }
            
            // Buscar la relación con proveedor
            $relacion = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor')
                ->where('TransaccionId', $id)
                ->first();
            
            // Buscar datos del proveedor
            $proveedor = null;
            if ($relacion) {
                $proveedor = DB::connection('sqlsrv')
                    ->table('Proveedores')
                    ->where('ProveedorId', $relacion->ProveedorId)
                    ->first();
            }
            
            // Obtener la imagen del comprobante usando FileHelper
            $comprobanteSrc = FileHelper::getOrDownloadFile(
                'images/comprobantes/',
                $pago->UrlComprobante,
                'assets/img/adminlte/img/no-image.png'
            );
            
            // Información adicional para la vista
            $numeroOperacion = $pago->NumeroOperacion ?? 'N/A';
            $montoDivisa = $pago->MontoDivisaAbonado ?? 0;
            $fecha = $pago->Fecha;
            
            return view('cpanel.pagos.ver_comprobante', compact(
                'pago',
                'proveedor',
                'comprobanteSrc',
                'numeroOperacion',
                'montoDivisa',
                'fecha'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en verComprobante: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el comprobante: ' . $e->getMessage());
        }
    }

    public function reciboPagosJson($id)
    {
        try {
            // Buscar la factura
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $id)
                ->first();
            
            if (!$factura) {
                return redirect()->back()->with('error', 'Factura no encontrada');
            }
            
            // Buscar los pagos de la factura
            $pagos = DB::connection('sqlsrv')
                ->table('TransaccionesProveedor as tp')
                ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                ->where('tp.FacturaId', $id)
                ->select([
                    't.ID',
                    't.NumeroOperacion',
                    't.Fecha',
                    't.MontoDivisaAbonado',
                    't.MontoAbonado',
                    't.TasaDeCambio',
                    't.Estatus',
                    't.Descripcion',
                    't.UrlComprobante'
                ])
                ->orderBy('t.Fecha', 'desc')
                ->get();
            
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $factura->ProveedorId)
                ->first();
            
            // Buscar sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $factura->SucursalId)
                ->first();
            
            // Calcular total pagado
            $totalPagado = $pagos->sum('MontoDivisaAbonado');
            
            // Calcular saldo pendiente
            $montoReal = DB::connection('sqlsrv')
                ->table('FacturaDetalles')
                ->where('FacturaId', $id)
                ->select(DB::raw('COALESCE(SUM(CantidadEmitida * CostoDivisa), 0) as total'))
                ->first();
            
            $montoFactura = ($montoReal->total ?? 0) + ($factura->Traspaso ?? 0);
            $saldoPendiente = max(0, $montoFactura - $totalPagado);
            
            // Mapear estatus
            $estatusMap = [
                1 => 'Pendiente',
                2 => 'Pagada',
                4 => 'Cerrada',
                5 => 'Anulada'
            ];
            
            foreach ($pagos as $pago) {
                $pago->EstatusTexto = $estatusMap[$pago->Estatus] ?? 'Desconocido';
            }
            
            $formaPagoMap = [
                0 => 'Efectivo',
                1 => 'Transferencia',
                2 => 'Tarjeta',
                3 => 'Cheque',
                4 => 'Otros'
            ];
            
            return view('cpanel.pagos.lista_pagos', compact(
                'factura',
                'pagos',
                'proveedor',
                'sucursal',
                'totalPagado',
                'saldoPendiente',
                'montoFactura',
                'formaPagoMap'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en reciboPagos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    public function reciboProductos($id)
    {
        try {
            // Buscar la factura
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $id)
                ->first();
            
            if (!$factura) {
                return redirect()->back()->with('error', 'Factura no encontrada');
            }
            
            // Buscar los detalles de la factura (productos)
            $detalles = DB::connection('sqlsrv')
                ->table('FacturaDetalles as fd')
                ->leftJoin('Productos as pr', 'fd.ProductoId', '=', 'pr.ID')
                ->where('fd.FacturaId', $id)
                ->select([
                    'fd.*',
                    'pr.ID as producto_id',
                    'pr.Codigo',
                    'pr.Descripcion as producto_nombre',
                    'pr.Referencia',
                    'pr.CostoDivisa',
                    'pr.CostoBs',
                    'pr.UrlFoto'
                ])
                ->get();
            
            // Calcular subtotal
            $subtotal = $detalles->sum(function($detalle) {
                return ($detalle->CantidadEmitida ?? 0) * ($detalle->CostoDivisa ?? 0);
            });
            
            // Total factura = subtotal + traspaso
            $totalFactura = $subtotal + ($factura->Traspaso ?? 0);
            
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $factura->ProveedorId)
                ->first();
            
            // Buscar sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $factura->SucursalId)
                ->first();
            
            return view('cpanel.pagos.recibo_productos', compact(
                'factura',
                'detalles',
                'proveedor',
                'sucursal',
                'subtotal',
                'totalFactura'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en reciboProductos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    public function reciboListaFacturas($id)
    {
        try {
            // Buscar el proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $id)
                ->first();
            
            if (!$proveedor) {
                return redirect()->back()->with('error', 'Proveedor no encontrado');
            }
            
            // Obtener facturas vigentes del proveedor
            $facturas = $this->buscarFacturasActivas($id);
            
            // Calcular totales
            $totalFacturas = $facturas->sum('MontoDivisa');
            $totalPagado = $facturas->sum('total_pagado');
            $saldoPendiente = $totalFacturas - $totalPagado;
            
            // Obtener imagen del proveedor
            $imgSrc = FileHelper::getOrDownloadFile(
                'images/proveedores/',
                $proveedor->UrlImagen ?? '',
                'assets/img/adminlte/img/proveedor_default.png'
            );
            
            return view('cpanel.proveedores.lista_recibo_facturas', compact(
                'proveedor',
                'facturas',
                'totalFacturas',
                'totalPagado',
                'saldoPendiente',
                'imgSrc'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en reciboFacturas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    public function listaContenedores()
    {
        try {
            $contenedores = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->whereIn('Estatus', [0, 1, 2])
                ->get();

            // Configurar menú activo
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Contenedores'
            ]);
            
            return view('cpanel.proveedores.listado_contenedores', compact('contenedores'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listaContenedores: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de contenedores: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario para crear contenedor
     */
    public function crearContenedor()
    {
        session([
            'menu_active' => 'Proveedor Mercancía',
            'submenu_active' => 'Contenedores'
        ]);
        
        return view('cpanel.proveedores.crear_contenedor');
    }

    /**
     * Guardar nuevo contenedor
     */
    public function guardarContenedor(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'fecha_creacion' => 'required|date',
                'flete' => 'nullable|numeric|min:0',
                'origen' => 'nullable|string|max:100',
                'aduana' => 'nullable|numeric|min:0',
                'monto_total_facturas' => 'nullable|numeric|min:0',
                'estatus' => 'required|integer|in:0,1,2',
                'fecha_recepcion' => 'nullable|date'
            ]);
            
            // Calcular porcentaje de gastos
            $flete = $request->flete ?? 0;
            $aduana = $request->aduana ?? 0;
            $montoTotalFacturas = $request->monto_total_facturas ?? 0;
            
            $porcentajeGastos = 0;
            if ($montoTotalFacturas > 0) {
                $totalGastos = $flete + $aduana;
                $porcentajeGastos = ($totalGastos * 100) / $montoTotalFacturas;
            }
            
            // Generar número de operación (como en .NET)
            $numeroOperacion = date('YmdHi');
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // Insertar contenedor
            $contenedorId = DB::connection('sqlsrv')->table('Contenedor')->insertGetId([
                'Nombre' => $request->nombre,
                'FechaCreacion' => $request->fecha_creacion,
                'FechaRecepcion' => $request->fecha_recepcion,
                'Flete' => $flete,
                'Aduana' => $aduana,
                'NumeroOperacion' => $numeroOperacion,
                'Origen' => $request->origen,
                'Estatus' => $request->estatus,
                'MontoTotalFacturas' => $montoTotalFacturas,
                'PorcentajeGastos' => $porcentajeGastos
            ]);
            
            DB::connection('sqlsrv')->commit();
            
            return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                ->with('success', 'El contenedor se ha creado exitósamente');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al guardar contenedor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al guardar el contenedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Ver detalle de un contenedor
     */
    public function detalleContenedor($id)
    {
        try {
            // Buscar el contenedor por ID
            $contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $id)
                ->first();
            
            if (!$contenedor) {
                return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                    ->with('error', 'Contenedor no encontrado');
            }
            
            // Calcular porcentaje de gastos si no existe
            if (!isset($contenedor->PorcentajeGastos) && $contenedor->MontoTotalFacturas > 0) {
                $totalGastos = ($contenedor->Flete ?? 0) + ($contenedor->Aduana ?? 0);
                $contenedor->PorcentajeGastos = ($totalGastos * 100) / $contenedor->MontoTotalFacturas;
            }
            
            // Buscar facturas asociadas a este contenedor
            $facturas = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ContenedorId', $id)
                ->select('ID', 'Numero', 'FechaCreacion', 'MontoDivisa', 'Estatus')
                ->get();
            
            // Mapeo de estatus del contenedor
            $estatusMap = [
                0 => ['texto' => 'Nuevo', 'clase' => 'primary'],
                1 => ['texto' => 'En Tránsito', 'clase' => 'warning'],
                2 => ['texto' => 'En Aduana', 'clase' => 'info'],
                3 => ['texto' => 'Recibido', 'clase' => 'info']
            ];
            
            $estatusContenedor = $estatusMap[$contenedor->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'secondary'];
            
            // Configurar menú activo
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Contenedores'
            ]);
            
            return view('cpanel.proveedores.detalle_contenedor', compact(
                'contenedor',
                'facturas',
                'estatusContenedor'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detalleContenedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                ->with('error', 'Error al cargar el detalle del contenedor: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición de contenedor
     */
    public function editarContenedor($id)
    {
        try {
            // Buscar el contenedor por ID
            $contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $id)
                ->first();
            
            if (!$contenedor) {
                return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                    ->with('error', 'Contenedor no encontrado');
            }
            
            // Mapeo de estatus
            $estatusMap = [
                0 => 'Nuevo',
                1 => 'En Tránsito',
                2 => 'En Aduana',
                3 => 'Recibido'
            ];
            
            // Configurar menú activo
            session([
                'menu_active' => 'Proveedor Mercancía',
                'submenu_active' => 'Contenedores'
            ]);
            
            return view('cpanel.proveedores.editar_contenedor', compact('contenedor', 'estatusMap'));
            
        } catch (\Exception $e) {
            \Log::error('Error en editarContenedor: ' . $e->getMessage());
            return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar contenedor
     */
    public function actualizarContenedor(Request $request, $id)
    {
        try {
            // Validar datos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'fecha_creacion' => 'required|date',
                'flete' => 'nullable|numeric|min:0',
                'origen' => 'nullable|string|max:100',
                'aduana' => 'nullable|numeric|min:0',
                'monto_total_facturas' => 'nullable|numeric|min:0',
                'estatus' => 'required|integer|in:0,1,2',
                'fecha_recepcion' => 'nullable|date'
            ]);
            
            // Verificar que el contenedor existe
            $contenedor = DB::connection('sqlsrv')
                ->table('Contenedor')
                ->where('Id', $id)
                ->first();
            
            if (!$contenedor) {
                return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                    ->with('error', 'Contenedor no encontrado');
            }
            
            // Calcular porcentaje de gastos
            $flete = $request->flete ?? 0;
            $aduana = $request->aduana ?? 0;
            $montoTotalFacturas = $request->monto_total_facturas ?? 0;
            
            $porcentajeGastos = 0;
            if ($montoTotalFacturas > 0) {
                $totalGastos = $flete + $aduana;
                $porcentajeGastos = ($totalGastos * 100) / $montoTotalFacturas;
            }
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // Actualizar contenedor
            DB::connection('sqlsrv')->table('Contenedor')
                ->where('Id', $id)
                ->update([
                    'Nombre' => $request->nombre,
                    'FechaCreacion' => $request->fecha_creacion,
                    'FechaRecepcion' => $request->fecha_recepcion,
                    'Flete' => $flete,
                    'Aduana' => $aduana,
                    'Origen' => $request->origen,
                    'Estatus' => $request->estatus,
                    'MontoTotalFacturas' => $montoTotalFacturas,
                    'PorcentajeGastos' => $porcentajeGastos
                ]);
            
            DB::connection('sqlsrv')->commit();
            
            return redirect()->route('cpanel.proveedor.mercancia.contenedores')
                ->with('success', 'El contenedor se ha actualizado exitósamente');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al actualizar contenedor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al actualizar el contenedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar contenedor
     */
    public function eliminarContenedor($id)
    {
        try {
            DB::connection('sqlsrv')->table('Contenedor')
                ->where('Id', $id)
                ->delete();
            
            return response()->json(['success' => true, 'message' => 'Contenedor eliminado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}