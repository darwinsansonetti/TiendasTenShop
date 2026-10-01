@extends('layout.layout_dashboard')

@section('title', ($configuracion ? 'Editar' : 'Nueva') . ' Configuración Pago Móvil')

@php
    $esEdicion = !is_null($configuracion);
    $hdrBg = 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)';
    $hdrIcon = 'credit-card';
    $hdrTitle = $esEdicion ? 'Editar Configuración' : 'Nueva Configuración';
    $hdrSubtitle = 'Pago Móvil del Banco de Venezuela';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.configuracion.pago.movil') }}">Configuración Pago Móvil</a></li>
                    <li class="breadcrumb-item active">{{ $esEdicion ? 'Editar' : 'Nueva' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-pencil-square me-2"></i>Datos de la Configuración
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ $esEdicion 
                                ? route('cpanel.configuracion.pago.movil.update', $configuracion->SucursalPagoMovilId) 
                                : route('cpanel.configuracion.pago.movil.store') }}"
                      method="POST">
                    @csrf
                    @if($esEdicion)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                Sucursal <span class="text-danger">*</span>
                            </label>
                            <select name="SucursalId" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->ID }}"
                                        {{ old('SucursalId', $configuracion->SucursalId ?? '') == $suc->ID ? 'selected' : '' }}>
                                        {{ $suc->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                Alias <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="Alias" class="form-control"
                                   value="{{ old('Alias', $configuracion->Alias ?? '') }}"
                                   placeholder="Ej: Caja Principal" maxlength="100" required>
                            <small class="text-muted">Nombre descriptivo para identificar este Pago Móvil.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                RIF del Comercio <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="Rif" class="form-control"
                                   value="{{ old('Rif', $configuracion->Rif ?? '') }}"
                                   placeholder="Ej: J-12345678-9" maxlength="20" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                Teléfono Destino <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="Telefono" class="form-control"
                                   value="{{ old('Telefono', $configuracion->Telefono ?? '') }}"
                                   placeholder="Ej: 04121234567" maxlength="20" required>
                            <small class="text-muted">Teléfono afiliado al Pago Móvil BDV.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">Estado</label>
                            <select name="Activo" class="form-select">
                                <option value="1" {{ old('Activo', $configuracion->Activo ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('Activo', $configuracion->Activo ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                API Key <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ApiKey" class="form-control"
                                   value="{{ old('ApiKey', $configuracion->ApiKey ?? '') }}"
                                   placeholder="Clave entregada por el Banco de Venezuela"
                                   maxlength="100" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                Endpoint (opcional)
                            </label>
                            <input type="text" name="Endpoint" class="form-control"
                                   value="{{ old('Endpoint', $configuracion->Endpoint ?? '') }}"
                                   placeholder="Dejar vacío para usar el endpoint del sistema"
                                   maxlength="200">
                            <small class="text-muted">Solo si necesitas apuntar a otro endpoint distinto al configurado en el sistema.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('cpanel.configuracion.pago.movil') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn fw-semibold text-white"
                                style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-check-lg me-1"></i>{{ $esEdicion ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection