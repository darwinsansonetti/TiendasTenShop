@extends('layout.layout_dashboard')

@section('title', 'Detalle del Proveedor')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="fas fa-truck me-2"></i>Registrar Pago Proveedor
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Pago</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Tarjeta de Información del Proveedor -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>Información del Proveedor
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}" 
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i>Ver Detalles
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $proveedor->Nombre }}"
                             class="img-fluid rounded-circle border border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <div class="mt-2">
                            @if($proveedor->Estatus == 0)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge bg-primary">Mercancía</span>
                            @else
                                <span class="badge bg-info">Servicio</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="120">Nombre:</th>
                                        <td><strong>{{ $proveedor->Nombre }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Rif/Cédula:</th>
                                        <td>{{ $proveedor->Rif_Cedula ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono Móvil:</th>
                                        <td>{{ $proveedor->TelefonoMovil ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono Fijo:</th>
                                        <td>{{ $proveedor->TelefonoFijo ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="120">Email:</th>
                                        <td>{{ $proveedor->CorreoElectronico ?: 'N/A' }}</td>
                                    </tr>
                                    </tr>
                                        <th>Fecha Registro:</th>
                                        <td>{{ \Carbon\Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dirección:</th>
                                        <td>{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 50) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Número Cuenta:</th>
                                        <td>{{ $proveedor->NumeroDeCuenta ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </br>

        <!-- Tarjetas de Resumen -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</h3>
                        <p>Total Facturas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($balanceFacturas->totalPagado, 2) }}</h3>
                        <p>Total Pagado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</h3>
                        <p>Saldo Pendiente</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Overlay para zoom de imágenes -->
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display: none;" onclick="closeZoom()">
    <div class="image-zoom-container" onclick="event.stopPropagation()">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="Zoom">
        <div class="image-description" id="imageDescription"></div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // ============================================
        // ZOOM DE IMÁGENES
        // ============================================
        document.querySelectorAll('.img-zoomable').forEach(img => {
            img.addEventListener('click', function() {
                const fullImage = this.getAttribute('data-full-image');
                const description = this.getAttribute('data-description');
                
                document.getElementById('zoomedImage').src = fullImage;
                document.getElementById('imageDescription').textContent = description;
                document.getElementById('imageZoomOverlay').style.display = 'flex';
                
                // Prevenir scroll del body
                document.body.style.overflow = 'hidden';
            });
        });
    });
    
    // ============================================
    // FUNCIONES DE ZOOM
    // ============================================
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeZoom();
        }
    });
    
    // Cerrar al hacer clic fuera de la imagen
    document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeZoom();
        }
    });
    
    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');
        
        document.getElementById('zoomedImage').src = fullImage;
        document.getElementById('imageDescription').textContent = description;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // ============================================
    // ESTADO DE CUENTA
    // ============================================
    function exportarExcelEstadoCuenta() {
        const tabla = document.getElementById('tablaEstadoCuenta');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Estado de Cuenta" });
        XLSX.utils.book_append_sheet(wb, ws, 'Estado de Cuenta');
        XLSX.writeFile(wb, `Estado_Cuenta_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaEstadoCuenta() {
        const tabla = document.getElementById('tablaEstadoCuenta');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Estado de Cuenta', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaEstadoCuenta', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Estado_Cuenta_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // ============================================
    // FACTURAS
    // ============================================
    function exportarExcelFacturas() {
        const tabla = document.getElementById('tablaFacturas');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Facturas" });
        XLSX.utils.book_append_sheet(wb, ws, 'Facturas');
        XLSX.writeFile(wb, `Facturas_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaFacturas() {
        const tabla = document.getElementById('tablaFacturas');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Facturas', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaFacturas', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Facturas_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // ============================================
    // PAGOS
    // ============================================
    function exportarExcelPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Pagos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Pagos');
        XLSX.writeFile(wb, `Pagos_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Pagos', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaPagos', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Pagos_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // ============================================
    // PRODUCTOS
    // ============================================
    function exportarExcelProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Productos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Productos');
        XLSX.writeFile(wb, `Productos_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Productos del Proveedor', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaProductos', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Productos_${new Date().toISOString().slice(0,10)}.pdf`);
    }
</script>
@endsection

@push('styles')
<style>
    .small-box {
        border-radius: 8px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }
    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    }
    .small-box .inner {
        padding: 10px 15px;
    }
    .small-box h3 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0 0 5px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
        margin-bottom: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 5px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(255,255,255,0.3);
        transition: transform 0.3s ease;
    }
    .small-box:hover .icon {
        transform: scale(1.05);
    }
    .table-dark {
        background-color: #343a40 !important;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,0.02);
    }

    .table-sm td, .table-sm th {
        padding: 0.4rem 0.5rem;
        vertical-align: middle;
    }

    .text-wrap {
        word-wrap: break-word;
        white-space: normal !important;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-responsive {
        scrollbar-width: thin;
    }

    /* Overlay para zoom */
    .image-zoom-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeInOverlay 0.3s ease-out;
    }

    .image-zoom-container {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .image-zoom-container img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        animation: zoomInImage 0.3s ease-out;
    }

    .image-zoom-close {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        background: rgba(0, 0, 0, 0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .image-zoom-close:hover {
        color: #ff6b6b;
        background: rgba(0, 0, 0, 0.7);
    }

    .image-description {
        color: white;
        text-align: center;
        margin-top: 20px;
        font-size: 1.1rem;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 20px;
        border-radius: 8px;
        max-width: 80%;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes zoomInImage {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    /* Animaciones para la tabla de productos */
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }

    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush