@extends('layout.layout_dashboard')

@section('title', 'Editar Contenedor')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>Editar Contenedor
                </h3>
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
        
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-pencil-square me-2"></i>Editar Contenedor
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.contenedores.actualizar', $contenedor->Id) }}" method="POST" id="formEditarContenedor">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Descripción / Nombre -->
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Descripción *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-card-text"></i>
                                </span>
                                <input type="text" name="nombre" id="nombre" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       value="{{ old('nombre', $contenedor->Nombre) }}" 
                                       placeholder="Descripción o nombre del contenedor" required>
                            </div>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Fecha de Creación -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_creacion" class="form-label">Fecha *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                </span>
                                <input type="date" name="fecha_creacion" id="fecha_creacion" 
                                       class="form-control @error('fecha_creacion') is-invalid @enderror" 
                                       value="{{ old('fecha_creacion', date('Y-m-d', strtotime($contenedor->FechaCreacion))) }}" required>
                            </div>
                            @error('fecha_creacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Flete -->
                        <div class="col-md-6 mb-3">
                            <label for="flete" class="form-label">Flete</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="flete" id="flete" 
                                       class="form-control formato-numerico" 
                                       value="{{ old('flete', $contenedor->Flete ?? 0) }}" 
                                       placeholder="Monto del flete">
                            </div>
                            @error('flete')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- País de Origen -->
                        <div class="col-md-6 mb-3">
                            <label for="origen" class="form-label">País de origen</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <input type="text" name="origen" id="origen" 
                                       class="form-control" 
                                       value="{{ old('origen', $contenedor->Origen) }}" 
                                       placeholder="País de origen">
                            </div>
                            @error('origen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Costo Aduana -->
                        <div class="col-md-6 mb-3">
                            <label for="aduana" class="form-label">Costo Aduana</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="aduana" id="aduana" 
                                       class="form-control formato-numerico" 
                                       value="{{ old('aduana', $contenedor->Aduana ?? 0) }}" 
                                       placeholder="Costo de aduana">
                            </div>
                            @error('aduana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Monto Total de Facturas -->
                        <div class="col-md-6 mb-3">
                            <label for="monto_total_facturas" class="form-label">Monto Total de Facturas</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="monto_total_facturas" id="monto_total_facturas" 
                                       class="form-control formato-numerico" 
                                       value="{{ old('monto_total_facturas', $contenedor->MontoTotalFacturas ?? 0) }}" 
                                       placeholder="Total de facturas asociadas">
                            </div>
                            <small class="text-muted">Suma total de las facturas asociadas a este contenedor</small>
                        </div>
                        
                        <!-- Porcentaje de Gastos (NO EDITABLE) -->
                        <div class="col-md-6 mb-3">
                            <label for="porcentaje_gastos" class="form-label">Porcentaje de Gastos (%)</label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="text" name="porcentaje_gastos" id="porcentaje_gastos" 
                                       class="form-control bg-light" 
                                       value="{{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }}" readonly>
                            </div>
                            <small class="text-muted">Se calcula automáticamente: (Flete + Aduana) / Monto Total Facturas × 100</small>
                        </div>
                        
                        <!-- Estatus -->
                        <div class="col-md-6 mb-3">
                            <label for="estatus" class="form-label">Estatus *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-toggle-on"></i>
                                </span>
                                <select name="estatus" id="estatus" class="form-select @error('estatus') is-invalid @enderror" required>
                                    <option value="">Seleccione un valor</option>
                                    <option value="0" {{ old('estatus', $contenedor->Estatus) == 0 ? 'selected' : '' }}>Nuevo</option>
                                    <option value="2" {{ old('estatus', $contenedor->Estatus) == 2 ? 'selected' : '' }}>En Aduana</option>
                                    <option value="1" {{ old('estatus', $contenedor->Estatus) == 1 ? 'selected' : '' }}>En Tránsito</option>
                                </select>
                            </div>
                            @error('estatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Fecha de Recepción -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_recepcion" class="form-label">Fecha de Recepción</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar-check"></i>
                                </span>
                                <input type="date" name="fecha_recepcion" id="fecha_recepcion" 
                                       class="form-control" 
                                       value="{{ old('fecha_recepcion', $contenedor->FechaRecepcion ? date('Y-m-d', strtotime($contenedor->FechaRecepcion)) : '') }}">
                            </div>
                            @error('fecha_recepcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save me-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
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
    .form-control.bg-light {
        background-color: #e9ecef;
    }
</style>
@endpush