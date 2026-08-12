@extends('layout.layout_dashboard')

@section('title', 'Planificar un Inventario')

@php
    $isEdit = isset($inventario) && $inventario->InventarioId > 0;
    $inventario = $inventario ?? (object)[
        'InventarioId' => 0,
        'Codigo' => '',
        'SucursalId' => '',
        'Descripcion' => '',
        'FechaInicio' => now()->toDateString(),
        'FechaFin' => now()->toDateString(),
        'Estatus' => 0,
    ];
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            {{ $isEdit ? 'Editar Inventario' : 'Planificar un Inventario' }}
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $isEdit ? 'Actualiza los datos del inventario' : 'Crea una nueva tarea de inventario' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.inventario.listado') }}">Inventarios</a></li>
                    <li class="breadcrumb-item active">{{ $isEdit ? 'Editar' : 'Nuevo' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card border-0 shadow-sm">
                    {{-- Header --}}
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h2 class="mb-0 fw-bold text-white" style="font-size:1.15rem;">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    {{ $isEdit ? 'Editar Inventario' : 'Nuevo Inventario' }}
                                    <small class="d-block text-white-50" style="font-size:0.7rem;font-weight:400;">
                                        {{ $isEdit ? 'Actualiza los datos del inventario' : 'Crea una nueva tarea de inventario' }}
                                    </small>
                                </h2>
                            </div>
                            <div>
                                <a href="{{ route('cpanel.inventario.listado') }}" 
                                   class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-list me-1"></i> LISTADO
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body p-4">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Por favor corrige los siguientes errores:
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" 
                            action="{{ $isEdit ? route('cpanel.inventario.actualizar', $inventario->InventarioId ?? 0) : route('cpanel.inventario.guardar') }}" 
                            id="crear-inventario">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <input type="hidden" name="InventarioId" value="{{ $inventario->InventarioId ?? 0 }}">
                            <input type="hidden" name="Codigo" value="{{ $inventario->Codigo ?? '' }}">
                            <input type="hidden" name="Tipo" value="1"> {{-- 1 = General (fijo) --}}

                            <div class="row">
                                {{-- Columna izquierda --}}
                                <div class="col-md-6">
                                    {{-- Fecha Inicio --}}
                                    <div class="form-group mb-3">
                                        <label for="FechaInicio" class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Fecha Inicio <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('FechaInicio') is-invalid @enderror" 
                                            id="FechaInicio" 
                                            name="FechaInicio" 
                                            value="{{ old('FechaInicio', $inventario->FechaInicio ?? now()->toDateString()) }}"
                                            required
                                            style="border-radius:6px;font-size:0.85rem;padding:0.45rem 0.75rem;">
                                        @error('FechaInicio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Fecha Fin --}}
                                    <div class="form-group mb-3">
                                        <label for="FechaFin" class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Fecha Fin <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('FechaFin') is-invalid @enderror" 
                                            id="FechaFin" 
                                            name="FechaFin" 
                                            value="{{ old('FechaFin', $inventario->FechaFin ?? now()->toDateString()) }}"
                                            required
                                            style="border-radius:6px;font-size:0.85rem;padding:0.45rem 0.75rem;">
                                        @error('FechaFin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Descripción --}}
                                    <div class="form-group mb-3">
                                        <label for="Descripcion" class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Descripción
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius:6px 0 0 6px;background:#f8fafc;">
                                                <i class="bi bi-building"></i>
                                            </span>
                                            <input type="text" class="form-control @error('Descripcion') is-invalid @enderror" 
                                                   id="Descripcion" 
                                                   name="Descripcion" 
                                                   value="{{ old('Descripcion', $inventario->Descripcion ?? '') }}"
                                                   placeholder="Escriba la descripción..."
                                                   style="border-radius:0 6px 6px 0;font-size:0.85rem;padding:0.45rem 0.75rem;">
                                        </div>
                                        @error('Descripcion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Columna derecha --}}
                                <div class="col-md-6">
                                    {{-- Sucursal --}}
                                    <div class="form-group mb-3">
                                        <label for="SucursalId" class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Sucursal <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius:6px 0 0 6px;background:#f8fafc;">
                                                <i class="bi bi-store"></i>
                                            </span>
                                            <select class="form-select @error('SucursalId') is-invalid @enderror" 
                                                    id="SucursalId" 
                                                    name="SucursalId" 
                                                    required
                                                    style="border-radius:0 6px 6px 0;font-size:0.85rem;padding:0.45rem 0.75rem;">
                                                <option value="">Seleccione un valor</option>
                                                @foreach($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->ID }}" 
                                                        {{ old('SucursalId', $inventario->SucursalId ?? '') == $sucursal->ID ? 'selected' : '' }}>
                                                        {{ $sucursal->Nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('SucursalId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Tipo (solo lectura, fijo en General) --}}
                                    <div class="form-group mb-3">
                                        <label class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Tipo
                                        </label>
                                        <div class="form-control" style="border-radius:6px;font-size:0.85rem;padding:0.45rem 0.75rem;background:#f8fafc;cursor:default;">
                                            <i class="bi bi-check-circle-fill text-success me-1"></i> General
                                        </div>
                                    </div>

                                    {{-- ============================================ --}}
                                    {{-- ESTATUS (SOLO EN EDICIÓN) --}}
                                    {{-- ============================================ --}}
                                    @if($isEdit)
                                    <div class="form-group mb-3">
                                        <label class="control-label fw-semibold" style="font-size:0.85rem;">
                                            Estatus
                                        </label>
                                        @php
                                            $estatusLabels = [
                                                0 => 'Nuevo',
                                                1 => 'En Conteo',
                                                2 => 'En Auditoría',
                                                3 => 'Cerrado'
                                            ];
                                            $estatusColors = [
                                                0 => '#6366f1',
                                                1 => '#22c55e',
                                                2 => '#eab308',
                                                3 => '#ef4444'
                                            ];
                                            $estatusActual = $estatusLabels[$inventario->Estatus ?? 0] ?? 'Desconocido';
                                            $estatusColor = $estatusColors[$inventario->Estatus ?? 0] ?? '#6b7280';
                                            $estatusTextColor = ($inventario->Estatus ?? 0) == 2 ? '#000' : '#fff';
                                        @endphp
                                        <div class="form-control" style="border-radius:6px;font-size:0.85rem;padding:0.45rem 0.75rem;background:#f8fafc;cursor:default;">
                                            <span class="badge rounded-pill px-2 py-1" 
                                                  style="background:{{ $estatusColor }}; color: {{ $estatusTextColor }};">
                                                {{ $estatusActual }}
                                            </span>
                                        </div>
                                        <input type="hidden" name="Estatus" value="{{ $inventario->Estatus ?? 0 }}">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            El estatus no se puede modificar en la edición
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Botón Guardar --}}
                            <div class="mt-4">
                                <button type="submit" class="btn" id="btnSave"
                                        style="background:#f59e0b;color:#fff;border-radius:6px;padding:0.5rem 2rem;min-width:120px;">
                                    <i class="bi bi-save me-1"></i> GUARDAR
                                </button>
                                <a href="{{ route('cpanel.inventario.listado') }}" 
                                   class="btn btn-secondary" style="border-radius:6px;padding:0.5rem 1.5rem;">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                            </div>
                        </form>

                        {{-- Mensajes --}}
                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }
    .btn {
        transition: all 0.15s ease;
    }
    .btn:hover {
        transform: scale(1.02);
        opacity: 0.9;
    }
    .input-group-text {
        background-color: #f8fafc;
        border: 1px solid #d1d5db;
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush