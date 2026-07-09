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

            // 1. VALIDACIÓN SIMPLE (SIN mimes)
            $request->validate([
                'excel_file' => 'required|file|max:10240'
            ]);

            $file = $request->file('excel_file');

            \Log::info('=== INICIO IMPORT A2 SIN EXCEL ===');
            \Log::info('Archivo: ' . $file->getClientOriginalName());

            // 2. LEER ARCHIVO COMO TEXTO BINARIO
            $content = file_get_contents($file->getRealPath());

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer el archivo'
                ], 500);
            }

            // 3. LIMPIAR CARACTERES NO VÁLIDOS
            $content = preg_replace('/[^\x20-\x7E\t\r\n]/', '', $content);

            // 4. CONVERTIR A LÍNEAS
            $lines = preg_split('/\r\n|\n|\r/', $content);

            $data = [];
            $errores = [];

            foreach ($lines as $line) {

                // separar por tabs o múltiples espacios
                $cols = preg_split('/\t+|\s{2,}/', trim($line));

                if (count($cols) < 2) {
                    continue;
                }

                $codigo = trim($cols[0] ?? '');
                $cantidad = trim($cols[1] ?? '');

                if ($codigo === '' || !is_numeric($cantidad)) {
                    continue;
                }

                $data[] = [
                    'codigo' => $codigo,
                    'cantidad' => (float)$cantidad
                ];
            }

            \Log::info('Registros detectados: ' . count($data));

            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado correctamente sin Excel',
                'total' => count($data),
                'preview' => array_slice($data, 0, 10)
            ]);

        } catch (\Throwable $e) {

            \Log::error('ERROR IMPORT A2: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error procesando archivo A2',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}