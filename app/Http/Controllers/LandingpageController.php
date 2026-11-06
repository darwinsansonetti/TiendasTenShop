<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\GeneralHelper;
use App\Helpers\ParametrosFiltroFecha;
use App\DTO\VentasPeriodoDTO;
use App\Models\ProductosSucursalView;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactoMail; 
use App\Models\Publicidad;
use App\Models\Banner;

class LandingpageController extends Controller
{
    // Llamada al Index del LandinPage
    public function index()
    {
        $mensaje = GeneralHelper::ultimoMensaje();

        // 1. Fechas (Últimos 30 días)
        $fechaInicio = Carbon::now()->subDays(30)->startOfDay();
        $fechaFin = Carbon::now()->endOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false, $fechaInicio, $fechaFin
        );

        // 2. Obtener ventas y Top 12
        $ventas = GeneralHelper::generarDatosVentas($filtroFecha);
        $dtoVenta = new VentasPeriodoDTO($ventas);

        $topTen = $dtoVenta->getListaItemsTop12Periodo() ?? [];
        
        // Filtrar SALDO y reindexar
        $topTen = array_filter($topTen, fn($item) => ($item['Codigo'] ?? '') !== 'SALDO');
        $topTen = array_values($topTen);

        // 3. Obtener los precios más bajos usando la vista (aplicando la regla NuevoPvp + Margen de 15%)
                // 3. Obtener los precios más bajos usando la vista (aplicando la regla NuevoPvp + Margen del 90%)
        $productosIds = array_column($topTen, 'Id');

        // 🔥 Calculamos la fecha de hace 4 meses
        $fechaLimite = Carbon::now()->subMonths(4);

        // Traemos los registros aplicando el filtro de antigüedad
        $preciosSucursales = ProductosSucursalView::whereIn('ID', $productosIds)
            ->where('Estatus', 1) // Solo disponibles
            ->where('FechaCreacion', '>=', $fechaLimite) // <-- FILTRO DE ANTIGÜEDAD (últimos 4 meses)
            ->select('ID', 'PvpDivisa', 'NuevoPvp', 'CostoDivisa') 
            ->get();

        // Agrupamos por ID de producto para calcular el precio real más bajo
        $preciosBajos = [];
        foreach ($preciosSucursales as $precio) {
            // 🔥 Aplicamos la regla: Si NuevoPvp > 0, usamos ese. Si no, usamos PvpDivisa.
            $precioReal = ($precio->NuevoPvp > 0) ? (float) $precio->NuevoPvp : (float) $precio->PvpDivisa;
            $costo = (float) $precio->CostoDivisa;

            // 🔥 FILTRO: Exigir un margen mínimo del 90% sobre el costo.
            $precioMinimoAceptable = $costo * 2; 

            // Solo guardamos el precio si es mayor a 0 y si tiene el margen saludable (evita remates)
            if ($precioReal > 0 && $precioReal >= $precioMinimoAceptable) {
                if (!isset($preciosBajos[$precio->ID]) || $precioReal < $preciosBajos[$precio->ID]) {
                    $preciosBajos[$precio->ID] = $precioReal;
                }
            }
        }

        // 4. Inyectar el precio al array del Top 12 y FILTRAR los que no pasaron la prueba
        $topTenFiltrado = [];
        foreach ($topTen as $item) {
            // Si el producto tiene un precio válido (superó el margen del 15%), lo mantenemos
            if (isset($preciosBajos[$item['Id']])) {
                $item['PrecioMostrar'] = $preciosBajos[$item['Id']];
                $topTenFiltrado[] = $item;
            }
            // Si no está en $preciosBajos, significa que no superó el margen y lo ignoramos
        }
        
        // Reemplazamos el topTen original por el filtrado y tomamos los primeros 12
        $topTen = array_slice($topTenFiltrado, 0, 12);

        // ==========================================
        // 5. PRODUCTOS MÁS RECIENTES
        // ==========================================
        $sucursalesActivas = [3, 4, 5, 7]; // Tus 4 tiendas

        // Traemos todos los registros válidos de esas sucursales
        $registrosRecientes = ProductosSucursalView::whereIn('SucursalId', $sucursalesActivas)
            ->where('Estatus', 1)
            ->where('Existencia', '>', 0) // Que tenga stock
            ->where('NuevoPvp', 0)
            ->where('PvpAnterior', 0)
            ->whereNull('FechaNuevoPrecio')
            ->orderByDesc('FechaActualizacion')
            ->orderByDesc('FechaCreacion')
            ->get();

        // Agrupamos por ID de producto
        $productosAgrupados = $registrosRecientes->groupBy('ID');

        $productosRecientes = $productosAgrupados->map(function ($sucursales, $productoId) {
            // 1. Validar que tenga existencia en al menos 3 sucursales
            if ($sucursales->count() < 3) {
                return null; 
            }

            // 2. Tomamos el primer registro para los datos generales (Descripción, Foto, etc.)
            $productoBase = $sucursales->first();

            // 3. Calcular el precio más bajo aplicando la regla
            $precioMasBajo = null;
            foreach ($sucursales as $sucursal) {
                $precioReal = ($sucursal->NuevoPvp > 0) ? (float) $sucursal->NuevoPvp : (float) $sucursal->PvpDivisa;
                
                if ($precioReal > 0) {
                    if ($precioMasBajo === null || $precioReal < $precioMasBajo) {
                        $precioMasBajo = $precioReal;
                    }
                }
            }

            // Asignamos el precio calculado al objeto
            $productoBase->PrecioMostrar = $precioMasBajo ?? 0;

            return $productoBase;
        })
        ->filter() // Elimina los nulos (los que no tenían 3 sucursales)
        ->take(12) // Tomamos los primeros 12 para el carrusel
        ->values(); // Reindexamos

        // 6. Nuevos productos destacados para la sección de abajo
        $productosDestacados = ProductosSucursalView::where('Estatus', 1)
            ->where('Existencia', '>', 0)
            ->whereIn('SucursalId', [3, 4, 5, 7])
            ->inRandomOrder() 
            ->limit(4)
            ->get()
            ->unique('ID');

        // 7. PUBLICIDAD ACTIVA
        $publicidadActiva = Publicidad::where('EsActivo', 1)
            ->where(function($q) {
                $q->whereNull('FechaIni')->orWhere('FechaIni', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('FechaFin')->orWhere('FechaFin', '>=', now());
            })
            ->first();

        $urlPublicidad = null;
        if ($publicidadActiva) {
            // Usamos la misma carpeta de productos
            $urlPublicidad = \App\Helpers\FileHelper::getOrDownloadFile(
                'images/items/thumbs/',
                $publicidadActiva->UrlFoto,
                null 
            );
        }
        
        // 8. BANNERS ACTIVOS (para el carrusel principal)
        $bannersActivos = Banner::where('EsActivo', 1)->orderBy('Id', 'desc')->get();

        // Generamos la URL de cada banner con el FileHelper
        $bannersActivos = $bannersActivos->map(function($banner) {
            $banner->UrlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                'images/items/thumbs/',
                $banner->UrlFoto,
                'assets/img/slides/slide_home_2.jpg' // Imagen por defecto
            );
            return $banner;
        });

        return view('landingpage.index', compact('mensaje', 'topTen', 'productosRecientes', 'productosDestacados', 'urlPublicidad', 'bannersActivos'));
    }

    public function showProducto($id)
    {
        $mensaje = GeneralHelper::ultimoMensaje();
        $producto = Producto::findOrFail($id);

        // Detectar categoría por palabras clave en la descripción
        $desc = strtolower($producto->Descripcion);

        // Buscamos coincidencias (usando las variables con y sin acento/ñ)
        if (str_contains($desc, 'dama') || str_contains($desc, 'mujer')) {
            $producto->CategoriaDetectada = 'Damas';
        } elseif (str_contains($desc, 'caballero') || str_contains($desc, 'hombre')) {
            $producto->CategoriaDetectada = 'Caballeros';
        } elseif (str_contains($desc, 'nino') || str_contains($desc, 'niño') || str_contains($desc, 'bebe') || str_contains($desc, 'bebé')) {
            $producto->CategoriaDetectada = 'Niños';
        } elseif (str_contains($desc, 'nina') || str_contains($desc, 'niña')) {
            $producto->CategoriaDetectada = 'Niñas';
        } else {
            $producto->CategoriaDetectada = 'Calzado General';
        }

        $sucursalesActivas = [3, 4, 5, 7]; 

        // 🔥 Hacemos un JOIN para traer el nombre de la sucursal
        $productosSucursal = DB::connection('sqlsrv')
            ->table('ProductosSucursalView as psv')
            ->join('Sucursales as s', 'psv.SucursalId', '=', 's.ID')
            ->where('psv.ID', $id)
            ->whereIn('psv.SucursalId', $sucursalesActivas)
            ->where('psv.Estatus', 1)
            ->select('psv.*', 's.Nombre as SucursalNombre')
            ->get()
            ->map(function ($item) {
                // Aplicamos la regla de negocio para el precio
                $item->PrecioFinal = ($item->NuevoPvp > 0) ? (float) $item->NuevoPvp : (float) $item->PvpDivisa;
                return $item;
            })
            ->filter(function($item) {
                return $item->PrecioFinal > 0; 
            });

        if ($productosSucursal->isEmpty()) {
            return redirect()->route('landingpage.index')->with('error', 'Producto no disponible actualmente.');
        }

        $precioMasBajo = $productosSucursal->min('PrecioFinal');
        $stockTotal = $productosSucursal->sum('Existencia');

        $relacionados = ProductosSucursalView::where('Estatus', 1)
            ->where('Existencia', '>', 0)
            ->where('ID', '!=', $id)
            ->inRandomOrder()
            ->limit(6)
            ->get()
            ->unique('ID');

        return view('landingpage.producto', compact(
            'mensaje',
            'producto', 
            'productosSucursal', 
            'precioMasBajo', 
            'stockTotal', 
            'relacionados'
        ));
    }

    public function listadoProductos(Request $request)
    {
        $mensaje = GeneralHelper::ultimoMensaje();
        $buscar = $request->input('buscar');
        $categoria = $request->input('categoria');
        $sucursalId = $request->input('sucursal_id'); // Capturamos la sucursal

        // 1. Consulta Base
        $query = DB::connection('sqlsrv')->table('ProductosSucursalView')
            ->where('Estatus', 1)
            ->where('Existencia', '>', 0);

        // 2. Filtro por Sucursal (Si viene del request)
        if ($sucursalId) {
            $query->where('SucursalId', $sucursalId);
        } else {
            // Si no hay sucursal, filtramos por las 4 tiendas activas por defecto
            $query->whereIn('SucursalId', [3, 4, 5, 7]);
        }

        // 3. Filtro de búsqueda (Aplica para ambas ramas)
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('Descripcion', 'LIKE', "%{$buscar}%")
                ->orWhere('Codigo', 'LIKE', "%{$buscar}%");
            });
        }

        // 4. Filtro por Categoría (Aplica para ambas ramas)
        if ($categoria) {
            if ($categoria == 'Damas') {
                $query->where(function ($q) {
                    $q->where('Descripcion', 'LIKE', '%DAMA%')->orWhere('Descripcion', 'LIKE', '%MUJER%');
                });
            } elseif ($categoria == 'Caballeros') {
                $query->where(function ($q) {
                    $q->where('Descripcion', 'LIKE', '%CABALLERO%')->orWhere('Descripcion', 'LIKE', '%HOMBRE%');
                });
            } elseif ($categoria == 'Niños') {
                $query->where(function ($q) {
                    $q->where('Descripcion', 'LIKE', '%NINO%')->orWhere('Descripcion', 'LIKE', '%NIÑO%')->orWhere('Descripcion', 'LIKE', '%BEBE%');
                });
            } elseif ($categoria == 'Niñas') {
                $query->where(function ($q) {
                    $q->where('Descripcion', 'LIKE', '%NINA%')->orWhere('Descripcion', 'LIKE', '%NIÑA%');
                });
            }
        }

        // 5. LA DECISIÓN: ¿Filtramos por sucursal o agrupamos?
        if ($sucursalId) {
            // SI HAY SUCURSAL: Consulta directa, sin GROUP BY (Mucho más rápida)
            $productos = $query->select(
                'ID', 
                'Codigo', 
                'Descripcion', 
                'UrlFoto', 
                'NuevoPvp', 
                'PvpDivisa', 
                'PvpAnterior',
                'Existencia' // Agregamos existencia porque sabemos que es de esa sucursal
            )
            ->orderBy('ID', 'desc')
            ->paginate(12)
            ->withQueryString();

        } else {
            // SI NO HAY SUCURSAL: Tu lógica original agrupada
            $productos = $query->select(
                'ID', 
                'Codigo', 
                'Descripcion', 
                'UrlFoto', 
                DB::raw('MIN(CASE WHEN NuevoPvp > 0 THEN NuevoPvp ELSE PvpDivisa END) as PrecioMostrar'),
                DB::raw('MAX(PvpAnterior) as PvpAnterior')
            )
            ->groupBy('ID', 'Codigo', 'Descripcion', 'UrlFoto')
            ->orderBy('ID', 'desc')
            ->paginate(12)
            ->withQueryString();
        }

        return view('landingpage.productos', compact('mensaje', 'productos', 'buscar', 'categoria', 'sucursalId'));
    }

    public function nosotros()
    {
        $mensaje = GeneralHelper::ultimoMensaje();
        
        // Aquí puedes traer las sucursales de tu base de datos si lo deseas
        $sucursales = DB::connection('sqlsrv')->table('Sucursales')
            ->where('EsActiva', 1)
            ->where('Tipo', 1)
            ->orderBy('ID', 'asc') // Las ordenamos por ID
            ->get();

        return view('landingpage.nosotros', compact('mensaje', 'sucursales'));
    }

    public function contactanos()
    {
        $mensaje = GeneralHelper::ultimoMensaje();
        return view('landingpage.contactanos', compact('mensaje'));
    }

    public function enviarContacto(Request $request)
    {
        // 1. Validar los datos
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'asunto' => 'required|string|max:150',
            'mensaje' => 'required|string',
        ]);

        try {
            // 2. Enviar el correo
            // Cambia 'ventas@tiendastenshop.com' por el correo real donde quieres recibir los mensajes
            Mail::to('Hussein@Tiendastenshop.com')->send(new ContactoMail($request->all()));

            // 3. Redirigir con mensaje de éxito
            return redirect()->route('landingpage.contactanos')
                ->with('success', '¡Gracias por escribirnos! Tu mensaje ha sido enviado correctamente. Te responderemos a la brevedad.');

        } catch (\Exception $e) {
            return redirect()->route('landingpage.contactanos')
                ->with('error', 'Hubo un problema al enviar tu mensaje. Por favor, intenta de nuevo o contáctanos por WhatsApp.');
        }
    }

    public function galeria()
    {
        $mensaje = GeneralHelper::ultimoMensaje();

        // 1. Buscamos solo las imágenes activas de la galería
        $galerias = \App\Models\Galeria::where('EsActivo', 1)
            ->orderBy('Id', 'desc')
            ->get();

        // 2. Generamos la URL real de cada imagen con el FileHelper
        $galerias = $galerias->map(function($foto) {
            $foto->UrlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                'images/items/thumbs/',
                $foto->UrlFoto,
                'assets/img/products/product_placeholder_square_medium.jpg'
            );
            return $foto;
        });

        return view('landingpage.galeria', compact('mensaje', 'galerias'));
    }
}
