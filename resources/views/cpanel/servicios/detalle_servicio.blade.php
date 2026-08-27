@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Detalle Servicio')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = 'info-circle';
    $hdrTitle = 'Detalle del Servicio';
    $hdrSubtitle = 'Información completa del servicio';
    
    $monedaLabels = [
        0 => 'Divisas',
        1 => 'Bolívares'
    ];
    
    $estatusLabels = [
        0 => 'Activo',
        1 => 'Inactivo'
    ];
    
    $estatusBadge = [
        0 => 'success',
        1 => 'danger'
    ];
@endphp

@section('content')

{{-- HEADER --}}
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.servicios.listado') }}">Proveedores</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.servicios.detalle.seleccion', $proveedor->ProveedorId ?? 0) }}">
                            Servicios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- CONTENIDO --}}
<div class="app-content">
    <div class="container-fluid">

        {{-- Información del Proveedor --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Proveedor: {{ $proveedor->Nombre ?? 'N/A' }}
                    </h6>
                    <div>
                        <a href="{{ route('cpanel.proveedores.servicios.detalle.seleccion', $proveedor->ProveedorId ?? 0) }}" 
                           class="btn btn-sm text-white" 
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-arrow-left me-1"></i>Volver a servicios
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc ?? asset('assets/img/adminlte/img/proveedor_default.png') }}"
                             alt="{{ $proveedor->Nombre ?? 'Proveedor' }}"
                             class="rounded-circle"
                             style="width:80px;height:80px;object-fit:cover;border:3px solid #e2e8f0;">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">RIF / Cédula</small>
                                <p class="mb-0 fw-semibold">{{ $proveedor->Rif_Cedula ?? $proveedor->Rif ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Teléfono</small>
                                <p class="mb-0 fw-semibold">{{ $proveedor->TelefonoMovil ?? $proveedor->Telefono1 ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Correo</small>
                                <p class="mb-0 fw-semibold">{{ $proveedor->CorreoElectronico ?? $proveedor->Email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detalle del Servicio --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-{{ $hdrIcon }} me-2"></i>
                    Detalle del Servicio
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Número</label>
                            <p class="fw-bold text-dark fs-5">
                                <code class="px-3 py-2 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:1rem;">
                                    {{ $servicio->Numero ?? 'N/A' }}
                                </code>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Estatus</label>
                            <p>
                                <span class="badge bg-{{ $estatusBadge[$servicio->Estatus ?? 0] }}" style="font-size:0.9rem;">
                                    {{ $estatusLabels[$servicio->Estatus ?? 0] }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Descripción</label>
                            <p class="fw-semibold text-dark">{{ $servicio->Descripcion ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Moneda Principal</label>
                            <p class="fw-semibold text-dark">{{ $monedaLabels[$servicio->MonedaPrincipal ?? 0] }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Tarifa Divisa</label>
                            <p class="fw-semibold text-dark">${{ number_format($servicio->MontoDivisa ?? 0, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Tarifa Bs.</label>
                            <p class="fw-semibold text-dark">Bs. {{ number_format($servicio->Monto ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Sucursal</label>
                            <p class="fw-semibold text-dark">{{ $servicio->sucursal_nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Fecha de Creación</label>
                            <p class="fw-semibold text-dark">
                                {{ isset($servicio->FechaCreacion) ? Carbon::parse($servicio->FechaCreacion)->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <a href="{{ route('cpanel.servicios.editar', $servicio->ServiciosPlantillaId) }}" 
                       class="btn px-4 fw-semibold text-white" 
                       style="background:{{ $hdrBg }};border:none;">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                    <a href="{{ route('cpanel.proveedores.servicios.detalle.seleccion', $proveedor->ProveedorId ?? 0) }}" 
                       class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection