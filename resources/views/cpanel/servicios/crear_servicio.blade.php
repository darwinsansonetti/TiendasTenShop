@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | ' . ($modo == 'crear' ? 'Nuevo' : 'Editar') . ' Servicio')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = $modo == 'crear' ? 'plus-circle' : 'pencil';
    $hdrTitle = $modo == 'crear' ? 'Nuevo Servicio' : 'Editar Servicio';
    $hdrSubtitle = $modo == 'crear' 
        ? 'Registrar un nuevo servicio para ' . ($proveedor->Nombre ?? '') 
        : 'Editando servicio: ' . ($servicio->Numero ?? '');
    
    $isEdit = $modo == 'editar';
    $actionUrl = $isEdit 
        ? route('cpanel.servicios.actualizar', $servicio->ServiciosPlantillaId ?? 0) 
        : route('cpanel.servicios.crear');
    $formMethod = $isEdit ? 'PUT' : 'POST';
    $buttonText = $isEdit ? 'Actualizar Servicio' : 'Guardar Servicio';
    $cancelUrl = route('cpanel.proveedores.servicios.detalle.seleccion', $proveedor->ProveedorId ?? 0);
    
    // Valores para los selects (igual que en .NET)
    $monedaOptions = [
        0 => 'Divisas',
        1 => 'Bolívares'
    ];
    
    $estatusOptions = [
        0 => 'Activo',
        1 => 'Inactivo'
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
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $modo == 'crear' ? 'Nuevo' : 'Editar' }}
                    </li>
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
                    <a href="{{ $cancelUrl }}" 
                       class="btn btn-sm text-white" 
                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-arrow-left me-1"></i>Volver a servicios
                    </a>
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

        {{-- Formulario de Servicio --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-{{ $hdrIcon }} me-2"></i>
                    {{ $modo == 'crear' ? 'Registrar Nuevo Servicio' : 'Editar Servicio' }}
                </h6>
            </div>
            <div class="card-body">
                <form id="formServicio" action="{{ $actionUrl }}" method="POST">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    {{-- Campos ocultos (igual que en .NET) --}}
                    <input type="hidden" name="ProveedorId" value="{{ $proveedor->ProveedorId ?? '' }}">
                    <input type="hidden" name="Numero" value="{{ $numeroGenerado ?? $servicio->Numero ?? '' }}">
                    
                    {{-- En creación, el estatus siempre será Activo (0) --}}
                    @if(!$isEdit)
                        <input type="hidden" name="Estatus" value="0">
                    @endif

                    <div class="row g-3">
                        {{-- Sucursal --}}
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="sucursal_id" class="form-label fw-semibold">
                                        <i class="bi bi-store me-1" style="color:#8b5cf6;"></i>Sucursal
                                    </label>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-store" style="color:#8b5cf6;"></i>
                                        </span>
                                        <select name="SucursalId" id="sucursal_id" class="form-select @error('SucursalId') is-invalid @enderror">
                                            <option value="">Seleccione un valor</option>
                                            @foreach($sucursales as $sucursal)
                                                <option value="{{ $sucursal->ID }}" 
                                                    {{ (old('SucursalId', $servicio->SucursalId ?? '') == $sucursal->ID) ? 'selected' : '' }}>
                                                    {{ $sucursal->Nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('SucursalId')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-sm" style="background:#673ab7;color:#fff;">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="descripcion" class="form-label fw-semibold">
                                        <i class="bi bi-file-text me-1" style="color:#8b5cf6;"></i>Descripción
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-file-text" style="color:#8b5cf6;"></i>
                                        </span>
                                        <input type="text" 
                                               name="Descripcion" 
                                               id="descripcion" 
                                               class="form-control @error('Descripcion') is-invalid @enderror" 
                                               required
                                               placeholder="Descripción"
                                               value="{{ old('Descripcion', $servicio->Descripcion ?? '') }}">
                                    </div>
                                    @error('Descripcion')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Moneda Principal --}}
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="moneda_principal" class="form-label fw-semibold">
                                        <i class="bi bi-currency-exchange me-1" style="color:#8b5cf6;"></i>Moneda ppal.
                                    </label>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-currency-exchange" style="color:#8b5cf6;"></i>
                                        </span>
                                        <select name="MonedaPrincipal" id="moneda_principal" class="form-select @error('MonedaPrincipal') is-invalid @enderror">
                                            <option value="">Seleccione un valor</option>
                                            @foreach($monedaOptions as $value => $label)
                                                <option value="{{ $value }}" 
                                                    {{ (old('MonedaPrincipal', $servicio->MonedaPrincipal ?? '') == $value) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('MonedaPrincipal')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Tarifa Divisa --}}
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="monto_divisa" class="form-label fw-semibold">
                                        <i class="bi bi-cash me-1" style="color:#8b5cf6;"></i>Tarifa Divisa
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-cash" style="color:#8b5cf6;"></i>
                                        </span>
                                        <input type="number" 
                                               name="MontoDivisa" 
                                               id="monto_divisa" 
                                               class="form-control @error('MontoDivisa') is-invalid @enderror" 
                                               step="0.01" 
                                               min="0"
                                               required
                                               placeholder="Tarifa en divisas"
                                               value="{{ old('MontoDivisa', $servicio->MontoDivisa ?? '') }}">
                                    </div>
                                    @error('MontoDivisa')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Tarifa Bs --}}
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="monto" class="form-label fw-semibold">
                                        <i class="bi bi-cash-stack me-1" style="color:#8b5cf6;"></i>Tarifa Bs.
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-cash-stack" style="color:#8b5cf6;"></i>
                                        </span>
                                        <input type="number" 
                                               name="Monto" 
                                               id="monto" 
                                               class="form-control @error('Monto') is-invalid @enderror" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="Tarifa en bolívares"
                                               value="{{ old('Monto', $servicio->Monto ?? '') }}">
                                    </div>
                                    @error('Monto')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fecha Creación (solo en edición) --}}
                        @if($isEdit)
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="fecha_creacion" class="form-label fw-semibold">
                                        <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>F. Creación *
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar" style="color:#8b5cf6;"></i>
                                        </span>
                                        <input type="text" 
                                               id="fecha_creacion" 
                                               class="form-control" 
                                               value="{{ isset($servicio->FechaCreacion) ? Carbon::parse($servicio->FechaCreacion)->format('d/m/Y H:i') : 'N/A' }}"
                                               disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Estatus (SOLO EN EDICIÓN) --}}
                        @if($isEdit)
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="estatus" class="form-label fw-semibold">
                                        <i class="bi bi-toggle-on me-1" style="color:#8b5cf6;"></i>Estatus
                                    </label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-toggle-on" style="color:#8b5cf6;"></i>
                                        </span>
                                        <select name="Estatus" id="estatus" class="form-select @error('Estatus') is-invalid @enderror">
                                            @foreach($estatusOptions as $value => $label)
                                                <option value="{{ $value }}" 
                                                    {{ (old('Estatus', $servicio->Estatus ?? 0) == $value) ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('Estatus')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-save me-1"></i> {{ $buttonText }}
                        </button>
                        <a href="{{ $cancelUrl }}" class="btn btn-secondary px-4">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Validación del formulario
        document.getElementById('formServicio').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion').value.trim();
            const moneda = document.getElementById('moneda_principal').value;
            const montoDivisa = document.getElementById('monto_divisa').value.trim();

            if (!descripcion) {
                e.preventDefault();
                Swal.fire('Error', 'La descripción es obligatoria', 'error');
                document.getElementById('descripcion').focus();
                return false;
            }

            if (!moneda) {
                e.preventDefault();
                Swal.fire('Error', 'Debe seleccionar una moneda principal', 'error');
                document.getElementById('moneda_principal').focus();
                return false;
            }

            if (!montoDivisa || parseFloat(montoDivisa) < 0) {
                e.preventDefault();
                Swal.fire('Error', 'La tarifa en divisas es obligatoria y debe ser mayor o igual a 0', 'error');
                document.getElementById('monto_divisa').focus();
                return false;
            }
        });

        // Mostrar errores de validación
        @if($errors->any())
            Swal.fire({
                title: 'Error de validación',
                text: '{{ $errors->first() }}',
                icon: 'error',
                confirmButtonColor: '#8b5cf6'
            });
        @endif

        // Mostrar mensajes de éxito/error
        @if(session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#8b5cf6'
            });
        @endif
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.15);
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }
    .input-group .form-control,
    .input-group .form-select {
        border-left: none;
    }
    .input-group .form-control:focus,
    .input-group .form-select:focus {
        border-left: none;
    }
</style>
@endpush