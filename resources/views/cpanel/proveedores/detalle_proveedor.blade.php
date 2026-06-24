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
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                  <i class="bi bi-building text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle del Proveedor</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Información y facturas del proveedor</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Información del Proveedor
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.proveedores.editar', $proveedor->ProveedorId) }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(245,158,11,0.25);color:#fef3c7;border:1px solid rgba(255,255,255,0.3);font-size:0.8rem;">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}"
                           class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-start">
                    {{-- Imagen + badges --}}
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc }}"
                             alt="{{ $proveedor->Nombre }}"
                             class="rounded-circle img-zoomable"
                             style="width:120px;height:120px;object-fit:cover;border:3px solid #e2e8f0;cursor:zoom-in;"
                             onclick="zoomImagen(this)"
                             data-full-image="{{ $imgSrc }}"
                             data-description="{{ $proveedor->Nombre }}">
                        <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                            @if($proveedor->Estatus == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Activo</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.72rem;">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.72rem;">Mercancía</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Servicio</span>
                            @endif
                        </div>
                    </div>
                    {{-- Datos --}}
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                                <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->Rif_Cedula ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Registro</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono Móvil</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoMovil ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono Fijo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoFijo ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Correo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->CorreoElectronico ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-8 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Dirección</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 80) }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Número de Cuenta</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->NumeroDeCuenta ?: 'N/A' }}</p>
                            </div>
                            @if($banco)
                            <div class="col-12">
                                <div class="d-inline-flex align-items-center gap-2 rounded-2 px-3 py-2"
                                     style="background:#eff6ff;border:1px solid #bfdbfe;">
                                    <i class="bi bi-bank text-primary" style="font-size:0.9rem;"></i>
                                    <span class="fw-semibold text-dark" style="font-size:0.88rem;">{{ $banco->Nombre }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-3" style="border-top:1px solid #f1f5f9;">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cpanel.proveedores.pagar', $proveedor->ProveedorId) }}"
                       class="btn btn-success px-3 fw-semibold"
                       title="Registrar Pago" data-bs-toggle="tooltip">
                        <i class="bi bi-cash-stack me-1"></i>Registrar Pago
                    </a>
                    <button type="button"
                            class="btn btn-primary px-3 fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCrearFactura">
                        <i class="bi bi-file-earmark-plus me-1"></i>Crear Factura
                    </button>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- KPI CARDS --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                            <i class="bi bi-file-earmark-text text-white" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Facturas</p>
                            <h4 class="mb-0 fw-bold text-dark">$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</h4>
                        </div>
                    </div>
                    <div class="progress mt-1" style="height:4px;background:#dbeafe;">
                        <div class="progress-bar" style="width:100%;background:linear-gradient(90deg,#3b82f6,#1d4ed8);"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Total acumulado de todas las facturas</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="bi bi-check-circle text-white" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Pagado</p>
                            <h4 class="mb-0 fw-bold text-dark">$ {{ number_format($balanceFacturas->totalPagado, 2) }}</h4>
                        </div>
                    </div>
                    <div class="progress mt-1" style="height:4px;background:#d1fae5;">
                        <div class="progress-bar"
                             style="width:{{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->totalPagado / $balanceFacturas->totalFacturas) * 100 : 0 }}%;background:linear-gradient(90deg,#10b981,#059669);"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                        {{ number_format($balanceFacturas->porcentajePagado ?? 0, 1) }}% del total facturado
                    </small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                            <i class="bi bi-clock text-white" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Saldo Pendiente</p>
                            <h4 class="mb-0 fw-bold text-dark">$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</h4>
                        </div>
                    </div>
                    <div class="progress mt-1" style="height:4px;background:#fef3c7;">
                        <div class="progress-bar"
                             style="width:{{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->saldoPendiente / $balanceFacturas->totalFacturas) * 100 : 0 }}%;background:linear-gradient(90deg,#f59e0b,#d97706);"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Monto pendiente por pagar</small>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABS CARD --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-white pb-0 pt-3 px-4">
                <ul class="nav nav-tabs border-0" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estado-cuenta-tab" data-bs-toggle="tab"
                                data-bs-target="#estado-cuenta" type="button" role="tab">
                            <i class="bi bi-graph-up me-1"></i>Estado de Cuenta
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="facturas-tab" data-bs-toggle="tab"
                                data-bs-target="#facturas" type="button" role="tab">
                            <i class="bi bi-file-earmark-text me-1"></i>Facturas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pagos-tab" data-bs-toggle="tab"
                                data-bs-target="#pagos" type="button" role="tab">
                            <i class="bi bi-cash-stack me-1"></i>Pagos
                        </button>
                    </li>
                    @if($proveedor->Tipo == 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="productos-tab" data-bs-toggle="tab"
                                data-bs-target="#productos" type="button" role="tab">
                            <i class="bi bi-boxes me-1"></i>Productos
                        </button>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="myTabContent">

                    {{-- ============================================ --}}
                    {{-- TAB: ESTADO DE CUENTA --}}
                    {{-- ============================================ --}}
                    <div class="tab-pane fade show active" id="estado-cuenta" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="pdfTablaEstadoCuenta()">
                                <i class="bi bi-printer me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="exportarExcelEstadoCuenta()">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaEstadoCuenta">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO USD</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PAGO USD</th>
                                        <th class="pe-3 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SALDO USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($estadoCuenta['operaciones'] as $op)
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3">{{ $op->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            @if($op->tipo == 'factura')
                                                <i class="bi bi-file-earmark-text text-primary me-1"></i>
                                            @else
                                                <i class="bi bi-cash text-success me-1"></i>
                                            @endif
                                            {{ $op->descripcion }}
                                        </td>
                                        <td>{{ $op->referencia }}</td>
                                        <td class="text-end">
                                            @if($op->monto_divisa > 0)
                                                <span class="fw-semibold text-dark">$ {{ number_format($op->monto_divisa, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($op->pago_divisa > 0)
                                                <span class="fw-semibold text-success">$ {{ number_format($op->pago_divisa, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 text-end fw-bold text-dark">
                                            $ {{ number_format($op->saldo_divisa, 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox me-2"></i>No hay operaciones registradas
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f0fdf4;border-top:2px solid #bbf7d0;">
                                        <td colspan="3" class="ps-3 py-2 text-end fw-bold text-dark">TOTALES:</td>
                                        <td class="py-2 text-end fw-bold text-dark">$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</td>
                                        <td class="py-2 text-end fw-bold text-success">$ {{ number_format($balanceFacturas->totalPagado, 2) }}</td>
                                        <td class="pe-3 py-2 text-end fw-bold text-dark">$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- TAB: FACTURAS --}}
                    {{-- ============================================ --}}
                    <div class="tab-pane fade" id="facturas" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <a href="{{ route('cpanel.proveedores.recibo-facturas', $proveedor->ProveedorId) }}"
                               class="btn btn-sm" target="_blank"
                               style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                               title="Generar recibo de facturas en PDF" data-bs-toggle="tooltip">
                                <i class="bi bi-printer me-1"></i>PDF
                            </a>
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="exportarExcelFacturas()">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaFacturas">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NÚMERO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA EMISIÓN</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TOTAL USD</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PAGADO USD</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SALDO USD</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                        <th class="pe-3 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($facturasVigentes as $factura)
                                    @php
                                        $estatusTexto = '';
                                        $badgeStyle   = '';
                                        switch($factura->Estatus) {
                                            case 1:
                                                $estatusTexto = 'En Proceso';
                                                $badgeStyle   = 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)';
                                                break;
                                            case 2:
                                                $estatusTexto = 'Recibiendo';
                                                $badgeStyle   = 'background:rgba(6,182,212,0.1);color:#0c4a6e;border:1px solid rgba(6,182,212,0.25)';
                                                break;
                                            case 4:
                                                $estatusTexto = 'Recibida';
                                                $badgeStyle   = 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)';
                                                break;
                                            default:
                                                $estatusTexto = 'Desconocido';
                                                $badgeStyle   = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                        }
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3 fw-bold text-dark">{{ $factura->Numero }}</td>
                                        <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}</td>
                                        <td class="text-end fw-semibold text-dark">$ {{ number_format($factura->MontoDivisa, 2) }}</td>
                                        <td class="text-end fw-semibold text-success">$ {{ number_format($factura->total_pagado, 2) }}</td>
                                        <td class="text-end fw-bold text-dark">$ {{ number_format($factura->saldo_pendiente, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                                  style="{{ $badgeStyle }};font-size:0.75rem;">
                                                {{ $estatusTexto }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <a href="{{ route('cpanel.facturas.detalle', $factura->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                                   title="Ver detalle" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                                </a>
                                                <a href="{{ route('cpanel.facturas.editar', $factura->ID) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                   title="Editar factura" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                                @if($factura->saldo_pendiente == 0 || $factura->Estatus == 1)
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarFactura({{ $factura->ID }}, '{{ $factura->Numero }}')"
                                                        title="Eliminar factura" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                                @else
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(107,114,128,0.06);color:#9ca3af;border:1px solid rgba(107,114,128,0.15);cursor:not-allowed;"
                                                        disabled
                                                        title="No se puede eliminar (tiene saldo pendiente)" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;opacity:0.5;"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-file-earmark-x me-2"></i>No hay facturas registradas
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- TAB: PAGOS --}}
                    {{-- ============================================ --}}
                    <div class="tab-pane fade" id="pagos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="pdfTablaPagos()">
                                <i class="bi bi-printer me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="exportarExcelPagos()">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaPagos">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° OPERACIÓN</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO USD</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO BS</th>
                                        <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TASA</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                        <th class="pe-3 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:140px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transaccionesVigentes as $transaccion)
                                    @php
                                        $estatusTexto  = '';
                                        $pagosBadge    = '';
                                        $estatusNumero = (int)($transaccion->Estatus ?? 0);
                                        switch($estatusNumero) {
                                            case 2:
                                                $estatusTexto = 'Pagada';
                                                $pagosBadge   = 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)';
                                                break;
                                            case 4:
                                                $estatusTexto = 'Cerrada';
                                                $pagosBadge   = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                                break;
                                            default:
                                                $estatusTexto = 'Pendiente';
                                                $pagosBadge   = 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)';
                                        }
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3">{{ \Carbon\Carbon::parse($transaccion->Fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $transaccion->Descripcion ?? 'Pago registrado' }}</td>
                                        <td>
                                            @if($transaccion->NumeroOperacion)
                                                <code class="px-2 py-1 rounded-2"
                                                      style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $transaccion->NumeroOperacion }}</code>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-dark">$ {{ number_format($transaccion->MontoDivisa, 2) }}</td>
                                        <td class="text-end text-muted">Bs {{ number_format($transaccion->MontoBs, 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($transaccion->Tasa, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                                  style="{{ $pagosBadge }};font-size:0.75rem;">
                                                {{ $estatusTexto }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <a href="{{ route('cpanel.pagos.detalle', $transaccion->TransaccionId) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                                   title="Ver detalle" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                                </a>
                                                @if(in_array($estatusNumero, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $transaccion->TransaccionId) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                   title="Editar pago" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                                @else
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(107,114,128,0.06);color:#9ca3af;border:1px solid rgba(107,114,128,0.15);cursor:not-allowed;"
                                                        disabled
                                                        title="No se puede editar (Pago {{ $estatusTexto }})" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;opacity:0.5;"></i>
                                                </button>
                                                @endif
                                                <a href="{{ route('cpanel.pagos.imprimir', $transaccion->TransaccionId) }}"
                                                   class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                   style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                   title="Imprimir recibo" target="_blank" data-bs-toggle="tooltip">
                                                    <i class="bi bi-printer" style="font-size:0.8rem;"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarPago({{ $transaccion->TransaccionId }}, '{{ $transaccion->NumeroOperacion }}')"
                                                        title="Eliminar pago" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-cash-stack me-2"></i>No hay pagos registrados
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- TAB: PRODUCTOS --}}
                    {{-- ============================================ --}}
                    @if($proveedor->Tipo == 0)
                    <div class="tab-pane fade" id="productos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(107,114,128,0.08);color:#374151;border:1px solid #e2e8f0;font-size:0.8rem;"
                                    onclick="pdfTablaProductos()">
                                <i class="bi bi-printer me-1"></i>PDF
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
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">FOTO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">CÓDIGO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">REFERENCIA</th>
                                        <th class="pe-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NOMBRE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productos as $producto)
                                    @php
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/items/thumbs/',
                                            $producto->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-3">
                                            <img src="{{ $imgSrc }}"
                                                 alt="{{ $producto->Codigo }}"
                                                 class="rounded img-zoomable"
                                                 style="width:46px;height:46px;object-fit:cover;border:1px solid #e2e8f0;cursor:zoom-in;"
                                                 onclick="zoomImagen(this)"
                                                 data-full-image="{{ $imgSrc }}"
                                                 data-description="{{ $producto->Nombre }}">
                                        </td>
                                        <td>
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $producto->Codigo ?? 'N/A' }}</code>
                                        </td>
                                        <td class="text-muted" style="font-size:0.88rem;">{{ $producto->Referencia ?? 'N/A' }}</td>
                                        <td class="pe-3" style="word-wrap:break-word;white-space:normal;">{{ $producto->Nombre }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-boxes me-2"></i>No hay productos registrados
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2 pt-2" style="border-top:1px solid #f1f5f9;">
                            <small class="text-muted">
                                <i class="bi bi-boxes me-1"></i>
                                Total productos: <strong>{{ $productos->count() }}</strong>
                            </small>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

{{-- Overlay zoom (usado por zoomImagen / closeZoom en JS) --}}
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display:none;" onclick="closeZoom()">
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


    // ============================================
    // FUNCIONES PARA FACTURAS
    // ============================================

    // Ver detalle de factura
    function verDetalleFactura(facturaId) {
        // Puedes abrir un modal con los detalles o redirigir a otra página
        Swal.fire({
            title: 'Cargando...',
            text: 'Obteniendo detalles de la factura',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                // Llamada AJAX para obtener los detalles
                fetch(`/cpanel/facturas/${facturaId}/detalle`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar modal con los detalles
                        mostrarModalDetalleFactura(data.factura);
                    } else {
                        Swal.fire('Error', data.message || 'Error al cargar los detalles', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // Editar factura
    function editarFactura(facturaId) {
        Swal.fire({
            title: '¿Editar factura?',
            text: "Podrás modificar los datos de la factura",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, editar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir a la página de edición
                window.location.href = `/cpanel/facturas/${facturaId}/editar`;
            }
        });
    }

    // ============================================
    // FUNCIÓN PARA ELIMINAR FACTURA
    // ============================================

    function eliminarFactura(facturaId, facturaNumero) {
        Swal.fire({
            title: '¿Eliminar factura?',
            html: `Estás a punto de eliminar la factura <strong>${facturaNumero}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Llamada AJAX para eliminar
                fetch(`/cpanel/facturas/${facturaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminada!',
                            text: 'La factura ha sido eliminada correctamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página para actualizar la lista
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar la factura', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // Modal para mostrar detalle de factura (opcional)
    function mostrarModalDetalleFactura(factura) {
        // Puedes implementar un modal bonito con Bootstrap
        // Por ahora usamos SweetAlert2 con HTML
        let htmlContent = `
            <div style="text-align: left;">
                <p><strong>Número:</strong> ${factura.Numero}</p>
                <p><strong>Fecha:</strong> ${new Date(factura.FechaCreacion).toLocaleDateString('es-VE')}</p>
                <p><strong>Total USD:</strong> $ ${Number(factura.MontoDivisa).toFixed(2)}</p>
                <p><strong>Pagado:</strong> $ ${Number(factura.total_pagado).toFixed(2)}</p>
                <p><strong>Saldo Pendiente:</strong> $ ${Number(factura.saldo_pendiente).toFixed(2)}</p>
                <p><strong>Estatus:</strong> ${getEstatusTexto(factura.Estatus)}</p>
            </div>
        `;

        Swal.fire({
            title: 'Detalle de Factura',
            html: htmlContent,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // Helper para obtener texto de estatus
    function getEstatusTexto(estatus) {
        switch(estatus) {
            case 1: return 'En Proceso';
            case 2: return 'Recibiendo';
            case 4: return 'Recibida';
            default: return 'Desconocido';
        }
    }

    // ============================================
    // FUNCIÓN PARA ELIMINAR PAGO
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
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/cpanel/pagos/${pagoId}`, {
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
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El pago ha sido eliminado correctamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
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
    /* Nav tabs */
    #myTab .nav-link {
        color: #6b7280;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.6rem 1rem;
        font-size: 0.88rem;
        background: transparent;
    }
    #myTab .nav-link:hover { color: #1d4ed8; border-bottom-color: #bfdbfe; }
    #myTab .nav-link.active { color: #1d4ed8; font-weight: 600; border-bottom: 3px solid #3b82f6; }

    /* Tablas */
    #tablaEstadoCuenta tbody tr:hover,
    #tablaFacturas tbody tr:hover,
    #tablaPagos tbody tr:hover,
    #tablaProductos tbody tr:hover { background: #f8fafc; }

    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Overlay zoom */
    .image-zoom-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.9); z-index: 9999;
        justify-content: center; align-items: center;
        animation: fadeInOverlay 0.3s ease-out;
    }
    .image-zoom-container {
        position: relative; max-width: 90%; max-height: 90%;
        display: flex; flex-direction: column; align-items: center;
    }
    .image-zoom-container img {
        max-width: 100%; max-height: 80vh; object-fit: contain;
        border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        animation: zoomInImage 0.3s ease-out;
    }
    .image-zoom-close {
        position: absolute; top: -40px; right: -10px;
        color: #fff; font-size: 40px; font-weight: bold;
        cursor: pointer; z-index: 10000;
        background: rgba(0,0,0,0.5); width: 50px; height: 50px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        transition: color 0.2s, background 0.2s;
    }
    .image-zoom-close:hover { color: #ff6b6b; background: rgba(0,0,0,0.7); }
    .image-description {
        color: #fff; text-align: center; margin-top: 16px;
        font-size: 1rem; background: rgba(0,0,0,0.7);
        padding: 8px 20px; border-radius: 8px; max-width: 80%;
    }
    @keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }
    @keyframes zoomInImage {
        from { transform: scale(0.8); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
</style>
@endpush
