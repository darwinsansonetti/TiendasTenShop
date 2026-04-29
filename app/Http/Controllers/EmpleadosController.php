<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUserRoles;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\DivisaValor;
use App\Models\Venta;
use App\Models\VentaProducto;
use App\Models\VentaVendedor;
use App\Models\Producto;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\BonoEmpleado;
use App\Models\Deduccion;
use App\DTO\CierreDiarioPeriodoDTO;
use App\Models\CierreDiario;
use App\Models\PagoPuntoDeVenta;
use App\Models\ValorizacionInventario;
use App\Models\Prestamo;
use App\Models\Transaccion;
use App\Models\CierreOfp;
use App\DTO\EDCOficinaPrincipalDTO;
use App\DTO\TransferenciaDTO;
use App\DTO\TransferenciaDetalleDTO;
use App\DTO\ProductoDTO;

use App\Models\Liberalidad;
use App\Models\LiberalidadDetalle;
use App\DTO\LiberalidadDTO;
use App\Models\LiberalidadEmpleadoTempo;

use App\Models\AspNetRoles;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

class EmpleadosController extends Controller
{
    public function listado_empleados(Request $request)
    {
        $now = Carbon::now('America/Caracas');
        
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio') 
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : $now->copy()->subDay()->startOfDay();

        $fechaFin = $request->input('fecha_fin') 
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : $now->copy()->subDay()->endOfDay();

        // $fechaInicio = Carbon::parse('2026-03-15', 'America/Caracas');
        // $fechaFin = Carbon::parse('2026-03-15', 'America/Caracas');

        $filtroFecha = new ParametrosFiltroFecha(
            null,   // tipoFiltroFecha
            null,   // mesSeleccionado
            null,   // año
            false,  // añoAnterior
            $fechaInicio,
            $fechaFin
        );

        $sucursalId = session('sucursal_id', 0);
        // Si es 0, convertirlo a null
        $sucursalId = $sucursalId ?: null;

        // Obtener ranking de vendedores
        $rankingVendedor = GeneralHelper::ObtenerRankingVendedoresSinAgrupar($filtroFecha, null, $sucursalId);

        session([
            'menu_active' => 'Empleados',
            'submenu_active' => 'Ventas Diarias'
        ]);

        return view('cpanel.empleados.listado_ventas_empleados', [
            'rankingVendedor' => $rankingVendedor,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
        ]);
    }

    public function detallesVenta($id, $vti)
    {
        try {
            // Buscar información del vendedor
            $vendedor = Usuario::where('UsuarioId', $id)
                ->where('EsActivo', 1)
                ->first();
            
            if (!$vendedor) {
                return back()->with('error', 'Vendedor no encontrado');
            }
            
            // Obtener los detalles de la venta (productos)
            $detallesVenta = DB::table('VentaVendedoresView')
                ->select([
                    'ID',
                    'ID as VentaId',
                    'Fecha',
                    'SucursalId',
                    'ProductoId',
                    'Cantidad',
                    'PrecioVenta',
                    'MontoDivisa',
                    'UsuarioId',
                    'CostoDivisa',
                    'Costo',
                    'Existencia'
                ])
                ->where('UsuarioId', $id)
                ->where('ID', $vti)
                ->orderBy('ProductoId')
                ->get();

            // ============================================
            // OBTENER RANGO DE FECHAS DE LOS REGISTROS
            // ============================================
            if ($detallesVenta->isNotEmpty()) {
                // Obtener todas las fechas y convertir a Carbon
                $fechas = $detallesVenta->pluck('Fecha')->map(function($fecha) {
                    return Carbon::parse($fecha);
                });
                
                // Obtener la fecha mínima y máxima
                $fechaInicio = $fechas->min();
                $fechaFin = $fechas->max();
            } else {
                // Si no hay registros, usar fechas actuales
                $fechaInicio = Carbon::now('America/Caracas');
                $fechaFin = Carbon::now('America/Caracas');
            }
            
            // Obtener IDs de productos para cargar TODOS sus datos
            $productoIds = $detallesVenta->pluck('ProductoId')->unique()->toArray();
            
            // Cargar TODA la información de productos (no solo nombre y código)
            $productos = Producto::whereIn('ID', $productoIds)
                ->get()
                ->keyBy('ID');
            
            // Enriquecer detalles con TODOS los datos del producto
            $detallesVenta->transform(function ($item) use ($productos) {
                $producto = $productos->get($item->ProductoId);
                
                if ($producto) {
                    // Asignar TODOS los campos del producto
                    $item->ProductoNombre = $producto->Nombre;
                    $item->ProductoCodigo = $producto->Codigo;
                    $item->ProductoDescripcion = $producto->Descripcion ?? '';
                    $item->ProductoUrlFoto = $producto->UrlFoto ?? '';
                    $item->ProductoCategoria = $producto->Categoria ?? '';
                    $item->ProductoMarca = $producto->Marca ?? '';
                    $item->ProductoModelo = $producto->Modelo ?? '';
                    $item->ProductoProveedorId = $producto->ProveedorId ?? null;
                    $item->ProductoEstatus = $producto->Estatus ?? 1;
                    $item->ProductoFechaRegistro = $producto->FechaRegistro ?? null;
                    $item->ProductoPrecioCompra = $producto->PrecioCompra ?? 0;
                    $item->ProductoPrecioVenta = $producto->PrecioVenta ?? 0;
                    $item->ProductoStock = $producto->Stock ?? 0;
                    $item->ProductoStockMinimo = $producto->StockMinimo ?? 0;
                    $item->ProductoUbicacion = $producto->Ubicacion ?? '';
                } else {
                    // Valores por defecto si no se encuentra el producto
                    $item->ProductoNombre = 'Producto no encontrado';
                    $item->ProductoCodigo = 'N/A';
                    $item->ProductoDescripcion = '';
                    $item->ProductoUrlFoto = '';
                    $item->ProductoCategoria = '';
                    $item->ProductoMarca = '';
                    $item->ProductoModelo = '';
                    $item->ProductoProveedorId = null;
                    $item->ProductoEstatus = 0;
                    $item->ProductoFechaRegistro = null;
                    $item->ProductoPrecioCompra = 0;
                    $item->ProductoPrecioVenta = 0;
                    $item->ProductoStock = 0;
                    $item->ProductoStockMinimo = 0;
                    $item->ProductoUbicacion = '';
                }
                
                // Calcular subtotales (estos ya los tenías)
                $item->SubtotalDivisa = $item->Cantidad * $item->PrecioVenta;
                $item->SubtotalBs = $item->SubtotalDivisa * ($item->TasaDeCambio ?? 1);
                
                return $item;
            });

            // Calcular totales de la venta
            $totales = (object) [
                'totalCantidad' => $detallesVenta->sum('Cantidad'),
                'totalDivisa' => $detallesVenta->sum('MontoDivisa'),
                'totalBs' => $detallesVenta->sum(function ($item) {
                    return $item->Cantidad * $item->PrecioVenta * ($item->TasaDeCambio ?? 1);
                })
            ];
            
            // Obtener información de la sucursal
            $sucursal = null;
            if ($vendedor->SucursalId) {
                $sucursal = Sucursal::find($vendedor->SucursalId);
            }
            
            // Puedes hacer un dd para verificar que ahora tienes todos los campos
            // dd($detallesVenta->first());
            
            return view('cpanel.empleados.detalles_venta', compact(
                'vendedor',
                'detallesVenta',
                'totales',
                'sucursal',
                'vti',
                'fechaInicio',
                'fechaFin'
            ));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
        }
    }

    public function listado_ranking(Request $request)
    {
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : Carbon::now('America/Caracas')->startOfMonth();  // Primer día del mes

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : Carbon::now('America/Caracas')->endOfMonth();    // Último día del mes

        // $fechaInicio = Carbon::parse('2026-03-15', 'America/Caracas');
        // $fechaFin = Carbon::parse('2026-03-15', 'America/Caracas');

        $filtroFecha = new ParametrosFiltroFecha(
            null,   // tipoFiltroFecha
            null,   // mesSeleccionado
            null,   // año
            false,  // añoAnterior
            $fechaInicio,
            $fechaFin
        );

        $sucursalId = session('sucursal_id', 0);
        // Si es 0, convertirlo a null
        $sucursalId = $sucursalId ?: null;

        // Obtener ranking según el filtro de fechas
        $rankingVendedor = GeneralHelper::ObtenerRankingVendedores($filtroFecha);

        session([
            'menu_active' => 'Empleados',
            'submenu_active' => 'Ranking General'
        ]);

        return view('cpanel.empleados.listado_ranking_empleados', [
            'rankingVendedor' => $rankingVendedor,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
        ]);
    }

    public function ventasVendedor(Request $request, $id)
    {
        try {
            // Obtener fechas del request o usar el período actual
            $fechaInicio = $request->input('fecha_inicio')
                ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
                : Carbon::now('America/Caracas')->startOfMonth();

            $fechaFin = $request->input('fecha_fin')
                ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
                : Carbon::now('America/Caracas')->endOfMonth();

            // Crear filtro de fechas
            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                $fechaInicio,
                $fechaFin
            );

            // Obtener información del vendedor
            $vendedor = Usuario::where('UsuarioId', $id)
                // ->where('EsActivo', 1)
                ->first();

            if (!$vendedor) {
                return back()->with('error', 'Vendedor no encontrado');
            }

            // Obtener los detalles de TODAS las ventas del vendedor en el rango de fechas
            $detallesVenta = DB::table('VentaVendedoresView')
                ->select([
                    'ID',
                    'ID as VentaId',
                    'Fecha',
                    'SucursalId',
                    'ProductoId',
                    'Cantidad',
                    'PrecioVenta',
                    'MontoDivisa',
                    'UsuarioId',
                    'CostoDivisa',
                    'Costo',
                    'Existencia'
                ])
                ->where('UsuarioId', $id)
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])  
                ->orderBy('Fecha', 'desc')
                ->orderBy('ProductoId')
                ->get();
            
            // Obtener IDs de productos para cargar TODOS sus datos
            $productoIds = $detallesVenta->pluck('ProductoId')->unique()->toArray();
            
            // Cargar TODA la información de productos (no solo nombre y código)
            $productos = Producto::whereIn('ID', $productoIds)
                ->get()
                ->keyBy('ID');
            
            // Enriquecer detalles con TODOS los datos del producto
            $detallesVenta->transform(function ($item) use ($productos) {
                $producto = $productos->get($item->ProductoId);
                
                if ($producto) {
                    // Asignar TODOS los campos del producto
                    $item->ProductoNombre = $producto->Nombre;
                    $item->ProductoCodigo = $producto->Codigo;
                    $item->ProductoDescripcion = $producto->Descripcion ?? '';
                    $item->ProductoUrlFoto = $producto->UrlFoto ?? '';
                    $item->ProductoCategoria = $producto->Categoria ?? '';
                    $item->ProductoMarca = $producto->Marca ?? '';
                    $item->ProductoModelo = $producto->Modelo ?? '';
                    $item->ProductoProveedorId = $producto->ProveedorId ?? null;
                    $item->ProductoEstatus = $producto->Estatus ?? 1;
                    $item->ProductoFechaRegistro = $producto->FechaRegistro ?? null;
                    $item->ProductoPrecioCompra = $producto->PrecioCompra ?? 0;
                    $item->ProductoPrecioVenta = $producto->PrecioVenta ?? 0;
                    $item->ProductoStock = $producto->Stock ?? 0;
                    $item->ProductoStockMinimo = $producto->StockMinimo ?? 0;
                    $item->ProductoUbicacion = $producto->Ubicacion ?? '';
                } else {
                    // Valores por defecto si no se encuentra el producto
                    $item->ProductoNombre = 'Producto no encontrado';
                    $item->ProductoCodigo = 'N/A';
                    $item->ProductoDescripcion = '';
                    $item->ProductoUrlFoto = '';
                    $item->ProductoCategoria = '';
                    $item->ProductoMarca = '';
                    $item->ProductoModelo = '';
                    $item->ProductoProveedorId = null;
                    $item->ProductoEstatus = 0;
                    $item->ProductoFechaRegistro = null;
                    $item->ProductoPrecioCompra = 0;
                    $item->ProductoPrecioVenta = 0;
                    $item->ProductoStock = 0;
                    $item->ProductoStockMinimo = 0;
                    $item->ProductoUbicacion = '';
                }
                
                // Calcular subtotales (estos ya los tenías)
                $item->SubtotalDivisa = $item->Cantidad * $item->PrecioVenta;
                $item->SubtotalBs = $item->SubtotalDivisa * ($item->TasaDeCambio ?? 1);
                
                return $item;
            });

            // Calcular totales de la venta
            $totales = (object) [
                'totalCantidad' => $detallesVenta->sum('Cantidad'),
                'totalDivisa' => $detallesVenta->sum('MontoDivisa'),
                'totalBs' => $detallesVenta->sum(function ($item) {
                    return $item->Cantidad * $item->PrecioVenta * ($item->TasaDeCambio ?? 1);
                })
            ];
            
            // Obtener información de la sucursal
            $sucursal = null;
            if ($vendedor->SucursalId) {
                $sucursal = Sucursal::find($vendedor->SucursalId);
            }
            
            // Puedes hacer un dd para verificar que ahora tienes todos los campos
            // dd($detallesVenta->first());
            
            return view('cpanel.empleados.detalles_venta', compact(
                'vendedor',
                'detallesVenta',
                'totales',
                'sucursal',
                'fechaInicio',
                'fechaFin'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar las ventas: ' . $e->getMessage());
        }
    }

    public function listado_vendedores(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : Carbon::now('America/Caracas')->startOfMonth();

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : Carbon::now('America/Caracas')->endOfMonth();

        $estatus = $request->input('estatus');

        $whereEstatus = "";
        if ($estatus === '1') {
            $whereEstatus = " AND u.EsActivo = 1 ";
        } elseif ($estatus === '0') {
            $whereEstatus = " AND u.EsActivo = 0 ";
        }

        $vendedores = DB::connection('sqlsrv')
        ->select("
            -- Vendedores del POS (tabla Usuarios)
            SELECT 
                u.UsuarioId as id,
                u.VendedorId,
                u.Email,
                u.PhoneNumber,
                u.EsActivo,
                u.NombreCompleto,
                u.Direccion,
                u.FechaCreacion,
                u.FechaNacimiento,
                u.SucursalId,
                u.FotoPerfil,
                u.EsRegistrado,
                s.Nombre as sucursal_nombre,
                'pos' as origen,
                -- 🔴 NUEVO: Indicar si existe en AspNetUsers
                CASE 
                    WHEN au.Id IS NOT NULL THEN 1 
                    ELSE 0 
                END as existe_en_identity,
                -- 🔴 NUEVO: Indicar si está activo en Identity
                CASE 
                    WHEN au.Id IS NOT NULL AND au.EsActivo = 1 THEN 1 
                    ELSE 0 
                END as identity_activo
            FROM Usuarios u
            INNER JOIN Sucursales s ON u.SucursalId = s.ID
            LEFT JOIN AspNetUsers au ON u.UsuarioId = au.ExternalId
            WHERE s.Tipo = 1
            {$whereEstatus}
            
            UNION
            
            -- Vendedores de Identity con rol VENDEDORES
            SELECT 
                au.Id as id,
                au.VendedorId,
                au.Email,
                au.PhoneNumber,
                au.EsActivo,
                au.NombreCompleto,
                au.Direccion,
                au.FechaCreacion,
                au.FechaNacimiento,
                au.SucursalId,
                au.FotoPerfil,
                CAST(au.EmailConfirmed as int) as EsRegistrado,
                s.Nombre as sucursal_nombre,
                'identity' as origen,
                1 as existe_en_identity,
                au.EsActivo as identity_activo
            FROM AspNetUsers au
            INNER JOIN AspNetUserRoles ur ON au.Id = ur.UserId
            INNER JOIN AspNetRoles r ON ur.RoleId = r.Id
            INNER JOIN Sucursales s ON au.SucursalId = s.ID
            WHERE r.Name = 'VENDEDORES'
                AND s.Tipo = 1
                " . ($estatus === '1' ? "AND au.EsActivo = 1" : ($estatus === '0' ? "AND au.EsActivo = 0" : "")) . "
            
            ORDER BY NombreCompleto
        ");
        
        // Convertir a colección y eliminar duplicados (priorizar POS)
        $vendedores = collect($vendedores);
        
        // Eliminar duplicados por VendedorId + SucursalId (priorizar 'pos' sobre 'identity')
        $vendedores = $vendedores->groupBy(function($item) {
            return $item->VendedorId . '_' . $item->SucursalId;
        })->map(function($group) {
            // Priorizar el que tiene origen 'pos' sobre 'identity'
            $vendedor = $group->firstWhere('origen', 'pos') ?? $group->first();
            
            // 🔴 NUEVO: Determinar si mostrar el botón
            // Mostrar botón si:
            // 1. Es del POS (origen 'pos')
            // 2. NO existe en Identity (existe_en_identity == 0)
            //    O existe pero está inactivo (identity_activo == 0)
            if ($vendedor->origen === 'pos') {
                if ($vendedor->existe_en_identity == 0) {
                    // No existe en Identity → crear nuevo
                    $vendedor->mostrar_boton_crear = true;
                    $vendedor->boton_texto = 'Crear en Identity';
                    $vendedor->boton_color = 'success';
                } elseif ($vendedor->identity_activo == 0) {
                    // Existe pero está inactivo → reactivar
                    $vendedor->mostrar_boton_crear = true;
                    $vendedor->boton_texto = 'Reactivar';
                    $vendedor->boton_color = 'warning';
                } else {
                    // Existe y está activo → no mostrar botón
                    $vendedor->mostrar_boton_crear = false;
                }
            } else {
                $vendedor->mostrar_boton_crear = false;
            }
            
            return $vendedor;
        })->values()->sortBy('NombreCompleto');
        
        session([
            'menu_active' => 'Empleados',
            'submenu_active' => 'Vendedores'
        ]);
        
        return view('cpanel.empleados.vendedores_listado', compact(
            'vendedores',
            'fechaInicio',
            'fechaFin',
            'estatus'
        ));
    }

    public function editarVendedor($id)
    {
        try {
            // Buscar en ambas tablas por el ID
            $vendedor = null;
            $origen = null;
            
            // Primero buscar en Usuarios (POS)
            $vendedor = Usuario::where('UsuarioId', $id)->first();
            
            if ($vendedor) {
                $origen = 'pos';
            } else {
                // Si no, buscar en AspNetUsers (Identity)
                $vendedor = AspNetUser::where('Id', $id)->first();
                if ($vendedor) {
                    $origen = 'identity';
                }
            }
            
            if (!$vendedor) {
                return back()->with('error', 'Vendedor no encontrado');
            }
            
            // Obtener lista de sucursales para el select
            $sucursales = Sucursal::where('EsActiva', 1)
                ->orderBy('Nombre')
                ->get();
            
            return view('cpanel.empleados.vendedor_editar', compact('vendedor', 'origen', 'sucursales'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar el vendedor: ' . $e->getMessage());
        }
    }

    public function actualizarVendedor(Request $request, $id)
    {
        try {
            // Validar los datos del formulario
            $request->validate([
                'NombreCompleto' => 'required|string|max:255',
                'PhoneNumber' => 'nullable|string|max:20',
                'FechaNacimiento' => 'nullable|date',
                'FechaIngreso' => 'nullable|date',
                'Direccion' => 'nullable|string|max:500',
                'SucursalId' => 'nullable|integer',
                'EsActivo' => 'boolean',
                'foto' => 'nullable|image|mimes:jpeg,png,gif|max:2048' // 2MB max
            ]);

            // Buscar el vendedor en ambas tablas
            $vendedor = Usuario::where('UsuarioId', $id)->first();
            $origen = 'pos';
            
            if (!$vendedor) {
                $vendedor = AspNetUser::where('Id', $id)->first();
                $origen = 'identity';
            }
            
            if (!$vendedor) {
                return back()->with('error', 'Vendedor no encontrado')->withInput();
            }

            // 1. ACTUALIZAR DATOS DEL VENDEDOR (como GuardarVendedor en .NET)
            $vendedor->NombreCompleto = $request->NombreCompleto;
            $vendedor->PhoneNumber = $request->PhoneNumber;
            $vendedor->Direccion = $request->Direccion;
            $vendedor->SucursalId = $request->SucursalId;
            $vendedor->EsActivo = $request->has('EsActivo') ? 1 : 0;
            
            // Manejo de fechas (como en .NET)
            if ($request->filled('FechaNacimiento')) {
                $vendedor->FechaNacimiento = Carbon::parse($request->FechaNacimiento)->format('Y-m-d H:i:s');
            }
            
            if ($request->filled('FechaIngreso')) {
                $vendedor->FechaCreacion = Carbon::parse($request->FechaIngreso)->format('Y-m-d H:i:s');
            }
            
            // Guardar datos básicos primero
            $vendedor->save();

            // 2. MANEJAR LA FOTO (como GuardarFotoVendedor en .NET)
            if ($request->hasFile('foto')) {
                $this->guardarFotoVendedor($request, $vendedor);
            }

            // 🔴 SINCRONIZAR FOTO ENTRE TABLAS (con SucursalId)
            if ($vendedor->VendedorId && $vendedor->SucursalId) {
                
                if ($vendedor instanceof Usuario) {
                    // Vendedor POS → sincronizar con Identity
                    $usuarioIdentity = AspNetUser::where('VendedorId', $vendedor->VendedorId)
                        ->where('SucursalId', $vendedor->SucursalId)  // 🔴 CRUCIAL: mismo SucursalId
                        ->first();
                    
                    if ($usuarioIdentity) {
                        $usuarioIdentity->FotoPerfil = $vendedor->FotoPerfil;
                        $usuarioIdentity->save();
                        \Log::info('Foto sincronizada: Usuario → Identity', [
                            'vendedor_id' => $vendedor->VendedorId,
                            'sucursal_id' => $vendedor->SucursalId,
                            'foto' => $vendedor->FotoPerfil
                        ]);
                    }
                } 
                elseif ($vendedor instanceof AspNetUser) {
                    // Identity → sincronizar con vendedor POS
                    $vendedorPOS = Usuario::where('VendedorId', $vendedor->VendedorId)
                        ->where('SucursalId', $vendedor->SucursalId)  // 🔴 CRUCIAL: mismo SucursalId
                        ->first();
                    
                    if ($vendedorPOS) {
                        $vendedorPOS->FotoPerfil = $vendedor->FotoPerfil;
                        $vendedorPOS->save();
                        \Log::info('Foto sincronizada: Identity → Usuario', [
                            'vendedor_id' => $vendedor->VendedorId,
                            'sucursal_id' => $vendedor->SucursalId,
                            'foto' => $vendedor->FotoPerfil
                        ]);
                    }
                }
            }

            $mensaje = $origen == 'pos' 
                ? 'Vendedor actualizado correctamente (POS)' 
                : 'Vendedor actualizado correctamente (Identity)';

            return redirect()->route('cpanel.empleados.vendedores')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function guardarFotoVendedor(Request $request, $vendedor)
    {
        try {
            $file = $request->file('foto');
            
            // Generar nombre del archivo
            $vendedorId = $vendedor->VendedorId ?? 'VEND';
            $sucursalId = $vendedor->SucursalId ?? '0';
            $extension = strtoupper($file->getClientOriginalExtension());
            $filename = $vendedorId . '_' . $sucursalId . '.' . $extension;
            
            // DETECTAR ENTORNO
            $environment = app()->environment();
            
            if ($environment === 'production') {
                // LÓGICA PARA SMARTERASP (PRODUCCIÓN)
                $folder = 'images/usuarios/';
                $physicalPath = base_path('public/' . $folder);
                
                if (!is_dir($physicalPath)) {
                    mkdir($physicalPath, 0777, true);
                }
                
                // Eliminar foto anterior
                if ($vendedor->FotoPerfil) {
                    $oldFilePath = $physicalPath . $vendedor->FotoPerfil;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                
                // Mover archivo
                $file->move($physicalPath, $filename);
                
            } else {
                // LÓGICA PARA LOCAL (USANDO STORAGE)
                $folder = 'images/usuarios/';
                $storagePath = 'public/' . $folder;
                
                if (!Storage::exists($storagePath)) {
                    Storage::makeDirectory($storagePath, 0755, true);
                }
                
                // Eliminar foto anterior
                if ($vendedor->FotoPerfil) {
                    $oldFile = $storagePath . $vendedor->FotoPerfil;
                    if (Storage::exists($oldFile)) {
                        Storage::delete($oldFile);
                    }
                }
                
                // Guardar nueva foto
                Storage::putFileAs($storagePath, $file, $filename);
            }
            
            // Actualizar BD (común para ambos entornos)
            $vendedor->FotoPerfil = $filename;
            $vendedor->save();
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Error al guardar foto: ' . $e->getMessage());
            throw $e;
        }
    }

    public function listado_personal(Request $request)
    {
        try {
            // ============================================
            // 1. OBTENER EMPLEADOS ACTIVOS
            // ============================================
            $incluirInactivos = $request->input('incluir_inactivos', false);
            
            // Consulta base de usuarios
            $query = DB::connection('sqlsrv')
                ->table('AspNetUsers as u')
                ->select([
                    'u.*',
                    's.Nombre as sucursal_nombre',
                    's.ID as sucursal_id'
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID');
            
            // Filtrar por activos si es necesario
            if (!$incluirInactivos) {
                $query->where('u.EsActivo', 1);
            }
            
            $empleados = $query->get();
            
            // ============================================
            // 2. OBTENER ROLES PARA CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                // Buscar el rol del usuario en AspNetUserRoles
                $userRole = DB::connection('sqlsrv')
                    ->table('AspNetUserRoles')
                    ->where('UserId', $empleado->Id)
                    ->first();
                
                if ($userRole) {
                    // Buscar el nombre del rol en AspNetRoles
                    $role = DB::connection('sqlsrv')
                        ->table('AspNetRoles')
                        ->where('Id', $userRole->RoleId)
                        ->first();
                    
                    $empleado->rol_id = $userRole->RoleId;
                    $empleado->rol_nombre = $role ? $role->Name : 'Desconocido';
                } else {
                    $empleado->rol_id = null;
                    $empleado->rol_nombre = 'Sin rol';
                }
            }
            // dd($empleados);
            
            // ============================================
            // 3. CONFIGURAR MENÚ ACTIVO
            // ============================================
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Personal Interno'
            ]);
            
            // ============================================
            // 4. RETORNAR VISTA
            // ============================================
            return view('cpanel.empleados.personal_listado', compact('empleados'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_personal: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de personal: ' . $e->getMessage());
        }
    }

    public function crearUsuarioIdentity(Request $request)
    {
        try {
            \Log::info('🔵 crearUsuarioIdentity - Iniciado', [
                'id' => $request->input('id'),
                'accion' => $request->input('accion')
            ]);
            
            $id = $request->input('id');
            $accion = $request->input('accion');
            
            if (!$id) {
                \Log::warning('⚠️ ID no proporcionado');
                return response()->json([
                    'success' => false,
                    'message' => 'ID de vendedor no proporcionado'
                ]);
            }
            
            // 1. BUSCAR VENDEDOR EN TABLA Usuarios
            \Log::info('🔵 Buscando vendedor en Usuarios', ['id' => $id]);
            
            $vendedor = DB::connection('sqlsrv')
                ->table('Usuarios')
                ->where('UsuarioId', $id)
                ->first();
            
            if (!$vendedor) {
                \Log::warning('⚠️ Vendedor no encontrado', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vendedor no encontrado en la tabla Usuarios'
                ]);
            }
            
            \Log::info('🔵 Vendedor encontrado', [
                'nombre' => $vendedor->NombreCompleto,
                'vendedor_id' => $vendedor->VendedorId
            ]);
            
            // Contraseña fija
            $passwordFijo = '12345678';
            $passwordHash = bcrypt($passwordFijo);
            
            // Email
            $email = $vendedor->Email;
            if (empty($email)) {
                $email = strtolower(str_replace(' ', '.', $vendedor->NombreCompleto)) . '@tiendastenshop.com';
            }
            
            \Log::info('🔵 Email generado', ['email' => $email]);
            
            // 2. BUSCAR SI EXISTE EN AspNetUsers
            \Log::info('🔵 Buscando en AspNetUsers');
            
            $usuarioExistente = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('ExternalId', $id)
                ->orWhere('VendedorId', $vendedor->VendedorId)
                ->first();
            
            if ($usuarioExistente && $accion === 'reactivar') {
                \Log::info('🔵 Reactivando usuario existente', ['usuario_id' => $usuarioExistente->Id]);
                
                try {
                    DB::connection('sqlsrv')
                        ->table('AspNetUsers')
                        ->where('Id', $usuarioExistente->Id)
                        ->update([
                            'EsActivo' => 1,
                            'NombreCompleto' => $vendedor->NombreCompleto,
                            'Email' => $email,
                            'NormalizedUserName' => strtoupper($email),
                            'NormalizedEmail' => strtoupper($email),
                            'PhoneNumber' => $vendedor->PhoneNumber,
                            'Direccion' => $vendedor->Direccion,
                            'SucursalId' => $vendedor->SucursalId,
                            'FechaNacimiento' => $vendedor->FechaNacimiento,
                            'FotoPerfil' => $vendedor->FotoPerfil,
                            'ExternalId' => $id,
                            'VendedorId' => $vendedor->VendedorId,
                            'PasswordHash' => $passwordHash,
                            'FechaCreacion' => now()
                        ]);
                    
                    \Log::info('✅ Usuario reactivado correctamente');
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Vendedor reactivado correctamente. Contraseña: ' . $passwordFijo
                    ]);
                } catch (\Exception $e) {
                    \Log::error('❌ Error al reactivar: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al reactivar: ' . $e->getMessage()
                    ]);
                }
            }
            
            if ($usuarioExistente && $usuarioExistente->EsActivo == 1) {
                \Log::warning('⚠️ Usuario ya existe y está activo');
                return response()->json([
                    'success' => false,
                    'message' => 'El vendedor ya existe y está activo en el sistema Identity'
                ]);
            }
            
            // 3. CREAR NUEVO USUARIO
            \Log::info('🔵 Creando nuevo usuario');
            
            $nuevoId = (string) \Illuminate\Support\Str::uuid();
            
            try {
                DB::connection('sqlsrv')
                    ->table('AspNetUsers')
                    ->insert([
                        'Id' => $nuevoId,
                        'UserName' => $email,
                        'NormalizedUserName' => strtoupper($email),
                        'Email' => $email,
                        'NormalizedEmail' => strtoupper($email),
                        'EmailConfirmed' => 1,
                        'PasswordHash' => $passwordHash,
                        'SecurityStamp' => \Illuminate\Support\Str::random(32),
                        'ConcurrencyStamp' => \Illuminate\Support\Str::random(32),
                        'PhoneNumber' => $vendedor->PhoneNumber,
                        'PhoneNumberConfirmed' => 1,
                        'TwoFactorEnabled' => 0,
                        'LockoutEnabled' => 1,
                        'AccessFailedCount' => 0,
                        'VendedorId' => $vendedor->VendedorId,
                        'EsActivo' => 1,
                        'NombreCompleto' => $vendedor->NombreCompleto,
                        'Direccion' => $vendedor->Direccion,
                        'FechaCreacion' => now(),
                        'FechaNacimiento' => $vendedor->FechaNacimiento,
                        'SucursalId' => $vendedor->SucursalId,
                        'FotoPerfil' => $vendedor->FotoPerfil,
                        'ExternalId' => $id
                    ]);
                
                // Asignar rol VENDEDORES
                $roleId = DB::connection('sqlsrv')
                    ->table('AspNetRoles')
                    ->where('Name', 'VENDEDORES')
                    ->value('Id');
                
                if ($roleId) {
                    DB::connection('sqlsrv')
                        ->table('AspNetUserRoles')
                        ->insert([
                            'UserId' => $nuevoId,
                            'RoleId' => $roleId
                        ]);
                }
                
                \Log::info('✅ Nuevo usuario creado correctamente', ['nuevo_id' => $nuevoId]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Vendedor creado correctamente. Contraseña: ' . $passwordFijo
                ]);
                
            } catch (\Exception $e) {
                \Log::error('❌ Error al crear usuario: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear usuario: ' . $e->getMessage()
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('❌ Error general en crearUsuarioIdentity: ' . $e->getMessage());
            \Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error general: ' . $e->getMessage()
            ]);
        }
    }

    public function editarEmpleadoInterno($id)
    {
        try {
            // 1. Buscar usuario por ID
            $usuarioInterno = AspNetUser::where('Id', $id)->first();
            
            if (!$usuarioInterno) {
                return back()->with('error', 'Usuario no encontrado');
            }
            
            // 2. Obtener el rol actual del usuario
            $userRole = DB::connection('sqlsrv')
                ->table('AspNetUserRoles')
                ->where('UserId', $usuarioInterno->Id)
                ->first();
            
            $userRoleId = $userRole ? $userRole->RoleId : null;
            
            // ✅ Inicializar variable (aunque no se use en la vista)
            $userRoleName = null;
            
            // 3. Obtener TODOS los roles disponibles (para el select)
            $roles = DB::connection('sqlsrv')
                ->table('AspNetRoles')
                ->orderBy('Name')
                ->get();
            
            // 4. Obtener lista de sucursales
            $sucursales = Sucursal::where('EsActiva', 1)
                ->orderBy('Nombre')
                ->get();
            
            // 5. Retornar vista con los datos
            return view('cpanel.empleados.interno_editar', compact(
                'usuarioInterno',
                'roles',           // Lista de roles para el select
                'userRoleId',      // ID del rol actual
                'sucursales'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en editarEmpleadoInterno: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el usuario: ' . $e->getMessage());
        }
    }

    // Método para actualizar empleado interno
    public function actualizarEmpleadoInterno(Request $request, $id)
    {
        try {
            // Validar los datos del formulario
            $request->validate([
                'NombreCompleto' => 'required|string|max:255',
                'Email' => 'required|email|max:255',
                'PhoneNumber' => 'nullable|string|max:20',
                'FechaNacimiento' => 'nullable|date',
                'FechaIngreso' => 'nullable|date',
                'Direccion' => 'nullable|string|max:500',
                'SucursalId' => 'nullable|integer',
                'EsActivo' => 'boolean',
                'rol_id' => 'required|string',
                'LockoutEnabled' => 'boolean', // 👈 Agregar si viene del formulario
                'foto' => 'nullable|image|mimes:jpeg,png,gif|max:2048'
            ]);

            // Buscar el empleado interno
            $empleado = AspNetUser::where('Id', $id)->first();
            
            if (!$empleado) {
                return back()->with('error', 'Empleado no encontrado')->withInput();
            }

            // Guardar valores anteriores para logs
            $estadoAnterior = [
                'activo' => $empleado->EsActivo,
                'rol' => DB::connection('sqlsrv')
                    ->table('AspNetUserRoles')
                    ->where('UserId', $empleado->Id)
                    ->pluck('RoleId')
                    ->toArray()
            ];

            // 1. ACTUALIZAR DATOS DEL EMPLEADO
            $empleado->NombreCompleto = $request->NombreCompleto;
            $empleado->Email = $request->Email;
            $empleado->UserName = $request->Email;
            $empleado->PhoneNumber = $request->PhoneNumber;
            $empleado->Direccion = $request->Direccion;
            $empleado->SucursalId = $request->SucursalId;
            $empleado->VendedorId = $request->VendedorId ?? $empleado->VendedorId;
            $empleado->EsActivo = $request->has('EsActivo') ? 1 : 0;
            
            // Campos de confirmación (como en .NET)
            $empleado->EmailConfirmed = true;
            $empleado->PhoneNumberConfirmed = true;
            
            // Manejo de LockoutEnabled (como en .NET)
            $empleado->LockoutEnabled = $request->has('LockoutEnabled') ? 1 : 0;
            
            // Manejo de fechas
            if ($request->filled('FechaNacimiento')) {
                $empleado->FechaNacimiento = Carbon::parse($request->FechaNacimiento)->format('Y-m-d H:i:s');
            }
            
            if ($request->filled('FechaIngreso')) {
                $empleado->FechaCreacion = Carbon::parse($request->FechaIngreso)->format('Y-m-d H:i:s');
            }
            
            // Guardar en Identity (equivalente a UpdateAsync)
            $empleado->save();

            // 2. 🔴 NUEVO: Desactivar vendedor asociado si el empleado se desactiva
            if (!$request->has('EsActivo') && $empleado->VendedorId && $empleado->SucursalId) {
                $this->desactivarVendedorAsociado($empleado->VendedorId, $empleado->SucursalId);
            }

            // 3. 🔴 NUEVO: Manejo de roles (como en .NET)
            // Obtener roles actuales del usuario
            $rolesActuales = DB::connection('sqlsrv')
                ->table('AspNetUserRoles')
                ->where('UserId', $empleado->Id)
                ->pluck('RoleId')
                ->toArray();
            
            // Remover TODOS los roles actuales (como RemoveFromRolesAsync)
            if (!empty($rolesActuales)) {
                DB::connection('sqlsrv')
                    ->table('AspNetUserRoles')
                    ->where('UserId', $empleado->Id)
                    ->delete();
            }
            
            // Obtener el nombre del nuevo rol (como FindByIdAsync en .NET)
            $nuevoRol = DB::connection('sqlsrv')
                ->table('AspNetRoles')
                ->where('Id', $request->rol_id)
                ->first();
            
            // Agregar el nuevo rol (como AddToRoleAsync)
            if ($nuevoRol) {
                DB::connection('sqlsrv')
                    ->table('AspNetUserRoles')
                    ->insert([
                        'UserId' => $empleado->Id,
                        'RoleId' => $request->rol_id
                    ]);
            }

            // 4. MANEJAR LA FOTO (igual que antes)
            if ($request->hasFile('foto')) {
                // $this->guardarFotoEmpleadoInterno($request, $empleado);
                $this->guardarFotoVendedor($request, $empleado);
            }

            // 🔴 NUEVO: SINCRONIZAR FOTO CON TABLA USUARIOS (vendedor POS)
            if ($empleado->VendedorId && $empleado->SucursalId) {
                $vendedorPOS = Usuario::where('VendedorId', $empleado->VendedorId)
                    ->where('SucursalId', $empleado->SucursalId)  // 🔴 IMPORTANTE: incluir SucursalId
                    ->first();
                
                if ($vendedorPOS) {
                    $vendedorPOS->FotoPerfil = $empleado->FotoPerfil;
                    $vendedorPOS->save();
                    \Log::info('Foto sincronizada: Identity → Usuario', [
                        'vendedor_id' => $empleado->VendedorId,
                        'sucursal_id' => $empleado->SucursalId,
                        'foto' => $empleado->FotoPerfil
                    ]);
                }
            }

            return redirect()->route('cpanel.empleados.personal')
                ->with('success', 'Empleado actualizado correctamente');

        } catch (\Exception $e) {
            \Log::error('Error actualizando empleado interno: ' . $e->getMessage());
            return back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function desactivarVendedorAsociado($vendedorId, $sucursalId)
    {
        try {
            if (!$vendedorId || !$sucursalId) {
                return 0;
            }
            
            $vendedor = Usuario::where('VendedorId', $vendedorId)
                ->where('SucursalId', $sucursalId)
                ->first();
            
            if ($vendedor) {
                $vendedor->EsActivo = 0;
                $vendedor->save();
                
                \Log::info('Vendedor desactivado desde empleado interno', [
                    'vendedor_id' => $vendedorId,
                    'sucursal_id' => $sucursalId,
                    'nombre' => $vendedor->NombreCompleto
                ]);
                
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            \Log::error('Error desactivando vendedor: ' . $e->getMessage());
            return 0;
        }
    }

    public function cambiarPassword($id)
    {
        try {
            $usuarioInterno = AspNetUser::where('Id', $id)->first();
            
            if (!$usuarioInterno) {
                return back()->with('error', 'Usuario no encontrado');
            }
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Personal Interno'
            ]);
            
            return view('cpanel.empleados.cambiar_password', compact('usuarioInterno'));
            
        } catch (\Exception $e) {
            \Log::error('Error al cargar cambio de password: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la página');
        }
    }

    public function actualizarPassword(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required'
            ]);
            
            $usuarioInterno = AspNetUser::where('Id', $id)->first();
            
            if (!$usuarioInterno) {
                return back()->with('error', 'Usuario no encontrado');
            }
            
            // Guardar contraseña hasheada
            $usuarioInterno->Password = bcrypt($request->password);
            $usuarioInterno->save();
            
            return redirect()->route('cpanel.empleados.personal')
                ->with('success', 'Contraseña actualizada correctamente');
            
        } catch (\Exception $e) {
            \Log::error('Error actualizando password: ' . $e->getMessage());
            return back()
                ->with('error', 'Error al actualizar la contraseña: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function agregarEmpleado(Request $request)
    {
        try {
            \Log::info('🔵 agregarEmpleado - Iniciado');
            
            // Verificar si hay datos precargados
            if (session()->has('precargar_vendedor')) {
                \Log::info('🔵 Datos precargados encontrados', session('precargar_vendedor'));
            } else {
                \Log::info('⚠️ No hay datos precargados en sesión');
            }
            
            // 3. Obtener TODOS los roles disponibles
            $roles = DB::connection('sqlsrv')
                ->table('AspNetRoles')
                ->orderBy('Name')
                ->get();
            
            // 4. Obtener lista de sucursales
            $sucursales = Sucursal::where('EsActiva', 1)
                ->orderBy('Nombre')
                ->get();
            
            \Log::info('🔵 agregarEmpleado - Datos cargados', [
                'roles_count' => $roles->count(),
                'sucursales_count' => $sucursales->count()
            ]);
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Agregar Empleado'
            ]);
            
            return view('cpanel.empleados.show_agregar_empleado', compact(
                'roles',           
                'sucursales'
            ));
            
        } catch (\Exception $e) {
            \Log::error('❌ Error en agregarEmpleado: ' . $e->getMessage());
            return back()->with('error', 'Error en la vista Agregar Empleado: ' . $e->getMessage());
        }
    }

    public function guardarEmpleado(Request $request)
    {
        try {
            
            // 1. Validar datos del formulario
            $request->validate([
                'NombreCompleto' => 'required|string|max:255',
                'Email' => 'required|email|max:255|unique:sqlsrv.AspNetUsers,Email',
                'password' => 'required|string|min:8|confirmed',
                'PhoneNumber' => 'nullable|string|max:20',
                'FechaNacimiento' => 'nullable|date',
                'FechaIngreso' => 'nullable|date',
                'Direccion' => 'nullable|string|max:500',
                'SucursalId' => 'nullable|integer',
                'rol_id' => 'required|string',
                'VendedorId' => 'nullable|string|max:10',
                'EsActivo' => 'boolean',
                'foto' => 'nullable|image|mimes:jpeg,png,gif|max:2048'
            ]);

            // 2. Verificar si el usuario ya existe por email
            $existeUsuario = AspNetUser::where('Email', $request->Email)->first();
            
            if ($existeUsuario) {
                return back()
                    ->with('error', 'Ya existe un usuario con este email')
                    ->withInput();
            }

            // 3. Preparar datos del nuevo usuario
            $nuevoUsuario = new AspNetUser();
            $nuevoUsuario->Id = (string) \Str::uuid();
            
            $nuevoUsuario->UserName = $request->Email;
            $nuevoUsuario->Email = $request->Email;
            $nuevoUsuario->NormalizedUserName = strtoupper($request->Email);
            $nuevoUsuario->NormalizedEmail = strtoupper($request->Email);
            $nuevoUsuario->EmailConfirmed = true;
            $nuevoUsuario->Password = bcrypt($request->password);
            $nuevoUsuario->SecurityStamp = \Str::random(32);
            $nuevoUsuario->ConcurrencyStamp = (string) \Str::uuid();
            $nuevoUsuario->PhoneNumber = $request->PhoneNumber;
            $nuevoUsuario->PhoneNumberConfirmed = true;
            $nuevoUsuario->TwoFactorEnabled = false;
            $nuevoUsuario->LockoutEnabled = true;
            $nuevoUsuario->AccessFailedCount = 0;
            $nuevoUsuario->NombreCompleto = $request->NombreCompleto;
            $nuevoUsuario->Direccion = $request->Direccion;
            $nuevoUsuario->SucursalId = $request->SucursalId;
            $nuevoUsuario->VendedorId = $request->VendedorId;
            $nuevoUsuario->EsActivo = $request->has('EsActivo') ? 1 : 0;
            $nuevoUsuario->FechaCreacion = $request->filled('FechaIngreso') 
                ? Carbon::parse($request->FechaIngreso) 
                : Carbon::now();
            
            if ($request->filled('FechaNacimiento')) {
                $nuevoUsuario->FechaNacimiento = Carbon::parse($request->FechaNacimiento);
            }

            // 4. Guardar en AspNetUsers
            $nuevoUsuario->save();

            // 5. LOG ANTES DE ROL: Verificar que el rol existe
            $rolExiste = DB::connection('sqlsrv')
                ->table('AspNetRoles')
                ->where('Id', $request->rol_id)
                ->exists();

            if (!$rolExiste) {
                \Log::error('El rol especificado no existe: ' . $request->rol_id);
            }

            // 6. Asignar rol
            $insertRol = DB::connection('sqlsrv')
                ->table('AspNetUserRoles')
                ->insert([
                    'UserId' => $nuevoUsuario->Id,
                    'RoleId' => $request->rol_id
                ]);

            // 7. Verificar que el rol se insertó
            $rolInsertado = DB::connection('sqlsrv')
                ->table('AspNetUserRoles')
                ->where('UserId', $nuevoUsuario->Id)
                ->first();

            // 8. Guardar foto PRIMERO (para que tenga el nombre)
            if ($request->hasFile('foto')) {
                $this->guardarFotoVendedor($request, $nuevoUsuario);
            }

            // 9. DESPUÉS sincronizar con Usuarios (ya con la foto)
            if ($request->filled('VendedorId')) {
                $this->sincronizarVendedorPOS($nuevoUsuario, $request);
            }

            return redirect()->route('cpanel.empleados.personal')
                ->with('success', 'Empleado interno creado correctamente');

        } catch (\Exception $e) {
            
            return back()
                ->with('error', 'Error al crear el empleado: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function sincronizarVendedorPOS($usuario, $request)
    {
        try {
            
            // Buscar si ya existe en Usuarios por VendedorId
            $vendedorExistente = Usuario::where('VendedorId', $request->VendedorId)
                ->where('SucursalId', $request->SucursalId)
                ->first();

            if (!$vendedorExistente) {
                
                // Obtener todos los IDs como enteros
                $ids = Usuario::pluck('UsuarioId')->map(function($id) {
                    return (int) $id;
                })->toArray();

                $ultimoId = !empty($ids) ? max($ids) : 0;
                $nuevoId = $ultimoId + 1;
                
                $nuevoVendedor = new Usuario();
                $nuevoVendedor->UsuarioId = (string) $nuevoId;
                
                $nuevoVendedor->VendedorId = $request->VendedorId;
                $nuevoVendedor->Email = $request->Email;
                $nuevoVendedor->NombreCompleto = $request->NombreCompleto;
                $nuevoVendedor->PhoneNumber = $request->PhoneNumber;
                $nuevoVendedor->Direccion = $request->Direccion;
                $nuevoVendedor->SucursalId = $request->SucursalId;
                $nuevoVendedor->EsActivo = $request->has('EsActivo') ? 1 : 0;
                $nuevoVendedor->FechaCreacion = Carbon::now();
                $nuevoVendedor->FechaNacimiento = $request->FechaNacimiento 
                    ? Carbon::parse($request->FechaNacimiento) 
                    : null;
                $nuevoVendedor->FotoPerfil = $usuario->FotoPerfil;
                $nuevoVendedor->EsRegistrado = 0;
                
                $nuevoVendedor->save();
                
            } else {
                // UPDATE (igual)
                $vendedorExistente->Email = $request->Email;
                $vendedorExistente->NombreCompleto = $request->NombreCompleto;
                $vendedorExistente->PhoneNumber = $request->PhoneNumber;
                $vendedorExistente->Direccion = $request->Direccion;
                $vendedorExistente->SucursalId = $request->SucursalId;
                $vendedorExistente->EsActivo = $request->has('EsActivo') ? 1 : 0;
                $vendedorExistente->FechaNacimiento = $request->FechaNacimiento 
                    ? Carbon::parse($request->FechaNacimiento) 
                    : $vendedorExistente->FechaNacimiento;
                $vendedorExistente->FotoPerfil = $usuario->FotoPerfil ?? $vendedorExistente->FotoPerfil;
                
                $vendedorExistente->save();
            }
            
        } catch (\Exception $e) {
            \Log::error('--- ERROR en sincronizarVendedorPOS ---');
            \Log::error('Mensaje: ' . $e->getMessage());
        }
    }

    public function obtenerProximoVendedorId($sucursalId)
    {
        try {
            // Buscar el último vendedor (con rol VENDEDORES) en esta sucursal
            // Buscar en la tabla Usuarios (vendedores POS)
            $ultimoVendedor = Usuario::where('SucursalId', $sucursalId)
                ->where('VendedorId', 'like', 'VDD%')
                ->orderByRaw('CAST(SUBSTRING(VendedorId, 4, LEN(VendedorId)) AS INT) DESC')
                ->first();
            
            if ($ultimoVendedor) {
                // Extraer el número del VendedorId (ej: VDD123 → 123)
                preg_match('/VDD(\d+)/', $ultimoVendedor->VendedorId, $matches);
                $ultimoNumero = isset($matches[1]) ? intval($matches[1]) : 0;
                $nuevoNumero = $ultimoNumero + 1;
                $nuevoVendedorId = 'VDD' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
            } else {
                // Si no hay vendedores, empezar con VDD001
                $nuevoVendedorId = 'VDD001';
            }
            
            // También verificar en AspNetUsers (por si hay vendedores solo en Identity)
            $ultimoIdentity = AspNetUser::where('SucursalId', $sucursalId)
                ->where('VendedorId', 'like', 'VDD%')
                ->orderByRaw('CAST(SUBSTRING(VendedorId, 4, LEN(VendedorId)) AS INT) DESC')
                ->first();
            
            if ($ultimoIdentity) {
                preg_match('/VDD(\d+)/', $ultimoIdentity->VendedorId, $matches);
                $numeroIdentity = isset($matches[1]) ? intval($matches[1]) : 0;
                $numeroActual = intval(preg_replace('/[^0-9]/', '', $nuevoVendedorId));
                if ($numeroIdentity >= $numeroActual) {
                    $nuevoNumero = $numeroIdentity + 1;
                    $nuevoVendedorId = 'VDD' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
                }
            }
            
            return response()->json([
                'success' => true,
                'vendedor_id' => $nuevoVendedorId,
                'sucursal_id' => $sucursalId
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo próximo vendedor ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al generar ID de vendedor'
            ], 500);
        }
    }

    public function listado_liberalidad(Request $request)
    {
        try {
            
            // ============================================
            // PROCESAR PERÍODO (formato YYYY-MM)
            // ============================================
            $periodo = $request->input('periodo', date('Y-m'));
            $partes = explode('-', $periodo);
            $anioSeleccionado = intval($partes[0]);
            $mesSeleccionado = intval($partes[1]);
            
            // Validar
            $mesSeleccionado = max(1, min(12, $mesSeleccionado));
            $anioSeleccionado = max(2000, min(2099, $anioSeleccionado));
            
            // Guardar en array para la vista
            $filtroMesAnio = [
                'mes' => $mesSeleccionado,
                'anio' => $anioSeleccionado
            ];
            
            // Calcular fechas de inicio y fin del mes
            $fechaInicio = Carbon::createFromDate($anioSeleccionado, $mesSeleccionado, 1)->startOfDay();
            $fechaFin = $fechaInicio->copy()->endOfMonth()->endOfDay();
            
            // Crear filtro de fechas
            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                $fechaInicio,
                $fechaFin
            );

            $estatus = 1;
            
            // Buscar liberalidad
            $liberalidadDTO = $this->buscarLiberalidad($filtroFecha, true, $estatus);

                // dd($liberalidadDTO);

            if (!$liberalidadDTO) {
                // No existe liberalidad cerrada, calcular datos actuales
                $liberalidad = $this->obtenerLiberalidad($filtroFecha);
                
                // Obtener la colección de detalles del DTO
                $detalles = $liberalidad->detalles;
                
                // Filtrar solo vendedores (EsVendedor = true) y agrupar por sucursal
                $liberalidadPorSucursal = $detalles
                    ->filter(function($item) {
                        return $item->EsVendedor === true;
                    })
                    ->groupBy(function($item) {
                        return $item->Usuario->SucursalId;
                    })
                    ->map(function($grupo, $sucursalId) {
                        $primerItem = $grupo->first();
                        
                        return [
                            'SucursalId' => $sucursalId,
                            'SucursalNombre' => $primerItem->Usuario->SucursalNombre,
                            'MontoLiberalidadSucursal' => $grupo->sum('MontoLiberalidad'),
                            'CantidadVendedores' => $grupo->count(),
                            'DetalleVendedores' => $grupo // Opcional: si quieres mantener el detalle
                        ];
                    })
                    ->values();

                $liberalidadPorSucursalJSON = json_encode($liberalidadPorSucursal);
                
                // Agregar empleados activos sin ventas
                $liberalidad = $this->obtenerEmpleadosActivosSinVentas($liberalidad, null);
                
                // Enriquecer con bonos y deducciones
                $liberalidad = $this->enriquecerLiberalidad($liberalidad, $mesSeleccionado, $anioSeleccionado, false);
                
                // NUEVO: Filtrar empleados que no tienen ventas, bonos ni deducciones
                $liberalidad = $this->filtrarEmpleadosSinNada($liberalidad);

                // dd($liberalidad);
                
            } else {
                // Existe liberalidad cerrada, mostrar datos guardados
                $liberalidad = null;
                $liberalidadPorSucursal = null;
                $liberalidadPorSucursalJSON = '[]';
                
                // Enriquecer con bonos y deducciones
                $liberalidadDTO = $this->enriquecerLiberalidad($liberalidadDTO, $mesSeleccionado, $anioSeleccionado, true);

                // dd($liberalidadDTO);
            }
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Liberalidad'
            ]);
            
            return view('cpanel.empleados.lista_liberalidad', compact(
                'liberalidad',
                'liberalidadDTO',
                'filtroMesAnio',
                'fechaInicio',
                'fechaFin',
                'periodo',
                'liberalidadPorSucursal',
                'liberalidadPorSucursalJSON'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_liberalidad: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al cargar liberalidad: ' . $e->getMessage());
        }
    }

    private function obtenerAjusteLiberalidadTemporal($usuarioId, $empleadoId, $mes, $anio)
    {
        $query = DB::connection('sqlsrv')
            ->table('LiberalidadEmpleadoTempo')
            ->select(['MontoDescuentoDivisa', 'MontoDescuentoBs'])
            ->where('Mes', $mes)
            ->where('Anno', $anio);
        
        if ($usuarioId) {
            $query->where('UsuarioId', $usuarioId);
        } elseif ($empleadoId) {
            $query->where('EmpleadoId', $empleadoId);
        } else {
            return null;
        }
        
        return $query->first();
    }

    private function obtenerEmpleadosActivosSinVentas($liberalidad, $sucursalId = null)
    {
        // Crear un mapa de claves únicas
        $clavesUnicas = [];
        foreach ($liberalidad->detalles as $detalle) {
            if (isset($detalle->UsuarioId) && $detalle->UsuarioId) {
                $clavesUnicas['usuario_' . $detalle->UsuarioId] = true;
            }
            if (isset($detalle->EmpleadoId) && $detalle->EmpleadoId) {
                $clavesUnicas['empleado_' . $detalle->EmpleadoId] = true;
            }
        }
        
        // Obtener empleados activos de AspNetUsers
        $aspNetUsers = DB::connection('sqlsrv')
            ->table('AspNetUsers')
            ->where('EsActivo', 1)
            ->when($sucursalId, function($query) use ($sucursalId) {
                return $query->where('SucursalId', $sucursalId);
            })
            ->get();
        
        // Obtener vendedores temporales activos de Usuarios
        $usuariosTemp = DB::connection('sqlsrv')
            ->table('Usuarios')
            ->where('EsActivo', 1)
            ->when($sucursalId, function($query) use ($sucursalId) {
                return $query->where('SucursalId', $sucursalId);
            })
            ->get();
        
        // Crear un mapa de ExternalId a GUID para evitar duplicados
        $externalIdToGuid = [];
        foreach ($aspNetUsers as $empleado) {
            if ($empleado->ExternalId) {
                $externalIdToGuid[$empleado->ExternalId] = $empleado->Id;
            }
        }
        
        $nuevosAgregados = 0;
        
        // Agregar empleados de sistema que faltan
        foreach ($aspNetUsers as $empleado) {
            $key = 'empleado_' . $empleado->Id;
            if (!isset($clavesUnicas[$key])) {
                
                // Verificar si ya existe un vendedor temporal con este ExternalId
                $externalId = $empleado->ExternalId;
                if ($externalId && isset($clavesUnicas['usuario_' . $externalId])) {
                    // Ya existe el vendedor temporal, no agregar duplicado
                    continue;
                }
                
                $nuevoDetalle = (object)[
                    'EmpleadoId' => $empleado->Id,
                    'UsuarioId' => null,
                    'Empleado' => $empleado,
                    'Usuario' => null,
                    'Unidades' => 0,
                    'Venta' => 0,
                    'MontoLiberalidad' => 0,
                    'MontoExcluido' => 0,
                    'MontoLiberacion' => 0,
                    'ProductosExcluidos' => 0,
                    'OtraLiberalidad' => 0,
                    'SaldoFavor' => 0,
                    'AbonoPrestamo' => 0,
                    'Estatus' => 0,
                    'EsVendedor' => false
                ];
                $liberalidad->detalles->push($nuevoDetalle);
                $nuevosAgregados++;
            }
        }
        
        // Agregar vendedores temporales que faltan
        foreach ($usuariosTemp as $vendedor) {
            $key = 'usuario_' . $vendedor->UsuarioId;
            if (!isset($clavesUnicas[$key])) {
                
                // Verificar si este vendedor temporal ya existe como empleado de sistema (por ExternalId)
                if (isset($externalIdToGuid[$vendedor->UsuarioId])) {
                    $guid = $externalIdToGuid[$vendedor->UsuarioId];
                    if (isset($clavesUnicas['empleado_' . $guid])) {
                        // Ya existe el empleado de sistema, no agregar duplicado
                        continue;
                    }
                }
                
                $nuevoDetalle = (object)[
                    'EmpleadoId' => null,
                    'UsuarioId' => $vendedor->UsuarioId,
                    'Empleado' => null,
                    'Usuario' => $vendedor,
                    'Unidades' => 0,
                    'Venta' => 0,
                    'MontoLiberalidad' => 0,
                    'MontoExcluido' => 0,
                    'MontoLiberacion' => 0,
                    'ProductosExcluidos' => 0,
                    'OtraLiberalidad' => 0,
                    'SaldoFavor' => 0,
                    'AbonoPrestamo' => 0,
                    'Estatus' => 0,
                    'EsVendedor' => true
                ];
                $liberalidad->detalles->push($nuevoDetalle);
                $nuevosAgregados++;
            }
        }
        
        return $liberalidad;
    }

    private function filtrarEmpleadosSinNada($liberalidad)
    {
        if (!$liberalidad || !isset($liberalidad->detalles)) {
            return $liberalidad;
        }
        
        // Filtrar: mantener empleados que tengan ventas, bonos, deducciones, deuda préstamo u otra liberalidad
        $detallesFiltrados = $liberalidad->detalles->filter(function($detalle) {
            $tieneVentas = ($detalle->Venta ?? 0) > 0;
            $tieneBonos = ($detalle->total_bonos_usd ?? 0) > 0;
            $tieneDeducciones = ($detalle->total_deducciones_usd ?? 0) > 0;
            $tieneDeudaPrestamo = ($detalle->DeudaPrestamo ?? 0) > 0;
            $tieneOtraLiberalidad = ($detalle->OtraLiberalidad ?? 0) > 0;
            $tieneMontoLiberalidad = ($detalle->MontoLiberalidad ?? 0) > 0;
            
            return $tieneVentas || $tieneBonos || $tieneDeducciones || $tieneDeudaPrestamo || $tieneOtraLiberalidad || $tieneMontoLiberalidad;
        });
        
        $liberalidad->detalles = $detallesFiltrados->values();
        
        return $liberalidad;
    }
    
    private function enriquecerLiberalidad($liberalidad, $mes, $anio, $esCerrado = false)
    {
        if (!$liberalidad || !isset($liberalidad->detalles)) {
            return $liberalidad;
        }
        
        foreach ($liberalidad->detalles as $detalle) {
            $usuarioId = $detalle->UsuarioId ?? null;
            $empleadoId = $detalle->EmpleadoId ?? null;
            
            // ============================================
            // BONOS
            // ============================================
            $bonos = $this->obtenerBonosPorEmpleado($usuarioId, $empleadoId, $mes, $anio);
            
            $detalle->bonos = $bonos;
            $detalle->total_bonos_bs = $bonos->sum('MontoBs');
            $detalle->total_bonos_usd = $bonos->sum('MontoDivisa');
            $detalle->cantidad_bonos = $bonos->count();
            $detalle->bonos_pendientes = $bonos->where('EsPagado', 0)->count();
            $detalle->bonos_pagados = $bonos->where('EsPagado', 1)->count();
            
            // ============================================
            // DEDUCCIONES
            // ============================================
            $deducciones = $this->obtenerDeduccionesPorEmpleado($usuarioId, $empleadoId, $mes, $anio);
            
            $detalle->deducciones = $deducciones;
            $detalle->total_deducciones_bs = $deducciones->sum('MontoBs');
            $detalle->total_deducciones_usd = $deducciones->sum('MontoDivisa');
            $detalle->cantidad_deducciones = $deducciones->count();
            $detalle->deducciones_pendientes = $deducciones->where('EsPagado', 0)->count();
            $detalle->deducciones_pagadas = $deducciones->where('EsPagado', 1)->count();
            
            // ============================================
            // NETO (Liberalidad + Bonos - Deducciones)
            // ============================================
            // $detalle->neto_usd = ($detalle->MontoLiberalidad ?? 0) + $detalle->total_bonos_usd - $detalle->total_deducciones_usd;

            // ============================================
            // 3. APLICAR AJUSTE DE LIBERALIDAD TEMPORAL (NUEVO)
            // ============================================
            $ajuste = $this->obtenerAjusteLiberalidadTemporal($usuarioId, $empleadoId, $mes, $anio);
            
            $montoLiberalidadOriginal = $detalle->MontoLiberalidad ?? 0;
            
            if ($ajuste && $ajuste->MontoDescuentoDivisa > 0) {
                // Restar el descuento al MontoLiberalidad original
                $detalle->MontoLiberalidad = max(0, $montoLiberalidadOriginal - $ajuste->MontoDescuentoDivisa);
                $detalle->ajuste_liberalidad = $ajuste->MontoDescuentoDivisa;
                
                \Log::info('📝 Ajuste de liberalidad aplicado', [
                    'empleado' => $detalle->Usuario->NombreCompleto ?? $detalle->Empleado->NombreCompleto ?? 'N/A',
                    'original' => $montoLiberalidadOriginal,
                    'descuento' => $ajuste->MontoDescuentoDivisa,
                    'nuevo' => $detalle->MontoLiberalidad
                ]);
            } else {
                $detalle->ajuste_liberalidad = 0;
                $detalle->ajuste_motivo = null;
            }

            // ============================================
            // NETO (comportamiento diferente según bandera)
            // ============================================
            if ($esCerrado) {
                // Mes CERRADO: respetar el valor guardado en LiberalidadDetalles
                // El neto ya está en MontoLiberalidad (o calculado con OtraLiberalidad - DeudaPrestamo)
                $detalle->neto_usd = ($detalle->MontoLiberalidad ?? 0) + ($detalle->OtraLiberalidad ?? 0) - ($detalle->DeudaPrestamo ?? 0) - ($detalle->AbonoPrestamo ?? 0);
            } else {
                // Mes ABIERTO: recalcular con datos actuales
                $detalle->neto_usd = ($detalle->MontoLiberalidad ?? 0) + $detalle->total_bonos_usd - $detalle->total_deducciones_usd;
            }
        }
        
        return $liberalidad;
    }

    private function obtenerBonosPorEmpleado($usuarioId, $empleadoId, $mes, $anio)
    {
        $query = DB::connection('sqlsrv')
            ->table('BonosEmpleados')
            ->select([
                'ID',
                'TipoBono',
                'MontoBs',
                'MontoDivisa',
                'Tasa',
                'EsPagado',
                'FechaCreacion',
                'Motivo'
            ])
            ->where('MesBono', $mes)
            ->where('AnnoBono', $anio);
        
        // Buscar por AMBOS campos (OR)
        $query->where(function($q) use ($usuarioId, $empleadoId) {
            if ($usuarioId) {
                $q->orWhere('UsuarioId', $usuarioId);
            }
            if ($empleadoId) {
                $q->orWhere('EmpleadoId', $empleadoId);
            }
        });
        
        // Si no hay ningún criterio, retornar vacío
        if (!$usuarioId && !$empleadoId) {
            return collect();
        }
        
        return $query->orderBy('FechaCreacion', 'desc')->get();
    }

    private function obtenerDeduccionesPorEmpleado($usuarioId, $empleadoId, $mes, $anio)
    {
        $query = DB::connection('sqlsrv')
            ->table('Deducciones')
            ->select([
                'ID',
                'TipoDeduccion',
                'MontoBs',
                'MontoDivisa',
                'Tasa',
                'EsPagado',
                'FechaCreacion',
                'Motivo'
            ])
            ->where('MesDeduccion', $mes)
            ->where('AnnoDeduccion', $anio);
        
        // Buscar por AMBOS campos (OR)
        $query->where(function($q) use ($usuarioId, $empleadoId) {
            if ($usuarioId) {
                $q->orWhere('UsuarioId', $usuarioId);
            }
            if ($empleadoId) {
                $q->orWhere('EmpleadoId', $empleadoId);
            }
        });
        
        // Si no hay ningún criterio, retornar vacío
        if (!$usuarioId && !$empleadoId) {
            return collect();
        }
        
        return $query->orderBy('FechaCreacion', 'desc')->get();
    }

    private function buscarLiberalidad($filtroFecha, $esDetalles = true, $estatus)
    {
        try {
            // Usar las propiedades del filtro
            // $mes = $filtroFecha->mes->value;  // Obtener el valor numérico del enum
            // $anio = $filtroFecha->anno;

            $mes = $filtroFecha->fechaInicio->month;      // Devuelve el mes como número (1-12)
            $anio = $filtroFecha->fechaInicio->year; 
            
            // Buscar liberalidad existente
            $liberalidad = Liberalidad::where('Anno', $anio)
                ->where('Mes', $mes)
                ->first();

            if($liberalidad == null) return null;
            
            // Generar objeto liberalidad (como GenerarObjetoLiberalidad)
            $liberalidadDTO = $this->generarObjetoLiberalidad($liberalidad, $esDetalles);

            // dd($liberalidadDTO);
            
            return $liberalidadDTO;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function generarObjetoLiberalidad($liberalidad, $esDetalles = true)
    {
        // Crear DTO básico
        $liberalidadDTO = new LiberalidadDTO($liberalidad);

        foreach ($liberalidadDTO->detalles as $detalle) {
            $sucursalId = null;
            
            if ($detalle->Usuario && $detalle->Usuario->SucursalId) {
                $sucursalId = $detalle->Usuario->SucursalId;
            } elseif ($detalle->Empleado && $detalle->Empleado->SucursalId) {
                $sucursalId = $detalle->Empleado->SucursalId;
            }
            
            if ($sucursalId) {
                $detalle->Sucursal = Sucursal::find($sucursalId);                
            } else {
                $detalle->Sucursal = 'N/A';
            }
        }

        // dd($liberalidadDTO);
        
        // // Si se requieren detalles
        // if ($esDetalles) {
        //     try {
        //         // Obtener detalles con estatus Disponible (0)
        //         $detalles = LiberalidadDetalle::where('LiberalidadId', $liberalidad->LiberalidadId)
        //             ->where('Estatus', 0)
        //             ->get();
                
        //         $liberalidadDTO->detalles = collect();
                
        //         foreach ($detalles as $item) {
        //             // Crear DTO del detalle
        //             $detalleDTO = new \App\DTOs\LiberalidadDetalleDTO($item);
                    
        //             // Cargar información del vendedor según el tipo
        //             if ($item->EsVendedor) {
        //                 // Es vendedor de POS (tabla Usuarios)
        //                 $vendedor = $this->buscarVendedor($item->UsuarioId);
        //                 if ($vendedor) {
        //                     $detalleDTO->Usuario = $vendedor;
        //                 }
        //             } else {
        //                 // Es empleado interno (tabla AspNetUsers)
        //                 $empleado = AspNetUser::where('Id', $item->EmpleadoId)->first();
        //                 if ($empleado) {
        //                     $vendedorDTO = $this->generarObjetoVendedor($empleado);
        //                     if ($vendedorDTO) {
        //                         $detalleDTO->Usuario = $vendedorDTO;
        //                         $detalleDTO->UsuarioId = 0; // Como en .NET
        //                     }
        //                 }
        //             }
                    
        //             $liberalidadDTO->detalles->push($detalleDTO);
        //         }
                
        //     } catch (\Exception $e) {
        //         \Log::error('Error al cargar detalles: ' . $e->getMessage());
        //         throw $e;
        //     }
        // }
        
        return $liberalidadDTO;
    }

    private function buscarVendedor($id)
    {
        try {
            $usuario = Usuario::where('UsuarioId', $id)->first();

            if ($usuario) {
                
                // Buscar sucursal por separado (sin relación)
                $sucursal = null;
                if ($usuario->SucursalId) {
                    $sucursal = Sucursal::where('ID', $usuario->SucursalId)->first();
                }
                
                return $this->generarVendedorDTO($usuario, $sucursal);
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error en buscarVendedor: ' . $e->getMessage());
            return null;
        }
    }

    private function generarVendedorDTO($vendedor, $sucursal = null)
    {
        $vendedorDTO = new \stdClass();
        
        // Verificar si es un objeto Usuario (POS) o AspNetUser (Identity)
        if (isset($vendedor->UsuarioId) && !isset($vendedor->Id)) {
            // Es Usuario
            $vendedorDTO->UsuarioId = $vendedor->UsuarioId;
            $vendedorDTO->VendedorId = $vendedor->VendedorId;
            $vendedorDTO->NombreCompleto = $vendedor->NombreCompleto;
            $vendedorDTO->Email = $vendedor->Email;
            $vendedorDTO->PhoneNumber = $vendedor->PhoneNumber;
            $vendedorDTO->Direccion = $vendedor->Direccion;
            $vendedorDTO->EsActivo = $vendedor->EsActivo;
            $vendedorDTO->EsRegistrado = $vendedor->EsRegistrado;
            $vendedorDTO->FechaCreacion = $vendedor->FechaCreacion;
            $vendedorDTO->FechaNacimiento = $vendedor->FechaNacimiento;
            $vendedorDTO->FotoPerfil = $vendedor->FotoPerfil;
            $vendedorDTO->SucursalId = $vendedor->SucursalId;
            $vendedorDTO->SucursalNombre = $sucursal ? $sucursal->Nombre : null;
        } else {
            // Es AspNetUser
            $vendedorDTO->UsuarioId = $vendedor->Id;
            $vendedorDTO->VendedorId = $vendedor->VendedorId;
            $vendedorDTO->NombreCompleto = $vendedor->NombreCompleto;
            $vendedorDTO->Email = $vendedor->Email;
            $vendedorDTO->PhoneNumber = $vendedor->PhoneNumber;
            $vendedorDTO->Direccion = $vendedor->Direccion;
            $vendedorDTO->EsActivo = $vendedor->EsActivo;
            $vendedorDTO->EsRegistrado = true;
            $vendedorDTO->FechaCreacion = $vendedor->FechaCreacion;
            $vendedorDTO->FechaNacimiento = $vendedor->FechaNacimiento;
            $vendedorDTO->FotoPerfil = $vendedor->FotoPerfil;
            $vendedorDTO->SucursalId = $vendedor->SucursalId;
            $vendedorDTO->SucursalNombre = $sucursal ? $sucursal->Nombre : null;
        }
        
        return $vendedorDTO;
    }

    private function generarObjetoVendedor($usuarioIdentity)
    {
        if (!$usuarioIdentity) {
            return null;
        }
        
        // Cargar sucursal si no está cargada
        if (!isset($usuarioIdentity->sucursal) && $usuarioIdentity->SucursalId) {
            $usuarioIdentity->sucursal = Sucursal::find($usuarioIdentity->SucursalId);
        }
        
        $vendedorDTO = new \stdClass();
        $vendedorDTO->UsuarioId = $usuarioIdentity->Id;
        $vendedorDTO->VendedorId = $usuarioIdentity->VendedorId;
        $vendedorDTO->NombreCompleto = $usuarioIdentity->NombreCompleto;
        $vendedorDTO->Email = $usuarioIdentity->Email;
        $vendedorDTO->PhoneNumber = $usuarioIdentity->PhoneNumber;
        $vendedorDTO->Direccion = $usuarioIdentity->Direccion;
        $vendedorDTO->EsActivo = $usuarioIdentity->EsActivo;
        $vendedorDTO->EsRegistrado = true;
        $vendedorDTO->ExternalId = $usuarioIdentity->ExternalId;
        $vendedorDTO->FechaCreacion = $usuarioIdentity->FechaCreacion;
        $vendedorDTO->FechaNacimiento = $usuarioIdentity->FechaNacimiento;
        $vendedorDTO->FotoPerfil = $usuarioIdentity->FotoPerfil;
        $vendedorDTO->SucursalId = $usuarioIdentity->SucursalId;
        $vendedorDTO->Sucursal = $usuarioIdentity->sucursal;
        
        return $vendedorDTO;
    }

    private function obtenerLiberalidad($filtroFecha)
    {
        try {
            // Obtener usuario actual
            $usuarioActual = Auth::user();

            $roles = DB::connection('sqlsrv')
                    ->table('AspNetUserRoles as ur')
                    ->join('AspNetRoles as r', 'ur.RoleId', '=', 'r.Id')
                    ->where('ur.UserId', $usuarioActual->Id)
                    ->pluck('r.Name')
                    ->toArray();
            
            // Crear objeto liberalidad
            $liberalidadDTO = LiberalidadDTO::empty();
            $liberalidadDTO->detalles = collect();
            
            if ($roles[0] != "MASTER" && $roles[0] != "SECRETARIA") {
                // Usuario normal (vendedor)
                $externalId = $usuarioActual->ExternalId;
                $liberalidadDTO->detalles = $this->obtenerVentaDetalladaPorUsuario($filtroFecha, $externalId);
            } else {
                // MASTER o SECRETARIA
                $liberalidadDTO->detalles = $this->obtenerVentaDetalladaTodos($filtroFecha);
            }
            
            // Asignar datos del filtro al DTO
            $liberalidadDTO->Mes = $filtroFecha->mes->value;
            $liberalidadDTO->Anno = $filtroFecha->anno;
            $liberalidadDTO->FechaInicio = $filtroFecha->fechaInicio;
            $liberalidadDTO->FechaFinal = $filtroFecha->fechaFin;
            $liberalidadDTO->Estatus = $this->buscarEstatusLiberalidad($liberalidadDTO->Mes, $liberalidadDTO->Anno);
            
            // Buscar liberalidad de empleados
            $liberalidadDTO = $this->buscarLiberalidadEmpleados($liberalidadDTO);

            // dd($liberalidadDTO);
            
            return $liberalidadDTO;
            
        } catch (\Exception $e) {
            \Log::error('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function buscarLiberalidadEmpleados($liberalidadDTO)
    {
        // Obtener todos los empleados activos (equivalente a _userManager.Users.Where(x => x.EsActivo))
        $listaEmpleados = AspNetUser::where('EsActivo', 1)->get();

        if ($listaEmpleados && $listaEmpleados->count() > 0 && $liberalidadDTO && $liberalidadDTO->detalles) {
            foreach ($listaEmpleados as $item) {

                $externalId = $item->ExternalId ?? $item->Id;
                
                // Verificar si el empleado ya existe en los detalles
                $existe = $liberalidadDTO->detalles->contains(function($detalle) use ($externalId) {
                    return $detalle->UsuarioId == $externalId;
                });

                if (!$existe) {
                    $detalleNuevo = new \stdClass();
                    $detalleNuevo->Empleado = $item;
                    $detalleNuevo->EmpleadoId = $item->Id;
                    $detalleNuevo->Estatus = 0; // EnumDetalleLiberalidad.Disponible
                    $detalleNuevo->EsUsuarioRegistrado = true;
                    $detalleNuevo->EsVendedor = false;
                    $detalleNuevo->LiberalidadId = $liberalidadDTO->LiberalidadId ?? null;
                    $detalleNuevo->MontoLiberalidad = 0;
                    $detalleNuevo->OtraLiberalidad = 0;
                    $detalleNuevo->Pago = 0;
                    $detalleNuevo->PagoPrestamo = 0;
                    $detalleNuevo->TotalPagado = 0;
                    $detalleNuevo->SaldoFavor = 0;
                    $detalleNuevo->Unidades = 0;
                    $detalleNuevo->Usuario = $this->generarObjetoVendedor($item);
                    $detalleNuevo->UsuarioId = 0;
                    
                    $liberalidadDTO->detalles->push($detalleNuevo);
                }
            }
        }
        
        return $liberalidadDTO;
    }

    private function buscarEstatusLiberalidad($mes, $anio)
    {
        $liberalidad = Liberalidad::where('Anno', $anio)
            ->where('Mes', $mes)
            ->first();

        if ($liberalidad) {
            return $liberalidad->Estatus;
        }

        return 0;
    }

    private function obtenerVentaDetalladaTodos($filtroFecha)
    {
        return $this->obtenerVentaDetalladaPorUsuario($filtroFecha, '');
    }

    private function obtenerVentaDetalladaPorUsuario($filtroFecha, $externalId)
    {
        try {
            
            // 1. Obtener lista de EDCUsuario (ventas agrupadas por vendedor)
            $lista = $this->obtenerVentasVendedorPeriodo($filtroFecha, $externalId);

            // dd($lista);
            
            $detalles = collect();
            
            if ($lista && $lista->count() > 0) {
                foreach ($lista as $item) {

                    // Verificar si el vendedor está activo (como en .NET)
                    if ($item->Usuario && $item->Usuario->EsActivo == 1) {
                        $detalle = new \stdClass();
                        $detalle->OtraLiberalidad = 0;                                              // Bien
                        $detalle->SaldoFavor = 0;                                                   // Bien
                        $detalle->AbonoPrestamo = 0;                                                // Bien
                        $detalle->Empleado = $item->UsuarioInternoId;                               // Bien
                        $detalle->EmpleadoId = $item->Usuario->UsuarioId ?? $item->Usuario->Id;     // Bien
                        $detalle->Estatus = 0; // EnumDetalleLiberalidad.Disponible                 // Bien
                        $detalle->MontoLiberalidad = $item->ComisionesDivisas ?? 0;
                        $detalle->Unidades = $item->TotalUnidades ?? 0;                             // Bien
                        $detalle->Usuario = $item->Usuario;                                         // Bien
                        // $detalle->UsuarioId = $item->Usuario->ExternalId ?? $item->Usuario->UsuarioId ?? $item->Usuario->Id ?? null;
                        $detalle->UsuarioId = $item->Usuario->ExternalId ?? $item->Usuario->UsuarioId ?? $item->Usuario->Id ?? null;
                        $detalle->Venta = $item->TotalVentas ?? 0;                                  // Bien
                        $detalle->EsVendedor = true;                                                // Bien

                        $detalle->MontoExcluido = $item->MontoExcluido ?? 0;
                        $detalle->MontoLiberacion = $item->MontoLiberacion ?? 0;
                        $detalle->ProductosExcluidos = $item->ProductosExcluidos ?? 0;
                        
                        $detalles->push($detalle);
                    }
                }
            }

            // dd($detalles);
            
            return $detalles;
            
        } catch (\Exception $e) {
            \Log::error('Error en obtenerVentaDetallada: ' . $e->getMessage());
            return collect();
        }
    }

    private function obtenerVentasVendedorPeriodo($filtroFecha, $usuarioId = null)
    {
        
        $ventaDiaria = $this->obtenerVentasDiariasDeVendedor($filtroFecha, null, $usuarioId);

        // dd($ventaDiaria);
        
        $listaUsuarios = collect();

        if ($ventaDiaria && $ventaDiaria->ListaVentasDiarias && $ventaDiaria->ListaVentasDiarias->count() > 0) {

            // OBTENER TODOS LOS IDs DE VENTAS
            $ventaIds = $ventaDiaria->ListaVentasDiarias->pluck('ID')->unique()->toArray();
            
            // CARGAR TODOS LOS PRODUCTOS DE TODAS LAS VENTAS DE UNA SOLA VEZ
            $todosLosProductos = DB::connection('sqlsrv')
                ->table('VentaVendedoresView')
                ->select([
                    'ID as VentaId',
                    'ProductoId',
                    'Cantidad',
                    'PrecioVenta',
                    'MontoDivisa',
                    'UsuarioId',
                    'CostoDivisa',
                    'Costo',
                    'Existencia'
                ])
                ->whereIn('ID', $ventaIds)
                ->when($filtroFecha && $filtroFecha->fechaInicio && $filtroFecha->fechaFin, function($query) use ($filtroFecha) {
                    return $query->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);
                })
                ->orderBy('ProductoId')
                ->get();

            // dd($todosLosProductos);
            
            // AGRUPAR PRODUCTOS POR VENTAId
            $productosPorVenta = $todosLosProductos->groupBy('VentaId');

            // dd($productosPorVenta[12912]);
            
            // CARGAR TODOS LOS PRODUCTOS (modelo Producto) DE UNA SOLA VEZ
            $todosLosProductoIds = $todosLosProductos->pluck('ProductoId')->unique()->toArray();
            $productosModel = Producto::whereIn('ID', $todosLosProductoIds)->get()->keyBy('ID');
            
            // CARGAR TODOS LOS PRODUCTOS SUCURSAL DE UNA SOLA VEZ
            $productosSucursal = ProductoSucursal::whereIn('ProductoId', $todosLosProductoIds)
                ->where('SucursalId', $ventaDiaria->ListaVentasDiarias->first()->SucursalId ?? 0)
                ->where('Estatus', 1)
                ->get()
                ->keyBy('ProductoId');

            foreach ($ventaDiaria->ListaVentasDiarias as $item) {
                $productosVenta = $productosPorVenta->get($item->ID, collect());

                // dd($productosVenta);

                $listaDetalleDTO = collect();
                foreach ($productosVenta as $productoVenta) {
                    $productoDetalleDTO = new \stdClass();
                    $productoDetalleDTO->VentaId = $productoVenta->VentaId;
                    $productoDetalleDTO->ProductoId = $productoVenta->ProductoId;
                    $productoDetalleDTO->Cantidad = $productoVenta->Cantidad;
                    $productoDetalleDTO->PrecioVenta = $productoVenta->PrecioVenta;
                    $productoDetalleDTO->MontoDivisa = $productoVenta->MontoDivisa;
                    $productoDetalleDTO->CostoDivisa = $productoVenta->CostoDivisa;
                    $productoDetalleDTO->Costo = $productoVenta->Costo;
                    $productoDetalleDTO->Existencia = $productoVenta->Existencia;
                    $productoDetalleDTO->UsuarioId = $productoVenta->UsuarioId;
                    
                    $productoModel = $productosModel->get($productoVenta->ProductoId);
                    $productoSucursal = $productosSucursal->get($productoVenta->ProductoId);

                    // dd($productoSucursal);
                    
                    if ($productoModel) {
                        $productoDetalleDTO->Producto = (object) [
                            'ID' => $productoModel->ID,
                            'Codigo' => $productoModel->Codigo,
                            // 'Nombre' => $productoModel->Nombre,
                            'Descripcion' => $productoModel->Descripcion,
                            'UrlFoto' => $productoModel->UrlFoto,
                            // 'Categoria' => $productoModel->Categoria,
                            // 'Marca' => $productoModel->Marca,
                            'PvpBS' => $productoSucursal ? $productoSucursal->PvpBs : null,
                            'PvpDivisa' => $productoSucursal ? $productoSucursal->PvpDivisa : null,
                            'CostoBs' => $productoModel->CostoBs,
                            'CostoDivisa' => $productoModel->CostoDivisa
                        ];
                    }
                    
                    $listaDetalleDTO->push($productoDetalleDTO);
                }
                $item->ListadoProductosVentaDiaria = $listaDetalleDTO;
            }

            $listEdcUsuario = $ventaDiaria->ListaVentasDiarias->groupBy('UsuarioId');
            
            // dd($listEdcUsuario->first());

            foreach ($listEdcUsuario as $ventas) {
                $edcUsuario = new \stdClass();
                $edcUsuario->Ventas = new \stdClass();
                $edcUsuario->Ventas->ListaVentasDiarias = $ventas->values();
                $edcUsuario->Usuario = $ventas->first()->Usuario;
                $edcUsuario->FechaFin = $filtroFecha->fechaFin;
                $edcUsuario->FechaInicio = $filtroFecha->fechaInicio;
                $edcUsuario->UsuarioInternoId = $ventas->first()->Usuario->UsuarioId ?? $ventas->first()->Usuario->Id;

                // 🔴 CALCULAR TOTALES
                $edcUsuario->TotalUnidades = $ventas->sum('Cantidad');
                $edcUsuario->TotalVentas = $ventas->sum('TotalDivisa');

                // Variables para acumular totales de comisiones y productos excluidos
                $totalComisiones = 0;
                $totalProductosExcluidos = 0;
                $totalMontoLiberacion = 0;
                $totalCantidadExcluida = 0;

                // 🔴 OBTENER EL ID DEL VENDEDOR ACTUAL
                $vendedorId = $edcUsuario->UsuarioInternoId;

                $edcUsuario->ComisionesDivisas = $ventas->sum(function($venta) use (&$totalProductosExcluidos, &$totalMontoLiberacion, &$totalCantidadExcluida, &$vendedorId) {
                    // $productosVenta = $venta->ListadoProductosVentaDiaria;
                    // 🔴 FILTRAR SOLO LOS PRODUCTOS DE ESTE VENDEDOR
                    $productosVenta = collect($venta->ListadoProductosVentaDiaria)
                        ->filter(function($producto) use ($vendedorId) {
                            // Comparar el UsuarioId del producto con el ID del vendedor actual
                            return ($producto->UsuarioId ?? null) == $vendedorId;
                        });

                    $totalExcluir = 0;
                    $margenMinimo = 10;
                    $cantidadExcluida = 0;

                    // dd($productosVenta);
                    
                    foreach ($productosVenta as $productoVenta) {
                        $cantidad = $productoVenta->Cantidad;
                        $montoTotalProducto = $productoVenta->MontoDivisa;
                        $costoTotalProducto = $productoVenta->CostoDivisa;
                        
                        if ($costoTotalProducto <= 0 || $montoTotalProducto <= 0) {
                            continue;
                        }
                        
                        $precioUnitario = $montoTotalProducto / $cantidad;
                        $costoUnitario = $costoTotalProducto / $cantidad;
                        $margen = (($precioUnitario * 100) / $costoUnitario) - 100;
                        
                        if ($margen <= $margenMinimo) {
                            $totalExcluir += $montoTotalProducto;
                            $cantidadExcluida += $cantidad;
                        }
                    }
                    
                    $baseComision = $venta->TotalDivisa - $totalExcluir;
                    
                    $totalProductosExcluidos += $totalExcluir;
                    $totalMontoLiberacion += $baseComision;
                    $totalCantidadExcluida += $cantidadExcluida;
                    
                    if ($baseComision <= 0) {
                        return 0;
                    }
                    
                    return $baseComision * 0.01;
                });

                // Redondear después de la suma para que coincida con MontoLiberacion * 0.01
                $edcUsuario->ComisionesDivisas = round($totalMontoLiberacion * 0.01, 2);
                $edcUsuario->MontoExcluido = round($totalProductosExcluidos, 2);
                $edcUsuario->MontoLiberacion = round($totalMontoLiberacion, 2);
                $edcUsuario->ProductosExcluidos = $totalCantidadExcluida;

                $listaUsuarios->push($edcUsuario);
            }
            // dd($listaUsuarios[11]);
        } else {
            \Log::warning('No hay ventas en el período');
        }
        
        return $listaUsuarios;
    }

    private function obtenerVentasDiariasDeVendedor($filtroFecha, $sucursalId = null, $usuarioId = null)
    {
        
        $ventasDiariaPeriodo = new \stdClass();
        $ventasDiariaPeriodo->FechaInicio = $filtroFecha->fechaInicio;
        $ventasDiariaPeriodo->FechaFin = $filtroFecha->fechaFin;

        $query = DB::connection('sqlsrv')
            ->table('VentaVendedoresTotalizada as vt')
            ->select([
                'vt.ID',
                'vt.Fecha',
                'vt.SucursalId',
                'vt.TasaDeCambio',
                'vt.UsuarioId',
                DB::raw('0.00 as Saldo'),
                'vt.Cantidad',
                'vt.TotalBs',
                'vt.TotalDivisa',
                'vt.CostoDivisa',
                'vt.Estatus',
                DB::raw('null as ProveedorId')
            ])
            ->whereBetween('vt.Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
            ->when($sucursalId, function($q) use ($sucursalId) {
                return $q->where('vt.SucursalId', $sucursalId);
            })
            ->when($usuarioId, function($q) use ($usuarioId) {
                return $q->where('vt.UsuarioId', $usuarioId);
            })
            ->orderBy('vt.UsuarioId', 'desc');

        $listaVentas = $query->get();

        // OBTENER TODOS LOS IDs DE VENDEDORES ÚNICOS
        $vendedorIds = $listaVentas->pluck('UsuarioId')->unique()->toArray();
        
        // CARGAR TODOS LOS VENDEDORES DE UNA SOLA VEZ
        $vendedoresMap = [];
        
        // Buscar en Usuarios
        $usuarios = Usuario::whereIn('UsuarioId', $vendedorIds)->get();

        foreach ($usuarios as $usuario) {
            $sucursal = null;
            if ($usuario->SucursalId) {
                $sucursal = Sucursal::where('ID', $usuario->SucursalId)->first();
            }
            $vendedoresMap[$usuario->UsuarioId] = $this->generarVendedorDTO($usuario, $sucursal);
        }
        
        // Buscar en AspNetUsers los que no se encontraron en Usuarios
        $idsNoEncontrados = array_diff($vendedorIds, array_keys($vendedoresMap));
        if (!empty($idsNoEncontrados)) {
            $empleados = AspNetUser::whereIn('Id', $idsNoEncontrados)->get();

            foreach ($empleados as $empleado) {
                $sucursal = null;
                if ($empleado->SucursalId) {
                    $sucursal = Sucursal::where('ID', $empleado->SucursalId)->first();
                }
                $vendedoresMap[$empleado->Id] = $this->generarVendedorDTO($empleado, $sucursal);
            }
        }
        
        $listaDetalleDTO = collect();

        foreach ($listaVentas as $ventaDiaria) {
            $ventaDiariaDTO = new \stdClass();
            $ventaDiariaDTO->ID = $ventaDiaria->ID;
            $ventaDiariaDTO->Fecha = $ventaDiaria->Fecha;
            $ventaDiariaDTO->SucursalId = $ventaDiaria->SucursalId;
            $ventaDiariaDTO->TasaDeCambio = $ventaDiaria->TasaDeCambio;
            $ventaDiariaDTO->UsuarioId = $ventaDiaria->UsuarioId;
            $ventaDiariaDTO->Saldo = 0.00;
            $ventaDiariaDTO->Cantidad = $ventaDiaria->Cantidad;
            $ventaDiariaDTO->TotalBs = $ventaDiaria->TotalBs;
            $ventaDiariaDTO->TotalDivisa = $ventaDiaria->TotalDivisa;
            $ventaDiariaDTO->CostoDivisa = $ventaDiaria->CostoDivisa;
            $ventaDiariaDTO->Estatus = $ventaDiaria->Estatus;
            $ventaDiariaDTO->ProveedorId = null;
            
            $ventaDiariaDTO->Usuario = $vendedoresMap[$ventaDiaria->UsuarioId] ?? null;

            $listaDetalleDTO->push($ventaDiariaDTO);
        }

        $ventasDiariaPeriodo->ListaVentasDiarias = $listaDetalleDTO;

        return $ventasDiariaPeriodo;
    }

    private function obtenerVentasDetalladasDeVendedor($filtroFecha, $usuarioId, $sucursalId, $ventaId)
    {
        $query = DB::connection('sqlsrv')
            ->table('VentaVendedoresView')
            ->select([
                'ID',
                'ID as VentaId',
                'Fecha',
                'SucursalId',
                'ProductoId',
                'Cantidad',
                'PrecioVenta',
                'MontoDivisa',
                'UsuarioId',
                'CostoDivisa',
                'Costo',
                'Existencia'
            ])
            ->where('UsuarioId', $usuarioId)
            ->where('ID', $ventaId);

        if ($filtroFecha && $filtroFecha->fechaInicio && $filtroFecha->fechaFin) {
            $query->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);
        }

        $ventaProductos = $query->orderBy('ProductoId')->get();

        $listaDetalleDTO = collect();

        foreach ($ventaProductos as $productoVentaDiaria) {
            $productoDetalleDTO = new \stdClass();
            $productoDetalleDTO->ID = $productoVentaDiaria->ID;
            $productoDetalleDTO->VentaId = $productoVentaDiaria->VentaId;
            $productoDetalleDTO->Fecha = $productoVentaDiaria->Fecha;
            $productoDetalleDTO->SucursalId = $productoVentaDiaria->SucursalId;
            $productoDetalleDTO->ProductoId = $productoVentaDiaria->ProductoId;
            $productoDetalleDTO->Cantidad = $productoVentaDiaria->Cantidad;
            $productoDetalleDTO->PrecioVenta = $productoVentaDiaria->PrecioVenta;
            $productoDetalleDTO->MontoDivisa = $productoVentaDiaria->MontoDivisa;
            $productoDetalleDTO->UsuarioId = $productoVentaDiaria->UsuarioId;
            $productoDetalleDTO->CostoDivisa = $productoVentaDiaria->CostoDivisa;
            $productoDetalleDTO->Costo = $productoVentaDiaria->Costo;
            $productoDetalleDTO->Existencia = $productoVentaDiaria->Existencia;

            // Buscar producto (equivalente a BuscarProducto en .NET)
            $productoDetalleDTO->Producto = $this->buscarProducto($productoDetalleDTO->ProductoId, $productoDetalleDTO->SucursalId);

            $listaDetalleDTO->push($productoDetalleDTO);
        }

        return $listaDetalleDTO;
    }

    private function buscarProducto($productoId, $sucursalId)
    {
        $productoDto = null;

        if ($sucursalId != 0) {
            $producto = ProductoSucursal::where('ProductoId', $productoId)
                ->where('SucursalId', $sucursalId)
                ->where('Estatus', 1)
                ->with('producto')
                ->first();

            if ($producto && $producto->producto) {
                $productoDto = (object) [
                    'ID' => $producto->producto->ID,
                    'Codigo' => $producto->producto->Codigo,
                    'Nombre' => $producto->producto->Nombre,
                    'Descripcion' => $producto->producto->Descripcion,
                    'UrlFoto' => $producto->producto->UrlFoto,
                    'Categoria' => $producto->producto->Categoria,
                    'Marca' => $producto->producto->Marca,
                    'PvpBS' => $producto->PvpBS,
                    'PvpDivisa' => $producto->PvpDivisa,
                    'CostoBs' => $producto->producto->CostoBs,
                    'CostoDivisa' => $producto->producto->CostoDivisa
                ];
            }
        } else {
            $producto = Producto::where('ID', $productoId)->first();

            if ($producto) {
                $productoDto = (object) [
                    'ID' => $producto->ID,
                    'Codigo' => $producto->Codigo,
                    'Nombre' => $producto->Nombre,
                    'Descripcion' => $producto->Descripcion,
                    'UrlFoto' => $producto->UrlFoto,
                    'Categoria' => $producto->Categoria,
                    'Marca' => $producto->Marca,
                    'CostoBs' => $producto->CostoBs,
                    'CostoDivisa' => $producto->CostoDivisa
                ];
            }
        }

        return $productoDto;
    }

    public function verDetalleLiberalidad($id)
    {
        try {
            // Obtener el detalle de liberalidad con las relaciones
            $detalle = LiberalidadDetalle::with(['liberalidad'])
                ->where('LiberalidadDetalleId', $id)
                ->first();
            
            if (!$detalle) {
                return back()->with('error', 'No se encontró el detalle de liberalidad');
            }
            
            // Obtener la liberalidad padre
            $liberalidad = $detalle->liberalidad;
            
            // Obtener el usuario o empleado según corresponda
            $entidad = null;
            $nombreCompleto = 'N/A';
            $vendedorId = '';
            $fotoPerfil = '';
            $email = '';
            
            // Priorizar Usuario si existe UsuarioId
            if ($detalle->UsuarioId) {
                $entidad = Usuario::where('UsuarioId', $detalle->UsuarioId)->first();
                if ($entidad) {
                    $nombreCompleto = $entidad->NombreCompleto ?? 'N/A';
                    $vendedorId = $entidad->VendedorId ?? '';
                    $fotoPerfil = $entidad->FotoPerfil ?? '';
                    $email = $entidad->Email ?? '';
                }
            } 
            // Si no tiene UsuarioId, usar EmpleadoId
            elseif ($detalle->EmpleadoId) {
                $entidad = Empleado::where('Id', $detalle->EmpleadoId)->first();
                if ($entidad) {
                    $nombreCompleto = $entidad->NombreCompleto ?? 'N/A';
                    $vendedorId = $entidad->VendedorId ?? '';
                    $fotoPerfil = $entidad->FotoPerfil ?? '';
                    $email = $entidad->Email ?? '';
                }
            }
            
            // Obtener la sucursal si existe
            $sucursalNombre = 'N/A';
            if ($entidad && isset($entidad->SucursalId)) {
                $sucursal = \App\Models\Sucursal::find($entidad->SucursalId);
                $sucursalNombre = $sucursal ? $sucursal->Nombre : 'N/A';
            }
            
            // Calcular valores adicionales
            $disponible = ($detalle->MontoLiberalidad ?? 0) - ($detalle->Pago ?? 0);
            
            return view('cpanel.empleados.detalle_liberalidad', compact(
                'detalle', 
                'liberalidad', 
                'entidad',
                'nombreCompleto',
                'vendedorId',
                'fotoPerfil',
                'email',
                'sucursalNombre',
                'disponible'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error al ver detalle de liberalidad: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al cargar el detalle: ' . $e->getMessage());
        }
    }

    public function listado_empleados_bonos(Request $request)
    {
        try {
            // ============================================
            // 1. OBTENER EMPLEADOS DE AspNetUsers (ACTIVOS)
            // ============================================
            $aspNetUsers = DB::connection('sqlsrv')
                ->table('AspNetUsers as u')
                ->select([
                    'u.Id as id',
                    'u.UserName as user_name',
                    'u.Email as email',
                    'u.EsActivo as activo',
                    'u.PhoneNumber as telefono',
                    'u.NombreCompleto as nombre_completo',
                    'u.Direccion as direccion',
                    'u.FechaCreacion as fecha_creacion',
                    'u.FechaNacimiento as fecha_nacimiento',
                    'u.SucursalId as sucursal_id',
                    'u.FotoPerfil as foto_perfil',
                    'u.VendedorId as vendedor_id',
                    'u.ExternalId as external_id',
                    's.Nombre as sucursal_nombre',
                    DB::raw("'AspNetUser' as origen")
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID')
                ->where('u.EsActivo', 1)
                ->get();

            // ============================================
            // 2. OBTENER VENDEDORES DE Usuarios (ACTIVOS)
            // ============================================
            $usuariosTemp = DB::connection('sqlsrv')
                ->table('Usuarios as u')
                ->select([
                    'u.UsuarioId as id',
                    DB::raw("NULL as user_name"),
                    'u.Email as email',
                    'u.EsActivo as activo',
                    'u.PhoneNumber as telefono',
                    'u.NombreCompleto as nombre_completo',
                    'u.Direccion as direccion',
                    'u.FechaCreacion as fecha_creacion',
                    'u.FechaNacimiento as fecha_nacimiento',
                    'u.SucursalId as sucursal_id',
                    'u.FotoPerfil as foto_perfil',
                    'u.VendedorId as vendedor_id',
                    's.Nombre as sucursal_nombre',
                    DB::raw("'Usuario' as origen")
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID')
                ->where('u.EsActivo', 1)
                ->get();

            // ============================================
            // 3. COMBINAR Y ELIMINAR DUPLICADOS POR SUCURSAL
            // ============================================
            
            // Crear array para almacenar los mejores registros por (nombre + sucursal)
            $mejoresRegistros = [];
            
            // Procesar AspNetUsers primero (tienen prioridad si son más recientes)
            foreach ($aspNetUsers as $empleado) {
                $key = $empleado->nombre_completo . '|' . $empleado->sucursal_id;
                
                if (!isset($mejoresRegistros[$key])) {
                    $mejoresRegistros[$key] = $empleado;
                } else {
                    // Si ya existe, comparar fechas y quedarse con la más reciente
                    $fechaExistente = strtotime($mejoresRegistros[$key]->fecha_creacion);
                    $fechaNueva = strtotime($empleado->fecha_creacion);
                    
                    if ($fechaNueva > $fechaExistente) {
                        $mejoresRegistros[$key] = $empleado;
                    }
                }
            }
            
            // Procesar UsuariosTemp
            foreach ($usuariosTemp as $empleado) {
                $key = $empleado->nombre_completo . '|' . $empleado->sucursal_id;
                
                if (!isset($mejoresRegistros[$key])) {
                    $mejoresRegistros[$key] = $empleado;
                } else {
                    // Si ya existe, comparar fechas y quedarse con la más reciente
                    $fechaExistente = strtotime($mejoresRegistros[$key]->fecha_creacion);
                    $fechaNueva = strtotime($empleado->fecha_creacion);
                    
                    if ($fechaNueva > $fechaExistente) {
                        $mejoresRegistros[$key] = $empleado;
                    }
                }
            }
            
            // Convertir a colección
            $empleados = collect(array_values($mejoresRegistros));

            // ============================================
            // 4. OBTENER ROLES PARA CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                $empleado->rol_id = null;
                $empleado->rol_nombre = 'Sin rol';
                
                // Solo buscar rol si es de AspNetUser
                if ($empleado->origen === 'AspNetUser' && $empleado->id) {
                    $userRole = DB::connection('sqlsrv')
                        ->table('AspNetUserRoles')
                        ->where('UserId', $empleado->id)
                        ->first();
                    
                    if ($userRole) {
                        $role = DB::connection('sqlsrv')
                            ->table('AspNetRoles')
                            ->where('Id', $userRole->RoleId)
                            ->first();
                        
                        $empleado->rol_id = $userRole->RoleId;
                        $empleado->rol_nombre = $role ? $role->Name : 'Desconocido';
                    }
                }
                
                // Para vendedores temporales (origen = 'Usuario')
                if ($empleado->origen === 'Usuario') {
                    $empleado->rol_nombre = 'VENDEDOR';
                }
            }

            // ============================================
            // 5. AGREGAR INFORMACIÓN DEL ÚLTIMO BONO (VERSIÓN SIMPLIFICADA)
            // ============================================

            // Obtener IDs de empleados de sistema (AspNetUsers)
            $empleadosSistemaIds = $empleados->where('origen', 'AspNetUser')
                ->pluck('id')
                ->filter()
                ->toArray();

            // Obtener IDs de vendedores temporales (Usuarios)
            $vendedoresTemporalesIds = $empleados->where('origen', 'Usuario')
                ->pluck('id')
                ->filter()
                ->toArray();

            // Obtener últimos bonos para empleados de sistema
            $ultimosBonosSistema = [];
            if (!empty($empleadosSistemaIds)) {
                $idsString = implode("','", $empleadosSistemaIds);
                
                $ultimosBonosSistema = DB::connection('sqlsrv')
                    ->select("
                        SELECT EmpleadoId, FechaCreacion as ultimo_bono_fecha, 
                            MontoDivisa as ultimo_bono_monto_divisa, 
                            MontoBs as ultimo_bono_monto_bs,
                            TipoBono as ultimo_bono_tipo, Tasa as ultimo_bono_tasa,
                            EsPagado as ultimo_bono_pagado
                        FROM BonosEmpleados b1
                        WHERE EmpleadoId IN ('{$idsString}')
                        AND FechaCreacion = (
                            SELECT MAX(FechaCreacion) 
                            FROM BonosEmpleados b2 
                            WHERE b2.EmpleadoId = b1.EmpleadoId
                        )
                    ");
                
                $ultimosBonosSistema = collect($ultimosBonosSistema)->keyBy('EmpleadoId');
            }

            // Obtener últimos bonos para vendedores temporales
            $ultimosBonosTemporales = [];
            if (!empty($vendedoresTemporalesIds)) {
                $idsString = implode("','", $vendedoresTemporalesIds);
                
                $ultimosBonosTemporales = DB::connection('sqlsrv')
                    ->select("
                        SELECT UsuarioId, FechaCreacion as ultimo_bono_fecha, 
                            MontoDivisa as ultimo_bono_monto_divisa, 
                            MontoBs as ultimo_bono_monto_bs,
                            TipoBono as ultimo_bono_tipo, Tasa as ultimo_bono_tasa,
                            EsPagado as ultimo_bono_pagado
                        FROM BonosEmpleados b1
                        WHERE UsuarioId IN ('{$idsString}')
                        AND FechaCreacion = (
                            SELECT MAX(FechaCreacion) 
                            FROM BonosEmpleados b2 
                            WHERE b2.UsuarioId = b1.UsuarioId
                        )
                    ");
                
                $ultimosBonosTemporales = collect($ultimosBonosTemporales)->keyBy('UsuarioId');
            }

            // ============================================
            // 5.1 ASIGNAR LA INFORMACIÓN DEL BONO A CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                // Inicializar propiedades del bono como null
                $empleado->ultimo_bono_fecha = null;
                $empleado->ultimo_bono_monto_divisa = null;
                $empleado->ultimo_bono_monto_bs = null;
                $empleado->ultimo_bono_tipo = null;
                $empleado->ultimo_bono_tasa = null;
                $empleado->ultimo_bono_pagado = null;
                
                // Buscar el bono según el origen del empleado
                if ($empleado->origen === 'AspNetUser') {
                    $bono = $ultimosBonosSistema[$empleado->id] ?? null;
                } else {
                    $bono = $ultimosBonosTemporales[$empleado->id] ?? null;
                }
                
                if ($bono) {
                    $empleado->ultimo_bono_fecha = $bono->ultimo_bono_fecha;
                    $empleado->ultimo_bono_monto_divisa = $bono->ultimo_bono_monto_divisa;
                    $empleado->ultimo_bono_monto_bs = $bono->ultimo_bono_monto_bs;
                    $empleado->ultimo_bono_tipo = $bono->ultimo_bono_tipo;
                    $empleado->ultimo_bono_tasa = $bono->ultimo_bono_tasa;
                    $empleado->ultimo_bono_pagado = $bono->ultimo_bono_pagado;
                }
            }

            // ============================================
            // 6. ORDENAR POR NOMBRE
            // ============================================
            $empleados = $empleados->sortBy('nombre_completo')->values();

            // ============================================
            // 7. LOG PARA DEPURACIÓN
            // ============================================
            // \Log::info('📋 LISTADO DE EMPLEADOS PARA BONOS', [
            //     'total_empleados' => $empleados->count(),
            //     'bonos_asignados_sistema' => $ultimosBonosSistema->count(),
            //     'bonos_asignados_temporales' => $ultimosBonosTemporales->count()
            // ]);

            // dd($empleados);

            // ============================================
            // 8. CONFIGURAR MENÚ ACTIVO
            // ============================================
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Bonos'
            ]);

            // ============================================
            // 9. RETORNAR VISTA
            // ============================================
            return view('cpanel.empleados.personal_listado_bonos', compact('empleados'));

        } catch (\Exception $e) {
            \Log::error('Error en listado_empleados_bonos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de personal para bonos: ' . $e->getMessage());
        }
    }

    public function asignarBono(Request $request, $tipo, $id)
    {
        try {
            // ============================================
            // 1. OBTENER EMPLEADO
            // ============================================
            $empleado = null;
            
            if ($tipo == 'sistema') {
                $empleado = DB::connection('sqlsrv')
                    ->table('AspNetUsers')
                    ->where('Id', $id)
                    ->select('Id', 'NombreCompleto', 'SucursalId', 'ExternalId')
                    ->first();
                $empleadoId = $id;
                $usuarioId = $empleado->ExternalId ?? null;
            } else {
                $empleado = DB::connection('sqlsrv')
                    ->table('Usuarios')
                    ->where('UsuarioId', $id)
                    ->select('UsuarioId as Id', 'NombreCompleto', 'SucursalId')
                    ->first();
                $empleadoId = null;
                $usuarioId = $id;
            }
            
            if (!$empleado) {
                return back()->with('error', 'Empleado no encontrado');
            }
            
            // ============================================
            // 2. FILTRO DE FECHAS (ACTUALIZADO)
            // ============================================
            
            // Obtener período del request (formato: YYYY-MM)
            $periodo = $request->input('periodo', date('Y-m'));
            $partes = explode('-', $periodo);
            $anioSeleccionado = intval($partes[0]);
            $mesSeleccionado = intval($partes[1]);
            
            // Validar mes y año
            $mesSeleccionado = max(1, min(12, $mesSeleccionado));
            $anioSeleccionado = max(2000, min(2099, $anioSeleccionado));
            
            // ============================================
            // 3. OBTENER LISTADO DE BONOS DEL EMPLEADO
            // USANDO MesBono Y AnnoBono (NO FechaCreacion)
            // ============================================
            
            $bonos = DB::connection('sqlsrv')
                ->table('BonosEmpleados')
                ->select([
                    'ID',
                    'MesBono',
                    'AnnoBono',
                    'FechaCreacion',
                    'TipoBono',
                    'MontoBs',
                    'MontoDivisa',
                    'Tasa',
                    'EsPagado',
                    'Motivo'
                ])
                ->when($empleadoId, function($query) use ($empleadoId) {
                    return $query->where('EmpleadoId', $empleadoId);
                })
                ->when($usuarioId, function($query) use ($usuarioId) {
                    return $query->where('UsuarioId', $usuarioId);
                })
                ->where('MesBono', $mesSeleccionado)
                ->where('AnnoBono', $anioSeleccionado)
                ->orderBy('FechaCreacion', 'desc')
                ->get();
            
            // ============================================
            // 4. CALCULAR TOTALES
            // ============================================
            
            $totalBonos = $bonos->count();
            $totalMontoBs = $bonos->sum('MontoBs');
            $totalMontoDivisa = $bonos->sum('MontoDivisa');
            $bonosPendientes = $bonos->where('EsPagado', 0)->count();
            $bonosPagados = $bonos->where('EsPagado', 1)->count();
            
            // ============================================
            // 5. OBTENER TASA DE CAMBIO ACTUAL
            // ============================================
            
            $tasa = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->orderBy('ID', 'desc')
                ->first();
            
            // ============================================
            // 6. LISTA DE MESES (para mostrar en la vista)
            // ============================================
            
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            
            // ============================================
            // 7. RETORNAR VISTA CON DATOS
            // ============================================

            $sucursal = Sucursal::find($empleado->SucursalId);
            
            return view('cpanel.empleados.asignar_bono', compact(
                'empleado',
                'tipo',
                'empleadoId',
                'usuarioId',
                'tasa',
                'bonos',
                'mesSeleccionado',
                'anioSeleccionado',
                'meses',
                'totalBonos',
                'totalMontoBs',
                'totalMontoDivisa',
                'bonosPendientes',
                'bonosPagados', 
                'sucursal'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en asignarBono: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de asignación de bono: ' . $e->getMessage());
        }
    }

    public function guardarBono(Request $request)
    {
        try {
            // ============================================
            // 1. VALIDAR DATOS
            // ============================================
            $validated = $request->validate([
                'tipo' => 'required|in:sistema,temporal',
                'empleado_id' => 'nullable|string',
                'usuario_id' => 'nullable|string',
                'periodo_bono' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'tasa' => 'required|numeric|min:0',
                'tipo_bono' => 'required|in:BS,USD',
                'monto' => 'required|numeric|min:0.01',
                'motivo' => 'required|string|min:3|max:500'
            ]);

            // ============================================
            // 2. EXTRAER MES Y AÑO DEL PERÍODO
            // ============================================
            $partes = explode('-', $request->periodo_bono);
            $annoBono = intval($partes[0]);
            $mesBono = intval($partes[1]);

            // ============================================
            // 3. CALCULAR MONTOS
            // ============================================
            $tipoBono = $request->tipo_bono;
            $monto = $request->monto;
            $tasa = $request->tasa;
            
            if ($tipoBono == 'BS') {
                $montoBs = $monto;
                $montoDivisa = round($monto / $tasa, 2);
            } else {
                $montoDivisa = $monto;
                $montoBs = round($monto * $tasa, 2);
            }

            // ============================================
            // 4. CREAR EL BONO
            // ============================================
            $bono = BonoEmpleado::create([
                'MesBono' => $mesBono,
                'AnnoBono' => $annoBono,
                'FechaCreacion' => now(),
                'EmpleadoId' => $request->empleado_id,
                'UsuarioId' => $request->usuario_id,
                'TipoBono' => $tipoBono,
                'MontoBs' => $montoBs,
                'MontoDivisa' => $montoDivisa,
                'Tasa' => $tasa,
                'EsPagado' => 0,
                'Motivo' => $request->motivo
            ]);

            return redirect()->back()->with('success', 'Bono asignado correctamente');

        } catch (\Exception $e) {
            \Log::error('Error al guardar bono: ' . $e->getMessage());
            return back()->with('error', 'Error al asignar el bono: ' . $e->getMessage());
        }
    }

    public function eliminarBono(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $bono = BonoEmpleado::find($id);
            
            if (!$bono) {
                return response()->json(['success' => false, 'message' => 'Bono no encontrado']);
            }
            
            // Solo permitir eliminar si está pendiente
            if ($bono->EsPagado == 1) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar un bono ya pagado']);
            }
            
            $bono->delete();
            
            \Log::info('✅ Bono eliminado', ['bono_id' => $id]);
            
            return response()->json(['success' => true, 'message' => 'Bono eliminado correctamente']);
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar bono: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el bono']);
        }
    }

    public function listado_empleados_deducciones(Request $request)
    {
        try {
            // ============================================
            // 1. OBTENER EMPLEADOS DE AspNetUsers (ACTIVOS)
            // ============================================
            $aspNetUsers = DB::connection('sqlsrv')
                ->table('AspNetUsers as u')
                ->select([
                    'u.Id as id',
                    'u.UserName as user_name',
                    'u.Email as email',
                    'u.EsActivo as activo',
                    'u.PhoneNumber as telefono',
                    'u.NombreCompleto as nombre_completo',
                    'u.Direccion as direccion',
                    'u.FechaCreacion as fecha_creacion',
                    'u.FechaNacimiento as fecha_nacimiento',
                    'u.SucursalId as sucursal_id',
                    'u.FotoPerfil as foto_perfil',
                    'u.VendedorId as vendedor_id',
                    'u.ExternalId as external_id',
                    's.Nombre as sucursal_nombre',
                    DB::raw("'AspNetUser' as origen")
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID')
                ->where('u.EsActivo', 1)
                ->get();

            // ============================================
            // 2. OBTENER VENDEDORES DE Usuarios (ACTIVOS)
            // ============================================
            $usuariosTemp = DB::connection('sqlsrv')
                ->table('Usuarios as u')
                ->select([
                    'u.UsuarioId as id',
                    DB::raw("NULL as user_name"),
                    'u.Email as email',
                    'u.EsActivo as activo',
                    'u.PhoneNumber as telefono',
                    'u.NombreCompleto as nombre_completo',
                    'u.Direccion as direccion',
                    'u.FechaCreacion as fecha_creacion',
                    'u.FechaNacimiento as fecha_nacimiento',
                    'u.SucursalId as sucursal_id',
                    'u.FotoPerfil as foto_perfil',
                    'u.VendedorId as vendedor_id',
                    's.Nombre as sucursal_nombre',
                    DB::raw("'Usuario' as origen")
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID')
                ->where('u.EsActivo', 1)
                ->get();

            // ============================================
            // 3. COMBINAR Y ELIMINAR DUPLICADOS POR SUCURSAL
            // ============================================
            
            // Crear array para almacenar los mejores registros por (nombre + sucursal)
            $mejoresRegistros = [];
            
            // Procesar AspNetUsers primero (tienen prioridad si son más recientes)
            foreach ($aspNetUsers as $empleado) {
                $key = $empleado->nombre_completo . '|' . $empleado->sucursal_id;
                
                if (!isset($mejoresRegistros[$key])) {
                    $mejoresRegistros[$key] = $empleado;
                } else {
                    // Si ya existe, comparar fechas y quedarse con la más reciente
                    $fechaExistente = strtotime($mejoresRegistros[$key]->fecha_creacion);
                    $fechaNueva = strtotime($empleado->fecha_creacion);
                    
                    if ($fechaNueva > $fechaExistente) {
                        $mejoresRegistros[$key] = $empleado;
                    }
                }
            }
            
            // Procesar UsuariosTemp
            foreach ($usuariosTemp as $empleado) {
                $key = $empleado->nombre_completo . '|' . $empleado->sucursal_id;
                
                if (!isset($mejoresRegistros[$key])) {
                    $mejoresRegistros[$key] = $empleado;
                } else {
                    // Si ya existe, comparar fechas y quedarse con la más reciente
                    $fechaExistente = strtotime($mejoresRegistros[$key]->fecha_creacion);
                    $fechaNueva = strtotime($empleado->fecha_creacion);
                    
                    if ($fechaNueva > $fechaExistente) {
                        $mejoresRegistros[$key] = $empleado;
                    }
                }
            }
            
            // Convertir a colección
            $empleados = collect(array_values($mejoresRegistros));

            // ============================================
            // 4. OBTENER ROLES PARA CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                $empleado->rol_id = null;
                $empleado->rol_nombre = 'Sin rol';
                
                // Solo buscar rol si es de AspNetUser
                if ($empleado->origen === 'AspNetUser' && $empleado->id) {
                    $userRole = DB::connection('sqlsrv')
                        ->table('AspNetUserRoles')
                        ->where('UserId', $empleado->id)
                        ->first();
                    
                    if ($userRole) {
                        $role = DB::connection('sqlsrv')
                            ->table('AspNetRoles')
                            ->where('Id', $userRole->RoleId)
                            ->first();
                        
                        $empleado->rol_id = $userRole->RoleId;
                        $empleado->rol_nombre = $role ? $role->Name : 'Desconocido';
                    }
                }
                
                // Para vendedores temporales (origen = 'Usuario')
                if ($empleado->origen === 'Usuario') {
                    $empleado->rol_nombre = 'VENDEDOR';
                }
            }

            // ============================================
            // 5. AGREGAR INFORMACIÓN DEL ÚLTIMA DEDUCCION (VERSIÓN SIMPLIFICADA)
            // ============================================

            // Obtener IDs de empleados de sistema (AspNetUsers)
            $empleadosSistemaIds = $empleados->where('origen', 'AspNetUser')
                ->pluck('id')
                ->filter()
                ->toArray();

            // Obtener IDs de vendedores temporales (Usuarios)
            $vendedoresTemporalesIds = $empleados->where('origen', 'Usuario')
                ->pluck('id')
                ->filter()
                ->toArray();

            // Obtener últimas deducciones para empleados de sistema
            $ultimaDeduccionSistema = [];
            if (!empty($empleadosSistemaIds)) {
                $idsString = implode("','", $empleadosSistemaIds);
                
                $ultimaDeduccionSistema = DB::connection('sqlsrv')
                    ->select("
                        SELECT EmpleadoId, FechaCreacion as ultima_deduccion_fecha, 
                            MontoDivisa as ultima_deduccion_monto_divisa, 
                            MontoBs as ultima_deduccion_monto_bs,
                            TipoDeduccion as ultima_deduccion_tipo, Tasa as ultima_deduccion_tasa,
                            EsPagado as ultima_deduccion_pagado
                        FROM Deducciones b1
                        WHERE EmpleadoId IN ('{$idsString}')
                        AND FechaCreacion = (
                            SELECT MAX(FechaCreacion) 
                            FROM Deducciones b2 
                            WHERE b2.EmpleadoId = b1.EmpleadoId
                        )
                    ");
                
                $ultimaDeduccionSistema = collect($ultimaDeduccionSistema)->keyBy('EmpleadoId');
            }

            // Obtener últimas deducciones para vendedores temporales
            $ultimasDeduccionesTemporales = [];
            if (!empty($vendedoresTemporalesIds)) {
                $idsString = implode("','", $vendedoresTemporalesIds);
                
                $ultimasDeduccionesTemporales = DB::connection('sqlsrv')
                    ->select("
                        SELECT UsuarioId, FechaCreacion as ultima_deduccion_fecha, 
                            MontoDivisa as ultima_deduccion_monto_divisa, 
                            MontoBs as ultima_deduccion_monto_bs,
                            TipoDeduccion as ultima_deduccion_tipo, Tasa as ultima_deduccion_tasa,
                            EsPagado as ultima_deduccion_pagado
                        FROM Deducciones b1
                        WHERE UsuarioId IN ('{$idsString}')
                        AND FechaCreacion = (
                            SELECT MAX(FechaCreacion) 
                            FROM Deducciones b2 
                            WHERE b2.UsuarioId = b1.UsuarioId
                        )
                    ");
                
                $ultimasDeduccionesTemporales = collect($ultimasDeduccionesTemporales)->keyBy('UsuarioId');
            }

            // ============================================
            // 5.1 ASIGNAR LA INFORMACIÓN DE LA DEDUCCION A CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                // Inicializar propiedades de la deduccion como null
                $empleado->ultima_deduccion_fecha = null;
                $empleado->ultima_deduccion_monto_divisa = null;
                $empleado->ultima_deduccion_monto_bs = null;
                $empleado->ultima_deduccion_tipo = null;
                $empleado->ultima_deduccion_tasa = null;
                $empleado->ultima_deduccion_pagado = null;
                
                // Buscar el bono según el origen del empleado
                if ($empleado->origen === 'AspNetUser') {
                    $deduccion = $ultimaDeduccionSistema[$empleado->id] ?? null;
                } else {
                    $deduccion = $ultimasDeduccionesTemporales[$empleado->id] ?? null;
                }
                
                if ($deduccion) {
                    $empleado->ultima_deduccion_fecha = $deduccion->ultima_deduccion_fecha;
                    $empleado->ultima_deduccion_monto_divisa = $deduccion->ultima_deduccion_monto_divisa;
                    $empleado->ultima_deduccion_monto_bs = $deduccion->ultima_deduccion_monto_bs;
                    $empleado->ultima_deduccion_tipo = $deduccion->ultima_deduccion_tipo;
                    $empleado->ultima_deduccion_tasa = $deduccion->ultima_deduccion_tasa;
                    $empleado->ultima_deduccion_pagado = $deduccion->ultima_deduccion_pagado;
                }
            }

            // ============================================
            // 6. ORDENAR POR NOMBRE
            // ============================================
            $empleados = $empleados->sortBy('nombre_completo')->values();

            // dd($empleados);

            // ============================================
            // 7. LOG PARA DEPURACIÓN
            // ============================================
            // \Log::info('📋 LISTADO DE EMPLEADOS PARA BONOS', [
            //     'total_empleados' => $empleados->count(),
            //     'deducciones_sistema' => $ultimaDeduccionSistema->count(),
            //     'deducciones_temporales' => $ultimasDeduccionesTemporales->count()
            // ]);

            // ============================================
            // 8. CONFIGURAR MENÚ ACTIVO
            // ============================================
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Deducciones'
            ]);

            // ============================================
            // 9. RETORNAR VISTA
            // ============================================
            return view('cpanel.empleados.personal_listado_deducciones', compact('empleados'));

        } catch (\Exception $e) {
            \Log::error('Error en listado_empleados_deducciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de personal para deducciones: ' . $e->getMessage());
        }
    }

    public function asignarDeduccion(Request $request, $tipo, $id)
    {
        try {
            // ============================================
            // 1. OBTENER EMPLEADO
            // ============================================
            $empleado = null;
            
            if ($tipo == 'sistema') {
                $empleado = DB::connection('sqlsrv')
                    ->table('AspNetUsers')
                    ->where('Id', $id)
                    ->select('Id', 'NombreCompleto', 'SucursalId', 'ExternalId')
                    ->first();
                $empleadoId = $id;
                $usuarioId = $empleado->ExternalId ?? null;
            } else {
                $empleado = DB::connection('sqlsrv')
                    ->table('Usuarios')
                    ->where('UsuarioId', $id)
                    ->select('UsuarioId as Id', 'NombreCompleto', 'SucursalId')
                    ->first();
                $empleadoId = null;
                $usuarioId = $id;
            }
            
            if (!$empleado) {
                return back()->with('error', 'Empleado no encontrado');
            }
            
            // ============================================
            // 2. FILTRO DE FECHAS (ACTUALIZADO)
            // ============================================
            
            // Obtener período del request (formato: YYYY-MM)
            $periodo = $request->input('periodo', date('Y-m'));
            $partes = explode('-', $periodo);
            $anioSeleccionado = intval($partes[0]);
            $mesSeleccionado = intval($partes[1]);
            
            // Validar mes y año
            $mesSeleccionado = max(1, min(12, $mesSeleccionado));
            $anioSeleccionado = max(2000, min(2099, $anioSeleccionado));
            
            // ============================================
            // 3. OBTENER LISTADO DE DEDUCCIONES DEL EMPLEADO
            // ============================================
            
            // CORREGIDO: renombrar $deduccion a $deducciones (plural)
            $deducciones = DB::connection('sqlsrv')
                ->table('Deducciones')
                ->select([
                    'ID',
                    'MesDeduccion',
                    'AnnoDeduccion',
                    'FechaCreacion',
                    'TipoDeduccion',
                    'MontoBs',
                    'MontoDivisa',
                    'Tasa',
                    'EsPagado',
                    'Motivo'
                ])
                ->when($empleadoId, function($query) use ($empleadoId) {
                    return $query->where('EmpleadoId', $empleadoId);
                })
                ->when($usuarioId, function($query) use ($usuarioId) {
                    return $query->where('UsuarioId', $usuarioId);
                })
                ->where('MesDeduccion', $mesSeleccionado)
                ->where('AnnoDeduccion', $anioSeleccionado)
                ->orderBy('FechaCreacion', 'desc')
                ->get();
            
            // ============================================
            // 4. CALCULAR TOTALES
            // ============================================
            
            $totalDeducciones = $deducciones->count();
            $totalMontoBs = $deducciones->sum('MontoBs');
            $totalMontoDivisa = $deducciones->sum('MontoDivisa');
            $deduccionesPendientes = $deducciones->where('EsPagado', 0)->count();
            $deduccionesPagadas = $deducciones->where('EsPagado', 1)->count();
            
            // ============================================
            // 5. OBTENER TASA DE CAMBIO ACTUAL
            // ============================================
            
            $tasa = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->orderBy('ID', 'desc')
                ->first();
            
            // ============================================
            // 6. LISTA DE MESES (para mostrar en la vista)
            // ============================================
            
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            
            // ============================================
            // 7. OBTENER SUCURSAL
            // ============================================
            
            $sucursal = Sucursal::find($empleado->SucursalId);
            
            // ============================================
            // 8. RETORNAR VISTA CON DATOS
            // ============================================
            
            return view('cpanel.empleados.asignar_deduccion', compact(
                'empleado',
                'tipo',
                'empleadoId',
                'usuarioId',
                'tasa',
                'deducciones',        // CORREGIDO: plural
                'mesSeleccionado',
                'anioSeleccionado',
                'meses',
                'totalDeducciones',
                'totalMontoBs',
                'totalMontoDivisa',
                'deduccionesPendientes',
                'deduccionesPagadas', // CORREGIDO: Pagadas (femenino plural)
                'sucursal'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en asignarDeduccion: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de asignación de deducciones: ' . $e->getMessage());
        }
    }

    public function guardarDeduccion(Request $request)
    {
        try {
            // ============================================
            // 1. VALIDAR DATOS
            // ============================================
            $validated = $request->validate([
                'tipo' => 'required|in:sistema,temporal',
                'empleado_id' => 'nullable|string',
                'usuario_id' => 'nullable|string',
                'periodo_deduccion' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'tasa' => 'required|numeric|min:0',
                'tipo_deduccion' => 'required|in:BS,USD',
                'monto' => 'required|numeric|min:0.01',
                'motivo' => 'required|string|min:3|max:500'
            ]);

            // ============================================
            // 2. VERIFICAR QUE TENGA AL MENOS UN IDENTIFICADOR
            // ============================================
            if (empty($request->empleado_id) && empty($request->usuario_id)) {
                return back()->with('error', 'Debe especificar un empleado');
            }

            // ============================================
            // 3. EXTRAER MES Y AÑO DEL PERÍODO
            // ============================================
            $partes = explode('-', $request->periodo_deduccion);
            $annoDeduccion = intval($partes[0]);
            $mesDeduccion = intval($partes[1]);

            // ============================================
            // 4. CALCULAR MONTOS
            // ============================================
            $tipoDeduccion = $request->tipo_deduccion;
            $monto = $request->monto;
            $tasa = $request->tasa;
            
            if ($tipoDeduccion == 'BS') {
                $montoBs = $monto;
                $montoDivisa = round($monto / $tasa, 2);
            } else {
                $montoDivisa = $monto;
                $montoBs = round($monto * $tasa, 2);
            }

            // ============================================
            // 5. CREAR LA DEDUCCIÓN
            // ============================================
            $deduccion = Deduccion::create([
                'MesDeduccion' => $mesDeduccion,
                'AnnoDeduccion' => $annoDeduccion,
                'FechaCreacion' => now(),
                'EmpleadoId' => $request->empleado_id,
                'UsuarioId' => $request->usuario_id,
                'TipoDeduccion' => $tipoDeduccion,
                'MontoBs' => $montoBs,
                'MontoDivisa' => $montoDivisa,
                'Tasa' => $tasa,
                'EsPagado' => 0,
                'Motivo' => $request->motivo
            ]);

            // ============================================
            // 6. LOG DE ÉXITO
            // ============================================
            \Log::info('✅ Deducción asignada correctamente', [
                'deduccion_id' => $deduccion->ID,
                'empleado_id' => $request->empleado_id,
                'usuario_id' => $request->usuario_id,
                'tipo' => $tipoDeduccion,
                'monto_bs' => $montoBs,
                'monto_divisa' => $montoDivisa,
                'mes' => $mesDeduccion,
                'anio' => $annoDeduccion,
                'motivo' => $request->motivo
            ]);

            // ============================================
            // 7. REDIRECCIONAR CON MENSAJE DE ÉXITO
            // ============================================
            return redirect()->back()->with('success', 'Deducción asignada correctamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error al guardar deducción: ' . $e->getMessage());
            return back()->with('error', 'Error al asignar la deducción: ' . $e->getMessage());
        }
    }

    public function eliminarDeduccion(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $deduccion = Deduccion::find($id);
            
            if (!$deduccion) {
                return response()->json(['success' => false, 'message' => 'Deducción no encontrada']);
            }
            
            // Solo permitir eliminar si está pendiente
            if ($deduccion->EsPagado == 1) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar una deducción ya pagada']);
            }
            
            $deduccion->delete();
            
            \Log::info('✅ Deducción eliminada', ['deduccion_id' => $id]);
            
            return response()->json(['success' => true, 'message' => 'Deducción eliminada correctamente']);
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar deducción: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la deducción']);
        }
    }

    public function listado_empleados_prestamos(Request $request)
    {
        try {
            // ============================================
            // 1. OBTENER TODOS LOS EMPLEADOS DE AspNetUsers (ACTIVOS)
            // ============================================
            $empleados = DB::connection('sqlsrv')
                ->table('AspNetUsers as u')
                ->select([
                    'u.Id as id',
                    'u.UserName as user_name',
                    'u.Email as email',
                    'u.EsActivo as activo',
                    'u.PhoneNumber as telefono',
                    'u.NombreCompleto as nombre_completo',
                    'u.Direccion as direccion',
                    'u.FechaCreacion as fecha_creacion',
                    'u.FechaNacimiento as fecha_nacimiento',
                    'u.SucursalId as sucursal_id',
                    'u.FotoPerfil as foto_perfil',
                    'u.VendedorId as vendedor_id',
                    'u.ExternalId as external_id',
                    's.Nombre as sucursal_nombre',
                    DB::raw("'AspNetUser' as origen")
                ])
                ->leftJoin('Sucursales as s', 'u.SucursalId', '=', 's.ID')
                ->where('u.EsActivo', 1)
                ->get();

            // ============================================
            // 2. OBTENER ROLES PARA CADA EMPLEADO
            // ============================================
            foreach ($empleados as $empleado) {
                $empleado->rol_id = null;
                $empleado->rol_nombre = 'Sin rol';
                
                $userRole = DB::connection('sqlsrv')
                    ->table('AspNetUserRoles')
                    ->where('UserId', $empleado->id)
                    ->first();
                
                if ($userRole) {
                    $role = DB::connection('sqlsrv')
                        ->table('AspNetRoles')
                        ->where('Id', $userRole->RoleId)
                        ->first();
                    
                    $empleado->rol_id = $userRole->RoleId;
                    $empleado->rol_nombre = $role ? $role->Name : 'Desconocido';
                }
                
                // INICIALIZAR propiedades de préstamos para TODOS los empleados
                $empleado->prestamos = collect();
                $empleado->total_prestamos = 0;
                $empleado->monto_total_prestamo = 0;
            }

            // ============================================
            // 3. BUSCAR PRÉSTAMOS POR EMPLEADO (SOLO LOS QUE TIENEN)
            // ============================================
            
            // Obtener todos los IDs de empleados
            $empleadosIds = $empleados->pluck('id')->toArray();
            
            // Buscar TODOS los préstamos activos de TODOS los empleados en UNA sola consulta
            $todosLosPrestamos = DB::connection('sqlsrv')
                ->table('Prestamos')
                ->whereIn('UsuarioId', $empleadosIds)
                ->where(function($query) {
                    $query->where('Estatus', 1)
                        ->orWhere('Estatus', 2)
                        ->orWhere('Estatus', 4);
                })
                ->orderBy('Fecha', 'desc')
                ->get()
                ->groupBy('UsuarioId');  // Agrupar por UsuarioId
            
            // Obtener todos los PrestamoIds de todos los préstamos encontrados
            $todosLosPrestamoIds = $todosLosPrestamos->flatMap(function($prestamos) {
                return $prestamos->pluck('PrestamoId');
            })->toArray();
            
            // Buscar TODOS los detalles y productos en UNA sola consulta
            $todosLosDetalles = collect();
            if (!empty($todosLosPrestamoIds)) {
                $todosLosDetalles = DB::connection('sqlsrv')
                    ->table('PrestamosDetalles as pd')
                    ->leftJoin('Productos as p', 'pd.ProductoId', '=', 'p.ID')
                    ->whereIn('pd.PrestamoId', $todosLosPrestamoIds)
                    ->select(
                        'pd.PrestamoId',
                        'pd.ProductoId',
                        'pd.Cantidad',
                        'pd.PvpBs',
                        'pd.PvpDivisa',
                        'p.ID as ProductoID',
                        'p.Descripcion as ProductoDescripcion',
                        'p.Codigo as ProductoCodigo',
                        'p.Referencia as ProductoReferencia'
                    )
                    ->get()
                    ->groupBy('PrestamoId');
            }
            
            // Buscar TODOS los pagos/abonos en UNA sola consulta
            $todosLosPagos = collect();
            if (!empty($todosLosPrestamoIds)) {
                $todosLosPagos = DB::connection('sqlsrv')
                    ->table('Transacciones as t')
                    ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
                    ->whereIn('tu.PrestamoId', $todosLosPrestamoIds)
                    ->select(
                        'tu.PrestamoId',
                        't.ID as TransaccionId',
                        't.Fecha',
                        't.MontoAbonado as Monto',
                        't.MontoDivisaAbonado as MontoDivisa',
                        't.Tipo as TipoTransaccion',
                        't.Observacion',
                        't.Estatus',
                        't.Descripcion',
                        't.FormaDePago',
                        't.NumeroOperacion'
                    )
                    ->orderBy('t.Fecha', 'desc')
                    ->get()
                    ->groupBy('PrestamoId');
            }
            
            // Procesar cada empleado que TIENE préstamos
            foreach ($todosLosPrestamos as $usuarioId => $prestamos) {
                // Encontrar el empleado correspondiente
                $empleado = $empleados->firstWhere('id', $usuarioId);
                
                if ($empleado) {
                    $prestamoIds = $prestamos->pluck('PrestamoId')->toArray();
                    $totalDivisa = 0;
                    
                    foreach ($prestamos as $prestamo) {
                        // Sumar MontoDivisa del préstamo
                        $montoDivisa = floatval($prestamo->MontoDivisa ?? 0);
                        $totalDivisa += $montoDivisa;
                        
                        // Obtener detalles de este préstamo
                        $detalles = isset($todosLosDetalles[$prestamo->PrestamoId]) 
                            ? $todosLosDetalles[$prestamo->PrestamoId] 
                            : collect();
                        
                        // Sumar PvpDivisa de los detalles
                        if ($detalles->count() > 0) {
                            $sumaDetalles = $detalles->sum('PvpDivisa');
                            $totalDivisa += $sumaDetalles;
                        }
                        
                        // Obtener pagos de este préstamo
                        $pagos = isset($todosLosPagos[$prestamo->PrestamoId]) 
                            ? $todosLosPagos[$prestamo->PrestamoId] 
                            : collect();
                        
                        // Agregar toda la información al objeto préstamo
                        $prestamo->detalles = $detalles;
                        $prestamo->total_detalles = $detalles->count();
                        $prestamo->total_detalles_divisa = $detalles->sum('PvpDivisa');
                        
                        $prestamo->pagos = $pagos;
                        $prestamo->total_pagos = $pagos->count();
                        $prestamo->total_pagado_divisa = $pagos->sum('MontoDivisa');
                        $prestamo->total_pagado_bs = $pagos->sum('Monto');
                        $prestamo->MontoDivisa = $montoDivisa;
                        
                        // Calcular saldo pendiente
                        $prestamo->saldo_pendiente_divisa = ($prestamo->MontoDivisa + $prestamo->total_detalles_divisa) - $prestamo->total_pagado_divisa;
                    }
                    
                    $empleado->prestamos = $prestamos;
                    $empleado->total_prestamos = $prestamos->count();
                    $empleado->monto_total_prestamo = $totalDivisa;
                }
            }
            
            // Ordenar empleados por nombre
            $empleados = $empleados->sortBy('nombre_completo')->values();

            // dd($empleados);

            // ============================================
            // 4. LOG PARA DEPURACIÓN
            // ============================================
            // \Log::info('📋 LISTADO DE EMPLEADOS (CON Y SIN PRÉSTAMOS)', [
            //     'total_empleados_activos' => $empleados->count(),
            //     'total_con_prestamos' => $empleados->filter(function($emp) {
            //         return $emp->total_prestamos > 0;
            //     })->count(),
            //     'total_sin_prestamos' => $empleados->filter(function($emp) {
            //         return $emp->total_prestamos == 0;
            //     })->count()
            // ]);

            // ============================================
            // 5. CONFIGURAR MENÚ ACTIVO
            // ============================================
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Prestamos'
            ]);

            // ============================================
            // 6. RETORNAR VISTA CON TODOS LOS EMPLEADOS
            // ============================================
            return view('cpanel.empleados.personal_listado_prestamos', compact('empleados'));

        } catch (\Exception $e) {
            \Log::error('Error en listado_empleados_prestamos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de préstamos: ' . $e->getMessage());
        }
    }

    private function buscarPrestamosPorEmpleado($usuarioId, $estatus)
    {
        if (!$usuarioId) {
            return collect();
        }
        
        return Prestamo::with(['usuarioSistema', 'detalles', 'sucursal'])
            ->where('UsuarioId', $usuarioId)
            ->where(function($query) use ($estatus) {
                $query->where('Estatus', $estatus)
                    ->orWhere('Estatus', 4);  // Como en .NET
            })
            ->orderBy('Fecha', 'asc')
            ->get();
    }

    private function obtenerPrestamosPorEmpleado($usuarioId)
    {
        if (!$usuarioId) {
            return collect();
        }
        
        $prestamos = collect();
        
        // Buscar préstamos NUEVOS (Estatus = 1)
        $nuevos = $this->buscarPrestamosPorEmpleado($usuarioId, 1);
        if ($nuevos && $nuevos->count() > 0) {
            $prestamos = $prestamos->concat($nuevos);
        }
        
        // Buscar préstamos EN PROCESO (Estatus = 2)
        $enProceso = $this->buscarPrestamosPorEmpleado($usuarioId, 2);
        if ($enProceso && $enProceso->count() > 0) {
            $prestamos = $prestamos->concat($enProceso);
        }
        
        // Ordenar por fecha ascendente
        return $prestamos->sortBy('Fecha')->values();
    }

    public function obtenerBonosDisponiblesPrestamo($usuarioId)
    {
        \Log::info('=== obtenerBonosDisponiblesPrestamo ===');
        \Log::info('usuarioId: ' . $usuarioId);
        
        try {
            $mesActual = date('n');
            $anioActual = date('Y');
            
            // Buscar el empleado para obtener sus IDs
            $empleado = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('Id', $usuarioId)
                ->first();
            
            $usuarioIdNum = $empleado->ExternalId ?? null;
            $empleadoIdGuid = $empleado->Id;
            
            // Usar la función existente para obtener bonos
            $bonos = $this->obtenerBonosPorEmpleado($usuarioIdNum, $empleadoIdGuid, $mesActual, $anioActual);
            
            // Filtrar solo bonos pendientes (EsPagado = 0)
            $bonosPendientes = $bonos->filter(function($bono) {
                return $bono->EsPagado == 0;
            })->values();
            
            $totalBonosDisponibles = $bonosPendientes->sum('MontoDivisa');

            // ============================================
            // OBTENER LIBERALIDAD ACTUAL DEL EMPLEADO
            // ============================================
            $liberalidadData = $this->obtenerLiberalidadEmpleado($usuarioIdNum, $empleadoIdGuid, $mesActual, $anioActual);
            
            if ($liberalidadData && isset($liberalidadData->detalles)) {
                // Buscar el detalle específico del empleado para obtener MontoLiberalidad
                $detalleEmpleado = null;
                
                foreach ($liberalidadData->detalles as $detalle) {
                    // Buscar por UsuarioId (numérico)
                    if ($usuarioIdNum && isset($detalle->UsuarioId) && $detalle->UsuarioId == $usuarioIdNum) {
                        $detalleEmpleado = $detalle;
                        break;
                    }
                    // Buscar por EmpleadoId (GUID)
                    if ($empleadoIdGuid && isset($detalle->EmpleadoId) && $detalle->EmpleadoId == $empleadoIdGuid) {
                        $detalleEmpleado = $detalle;
                        break;
                    }
                }
                
                // Inicializar variables
                $montoLiberalidadDivisa = 0;
                $montoDescuentoDivisa = 0;
                $disponibleLiberalidadDivisa = 0;
                
                if ($detalleEmpleado) {
                    $montoLiberalidadDivisa = $detalleEmpleado->MontoLiberalidad ?? 0;
                    
                    // SOLO si el monto es mayor que 0, crear o actualizar
                    if ($montoLiberalidadDivisa > 0) {
                        // Buscar si ya existe un registro en LiberalidadEmpleadoTempo
                        $registroLiberalidad = DB::connection('sqlsrv')
                            ->table('LiberalidadEmpleadoTempo')
                            ->where('Mes', $mesActual)
                            ->where('Anno', $anioActual)
                            ->where(function($query) use ($empleadoIdGuid, $usuarioIdNum) {
                                if ($empleadoIdGuid) {
                                    $query->where('EmpleadoId', $empleadoIdGuid);
                                }
                                if ($usuarioIdNum) {
                                    $query->orWhere('UsuarioId', $usuarioIdNum);
                                }
                            })
                            ->first();
                        
                        if ($registroLiberalidad) {
                            // Actualizar solo MontoLiberalidadDivisa
                            DB::connection('sqlsrv')
                                ->table('LiberalidadEmpleadoTempo')
                                ->where('Id', $registroLiberalidad->Id)
                                ->update([
                                    'MontoLiberalidadDivisa' => $montoLiberalidadDivisa,
                                    'FechaCreacion' => now()
                                ]);
                            
                            // Obtener los valores actualizados
                            $montoDescuentoDivisa = $registroLiberalidad->MontoDescuentoDivisa ?? 0;
                            $disponibleLiberalidadDivisa = $registroLiberalidad->DisponibleLiberalidadDivisa ?? 0;
                            
                            \Log::info('Registro de liberalidad actualizado', [
                                'empleado' => $empleadoIdGuid,
                                'monto_liberalidad_divisa' => $montoLiberalidadDivisa,
                                'monto_descuento_divisa' => $montoDescuentoDivisa,
                                'disponible_liberalidad_divisa' => $disponibleLiberalidadDivisa
                            ]);
                        } else {
                            // Crear nuevo registro (los demás campos quedan en 0)
                            DB::connection('sqlsrv')
                                ->table('LiberalidadEmpleadoTempo')
                                ->insert([
                                    'Mes' => $mesActual,
                                    'Anno' => $anioActual,
                                    'EmpleadoId' => $empleadoIdGuid,
                                    'UsuarioId' => $usuarioIdNum,
                                    'FechaCreacion' => now(),
                                    'MontoLiberalidadDivisa' => $montoLiberalidadDivisa,
                                    'MontoLiberalidadBs' => 0,
                                    'MontoDescuentoDivisa' => 0,
                                    'MontoDescuentoBs' => 0,
                                    'DisponibleLiberalidadDivisa' => $montoLiberalidadDivisa,
                                    'DisponibleLiberalidadBs' => 0,
                                    'Tasa' => 0
                                ]);
                            
                            \Log::info('Registro de liberalidad creado', [
                                'empleado' => $empleadoIdGuid,
                                'monto_liberalidad_divisa' => $montoLiberalidadDivisa
                            ]);
                        }
                    } else {
                        // Si el monto es 0, buscar el registro existente para obtener sus valores
                        $registroLiberalidad = DB::connection('sqlsrv')
                            ->table('LiberalidadEmpleadoTempo')
                            ->where('Mes', $mesActual)
                            ->where('Anno', $anioActual)
                            ->where(function($query) use ($empleadoIdGuid, $usuarioIdNum) {
                                if ($empleadoIdGuid) {
                                    $query->where('EmpleadoId', $empleadoIdGuid);
                                }
                                if ($usuarioIdNum) {
                                    $query->orWhere('UsuarioId', $usuarioIdNum);
                                }
                            })
                            ->first();
                        
                        if ($registroLiberalidad) {
                            $montoDescuentoDivisa = $registroLiberalidad->MontoDescuentoDivisa ?? 0;
                            $disponibleLiberalidadDivisa = $registroLiberalidad->DisponibleLiberalidadDivisa ?? 0;
                        }
                        
                        \Log::info('MontoLiberalidad es 0, no se crea/actualiza registro', [
                            'empleado' => $empleadoIdGuid,
                            'monto_liberalidad_divisa' => $montoLiberalidadDivisa,
                            'monto_descuento_divisa' => $montoDescuentoDivisa,
                            'disponible_liberalidad_divisa' => $disponibleLiberalidadDivisa
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'bonos' => $bonosPendientes,
                    'total_bonos_disponibles' => $totalBonosDisponibles,
                    'liberalidad' => [
                        'monto' => floatval($montoLiberalidadDivisa ?? 0),
                        'monto_descuento' => floatval($montoDescuentoDivisa ?? 0),
                        'disponible' => floatval($disponibleLiberalidadDivisa ?? 0),
                        'tiene_liberalidad' => ($montoLiberalidadDivisa ?? 0) > 0
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en obtenerBonosDisponiblesPrestamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener bonos disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerLiberalidadEmpleado($usuarioIdNum, $empleadoIdGuid, $mes, $anio)
    {
        try {
            // Calcular fechas de inicio y fin del mes
            $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->startOfDay();
            $fechaFin = $fechaInicio->copy()->endOfMonth()->endOfDay();
            
            // Crear filtro de fechas
            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                $fechaInicio,
                $fechaFin
            );
            
            // Crear objeto liberalidad
            $liberalidadDTO = LiberalidadDTO::empty();
            $liberalidadDTO->detalles = collect();
            
            $externalId = $usuarioIdNum ?? $empleadoIdGuid;
            $liberalidadDTO->detalles = $this->obtenerVentaDetalladaPorUsuario($filtroFecha, $externalId);
            
            // Asignar datos del filtro al DTO
            $liberalidadDTO->Mes = $filtroFecha->mes->value;
            $liberalidadDTO->Anno = $filtroFecha->anno;
            $liberalidadDTO->FechaInicio = $filtroFecha->fechaInicio;
            $liberalidadDTO->FechaFinal = $filtroFecha->fechaFin;
            $liberalidadDTO->Estatus = 0;
            
            // Buscar liberalidad de empleados
            $liberalidadDTO = $this->buscarLiberalidadEmpleados($liberalidadDTO);

            return $liberalidadDTO;
            
        } catch (\Exception $e) {
            \Log::error('Error en obtenerLiberalidadEmpleado: ' . $e->getMessage());
            return null;
        }
    }

    public function registrarPagoNormal(Request $request)
    {
        try {
            $request->validate([
                'usuarioId' => 'required|string',
                'Fecha' => 'required|date',
                'Descripcion' => 'required|string',
                'MontoDivisaAbonado' => 'required|numeric|min:0.01',
                'FormaDePago' => 'required|integer|in:0,2,3',
                'Observacion' => 'nullable|string',
                'NumeroOperacion' => 'nullable|string'
            ]);
            
            $usuarioId = $request->usuarioId;
            $montoTotal = $request->MontoDivisaAbonado;
            
            // Obtener TODOS los préstamos activos del empleado (más antiguos primero)
            $prestamos = DB::connection('sqlsrv')
                ->table('Prestamos')
                ->where('UsuarioId', $usuarioId)
                ->whereIn('Estatus', [1, 2])
                ->orderBy('Fecha', 'asc')
                ->get();
            
            if ($prestamos->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No hay préstamos activos'], 400);
            }
            
            // Obtener tasa de cambio según la fecha
            $tasaCambio = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->whereDate('Fecha', $request->Fecha)
                ->orderBy('ID', 'desc')
                ->first();
            
            if (!$tasaCambio) {
                $tasaCambio = DB::connection('sqlsrv')
                    ->table('DivisaValor')
                    ->orderBy('ID', 'desc')
                    ->first();
            }
            
            $tasaValor = $tasaCambio->Valor ?? 475.01;
            $tasaId = $tasaCambio->ID ?? null;
            
            $montoRestante = $montoTotal;
            $prestamosAfectados = [];
            $transaccionesRegistradas = [];
            
            foreach ($prestamos as $prestamo) {
                if ($montoRestante <= 0) break;
                
                // Calcular saldo pendiente del préstamo
                $detalles = DB::connection('sqlsrv')
                    ->table('PrestamosDetalles')
                    ->where('PrestamoId', $prestamo->PrestamoId)
                    ->sum('PvpDivisa');
                
                $pagos = DB::connection('sqlsrv')
                    ->table('Transacciones as t')
                    ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
                    ->where('tu.PrestamoId', $prestamo->PrestamoId)
                    ->sum('t.MontoDivisaAbonado');
                
                $totalPrestamo = floatval($prestamo->MontoDivisa) + floatval($detalles);
                $saldoPendiente = $totalPrestamo - floatval($pagos);
                
                if ($saldoPendiente <= 0) {
                    continue; // Este préstamo ya está pagado, pasar al siguiente
                }
                
                // Calcular cuánto se paga de este préstamo
                $montoAPagar = min($montoRestante, $saldoPendiente);
                $montoAPagarBs = round($montoAPagar * $tasaValor, 2);
                
                // Generar número de operación para este préstamo
                $numeroOperacion = $request->NumeroOperacion;
                if (empty($numeroOperacion)) {
                    $numeroOperacion = 'ABP' . date('Ymd') . '-' . $prestamo->PrestamoId;
                }
                
                // Crear descripción con prefijo "Auto."
                $descripcion = $request->Descripcion;
                if (!str_contains($descripcion, 'Auto.')) {
                    $descripcion = 'Auto.' . $descripcion;
                }
                
                // Observación
                $observacion = $request->Observacion;
                if (empty($observacion)) {
                    $observacion = $descripcion;
                }
                
                // Registrar transacción para este préstamo
                $transaccionId = DB::connection('sqlsrv')->table('Transacciones')->insertGetId([
                    'Fecha' => $request->Fecha,
                    'Descripcion' => $descripcion,
                    'DivisaId' => 1,
                    'MontoDivisaAbonado' => $montoAPagar,
                    'NumeroOperacion' => $numeroOperacion,
                    'Estatus' => 2,
                    'FormaDePago' => $request->FormaDePago,
                    'SucursalId' => $prestamo->SucursalId,
                    'TasaDeCambio' => $tasaValor,
                    'MontoAbonado' => $montoAPagarBs,
                    'Tipo' => 4,
                    'Observacion' => $observacion,
                    'UrlComprobante' => null,
                    'Nombre' => null,
                    'Cedula' => null
                ]);
                
                // Relacionar transacción con préstamo y usuario
                DB::connection('sqlsrv')->table('TransaccionesUsuario')->insert([
                    'UsuarioId' => $usuarioId,
                    'TransaccionId' => $transaccionId,
                    'PrestamoId' => $prestamo->PrestamoId
                ]);
                
                $transaccionesRegistradas[] = $transaccionId;
                $prestamosAfectados[] = [
                    'PrestamoId' => $prestamo->PrestamoId,
                    'montoPagado' => $montoAPagar,
                    'saldoAnterior' => $saldoPendiente,
                    'nuevoSaldo' => $saldoPendiente - $montoAPagar
                ];
                
                // Verificar si este préstamo quedó pagado completamente
                if ($montoAPagar >= $saldoPendiente) {
                    DB::connection('sqlsrv')
                        ->table('Prestamos')
                        ->where('PrestamoId', $prestamo->PrestamoId)
                        ->update(['Estatus' => 3]);
                }
                
                $montoRestante -= $montoAPagar;
            }
            
            $totalPagado = array_sum(array_column($prestamosAfectados, 'montoPagado'));
            $todosPagados = $montoRestante <= 0 && count($prestamosAfectados) == count($prestamos);

            $message = "Se ha registrado el pago de {$totalPagado} USD";
            if ($todosPagados) {
                $message = "Todos los préstamos han sido pagados completamente. Total: {$totalPagado} USD";
            } elseif ($montoRestante > 0) {
                $message = "Se pagaron {$totalPagado} USD. Quedan {$montoRestante} USD por aplicar (no hay más préstamos activos)";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'transacciones' => $transaccionesRegistradas,
                    'prestamos_afectados' => $prestamosAfectados,
                    'monto_total_pagado' => $totalPagado,
                    'monto_restante' => $montoRestante
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en registrarPagoNormal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarPagoConBono(Request $request)
    {
        try {
            $request->validate([
                'usuarioId' => 'required|string',
                'monto' => 'required|numeric|min:0.01',
                'bono_id' => 'required|integer',
                'fecha' => 'required|date',
                'descripcion' => 'nullable|string',
                'observacion' => 'nullable|string',
                'numero_operacion' => 'nullable|string'
            ]);
            
            $usuarioId = $request->usuarioId;
            $montoTotal = $request->monto;
            $bonoId = $request->bono_id;
            $fecha = $request->fecha;
            $descripcion = $request->descripcion ?: "Pago de préstamo con bono #{$bonoId}";
            $observacion = $request->observacion;
            $numeroOperacionBase = $request->numero_operacion;
            
            // Obtener el empleado
            $empleado = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('Id', $usuarioId)
                ->first();
            
            if (!$empleado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 400);
            }
            
            // Obtener TODOS los préstamos activos del empleado (más antiguos primero)
            $prestamos = DB::connection('sqlsrv')
                ->table('Prestamos')
                ->where('UsuarioId', $usuarioId)
                ->whereIn('Estatus', [1, 2])
                ->orderBy('Fecha', 'asc')
                ->get();
            
            if ($prestamos->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No hay préstamos activos'], 400);
            }
            
            // Obtener tasa de cambio del día
            $tasaCambio = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->whereDate('Fecha', $fecha)
                ->orderBy('ID', 'desc')
                ->first();
            
            if (!$tasaCambio) {
                $tasaCambio = DB::connection('sqlsrv')
                    ->table('DivisaValor')
                    ->orderBy('ID', 'desc')
                    ->first();
            }
            
            $tasaValor = $tasaCambio->Valor ?? 475.01;
            
            // Verificar bono
            $usuarioIdNum = $empleado->ExternalId ?? null;
            $empleadoIdGuid = $empleado->Id;
            $mesActual = date('n');
            $anioActual = date('Y');
            
            $bonos = $this->obtenerBonosPorEmpleado($usuarioIdNum, $empleadoIdGuid, $mesActual, $anioActual);
            $bono = $bonos->firstWhere('ID', $bonoId);
            
            if (!$bono || $bono->EsPagado == 1) {
                return response()->json(['success' => false, 'message' => 'Bono no encontrado o ya fue pagado'], 400);
            }
            
            if ($montoTotal > $bono->MontoDivisa) {
                return response()->json(['success' => false, 'message' => 'El monto excede el bono disponible'], 400);
            }
            
            $montoRestante = $montoTotal;
            $prestamosAfectados = [];
            $transaccionesRegistradas = [];
            $montoTotalUsadoBono = 0;
            
            foreach ($prestamos as $prestamo) {
                if ($montoRestante <= 0) break;
                
                // Calcular saldo pendiente del préstamo
                $detalles = DB::connection('sqlsrv')
                    ->table('PrestamosDetalles')
                    ->where('PrestamoId', $prestamo->PrestamoId)
                    ->sum('PvpDivisa');
                
                $pagos = DB::connection('sqlsrv')
                    ->table('Transacciones as t')
                    ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
                    ->where('tu.PrestamoId', $prestamo->PrestamoId)
                    ->sum('t.MontoDivisaAbonado');
                
                $totalPrestamo = floatval($prestamo->MontoDivisa) + floatval($detalles);
                $saldoPendiente = $totalPrestamo - floatval($pagos);
                
                if ($saldoPendiente <= 0) {
                    continue;
                }
                
                // Calcular cuánto se paga de este préstamo
                $montoAPagar = min($montoRestante, $saldoPendiente);
                $montoAPagarBs = round($montoAPagar * $tasaValor, 2);
                
                // Generar número de operación para este préstamo
                $numeroOperacion = $numeroOperacionBase;
                if (empty($numeroOperacion)) {
                    $numeroOperacion = 'ABP' . date('Ymd') . '-' . $prestamo->PrestamoId;
                }
                
                // Crear descripción con prefijo "Auto."
                $descripcionFinal = $descripcion;
                if (!str_contains($descripcionFinal, 'Auto.')) {
                    $descripcionFinal = 'Auto.' . $descripcionFinal;
                }
                
                // Observación
                $observacionFinal = $observacion;
                if (empty($observacionFinal)) {
                    $observacionFinal = $descripcionFinal;
                }
                
                // Registrar transacción para este préstamo
                $transaccionId = DB::connection('sqlsrv')->table('Transacciones')->insertGetId([
                    'Fecha' => $fecha,
                    'Descripcion' => $descripcionFinal,
                    'DivisaId' => 1,
                    'MontoDivisaAbonado' => $montoAPagar,
                    'NumeroOperacion' => $numeroOperacion,
                    'Estatus' => 2,
                    'FormaDePago' => 3,
                    'SucursalId' => $prestamo->SucursalId,
                    'TasaDeCambio' => $tasaValor,
                    'MontoAbonado' => $montoAPagarBs,
                    'Tipo' => 4,
                    'Observacion' => $observacionFinal,
                    'UrlComprobante' => null,
                    'Nombre' => null,
                    'Cedula' => null
                ]);
                
                // Relacionar transacción con préstamo y usuario
                DB::connection('sqlsrv')->table('TransaccionesUsuario')->insert([
                    'UsuarioId' => $usuarioId,
                    'TransaccionId' => $transaccionId,
                    'PrestamoId' => $prestamo->PrestamoId
                ]);
                
                $transaccionesRegistradas[] = $transaccionId;
                $prestamosAfectados[] = [
                    'PrestamoId' => $prestamo->PrestamoId,
                    'montoPagado' => $montoAPagar,
                    'saldoAnterior' => $saldoPendiente,
                    'nuevoSaldo' => $saldoPendiente - $montoAPagar
                ];
                
                // Verificar si este préstamo quedó pagado completamente
                if ($montoAPagar >= $saldoPendiente) {
                    DB::connection('sqlsrv')
                        ->table('Prestamos')
                        ->where('PrestamoId', $prestamo->PrestamoId)
                        ->update(['Estatus' => 3]);
                }
                
                $montoRestante -= $montoAPagar;
                $montoTotalUsadoBono += $montoAPagar;
            }
            
            // Actualizar el bono con el monto total usado
            $nuevoMonto = $bono->MontoDivisa - $montoTotalUsadoBono;
            if ($nuevoMonto <= 0) {
                DB::connection('sqlsrv')
                    ->table('BonosEmpleados')
                    ->where('ID', $bonoId)
                    ->update([
                        'EsPagado' => 1,
                        'MontoDivisa' => 0,
                        'MontoBs' => 0
                    ]);
            } else {
                DB::connection('sqlsrv')
                    ->table('BonosEmpleados')
                    ->where('ID', $bonoId)
                    ->update([
                        'MontoDivisa' => $nuevoMonto,
                        'MontoBs' => round($nuevoMonto * $bono->Tasa, 2)
                    ]);
            }
            
            $todosPagados = $montoRestante <= 0 && count($prestamosAfectados) == count($prestamos);
            
            $message = "Se ha registrado el pago de {$montoTotalUsadoBono} USD usando bono";
            if ($todosPagados) {
                $message = "Todos los préstamos han sido pagados completamente con bono. Total: {$montoTotalUsadoBono} USD";
            } elseif ($montoRestante > 0) {
                $message = "Se pagaron {$montoTotalUsadoBono} USD con bono. Quedan {$montoRestante} USD por aplicar (no hay más préstamos activos)";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'transacciones' => $transaccionesRegistradas,
                    'prestamos_afectados' => $prestamosAfectados,
                    'monto_total_pagado' => $montoTotalUsadoBono,
                    'monto_restante' => $montoRestante,
                    'bono_utilizado' => $bonoId,
                    'bono_saldo_restante' => $nuevoMonto
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en registrarPagoConBono: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago con bono: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarPagoConLiberalidad(Request $request)
    {
        try {
            $request->validate([
                'usuarioId' => 'required|string',
                'monto' => 'required|numeric|min:0.01',
                'fecha' => 'required|date',
                'descripcion' => 'required|string',
                'observacion' => 'nullable|string',
                'numero_operacion' => 'nullable|string'
            ]);
            
            $usuarioId = $request->usuarioId;
            $monto = $request->monto;
            $fecha = $request->fecha;
            $descripcion = $request->descripcion;
            $observacion = $request->observacion;
            $numeroOperacion = $request->numero_operacion;
            
            // Obtener el empleado
            $empleado = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('Id', $usuarioId)
                ->first();
            
            if (!$empleado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 400);
            }
            
            $usuarioIdNum = $empleado->ExternalId ?? null;
            $empleadoIdGuid = $empleado->Id;
            $mesActual = date('n');
            $anioActual = date('Y');
            
            // Verificar liberalidad disponible
            $liberalidad = DB::connection('sqlsrv')
                ->table('LiberalidadEmpleadoTempo')
                ->where('Mes', $mesActual)
                ->where('Anno', $anioActual)
                ->where(function($query) use ($empleadoIdGuid, $usuarioIdNum) {
                    if ($empleadoIdGuid) {
                        $query->where('EmpleadoId', $empleadoIdGuid);
                    }
                    if ($usuarioIdNum) {
                        $query->orWhere('UsuarioId', $usuarioIdNum);
                    }
                })
                ->first();
            
            if (!$liberalidad || $liberalidad->DisponibleLiberalidadDivisa < $monto) {
                return response()->json(['success' => false, 'message' => 'No hay suficiente liberalidad disponible'], 400);
            }
            
            // Obtener tasa de cambio
            $tasaCambio = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->whereDate('Fecha', $fecha)
                ->orderBy('ID', 'desc')
                ->first();
            
            if (!$tasaCambio) {
                $tasaCambio = DB::connection('sqlsrv')
                    ->table('DivisaValor')
                    ->orderBy('ID', 'desc')
                    ->first();
            }
            
            $tasaValor = $tasaCambio->Valor;
            $tasaId = $tasaCambio->ID ?? null;
            
            // Obtener el primer préstamo activo
            $prestamo = DB::connection('sqlsrv')
                ->table('Prestamos')
                ->where('UsuarioId', $usuarioId)
                ->whereIn('Estatus', [1, 2])
                ->orderBy('Fecha', 'asc')
                ->first();
            
            if (!$prestamo) {
                return response()->json(['success' => false, 'message' => 'No hay préstamos activos'], 400);
            }
            
            // Calcular saldo pendiente
            $detalles = DB::connection('sqlsrv')
                ->table('PrestamosDetalles')
                ->where('PrestamoId', $prestamo->PrestamoId)
                ->sum('PvpDivisa');
            
            $pagos = DB::connection('sqlsrv')
                ->table('Transacciones as t')
                ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
                ->where('tu.PrestamoId', $prestamo->PrestamoId)
                ->sum('t.MontoDivisaAbonado');
            
            $totalPrestamo = floatval($prestamo->MontoDivisa) + floatval($detalles);
            $saldoPendiente = $totalPrestamo - floatval($pagos);
            
            if ($saldoPendiente <= 0) {
                return response()->json(['success' => false, 'message' => 'El préstamo ya está pagado'], 400);
            }
            
            $montoAPagar = min($monto, $saldoPendiente);
            $montoAPagarBs = round($montoAPagar * $tasaValor, 2);
            
            // Generar número de operación
            if (empty($numeroOperacion)) {
                $numeroOperacion = 'ABP' . date('Ymd') . '-' . $prestamo->PrestamoId;
            }
            
            // Crear descripción con prefijo "Auto."
            $descripcionFinal = $descripcion;
            if (!str_contains($descripcionFinal, 'Auto.')) {
                $descripcionFinal = 'Auto.' . $descripcionFinal;
            }
            
            // Observación
            $observacionFinal = $observacion;
            if (empty($observacionFinal)) {
                $observacionFinal = $descripcionFinal;
            }
            
            // Registrar transacción (forma de pago = 3 para transferencia)
            $transaccionId = DB::connection('sqlsrv')->table('Transacciones')->insertGetId([
                'Fecha' => $fecha,
                'Descripcion' => $descripcionFinal,
                'DivisaId' => 1,
                'MontoDivisaAbonado' => $montoAPagar,
                'NumeroOperacion' => $numeroOperacion,
                'Estatus' => 2,
                'FormaDePago' => 3,
                'SucursalId' => $prestamo->SucursalId,
                'TasaDeCambio' => $tasaValor,
                'MontoAbonado' => $montoAPagarBs,
                'Tipo' => 4,
                'Observacion' => $observacionFinal,
                'UrlComprobante' => null,
                'Nombre' => null,
                'Cedula' => null
            ]);
            
            // Relacionar transacción
            DB::connection('sqlsrv')->table('TransaccionesUsuario')->insert([
                'UsuarioId' => $usuarioId,
                'TransaccionId' => $transaccionId,
                'PrestamoId' => $prestamo->PrestamoId
            ]);
            
            // Actualizar la liberalidad (reducir el disponible)
            $nuevoDisponible = $liberalidad->DisponibleLiberalidadDivisa - $montoAPagar;
            $nuevoMontoDescuento = ($liberalidad->MontoDescuentoDivisa ?? 0) + $montoAPagar;
            
            DB::connection('sqlsrv')
                ->table('LiberalidadEmpleadoTempo')
                ->where('Id', $liberalidad->Id)
                ->update([
                    'MontoDescuentoDivisa' => $nuevoMontoDescuento,
                    'DisponibleLiberalidadDivisa' => $nuevoDisponible,
                    'DisponibleLiberalidadBs' => $nuevoDisponible * $tasaValor,
                    'FechaCreacion' => now()
                ]);
            
            // Verificar si el préstamo quedó pagado
            $nuevoSaldo = $saldoPendiente - $montoAPagar;
            $prestamoPagado = $nuevoSaldo <= 0;
            
            if ($prestamoPagado) {
                DB::connection('sqlsrv')
                    ->table('Prestamos')
                    ->where('PrestamoId', $prestamo->PrestamoId)
                    ->update(['Estatus' => 3]);
            }
            
            return response()->json([
                'success' => true,
                'message' => $prestamoPagado 
                    ? "El préstamo se ha pagado completamente con liberalidad. Monto: $ {$montoAPagar} USD"
                    : "El abono ha sido registrado con liberalidad. Monto: $ {$montoAPagar} USD"
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en registrarPagoConLiberalidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago con liberalidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detallePrestamosEmpleado($usuarioId)
    {
        try {
            // Obtener el empleado
            $empleado = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('Id', $usuarioId)
                ->first();
            
            if (!$empleado) {
                return back()->with('error', 'Empleado no encontrado');
            }
            
            // Obtener sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $empleado->SucursalId)
                ->first();
            
            // Obtener TODOS los préstamos del empleado (sin filtrar por estatus)
            $prestamos = DB::connection('sqlsrv')
                ->table('Prestamos')
                ->where('UsuarioId', $usuarioId)
                ->orderBy('Fecha', 'desc')
                ->get();
            
            // Procesar cada préstamo con sus detalles y pagos
            foreach ($prestamos as $prestamo) {
                // Obtener detalles (productos) del préstamo
                $detalles = DB::connection('sqlsrv')
                    ->table('PrestamosDetalles as pd')
                    ->leftJoin('Productos as p', 'pd.ProductoId', '=', 'p.ID')
                    ->where('pd.PrestamoId', $prestamo->PrestamoId)
                    ->select(
                        'pd.*',
                        'p.Descripcion as ProductoDescripcion',
                        'p.Codigo as ProductoCodigo',
                        'p.UrlFoto as ProductoImagen'  
                    )
                    ->get();
                
                $prestamo->detalles = $detalles;
                $prestamo->total_productos = $detalles->sum('PvpDivisa');
                
                // Obtener pagos del préstamo
                $pagos = DB::connection('sqlsrv')
                    ->table('Transacciones as t')
                    ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
                    ->where('tu.PrestamoId', $prestamo->PrestamoId)
                    ->select(
                        't.ID',
                        't.Fecha',
                        't.MontoDivisaAbonado',
                        't.MontoAbonado',
                        't.FormaDePago',
                        't.NumeroOperacion',
                        't.Observacion'
                    )
                    ->orderBy('t.Fecha', 'desc')
                    ->get();
                
                $prestamo->pagos = $pagos;
                $prestamo->total_pagado = $pagos->sum('MontoDivisaAbonado');
                
                // Calcular saldo pendiente
                $totalPrestamo = floatval($prestamo->MontoDivisa) + $prestamo->total_productos;
                $prestamo->saldo_pendiente = $totalPrestamo - $prestamo->total_pagado;
                
                // Estatus texto
                switch ($prestamo->Estatus) {
                    case 1:
                        $prestamo->estatus_texto = 'Nuevo';
                        $prestamo->estatus_color = 'warning';
                        break;
                    case 2:
                        $prestamo->estatus_texto = 'En Proceso';
                        $prestamo->estatus_color = 'info';
                        break;
                    case 3:
                        $prestamo->estatus_texto = 'Pagado';
                        $prestamo->estatus_color = 'success';
                        break;
                    case 4:
                        $prestamo->estatus_texto = 'Incluido';
                        $prestamo->estatus_color = 'secondary';
                        break;
                    default:
                        $prestamo->estatus_texto = 'Desconocido';
                        $prestamo->estatus_color = 'dark';
                }
            }
            
            // Calcular resumen general
            $resumen = [
                'total_prestamos' => $prestamos->count(),
                'total_monto' => $prestamos->sum('MontoDivisa') + $prestamos->sum('total_productos'),
                'total_pagado' => $prestamos->sum('total_pagado'),
                'total_pendiente' => 0,
                'prestamos_nuevos' => $prestamos->where('Estatus', 1)->count(),
                'prestamos_proceso' => $prestamos->where('Estatus', 2)->count(),
                'prestamos_pagados' => $prestamos->where('Estatus', 3)->count(),
                'prestamos_incluidos' => $prestamos->where('Estatus', 4)->count()
            ];
            
            $resumen['total_pendiente'] = $resumen['total_monto'] - $resumen['total_pagado'];
            
            // Productos pendientes (no pagados completamente)
            $productosPendientes = collect();
            foreach ($prestamos->where('Estatus', 2) as $prestamo) {
                foreach ($prestamo->detalles as $detalle) {
                    $productosPendientes->push($detalle);
                }
            }
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Prestamos'
            ]);
            
            return view('cpanel.empleados.prestamos_detalle', compact(
                'empleado',
                'sucursal',
                'prestamos',
                'resumen',
                'productosPendientes'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detallePrestamosEmpleado: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
        }
    }

    public function formularioSolicitarPrestamo($usuarioId)
    {
        try {
            // Obtener el empleado
            $empleado = DB::connection('sqlsrv')
                ->table('AspNetUsers')
                ->where('Id', $usuarioId)
                ->first();
            
            if (!$empleado) {
                return back()->with('error', 'Empleado no encontrado');
            }
            
            // Obtener sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $empleado->SucursalId)
                ->first();
            
            // Obtener tasa de cambio actual
            $tasa = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->orderBy('ID', 'desc')
                ->first();
            
            // Obtener productos para préstamo de productos (opcional)
            $productos = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('Estatus', 0) // Productos activos
                ->select('ID', 'Codigo', 'Descripcion', 'CostoDivisa')
                ->orderBy('Descripcion')
                ->get();
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Prestamos'
            ]);
            
            return view('cpanel.empleados.prestamos_solicitar', compact(
                'empleado',
                'sucursal',
                'tasa',
                'productos'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en formularioSolicitarPrestamo: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function guardarSolicitarPrestamo(Request $request)
    {
        try {
            $request->validate([
                'usuario_id' => 'required|string',
                'sucursal_id' => 'required|string',
                'tipo_prestamo' => 'required|in:dinero,producto',
                'observacion' => 'nullable|string'
            ]);
            
            $usuarioId = $request->usuario_id;
            $sucursalId = $request->sucursal_id;
            $tipoPrestamo = $request->tipo_prestamo;
            $observacion = $request->observacion;
            
            // Obtener tasa de cambio actual
            $tasa = DB::connection('sqlsrv')
                ->table('DivisaValor')
                ->orderBy('ID', 'desc')
                ->first();
            
            $tasaValor = $tasa->Valor;
            $tasaId = $tasa->ID ?? null;
            
            // PASO 1: Crear el préstamo con valores iniciales
            $prestamoId = DB::connection('sqlsrv')->table('Prestamos')->insertGetId([
                'UsuarioId' => $usuarioId,
                'Fecha' => now(),
                'FechaCierre' => now(),
                'Observacion' => $observacion,
                'MontoBs' => 0,
                'MontoDivisa' => 0,
                'Estatus' => 1,
                'Tipo' => 0,
                'TasaCambioId' => $tasaId,
                'SucursalId' => $sucursalId
            ]);
            
            if ($tipoPrestamo == 'dinero') {
                // Préstamo de dinero
                $request->validate([
                    'monto_divisa' => 'required|numeric|min:0.01'
                ]);
                
                $montoDivisa = $request->monto_divisa;
                $montoBs = round($montoDivisa * $tasaValor, 2);
                
                DB::connection('sqlsrv')
                    ->table('Prestamos')
                    ->where('PrestamoId', $prestamoId)
                    ->update([
                        'MontoDivisa' => $montoDivisa,
                        'MontoBs' => $montoBs,
                        'Observacion' => $observacion ?: "Préstamo en dinero de $ {$montoDivisa} USD"
                    ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Préstamo en dinero de $ {$montoDivisa} USD solicitado correctamente"
                ]);
                
            } else {
                // Préstamo de producto
                $request->validate([
                    'productos' => 'required|string'
                ]);
                
                $productos = json_decode($request->productos, true);
                
                if (empty($productos)) {
                    DB::connection('sqlsrv')->table('Prestamos')->where('PrestamoId', $prestamoId)->delete();
                    return response()->json(['success' => false, 'message' => 'No hay productos para procesar'], 400);
                }
                
                $totalDivisa = 0;
                
                foreach ($productos as $item) {
                    $producto = DB::connection('sqlsrv')
                        ->table('Productos')
                        ->where('ID', $item['id'])
                        ->first();
                    
                    if (!$producto) {
                        continue;
                    }
                    
                    $montoDivisa = $producto->CostoDivisa * $item['cantidad'];
                    $montoBs = round($montoDivisa * $tasaValor, 2);
                    $totalDivisa += $montoDivisa;
                    
                    DB::connection('sqlsrv')->table('PrestamosDetalles')->insert([
                        'PrestamoId' => $prestamoId,
                        'ProductoId' => $item['id'],
                        'Cantidad' => $item['cantidad'],
                        'PvpBs' => $montoBs,
                        'PvpDivisa' => $montoDivisa
                    ]);
                }
                
                // Actualizar el préstamo con el total
                $totalBs = round($totalDivisa * $tasaValor, 2);
                DB::connection('sqlsrv')
                    ->table('Prestamos')
                    ->where('PrestamoId', $prestamoId)
                    ->update([
                        'MontoDivisa' => $totalDivisa,
                        'MontoBs' => $totalBs,
                        'Estatus' => 2,
                        'Observacion' => $observacion ?: "Préstamo de productos - Total: $ {$totalDivisa} USD"
                    ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Préstamo de productos por $ {$totalDivisa} USD solicitado correctamente"
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error en guardarSolicitarPrestamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al solicitar el préstamo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarProductoPorCodigo(Request $request)
    {
        try {
            $codigo = $request->codigo;
            
            $producto = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('Codigo', $codigo)
                ->orWhere('CodigoBarra', $codigo)
                ->select('ID', 'Codigo', 'Descripcion', 'Referencia', 'CostoDivisa')
                ->first();
            
            if (!$producto) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado']);
            }
            
            return response()->json([
                'success' => true,
                'producto' => $producto
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cerrarLiberalidad(Request $request)
    {
        try {
            $periodo = $request->input('periodo');
            $partes = explode('-', $periodo);
            $anio = intval($partes[0]);
            $mes = intval($partes[1]);
            
            // Validar que no esté ya cerrada
            $existe = DB::connection('sqlsrv')
                ->table('Liberalidad')
                ->where('Mes', $mes)
                ->where('Anno', $anio)
                ->exists();
            
            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'La liberalidad de este período ya está cerrada'
                ]);
            }
            
            // ============================================
            // 1. MARCAR BONOS DEL PERÍODO COMO PAGADOS
            // ============================================
            $bonosActualizados = DB::connection('sqlsrv')
                ->table('BonosEmpleados')
                ->where('MesBono', $mes)
                ->where('AnnoBono', $anio)
                ->where('EsPagado', 0)
                ->update(['EsPagado' => 1]);
            
            // ============================================
            // 2. MARCAR DEDUCCIONES DEL PERÍODO COMO PAGADAS
            // ============================================
            $deduccionesActualizadas = DB::connection('sqlsrv')
                ->table('Deducciones')
                ->where('MesDeduccion', $mes)
                ->where('AnnoDeduccion', $anio)
                ->where('EsPagado', 0)
                ->update(['EsPagado' => 1]);
            
            // ============================================
            // 3. OBTENER DATOS ACTUALES DE LIBERALIDAD
            // ============================================
            $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->startOfDay();
            $fechaFin = $fechaInicio->copy()->endOfMonth()->endOfDay();
            
            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                $fechaInicio,
                $fechaFin
            );
            
            $liberalidad = $this->obtenerLiberalidad($filtroFecha);
            
            // Agregar empleados activos sin ventas
            $liberalidad = $this->obtenerEmpleadosActivosSinVentas($liberalidad, null);
            
            // Enriquecer con bonos y deducciones (ya están como pagados)
            $liberalidad = $this->enriquecerLiberalidad($liberalidad, $mes, $anio, false);
            
            // ============================================
            // 4. FILTRAR EMPLEADOS SIN NADA (NUEVO)
            // ============================================
            $liberalidad = $this->filtrarEmpleadosSinNada($liberalidad);
            
            // ============================================
            // 5. CREAR REGISTRO EN LIBERALIDAD (CABECERA)
            // ============================================
            $liberalidadId = DB::connection('sqlsrv')
                ->table('Liberalidad')
                ->insertGetId([
                    'Mes' => $mes,
                    'Anno' => $anio,
                    'FechaInicio' => $fechaInicio,
                    'FechaFinal' => $fechaFin,
                    'Estatus' => 1
                ]);
            
            // ============================================
            // 6. GUARDAR DETALLES POR CADA EMPLEADO (SOLO LOS FILTRADOS)
            // ============================================
            foreach ($liberalidad->detalles as $detalle) {
                // Calcular valores según tu lógica
                $bonosDelMes = $detalle->total_bonos_usd ?? 0;
                $deduccionesDelMes = $detalle->total_deducciones_usd ?? 0;
                $montoLiberalidad = $detalle->MontoLiberalidad ?? 0;
                
                // TotalPagado = Liberalidad + Bonos - Deducciones
                $totalPagado = $montoLiberalidad + $bonosDelMes - $deduccionesDelMes;
                
                // OtraLiberalidad = Bonos del mes
                $otraLiberalidad = $bonosDelMes;
                
                // AbonoPrestamo = lo que se pagó del préstamo en el mes
                $abonoPrestamo = $detalle->AbonoPrestamo ?? 0;
                
                // DeudaPrestamo = saldo pendiente después del abono
                $deudaPrestamo = $detalle->DeudaPrestamo ?? 0;
                
                DB::connection('sqlsrv')
                    ->table('LiberalidadDetalles')
                    ->insert([
                        'LiberalidadId' => $liberalidadId,
                        'EmpleadoId' => $detalle->EmpleadoId ?? null,
                        'UsuarioId' => $detalle->UsuarioId ?? null,
                        'Unidades' => $detalle->Unidades ?? 0,
                        'Venta' => $detalle->Venta ?? 0,
                        'MontoLiberalidad' => $montoLiberalidad,
                        'OtraLiberalidad' => $otraLiberalidad,
                        'SaldoFavor' => $detalle->SaldoFavor ?? 0,
                        'AbonoPrestamo' => $abonoPrestamo,
                        'DeudaPrestamo' => $deudaPrestamo,
                        'TotalPagado' => $totalPagado,
                        'Estatus' => 1,
                        'EsVendedor' => $detalle->EsVendedor ?? 0,
                        'Pago' => 0
                    ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Liberalidad cerrada correctamente. ' .
                            "Se marcaron {$bonosActualizados} bonos y {$deduccionesActualizadas} deducciones como pagados. " .
                            "Se guardaron {$liberalidad->detalles->count()} empleados."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la liberalidad: ' . $e->getMessage()
            ]);
        }
    }
}