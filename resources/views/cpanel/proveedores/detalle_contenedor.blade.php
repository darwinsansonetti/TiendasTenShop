@extends('layout.layout_dashboard')

@section('title', 'Detalle del Contenedor')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                  <i class="bi bi-box-seam text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle del Contenedor</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $contenedor->Nombre }}</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}">Contenedores</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $contenedor->Nombre }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Información General -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información del Contenedor
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.contenedores.editar', $contenedor->Id) }}"
                           class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}"
                           class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">

                {{-- Datos generales --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                        <p class="mb-0 fw-bold text-dark">{{ $contenedor->Nombre }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">N° Operación</p>
                        <p class="mb-0 fw-semibold">{{ $contenedor->NumeroOperacion ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">Estatus</p>
                        <span class="badge rounded-pill bg-{{ $estatusContenedor['clase'] }} px-3 py-2" style="font-size:0.8rem;">
                            {{ $estatusContenedor['texto'] }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">País de Origen</p>
                        <p class="mb-0 fw-semibold">
                            @if($contenedor->Origen)
                                <i class="bi bi-geo-alt text-muted me-1"></i>{{ $contenedor->Origen }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">Fecha de Creación</p>
                        <p class="mb-0 fw-semibold">
                            <i class="bi bi-calendar text-muted me-1"></i>
                            {{ \Carbon\Carbon::parse($contenedor->FechaCreacion)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:.05em;font-weight:600;">Fecha de Recepción</p>
                        <p class="mb-0 fw-semibold">
                            @if($contenedor->FechaRecepcion)
                                <i class="bi bi-calendar-check text-muted me-1"></i>
                                {{ \Carbon\Carbon::parse($contenedor->FechaRecepcion)->format('d/m/Y') }}
                            @else
                                <span class="badge rounded-pill text-bg-warning" style="font-size:0.75rem;">Pendiente</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr class="my-3" style="border-color:#f1f5f9;">

                {{-- KPI financieros --}}
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                    <i class="bi bi-truck text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Flete</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($contenedor->Flete ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                    <i class="bi bi-shield-check text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Aduana</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($contenedor->Aduana ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                                    <i class="bi bi-calculator text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Gastos</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-primary">$ {{ number_format(($contenedor->Flete ?? 0) + ($contenedor->Aduana ?? 0), 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#10b981,#059669);">
                                    <i class="bi bi-percent text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">% Gastos</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-success">{{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }}%</h5>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#ef4444,#dc2626);">
                                    <i class="bi bi-receipt text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Facturas</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">$ {{ number_format($contenedor->MontoTotalFacturas ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Facturas Asociadas -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-file-text me-2"></i>Facturas Asociadas
                    </h6>
                    <span class="badge bg-white text-primary fw-semibold" style="font-size:0.75rem;">
                        {{ $facturas->count() }} factura{{ $facturas->count() != 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($facturas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° FACTURA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">MONTO USD</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturas as $factura)
                            @php
                                $estatusFactura = match($factura->Estatus) {
                                    1 => ['texto' => 'En Proceso', 'clase' => 'warning'],
                                    2 => ['texto' => 'Recibiendo', 'clase' => 'info'],
                                    4 => ['texto' => 'Recibida', 'clase' => 'success'],
                                    3 => ['texto' => 'Pagada', 'clase' => 'success'],
                                    0 => ['texto' => 'Anulada', 'clase' => 'danger'],
                                    default => ['texto' => 'Desconocido', 'clase' => 'secondary']
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark"># {{ $factura->Numero }}</span>
                                </td>
                                <td>
                                    <i class="bi bi-calendar3 text-muted me-1" style="font-size:0.8rem;"></i>
                                    {{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    $ {{ number_format($factura->MontoDivisa ?? 0, 2) }}
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $estatusFactura['clase'] }}" style="font-size:0.78rem;">
                                        {{ $estatusFactura['texto'] }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('cpanel.facturas.detalle', $factura->ID) }}"
                                       class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                       style="width:30px;height:30px;background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);"
                                       title="Ver detalle de factura" data-bs-toggle="tooltip">
                                        <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;">
                                <td colspan="2" class="ps-4 py-3 text-end fw-bold text-muted" style="font-size:0.85rem;">TOTAL</td>
                                <td class="py-3 text-end fw-bold text-dark">$ {{ number_format($facturas->sum('MontoDivisa'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="py-5 text-center">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:56px;height:56px;background:rgba(59,130,246,0.08);">
                            <i class="bi bi-file-earmark-x text-primary" style="font-size:1.6rem;opacity:.5;"></i>
                        </div>
                        <p class="text-muted mb-0" style="font-size:0.9rem;">No hay facturas asociadas a este contenedor</p>
                    </div>
                </div>
                @endif
            </div>
            @if($facturas->count() > 0)
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Total acumulado: <strong>$ {{ number_format($facturas->sum('MontoDivisa'), 2) }}</strong>
                    en {{ $facturas->count() }} factura{{ $facturas->count() != 1 ? 's' : '' }}
                </small>
            </div>
            @endif
        </div>
        
    </div>
</div>

@endsection