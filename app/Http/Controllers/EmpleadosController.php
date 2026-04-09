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

        // dd($rankingVendedor);

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

        // dd($rankingVendedor);

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
            // $id = UsuarioId del vendedor
            
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

        // Crear filtro de fechas
        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false,
            $fechaInicio,
            $fechaFin
        );

        // 🔴 OBTENER ESTATUS DEL REQUEST (1: activos, 0: inactivos, null: todos)
        $estatus = $request->input('estatus'); // Puede ser '1', '0', o null        

        // Construir la condición WHERE para el estatus
        $whereEstatus = "";
        if ($estatus === '1') {
            $whereEstatus = " AND u.EsActivo = 1 ";
        } elseif ($estatus === '0') {
            $whereEstatus = " AND u.EsActivo = 0 ";
        }

        // $vendedores = DB::connection('sqlsrv')
        // ->select("
        //     -- Vendedores del POS (tabla Usuarios) - Incluir TODOS los activos
        //     SELECT 
        //         u.UsuarioId as id,
        //         u.VendedorId,
        //         u.Email,
        //         u.PhoneNumber,
        //         u.EsActivo,
        //         u.NombreCompleto,
        //         u.Direccion,
        //         u.FechaCreacion,
        //         u.FechaNacimiento,
        //         u.SucursalId,
        //         u.FotoPerfil,
        //         u.EsRegistrado,
        //         s.Nombre as sucursal_nombre,
        //         'pos' as origen
        //     FROM Usuarios u
        //     INNER JOIN Sucursales s ON u.SucursalId = s.ID  -- Cambiar a INNER JOIN
        //     WHERE u.EsActivo = 1 
        //         AND s.Tipo = 1  -- 🔴 Filtrar por Tipo = 1 (Tienda)
            
        //     UNION
            
        //     -- Vendedores de Identity con rol VENDEDORES
        //     SELECT 
        //         au.Id as id,
        //         au.VendedorId,
        //         au.Email,
        //         au.PhoneNumber,
        //         au.EsActivo,
        //         au.NombreCompleto,
        //         au.Direccion,
        //         au.FechaCreacion,
        //         au.FechaNacimiento,
        //         au.SucursalId,
        //         au.FotoPerfil,
        //         CAST(au.EmailConfirmed as int) as EsRegistrado,
        //         s.Nombre as sucursal_nombre,
        //         'identity' as origen
        //     FROM AspNetUsers au
        //     INNER JOIN AspNetUserRoles ur ON au.Id = ur.UserId
        //     INNER JOIN AspNetRoles r ON ur.RoleId = r.Id
        //     INNER JOIN Sucursales s ON au.SucursalId = s.ID  -- Cambiar a INNER JOIN
        //     WHERE r.Name = 'VENDEDORES'
        //         AND au.EsActivo = 1
        //         AND s.Tipo = 1  -- 🔴 Filtrar por Tipo = 1 (Tienda)
            
        //     ORDER BY NombreCompleto
        // ");

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
                'pos' as origen
            FROM Usuarios u
            INNER JOIN Sucursales s ON u.SucursalId = s.ID
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
                'identity' as origen
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
            return $group->firstWhere('origen', 'pos') ?? $group->first();
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

    // private function guardarFotoVendedor(Request $request, $vendedor)
    // {
    //     try {
    //         $file = $request->file('foto');
            
    //         // Generar el nombre del archivo como en .NET (ej: VDD93_4.PNG)
    //         $vendedorId = $vendedor->VendedorId ?? 'VEND';
    //         $sucursalId = $vendedor->SucursalId ?? '0';
    //         $extension = strtoupper($file->getClientOriginalExtension()); // PNG, JPG, etc.
    //         $filename = $vendedorId . '_' . $sucursalId . '.' . $extension;
            
    //         // Ruta donde se guardan las fotos (la misma que usa el helper)
    //         $folder = 'images/usuarios/';
    //         $storagePath = 'public/' . $folder;
            
    //         // Crear carpeta si no existe
    //         if (!Storage::exists($storagePath)) {
    //             Storage::makeDirectory($storagePath, 0755, true);
    //         }
            
    //         // Eliminar foto anterior si existe
    //         if ($vendedor->FotoPerfil) {
    //             $oldFile = $storagePath . $vendedor->FotoPerfil;
    //             if (Storage::exists($oldFile)) {
    //                 Storage::delete($oldFile);
    //             }
    //         }
            
    //         // Guardar la nueva foto en storage/app/public/images/usuarios/
    //         Storage::putFileAs($storagePath, $file, $filename);
            
    //         // Actualizar SOLO el campo FotoPerfil en la BD
    //         $vendedor->FotoPerfil = $filename;
    //         $vendedor->save();
            
    //         return true;
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error al guardar foto del vendedor: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

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

    // private function guardarFotoEmpleadoInterno(Request $request, $empleado)
    // {
    //     try {
    //         $file = $request->file('foto');
            
    //         // Generar nombre del archivo (formato similar al de vendedores)
    //         // Si tiene VendedorId, lo usamos, si no, usamos parte del Email o ID
    //         $vendedorId = $empleado->VendedorId ?? 'EMP';
    //         $sucursalId = $empleado->SucursalId ?? '0';
    //         $extension = strtoupper($file->getClientOriginalExtension());
            
    //         // Para empleados sin VendedorId, usar un identificador único
    //         if ($vendedorId == 'EMP') {
    //             // Usar los primeros 8 caracteres del ID (GUID) para un nombre único
    //             $idCorto = substr(str_replace('-', '', $empleado->Id), 0, 8);
    //             $vendedorId = 'EMP_' . $idCorto;
    //         }
            
    //         $filename = $vendedorId . '_' . $sucursalId . '.' . $extension;
            
    //         // DETECTAR ENTORNO (igual que en guardarFotoVendedor)
    //         $environment = app()->environment();
            
    //         if ($environment === 'production') {
    //             // LÓGICA PARA PRODUCCIÓN (SMARTERASP)
    //             $folder = 'images/usuarios/';
    //             $physicalPath = base_path('public/' . $folder);
                
    //             if (!is_dir($physicalPath)) {
    //                 mkdir($physicalPath, 0777, true);
    //             }
                
    //             // Eliminar foto anterior si existe
    //             if ($empleado->FotoPerfil) {
    //                 $oldFilePath = $physicalPath . $empleado->FotoPerfil;
    //                 if (file_exists($oldFilePath)) {
    //                     unlink($oldFilePath);
    //                 }
    //             }
                
    //             // Mover nueva foto
    //             $file->move($physicalPath, $filename);
                
    //         } else {
    //             // LÓGICA PARA LOCAL (USANDO STORAGE)
    //             $folder = 'images/usuarios/';
    //             $storagePath = 'public/' . $folder;
                
    //             if (!Storage::exists($storagePath)) {
    //                 Storage::makeDirectory($storagePath, 0755, true);
    //             }
                
    //             // Eliminar foto anterior si existe
    //             if ($empleado->FotoPerfil) {
    //                 $oldFile = $storagePath . $empleado->FotoPerfil;
    //                 if (Storage::exists($oldFile)) {
    //                     Storage::delete($oldFile);
    //                 }
    //             }
                
    //             // Guardar nueva foto
    //             Storage::putFileAs($storagePath, $file, $filename);
    //         }
            
    //         // Actualizar campo FotoPerfil en BD
    //         $empleado->FotoPerfil = $filename;
    //         $empleado->save();
            
    //         \Log::info('Foto de empleado interno guardada:', [
    //             'id' => $empleado->Id,
    //             'filename' => $filename
    //         ]);
            
    //         return true;
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error al guardar foto de empleado interno: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

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
            
            // 3. Obtener TODOS los roles disponibles (para el select)
            $roles = DB::connection('sqlsrv')
                ->table('AspNetRoles')
                ->orderBy('Name')
                ->get();
            
            // 4. Obtener lista de sucursales
            $sucursales = Sucursal::where('EsActiva', 1)
                ->orderBy('Nombre')
                ->get();
            
            session([
                'menu_active' => 'Empleados',
                'submenu_active' => 'Agregar Empleado'
            ]);
            
            return view('cpanel.empleados.show_agregar_empleado', compact(
                'roles',           
                'sucursales'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en la vista Agregar Empleado: ' . $e->getMessage());
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
            
            // Buscar liberalidad
            $liberalidadDTO = $this->buscarLiberalidad($filtroFecha, true);

            // if (!$liberalidadDTO) {
            //     // No existe liberalidad cerrada, calcular datos actuales
            //     $liberalidad = $this->obtenerLiberalidad($filtroFecha);

            //     dd($liberalidad);
            //     // Enriquecer con bonos y deducciones
            //     $liberalidad = $this->enriquecerLiberalidad($liberalidad, $mesSeleccionado, $anioSeleccionado);

            //     //dd($liberalidad);
            // } else {
            //     // Existe liberalidad cerrada, mostrar datos guardados
            //     $liberalidad = null;
            //     // Enriquecer con bonos y deducciones
            //     $liberalidadDTO = $this->enriquecerLiberalidad($liberalidadDTO, $mesSeleccionado, $anioSeleccionado);

            //     //dd($liberalidadDTO);
            // }

            if (!$liberalidadDTO) {
                // No existe liberalidad cerrada, calcular datos actuales
                $liberalidad = $this->obtenerLiberalidad($filtroFecha);
                
                // Agregar empleados activos sin ventas
                $liberalidad = $this->obtenerEmpleadosActivosSinVentas($liberalidad, null);
                
                // Enriquecer con bonos y deducciones
                $liberalidad = $this->enriquecerLiberalidad($liberalidad, $mesSeleccionado, $anioSeleccionado);
                
                // NUEVO: Filtrar empleados que no tienen ventas, bonos ni deducciones
                $liberalidad = $this->filtrarEmpleadosSinNada($liberalidad);
                
            } else {
                // Existe liberalidad cerrada, mostrar datos guardados
                $liberalidad = null;
                
                // Enriquecer con bonos y deducciones
                $liberalidadDTO = $this->enriquecerLiberalidad($liberalidadDTO, $mesSeleccionado, $anioSeleccionado);
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
                'periodo'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en listado_liberalidad: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al cargar liberalidad: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los empleados activos que no están en la liberalidad
     */
    private function obtenerEmpleadosActivosSinVentas($liberalidad, $sucursalId = null)
    {
        // Crear un mapa de claves únicas (usando combinación de tipo + id)
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
        
        $nuevosAgregados = 0;
        
        // Agregar empleados de sistema que faltan
        foreach ($aspNetUsers as $empleado) {
            $key = 'empleado_' . $empleado->Id;
            if (!isset($clavesUnicas[$key])) {
                
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

    /**
     * Filtra los empleados que no tienen ventas, ni bonos, ni deducciones
     */
    private function filtrarEmpleadosSinNada($liberalidad)
    {
        if (!$liberalidad || !isset($liberalidad->detalles)) {
            return $liberalidad;
        }
        
        $originalCount = $liberalidad->detalles->count();
        
        // Filtrar: mantener empleados que tengan ventas, bonos o deducciones
        $detallesFiltrados = $liberalidad->detalles->filter(function($detalle) {
            $tieneVentas = ($detalle->Venta ?? 0) > 0;
            $tieneBonos = ($detalle->total_bonos_usd ?? 0) > 0;
            $tieneDeducciones = ($detalle->total_deducciones_usd ?? 0) > 0;
            
            return $tieneVentas || $tieneBonos || $tieneDeducciones;
        });
        
        $liberalidad->detalles = $detallesFiltrados->values();
        
        return $liberalidad;
    }

    /**
     * Enriquece los detalles de liberalidad con información de bonos y deducciones
     */
    private function enriquecerLiberalidad($liberalidad, $mes, $anio)
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
            $detalle->neto_usd = ($detalle->MontoLiberalidad ?? 0) + $detalle->total_bonos_usd - $detalle->total_deducciones_usd;
        }
        
        return $liberalidad;
    }

    /**
     * Obtiene los bonos de un empleado para un período específico
     */
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
                'FechaCreacion'
            ])
            ->where('MesBono', $mes)
            ->where('AnnoBono', $anio);
        
        if ($usuarioId) {
            $query->where('UsuarioId', $usuarioId);
        } elseif ($empleadoId) {
            $query->where('EmpleadoId', $empleadoId);
        } else {
            return collect();
        }
        
        return $query->orderBy('FechaCreacion', 'desc')->get();
    }

    /**
     * Obtiene las deducciones de un empleado para un período específico
     */
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
        
        if ($usuarioId) {
            $query->where('UsuarioId', $usuarioId);
        } elseif ($empleadoId) {
            $query->where('EmpleadoId', $empleadoId);
        } else {
            return collect();
        }
        
        return $query->orderBy('FechaCreacion', 'desc')->get();
    }

    private function buscarLiberalidad($filtroFecha, $esDetalles = true)
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
            \Log::error('Error en buscarLiberalidad: ' . $e->getMessage());
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
            \Log::error('Error en obtenerLiberalidad: ' . $e->getMessage());
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
            \Log::info('📋 LISTADO DE EMPLEADOS PARA BONOS', [
                'total_empleados' => $empleados->count(),
                'bonos_asignados_sistema' => $ultimosBonosSistema->count(),
                'bonos_asignados_temporales' => $ultimosBonosTemporales->count()
            ]);

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
                    ->select('Id', 'NombreCompleto', 'SucursalId')
                    ->first();
                $empleadoId = $id;
                $usuarioId = null;
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

            // ============================================
            // 7. LOG PARA DEPURACIÓN
            // ============================================
            \Log::info('📋 LISTADO DE EMPLEADOS PARA BONOS', [
                'total_empleados' => $empleados->count(),
                'deducciones_sistema' => $ultimaDeduccionSistema->count(),
                'deducciones_temporales' => $ultimasDeduccionesTemporales->count()
            ]);

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
                    ->select('Id', 'NombreCompleto', 'SucursalId')
                    ->first();
                $empleadoId = $id;
                $usuarioId = null;
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

    /**
     * Obtiene todos los préstamos activos de un empleado (Nuevo + En Proceso)
     */
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
}