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
use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

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
        try {
            // 1. Validar archivo y sucursal
            $request->validate([
                'excel_file' => 'required|file|max:10240',
                'sucursal_id' => 'required|integer|min:1'
            ]);

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

            // La cantidad está en la columna P (índice 15)
            $colExistencia = 15;

            // 6. Procesar datos
            $productos = [];
            $noEncontrados = [];
            $actualizados = 0;
            $totalFilas = 0;
            $errores = [];
            
            // ✅ Array para almacenar productos problemáticos (auditoría)
            $productosAuditoria = [];
            $auditoriaId = null;
            $numeroAuditoria = null;

            for ($i = $dataStartRowIndex; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                if (empty(array_filter($row))) {
                    continue;
                }

                $codigo = trim($row[$colCodigo] ?? '');
                $referencia = isset($row[$colReferencia]) ? trim($row[$colReferencia]) : '';
                
                // ✅ Caso: Sin Código ni Referencia
                if (empty($codigo) && empty($referencia)) {
                    $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : '';
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

                $totalFilas++;

                // Obtener existencia
                $existenciaRaw = isset($row[$colExistencia]) ? trim($row[$colExistencia]) : '';
                $existencia = str_replace(',', '', $existenciaRaw);
                $existencia = str_replace(' ', '', $existencia);

                if (!is_numeric($existencia)) {
                    $errores[] = "Fila " . ($i + 1) . ": Existencia inválida para producto " . ($codigo ?: $referencia);
                    continue;
                }

                if ($existencia < 0) {
                    $existencia = 0;
                }

                // ============================================================
                // ✅ BUSCAR PRODUCTO
                // ============================================================
                $producto = null;

                // 1. BUSCAR POR CÓDIGO (prioridad absoluta)
                if (!empty($codigo)) {
                    $producto = DB::connection('sqlsrv')
                        ->table('Productos')
                        ->where('Codigo', $codigo)
                        ->first();
                }

                // 2. SOLO si NO hay código, buscar por referencia
                if (!$producto && empty($codigo) && !empty($referencia)) {
                    $productosPorReferencia = DB::connection('sqlsrv')
                        ->table('Productos')
                        ->where('Referencia', $referencia)
                        ->where('Estatus', 1)
                        ->get();

                    if ($productosPorReferencia->count() === 1) {
                        $producto = $productosPorReferencia->first();
                    } elseif ($productosPorReferencia->count() > 1) {
                        $productoIds = $productosPorReferencia->pluck('ID')->toArray();
                        
                        $productosEnSucursal = DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->whereIn('ProductoId', $productoIds)
                            ->where('SucursalId', $sucursalId)
                            ->get()
                            ->keyBy('ProductoId');

                        if ($productosEnSucursal->count() === 1) {
                            $productoId = $productosEnSucursal->keys()->first();
                            $producto = $productosPorReferencia->firstWhere('ID', $productoId);
                        } elseif ($productosEnSucursal->count() > 1) {
                            $codigos = $productosPorReferencia
                                ->filter(function($p) use ($productosEnSucursal) {
                                    return $productosEnSucursal->has($p->ID);
                                })
                                ->pluck('Codigo')
                                ->filter()
                                ->toArray();
                            
                            $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : 'Referencia duplicada';
                            
                            foreach ($productosEnSucursal as $productoEnSucursal) {
                                $producto = $productosPorReferencia->firstWhere('ID', $productoEnSucursal->ProductoId);
                                
                                if ($producto) {
                                    $productosAuditoria[] = [
                                        'sucursal_id' => $sucursalId,
                                        'producto_id' => $producto->ID,
                                        'codigo' => $producto->Codigo ?? '',
                                        'referencia' => $referencia,
                                        'descripcion' => $descripcion,
                                        'cantidad' => (int) $existencia,
                                        'existencia_anterior' => $productoEnSucursal->Existencia ?? 0,
                                        'motivo' => "Referencia duplicada - Seleccione este producto para actualizar"
                                    ];
                                }
                            }
                            continue;
                        } else {
                            $codigos = $productosPorReferencia->pluck('Codigo')->filter()->implode(', ');
                            $descripcion = isset($row[$colDescripcion]) ? trim($row[$colDescripcion]) : '';
                            
                            // ❌ Ningún producto de esta referencia existe en la sucursal
                            // NO se crea auditoría, solo se agrega error
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

                // ✅ Actualizar ProductoSucursal (SOLO si existe)
                $productoSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('ProductoId', $producto->ID)
                    ->where('SucursalId', $sucursalId)
                    ->first();

                if ($productoSucursal) {
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->where('ProductoId', $producto->ID)
                        ->where('SucursalId', $sucursalId)
                        ->update([
                            'Existencia' => (int) $existencia
                        ]);
                    $actualizados++;
                } else {
                    // ❌ Producto no existe en la sucursal - NO se registra en auditoría
                    // Solo se agrega un error para informar al usuario
                    $errores[] = "Fila " . ($i + 1) . ": Producto " . ($codigo ?: $referencia) . " no existe en la sucursal seleccionada. No se puede actualizar.";
                }
            }

            // ============================================================
            // ✅ CREAR AUDITORÍA SOLO SI HAY PRODUCTOS PROBLEMÁTICOS
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

            // Construir mensaje
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
                    $mensaje .= " ... y ' . (count($errores) - 5) . ' más";
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
                'productos_auditoria' => count($productosAuditoria)
            ]);

        } catch (\Exception $e) {
            \Log::error('ERROR AL CARGAR INVENTARIO: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

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
            // env('CLOUDCONVERT_API_KEY_3'),
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

            // 3. Si tiene ProductoId, actualizar la existencia
            if ($detalle->ProductoId) {
                // Obtener el ProductoSucursal actual
                $productoSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('ProductoId', $detalle->ProductoId)
                    ->where('SucursalId', $detalle->SucursalId)
                    ->first();

                if ($productoSucursal) {
                    // Actualizar existencia
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
                    // Si no existe en ProductoSucursal, crearlo
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->insert([
                            'ProductoId' => $detalle->ProductoId,
                            'SucursalId' => $detalle->SucursalId,
                            'Existencia' => $detalle->Cantidad,
                            'Estatus' => 1
                        ]);

                    \Log::info('Producto creado en sucursal por auditoría', [
                        'auditoria_id' => $auditoriaId,
                        'producto_id' => $detalle->ProductoId,
                        'existencia' => $detalle->Cantidad
                    ]);
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
}