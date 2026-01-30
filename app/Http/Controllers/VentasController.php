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
use App\Models\Usuario;

use Illuminate\Support\Facades\Log;

use CloudConvert\CloudConvert;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;
use CloudConvert\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VentasController extends Controller
{   

    // Ventas Diarias
    public function ventas_diarias(Request $request)
    {       
        // 🚀 Aquí: usar fechas del request si existen
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // Asignacion al menu
        session([
            'menu_active' => 'Análisis de Ventas',
            'submenu_active' => 'Ventas Diarias'
        ]);

        // 5️⃣ Obtener sucursal activa
        $sucursalId = session('sucursal_id');

        $ventasDiariaPeriodo = VentasHelper::BuscarListadoVentasDiarias($filtroFecha, $sucursalId);

        return view('cpanel.ventas.ventas_diarias', [
            'ventas' => $ventasDiariaPeriodo['Ventas'],
            'fechaInicio' => $ventasDiariaPeriodo['FechaInicio'],
            'fechaFin' => $ventasDiariaPeriodo['FechaFin']
        ]);
    }

    // Eliminar Venta Diaria
    public function eliminar_venta(Request $request)
    {
        $id = $request->input('venta_id');

        try {
            $ventasService = new VentasService();
            $ok = $ventasService->borrarVentaDiaria($id);

            return response()->json([
                'ok' => $ok,
                'message' => $ok ? 'Venta eliminada' : 'Venta no encontrada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function detalleVenta($ventaId, $sucursalId)
    {
        $margenPromedio = request()->query('margen', 0);
    
        try {
            // 1️⃣ Obtener la venta - USAR 'ID' en mayúsculas
            $venta = DB::table('Ventas')
                ->where('ID', $ventaId) // ← CORRECCIÓN: 'ID' no 'Id'
                ->first();

            if (!$venta) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Venta no encontrada'
                ], 404);
            }

            // 2️⃣ Si sucursal viene en 0 se usa la de la venta
            if ($sucursalId == 0) {
                // Intentar diferentes nombres de columna
                $sucursalId = $venta->SucursalId ?? $venta->sucursalId ?? $venta->ID_Sucursal ?? 0;
            }

            // 3️⃣ Obtener los productos vendidos - VERIFICAR NOMBRES DE COLUMNAS
            $detalles = DB::table('VentaProductos')
                ->where('VentaId', $ventaId) // ← 'VentaId' o 'VentaID'?
                ->get();

            if ($detalles->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'data' => []
                ]);
            }

            // Verificar estructura del primer detalle
            if ($detalles->isNotEmpty()) {
                $primerDetalle = (array)$detalles->first();
            }

            // Lista de productoIds - VERIFICAR NOMBRE DE COLUMNA
            $productoIds = $detalles->pluck('ProductoId')->toArray();
            if (empty($productoIds)) {
                // Intentar con diferentes nombres
                $productoIds = $detalles->pluck('ProductoID')->toArray();
            }

            // 4️⃣ BUSCAR PRODUCTOS EN LA VISTA ProductosSucursalView
            
            $productos = DB::table('ProductosSucursalView')
                ->whereIn('ID', $productoIds)
                ->where('SucursalId', $sucursalId)
                ->get()
                ->keyBy('ID');

            // Si no hay productos, intentar con nombre de columna diferente
            if ($productos->isEmpty()) {
                $productos = DB::table('ProductosSucursalView')
                    ->whereIn('ID', $productoIds)
                    ->where('SucursalId', $sucursalId)
                    ->get()
                    ->keyBy('ID');
                
            }

            // 5️⃣ Si no se encuentran productos en la vista, buscar en tabla maestra
            if ($productos->isEmpty() && $sucursalId != 0) {
                
                $productos = DB::table('Productos')
                    ->whereIn('ID', $productoIds)
                    ->select(
                        'ID',
                        'Codigo',
                        'Descripcion',
                        'CostoBs',
                        'CostoDivisa',
                        DB::raw('0 as Existencia'),
                        DB::raw($sucursalId . ' as SucursalId'),
                        DB::raw('0 as PvpBs'),
                        DB::raw('0 as PvpDivisa')
                    )
                    ->get()
                    ->keyBy('ID');
            }

            // 6️⃣ Unir resultados - MANEJAR DIFERENTES NOMBRES DE COLUMNAS
            $respuesta = $detalles->map(function ($d) use ($productos, $sucursalId) {
                // Obtener ProductoId del detalle (puede ser ProductoId o ProductoID)
                $productoId = $d->ProductoId ?? $d->ProductoID ?? $d->productoId ?? null;
                
                if (!$productoId) {
                    $producto = null;
                } else {
                    // Buscar producto (puede estar keyed por 'Id' o 'ID')
                    $producto = $productos[$productoId] ?? 
                            $productos->firstWhere('Id', $productoId) ??
                            $productos->firstWhere('ID', $productoId);
                }
                
                // Obtener precio (puede ser Precio o MontoDivisa)
                $precio = $d->Precio ?? $d->precio ?? 0;
                $montoDivisa = $d->MontoDivisa ?? $d->montoDivisa ?? $precio;
                
                $item = [
                    'id' => $d->Id ?? $d->ID,
                    'venta_id' => $d->VentaId ?? $d->VentaID,
                    'producto_id' => $productoId,
                    'cantidad' => $d->Cantidad ?? $d->cantidad ?? 0,
                    'precio' => $precio,
                    'monto_divisa' => $montoDivisa,
                ];
                
                if ($producto) {
                    // Obtener ID del producto (puede ser Id o ID)
                    $prodId = $producto->Id ?? $producto->ID;
                    $prodDesc = $producto->Descripcion ?? $producto->descripcion ?? '';
                    $prodCodigo = $producto->Codigo ?? $producto->codigo ?? '';
                    $prodUrlFoto = $producto->UrlFoto ?? '';
                    // $fechaActualizacion = $producto->FechaActualizacion ?? $producto->FechaCreacion;
                    $fechaActualizacion = $producto->FechaCreacion;

                    // Calcular días desde FechaActualizacion hasta hoy
                    if ($fechaActualizacion) {
                        $fechaActualizacionCarbon = Carbon::parse($fechaActualizacion);
                        $diasDesdeActualizacion = $fechaActualizacionCarbon->diffInDays(Carbon::now());
                    } else {
                        $diasDesdeActualizacion = null;
                    }
                    
                    // Datos del producto
                    $item['producto'] = [
                        'Id' => intval($prodId),
                        'Codigo' => $prodCodigo,
                        'Descripcion' => $prodDesc,
                        'UrlFoto' => $prodUrlFoto,
                        'DiasDesdeActualizacion' => $diasDesdeActualizacion,
                        'CostoBs' => floatval($producto->CostoBs ?? $producto->costoBs ?? 0),
                        'CostoDivisa' => floatval($producto->CostoDivisa ?? $producto->costoDivisa ?? 0),
                        'Existencia' => intval($producto->Existencia ?? $producto->existencia ?? 0),
                        'SucursalId' => intval($producto->SucursalId ?? $producto->sucursalId ?? $sucursalId),
                        'PvpBs' => floatval($producto->PvpBs ?? $producto->pvpBs ?? 0),
                        'PvpDivisa' => floatval($producto->PvpDivisa ?? $producto->pvpDivisa ?? 0),
                    ];
                    
                    // Calcular valores básicos
                    $costoBs = floatval($producto->CostoBs ?? $producto->costoBs ?? 0);
                    $costoDivisa = floatval($producto->CostoDivisa ?? $producto->costoDivisa ?? 0);
                    $cantidad = $item['cantidad'];
                    
                    if ($cantidad > 0) {
                        $item['monto_unitario'] = round($precio / $cantidad, 2);
                        $item['monto_divisa_unitario'] = round($montoDivisa / $cantidad, 2);
                        
                        // Margen si hay costo en divisa
                        if ($costoDivisa > 0) {
                            $montoDivisaUnitario = $montoDivisa / $cantidad;
                            $margen = (($montoDivisaUnitario * 100) / $costoDivisa) - 100;
                            $item['margen'] = round($margen, 2);
                        } else {
                            $item['margen'] = 0;
                        }
                    } else {
                        $item['monto_unitario'] = 0;
                        $item['monto_divisa_unitario'] = 0;
                        $item['margen'] = 0;
                    }
                    
                    $item['costo_total_bs'] = round($cantidad * $costoBs, 2);
                    $item['costo_total_divisa'] = round($cantidad * $costoDivisa, 2);
                    $item['utilidad_bs'] = round($precio - ($cantidad * $costoBs), 2);
                    $item['utilidad_divisa'] = round($montoDivisa - ($cantidad * $costoDivisa), 2);
                    
                } else {
                    $item['producto'] = null;
                    $item['monto_unitario'] = 0;
                    $item['monto_divisa_unitario'] = 0;
                    $item['margen'] = 0;
                    $item['costo_total_bs'] = 0;
                    $item['costo_total_divisa'] = 0;
                    $item['utilidad_bs'] = 0;
                    $item['utilidad_divisa'] = 0;
                }
                
                return $item;
            });

            // return response()->json([
            //     'ok' => true,
            //     'data' => $respuesta
            // ]);

            $totalItems = $respuesta->count();

            $totalDivisa = $respuesta->sum('monto_divisa');
            $totalUtilidad = $respuesta->sum('utilidad_divisa');

            // margen promedio
            $promedioMargen = $totalDivisa > 0
                ? round(($totalUtilidad * 100) / $totalDivisa, 2)
                : 0;

            return view('cpanel.ventas.detalles_ventas_diarias', [
                'venta' => $venta,
                'detalles' => $respuesta,
                'sucursalId' => $sucursalId,
                'totalItems' => $totalItems,
                'totalDivisa' => $totalDivisa,
                'totalUtilidad' => $totalUtilidad,
                'promedioMargen' => $margenPromedio,
            ]);

        } catch (\Throwable $e) {
            \Log::error('💥 ERROR en detalleVenta:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'ok' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cargar Ventas Diarias
    public function cargar_ventas_diarias(Request $request)
    {
        // Asignacion al menu
        session([
            'menu_active' => 'Análisis de Ventas',
            'submenu_active' => 'Cargar Venta Diaria'
        ]);

        return view('cpanel.ventas.cargar_ventas_diarias');
    }

    // Crear ventas diarias y ventas por vendedor
    public function store(Request $request)
    {
        DB::beginTransaction(); // Comienza la transacción

        try {
            // Validación de archivos
            // $request->validate([
            //     'daily_sales_file' => 'required|file|mimes:xlsx,xls,csv,Xlsx,Xls',
            //     'sales_by_seller_file' => 'required|file|mimes:xlsx,xls,csv,Xlsx,Xls',
            //     'sale_date' => 'required|date',
            //     'exchange_rate' => 'required|numeric|min:0.000001',
            //     'sucursal_id' => 'required|integer',
            // ]);
            $request->validate([
                'daily_sales_file' => [
                    'required',
                    'file',
                    function ($attribute, $value, $fail) {
                        $allowed = ['xls', 'xlsx'];
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, $allowed)) {
                            $fail("The $attribute must be a file of type: xls or xlsx.");
                        }
                    }
                ],
                'sales_by_seller_file' => [
                    'required',
                    'file',
                    function ($attribute, $value, $fail) {
                        $allowed = ['xls', 'xlsx'];
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, $allowed)) {
                            $fail("The $attribute must be a file of type: xls or xlsx.");
                        }
                    }
                ],
                'sale_date' => 'required|date',
                'exchange_rate' => 'required|numeric|min:0.000001',
                'sucursal_id' => 'required|integer',
            ]);

            $sucursalId = $request->sucursal_id;
            $saleDate = Carbon::parse($request->sale_date)->startOfDay();
            $exchangeRate = $request->exchange_rate;

            // Guardar tasa de cambio
            DivisaValor::updateOrCreate(
                ['Fecha' => $saleDate, 'DivisaId' => 1], // 1 = USD
                ['Valor' => $exchangeRate]
            );

            // Procesar Ventas Diarias
            // Convertir XLS a XLSX usando CloudConvert
            $xlsxFileVentas = $this->convertidor($request->file('daily_sales_file'));

            // Crear objeto UploadedFile temporal
            $xlsxFile = new UploadedFile(
                $xlsxFileVentas,
                basename($xlsxFileVentas),
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true // $test = true para indicar que es un archivo temporal
            );

            //$rowsVentas = $this->cargarExcelDefinitivo($xlsxFile);
            $this->procesarVentasDiarias($xlsxFile, $sucursalId, $saleDate);
            @unlink($xlsxFileVentas);


            // Procesar Ventas Diarias por Vendedor
            $xlsxFileVendedor = $this->convertidor($request->file('sales_by_seller_file'));

            // Crear objeto UploadedFile temporal
            $xlsxFileVen = new UploadedFile(
                $xlsxFileVendedor,
                basename($xlsxFileVendedor),
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true // $test = true para indicar que es un archivo temporal
            );

            //$rowsVendedor = $this->cargarExcelDefinitivo($xlsxFileVen);
            $this->procesarVentasPorVendedor($xlsxFileVen, $sucursalId, $saleDate);
            @unlink($xlsxFileVendedor);

            // Si todo es exitoso, confirmar la transacción
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Archivos procesados correctamente']);
        } catch (\Throwable $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            \Log::error('💥 ERROR en store ventas:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // En caso de error, devolver mensaje
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    private function procesarVentasDiarias($fileOrRows, $sucursalId, $saleDate)
    {
        // // Cargar el archivo de Excel
        // $spreadsheet = IOFactory::load($file->getPathname());
        // $sheet = $spreadsheet->getActiveSheet();
        // $rows = $sheet->toArray();

        // Determinar si es archivo o array
        if ($fileOrRows instanceof \Illuminate\Http\UploadedFile) {
            $spreadsheet = IOFactory::load($fileOrRows->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            \Log::info('ARRAY DE .XLSX:', ['primeras_3_filas' => array_slice($rows, 0, 3)]);
        } else {
            $rows = $fileOrRows;
            \Log::info('ARRAY DE .XLS (extraído):', ['primeras_3_filas' => array_slice($rows, 0, 3)]);
        }

        // Iniciar transacción de base de datos
        // DB::transaction(function () use ($rows, $sucursalId, $saleDate) {
        DB::transaction(function () use ($rows, $sucursalId, $saleDate) {
            try {
                // Estado inicial
                $estado = 'INICIO';
                $venta = null;
                $fechaEncontrada = false;
                
                // Recorremos las filas del archivo Excel
                foreach ($rows as $i => $row) {
                    // Normalizar la fila manteniendo TODAS las columnas
                    $row = array_map(function($cell) {
                        if (is_null($cell) || $cell === '') {
                            return null;
                        }
                        if (is_numeric($cell)) {
                            return $cell;
                        }
                        return trim((string)$cell);
                    }, $row);
                    
                    // Saltar filas completamente vacías
                    if (empty(array_filter($row, function($value) {
                        return !is_null($value) && $value !== '';
                    }))) {
                        continue;
                    }
                    
                    // Unir todas las celdas para búsqueda de texto
                    $rowText = '';
                    foreach ($row as $cell) {
                        if (is_string($cell)) {
                            $rowText .= ' ' . strtolower(trim($cell));
                        }
                    }
                    $rowText = trim($rowText);
                    
                    // Lógica de estados
                    switch ($estado) {
                        case 'INICIO':
                            if (str_contains($rowText, 'ventas diarias')) {
                                $estado = 'CABECERA';
                            }
                            break;
                            
                        case 'CABECERA':
                            // Buscar fecha primero
                            // IGNORAR fecha encontrada dentro del Excel, siempre usar $saleDate
                            if (str_contains($rowText, 'fecha') && !$fechaEncontrada) {
                                $fechaEncontrada = true;
                            }
                            
                            // Buscar "item" para cambiar a detalles - buscar en columna A (índice 0)
                            if (isset($row[0]) && strtolower($row[0]) === 'item') {
                                // Crear la venta principal
                                $venta = Venta::create([
                                    'Fecha' => $saleDate,
                                    'SucursalId' => $sucursalId,
                                    'Estatus' => 1,
                                    'Saldo' => 0
                                ]);
                                
                                $estado = 'DETALLES';
                            }
                            break;
                            
                        case 'DETALLES':
                            // Para PRODUCTOS, las posiciones son diferentes:
                            // A[0] = Item (código)
                            // B[1] = Descripción
                            // F[5] = Cantidad (NO C[2] como en cabecera!)
                            // H[7] = Monto Bruto (NO F[5] como en cabecera!)
                            // K[10] = Impuestos (NO H[7] como en cabecera!)
                            // O[14] = Monto Neto (NO K[10] como en cabecera!)
                            
                            // Verificar que es un producto (código en columna A y no es cabecera)
                            if (isset($row[0]) && 
                                !empty($row[0]) && 
                                is_string($row[0]) &&
                                strtolower($row[0]) !== 'item' &&
                                !str_contains(strtolower($row[0]), 'total') &&
                                !str_contains(strtolower($row[0]), 'subtotal')) {
                                
                                $codigo = trim($row[0]);
                                
                                // Función para convertir números con formato europeo (comas decimales)
                                $convertirNumero = function($valor) {
                                    if (is_null($valor) || $valor === '' || $valor === '-') {
                                        return 0;
                                    }
                                    
                                    // Si ya es numérico
                                    if (is_numeric($valor)) {
                                        return floatval($valor);
                                    }
                                    
                                    $valor = trim((string)$valor);
                                    
                                    // Manejar formato con comas decimales (europeo)
                                    if (str_contains($valor, ',')) {
                                        // Si tiene solo una coma y después tiene 2 dígitos, es decimal
                                        if (substr_count($valor, ',') === 1) {
                                            $partes = explode(',', $valor);
                                            if (isset($partes[1]) && strlen($partes[1]) <= 2) {
                                                // Es decimal europeo: 1234,56
                                                $valor = str_replace(',', '.', $valor);
                                            } else {
                                                // Es separador de miles: 1,234
                                                $valor = str_replace(',', '', $valor);
                                            }
                                        } else {
                                            // Eliminar todas las comas (separadores de miles)
                                            $valor = str_replace(',', '', $valor);
                                        }
                                    }
                                    
                                    return is_numeric($valor) ? floatval($valor) : 0;
                                };
                                
                                // Obtener valores usando posiciones de PRODUCTO
                                // NOTA: Los índices de array empiezan en 0
                                // A=0, B=1, C=2, D=3, E=4, F=5, G=6, H=7, I=8, J=9, K=10, L=11, M=12, N=13, O=14
                                
                                $cantidad = isset($row[5]) ? intval($row[5]) : 1; // Columna F
                                
                                // Para montos, necesitamos convertir el formato
                                $montoBruto = isset($row[7]) ? $convertirNumero($row[7]) : 0; // Columna H
                                $impuestos = isset($row[10]) ? $convertirNumero($row[10]) : 0; // Columna K
                                $montoNeto = isset($row[14]) ? $convertirNumero($row[14]) : 0; // Columna O
                                
                                // Si no hay monto neto en O, puede estar en otra columna
                                if ($montoNeto == 0) {
                                    // Intentar con K si tiene valor
                                    if ($impuestos > 0 && is_numeric($impuestos)) {
                                        $montoNeto = $impuestos;
                                        $impuestos = 0;
                                    }
                                    // O intentar con H
                                    elseif ($montoBruto > 0 && is_numeric($montoBruto)) {
                                        $montoNeto = $montoBruto;
                                    }
                                }
                                
                                // Validar que sea un código de producto válido
                                if (preg_match('/^[A-Za-z0-9\-_]+$/', $codigo) && $cantidad > 0 && $montoNeto > 0) {
                                    
                                    // Buscar producto
                                    $producto = Producto::where('Codigo', $codigo)->first();

                                    $productoSucursal = ProductoSucursal::where('ProductoId', $producto->ID)
                                                        ->where('SucursalId', $sucursalId)
                                                        ->where('Estatus', 1)
                                                        ->first();
                                    
                                    if ($producto) {
                                        // Calcular precio unitario
                                        $precioUnitario = $cantidad > 0 ? $montoNeto / $cantidad : $montoNeto;
                                        
                                        // Crear detalle de venta
                                        VentaProducto::create([
                                            'VentaId' => $venta->ID,
                                            'ProductoId' => $producto->ID,
                                            'Cantidad' => $cantidad,
                                            'PrecioVenta' => $precioUnitario,
                                            'MontoDivisa' => $productoSucursal->PvpDivisa,
                                            'TicketId' => 0,
                                        ]);
                                        
                                    } 
                                } 
                            }
                            
                            // Detectar fin de productos
                            if (isset($row[0]) && (
                                empty($row[0]) || 
                                str_contains(strtolower($row[0]), 'total') ||
                                str_contains(strtolower($row[0]), 'gran total') ||
                                str_contains(strtolower($row[0]), 'ventas diarias'))) {
                                break 2; // Salir del foreach completamente
                            }
                            break;
                    }
                }
                
                if ($venta) {
                    $totalProductos = VentaProducto::where('VentaId', $venta->ID)->count();
                    $totalVenta = VentaProducto::where('VentaId', $venta->ID)->sum('MontoDivisa');
                    
                    return $venta;
                } else {
                    throw new \Exception("No se pudo crear la venta. Formato de archivo incorrecto.");
                }

            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

    private function procesarVentasPorVendedor($fileOrRows, $sucursalId, $saleDate)
    {
        // $spreadsheet = IOFactory::load($file->getPathname());
        // $sheet = $spreadsheet->getActiveSheet();
        // $rows = $sheet->toArray();

        // Determinar si es archivo o array
        if ($fileOrRows instanceof \Illuminate\Http\UploadedFile) {
            // Es un archivo (comportamiento original)
            $spreadsheet = IOFactory::load($fileOrRows->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } else {
            // Es un array (nuevo comportamiento)
            $rows = $fileOrRows;
        }

        DB::transaction(function () use ($rows, $sucursalId, $saleDate) {
            try {
                Log::info("=== INICIANDO PROCESAMIENTO VENTAS POR VENDEDOR ===");
                Log::info("Total de filas: " . count($rows));

                $vendedorActual = null;
                $nombreVendedor = null;
                $enDetallesVendedor = false;
                $vendedorCount = 0;
                $ventaCount = 0;
                $productoCount = 0;
                $documentoActual = null;

                foreach ($rows as $i => $row) {
                    $row = array_map(function($cell) {
                        if (is_null($cell) || $cell === '') return null;
                        if (is_numeric($cell)) return $cell;
                        return trim((string)$cell);
                    }, $row);

                    // Saltar filas completamente vacías
                    if (!array_filter($row, fn($c) => !is_null($c))) {
                        continue;
                    }

                    // 1. BUSCAR VENDEDORES
                    $vendedorEncontrado = false;
                    foreach ($row as $cell) {
                        if (is_string($cell)) {
                            $cell = trim($cell);
                            
                            if (preg_match('/VDD\d+/i', $cell)) {
                                if (preg_match('/(VDD\d+[A-Z]*)/i', $cell, $matchesCodigo)) {
                                    $codigoVendedor = strtoupper(trim($matchesCodigo[1]));
                                    
                                    $nombre = '';
                                    if (preg_match('/VDD\d+[A-Z]*\s*[-\s]+\s*(.+)/i', $cell, $matchesNombre)) {
                                        $nombre = trim($matchesNombre[1]);
                                    } else {
                                        $nombre = "Vendedor " . $codigoVendedor;
                                    }
                                    
                                    $vendedorActual = $codigoVendedor;
                                    $nombreVendedor = $nombre;
                                    $vendedorCount++;
                                    $enDetallesVendedor = true;
                                    $documentoActual = null; // Reiniciar documento al cambiar de vendedor
                                    
                                    Log::info("👤 Vendedor {$vendedorCount}: {$vendedorActual} - {$nombreVendedor} en fila {$i}");
                                    $vendedorEncontrado = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($vendedorEncontrado) {
                        continue;
                    }

                    // 2. PROCESAR DETALLES (solo si tenemos un vendedor activo)
                    if ($vendedorActual && $enDetallesVendedor) {
                        
                        // 2A. BUSCAR DOCUMENTOS/FACTURAS (F0000xxxx)
                        $documentoEncontrado = false;
                        foreach ($row as $cell) {
                            if (is_string($cell) && preg_match('/^F0000\d+$/i', trim($cell))) {
                                $documentoActual = trim($cell);
                                $documentoEncontrado = true;
                                
                                // Buscar fecha en esta fila
                                $fechaDoc = '';
                                foreach ($row as $cell2) {
                                    if (is_string($cell2) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', trim($cell2))) {
                                        $fechaDoc = trim($cell2);
                                        break;
                                    }
                                }
                                
                                // Buscar monto total del documento
                                $montoDocumento = 0;
                                foreach ($row as $cell2) {
                                    if (is_numeric($cell2) && $cell2 > 100 && $cell2 < 50000) {
                                        $montoDocumento = floatval($cell2);
                                        break;
                                    }
                                }
                                
                                if ($montoDocumento > 0) {
                                    $ventaCount++;
                                    Log::info("📄 Venta {$ventaCount}: Doc: {$documentoActual}, Fecha: {$fechaDoc}, Monto: {$montoDocumento}, Vendedor: {$vendedorActual}");
                                }
                                break;
                            }
                        }

                        // Si encontramos un documento, saltamos el procesamiento de productos en esta fila
                        if ($documentoEncontrado) {
                            continue;
                        }

                        // 2B. BUSCAR PRODUCTOS (solo si tenemos un documento activo)
                        // Los códigos de producto son como: LA1635, GZ1908, TEN88, C166, GT10, etc.
                        // PERO NO documentos (F0000xxx)
                        foreach ($row as $cell) {
                            if (is_string($cell) && !empty(trim($cell))) {
                                $cellTrim = trim($cell);
                                
                                // Patrón para códigos de producto (NO documentos)
                                if (preg_match('/^[A-Z]{1,3}\d+$/i', $cellTrim) || 
                                    preg_match('/^[A-Z]{1,3}\d+[A-Z]?$/i', $cellTrim)) {
                                    
                                    // Excluir documentos que coincidan con patrón
                                    if (preg_match('/^F0000/i', $cellTrim)) {
                                        continue;
                                    }
                                    
                                    $codigoProducto = $cellTrim;
                                    
                                    // Buscar descripción
                                    $descripcion = '';
                                    foreach ($row as $cell2) {
                                        if (is_string($cell2) && $cell2 !== $codigoProducto && 
                                            strlen(trim($cell2)) > 10 &&
                                            !preg_match('/^\d/', trim($cell2)) &&
                                            !preg_match('/^[A-Z]{1,4}\d+/i', trim($cell2))) {
                                            $descripcion = trim($cell2);
                                            break;
                                        }
                                    }
                                    
                                    // Buscar precio unitario
                                    $precioUnitario = 0;
                                    foreach ($row as $cell2) {
                                        if (is_numeric($cell2) && $cell2 > 100 && $cell2 < 20000) {
                                            $precioUnitario = floatval($cell2);
                                            break;
                                        }
                                    }
                                    
                                    // Cantidad (en tu archivo siempre parece ser 1)
                                    $cantidad = 1;
                                    
                                    if ($precioUnitario > 0) {
                                        $productoCount++;
                                        $totalProducto = $cantidad * $precioUnitario;
                                        Log::info("🛒 Producto {$productoCount}: Código: {$codigoProducto}, Desc: {$descripcion}, Cant: {$cantidad}, Precio: {$precioUnitario}, Total: {$totalProducto}, Vendedor: {$vendedorActual}, Documento: {$documentoActual}");
                                        
                                        $usuario = Usuario::where('VendedorId', $vendedorActual)
                                        ->where('EsActivo', 1)
                                        ->first();

                                        $producto = Producto::where('Codigo', $codigoProducto)->first();

                                        if ($usuario && $producto) {

                                            $productoSucursal = ProductoSucursal::where('ProductoId', $producto->ID)
                                                ->where('SucursalId', $sucursalId)
                                                ->where('Estatus', 1)
                                                ->first();

                                            // VentaVendedor::create([
                                            //     'VentaId' => $venta->ID,
                                            //     'ProductoId' => $producto->ID,
                                            //     'UsuarioId' => $usuario->ID,
                                            //     'Cantidad' => $cantidad,
                                            //     'Costo' => 0,
                                            //     'CostoDivisa' => $producto->CostoDivisa ?? 0,
                                            //     'PrecioVenta' => $productoSucursal->PvpBS ?? $precioUnitario,
                                            //     'MontoDivisa' => $productoSucursal->PvpDivisa ?? ($totalProducto / $exchangeRate),
                                            // ]);

                                            $venta = Venta::where('SucursalId', $sucursalId)
                                            ->whereDate('Fecha', $saleDate)
                                            ->orderByDesc('ID')
                                            ->first();

                                            if ($venta) {
                                                VentaVendedor::create([
                                                    'VentaId' => $venta->ID,
                                                    'ProductoId' => $producto->ID,
                                                    'UsuarioId' => $usuario->UsuarioId,
                                                    'Cantidad' => $cantidad,
                                                    'Costo' => 0,
                                                    'CostoDivisa' => $producto->CostoDivisa ?? 0,
                                                    'PrecioVenta' => $productoSucursal->PvpBS ?? $precioUnitario,
                                                    'MontoDivisa' => $productoSucursal->PvpDivisa ?? ($totalProducto / $exchangeRate),
                                                ]);
                                            } else {
                                                Log::error("❌ No se encontró la venta.");
                                                // O bien, lanzar una excepción si es necesario
                                            }

                                        } else {
                                            Log::warning("No se pudo insertar producto {$codigoProducto}: " . 
                                                        ($usuario ? '' : 'usuario no encontrado ') . 
                                                        ($producto ? '' : 'producto no encontrado'));
                                        }
                                    }
                                    break;
                                }
                            }
                        }

                        // Detectar fin de sección de vendedor
                        $rowText = strtolower(implode(' ', array_filter($row, fn($c) => is_string($c))));
                        if (str_contains($rowText, 'total documentos') ||
                            str_contains($rowText, 'total efectivo') ||
                            str_contains($rowText, 'total ingresos')) {
                            
                            Log::info("📊 Fin de sección para vendedor: {$vendedorActual}");
                            $enDetallesVendedor = false;
                            $documentoActual = null; // Reiniciar documento al final de la sección
                        }
                    }
                }

                Log::info("=== RESUMEN FINAL ===");
                Log::info("Total vendedores encontrados: {$vendedorCount}");
                Log::info("Total ventas/documentos procesados: {$ventaCount}");
                Log::info("Total productos procesados: {$productoCount}");
                Log::info("=== PROCESAMIENTO COMPLETADO ===");

            } catch (\Exception $e) {
                Log::error("❌ Error en procesamiento: " . $e->getMessage());
                Log::error("❌ Trace: " . $e->getTraceAsString());
                throw $e;
            }
        });
    }

    // private function cargarExcelDefinitivo($file)
    // {
    //     $ext = strtolower($file->getClientOriginalExtension());
    //     $nombre = $file->getClientOriginalName();
    //     $path = $file->getPathname();
        
    //     \Log::info("📁 Procesando: {$nombre} (.{$ext})");

    //     // CASO 1: .XLSX - MODERNO (SIEMPRE FUNCIONA)
    //     if ($ext === 'xlsx') {
    //         $reader = new Xlsx();
    //         $reader->setReadDataOnly(true);
    //         $spreadsheet = $reader->load($path);
    //         return $spreadsheet->getActiveSheet()->toArray();
    //     }
        
    //     // CASO 2: .XLS - VERIFICAR TIPO
    //     if ($ext === 'xls') {
    //         $content = file_get_contents($path);
    //         $header = substr($content, 0, 8);
    //         $firmaHex = bin2hex($header);
            
    //         \Log::info("🔍 Firma .XLS: {$firmaHex}");
            
    //         // DETECTAR TIPO DE ARCHIVO .XLS
    //         if ($firmaHex === 'd0cf11e0a1b11ae1') {
    //             // ✅ Excel 97-2003 (BIFF8) - SOPORTADO
    //             $reader = new Xls();
    //             $reader->setReadDataOnly(true);
    //             $spreadsheet = $reader->load($path);
    //             return $spreadsheet->getActiveSheet()->toArray();
                
    //         } elseif (in_array($firmaHex, ['0904060000001000', '0904060000000000', '0904060000000200'])) {
    //             // ⚠️ Excel 4.0 (BIFF4) - NO SOPORTADO POR PHPSPREADSHEET
    //             throw new \Exception(
    //                 "❌ **EXCEL MUY ANTIGUO DETECTADO**\n\n" .
    //                 "El archivo '{$nombre}' es Excel 4.0 (1992-1994).\n" .
    //                 "Este formato **no es compatible** con el sistema.\n\n" .
    //                 "🔧 **SOLUCIONES:**\n" .
    //                 "1. **Ábralo en Excel moderno** y guárdelo como .XLSX\n" .
    //                 "2. **Expórtelo como CSV** con punto y coma (;)\n" .
    //                 "3. **Use Excel 2003 o superior** para generar el archivo\n\n" .
    //                 "✅ **Formatos compatibles:** .XLSX y .XLS (Excel 97-2003)"
    //             );
                
    //         } elseif ($firmaHex === '0908060000001000') {
    //             // ⚠️ Excel 2.x, 3.0 (BIFF2/BIFF3) - NO SOPORTADO
    //             // PERO SABEMOS QUE TIENE DATOS - INTENTAR EXTRAER TEXTO
                
    //             \Log::warning("📜 Excel 2.x/3.0 detectado, intentando extraer texto...");
                
    //             $rows = $this->extraerTextoDeBiffAntiguo($content);
                
    //             if (!empty($rows) && count($rows) > 1) {
    //                 \Log::info("✅ Texto extraído de Excel antiguo", [
    //                     'filas' => count($rows),
    //                     'columnas' => !empty($rows[0]) ? count($rows[0]) : 0
    //                 ]);
                    
    //                 return $rows;
    //             }
                
    //             throw new \Exception(
    //                 "❌ **EXCEL MUY ANTIGUO (2.x/3.0)**\n\n" .
    //                 "El archivo '{$nombre}' es de Excel 2.x o 3.0 (1987-1990).\n" .
    //                 "**Este formato tiene más de 30 años** y no es compatible.\n\n" .
    //                 "🔧 **SOLUCIONES INMEDIATAS:**\n" .
    //                 "1. **Ábralo en Excel moderno** → Archivo → Guardar como → .XLSX\n" .
    //                 "2. **Copie los datos** a un archivo nuevo de Excel\n" .
    //                 "3. **Expórtelo como CSV**\n\n" .
    //                 "📞 **Si el sistema origen es muy antiguo,** contacte al proveedor\n" .
    //                 "   para que actualice la exportación a un formato moderno."
    //             );
                
    //         } else {
    //             // ❌ FORMATO DESCONOCIDO
    //             throw new \Exception(
    //                 "❌ **FORMATO DE ARCHIVO DESCONOCIDO**\n\n" .
    //                 "El archivo '{$nombre}' no es un Excel reconocido.\n" .
    //                 "Firma detectada: {$firmaHex}\n\n" .
    //                 "🔧 **POR FAVOR:**\n" .
    //                 "1. Guarde como .XLSX (Excel 2007+)\n" .
    //                 "2. O use Excel 97-2003 para generar el .XLS\n" .
    //                 "3. Verifique que el archivo no esté corrupto"
    //             );
    //         }
    //     }
        
    //     throw new \Exception("Tipo de archivo no soportado: .{$ext}");
    // }

    // private function extraerTextoDeBiffAntiguo($content)
    // {
    //     // EXTRAER TODO EL TEXTO ASCII DEL ARCHIVO BIFF ANTIGUO
    //     $rows = [];
    //     $currentRow = [];
    //     $currentCell = '';
        
    //     for ($i = 0; $i < strlen($content); $i++) {
    //         $char = $content[$i];
    //         $ord = ord($char);
            
    //         // Caracteres imprimibles ASCII
    //         if ($ord >= 32 && $ord <= 126) {
    //             $currentCell .= $char;
    //         }
    //         // Tabulador, nueva línea, retorno de carro
    //         elseif ($ord == 9 || $ord == 10 || $ord == 13) {
    //             if (!empty($currentCell)) {
    //                 $currentRow[] = trim($currentCell);
    //                 $currentCell = '';
    //             }
                
    //             if ($ord == 10 || $ord == 13) { // Nueva línea
    //                 if (!empty($currentRow)) {
    //                     $rows[] = $currentRow;
    //                     $currentRow = [];
    //                 }
    //             }
    //         }
    //         // Byte nulo o control - final de celda
    //         elseif ($ord == 0 || $ord < 32) {
    //             if (!empty($currentCell)) {
    //                 $currentRow[] = trim($currentCell);
    //                 $currentCell = '';
    //             }
    //         }
    //     }
        
    //     // Agregar última celda/fila si existe
    //     if (!empty($currentCell)) {
    //         $currentRow[] = trim($currentCell);
    //     }
    //     if (!empty($currentRow)) {
    //         $rows[] = $currentRow;
    //     }
        
    //     // Filtrar filas vacías y limpiar
    //     $rows = array_filter($rows, function($row) {
    //         return !empty(array_filter($row, function($cell) {
    //             return !empty(trim($cell));
    //         }));
    //     });
        
    //     $rows = array_values($rows);
        
    //     \Log::info("📝 Texto extraído de BIFF antiguo", [
    //         'total_filas' => count($rows),
    //         'ejemplo_fila_1' => !empty($rows[0]) ? $rows[0] : [],
    //         'ejemplo_fila_2' => !empty($rows[1]) ? $rows[1] : []
    //     ]);
        
    //     return $rows;
    // }

    private function convertidor(UploadedFile $file): string
    {
        $apiKey = env('CLOUDCONVERT_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception("CLOUDCONVERT_API_KEY no configurada en .env");
        }
        
        $originalName = $file->getClientOriginalName();
        
        \Log::info("🔄 CloudConvert con API Key válida: {$originalName}");
        
        $client = new \GuzzleHttp\Client([
            'timeout' => 60,
        ]);
        
        try {
            // 1️⃣ CREAR JOB
            \Log::info("📋 Creando job...");
            
            $jobResponse = $client->post('https://api.cloudconvert.com/v2/jobs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel-Excel-Converter/1.0'
                ],
                'json' => [
                    'tasks' => [
                        'upload' => [
                            'operation' => 'import/upload',
                            'filename' => $originalName
                        ],
                        'convert' => [
                            'operation' => 'convert',
                            'input' => ['upload'],
                            'output_format' => 'xlsx',
                            'engine' => 'libreoffice'
                        ],
                        'export' => [
                            'operation' => 'export/url',
                            'input' => ['convert']
                        ]
                    ]
                ]
            ]);
            
            $jobData = json_decode($jobResponse->getBody(), true);
            
            if (!isset($jobData['data']['id'])) {
                throw new \Exception("No se pudo crear el job: " . json_encode($jobData));
            }
            
            $jobId = $jobData['data']['id'];
            \Log::info("✅ Job creado: {$jobId}");
            
            // 2️⃣ OBTENER INFO DE UPLOAD
            $uploadTask = $jobData['data']['tasks'][0];
            $uploadInfo = $uploadTask['result'];
            
            if (!isset($uploadInfo['form'])) {
                throw new \Exception("No se recibió formulario de upload: " . json_encode($uploadInfo));
            }
            
            $uploadUrl = $uploadInfo['form']['url'];
            $formParams = $uploadInfo['form']['parameters'];
            
            \Log::info("📤 URL Upload: {$uploadUrl}");
            \Log::info("📋 Parámetros: " . json_encode($formParams));
            
            // 3️⃣ PREPARAR MULTIPART DATA (INCLUYENDO KEY)
            $multipart = [];
            
            // Primero agregar los parámetros del formulario
            foreach ($formParams as $key => $value) {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value
                ];
            }
            
            // Luego agregar el archivo (DEBE ser el último)
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $originalName,
                'headers' => [
                    'Content-Type' => 'application/vnd.ms-excel'
                ]
            ];
            
            // 4️⃣ SUBIR ARCHIVO
            \Log::info("⬆️ Subiendo archivo...");
            
            $uploadResponse = $client->post($uploadUrl, [
                'multipart' => $multipart,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);
            
            \Log::info("✅ Archivo subido. Código: " . $uploadResponse->getStatusCode());
            
            // 5️⃣ ESPERAR CONVERSIÓN (con polling)
            \Log::info("⏳ Esperando conversión...");
            
            $maxAttempts = 20; // 20 intentos × 3 segundos = 60 segundos máximo
            $converted = false;
            $downloadUrl = null;
            
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                sleep(3); // Esperar 3 segundos entre intentos
                
                $statusResponse = $client->get("https://api.cloudconvert.com/v2/jobs/{$jobId}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Accept' => 'application/json'
                    ]
                ]);
                
                $statusData = json_decode($statusResponse->getBody(), true);
                $jobStatus = $statusData['data']['status'];
                
                \Log::info("📊 Intento {$attempt}/{$maxAttempts} - Estado: {$jobStatus}");
                
                if ($jobStatus === 'finished') {
                    // Buscar task de export
                    foreach ($statusData['data']['tasks'] as $task) {
                        if ($task['operation'] === 'export/url' && isset($task['result']['files'][0]['url'])) {
                            $downloadUrl = $task['result']['files'][0]['url'];
                            $converted = true;
                            \Log::info("✅ Conversión completada. URL: {$downloadUrl}");
                            break 2;
                        }
                    }
                } elseif ($jobStatus === 'error') {
                    $errorMsg = $statusData['data']['message'] ?? 'Error desconocido en CloudConvert';
                    throw new \Exception("Error en conversión: {$errorMsg}");
                }
            }
            
            if (!$converted) {
                throw new \Exception("Timeout: La conversión no se completó en 60 segundos");
            }
            
            // 6️⃣ DESCARGAR ARCHIVO CONVERTIDO
            \Log::info("💾 Descargando archivo convertido...");
            
            $downloadResponse = $client->get($downloadUrl);
            $convertedContent = $downloadResponse->getBody();
            
            // 7️⃣ GUARDAR TEMPORALMENTE
            $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
            file_put_contents($tempPath, $convertedContent);
            
            $fileSize = filesize($tempPath);
            \Log::info("💾 Archivo guardado: {$tempPath} ({$fileSize} bytes)");
            
            // Verificar que sea un XLSX válido
            if ($fileSize < 100) { // XLSX vacío o error
                $content = file_get_contents($tempPath, false, null, 0, 100);
                if (strpos($content, 'Error') !== false || strpos($content, '<?xml') === false) {
                    throw new \Exception("El archivo convertido parece inválido");
                }
            }
            
            return $tempPath;
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorDetails = "Error CloudConvert: ";
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();
                
                $errorDetails .= "HTTP {$statusCode} - ";
                
                // Intentar parsear JSON de error
                $errorData = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($errorData['message'])) {
                    $errorDetails .= $errorData['message'];
                    if (isset($errorData['errors'])) {
                        $errorDetails .= " - " . json_encode($errorData['errors']);
                    }
                } else {
                    $errorDetails .= $body;
                }
                
                \Log::error("CloudConvert Response: {$body}");
            } else {
                $errorDetails .= $e->getMessage();
            }
            
            throw new \Exception($errorDetails);
            
        } catch (\Exception $e) {
            \Log::error("Error en convertidor: " . $e->getMessage());
            throw $e;
        }
    }

    // Ventas por producto
    public function ventas_producto(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : null;

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
            : null;

        $filtroFecha = new ParametrosFiltroFecha(
            null,
            null,
            null,
            false,
            $fechaInicio,
            $fechaFin
        );

        // Menú activo
        session([
            'menu_active' => 'Análisis de Ventas',
            'submenu_active' => 'Ventas por producto'
        ]);

        // Sucursal activa
        $sucursalId = session('sucursal_id');

        if ($sucursalId && $sucursalId != 0) {
            $ventasDTO = VentasHelper::GenerarDatosdeVentasParaEscritorio($filtroFecha, $sucursalId);
        } else {
            $ventasDTO = null;
        }

        // dd($ventasDTO);
        // dd($ventasDTO['ProductosAgrupados']->first());

        return view('cpanel.ventas.ventas_producto', [
            'ventas' => $ventasDTO,
            'fechaInicio' => $filtroFecha->fechaInicio, 
            'fechaFin' => $filtroFecha->fechaFin,
        ]);
    }
}
