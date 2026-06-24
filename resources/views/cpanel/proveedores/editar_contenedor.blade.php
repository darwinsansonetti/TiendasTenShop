@extends('layout.layout_dashboard')

@section('title', 'Editar Contenedor')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                  <i class="bi bi-box-seam text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Contenedor</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Modificar datos del contenedor</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}">Contenedores</a>
                    </li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <form action="{{ route('cpanel.contenedores.actualizar', $contenedor->Id) }}" method="POST" id="formEditarContenedor">
        @csrf
        @method('PUT')

        {{-- Sección 1: Identificación --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-card-text me-2"></i>Identificación del Contenedor
                    </h6>
                    <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    {{-- Nombre / Descripción --}}
                    <div class="col-md-8">
                        <label for="nombre" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Descripción <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-card-text text-warning"></i>
                            </span>
                            <input type="text" name="nombre" id="nombre"
                                   class="form-control border-start-0 @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $contenedor->Nombre) }}"
                                   placeholder="Descripción o nombre del contenedor" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Fecha de Creación --}}
                    <div class="col-md-4">
                        <label for="fecha_creacion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Fecha <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-calendar text-warning"></i>
                            </span>
                            <input type="date" name="fecha_creacion" id="fecha_creacion"
                                   class="form-control border-start-0 @error('fecha_creacion') is-invalid @enderror"
                                   value="{{ old('fecha_creacion', date('Y-m-d', strtotime($contenedor->FechaCreacion))) }}" required>
                            @error('fecha_creacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Estatus --}}
                    <div class="col-md-4">
                        <label for="estatus" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Estatus <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-toggle-on text-warning"></i>
                            </span>
                            <select name="estatus" id="estatus"
                                    class="form-select border-start-0 @error('estatus') is-invalid @enderror" required>
                                <option value="">Seleccione un valor</option>
                                <option value="0" {{ old('estatus', $contenedor->Estatus) == 0 ? 'selected' : '' }}>Nuevo</option>
                                <option value="1" {{ old('estatus', $contenedor->Estatus) == 1 ? 'selected' : '' }}>En Tránsito</option>
                                <option value="2" {{ old('estatus', $contenedor->Estatus) == 2 ? 'selected' : '' }}>En Aduana</option>
                            </select>
                            @error('estatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- País de Origen --}}
                    <div class="col-md-4">
                        <label for="origen" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            País de Origen
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-geo-alt text-warning"></i>
                            </span>
                            <input type="text" name="origen" id="origen"
                                   class="form-control border-start-0 @error('origen') is-invalid @enderror"
                                   value="{{ old('origen', $contenedor->Origen) }}"
                                   placeholder="País de origen">
                            @error('origen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Fecha de Recepción --}}
                    <div class="col-md-4">
                        <label for="fecha_recepcion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Fecha de Recepción
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-calendar-check text-warning"></i>
                            </span>
                            <input type="date" name="fecha_recepcion" id="fecha_recepcion"
                                   class="form-control border-start-0 @error('fecha_recepcion') is-invalid @enderror"
                                   value="{{ old('fecha_recepcion', $contenedor->FechaRecepcion ? date('Y-m-d', strtotime($contenedor->FechaRecepcion)) : '') }}">
                            @error('fecha_recepcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección 2: Costos y Porcentaje --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-currency-dollar me-2"></i>Costos y Gastos
                </h6>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    {{-- Flete --}}
                    <div class="col-md-4">
                        <label for="flete" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Flete
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                            <input type="number" step="0.01" name="flete" id="flete"
                                   class="form-control border-start-0 formato-numerico @error('flete') is-invalid @enderror"
                                   value="{{ old('flete', $contenedor->Flete ?? 0) }}"
                                   placeholder="0.00">
                            @error('flete')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Costo Aduana --}}
                    <div class="col-md-4">
                        <label for="aduana" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Costo Aduana
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                            <input type="number" step="0.01" name="aduana" id="aduana"
                                   class="form-control border-start-0 formato-numerico @error('aduana') is-invalid @enderror"
                                   value="{{ old('aduana', $contenedor->Aduana ?? 0) }}"
                                   placeholder="0.00">
                            @error('aduana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Monto Total Facturas --}}
                    <div class="col-md-4">
                        <label for="monto_total_facturas" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Monto Total de Facturas
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                            <input type="number" step="0.01" name="monto_total_facturas" id="monto_total_facturas"
                                   class="form-control border-start-0 formato-numerico"
                                   value="{{ old('monto_total_facturas', $contenedor->MontoTotalFacturas ?? 0) }}"
                                   placeholder="0.00">
                        </div>
                        <div class="form-text">Suma total de las facturas asociadas a este contenedor</div>
                    </div>

                    {{-- Porcentaje de Gastos (calculado) --}}
                    <div class="col-md-4">
                        <label for="porcentaje_gastos" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            Porcentaje de Gastos
                            <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Auto</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="porcentaje_gastos" id="porcentaje_gastos"
                                   class="form-control text-end fw-bold"
                                   style="background:#f8fafc;color:#059669;"
                                   value="{{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }}" readonly>
                            <span class="input-group-text bg-white">%</span>
                        </div>
                        <div class="form-text">Se calcula: (Flete + Aduana) / Total Facturas × 100</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning px-4 text-white fw-semibold" id="btnGuardar">
                <i class="bi bi-save me-2"></i>Guardar Cambios
            </button>
            <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}" class="btn btn-light border px-4">
                <i class="bi bi-x-circle me-2"></i>Cancelar
            </a>
        </div>

        </form>
        
    </div>
</div>

@endsection

@section('js')
<script>
    // ============================================
    // CÁLCULO AUTOMÁTICO DEL PORCENTAJE DE GASTOS
    // ============================================
    function calcularPorcentajeGastos() {
        let flete = parseFloat(document.getElementById('flete')?.value) || 0;
        let aduana = parseFloat(document.getElementById('aduana')?.value) || 0;
        let montoTotalFacturas = parseFloat(document.getElementById('monto_total_facturas')?.value) || 0;
        
        let totalGastos = flete + aduana;
        
        let porcentaje = 0;
        if (montoTotalFacturas > 0) {
            porcentaje = (totalGastos * 100) / montoTotalFacturas;
        }
        
        let porcentajeInput = document.getElementById('porcentaje_gastos');
        if (porcentajeInput) {
            porcentajeInput.value = porcentaje.toFixed(2);
        }
    }
    
    // Agregar event listeners
    document.getElementById('flete')?.addEventListener('input', calcularPorcentajeGastos);
    document.getElementById('flete')?.addEventListener('change', calcularPorcentajeGastos);
    document.getElementById('aduana')?.addEventListener('input', calcularPorcentajeGastos);
    document.getElementById('aduana')?.addEventListener('change', calcularPorcentajeGastos);
    document.getElementById('monto_total_facturas')?.addEventListener('input', calcularPorcentajeGastos);
    document.getElementById('monto_total_facturas')?.addEventListener('change', calcularPorcentajeGastos);
    
    // Inicializar cálculo (por si cambian los valores)
    document.addEventListener('DOMContentLoaded', function() {
        calcularPorcentajeGastos();
    });
    
    // Loading al enviar formulario
    document.getElementById('formEditarContenedor')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnGuardar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        }
    });
</script>
@endsection

@push('styles')
<style>
    .input-group-text { border-color: #dee2e6; }
    .form-control:focus, .form-select:focus { border-color: #f59e0b; box-shadow: 0 0 0 0.2rem rgba(245,158,11,.15); }
    #porcentaje_gastos { letter-spacing: .03em; }
</style>
@endpush