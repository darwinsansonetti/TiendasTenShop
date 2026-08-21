@extends('layout.layout_dashboard')

@section('title', 'Detalle de diferencia - Proveedores')

@php
    use Carbon\Carbon;
    use App\Helpers\FileHelper;
    
    $hdrBg = 'linear-gradient(135deg,#f59e0b,#d97706)';
    $hdrIcon = 'arrow-left-right';
    $hdrTitle = 'Detalle de diferencia';
    $hdrSubtitle = 'Factura: ' . trim($factura->Numero ?? 'N/A');
    
    $estatusTexto = $factura->Estatus == 3 ? 'Pagada' : ($factura->Estatus == 4 ? 'Recibida' : 'Desconocido');
    $estatusColor = $factura->Estatus == 3 ? 'success' : ($factura->Estatus == 4 ? 'info' : 'secondary');
@endphp

@section('content')

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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.diferencia') }}">Diferencia</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Botón Volver --}}
        <div class="mb-3">
            <a href="{{ route('cpanel.proveedor.mercancia.diferencia') }}" 
               class="btn btn-light border fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>

        {{-- Información de la factura --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #0d6efd;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">PROVEEDOR</small>
                        <span class="fw-bold text-dark">{{ $factura->proveedor_nombre ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #6c757d;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">FACTURA</small>
                        <code class="fw-bold" style="color:#d97706;">{{ trim($factura->Numero ?? 'N/A') }}</code>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #0dcaf0;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">FECHA</small>
                        <span class="fw-bold text-dark">{{ $factura->FechaCreacion ? Carbon::parse($factura->FechaCreacion)->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid {{ $estatusColor == 'success' ? '#198754' : '#0dcaf0' }};">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">ESTATUS</small>
                        <span class="badge bg-{{ $estatusColor }}">{{ $estatusTexto }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errores de recepción --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Detalles de la factura
                        @if($erroresRecepcion && $erroresRecepcion->count() > 0)
                            <span class="badge bg-white ms-2 fw-semibold" style="color:#d97706;">
                                {{ $erroresRecepcion->count() }}
                            </span>
                        @endif
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelErrores()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFErroresConImagenes()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($erroresRecepcion && $erroresRecepcion->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaErrores">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;width:90px;">FOTO</th>  {{-- 🔥 Aumentar de 60px a 90px --}}
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">CÓDIGO</th>
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">PRODUCTO</th>
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">REFERENCIA</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PEDIDO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">RECIBIDO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">CAJA VACÍA</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE INV.</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE SOLO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">DAÑADO</th>
                                    <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalDiferencia = 0; @endphp
                                @foreach($erroresRecepcion as $error)
                                    @php 
                                        $totalDiferencia += $error->Diferencia ?? 0;
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/items/thumbs/',
                                            $error->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                        $imgFull = FileHelper::getOrDownloadFile(
                                            'images/items/',
                                            $error->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-4 text-center">
                                            <img src="{{ $imgSrc }}"
                                                loading="lazy"
                                                alt="{{ $error->ProductoCodigo ?? 'Producto' }}"
                                                class="img-thumbnail img-zoomable"
                                                style="width:70px;height:70px;object-fit:cover;cursor:pointer;border-radius:4px;"
                                                onclick="zoomImagen(this)"
                                                data-full-image="{{ $imgFull }}"
                                                data-description="{{ $error->ProductoCodigo ?? 'Producto' }} - {{ $error->ProductoReferencia ?? '' }}"
                                                onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                        </td>
                                        <td>
                                            <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#d97706;font-weight:bold;">
                                                {{ $error->ProductoCodigo ?? 'N/A' }}
                                            </code>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $error->ProductoDescripcion ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted" style="font-size:0.8rem;">{{ $error->ProductoReferencia ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($error->CantidadPedida ?? 0, 0) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($error->CantidadRecibida ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadCajaVacia ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPieInvertido ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPieSolo ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPiezaDanada ?? 0, 0) }}</td>
                                        <td class="pe-4 text-end fw-bold text-danger">{{ number_format($error->Diferencia ?? 0, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:600;">
                                    <td colspan="10" class="ps-4 text-end">TOTAL DIFERENCIA:</td>
                                    <td class="pe-4 text-end fw-bold text-danger">{{ number_format($totalDiferencia, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2">No hay errores de recepción para esta factura</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Modal para zoom de imagen --}}
<div class="modal fade" id="modalZoomImagen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalZoomTitle">Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalZoomImage" src="" alt="Producto" style="max-width:100%;max-height:500px;border-radius:8px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    // ==========================
    // ZOOM DE IMAGEN
    // ==========================
    function zoomImagen(img) {
        const imgSrc = img.getAttribute('data-full-image') || img.src;
        const descripcion = img.getAttribute('data-description') || 'Producto';
        
        document.getElementById('modalZoomImage').src = imgSrc;
        document.getElementById('modalZoomTitle').textContent = descripcion;
        
        const modal = new bootstrap.Modal(document.getElementById('modalZoomImagen'));
        modal.show();
    }

    // ==========================
    // EXPORTAR EXCEL
    // ==========================
    function exportarExcelErrores() {
        const tabla = document.getElementById('tablaErrores');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar los errores de recepción?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Errores recepción" });
                XLSX.utils.book_append_sheet(wb, ws, 'Errores recepción');
                XLSX.writeFile(wb, `Errores_recepcion_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ==========================
    // EXPORTAR PDF
    // ==========================
    function exportarPDFErrores() {
        const tabla = document.getElementById('tablaErrores');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar los errores de recepción?',
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
                doc.setTextColor(245, 158, 11);
                doc.text('Errores de recepción', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Factura: {{ trim($factura->Numero ?? 'N/A') }}`, 14, 22);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 29);

                doc.autoTable({
                    html: '#tablaErrores',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [245, 158, 11] }
                });

                doc.save(`Errores_recepcion_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ==========================
    // EXPORTAR PDF CON IMÁGENES (usando html2canvas)
    // ==========================
    function exportarPDFErroresConImagenes() {
        const tabla = document.getElementById('tablaErrores');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: 'Este proceso puede tomar unos segundos',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Generando PDF...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Usar html2canvas para capturar la tabla como imagen
                html2canvas(tabla, {
                    scale: 4,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
                }).then((canvas) => {
                    const imgData = canvas.toDataURL('image/png');
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF('landscape');

                    doc.setFontSize(16);
                    doc.setTextColor(245, 158, 11);
                    doc.text('Diferencias de recepcion', 14, 15);

                    doc.setFontSize(10);
                    doc.setTextColor(100, 100, 100);
                    doc.text(`Factura: {{ trim($factura->Numero ?? 'N/A') }}`, 14, 22);
                    doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 29);

                    // Agregar la imagen de la tabla
                    const imgWidth = doc.internal.pageSize.getWidth() - 20;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    doc.addImage(imgData, 'PNG', 10, 35, imgWidth, imgHeight);

                    doc.save(`Diferencias_recepcion_${new Date().toISOString().slice(0,10)}.pdf`);
                    
                    Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
                }).catch((error) => {
                    Swal.fire('Error', 'Error al generar el PDF: ' + error.message, 'error');
                });
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-hover tbody tr:hover { background-color: #f8fafc; }
    .img-zoomable { 
        transition: transform 0.2s ease, box-shadow 0.2s ease; 
    }
    .img-zoomable:hover { 
        transform: scale(1.1); 
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
    }
</style>
@endpush