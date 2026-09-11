@extends('layout.layout_dashboard')

@section('title', 'Crear Punto de Venta')

@php
    $hdrBg = 'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)';
    $hdrIcon = 'plus-circle';
    $hdrTitle = 'Crear Punto de Venta';
    $hdrSubtitle = 'Registrar un nuevo punto de venta';
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
                    <li class="breadcrumb-item active" aria-current="page">Crear</li>
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
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Punto de Venta
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.puntos.guardar') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        {{-- Banco --}}
                        <div class="col-md-6">
                            <label for="banco_id" class="form-label fw-semibold">
                                <i class="bi bi-bank me-1" style="color:#3b82f6;"></i>Banco <span class="text-danger">*</span>
                            </label>
                            <select name="banco_id" id="banco_id" class="form-select @error('banco_id') is-invalid @enderror" required>
                                <option value="">Seleccione un banco</option>
                                @foreach($bancos as $banco)
                                    <option value="{{ $banco->ID }}" {{ old('banco_id') == $banco->ID ? 'selected' : '' }}>
                                        {{ $banco->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('banco_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Sucursal --}}
                        <div class="col-md-6">
                            <label for="sucursal_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1" style="color:#3b82f6;"></i>Sucursal <span class="text-danger">*</span>
                            </label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select @error('sucursal_id') is-invalid @enderror" required>
                                <option value="">Seleccione una sucursal</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->ID }}" {{ old('sucursal_id') == $sucursal->ID ? 'selected' : '' }}>
                                        {{ $sucursal->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sucursal_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="col-md-6">
                            <label for="descripcion" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1" style="color:#3b82f6;"></i>Descripción <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="descripcion" id="descripcion"
                                   class="form-control @error('descripcion') is-invalid @enderror"
                                   placeholder="Descripción del punto de venta"
                                   value="{{ old('descripcion') }}" required>
                            @error('descripcion')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Serial --}}
                        <div class="col-md-6">
                            <label for="serial" class="form-label fw-semibold">
                                <i class="bi bi-hash me-1" style="color:#3b82f6;"></i>Serial
                            </label>
                            <input type="text" name="serial" id="serial"
                                   class="form-control @error('serial') is-invalid @enderror"
                                   placeholder="Serial del punto de venta"
                                   value="{{ old('serial') }}">
                            @error('serial')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Código --}}
                        <div class="col-md-6">
                            <label for="codigo" class="form-label fw-semibold">
                                <i class="bi bi-code me-1" style="color:#3b82f6;"></i>Código
                            </label>
                            <input type="number" name="codigo" id="codigo"
                                   class="form-control @error('codigo') is-invalid @enderror"
                                   placeholder="Código del punto de venta"
                                   value="{{ old('codigo') }}">
                            @error('codigo')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estatus --}}
                        <div class="col-md-6">
                            <label for="es_activo" class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1" style="color:#3b82f6;"></i>Estatus
                            </label>
                            <select name="es_activo" id="es_activo" class="form-select @error('es_activo') is-invalid @enderror">
                                <option value="1" {{ old('es_activo', 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('es_activo') == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('es_activo')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="mt-4 pt-2 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-save me-1"></i> Guardar Punto de Venta
                        </button>
                        <a href="{{ route('cpanel.puntos.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
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
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
    }
</style>
@endpush