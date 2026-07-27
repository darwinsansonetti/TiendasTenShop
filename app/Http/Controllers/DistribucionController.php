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

use Symfony\Component\HttpFoundation\StreamedResponse;

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
    // private function cerrarRecepciones($sucursalId, $fecha)
    // {
    //     \Log::info('=== cerrarRecepciones INICIO ===', [
    //         'sucursal_id' => $sucursalId,
    //         'fecha' => $fecha
    //     ]);
        
    //     try {
    //         // 1. Buscar transferencias de la sucursal con saldo > 0
    //         $transferencias = $this->buscarTransferenciasParaCerrar($sucursalId, $fecha);
            
    //         if ($transferencias->isEmpty()) {
    //             \Log::info('No hay transferencias con saldo pendiente');
    //         } else {
    //             \Log::info('Transferencias con saldo pendiente', ['total' => $transferencias->count()]);
                
    //             foreach ($transferencias as $transferencia) {
    //                 // 2. Abonar deuda con cada transferencia
    //                 $this->abonarDeudaSucursal($sucursalId, $transferencia, $fecha);
    //             }
    //         }
            
    //         // 3. (Opcional) Buscar ventas diarias de la sucursal
    //         // NOTA: La parte de ventas es opcional y puede implementarse después
    //         //$ventas = $this->buscarVentasParaCerrar($sucursalId, $fecha);

    //         $ventasService = new VentasService();

    //         // ✅ Crear objeto con fechaFin para el servicio
    //         $filtroFecha = (object) [
    //             'fechaFin' => $fecha
    //         ];

    //         $ventas = $ventasService->obtenerListadoVentasDiariasParaCerrarSinTotalizar(
    //             $filtroFecha, $sucursalId, true
    //         );
    //         foreach ($ventas as $venta) {
    //             $this->abonarDeudaSucursal($sucursalId, $venta, $fecha);
    //         }
            
    //         \Log::info('=== cerrarRecepciones FIN ===');
            
    //     } catch (\Exception $e) {
    //         \Log::error('Error en cerrarRecepciones: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    private function cerrarRecepciones($sucursalId, $fecha)
    {
        \Log::info('=== cerrarRecepciones INICIO ===', [
            'sucursal_id' => $sucursalId,
            'fecha' => $fecha
        ]);
        
        try {
            // 1. Transferencias
            $transferencias = $this->buscarTransferenciasParaCerrar($sucursalId, $fecha);
            
            if ($transferencias->isNotEmpty()) {
                \Log::info('Transferencias con saldo pendiente', ['total' => $transferencias->count()]);
                foreach ($transferencias as $transferencia) {
                    $this->abonarDeudaSucursal($sucursalId, $transferencia, $fecha);
                }
            }
            
            // 2. Ventas diarias
            $filtroFecha = (object) ['fechaFin' => $fecha];
            
            $ventasService = new VentasService();
            $resultadoVentas = $ventasService->obtenerListadoVentasDiariasParaCerrarSinTotalizar(
                $filtroFecha, $sucursalId, true
            );
            
            // ✅ Normalizar ventas para que sean compatibles con abonarDeudaSucursal
            if (isset($resultadoVentas['ListaVentasDiarias']) && !empty($resultadoVentas['ListaVentasDiarias'])) {
                foreach ($resultadoVentas['ListaVentasDiarias'] as $ventaDTO) {
                    // ✅ Crear objeto normalizado con las propiedades que espera abonarDeudaSucursal
                    $ventaNormalizada = (object) [
                        'TransferenciaId' => null,  // No es una transferencia
                        'VentaDiariaId' => $ventaDTO->id ?? null,
                        'Numero' => 'VENTA-' . ($ventaDTO->id ?? ''),
                        'Fecha' => $ventaDTO->fecha ?? $fecha,
                        'Saldo' => $ventaDTO->saldo ?? 0,
                        'SucursalOrigenId' => $ventaDTO->sucursalId ?? $sucursalId,
                        'SucursalDestinoId' => null,
                        'Estatus' => $ventaDTO->estatus ?? null
                    ];
                    
                    \Log::info('🔄 Procesando venta normalizada', [
                        'venta_id' => $ventaNormalizada->VentaDiariaId,
                        'saldo' => $ventaNormalizada->Saldo
                    ]);
                    
                    $this->abonarDeudaSucursal($sucursalId, $ventaNormalizada, $fecha);
                }
            }
            
            \Log::info('=== cerrarRecepciones FIN ===', [
                'transferencias_procesadas' => $transferencias->count(),
                'ventas_procesadas' => isset($resultadoVentas['ListaVentasDiarias']) ? count($resultadoVentas['ListaVentasDiarias']) : 0
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en cerrarRecepciones: ' . $e->getMessage());
            throw $e;
        }
    }

    private function buscarVentasParaCerrar($sucursalId, $fechaFin)
    {
        \Log::info('=== buscarVentasParaCerrar ===', [
            'sucursal_id' => $sucursalId,
            'fecha_fin' => $fechaFin
        ]);
        
        // Buscar ventas diarias que cumplan con las condiciones:
        // - Sean de la sucursal
        // - Sean del período de fecha
        // - No estén cerradas aún
        $ventas = DB::connection('sqlsrv')
            ->table('VentasDiarias')
            ->where('SucursalId', $sucursalId)
            ->whereDate('Fecha', '<=', $fechaFin)
            ->whereNull('FechaCierre') // No cerrada aún
            ->where('Total', '>', 0) // Con saldo pendiente
            ->select([
                'VentaDiariaId as ID',
                'Numero',
                'Fecha',
                'SucursalId',
                'Total as Saldo',
                'Estatus'
            ])
            ->orderBy('Fecha', 'asc')
            ->get()
            ->map(function($item) {
                // Agregar propiedades necesarias para que funcione con abonarDeudaSucursal
                $item->VentaDiariaId = $item->ID;
                $item->Saldo = $item->Saldo ?? 0;
                return $item;
            });
        
        \Log::info('Ventas encontradas', [
            'sucursal_id' => $sucursalId,
            'total' => $ventas->count()
        ]);
        
        return $ventas;
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

    public function transferencia_listado(Request $request)
    {
        try {
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Nueva Transferencia'
            ]);

            // Obtener transferencias con estatus EnEdicion (2) y Tipo Transferencia (1)
            $transferencias = DB::connection('sqlsrv')
                ->table('TransferenciaTMPTotalizadaView')
                ->where('Estatus', '<=', 2)  // ✅ EnEdicion (2)
                ->where('Tipo', 1)      // Transferencia
                ->select([
                    'TransferenciaId',
                    'Numero',
                    'Fecha',
                    'SucursalOrigenId',
                    'Origen as sucursal_origen',
                    'Estatus',
                    'Tipo',
                    'CantidadEmitida',
                    'CantidadDisponible',
                    'CantidadRecibida',
                    'CantidadItems'
                ])
                ->orderBy('Fecha', 'desc')
                ->get();

            // Formatear datos
            $transferencias->transform(function ($item) {
                // Obtener sucursales destino
                $sucursalesDestino = DB::connection('sqlsrv')
                    ->table('TransferenciasSucursales as ts')
                    ->leftJoin('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                    ->where('ts.TransferenciaId', $item->TransferenciaId)
                    ->pluck('s.Nombre')
                    ->toArray();

                $item->sucursales_destino = implode(', ', $sucursalesDestino);
                $item->FechaFormateada = $item->Fecha ? \Carbon\Carbon::parse($item->Fecha)->format('d/m/Y H:i') : 'N/A';
                $item->EstatusTexto = $this->getEstatusTransferencia($item->Estatus);
                $item->EstatusClase = $this->getEstatusClase($item->Estatus);
                $item->EstatusBadgeStyle = $this->getEstatusBadgeStyle($item->Estatus);
                
                return $item;
            });

            // dd($transferencias);

            return view('cpanel.inventario.listado_transferencias', compact('transferencias'));
            
        } catch (\Exception $e) {
            \Log::error('Error en transferencia_listado: ' . $e->getMessage());
            return back()->with('error', 'Error al listar las transferencias: ' . $e->getMessage());
        }
    }

    private function getEstatusTransferencia($estatus)
    {
        $map = [
            1 => 'Nueva',
            2 => 'En Edición',
            3 => 'Registrada',
            4 => 'Recibiendo',
            5 => 'Disponible',
            6 => 'Procesada',
            9 => 'Anulada',
            10 => 'Todas'
        ];
        return $map[$estatus] ?? 'Desconocido';
    }

    private function getEstatusClase($estatus)
    {
        $map = [
            1 => 'info',        // Nueva
            2 => 'warning',     // En Edición
            3 => 'primary',     // Registrada
            4 => 'success',     // Recibiendo
            5 => 'success',     // Disponible
            6 => 'success',     // Procesada
            9 => 'danger',      // Anulada
            10 => 'secondary'   // Todas
        ];
        return $map[$estatus] ?? 'secondary';
    }

    private function getEstatusBadgeStyle($estatus)
    {
        $map = [
            1 => 'background:rgba(6,182,212,0.1);color:#0c4a6e;border:1px solid rgba(6,182,212,0.25)',  // Nueva
            2 => 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)', // En Edición
            3 => 'background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25)',  // Registrada
            4 => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',  // Recibiendo
            5 => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',  // Disponible
            6 => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',  // Procesada
            9 => 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)',    // Anulada
            10 => 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)' // Todas
        ];
        return $map[$estatus] ?? 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
    }

    public function transferencia_crear(Request $request)
    {
        try {
            // Obtener el ID de la transferencia
            $id = $request->input('id');
            
            session([
                'menu_active' => 'Distribuciones',
                'submenu_active' => 'Nueva Transferencia'
            ]);

            // Obtener sucursales activas
            $sucursales = GeneralHelper::buscarSucursales(0);

            // Crear DTO con valores por defecto
            $transferenciaDTO = (object) [
                'Id' => null,
                'TransferenciaId' => 0,
                'Numero' => null,
                'Estatus' => 1,        // Nueva
                'Fecha' => date('Y-m-d'),
                'PasoOperacion' => 0,   // PasoUno
                'Tipo' => 1,            // Transferencia
                'SucursalOrigenId' => null,
                'SucursalDestinoId' => null,
                'Observacion' => null
            ];

            $listaProductos = collect([]);
            $mostrarProductos = false;
            $sucursalDestinoNombre = '';
            $mostrarTotales = false;  // ✅ Definir aquí con valor por defecto
            $detallesExistentes = collect([]);
            $totalesData = (object) [
                'CantidadItems' => 0,
                'CantidadEmitida' => 0,
                'CostoDivisaTotal' => 0
            ];

            // ✅ Si hay ID, cargar la transferencia desde la base de datos
            if ($id) {
                $transferencia = DB::connection('sqlsrv')
                    ->table('TransferenciasTMP')
                    ->where('TransferenciaId', $id)
                    ->first();

                if ($transferencia) {
                    // Actualizar DTO con datos de la transferencia
                    $transferenciaDTO->Id = $transferencia->TransferenciaId;
                    $transferenciaDTO->TransferenciaId = $transferencia->TransferenciaId;
                    $transferenciaDTO->Numero = $transferencia->Numero;
                    $transferenciaDTO->Fecha = $transferencia->Fecha ? date('Y-m-d', strtotime($transferencia->Fecha)) : date('Y-m-d');
                    $transferenciaDTO->SucursalOrigenId = $transferencia->SucursalOrigenId;
                    $transferenciaDTO->Observacion = $transferencia->Observacion ?? '';
                    $transferenciaDTO->Estatus = $transferencia->Estatus ?? 1;
                    $transferenciaDTO->Tipo = $transferencia->Tipo ?? 1;
                    $transferenciaDTO->PasoOperacion = 1; // PasoDos

                    // ✅ Obtener sucursal destino
                    $sucursalDestino = DB::connection('sqlsrv')
                        ->table('TransferenciasSucursalesTMP')
                        ->where('TransferenciaId', $id)
                        ->first();

                    if ($sucursalDestino) {
                        $transferenciaDTO->SucursalDestinoId = $sucursalDestino->SucursalId;
                        
                        // ✅ Obtener el nombre de la sucursal destino
                        $sucursalInfo = DB::connection('sqlsrv')
                            ->table('Sucursales')
                            ->where('ID', $sucursalDestino->SucursalId)
                            ->first();
                        
                        if ($sucursalInfo) {
                            $sucursalDestinoNombre = $sucursalInfo->Nombre;
                        }
                    }

                    // ✅ Obtener productos de la sucursal origen
                    $listaProductos = $this->buscarProductosPorSucursal(
                        $transferenciaDTO->SucursalOrigenId,
                        true
                    );

                    // ✅ Cargar los detalles existentes (cantidades guardadas)
                    $detallesExistentes = DB::connection('sqlsrv')
                        ->table('TransferenciaDetallesTMP')
                        ->where('TransferenciaId', $id)
                        ->select('ProductoId', 'CantidadEmitida')
                        ->get()
                        ->keyBy('ProductoId');

                    // ✅ Calcular totales desde la base de datos
                    $totales = DB::connection('sqlsrv')
                        ->table('TransferenciaTMPTotalizadaView')
                        ->where('TransferenciaId', $id)
                        ->select([
                            'CantidadEmitida',
                            'CantidadRecibida',
                            'CantidadDisponible',
                            'CantidadItems'
                        ])
                        ->first();

                    // ✅ Calcular costo divisa total
                    $costoDivisaTotal = 0;
                    foreach ($detallesExistentes as $detalle) {
                        $producto = DB::connection('sqlsrv')
                            ->table('ProductosSucursalView')
                            ->where('ID', $detalle->ProductoId)
                            ->select('CostoDivisa')
                            ->first();
                        
                        if ($producto) {
                            $costoDivisaTotal += $detalle->CantidadEmitida * ($producto->CostoDivisa ?? 0);
                        }
                    }

                    // ✅ Crear objeto con los totales
                    $totalesData = (object) [
                        'CantidadItems' => (int) ($totales->CantidadItems ?? 0),
                        'CantidadEmitida' => (int) ($totales->CantidadEmitida ?? 0),
                        'CostoDivisaTotal' => $costoDivisaTotal
                    ];

                    // ✅ Combinar productos con sus cantidades guardadas
                    $listaProductos = $listaProductos->map(function($producto) use ($detallesExistentes) {
                        $producto->CantidadGuardada = 0;
                        if (isset($detallesExistentes[$producto->ID])) {
                            $producto->CantidadGuardada = $detallesExistentes[$producto->ID]->CantidadEmitida;
                        }
                        return $producto;
                    });

                    $mostrarProductos = true;
                    $mostrarTotales = true;  // ✅ Activar la sección de totales

                    Log::info('📦 Transferencia cargada', [
                        'id' => $id,
                        'numero' => $transferenciaDTO->Numero,
                        'productos' => $listaProductos->count(),
                        'detalles_cargados' => $detallesExistentes->count(),
                        'total_items' => $totalesData->CantidadItems,
                        'total_unidades' => $totalesData->CantidadEmitida,
                        'total_costo_divisa' => $totalesData->CostoDivisaTotal
                    ]);
                }
            }

            return view('cpanel.distribuciones.transferencia_crear', compact(
                'sucursales',
                'transferenciaDTO',
                'listaProductos',
                'mostrarProductos',
                'mostrarTotales',       // ✅ Ahora siempre existe
                'sucursalDestinoNombre',
                'detallesExistentes',
                'totalesData'           // ✅ Ahora siempre existe
            ));

        } catch (\Exception $e) {
            Log::error('Error en transferencia_crear: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Inicia una nueva transferencia
     */
    public function transferencia_iniciar(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'sucursal_origen' => 'required|integer|min:1',
                'sucursal_destino' => 'required|integer|min:1|different:sucursal_origen',
                'fecha' => 'required|date',
                'observacion' => 'nullable|string|max:500'
            ]);

            $transferenciaId = (int) $request->input('transferencia_id', 0);
            $sucursalOrigenId = (int) $request->sucursal_origen;
            $sucursalDestinoId = (int) $request->sucursal_destino;

            // Si es nueva transferencia (ID = 0)
            if ($transferenciaId == 0) {
                
                // Generar número de transferencia
                $fecha = date('YmdHi');
                $numero = "TRA{$fecha}-{$sucursalOrigenId}";

                DB::connection('sqlsrv')->beginTransaction();

                try {
                    // 1. Insertar cabecera de transferencia (SIN SucursalDestinoId)
                    $transferenciaId = DB::connection('sqlsrv')
                        ->table('TransferenciasTMP')
                        ->insertGetId([
                            'Numero' => $numero,
                            'Fecha' => $request->fecha,
                            'SucursalOrigenId' => $sucursalOrigenId,
                            'Estatus' => 1, // Nueva
                            'Tipo' => 1, // Transferencia
                            'Observacion' => $request->observacion
                        ]);

                    // 2. Insertar sucursal destino en TransferenciasSucursalesTMP
                    DB::connection('sqlsrv')
                        ->table('TransferenciasSucursalesTMP')
                        ->insert([
                            'TransferenciaId' => $transferenciaId,
                            'SucursalId' => $sucursalDestinoId,
                            'Estatus' => 1 // Nueva
                        ]);

                    DB::connection('sqlsrv')->commit();

                    Log::info('✅ Transferencia creada', [
                        'id' => $transferenciaId,
                        'numero' => $numero,
                        'sucursal_origen' => $sucursalOrigenId,
                        'sucursal_destino' => $sucursalDestinoId
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Se ha iniciado la transferencia, por favor agregue los productos',
                        'transferencia_id' => $transferenciaId,
                        'redirect' => route('cpanel.distribucion.transferencia-crear', ['id' => $transferenciaId])
                    ]);

                } catch (\Exception $e) {
                    DB::connection('sqlsrv')->rollBack();
                    Log::error('❌ Error al crear transferencia: ' . $e->getMessage());
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo iniciar la transferencia: ' . $e->getMessage()
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Funcionalidad de edición pendiente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al iniciar transferencia: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar la transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca productos por sucursal
     */
    private function buscarProductosPorSucursal($sucursalId, $soloConExistencia = true)
    {
        try {
            $productos = DB::connection('sqlsrv')
                ->table('ProductosSucursalView')
                ->where('SucursalId', $sucursalId)
                ->where(function ($query) {
                    $query->where('Estatus', 10)  // EnumProducto.Todos = 10
                        ->orWhere('Estatus', 1); // Activo
                })
                ->when($soloConExistencia, function ($query) {
                    $query->where('Existencia', '>', 0);
                })
                ->select([
                    'ID',
                    'Codigo',
                    'CodigoBarra',
                    'Referencia',
                    'Descripcion',
                    'SucursalId',
                    'CostoBs',
                    'CostoDivisa',
                    'UrlFoto',
                    'FechaActualizacion',
                    'FechaCreacion',
                    'DepartamentoId',
                    'EsProveedorAsignado',
                    'PvpBs',
                    'PvpDivisa',
                    'Estatus',
                    'Existencia',
                    'FechaUltimaVenta',
                    'NuevoPvp',
                    'FechaNuevoPrecio',
                    'Tipo',
                    'PvpAnterior'
                ])
                ->orderBy('Codigo')
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'ID' => $item->ID,
                        'Codigo' => $item->Codigo,
                        'CodigoBarra' => $item->CodigoBarra,
                        'Referencia' => $item->Referencia,
                        'Descripcion' => $item->Descripcion,
                        'SucursalId' => $item->SucursalId,
                        'CostoBs' => $item->CostoBs,
                        'CostoDivisa' => $item->CostoDivisa,
                        'UrlFoto' => $item->UrlFoto,
                        'FechaActualizacion' => $item->FechaActualizacion,
                        'FechaCreacion' => $item->FechaCreacion,
                        'DepartamentoId' => $item->DepartamentoId,
                        'EsProveedorAsignado' => $item->EsProveedorAsignado,
                        'PvpBs' => $item->PvpBs,
                        'PvpDivisa' => $item->PvpDivisa,
                        'Estatus' => $item->Estatus,
                        'Existencia' => $item->Existencia,
                        'FechaUltimaVenta' => $item->FechaUltimaVenta,
                        'NuevoPvp' => $item->NuevoPvp,
                        'FechaNuevoPrecio' => $item->FechaNuevoPrecio,
                        'Tipo' => $item->Tipo,
                        'PvpAnterior' => $item->PvpAnterior
                    ];
                });

            Log::info('📦 Productos encontrados', [
                'sucursal_id' => $sucursalId,
                'total' => $productos->count()
            ]);

            return $productos;

        } catch (\Exception $e) {
            Log::error('Error al buscar productos: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function guardarDetalleTransferencia(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'transferencia_id' => 'required|integer',
                'detalles' => 'required|array',
                'detalles.*.producto_id' => 'required|integer',
                'detalles.*.sucursal_id' => 'required|integer',
                'detalles.*.cantidad' => 'required|numeric|min:0'
            ]);

            $transferenciaId = $request->transferencia_id;
            $detalles = $request->detalles;

            Log::info('📦 Guardando detalles de transferencia', [
                'transferencia_id' => $transferenciaId,
                'total_detalles' => count($detalles)
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            $totalEmitidos = 0;
            $totalItems = 0;

            // ==========================================
            // 1. TABLA: TransferenciaDetallesTMP
            // ==========================================
            foreach ($detalles as $detalle) {
                $productoId = $detalle['producto_id'];
                $sucursalId = $detalle['sucursal_id'];
                $cantidad = $detalle['cantidad'];

                // Verificar si el detalle ya existe
                $detalleExistente = DB::connection('sqlsrv')
                    ->table('TransferenciaDetallesTMP')
                    ->where('TransferenciaId', $transferenciaId)
                    ->where('ProductoId', $productoId)
                    ->where('SucursalId', $sucursalId)
                    ->first();

                if ($detalleExistente) {
                    // ✅ UPDATE: Actualizar detalle existente
                    DB::connection('sqlsrv')
                        ->table('TransferenciaDetallesTMP')
                        ->where('TransferenciaDetalleId', $detalleExistente->TransferenciaDetalleId)
                        ->update([
                            'CantidadEmitida' => $cantidad,
                            'CantidadDisponible' => $cantidad
                        ]);
                } else {
                    // ✅ INSERT: Nuevo detalle
                    DB::connection('sqlsrv')
                        ->table('TransferenciaDetallesTMP')
                        ->insert([
                            'TransferenciaId' => $transferenciaId,
                            'ProductoId' => $productoId,
                            'SucursalId' => $sucursalId,
                            'CantidadEmitida' => $cantidad,
                            'CantidadDisponible' => $cantidad,
                            'CantidadRecibida' => 0
                        ]);
                }

                $totalEmitidos += $cantidad;
                $totalItems++;
            }

            // ==========================================
            // 2. TABLA: TransferenciasTMP (SOLO Estatus)
            // ✅ Igual que en .NET
            // ==========================================
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->update([
                    'Estatus' => 2 // EnEdicion
                ]);

            DB::connection('sqlsrv')->commit();

            Log::info('✅ Detalles guardados correctamente', [
                'transferencia_id' => $transferenciaId,
                'total_emitidos' => $totalEmitidos,
                'total_items' => $totalItems
            ]);

            // ==========================================
            // 3. Obtener totales desde la VISTA (igual que .NET)
            // ==========================================
            $totales = DB::connection('sqlsrv')
                ->table('TransferenciaTMPTotalizadaView')
                ->where('TransferenciaId', $transferenciaId)
                ->select([
                    'CantidadEmitida',
                    'CantidadRecibida',
                    'CantidadDisponible',
                    'CantidadItems'
                ])
                ->first();

            // ✅ Calcular CostoDivisaTotal
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->get();

            $costoDivisaTotal = 0;
            foreach ($detalles as $detalle) {
                $producto = DB::connection('sqlsrv')
                    ->table('ProductosSucursalView')
                    ->where('ID', $detalle->ProductoId)
                    ->select('CostoDivisa')
                    ->first();
                
                if ($producto) {
                    $costoDivisaTotal += $detalle->CantidadEmitida * ($producto->CostoDivisa ?? 0);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Detalles guardados correctamente',
                'totales' => [
                    'CantidadItems' => (int) ($totales->CantidadItems ?? 0),
                    'CantidadEmitida' => (int) ($totales->CantidadEmitida ?? 0),
                    'CostoDivisaTotal' => $costoDivisaTotal
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('❌ Error al guardar detalles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarProductosTransferencia($id)
    {
        try {
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $id)
                ->get();

            $total_items = $detalles->count();
            $total_unidades = $detalles->sum('CantidadEmitida');

            return response()->json([
                'success' => true,
                'has_productos' => $total_items > 0,
                'total_items' => $total_items,
                'total_unidades' => (int) $total_unidades
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerTotalesTransferencia($transferenciaId)
    {
        $totales = DB::connection('sqlsrv')
            ->table('Transferencias')
            ->where('ID', $transferenciaId)
            ->select([
                'CantidadEmitida',
                'CantidadDisponible',
                'CantidadRecibida',
                'CantidadItems'
            ])
            ->first();

        // Obtener detalles
        $detalles = DB::connection('sqlsrv')
            ->table('TransferenciaDetallesTMP as td')
            ->leftJoin('Productos as p', 'td.ProductoId', '=', 'p.ID')
            ->where('td.TransferenciaId', $transferenciaId)
            ->select([
                'td.*',
                'p.Codigo',
                'p.Descripcion',
                'p.Referencia'
            ])
            ->get();

        return (object) [
            'totales' => $totales,
            'detalles' => $detalles,
            'total_productos' => $detalles->count()
        ];
    }

    public function eliminarDetalleTransferencia(Request $request)
    {
        try {
            // 1. Validar datos
            $request->validate([
                'transferencia_id' => 'required|integer',
                'producto_id' => 'required|integer',
                'sucursal_id' => 'required|integer'
            ]);

            $transferenciaId = $request->transferencia_id;
            $productoId = $request->producto_id;
            $sucursalId = $request->sucursal_id;

            Log::info('🗑️ Eliminando detalle de transferencia', [
                'transferencia_id' => $transferenciaId,
                'producto_id' => $productoId,
                'sucursal_id' => $sucursalId
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // 2. Buscar el detalle
            $detalle = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->where('ProductoId', $productoId)
                ->where('SucursalId', $sucursalId)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'El detalle no existe'
                ], 404);
            }

            // 3. Eliminar el detalle (igual que en .NET)
            DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->where('ProductoId', $productoId)
                ->where('SucursalId', $sucursalId)
                ->delete();

            // 4. Obtener totales actualizados desde la vista (igual que en .NET)
            $totales = DB::connection('sqlsrv')
                ->table('TransferenciaTMPTotalizadaView')
                ->where('TransferenciaId', $transferenciaId)
                ->select([
                    'CantidadEmitida',
                    'CantidadRecibida',
                    'CantidadDisponible',
                    'CantidadItems'
                ])
                ->first();

            // ✅ 5. NO actualizar TransferenciasTMP (igual que en .NET)
            // El estatus permanece como está (2 = EnEdicion)
            // En .NET NO se modifica el estatus al eliminar un producto

            DB::connection('sqlsrv')->commit();

            Log::info('✅ Detalle eliminado correctamente', [
                'transferencia_id' => $transferenciaId,
                'producto_id' => $productoId,
                'estatus_permanece' => 2 // EnEdicion (igual que en .NET)
            ]);

            $detalles = DB::connection('sqlsrv')
            ->table('TransferenciaDetallesTMP')
            ->where('TransferenciaId', $transferenciaId)
            ->get();

            $costoDivisaTotal = 0;
            foreach ($detalles as $detalle) {
                $producto = DB::connection('sqlsrv')
                    ->table('ProductosSucursalView')
                    ->where('ID', $detalle->ProductoId)
                    ->select('CostoDivisa')
                    ->first();
                
                if ($producto) {
                    $costoDivisaTotal += $detalle->CantidadEmitida * ($producto->CostoDivisa ?? 0);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente',
                'totales' => [
                    'CantidadItems' => (int) ($totales->CantidadItems ?? 0),
                    'CantidadEmitida' => (int) ($totales->CantidadEmitida ?? 0),
                    'CostoDivisaTotal' => $costoDivisaTotal
                ]
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('❌ Error al eliminar detalle: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarPlantillaTransferencia($id)
    {
        try {
            // 1. Obtener la transferencia
            $transferencia = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $id)
                ->first();

            if (!$transferencia) {
                return back()->with('error', 'Transferencia no encontrada');
            }

            // 2. Obtener la sucursal origen
            $sucursalOrigen = DB::connection('sqlsrv')
                ->table('Sucursales')
                ->where('ID', $transferencia->SucursalOrigenId)
                ->first();

            // 3. Obtener la sucursal destino
            $sucursalDestino = DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTMP as ts')
                ->join('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                ->where('ts.TransferenciaId', $id)
                ->select('s.ID', 's.Nombre')
                ->first();

            if (!$sucursalDestino) {
                return back()->with('error', 'Sucursal destino no encontrada');
            }

            // 4. Obtener los productos de la sucursal origen con existencia
            $productos = DB::connection('sqlsrv')
                ->table('ProductosSucursalView')
                ->where('SucursalId', $transferencia->SucursalOrigenId)
                ->where('Existencia', '>', 0)
                ->where('Estatus', 1)
                ->select([
                    'ID',
                    'Codigo',
                    'Referencia',
                    'Descripcion',
                    'Existencia'
                ])
                ->orderBy('Codigo')
                ->get();

            // 5. Crear el Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Hoja1');

            // ==========================================
            // ENCABEZADOS DEL EXCEL (igual que en .NET)
            // ==========================================

            // Título: TRANSFERENCIA
            $sheet->setCellValue('A1', 'TRANSFERENCIA');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Subtítulo: ENTRADA DE TRANSFERENCIA
            $sheet->setCellValue('A2', 'ENTRADA DE TRANSFERENCIA');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Fila 4: Sucursal Origen y Número
            $sheet->setCellValue('A4', 'Sucursal Origen');
            $sheet->setCellValue('B4', $sucursalOrigen->Nombre ?? '');
            $sheet->setCellValue('E4', 'Numero');
            $sheet->setCellValue('F4', $transferencia->Numero ?? '');
            $sheet->getStyle('A4:E4')->getFont()->setBold(true);

            // Fila 6: Fecha y Observaciones
            $sheet->setCellValue('A6', 'Fecha');
            $sheet->setCellValue('B6', date('d/m/Y', strtotime($transferencia->Fecha)));
            $sheet->setCellValue('E6', 'Observaciones');
            $sheet->setCellValue('F6', $transferencia->Observacion ?? '');
            $sheet->getStyle('A6:E6')->getFont()->setBold(true);

            // Fila 8: Título de la tabla
            $sheet->setCellValue('A8', 'Productos');
            $sheet->mergeCells('A8:G8');
            $sheet->getStyle('A8')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'name' => 'Arial'],
            ]);

            // Fila 9: Encabezados de la tabla (SIN IdProducto y SIN Costo)
            $headers = [
                'A' => 'Codigo',
                'B' => 'Referencia',
                'C' => 'Descripcion',
                'D' => 'Existencia',
                'E' => $sucursalDestino->Nombre . ' (' . $sucursalDestino->ID . ')'
            ];

            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . '9', $header);
            }

            // Estilo de encabezados
            $sheet->getStyle('A9:E9')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
            ]);
            $sheet->getRowDimension(9)->setRowHeight(20);

            // ==========================================
            // DATOS DE PRODUCTOS
            // ==========================================
            $fila = 10;
            foreach ($productos as $producto) {
                $sheet->setCellValue('A' . $fila, $producto->Codigo ?? '');
                $sheet->setCellValue('B' . $fila, $producto->Referencia ?? '');
                $sheet->setCellValue('C' . $fila, $producto->Descripcion ?? '');
                $sheet->setCellValue('D' . $fila, $producto->Existencia ?? 0);
                $sheet->setCellValue('E' . $fila, ''); // Columna vacía para que el usuario ingrese la cantidad

                // Bordes
                // $sheet->getStyle('A' . $fila . ':E' . $fila)->applyFromArray([
                //     'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                // ]);

                // Filas alternadas
                if ($fila % 2 == 0) {
                    $sheet->getStyle('A' . $fila . ':E' . $fila)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
                    ]);
                }

                $fila++;
            }

            // ==========================================
            // CONFIGURAR ANCHOS DE COLUMNA
            // ==========================================
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(40);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(30);

            // ==========================================
            // GENERAR ARCHIVO
            // ==========================================
            $writer = new Xlsx($spreadsheet);
            $fileName = 'EntradaTransferencia_' . $sucursalOrigen->Nombre . '_' . $transferencia->Numero . '.xlsx';

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
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            );

        } catch (\Exception $e) {
            Log::error('❌ Error al descargar plantilla: ' . $e->getMessage());
            return back()->with('error', 'Error al descargar la plantilla: ' . $e->getMessage());
        }
    }

    public function subirPlantillaTransferencia(Request $request, $id)
    {
        try {
            $request->validate([
                'archivo_excel' => 'required|file|mimes:xlsx,xls|max:5120'
            ]);

            $file = $request->file('archivo_excel');
            $transferenciaId = $id;

            // 1. Obtener la transferencia
            $transferencia = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->first();

            if (!$transferencia) {
                return redirect()->back()->with('error', 'Transferencia no encontrada');
            }

            // 2. Obtener la sucursal destino
            $sucursalDestino = DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTMP as ts')
                ->join('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                ->where('ts.TransferenciaId', $transferenciaId)
                ->select('s.ID', 's.Nombre')
                ->first();

            if (!$sucursalDestino) {
                return redirect()->back()->with('error', 'Sucursal destino no encontrada');
            }

            // 3. Leer el archivo Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // 4. Extraer productos y cantidades del Excel
            $startRow = 9; // Fila 10 (índice 9)
            $productosCodigos = [];
            $productosNoEncontrados = [];

            // Mapeo de columnas (A=0, B=1, C=2, D=3, E=4)
            // A: Codigo, B: Referencia, C: Descripcion, D: Existencia, E: Cantidad
            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Verificar que la fila tenga datos
                if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                    continue;
                }

                $codigo = trim($row[0] ?? '');
                $cantidad = trim($row[4] ?? '');

                // Solo tomar productos con cantidad > 0
                if (empty($codigo) || empty($cantidad) || !is_numeric($cantidad) || floatval($cantidad) <= 0) {
                    continue;
                }

                $productosCodigos[] = [
                    'codigo' => $codigo,
                    'cantidad' => floatval($cantidad)
                ];
            }

            if (empty($productosCodigos)) {
                return redirect()->back()->with('error', 'No se encontraron productos con cantidades válidas en el archivo');
            }

            // 5. Buscar productos por código y preparar detalles
            $detalles = [];
            $detallesConCosto = [];

            foreach ($productosCodigos as $item) {
                // Buscar el producto por código en la sucursal origen
                $producto = DB::connection('sqlsrv')
                    ->table('ProductosSucursalView')
                    ->where('Codigo', $item['codigo'])
                    ->where('SucursalId', $transferencia->SucursalOrigenId)
                    ->where('Estatus', 1)
                    ->select('ID', 'Codigo', 'CostoDivisa', 'Existencia')
                    ->first();

                if ($producto) {
                    $detalles[] = [
                        'producto_id' => $producto->ID,
                        'sucursal_id' => $sucursalDestino->ID,
                        'cantidad' => $item['cantidad']
                    ];
                    $detallesConCosto[] = [
                        'producto_id' => $producto->ID,
                        'cantidad' => $item['cantidad'],
                        'costo_divisa' => $producto->CostoDivisa ?? 0
                    ];
                } else {
                    $productosNoEncontrados[] = $item['codigo'];
                }
            }

            if (empty($detalles)) {
                $mensaje = 'No se encontraron productos válidos en el archivo.';
                if (!empty($productosNoEncontrados)) {
                    $mensaje .= ' Productos no encontrados: ' . implode(', ', array_slice($productosNoEncontrados, 0, 10));
                    if (count($productosNoEncontrados) > 10) {
                        $mensaje .= '... y ' . (count($productosNoEncontrados) - 10) . ' más';
                    }
                }
                return redirect()->back()->with('error', $mensaje);
            }

            // 6. Guardar detalles
            Log::info('📦 Guardando detalles desde plantilla Excel', [
                'transferencia_id' => $transferenciaId,
                'total_detalles' => count($detalles)
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            $totalEmitidos = 0;
            $totalItems = 0;

            foreach ($detalles as $detalle) {
                $productoId = $detalle['producto_id'];
                $sucursalId = $detalle['sucursal_id'];
                $cantidad = $detalle['cantidad'];

                // Verificar si el detalle ya existe
                $detalleExistente = DB::connection('sqlsrv')
                    ->table('TransferenciaDetallesTMP')
                    ->where('TransferenciaId', $transferenciaId)
                    ->where('ProductoId', $productoId)
                    ->where('SucursalId', $sucursalId)
                    ->first();

                if ($detalleExistente) {
                    DB::connection('sqlsrv')
                        ->table('TransferenciaDetallesTMP')
                        ->where('TransferenciaDetalleId', $detalleExistente->TransferenciaDetalleId)
                        ->update([
                            'CantidadEmitida' => $cantidad,
                            'CantidadDisponible' => $cantidad
                        ]);
                } else {
                    DB::connection('sqlsrv')
                        ->table('TransferenciaDetallesTMP')
                        ->insert([
                            'TransferenciaId' => $transferenciaId,
                            'ProductoId' => $productoId,
                            'SucursalId' => $sucursalId,
                            'CantidadEmitida' => $cantidad,
                            'CantidadDisponible' => $cantidad,
                            'CantidadRecibida' => 0
                        ]);
                }

                $totalEmitidos += $cantidad;
                $totalItems++;
            }

            // Actualizar estatus de la transferencia
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->update([
                    'Estatus' => 2 // EnEdicion
                ]);

            DB::connection('sqlsrv')->commit();

            // ✅ 7. Calcular totales después de guardar
            $totales = DB::connection('sqlsrv')
                ->table('TransferenciaTMPTotalizadaView')
                ->where('TransferenciaId', $transferenciaId)
                ->select([
                    'CantidadEmitida',
                    'CantidadRecibida',
                    'CantidadDisponible',
                    'CantidadItems'
                ])
                ->first();

            // ✅ Calcular CostoDivisaTotal
            $costoDivisaTotal = 0;
            foreach ($detallesConCosto as $detalle) {
                $costoDivisaTotal += $detalle['cantidad'] * $detalle['costo_divisa'];
            }

            Log::info('✅ Detalles guardados desde plantilla Excel', [
                'transferencia_id' => $transferenciaId,
                'total_emitidos' => $totalEmitidos,
                'total_items' => $totalItems,
                'costo_divisa_total' => $costoDivisaTotal
            ]);

            // ✅ 8. Guardar totales en sesión para mostrarlos al recargar
            session()->put('totales_actualizados', [
                'CantidadItems' => (int) ($totales->CantidadItems ?? 0),
                'CantidadEmitida' => (int) ($totales->CantidadEmitida ?? 0),
                'CostoDivisaTotal' => $costoDivisaTotal
            ]);

            // 9. Construir mensaje de éxito
            $mensaje = "✅ Plantilla procesada correctamente. ";
            $mensaje .= "Se procesaron {$totalItems} productos. ";
            $mensaje .= "Total de unidades: {$totalEmitidos}. ";
            $mensaje .= "Total Costo Divisa: $" . number_format($costoDivisaTotal, 2);

            if (!empty($productosNoEncontrados)) {
                $mostrar = array_slice($productosNoEncontrados, 0, 10);
                $mensaje .= " ⚠️ Productos no encontrados: " . implode(', ', $mostrar);
                if (count($productosNoEncontrados) > 10) {
                    $mensaje .= '... y ' . (count($productosNoEncontrados) - 10) . ' más';
                }
            }

            return redirect()->back()->with([
                'success' => $mensaje,
                'totales_actualizados' => session('totales_actualizados')
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('❌ Error al subir plantilla: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Guarda una nueva transferencia
     */
    public function transferencia_guardar(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'sucursal_origen' => 'required|integer',
                'sucursales_destino' => 'required|array|min:1',
                'sucursales_destino.*' => 'integer|different:sucursal_origen',
                'fecha' => 'required|date',
                'productos' => 'required|array|min:1',
                'productos.*.id' => 'required|integer',
                'productos.*.cantidad' => 'required|numeric|min:0.01',
                'productos.*.disponible' => 'required|numeric|min:0'
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // 1. Crear la transferencia (cabecera)
            $transferenciaId = DB::connection('sqlsrv')
                ->table('Transferencias')
                ->insertGetId([
                    'Numero' => $this->generarNumeroTransferencia(),
                    'Fecha' => $request->fecha,
                    'SucursalOrigenId' => $request->sucursal_origen,
                    'Estatus' => 2, // EnEdición
                    'Tipo' => 1, // Transferencia
                    'CantidadEmitida' => 0,
                    'CantidadDisponible' => 0,
                    'CantidadRecibida' => 0,
                    'CantidadItems' => count($request->productos),
                    'UsuarioCreaId' => auth()->id(),
                    'FechaCreacion' => now()
                ]);

            // 2. Guardar sucursales destino
            foreach ($request->sucursales_destino as $sucursalDestinoId) {
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursales')
                    ->insert([
                        'TransferenciaId' => $transferenciaId,
                        'SucursalId' => $sucursalDestinoId
                    ]);
            }

            // 3. Guardar detalles de productos
            $totalEmitidos = 0;
            $totalDisponibles = 0;

            foreach ($request->productos as $producto) {
                $cantidad = $producto['cantidad'];
                $disponible = $producto['disponible'] ?? 0;
                
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursalesDetalles')
                    ->insert([
                        'TransferenciaId' => $transferenciaId,
                        'ProductoId' => $producto['id'],
                        'Cantidad' => $cantidad,
                        'Disponible' => $disponible,
                        'Recibido' => 0
                    ]);

                $totalEmitidos += $cantidad;
                $totalDisponibles += $disponible;
            }

            // 4. Actualizar totales en la cabecera
            DB::connection('sqlsrv')
                ->table('Transferencias')
                ->where('ID', $transferenciaId)
                ->update([
                    'CantidadEmitida' => $totalEmitidos,
                    'CantidadDisponible' => $totalDisponibles,
                    'CantidadItems' => count($request->productos)
                ]);

            DB::connection('sqlsrv')->commit();

            Log::info('Transferencia creada', [
                'transferencia_id' => $transferenciaId,
                'usuario' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transferencia creada correctamente',
                'transferencia_id' => $transferenciaId
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('Error al crear transferencia: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transferencia_finalizar(Request $request, $id)
    {
        try {
            $transferenciaId = $id;

            Log::info('🏁 Iniciando finalización de transferencia', [
                'transferencia_id' => $transferenciaId,
                'usuario' => auth()->id()
            ]);

            // 1. Obtener la transferencia de la sesión (si existe)
            // En Laravel no usamos sesión para esto, usamos la base de datos
            $transferencia = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->first();

            // 2. Validar que la transferencia existe (equivalente a _transferenciaDTO == null)
            if (!$transferencia) {
                Log::warning('⚠️ Transferencia no encontrada', [
                    'transferencia_id' => $transferenciaId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No se encuentra disponible la transferencia que desea finalizar, por favor intente de nuevo'
                ], 404);
            }

            // 3. Validar que tenga ID (equivalente a _transferenciaDTO.TransferenciaId > 0)
            if ($transferencia->TransferenciaId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de transferencia inválido'
                ], 400);
            }

            // 4. Validar que esté en estado "EnEdicion" (2)
            if ($transferencia->Estatus != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'La transferencia no está en estado de edición'
                ], 400);
            }

            // 5. Validar que tenga productos
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->count();

            if ($detalles == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'La transferencia no tiene productos asignados'
                ], 400);
            }

            // 6. Llamar al servicio que finaliza (equivalente a _transferenciaService.Finalizar())
            $resultado = $this->finalizarTransferenciaServicio($transferenciaId);

            if (!$resultado['success']) {
                Log::error('❌ Error al finalizar transferencia en servicio', [
                    'transferencia_id' => $transferenciaId,
                    'error' => $resultado['message']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo finalizar la transferencia, intente de nuevo: ' . $resultado['message']
                ], 500);
            }

            // 7. Obtener sucursales destino para cerrar recepciones
            // Equivalente a: foreach (item in _transferenciaDTO.ListaSucursalDestino)
            $sucursalesDestino = DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->pluck('SucursalId')
                ->toArray();

            // 8. Cerrar recepciones para cada sucursal destino
            // Equivalente a: await _recepcionService.CerrarRecepciones()
            foreach ($sucursalesDestino as $sucursalDestinoId) {
                try {
                    $this->cerrarRecepciones($transferencia->SucursalOrigenId, $transferencia->Fecha);
                    Log::info('📦 Recepciones cerradas', [
                        'sucursal_origen' => $transferencia->SucursalOrigenId,
                        'sucursal_destino' => $sucursalDestinoId
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ Error al cerrar recepciones (no crítico): ' . $e->getMessage());
                    // En .NET, esto no detiene el flujo principal
                }
            }

            // // 9. Limpiar sesión (equivalente a HttpContext.Session.SetObject(C.TRANSFERENCIA, null))
            // session()->forget('transferencia');
            // session()->forget('productos_plantilla');
            // session()->forget('transferencia_id_plantilla');

            Log::info('✅ Transferencia finalizada correctamente', [
                'transferencia_id' => $transferenciaId,
                'numero' => $transferencia->Numero
            ]);

            // 10. Redirigir al listado (equivalente a RedirectToAction("DistribucionesSucursales"))
            return response()->json([
                'success' => true,
                'message' => 'La transferencia se finalizó correctamente',
                'redirect' => route('cpanel.distribucion.transferencia')
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al finalizar transferencia: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    private function finalizarTransferenciaServicio($transferenciaId)
    {
        try {
            DB::connection('sqlsrv')->beginTransaction();

            // 1. Obtener datos
            $transferenciaTMP = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->first();

            if (!$transferenciaTMP) {
                throw new \Exception('Transferencia temporal no encontrada');
            }

            $detallesTMP = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->get();

            $sucursalesDestino = DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->get();

            // 2. Crear transferencias definitivas
            foreach ($sucursalesDestino as $sucDestino) {
                // Calcular saldo
                $saldo = 0;
                foreach ($detallesTMP as $detalle) {
                    if ($detalle->SucursalId == $sucDestino->SucursalId) {
                        $producto = DB::connection('sqlsrv')
                            ->table('ProductosSucursalView')
                            ->where('ID', $detalle->ProductoId)
                            ->select('CostoDivisa')
                            ->first();
                        $costoDivisa = $producto->CostoDivisa ?? 0;
                        $saldo += $detalle->CantidadEmitida * $costoDivisa;
                    }
                }

                $numeroTransferencia = $transferenciaTMP->Numero . '-' . $sucDestino->SucursalId;

                // Insertar en Transferencias
                $nuevaTransferenciaId = DB::connection('sqlsrv')
                    ->table('Transferencias')
                    ->insertGetId([
                        'Numero' => $numeroTransferencia,
                        'Fecha' => $transferenciaTMP->Fecha,
                        'SucursalOrigenId' => $transferenciaTMP->SucursalOrigenId,
                        'SucursalDestinoId' => $sucDestino->SucursalId,
                        'Estatus' => 3,
                        'Tipo' => 0,
                        'Observacion' => $transferenciaTMP->Observacion,
                        'Saldo' => $saldo
                        // ❌ ELIMINAR: UsuarioCreaId y FechaCreacion (no existen en la tabla)
                    ]);

                // Insertar detalles
                foreach ($detallesTMP as $detalle) {
                    if ($detalle->CantidadEmitida > 0 && $detalle->SucursalId == $sucDestino->SucursalId) {
                        DB::connection('sqlsrv')
                            ->table('TransferenciaDetalles')
                            ->insert([
                                'TransferenciaId' => $nuevaTransferenciaId,
                                'ProductoId' => $detalle->ProductoId,
                                'CantidadEmitida' => $detalle->CantidadEmitida,
                                'CantidadRecibida' => 0
                            ]);
                    }
                }

                // Insertar relación
                DB::connection('sqlsrv')
                    ->table('TransferenciasSucursales')
                    ->insert([
                        'TransferenciaId' => $nuevaTransferenciaId,
                        'SucursalId' => $sucDestino->SucursalId,
                        'Estatus' => 1
                    ]);
            }

            // 3. Actualizar inventario
            foreach ($detallesTMP as $detalle) {
                if ($detalle->CantidadEmitida > 0) {
                    DB::connection('sqlsrv')
                        ->table('ProductoSucursal')
                        ->where('ProductoId', $detalle->ProductoId)
                        ->where('SucursalId', $transferenciaTMP->SucursalOrigenId)
                        ->decrement('Existencia', $detalle->CantidadEmitida);
                }
            }

            // 4. Actualizar estatus de temporales
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->update(['Estatus' => 3]);

            DB::connection('sqlsrv')
                ->table('TransferenciasSucursalesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->update(['Estatus' => 3]);

            DB::connection('sqlsrv')->commit();

            return [
                'success' => true,
                'message' => 'Transferencia finalizada correctamente'
            ];

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function transferencia_eliminar($id)
    {
        try {
            $transferenciaId = $id;

            Log::info('🔒 Cerrando transferencia', [
                'transferencia_id' => $transferenciaId
            ]);

            // 1. Verificar que la transferencia existe
            $transferencia = DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->first();

            if (!$transferencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transferencia no encontrada'
                ], 404);
            }

            // ✅ 2. Validar que esté en estado "Nueva" (1) o "EnEdicion" (2)
            if (!in_array($transferencia->Estatus, [1, 2])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cerrar transferencias en estado Nueva (1) o EnEdición (2)'
                ], 400);
            }

            // 3. Obtener detalles de la transferencia
            $detalles = DB::connection('sqlsrv')
                ->table('TransferenciaDetallesTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->get();

            DB::connection('sqlsrv')->beginTransaction();

            // 4. Si hay productos, devolverlos al inventario (solo para estado EnEdicion)
            if ($transferencia->Estatus == 2 && $detalles->isNotEmpty()) {
                foreach ($detalles as $detalle) {
                    if ($detalle->CantidadDisponible > 0) {
                        DB::connection('sqlsrv')
                            ->table('ProductoSucursal')
                            ->where('ProductoId', $detalle->ProductoId)
                            ->where('SucursalId', $transferencia->SucursalOrigenId)
                            ->increment('Existencia', $detalle->CantidadDisponible);

                        Log::info('📦 Producto devuelto a inventario', [
                            'producto_id' => $detalle->ProductoId,
                            'sucursal_id' => $transferencia->SucursalOrigenId,
                            'cantidad' => $detalle->CantidadDisponible
                        ]);
                    }
                }
            } else {
                Log::info('📋 Transferencia sin productos, solo cambiando estatus', [
                    'transferencia_id' => $transferenciaId,
                    'estatus' => $transferencia->Estatus
                ]);
            }

            // 5. Cambiar estatus a Procesada (6)
            DB::connection('sqlsrv')
                ->table('TransferenciasTMP')
                ->where('TransferenciaId', $transferenciaId)
                ->update(['Estatus' => 6]);

            // 6. Si existe en Transferencias, actualizar también
            $transferenciaDefinitiva = DB::connection('sqlsrv')
                ->table('Transferencias')
                ->where('TransferenciaId', $transferenciaId)
                ->first();

            if ($transferenciaDefinitiva) {
                DB::connection('sqlsrv')
                    ->table('Transferencias')
                    ->where('TransferenciaId', $transferenciaId)
                    ->update(['Estatus' => 6]);
            }

            DB::connection('sqlsrv')->commit();

            Log::info('✅ Transferencia cerrada correctamente', [
                'transferencia_id' => $transferenciaId,
                'numero' => $transferencia->Numero,
                'estatus_anterior' => $transferencia->Estatus,
                'productos_devueltos' => $detalles->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transferencia cerrada correctamente',
                'redirect' => route('cpanel.distribucion.transferencia')
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('❌ Error al cerrar transferencia: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la transferencia: ' . $e->getMessage()
            ], 500);
        }
    }
}