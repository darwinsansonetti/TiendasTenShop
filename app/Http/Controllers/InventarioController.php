<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;
use App\Models\Proveedor;
use App\Models\DivisaValor;
use App\Models\ProductoSucursal;
use App\Models\Producto;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

// use PhpOffice\PhpSpreadsheet\Reader\Xls;
// use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Symfony\Component\HttpFoundation\StreamedResponse;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use CloudConvert\Exceptions\ApiException;
use CloudConvert\CloudConvert;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;

use Illuminate\Support\Facades\Validator;

use App\Helpers\FileHelper;

class InventarioController extends Controller
{ 

    // Vista para cargar excel de inventarios
    public function mostrarCargaInventarios()
    {
        // Configurar menú activo
        session([
            'menu_active' => 'Inventario',
            'submenu_active' => 'Cargar Inventario'
        ]);
    
        return view('cpanel.inventario.cargar_excel_inventario');
    }

    public function cargarExcel(Request $request)
    {
        set_time_limit(300);

        try {

            // 1. Validar archivo y sucursal
            $request->validate([
                'excel_file' => 'required|file|max:10240',
                'sucursal_id' => 'required|integer|min:1'
            ]);

            $productosIngresados = 0;

            $file = $request->file('excel_file');
            $sucursalId = $request->input('sucursal_id');
            $extension = strtolower($file->getClientOriginalExtension());

            // 2. Convertir .xls a .xlsx si es necesario
            if ($extension === 'xls') {
                $xlsxPath = $this->convertidor($file);
                $xlsxFile = new UploadedFile(
                    $xlsxPath,
                    basename($xlsxPath),
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    null,
                    true
                );
            } else {
                $xlsxFile = $file;
            }

            // 3. Leer el Excel
            $spreadsheet = IOFactory::load($xlsxFile->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if ($extension === 'xls' && isset($xlsxPath)) {
                @unlink($xlsxPath);
            }

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo está vacío'
                ], 400);
            }

            // 4. Buscar "Inventario fisico"
            $foundInventario = false;
            $headerRowIndex = -1;
            $dataStartRowIndex = -1;

            for ($i = 0; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row)) continue;

                foreach ($row as $cell) {
                    if (is_string($cell) && strtolower(trim($cell)) === 'inventario fisico') {
                        $foundInventario = true;
                        $headerRowIndex = $i + 1;
                        $dataStartRowIndex = $i + 2;
                        break 2;
                    }
                }
            }

            if (!$foundInventario) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no contiene la palabra "Inventario fisico" en la primera fila'
                ], 400);
            }

            // 5. Identificar encabezados
            if ($headerRowIndex < 0 || $headerRowIndex >= count($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron encabezados en el archivo'
                ], 400);
            }

            $headers = $rows[$headerRowIndex];
            $headers = array_map('strtoupper', array_map('trim', $headers));

            $colCodigo = null;
            $colReferencia = null;
            $colDescripcion = null;
            $colUnidad = null;

            foreach ($headers as $index => $header) {
                $headerClean = trim($header);
                $headerLower = strtolower($headerClean);
                
                if (strpos($headerLower, 'codigo') !== false || strpos($headerLower, 'código') !== false) {
                    $colCodigo = $index;
                }
                if (strpos($headerLower, 'referencia') !== false) {
                    $colReferencia = $index;
                }
                if (strpos($headerLower, 'descripcion') !== false || strpos($headerLower, 'descripción') !== false) {
                    $colDescripcion = $index;
                }
                if (strpos($headerLower, 'unidad') !== false) {
                    $colUnidad = $index;
                }
            }

            if ($colCodigo === null) {
                $colCodigo = 0;
            }
            if ($colReferencia === null) {
                $colReferencia = 2;
            }
            if ($colDescripcion === null) {
                $colDescripcion = 3;
            }

            $colExistencia = 15;

            // ============================================================
            // 6. INICIAR TRANSACCIÓN
            // ============================================================
            DB::connection('sqlsrv')->beginTransaction();

            try {
                // 6.1 Obtener TODOS los productos de la sucursal en una sola consulta
                $productosSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('SucursalId', $sucursalId)
                    // ->where('Estatus', 1)
                    ->get()
                    ->keyBy('ProductoId');

                // 6.2 Obtener TODOS los productos de la base de datos en una sola consulta
                $todosProductos = DB::connection('sqlsrv')
                    ->table('Productos')
                    ->where('Estatus', 1)
                    ->get()
                    ->keyBy('Codigo');

                // ============================================================
                // 6.3 LOG DE DEPURACIÓN: VERIFICAR ESTRUCTURA DEL EXCEL
                // ============================================================
                $primerosRegistros = [];
                for ($i = $dataStartRowIndex; $i < min($dataStartRowIndex + 10, count($rows)); $i++) {
                    $row = $rows[$i];
                    if (empty(array_filter($row))) continue;
                    
                    $codigo = trim($row[$colCodigo] ?? '');
                    $referencia = isset($row[$colReferencia]) ? trim($row[$colReferencia]) : '';
                    $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : '';
                    $existencia = isset($row[$colExistencia]) ? trim($row[$colExistencia]) : '';
                    
                    if (!empty($codigo) || !empty($referencia)) {
                        $primerosRegistros[] = [
                            'fila' => $i + 1,
                            'codigo' => $codigo,
                            'referencia' => $referencia,
                            'descripcion' => $descripcion,
                            'cantidad' => $existencia
                        ];
                    }
                }

                // 6.4 Procesar datos
                $productos = [];
                $noEncontrados = [];
                $actualizados = 0;
                $totalFilas = 0;
                $errores = [];
                $productosAuditoria = [];
                $batchUpdates = [];

                // Contadores para depuración
                $codigosProcesados = [];
                $productosEncontrados = 0;
                $productosNoEncontrados = 0;
                $productosNoEnSucursal = 0;

                $startTime = microtime(true);

                for ($i = $dataStartRowIndex; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $codigo = trim($row[$colCodigo] ?? '');
                    $referencia = isset($row[$colReferencia]) ? trim($row[$colReferencia]) : '';
                    $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : '';
                    
                    // ============================================================
                    // ✅ FILTRAR FILAS NO VÁLIDAS
                    // ============================================================
                    
                    // 1. Saltar filas con "Total Registros" en cualquier columna
                    $esFilaTotal = false;
                    foreach ($row as $celda) {
                        if (is_string($celda) && stripos($celda, 'total registros') !== false) {
                            $esFilaTotal = true;
                            break;
                        }
                    }
                    if ($esFilaTotal) {
                        continue;
                    }
                    
                    // 2. Saltar filas con solo números sueltos (sin letras)
                    if (!empty($codigo) && is_numeric($codigo) && !preg_match('/[A-Za-z]/', $codigo)) {
                        continue;
                    }
                    
                    // 3. Saltar filas con solo guiones bajos en descripción
                    if (trim($descripcion) === '_________________') {
                        continue;
                    }
                    
                    // 4. Saltar si no hay código, referencia ni descripción
                    if (empty($codigo) && empty($referencia) && empty($descripcion)) {
                        continue;
                    }
                    
                    // 5. Saltar si el código es solo número sin letras y no hay referencia
                    if (!empty($codigo) && !preg_match('/[A-Za-z]/', $codigo) && empty($referencia)) {
                        continue;
                    }
                    
                    // 6. Si la descripción es un número suelto (total de filas)
                    if (!empty($descripcion) && is_numeric(trim($descripcion)) && empty($codigo) && empty($referencia)) {
                        continue;
                    }
                    
                    // ============================================================
                    // Caso: Sin Código ni Referencia (pero con descripción válida)
                    // ============================================================
                    if (empty($codigo) && empty($referencia) && !empty($descripcion)) {
                        // Verificar que la descripción no sea un número o un total
                        if (is_numeric(trim($descripcion)) || stripos($descripcion, 'total') !== false) {
                            continue;
                        }
                        
                        $existencia = isset($row[$colExistencia]) ? (int) trim($row[$colExistencia]) : 0;
                        
                        $productosAuditoria[] = [
                            'sucursal_id' => $sucursalId,
                            'producto_id' => null,
                            'codigo' => null,
                            'referencia' => null,
                            'descripcion' => $descripcion ?: 'Sin código ni referencia',
                            'cantidad' => $existencia < 0 ? 0 : $existencia,
                            'existencia_anterior' => null,
                            'motivo' => 'Producto sin código ni referencia'
                        ];
                        continue;
                    }
                    
                    // Caso: Sin Código ni Referencia (sin descripción válida)
                    if (empty($codigo) && empty($referencia)) {
                        continue;
                    }

                    $totalFilas++;

                    // Guardar código para depuración
                    if (!empty($codigo)) {
                        $codigosProcesados[] = $codigo;
                    }

                    // Obtener existencia
                    $existenciaRaw = isset($row[$colExistencia]) ? trim($row[$colExistencia]) : '';
                    $existencia = str_replace(',', '', $existenciaRaw);
                    $existencia = str_replace(' ', '', $existencia);

                    // Validar existencia
                    if ($existencia === '' || $existencia === null) {
                        $existencia = 0;
                    }

                    if (!is_numeric($existencia)) {
                        $errores[] = "Fila " . ($i + 1) . ": Existencia inválida para producto " . ($codigo ?: $referencia);
                        continue;
                    }

                    if ($existencia < 0) {
                        $existencia = 0;
                    }

                    // ============================================================
                    // BUSCAR PRODUCTO (en memoria, no en BD)
                    // ============================================================
                    $producto = null;

                    // 1. BUSCAR POR CÓDIGO
                    if (!empty($codigo)) {
                        $producto = $todosProductos->get($codigo);
                        if ($producto) {
                            $productosEncontrados++;
                        } else {
                            $productosNoEncontrados++;
                        }
                    }

                    // 2. SOLO si NO hay código, buscar por referencia
                    if (!$producto && empty($codigo) && !empty($referencia)) {
                        // Buscar en la colección en memoria
                        $productosPorReferencia = $todosProductos->filter(function($p) use ($referencia) {
                            return $p->Referencia == $referencia;
                        });

                        if ($productosPorReferencia->count() === 1) {
                            $producto = $productosPorReferencia->first();
                            $productosEncontrados++;
                        } elseif ($productosPorReferencia->count() > 1) {
                            // Verificar cuáles existen en la sucursal
                            $productosEnSucursal = $productosPorReferencia->filter(function($p) use ($productosSucursal) {
                                return $productosSucursal->has($p->ID);
                            });

                            if ($productosEnSucursal->count() === 1) {
                                $producto = $productosEnSucursal->first();
                                $productosEncontrados++;
                            } elseif ($productosEnSucursal->count() > 1) {
                                $codigos = $productosEnSucursal->pluck('Codigo')->filter()->toArray();
                                $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : 'Referencia duplicada';
                                
                                foreach ($productosEnSucursal as $p) {
                                    $productosAuditoria[] = [
                                        'sucursal_id' => $sucursalId,
                                        'producto_id' => $p->ID,
                                        'codigo' => $p->Codigo ?? '',
                                        'referencia' => $referencia,
                                        'descripcion' => $descripcion,
                                        'cantidad' => (int) $existencia,
                                        'existencia_anterior' => $productosSucursal->get($p->ID)->Existencia ?? 0,
                                        'motivo' => "Referencia duplicada - Seleccione este producto para actualizar"
                                    ];
                                }
                                continue;
                            } else {
                                $codigos = $productosPorReferencia->pluck('Codigo')->filter()->implode(', ');
                                $errores[] = "Fila " . ($i + 1) . ": Ningún producto con referencia '$referencia' existe en la sucursal seleccionada. Productos disponibles: " . $codigos;
                                continue;
                            }
                        }
                    }

                    if (!$producto) {
                        $noEncontrados[] = $codigo ?: $referencia;
                        continue;
                    }

                    // Guardar para el inventario teórico
                    $productoData = [
                        'codigo' => $producto->Codigo ?? '',
                        'referencia' => $producto->Referencia ?? '',
                        'existencia' => (int) $existencia,
                        'producto_id' => $producto->ID
                    ];

                    if ($colDescripcion !== null && isset($row[$colDescripcion])) {
                        $productoData['descripcion'] = trim($row[$colDescripcion]);
                    }

                    $productos[] = $productoData;

                    // ✅ Verificar si existe en ProductoSucursal (en memoria)
                    if ($productosSucursal->has($producto->ID)) {
                        // Agregar a batch para actualización
                        $batchUpdates[] = [
                            'ProductoId' => $producto->ID,
                            'Existencia' => (int) $existencia
                        ];
                        $actualizados++;
                    } else {
                        // ✅ El producto no existe en la sucursal, buscar para clonar
                        \Log::info('📋 Buscando producto para clonar en sucursal: ' . $sucursalId . ' - Producto: ' . $producto->Codigo);
                        
                        // Buscar el producto en otras sucursales, ordenado por PvpDivisa DESC (el más alto primero)
                        $productosExistentes = DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $producto->ID)
                            ->where('Estatus', 1)
                            ->orderBy('PvpDivisa', 'desc')  // ✅ El más alto primero
                            ->get();
                        
                        $productoClonar = null;
                        $pvpDivisa = 0;
                        $pvpBs = 0;
                        
                        if ($productosExistentes->isNotEmpty()) {
                            // ✅ Tomar el primero (el que tiene PvpDivisa más alto)
                            $productoClonar = $productosExistentes->first();
                            $pvpDivisa = $productoClonar->PvpDivisa ?? 0;
                            $pvpBs = $productoClonar->PvpBs ?? 0;
                            
                            \Log::info('✅ Producto encontrado con PvpDivisa: ' . $pvpDivisa . ' (Sucursal: ' . $productoClonar->SucursalId . ' - PvpBs: ' . $pvpBs . ')');
                        }
                        
                        if ($productoClonar) {

                            // ✅ Clonar el producto con los valores obtenidos
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->insert([
                                    'SucursalId' => $sucursalId,
                                    'ProductoId' => $producto->ID,
                                    'PvpBs' => $pvpBs,
                                    'PvpDivisa' => $pvpDivisa,
                                    'Estatus' => 1,
                                    'Existencia' => (int) $existencia,
                                    'FechaIngreso' => $productoClonar->FechaIngreso ?? now(),
                                    'FechaUltimaVenta' => null,
                                    'Sobreventa' => null,
                                    'NuevoPvp' => 0.00,
                                    'FechaNuevoPrecio' => null,
                                    'Tipo' => null,
                                    'PvpAnterior' => 0.00,
                                    'FechaBajaPrecio' => null,
                                    'FechaSubePrecio' => null
                                ]);
                            $actualizados++;
                            $productosIngresados++;
                            \Log::info('✅ Producto clonado en sucursal: ' . $sucursalId . ' - Producto: ' . $producto->Codigo . ' - PvpDivisa: ' . $pvpDivisa . ' - PvpBs: ' . $pvpBs);
                        } else {
                            // ❌ No se encontró el producto en ninguna sucursal, crear desde cero
                            \Log::warning('⚠️ Producto no encontrado en ninguna sucursal, creando desde cero: ' . $producto->Codigo);
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->insert([
                                    'SucursalId' => $sucursalId,
                                    'ProductoId' => $producto->ID,
                                    'PvpBs' => 0.00,
                                    'PvpDivisa' => 0.00,
                                    'Estatus' => 1,
                                    'Existencia' => (int) $existencia,
                                    'FechaIngreso' => now(),
                                    'FechaUltimaVenta' => null,
                                    'Sobreventa' => null,
                                    'NuevoPvp' => 0.00,
                                    'FechaNuevoPrecio' => null,
                                    'Tipo' => null,
                                    'PvpAnterior' => 0.00,
                                    'FechaBajaPrecio' => null,
                                    'FechaSubePrecio' => null
                                ]);
                            $actualizados++;
                            \Log::info('✅ Producto creado desde cero en sucursal: ' . $sucursalId . ' - Producto: ' . $producto->Codigo);
                        }
                    }
                }

                // ============================================================
                // 6.6 EJECUTAR ACTUALIZACIONES EN BATCH
                // ============================================================
                if (!empty($batchUpdates)) {
                    \Log::info('📝 Actualizando ' . count($batchUpdates) . ' productos en batch real');

                    // Procesamos en chunks para no exceder el límite de parámetros de SQL Server (~2100)
                    $chunks = array_chunk($batchUpdates, 500);

                    foreach ($chunks as $chunk) {
                        // Case para Existencia
                        $caseSql = "CASE ProductoId ";
                        $caseBindings = [];

                        foreach ($chunk as $u) {
                            $caseSql .= "WHEN ? THEN ? ";
                            $caseBindings[] = $u['ProductoId'];
                            $caseBindings[] = $u['Existencia'];
                        }
                        $caseSql .= "END";

                        // ✅ Case para Estatus (siempre 1)
                        $caseEstatusSql = "CASE ProductoId ";
                        foreach ($chunk as $u) {
                            $caseEstatusSql .= "WHEN ? THEN 1 ";
                            $caseBindings[] = $u['ProductoId'];
                        }
                        $caseEstatusSql .= "END";

                        $ids = array_column($chunk, 'ProductoId');
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));

                        $sql = "UPDATE ProductoSucursal
                                SET Existencia = $caseSql,
                                    Estatus = $caseEstatusSql
                                WHERE SucursalId = ?
                                AND ProductoId IN ($placeholders)";

                        $bindings = array_merge($caseBindings, [$sucursalId], $ids);

                        DB::connection('sqlsrv')->update($sql, $bindings);
                    }

                    \Log::info('✅ Actualización batch completada (real, en ' . count($chunks) . ' lotes)');
                }


                // ============================================================
                // 6.7 CREAR AUDITORÍA SI HAY PRODUCTOS PROBLEMÁTICOS
                // ============================================================
                if (!empty($productosAuditoria)) {
                    $numeroAuditoria = 'AUD' . date('YmdHi') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

                    $auditoriaId = DB::connection('sqlsrv')
                        ->table('AuditoriaInventario')
                        ->insertGetId([
                            'SucursalId' => $sucursalId,
                            'Fecha' => now(),
                            'Numero' => $numeroAuditoria,
                            'Estatus' => 1
                        ]);

                    foreach ($productosAuditoria as $detalle) {
                        DB::connection('sqlsrv')
                            ->table('AuditoriaInventarioDetalles')
                            ->insert([
                                'AuditoriaInventarioId' => $auditoriaId,
                                'SucursalId' => $detalle['sucursal_id'],
                                'ProductoId' => $detalle['producto_id'],
                                'Codigo' => $detalle['codigo'],
                                'Referencia' => $detalle['referencia'],
                                'Descripcion' => $detalle['descripcion'],
                                'Cantidad' => $detalle['cantidad'],
                                'ExistenciaAnterior' => $detalle['existencia_anterior'],
                                'Estatus' => 1
                            ]);
                    }

                }

                // ============================================================
                // 6.8 COMMIT DE LA TRANSACCIÓN
                // ============================================================
                DB::connection('sqlsrv')->commit();

                $endTime = microtime(true);

            } catch (\Exception $e) {
                DB::connection('sqlsrv')->rollBack();
                \Log::error('❌ Error en transacción, rollback realizado: ' . $e->getMessage());
                throw $e;
            }

            // ============================================================
            // 7. CONSTRUIR MENSAJE DE RESPUESTA
            // ============================================================
            $mensaje = "✅ Inventario actualizado: {$actualizados} productos.";

            if (!empty($productosAuditoria)) {
                $mensaje .= " 📋 Auditoría #{$numeroAuditoria} creada con " . count($productosAuditoria) . " productos pendientes.";
            }

            if (!empty($noEncontrados)) {
                $lista = implode(', ', array_slice($noEncontrados, 0, 10));
                if (count($noEncontrados) > 10) {
                    $lista .= ' ... y ' . (count($noEncontrados) - 10) . ' más';
                }
                $mensaje .= " ⚠️ Productos no encontrados en la base de datos: {$lista}";
            }

            if (!empty($errores)) {
                $mensaje .= " ⚠️ " . count($errores) . " errores: " . implode('; ', array_slice($errores, 0, 5));
                if (count($errores) > 5) {
                    $mensaje .= " ... y " . (count($errores) - 5) . " más";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'total_filas' => $totalFilas,
                'actualizados' => $actualizados,
                'no_encontrados' => $noEncontrados,
                'errores' => $errores,
                'productos' => $productos,
                'auditoria_id' => $auditoriaId ?? null,
                'auditoria_numero' => $numeroAuditoria ?? null,
                'productos_auditoria' => count($productosAuditoria),
                'productos_ingresados' => $productosIngresados
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function convertidor(UploadedFile $file, $retryCount = 0): string
    {
        // Obtener todas las API Keys disponibles
        $allApiKeys = [
            env('CLOUDCONVERT_API_KEY'),
            env('CLOUDCONVERT_API_KEY_1'),
            env('CLOUDCONVERT_API_KEY_2'),
            env('CLOUDCONVERT_API_KEY_3'),
            // env('CLOUDCONVERT_API_KEY_4'),
            // Agrega más si es necesario
        ];
        
        // Filtrar keys vacías y obtener solo las que tienen valor
        $availableKeys = array_values(array_filter($allApiKeys, function($key) {
            return !empty($key);
        }));
        
        // Verificar que haya al menos una key
        if (empty($availableKeys)) {
            throw new \Exception("No hay API Keys de CloudConvert configuradas en .env");
        }
        
        // Si ya no hay más keys para intentar, lanzar error
        if ($retryCount >= count($availableKeys)) {
            throw new \Exception("Todas las API Keys de CloudConvert han fallado");
        }
        
        // Usar la key correspondiente según el reintento
        $apiKey = $availableKeys[$retryCount];
        
        if (!$apiKey) {
            throw new \Exception("CLOUDCONVERT_API_KEY no configurada en .env");
        }
        
        $originalName = $file->getClientOriginalName();
        
        \Log::info("🔄 CloudConvert con API Key válida: {$originalName} (Intento " . ($retryCount + 1) . ")");
        
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
                    
                    // 🔄 VERIFICAR SI ES ERROR POR CRÉDITOS Y REINTENTAR
                    if (strpos($errorData['message'], 'credits') !== false || 
                        strpos($errorData['message'], 'CREDITS_EXCEEDED') !== false ||
                        $statusCode === 402) {
                        
                        \Log::warning("⚠️ API Key sin créditos. Reintentando con siguiente API Key...");
                        
                        // Reintentar con la siguiente API Key
                        return $this->convertidor($file, $retryCount + 1);
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
            // 🔄 VERIFICAR SI EL ERROR ES POR CRÉDITOS EN OTROS CONTEXTOS
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'credits') !== false || 
                strpos($errorMessage, 'CREDITS_EXCEEDED') !== false) {
                
                \Log::warning("⚠️ API Key sin créditos. Reintentando con siguiente API Key...");
                
                // Reintentar con la siguiente API Key
                return $this->convertidor($file, $retryCount + 1);
            }
            
            \Log::error("Error en convertidor: " . $e->getMessage());
            throw $e;
        }
    }

    public function listadoInventarioAuditoria(Request $request)
    {
        try {
            session([
                'menu_active' => 'Inventario',
                'submenu_active' => 'Auditar Inventario'
            ]);
            
            // ✅ Calcular la fecha límite (6 meses atrás)
            $fechaLimite = now()->subMonths(6);
            
            // ✅ Obtener TODAS las auditorías (activas e inactivas) con Fecha > 6 meses
            $auditorias = DB::connection('sqlsrv')
                ->table('AuditoriaInventario as ai')
                ->leftJoin('Sucursales as s', 'ai.SucursalId', '=', 's.ID')
                ->whereIn('ai.Estatus', [0, 1])  // ✅ Activas e inactivas
                ->where('ai.Fecha', '>=', $fechaLimite)  // Solo auditorías de los últimos 6 meses
                ->select([
                    'ai.AuditoriaInventarioId',
                    'ai.SucursalId',
                    's.Nombre as sucursal_nombre',
                    'ai.Fecha',
                    'ai.Numero',
                    'ai.Estatus'
                ])
                ->orderBy('ai.Fecha', 'desc')
                ->get();
            
            // ✅ Obtener los detalles de cada auditoría
            foreach ($auditorias as $auditoria) {
                $auditoria->detalles = DB::connection('sqlsrv')
                    ->table('AuditoriaInventarioDetalles as aid')
                    ->leftJoin('Productos as p', 'aid.ProductoId', '=', 'p.ID')
                    ->where('aid.AuditoriaInventarioId', $auditoria->AuditoriaInventarioId)
                    ->select([
                        'aid.AuditoriaInventarioDetalleId',
                        'aid.ProductoId',
                        'aid.Codigo',
                        'aid.Referencia',
                        'aid.Descripcion',
                        'aid.Cantidad',
                        'aid.ExistenciaAnterior',
                        'aid.Estatus as detalle_estatus',
                        'p.Codigo as producto_codigo',
                        'p.Descripcion as producto_nombre'
                    ])
                    ->get();
                
                // ✅ Contar detalles por estatus
                $auditoria->total_pendientes = $auditoria->detalles->where('detalle_estatus', 1)->count();
                $auditoria->total_resueltos = $auditoria->detalles->where('detalle_estatus', 0)->count();
                $auditoria->total_rechazados = $auditoria->detalles->where('detalle_estatus', 2)->count();
                $auditoria->total_detalles = $auditoria->detalles->count();
                
                // ✅ Verificar si la auditoría tiene productos sin código ni referencia
                $auditoria->tiene_sin_codigo = $auditoria->detalles->whereNull('Codigo')->whereNull('Referencia')->count() > 0;
            }
            
            // ✅ Mapear el estatus
            $estatusMap = [
                1 => ['texto' => 'Activa', 'clase' => 'badge bg-success'],
                0 => ['texto' => 'Cerrada', 'clase' => 'badge bg-secondary']
            ];
            
            // ✅ Mapear estatus de detalles
            $detalleEstatusMap = [
                1 => ['texto' => 'Pendiente', 'clase' => 'badge bg-warning'],
                0 => ['texto' => 'Resuelto', 'clase' => 'badge bg-success'],
                2 => ['texto' => 'Rechazado', 'clase' => 'badge bg-danger']
            ];
            
            // ✅ Retornar la vista
            return view('cpanel.inventario.listado_auditoria', compact('auditorias', 'estatusMap', 'detalleEstatusMap'));
            
        } catch (\Exception $e) {
            \Log::error('Error en listadoInventarioAuditoria: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al cargar el listado de auditorías de inventario: ' . $e->getMessage());
        }
    }

    public function detalleAuditoriaInventario($id)
    {
        try {
            // 1. Obtener la auditoría
            $auditoria = DB::connection('sqlsrv')
                ->table('AuditoriaInventario as ai')
                ->leftJoin('Sucursales as s', 'ai.SucursalId', '=', 's.ID')
                ->where('ai.AuditoriaInventarioId', $id)
                ->select([
                    'ai.AuditoriaInventarioId',
                    'ai.SucursalId',
                    's.Nombre as sucursal_nombre',
                    'ai.Fecha',
                    'ai.Numero',
                    'ai.Estatus'
                ])
                ->first();

            if (!$auditoria) {
                return redirect()->route('cpanel.inventario.auditoria.listado')
                    ->with('error', 'Auditoría no encontrada');
            }

            // 2. Obtener los detalles de la auditoría
            $detalles = DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles as aid')
                ->leftJoin('Productos as p', 'aid.ProductoId', '=', 'p.ID')
                ->where('aid.AuditoriaInventarioId', $id)
                ->select([
                    'aid.AuditoriaInventarioDetalleId',
                    'aid.ProductoId',
                    'aid.Codigo',
                    'aid.Referencia',
                    'aid.Descripcion',
                    'aid.Cantidad',
                    'aid.ExistenciaAnterior',
                    'aid.Estatus as detalle_estatus',
                    'p.Codigo as producto_codigo',
                    'p.Descripcion as producto_nombre',
                    'p.Referencia as producto_referencia'
                ])
                ->orderBy('aid.AuditoriaInventarioDetalleId')
                ->get();

            // 3. Mapear estatus de detalles
            $detalleEstatusMap = [
                1 => ['texto' => 'Pendiente', 'clase' => 'badge bg-warning'],
                0 => ['texto' => 'Resuelto', 'clase' => 'badge bg-success'],
                2 => ['texto' => 'Rechazado', 'clase' => 'badge bg-danger']
            ];

            // 4. Contar pendientes
            $pendientes = $detalles->where('detalle_estatus', 1)->count();

            session([
                'menu_active' => 'Inventario',
                'submenu_active' => 'Auditar Inventario'
            ]);

            return view('cpanel.inventario.detalle_auditoria', compact(
                'auditoria',
                'detalles',
                'detalleEstatusMap',
                'pendientes'
            ));

        } catch (\Exception $e) {
            \Log::error('Error en detalleAuditoriaInventario: ' . $e->getMessage());
            return redirect()->route('cpanel.inventario.auditoria.listado')
                ->with('error', 'Error al cargar el detalle de la auditoría: ' . $e->getMessage());
        }
    }

    public function aceptarProductoAuditoria($auditoriaId, $detalleId)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();

            // 1. Obtener el detalle de auditoría
            $detalle = DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioDetalleId', $detalleId)
                ->where('AuditoriaInventarioId', $auditoriaId)
                ->first();

            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado']);
            }

            // 2. Verificar que esté pendiente
            if ($detalle->Estatus != 1) {
                return response()->json(['success' => false, 'message' => 'Este producto ya fue procesado']);
            }

            // 3. Si tiene ProductoId, actualizar la existencia (SOLO si existe)
            if ($detalle->ProductoId) {
                // Obtener el ProductoSucursal actual
                $productoSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('ProductoId', $detalle->ProductoId)
                    ->where('SucursalId', $detalle->SucursalId)
                    ->first();

                if ($productoSucursal) {
                    // ✅ Actualizar existencia
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->where('ProductoId', $detalle->ProductoId)
                        ->where('SucursalId', $detalle->SucursalId)
                        ->update([
                            'Existencia' => $detalle->Cantidad
                        ]);

                    \Log::info('Producto actualizado por auditoría', [
                        'auditoria_id' => $auditoriaId,
                        'producto_id' => $detalle->ProductoId,
                        'nueva_existencia' => $detalle->Cantidad
                    ]);
                } else {
                    // ❌ El producto no existe en la sucursal, NO se puede actualizar
                    DB::connection('sqlsrv')->rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El producto no existe en la sucursal seleccionada. No se puede actualizar.'
                    ], 400);
                }
            }

            // 4. Marcar detalle como resuelto
            DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioDetalleId', $detalleId)
                ->update([
                    'Estatus' => 0  // Resuelto
                ]);

            // 5. Verificar si todos los detalles están resueltos
            $pendientes = DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioId', $auditoriaId)
                ->where('Estatus', 1)
                ->count();

            // 6. Si no hay pendientes, cerrar la auditoría
            if ($pendientes == 0) {
                DB::connection('sqlsrv')
                    ->table('AuditoriaInventario')
                    ->where('AuditoriaInventarioId', $auditoriaId)
                    ->update([
                        'Estatus' => 0  // Cerrada
                    ]);

                \Log::info('Auditoría cerrada automáticamente', [
                    'auditoria_id' => $auditoriaId,
                    'motivo' => 'Todos los productos procesados'
                ]);
            }

            DB::connection('sqlsrv')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto aceptado correctamente',
                'pendientes' => $pendientes
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al aceptar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rechazarProductoAuditoria($auditoriaId, $detalleId)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();

            // 1. Obtener el detalle de auditoría
            $detalle = DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioDetalleId', $detalleId)
                ->where('AuditoriaInventarioId', $auditoriaId)
                ->first();

            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado']);
            }

            // 2. Verificar que esté pendiente
            if ($detalle->Estatus != 1) {
                return response()->json(['success' => false, 'message' => 'Este producto ya fue procesado']);
            }

            // 3. Marcar detalle como rechazado
            DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioDetalleId', $detalleId)
                ->update([
                    'Estatus' => 2  // Rechazado
                ]);

            // 4. Verificar si todos los detalles están resueltos
            $pendientes = DB::connection('sqlsrv')
                ->table('AuditoriaInventarioDetalles')
                ->where('AuditoriaInventarioId', $auditoriaId)
                ->where('Estatus', 1)
                ->count();

            // 5. Si no hay pendientes, cerrar la auditoría
            if ($pendientes == 0) {
                DB::connection('sqlsrv')
                    ->table('AuditoriaInventario')
                    ->where('AuditoriaInventarioId', $auditoriaId)
                    ->update([
                        'Estatus' => 0  // Cerrada
                    ]);

                \Log::info('Auditoría cerrada automáticamente', [
                    'auditoria_id' => $auditoriaId,
                    'motivo' => 'Todos los productos procesados'
                ]);
            }

            DB::connection('sqlsrv')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto rechazado correctamente',
                'pendientes' => $pendientes
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error al rechazar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarPlantilla(Request $request)
    {
        try {
            // ✅ Buscar la sucursal de tipo "Almacén" (Tipo = 2)
            $sucursalAlmacen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 2)
                ->first();

            if (!$sucursalAlmacen) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la sucursal Almacén'
                ], 404);
            }

            // ✅ Obtener productos de la sucursal Almacén con existencia > 0
            $productos = DB::connection('sqlsrv')
                ->table('ProductosSucursalView')
                ->where('SucursalId', $sucursalAlmacen->ID)
                ->where('Estatus', 1)
                ->where('Existencia', '>', 0)
                ->select(['Codigo', 'Descripcion', 'Referencia', 'Existencia'])
                ->orderBy('Codigo')
                ->get();

            // ==========================================
            // CREAR EXCEL
            // ==========================================
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Sheet1');

            // 📌 TÍTULO - Fila 1
            $sheet->setCellValue('A1', 'ALMACEN');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(25);

            // 📌 FECHA Y HORA - Filas 2 y 3
            $sheet->setCellValue('N2', 'Fecha :');
            $sheet->setCellValue('S2', date('d/m/Y'));
            $sheet->setCellValue('N3', 'Hora :');
            $sheet->setCellValue('S3', date('h:i a'));
            $sheet->getStyle('N2:N3')->getFont()->setBold(true);

            // 📌 PÁGINA - Fila 5
            $sheet->setCellValue('N5', 'Pág :');
            $sheet->setCellValue('S5', '1');
            $sheet->getStyle('N5')->getFont()->setBold(true);

            // 📌 TÍTULO DEL REPORTE - Fila 8
            $sheet->setCellValue('A8', 'Inventario fisico');
            $sheet->mergeCells('A8:E8');
            $sheet->getStyle('A8')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
            ]);
            $sheet->getRowDimension(8)->setRowHeight(25);

            // 📌 ENCABEZADOS - FILA 11
            $headers = [
                'A' => 'Código',
                'C' => 'Referencia',
                'D' => 'Descripción',
                'J' => 'Unidad',
                'M' => 'Existencia',
                'Q' => 'Existencia Real',
            ];
            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . '11', $header);
            }

            // Estilo de encabezados
            $sheet->getStyle('A11:Q11')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
            ]);
            $sheet->getRowDimension(11)->setRowHeight(20);

            // 📌 DATOS DE PRODUCTOS - DESDE FILA 12
            $fila = 12;
            foreach ($productos as $producto) {
                $sheet->setCellValue('A' . $fila, $producto->Codigo ?? '');
                $sheet->setCellValue('C' . $fila, $producto->Referencia ?? '');
                $sheet->setCellValue('D' . $fila, $producto->Descripcion ?? '');
                $sheet->setCellValue('J' . $fila, '');  // Unidad vacía
                $sheet->setCellValue('M' . $fila, '');  // Existencia vacía (para llenar)
                $sheet->setCellValue('P' . $fila, $producto->Existencia ?? '');
                $sheet->setCellValue('Q' . $fila, '_________________'); // Existencia Real

                $sheet->getStyle('A' . $fila . ':Q' . $fila)->applyFromArray([
                    'font' => ['size' => 9, 'name' => 'Arial'],
                ]);

                $fila++;
            }

            // // 📌 TOTAL DE REGISTROS
            // $fila++;
            // $sheet->setCellValue('O' . $fila, 'Total Registros :');
            // $sheet->setCellValue('T' . $fila, $productos->count());
            // $sheet->getStyle('O' . $fila . ':T' . $fila)->applyFromArray([
            //     'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
            // ]);

            // 📌 ANCHOS DE COLUMNA
            $columnWidths = [
                'A' => 12, 'B' => 2, 'C' => 30, 'D' => 35,
                'E' => 2, 'F' => 2, 'G' => 2, 'H' => 2,
                'I' => 2, 'J' => 10, 'K' => 2, 'L' => 2,
                'M' => 12, 'N' => 2, 'O' => 2, 'P' => 12,
                'Q' => 18
            ];
            foreach ($columnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            // 📌 CONGELAR PANELES
            //$sheet->freezePane('A12');

            // ==========================================
            // GENERAR ARCHIVO PARA DESCARGA
            // ==========================================
            // $writer = new Xlsx($spreadsheet);
            $writer = new XlsxWriter($spreadsheet);
            $fileName = 'Inventariofisico_' . date('Ymd_His') . '.xlsx';

            // Limpiar buffer
            if (ob_get_length()) {
                ob_end_clean();
            }

            return new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename=Almacen',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            );

        } catch (\Exception $e) {
            Log::error('❌ Error generando plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la plantilla: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getProductosPorSucursal($sucursalId, $soloConExistencia = true)
    {
        $query = DB::connection('sqlsrv')
            ->table('ProductosSucursalView')
            ->where('SucursalId', $sucursalId)
            ->where('Estatus', 1);  // Activo
        
        if ($soloConExistencia) {
            $query->where('Existencia', '>', 0);
        }
        
        return $query->select([
                'ID',
                'Codigo',
                'Descripcion',
                'Referencia',
                'CostoDivisa',
                'Existencia',
                'UrlFoto'
            ])
            ->orderBy('Codigo')
            ->get();
    }

    public function listadoInventario(Request $request)
    {
        try {
            session([
                'menu_active' => 'Inventario',
                'submenu_active' => 'Listado'
            ]);

            // 1️⃣ Obtener parámetros
            $ttmnd = (int) $request->input('ttmnd', 1);
            $incluirDetalles = true;
            
            // 2️⃣ Verificar si hay fechas en el request
            $tieneFechas = $request->has('fecha_inicio') && 
                        $request->has('fecha_fin') && 
                        $request->input('fecha_inicio') && 
                        $request->input('fecha_fin');
            
            // 3️⃣ Buscar inventarios según el filtro de estatus
            if ($ttmnd == 0) {
                $listaInventarios = $this->buscarInventariosActivos($incluirDetalles);
            } else {
                $listaInventarios = $this->buscarInventariosEstatus($incluirDetalles, $ttmnd);
            }

            // 4️⃣ 🚀 Aplicar filtro de fechas SOLO si hay fechas en el request
            if ($tieneFechas) {
                $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
                $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
                
                $listaInventarios = collect($listaInventarios)
                    ->filter(function ($item) use ($fechaInicio, $fechaFin) {
                        $fechaInicioItem = Carbon::parse($item->FechaInicio);
                        $fechaFinItem = Carbon::parse($item->FechaFin);
                        
                        return $fechaInicioItem >= $fechaInicio && 
                            $fechaFinItem <= $fechaFin;
                    })
                    ->sortBy('FechaInicio')
                    ->values()
                    ->all();
            }

            // 5️⃣ Retornar vista
            $listaInventarios = collect($listaInventarios);
            return view('cpanel.inventario.listado', compact('listaInventarios', 'ttmnd'));

        } catch (\Exception $e) {
            Log::error('Error en listadoInventario: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al cargar el listado de inventario: ' . $e->getMessage());
        }
    }

    private function buscarInventariosActivos($incluirDetalles = true)
    {
        $inventarioActivos = [];

        // 1. Buscar por cada estado (igual que en .NET)
        // EnumInventario.Nuevo = 0
        $invNuevos = $this->buscarListaInventarios(0);
        if ($invNuevos) {
            $inventarioActivos = array_merge($inventarioActivos, $invNuevos);
        }

        // EnumInventario.EnConteo = 1
        $invEnConteo = $this->buscarListaInventarios(1);
        if ($invEnConteo) {
            $inventarioActivos = array_merge($inventarioActivos, $invEnConteo);
        }

        // EnumInventario.EnAuditoria = 2
        $invEnAuditoria = $this->buscarListaInventarios(2);
        if ($invEnAuditoria) {
            $inventarioActivos = array_merge($inventarioActivos, $invEnAuditoria);
        }

        // EnumInventario.Cerrado = 3
        $invCerrados = $this->buscarListaInventarios(3);
        if ($invCerrados) {
            $inventarioActivos = array_merge($inventarioActivos, $invCerrados);
        }

        // 2. Si incluye detalles (igual que en .NET)
        if ($incluirDetalles) {
            foreach ($inventarioActivos as $item) {
                // EnumInventarioDetalle.Todo = 3
                $item->Detalles = $this->buscarDetallesInventario(
                    $item->InventarioId,
                    $item->SucursalId,
                    3,  // EnumInventarioDetalle.Todo
                    false
                );
            }
        }

        return $inventarioActivos;
    }

    private function buscarDetallesInventario($inventarioId, $sucursalId, $tipoDetalle = 3, $incluirProductos = false)
    {
        $listaDetalles = null;

        // Switch según el tipo de detalle (igual que en .NET)
        switch ($tipoDetalle) {
            case 0: // EnumInventarioDetalle.NoContado
                $listaDetalles = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->where('CantidadContada', 0)
                    ->get();
                break;

            case 1: // EnumInventarioDetalle.Direfencias (Diferencias)
                $listaDetalles = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->where('CantidadContada', '!=', 0)
                    ->whereRaw('CantidadContada != Existencia')
                    ->get();
                break;

            case 2: // EnumInventarioDetalle.Exacto
                $listaDetalles = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->where('CantidadContada', '!=', 0)
                    ->whereRaw('CantidadContada = Existencia')
                    ->get();
                break;

            case 5: // EnumInventarioDetalle.NoVendible
                $listaDetalles = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->where(function($query) {
                        $query->where('CantidadPieInvertido', '!=', 0)
                              ->orWhere('CantidadPieSolo', '!=', 0)
                              ->orWhere('CantidadPiezaDanada', '!=', 0)
                              ->orWhere('CantidadCajaVacia', '!=', 0);
                    })
                    ->get();
                break;

            case 3: // EnumInventarioDetalle.Todo
                $listaDetalles = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->get();
                break;

            case 6: // EnumInventarioDetalle.Comparacion
                // Buscar inventario anterior
                $invAnterior = DB::table('Inventario')
                    ->where('SucursalId', $sucursalId)
                    ->where('InventarioId', '<', $inventarioId)
                    ->orderBy('InventarioId', 'desc')
                    ->first();

                if ($invAnterior) {
                    // Traer detalles de ambos inventarios
                    $listaDetallesTodo = DB::table('InventarioDetalle')
                        ->where(function($query) use ($inventarioId, $invAnterior) {
                            $query->where('InventarioId', $inventarioId)
                                  ->orWhere('InventarioId', $invAnterior->InventarioId);
                        })
                        ->where('CantidadContada', '!=', 0)
                        ->whereRaw('CantidadContada != Existencia')
                        ->orderBy('ProductoId')
                        ->get();

                    // Productos que aparecen en ambos inventarios (duplicados)
                    $productosDuplicados = $listaDetallesTodo
                        ->groupBy('ProductoId')
                        ->filter(function($group) {
                            return $group->count() > 1;
                        })
                        ->keys()
                        ->toArray();

                    // Filtrar solo los duplicados
                    $listaDetalles = $listaDetallesTodo
                        ->filter(function($item) use ($productosDuplicados) {
                            return in_array($item->ProductoId, $productosDuplicados);
                        })
                        ->sortBy('ProductoId')
                        ->values();
                }
                break;
        }

        // Convertir a DTO y agregar productos si es necesario
        $inventarioDTO = [];
        
        if ($listaDetalles && count($listaDetalles) > 0) {
            foreach ($listaDetalles as $item) {
                $invDetalle = (array) $item; // Convertir a array para manipular
                
                if ($incluirProductos) {
                    // Buscar producto (esto lo veremos después)
                    $producto = $this->buscarProducto($item->ProductoId, $sucursalId);
                    
                    if ($producto) {
                        $invDetalle['Producto'] = $producto;
                        $invDetalle['CostoDivisa'] = $producto->CostoDivisa ?? 0;
                    }
                    
                    // Si es comparación, incluir inventario
                    if ($tipoDetalle == 6) {
                        $inventario = DB::table('Inventario')
                            ->where('InventarioId', $item->InventarioId)
                            ->first();
                        $invDetalle['Inventario'] = $inventario;
                    }
                }
                
                $inventarioDTO[] = $invDetalle;
            }
        }

        return $inventarioDTO;
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

    private function buscarInventariosEstatus($incluirDetalles = true, $status = 1)
    {
        $inventarioEstatus = [];

        // Buscar por el estado específico
        $invPorEstatus = $this->buscarListaInventarios($status);
        if ($invPorEstatus) {
            $inventarioEstatus = array_merge($inventarioEstatus, $invPorEstatus);
        }

        // Si incluye detalles (igual que en .NET)
        if ($incluirDetalles) {
            foreach ($inventarioEstatus as $item) {
                // EnumInventarioDetalle.Todo = 3
                $item->Detalles = $this->buscarDetallesInventario(
                    $item->InventarioId,
                    $item->SucursalId,
                    3,  // EnumInventarioDetalle.Todo
                    false
                );
            }
        }

        return $inventarioEstatus;
    }

    private function buscarListaInventarios($estatus)
    {
        $inventario = DB::table('Inventario')
            ->where('Estatus', $estatus)
            ->select(
                'InventarioId',
                'FechaInicio',
                'FechaFin',
                'FechaConteo',
                'FechaCierre',
                'Codigo',
                'Descripcion',
                'SucursalId',
                // ❌ ELIMINAR 'CantidadParaContar' de aquí
                // 'CantidadParaContar',
                'CantidadContada',
                'CantidadDiferencias',
                'ItemsParaContar',
                'ItemsContados',
                'ItemsDiferencias',
                'Estatus',
                'Tipo',
                'ArchivoConteo',
                'ProductoInicialId',
                'ProductoFinalId'
            )
            ->get();

        $inventarioDTO = [];

        if ($inventario && count($inventario) > 0) {
            foreach ($inventario as $item) {
                $invDTO = (object) (array) $item;
                
                // ============================================
                // DATOS REALES DESDE InventarioDetalle
                // ============================================
                
                $estadisticas = DB::table('InventarioDetalle')
                    ->where('InventarioId', $item->InventarioId)
                    ->select(
                        DB::raw('COUNT(*) as TotalDetalles'),
                        DB::raw('SUM(CantidadContada) as TotalCantidadContada'),
                        DB::raw('SUM(Existencia) as TotalExistencia'),  // ← Para CantidadParaContar
                        DB::raw('COUNT(CASE WHEN CantidadContada > 0 THEN 1 END) as ItemsContadosReales'),
                        DB::raw('COUNT(CASE WHEN CantidadContada > 0 AND CantidadContada = Existencia THEN 1 END) as CoincidenciasReales')
                    )
                    ->first();
                
                // ============================================
                // ASIGNAR VALORES REALES
                // ============================================
                
                // 📊 Items
                $itemsContados = $estadisticas->ItemsContadosReales ?? 0;
                $itemsParaContar = $estadisticas->TotalDetalles ?? 0;
                $invDTO->ItemsContados = $itemsContados;
                $invDTO->ItemsParaContar = $itemsParaContar;
                $invDTO->PorcentajeItemsContados = $itemsParaContar > 0 
                    ? round(($itemsContados / $itemsParaContar) * 100, 2) 
                    : 0;
                
                // 📦 Unidades - 🔥 CALCULAR desde detalles
                $cantidadContada = $estadisticas->TotalCantidadContada ?? 0;
                $cantidadParaContar = $estadisticas->TotalExistencia ?? 0;  // ← 10,275
                $invDTO->CantidadContada = $cantidadContada;
                $invDTO->CantidadParaContar = $cantidadParaContar;  // ← AHORA 10,275
                $invDTO->PorcentajeCantidadContada = $cantidadParaContar > 0 
                    ? round(($cantidadContada / $cantidadParaContar) * 100, 2) 
                    : 0;
                
                // 🎯 Exactitud
                $coincidencias = $estadisticas->CoincidenciasReales ?? 0;
                $invDTO->CoincidenciasReales = $coincidencias;
                $invDTO->Exactitud = $itemsParaContar > 0 
                    ? round(($coincidencias / $itemsParaContar) * 100, 2) 
                    : 0;
                
                // Buscar sucursal
                $invDTO->Sucursal = $this->buscarSucursal($item->SucursalId);
                
                $inventarioDTO[] = $invDTO;
            }
        }

        return $inventarioDTO;
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

    public function crearInventario()
    {
        try {
            session([
                'menu_active' => 'Inventario',
                'submenu_active' => 'Listado'
            ]);

            // 1️⃣ Obtener sucursales activas
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            // 2️⃣ Obtener series con SucursalId = 0 (igual que .NET)
            //    Esto siempre devuelve vacío, pero mantiene la consistencia
            $series = $this->construirListadoSeriesDeMarca(0);
            
            // 3️⃣ Preparar SelectList vacío (como en .NET)
            $seriesSelect = [];

            return view('cpanel.inventario.crear', compact('sucursales', 'seriesSelect'));

        } catch (\Exception $e) {
            Log::error('Error en crearInventario: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function guardarInventario(Request $request)
    {

        DB::beginTransaction();

        try {
            // ============================================
            // 1. VALIDAR
            // ============================================
            $validated = $request->validate([
                'FechaInicio' => 'required|date',
                'FechaFin' => 'required|date|after_or_equal:FechaInicio',
                'Descripcion' => 'nullable|string',
                'SucursalId' => 'required|integer',
                'Tipo' => 'required|integer',
                'ProductoInicialId' => 'nullable|integer',
                'ProductoFinalId' => 'nullable|integer'
            ]);

            // ============================================
            // 2. GENERAR CÓDIGO
            // ============================================
            $codigo = 'IV' . Carbon::now()->format('Ymd') . '-' . $validated['SucursalId'];

            // ============================================
            // 3. ASIGNAR FECHAS
            // ============================================
            $fechaConteo = $validated['FechaInicio'];
            $fechaCierre = $validated['FechaFin'];

            // ============================================
            // 4. GUARDAR INVENTARIO (INSERT)
            // ============================================
            $inventarioId = DB::table('Inventario')->insertGetId([
                'Codigo' => $codigo,
                'Descripcion' => $validated['Descripcion'] ?? '',
                'FechaInicio' => $validated['FechaInicio'],
                'FechaFin' => $validated['FechaFin'],
                'FechaConteo' => $fechaConteo,
                'FechaCierre' => $fechaCierre,
                'SucursalId' => $validated['SucursalId'],
                'Tipo' => $validated['Tipo'],
                'Estatus' => 0, // Nuevo
                'CantidadContada' => 0,
                'ItemsContados' => 0,
                'ProductoInicialId' => $validated['ProductoInicialId'] ?? null,
                'ProductoFinalId' => $validated['ProductoFinalId'] ?? null
            ]);

            // ============================================
            // 5. AGREGAR PRODUCTOS (AgregarProductos)
            // ============================================
            $this->agregarProductos($inventarioId, $validated);

            // ============================================
            // 6. CONFIRMAR TRANSACCIÓN
            // ============================================
            DB::commit();

            // return redirect()
            //     ->route('cpanel.inventario.listado')
            //     ->with('success', 'Se ha guardado el inventario');

            // 🔥 Redirigir directamente a iniciar-conteo
            return redirect()
                ->route('cpanel.inventario.iniciar-conteo', $inventarioId);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ ERROR en guardarInventario: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'No se ha guardado el inventario: ' . $e->getMessage());
        }
    }

    /**
     * Agregar productos al inventario
     */
    private function agregarProductos($inventarioId, $validated)
    {

        $listadoProductos = [];

        if ($validated['Tipo'] == 1) {
            $listadoProductos = $this->buscarProductosPorSucursal(
                $validated['SucursalId'],
                false
            );
        }

        if ($listadoProductos && count($listadoProductos) > 0) {
            // ============================================
            // INSERTAR EN LOTES (150 registros por lote)
            // 150 × 12 campos = 1800 parámetros (dentro del límite de 2100)
            // ============================================
            $totalItems = 0;
            $totalExistencia = 0;
            $batchSize = 150;
            $detalles = [];
            $batchCount = 0;

            foreach ($listadoProductos as $producto) {
                $detalles[] = [
                    'InventarioId' => $inventarioId,
                    'ProductoId' => $producto->Id,
                    'Existencia' => $producto->Existencia ?? 0,
                    'CostoDivisa' => 0,
                    'CantidadContada' => 0,
                    'CantidadVendida' => 0,
                    'CantidadCajaVacia' => 0,
                    'CantidadPieSolo' => 0,
                    'CantidadPieInvertido' => 0,
                    'CantidadPiezaDanada' => 0,
                    'EsUsuarioRestringido' => 0,
                    'Fecha' => $validated['FechaInicio']
                ];

                $totalExistencia += $producto->Existencia ?? 0;
                $totalItems++;

                if (count($detalles) >= $batchSize) {
                    $batchCount++;
                    DB::table('InventarioDetalle')->insert($detalles);
                    $detalles = [];
                }
            }

            if (count($detalles) > 0) {
                $batchCount++;
                DB::table('InventarioDetalle')->insert($detalles);
            }

            // Actualizar cabecera
            $cantidadParaContar = collect($listadoProductos)
                ->filter(function($producto) {
                    return ($producto->Existencia ?? 0) > 0;
                })
                ->sum('Existencia');

            $itemsParaContar = count($listadoProductos);

            DB::table('Inventario')
                ->where('InventarioId', $inventarioId)
                ->update([
                    'ItemsParaContar' => $itemsParaContar,
                    'CantidadParaContar' => $cantidadParaContar
                ]);

            return [
                'total' => $totalItems,
                'existencia' => $totalExistencia
            ];
        }

        return ['total' => 0, 'existencia' => 0];
    }

    /**
     * Buscar productos por sucursal
     */
    private function buscarProductosPorSucursal($idSucursal, $esSoloConExistencia)
    {
        return $this->buscarProductosEnBaseDeDatos(
            $idSucursal,
            1,      // EnumProducto.Activo = 1
            null,   // Sin filtro de proveedor
            $esSoloConExistencia
        );
    }

    /**
     * Buscar productos en base de datos
     */
    private function buscarProductosEnBaseDeDatos(
        $idSucursal,
        $estatusProducto,
        $esProveedorAsignado,
        $esSoloConExistencia
    ) {
        $query = DB::connection('sqlsrv')
            ->table('ProductosSucursalView')
            ->where('SucursalId', $idSucursal)
            ->where(function ($q) use ($estatusProducto) {
                if ($estatusProducto != 10) {
                    $q->where('Estatus', $estatusProducto);
                }
            })
            ->where(function ($q) use ($esProveedorAsignado) {
                if ($esProveedorAsignado !== null) {
                    $q->where('EsProveedorAsignado', $esProveedorAsignado);
                }
            })
            ->where(function ($q) use ($esSoloConExistencia) {
                if ($esSoloConExistencia) {
                    $q->where('Existencia', '>', 0);
                }
            })
            ->select(
                'ID',
                'Codigo',
                'CodigoBarra',
                'CostoBs',
                'CostoDivisa',
                'Descripcion',
                'EsProveedorAsignado',
                'Estatus',
                'Existencia',
                'FechaActualizacion',
                'FechaCreacion',
                'PvpBs',
                'PvpDivisa',
                'Referencia',
                'PvpAnterior',
                'Tipo',
                'SucursalId',
                'UrlFoto',
                'FechaNuevoPrecio',
                'FechaUltimaVenta',
                'NuevoPvp'
            )
            ->orderBy('Codigo');

        $productosModel = $query->get();

        return $this->generarListadoProductosDTO($productosModel);
    }

    /**
     * Generar listado de ProductoDTO
     */
    private function generarListadoProductosDTO($listadoProductosModel)
    {
        $productosDTO = [];

        if ($listadoProductosModel && $listadoProductosModel->isNotEmpty()) {
            foreach ($listadoProductosModel as $item) {
                $productosDTO[] = (object) [
                    'Id' => $item->ID,
                    'Codigo' => $item->Codigo ?? '',
                    'CodigoBarra' => $item->CodigoBarra ?? '',
                    'Descripcion' => $item->Descripcion ?? '',
                    'CostoBs' => $item->CostoBs ?? 0,
                    'CostoDivisa' => $item->CostoDivisa ?? 0,
                    'Existencia' => $item->Existencia ?? 0,
                    'PvpBs' => $item->PvpBs ?? 0,
                    'PvpDivisa' => $item->PvpDivisa ?? 0,
                    'Referencia' => $item->Referencia ?? '',
                    'SucursalId' => $item->SucursalId ?? 0,
                    'UrlFoto' => $item->UrlFoto ?? '',
                    'Tipo' => $item->Tipo ?? 0,
                    'Estatus' => $item->Estatus ?? 0,
                    'EsProveedorAsignado' => $item->EsProveedorAsignado ?? false,
                    'FechaCreacion' => $item->FechaCreacion ?? null,
                    'FechaActualizacion' => $item->FechaActualizacion ?? null,
                    'FechaUltimaVenta' => $item->FechaUltimaVenta ?? null,
                    'PvpAnterior' => $item->PvpAnterior ?? null,
                    'FechaNuevoPrecio' => $item->FechaNuevoPrecio ?? null,
                    'NuevoPvp' => $item->NuevoPvp ?? null,
                ];
            }
        }

        return $productosDTO;
    }

    private function construirListadoSeriesDeMarca($sucursalId)
    {
        $series = [];

        try {
            if ($sucursalId == 0) {
                return $series;
            }

            $query = DB::connection('sqlsrv')
                ->table('ProductosSucursalView')
                ->where('SucursalId', $sucursalId)
                ->orderBy('Codigo')
                ->orderBy('ID')
                ->select([
                    'ID',
                    'Codigo',
                    'CodigoBarra',
                    'CostoBs',
                    'CostoDivisa',
                    'Descripcion',
                    'EsProveedorAsignado',
                    'Estatus',
                    'Existencia',
                    'FechaActualizacion',
                    'FechaCreacion',
                    'FechaUltimaVenta',
                    'PvpBs',
                    'PvpDivisa',
                    'Referencia',
                    'SucursalId',
                    'UrlFoto',
                    'FechaNuevoPrecio',
                    'NuevoPvp',
                    'PvpAnterior',
                    'Tipo'
                ]);

            $listadoProductos = $query->get();

            if ($listadoProductos && count($listadoProductos) > 0) {
                $primerProducto = $listadoProductos->first();
                $serieActual = $this->obtenerTextoCodigoProducto($primerProducto);
                $unidades = 0;

                foreach ($listadoProductos as $producto) {
                    $serieNueva = $this->obtenerTextoCodigoProducto($producto);

                    if ($serieActual !== $serieNueva) {
                        $series[] = "{$serieActual} -- {$unidades} referencias";
                        $serieActual = $serieNueva;
                        $unidades = 0;
                    }
                    $unidades++;
                }

                $series[] = "{$serieActual} -- {$unidades} referencias";
            }

        } catch (\Exception $e) {
            Log::error('❌ [construirListadoSeriesDeMarca] Error: ' . $e->getMessage());
            throw $e;
        }

        return $series;
    }

    private function obtenerTextoCodigoProducto($producto)
    {
        $codigo = '';

        if (isset($producto->Codigo) && !empty($producto->Codigo)) {
            $codigoCompleto = $producto->Codigo;

            for ($i = 0; $i < strlen($codigoCompleto); $i++) {
                $caracter = $codigoCompleto[$i];

                if (ctype_alpha($caracter)) {
                    $codigo .= $caracter;
                } else {
                    break;
                }
            }
        }

        return $codigo;
    }

    public function editarInventario($id)
    {
        try {
            session([
                'menu_active' => 'Inventario',
                'submenu_active' => 'Listado'
            ]);

            // ============================================
            // 1. BUSCAR INVENTARIO (BuscarInventario)
            // ============================================
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                return redirect()
                    ->route('cpanel.inventario.listado')
                    ->with('error', 'No se pudo encontrar el inventario solicitado');
            }

            // ============================================
            // 2. CONVERTIR A DTO CON FECHAS FORMATEADAS
            //    Equivalente a _mapper.Map<InventarioDTO>(inventario)
            // ============================================
            $inventarioDTO = (object) [
                'InventarioId' => $inventario->InventarioId,
                'Codigo' => $inventario->Codigo,
                'Descripcion' => $inventario->Descripcion,
                // 🔥 FORMATO DE FECHAS (YYYY-MM-DD)
                'FechaInicio' => Carbon::parse($inventario->FechaInicio)->toDateString(),
                'FechaFin' => Carbon::parse($inventario->FechaFin)->toDateString(),
                'FechaConteo' => Carbon::parse($inventario->FechaConteo)->toDateString(),
                'FechaCierre' => Carbon::parse($inventario->FechaCierre)->toDateString(),
                'SucursalId' => $inventario->SucursalId,
                'Tipo' => $inventario->Tipo,
                'Estatus' => $inventario->Estatus,
                'CantidadParaContar' => $inventario->CantidadParaContar,
                'CantidadContada' => $inventario->CantidadContada,
                'ItemsParaContar' => $inventario->ItemsParaContar,
                'ItemsContados' => $inventario->ItemsContados,
                'ProductoInicialId' => $inventario->ProductoInicialId,
                'ProductoFinalId' => $inventario->ProductoFinalId,
            ];

            // ============================================
            // 3. BUSCAR LA SUCURSAL
            // ============================================
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $inventario->SucursalId)
                ->select('ID', 'Nombre', 'Direccion', 'EsActiva')
                ->first();

            $inventarioDTO->Sucursal = $sucursal;

            // ============================================
            // 4. PREPARAR DATOS PARA LA VISTA
            // ============================================
            
            // 4a. Sucursales activas
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            // 4b. Series
            $series = $this->construirListadoSeriesDeMarca($inventarioDTO->SucursalId);

            // ============================================
            // 5. RETORNAR VISTA
            // ============================================
            return view('cpanel.inventario.crear', [
                'inventario' => $inventarioDTO,
                'sucursales' => $sucursales,
                'seriesSelect' => $series,
                'isEdit' => true
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en editarInventario: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());

            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('error', 'Error al cargar el inventario: ' . $e->getMessage());
        }
    }

    public function actualizarInventario(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            // ============================================
            // 1. VALIDAR (ModelState.IsValid)
            // ============================================
            $validated = $request->validate([
                'FechaInicio' => 'required|date',
                'FechaFin' => 'required|date|after_or_equal:FechaInicio',
                'Descripcion' => 'nullable|string',
                'SucursalId' => 'required|integer',
                'Tipo' => 'required|integer',
                'Usuarios' => 'nullable|array'
            ]);

            // ============================================
            // 2. VERIFICAR QUE EL INVENTARIO EXISTA
            // ============================================
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                throw new \Exception('Inventario no encontrado');
            }

            // ============================================
            // 3. GENERAR CÓDIGO (si cambia la sucursal)
            //    Equivalente a: $"IV{DateTime.Now:yyyyMMdd}-{SucursalId}"
            // ============================================
            $codigo = 'IV' . Carbon::now()->format('Ymd') . '-' . $validated['SucursalId'];

            // ============================================
            // 4. ASIGNAR FECHAS (como en .NET)
            // ============================================
            $fechaConteo = $validated['FechaInicio'];
            $fechaCierre = $validated['FechaFin'];

            // ============================================
            // 5. ACTUALIZAR INVENTARIO (GuardarInventario)
            //    SOLO actualiza cabecera, NO productos
            // ============================================
            DB::table('Inventario')
                ->where('InventarioId', $id)
                ->update([
                    'Codigo' => $codigo,
                    'Descripcion' => $validated['Descripcion'] ?? '',
                    'FechaInicio' => $validated['FechaInicio'],
                    'FechaFin' => $validated['FechaFin'],
                    'FechaConteo' => $validated['FechaInicio'],
                    'FechaCierre' => $validated['FechaFin'],
                    'SucursalId' => $validated['SucursalId'],
                    'Tipo' => $validated['Tipo']
                    // ❌ ELIMINAR: 'updated_at' => now()
                ]);

            // ============================================
            // 6. PREPARAR DATOS PARA LA VISTA
            //    Equivalente a GenerarListaSucursales()
            // ============================================
            $sucursales = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('EsActiva', 1)
                ->select('ID', 'Nombre')
                ->orderBy('Nombre')
                ->get();

            // ============================================
            // 7. CONFIRMAR TRANSACCIÓN
            // ============================================
            DB::commit();

            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('success', 'Se ha guardado el inventario');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Por favor corrige los errores del formulario');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ ERROR en actualizarInventario: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'No se ha guardado el inventario: ' . $e->getMessage());
        }
    }

    public function iniciarConteo($id)
    {
        // 1. Buscar el inventario
        $inventario = DB::table('Inventario')
            ->where('InventarioId', $id)
            ->first();

        if (!$inventario) {
            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('error', 'No se pudo encontrar un inventario con el identificador indicado');
        }

        // 2. Buscar la sucursal
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('ID', $inventario->SucursalId)
            ->first();

        // Es Nuevo y se cambia el estatius a 1
        if($inventario->Estatus == 0){

            // 3. Cambiar estatus a "En Conteo"
            DB::table('Inventario')
                ->where('InventarioId', $id)
                ->update([
                    'Estatus' => 1,
                    'FechaConteo' => now()
                ]);

        }

        // 4. Obtener detalles NO contados CON información del producto
        $detalles = DB::table('InventarioDetalle')
            ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
            ->where('InventarioDetalle.InventarioId', $id)
            ->where('InventarioDetalle.CantidadContada', 0)
            ->where('InventarioDetalle.Existencia', '>', 0)
            ->select(
                'InventarioDetalle.*',
                'Productos.Codigo',
                'Productos.Referencia',
                'Productos.CostoDivisa',
                'Productos.UrlFoto'
            )
            ->get();

        // 5. Calcular estadísticas
        $totalProductos = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->count();

        $contados = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->where('CantidadContada', '>', 0)
            ->count();

        return view('cpanel.inventario.conteo-general', [
            'inventario' => $inventario,
            'sucursal' => $sucursal,
            'detalles' => $detalles,
            'totalProductos' => $totalProductos,
            'contados' => $contados,
            'pendientes' => $totalProductos - $contados,
            'porcentaje' => $totalProductos > 0 ? round(($contados / $totalProductos) * 100, 2) : 0
        ]);
    }

    public function buscarProductoConteo(Request $request)
    {
        $codigo = $request->input('Codigo');
        $inventarioId = $request->input('InventarioId');
        $sucursalId = $request->input('SucursalId');

        if (empty($codigo)) {
            return response()->json([
                'success' => false,
                'message' => 'Ingrese un código de producto'
            ]);
        }

        // Buscar el producto por código y sucursal
        $producto = DB::connection('sqlsrv')
            ->table('Productos')
            ->where('Codigo', $codigo)
            ->select('ID', 'Codigo', 'Descripcion', 'Referencia', 'CostoDivisa', 'UrlFoto')
            ->first();

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ]);
        }

        // 🔥 Usar FileHelper para obtener las URLs de las imágenes
        $thumbUrl = FileHelper::getOrDownloadFile(
            'images/items/thumbs/',
            $producto->UrlFoto ?? '',
            'assets/img/adminlte/img/produc_default.jfif'
        );
        
        $fullUrl = FileHelper::getOrDownloadFile(
            'images/items/',
            $producto->UrlFoto ?? '',
            'assets/img/adminlte/img/produc_default.jfif'
        );

        // Buscar el detalle del inventario para este producto
        $detalle = DB::table('InventarioDetalle')
            ->where('InventarioId', $inventarioId)
            ->where('ProductoId', $producto->ID)
            ->select('Existencia', 'CantidadContada', 'CantidadPieSolo', 'CantidadPieInvertido', 'CantidadPiezaDanada', 'CantidadCajaVacia')
            ->first();

        // Convertir valores a números
        $producto->CostoDivisa = floatval($producto->CostoDivisa ?? 0);
        $producto->ID = intval($producto->ID);

        return response()->json([
            'success' => true,
            'producto' => [
                'ID' => $producto->ID,
                'Codigo' => $producto->Codigo,
                'Descripcion' => $producto->Descripcion,
                'Referencia' => $producto->Referencia,
                'CostoDivisa' => $producto->CostoDivisa,
                'UrlFoto' => $producto->UrlFoto,
                'thumbUrl' => $thumbUrl,  // 🔥 URL de la imagen en miniatura
                'fullUrl' => $fullUrl     // 🔥 URL de la imagen completa
            ],
            'detalle' => $detalle ? [
                'Existencia' => intval($detalle->Existencia ?? 0),
                'CantidadContada' => intval($detalle->CantidadContada ?? 0),
                'CantidadPieSolo' => intval($detalle->CantidadPieSolo ?? 0),
                'CantidadPieInvertido' => intval($detalle->CantidadPieInvertido ?? 0),
                'CantidadPiezaDanada' => intval($detalle->CantidadPiezaDanada ?? 0),
                'CantidadCajaVacia' => intval($detalle->CantidadCajaVacia ?? 0)
            ] : []
        ]);
    }

    public function guardarConteoManual(Request $request)
    {
        // Iniciar transacción
        DB::beginTransaction();

        try {
            $productoId = $request->input('ProductoId');
            $inventarioId = $request->input('InventarioId');
            $sucursalId = $request->input('SucursalId');
            $cantidadContada = (int) $request->input('CantidadContada', 0);
            $cantidadPieSolo = (int) $request->input('CantidadPieSolo', 0);
            $cantidadPieInvertido = (int) $request->input('CantidadPieInvertido', 0);
            $cantidadDanado = (int) $request->input('CantidadDanado', 0);
            $cantidadCajaVacia = (int) $request->input('CantidadCajaVacia', 0);

            // 1. Buscar el detalle existente
            $detalle = DB::table('InventarioDetalle')
                ->where('InventarioId', $inventarioId)
                ->where('ProductoId', $productoId)
                ->first();

            if (!$detalle) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado en el inventario'
                ]);
            }

            // 2. Guardar el valor anterior
            $cantidadContadaAnterior = $detalle->CantidadContada ?? 0;

            // 3. Actualizar el detalle
            DB::table('InventarioDetalle')
                ->where('InventarioDetalleId', $detalle->InventarioDetalleId)
                ->update([
                    'CantidadContada' => $cantidadContada,
                    'CantidadPieInvertido' => $cantidadPieInvertido,
                    'CantidadPieSolo' => $cantidadPieSolo,
                    'CantidadPiezaDanada' => $cantidadDanado,
                    'CantidadCajaVacia' => 0
                ]);

            // 4. Actualizar la cabecera del inventario
            $this->actualizarCabeceraInventario($inventarioId, $cantidadContada, $cantidadContadaAnterior);

            // 5. Buscar el producto para respuesta
            $producto = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('ID', $productoId)
                ->select('ID', 'Codigo', 'Descripcion', 'Referencia', 'CostoDivisa', 'UrlFoto')
                ->first();

            // 6. Calcular diferencia
            $existencia = $detalle->Existencia ?? 0;
            $diferencia = $cantidadContada - $existencia;

            // 7. Obtener datos actualizados para la vista
            $inventarioActualizado = DB::table('Inventario')
                ->where('InventarioId', $inventarioId)
                ->first();

            // Confirmar transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conteo registrado correctamente',
                'diferencia' => $diferencia,
                'inventario' => $inventarioActualizado,
                'detalle' => $detalle
            ]);

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();

            Log::error('Error en guardarConteoManual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el conteo: ' . $e->getMessage()
            ]);
        }
    }

    private function actualizarCabeceraInventario($inventarioId, $nuevaCantidad, $anteriorCantidad)
    {
        try {
            $diferencia = $nuevaCantidad - $anteriorCantidad;

            // 1. Actualizar CantidadContada (sin updated_at)
            DB::table('Inventario')
                ->where('InventarioId', $inventarioId)
                ->update([
                    'CantidadContada' => DB::raw("CantidadContada + {$diferencia}")
                ]);

            // 2. Actualizar ItemsContados (sin updated_at)
            $itemsContados = DB::table('InventarioDetalle')
                ->where('InventarioId', $inventarioId)
                ->where('CantidadContada', '>', 0)
                ->count();

            DB::table('Inventario')
                ->where('InventarioId', $inventarioId)
                ->update([
                    'ItemsContados' => $itemsContados
                ]);

        } catch (\Exception $e) {
            Log::error('Error en actualizarCabeceraInventario: ' . $e->getMessage());
            throw $e;
        }
    }

    private function actualizarContadoresInventario($inventarioId)
    {
        // Obtener estadísticas de los detalles
        $stats = DB::table('InventarioDetalle')
            ->where('InventarioId', $inventarioId)
            ->select(
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(CantidadContada) as total_contado'),
                DB::raw('COUNT(CASE WHEN CantidadContada > 0 THEN 1 END) as items_contados')
            )
            ->first();

        // Actualizar la cabecera del inventario
        DB::table('Inventario')
            ->where('InventarioId', $inventarioId)
            ->update([
                'CantidadContada' => $stats->total_contado ?? 0,
                'ItemsContados' => $stats->items_contados ?? 0,
                'updated_at' => now()
            ]);
    }

    public function generarPlantillaConteo($id)
    {
        try {
            // 1. Buscar el inventario
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                return back()->with('error', 'Inventario no encontrado');
            }

            // 2. Buscar la sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $inventario->SucursalId)
                ->first();

            // 3. Obtener TODOS los detalles del inventario (sin filtrar por existencia)
            $detalles = DB::table('InventarioDetalle')
                ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
                ->where('InventarioDetalle.InventarioId', $id)
                // ❌ ELIMINADO: ->where('InventarioDetalle.Existencia', '>', 0)
                ->orderBy('Productos.Codigo')
                ->select(
                    'InventarioDetalle.*',
                    'Productos.Codigo',
                    'Productos.Descripcion',
                    'Productos.Referencia',
                    'Productos.CostoDivisa'
                )
                ->get();

            // 4. Crear el Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ============================================
            // TÍTULO: CONTEO DE INVENTARIO
            // ============================================
            $sheet->setCellValue('A1', 'CONTEO DE INVENTARIO');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // ============================================
            // INFORMACIÓN DEL INVENTARIO
            // ============================================
            $sheet->setCellValue('A2', 'FECHA INICIO');
            $sheet->setCellValue('B2', date('d/m/Y', strtotime($inventario->FechaInicio)));
            $sheet->setCellValue('D2', 'FECHA FIN');
            $sheet->setCellValue('E2', date('d/m/Y', strtotime($inventario->FechaFin)));

            $sheet->setCellValue('A3', 'SUCURSAL');
            $sheet->setCellValue('B3', $sucursal->Nombre ?? 'N/A');
            $sheet->setCellValue('D3', 'ID');
            $sheet->setCellValue('E3', $inventario->SucursalId);

            $sheet->setCellValue('A4', 'TIPO');
            $sheet->setCellValue('B4', 'General');
            $sheet->setCellValue('D4', 'CODIGO');
            $sheet->setCellValue('E4', $inventario->Codigo);

            $sheet->setCellValue('A5', 'EMPLEADO');
            $sheet->setCellValue('B5', '');
            $sheet->setCellValue('D5', 'ID');
            $sheet->setCellValue('E5', '');

            // ============================================
            // ENCABEZADOS DE LA TABLA
            // ============================================
            $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO', 'TOTAL'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '7', $header);
                $sheet->getStyle($col . '7')->getFont()->setBold(true);
                $sheet->getStyle($col . '7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
            }

            // ============================================
            // DATOS DE TODOS LOS PRODUCTOS
            // ============================================
            $row = 8;
            foreach ($detalles as $detalle) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, 0);
                $sheet->setCellValue('C' . $row, 0);
                $sheet->setCellValue('D' . $row, 0);
                $sheet->setCellValue('E' . $row, 0);
                $sheet->setCellValue('F' . $row, 0);
                $sheet->setCellValue('G' . $row, 0);
                
                $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                $row++;
            }

            // ============================================
            // APLICAR ESTILOS
            // ============================================
            $sheet->getStyle('A7:G7')->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            $sheet->getStyle('A7:G7')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            $sheet->getRowDimension('7')->setRowHeight(22);

            // ============================================
            // GUARDAR Y DESCARGAR
            // ============================================
            $fileName = 'ConteoInventario' . $inventario->InventarioId . '.xlsx';
            $filePath = storage_path('app/download/' . $fileName);
            
            if (!file_exists(storage_path('app/download'))) {
                mkdir(storage_path('app/download'), 0777, true);
            }

            $writer = new XlsxWriter($spreadsheet);
            $writer->save($filePath);

            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error en generarPlantillaConteo: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    public function uploadConteoExcel(Request $request)
    {
        // Iniciar transacción
        DB::beginTransaction();

        try {
            // 1. Validar el archivo
            $request->validate([
                'conteoProductosExcelFile' => 'required|file|mimes:xlsx,xls|max:10240',
                'InventarioId' => 'required|integer'
            ]);

            $inventarioId = $request->input('InventarioId');
            $archivo = $request->file('conteoProductosExcelFile');

            // 2. Leer el archivo Excel
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($archivo->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // 3. Buscar el inventario
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $inventarioId)
                ->first();

            if (!$inventario) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Inventario no encontrado'
                ]);
            }

            // 4. Buscar el inicio de los datos (igual que .NET)
            $estatusArchivo = 0; // Inicio
            $inicioDatos = -1;
            $filaCabecera = -1;

            foreach ($rows as $index => $row) {
                if (empty($row) || empty(trim($row[0] ?? ''))) {
                    continue;
                }

                $valorColumna0 = trim(strtoupper($row[0] ?? ''));

                // Buscar "CONTEO DE INVENTARIO" (Inicio → Cabecera)
                if ($estatusArchivo == 0) {
                    if ($valorColumna0 == 'CONTEO DE INVENTARIO') {
                        $estatusArchivo = 1; // Cabecera
                    }
                    continue;
                }

                // Buscar "CODIGO" (Cabecera → Detalles)
                if ($estatusArchivo == 1) {
                    if ($valorColumna0 == 'CODIGO' || $valorColumna0 == 'CÓDIGO') {
                        $estatusArchivo = 2; // Detalles
                        $inicioDatos = $index + 1; // La siguiente fila es la primera con datos
                        $filaCabecera = $index;
                    }
                    continue;
                }

                if ($estatusArchivo == 2 && $inicioDatos > 0) {
                    break;
                }
            }

            if ($inicioDatos == -1) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos en el archivo Excel'
                ]);
            }

            // 5. Procesar los productos
            $procesados = 0;
            $errores = [];
            $productosSaltados = 0;
            $totalFilasProcesadas = 0;

            for ($i = $inicioDatos; $i < count($rows); $i++) {
                $fila = $rows[$i];
                $totalFilasProcesadas++;
                
                // Verificar que la fila tenga código
                if (empty($fila[0]) || empty(trim($fila[0]))) {
                    continue;
                }

                $codigo = trim($fila[0]);
                $cantidadContada = isset($fila[1]) ? (int) $fila[1] : 0;

                // Solo procesar si CantidadContada > 0 (igual que .NET)
                if ($cantidadContada <= 0) {
                    $productosSaltados++;
                    continue;
                }

                $cantidadInvertido = isset($fila[2]) ? (int) $fila[2] : 0;
                $cantidadSolo = isset($fila[3]) ? (int) $fila[3] : 0;
                $cantidadDanado = isset($fila[4]) ? (int) $fila[4] : 0;
                $cantidadVacio = isset($fila[5]) ? (int) $fila[5] : 0;

                // Buscar el producto por código (case insensitive)
                $producto = DB::connection('sqlsrv')
                    ->table('Productos')
                    ->whereRaw('LOWER(Codigo) = ?', [strtolower($codigo)])
                    ->first();

                if (!$producto) {
                    $errores[] = "Producto no encontrado: $codigo";
                    continue;
                }

                // Buscar el detalle del inventario
                $detalle = DB::table('InventarioDetalle')
                    ->where('InventarioId', $inventarioId)
                    ->where('ProductoId', $producto->ID)
                    ->first();

                if (!$detalle) {
                    $errores[] = "Producto $codigo no está en el inventario";
                    continue;
                }

                // Guardar el valor anterior
                $cantidadContadaAnterior = $detalle->CantidadContada ?? 0;

                // Actualizar el detalle
                DB::table('InventarioDetalle')
                    ->where('InventarioDetalleId', $detalle->InventarioDetalleId)
                    ->update([
                        'CantidadContada' => $cantidadContada,
                        'CantidadPieInvertido' => $cantidadInvertido,
                        'CantidadPieSolo' => $cantidadSolo,
                        'CantidadPiezaDanada' => $cantidadDanado,
                        'CantidadCajaVacia' => $cantidadVacio
                    ]);

                // Actualizar la cabecera del inventario
                $this->actualizarCabeceraInventario($inventarioId, $cantidadContada, $cantidadContadaAnterior);

                $procesados++;
            }

            // Confirmar transacción
            DB::commit();

            // Mensajes de resultado
            $mensaje = "Se procesaron $procesados productos correctamente.";
            if (count($errores) > 0) {
                $mensaje .= " Errores: " . implode(', ', array_slice($errores, 0, 5));
                if (count($errores) > 5) {
                    $mensaje .= " y " . (count($errores) - 5) . " más.";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'procesados' => $procesados,
                'errores' => $errores,
                'detalles' => [
                    'total_filas' => $totalFilasProcesadas,
                    'productos_saltados' => $productosSaltados
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el archivo: ' . $e->getMessage()
            ]);
        }
    }

    public function buscarDetalleJSON(Request $request)
    {
        try {
            $tipoProductos = $request->input('TipoProductos');
            $inventarioId = $request->input('InventarioId');

            if (!$inventarioId) {
                return response()->json([]);
            }

            $query = DB::table('InventarioDetalle')
                ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
                ->where('InventarioDetalle.InventarioId', $inventarioId)
                ->select(
                    'InventarioDetalle.*',
                    'Productos.ID as producto_id',
                    'Productos.Codigo',
                    'Productos.Referencia',
                    'Productos.CostoDivisa',
                    'Productos.UrlFoto'
                );

            switch ($tipoProductos) {
                case 'SinContar':
                    $query->where('InventarioDetalle.CantidadContada', 0)
                        ->where('InventarioDetalle.Existencia', '>', 0);
                    break;

                case 'Diferencias':
                    $query->where('InventarioDetalle.CantidadContada', '!=', 0)
                        ->whereRaw('InventarioDetalle.CantidadContada != InventarioDetalle.Existencia');
                    break;

                case 'Exactos':
                    $query->where('InventarioDetalle.CantidadContada', '!=', 0)
                        ->whereRaw('InventarioDetalle.CantidadContada = InventarioDetalle.Existencia');
                    break;

                case 'Todo':
                    // Sin filtro adicional
                    break;

                case 'NoVendible':
                    $query->where(function($q) {
                        $q->where('InventarioDetalle.CantidadPieInvertido', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadPieSolo', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadPiezaDanada', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadCajaVacia', '!=', 0);
                    });
                    break;

                case 'Comparacion':
                    // Buscar inventario anterior
                    $invAnterior = DB::table('Inventario')
                        ->where('SucursalId', function($q) use ($inventarioId) {
                            $q->select('SucursalId')
                            ->from('Inventario')
                            ->where('InventarioId', $inventarioId);
                        })
                        ->where('InventarioId', '<', $inventarioId)
                        ->orderBy('InventarioId', 'desc')
                        ->first();

                    if ($invAnterior) {
                        // Obtener detalles con diferencias de AMBOS inventarios
                        $detallesActual = DB::table('InventarioDetalle')
                            ->where('InventarioId', $inventarioId)
                            ->where('CantidadContada', '!=', 0)
                            ->whereRaw('CantidadContada != Existencia')
                            ->get();

                        $detallesAnterior = DB::table('InventarioDetalle')
                            ->where('InventarioId', $invAnterior->InventarioId)
                            ->where('CantidadContada', '!=', 0)
                            ->whereRaw('CantidadContada != Existencia')
                            ->get();

                        // Unir por ProductoId y mostrar una sola fila por producto
                        $resultado = [];
                        $productosProcesados = [];

                        foreach ($detallesActual as $actual) {
                            $anterior = $detallesAnterior->firstWhere('ProductoId', $actual->ProductoId);
                            
                            if ($anterior) {
                                $producto = DB::table('Productos')->where('ID', $actual->ProductoId)->first();
                                
                                // Evitar duplicados
                                if (in_array($actual->ProductoId, $productosProcesados)) {
                                    continue;
                                }
                                $productosProcesados[] = $actual->ProductoId;

                                $resultado[] = [
                                    'producto' => [
                                        'id' => $producto->ID,
                                        'codigo' => $producto->Codigo,
                                        'referencia' => $producto->Referencia,
                                        'costoDivisa' => $producto->CostoDivisa,
                                        'urlFoto' => $producto->UrlFoto,
                                        'thumbUrl' => FileHelper::getOrDownloadFile('images/items/thumbs/', $producto->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif'),
                                        'fullUrl' => FileHelper::getOrDownloadFile('images/items/', $producto->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif')
                                    ],
                                    'inventarioActual' => (object) [
                                        'Codigo' => DB::table('Inventario')->where('InventarioId', $inventarioId)->value('Codigo'),
                                        'Existencia' => $actual->Existencia,
                                        'CantidadContada' => $actual->CantidadContada,
                                        'CantidadPieSolo' => $actual->CantidadPieSolo,
                                        'CantidadPieInvertido' => $actual->CantidadPieInvertido,
                                        'CantidadPiezaDanada' => $actual->CantidadPiezaDanada,
                                    ],
                                    'inventarioAnterior' => (object) [
                                        'Codigo' => $invAnterior->Codigo,
                                        'Existencia' => $anterior->Existencia,
                                        'CantidadContada' => $anterior->CantidadContada,
                                        'CantidadPieSolo' => $anterior->CantidadPieSolo,
                                        'CantidadPieInvertido' => $anterior->CantidadPieInvertido,
                                        'CantidadPiezaDanada' => $anterior->CantidadPiezaDanada,
                                    ],
                                    'diferenciaActual' => $actual->CantidadContada - $actual->Existencia,
                                    'diferenciaAnterior' => $anterior->CantidadContada - $anterior->Existencia,
                                ];
                            }
                        }
                        return response()->json($resultado);
                    } else {
                        return response()->json([]);
                    }
                    break;

                default:
                    return response()->json([]);
            }

            $detalles = $query->get();

            // Formatear respuesta con FileHelper
            $resultado = [];
            foreach ($detalles as $item) {
                // 🔥 Usar FileHelper para obtener las URLs de las imágenes
                $thumbUrl = FileHelper::getOrDownloadFile(
                    'images/items/thumbs/',
                    $item->UrlFoto ?? '',
                    'assets/img/adminlte/img/produc_default.jfif'
                );
                
                $fullUrl = FileHelper::getOrDownloadFile(
                    'images/items/',
                    $item->UrlFoto ?? '',
                    'assets/img/adminlte/img/produc_default.jfif'
                );

                $producto = [
                    'id' => $item->producto_id ?? $item->ProductoId,
                    'codigo' => $item->Codigo,
                    'referencia' => $item->Referencia,
                    'costoDivisa' => (float) ($item->CostoDivisa ?? 0),
                    'urlFoto' => $item->UrlFoto,
                    'thumbUrl' => $thumbUrl,
                    'fullUrl' => $fullUrl
                ];

                $resultado[] = [
                    'producto' => $producto,
                    'existencia' => (int) ($item->Existencia ?? 0),
                    'cantidadContada' => (int) ($item->CantidadContada ?? 0),
                    'cantidadPieSolo' => (int) ($item->CantidadPieSolo ?? 0),
                    'cantidadPieInvertido' => (int) ($item->CantidadPieInvertido ?? 0),
                    'cantidadPiezaDanada' => (int) ($item->CantidadPiezaDanada ?? 0),
                    'cantidadCajaVacia' => (int) ($item->CantidadCajaVacia ?? 0),
                    'totalCostoDetalle' => (float) (($item->CantidadContada - $item->CantidadPieSolo - $item->CantidadPieInvertido - $item->CantidadPiezaDanada) * ($item->CostoDivisa ?? 0)),
                    'diferencia' => (int) (($item->CantidadContada ?? 0) - ($item->Existencia ?? 0))
                ];
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('❌ Error en buscarDetalleJSON: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());
            return response()->json([]);
        }
    }

    public function auditarConteo($id, $tipo)
    {
        try {
            // 1. Buscar el inventario (con el tipo de detalle especificado)
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                return redirect()
                    ->route('cpanel.inventario.listado')
                    ->with('error', 'Inventario no encontrado');
            }

            // 2. Buscar la sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $inventario->SucursalId)
                ->first();

            // 3. Obtener detalles según el tipo (igual que en .NET)
            $query = DB::table('InventarioDetalle')
                ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
                ->where('InventarioDetalle.InventarioId', $id);

            switch ($tipo) {
                case 'diferencias':
                    // EnumInventarioDetalle.Direfencias = 1
                    $query->where('InventarioDetalle.CantidadContada', '!=', 0)
                        ->whereRaw('InventarioDetalle.CantidadContada != InventarioDetalle.Existencia');
                    break;
                case 'exactos':
                    // EnumInventarioDetalle.Exacto = 2
                    $query->where('InventarioDetalle.CantidadContada', '!=', 0)
                        ->whereRaw('InventarioDetalle.CantidadContada = InventarioDetalle.Existencia');
                    break;
                case 'novendible':
                    // EnumInventarioDetalle.NoVendible = 5
                    $query->where(function($q) {
                        $q->where('InventarioDetalle.CantidadPieInvertido', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadPieSolo', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadPiezaDanada', '!=', 0)
                        ->orWhere('InventarioDetalle.CantidadCajaVacia', '!=', 0);
                    });
                    break;
                default:
                    return redirect()
                        ->route('cpanel.inventario.listado')
                        ->with('error', 'Tipo de auditoría no válido');
            }

            $detalles = $query->select(
                    'InventarioDetalle.*',
                    'Productos.Codigo',
                    'Productos.Referencia',
                    'Productos.CostoDivisa',
                    'Productos.UrlFoto'
                )
                ->get();

            // 4. Convertir a DTO (como en .NET)
            $inventarioDTO = (object) [
                'InventarioId' => $inventario->InventarioId,
                'Codigo' => $inventario->Codigo,
                'Descripcion' => $inventario->Descripcion,
                'FechaInicio' => $inventario->FechaInicio,
                'FechaFin' => $inventario->FechaFin,
                'SucursalId' => $inventario->SucursalId,
                'Tipo' => $inventario->Tipo,
                'Estatus' => $inventario->Estatus,
                'Detalles' => $detalles,
                'Sucursal' => $sucursal,
                // 🔥 EsEditable = true (como en .NET)
                'EsEditable' => true
            ];

            // 5. Calcular estadísticas para la vista
            $totalProductos = $detalles->count();
            $totalExistencia = $detalles->sum('Existencia');
            $totalContado = $detalles->sum('CantidadContada');

            // 6. Retornar la vista (igual que .NET)
            return view('cpanel.inventario.auditoria-conteo', [
                'inventario' => $inventarioDTO,
                'detalles' => $detalles,
                'totalProductos' => $totalProductos,
                'totalExistencia' => $totalExistencia,
                'totalContado' => $totalContado,
                'tipo' => $tipo
            ]);

        } catch (\Exception $e) {
            Log::error('Error en auditarConteo: ' . $e->getMessage());
            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('error', 'Error al cargar la auditoría: ' . $e->getMessage());
        }
    }

    public function guardarConteoProducto(Request $request)
    {
        try {
            $detalleId = $request->input('Id');
            $cantidadContada = (int) $request->input('CantidadContada', 0);
            $cantidadPieSolo = (int) $request->input('CantidadPieSolo', 0);
            $cantidadPieInvertido = (int) $request->input('CantidadPieInvertido', 0);
            $cantidadDanado = (int) $request->input('CantidadDanado', 0);
            $sucursalId = (int) $request->input('SucursalId', 0);

            Log::info('📌 guardarConteoProducto - DetalleId: ' . $detalleId);

            // 1. Buscar el detalle por ID
            $detalle = DB::table('InventarioDetalle')
                ->where('InventarioDetalleId', $detalleId)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle no encontrado'
                ]);
            }

            // 2. Guardar el valor anterior (para actualizar cabecera)
            $cantidadContadaAnterior = $detalle->CantidadContada ?? 0;

            // 3. Actualizar el detalle (igual que GuardarConteoProducto en .NET)
            DB::table('InventarioDetalle')
                ->where('InventarioDetalleId', $detalleId)
                ->update([
                    'CantidadContada' => $cantidadContada,
                    'CantidadPieSolo' => $cantidadPieSolo,
                    'CantidadPieInvertido' => $cantidadPieInvertido,
                    'CantidadPiezaDanada' => $cantidadDanado,
                    'CantidadCajaVacia' => 0
                ]);

            // 4. Actualizar la cabecera del inventario (igual que en .NET)
            $this->actualizarCabeceraInventario($detalle->InventarioId, $cantidadContada, $cantidadContadaAnterior);

            // 5. Buscar el producto (igual que en .NET)
            $producto = DB::connection('sqlsrv')
                ->table('Productos')
                ->where('ID', $detalle->ProductoId)
                ->select('ID', 'Codigo', 'Descripcion', 'Referencia', 'CostoDivisa', 'UrlFoto')
                ->first();

            // 6. Obtener el detalle actualizado
            $detalleActualizado = DB::table('InventarioDetalle')
                ->where('InventarioDetalleId', $detalleId)
                ->first();

            // 7. Calcular total y diferencia (para la respuesta)
            $existencia = $detalle->Existencia ?? 0;
            $diferencia = $cantidadContada - $existencia;
            $totalCosto = ($cantidadContada - $cantidadPieSolo - $cantidadPieInvertido - $cantidadDanado) * ($producto->CostoDivisa ?? 0);

            // 8. Obtener el inventario actualizado (igual que en .NET)
            $inventarioActualizado = DB::table('Inventario')
                ->where('InventarioId', $detalle->InventarioId)
                ->first();

            // 🔥 9. Notificar a todos los clientes (equivalente a SignalR)
            // En Laravel, esto se puede hacer con eventos o simplemente retornando los datos
            // El cliente puede actualizar la UI con la respuesta

            return response()->json([
                'success' => true,
                'message' => 'Conteo guardado correctamente',
                'detalle' => $detalleActualizado,
                'producto' => $producto,
                'diferencia' => $diferencia,
                'totalCosto' => $totalCosto,
                'inventario' => $inventarioActualizado
            ]);

        } catch (\Exception $e) {
            Log::error('Error en guardarConteoProducto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el conteo: ' . $e->getMessage()
            ]);
        }
    }

    public function finalizarConteo($id)
    {
        try {
            // 1. Buscar el inventario
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                return redirect()
                    ->route('cpanel.inventario.listado')
                    ->with('error', 'Inventario no encontrado');
            }

            // 2. Cambiar estatus a "Cerrado" (3) y asignar fecha de cierre
            DB::table('Inventario')
                ->where('InventarioId', $id)
                ->update([
                    'Estatus' => 3, // EnumInventario.Cerrado
                    'FechaCierre' => now()
                ]);

            Log::info('✅ Inventario finalizado (cerrado): ' . $id);

            // 3. Redirigir al listado
            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('success', 'El inventario ha sido finalizado correctamente');

        } catch (\Exception $e) {
            Log::error('Error en finalizarConteo: ' . $e->getMessage());
            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('error', 'Error al finalizar el inventario: ' . $e->getMessage());
        }
    }

    public function generarResultadosExcel($id)
    {
        try {
            // 1. Buscar el inventario
            $inventario = DB::table('Inventario')
                ->where('InventarioId', $id)
                ->first();

            if (!$inventario) {
                return back()->with('error', 'Inventario no encontrado');
            }

            // 2. Buscar la sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $inventario->SucursalId)
                ->first();

            // 3. Obtener TODOS los detalles (incluyendo Existencia = 0)
            $todosLosDetalles = DB::table('InventarioDetalle')
                ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
                ->where('InventarioDetalle.InventarioId', $id)
                ->orderBy('Productos.Codigo')
                ->select(
                    'InventarioDetalle.*',
                    'Productos.Codigo',
                    'Productos.Descripcion',
                    'Productos.Referencia',
                    'Productos.CostoDivisa',
                    'Productos.UrlFoto'
                )
                ->get();

            // 4. Filtrar SOLO productos con Existencia > 0 para las hojas principales
            $detallesConExistencia = $todosLosDetalles->filter(function($item) {
                return ($item->Existencia ?? 0) > 0;
            });

            // 5. Crear el Excel con todas las hojas
            $spreadsheet = new Spreadsheet();

            // Eliminar la hoja por defecto
            $spreadsheet->removeSheetByIndex(0);

            // ============================================
            // HOJAS PRINCIPALES (solo productos con Existencia > 0)
            // ============================================
            
            // HOJA 1: COMPLETO
            $this->crearHojaCompleto($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // HOJA 2: AJUSTES
            $this->crearHojaAjustes($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // HOJA 3: SIN CONTAR
            $this->crearHojaSinContar($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // HOJA 4: DIFERENCIAS
            $this->crearHojaDiferencias($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // HOJA 5: NO VENDIBLE
            $this->crearHojaNoVendible($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // HOJA 6: EXACTOS
            $this->crearHojaExactos($spreadsheet, $inventario, $sucursal, $detallesConExistencia);

            // ============================================
            // HOJA 7: VALORIZACION (usa TODOS los productos)
            // ============================================
            $this->crearHojaValorizacion($spreadsheet, $inventario, $sucursal, $todosLosDetalles);

            // ============================================
            // GUARDAR Y DESCARGAR
            // ============================================
            $fileName = 'ResultadosConteo-IV' . $inventario->InventarioId . '.xlsx';
            $filePath = storage_path('app/download/' . $fileName);

            if (!file_exists(storage_path('app/download'))) {
                mkdir(storage_path('app/download'), 0777, true);
            }

            $writer = new XlsxWriter($spreadsheet);
            $writer->save($filePath);

            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error en generarResultadosExcel: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al generar resultados: ' . $e->getMessage());
        }
    }

    /**
     * Crear hoja COMPLETO
     */
    private function crearHojaCompleto($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'COMPLETO');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO', 'VENTA', 'DISPONIBLE', 'NO VENDIBLE', 'DIFERENCIA'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $contado = $detalle->CantidadContada ?? 0;
            $pieSolo = $detalle->CantidadPieSolo ?? 0;
            $pieInvertido = $detalle->CantidadPieInvertido ?? 0;
            $danado = $detalle->CantidadPiezaDanada ?? 0;
            $cajaVacia = $detalle->CantidadCajaVacia ?? 0;

            // VENTA = 0 (no se usa en este contexto)
            $venta = 0;
            // DISPONIBLE = CONTADO - INVERTIDO - SOLO - DAÑADO - VACIO
            $disponible = $contado - $pieInvertido - $pieSolo - $danado - $cajaVacia;
            // NO VENDIBLE = INVERTIDO + SOLO + DAÑADO + VACIO
            $noVendible = $pieInvertido + $pieSolo + $danado + $cajaVacia;
            // DIFERENCIA = CONTADO - EXISTENCIA
            $diferencia = $contado - ($detalle->Existencia ?? 0);
            $textoDiferencia = $diferencia < 0 ? 'Falta' : ($diferencia > 0 ? 'Sobra' : 'Exactos');

            $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
            $sheet->setCellValue('B' . $row, $contado);
            $sheet->setCellValue('C' . $row, $pieInvertido);
            $sheet->setCellValue('D' . $row, $pieSolo);
            $sheet->setCellValue('E' . $row, $danado);
            $sheet->setCellValue('F' . $row, $cajaVacia);
            $sheet->setCellValue('G' . $row, $venta);
            $sheet->setCellValue('H' . $row, $disponible);
            $sheet->setCellValue('I' . $row, $noVendible);
            $sheet->setCellValue('J' . $row, $textoDiferencia);
            $sheet->setCellValue('K' . $row, abs($diferencia));

            $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }
    }

    /**
     * Crear hoja AJUSTES
     */
    private function crearHojaAjustes($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'AJUSTES');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO', 'AJUSTE', 'CANTIDAD'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $contado = $detalle->CantidadContada ?? 0;
            $existencia = $detalle->Existencia ?? 0;
            $diferencia = $contado - $existencia;

            // Solo mostrar productos que tienen diferencia
            if ($diferencia != 0) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $contado);
                $sheet->setCellValue('C' . $row, $detalle->CantidadPieInvertido ?? 0);
                $sheet->setCellValue('D' . $row, $detalle->CantidadPieSolo ?? 0);
                $sheet->setCellValue('E' . $row, $detalle->CantidadPiezaDanada ?? 0);
                $sheet->setCellValue('F' . $row, $detalle->CantidadCajaVacia ?? 0);
                $sheet->setCellValue('G' . $row, $diferencia < 0 ? 'Restar' : 'Sumar');
                
                // 🔥 Si es Restar, la cantidad es negativa; si es Sumar, positiva
                $sheet->setCellValue('H' . $row, $diferencia < 0 ? $diferencia : abs($diferencia));

                $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }
        }
    }

    /**
     * Crear hoja SIN CONTAR
     */
    private function crearHojaSinContar($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'SIN CONTAR');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $contado = $detalle->CantidadContada ?? 0;

            // Solo productos NO contados (CantidadContada == 0)
            if ($contado == 0) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $contado);
                $sheet->setCellValue('C' . $row, $detalle->CantidadPieInvertido ?? 0);
                $sheet->setCellValue('D' . $row, $detalle->CantidadPieSolo ?? 0);
                $sheet->setCellValue('E' . $row, $detalle->CantidadPiezaDanada ?? 0);
                $sheet->setCellValue('F' . $row, $detalle->CantidadCajaVacia ?? 0);

                $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }
        }
    }

    /**
     * Crear hoja DIFERENCIAS (igual que el tab "Con diferencias")
     */
    private function crearHojaDiferencias($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'DIFERENCIAS');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $contado = $detalle->CantidadContada ?? 0;
            $existencia = $detalle->Existencia ?? 0;
            $diferencia = $contado - $existencia;

            // 🔥 Solo productos con diferencia Y que están contados (CantidadContada > 0)
            if ($diferencia != 0 && $contado > 0) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $contado);
                $sheet->setCellValue('C' . $row, $detalle->CantidadPieInvertido ?? 0);
                $sheet->setCellValue('D' . $row, $detalle->CantidadPieSolo ?? 0);
                $sheet->setCellValue('E' . $row, $detalle->CantidadPiezaDanada ?? 0);
                $sheet->setCellValue('F' . $row, $detalle->CantidadCajaVacia ?? 0);

                $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }
        }
    }

    /**
     * Crear hoja NO VENDIBLE
     */
    private function crearHojaNoVendible($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'NO VENDIBLE');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $pieSolo = $detalle->CantidadPieSolo ?? 0;
            $pieInvertido = $detalle->CantidadPieInvertido ?? 0;
            $danado = $detalle->CantidadPiezaDanada ?? 0;
            $cajaVacia = $detalle->CantidadCajaVacia ?? 0;

            // Solo productos NO VENDIBLES (alguno de estos valores > 0)
            if ($pieSolo > 0 || $pieInvertido > 0 || $danado > 0 || $cajaVacia > 0) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $detalle->CantidadContada ?? 0);
                $sheet->setCellValue('C' . $row, $pieInvertido);
                $sheet->setCellValue('D' . $row, $pieSolo);
                $sheet->setCellValue('E' . $row, $danado);
                $sheet->setCellValue('F' . $row, $cajaVacia);

                $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }
        }
    }

    /**
     * Crear hoja EXACTOS
     */
    private function crearHojaExactos($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'EXACTOS');
        $spreadsheet->addSheet($sheet);

        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal);

        $headers = ['CODIGO', 'CONTADO', 'INVERTIDO', 'SOLO', 'DAÑADO', 'VACIO'];
        $this->agregarHeaders($sheet, $headers, 7);

        $row = 8;
        foreach ($detalles as $detalle) {
            $contado = $detalle->CantidadContada ?? 0;
            $existencia = $detalle->Existencia ?? 0;

            // Solo productos EXACTOS (Contado == Existencia y Contado > 0)
            if ($contado == $existencia && $contado > 0) {
                $sheet->setCellValue('A' . $row, $detalle->Codigo ?? '');
                $sheet->setCellValue('B' . $row, $contado);
                $sheet->setCellValue('C' . $row, $detalle->CantidadPieInvertido ?? 0);
                $sheet->setCellValue('D' . $row, $detalle->CantidadPieSolo ?? 0);
                $sheet->setCellValue('E' . $row, $detalle->CantidadPiezaDanada ?? 0);
                $sheet->setCellValue('F' . $row, $detalle->CantidadCajaVacia ?? 0);

                $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;
            }
        }
    }

    /**
     * Convertir a float de forma segura
     */
    private function toFloat($value)
    {
        if (is_null($value)) {
            return 0.0;
        }
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
            $value = str_replace(' ', '', $value);
        }
        return floatval($value);
    }

    /**
     * Crear hoja VALORIZACION (Resumen) - CORREGIDA
     */
    private function crearHojaValorizacion($spreadsheet, $inventario, $sucursal, $detalles)
    {
        $sheet = new Worksheet($spreadsheet, 'VALORIZACION');
        $spreadsheet->addSheet($sheet);

        // Encabezado del inventario
        $this->agregarEncabezadoInventario($sheet, $inventario, $sucursal, 1);

        // Título "RESULTADOS DEL CONTEO"
        $sheet->setCellValue('A7', 'RESULTADOS DEL CONTEO');
        $sheet->mergeCells('A7:E7');
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // ============================================
        // CALCULAR ESTADÍSTICAS
        // ============================================
        $totalItems = $detalles->count();
        $totalExistencia = $this->toFloat($detalles->sum('Existencia'));
        $totalContado = $this->toFloat($detalles->sum('CantidadContada'));
        $itemsContados = $detalles->where('CantidadContada', '>', 0)->count();
        $itemsSinContar = $totalItems - $itemsContados;
        $totalSinContar = $totalExistencia - $totalContado;

        // Montos
        $montoExistencia = $detalles->sum(function($d) {
            return $this->toFloat($d->Existencia ?? 0) * $this->toFloat($d->CostoDivisa ?? 0);
        });
        $montoContado = $detalles->sum(function($d) {
            return $this->toFloat($d->CantidadContada ?? 0) * $this->toFloat($d->CostoDivisa ?? 0);
        });
        $montoSinContar = $montoExistencia - $montoContado;

        // Porcentaje completado
        $porcentajeItems = $totalItems > 0 ? round(($itemsContados / $totalItems) * 100, 0) : 0;
        $porcentajeUnidades = $totalExistencia > 0 ? round(($totalContado / $totalExistencia) * 100, 0) : 0;

        // No vendibles
        $pieSolo = $this->toFloat($detalles->sum('CantidadPieSolo'));
        $pieInvertido = $this->toFloat($detalles->sum('CantidadPieInvertido'));
        $danado = $this->toFloat($detalles->sum('CantidadPiezaDanada'));

        // Montos no vendibles
        $montoPieSolo = $detalles->sum(function($d) {
            return $this->toFloat($d->CantidadPieSolo ?? 0) * $this->toFloat($d->CostoDivisa ?? 0);
        });
        $montoPieInvertido = $detalles->sum(function($d) {
            return $this->toFloat($d->CantidadPieInvertido ?? 0) * $this->toFloat($d->CostoDivisa ?? 0);
        });
        $montoDanado = $detalles->sum(function($d) {
            return $this->toFloat($d->CantidadPiezaDanada ?? 0) * $this->toFloat($d->CostoDivisa ?? 0);
        });

        // 🔥 Cálculos como en .NET - SUBTOTAL y TOTAL GENERAL son POSITIVOS
        $subtotalCantidad = abs($totalContado - $pieSolo - $pieInvertido - $danado);
        $subtotalMonto = abs($montoContado - $montoPieSolo - $montoPieInvertido - $montoDanado);
        $totalGeneralCantidad = $subtotalCantidad;
        $totalGeneralMonto = $subtotalMonto;

        // ============================================
        // TABLA DE RESULTADOS
        // ============================================
        $data = [
            ['ITEMS', 'CANT.', 'UNIDADES', 'CANT.', 'MONTO'],
            ['EXISTENCIA', number_format($totalItems, 2, ',', '.'), 'EXISTENCIA', number_format($totalExistencia, 2, ',', '.'), number_format($montoExistencia, 2, ',', '.')],
            ['CONTADOS', number_format($itemsContados, 2, ',', '.'), 'CONTADOS', number_format($totalContado, 2, ',', '.'), number_format($montoContado, 2, ',', '.')],
            ['SIN CONTAR', number_format($itemsSinContar, 2, ',', '.'), 'SIN CONTAR', number_format($totalSinContar, 2, ',', '.'), number_format($montoSinContar, 2, ',', '.')],
            ['COMPLETADO', $porcentajeItems . '%', 'COMPLETADO', $porcentajeUnidades . '%', ''],
            ['', '', '', '', ''],
            ['CONCEPTO', 'CANTIDAD', '', 'MONTO', ''],
            ['SOBRANTES', '-', '', '-', ''],
            ['FALTANTE', number_format($totalSinContar, 2, ',', '.'), '', number_format($montoSinContar, 2, ',', '.'), ''],
            ['PIE SOLO', number_format($pieSolo, 2, ',', '.'), '', number_format($montoPieSolo, 2, ',', '.'), ''],
            ['PIE INVERTIDO', number_format($pieInvertido, 2, ',', '.'), '', number_format($montoPieInvertido, 2, ',', '.'), ''],
            ['SUBTOTAL', number_format($subtotalCantidad, 2, ',', '.'), '', number_format($subtotalMonto, 2, ',', '.'), ''],
            ['DAÑADO', number_format($danado, 2, ',', '.'), '', number_format($montoDanado, 2, ',', '.'), ''],
            ['TOTAL GENERAL', number_format($totalGeneralCantidad, 2, ',', '.'), '', number_format($totalGeneralMonto, 2, ',', '.'), ''],
        ];

        // ============================================
        // PINTAR DATOS
        // ============================================
        $row = 9;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($col . $row, $cellData);
                $col++;
            }
            $row++;
        }

        // ============================================
        // APLICAR ESTILOS
        // ============================================
        
        // Bordes a toda la tabla
        $sheet->getStyle('A9:E22')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Encabezados de la tabla (fila 9)
        $sheet->getStyle('A9:E9')->getFont()->setBold(true);
        $sheet->getStyle('A9:E9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A9:E9')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Títulos de sección (fila 15)
        $sheet->getStyle('A15:E15')->getFont()->setBold(true);
        $sheet->getStyle('A15:E15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A15:E15')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Fila de SUBTOTAL (fila 21)
        $sheet->getStyle('A21:E21')->getFont()->setBold(true);
        $sheet->getStyle('A21:E21')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Fila de TOTAL GENERAL (fila 23)
        $sheet->getStyle('A23:E23')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A23:E23')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD97706');

        // Alinear números a la derecha
        $sheet->getStyle('B10:B23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D10:D23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E10:E23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ============================================
        // AJUSTAR ANCHO DE COLUMNAS
        // ============================================
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(16);

        // ============================================
        // RESALTADO DE COLORES
        // ============================================
        
        // Fila de CONTADOS (verde claro)
        $sheet->getStyle('A11:E11')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F5E9');

        // Fila de SIN CONTAR (rojo claro)
        $sheet->getStyle('A12:E12')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFEBEE');

        // Total General (naranja, texto blanco)
        $sheet->getStyle('A23:E23')->getFont()->getColor()->setARGB('FFFFFFFF');
    }

    /**
     * Agregar encabezado del inventario a una hoja
     */
    private function agregarEncabezadoInventario($sheet, $inventario, $sucursal, $startRow = 1)
    {
        $row = $startRow;

        $estatusLabels = [0 => 'Nuevo', 1 => 'En Conteo', 2 => 'En Auditoría', 3 => 'Cerrado'];

        // Título
        $sheet->setCellValue('A' . $row, 'CONTEO DE INVENTARIO');
        $sheet->mergeCells('A' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);

        $row++;
        $sheet->setCellValue('A' . $row, 'FECHA INICIO');
        $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($inventario->FechaInicio)));
        $sheet->setCellValue('D' . $row, 'FECHA FIN');
        $sheet->setCellValue('E' . $row, date('d/m/Y', strtotime($inventario->FechaFin)));

        $row++;
        $sheet->setCellValue('A' . $row, 'SUCURSAL');
        $sheet->setCellValue('B' . $row, $sucursal->Nombre ?? 'N/A');
        $sheet->setCellValue('D' . $row, 'ID');
        $sheet->setCellValue('E' . $row, $inventario->SucursalId);

        $row++;
        $sheet->setCellValue('A' . $row, 'TIPO');
        $sheet->setCellValue('B' . $row, $inventario->Tipo == 1 ? 'General' : 'Cíclico');
        $sheet->setCellValue('D' . $row, 'ID');
        $sheet->setCellValue('E' . $row, $inventario->Codigo);

        $row++;
        $sheet->setCellValue('A' . $row, 'EMPLEADO');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('D' . $row, 'ID');
        $sheet->setCellValue('E' . $row, '');

        $row++;
        // Fila vacía
        $row++;

        return $row;
    }

    /**
     * Agregar encabezados de tabla
     */
    private function agregarHeaders($sheet, $headers, $row)
    {
        $col = 'A';
        $colIndex = 0; // 🔥 Variable numérica para contar columnas
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getColumnDimension($col)->setWidth(15);
            $col++;
            $colIndex++;
        }
        
        // 🔥 Usar $colIndex para el rango
        $lastColumn = chr(ord('A') + $colIndex - 1);
        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
    }    

    // En el controlador, modifica el método monitorearConteo
    public function monitorearConteo($id)
    {
        // 1. Buscar el inventario
        $inventario = DB::table('Inventario')
            ->where('InventarioId', $id)
            ->first();

        if (!$inventario) {
            return redirect()
                ->route('cpanel.inventario.listado')
                ->with('error', 'No se pudo encontrar un inventario con el identificador indicado');
        }

        // 2. Buscar la sucursal
        $sucursal = DB::connection('sqlsrv')
            ->table('Sucursales')
            ->where('ID', $inventario->SucursalId)
            ->first();

        // Si es Nuevo, cambiar estatus a "En Conteo"
        if($inventario->Estatus == 0){
            DB::table('Inventario')
                ->where('InventarioId', $id)
                ->update([
                    'Estatus' => 1,
                    'FechaConteo' => now()
                ]);
        }

        // Obtener detalles CON información del producto (todos)
        $detalles = DB::table('InventarioDetalle')
            ->join('Productos', 'InventarioDetalle.ProductoId', '=', 'Productos.ID')
            ->where('InventarioDetalle.InventarioId', $id)
            ->where('InventarioDetalle.Existencia', '>', 0)
            ->select(
                'InventarioDetalle.*',
                'Productos.Codigo',
                'Productos.Referencia',
                'Productos.CostoDivisa',
                'Productos.UrlFoto'
            )
            ->get();

        // Calcular estadísticas
        $totalProductos = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->count();

        $contados = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->where('CantidadContada', '>', 0)
            ->count();

        // Calcular productos exactos (solo entre los que YA FUERON CONTADOS)
        $exactos = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->where('CantidadContada', '>', 0)  // Solo los contados
            ->whereRaw('CantidadContada = Existencia')
            ->count();

        // Calcular productos con diferencias (solo entre los que YA FUERON CONTADOS)
        $conDiferencias = DB::table('InventarioDetalle')
            ->where('InventarioId', $id)
            ->where('Existencia', '>', 0)
            ->where('CantidadContada', '>', 0)  // Solo los contados
            ->whereRaw('CantidadContada != Existencia')
            ->count();

        // Calcular exactitud (solo si hay productos contados)
        $exactitud = 0;
        if ($contados > 0) {
            $exactitud = round(($exactos / $contados) * 100, 2);
        }

        // Calcular avance (progreso del conteo)
        $porcentaje = 0;
        if ($totalProductos > 0) {
            $porcentaje = round(($contados / $totalProductos) * 100, 2);
        }

        return view('cpanel.inventario.monitorear-general', [
            'inventario' => $inventario,
            'sucursal' => $sucursal,
            'detalles' => $detalles,
            'totalProductos' => $totalProductos,
            'contados' => $contados,
            'pendientes' => $totalProductos - $contados,
            'porcentaje' => $porcentaje,
            'exactos' => $exactos,
            'conDiferencias' => $conDiferencias,
            'exactitud' => $exactitud
        ]);
    }
}