@extends('layout.layout_dashboard')

@section('title', 'Registrar Factura')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>Registrar Factura
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Registrar Factura</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Tarjeta de Información del Proveedor -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>Información del Proveedor
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $proveedor->Nombre }}"
                             class="img-fluid rounded-circle border border-primary"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <div class="mt-2">
                            @if($proveedor->Estatus == 0)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><th>Nombre:</th><td><strong>{{ $proveedor->Nombre }}</strong></td>
                                    <tr><th>Rif/Cédula:</th><td>{{ $proveedor->Rif_Cedula ?: 'N/A' }}</td>
                                    <tr><th>Teléfono:</th><td>{{ $proveedor->TelefonoMovil ?: $proveedor->TelefonoFijo ?: 'N/A' }}</td>
                                    <tr><th>Email:</th><td>{{ $proveedor->CorreoElectronico ?: 'N/A' }}</td>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <td><th>Dirección:</th><td>{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 50) }}</td>
                                    <tr><th>Tipo:</th>
                                        <td>
                                            @if($proveedor->Tipo == 0)
                                                <span class="badge bg-primary">Mercancía</span>
                                            @else
                                                <span class="badge bg-info">Servicio</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Registro de Factura -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-file-text me-2"></i>Nueva Factura
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.facturas.guardar') }}" method="POST" id="formRegistrarFactura">
                    @csrf
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">
                    <input type="hidden" name="tipo" value="{{ $proveedor->Tipo }}">
                    
                    <div class="row">
                        <!-- Contenedor con botón para crear nuevo -->
                        <div class="col-md-6 mb-3">
                            <label for="contenedor_id" class="form-label">Contenedor</label>
                            <div class="input-group">
                                <select name="contenedor_id" id="contenedor_id" class="form-select">
                                    <option value="0">Seleccione un valor</option>
                                    @foreach($contenedores as $contenedor)
                                        <option value="{{ $contenedor->Id }}" {{ old('contenedor_id') == $contenedor->Id ? 'selected' : '' }}>
                                            {{ $contenedor->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('cpanel.contenedores.crear') }}" 
                                    class="btn btn-outline-primary"
                                    title="Crear nuevo contenedor"
                                    data-bs-toggle="tooltip">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                            </div>
                            <small class="text-muted">Seleccione un contenedor existente o cree uno nuevo</small>
                        </div>
                        
                        <!-- Sumar flete en Factura (Checkbox) -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" 
                                       name="es_cargar_flete" id="es_cargar_flete" 
                                       value="1" {{ old('es_cargar_flete') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="es_cargar_flete">
                                    Sumar flete en Factura
                                </label>
                            </div>
                        </div>
                        
                        <!-- Fecha de Creación -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_creacion" class="form-label">Fecha *</label>
                            <input type="date" name="fecha_creacion" id="fecha_creacion" 
                                   class="form-control @error('fecha_creacion') is-invalid @enderror" 
                                   value="{{ old('fecha_creacion', date('Y-m-d')) }}" required>
                            @error('fecha_creacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Traspaso -->
                        <div class="col-md-6 mb-3">
                            <label for="traspaso" class="form-label">Traspaso</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="traspaso" id="traspaso" 
                                       class="form-control" value="{{ old('traspaso', 0) }}">
                            </div>
                        </div>
                        
                        <!-- Estatus -->
                        <div class="col-md-6 mb-3">
                            <label for="estatus" class="form-label">Estatus *</label>
                            <select name="estatus" id="estatus" class="form-select" required>
                                <option value="" selected>Seleccione un valor</option>
                                <option value="1">En Proceso</option>
                                <option value="2">Recibiendo</option>
                                <option value="4">Recibida</option>
                                <option value="3">Pagada</option>
                                <option value="0">Anulada</option>
                            </select>
                            <small class="text-muted">El estatus "En Proceso" permite editar la factura</small>
                            @error('estatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-2">
                        <i class="bi bi-info-circle me-2"></i>
                        Los detalles de la factura (productos) se podrán agregar después de crear la factura.
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btnGuardarFactura">
                                <i class="bi bi-save me-1"></i> Generar Factura
                            </button>
                            <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                               class="btn btn-secondary">
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
    document.getElementById('formRegistrarFactura')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnGuardarFactura');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        }
    });
</script>
@endsection