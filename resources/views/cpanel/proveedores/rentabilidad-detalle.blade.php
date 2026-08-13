@extends('layout.layout_dashboard')

@section('title', 'Detalle de Rentabilidad - ' . ($proveedor->Nombre ?? 'Proveedor'))

@php
    use Carbon\Carbon;
    use App\Helpers\FileHelper;
    
    $hdrBg = 'linear-gradient(135deg,#14b8a6,#0d9488)';
    $hdrIcon = 'cash-stack';
    
    $rentabilidadColor = ($rentabilidadGeneral ?? 0) >= 30 ? '#22c55e' : (($rentabilidadGeneral ?? 0) >= 10 ? '#eab308' : '#ef4444');
    
    $imgSrc = FileHelper::getOrDownloadFile(
        'images/proveedores/',
        $proveedor->UrlImagen ?? '',
        'assets/img/adminlte/img/proveedor_default.png'
    );
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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Rentabilidad: {{ $proveedor->Nombre ?? 'N/A' }}
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Detalle de rentabilidad por sucursal
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.rentabilidad', [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin
                    ]) }}">Rentabilidad</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $proveedor->Nombre ?? 'Detalle' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc }}"
                             alt="{{ $proveedor->Nombre }}"
                             class="rounded-circle"
                             style="width:80px;height:80px;object-fit:cover;border:3px solid #e2e8f0;">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Código</small>
                                <strong>{{ $proveedor->ProveedorId }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Nombre</small>
                                <strong>{{ $proveedor->Nombre ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">RIF / Cédula</small>
                                <strong>{{ $proveedor->RifCedula ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Período</small>
                                <strong style="font-size:0.8rem;">
                                    {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS GENERALES --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(20,184,166,0.12);">
                                <i class="bi bi-cart-check text-teal" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">TOTAL COMPRAS</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalCosto ?? 0, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(34,197,94,0.12);">
                                <i class="bi bi-cash-stack text-success" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">TOTAL VENTAS</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalVentas ?? 0, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(245,158,11,0.12);">
                                <i class="bi bi-graph-up-arrow text-warning" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">UTILIDAD</small>
                                <h6 class="mb-0 fw-bold" style="color:{{ ($totalUtilidad ?? 0) >= 0 ? '#22c55e' : '#ef4444' }};">
                                    ${{ number_format($totalUtilidad ?? 0, 2) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(20,184,166,0.12);">
                                <i class="bi bi-percent text-teal" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">RENTABILIDAD</small>
                                <h6 class="mb-0 fw-bold" style="color:{{ $rentabilidadColor }};">
                                    {{ number_format($rentabilidadGeneral ?? 0, 2) }}%
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- SUCURSALES (UNA POR FILA) --}}
        {{-- ================================================ --}}
        <h5 class="fw-bold mb-3">
            <i class="bi bi-building me-2 text-teal"></i>Rentabilidad por Sucursal
        </h5>

        @if($sucursalesData && count($sucursalesData) > 0)
            @foreach($sucursalesData as $sucursal)
                @php
                    $sucColor = ($sucursal->Rentabilidad ?? 0) >= 30 ? '#22c55e' : (($sucursal->Rentabilidad ?? 0) >= 10 ? '#eab308' : '#ef4444');
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <h6 class="mb-0 fw-bold text-white">
                                    <i class="bi bi-shop me-2"></i>
                                    {{ $sucursal->SucursalNombre ?? 'Sucursal ' . $sucursal->SucursalId }}
                                </h6>
                                <span class="badge rounded-pill" style="background:{{ $sucColor }};color:#fff;font-size:0.75rem;">
                                    Rentabilidad: {{ number_format($sucursal->Rentabilidad ?? 0, 2) }}%
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle.sucursal', [
                                    'proveedorId' => $proveedor->ProveedorId, 
                                    'sucursalId' => $sucursal->SucursalId
                                ]) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                                   class="btn btn-sm text-white"
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-box-seam me-1"></i> Ver Productos
                                </a>
                                <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.estadisticas', [
                                    'proveedorId' => $proveedor->ProveedorId, 
                                    'sucursalId' => $sucursal->SucursalId
                                ]) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                                   class="btn btn-sm text-white"
                                   style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 border-end">
                                    <small class="text-muted d-block">Ventas</small>
                                    <h5 class="mb-0 fw-bold">${{ number_format($sucursal->TotalVentas ?? 0, 2) }}</h5>
                                    <small class="text-muted">{{ number_format($sucursal->NumeroVentas ?? 0, 0) }} transacciones</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border-end">
                                    <small class="text-muted d-block">Compras</small>
                                    <h5 class="mb-0 fw-bold">${{ number_format($sucursal->TotalCosto ?? 0, 2) }}</h5>
                                    <small class="text-muted">{{ number_format($sucursal->TotalCantidad ?? 0, 0) }} unidades</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border-end">
                                    <small class="text-muted d-block">Utilidad</small>
                                    <h5 class="mb-0 fw-bold" style="color:{{ ($sucursal->TotalUtilidad ?? 0) >= 0 ? '#22c55e' : '#ef4444' }};">
                                        ${{ number_format($sucursal->TotalUtilidad ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted">
                                        {{ ($sucursal->TotalUtilidad ?? 0) >= 0 ? 'Ganancia' : 'Pérdida' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <small class="text-muted d-block">Rentabilidad</small>
                                    <h5 class="mb-0 fw-bold" style="color:{{ $sucColor }};">
                                        {{ number_format($sucursal->Rentabilidad ?? 0, 2) }}%
                                    </h5>
                                    <small class="text-muted">
                                        {{ $sucursal->Rentabilidad >= 30 ? 'Excelente' : ($sucursal->Rentabilidad >= 10 ? 'Buena' : 'Baja') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        {{-- Barra de progreso de rentabilidad --}}
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Rentabilidad</small>
                                <small class="fw-bold" style="color:{{ $sucColor }};">
                                    {{ number_format($sucursal->Rentabilidad ?? 0, 2) }}%
                                </small>
                            </div>
                            <div class="progress" style="height:8px;border-radius:9999px;background:#e5e7eb;">
                                @php
                                    $progressWidth = min(($sucursal->Rentabilidad ?? 0), 100);
                                @endphp
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $progressWidth }}%; background: {{ $sucColor }}; border-radius:9999px;"
                                     aria-valuenow="{{ $progressWidth }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">0%</small>
                                <small class="text-muted">30%</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
                    <p class="text-muted mt-2">No hay ventas para este proveedor en el período seleccionado</p>
                </div>
            </div>
        @endif

        {{-- ================================================ --}}
        {{-- BOTÓN VOLVER --}}
        {{-- ================================================ --}}
        <div class="mt-3">
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad', [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]) }}" 
               class="btn btn-light border fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection

@push('styles')
<style>
    .text-teal { color: #14b8a6; }
    .border-end {
        border-right: 1px solid #e5e7eb !important;
    }
    .progress {
        background: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
        border-radius: 9999px;
    }
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush