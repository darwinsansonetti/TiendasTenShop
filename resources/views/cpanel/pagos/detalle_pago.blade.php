@extends('layout.layout_dashboard')

@section('title', 'Detalle de Pago')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-cash-stack text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Pago</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Información del pago #{{ $pago->NumeroOperacion }}</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    @if($proveedor)
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}">
                            {{ $proveedor->Nombre }}
                        </a>
                    </li>
                    @endif
                    <li class="breadcrumb-item active">Pago #{{ $pago->NumeroOperacion }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información del Pago
                    </h6>
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId ?? 0) }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body py-4">

                {{-- Fila 1: datos del documento --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">N° Operación</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size:1rem;">{{ $pago->NumeroOperacion }}</p>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha</p>
                        <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Estatus</p>
                        @php
                            $estatusMap = match($estatusPago['clase']) {
                                'success'   => ['bg' => 'rgba(16,185,129,0.1)',  'color' => '#059669',  'border' => 'rgba(16,185,129,0.25)'],
                                'warning'   => ['bg' => 'rgba(245,158,11,0.1)',  'color' => '#92400e',  'border' => 'rgba(245,158,11,0.25)'],
                                'secondary' => ['bg' => 'rgba(107,114,128,0.1)', 'color' => '#374151',  'border' => 'rgba(107,114,128,0.25)'],
                                'info'      => ['bg' => 'rgba(6,182,212,0.1)',   'color' => '#0c4a6e',  'border' => 'rgba(6,182,212,0.25)'],
                                default     => ['bg' => 'rgba(107,114,128,0.1)', 'color' => '#374151',  'border' => 'rgba(107,114,128,0.25)'],
                            };
                        @endphp
                        <span class="badge rounded-pill px-3 py-2 fw-semibold"
                              style="background:{{ $estatusMap['bg'] }};color:{{ $estatusMap['color'] }};border:1px solid {{ $estatusMap['border'] }};font-size:0.8rem;">
                            {{ $estatusPago['texto'] }}
                        </span>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Forma de Pago</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $formaPagoTexto }}</p>
                    </div>
                    <div class="col-md-8 col-12">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Descripción</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $pago->Descripcion ?? 'Sin descripción' }}</p>
                    </div>
                </div>

                {{-- KPI financieros --}}
                <hr style="border-color:#f1f5f9;">
                <div class="row g-3 mt-1">
                    {{-- Monto USD (destacado) --}}
                    <div class="col-md-4 col-12">
                        <div class="rounded-3 p-3 h-100" style="background:linear-gradient(135deg,#10b981,#059669);border:1px solid #059669;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:rgba(255,255,255,0.2);">
                                    <i class="bi bi-currency-dollar text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;color:rgba(255,255,255,0.85);">Monto USD</p>
                            </div>
                            <h4 class="mb-0 fw-bold text-white">$ {{ number_format($pago->MontoDivisaAbonado ?? 0, 2) }}</h4>
                        </div>
                    </div>
                    {{-- Monto Bs --}}
                    <div class="col-md-4 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                    <i class="bi bi-cash text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Monto Bs</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">Bs {{ number_format($pago->MontoAbonado ?? 0, 2) }}</h5>
                        </div>
                    </div>
                    {{-- Tasa de Cambio --}}
                    <div class="col-md-4 col-6">
                        <div class="rounded-3 p-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                    <i class="bi bi-arrow-left-right text-white" style="font-size:0.75rem;"></i>
                                </div>
                                <p class="text-uppercase text-muted mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Tasa de Cambio</p>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">{{ number_format($pago->TasaDeCambio ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>

                {{-- Factura asociada --}}
                @if($factura)
                <hr style="border-color:#f1f5f9;" class="mt-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mt-2"
                     style="background:#eff6ff;border:1px solid #bfdbfe;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="bi bi-file-earmark-text text-white" style="font-size:0.9rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-uppercase mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;color:#1d4ed8;">Factura Asociada</p>
                        <p class="mb-0 fw-semibold text-dark">Factura #{{ $factura->Numero }}</p>
                        <small class="text-muted">Este pago se aplicó a la factura #{{ $factura->Numero }}</small>
                    </div>
                </div>
                @endif

                {{-- Comprobante de pago --}}
                @if($pago->UrlComprobante)
                @php
                    $comprobanteSrc = FileHelper::getOrDownloadFile(
                        'images/comprobantes/',
                        $pago->UrlComprobante,
                        'assets/img/adminlte/img/no-image.png'
                    );
                @endphp
                <hr style="border-color:#f1f5f9;" class="mt-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mt-2"
                     style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-file-earmark-image text-white" style="font-size:0.9rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-uppercase mb-0" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;color:#7c3aed;">Comprobante</p>
                        <p class="mb-0 fw-semibold text-dark">Comprobante de pago adjunto</p>
                    </div>
                    <a href="{{ route('cpanel.pagos.ver-comprobante', $pago->ID) }}"
                       target="_blank"
                       class="btn btn-sm rounded-2 fw-semibold flex-shrink-0"
                       style="background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);font-size:0.8rem;">
                        <i class="bi bi-eye me-1"></i>Ver Comprobante
                    </a>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection
