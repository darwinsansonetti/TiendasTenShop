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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

use App\Helpers\FileHelper;

class DistribucionController extends Controller
{
    public function distribuciones_listado(Request $request)
    {
        try {
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Nueva Distribución'
            ]);
            
            $sucursalId = auth()->user()->SucursalId ?? null;
            
            if (!$sucursalId) {
                return redirect()->back()->with('error', 'No se ha asignado una sucursal al usuario');
            }
            
            // Buscar distribuciones (Tipo = 0, Estatus <= 2) EnEdicion
            $distribuciones = DB::connection('sqlsrv')
                ->table('TransferenciaTMPTotalizadaView')
                ->where('Tipo', 0)  // 0 = Distribucion
                ->where('Estatus', '<=', 2)  // Estatus <= EnEdicion (2)
                ->orderBy('Fecha', 'desc')
                ->get();
            
            // Obtener sucursales destino para cada distribución
            foreach ($distribuciones as $distribucion) {
                $sucursalesDestino = DB::connection('sqlsrv')
                    ->table('TransferenciasSucursales as ts')
                    ->leftJoin('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                    ->where('ts.TransferenciaId', $distribucion->TransferenciaId)
                    ->select('s.ID', 's.Nombre')
                    ->get();
                
                $distribucion->sucursales_destino = $sucursalesDestino;
            }
            
            // Mapear estatus
            $estatusMap = [
                1 => ['texto' => 'Nueva', 'clase' => 'badge bg-secondary'],
                2 => ['texto' => 'En Edición', 'clase' => 'badge bg-warning'],
                3 => ['texto' => 'Registrada', 'clase' => 'badge bg-info'],
                4 => ['texto' => 'Recibiendo', 'clase' => 'badge bg-primary'],
                5 => ['texto' => 'Disponible', 'clase' => 'badge bg-success'],
                6 => ['texto' => 'Procesada', 'clase' => 'badge bg-dark'],
                9 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger']
            ];
            
            return view('cpanel.distribuciones.index', compact('distribuciones', 'estatusMap'));
            
        } catch (\Exception $e) {
            \Log::error('Error en indexDistribuciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las distribuciones: ' . $e->getMessage());
        }
    }

    public function createDistribucion(Request $request)
    {
        try {
            // ✅ Si hay ID, es una edición (Paso 2)
            $id = $request->input('id');
            
            if ($id) {
                return $this->editDistribucion($id);
            }
            
            // Si no hay ID, es una nueva creación (Paso 1)
            session()->forget('transferencia_activa');
            
            $sucursalAlmacen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 2)
                ->select('ID', 'Nombre')
                ->first();
            
            if (!$sucursalAlmacen) {
                return redirect()->route('cpanel.distribucion.distribuciones')
                    ->with('error', 'No se encontró una sucursal de tipo Almacén');
            }
            
            $sucursalesDestino = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 1)
                ->where('ID', '!=', $sucursalAlmacen->ID)
                ->orderBy('Nombre')
                ->select('ID', 'Nombre', 'Direccion')
                ->get();
            
            $transferencia = (object) [
                'TransferenciaId' => 0,
                'Numero' => null,
                'Fecha' => now()->format('Y-m-d'),
                'SucursalOrigenId' => $sucursalAlmacen->ID,
                'SucursalOrigen' => $sucursalAlmacen->Nombre,
                'Estatus' => 1,  // Nueva (1)
                'Tipo' => 0,     // Distribucion
                'PasoOperacion' => 0,  // PasoUno
                'Observacion' => '',
                'Detalles' => [],
                'sucursales_destino_seleccionadas' => []
            ];
            
            $detalles = [];
            $totalUnidades = 0;
            
            session(['transferencia_activa' => $transferencia]);
            
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Nueva Distribución'
            ]);
            
            return view('cpanel.distribuciones.create', compact(
                'transferencia',
                'sucursalesDestino',
                'sucursalAlmacen',
                'detalles',
                'totalUnidades'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en createDistribucion: ' . $e->getMessage());
            return redirect()->route('cpanel.distribucion.distribuciones')
                ->with('error', 'Error al crear la distribución: ' . $e->getMessage());
        }
    }

    public function storeDistribucion(Request $request)
    {
        try {
            Log::info('=== storeDistribucion INICIO ===');
            
            // 1. Validar datos
            $request->validate([
                'fecha' => 'required|date',
                'observacion' => 'nullable|string'
            ]);
            
            // 2. Obtener la transferencia de la sesión
            $transferencia = session('transferencia_activa');
            
            if (!$transferencia) {
                return redirect()->route('cpanel.distribuciones.create')
                    ->with('error', 'No hay una distribución activa');
            }
            
            // 3. Validar que haya sucursales destino seleccionadas
            $sucursalesSeleccionadas = $transferencia->sucursales_destino_seleccionadas ?? [];
            if (empty($sucursalesSeleccionadas)) {
                return redirect()->route('cpanel.distribuciones.create')
                    ->with('error', 'Debe seleccionar al menos una sucursal destino.')
                    ->withInput();
            }
            
            // 4. Buscar sucursal Almacén (origen)
            $sucursalAlmacen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 2)
                ->first();
            
            if (!$sucursalAlmacen) {
                return redirect()->route('cpanel.distribuciones.create')
                    ->with('error', 'No se encontró una sucursal de tipo Almacén');
            }
            
            // 5. Actualizar datos
            $transferencia->Fecha = $request->input('fecha');
            $transferencia->Observacion = $request->input('observacion');
            $transferencia->SucursalOrigenId = $sucursalAlmacen->ID;
            $transferencia->Estatus = 1;  // Nueva
            $transferencia->Tipo = 0;     // Distribucion
            
            // 6. Generar número
            $numero = 'DIS' . date('YmdHi') . '-' . $sucursalAlmacen->ID;
            $transferencia->Numero = $numero;
            
            Log::info('Número de distribución: ' . $numero);
            
            // 7. Guardar en TransferenciasTMP (SIN created_at y updated_at)
            DB::connection('sqlsrv')->beginTransaction();
            
            $transferenciaId = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->insertGetId([
                    'Numero' => $numero,
                    'Fecha' => $transferencia->Fecha,
                    'SucursalOrigenId' => $transferencia->SucursalOrigenId,
                    'Estatus' => 1,  // Nueva
                    'Tipo' => 0,     // Distribucion
                    'Observacion' => $transferencia->Observacion ?? ''
                    // ❌ Eliminar 'created_at' y 'updated_at'
                ]);
            
            Log::info('TransferenciaTMP creada con ID: ' . $transferenciaId);
            
            // 8. Guardar sucursales destino
            foreach ($sucursalesSeleccionadas as $sucursalId) {
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursalesTMP')
                    ->insert([
                        'TransferenciaId' => $transferenciaId,
                        'SucursalId' => $sucursalId,
                        'Estatus' => 1  // Nueva
                    ]);
                Log::info('Sucursal ' . $sucursalId . ' asociada');
            }
            
            DB::connection('sqlsrv')->commit();
            
            // 9. Obtener productos de la sucursal origen
            $productos = $this->getProductosPorSucursal($transferencia->SucursalOrigenId, true);
            
            Log::info('Productos encontrados: ' . $productos->count());
            
            // 10. Guardar productos en sesión
            session(['productos_disponibles' => $productos]);
            
            // 11. Actualizar sesión
            $transferencia->TransferenciaId = $transferenciaId;
            $transferencia->PasoOperacion = 1;  // PasoDos
            session(['transferencia_activa' => $transferencia]);
            
            // 12. Redirigir a edición (Paso 2)
            return redirect()->route('cpanel.distribuciones.create', ['id' => $transferenciaId])
                    ->with('success', 'Distribución creada exitosamente. Ahora agregue los productos.');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('Error en storeDistribucion: ' . $e->getMessage());
            return redirect()->route('cpanel.distribuciones.create')
                ->with('error', 'Error al crear la distribución: ' . $e->getMessage());
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

    public function editDistribucion($id)
    {
        try {
            // 1. Obtener la transferencia temporal
            $transferencia = DB::connection('sqlsrv')
                ->table('TransferenciasTMP as t')
                ->leftJoin('Sucursales as so', 't.SucursalOrigenId', '=', 'so.ID')
                ->leftJoin('TransferenciasSucursalesTmp as ts', 't.TransferenciaId', '=', 'ts.TransferenciaId')
                ->leftJoin('Sucursales as sd', 'ts.SucursalId', '=', 'sd.ID')
                ->where('t.TransferenciaId', $id)
                ->select([
                    't.TransferenciaId',
                    't.Numero',
                    't.Fecha',
                    't.SucursalOrigenId',
                    't.Estatus',
                    't.Tipo',
                    't.Observacion',
                    'so.Nombre as sucursal_origen',
                    'so.ID as sucursal_origen_id',
                    DB::raw("STRING_AGG(sd.Nombre, ', ') as sucursales_destino_nombres"),
                    DB::raw("STRING_AGG(CAST(sd.ID AS VARCHAR), ',') as sucursales_destino_ids")
                ])
                ->groupBy([
                    't.TransferenciaId',
                    't.Numero',
                    't.Fecha',
                    't.SucursalOrigenId',
                    't.Estatus',
                    't.Tipo',
                    't.Observacion',
                    'so.Nombre',
                    'so.ID'
                ])
                ->first();
            
            if (!$transferencia) {
                return redirect()->route('cpanel.distribucion.distribuciones')
                    ->with('error', 'Distribución no encontrada');
            }
            
            // 2. Obtener los detalles de la transferencia
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTmp as td')
                ->leftJoin('Productos as p', 'td.ProductoId', '=', 'p.ID')
                ->where('td.TransferenciaId', $id)
                ->select([
                    'td.*',
                    'p.Codigo',
                    'p.Descripcion as producto_nombre',
                    'p.Referencia'
                ])
                ->get();
            
            // 3. Obtener productos de la sucursal origen
            $productos = $this->getProductosPorSucursal($transferencia->SucursalOrigenId, true);
            
            // 4. Obtener sucursales destino disponibles
            $sucursalesDestino = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 1)
                ->where('ID', '!=', $transferencia->SucursalOrigenId)
                ->orderBy('Nombre')
                ->select('ID', 'Nombre', 'Direccion')
                ->get();
            
            // 5. Actualizar sesión
            $transferencia->PasoOperacion = 1;  // PasoDos
            session(['transferencia_activa' => $transferencia]);

            if ($transferencia->Fecha) {
                $transferencia->Fecha = \Carbon\Carbon::parse($transferencia->Fecha)->format('Y-m-d');
            }
            
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Nueva Distribución'
            ]);
            
            // 6. Usar la misma vista create pero en modo edición
            return view('cpanel.distribuciones.create', compact(
                'transferencia',
                'detalles',
                'productos',
                'sucursalesDestino'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en editDistribucion: ' . $e->getMessage());
            return redirect()->route('cpanel.distribucion.distribuciones')
                ->with('error', 'Error al cargar la distribución: ' . $e->getMessage());
        }
    }

    public function updateDistribucion(Request $request, $id)
    {
        try {
            Log::info('=== updateDistribucion INICIO ===');
            
            // 1. Validar datos
            $request->validate([
                'fecha' => 'required|date',
                'observacion' => 'nullable|string'
            ]);
            
            // 2. Actualizar la distribución
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $id)
                ->update([
                    'Fecha' => $request->input('fecha'),
                    'Observacion' => $request->input('observacion')
                ]);
            
            Log::info('Distribución actualizada', ['id' => $id]);
            
            // 3. Actualizar sesión
            $transferencia = session('transferencia_activa');
            if ($transferencia) {
                $transferencia->Fecha = $request->input('fecha');
                $transferencia->Observacion = $request->input('observacion');
                $transferencia->TransferenciaId = $id;
                session(['transferencia_activa' => $transferencia]);
            }
            
            // 4. ✅ Redirigir a edición (Paso 2) para seguir agregando productos
            return redirect()->route('cpanel.distribuciones.create', ['id' => $id])
                ->with('success', 'Distribución actualizada exitosamente. Continúe agregando productos.');
            
        } catch (\Exception $e) {
            Log::error('Error en updateDistribucion: ' . $e->getMessage());
            return redirect()->route('cpanel.distribucion.distribuciones')
                ->with('error', 'Error al actualizar la distribución: ' . $e->getMessage());
        }
    }

    public function asociarSucursal(Request $request)
    {
        try {
            \Log::info('=== asociarSucursal INICIO ===');
            $sucursalId = $request->input('sucursal_id');
            \Log::info('sucursal_id: ' . $sucursalId);
            
            // 1. Obtener la transferencia de la sesión
            $transferencia = session('transferencia_activa');
            
            if (!$transferencia) {
                // Si no hay sesión, crear una nueva
                $transferencia = (object) [
                    'TransferenciaId' => 0,
                    'sucursales_destino_seleccionadas' => []
                ];
            }
            
            // 2. Buscar la sucursal
            $sucursal = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $sucursalId)
                ->select('ID', 'Nombre', 'Direccion')
                ->first();
            
            if (!$sucursal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ]);
            }
            
            // 3. Si la transferencia ya existe (ID > 0), guardar en BD
            if ($transferencia->TransferenciaId > 0) {
                // Verificar si ya existe la relación
                $existe = DB::connection('sqlsrv')
                    ->table('TransferenciasSucursalesTmp')
                    ->where('TransferenciaId', $transferencia->TransferenciaId)
                    ->where('SucursalId', $sucursalId)
                    ->exists();
                
                if (!$existe) {
                    DB::connection('sqlsrv')
                        ->table('TransferenciasSucursalesTmp')
                        ->insert([
                            'TransferenciaId' => $transferencia->TransferenciaId,
                            'SucursalId' => $sucursalId,
                            'Estatus' => 1  // Nueva
                        ]);
                }
            }
            
            // 4. Agregar a la lista en sesión
            $seleccionadas = $transferencia->sucursales_destino_seleccionadas ?? [];
            if (!in_array($sucursalId, $seleccionadas)) {
                $seleccionadas[] = $sucursalId;
                $transferencia->sucursales_destino_seleccionadas = $seleccionadas;
                session(['transferencia_activa' => $transferencia]);
            }
            
            \Log::info('=== asociarSucursal FIN ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Sucursal asociada correctamente',
                'sucursal' => $sucursal
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en asociarSucursal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al asociar la sucursal: ' . $e->getMessage()
            ]);
        }
    }
    
    public function removerSucursal(Request $request)
    {
        try {
            \Log::info('=== removerSucursal INICIO ===');
            $sucursalId = $request->input('sucursal_id');
            \Log::info('sucursal_id: ' . $sucursalId);
            
            // Obtener la transferencia de la sesión
            $transferencia = session('transferencia_activa');
            
            if (!$transferencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay una distribución activa'
                ]);
            }
            
            // 1. Si la transferencia ya existe (ID > 0), eliminar de BD
            if ($transferencia->TransferenciaId > 0) {
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursalesTmp')
                    ->where('TransferenciaId', $transferencia->TransferenciaId)
                    ->where('SucursalId', $sucursalId)
                    ->delete();
            }
            
            // 2. Remover de la lista en sesión
            $seleccionadas = $transferencia->sucursales_destino_seleccionadas ?? [];
            $seleccionadas = array_filter($seleccionadas, function($id) use ($sucursalId) {
                return $id != $sucursalId;
            });
            $transferencia->sucursales_destino_seleccionadas = array_values($seleccionadas);
            session(['transferencia_activa' => $transferencia]);
            
            \Log::info('=== removerSucursal FIN ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Sucursal removida correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en removerSucursal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al remover la sucursal: ' . $e->getMessage()
            ]);
        }
    }

    public function downloadDetailsTransferencia(Request $request)
    {
        try {
            // 1. Obtener la transferencia de la sesión
            $transferencia = session('transferencia_activa');
            
            if (!$transferencia || $transferencia->TransferenciaId == 0) {
                return redirect()->back()->with('error', 'No hay una distribución activa');
            }
            
            // Obtener el nombre de la sucursal origen
            $sucursalOrigen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $transferencia->SucursalOrigenId)
                ->value('Nombre');
            
            if (!$sucursalOrigen) {
                return redirect()->back()->with('error', 'Sucursal origen no encontrada');
            }
            
            // 2. Obtener productos
            $productos = $this->getProductosPorSucursal($transferencia->SucursalOrigenId, true);
            
            if ($productos->isEmpty()) {
                return redirect()->back()->with('error', 'No hay productos disponibles en esta sucursal');
            }
            
            // 3. Obtener sucursales destino
            $sucursalesDestino = collect();
            
            if (!empty($transferencia->sucursales_destino_seleccionadas)) {
                $sucursalesDestino = DB::connection('sqlsrv')
                    ->table('Sucursales')
                    ->whereIn('ID', $transferencia->sucursales_destino_seleccionadas)
                    ->orderBy('Nombre')
                    ->get();
            }
            
            if ($sucursalesDestino->isEmpty() && $transferencia->TransferenciaId > 0) {
                $sucursalesDestino = DB::connection('sqlsrv')
                    ->table('TransferenciasSucursalesTmp as ts')
                    ->leftJoin('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                    ->where('ts.TransferenciaId', $transferencia->TransferenciaId)
                    ->select('s.ID', 's.Nombre')
                    ->orderBy('s.Nombre')
                    ->get();
            }
            
            // 4. Crear Excel desde cero
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Títulos
            $sheet->setCellValue('A1', 'TRANSFERENCIA');
            $sheet->setCellValue('A2', 'ENTRADA DE TRANSFERENCIA');
            
            // Cabecera
            $sheet->setCellValue('A4', 'Sucursal Origen');
            $sheet->setCellValue('J4', 'Numero');
            $sheet->setCellValue('A6', 'Fecha');
            $sheet->setCellValue('J6', 'Observaciones');
            $sheet->setCellValue('A9', 'Productos');
            
            // ✅ Encabezados de columnas (fila 10) - SIN IdProducto y SIN Costo
            $sheet->setCellValue('A10', 'Codigo');
            $sheet->setCellValue('B10', 'Referencia');
            $sheet->setCellValue('C10', 'Descripcion');
            $sheet->setCellValue('D10', 'Existencia');
            // ✅ Las sucursales destino empiezan en la columna E (5)
            
            // ✅ Agregar sucursales destino como columnas (desde la columna E = 5)
            $columna = 5; // E (columna 5)
            foreach ($sucursalesDestino as $sucursal) {
                $columnaLetra = $this->getExcelColumnName($columna);
                $sheet->setCellValue($columnaLetra . '10', $sucursal->Nombre . ' (' . $sucursal->ID . ')');
                $columna++;
            }
            
            // Llenar datos de cabecera
            $sheet->setCellValue('B4', $sucursalOrigen);
            $sheet->setCellValue('J4', $transferencia->Numero ?? 'N/A');
            $sheet->setCellValue('B6', now()->format('d/m/Y'));
            $sheet->setCellValue('J6', $transferencia->Observacion ?? '');
            
            // ✅ Llenar productos (sin IdProducto y sin Costo)
            $fila = 11;
            foreach ($productos as $producto) {
                $sheet->setCellValue('A' . $fila, $producto->Codigo);           // Codigo
                $sheet->setCellValue('B' . $fila, $producto->Referencia ?? '');  // Referencia
                $sheet->setCellValue('C' . $fila, $producto->Descripcion);       // Descripcion
                $sheet->setCellValue('D' . $fila, $producto->Existencia ?? 0);   // Existencia
                $fila++;
            }
            
            // Ajustar anchos
            $ultimaColumna = 4 + max($sucursalesDestino->count(), 0);
            $ultimaLetra = $this->getExcelColumnName(max($ultimaColumna, 5));
            foreach (range('A', $ultimaLetra) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Generar archivo
            $writer = new Xlsx($spreadsheet);
            $filename = 'EntradaTransferencia_' . $sucursalOrigen . '_' . $transferencia->TransferenciaId . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Error en downloadDetailsTransferencia: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al descargar el archivo: ' . $e->getMessage());
        }
    }
    
    private function getExcelColumnName($index)
    {
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = (int)(($index - $mod) / 26);
        }
        return $letters;
    }

    public function uploadExcelDistribucion(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:5120'
            ]);

            // 1. Obtener la transferencia de la sesión
            $transferencia = session('transferencia_activa');
            
            if (!$transferencia || $transferencia->TransferenciaId == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay una distribución activa'
                ]);
            }

            // 2. Procesar el archivo Excel
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // 3. Identificar sucursales destino desde la fila 10
            $sucursalesColumnas = [];
            $filaEncabezados = 9; // Fila 10 (índice 9)
            
            if (isset($rows[$filaEncabezados])) {
                for ($i = 4; $i < count($rows[$filaEncabezados]); $i++) {
                    $valor = trim($rows[$filaEncabezados][$i] ?? '');
                    if (!empty($valor)) {
                        // Formato: "Nombre (ID)"
                        preg_match('/\((\d+)\)$/', $valor, $matches);
                        if (isset($matches[1])) {
                            $sucursalesColumnas[] = [
                                'id' => $matches[1],
                                'columna' => $i
                            ];
                        }
                    }
                }
            }

            // 4. Leer productos y cantidades
            $productosData = [];
            for ($i = 10; $i < count($rows); $i++) {
                $row = $rows[$i];
                $codigo = trim($row[0] ?? '');
                if (empty($codigo)) {
                    continue;
                }

                $cantidadesPorSucursal = [];
                foreach ($sucursalesColumnas as $sucursal) {
                    $cantidad = floatval(trim($row[$sucursal['columna']] ?? 0));
                    if ($cantidad > 0) {
                        $cantidadesPorSucursal[$sucursal['id']] = $cantidad;
                    }
                }

                if (!empty($cantidadesPorSucursal)) {
                    // Buscar el producto para validar que existe
                    $producto = DB::connection('sqlsrv')
                        ->table('Productos')
                        ->where('Codigo', $codigo)
                        ->first();
                    
                    if ($producto) {
                        $productosData[] = [
                            'codigo' => $codigo,
                            'producto_id' => $producto->ID,
                            'cantidades' => $cantidadesPorSucursal
                        ];
                    }
                }
            }

            if (empty($productosData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron productos con cantidades en el archivo'
                ]);
            }

            // 5. ✅ SOLO guardar en sesión para mostrar en la vista (NO guardar en BD)
            session(['productos_excel' => $productosData]);
            session(['sucursales_excel' => $sucursalesColumnas]);

            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado correctamente. ' . count($productosData) . ' productos cargados.',
                'productos' => $productosData,
                'sucursales' => $sucursalesColumnas
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en uploadExcelDistribucion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ]);
        }
    }

    public function finalizarDistribucion(Request $request, $id)
    {
        try {
            
            $detallesFrontend = $request->input('detalles', []);
            
            if (empty($detallesFrontend)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos para finalizar'
                ]);
            }

            DB::connection('sqlsrv')->beginTransaction();

            $transferenciaTmp = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $id)
                ->first();

            if (!$transferenciaTmp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distribución no encontrada'
                ]);
            }

            $sucursalesDestino = DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTmp')
                ->where('TransferenciaId', $id)
                ->get();

            if ($sucursalesDestino->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay sucursales destino asociadas'
                ]);
            }

            // Validar existencias
            $errores = [];
            $productosExcedidos = [];

            foreach ($detallesFrontend as $detalle) {
                $producto = DB::connection('sqlsrv')
                    ->table('Productos')
                    ->where('ID', $detalle['producto_id'])
                    ->first();

                if (!$producto) {
                    $errores[] = "Producto ID {$detalle['producto_id']} no encontrado";
                    continue;
                }

                $productoSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('ProductoId', $detalle['producto_id'])
                    ->where('SucursalId', $transferenciaTmp->SucursalOrigenId)
                    ->first();

                $existenciaDisponible = $productoSucursal->Existencia ?? 0;
                $totalAsignado = $detalle['total_asignado'] ?? 0;

                if ($totalAsignado > $existenciaDisponible) {
                    $productosExcedidos[] = [
                        'codigo' => $producto->Codigo,
                        'existencia' => $existenciaDisponible,
                        'total_asignado' => $totalAsignado,
                        'exceso' => $totalAsignado - $existenciaDisponible
                    ];
                }
            }

            if (!empty($errores)) {
                DB::connection('sqlsrv')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => implode("\n", $errores)
                ]);
            }

            if (!empty($productosExcedidos)) {
                DB::connection('sqlsrv')->rollBack();
                $mensaje = "Los siguientes productos exceden la cantidad disponible:\n";
                foreach ($productosExcedidos as $item) {
                    $mensaje .= "• {$item['codigo']}: disponible {$item['existencia']}, asignado {$item['total_asignado']} (exceso de {$item['exceso']})\n";
                }
                return response()->json([
                    'success' => false,
                    'message' => $mensaje,
                    'productos_excedidos' => $productosExcedidos
                ]);
            }

            // Procesar cada sucursal destino
            $transferenciasCreadas = 0;
            $detallesGuardados = 0;

            foreach ($sucursalesDestino as $sucursal) {
                
                $numeroTransferencia = $transferenciaTmp->Numero . '-' . $sucursal->SucursalId;
                
                // Calcular saldo
                $saldo = 0;
                foreach ($detallesFrontend as $detalle) {
                    if (isset($detalle['cantidades'][$sucursal->SucursalId]) && $detalle['cantidades'][$sucursal->SucursalId] > 0) {
                        $producto = DB::connection('sqlsrv')
                            ->table('Productos')
                            ->where('ID', $detalle['producto_id'])
                            ->first();
                        if ($producto) {
                            $saldo += $detalle['cantidades'][$sucursal->SucursalId] * ($producto->CostoDivisa ?? 0);
                        }
                    }
                }

                // ✅ INSERT sin created_at
                $transferenciaId = DB::connection('sqlsrv')
                    ->table('Transferencias')
                    ->insertGetId([
                        'Numero' => $numeroTransferencia,
                        'Fecha' => $transferenciaTmp->Fecha,
                        'SucursalOrigenId' => $transferenciaTmp->SucursalOrigenId,
                        'SucursalDestinoId' => $sucursal->SucursalId,
                        'Estatus' => 3,
                        'Tipo' => 0,
                        'Observacion' => $transferenciaTmp->Observacion ?? '',
                        'Saldo' => $saldo
                        // ❌ Eliminar 'created_at'
                    ]);

                $transferenciasCreadas++;

                // Guardar detalles
                foreach ($detallesFrontend as $detalle) {
                    $cantidad = $detalle['cantidades'][$sucursal->SucursalId] ?? 0;
                    if ($cantidad > 0) {
                        // ✅ INSERT sin created_at
                        DB::connection('sqlsrv')
                            ->table('TransferenciaDetalles')
                            ->insert([
                                'TransferenciaId' => $transferenciaId,
                                'ProductoId' => $detalle['producto_id'],
                                'CantidadEmitida' => $cantidad,
                                'CantidadRecibida' => 0
                                // ❌ Eliminar 'created_at'
                            ]);
                        $detallesGuardados++;
                    }
                }

                // Relación sucursal-transferencia
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursales')
                    ->insert([
                        'TransferenciaId' => $transferenciaId,
                        'SucursalId' => $sucursal->SucursalId,
                        'Estatus' => 1
                    ]);

                // Reducir existencia en origen
                foreach ($detallesFrontend as $detalle) {
                    $cantidad = $detalle['cantidades'][$sucursal->SucursalId] ?? 0;
                    if ($cantidad > 0) {
                        DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $detalle['producto_id'])
                            ->where('SucursalId', $transferenciaTmp->SucursalOrigenId)
                            ->decrement('Existencia', $cantidad);
                    }
                }
            }

            // Actualizar TransferenciasTMP
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $id)
                ->update(['Estatus' => 3]);

            // Actualizar TransferenciasSucursalesTmp
            DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTmp')
                ->where('TransferenciaId', $id)
                ->update(['Estatus' => 3]);

            DB::connection('sqlsrv')->commit();

            session()->forget('transferencia_activa');

            return response()->json([
                'success' => true,
                'message' => 'Distribución finalizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la distribución: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelarDistribucion(Request $request, $id)
    {
        Log::info('=== INICIO cancelarDistribucion (Laravel) ===', [
            'transferencia_id' => $id,
            'usuario' => auth()->id() ?? 'Sistema',
            'fecha' => now()
        ]);

        try {
            DB::connection('sqlsrv')->beginTransaction();
            Log::info('✅ Transacción iniciada');

            // ✅ PRIMERO: Buscar en TransferenciasTMP (temporal)
            $transferenciaTMP = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $id)
                ->first();

            if ($transferenciaTMP) {
                Log::info('📌 Transferencia encontrada en TMP', [
                    'id' => $transferenciaTMP->TransferenciaId,
                    'numero' => $transferenciaTMP->Numero,
                    'estatus' => $transferenciaTMP->Estatus,
                    'origen' => $transferenciaTMP->SucursalOrigenId
                ]);

                // 1. Obtener detalles de TMP
                $detalles = DB::connection('sqlsrv')
                    ->table('TransferenciaDetallesTMP')
                    ->where('TransferenciaId', $id)
                    ->get();

                Log::info('Detalles TMP encontrados', ['total' => $detalles->count()]);

                // 2. Actualizar inventario en origen (usando los detalles TMP)
                $productosActualizados = 0;
                $totalCantidadDevuelta = 0;

                foreach ($detalles as $detalle) {
                    $cantidad = $detalle->CantidadEmitida ?? 0;
                    
                    if ($cantidad > 0) {
                        $productoSucursal = DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $detalle->ProductoId)
                            ->where('SucursalId', $transferenciaTMP->SucursalOrigenId)
                            ->first();

                        if ($productoSucursal) {
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->where('ProductoId', $detalle->ProductoId)
                                ->where('SucursalId', $transferenciaTMP->SucursalOrigenId)
                                ->update([
                                    'Existencia' => $productoSucursal->Existencia + $cantidad
                                ]);

                            Log::info('Producto actualizado en origen (TMP)', [
                                'producto_id' => $detalle->ProductoId,
                                'sucursal_origen' => $transferenciaTMP->SucursalOrigenId,
                                'existencia_anterior' => $productoSucursal->Existencia,
                                'cantidad_agregada' => $cantidad,
                                'existencia_nueva' => $productoSucursal->Existencia + $cantidad
                            ]);
                        } else {
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->insert([
                                    'SucursalId' => $transferenciaTMP->SucursalOrigenId,
                                    'ProductoId' => $detalle->ProductoId,
                                    'Existencia' => $cantidad,
                                    'PvpBs' => 0,
                                    'PvpDivisa' => 0,
                                    'Estatus' => 1,
                                    'FechaIngreso' => now()
                                ]);

                            Log::info('Producto creado en origen (TMP)', [
                                'producto_id' => $detalle->ProductoId,
                                'sucursal_origen' => $transferenciaTMP->SucursalOrigenId,
                                'existencia_inicial' => $cantidad
                            ]);
                        }

                        $productosActualizados++;
                        $totalCantidadDevuelta += $cantidad;
                    }
                }

                Log::info('📊 RESUMEN DEVOLUCIÓN TMP', [
                    'productos_actualizados' => $productosActualizados,
                    'total_cantidad_devuelta' => $totalCantidadDevuelta,
                    'sucursal_origen' => $transferenciaTMP->SucursalOrigenId
                ]);

                // 3. Cambiar estatus en TMP a Procesada (6)
                DB::connection('sqlsrv')
                    ->table('TransferenciasTMP')
                    ->where('TransferenciaId', $id)
                    ->update(['Estatus' => 6]);

                Log::info('✅ Estatus TMP actualizado a Procesada (6)');

            } else {
                // ✅ Si no está en TMP, buscar en Transferencias (definitiva)
                $transferencia = DB::connection('sqlsrv')
                    ->table('Transferencias')
                    ->where('TransferenciaId', $id)
                    ->first();

                if (!$transferencia) {
                    Log::warning('Transferencia no encontrada', ['id' => $id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Distribución no encontrada'
                    ], 404);
                }

                Log::info('📌 Transferencia encontrada en Transferencias (definitiva)', [
                    'id' => $transferencia->TransferenciaId,
                    'numero' => $transferencia->Numero,
                    'estatus' => $transferencia->Estatus,
                    'origen' => $transferencia->SucursalOrigenId,
                    'destino' => $transferencia->SucursalDestinoId
                ]);

                // 1. Obtener detalles de Transferencias
                $detalles = DB::connection('sqlsrv')
                    ->table('TransferenciaDetalles')
                    ->where('TransferenciaId', $id)
                    ->get();

                Log::info('Detalles encontrados', ['total' => $detalles->count()]);

                // 2. Actualizar inventario en origen (sumar al origen - DEVOLUCIÓN)
                $productosActualizados = 0;
                $totalCantidadDevuelta = 0;

                foreach ($detalles as $detalle) {
                    $cantidad = $detalle->CantidadEmitida ?? 0;
                    
                    if ($cantidad > 0) {
                        $productoSucursal = DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $detalle->ProductoId)
                            ->where('SucursalId', $transferencia->SucursalOrigenId)
                            ->first();

                        if ($productoSucursal) {
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->where('ProductoId', $detalle->ProductoId)
                                ->where('SucursalId', $transferencia->SucursalOrigenId)
                                ->update([
                                    'Existencia' => $productoSucursal->Existencia + $cantidad
                                ]);

                            Log::info('Producto actualizado en origen (definitiva)', [
                                'producto_id' => $detalle->ProductoId,
                                'sucursal_origen' => $transferencia->SucursalOrigenId,
                                'existencia_anterior' => $productoSucursal->Existencia,
                                'cantidad_agregada' => $cantidad,
                                'existencia_nueva' => $productoSucursal->Existencia + $cantidad
                            ]);
                        } else {
                            DB::connection('sqlsrv')
                                ->table('ProductoSucursal')
                                ->insert([
                                    'SucursalId' => $transferencia->SucursalOrigenId,
                                    'ProductoId' => $detalle->ProductoId,
                                    'Existencia' => $cantidad,
                                    'PvpBs' => 0,
                                    'PvpDivisa' => 0,
                                    'Estatus' => 1,
                                    'FechaIngreso' => now()
                                ]);

                            Log::info('Producto creado en origen (definitiva)', [
                                'producto_id' => $detalle->ProductoId,
                                'sucursal_origen' => $transferencia->SucursalOrigenId,
                                'existencia_inicial' => $cantidad
                            ]);
                        }

                        $productosActualizados++;
                        $totalCantidadDevuelta += $cantidad;
                    }
                }

                Log::info('📊 RESUMEN DEVOLUCIÓN DEFINITIVA', [
                    'productos_actualizados' => $productosActualizados,
                    'total_cantidad_devuelta' => $totalCantidadDevuelta,
                    'sucursal_origen' => $transferencia->SucursalOrigenId
                ]);

                // 3. Cambiar estatus a Procesada (6)
                DB::connection('sqlsrv')
                    ->table('Transferencias')
                    ->where('TransferenciaId', $id)
                    ->update(['Estatus' => 6]);

                Log::info('✅ Estatus definitivo actualizado a Procesada (6)');
            }

            DB::connection('sqlsrv')->commit();
            Log::info('✅ Transacción confirmada exitosamente');

            return response()->json([
                'success' => true,
                'message' => 'Distribución cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();

            Log::error('❌ ERROR en cancelarDistribucion', [
                'transferencia_id' => $id,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la distribución: ' . $e->getMessage()
            ], 500);
        }
    }

    public function distribuciones_listado_aceptar(Request $request)
    {
        try {
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Listado Dist. / Trans.'
            ]);
            
            $sucursalId = auth()->user()->SucursalId ?? null;
            $userEmail = auth()->user()->Email ?? null;
            $userName = auth()->user()->NombreCompleto ?? null;
            
            // ✅ Verificar si es Super Admin por Email o Nombre
            $esSuperAdmin = (
                $userEmail == 'Hussein@Tiendastenshop.com' ||
                $userName == 'MASTER GENERAL' ||
                $userEmail == 'admin@tiendastenshop.com'
            );
            
            // ✅ Consulta base
            $query = DB::connection('sqlsrv')
                ->table('TransferenciaTotalizadaView')
                ->whereIn('Estatus', [1, 3, 4, 5]); // Nueva, Registrada, Recibiendo, Disponible
            
            // Si NO es Super Admin, filtrar por su sucursal
            if (!$esSuperAdmin) {
                if (!$sucursalId) {
                    return redirect()->back()->with('error', 'No se ha asignado una sucursal al usuario');
                }
                $query->where('SucursalDestinoId', $sucursalId);
            }
            
            $transferencias = $query->orderBy('Fecha', 'desc')->get();
            
            // ✅ Mapear estatus
            $estatusMap = [
                1 => ['texto' => 'Nueva', 'clase' => 'badge bg-secondary text-white'],
                2 => ['texto' => 'En Edición', 'clase' => 'badge bg-warning text-dark'],
                3 => ['texto' => 'Registrada', 'clase' => 'badge bg-info text-white'],
                4 => ['texto' => 'Recibiendo', 'clase' => 'badge bg-primary text-white'],
                5 => ['texto' => 'Disponible', 'clase' => 'badge bg-success text-white'],
                6 => ['texto' => 'Procesada', 'clase' => 'badge bg-dark text-white'],
                9 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger text-white']
            ];
            
            return view('cpanel.distribuciones.listado_transferencias', compact('transferencias', 'estatusMap'));
            
        } catch (\Exception $e) {
            \Log::error('Error en distribuciones_listado_aceptar: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el listado de transferencias: ' . $e->getMessage());
        }
    }

    public function recibirTransferencia($id)
    {
        try {
            // 1. Obtener la transferencia
            $transferencia = DB::connection('sqlsrv')
                ->table('Transferencias as t')
                ->leftJoin('Sucursales as so', 't.SucursalOrigenId', '=', 'so.ID')
                ->leftJoin('Sucursales as sd', 't.SucursalDestinoId', '=', 'sd.ID')
                ->where('t.TransferenciaId', $id)
                ->select([
                    't.*',
                    'so.Nombre as sucursal_origen',
                    'sd.Nombre as sucursal_destino'
                ])
                ->first();
            
            if (!$transferencia) {
                return redirect()->route('cpanel.distribucion.listado')
                    ->with('error', 'Transferencia no encontrada');
            }
            
            // 2. Obtener los detalles de la transferencia (productos)
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetalles as td')
                ->leftJoin('Productos as p', 'td.ProductoId', '=', 'p.ID')
                ->where('td.TransferenciaId', $id)
                ->select([
                    'td.*',
                    'p.Codigo',
                    'p.Descripcion as producto_nombre',
                    'p.Referencia',
                    'p.UrlFoto'
                ])
                ->get();
            
            // 3. Mapear estatus
            $estatusMap = [
                1 => ['texto' => 'Nueva', 'clase' => 'badge bg-secondary text-white'],
                2 => ['texto' => 'En Edición', 'clase' => 'badge bg-warning text-dark'],
                3 => ['texto' => 'Registrada', 'clase' => 'badge bg-info text-white'],
                4 => ['texto' => 'Recibiendo', 'clase' => 'badge bg-primary text-white'],
                5 => ['texto' => 'Disponible', 'clase' => 'badge bg-success text-white'],
                6 => ['texto' => 'Procesada', 'clase' => 'badge bg-dark text-white'],
                9 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger text-white']
            ];
            
            $estatus = $estatusMap[$transferencia->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary text-white'];
            
            // 4. Totales
            $totalItems = $detalles->count();
            $totalUnidades = $detalles->sum('CantidadEmitida');
            $totalRecibido = $detalles->sum('CantidadRecibida');
            
            return view('cpanel.distribuciones.recibir_transferencia', compact(
                'transferencia',
                'detalles',
                'estatus',
                'totalItems',
                'totalUnidades',
                'totalRecibido'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en recibirTransferencia: ' . $e->getMessage());
            return redirect()->route('cpanel.distribucion.listado')
                ->with('error', 'Error al cargar la transferencia: ' . $e->getMessage());
        }
    }

    public function finalizarRecibirTransferencia(Request $request, $id)
    {
        try {
            $detallesFrontend = $request->input('detalles', []);
            
            if (empty($detallesFrontend)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos para recibir'
                ]);
            }
            
            DB::connection('sqlsrv')->beginTransaction();
            
            // 1. Obtener la transferencia
            $transferencia = DB::connection('sqlsrv')
                ->table('Transferencias')
                ->where('TransferenciaId', $id)
                ->first();
            
            if (!$transferencia) {
                return response()->json(['success' => false, 'message' => 'Transferencia no encontrada']);
            }
            
            // 2. Actualizar cantidades recibidas
            foreach ($detallesFrontend as $detalle) {
                DB::connection('sqlsrv')
                    ->table('TransferenciaDetalles')
                    ->where('TransferenciaDetalleId', $detalle['id'])
                    ->update([
                        'CantidadRecibida' => $detalle['cantidad_recibida']
                    ]);
            }
            
            // 3. Cambiar estatus a Procesada (6)
            DB::connection('sqlsrv')
                ->table('Transferencias')
                ->where('TransferenciaId', $id)
                ->update(['Estatus' => 6]);
            
            // 4. Actualizar inventario en sucursal destino
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetalles')
                ->where('TransferenciaId', $id)
                ->get();
            
            foreach ($detalles as $detalle) {
                if ($detalle->CantidadRecibida > 0) {
                    $productoSucursal = DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->where('ProductoId', $detalle->ProductoId)
                        ->where('SucursalId', $transferencia->SucursalDestinoId)
                        ->first();
                    
                    if ($productoSucursal) {
                        DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $detalle->ProductoId)
                            ->where('SucursalId', $transferencia->SucursalDestinoId)
                            ->update([
                                'Existencia' => $productoSucursal->Existencia + $detalle->CantidadRecibida
                            ]);
                    } else {
                        DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->insert([
                                'SucursalId' => $transferencia->SucursalDestinoId,
                                'ProductoId' => $detalle->ProductoId,
                                'Existencia' => $detalle->CantidadRecibida,
                                'PvpBs' => 0,
                                'PvpDivisa' => 0,
                                'Estatus' => 1,
                                'FechaIngreso' => now()
                            ]);
                    }
                }
            }
            
            DB::connection('sqlsrv')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transferencia recibida exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('Error en finalizarRecibirTransferencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al recibir la transferencia: ' . $e->getMessage()
            ]);
        }
    }

    public function detalleTransferencia($id)
    {
        try {
            // 1. Obtener la transferencia
            $transferencia = DB::connection('sqlsrv')
                ->table('Transferencias as t')
                ->leftJoin('Sucursales as so', 't.SucursalOrigenId', '=', 'so.ID')
                ->leftJoin('Sucursales as sd', 't.SucursalDestinoId', '=', 'sd.ID')
                ->where('t.TransferenciaId', $id)
                ->select([
                    't.*',
                    'so.Nombre as sucursal_origen',
                    'sd.Nombre as sucursal_destino'
                ])
                ->first();
            
            if (!$transferencia) {
                return redirect()->route('cpanel.distribucion.listado')
                    ->with('error', 'Transferencia no encontrada');
            }
            
            // 2. Obtener los detalles de la transferencia
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetalles as td')
                ->leftJoin('Productos as p', 'td.ProductoId', '=', 'p.ID')
                ->where('td.TransferenciaId', $id)
                ->select([
                    'td.*',
                    'p.Codigo',
                    'p.Descripcion as producto_nombre',
                    'p.Referencia',
                    'p.UrlFoto'
                ])
                ->get();
            
            // 3. Calcular totales
            $totalItems = $detalles->count();
            $totalUnidades = $detalles->sum('CantidadEmitida');
            $totalRecibido = $detalles->sum('CantidadRecibida');
            
            // 4. Mapear estatus
            $estatusMap = [
                1 => ['texto' => 'Nueva', 'clase' => 'badge bg-secondary text-white'],
                2 => ['texto' => 'En Edición', 'clase' => 'badge bg-warning text-dark'],
                3 => ['texto' => 'Registrada', 'clase' => 'badge bg-info text-white'],
                4 => ['texto' => 'Recibiendo', 'clase' => 'badge bg-primary text-white'],
                5 => ['texto' => 'Disponible', 'clase' => 'badge bg-success text-white'],
                6 => ['texto' => 'Procesada', 'clase' => 'badge bg-dark text-white'],
                9 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger text-white']
            ];
            
            $estatus = $estatusMap[$transferencia->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary text-white'];
            
            return view('cpanel.distribuciones.detalle', compact(
                'transferencia',
                'detalles',
                'estatus',
                'totalItems',
                'totalUnidades',
                'totalRecibido'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detalleTransferencia: ' . $e->getMessage());
            return redirect()->route('cpanel.distribucion.listado')
                ->with('error', 'Error al cargar el detalle de la transferencia: ' . $e->getMessage());
        }
    }

    public function recibirTransferenciaProducto($id)
    {
        \Log::info('=========================================');
        \Log::info('INICIO recibirTransferenciaProducto', ['transferencia_id' => $id, 'inicio' => now()]);
        
        try {
            DB::connection('sqlsrv')->beginTransaction();
            \Log::info('✅ Transacción iniciada');
            
            // 1. Obtener la transferencia
            \Log::info('Paso 1: Obteniendo transferencia', ['transferencia_id' => $id]);
            
            $transferencia = DB::connection('sqlsrv')
                ->table('Transferencias')
                ->where('TransferenciaId', $id)
                ->first();
            
            if (!$transferencia) {
                \Log::warning('Transferencia no encontrada', ['transferencia_id' => $id]);
                return redirect()->route('cpanel.distribucion.listado')
                    ->with('error', 'No se encuentra disponible la transferencia');
            }
            
            \Log::info('Transferencia encontrada', [
                'transferencia_id' => $transferencia->TransferenciaId,
                'numero' => $transferencia->Numero,
                'estatus' => $transferencia->Estatus,
                'origen' => $transferencia->SucursalOrigenId,
                'destino' => $transferencia->SucursalDestinoId,
                'fecha' => $transferencia->Fecha
            ]);
            
            // 2. Validar que esté pendiente
            \Log::info('Paso 2: Validando estatus', ['estatus_actual' => $transferencia->Estatus]);
            
            if (!in_array($transferencia->Estatus, [1, 3, 4, 5])) {
                \Log::warning('Transferencia ya procesada', ['estatus' => $transferencia->Estatus]);
                return redirect()->route('cpanel.distribucion.listado')
                    ->with('error', 'La transferencia ya fue procesada');
            }
            
            // 3. Procesar la recepción
            \Log::info('Paso 3: Procesando recepción de transferencia');
            $this->procesarRecepcionTransferencia($transferencia);
            
            // 4. Cerrar recepciones
            \Log::info('Paso 4: Cerrando recepciones', [
                'sucursal_origen' => $transferencia->SucursalOrigenId,
                'fecha' => $transferencia->Fecha
            ]);
            $this->cerrarRecepciones($transferencia->SucursalOrigenId, $transferencia->Fecha);
            
            DB::connection('sqlsrv')->commit();
            \Log::info('✅ Transacción confirmada exitosamente');
            
            // 5. Limpiar sesión
            session()->forget('transferencia_activa');
            \Log::info('Sesión limpiada');
            
            \Log::info('✅ FINALIZADO recibirTransferenciaProducto', ['transferencia_id' => $id]);
            \Log::info('=========================================');
            
            return redirect()->route('cpanel.distribucion.listado')
                ->with('success', 'La transferencia se finalizó correctamente');
            
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            \Log::error('❌ ERROR en recibirTransferenciaProducto', [
                'transferencia_id' => $id,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('cpanel.distribucion.listado')
                ->with('error', 'No se pudo finalizar la transferencia: ' . $e->getMessage());
        }
    }

    /**
     * Procesar la recepción de la transferencia (equivalente a _transferenciaService.Finalizar())
     */
    private function procesarRecepcionTransferencia($transferencia)
    {
        \Log::info('=== procesarRecepcionTransferencia INICIO ===', [
            'transferencia_id' => $transferencia->TransferenciaId
        ]);
        
        // 1. Obtener detalles
        \Log::info('Obteniendo detalles de la transferencia');
        $detalles = DB::connection('sqlsrv')
            ->table('TransferenciaDetalles')
            ->where('TransferenciaId', $transferencia->TransferenciaId)
            ->get();
        
        \Log::info('Detalles encontrados', ['total_detalles' => $detalles->count()]);
        
        // 2. Actualizar CantidadRecibida
        \Log::info('Actualizando CantidadRecibida');
        $productosActualizados = 0;
        
        foreach ($detalles as $detalle) {
            DB::connection('sqlsrv')
                ->table('TransferenciaDetalles')
                ->where('TransferenciaDetalleId', $detalle->TransferenciaDetalleId)
                ->update([
                    'CantidadRecibida' => $detalle->CantidadEmitida
                ]);
            $productosActualizados++;
            
            \Log::info('Producto actualizado', [
                'detalle_id' => $detalle->TransferenciaDetalleId,
                'producto_id' => $detalle->ProductoId,
                'cantidad_emitida' => $detalle->CantidadEmitida,
                'cantidad_recibida' => $detalle->CantidadEmitida
            ]);
        }
        \Log::info('CantidadRecibida actualizada para ' . $productosActualizados . ' productos');
        
        // 3. Actualizar inventario en sucursal destino
        \Log::info('Actualizando inventario en sucursal destino', [
            'sucursal_destino' => $transferencia->SucursalDestinoId
        ]);
        
        $productosInventario = 0;
        foreach ($detalles as $detalle) {
            if ($detalle->CantidadEmitida > 0) {
                $productoSucursal = DB::connection('sqlsrv')
                    ->table('ProductoSucursal')
                    ->where('ProductoId', $detalle->ProductoId)
                    ->where('SucursalId', $transferencia->SucursalDestinoId)
                    ->first();
                
                if ($productoSucursal) {
                    $nuevaExistencia = $productoSucursal->Existencia + $detalle->CantidadEmitida;
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->where('ProductoId', $detalle->ProductoId)
                        ->where('SucursalId', $transferencia->SucursalDestinoId)
                        ->update([
                            'Existencia' => $nuevaExistencia
                        ]);
                    
                    \Log::info('ProductoSucursal actualizado', [
                        'producto_id' => $detalle->ProductoId,
                        'sucursal_id' => $transferencia->SucursalDestinoId,
                        'existencia_anterior' => $productoSucursal->Existencia,
                        'cantidad_agregada' => $detalle->CantidadEmitida,
                        'existencia_nueva' => $nuevaExistencia
                    ]);
                } else {
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->insert([
                            'SucursalId' => $transferencia->SucursalDestinoId,
                            'ProductoId' => $detalle->ProductoId,
                            'Existencia' => $detalle->CantidadEmitida,
                            'PvpBs' => 0,
                            'PvpDivisa' => 0,
                            'Estatus' => 1,
                            'FechaIngreso' => now()
                        ]);
                    
                    \Log::info('ProductoSucursal insertado', [
                        'producto_id' => $detalle->ProductoId,
                        'sucursal_id' => $transferencia->SucursalDestinoId,
                        'existencia_inicial' => $detalle->CantidadEmitida
                    ]);
                }
                $productosInventario++;
            }
        }
        \Log::info('Inventario actualizado para ' . $productosInventario . ' productos');
        
        // 4. Cambiar estatus a Procesada (6)
        \Log::info('Cambiando estatus de la transferencia a Procesada (6)');
        DB::connection('sqlsrv')
            ->table('Transferencias')
            ->where('TransferenciaId', $transferencia->TransferenciaId)
            ->update(['Estatus' => 6]);
        
        \Log::info('Estatus actualizado', [
            'transferencia_id' => $transferencia->TransferenciaId,
            'nuevo_estatus' => 6
        ]);
        
        \Log::info('=== procesarRecepcionTransferencia FIN ===');
    }

    /**
     * Buscar transferencias con saldo pendiente para cerrar
     */
    private function buscarTransferenciasParaCerrar($sucursalId, $fechaFin)
    {
        \Log::info('=== buscarTransferenciasParaCerrar ===', [
            'sucursal_id' => $sucursalId,
            'fecha_fin' => $fechaFin
        ]);
        
        $transferencias = DB::connection('sqlsrv')
            ->table('Transferencias')
            ->where('SucursalOrigenId', $sucursalId)
            ->where('Fecha', '<=', $fechaFin)
            ->where('Saldo', '>', 0)
            ->get();
        
        \Log::info('Transferencias encontradas', ['total' => $transferencias->count()]);
        
        return $transferencias;
    }

    /**
     * Buscar recepciones con saldo pendiente para cerrar
     */
    private function buscarRecepcionesParaCerrar($sucursalId, $fechaFin)
    {
        \Log::info('=== buscarRecepcionesParaCerrar ===', [
            'sucursal_id' => $sucursalId,
            'fecha_fin' => $fechaFin
        ]);
        
        $recepciones = DB::connection('sqlsrv')
            ->table('Recepciones')
            ->where('SucursalDestinoId', $sucursalId)
            ->whereNotIn('Estatus', [7, 8])  // No Pagada o FinalizadaPagada
            ->where('FechaCreacion', '<=', $fechaFin)
            ->get();
        
        \Log::info('Recepciones encontradas (sin filtrar)', ['total' => $recepciones->count()]);
        
        foreach ($recepciones as $recepcion) {
            // Calcular saldo de la recepción
            $totalPagado = DB::connection('sqlsrv')
                ->table('TransaccionesRecepciones as tr')
                ->join('Transacciones as t', 'tr.TransaccionId', '=', 't.ID')
                ->where('tr.RecepcionId', $recepcion->RecepcionId)
                ->sum('t.MontoDivisaAbonado');
            
            $recepcion->SaldoDivisa = ($recepcion->TotalDivisa ?? 0) - ($totalPagado ?? 0);
            
            \Log::info('Recepción procesada', [
                'recepcion_id' => $recepcion->RecepcionId,
                'total_divisa' => $recepcion->TotalDivisa ?? 0,
                'total_pagado' => $totalPagado ?? 0,
                'saldo_divisa' => $recepcion->SaldoDivisa
            ]);
        }
        
        // Filtrar solo las que tienen saldo > 0
        $recepcionesFiltradas = $recepciones->filter(function($item) {
            return $item->SaldoDivisa > 0;
        });
        
        \Log::info('Recepciones con saldo > 0', ['total' => $recepcionesFiltradas->count()]);
        
        return $recepcionesFiltradas;
    }

    /**
     * Generar una transacción de abono
     */
    private function generarTransaccionAbono($transferencia, $montoAbono)
    {
        $numeroOperacion = 'ABT' . date('Ymd', strtotime($transferencia->Fecha)) . '-' . $transferencia->TransferenciaId;
        
        \Log::info('Generando transacción de abono', [
            'transferencia_id' => $transferencia->TransferenciaId,
            'monto_abono' => $montoAbono,
            'numero_operacion' => $numeroOperacion
        ]);
        
        return [
            'Estatus' => 2,  // Pagada
            'Fecha' => $transferencia->Fecha,
            'FormaDePago' => 0,  // Efectivo
            'MontoAbonado' => 0,
            'MontoDivisaAbonado' => $montoAbono,
            'Descripcion' => 'ABONO DEUDA X TRANSFERENCIA',
            'NumeroOperacion' => $numeroOperacion,
            'Observacion' => 'ABONO DEUDA X TRANSFERENCIA',
            'SucursalId' => $transferencia->SucursalOrigenId,
            'SucursalOrigenId' => $transferencia->SucursalOrigenId,
            'TasaDeCambio' => 0,
            'Tipo' => 7,  // AbonoDeuda
            'UrlComprobante' => null
        ];
    }

    /**
     * Asignar el monto del abono a una recepción
     */
    private function asignarMontoAbono($recepcion, $montoDisponible)
    {
        \Log::info('=== asignarMontoAbono ===', [
            'recepcion_id' => $recepcion->RecepcionId,
            'saldo_divisa' => $recepcion->SaldoDivisa,
            'monto_disponible' => $montoDisponible
        ]);
        
        if ($recepcion->SaldoDivisa > 0 && $recepcion->SaldoDivisa <= $montoDisponible) {
            // Se paga completamente la recepción
            $montoAbono = $recepcion->SaldoDivisa;
            $montoRestante = $montoDisponible - $montoAbono;
            $esCerrarOperacion = true;
            
            \Log::info('Recepción pagada completamente', [
                'monto_abono' => $montoAbono,
                'monto_restante' => $montoRestante
            ]);
        } else {
            // Se paga parcialmente
            $montoAbono = $montoDisponible;
            $montoRestante = 0;
            $esCerrarOperacion = false;
            
            \Log::info('Recepción pagada parcialmente', [
                'monto_abono' => $montoAbono,
                'monto_restante' => $montoRestante
            ]);
        }
        
        return [
            'monto_abono' => $montoAbono,
            'monto_restante' => $montoRestante,
            'es_cerrar_operacion' => $esCerrarOperacion
        ];
    }

    /**
     * Guardar el abono de una recepción
     */
    private function guardarAbonoRecepcion($sucursalId, $abonoData, $recepcionId, $montoAbono, $esCerrarOperacion)
    {
        \Log::info('=== guardarAbonoRecepcion ===', [
            'sucursal_id' => $sucursalId,
            'recepcion_id' => $recepcionId,
            'monto_abono' => $montoAbono,
            'es_cerrar_operacion' => $esCerrarOperacion
        ]);
        
        // 1. Insertar transacción
        $abonoData['MontoDivisaAbonado'] = $montoAbono;
        $transaccionId = DB::connection('sqlsrv')
            ->table('Transacciones')
            ->insertGetId($abonoData);
        
        \Log::info('Transacción insertada', ['transaccion_id' => $transaccionId]);
        
        // 2. Insertar relación TransaccionesRecepciones
        DB::connection('sqlsrv')
            ->table('TransaccionesRecepciones')
            ->insert([
                'RecepcionId' => $recepcionId,
                'TransaccionId' => $transaccionId,
                'SucursalId' => $sucursalId
            ]);
        
        \Log::info('Relación TransaccionesRecepciones insertada');
        
        // 3. Actualizar estatus de la recepción si se pagó completamente
        if ($esCerrarOperacion) {
            $recepcion = DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->first();
            
            $nuevoEstatus = ($recepcion->Estatus == 6) ? 8 : 7;
            // 6 = Finalizada, 8 = FinalizadaPagada, 7 = Pagada
            
            DB::connection('sqlsrv')
                ->table('Recepciones')
                ->where('RecepcionId', $recepcionId)
                ->update(['Estatus' => $nuevoEstatus]);
            
            \Log::info('Estatus de recepción actualizado', [
                'recepcion_id' => $recepcionId,
                'estatus_anterior' => $recepcion->Estatus,
                'nuevo_estatus' => $nuevoEstatus
            ]);
        }
        
        return $transaccionId;
    }

    /**
     * Abonar deuda de una sucursal con una transferencia
     */
    private function abonarDeudaSucursal($sucursalId, $transferencia, $fechaFin)
    {
        \Log::info('=== abonarDeudaSucursal ===', [
            'sucursal_id' => $sucursalId,
            'transferencia_id' => $transferencia->TransferenciaId,
            'saldo' => $transferencia->Saldo,
            'fecha_fin' => $fechaFin
        ]);
        
        // 1. Generar transacción de abono base
        $abonoData = $this->generarTransaccionAbono($transferencia, $transferencia->Saldo);
        $montoDisponible = $transferencia->Saldo;
        $abonosRealizados = 0;
        
        // 2. Buscar recepciones para abonar
        $recepciones = $this->buscarRecepcionesParaCerrar($sucursalId, $fechaFin);
        
        foreach ($recepciones as $recepcion) {
            if ($montoDisponible <= 0) {
                \Log::info('Monto disponible agotado, deteniendo abonos');
                break;
            }
            
            // 3. Asignar monto del abono
            $resultado = $this->asignarMontoAbono($recepcion, $montoDisponible);
            
            // 4. Guardar el abono
            $this->guardarAbonoRecepcion(
                $sucursalId,
                $abonoData,
                $recepcion->RecepcionId,
                $resultado['monto_abono'],
                $resultado['es_cerrar_operacion']
            );
            
            $abonosRealizados++;
            $montoDisponible = $resultado['monto_restante'];
            
            \Log::info('Abono procesado', [
                'recepcion_id' => $recepcion->RecepcionId,
                'abono_numero' => $abonosRealizados,
                'monto_restante' => $montoDisponible
            ]);
        }
        
        // 5. Actualizar saldo de la transferencia
        DB::connection('sqlsrv')
            ->table('Transferencias')
            ->where('TransferenciaId', $transferencia->TransferenciaId)
            ->update(['Saldo' => $montoDisponible]);
        
        \Log::info('Saldo de transferencia actualizado', [
            'transferencia_id' => $transferencia->TransferenciaId,
            'nuevo_saldo' => $montoDisponible,
            'abonos_realizados' => $abonosRealizados
        ]);
    }

    /**
     * Cerrar recepciones de una sucursal (abonar deudas)
     * Equivalente a CerrarRecepciones en .NET
     */
    private function cerrarRecepciones($sucursalId, $fecha)
    {
        \Log::info('=== cerrarRecepciones INICIO ===', [
            'sucursal_id' => $sucursalId,
            'fecha' => $fecha
        ]);
        
        try {
            // 1. Buscar transferencias de la sucursal con saldo > 0
            $transferencias = $this->buscarTransferenciasParaCerrar($sucursalId, $fecha);
            
            if ($transferencias->isEmpty()) {
                \Log::info('No hay transferencias con saldo pendiente');
            } else {
                \Log::info('Transferencias con saldo pendiente', ['total' => $transferencias->count()]);
                
                foreach ($transferencias as $transferencia) {
                    // 2. Abonar deuda con cada transferencia
                    $this->abonarDeudaSucursal($sucursalId, $transferencia, $fecha);
                }
            }
            
            // 3. (Opcional) Buscar ventas diarias de la sucursal
            // NOTA: La parte de ventas es opcional y puede implementarse después
            // $ventas = $this->buscarVentasParaCerrar($sucursalId, $fecha);
            // foreach ($ventas as $venta) {
            //     $this->abonarDeudaSucursal($sucursalId, $venta, $fecha);
            // }
            
            \Log::info('=== cerrarRecepciones FIN ===');
            
        } catch (\Exception $e) {
            \Log::error('Error en cerrarRecepciones: ' . $e->getMessage());
            throw $e;
        }
    }

    public function distribuciones_inventario(Request $request)
    {
        try {
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Inventario de almacen'
            ]);
            
            // ✅ Buscar la sucursal de tipo "Almacén" (Tipo = 0 o el que corresponda)
            $sucursalAlmacen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('Tipo', 2) // 0 = Almacén (ajusta según tu base de datos)
                ->first();

            if (!$sucursalAlmacen) {
                return redirect()->back()->with('error', 'No se encontró la sucursal Almacén');
            }

            // ✅ Obtener productos de la sucursal Almacén
            $productos = $this->getProductosPorSucursal($sucursalAlmacen->ID, true);

            return view('cpanel.distribuciones.inventario', compact('productos', 'sucursalAlmacen'));
            
        } catch (\Exception $e) {
            \Log::error('Error en indexDistribuciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las distribuciones: ' . $e->getMessage());
        }
    }
}