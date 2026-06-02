@extends('layout.layout_dashboard')

@section('title', 'Detalle de Factura')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>Detalle de Factura
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}">
                            Detalle Proveedor
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Factura #{{ $facturaDTO->Numero }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- ========================================== -->
        <!-- TARJETA DE INFORMACIÓN GENERAL -->
        <!-- ========================================== -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle-fill me-2"></i>Información de la Factura
                        </h3>
                        <div class="card-tools">
                            @if($facturaDTO->Estatus == 1)
                            <a href="{{ route('cpanel.facturas.editar', $facturaDTO->ID) }}" 
                               class="btn btn-sm btn-light text-warning">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                            @endif
                            <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}" 
                               class="btn btn-sm btn-light text-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box p-3 h-100">
                                    <div class="d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="text-muted">Número:</span>
                                            <strong class="fs-5 d-block">{{ $facturaDTO->Numero }}</strong>
                                            @if($facturaDTO->Serie) 
                                                <small class="text-muted">Serie: {{ $facturaDTO->Serie }}</small>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">Fecha Emisión:</span>
                                            <span class="d-block">{{ \Carbon\Carbon::parse($facturaDTO->FechaCreacion)->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">Fecha Despacho:</span>
                                            <span class="d-block">{{ $facturaDTO->FechaDespacho ? \Carbon\Carbon::parse($facturaDTO->FechaDespacho)->format('d/m/Y') : 'Pendiente' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted">Estatus:</span>
                                            <div class="mt-1">
                                                @php
                                                    $estatusActual = (int)$facturaDTO->Estatus;
                                                    $estadoTexto = match($estatusActual) {
                                                        1 => 'En Proceso',
                                                        2 => 'Recibiendo',
                                                        4 => 'Recibida',
                                                        default => 'Desconocido'
                                                    };
                                                    $estadoColor = match($estatusActual) {
                                                        1 => 'warning',
                                                        2 => 'info',
                                                        4 => 'success',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $estadoColor }} fs-6 px-3 py-2">{{ $estadoTexto }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box p-3 h-100">
                                    <div class="d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="text-muted">Proveedor:</span>
                                            <strong class="d-block">{{ $facturaDTO->proveedor_nombre }}</strong>
                                            <small>RIF: {{ $facturaDTO->proveedor_rif ?? 'N/A' }}</small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">Sucursal:</span>
                                            <span class="d-block">{{ $facturaDTO->sucursal_nombre }}</span>
                                            <small>{{ $facturaDTO->sucursal_direccion ?? '' }}</small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">Contenedor:</span>
                                            <span class="d-block">{{ $facturaDTO->Contenedor->Nombre ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted">Descripción:</span>
                                            <span class="d-block">{{ $facturaDTO->Descripcion ?? 'Sin descripción' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RESUMEN FINANCIERO (2 columnas) -->
        <!-- ========================================== -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-info h-100 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-calculator-fill me-2"></i>RESUMEN DE LA FACTURA
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Subtotal productos:</span>
                            <strong>$ {{ number_format($facturaDTO->Subtotal ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Flete contenedor:</span>
                            <strong>$ {{ number_format($facturaDTO->Flete ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Costo traspaso:</span>
                            <strong>$ {{ number_format($facturaDTO->CostoTraspaso ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="fw-bold fs-5">TOTAL FACTURA:</span>
                            <strong class="fw-bold text-success fs-5">$ {{ number_format($facturaDTO->TotalFactura ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-warning h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-cash-stack me-2"></i>GASTOS Y ADUANA
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Aduana:</span>
                            <strong>$ {{ number_format($facturaDTO->Aduana ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Porcentaje de gastos:</span>
                            <strong>{{ number_format($facturaDTO->PorcentajeGastos ?? 0, 2) }} %</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TABS: PRODUCTOS | PAGOS -->
        <!-- ========================================== -->
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="facturaTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" 
                                data-bs-target="#productos" type="button">
                            <i class="bi bi-box-seam me-1"></i>Productos 
                            <span class="badge bg-primary ms-1">{{ $facturaDTO->Detalles->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" 
                                data-bs-target="#pagos" type="button">
                            <i class="bi bi-cash-stack me-1"></i>Pagos 
                            <span class="badge bg-success ms-1">{{ $facturaDTO->Pagos->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB: PRODUCTOS (CORREGIDO) -->
                    <div class="tab-pane fade show active" id="productos">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-danger" onclick="exportarPDFProductos()">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="exportarExcelProductos()">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" id="tablaProductos">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th style="width: 80px;">Imagen</th>
                                        <th>Código</th>
                                        <th>Referencia</th>
                                        <th>Producto</th>
                                        <th class="text-end">Cantidad (Unidades)</th>
                                        <th class="text-end">Costo Unitario USD</th>
                                        <th class="text-end">Total USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($facturaDTO->Detalles as $detalle)
                                    @php
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/items/thumbs/',
                                            $detalle->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                        
                                        // ✅ Calcular correctamente
                                        $totalUnidades = $detalle->CantidadRecibida ?? 0;
                                        $costoUnitario = $totalUnidades > 0 ? ($detalle->CostoDivisa / $totalUnidades) : 0;
                                        $totalUSD = $detalle->CostoDivisa ?? 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <img src="{{ $imgSrc }}" 
                                                alt="{{ $detalle->Codigo ?? 'producto' }}"
                                                class="img-thumbnail img-zoomable"
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                data-full-image="{{ $imgSrc }}"
                                                data-description="{{ $detalle->producto_nombre ?? 'Sin descripción' }}"
                                                onclick="zoomImagen(this)">
                                        </td>
                                        <td class="align-middle"><code>{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                        <td class="align-middle">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                        <td class="align-middle">{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td class="text-end align-middle">{{ number_format($totalUnidades, 2) }}</td>
                                        <td class="text-end align-middle">$ {{ number_format($costoUnitario, 2) }}</td>
                                        <td class="text-end align-middle fw-bold">$ {{ number_format($totalUSD, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                            No hay productos registrados en esta factura
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- TAB: PAGOS (sin cambios) -->
                    <div class="tab-pane fade" id="pagos">
                        <!-- El contenido del tab de pagos se mantiene igual -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold text-success">
                                <i class="bi bi-cash-stack me-1"></i>
                                Total Pagado: $ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}
                                <span class="text-danger ms-3">
                                    Saldo Pendiente: $ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cpanel.facturas.recibo-pagos', $facturaDTO->ID) }}" 
                                   class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </a>
                                <button type="button" class="btn btn-outline-success" onclick="exportarExcelPagos()">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                                <a href="{{ route('cpanel.proveedores.pagar', $facturaDTO->ProveedorId) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i>Registrar Pago
                                </a>
                            </div>
                        </div>
                        
                        @if($facturaDTO->Pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaPagos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>N° Operación</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Monto USD</th>
                                        <th>Estatus</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturaDTO->Pagos as $pago)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $pago->NumeroOperacion ?? 'N/A' }}</td>
                                        <td>{{ $pago->Descripcion ?? 'Abono factura' }}</td>
                                        <td class="text-end">$ {{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                        <td class="text-center">
                                            @php
                                                $estatusPago = match((int)$pago->Estatus) {
                                                    2 => ['texto' => 'Pagada', 'clase' => 'success'],
                                                    4 => ['texto' => 'Cerrada', 'clase' => 'secondary'],
                                                    1 => ['texto' => 'Pendiente', 'clase' => 'warning'],
                                                    default => ['texto' => 'Desconocido', 'clase' => 'secondary']
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $estatusPago['clase'] }}">
                                                {{ $estatusPago['texto'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                @if(in_array((int)$pago->Estatus, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $pago->ID) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @endif
                                                
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarPago({{ $pago->ID }}, '{{ $pago->NumeroOperacion }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('cpanel.pagos.imprimir', $pago->ID) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">TOTAL PAGADO:</td>
                                        <td class="text-end fw-bold text-success">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-danger">SALDO PENDIENTE:</td>
                                        <td class="text-end fw-bold text-danger">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay pagos registrados para esta factura
                        </div>
                        @endif
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
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // ZOOM DE IMÁGENES
    // ============================================
    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');
        document.getElementById('zoomedImage').src = fullImage;
        document.getElementById('imageDescription').textContent = description;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeZoom();
    });
    
    // ============================================
    // EXPORTAR PRODUCTOS
    // ============================================
    function exportarExcelProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Productos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Productos');
        XLSX.writeFile(wb, `Productos_Factura_${new Date().toISOString().slice(0,10)}.xlsx`);
    }
    
    function exportarPDFProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Productos de la Factura', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ 
            html: '#tablaProductos', 
            startY: 35, 
            theme: 'grid', 
            headStyles: { fillColor: [41, 128, 185] },
            styles: { fontSize: 8 }
        });
        doc.save(`Productos_Factura_${new Date().toISOString().slice(0,10)}.pdf`);
    }
    
    // ============================================
    // EXPORTAR PAGOS
    // ============================================
    function exportarExcelPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Pagos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Pagos');
        XLSX.writeFile(wb, `Pagos_Factura_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportarPDFPagos() {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        const facturaId = '{{ $facturaDTO->ID }}';
        const url = '/cpanel/facturas/' + facturaId + '/recibo-pagos';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) generarPDFReciboPagos(data);
            else Swal.fire('Error', data.message || 'Error al generar el PDF', 'error');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexión al servidor: ' + error.message, 'error');
        });
    }

    function generarPDFReciboPagos(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 20;
        
        doc.setFillColor(43, 140, 94);
        doc.rect(0, 0, pageWidth, 45, 'F');
        doc.setFontSize(18);
        doc.setTextColor(255, 255, 255);
        doc.text('TENSHOP', pageWidth / 2, 25, { align: 'center' });
        doc.setFontSize(10);
        doc.text('Comprobante de pagos', pageWidth / 2, 35, { align: 'center' });
        
        yPos = 60;
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('REGISTRO DE PAGOS', pageWidth / 2, yPos, { align: 'center' });
        yPos += 10;
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Factura N°: ${data.factura_numero}`, 20, yPos);
        doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, pageWidth - 20, yPos, { align: 'right' });
        yPos += 10;
        
        const totalPagado = parseFloat(data.total_pagado) || 0;
        const saldoPendiente = parseFloat(data.saldo_pendiente) || 0;
        
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.setFillColor(240, 240, 240);
        doc.rect(20, yPos, pageWidth - 40, 35, 'F');
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text(`Total Pagado:`, 25, yPos + 10);
        doc.setFontSize(12);
        doc.setTextColor(43, 140, 94);
        doc.text(`$ ${totalPagado.toFixed(2)}`, 100, yPos + 10);
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text(`Saldo Pendiente:`, 25, yPos + 22);
        doc.setFontSize(12);
        doc.setTextColor(220, 53, 69);
        doc.text(`$ ${saldoPendiente.toFixed(2)}`, 100, yPos + 22);
        
        yPos += 45;
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text('Detalle de pagos realizados:', 20, yPos);
        yPos += 5;
        
        const tableColumnas = ['Fecha', 'N° Operación', 'Descripción', 'Monto USD', 'Estatus'];
        const tableData = [];
        data.pagos.forEach(pago => {
            const monto = parseFloat(pago.MontoDivisaAbonado) || 0;
            tableData.push([
                new Date(pago.Fecha).toLocaleDateString('es-VE'),
                pago.NumeroOperacion || 'N/A',
                pago.Descripcion || 'Abono factura',
                `$ ${monto.toFixed(2)}`,
                pago.EstatusTexto
            ]);
        });
        
        doc.autoTable({
            head: [tableColumnas],
            body: tableData,
            startY: yPos,
            margin: { left: 20, right: 20 },
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: [255, 255, 255], fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            columnStyles: {
                0: { cellWidth: 35 },
                1: { cellWidth: 45 },
                2: { cellWidth: 60 },
                3: { cellWidth: 35, halign: 'right' },
                4: { cellWidth: 30, halign: 'center' }
            }
        });
        
        yPos = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(9);
        doc.setTextColor(128, 128, 128);
        doc.text('Este documento es un comprobante de pagos válido para efectos contables.', pageWidth / 2, yPos, { align: 'center' });
        doc.text(`Generado el ${new Date().toLocaleString('es-VE')}`, pageWidth / 2, yPos + 7, { align: 'center' });
        
        yPos += 20;
        doc.line(30, yPos, 80, yPos);
        doc.line(pageWidth - 80, yPos, pageWidth - 30, yPos);
        doc.setFontSize(9);
        doc.text('FIRMA DEL PROVEEDOR', 55, yPos + 5, { align: 'center' });
        doc.text('FIRMA DEL RECIBIDOR', pageWidth - 55, yPos + 5, { align: 'center' });
        
        doc.save(`Recibo_Pagos_Factura_${data.factura_numero}_${new Date().toISOString().slice(0,10)}.pdf`);
        Swal.close();
    }
    
    // ============================================
    // REGISTRAR PAGO
    // ============================================
    function registrarPago(facturaId) {
        Swal.fire({
            title: 'Registrar Pago',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // ============================================
    // ELIMINAR PAGO
    // ============================================
    function eliminarPago(pagoId, numeroOperacion) {
        Swal.fire({
            title: '¿Eliminar pago?',
            html: `Estás a punto de eliminar el pago <strong>${numeroOperacion}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                fetch(`{{ url('pagos') }}/${pagoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ title: '¡Eliminado!', text: 'El pago ha sido eliminado correctamente', icon: 'success', timer: 2000, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el pago', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    .sticky-top { position: sticky; top: 0; z-index: 10; }
    .img-thumbnail { border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-thumbnail:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0,0,0,0.2); cursor: pointer; }
    .image-zoom-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; }
    .image-zoom-container { position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; }
    .image-zoom-container img { max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .image-zoom-close { position: absolute; top: -40px; right: -10px; color: white; font-size: 40px; cursor: pointer; background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .image-zoom-close:hover { color: #ff6b6b; }
    .image-description { color: white; text-align: center; margin-top: 20px; background: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 8px; }
    .nav-tabs .nav-link { font-weight: 500; }
    .nav-tabs .nav-link.active { border-top: 3px solid #007bff; }
    .card-header-tabs { margin-right: -1rem; margin-left: -1rem; border-bottom: 0; }
    .bg-gradient-primary { background: linear-gradient(135deg, #1e5799 0%, #2b8c5e 100%); }
</style>
@endpush