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
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                  <i class="bi bi-file-earmark-text text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Factura #{{ $facturaDTO->Numero }}</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Información completa de la factura del proveedor</p>
                </div>
              </div>
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

        @php
            $estatusActual  = (int)$facturaDTO->Estatus;
            $estadoTexto    = match($estatusActual) {
                1 => 'En Proceso',
                2 => 'Recibiendo',
                4 => 'Recibida',
                default => 'Desconocido'
            };
            $estadoColorMap = match($estatusActual) {
                1 => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a'],
                2 => ['bg' => '#e0f2fe', 'text' => '#0c4a6e', 'border' => '#bae6fd'],
                4 => ['bg' => '#dcfce7', 'text' => '#14532d', 'border' => '#bbf7d0'],
                default => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
            };
        @endphp

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN GENERAL --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Factura
                    </h6>
                    <div class="d-flex gap-2">
                        @if($facturaDTO->Estatus == 1)
                        <a href="{{ route('cpanel.facturas.editar', $facturaDTO->ID) }}"
                           class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        @endif
                        <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}"
                           class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4">
                    {{-- Columna izquierda: datos del documento --}}
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Número</p>
                                <p class="mb-0 fw-bold text-dark" style="font-size:1rem;">{{ $facturaDTO->Numero }}</p>
                                @if($facturaDTO->Serie)
                                    <small class="text-muted">Serie: {{ $facturaDTO->Serie }}</small>
                                @endif
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Estatus</p>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                      style="background:{{ $estadoColorMap['bg'] }};color:{{ $estadoColorMap['text'] }};border:1px solid {{ $estadoColorMap['border'] }};font-size:0.8rem;">
                                    {{ $estadoTexto }}
                                </span>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Emisión</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($facturaDTO->FechaCreacion)->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Despacho</p>
                                <p class="mb-0 fw-semibold text-dark">
                                    {{ $facturaDTO->FechaDespacho ? \Carbon\Carbon::parse($facturaDTO->FechaDespacho)->format('d/m/Y') : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Columna derecha: datos del proveedor / sucursal --}}
                    <div class="col-md-6" style="border-left:1px solid #f1f5f9;">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Proveedor</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->proveedor_nombre }}</p>
                                <small class="text-muted">RIF: {{ $facturaDTO->proveedor_rif ?? 'N/A' }}</small>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->sucursal_nombre }}</p>
                                <small class="text-muted">{{ $facturaDTO->sucursal_direccion ?? '' }}</small>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Contenedor</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->Contenedor->Nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Descripción</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->Descripcion ?? 'Sin descripción' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: RESUMEN FINANCIERO (mini KPI blocks) --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calculator me-2"></i>Resumen Financiero
                </h6>
            </div>
            <div class="card-body py-4">
                <div class="row g-3">
                    {{-- Subtotal --}}
                    <div class="col-lg col-md-4 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                    <i class="bi bi-receipt text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Subtotal</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($facturaDTO->Subtotal ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    {{-- Flete --}}
                    <div class="col-lg col-md-4 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#10b981,#059669);">
                                    <i class="bi bi-truck text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Flete</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($facturaDTO->Flete ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    {{-- Costo Traspaso --}}
                    <div class="col-lg col-md-4 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                                    <i class="bi bi-arrow-left-right text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Costo Traspaso</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($facturaDTO->CostoTraspaso ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    {{-- Aduana --}}
                    <div class="col-lg col-md-6 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                    <i class="bi bi-building text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Aduana</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($facturaDTO->Aduana ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    {{-- Total Factura (destacado con gradiente) --}}
                    <div class="col-lg col-md-6 col-12">
                        <div class="rounded-3 p-3 h-100" style="background:linear-gradient(135deg,#10b981,#059669);border:1px solid #059669;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:rgba(255,255,255,0.2);">
                                    <i class="bi bi-check-circle text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;color:rgba(255,255,255,0.85);">Total Factura</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-white">$ {{ number_format($facturaDTO->TotalFactura ?? 0, 2) }}</h5>
                            <small style="color:rgba(255,255,255,0.7);font-size:0.7rem;">{{ number_format($facturaDTO->PorcentajeGastos ?? 0, 2) }}% gastos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 3: TABS — PRODUCTOS | PAGOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-0 px-0" style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <ul class="nav nav-tabs border-0 px-4 pt-3" id="facturaTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active pb-3 fw-semibold" id="productos-tab"
                                data-bs-toggle="tab" data-bs-target="#productos" type="button"
                                style="font-size:0.85rem;border:none;background:transparent;">
                            <i class="bi bi-box-seam me-1"></i>Productos
                            <span class="badge ms-1 rounded-pill"
                                  style="background:rgba(59,130,246,0.1);color:#1d4ed8;font-size:0.7rem;">
                                {{ $facturaDTO->Detalles->count() }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link pb-3 fw-semibold" id="pagos-tab"
                                data-bs-toggle="tab" data-bs-target="#pagos" type="button"
                                style="font-size:0.85rem;border:none;background:transparent;">
                            <i class="bi bi-cash-stack me-1"></i>Pagos
                            <span class="badge ms-1 rounded-pill"
                                  style="background:rgba(16,185,129,0.1);color:#059669;font-size:0.7rem;">
                                {{ $facturaDTO->Pagos->count() }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content">

                    {{-- TAB: PRODUCTOS --}}
                    <div class="tab-pane fade show active" id="productos">
                        <div class="d-flex justify-content-end p-3 gap-2" style="border-bottom:1px solid #f1f5f9;">
                            <button type="button" class="btn btn-sm rounded-2"
                                    style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.8rem;"
                                    onclick="exportarPDFProductos()">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm rounded-2"
                                    style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.8rem;"
                                    onclick="exportarExcelProductos()">
                                <i class="bi bi-file-excel me-1"></i>Excel
                            </button>
                        </div>

                        <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0" id="tablaProductos">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                        <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">IMAGEN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PRODUCTO</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANTIDAD</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO USD</th>
                                        <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TOTAL USD</th>
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
                                        $totalUnidades = $detalle->CantidadEmitida ?? 0;
                                        $costoUnitario = $detalle->CostoDivisa ?? 0;
                                        $totalUSD = $costoUnitario * $totalUnidades * $detalle->UxE;
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-4 py-3 text-center">
                                            <img src="{{ $imgSrc }}"
                                                 loading="lazy" 
                                                 alt="{{ $detalle->Codigo ?? 'producto' }}"
                                                 class="img-thumbnail img-zoomable"
                                                 style="width:50px;height:50px;object-fit:cover;cursor:pointer;"
                                                 data-full-image="{{ $imgSrc }}"
                                                 data-description="{{ $detalle->producto_nombre ?? 'Sin descripción' }}"
                                                 onclick="zoomImagen(this)">
                                        </td>
                                        <td class="py-3">
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.78rem;">{{ $detalle->Codigo ?? 'N/A' }}</code>
                                        </td>
                                        <td class="py-3 text-muted" style="font-size:0.88rem;">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                        <td class="py-3 fw-semibold text-dark">{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td class="py-3 text-end fw-semibold text-dark">{{ number_format($detalle->CantidadEmitida * $detalle->UxE, 2) }}</td>
                                        <td class="py-3 text-end fw-semibold" style="color:#059669;">$ {{ number_format($detalle->CostoDivisa, 2) }}</td>
                                        <td class="pe-4 py-3 text-end fw-bold text-dark">$ {{ number_format($totalUSD, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-2">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                                     style="width:56px;height:56px;background:rgba(59,130,246,0.08);">
                                                    <i class="bi bi-box-seam text-primary" style="font-size:1.6rem;opacity:.5;"></i>
                                                </div>
                                                <p class="text-muted mb-0" style="font-size:0.9rem;">No hay productos registrados en esta factura</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: PAGOS --}}
                    <div class="tab-pane fade" id="pagos">
                        {{-- Barra de resumen + acciones --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3"
                             style="border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                            <div class="d-flex gap-4">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-check-circle-fill" style="color:#059669;"></i>
                                    <span class="fw-semibold text-dark" style="font-size:0.88rem;">Pagado:</span>
                                    <span class="fw-bold" style="color:#059669;">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-clock-fill" style="color:#dc2626;"></i>
                                    <span class="fw-semibold text-dark" style="font-size:0.88rem;">Pendiente:</span>
                                    <span class="fw-bold" style="color:#dc2626;">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('cpanel.facturas.recibo-pagos', $facturaDTO->ID) }}"
                                   class="btn btn-sm rounded-2" target="_blank"
                                   style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.8rem;">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </a>
                                <button type="button" class="btn btn-sm rounded-2"
                                        style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.8rem;"
                                        onclick="exportarExcelPagos()">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                                <a href="{{ route('cpanel.proveedores.pagar', $facturaDTO->ProveedorId) }}"
                                   class="btn btn-sm rounded-2"
                                   style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.8rem;">
                                    <i class="bi bi-plus-circle me-1"></i>Registrar Pago
                                </a>
                            </div>
                        </div>

                        @if($facturaDTO->Pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaPagos">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° OPERACIÓN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO USD</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                        <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturaDTO->Pagos as $pago)
                                    @php
                                        $estatusPago = match((int)$pago->Estatus) {
                                            2 => ['texto' => 'Pagada',      'bg' => 'rgba(16,185,129,0.1)',  'color' => '#059669',  'border' => 'rgba(16,185,129,0.25)'],
                                            4 => ['texto' => 'Cerrada',     'bg' => 'rgba(107,114,128,0.1)', 'color' => '#374151',  'border' => 'rgba(107,114,128,0.25)'],
                                            1 => ['texto' => 'Pendiente',   'bg' => 'rgba(245,158,11,0.1)',  'color' => '#92400e',  'border' => 'rgba(245,158,11,0.25)'],
                                            default => ['texto' => 'Desconocido', 'bg' => 'rgba(107,114,128,0.1)', 'color' => '#374151', 'border' => 'rgba(107,114,128,0.25)']
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-4 py-3 fw-semibold text-dark">{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                        <td class="py-3">
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.78rem;">{{ $pago->NumeroOperacion ?? 'N/A' }}</code>
                                        </td>
                                        <td class="py-3 text-muted" style="font-size:0.88rem;">{{ $pago->Descripcion ?? 'Abono factura' }}</td>
                                        <td class="py-3 text-end fw-bold text-dark">$ {{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                        <td class="py-3 text-center">
                                            <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                                  style="background:{{ $estatusPago['bg'] }};color:{{ $estatusPago['color'] }};border:1px solid {{ $estatusPago['border'] }};font-size:0.75rem;">
                                                {{ $estatusPago['texto'] }}
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);"
                                                   title="Ver detalle" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                                </a>

                                                @if(in_array((int)$pago->Estatus, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                   title="Editar" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                                @else
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:#f1f5f9;color:#94a3b8;border:1px solid #e2e8f0;"
                                                        disabled>
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </button>
                                                @endif

                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarPago({{ $pago->ID }}, '{{ $pago->NumeroOperacion }}')"
                                                        title="Eliminar" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>

                                                <a href="{{ route('cpanel.pagos.imprimir', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                   target="_blank" title="Imprimir" data-bs-toggle="tooltip">
                                                    <i class="bi bi-printer" style="font-size:0.8rem;"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f0fdf4;border-top:2px solid #bbf7d0;">
                                        <td colspan="3" class="ps-4 py-3 text-end fw-bold text-muted" style="font-size:0.82rem;letter-spacing:.04em;">TOTAL PAGADO</td>
                                        <td class="py-3 text-end fw-bold" style="color:#059669;font-size:1rem;">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr style="background:#fef2f2;border-top:1px solid #fee2e2;">
                                        <td colspan="3" class="ps-4 py-3 text-end fw-bold" style="font-size:0.82rem;letter-spacing:.04em;color:#dc2626;">SALDO PENDIENTE</td>
                                        <td class="py-3 text-end fw-bold" style="color:#dc2626;font-size:1rem;">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                     style="width:56px;height:56px;background:rgba(16,185,129,0.08);">
                                    <i class="bi bi-cash-stack text-success" style="font-size:1.6rem;opacity:.5;"></i>
                                </div>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">No hay pagos registrados para esta factura</p>
                                <a href="{{ route('cpanel.proveedores.pagar', $facturaDTO->ProveedorId) }}"
                                   class="btn btn-sm btn-primary mt-1">
                                    <i class="bi bi-plus-circle me-1"></i>Registrar primer pago
                                </a>
                            </div>
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
    .img-thumbnail { border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-thumbnail:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0,0,0,0.2); cursor: pointer; }
    .image-zoom-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; }
    .image-zoom-container { position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; }
    .image-zoom-container img { max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .image-zoom-close { position: absolute; top: -40px; right: -10px; color: white; font-size: 40px; cursor: pointer; background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .image-zoom-close:hover { color: #ff6b6b; }
    .image-description { color: white; text-align: center; margin-top: 20px; background: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 8px; }
    .nav-tabs .nav-link { color: #64748b; border: none; }
    .nav-tabs .nav-link.active { color: #1d4ed8; border-bottom: 3px solid #3b82f6 !important; background: transparent; }
    .nav-tabs .nav-link:hover { color: #3b82f6; }
</style>
@endpush
