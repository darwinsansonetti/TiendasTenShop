@extends('layout.layout_dashboard')

@section('title', 'Registrar Factura de Servicio')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#10b981,#059669)';
    $hdrIcon = 'file-text';
    $hdrTitle = 'Registrar Factura de Servicio';
    $hdrSubtitle = 'Proveedor: ' . ($proveedor->Nombre ?? '');
    
    $monedaOptions = [
        0 => 'Divisas',
        1 => 'Bolívares'
    ];
    
    $estatusOptions = [
        1 => 'En Proceso',
        2 => 'Recibiendo',
        4 => 'Recibida',
        3 => 'Pagada',
        0 => 'Anulada'
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.servicios.registrar_facturas') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Registrar Factura</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Información del Proveedor
                    </h6>
                    <a href="{{ route('cpanel.proveedor.servicios.registrar_facturas') }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-center">
                    {{-- Imagen + badges --}}
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc ?? asset('assets/img/adminlte/img/proveedor_default.png') }}"
                             alt="{{ $proveedor->Nombre ?? 'Proveedor' }}"
                             class="rounded-circle"
                             style="width:90px;height:90px;object-fit:cover;border:3px solid #e2e8f0;">
                        <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                            @if($proveedor->Estatus == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Activo</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.72rem;">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.72rem;">Mercancía</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Servicio</span>
                            @endif
                        </div>
                    </div>
                    {{-- Datos del proveedor --}}
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                                <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->Rif_Cedula ?? $proveedor->Rif ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoMovil ?? $proveedor->Telefono1 ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Correo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->CorreoElectronico ?? $proveedor->Email ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-8 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Dirección</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 60) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: FORMULARIO DE FACTURA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-{{ $hdrIcon }} me-2"></i>Registrar Factura
                </h6>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('cpanel.facturas.servicios.guardar') }}" method="POST" id="formRegistrarFactura">
                    @csrf
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">
                    <input type="hidden" name="tipo" value="{{ $proveedor->Tipo }}">

                    <div class="row g-3">

                        {{-- Sucursal --}}
                        <div class="col-md-6">
                            <label for="sucursal_id" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-building me-1 text-success"></i>Sucursal <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-building text-success"></i>
                                </span>
                                <select name="sucursal_id" id="sucursal_id" 
                                        class="form-select border-start-0 @error('sucursal_id') is-invalid @enderror" required>
                                    <option value="">Seleccione una sucursal</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->ID }}"
                                                {{ old('sucursal_id', $sucursalSeleccionada ?? '') == $sucursal->ID ? 'selected' : '' }}>
                                            {{ $sucursal->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('sucursal_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Seleccione la sucursal donde se presta el servicio</div>
                        </div>

                        {{-- Servicio --}}
                        <div class="col-md-6">
                            <label for="servicio_id" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-tools me-1 text-success"></i>Servicio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-tools text-success"></i>
                                </span>
                                <select name="servicio_id" id="servicio_id" 
                                        class="form-select border-start-0 @error('servicio_id') is-invalid @enderror" 
                                        disabled required>
                                    <option value="">Seleccione una sucursal</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->ServiciosPlantillaId }}" 
                                                data-sucursal="{{ $servicio->SucursalId }}"
                                                {{ old('servicio_id') == $servicio->ServiciosPlantillaId ? 'selected' : '' }}>
                                            {{ $servicio->Numero }} - {{ $servicio->Descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('cpanel.servicios.nuevo', $proveedor->ProveedorId) }}"
                                   class="btn btn-light border fw-semibold"
                                   title="Crear nuevo servicio" data-bs-toggle="tooltip"
                                   style="font-size:0.82rem;">
                                    <i class="bi bi-plus-circle me-1"></i>Nuevo
                                </a>
                            </div>
                            @error('servicio_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Seleccione el servicio a facturar</div>
                        </div>

                        {{-- Fecha (día actual por defecto) --}}
                        <div class="col-md-4">
                            <label for="fecha_creacion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1 text-success"></i>Fecha <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar text-success"></i>
                                </span>
                                <input type="date" name="fecha_creacion" id="fecha_creacion"
                                       class="form-control border-start-0 @error('fecha_creacion') is-invalid @enderror"
                                       value="{{ old('fecha_creacion', Carbon::now()->format('Y-m-d')) }}" required>
                            </div>
                            @error('fecha_creacion')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Moneda Principal --}}
                        <div class="col-md-4">
                            <label for="moneda_principal" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-currency-exchange me-1 text-success"></i>Moneda ppal.
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-currency-exchange text-success"></i>
                                </span>
                                <select name="moneda_principal" id="moneda_principal" 
                                        class="form-select border-start-0 @error('moneda_principal') is-invalid @enderror">
                                    <option value="">Seleccione un valor</option>
                                    @foreach($monedaOptions as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ old('moneda_principal', $monedaSeleccionada ?? 0) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('moneda_principal')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tasa de Cambio --}}
                        <div class="col-md-4">
                            <label for="tasa_cambio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-arrow-left-right me-1 text-success"></i>Tasa de Cambio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-arrow-left-right text-success"></i>
                                </span>
                                <input type="number" name="tasa_cambio" id="tasa_cambio"
                                       class="form-control border-start-0 @error('tasa_cambio') is-invalid @enderror"
                                       step="0.01" min="0.01" required
                                       placeholder="Tasa de cambio del día"
                                       value="{{ old('tasa_cambio', $tasaCambioActual ?? '') }}">
                            </div>
                            @error('tasa_cambio')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tasa de cambio del día (USD a Bs.)</div>
                        </div>

                        {{-- Monto en Divisas --}}
                        <div class="col-md-6">
                            <label for="monto_divisa" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-cash me-1 text-success"></i>Monto en Divisas <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">$</span>
                                <input type="number" name="monto_divisa" id="monto_divisa"
                                       class="form-control border-start-0 @error('monto_divisa') is-invalid @enderror"
                                       step="0.01" min="0.01" required
                                       placeholder="0.00"
                                       value="{{ old('monto_divisa') }}">
                            </div>
                            @error('monto_divisa')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Monto en divisas (USD)</div>
                        </div>

                        {{-- Monto en Bolívares --}}
                        <div class="col-md-6">
                            <label for="monto_bs" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-cash-stack me-1 text-success"></i>Monto en Bs.
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">Bs.</span>
                                <input type="number" name="monto_bs" id="monto_bs"
                                       class="form-control border-start-0 @error('monto_bs') is-invalid @enderror"
                                       step="0.01" min="0"
                                       placeholder="0.00"
                                       value="{{ old('monto_bs') }}">
                            </div>
                            @error('monto_bs')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Se calcula automáticamente según la tasa de cambio</div>
                        </div>

                        {{-- Estatus --}}
                        <div class="col-md-4">
                            <label for="estatus" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-toggle-on me-1 text-success"></i>Estatus <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-toggle-on text-success"></i>
                                </span>
                                <select name="estatus" id="estatus"
                                        class="form-select border-start-0 @error('estatus') is-invalid @enderror" required>
                                    <option value="" selected>Seleccione un valor</option>
                                    <option value="1" {{ old('estatus') == 1 ? 'selected' : '' }}>En Proceso</option>
                                    <option value="2" {{ old('estatus') == 2 ? 'selected' : '' }}>Recibiendo</option>
                                    <option value="4" {{ old('estatus') == 4 ? 'selected' : '' }}>Recibida</option>
                                    <option value="3" {{ old('estatus') == 3 ? 'selected' : '' }}>Pagada</option>
                                    <option value="0" {{ old('estatus') === '0' ? 'selected' : '' }}>Anulada</option>
                                </select>
                            </div>
                            @error('estatus')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Estatus de la factura</div>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;" id="btnGuardarFactura">
                            <i class="bi bi-save me-2"></i>Guardar Factura
                        </button>
                        <a href="{{ route('cpanel.proveedor.servicios.registrar_facturas') }}"
                           class="btn btn-light border px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ================================================
        // FILTRAR SERVICIOS POR SUCURSAL
        // ================================================
        const selectSucursal = document.getElementById('sucursal_id');
        const selectServicio = document.getElementById('servicio_id');
        
        if (selectSucursal && selectServicio) {
            // Guardar todas las opciones de servicios
            const todasLasOpciones = Array.from(selectServicio.querySelectorAll('option'));
            const opcionesDefault = todasLasOpciones.filter(opt => opt.value === '');
            const opcionesServicios = todasLasOpciones.filter(opt => opt.value !== '');

            function filtrarServicios() {
                const sucursalSeleccionada = selectSucursal.value;
                
                // Limpiar el select (mantener solo la opción por defecto)
                selectServicio.innerHTML = '';
                
                // Agregar la opción por defecto
                opcionesDefault.forEach(opt => selectServicio.appendChild(opt.cloneNode(true)));
                
                if (!sucursalSeleccionada) {
                    // Si no hay sucursal seleccionada, deshabilitar el select
                    selectServicio.disabled = true;
                    selectServicio.querySelector('option').textContent = 'Primero seleccione una sucursal';
                    return;
                }
                
                // Habilitar el select
                selectServicio.disabled = false;
                
                // Filtrar y agregar solo los servicios de la sucursal seleccionada
                let hayServicios = false;
                opcionesServicios.forEach(function(option) {
                    const sucursalServicio = option.getAttribute('data-sucursal');
                    
                    if (sucursalServicio === sucursalSeleccionada) {
                        const nuevoOption = document.createElement('option');
                        nuevoOption.value = option.value;
                        nuevoOption.textContent = option.textContent;
                        nuevoOption.setAttribute('data-sucursal', sucursalServicio);
                        
                        if (option.selected) {
                            nuevoOption.selected = true;
                        }
                        
                        selectServicio.appendChild(nuevoOption);
                        hayServicios = true;
                    }
                });
                
                if (!hayServicios) {
                    // Si no hay servicios para esta sucursal
                    const msgOption = document.createElement('option');
                    msgOption.value = '';
                    msgOption.textContent = 'No hay servicios en esta sucursal';
                    msgOption.disabled = true;
                    selectServicio.appendChild(msgOption);
                }
            }

            // Filtrar al cambiar la sucursal
            selectSucursal.addEventListener('change', filtrarServicios);
            
            // Filtrar al cargar la página
            if (selectSucursal.value) {
                filtrarServicios();
            } else {
                selectServicio.disabled = true;
            }
        }

        // ================================================
        // CALCULAR MONTOS (BIDIRECCIONAL)
        // ================================================
        const montoDivisa = document.getElementById('monto_divisa');
        const tasaCambio = document.getElementById('tasa_cambio');
        const montoBs = document.getElementById('monto_bs');

        let calculando = false;

        function calcularMontoBs() {
            if (calculando) return;
            calculando = true;
            
            const divisa = parseFloat(montoDivisa.value) || 0;
            const tasa = parseFloat(tasaCambio.value) || 0;
            
            if (tasa > 0) {
                montoBs.value = (divisa * tasa).toFixed(2);
            } else {
                montoBs.value = '';
            }
            
            calculando = false;
        }

        function calcularMontoDivisa() {
            if (calculando) return;
            calculando = true;
            
            const bs = parseFloat(montoBs.value) || 0;
            const tasa = parseFloat(tasaCambio.value) || 0;
            
            if (tasa > 0 && bs > 0) {
                montoDivisa.value = (bs / tasa).toFixed(2);
            } else {
                montoDivisa.value = '';
            }
            
            calculando = false;
        }

        if (montoDivisa && tasaCambio && montoBs) {
            // Cuando cambia Monto en Divisas -> calcular Monto en Bs.
            montoDivisa.addEventListener('input', calcularMontoBs);
            
            // Cuando cambia Tasa de Cambio -> recalcular ambos
            tasaCambio.addEventListener('input', function() {
                if (montoDivisa.value) {
                    calcularMontoBs();
                }
                if (montoBs.value) {
                    calcularMontoDivisa();
                }
            });
            
            // Cuando cambia Monto en Bs. -> calcular Monto en Divisas
            montoBs.addEventListener('input', calcularMontoDivisa);
        }

        // ================================================
        // DESHABILITAR BOTÓN AL ENVIAR
        // ================================================
        document.getElementById('formRegistrarFactura')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnGuardarFactura');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
            }
        });
    });
</script>
@endsection

@push('styles')
<style>
    .input-group-text { border-color: #dee2e6; }
    .form-control:focus, .form-select:focus { 
        border-color: #10b981; 
        box-shadow: 0 0 0 0.2rem rgba(16,185,129,.15); 
    }
    select:disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
</style>
@endpush