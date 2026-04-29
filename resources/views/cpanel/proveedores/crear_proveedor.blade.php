@extends('layout.layout_dashboard')

@section('title', 'Agregar Proveedor')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="fas fa-truck me-2"></i>Agregar Proveedor
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Agregar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle me-2"></i>Formulario de Registro
                </h3>
            </div>
            
            <form action="{{ route('cpanel.proveedores.guardar') }}" method="POST" id="proveedorForm" enctype="multipart/form-data">
                @csrf
                
                <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda - Logo/Imagen -->
                        <div class="col-md-3 text-center">
                            <div class="mb-3">
                                <img src="{{ asset('assets/img/adminlte/img/proveedor_default.png') }}" 
                                    class="img-fluid rounded-circle mb-3 border border-primary shadow"
                                    style="width: 150px; height: 150px; object-fit: cover;"
                                    id="previewLogo">
                                
                                <div>
                                    <label for="logo" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-camera me-1"></i>Subir logo
                                    </label>
                                    <input type="file" class="d-none" id="logo" name="logo" accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-2">Formatos: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                        </div>
                        
                        <!-- Columna derecha - Datos -->
                        <div class="col-md-9">
                            <!-- Fila 1: Tipo y Nombre -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="Tipo" class="form-label">
                                        <i class="fas fa-tag me-2"></i>Tipo *
                                    </label>
                                    <select name="Tipo" id="SelectTipoProveedor" class="form-control @error('Tipo') is-invalid @enderror" required>
                                        <option value="">Seleccione un valor</option>
                                        <option value="0" {{ old('Tipo') == '0' ? 'selected' : '' }}>Mercancía</option>
                                        <option value="1" {{ old('Tipo') == '1' ? 'selected' : '' }}>Servicio</option>
                                    </select>
                                    @error('Tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="Nombre" class="form-label">
                                        <i class="fas fa-building me-2"></i>Nombre *
                                    </label>
                                    <input type="text" 
                                           name="Nombre" 
                                           id="Nombre" 
                                           class="form-control @error('Nombre') is-invalid @enderror" 
                                           value="{{ old('Nombre') }}"
                                           placeholder="Nombre del proveedor"
                                           maxlength="150"
                                           required>
                                    @error('Nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Fila 2: Rif/Cédula y Fecha Registro -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="RifCedula" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>Rif/Cédula
                                    </label>
                                    <input type="text" 
                                           name="RifCedula" 
                                           id="RifCedula" 
                                           class="form-control @error('RifCedula') is-invalid @enderror" 
                                           value="{{ old('RifCedula') }}"
                                           placeholder="Cédula o Rif"
                                           maxlength="50">
                                    @error('RifCedula')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="FechaCreacion" class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Fecha de Registro *
                                    </label>
                                    <input type="date" 
                                           name="FechaCreacion" 
                                           id="FechaCreacion" 
                                           class="form-control @error('FechaCreacion') is-invalid @enderror" 
                                           value="{{ old('FechaCreacion', date('Y-m-d')) }}"
                                           required>
                                    @error('FechaCreacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Fila 3: Teléfono Móvil y Teléfono Fijo -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="TelefonoMovil" class="form-label">
                                        <i class="fas fa-mobile-alt me-2"></i>Teléfono Móvil *
                                    </label>
                                    <input type="tel" 
                                           name="TelefonoMovil" 
                                           id="TelefonoMovil" 
                                           class="form-control @error('TelefonoMovil') is-invalid @enderror" 
                                           value="{{ old('TelefonoMovil') }}"
                                           placeholder="Teléfono móvil o celular"
                                           maxlength="20"
                                           required>
                                    @error('TelefonoMovil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="TelefonoFijo" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Teléfono Fijo
                                    </label>
                                    <input type="tel" 
                                           name="TelefonoFijo" 
                                           id="TelefonoFijo" 
                                           class="form-control @error('TelefonoFijo') is-invalid @enderror" 
                                           value="{{ old('TelefonoFijo') }}"
                                           placeholder="Teléfono fijo"
                                           maxlength="20">
                                    @error('TelefonoFijo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Fila 4: Correo Electrónico y Estatus -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="CorreoElectronico" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                    </label>
                                    <input type="email" 
                                           name="CorreoElectronico" 
                                           id="CorreoElectronico" 
                                           class="form-control @error('CorreoElectronico') is-invalid @enderror" 
                                           value="{{ old('CorreoElectronico') }}"
                                           placeholder="correo@ejemplo.com"
                                           maxlength="100">
                                    @error('CorreoElectronico')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="Estatus" class="form-label">
                                        <i class="fas fa-check-circle me-2"></i>Estatus *
                                    </label>
                                    <select name="Estatus" id="Estatus" class="form-control @error('Estatus') is-invalid @enderror" required>
                                        <option value="">Seleccione un valor</option>
                                        <option value="1" {{ old('Estatus') == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('Estatus') == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('Estatus')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Fila 5: Dirección (ancho completo) -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="Direccion" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Dirección
                                    </label>
                                    <textarea name="Direccion" 
                                              id="Direccion" 
                                              class="form-control @error('Direccion') is-invalid @enderror" 
                                              rows="2"
                                              placeholder="Escriba la dirección..."
                                              maxlength="500">{{ old('Direccion') }}</textarea>
                                    @error('Direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Datos Bancarios (solo para Servicios) -->
                            <div id="divBancoProveedor" style="display: none;">
                                <hr>
                                <h5 class="mb-3"><i class="fas fa-university me-2"></i>Datos Bancarios</h5>
                                
                                <!-- Fila: Banco y Número de Cuenta -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="BancoId" class="form-label">
                                            <i class="fas fa-building me-2"></i>Banco
                                        </label>
                                        <select name="BancoId" id="BancoId" class="form-control @error('BancoId') is-invalid @enderror">
                                            <option value="">Seleccione un banco</option>
                                            @foreach($bancos as $banco)
                                                <option value="{{ $banco->ID }}" {{ old('BancoId') == $banco->ID ? 'selected' : '' }}>
                                                    {{ $banco->Nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('BancoId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="NumeroDeCuenta" class="form-label">
                                            <i class="fas fa-credit-card me-2"></i>Número de Cuenta
                                        </label>
                                        <input type="text" 
                                               name="NumeroDeCuenta" 
                                               id="NumeroDeCuenta" 
                                               class="form-control @error('NumeroDeCuenta') is-invalid @enderror" 
                                               value="{{ old('NumeroDeCuenta') }}"
                                               placeholder="Número de cuenta bancaria"
                                               maxlength="50">
                                        @error('NumeroDeCuenta')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Proveedor
                    </button>
                    <a href="{{ route('cpanel.proveedor.mercancia.listado') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectTipo = document.getElementById('SelectTipoProveedor');
        const divBanco = document.getElementById('divBancoProveedor');
        
        function toggleBancoDiv() {
            if (selectTipo.value === '1') {
                divBanco.style.display = 'block';
            } else {
                divBanco.style.display = 'none';
            }
        }
        
        selectTipo.addEventListener('change', toggleBancoDiv);
        toggleBancoDiv(); // Ejecutar al cargar
        
        // ==========================
        // PREVISUALIZACIÓN DE LOGO
        // ==========================
        const inputLogo = document.getElementById('logo');
        const previewLogo = document.getElementById('previewLogo');
        
        inputLogo.addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewLogo.src = e.target.result;
            }
            if (this.files[0]) {
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection