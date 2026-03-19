<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use App\DTOs\CierreDiarioPeriodoDTO;
use App\Models\CierreDiario;
use App\Models\PagoPuntoDeVenta;
use App\Models\ValorizacionInventario;
use App\Models\Transaccion;
use App\Models\CierreOfp;
use App\DTO\EDCOficinaPrincipalDTO;
use App\DTO\TransferenciaDTO;
use App\DTO\TransferenciaDetalleDTO;
use App\DTO\ProductoDTO;

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
                ->where('EsActivo', 1)
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
        $vendedoresQuery = DB::connection('sqlsrv')
                    ->select("
                        -- Vendedores del POS (tabla Usuarios) - PRIORITARIOS
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
                            1 as prioridad  -- Mayor prioridad
                        FROM Usuarios u
                        LEFT JOIN Sucursales s ON u.SucursalId = s.ID
                        WHERE u.EsActivo = 1 
                            AND u.EsRegistrado = 0
                        
                        UNION ALL
                        
                        -- Vendedores de Identity (solo los que NO están en POS)
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
                            0 as prioridad  -- Menor prioridad
                        FROM AspNetUsers au
                        INNER JOIN AspNetUserRoles ur ON au.Id = ur.UserId
                        INNER JOIN AspNetRoles r ON ur.RoleId = r.Id
                        LEFT JOIN Sucursales s ON au.SucursalId = s.ID
                        WHERE r.Name = 'VENDEDORES'
                            AND au.EsActivo = 1
                            AND NOT EXISTS (  -- 🔴 Excluir los que ya están en Usuarios
                                SELECT 1 FROM Usuarios u2 
                                WHERE u2.VendedorId = au.VendedorId 
                                AND u2.SucursalId = au.SucursalId
                            )
                        
                        ORDER BY NombreCompleto
                    ");
        
        // Convertir a colección
        $vendedores = collect($vendedoresQuery);
        
        session([
            'menu_active' => 'Empleados',
            'submenu_active' => 'Vendedores'
        ]);
        
        // dd($vendedores); // Quitar después de probar
        
        return view('cpanel.empleados.vendedores_listado', compact('vendedores'));
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
}