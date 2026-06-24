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

// use PhpOffice\PhpSpreadsheet\Reader\Xls;
// use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
// use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

use App\Helpers\FileHelper;

class RecepcionesController extends Controller
{
    public function listado_recepciones_proveedores(Request $request)
    {
        try {
            // Configurar menú activo
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Recibir de proveedor'
            ]);
            
            // Obtener fecha de inicio y fin del mes actual
            $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            $fechaFin = now()->endOfMonth()->format('Y-m-d');
            
            // Estatus = 1 (En Proceso) - como EnumRecepcion.EnProceso
            // Si quieres mostrar TODAS, usa RECEPCION_TODAS = -100
            $estatusRecepcion = 1; // 1 = En Proceso
            
            // Buscar recepciones
            $listaRecepciones = $this->buscarListadoRecepciones($fechaInicio, $fechaFin, $estatusRecepcion);
            
            return view('cpanel.recepciones.listado', compact('listaRecepciones'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_recepciones_proveedores: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de recepciones de proveedores: ' . $e->getMessage());
        }
    }

    private function buscarListadoRecepciones($fechaInicio = null, $fechaFin = null, $estatusRecepcion = null, $proveedorId = null, $recepcionId = null)
    {
        try {
            // Si no se especifican fechas, usar mes actual
            if (!$fechaInicio || !$fechaFin) {
                $fechaInicio = now()->startOfMonth()->format('Y-m-d');
                $fechaFin = now()->endOfMonth()->format('Y-m-d');
            }
            
            $recepcionesDTO = collect();
            
            // ==============================================
            // 1. Recepciones de PROVEEDOR (Tipo = 0)
            // ==============================================
            $queryProveedor = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('RecepcionesFacturas as rf', 'r.RecepcionId', '=', 'rf.RecepcionId')
                ->where('r.Tipo', 0)
                ->whereBetween('r.FechaCreacion', [$fechaInicio, $fechaFin])
                ->select([
                    'r.RecepcionId',
                    'r.Numero',
                    'r.FechaRecepcion',
                    'r.FechaCreacion',
                    'r.Estatus',
                    'r.Tipo',
                    'r.ProveedorId',
                    'r.SucursalDestinoId',
                    'r.EsConFactura',
                    'rf.FacturaId'
                ]);
            
            // Aplicar filtros
            if ($estatusRecepcion !== null && $estatusRecepcion != -100) {
                $queryProveedor->where('r.Estatus', $estatusRecepcion);
            }
            if ($proveedorId !== null) {
                $queryProveedor->where('r.ProveedorId', $proveedorId);
            }
            if ($recepcionId !== null) {
                $queryProveedor->where('r.RecepcionId', $recepcionId);
            }
            
            $recepcionesProveedor = $queryProveedor->get();
            
            foreach ($recepcionesProveedor as $item) {
                $recepcionDTO = new \stdClass();
                $recepcionDTO->RecepcionId = $item->RecepcionId;
                $recepcionDTO->Numero = $item->Numero;
                $recepcionDTO->FechaRecepcion = $item->FechaRecepcion;
                $recepcionDTO->FechaCreacion = $item->FechaCreacion;
                $recepcionDTO->Estatus = $item->Estatus;
                $recepcionDTO->Tipo = $item->Tipo;
                $recepcionDTO->ProveedorId = $item->ProveedorId;
                $recepcionDTO->SucursalDestinoId = $item->SucursalDestinoId;
                $recepcionDTO->EsConFactura = $item->EsConFactura;
                
                if ($item->FacturaId) {
                    $recepcionDTO->Factura = $this->buscarDatosFactura($item->FacturaId);
                }
                
                if ($recepcionDTO->ProveedorId) {
                    $recepcionDTO->Proveedor = $this->buscarProveedor($recepcionDTO->ProveedorId);
                }
                
                if ($item->SucursalDestinoId) {
                    $recepcionDTO->SucursalDestino = $this->buscarSucursal($item->SucursalDestinoId);
                }
                
                $recepcionesDTO->push($recepcionDTO);
            }
            
            // ==============================================
            // 2. Recepciones de TRANSFERENCIA o DISTRIBUCION (Tipo = 1 o 2)
            // ==============================================
            $queryTransferencia = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('RecepcionesTransferencias as rt', 'r.RecepcionId', '=', 'rt.RecepcionId')
                ->leftJoin('Transferencias as t', 'rt.TransferenciaId', '=', 't.TransferenciaId')  // ✅ CORREGIDO
                ->whereIn('r.Tipo', [1, 2])
                ->whereBetween('r.FechaCreacion', [$fechaInicio, $fechaFin])
                ->select([
                    'r.RecepcionId',
                    'r.Numero',
                    'r.FechaRecepcion',
                    'r.FechaCreacion',
                    'r.Estatus',
                    'r.Tipo',
                    'r.SucursalOrigenId',
                    'r.SucursalDestinoId',
                    't.TransferenciaId as transferencia_id',  // ✅ CORREGIDO
                    't.Numero as transferencia_numero'
                ]);
            
            // Aplicar filtros
            if ($estatusRecepcion !== null && $estatusRecepcion != -100) {
                $queryTransferencia->where('r.Estatus', $estatusRecepcion);
            }
            if ($recepcionId !== null) {
                $queryTransferencia->where('r.RecepcionId', $recepcionId);
            }
            
            $recepcionesTransferencia = $queryTransferencia->get();
            
            foreach ($recepcionesTransferencia as $item) {
                $recepcionDTO = new \stdClass();
                $recepcionDTO->RecepcionId = $item->RecepcionId;
                $recepcionDTO->Numero = $item->Numero;
                $recepcionDTO->FechaRecepcion = $item->FechaRecepcion;
                $recepcionDTO->FechaCreacion = $item->FechaCreacion;
                $recepcionDTO->Estatus = $item->Estatus;
                $recepcionDTO->Tipo = $item->Tipo;
                $recepcionDTO->SucursalOrigenId = $item->SucursalOrigenId;
                $recepcionDTO->SucursalDestinoId = $item->SucursalDestinoId;
                
                if ($item->transferencia_id) {
                    $recepcionDTO->TransferenciaRecibida = $this->buscarTransferencia($item->transferencia_id);
                }
                
                if ($recepcionDTO->SucursalDestinoId && $recepcionDTO->SucursalDestinoId != 0) {
                    $recepcionDTO->SucursalDestino = $this->buscarSucursal($recepcionDTO->SucursalDestinoId);
                }
                
                if ($recepcionDTO->SucursalOrigenId && $recepcionDTO->SucursalOrigenId != 0) {
                    $recepcionDTO->SucursalOrigen = $this->buscarSucursal($recepcionDTO->SucursalOrigenId);
                }
                
                $recepcionesDTO->push($recepcionDTO);
            }
            
            return $recepcionesDTO;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarListadoRecepciones: ' . $e->getMessage());
            return collect();
        }
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
        
        // ✅ NUEVO: Calcular cantidad recibida por producto (sumando todas las recepciones)
        foreach ($factura->Detalles as $detalle) {
            // Sumar todas las cantidades recibidas de este producto en todas las recepciones asociadas a esta factura
            $totalRecibido = DB::connection('sqlsrv')
                ->table('RecepcionesFacturas as rf')
                ->join('RecepcionesDetalles as rd', 'rf.RecepcionId', '=', 'rd.RecepcionId')
                ->where('rf.FacturaId', $id)
                ->where('rd.ProductoId', $detalle->ProductoId)
                ->sum('rd.CantidadRecibida');
            
            $detalle->CantidadRecibida = $totalRecibido ?? 0;
            $detalle->CantidadDisponible = ($detalle->CantidadEmitida ?? 0) - ($detalle->CantidadRecibida ?? 0);
            
            \Log::info('Producto factura', [
                'producto_id' => $detalle->ProductoId,
                'codigo' => $detalle->Codigo ?? 'N/A',
                'emitido' => $detalle->CantidadEmitida,
                'recibido' => $detalle->CantidadRecibida,
                'disponible' => $detalle->CantidadDisponible
            ]);
        }
        
        // // Calcular subtotal
        // $factura->Subtotal = $factura->Detalles->sum(function($detalle) {
        //     return ($detalle->CantidadEmitida ?? 0) * ($detalle->CostoDivisa ?? 0);
        // });
        
        // // Calcular Total Factura (Subtotal + Traspaso)
        // $factura->TotalFactura = ($factura->Subtotal ?? 0) + ($factura->CostoTraspaso ?? 0);
        // Calcular subtotal correctamente (considerando UxE)
        
        
        $subtotalCalculado = 0;
        foreach ($factura->Detalles as $detalle) {
            $uxe = $detalle->UxE ?? 1;
            $cantidadUnidades = ($detalle->CantidadEmitida ?? 0) * $uxe;
            $subtotalCalculado += $uxe * ($detalle->productoCostoDivisa ?? 0);
        }

        $factura->Subtotal = $subtotalCalculado;
        $factura->TotalFactura = $subtotalCalculado + ($factura->CostoTraspaso ?? 0);
        $factura->SaldoPendiente = $factura->TotalFactura - ($factura->TotalPagado ?? 0);
        
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

    // private function uspObtenerPorcentajeGastosFlete($contenedorId)
    // {
    //     // 1. Calcular TotalFacturas (suma de productos)
    //     $totalFacturas = DB::connection('sqlsrv')
    //         ->table('FacturaDetalles as fd')
    //         ->join('Facturas as f', 'fd.FacturaId', '=', 'f.ID')
    //         ->where('f.ContenedorId', $contenedorId)
    //         ->sum(DB::raw('fd.CantidadEmitida * fd.CostoDivisa'));
        
    //     // 2. Calcular TotalFlete (Aduana + Flete + SUM(Traspaso))
    //     $contenedor = DB::connection('sqlsrv')
    //         ->table('Contenedor')
    //         ->where('Id', $contenedorId)
    //         ->first();
        
    //     $totalTraspaso = DB::connection('sqlsrv')
    //         ->table('Facturas')
    //         ->where('ContenedorId', $contenedorId)
    //         ->sum('Traspaso');
        
    //     $totalFlete = ($contenedor->Aduana ?? 0) + ($contenedor->Flete ?? 0) + ($totalTraspaso ?? 0);
        
    //     // 3. Calcular porcentaje
    //     if ($totalFacturas > 0) {
    //         return ($totalFlete * 100) / $totalFacturas;
    //     }
        
    //     return 0;
    // }

    private function uspObtenerPorcentajeGastosFlete($contenedorId)
    {
        // Buscar el contenedor
        $contenedor = DB::connection('sqlsrv')
            ->table('Contenedor')
            ->where('Id', $contenedorId)
            ->first();
        
        if (!$contenedor) {
            return 0;
        }
        
        // Si ya tiene el porcentaje calculado, devolverlo
        if (isset($contenedor->PorcentajeGastos) && $contenedor->PorcentajeGastos > 0) {
            return $contenedor->PorcentajeGastos;
        }
        
        // Si no tiene porcentaje, calcularlo
        $totalFacturas = DB::connection('sqlsrv')
            ->table('FacturaDetalles as fd')
            ->join('Facturas as f', 'fd.FacturaId', '=', 'f.ID')
            ->where('f.ContenedorId', $contenedorId)
            ->sum(DB::raw('fd.CantidadEmitida * fd.CostoDivisa'));
        
        $totalGastos = ($contenedor->Aduana ?? 0) + ($contenedor->Flete ?? 0);
        
        if ($totalFacturas > 0) {
            return ($totalGastos * 100) / $totalFacturas;
        }
        
        return 0;
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
                'fd.CostoDivisa as productoCostoDivisa',
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

    private function buscarProveedor($proveedorId, $enumDetalles = 0)
    {
        if (!$proveedorId) {
            return null;
        }
        
        // Buscar proveedor básico
        $proveedor = DB::connection('sqlsrv')
            ->table('Proveedores')
            ->where('ProveedorId', $proveedorId)
            ->first();
        
        if (!$proveedor) {
            return null;
        }
        
        // Según el nivel de detalle, cargar información adicional
        if ($enumDetalles >= 1) {
            // Cargar facturas del proveedor
            $facturas = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ProveedorId', $proveedorId)
                ->get();
            
            $proveedor->Facturas = $facturas;
            
            // Si se requieren detalles de facturas
            if ($enumDetalles >= 2) {
                foreach ($proveedor->Facturas as $factura) {
                    // Cargar detalles de cada factura
                    $detalles = DB::connection('sqlsrv')
                        ->table('FacturaDetalles')
                        ->where('FacturaId', $factura->ID)
                        ->get();
                    
                    $factura->FacturaDetalles = $detalles;
                }
            }
        }
        
        return $proveedor;
    }

    private function buscarSucursal($sucursalId)
    {
        if (!$sucursalId || $sucursalId == 0) {
            return null;
        }
        
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('ID', $sucursalId)
            ->select(['ID', 'Nombre', 'Direccion', 'EsActiva', 'Tipo'])
            ->first();
        
        return $sucursal;
    }

    private function buscarTransferencia($transferenciaId)
    {
        if (!$transferenciaId) {
            return null;
        }
        
        $transferencia = DB::connection('sqlsrv')
            ->table('Transferencias as t')
            ->leftJoin('Sucursales as s_origen', 't.SucursalOrigenId', '=', 's_origen.ID')
            ->leftJoin('Sucursales as s_destino', 't.SucursalDestinoId', '=', 's_destino.ID')
            ->where('t.TransferenciaId', $transferenciaId)
            ->select([
                't.ID',
                't.Numero',
                't.FechaTransferencia',
                't.Estatus',
                't.Observacion',
                't.SucursalOrigenId',
                't.SucursalDestinoId',
                's_origen.Nombre as sucursal_origen_nombre',
                's_destino.Nombre as sucursal_destino_nombre'
            ])
            ->first();
        
        return $transferencia;
    }

    public function nuevaRecepcion()
    {
        session()->forget('recepcion_activa');
        
        // Tipo Mercancia = 0
        $tipo = 0;
        
        // Obtener listado de proveedores
        $proveedores = GeneralHelper::BuscarListadoProveedores($tipo);
        
        return view('cpanel.recepciones.seleccionar_proveedor', compact('proveedores'));
    }

    public function crearRecepcion($proveedorId)
    {
        try {
            // Buscar proveedor
            $proveedor = DB::connection('sqlsrv')
                ->table('Proveedores')
                ->where('ProveedorId', $proveedorId)
                ->first();
            
            if (!$proveedor) {
                return redirect()->route('cpanel.recepciones.nuevo')
                    ->with('error', 'Proveedor no encontrado');
            }
            
            // Generar nueva recepción (guarda en BD)
            $recepcion = $this->generarNuevaRecepcion($proveedor);
            
            // Guardar en sesión la recepción activa
            session(['recepcion_activa' => $recepcion]);
            
            // Obtener lista de sucursales para el formulario (si es necesario)
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 0)
                ->get();
            
            //return view('cpanel.recepciones.crear', compact('recepcion', 'proveedor', 'sucursales'));

            // ✅ Redirigir a edición (no mostrar vista crear)
            return redirect()->route('cpanel.recepciones.editar', $recepcion->RecepcionId)
                ->with('success', 'Recepción creada correctamente. Ahora puede agregar los productos.');
            
        } catch (\Exception $e) {
            \Log::error('Error en crearRecepcion: ' . $e->getMessage());
            return redirect()->route('cpanel.recepciones.nuevo')
                ->with('error', 'Error al crear la recepción: ' . $e->getMessage());
        }
    }

    private function generarNuevaRecepcion($proveedor)
    {
        // Buscar sucursal de tipo Almacén
        $sucursalAlmacen = $this->buscarSucursalAlmacen();
        
        // Crear número de recepción: REC{yyyyMMddHHss}-{proveedorId}
        $numeroRecepcion = 'REC' . date('YmdHi') . '-' . $proveedor->ProveedorId;
        
        // Insertar en la tabla Recepciones
        $recepcionId = DB::connection('sqlsrv')->table('Recepciones')->insertGetId([
            'Numero' => $numeroRecepcion,
            'ProveedorId' => $proveedor->ProveedorId,
            'FechaCreacion' => now()->format('Y-m-d'),
            'FechaRecepcion' => now()->format('Y-m-d'),
            'Estatus' => 1, // En Proceso (EnumFactura.EnProceso = 1)
            'Tipo' => 0, // Proveedor (EnumTipoRecepcion.Proveedor = 0)
            'EsConFactura' => true,
            'SucursalDestinoId' => $sucursalAlmacen ? $sucursalAlmacen->ID : null,
            'SucursalOrigenId' => null,
            'TasaDeCambio' => 0
        ]);
        
        // Obtener la recepción recién creada
        $recepcion = DB::connection('sqlsrv')
            ->table('Recepciones')
            ->where('RecepcionId', $recepcionId)
            ->first();
        
        // Cargar la sucursal destino
        $sucursalDestino = null;
        if ($recepcion->SucursalDestinoId) {
            $sucursalDestino = $this->buscarSucursal($recepcion->SucursalDestinoId);
        }
        
        // Crear objeto para la sesión/vista
        $recepcionDTO = new \stdClass();
        $recepcionDTO->RecepcionId = $recepcion->RecepcionId;
        $recepcionDTO->Numero = $recepcion->Numero;
        $recepcionDTO->ProveedorId = $recepcion->ProveedorId;
        $recepcionDTO->FechaRecepcion = $recepcion->FechaRecepcion;
        $recepcionDTO->FechaCreacion = $recepcion->FechaCreacion;
        $recepcionDTO->Estatus = $recepcion->Estatus;
        $recepcionDTO->Tipo = $recepcion->Tipo;
        $recepcionDTO->EsConFactura = $recepcion->EsConFactura;
        $recepcionDTO->SucursalDestinoId = $recepcion->SucursalDestinoId;
        $recepcionDTO->SucursalDestino = $sucursalDestino;
        $recepcionDTO->Productos = collect();
        
        return $recepcionDTO;
    }

    private function buscarSucursalAlmacen()
    {
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('Tipo', 2) // EnumTipoSucursal.Almacen = 2 (ajusta según tu BD)
            ->first();
        
        return $sucursal;
    }

    public function detalleRecepcion($id)
    {
        try {
            // 1. Buscar la recepción con más información
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
                ->leftJoin('Sucursales as sd', 'r.SucursalDestinoId', '=', 'sd.ID')
                ->leftJoin('Sucursales as so', 'r.SucursalOrigenId', '=', 'so.ID')
                ->leftJoin('RecepcionesFacturas as rf', 'r.RecepcionId', '=', 'rf.RecepcionId')
                ->leftJoin('Facturas as f', 'rf.FacturaId', '=', 'f.ID')
                ->where('r.RecepcionId', $id)
                ->select([
                    'r.*',
                    'p.Nombre as proveedor_nombre',
                    'p.Rif_Cedula as proveedor_rif',
                    'p.TelefonoMovil as proveedor_telefono',
                    'p.CorreoElectronico as proveedor_email',
                    'sd.Nombre as sucursal_destino',
                    'so.Nombre as sucursal_origen',
                    'f.Numero as factura_numero',
                    'f.MontoDivisa as factura_monto'
                ])
                ->first();
            
            if (!$recepcion) {
                return redirect()->route('cpanel.recepciones.proveedor')
                    ->with('error', 'Recepción no encontrada');
            }
            
            // 2. Obtener los detalles de la recepción (productos)
            $detalles = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles as rd')
                ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
                ->where('rd.RecepcionId', $id)
                ->select([
                    'rd.*',
                    'p.Codigo',
                    'p.Descripcion as producto_nombre',
                    'p.Referencia'
                ])
                ->get();
            
            // 3. Calcular totales
            $totalRecepcion = 0;
            foreach ($detalles as $detalle) {
                $totalRecepcion += ($detalle->CantidadRecibida ?? 0) * ($detalle->CostoDivisa ?? 0);
            }
            
            // 4. Estatus de la recepción
            $estatusMap = [
                0 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger'],
                1 => ['texto' => 'En Proceso', 'clase' => 'badge bg-warning'],
                2 => ['texto' => 'Procesada', 'clase' => 'badge bg-info'],
                4 => ['texto' => 'En Auditoría', 'clase' => 'badge bg-secondary'],
                5 => ['texto' => 'Auditada', 'clase' => 'badge bg-success'],
                6 => ['texto' => 'Finalizada', 'clase' => 'badge bg-primary'],
                7 => ['texto' => 'Pagada', 'clase' => 'badge bg-success'],
                8 => ['texto' => 'Finalizada-Pagada', 'clase' => 'badge bg-dark']
            ];
            
            $estatus = $estatusMap[$recepcion->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary'];
            
            // 5. Tipo de recepción
            $tipoMap = [
                0 => 'De proveedor',
                1 => 'Distribución de almacén',
                2 => 'Transferencia'
            ];
            $tipoTexto = $tipoMap[$recepcion->Tipo] ?? 'Desconocido';
            
            // 6. Configurar menú activo según el estatus
            if (in_array($recepcion->Estatus, [1, 4])) {
                session([
                    'menu_active' => 'Recepciones',
                    'submenu_active' => 'Recibir de proveedor'
                ]);
            } else {
                session([
                    'menu_active' => 'Recepciones',
                    'submenu_active' => 'Recepciones Finalizadas'
                ]);
            }
            
            return view('cpanel.recepciones.detalle', compact(
                'recepcion',
                'detalles',
                'totalRecepcion',
                'estatus',
                'tipoTexto'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detalleRecepcion: ' . $e->getMessage());
            return redirect()->route('cpanel.recepciones.proveedor')
                ->with('error', 'Error al cargar el detalle de la recepción: ' . $e->getMessage());
        }
    }

    // public function editarRecepcion($id)
    // {
    //     try {
    //         // Buscar la recepción con proveedor
    //         $recepcion = DB::connection('sqlsrv')
    //             ->table('Recepciones as r')
    //             ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
    //             ->where('r.RecepcionId', $id)
    //             ->select([
    //                 'r.*',
    //                 'p.Nombre as proveedor_nombre',
    //                 'p.Rif_Cedula as proveedor_rif',
    //                 'p.TelefonoMovil as proveedor_telefono',
    //                 'p.CorreoElectronico as proveedor_email'
    //             ])
    //             ->first();
            
    //         if (!$recepcion) {
    //             return redirect()->route('cpanel.recepciones.listado')
    //                 ->with('error', 'Recepción no encontrada');
    //         }
            
    //         // ✅ Si la recepción tiene factura asociada, cargar los datos de la factura
    //         $facturaDTO = null;
    //         if ($recepcion->EsConFactura == 1) {
    //             // Obtener la factura asociada
    //             $facturaRelacion = DB::connection('sqlsrv')
    //                 ->table('RecepcionesFacturas')
    //                 ->where('RecepcionId', $id)
    //                 ->first();
                
    //             if ($facturaRelacion) {
    //                 $facturaDTO = $this->buscarDatosFactura($facturaRelacion->FacturaId);
    //             }
    //         }
            
    //         // Obtener facturas pendientes del proveedor (solo si no tiene factura asociada)
    //         $facturasPendientes = collect();
    //         if ($recepcion->EsConFactura == 0) {
                
    //             \Log::info('=== BUSCANDO FACTURAS ===', [
    //                 'proveedor_id' => $recepcion->ProveedorId,
    //                 'es_con_factura' => $recepcion->EsConFactura
    //             ]);
                
    //             $facturasPendientes = DB::connection('sqlsrv')
    //                 ->table('Facturas')
    //                 ->where('ProveedorId', $recepcion->ProveedorId)
    //                 ->where('Estatus', 1)
    //                 ->where('MontoDivisa', '>', 0)
    //                 ->select(['ID', 'Numero', 'MontoDivisa', 'Estatus'])
    //                 ->get();
                
    //             \Log::info('Facturas encontradas (sin pagos)', [
    //                 'cantidad' => $facturasPendientes->count(),
    //                 'facturas' => $facturasPendientes->toArray()
    //             ]);
                
    //             foreach ($facturasPendientes as $factura) {
    //                 $totalPagado = DB::connection('sqlsrv')
    //                     ->table('TransaccionesProveedor as tp')
    //                     ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
    //                     ->where('tp.FacturaId', $factura->ID)
    //                     ->sum('t.MontoDivisaAbonado');
                    
    //                 $factura->saldo_pendiente = max(0, ($factura->MontoDivisa ?? 0) - ($totalPagado ?? 0));
                    
    //                 \Log::info('Factura procesada', [
    //                     'id' => $factura->ID,
    //                     'numero' => $factura->Numero,
    //                     'monto' => $factura->MontoDivisa,
    //                     'total_pagado' => $totalPagado,
    //                     'saldo_pendiente' => $factura->saldo_pendiente
    //                 ]);
    //             }
                
    //             $facturasPendientes = $facturasPendientes->filter(function($factura) {
    //                 return $factura->saldo_pendiente > 0;
    //             });
                
    //             \Log::info('Facturas después de filtrar por saldo > 0', [
    //                 'cantidad' => $facturasPendientes->count()
    //             ]);
    //         }
            
    //         // Obtener detalles de productos de la recepción
    //         $detalles = $this->buscarDetallesRecepcion($id);
            
    //         // ✅ Calcular todos los totales
    //         $totalRecepcion = 0;
    //         $totalUnidades = 0;
    //         $totalItems = $detalles->count();
            
    //         foreach ($detalles as $detalle) {
    //             $subtotal = ($detalle->CantidadPedida ?? 0) * ($detalle->CostoDivisa ?? 0);
    //             $totalRecepcion += $subtotal;
    //             $totalUnidades += ($detalle->CantidadPedida ?? 0);
    //         }
            
    //         $subtotalRecepcion = $totalRecepcion;  // El subtotal es el mismo que el total
    //         $totalRecepcionBs = 0;  // Siempre en 0 como en .NET
            
    //         session([
    //             'menu_active' => 'Recepciones',
    //             'submenu_active' => 'Recibir de proveedor'
    //         ]);
            
    //         return view('cpanel.recepciones.editar', compact(
    //             'recepcion', 
    //             'facturasPendientes', 
    //             'detalles', 
    //             'totalRecepcion',
    //             'facturaDTO',
    //             'subtotalRecepcion',     // ✅ Agregar
    //             'totalRecepcionBs',      // ✅ Agregar
    //             'totalItems',            // ✅ Agregar
    //             'totalUnidades'          // ✅ Agregar
    //         ));
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error en editarRecepcion: ' . $e->getMessage());
    //         return redirect()->route('cpanel.recepciones.listado')
    //             ->with('error', 'Error al cargar la edición de la recepción: ' . $e->getMessage());
    //     }
    // }

    public function editarRecepcion($id)
    {
        try {
            // Buscar la recepción con proveedor
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
                ->where('r.RecepcionId', $id)
                ->select([
                    'r.*',
                    'p.Nombre as proveedor_nombre',
                    'p.Rif_Cedula as proveedor_rif',
                    'p.TelefonoMovil as proveedor_telefono',
                    'p.CorreoElectronico as proveedor_email'
                ])
                ->first();
            
            if (!$recepcion) {
                return redirect()->route('cpanel.recepciones.proveedor')
                    ->with('error', 'Recepción no encontrada');
            }
            
            // ✅ Verificar si tiene factura asociada (basado en RecepcionesFacturas, no en EsConFactura)
            $facturaRelacion = DB::connection('sqlsrv')
                ->table('RecepcionesFacturas')
                ->where('RecepcionId', $id)
                ->first();

            $tieneFacturaAsociada = $facturaRelacion ? true : false;  // ✅ Definir aquí

            // ✅ Si tiene factura asociada, cargar sus datos
            $facturaDTO = null;
            if ($tieneFacturaAsociada) {
                $facturaDTO = $this->buscarDatosFactura($facturaRelacion->FacturaId);
            }

            // dd($facturaDTO);

            // ✅ Obtener facturas pendientes SOLO si NO tiene factura asociada
            $facturasPendientes = collect();
            if (!$tieneFacturaAsociada) {  // ✅ Ahora sí está definida
                $facturasPendientes = DB::connection('sqlsrv')
                    ->table('Facturas')
                    ->where('ProveedorId', $recepcion->ProveedorId)
                    ->where('Estatus', 1)  // En Proceso
                    ->select(['ID', 'Numero', 'Traspaso'])
                    ->get();
                
                foreach ($facturasPendientes as $factura) {
                    // Calcular total desde FacturaDetalles
                    $totalDetalles = DB::connection('sqlsrv')
                        ->table('FacturaDetalles')
                        ->where('FacturaId', $factura->ID)
                        ->sum(DB::raw('CantidadEmitida * CostoDivisa'));
                    
                    // Sumar el traspaso
                    $totalFactura = ($totalDetalles ?? 0) + ($factura->Traspaso ?? 0);
                    
                    // Calcular pagado
                    $totalPagado = DB::connection('sqlsrv')
                        ->table('TransaccionesProveedor as tp')
                        ->join('Transacciones as t', 'tp.TransaccionId', '=', 't.ID')
                        ->where('tp.FacturaId', $factura->ID)
                        ->sum('t.MontoDivisaAbonado');
                    
                    $factura->MontoDivisa = $totalFactura;
                    $factura->saldo_pendiente = max(0, $totalFactura - ($totalPagado ?? 0));
                    
                    // Log para depurar
                    \Log::info('Factura procesada', [
                        'id' => $factura->ID,
                        'numero' => $factura->Numero,
                        'total_detalles' => $totalDetalles,
                        'traspaso' => $factura->Traspaso,
                        'total_factura' => $totalFactura,
                        'total_pagado' => $totalPagado,
                        'saldo' => $factura->saldo_pendiente
                    ]);
                }
                
                $facturasPendientes = $facturasPendientes->filter(function($factura) {
                    return $factura->saldo_pendiente > 0;
                });
            }
            
            // Obtener detalles de productos de la recepción
            $detalles = $this->buscarDetallesRecepcion($id);

            // dd($detalles);
            
            // Calcular totales
            $totalRecepcion = 0;
            $totalUnidades = 0;
            $totalItems = $detalles->count();
            
            foreach ($detalles as $detalle) {
                $subtotal = ($detalle->factura_uxe ?? 0) * ($detalle->factura_costo_divisa ?? 0);
                $totalRecepcion += $subtotal;
                $totalUnidades += ($detalle->CantidadPedida ?? 0);
            }
            
            $subtotalRecepcion = $totalRecepcion;
            $totalRecepcionBs = 0;
            
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Recibir de proveedor'
            ]);

            // dd($facturaDTO->Detalles);
            
            return view('cpanel.recepciones.editar', compact(
                'recepcion',
                'facturasPendientes',
                'detalles',
                'totalRecepcion',
                'facturaDTO',
                'subtotalRecepcion',
                'totalRecepcionBs',
                'totalItems',
                'totalUnidades'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en editarRecepcion: ' . $e->getMessage());
            return redirect()->route('cpanel.recepciones.proveedor')
                ->with('error', 'Error al cargar la edición de la recepción: ' . $e->getMessage());
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
    
    private function buscarDetallesRecepcion($recepcionId)
    {
        try {
            // $detalles = DB::connection('sqlsrv')
            //     ->table('RecepcionesDetalles as rd')
            //     ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
            //     ->where('rd.RecepcionId', $recepcionId)
            //     ->select([
            //         'rd.RecepcionesDetallesId',
            //         'rd.RecepcionId',
            //         'rd.ProductoId',
            //         'rd.CantidadRecibida',
            //         'rd.CantidadPedida',
            //         'rd.CostoBs',
            //         'rd.CostoDivisa',
            //         'rd.CantidadPieSolo',
            //         'rd.CantidadPieInvertido',
            //         'rd.CantidadCajaVacia',
            //         'rd.CantidadPiezaDanada',
            //         'p.Codigo',
            //         'p.Descripcion as producto_nombre',
            //         'p.Referencia'
            //     ])
            //     ->get();

            $detalles = DB::connection('sqlsrv')
                        ->table('RecepcionesDetalles as rd')
                        ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
                        ->leftJoin('RecepcionesFacturas as rf', 'rd.RecepcionId', '=', 'rf.RecepcionId')
                        ->leftJoin('FacturaDetalles as fd', function($join) {
                            $join->on('rf.FacturaId', '=', 'fd.FacturaId')
                                ->on('rd.ProductoId', '=', 'fd.ProductoId');
                        })
                        ->where('rd.RecepcionId', $recepcionId)
                        ->select([
                            'rd.RecepcionesDetallesId',
                            'rd.RecepcionId',
                            'rd.ProductoId',
                            'rd.CantidadRecibida',
                            'rd.CantidadPedida',
                            'rd.CostoBs',
                            'rd.CostoDivisa',
                            'rd.CantidadPieSolo',
                            'rd.CantidadPieInvertido',
                            'rd.CantidadCajaVacia',
                            'rd.CantidadPiezaDanada',
                            'p.Codigo',
                            'p.Descripcion as producto_nombre',
                            'p.Referencia',
                            'rf.FacturaId',
                            'fd.CostoDivisa as factura_costo_divisa',
                            'fd.UxE as factura_uxe'
                        ])
                        ->get();
            
            // Calcular total de la recepción para cada producto
            foreach ($detalles as $detalle) {
                // Total en divisas = CantidadRecibida × CostoDivisa
                $detalle->TotalDivisa = ($detalle->CantidadRecibida ?? 0) * ($detalle->CostoDivisa ?? 0);
                // Total en bolívares = CantidadRecibida × CostoBs
                $detalle->TotalBs = ($detalle->CantidadRecibida ?? 0) * ($detalle->CostoBs ?? 0);
            }
            
            return $detalles;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarDetallesRecepcion: ' . $e->getMessage());
            return collect();
        }
    }

    private function borrarDetallesRecepcionActual($recepcionId, $facturaId)
    {
        try {
            \Log::info('=== borrarDetallesRecepcionActual INICIO ===');
            \Log::info('RecepcionId: ' . $recepcionId . ', FacturaId: ' . ($facturaId ?? 'NULL'));
            
            // 1. Obtener detalles actuales de la recepción
            $detallesActuales = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles')
                ->where('RecepcionId', $recepcionId)
                ->get();
            
            \Log::info('Detalles actuales encontrados: ' . $detallesActuales->count());
            
            // 2. Devolver cantidades a la factura (si existe)
            if ($facturaId) {
                \Log::info('Devolviendo cantidades a la factura...');
                foreach ($detallesActuales as $detalle) {
                    // Buscar el detalle de la factura
                    $detalleFactura = DB::connection('sqlsrv')
                        ->table('FacturaDetalles')
                        ->where('FacturaId', $facturaId)
                        ->where('ProductoId', $detalle->ProductoId)
                        ->first();
                    
                    if ($detalleFactura) {
                        $nuevaCantidad = ($detalleFactura->CantidadDisponible ?? 0) + ($detalle->CantidadRecibida ?? 0);
                        \Log::info('Actualizando FacturaDetalle ID: ' . $detalleFactura->ID . 
                                ', Nueva CantidadDisponible: ' . $nuevaCantidad);
                        
                        DB::connection('sqlsrv')
                            ->table('FacturaDetalles')
                            ->where('Id', $detalleFactura->ID)
                            ->update([
                                'CantidadDisponible' => $nuevaCantidad
                            ]);
                    }
                }
            }
            
            // 3. Eliminar detalles de la recepción
            \Log::info('Eliminando detalles de RecepcionesDetalles...');
            $eliminados = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles')
                ->where('RecepcionId', $recepcionId)
                ->delete();
            \Log::info('Detalles eliminados: ' . $eliminados);
            
            // 4. Eliminar relación con factura
            if ($facturaId) {
                \Log::info('Eliminando relación en RecepcionesFacturas...');
                $eliminadosRelacion = DB::connection('sqlsrv')
                    ->table('RecepcionesFacturas')
                    ->where('RecepcionId', $recepcionId)
                    ->where('FacturaId', $facturaId)
                    ->delete();
                \Log::info('Relaciones eliminadas: ' . $eliminadosRelacion);
            }
            
            \Log::info('=== borrarDetallesRecepcionActual FIN ===');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error en borrarDetallesRecepcionActual: ' . $e->getMessage());
            throw $e;
        }
    }

    private function crearRecepcionDesdeFactura($recepcionId, $facturaId, $sucursalDestinoId)
    {
        try {
            \Log::info('=== crearRecepcionDesdeFactura INICIO ===');
            \Log::info('RecepcionId: ' . $recepcionId . ', FacturaId: ' . $facturaId . ', SucursalDestinoId: ' . $sucursalDestinoId);
            
            // 1. Actualizar estatus de la recepción a En Proceso (1)
            \Log::info('Actualizando estatus de la recepción a En Proceso (1)...');
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->update(['Estatus' => 1]);
            \Log::info('Estatus de recepción actualizado');
            
            // 2. Obtener detalles de la factura con sus productos
            \Log::info('Obteniendo detalles de la factura...');
            $detallesFactura = DB::connection('sqlsrv')
                ->table('FacturaDetalles as fd')
                ->leftJoin('Productos as p', 'fd.ProductoId', '=', 'p.ID')
                ->where('fd.FacturaId', $facturaId)
                ->select([
                    'fd.*',
                    'p.Codigo',
                    'p.Descripcion'
                ])
                ->get();
            
            \Log::info('Detalles de factura encontrados: ' . $detallesFactura->count());
            
            // 3. Obtener el porcentaje de gastos de la factura
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $facturaId)
                ->first();
            
            $porcentajeGastos = $factura->PorcentajeGastos ?? 0;
            \Log::info('Porcentaje de gastos de la factura: ' . $porcentajeGastos . '%');
            
            // 4. Insertar detalles en RecepcionesDetalles
            $insertados = 0;
            foreach ($detallesFactura as $detalle) {
                // ✅ Solo insertar si hay cantidad disponible (como en .NET)
                if (($detalle->CantidadDisponible ?? 0) > 0) {
                    // Calcular costo con porcentaje de gastos
                    $costoDivisa = $detalle->CostoDivisa * (1 + ($porcentajeGastos / 100));
                    
                    \Log::info('Insertando detalle - ProductoId: ' . $detalle->ProductoId . 
                            ', CantidadPedida: ' . $detalle->CantidadDisponible .
                            ', CostoDivisa: ' . $costoDivisa);
                    
                    DB::connection('sqlsrv')
                        ->table('RecepcionesDetalles')
                        ->insert([
                            'RecepcionId' => $recepcionId,
                            'ProductoId' => $detalle->ProductoId,
                            'CantidadPedida' => $detalle->CantidadDisponible,
                            'CantidadRecibida' => 0,
                            'CostoDivisa' => $costoDivisa,
                            'CostoBs' => $detalle->CostoBs ?? 0,
                            'CantidadPieSolo' => 0,
                            'CantidadPieInvertido' => 0,
                            'CantidadCajaVacia' => 0,
                            'CantidadPiezaDanada' => 0
                        ]);
                    $insertados++;
                } else {
                    \Log::info('Saltando detalle - ProductoId: ' . $detalle->ProductoId . 
                            ', CantidadDisponible: 0 (ya recibido completamente)');
                }
            }
            \Log::info('Detalles insertados en RecepcionesDetalles: ' . $insertados);
            
            // 5. Cambiar estatus de la factura a "Recibiendo" (2)
            \Log::info('Cambiando estatus de la factura a Recibiendo (2)...');
            DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $facturaId)
                ->update(['Estatus' => 2]);
            \Log::info('Estatus de factura actualizado');
            
            \Log::info('=== crearRecepcionDesdeFactura FIN ===');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error en crearRecepcionDesdeFactura: ' . $e->getMessage());
            throw $e;
        }
    }

    private function asociarRecepcionFactura($recepcionId, $facturaId)
    {
        try {
            \Log::info('=== asociarRecepcionFactura INICIO ===');
            \Log::info('RecepcionId: ' . $recepcionId . ', FacturaId: ' . $facturaId);
            
            // Verificar si ya existe la relación
            $existe = DB::connection('sqlsrv')
                ->table('RecepcionesFacturas')
                ->where('RecepcionId', $recepcionId)
                ->where('FacturaId', $facturaId)
                ->exists();
            
            \Log::info('Relación existe: ' . ($existe ? 'Sí' : 'No'));
            
            if (!$existe) {
                \Log::info('Insertando nueva relación en RecepcionesFacturas...');
                DB::connection('sqlsrv')
                    ->table('RecepcionesFacturas')
                    ->insert([
                        'RecepcionId' => $recepcionId,
                        'FacturaId' => $facturaId
                    ]);
                \Log::info('Relación insertada');
            }
            
            // ✅ Actualizar EsConFactura en la tabla Recepciones (columna que SÍ existe)
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->update(['EsConFactura' => 1]);
            
            \Log::info('Recepción actualizada con EsConFactura = 1');
            \Log::info('=== asociarRecepcionFactura FIN ===');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error en asociarRecepcionFactura: ' . $e->getMessage());
            throw $e;
        }
    }

    public function recuperarFactura(Request $request, $recepcionId)
    {
        try {
            \Log::info('========== RECUPERAR FACTURA INICIO ==========');
            \Log::info('RecepcionId: ' . $recepcionId);
            \Log::info('Request data:', $request->all());
            
            // ✅ VALIDAR: Verificar si la recepción ya tiene una factura asociada
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->first();
            
            if ($recepcion->EsConFactura == 1) {
                \Log::warning('La recepción ya tiene una factura asociada. No se puede cambiar.');
                return response()->json([
                    'success' => false,
                    'message' => 'Esta recepción ya tiene una factura asociada. No se puede cambiar.'
                ]);
            }
            
            $facturaId = $request->factura_id;
            
            if (!$facturaId) {
                \Log::warning('No se recibió factura_id');
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar una factura'
                ]);
            }
            
            \Log::info('FacturaId seleccionada: ' . $facturaId);
            
            // Buscar la factura
            \Log::info('Buscando datos de la factura...');
            $factura = $this->buscarDatosFactura($facturaId);
            
            if (!$factura) {
                \Log::error('Factura no encontrada con ID: ' . $facturaId);
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ]);
            }
            
            \Log::info('Factura encontrada - ID: ' . $factura->ID . ', Estatus: ' . $factura->Estatus);
            
            // Verificar estatus de la factura
            if ($factura->Estatus == 1 || $factura->Estatus == 3) { // En Proceso o Pagada
                \Log::info('Factura válida (Estatus: ' . $factura->Estatus . '). Procediendo...');
                
                // 1. Borrar detalles actuales de la recepción
                \Log::info('Paso 1: Borrando detalles actuales de la recepción...');
                $this->borrarDetallesRecepcionActual($recepcionId, $facturaId);
                
                // 2. Crear recepción desde la factura
                \Log::info('Paso 2: Creando recepción desde la factura...');
                $sucursalDestinoId = DB::connection('sqlsrv')
                    ->table('Recepciones')
                    ->where('RecepcionId', $recepcionId)
                    ->value('SucursalDestinoId');
                
                \Log::info('SucursalDestinoId: ' . ($sucursalDestinoId ?? 'NULL'));
                $this->crearRecepcionDesdeFactura($recepcionId, $facturaId, $sucursalDestinoId);
                
                // 3. Asociar factura a la recepción
                \Log::info('Paso 3: Asociando factura a la recepción...');
                $this->asociarRecepcionFactura($recepcionId, $facturaId);
                
                \Log::info('========== RECUPERAR FACTURA FIN (ÉXITO) ==========');
                return response()->json([
                    'success' => true,
                    'message' => 'Factura asociada correctamente'
                ]);
                
            } elseif ($factura->Estatus == 2) { // Recibiendo
                \Log::warning('Factura ya está en estado Recibiendo (2)');
                return response()->json([
                    'success' => false,
                    'message' => 'La factura ya encuentra en recepción. Por favor seleccione otra factura'
                ]);
            } else {
                \Log::warning('Factura con estatus no válido: ' . $factura->Estatus);
                return response()->json([
                    'success' => false,
                    'message' => 'La factura ya se encuentra recibida o está anulada'
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error en recuperarFactura: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al asociar la factura: ' . $e->getMessage()
            ]);
        }
    }
    
    public function uploadExcel(Request $request, $id)
    {
        try {
            
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:5120'
            ]);


            // Verificar que la recepción existe
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $id)
                ->first();

            if (!$recepcion) {
                return response()->json(['success' => false, 'message' => 'Recepción no encontrada'], 404);
            }

            // Guardar archivo temporal
            $file = $request->file('excel_file');
            $tempPath = storage_path('app/temp/recepciones/');
            
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0777, true);
            }
            
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $tempPath . $fileName;
            
            // Mover el archivo
            $file->move($tempPath, $fileName);
            
            // Verificar que el archivo existe antes de procesarlo
            if (!file_exists($filePath)) {
                throw new \Exception('El archivo no se pudo guardar correctamente');
            }

            // Procesar Excel
            $detalles = $this->leerDetallesDesdeExcel($filePath);
            
            // Eliminar archivo temporal DESPUÉS de procesarlo
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            if (count($detalles) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron productos válidos en el archivo'
                ], 400);
            }

            // Guardar en BD
            DB::connection('sqlsrv')->beginTransaction();
            
            try {
                $productosGuardados = 0;
                $productosNoEncontrados = [];

                foreach ($detalles as $index => $detalle) {

                    // Buscar producto por código
                    $producto = DB::connection('sqlsrv')
                        ->table('Productos')
                        ->where('Codigo', $detalle['codigo'])
                        ->first();

                    if (!$producto) {
                        $productosNoEncontrados[] = $detalle['codigo'];
                        continue;
                    }

                    // Verificar si ya existe el detalle
                    $detalleExistente = DB::connection('sqlsrv')
                        ->table('RecepcionesDetalles')
                        ->where('RecepcionId', $id)
                        ->where('ProductoId', $producto->ID)
                        ->first();

                    if ($detalleExistente) {
                        DB::connection('sqlsrv')
                            ->table('RecepcionesDetalles')
                            ->where('RecepcionesDetallesId', $detalleExistente->RecepcionesDetallesId)
                            ->update([
                                'CantidadRecibida' => $detalle['cantidad_recibida'],
                                'CantidadPedida' => $detalle['cantidad_recibida'],
                                'CostoDivisa' => $detalle['costo_divisa'],
                                'CantidadPieSolo' => $detalle['pie_solo'],
                                'CantidadPieInvertido' => $detalle['pie_invertido'],
                                'CantidadPiezaDanada' => $detalle['danado'],
                                'CantidadCajaVacia' => $detalle['vacio']
                            ]);
                    } else {
                        DB::connection('sqlsrv')
                            ->table('RecepcionesDetalles')
                            ->insert([
                                'RecepcionId' => $id,
                                'ProductoId' => $producto->ID,
                                'CantidadRecibida' => $detalle['cantidad_recibida'],
                                'CantidadPedida' => $detalle['cantidad_recibida'],
                                'CostoDivisa' => $detalle['costo_divisa'],
                                'CostoBs' => 0,  // O el valor correspondiente
                                'CantidadPieSolo' => $detalle['pie_solo'],
                                'CantidadPieInvertido' => $detalle['pie_invertido'],
                                'CantidadPiezaDanada' => $detalle['danado'],
                                'CantidadCajaVacia' => $detalle['vacio']
                            ]);
                    }
                    
                    $productosGuardados++;
                }

                DB::connection('sqlsrv')->commit();

                $mensaje = "{$productosGuardados} productos procesados correctamente.";
                if (count($productosNoEncontrados) > 0) {
                    $mensaje .= " Productos no encontrados: " . implode(', ', $productosNoEncontrados);
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensaje
                ]);

            } catch (\Exception $e) {
                DB::connection('sqlsrv')->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function leerDetallesDesdeExcel($filePath)
    {
        
        $detalles = [];
        $estatus = 0; // 0=Inicio, 1=Cabecera, 2=Detalles
        $filaActual = 0;
        
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        foreach ($rows as $rowIndex => $row) {
            $filaActual = $rowIndex + 1;
            
            switch ($estatus) {
                case 0: // Buscar "Recepcion"
                    foreach ($row as $colIndex => $cell) {
                        if (is_string($cell) && trim(strtolower($cell)) == 'recepcion') {
                            $estatus = 1;
                            break;
                        }
                    }
                    break;
                    
                case 1: // Buscar "código" o "codigo"
                    foreach ($row as $colIndex => $cell) {
                        $valor = trim(strtolower((string)$cell));
                        if ($valor == 'código' || $valor == 'codigo') {
                            $estatus = 2;
                            break;
                        }
                    }
                    break;
                    
                case 2: // Leer datos del producto
                    $codigo = isset($row[0]) ? trim($row[0]) : '';
                    $descripcion = isset($row[2]) ? trim($row[2]) : '';
                    $cantidadRecibida = isset($row[3]) ? floatval($row[3]) : 0;
                    $pieSolo = isset($row[4]) ? intval($row[4]) : 0;
                    $pieInvertido = isset($row[5]) ? intval($row[5]) : 0;
                    $danado = isset($row[6]) ? intval($row[6]) : 0;
                    $vacio = isset($row[7]) ? intval($row[7]) : 0;
                    
                    if (!empty($codigo)) {
                        // ✅ Buscar el producto en la tabla Productos para obtener el CostoDivisa
                        $producto = DB::connection('sqlsrv')
                            ->table('Productos')
                            ->where('Codigo', $codigo)
                            ->first();
                        
                        $costoDivisa = $producto ? $producto->CostoDivisa : 0;
                        
                        $detalles[] = [
                            'codigo' => $codigo,
                            'descripcion' => $descripcion,
                            'costo_divisa' => $costoDivisa,
                            'cantidad_recibida' => $cantidadRecibida,
                            'pie_solo' => $pieSolo,
                            'pie_invertido' => $pieInvertido,
                            'danado' => $danado,
                            'vacio' => $vacio
                        ];
                    } else {
                        Log::info('Fila sin código, omitiendo', ['fila' => $filaActual, 'contenido' => $row]);
                    }
                    break;
            }
        }
        
        return $detalles;
    }

    public function finalizarRecepcion($id)
    {
        $startTime = microtime(true);
        
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Obtener la recepción
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('RecepcionesFacturas as rf', 'r.RecepcionId', '=', 'rf.RecepcionId')
                ->where('r.RecepcionId', $id)
                ->select('r.*', 'rf.FacturaId')
                ->first();
            
            if (!$recepcion) {
                return response()->json(['success' => false, 'message' => 'Recepción no encontrada']);
            }
            
            // 2. Obtener detalles de la recepción ANTES de actualizar
            $detalles = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles')
                ->where('RecepcionId', $id)
                ->get();
            
            // 3. Cambiar estatus de la recepción a Procesada (2)
            
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $id)
                ->update(['Estatus' => 2]);
            
            // 4. Actualizar inventario y asociar productos al proveedor
            
            $productosActualizados = 0;
            $productosAsignados = 0;
            $productosEliminados = 0;
            
            foreach ($detalles as $detalle) {
                if ($detalle->CantidadRecibida > 0) {
                    
                    // 4a. Actualizar inventario
                    $this->actualizarInventarioProducto($detalle, $recepcion->SucursalDestinoId);
                    $productosActualizados++;
                    
                    // 4b. Asociar producto al proveedor
                    $this->asignarProductoProveedor($detalle->ProductoId, $recepcion->ProveedorId);
                    $productosAsignados++;
                    
                } else {
                    
                    DB::connection('sqlsrv')
                        ->table('RecepcionesDetalles')
                        ->where('RecepcionesDetallesId', $detalle->RecepcionesDetallesId)
                        ->delete();
                    
                    $productosEliminados++;
                }
            }
            
            // 5. Actualizar cantidades en la factura (si existe)
            $hayDiferencias = false;
            if ($recepcion->FacturaId && $recepcion->EsConFactura == 1) {
                
                $this->actualizarCantidadesEnFactura($recepcion->FacturaId, $detalles);
                
                // Generar auditoría si hay diferencias
                $hayDiferencias = $this->generarAuditoria($id, $detalles, $recepcion->FacturaId);
                
            } else {
                \Log::info('Paso 5: No hay factura asociada, omitiendo');
            }
            
            DB::connection('sqlsrv')->commit();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($hayDiferencias) {
                return response()->json([
                    'success' => true,
                    'message' => 'La recepción se ha finalizado exitosamente. Se ha generado un registro de auditoría pendiente por revisar',
                    'auditoria' => true
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'La recepción se ha finalizado exitosamente',
                    'auditoria' => false
                ]);
            }
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la recepción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarDetallesRecepcion(Request $request, $id)
    {
        \Log::info('=== actualizarDetallesRecepcion INICIO ===', ['recepcion_id' => $id]);
        
        try {
            $detalles = $request->input('detalles', []);
            
            \Log::info('Detalles recibidos desde frontend', [
                'total' => count($detalles),
                'detalles' => $detalles
            ]);
            
            foreach ($detalles as $detalle) {
                \Log::info('Actualizando detalle', [
                    'id' => $detalle['id'],
                    'cantidad_recibida' => $detalle['cantidad_recibida'],
                    'pie_solo' => $detalle['pie_solo'],
                    'pie_invertido' => $detalle['pie_invertido'],
                    'danado' => $detalle['danado'],
                    'vacio' => $detalle['vacio']
                ]);
                
                DB::connection('sqlsrv')
                    ->table('RecepcionesDetalles')
                    ->where('RecepcionesDetallesId', $detalle['id'])
                    ->update([
                        'CantidadRecibida' => $detalle['cantidad_recibida'],
                        'CantidadPieSolo' => $detalle['pie_solo'],
                        'CantidadPieInvertido' => $detalle['pie_invertido'],
                        'CantidadPiezaDanada' => $detalle['danado'],
                        'CantidadCajaVacia' => $detalle['vacio']
                        // ❌ Elimina 'updated_at' => now()
                    ]);
            }
            
            \Log::info('=== actualizarDetallesRecepcion FIN ===', ['success' => true]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            \Log::error('Error en actualizarDetallesRecepcion', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
            
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Actualizar inventario del producto en la sucursal (SP: uspProductosGuardarPorSucursalEXT)
     */
    private function actualizarInventarioProducto($detalle, $sucursalId)
    {
        // Primero, obtener o crear el producto
        $producto = DB::connection('sqlsrv')
            ->table('Productos')
            ->where('ID', $detalle->ProductoId)
            ->first();
        
        if (!$producto) {
            return;
        }
        
        // Actualizar o crear en ProductoSucursal
        $productoSucursal = DB::connection('sqlsrv')
            ->table('ProductoSucursal')
            ->where('ProductoId', $detalle->ProductoId)
            ->where('SucursalId', $sucursalId)
            ->first();
        
        if ($productoSucursal) {
            // Sumar existencia
            DB::connection('sqlsrv')
                ->table('ProductoSucursal')
                ->where('ProductoId', $detalle->ProductoId)
                ->where('SucursalId', $sucursalId)
                ->update([
                    'Existencia' => $productoSucursal->Existencia + ($detalle->CantidadRecibida ?? 0),
                    'Estatus' => 1
                ]);
        } else {
            // Crear nuevo registro
            DB::connection('sqlsrv')
                ->table('ProductoSucursal')
                ->insert([
                    'SucursalId' => $sucursalId,
                    'ProductoId' => $detalle->ProductoId,
                    'Existencia' => $detalle->CantidadRecibida ?? 0,
                    'PvpBs' => 0,
                    'PvpDivisa' => 0,
                    'Estatus' => 1,
                    'FechaIngreso' => now()
                ]);
        }
    }

    /**
     * Asociar producto al proveedor (tabla ProveedorProducto)
     */
    private function asignarProductoProveedor($productoId, $proveedorId)
    {
        $existe = DB::connection('sqlsrv')
            ->table('ProveedorProducto')
            ->where('ProductoId', $productoId)
            ->where('ProveedorId', $proveedorId)
            ->exists();
        
        if (!$existe) {
            DB::connection('sqlsrv')
                ->table('ProveedorProducto')
                ->insert([
                    'ProductoId' => $productoId,
                    'ProveedorId' => $proveedorId
                ]);
            
            // Marcar producto como asignado a proveedor
            DB::connection('sqlsrv')
                ->table('Productos')
                ->where('ID', $productoId)
                ->update(['EsProveedorAsignado' => 1]);
        }
    }

    private function actualizarCantidadesEnFactura($facturaId, $detalles)
    {
        foreach ($detalles as $detalle) {
            if ($detalle->CantidadRecibida > 0) {
                // Buscar el detalle de la factura
                $facturaDetalle = DB::connection('sqlsrv')
                    ->table('FacturaDetalles')
                    ->where('FacturaId', $facturaId)
                    ->where('ProductoId', $detalle->ProductoId)
                    ->first();
                
                if ($facturaDetalle) {
                    // ✅ Obtener UxE
                    $uxe = $facturaDetalle->UxE ?? 1;
                    
                    // ✅ Convertir a unidades reales
                    $cantidadEmitidaReal = ($facturaDetalle->CantidadEmitida ?? 0) * $uxe;  // 12 × 12 = 144
                    $cantidadRecibidaActual = $facturaDetalle->CantidadRecibida ?? 0;  // 0 (unidades reales)
                    $cantidadIntentada = $detalle->CantidadRecibida;  // 144 (unidades reales)
                    
                    // Calcular máximo permitido (en unidades reales)
                    $maximoPosible = $cantidadEmitidaReal - $cantidadRecibidaActual;
                    
                    if ($maximoPosible < 0) {
                        $maximoPosible = 0;
                    }
                    
                    if ($cantidadIntentada > $maximoPosible) {
                        \Log::warning('⚠️ Producto excede cantidad emitida', [
                            'producto_id' => $detalle->ProductoId,
                            'cantidad_emitida_real' => $cantidadEmitidaReal,
                            'cantidad_recibida_actual' => $cantidadRecibidaActual,
                            'intento_agregar' => $cantidadIntentada,
                            'maximo_posible' => $maximoPosible,
                            'se_ajustara_a' => $maximoPosible
                        ]);
                        
                        $cantidadRealRecibir = $maximoPosible;
                    } else {
                        $cantidadRealRecibir = $cantidadIntentada;
                    }
                    
                    if ($cantidadRealRecibir > 0) {
                        $nuevaCantidadRecibida = $cantidadRecibidaActual + $cantidadRealRecibir;
                        $nuevaCantidadDisponible = $cantidadEmitidaReal - $nuevaCantidadRecibida;
                        
                        if ($nuevaCantidadDisponible < 0) {
                            $nuevaCantidadDisponible = 0;
                        }
                        
                        DB::connection('sqlsrv')
                            ->table('FacturaDetalles')
                            ->where('ID', $facturaDetalle->ID)
                            ->update([
                                'CantidadRecibida' => $nuevaCantidadRecibida,
                                'CantidadDisponible' => $nuevaCantidadDisponible
                            ]);
                        
                        \Log::info('✅ Cantidad actualizada en factura', [
                            'factura_detalle_id' => $facturaDetalle->ID,
                            'producto_id' => $detalle->ProductoId,
                            'recibido_ahora' => $cantidadRealRecibir,
                            'recibido_total' => $nuevaCantidadRecibida,
                            'pendiente' => $nuevaCantidadDisponible
                        ]);
                    } else {
                        \Log::info('⚠️ No se puede recibir más de este producto', [
                            'producto_id' => $detalle->ProductoId,
                            'motivo' => 'cantidad emitida ya alcanzada'
                        ]);
                    }
                }
            }
        }
        
        // Verificar si la factura está completamente recibida (en unidades reales)
        $facturaDetallesPendientes = DB::connection('sqlsrv')
            ->table('FacturaDetalles')
            ->where('FacturaId', $facturaId)
            ->where('CantidadDisponible', '>', 0)
            ->count();
        
        $nuevoEstatus = $facturaDetallesPendientes == 0 ? 4 : 1;
        
        DB::connection('sqlsrv')
            ->table('Facturas')
            ->where('ID', $facturaId)
            ->update(['Estatus' => $nuevoEstatus]);
        
        \Log::info('📊 Estado actualizado de factura', [
            'factura_id' => $facturaId,
            'nuevo_estatus' => $nuevoEstatus,
            'detalles_pendientes' => $facturaDetallesPendientes
        ]);
    }

    /**
     * Generar auditoría si hay diferencias entre lo facturado y lo recibido
     */
    private function generarAuditoria($recepcionId, $detalles, $facturaId)
    {
        $facturaDetalles = DB::connection('sqlsrv')
            ->table('FacturaDetalles')
            ->where('FacturaId', $facturaId)
            ->get()
            ->keyBy('ProductoId');
        
        $hayDiferencias = false;
        $detallesConDiferencia = [];
        
        foreach ($detalles as $detalle) {
            $facturaDetalle = $facturaDetalles->get($detalle->ProductoId);
            
            if ($facturaDetalle) {
                // ✅ Usar CantidadRecibida de la factura (ya está en unidades reales)
                $cantidadEmitidaReal = $facturaDetalle->CantidadRecibida ?? 0;  // 144
                $cantidadRecibida = $detalle->CantidadRecibida ?? 0;  // 144
                
                if ($cantidadEmitidaReal != $cantidadRecibida) {
                    $hayDiferencias = true;
                    $detallesConDiferencia[] = $detalle;
                }
            }
        }
        
        if ($hayDiferencias) {
            return $this->guardarAuditoriaEnBD($recepcionId, $detallesConDiferencia);
        }
        
        return false;
    }

    /**
     * Guardar registro de auditoría cuando hay diferencias
     */
    private function guardarAuditoriaEnBD($recepcionId, $detalles)
    {
        try {
            // 1. Crear registro en Auditorias
            $numeroAuditoria = 'AUD' . now()->format('YmdHi') . '-' . $recepcionId;
            
            $auditoriaId = DB::connection('sqlsrv')
                ->table('Auditorias')
                ->insertGetId([
                    'Estatus' => 0, // Nueva
                    'Numero' => $numeroAuditoria,
                    'RecepcionId' => $recepcionId,
                    'Observacion' => 'Diferencias en Recepción',
                    'Fecha' => now()
                ]);
            
            // 2. Crear registros en AuditoriaDetalles para cada producto con diferencia
            foreach ($detalles as $detalle) {
                DB::connection('sqlsrv')
                    ->table('AuditoriaDetalles')
                    ->insert([
                        'AuditoriaId' => $auditoriaId,
                        'RecepcionDetalleId' => $detalle->RecepcionesDetallesId
                    ]);
            }
            
            // 3. Cambiar estatus de la recepción a "En Auditoria" (4)
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->update(['Estatus' => 4]); // EnAuditoria
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error al guardar auditoría: ' . $e->getMessage());
            return false;
        }
    }
    
    public function asociarFactura(Request $request, $id)
    {
        try {
            $facturaId = $request->input('factura_id');
            
            \Log::info('asociarFactura llamado', [
                'recepcion_id' => $id,
                'factura_id' => $facturaId
            ]);
            
            // Verificar que la recepción existe
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $id)
                ->first();
            
            if (!$recepcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recepción no encontrada'
                ]);
            }
            
            // Verificar que la factura existe
            $factura = DB::connection('sqlsrv')
                ->table('Facturas')
                ->where('ID', $facturaId)
                ->first();
            
            if (!$factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ]);
            }
            
            // Llamar a la función privada para asociar
            $this->asociarRecepcionFactura($id, $facturaId);
            
            return response()->json([
                'success' => true,
                'message' => 'Factura asociada correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en asociarFactura: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al asociar la factura: ' . $e->getMessage()
            ]);
        }
    }

    public function listado_recepciones_auditoria(Request $request)
    {
        try {
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Auditar Recepciones'
            ]);
            
            // ✅ Calcular la fecha límite (6 meses atrás)
            $fechaLimite = now()->subMonths(6);
            
            // ✅ Obtener las auditorías activas (Estatus = 0 y 1) con Fecha > 6 meses
            $auditorias = DB::connection('sqlsrv')
                ->table('Auditorias as a')
                ->leftJoin('Recepciones as r', 'a.RecepcionId', '=', 'r.RecepcionId')
                ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
                ->leftJoin('Sucursales as sd', 'r.SucursalDestinoId', '=', 'sd.ID')
                ->whereIn('a.Estatus', [0, 1])
                ->where('a.Fecha', '>=', $fechaLimite)  // ✅ Solo auditorías de los últimos 6 meses
                ->select([
                    'a.AuditoriaId',
                    'a.RecepcionId',
                    'a.Fecha',
                    'a.Numero',
                    'a.Estatus',
                    'a.Observacion',
                    'r.Numero as recepcion_numero',
                    'r.FechaCreacion as recepcion_fecha',
                    'r.FechaRecepcion as recepcion_fecha_real',
                    'p.Nombre as proveedor_nombre',
                    'sd.Nombre as sucursal_destino'
                ])
                ->orderBy('a.Fecha', 'desc')
                ->get();
            
            // ✅ Obtener los detalles de cada auditoría
            foreach ($auditorias as $auditoria) {
                $auditoria->detalles = DB::connection('sqlsrv')
                    ->table('AuditoriaDetalles as ad')
                    ->leftJoin('RecepcionesDetalles as rd', 'ad.RecepcionDetalleId', '=', 'rd.RecepcionesDetallesId')
                    ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
                    ->where('ad.AuditoriaId', $auditoria->AuditoriaId)
                    ->select([
                        'ad.AuditoriaDetalleId',
                        'ad.RecepcionDetalleId',
                        'rd.CantidadRecibida',
                        'rd.CantidadPedida',
                        'rd.CostoDivisa',
                        'p.Codigo',
                        'p.Descripcion as producto_nombre'
                    ])
                    ->get();
            }
            
            // ✅ Mapear el estatus según EnumAuditoria
            $estatusMap = [
                0 => ['texto' => 'Nueva', 'clase' => 'badge bg-warning'],
                1 => ['texto' => 'En Proceso', 'clase' => 'badge bg-info'],
                2 => ['texto' => 'Finalizada', 'clase' => 'badge bg-success'],
                3 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger']
            ];
            
            // ✅ Retornar la vista
            return view('cpanel.recepciones.listado_auditoria', compact('auditorias', 'estatusMap'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_recepciones_auditoria: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de auditorias de recepciones: ' . $e->getMessage());
        }
    }

    public function procesarAuditoria($id)
{
    try {
        \Log::info('=== INICIO procesarAuditoria ===', ['auditoria_id' => $id]);
        
        // 1. Obtener la auditoría
        $auditoria = DB::connection('sqlsrv')
            ->table('Auditorias as a')
            ->leftJoin('Recepciones as r', 'a.RecepcionId', '=', 'r.RecepcionId')
            ->leftJoin('RecepcionesFacturas as rf', 'r.RecepcionId', '=', 'rf.RecepcionId')
            ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
            ->leftJoin('Sucursales as sd', 'r.SucursalDestinoId', '=', 'sd.ID')
            ->where('a.AuditoriaId', $id)
            ->select([
                'a.*',
                'r.Numero as recepcion_numero',
                'r.FechaCreacion as recepcion_fecha',
                'p.Nombre as proveedor_nombre',
                'sd.Nombre as sucursal_destino',
                'rf.FacturaId'
            ])
            ->first();
        
        if (!$auditoria) {
            return redirect()->back()->with('error', 'Auditoría no encontrada');
        }
        
        // 2. Obtener los detalles de la auditoría (solo productos con datos)
        $detalles = DB::connection('sqlsrv')
                    ->table('AuditoriaDetalles as ad')
                    ->leftJoin('RecepcionesDetalles as rd', 'ad.RecepcionDetalleId', '=', 'rd.RecepcionesDetallesId')
                    ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
                    ->leftJoin('FacturaDetalles as fd', function($join) use ($auditoria) {
                        $join->on('fd.ProductoId', '=', 'rd.ProductoId')
                            ->where('fd.FacturaId', '=', $auditoria->FacturaId);
                    })
                    ->where('ad.AuditoriaId', $id)
                    ->whereNotNull('rd.CantidadRecibida')
                    ->select([
                        'ad.*',
                        'rd.CantidadRecibida',
                        'rd.CantidadPedida as recepcion_cantidad_pedida',
                        'rd.CostoDivisa',
                        'rd.CantidadPieSolo',
                        'rd.CantidadPieInvertido',
                        'rd.CantidadPiezaDanada',
                        'rd.CantidadCajaVacia',
                        'p.Codigo',
                        'p.Descripcion as producto_nombre',
                        'fd.CantidadEmitida as factura_cantidad_empaques',
                        'fd.UxE as factura_uxe'
                    ])
                    ->get();
        
        // 3. Calcular la diferencia y filtrar solo los que tienen diferencia != 0
        $detallesConDiferencia = [];
        foreach ($detalles as $detalle) {
            $uxe = $detalle->factura_uxe ?? 1;
            $cantidadFacturaReal = ($detalle->factura_cantidad_empaques ?? 0) * $uxe;
            $cantidadRecibida = $detalle->CantidadRecibida ?? 0;
            
            $detalle->cantidad_factura_real = $cantidadFacturaReal;
            $detalle->diferencia_cantidad = $cantidadRecibida - $cantidadFacturaReal;
            
            // ✅ Obtener diferencias en Pie Solo, Pie Inv., Dañado, Vacío
            // (estos valores vienen de RecepcionesDetalles)
            $detalle->diferencia_pie_solo = $detalle->CantidadPieSolo ?? 0;
            $detalle->diferencia_pie_invertido = $detalle->CantidadPieInvertido ?? 0;
            $detalle->diferencia_danado = $detalle->CantidadPiezaDanada ?? 0;
            $detalle->diferencia_vacio = $detalle->CantidadCajaVacia ?? 0;
            
            // ✅ Calcular diferencia total (suma de todas las diferencias)
            $detalle->diferencia_total = $detalle->diferencia_cantidad 
                                    + $detalle->diferencia_pie_solo 
                                    + $detalle->diferencia_pie_invertido 
                                    + $detalle->diferencia_danado 
                                    + $detalle->diferencia_vacio;
            
            // ✅ Mostrar si hay alguna diferencia
            if ($detalle->diferencia_total != 0) {
                $detallesConDiferencia[] = $detalle;
            }
        }
        
        $estatusMap = [
            0 => ['texto' => 'Nueva', 'clase' => 'badge bg-warning'],
            1 => ['texto' => 'En Proceso', 'clase' => 'badge bg-info'],
            2 => ['texto' => 'Finalizada', 'clase' => 'badge bg-success'],
            3 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger']
        ];
        
        return view('cpanel.recepciones.procesar', compact('auditoria', 'detallesConDiferencia', 'estatusMap'));
        
    } catch (\Exception $e) {
        \Log::error('Error en procesarAuditoria: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error al cargar la auditoría: ' . $e->getMessage());
    }
}

    // ============================================
    // APROBAR TODA LA AUDITORÍA
    // ============================================
    public function aprobarAuditoria($id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            $auditoria = DB::connection('sqlsrv')
                ->table('Auditorias')
                ->where('AuditoriaId', $id)
                ->first();
            
            if (!$auditoria) {
                return response()->json(['success' => false, 'message' => 'Auditoría no encontrada']);
            }
            
            // 1. Cambiar estatus de auditoría a Finalizada (2)
            DB::connection('sqlsrv')
                ->table('Auditorias')
                ->where('AuditoriaId', $id)
                ->update(['Estatus' => 2]);
            
            // 2. Cambiar estatus de recepción a Auditada (5)
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $auditoria->RecepcionId)
                ->update(['Estatus' => 5]);
            
            // 3. Actualizar todos los detalles con Accion = 1 (Aprobado)
            DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaId', $id)
                ->update([
                    'Accion' => 1,  // ✅ 1 = Aprobado
                    'Detalle' => 'Auditoría aprobada en general'
                ]);
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Auditoría aprobada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al aprobar auditoría: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================
    // RECHAZAR TODA LA AUDITORÍA
    // ============================================
    public function rechazarAuditoria($id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Obtener la auditoría
            $auditoria = DB::connection('sqlsrv')
                ->table('Auditorias')
                ->where('AuditoriaId', $id)
                ->first();
            
            if (!$auditoria) {
                return response()->json(['success' => false, 'message' => 'Auditoría no encontrada']);
            }
            
            // 2. Obtener la recepción
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $auditoria->RecepcionId)
                ->first();
            
            // 3. Obtener los detalles de auditoría
            $detallesAuditoria = DB::connection('sqlsrv')
                ->table('AuditoriaDetalles as ad')
                ->leftJoin('RecepcionesDetalles as rd', 'ad.RecepcionDetalleId', '=', 'rd.RecepcionesDetallesId')
                ->leftJoin('FacturaDetalles as fd', function($join) use ($auditoria) {
                    $join->on('fd.ProductoId', '=', 'rd.ProductoId')
                        ->where('fd.FacturaId', '=', $auditoria->FacturaId);
                })
                ->where('ad.AuditoriaId', $id)
                ->select([
                    'ad.*',
                    'rd.ProductoId',
                    'rd.CantidadRecibida as recepcion_cantidad_recibida',
                    'rd.CantidadPedida as recepcion_cantidad_pedida',
                    'rd.RecepcionesDetallesId',
                    'fd.CantidadEmitida as factura_cantidad_empaques',
                    'fd.UxE as factura_uxe'
                ])
                ->get();
            
            // 4. Corregir cantidades en RecepcionesDetalles
            foreach ($detallesAuditoria as $detalle) {
                $uxe = $detalle->factura_uxe ?? 1;
                $cantidadFacturaReal = ($detalle->factura_cantidad_empaques ?? 0) * $uxe;
                
                // Corregir la cantidad en RecepcionesDetalles
                DB::connection('sqlsrv')
                    ->table('RecepcionesDetalles')
                    ->where('RecepcionesDetallesId', $detalle->RecepcionesDetallesId)
                    ->update([
                        'CantidadRecibida' => $cantidadFacturaReal,
                        'CantidadPedida' => $cantidadFacturaReal
                    ]);
                
                // Actualizar Acción = 2 (Rechazado) con el detalle en texto
                DB::connection('sqlsrv')
                    ->table('AuditoriaDetalles')
                    ->where('AuditoriaDetalleId', $detalle->AuditoriaDetalleId)
                    ->update([
                        'Accion' => 2,  // ✅ 2 = Rechazado
                        'Detalle' => 'Rechazado - Corregido a ' . $cantidadFacturaReal
                    ]);
            }
            
            // 5. Cambiar estatus de auditoría a Anulada (3)
            DB::connection('sqlsrv')
                ->table('Auditorias')
                ->where('AuditoriaId', $id)
                ->update(['Estatus' => 3]);
            
            // 6. Cambiar estatus de recepción a Procesada (2)
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $auditoria->RecepcionId)
                ->update(['Estatus' => 2]);
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Auditoría rechazada. Las cantidades han sido corregidas a los valores de la factura.'
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al rechazar auditoría: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================
    // APROBAR PRODUCTO INDIVIDUAL
    // ============================================
    public function aprobarProducto($id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Obtener el detalle de auditoría
            $detalle = DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaDetalleId', $id)
                ->first();
            
            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado']);
            }
            
            // 2. Actualizar Acción = 1 (Aprobado)
            DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaDetalleId', $id)
                ->update([
                    'Accion' => 1,  // ✅ 1 = Aprobado
                    'Detalle' => 'Producto aprobado'
                ]);
            
            // 3. Verificar si todos los detalles tienen acción
            $pendientes = DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaId', $detalle->AuditoriaId)
                ->whereNull('Accion')
                ->count();
            
            // 4. Si no hay pendientes, actualizar estatus de auditoría y recepción
            if ($pendientes == 0) {
                // Cambiar estatus de auditoría a Finalizada (2)
                DB::connection('sqlsrv')
                    ->table('Auditorias')
                    ->where('AuditoriaId', $detalle->AuditoriaId)
                    ->update(['Estatus' => 2]);
                
                // Cambiar estatus de recepción a Auditada (5)
                $auditoria = DB::connection('sqlsrv')
                    ->table('Auditorias')
                    ->where('AuditoriaId', $detalle->AuditoriaId)
                    ->first();
                
                DB::connection('sqlsrv')
                    ->table('Recepciones')
                    ->where('RecepcionId', $auditoria->RecepcionId)
                    ->update(['Estatus' => 5]);
            }
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Producto aprobado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al aprobar producto: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================
    // RECHAZAR PRODUCTO INDIVIDUAL
    // ============================================
    public function rechazarProducto($id)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Obtener el detalle de auditoría
            $detalle = DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaDetalleId', $id)
                ->first();
            
            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado']);
            }
            
            // 2. Obtener la auditoría
            $auditoria = DB::connection('sqlsrv')
                ->table('Auditorias')
                ->where('AuditoriaId', $detalle->AuditoriaId)
                ->first();
            
            if (!$auditoria) {
                return response()->json(['success' => false, 'message' => 'Auditoría no encontrada']);
            }
            
            // 3. Obtener el FacturaId desde RecepcionesFacturas
            $recepcionFactura = DB::connection('sqlsrv')
                ->table('RecepcionesFacturas')
                ->where('RecepcionId', $auditoria->RecepcionId)
                ->first();
            
            if (!$recepcionFactura) {
                return response()->json(['success' => false, 'message' => 'Factura no encontrada para esta recepción']);
            }
            
            $facturaId = $recepcionFactura->FacturaId;
            
            // 4. Obtener información del producto con el FacturaId
            $infoProducto = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles as rd')
                ->leftJoin('FacturaDetalles as fd', function($join) use ($facturaId) {
                    $join->on('fd.ProductoId', '=', 'rd.ProductoId')
                        ->where('fd.FacturaId', '=', $facturaId);
                })
                ->where('rd.RecepcionesDetallesId', $detalle->RecepcionDetalleId)
                ->select([
                    'rd.RecepcionesDetallesId',
                    'rd.CantidadRecibida',
                    'rd.CantidadPedida',
                    'fd.CantidadEmitida as factura_cantidad_empaques',
                    'fd.UxE as factura_uxe'
                ])
                ->first();
            
            if ($infoProducto) {
                $uxe = $infoProducto->factura_uxe ?? 1;
                $cantidadFacturaReal = ($infoProducto->factura_cantidad_empaques ?? 0) * $uxe;
                
                // Corregir la cantidad en RecepcionesDetalles
                DB::connection('sqlsrv')
                    ->table('RecepcionesDetalles')
                    ->where('RecepcionesDetallesId', $infoProducto->RecepcionesDetallesId)
                    ->update([
                        'CantidadRecibida' => $cantidadFacturaReal,
                        'CantidadPedida' => $cantidadFacturaReal
                    ]);
            }
            
            // 5. Actualizar Acción = 2 (Rechazado)
            DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaDetalleId', $id)
                ->update([
                    'Accion' => 2,
                    'Detalle' => 'Cantidad corregida al valor de la factura'
                ]);
            
            // 6. Verificar si todos los detalles tienen acción
            $pendientes = DB::connection('sqlsrv')
                ->table('AuditoriaDetalles')
                ->where('AuditoriaId', $detalle->AuditoriaId)
                ->whereNull('Accion')
                ->count();
            
            // 7. Si no hay pendientes, actualizar estatus
            if ($pendientes == 0) {
                DB::connection('sqlsrv')
                    ->table('Auditorias')
                    ->where('AuditoriaId', $detalle->AuditoriaId)
                    ->update(['Estatus' => 2]);
                
                DB::connection('sqlsrv')
                    ->table('Recepciones')
                    ->where('RecepcionId', $auditoria->RecepcionId)
                    ->update(['Estatus' => 5]);
            }
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Producto rechazado y cantidad corregida exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al rechazar producto: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listado_recepciones_finalizadas(Request $request)
    {
        try {
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Recepciones Finalizadas'
            ]);
            
            $fechaInicio = $request->input('fecha_inicio') ?? now()->startOfMonth()->format('Y-m-d');
            $fechaFin = $request->input('fecha_fin') ?? now()->format('Y-m-d');
            
            $estatusPermitidos = [2, 6, 7, 8];
            
            $recepciones = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
                ->leftJoin('Sucursales as sd', 'r.SucursalDestinoId', '=', 'sd.ID')
                ->leftJoin('Sucursales as so', 'r.SucursalOrigenId', '=', 'so.ID')
                ->leftJoin('RecepcionesTransferencias as rt', 'r.RecepcionId', '=', 'rt.RecepcionId')  // ✅ Transferencias
                ->leftJoin('Transferencias as t', 'rt.TransferenciaId', '=', 't.TransferenciaId')      // ✅ Transferencias
                ->whereIn('r.Estatus', $estatusPermitidos)
                ->whereIn('r.Tipo', [0, 1, 2])
                ->whereBetween('r.FechaCreacion', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->select([
                    'r.RecepcionId',
                    'r.Numero',
                    'r.FechaCreacion',
                    'r.Estatus',
                    'r.Tipo',
                    'r.SucursalDestinoId',
                    'r.SucursalOrigenId',
                    'r.ProveedorId',
                    'sd.Nombre as sucursal_destino',
                    'so.Nombre as sucursal_origen',
                    'p.Nombre as proveedor_nombre',
                    'p.Rif_Cedula as proveedor_rif',
                    't.Numero as transferencia_numero'  // ✅ Número de transferencia
                ])
                ->orderBy('r.FechaCreacion', 'desc')
                ->get();
            
            // ✅ Obtener facturas y transferencias por separado
            foreach ($recepciones as $recepcion) {
                // Para tipo Proveedor (0): buscar Factura
                if ($recepcion->Tipo == 0) {
                    $factura = DB::connection('sqlsrv')
                        ->table('RecepcionesFacturas as rf')
                        ->leftJoin('Facturas as f', 'rf.FacturaId', '=', 'f.ID')
                        ->where('rf.RecepcionId', $recepcion->RecepcionId)
                        ->select('f.ID as documento_id', 'f.Numero as documento_numero', 'f.MontoDivisa as documento_monto')
                        ->first();
                    
                    $recepcion->documento_numero = $factura->documento_numero ?? 'N/A';
                    $recepcion->documento_monto = $factura->documento_monto ?? 0;
                } 
                // Para tipo Distribución (1) o Transferencia (2): buscar Transferencia
                else {
                    $transferencia = DB::connection('sqlsrv')
                        ->table('RecepcionesTransferencias as rt')
                        ->leftJoin('Transferencias as t', 'rt.TransferenciaId', '=', 't.TransferenciaId')
                        ->where('rt.RecepcionId', $recepcion->RecepcionId)
                        ->select('t.Numero as documento_numero')
                        ->first();
                    
                    $recepcion->documento_numero = $transferencia->documento_numero ?? 'N/A';
                    $recepcion->documento_monto = 0;  // Las transferencias no tienen monto
                }
            }
            
            $estatusMap = [
                0 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger'],
                1 => ['texto' => 'En Proceso', 'clase' => 'badge bg-warning'],
                2 => ['texto' => 'Procesada', 'clase' => 'badge bg-info'],
                4 => ['texto' => 'En Auditoría', 'clase' => 'badge bg-secondary'],
                5 => ['texto' => 'Auditada', 'clase' => 'badge bg-success'],
                6 => ['texto' => 'Finalizada', 'clase' => 'badge bg-primary'],
                7 => ['texto' => 'Pagada', 'clase' => 'badge bg-success'],
                8 => ['texto' => 'Finalizada-Pagada', 'clase' => 'badge bg-dark']
            ];
            
            return view('cpanel.recepciones.listado_finalizadas', compact('recepciones', 'estatusMap', 'fechaInicio', 'fechaFin'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_recepciones_finalizadas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar recepciones finalizadas: ' . $e->getMessage());
        }
    }

    public function exportarRecepcionExcel($id)
    {
        try {
            // 1. Obtener la recepción
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones as r')
                ->leftJoin('Proveedores as p', 'r.ProveedorId', '=', 'p.ProveedorId')
                ->leftJoin('Sucursales as sd', 'r.SucursalDestinoId', '=', 'sd.ID')
                ->where('r.RecepcionId', $id)
                ->select([
                    'r.*',
                    'p.Nombre as proveedor_nombre',
                    'sd.Nombre as sucursal_destino'
                ])
                ->first();
            
            if (!$recepcion) {
                return redirect()->back()->with('error', 'Recepción no encontrada');
            }
            
            // 2. Obtener detalles
            $detalles = DB::connection('sqlsrv')
                ->table('RecepcionesDetalles as rd')
                ->leftJoin('Productos as p', 'rd.ProductoId', '=', 'p.ID')
                ->where('rd.RecepcionId', $id)
                ->where('rd.CantidadRecibida', '>', 0)
                ->select([
                    'rd.CantidadRecibida',
                    'rd.CostoDivisa',
                    'p.Codigo',
                    'p.Referencia',
                    'p.Descripcion'
                ])
                ->get();
            
            // 3. Crear Excel desde cero
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Título
            $sheet->setCellValue('A1', 'RECEPCIÓN DE MERCANCÍA');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Información de la recepción
            $sheet->setCellValue('A3', 'N° Recepción:');
            $sheet->setCellValue('B3', $recepcion->Numero);
            $sheet->setCellValue('A4', 'Fecha:');
            $sheet->setCellValue('B4', date('d/m/Y H:i', strtotime($recepcion->FechaCreacion)));
            $sheet->setCellValue('A5', 'Proveedor:');
            $sheet->setCellValue('B5', $recepcion->proveedor_nombre ?? 'N/A');
            $sheet->setCellValue('A6', 'Sucursal Destino:');
            $sheet->setCellValue('B6', $recepcion->sucursal_destino ?? 'N/A');
            
            // Encabezados de productos (fila 8)
            $row = 8;
            $headers = ['Código', 'Referencia', 'Descripción', 'Cantidad', 'Costo Unitario'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->getStyle($col . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('4472C4');
                $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($col . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }
            
            // Datos de productos
            $row = 9;
            foreach ($detalles as $detalle) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $detalle->Referencia ?? '');
                $sheet->setCellValue('C' . $row, $detalle->Descripcion ?? '');
                $sheet->setCellValue('D' . $row, $detalle->CantidadRecibida ?? 0);
                $sheet->setCellValue('E' . $row, $detalle->CostoDivisa ?? 0);
                
                // Bordes
                foreach (range('A', 'E') as $col) {
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                $row++;
            }
            
            // Ajustar anchos de columna
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // 4. Generar archivo
            $writer = new Xls($spreadsheet);
            $filename = 'Recepcion_' . $recepcion->Numero . '.xls';
            
            // Crear directorio temporal
            $tempPath = storage_path('app/temp/exports/');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0777, true);
            }
            
            $tempFile = $tempPath . $filename;
            $writer->save($tempFile);
            
            // Descargar y eliminar
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.ms-excel'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Error al exportar recepción: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar la recepción: ' . $e->getMessage());
        }
    }

    public function listado_recepciones_sucursal(Request $request)
    {
        try {
            // Configurar menú activo
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Recibir de Sucursal' 
            ]);
            
            // Obtener fecha de inicio y fin del mes actual
            $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            $fechaFin = now()->endOfMonth()->format('Y-m-d');

            // Obtener la sucursal del usuario autenticado
            $sucursalId = auth()->user()->SucursalId ?? null;
            
            if (!$sucursalId) {
                return redirect()->back()->with('error', 'No se ha asignado una sucursal al usuario');
            }
            
            // Buscar transferencias con estatus: Registrada(3), Recibiendo(4), Disponible(5)
            // y que la sucursal destino sea la del usuario
            $transferencias = DB::connection('sqlsrv')
                ->table('TransferenciaTotalizadaView')
                ->whereIn('Estatus', [3, 4, 5])  // Registrada, Recibiendo, Disponible
                ->where('SucursalDestinoId', $sucursalId)
                ->orderBy('Fecha', 'desc')
                ->get();
            
            // Calcular porcentaje de avance para cada transferencia
            foreach ($transferencias as $transferencia) {
                $transferencia->PorcentajeRecibido = $transferencia->CantidadEmitida > 0 
                    ? ($transferencia->CantidadRecibida / $transferencia->CantidadEmitida) * 100 
                    : 0;
            }
            
            return view('cpanel.recepciones.distribuciones_sucursales', compact('transferencias'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_recepciones_sucursal: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de recepciones de sucursal: ' . $e->getMessage());
        }
    }

    public function createTransferencia()
    {
        try {
            // Obtener sucursales para el select
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Estatus', 1)
                ->select('ID', 'Nombre')
                ->get();
            
            // Obtener productos para el select (opcional)
            $productos = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('Estatus', 1)
                ->select('ID', 'Codigo', 'Descripcion')
                ->get();
            
            session([
                'menu_active' => 'Recepciones',
                'submenu_active' => 'Recibir de Sucursal'
            ]);
            
            return view('cpanel.recepcion.create', compact('sucursales', 'productos'));
            
        } catch (\Exception $e) {
            \Log::error('Error en createTransferencia: ' . $e->getMessage());
            return redirect()->route('cpanel.transferencias.distribuciones')
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }
}