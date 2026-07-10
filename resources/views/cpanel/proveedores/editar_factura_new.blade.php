@extends('layout.layout_dashboard')

@section('title', 'Editar Factura')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                  <i class="bi bi-file-earmark-text text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Factura #{{ $facturaDTO->Numero }}</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Modificar datos de la factura del proveedor</p>
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
                        <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}">Detalle Proveedor</a>
                    </li>
                    <li class="breadcrumb-item active">Factura #{{ $facturaDTO->Numero }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DE LA FACTURA --}}
        {{-- ================================================ --}}
        @php
            $estatusActual = (int)$facturaDTO->Estatus;
            $estadoTexto = match($estatusActual) {
                1 => 'En Proceso', 2 => 'Recibiendo', 4 => 'Recibida', default => 'Desconocido'
            };
            $estadoBadge = match($estatusActual) {
                1 => 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)',
                2 => 'background:rgba(6,182,212,0.1);color:#0c4a6e;border:1px solid rgba(6,182,212,0.25)',
                4 => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',
                default => 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)',
            };
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Factura
                    </h6>
                    <div class="d-flex gap-2">
                        @if($facturaDTO->Estatus == 1)
                        <a href="{{ route('cpanel.facturas.editar', $facturaDTO->ID) }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.4);font-size:0.8rem;">
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
                    {{-- Columna izquierda --}}
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Número</p>
                                <p class="mb-0 fw-bold text-dark" style="font-size:1.05rem;">{{ $facturaDTO->Numero }}</p>
                                @if($facturaDTO->Serie)
                                    <small class="text-muted">Serie: {{ $facturaDTO->Serie }}</small>
                                @endif
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Estatus</p>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                      style="{{ $estadoBadge }};font-size:0.82rem;">
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
                                    {{ $facturaDTO->FechaDespacho ? \Carbon\Carbon::parse($facturaDTO->FechaDespacho)->format('d/m/Y') : 'Pendiente' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- Columna derecha --}}
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Proveedor</p>
                                <p class="mb-0 fw-bold text-dark">{{ $facturaDTO->proveedor_nombre }}</p>
                                <small class="text-muted">RIF: {{ $facturaDTO->proveedor_rif ?? 'N/A' }}</small>
                            </div>
                            <div class="col-md-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->sucursal_nombre }}</p>
                                @if($facturaDTO->sucursal_direccion)
                                    <small class="text-muted">{{ $facturaDTO->sucursal_direccion }}</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Contenedor</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->Contenedor->Nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Descripción</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $facturaDTO->Descripcion ?? 'Sin descripción' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- RESUMEN FINANCIERO --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            {{-- Resumen de la factura --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-calculator me-2"></i>Resumen de la Factura
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center py-2"
                             style="border-bottom:1px solid #f1f5f9;">
                            <span class="text-muted" style="font-size:0.88rem;">Subtotal productos</span>
                            <span class="fw-semibold text-dark">$ {{ number_format($facturaDTO->Subtotal ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2"
                             style="border-bottom:1px solid #f1f5f9;">
                            <span class="text-muted" style="font-size:0.88rem;">Flete contenedor</span>
                            <span class="fw-semibold text-dark">$ {{ number_format($facturaDTO->Flete ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2"
                             style="border-bottom:1px solid #f1f5f9;">
                            <span class="text-muted" style="font-size:0.88rem;">Costo traspaso</span>
                            <span class="fw-semibold text-dark">$ {{ number_format($facturaDTO->CostoTraspaso ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-1">
                            <span class="fw-bold text-dark">TOTAL FACTURA</span>
                            <span class="fw-bold text-success" style="font-size:1.1rem;">$ {{ number_format($facturaDTO->TotalFactura ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Gastos y Aduana --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash-stack me-2"></i>Gastos y Aduana
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center py-2"
                             style="border-bottom:1px solid #f1f5f9;">
                            <span class="text-muted" style="font-size:0.88rem;">Aduana</span>
                            <span class="fw-semibold text-dark">$ {{ number_format($facturaDTO->Aduana ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-1">
                            <span class="fw-bold text-dark">Porcentaje de gastos</span>
                            <span class="fw-bold text-dark" style="font-size:1.1rem;">{{ number_format($facturaDTO->PorcentajeGastos ?? 0, 2) }} %</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- FORMULARIO DE EDICIÓN --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-pencil-square me-2"></i>Editar Factura
                </h6>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('cpanel.facturas.actualizar', $facturaDTO->ID) }}"
                      method="POST" id="formEditarFactura">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="proveedor_id" value="{{ $facturaDTO->ProveedorId }}">
                    <input type="hidden" name="tipo" value="{{ $facturaDTO->Tipo }}">

                    <div class="row g-3">

                        {{-- Contenedor --}}
                        <div class="col-md-6">
                            <label for="contenedor_id" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Contenedor</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-box-seam text-warning"></i>
                                </span>
                                <select name="contenedor_id" id="contenedor_id" class="form-select border-start-0">
                                    <option value="0">Seleccione un valor</option>
                                    @foreach($contenedores as $contenedor)
                                        <option value="{{ $contenedor->Id }}"
                                            {{ old('contenedor_id', $facturaDTO->ContenedorId) == $contenedor->Id ? 'selected' : '' }}>
                                            {{ $contenedor->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('cpanel.contenedores.crear') }}"
                                   class="btn btn-light border fw-semibold"
                                   title="Crear nuevo contenedor" data-bs-toggle="tooltip"
                                   style="font-size:0.82rem;">
                                    <i class="bi bi-plus-circle me-1"></i>Nuevo
                                </a>
                            </div>
                        </div>

                        {{-- Switch: Sumar flete --}}
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="rounded-3 p-3 w-100" style="background:#f8fafc;border:1px solid #e2e8f0;margin-top:1.6rem;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           name="es_cargar_flete" id="es_cargar_flete"
                                           value="1" {{ old('es_cargar_flete', $facturaDTO->EsCargarFleteEnFactura) == 1 ? 'checked' : '' }}
                                           style="width:2.4em;height:1.3em;">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="es_cargar_flete">
                                        Sumar flete en Factura
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="col-md-4">
                            <label for="fecha_creacion" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Fecha <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar text-warning"></i>
                                </span>
                                <input type="date" name="fecha_creacion" id="fecha_creacion"
                                       class="form-control border-start-0"
                                       value="{{ old('fecha_creacion', date('Y-m-d', strtotime($facturaDTO->FechaCreacion))) }}"
                                       required>
                            </div>
                        </div>

                        {{-- Traspaso --}}
                        <div class="col-md-4">
                            <label for="traspaso" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Traspaso</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="traspaso" id="traspaso"
                                       class="form-control border-start-0"
                                       value="{{ old('traspaso', $facturaDTO->Traspaso ?? 0) }}">
                            </div>
                        </div>

                        {{-- Estatus --}}
                        <div class="col-md-4">
                            <label for="estatus" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Estatus <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-toggle-on text-warning"></i>
                                </span>
                                <select name="estatus" id="estatus" class="form-select border-start-0" required>
                                    <option value="" {{ old('estatus', $facturaDTO->Estatus) == '' ? 'selected' : '' }}>Seleccione un valor</option>
                                    <option value="1" {{ old('estatus', $facturaDTO->Estatus) == 1 ? 'selected' : '' }}>En Proceso</option>
                                    <option value="2" {{ old('estatus', $facturaDTO->Estatus) == 2 ? 'selected' : '' }}>Recibiendo</option>
                                    <option value="4" {{ old('estatus', $facturaDTO->Estatus) == 4 ? 'selected' : '' }}>Recibida</option>
                                    <option value="3" {{ old('estatus', $facturaDTO->Estatus) == 3 ? 'selected' : '' }}>Pagada</option>
                                    <option value="0" {{ old('estatus', $facturaDTO->Estatus) === 0 || old('estatus', $facturaDTO->Estatus) === '0' ? 'selected' : '' }}>Anulada</option>
                                </select>
                            </div>
                            <div class="form-text">El estatus "En Proceso" permite editar la factura</div>
                        </div>

                        {{-- Nota informativa --}}
                        <div class="col-12">
                            <div class="d-flex align-items-start gap-3 rounded-3 p-3"
                                 style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);margin-top:1px;">
                                    <i class="bi bi-info-circle text-white" style="font-size:0.85rem;"></i>
                                </div>
                                <p class="mb-0 text-dark" style="font-size:0.88rem;">
                                    Los detalles de la factura (productos) se pueden agregar en la sección de productos.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-warning px-4 fw-semibold" id="btnActualizarFactura"
                                style="color:#fff;">
                            <i class="bi bi-save me-2"></i>Actualizar Factura
                        </button>
                        <button type="button" class="btn btn-success px-3 fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#modalCargarProductos">
                            <i class="bi bi-box-seam me-1"></i>Cargar Productos
                        </button>
                        <a href="{{ route('cpanel.facturas.detalle', $facturaDTO->ID) }}"
                           class="btn btn-light border px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABS: PRODUCTOS | PAGOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-white pb-0 pt-3 px-4">
                <ul class="nav nav-tabs border-0" id="facturaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="productos-tab" data-bs-toggle="tab"
                                data-bs-target="#productos" type="button" role="tab">
                            <i class="bi bi-box-seam me-1"></i>Productos
                            <span class="badge rounded-pill ms-1"
                                  style="background:rgba(59,130,246,0.15);color:#1d4ed8;font-size:0.72rem;">
                                {{ $facturaDTO->Detalles->count() }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pagos-tab" data-bs-toggle="tab"
                                data-bs-target="#pagos" type="button" role="tab">
                            <i class="bi bi-cash-stack me-1"></i>Pagos
                            <span class="badge rounded-pill ms-1"
                                  style="background:rgba(16,185,129,0.15);color:#059669;font-size:0.72rem;">
                                {{ $facturaDTO->Pagos->count() }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">

                    {{-- TAB: PRODUCTOS --}}
                    <div class="tab-pane fade show active" id="productos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="exportarPDFProductos()">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="exportarExcelProductos()">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                        </div>
                        <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0" id="tablaProductos">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">IMAGEN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PRODUCTO</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. UNIDADES</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO UNIT. USD</th>
                                        <th class="pe-3 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TOTAL USD</th>
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
                                        <td class="ps-3">
                                            <img src="{{ $imgSrc }}"
                                                 loading="lazy" 
                                                 alt="{{ $detalle->Codigo ?? 'producto' }}"
                                                 class="rounded img-zoomable"
                                                 style="width:46px;height:46px;object-fit:cover;border:1px solid #e2e8f0;cursor:zoom-in;"
                                                 data-full-image="{{ $imgSrc }}"
                                                 data-description="{{ $detalle->producto_nombre ?? 'Sin descripción' }}"
                                                 onclick="zoomImagen(this)">
                                        </td>
                                        <td>
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $detalle->Codigo ?? 'N/A' }}</code>
                                        </td>
                                        <td class="text-muted" style="font-size:0.88rem;">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                        <td>{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td class="text-end fw-semibold text-dark">{{ number_format($detalle->CantidadEmitida * $detalle->UxE, 2) }}</td>
                                        <td class="text-end fw-semibold text-dark">$ {{ number_format($detalle->CostoDivisa, 2) }}</td>
                                        <td class="pe-3 text-end fw-bold text-dark">$ {{ number_format($totalUSD, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
                                            No hay productos registrados en esta factura
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: PAGOS --}}
                    <div class="tab-pane fade" id="pagos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            {{-- Resumen financiero --}}
                            <div class="d-flex gap-3">
                                <div class="rounded-3 px-3 py-2"
                                     style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                                    <small class="text-muted d-block" style="font-size:0.7rem;letter-spacing:.04em;font-weight:600;">TOTAL PAGADO</small>
                                    <span class="fw-bold text-success">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</span>
                                </div>
                                <div class="rounded-3 px-3 py-2"
                                     style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">
                                    <small class="text-muted d-block" style="font-size:0.7rem;letter-spacing:.04em;font-weight:600;">SALDO PENDIENTE</small>
                                    <span class="fw-bold text-danger">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</span>
                                </div>
                            </div>
                            {{-- Botones --}}
                            <div class="d-flex gap-2">
                                <a href="{{ route('cpanel.facturas.recibo-pagos', $facturaDTO->ID) }}"
                                   class="btn btn-sm" target="_blank"
                                   style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </a>
                                <button type="button" class="btn btn-sm"
                                        style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                        onclick="exportarExcelPagos()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                                </button>
                                <a href="{{ route('cpanel.proveedores.pagar', $facturaDTO->ProveedorId) }}"
                                   class="btn btn-sm btn-success fw-semibold" style="font-size:0.8rem;">
                                    <i class="bi bi-plus-circle me-1"></i>Registrar Pago
                                </a>
                            </div>
                        </div>

                        @if($facturaDTO->Pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaPagos">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° OPERACIÓN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO USD</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                        <th class="pe-3 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturaDTO->Pagos as $pago)
                                    @php
                                        $estatusPago = match((int)$pago->Estatus) {
                                            2 => ['texto' => 'Pagada',   'clase' => 'success'],
                                            4 => ['texto' => 'Cerrada',  'clase' => 'secondary'],
                                            1 => ['texto' => 'Pendiente','clase' => 'warning'],
                                            default => ['texto' => 'Desconocido', 'clase' => 'secondary']
                                        };
                                        $pagoBadge = match($estatusPago['clase']) {
                                            'success'   => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',
                                            'warning'   => 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)',
                                            default     => 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)',
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3">{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($pago->NumeroOperacion)
                                                <code class="px-2 py-1 rounded-2"
                                                      style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $pago->NumeroOperacion }}</code>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $pago->Descripcion ?? 'Abono factura' }}</td>
                                        <td class="text-end fw-semibold text-dark">$ {{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                                  style="{{ $pagoBadge }};font-size:0.75rem;">
                                                {{ $estatusPago['texto'] }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                                   title="Ver detalle" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                                </a>
                                                @if(in_array((int)$pago->Estatus, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                   title="Editar pago" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                                @else
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(107,114,128,0.06);color:#9ca3af;border:1px solid rgba(107,114,128,0.15);cursor:not-allowed;"
                                                        disabled>
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;opacity:0.5;"></i>
                                                </button>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarPago({{ $pago->ID }}, '{{ $pago->NumeroOperacion }}')"
                                                        title="Eliminar pago" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                                <a href="{{ route('cpanel.pagos.imprimir', $pago->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                   target="_blank" title="Imprimir recibo" data-bs-toggle="tooltip">
                                                    <i class="bi bi-printer" style="font-size:0.8rem;"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f0fdf4;border-top:2px solid #bbf7d0;">
                                        <td colspan="3" class="ps-3 py-2 text-end fw-bold text-dark">TOTAL PAGADO:</td>
                                        <td class="py-2 text-end fw-bold text-success">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr style="background:#fef2f2;border-top:1px solid #fecaca;">
                                        <td colspan="3" class="ps-3 py-2 text-end fw-bold text-danger">SALDO PENDIENTE:</td>
                                        <td class="py-2 text-end fw-bold text-danger">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-cash-stack" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
                            No hay pagos registrados para esta factura
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- Zoom overlay --}}
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display:none;" onclick="closeZoom()">
    <div class="image-zoom-container" onclick="event.stopPropagation()">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="Zoom">
        <div class="image-description" id="imageDescription"></div>
    </div>
</div>

{{-- ================================================ --}}
{{-- MODAL: CARGAR PRODUCTOS --}}
{{-- ================================================ --}}
<div class="modal fade" id="modalCargarProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-box-seam me-2"></i>Cargar Productos a la Factura
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                {{-- Módulo 1: Buscar y gestionar producto --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-2"
                         style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white" style="font-size:0.88rem;">
                            <i class="bi bi-search me-2"></i>Buscar y Gestionar Producto
                        </h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Código</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="codigo_producto" placeholder="Ingrese código">
                                    <button class="btn btn-primary fw-semibold" type="button" id="btnBuscarProducto">
                                        <i class="bi bi-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Descripción</label>
                                <input type="text" class="form-control" id="descripcion_producto"
                                       style="background:#f8fafc;" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Empaque</label>
                                <select class="form-select" id="empaque">
                                    <option value="1">Unidad</option>
                                    <option value="12">Docena</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Costo (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                    <input type="number" step="0.01" class="form-control border-start-0"
                                           id="costo" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Cantidad</label>
                                <input type="number" step="0.01" class="form-control" id="cantidad" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark" style="font-size:0.83rem;">Total (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 fw-bold text-success"
                                          style="background:#f1f5f9;">$</span>
                                    <input type="text" class="form-control border-start-0"
                                           style="background:#f8fafc;" id="total" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-sm fw-semibold" id="btnEditarProducto"
                                    style="background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.3);">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </button>
                            <button type="button" class="btn btn-sm fw-semibold" id="btnBorrarProducto"
                                    style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.3);">
                                <i class="bi bi-trash me-1"></i>Borrar
                            </button>
                            <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnGuardarProducto">
                                <i class="bi bi-save me-1"></i>Guardar Producto
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Módulo 2: Gestión de Excel --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-2"
                         style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);">
                        <h6 class="mb-0 fw-bold text-white" style="font-size:0.88rem;">
                            <i class="bi bi-file-earmark-excel me-2"></i>Gestión de Excel
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm fw-semibold" id="btnDescargarFormato"
                                    style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.3);">
                                <i class="bi bi-download me-1"></i>Descargar Formato
                            </button>
                            <input type="file" id="excel_file_input" style="display:none;" accept=".xlsx,.xls">
                            <button type="button" class="btn btn-sm fw-semibold" id="btnCargarExcel"
                                    style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.3);">
                                <i class="bi bi-upload me-1"></i>Cargar Excel
                            </button>
                            <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btnGuardarExcel">
                                <i class="bi bi-save me-1"></i>Guardar Excel
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Módulo 3: Lista de productos --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-2"
                         style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-table text-muted"></i>
                            <span class="fw-semibold text-dark" style="font-size:0.88rem;">Lista de Productos</span>
                            <span class="badge rounded-pill ms-1"
                                  style="background:rgba(107,114,128,0.1);color:#374151;font-size:0.72rem;"
                                  id="totalProductosCount">0 productos</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0" id="tablaProductosModal">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                        <th class="ps-3 py-2" style="width:36px;">
                                            <input type="checkbox" id="seleccionarTodos">
                                        </th>
                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">EMPAQUE</th>
                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO UNIT. USD</th>
                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. UNIDADES</th>
                                        <th class="pe-3 py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TOTAL USD</th>
                                        <!-- ✅ Columna oculta para el costo del Excel -->
                                        <th style="display:none;">COSTO EXCEL</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosBody">
                                    @foreach($facturaDTO->Detalles as $detalle)
                                    @php
                                        $totalUnidades = ($detalle->CantidadEmitida ?? 0) * ($detalle->UxE ?? 1);
                                        $costoTotal = $detalle->CostoDivisa * $totalUnidades;
                                        
                                        if ($detalle->UxE == 1) $empaqueTexto = 'Unidad';
                                        elseif ($detalle->UxE == 12) $empaqueTexto = 'Docena';
                                        else $empaqueTexto = "Empaque x{$detalle->UxE}";
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3 text-center">
                                            <input type="checkbox" class="select-producto" value="{{ $detalle->ID }}">
                                        </td>
                                        <td><code class="px-1 rounded" style="background:#f1f5f9;color:#3b82f6;font-size:0.78rem;">{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                        <td style="font-size:0.85rem;">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                        <td style="font-size:0.85rem;">{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td style="font-size:0.85rem;">{{ $empaqueTexto }}</td>
                                        <td class="text-end" style="font-size:0.85rem;">$ {{ number_format($detalle->CostoDivisa, 2) }}</td>
                                        <td class="text-end" style="font-size:0.85rem;">{{ number_format($totalUnidades, 2) }}</td>
                                        <td class="pe-3 text-end fw-semibold" style="font-size:0.85rem;">$ {{ number_format($costoTotal, 2) }}</td>
                                        <!-- ✅ Columna oculta para el costo del Excel (se llena desde JS) -->
                                        <td style="display:none;" class="costo-excel">{{ $detalle->CostoDivisa ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
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
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
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

    // ============================================
    // EVENTOS DEL MODAL
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initModalEventos();
    });

    function initModalEventos() {
        // Buscar Producto
        const btnBuscar = document.getElementById('btnBuscarProducto');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', function() {
                let codigo = document.getElementById('codigo_producto')?.value;
                let proveedorId = '{{ $facturaDTO->ProveedorId }}';
                let facturaId = '{{ $facturaDTO->ID }}';
                if (!codigo) {
                    Swal.fire('Error', 'Ingrese un código de producto', 'warning');
                    return;
                }
                const url = '{{ url("/cpanel/buscar-producto") }}?codigo=' + encodeURIComponent(codigo) + '&proveedor_id=' + proveedorId + '&factura_id=' + facturaId;
                fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        document.getElementById('descripcion_producto').value = response.producto.Descripcion;
                        document.getElementById('costo').value = response.producto.CostoDivisa;
                        let productoIdInput = document.getElementById('producto_id');
                        if (!productoIdInput) {
                            let hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.id = 'producto_id';
                            document.getElementById('codigo_producto').parentElement.appendChild(hiddenInput);
                        }
                        document.getElementById('producto_id').value = response.producto.ID;
                        let cantidadEnFactura = response.producto.CantidadEnFactura || 0;
                        let cantidadDisponible = response.producto.CantidadDisponible || 0;
                        if (cantidadEnFactura > 0) {
                            document.getElementById('cantidad').value = cantidadEnFactura;
                            document.getElementById('cantidad').placeholder = `Cantidad actual en factura: ${cantidadEnFactura}`;
                        } else {
                            document.getElementById('cantidad').value = '';
                            document.getElementById('cantidad').placeholder = `Disponible: ${cantidadDisponible}`;
                            Swal.fire({ title: 'Producto encontrado', html: `Producto: <strong>${response.producto.Descripcion}</strong><br>Cantidad disponible: <strong>${cantidadDisponible}</strong>`, icon: 'success', timer: 2000, showConfirmButton: false });
                        }
                        calcularTotalModal();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        limpiarFormularioProductoModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al buscar el producto', 'error');
                });
            });
        }

        // Guardar Producto Individual
        document.getElementById('btnGuardarProducto')?.addEventListener('click', guardarProductoModal);
        document.getElementById('btnEditarProducto')?.addEventListener('click', editarProductoModal);
        document.getElementById('btnBorrarProducto')?.addEventListener('click', borrarProductoModal);

        // Gestión Excel
        document.getElementById('btnDescargarFormato')?.addEventListener('click', descargarFormatoExcel);
        document.getElementById('btnCargarExcel')?.addEventListener('click', function() {
            let input = document.createElement('input');
            input.type = 'file';
            input.accept = '.xlsx, .xls';
            input.onchange = function(e) {
                let file = e.target.files[0];
                if (!file) return;
                let formData = new FormData();
                formData.append('excel_file', file);
                formData.append('_token', '{{ csrf_token() }}');
                Swal.fire({ title: 'Cargando archivo...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                const facturaId = '{{ $facturaDTO->ID }}';
                const url = '{{ route("cpanel.facturas.upload.excel", $facturaDTO->ID) }}';
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        llenarTablaModalConProductos(data.detalles);
                        Swal.fire({ title: 'Éxito', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al cargar el archivo', 'error');
                });
            };
            input.click();
        });

        document.getElementById('btnGuardarExcel')?.addEventListener('click', guardarExcelModal);

        // Seleccionar todos
        document.getElementById('seleccionarTodos')?.addEventListener('change', function() {
            document.querySelectorAll('.select-producto').forEach(cb => cb.checked = this.checked);
            actualizarTotalProductos();
        });

        actualizarTotalProductos();
        document.getElementById('costo')?.addEventListener('input', calcularTotalModal);
        document.getElementById('cantidad')?.addEventListener('input', calcularTotalModal);
    }

    function llenarTablaModalConProductos(productos) {
        let tbody = document.getElementById('tablaProductosBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        productos.forEach(producto => {
            let uxe = producto.UxE || producto.uxe || 1;
            let cantidadEmpaques = producto.Cantidad || producto.cantidad || 0;
            let costoTotal = producto.Costo || producto.costo || 0;
            let costoPorEmpaque = producto.CostoPorEmpaque || producto.costo_por_empaque || 0;  // ✅ 6 o 20

            let totalUnidades = cantidadEmpaques * uxe;
            let costoUnitario = totalUnidades > 0 ? costoTotal / totalUnidades : 0;

            let empaqueTexto = uxe == 1 ? 'Unidad' : (uxe == 12 ? 'Docena' : `Empaque x${uxe}`);

            let row = tbody.insertRow();
            row.innerHTML = `
                <td class="text-center"><input type="checkbox" class="select-producto"></td>
                <td><code>${producto.Codigo || producto.codigo}</code></td>
                <td>${producto.Referencia || producto.referencia || ''}</td>
                <td>${producto.Descripcion || producto.descripcion}</td>
                <td>${empaqueTexto}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>
                <td class="text-end">${totalUnidades.toFixed(2)}</td>
                <td class="text-end">$${costoTotal.toFixed(2)}</td>
                <!-- ✅ Columna oculta: costo por empaque (6 o 20) -->
                <td style="display:none;" class="costo-excel">${costoPorEmpaque.toFixed(2)}</td>
            `;

            row.setAttribute('data-costo-total', costoTotal);
        });

        actualizarTotalProductos();
    }

    function actualizarTotalProductos() {
        let tbody = document.getElementById('tablaProductosBody');
        let total = tbody.querySelectorAll('tr').length;
        let span = document.getElementById('totalProductosCount');
        if (span) span.innerText = total + ' productos';
    }

    function descargarFormatoExcel() {
        // Crear el libro de trabajo
        const wb = XLSX.utils.book_new();
        
        // Datos exactos del Excel que mostraste
        const data = [
            ['Entrada de Factura'],
            ['ENTRADA DE FACTURA'],
            [],
            ['Empresa', 'Tiendas TenShop'],
            [],
            ['Fecha', ''],
            [],
            ['Proveedor', '', 'Nombre', ''],
            [],
            ['Productos'],
            ['Codigo', 'Referencia', 'Descripcion', 'Cantidad', 'UxE', 'Costo']
        ];
        
        // Crear la hoja de trabajo
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Ajustar ancho de columnas
        ws['!cols'] = [
            { wch: 15 }, // Codigo
            { wch: 15 }, // Referencia
            { wch: 35 }, // Descripcion
            { wch: 12 }, // Cantidad
            { wch: 10 }, // UxE
            { wch: 15 }  // Costo
        ];
        
        // Agregar la hoja al libro
        XLSX.utils.book_append_sheet(wb, ws, 'Hoja1');
        
        // Descargar el archivo
        XLSX.writeFile(wb, 'EntradaFactura.xlsx');
    }

    function calcularTotalModal() {
        let costo = parseFloat(document.getElementById('costo')?.value) || 0;
        let cantidad = parseFloat(document.getElementById('cantidad')?.value) || 0;
        document.getElementById('total').value = (costo * cantidad).toFixed(2);
    }

    function limpiarFormularioProductoModal() {
        document.getElementById('codigo_producto').value = '';
        document.getElementById('descripcion_producto').value = '';
        document.getElementById('costo').value = '';
        document.getElementById('cantidad').value = '';
        document.getElementById('total').value = '';
    }

    function guardarProductoModal() {
        let producto = {
            codigo: document.getElementById('codigo_producto').value,
            descripcion: document.getElementById('descripcion_producto').value,
            producto_id: document.getElementById('producto_id')?.value || null,
            empaque: document.getElementById('empaque').value,
            costo: parseFloat(document.getElementById('costo').value) || 0,
            cantidad: parseFloat(document.getElementById('cantidad').value) || 0
        };
        if (!producto.codigo || !producto.descripcion) {
            Swal.fire('Error', 'Debe buscar un producto válido', 'warning');
            return;
        }
        if (!producto.producto_id) {
            Swal.fire('Error', 'Producto no válido, búsquelo nuevamente', 'warning');
            return;
        }
        if (producto.cantidad <= 0) {
            Swal.fire('Error', 'La cantidad debe ser mayor a 0', 'warning');
            return;
        }
        Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        const facturaId = '{{ $facturaDTO->ID }}';
        const url = '{{ route("cpanel.facturas.agregar.producto", $facturaDTO->ID) }}';
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ producto_id: producto.producto_id, cantidad: producto.cantidad, costo: producto.costo, empaque: producto.empaque })
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                actualizarTablaProductosFactura(response.detalles, response.total_factura);
                Swal.fire('Éxito', response.message, 'success').then(() => {
                    limpiarFormularioProductoModal();
                    cargarProductosEnModal(response.detalles);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al guardar el producto', 'error');
        });
    }

    function actualizarTablaProductosFactura(detalles, totalFactura) {
        let tbody = document.querySelector('#tablaProductos tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        detalles.forEach(detalle => {
            // ✅ Calcular correctamente
            let totalUnidades = (detalle.CantidadEmitida || 0) * (detalle.UxE || 1);
            let costoUnitario = (detalle.CostoDivisa || 0) / (totalUnidades > 0 ? totalUnidades : 1);
            let totalUSD = detalle.CostoDivisa || 0;

            let row = tbody.insertRow();
            row.innerHTML = `
                <td><img src="${detalle.UrlFoto ? '/storage/images/items/thumbs/' + detalle.UrlFoto : '/assets/img/adminlte/img/produc_default.jfif'}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td>${detalle.Codigo || 'N/A'}</td>
                <td>${detalle.Referencia || 'N/A'}</td>
                <td>${detalle.producto_nombre || 'N/A'}</td>
                <td class="text-end">${totalUnidades.toFixed(2)}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>
                <td class="text-end">$${totalUSD.toFixed(2)}</td>
            `;
        });

        let totalSpan = document.querySelector('#tablaProductos tfoot .text-end:last-child');
        if (totalSpan) totalSpan.innerText = `$${parseFloat(totalFactura).toFixed(2)}`;
    }

    function cargarProductosEnModal(detalles) {
        let tbody = document.getElementById('tablaProductosBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        detalles.forEach(detalle => {
            // ✅ Calcular correctamente para el modal
            let totalUnidades = (detalle.CantidadEmitida || 0) * (detalle.UxE || 1);
            let costoUnitario = (detalle.CostoDivisa || 0) / (totalUnidades > 0 ? totalUnidades : 1);
            let totalUSD = detalle.CostoDivisa || 0;

            let empaqueTexto = '';
            if (detalle.UxE == 1) empaqueTexto = 'Unidad';
            else if (detalle.UxE == 12) empaqueTexto = 'Docena';
            else empaqueTexto = `Empaque x${detalle.UxE}`;

            let row = tbody.insertRow();
            row.innerHTML = `
                <td class="text-center"><input type="checkbox" class="select-producto" value="${detalle.ID}"></td>
                <td><code>${detalle.Codigo || 'N/A'}</code></td>
                <td>${detalle.producto_nombre || 'N/A'}</td>
                <td>${empaqueTexto}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>
                <td class="text-end">${totalUnidades.toFixed(2)}</td>
                <td class="text-end">$${totalUSD.toFixed(2)}</td>
            `;
        });

        actualizarTotalProductos();
    }

    function editarProductoModal() {
        let selected = document.querySelectorAll('.select-producto:checked');
        if (selected.length !== 1) {
            Swal.fire('Error', 'Seleccione un solo producto para editar', 'warning');
            return;
        }
        let row = selected[0].closest('tr');
        let cells = row.cells;
        document.getElementById('codigo_producto').value = cells[1].innerText;
        document.getElementById('descripcion_producto').value = cells[2].innerText;
        document.getElementById('empaque').value = cells[3].innerText;
        document.getElementById('costo').value = parseFloat(cells[4].innerText.replace('$', ''));
        document.getElementById('cantidad').value = parseFloat(cells[5].innerText);
        calcularTotalModal();
        row.remove();
    }

    function borrarProductoModal() {
        let selected = document.querySelectorAll('.select-producto:checked');
        if (selected.length === 0) {
            Swal.fire('Error', 'Seleccione al menos un producto para eliminar', 'warning');
            return;
        }
        Swal.fire({
            title: '¿Eliminar producto(s)?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                selected.forEach(cb => cb.closest('tr').remove());
                Swal.fire('Eliminado', 'Producto(s) eliminados', 'success');
                actualizarTotalProductos();
            }
        });
    }

    function guardarExcelModal() {
        let productos = [];
        let tbody = document.getElementById('tablaProductosBody');
        let rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            let cells = row.cells;
            if (cells.length >= 7) {
                let producto = {
                    codigo: cells[1].innerText,
                    referencia: cells[2].innerText.trim(),
                    descripcion: cells[3].innerText,
                    empaque: cells[4].innerText,  // ← Ver qué valor tiene
                    costo: parseFloat(cells[7].innerText.replace('$', '')),  // Total USD
                    costo_unitario: parseFloat(cells[5].innerText.replace('$', '').trim()),  // Costo Unitario
                    cantidad: parseFloat(cells[6].innerText),  // Cantidad (Unidades)
                    costo_excel: parseFloat(cells[8].innerText) || 0  // ✅ Leer columna oculta
                };
                productos.push(producto);
                console.log('Producto enviado:', producto);  // ← Ver en consola
            }
        });
        if (productos.length === 0) {
            Swal.fire('Error', 'No hay productos para guardar', 'warning');
            return;
        }
        Swal.fire({ title: 'Guardando productos...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        const url = '{{ route("cpanel.facturas.guardar.excel", $facturaDTO->ID) }}';
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ productos: productos, factura_id: '{{ $facturaDTO->ID }}' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    window.location.href = '{{ route("cpanel.proveedores.detalle", $facturaDTO->ProveedorId) }}';
                });
            } else {
                Swal.fire('Error', data.message || 'Error al guardar productos', 'error');
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    /* Nav tabs */
    #facturaTabs .nav-link {
        color: #6b7280; border: none;
        border-bottom: 3px solid transparent;
        padding: 0.6rem 1rem; font-size: 0.88rem; background: transparent;
    }
    #facturaTabs .nav-link:hover { color: #1d4ed8; border-bottom-color: #bfdbfe; }
    #facturaTabs .nav-link.active { color: #1d4ed8; font-weight: 600; border-bottom: 3px solid #3b82f6; }

    /* Tablas */
    #tablaProductos tbody tr:hover,
    #tablaPagos tbody tr:hover { background: #f8fafc; }

    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Input focus */
    .form-control:focus, .form-select:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 0.2rem rgba(245,158,11,.15);
    }

    /* Zoom overlay */
    .image-zoom-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.9); z-index: 9999;
        justify-content: center; align-items: center;
    }
    .image-zoom-container {
        position: relative; max-width: 90%; max-height: 90%;
        display: flex; flex-direction: column; align-items: center;
    }
    .image-zoom-container img {
        max-width: 100%; max-height: 80vh; object-fit: contain;
        border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .image-zoom-close {
        position: absolute; top: -40px; right: -10px;
        color: #fff; font-size: 40px; cursor: pointer;
        background: rgba(0,0,0,0.5); width: 50px; height: 50px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        transition: color 0.2s, background 0.2s;
    }
    .image-zoom-close:hover { color: #ff6b6b; background: rgba(0,0,0,0.7); }
    .image-description {
        color: #fff; text-align: center; margin-top: 16px;
        background: rgba(0,0,0,0.7); padding: 8px 20px; border-radius: 8px;
    }
</style>
@endpush
