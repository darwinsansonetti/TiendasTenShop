@extends('layout.layout_dashboard')

@section('title', 'Editar Factura')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                  <i class="bi bi-pencil-square text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Factura #{{ $factura->Numero }}</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Modificar datos de la factura del proveedor</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $factura->ProveedorId) }}">
                            Detalle Proveedor
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Editar Factura #{{ $factura->Numero }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="{{ route('cpanel.facturas.actualizar', $factura->ID) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-file-text me-2"></i>
                        Datos de la Factura
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cpanel.proveedores.detalle', $factura->ProveedorId) }}" 
                           class="btn btn-sm btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="Numero">Número de Factura *</label>
                                <input type="text" 
                                       class="form-control @error('Numero') is-invalid @enderror" 
                                       id="Numero" 
                                       name="Numero" 
                                       value="{{ old('Numero', $factura->Numero) }}"
                                       required>
                                @error('Numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="Serie">Serie</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="Serie" 
                                       name="Serie" 
                                       value="{{ old('Serie', $factura->Serie) }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="FechaCreacion">Fecha de Emisión *</label>
                                <input type="date" 
                                       class="form-control @error('FechaCreacion') is-invalid @enderror" 
                                       id="FechaCreacion" 
                                       name="FechaCreacion" 
                                       value="{{ old('FechaCreacion', date('Y-m-d', strtotime($factura->FechaCreacion))) }}"
                                       required>
                                @error('FechaCreacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="FechaDespacho">Fecha de Despacho</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="FechaDespacho" 
                                       name="FechaDespacho" 
                                       value="{{ old('FechaDespacho', $factura->FechaDespacho ? date('Y-m-d', strtotime($factura->FechaDespacho)) : '') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="ProveedorId">Proveedor *</label>
                                <select class="form-control @error('ProveedorId') is-invalid @enderror" 
                                        id="ProveedorId" 
                                        name="ProveedorId" 
                                        required>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->ProveedorId }}" 
                                            {{ old('ProveedorId', $factura->ProveedorId) == $proveedor->ProveedorId ? 'selected' : '' }}>
                                            {{ $proveedor->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ProveedorId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="SucursalId">Sucursal *</label>
                                <select class="form-control @error('SucursalId') is-invalid @enderror" 
                                        id="SucursalId" 
                                        name="SucursalId" 
                                        required>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->ID }}" 
                                            {{ old('SucursalId', $factura->SucursalId) == $sucursal->ID ? 'selected' : '' }}>
                                            {{ $sucursal->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('SucursalId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="Descripcion">Descripción</label>
                                <textarea class="form-control" 
                                          id="Descripcion" 
                                          name="Descripcion" 
                                          rows="3">{{ old('Descripcion', $factura->Descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Guardar Cambios
                    </button>
                    <a href="{{ route('cpanel.proveedores.detalle', $factura->ProveedorId) }}" 
                       class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Mostrar productos actuales (solo lectura en edición) -->
        @if($detalles->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-box-seam me-2"></i>
                    Productos de la Factura
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo USD</th>
                                <th class="text-end">Subtotal USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalles as $detalle)
                            <tr>
                                <td><code>{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                <td>{{ $detalle->producto_nombre }}</td>
                                <td class="text-end">{{ number_format($detalle->CantidadEmitida, 2) }}</td>
                                <td class="text-end">$ {{ number_format($detalle->CostoDivisa, 2) }}</td>
                                <td class="text-end">$ {{ number_format($detalle->CantidadEmitida * $detalle->CostoDivisa, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">$ {{ number_format($factura->monto_actual ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="bi bi-info-circle me-2"></i>
                    Los productos no se pueden modificar desde esta vista. Si necesita ajustar cantidades o precios, contacte al administrador.
                </div>
            </div>
        </div>
        @endif
        
    </div>
</div>

@endsection