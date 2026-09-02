@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Imprimir precios')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#10b981,#059669)';
    $hdrIcon = 'clipboard2-check';
    $hdrTitle = 'Imprimir precios';
    $hdrSubtitle = 'Exportar precios para imprimir';
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:{{ $hdrBg }};">
                        <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="#">Inventario</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Imprimir precios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD: INFORMACIÓN DE SUCURSAL --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-building me-2"></i>Información de Sucursal
                </h6>
            </div>
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Sucursal Activa</p>
                        <p class="fw-bold text-dark">{{ $sucursalNombre ?? 'No seleccionada' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Total Productos con Cambio de Precio</p>
                        <p class="fw-bold text-dark">{{ $totalProductos }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Fecha</p>
                        <p class="fw-bold text-dark">{{ Carbon::now()->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PRODUCTOS --}}
        {{-- ================================================ --}}
        @if($productos && $productos->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Productos con Cambio de Precio
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#10b981;">
                            {{ $productos->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="generarEtiquetasPDF()">
                            <i class="bi bi-tags me-1"></i> Etiquetas
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelPrecios()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFPrecios()">
                            <i class="bi bi-printer me-1"></i> PDF
                        </button>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaPrecios">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">
                                    IMAGEN
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    CÓDIGO
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:200px;">
                                    DESCRIPCIÓN
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    PRECIO ACTUAL
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    NUEVO PRECIO
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    DIFERENCIA
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                                @php
                                    $pvpActual = (float) ($producto->Pvp ?? 0);
                                    $nuevoPvp = (float) ($producto->NuevoPvp ?? 0);
                                    $diferencia = $nuevoPvp - $pvpActual;
                                    $esAumento = $diferencia > 0;
                                    
                                    // ✅ $producto->UrlFoto ya viene procesada del controlador
                                    $imgSrc = $producto->UrlFoto ?? 'assets/img/adminlte/img/produc_default.jfif';
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 text-center">
                                        <img src="{{ asset($imgSrc) }}"
                                            alt="{{ $producto->Descripcion ?? 'Producto' }}"
                                            loading="lazy"
                                            class="rounded img-zoomable"
                                            style="width:50px;height:50px;object-fit:cover;border:1px solid #e2e8f0;cursor:pointer;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ asset($imgSrc) }}"
                                            data-description="{{ $producto->Descripcion ?? 'Producto' }} - {{ $producto->Codigo ?? '' }}"
                                            onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#10b981;font-size:0.8rem;font-weight:bold;">
                                            {{ $producto->Codigo ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $producto->Descripcion ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            $ {{ number_format($pvpActual, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold text-success">
                                            $ {{ number_format($nuevoPvp, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $esAumento ? 'success' : 'danger' }}" style="font-size:0.75rem;">
                                            {{ $esAumento ? '+' : '' }}{{ number_format($diferencia, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-tags me-1"></i>
                        {{ $productos->count() }} producto{{ $productos->count() != 1 ? 's' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
        @else
        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    <i class="bi bi-tags text-muted" style="font-size:2rem;opacity:.5;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No hay productos con cambio de precio</h5>
                <p class="text-muted mb-0" style="font-size:0.9rem;">
                    No se encontraron productos con cambio de precio en esta sucursal.
                </p>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ================================================
    // ZOOM DE IMAGEN
    // ================================================
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'rounded-3 shadow-lg'
            }
        });
    }

    // ================================================
    // GENERAR ETIQUETAS PDF (jsPDF)
    // ================================================
    function generarEtiquetasPDF() {

        const productos = {!! $productosJson !!};

        // ------------------------------------------------------------
        // VALIDAR PRODUCTOS
        // ------------------------------------------------------------
        if (!productos || !Array.isArray(productos) || productos.length === 0) {

            Swal.fire({
                title: 'Sin productos',
                text: 'No hay productos con cambio de precio para imprimir',
                icon: 'info',
                confirmButtonColor: '#10b981'
            });

            return;
        }

        // ------------------------------------------------------------
        // MOSTRAR PROCESANDO
        // ------------------------------------------------------------
        Swal.fire({
            title: 'Generando etiquetas...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {

            // --------------------------------------------------------
            // VERIFICAR jsPDF
            // --------------------------------------------------------
            if (!window.jspdf || !window.jspdf.jsPDF) {
                throw new Error('La librería jsPDF no está disponible.');
            }

            const { jsPDF } = window.jspdf;

            // --------------------------------------------------------
            // DIMENSIONES DE LA ETIQUETA
            //
            // 2.25" x 1.25"
            //
            // 1 pulgada = 25.4 mm
            //
            // 2.25 x 25.4 = 57.15 mm
            // 1.25 x 25.4 = 31.75 mm
            // --------------------------------------------------------
            const labelWidth = 57.15;
            const labelHeight = 31.75;

            // --------------------------------------------------------
            // CREAR PDF
            // --------------------------------------------------------
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: [labelWidth, labelHeight],
                compress: true
            });

            // --------------------------------------------------------
            // MÁRGENES
            // --------------------------------------------------------
            const marginLeft = 3;
            const marginRight = 3;

            const centerX = labelWidth / 2;

            // --------------------------------------------------------
            // FUNCIÓN PARA FORMATEAR PRECIO
            // Ejemplo:
            // 7.08  -> 7,08
            // 10    -> 10,00
            // --------------------------------------------------------
            function formatearPrecio(valor) {

                let numero = parseFloat(valor);

                if (isNaN(numero)) {
                    numero = 0;
                }

                return numero
                    .toFixed(2)
                    .replace('.', ',');
            }

            // --------------------------------------------------------
            // FUNCIÓN PARA DIVIDIR DESCRIPCIÓN
            // EN MÁXIMO 2 LÍNEAS
            // --------------------------------------------------------
            function prepararDescripcion(texto, maxCaracteres) {

                texto = String(texto || '').trim();

                if (!texto) {
                    return ['', ''];
                }

                // Si cabe completa, utilizar una sola línea
                if (texto.length <= maxCaracteres) {
                    return [texto, ''];
                }

                // Buscar un espacio cercano al límite
                let puntoCorte = texto.lastIndexOf(' ', maxCaracteres);

                if (puntoCorte <= 0) {
                    puntoCorte = maxCaracteres;
                }

                const linea1 = texto.substring(0, puntoCorte).trim();

                const resto = texto.substring(puntoCorte).trim();

                // Segunda línea
                let linea2 = resto;

                // Limitar segunda línea
                if (linea2.length > maxCaracteres) {
                    linea2 = linea2.substring(0, maxCaracteres).trim();

                    // Evitar terminar en una palabra cortada
                    const ultimoEspacio = linea2.lastIndexOf(' ');

                    if (ultimoEspacio > 0) {
                        linea2 = linea2.substring(0, ultimoEspacio);
                    }

                    linea2 += '...';
                }

                return [linea1, linea2];
            }

            // --------------------------------------------------------
            // GENERAR CADA ETIQUETA
            // --------------------------------------------------------
            productos.forEach((producto, index) => {

                // ----------------------------------------------------
                // NUEVA PÁGINA PARA CADA PRODUCTO
                // ----------------------------------------------------
                if (index > 0) {
                    doc.addPage(
                        [labelWidth, labelHeight],
                        'landscape'
                    );
                }

                // ====================================================
                // DATOS DEL PRODUCTO
                // ====================================================

                const codigo = String(
                    producto.Codigo || 'N/A'
                ).trim();

                const descripcion = String(
                    producto.Descripcion || ''
                ).trim();

                const precio = formatearPrecio(
                    producto.NuevoPvp
                );

                // ====================================================
                // CÓDIGO
                // ====================================================

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(15);

                doc.text(
                    codigo,
                    centerX,
                    5.5,
                    {
                        align: 'center',
                        baseline: 'middle'
                    }
                );

                // ====================================================
                // DESCRIPCIÓN
                // ====================================================

                const lineasDescripcion =
                    prepararDescripcion(descripcion, 31);

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(6.5);

                // Primera línea
                if (lineasDescripcion[0]) {

                    doc.text(
                        lineasDescripcion[0],
                        centerX,
                        10,
                        {
                            align: 'center',
                            baseline: 'middle'
                        }
                    );
                }

                // Segunda línea
                if (lineasDescripcion[1]) {

                    doc.text(
                        lineasDescripcion[1],
                        centerX,
                        13,
                        {
                            align: 'center',
                            baseline: 'middle'
                        }
                    );
                }

                // ====================================================
                // PRECIO
                // ====================================================

                doc.setFont('helvetica', 'bold');

                // Precio bastante grande para que sea visible
                doc.setFontSize(26);

                doc.text(
                    precio,
                    centerX,
                    23,
                    {
                        align: 'center',
                        baseline: 'middle'
                    }
                );

                // ====================================================
                // REF.
                // ====================================================

                doc.setFont('helvetica', 'italic');
                doc.setFontSize(6);

                doc.text(
                    'Ref.',
                    centerX,
                    29,
                    {
                        align: 'center',
                        baseline: 'middle'
                    }
                );

            });

            // --------------------------------------------------------
            // CERRAR ALERTA
            // --------------------------------------------------------
            Swal.close();

            // --------------------------------------------------------
            // NOMBRE DEL ARCHIVO
            // --------------------------------------------------------
            const fecha = new Date()
                .toISOString()
                .split('T')[0];

            doc.save(
                'Etiquetas_' + fecha + '.pdf'
            );

        } catch (error) {

            console.error(
                'Error al generar etiquetas:',
                error
            );

            Swal.close();

            Swal.fire({
                title: 'Error',
                text: 'No fue posible generar las etiquetas. Revise la consola para más información.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    }

    // ================================================
    // EXPORTAR EXCEL
    // ================================================
    function exportarExcelPrecios() {
        const tabla = document.getElementById('tablaPrecios');
        if (!tabla) {
            Swal.fire('Error', 'No se encontró la tabla para exportar', 'error');
            return;
        }

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todos los productos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Cambio de Precios" });
                XLSX.utils.book_append_sheet(wb, ws, 'Cambio de Precios');
                
                const fecha = new Date().toISOString().split('T')[0];
                XLSX.writeFile(wb, `CambioPrecio_${fecha}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // EXPORTAR PDF (tabla)
    // ================================================
    function exportarPDFPrecios() {
        const tabla = document.getElementById('tablaPrecios');
        if (!tabla) {
            Swal.fire('Error', 'No se encontró la tabla para exportar', 'error');
            return;
        }

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todos los productos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');

                doc.setFontSize(16);
                doc.setTextColor(16, 185, 129);
                doc.text('Cambio de Precios', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaPrecios',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [16, 185, 129] }
                });

                const fecha = new Date().toISOString().split('T')[0];
                doc.save(`CambioPrecio_${fecha}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // TOOLTIPS
    // ================================================
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Verificar sucursal
        const sucursalId = {{ session('sucursal_id', 0) }};
        const totalProductos = {{ $totalProductos ?? 0 }};

        if (sucursalId === 0) {
            Swal.fire({
                title: 'Advertencia',
                text: 'No hay una sucursal seleccionada. Seleccione una sucursal para continuar.',
                icon: 'warning',
                confirmButtonColor: '#10b981'
            });
        }

        if (totalProductos === 0) {
            Swal.fire({
                title: 'Información',
                text: 'No hay productos con cambio de precio en esta sucursal.',
                icon: 'info',
                confirmButtonColor: '#10b981'
            });
        }
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .img-zoomable:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endpush