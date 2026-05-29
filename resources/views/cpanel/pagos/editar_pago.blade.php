@extends('layout.layout_dashboard')

@section('title', 'Editar Pago')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Editar Pago
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    @if($proveedor)
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}">
                            {{ $proveedor->Nombre }}
                        </a>
                    </li>
                    @endif
                    <li class="breadcrumb-item active">Editar Pago #{{ $pago->NumeroOperacion }}</li>
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
                    <i class="bi bi-cash-stack me-2"></i>Editar Pago
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body">
                
                <form action="{{ route('cpanel.pagos.actualizar', $pago->ID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Número de Operación -->
                        <div class="col-md-6 mb-3">
                            <label for="numero_operacion" class="form-label">Número de Operación</label>
                            <input type="text" name="numero_operacion" id="numero_operacion" 
                                   class="form-control" value="{{ old('numero_operacion', $pago->NumeroOperacion) }}">
                        </div>
                        
                        <!-- Fecha -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha *</label>
                            <input type="date" name="fecha" id="fecha" 
                                   class="form-control" value="{{ old('fecha', date('Y-m-d', strtotime($pago->Fecha))) }}" required>
                        </div>
                        
                        <!-- Monto en Divisas -->
                        <div class="col-md-4 mb-3">
                            <label for="monto_divisa" class="form-label">Monto en Divisas (USD) *</label>
                            <input type="number" step="0.01" name="monto_divisa" id="monto_divisa" 
                                   class="form-control" value="{{ old('monto_divisa', $pago->MontoDivisaAbonado) }}" required>
                        </div>
                        
                        <!-- Tasa de Cambio -->
                        <div class="col-md-4 mb-3">
                            <label for="tasa_cambio" class="form-label">Tasa de Cambio *</label>
                            <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio" 
                                   class="form-control" value="{{ old('tasa_cambio', $pago->TasaDeCambio) }}" required>
                        </div>
                        
                        <!-- Monto en Bolívares (calculado) -->
                        <div class="col-md-4 mb-3">
                            <label for="monto_bs" class="form-label">Monto en Bolívares (Bs)</label>
                            <input type="number" step="0.01" name="monto_bs" id="monto_bs" 
                                   class="form-control" value="{{ old('monto_bs', $pago->MontoAbonado) }}" readonly>
                            <small class="text-muted">Se calcula automáticamente</small>
                        </div>
                        
                        <!-- Forma de Pago (solo lectura) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Forma de Pago</label>
                            <input type="text" class="form-control" value="{{ $formaPagoMap[$pago->FormaDePago] ?? 'Desconocido' }}" readonly disabled>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="2" 
                                      class="form-control">{{ old('descripcion', $pago->Descripcion) }}</textarea>
                        </div>
                        
                        <!-- Comprobante actual -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Comprobante actual</label>
                            <div>
                                @if($pago->UrlComprobante)
                                    @php
                                        $comprobanteSrc = FileHelper::getOrDownloadFile(
                                            'images/comprobantes/',
                                            $pago->UrlComprobante,
                                            'assets/img/adminlte/img/no-image.png'
                                        );
                                    @endphp
                                    <a href="{{ $comprobanteSrc }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye me-1"></i>Ver comprobante actual
                                    </a>
                                @else
                                    <span class="text-muted">No hay comprobante asociado</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Nuevo comprobante -->
                        <div class="col-md-6 mb-3">
                            <label for="comprobante" class="form-label">Nuevo Comprobante</label>
                            <input type="file" name="comprobante" id="comprobante" 
                                   class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Formatos: JPG, PNG, PDF (Max 5MB). Al subir uno nuevo, reemplazará al anterior.</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> Al modificar el monto o la tasa de cambio, el pago se redistribuirá automáticamente entre las facturas pendientes del proveedor.
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Guardar Cambios
                            </button>
                            <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}" 
                               class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script>
    // Calcular monto en Bs automáticamente
    const montoDivisaInput = document.getElementById('monto_divisa');
    const tasaCambioInput = document.getElementById('tasa_cambio');
    const montoBsInput = document.getElementById('monto_bs');
    
    function calcularMontoBs() {
        const montoDivisa = parseFloat(montoDivisaInput.value) || 0;
        const tasaCambio = parseFloat(tasaCambioInput.value) || 0;
        const montoBs = montoDivisa * tasaCambio;
        montoBsInput.value = montoBs.toFixed(2);
    }
    
    montoDivisaInput.addEventListener('keyup', calcularMontoBs);
    montoDivisaInput.addEventListener('change', calcularMontoBs);
    tasaCambioInput.addEventListener('keyup', calcularMontoBs);
    tasaCambioInput.addEventListener('change', calcularMontoBs);
    
    calcularMontoBs();
</script>

@endsection