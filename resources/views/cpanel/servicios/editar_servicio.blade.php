@extends('layout.layout_dashboard')

@section('title', 'Editar Factura de Servicio')

@php
    $hdrBg = 'linear-gradient(135deg,#10b981,#059669)';
    $hdrIcon = 'pencil';
    $hdrTitle = 'Editar Factura de Servicio';
    $hdrSubtitle = 'Factura: ' . ($factura->Numero ?? '');
    
    $estatusBadge = [
        0 => 'danger',
        1 => 'warning',
        2 => 'info',
        3 => 'success',
        4 => 'primary'
    ];
    
    $estatusLabels = [
        0 => 'Anulada',
        1 => 'En Proceso',
        2 => 'Recibiendo',
        3 => 'Pagada',
        4 => 'Recibida'
    ];
    
    $monedaLabels = [
        0 => 'Divisas',
        1 => 'Bolívares'
    ];
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.servicios.listado') }} ">Facturas</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.servicios.detalle', $factura->ID) }}">Detalle</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
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
                    <i class="bi bi-pencil me-2"></i>Editar Estatus de Factura
                </h6>
            </div>
            <div class="card-body">

                {{-- ================================================ --}}
                {{-- INFORMACIÓN DE LA FACTURA (SOLO LECTURA) --}}
                {{-- ================================================ --}}
                <div class="row g-3 mb-4">
                    
                    {{-- Número --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Número</label>
                        <p class="fw-bold text-dark">
                            <code class="px-3 py-2 rounded-2" style="background:#f1f5f9;color:#059669;font-size:1rem;">
                                {{ $factura->Numero ?? 'N/A' }}
                            </code>
                        </p>
                    </div>

                    {{-- Estatus actual --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Estatus Actual</label>
                        <p>
                            <span class="badge bg-{{ $estatusBadge[$factura->Estatus] ?? 'secondary' }}" style="font-size:0.9rem;">
                                {{ $estatusLabels[$factura->Estatus] ?? 'Desconocido' }}
                            </span>
                        </p>
                    </div>

                    {{-- Proveedor --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Proveedor</label>
                        <p class="fw-bold text-dark">{{ $factura->proveedor_nombre ?? 'N/A' }}</p>
                    </div>

                    {{-- Sucursal --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Sucursal</label>
                        <p class="fw-bold text-dark">{{ $factura->sucursal_nombre ?? 'N/A' }}</p>
                    </div>

                    {{-- Descripción --}}
                    <div class="col-md-12">
                        <label class="form-label fw-semibold text-muted">Descripción</label>
                        <p class="fw-bold text-dark">{{ $factura->Descripcion ?? 'N/A' }}</p>
                    </div>

                    {{-- Fecha Creación --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-muted">Fecha Creación</label>
                        <p class="fw-bold text-dark">
                            {{ isset($factura->FechaCreacion) ? \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>

                    {{-- Moneda Principal --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-muted">Moneda Principal</label>
                        <p class="fw-bold text-dark">{{ $monedaLabels[$factura->MonedaPrincipal ?? 0] }}</p>
                    </div>

                    {{-- Tasa de Cambio --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-muted">Tasa de Cambio</label>
                        <p class="fw-bold text-dark">Bs. {{ number_format($factura->TasaDeCambio ?? 0, 2) }}</p>
                    </div>

                    {{-- Monto Divisa --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Monto en Divisas</label>
                        <p class="fw-bold text-dark text-success">$ {{ number_format($factura->MontoDivisa ?? 0, 2) }}</p>
                    </div>

                    {{-- Monto Bs --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Monto en Bs.</label>
                        <p class="fw-bold text-dark text-primary">Bs. {{ number_format($factura->MontoBs ?? 0, 2) }}</p>
                    </div>

                </div>

                <hr>

                {{-- ================================================ --}}
                {{-- FORMULARIO DE EDICIÓN (SOLO ESTATUS) --}}
                {{-- ================================================ --}}
                <form action="{{ route('cpanel.facturas.actualizar.servicio', $factura->ID) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="estatus" class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1 text-success"></i>Nuevo Estatus <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-toggle-on text-success"></i>
                                </span>
                                <select name="estatus" id="estatus" 
                                        class="form-select border-start-0 @error('estatus') is-invalid @enderror">
                                    @foreach($estatusOptions as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ old('estatus', $factura->Estatus) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text">Seleccione el nuevo estatus para la factura</div>
                            @error('estatus')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-2 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-save me-1"></i> Actualizar Estatus
                        </button>
                        <a href="{{ route('cpanel.facturas.detalle', $factura->ID) }}" class="btn btn-secondary px-4">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus { 
        border-color: #10b981; 
        box-shadow: 0 0 0 0.2rem rgba(16,185,129,.15); 
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush