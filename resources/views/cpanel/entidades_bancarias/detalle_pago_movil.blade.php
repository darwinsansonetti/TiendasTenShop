@extends('layout.layout_dashboard')

@section('title', 'Detalle Pago Móvil')

@php
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'credit-card';
    $hdrTitle = 'Detalle del Pago Móvil';
    $hdrSubtitle = 'Información del pago verificado';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.pago.movil.index') }}">Pago Móvil</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-receipt me-2"></i>Referencia: {{ $pago->Referencia }}
                    </h6>
                    <a href="{{ route('cpanel.pago.movil.index') }}" class="btn btn-light btn-sm fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="text-center mb-4">
                    <span class="badge bg-success" style="font-size:0.9rem;padding:0.6rem 1.2rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Pago Verificado
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-person me-1"></i>Datos del Pagador
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted" style="width:45%;">Cédula</td><td class="fw-semibold">{{ $pago->CedulaPagador }}</td></tr>
                                    <tr><td class="text-muted">Teléfono</td><td class="fw-semibold">{{ $pago->TelefonoPagador }}</td></tr>
                                    <tr><td class="text-muted">Banco Origen</td><td class="fw-semibold">{{ $pago->BancoOrigen }} - {{ $pago->BancoOrigenNombre }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-cash-coin me-1"></i>Datos del Pago
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted" style="width:45%;">Referencia</td><td class="fw-semibold">{{ $pago->Referencia }}</td></tr>
                                    <tr><td class="text-muted">Fecha del Pago</td><td class="fw-semibold">{{ $pago->FechaPagoFormateada }}</td></tr>
                                    <tr><td class="text-muted">Monto</td><td class="fw-bold text-success">VES {{ number_format($pago->Importe, 2) }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-building me-1"></i>Comercio
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted" style="width:45%;">Sucursal</td><td class="fw-semibold">{{ $pago->sucursal_nombre }}</td></tr>
                                    <tr><td class="text-muted">Alias</td><td class="fw-semibold">{{ $pago->alias_pago_movil ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">RIF</td><td class="fw-semibold">{{ $pago->config_rif ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Teléfono Destino</td><td class="fw-semibold">{{ $pago->TelefonoDestino }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-clock-history me-1"></i>Verificación
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted" style="width:45%;">Fecha Verificación</td><td class="fw-semibold">{{ $pago->FechaVerificacionFormateada }}</td></tr>
                                    <tr><td class="text-muted">Status BDV</td><td class="fw-semibold"><code>{{ $pago->StatusBdv }}</code></td></tr>
                                    <tr><td class="text-muted">Mensaje</td><td class="fw-semibold">{{ $pago->MensajeBdv ?? '—' }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection