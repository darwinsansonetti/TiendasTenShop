@extends('layout.layout_dashboard')

@section('title', 'Detalle del Punto de Venta')

@php
    $hdrBg = 'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)';
    $hdrIcon = 'eye';
    $hdrTitle = 'Detalle del Punto de Venta';
    $hdrSubtitle = 'Información del punto de venta';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.puntos.index') }}">Puntos de Venta</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-info-circle me-2"></i>Información del Punto de Venta
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">ID</p>
                        <p class="fw-bold text-dark">
                            <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#1d4ed8;">
                                {{ $punto->PuntoDeVentaId }}
                            </code>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Estatus</p>
                        <p>
                            <span class="badge bg-{{ $punto->EstatusBadge }}" style="font-size:0.9rem;">
                                {{ $punto->EstatusTexto }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Descripción</p>
                        <p class="fw-bold text-dark">{{ $punto->Descripcion ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Banco</p>
                        <p class="fw-bold text-dark">{{ $punto->banco_nombre ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Sucursal</p>
                        <p class="fw-bold text-dark">{{ $punto->sucursal_nombre ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Serial</p>
                        <p class="fw-bold text-dark">{{ $punto->Serial ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Código</p>
                        <p class="fw-bold text-dark">{{ $punto->Codigo ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                    <a href="{{ route('cpanel.puntos.index') }}" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <a href="{{ route('cpanel.puntos.editar', $punto->PuntoDeVentaId) }}" 
                       class="btn px-4 fw-semibold text-white" 
                       style="background:{{ $hdrBg }};border:none;">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush