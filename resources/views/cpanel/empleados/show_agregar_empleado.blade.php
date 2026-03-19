@extends('layout.layout_dashboard')

@section('title', 'Agregar Empleado Interno')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Agregar Empleado Interno
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.empleados.personal') }}">Empleados Internos</a>
                    </li>
                    <li class="breadcrumb-item active">Agregar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle me-2"></i>Formulario de Registro
                </h3>
            </div>
            
            <form action="{{ route('cpanel.empleados.internos.guardar') }}" 
                  method="POST" 
                  enctype="multipart/form-data"
                  id="empleadoForm">
                @csrf
                
                <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda - Foto (opcional) -->
                        <div class="col-md-3 text-center">
                            <div class="mb-3">
                                <img src="{{ asset('assets/img/adminlte/img/default.png') }}" 
                                     class="img-fluid rounded-circle mb-3 border border-success shadow"
                                     style="width: 180px; height: 180px; object-fit: cover;"
                                     id="previewFoto">
                                
                                <div>
                                    <label for="foto" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-camera me-1"></i>Subir foto
                                    </label>
                                    <input type="file" class="d-none" id="foto" name="foto" accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-2">Opcional</small>
                            </div>
                        </div>
                        
                        <!-- Columna derecha - Datos -->
                        <div class="col-md-9">
                            <div class="row">
                                <!-- Nombre Completo -->
                                <div class="col-md-12 mb-3">
                                    <label for="NombreCompleto" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre Completo *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('NombreCompleto') is-invalid @enderror" 
                                           id="NombreCompleto" 
                                           name="NombreCompleto" 
                                           value="{{ old('NombreCompleto') }}"
                                           placeholder="Ingrese el nombre completo"
                                           required>
                                    @error('NombreCompleto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="Email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('Email') is-invalid @enderror" 
                                           id="Email" 
                                           name="Email" 
                                           value="{{ old('Email') }}"
                                           placeholder="correo@ejemplo.com"
                                           required>
                                    @error('Email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label for="PhoneNumber" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Teléfono
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('PhoneNumber') is-invalid @enderror" 
                                           id="PhoneNumber" 
                                           name="PhoneNumber" 
                                           value="{{ old('PhoneNumber') }}"
                                           placeholder="Número de teléfono">
                                    @error('PhoneNumber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password"
                                               placeholder="Mínimo 8 caracteres"
                                               required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Confirmar Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmar Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation"
                                               placeholder="Repita la contraseña"
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Fecha Nacimiento -->
                                <div class="col-md-6 mb-3">
                                    <label for="FechaNacimiento" class="form-label">
                                        <i class="fas fa-birthday-cake me-2"></i>Fecha de Nacimiento
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('FechaNacimiento') is-invalid @enderror" 
                                           id="FechaNacimiento" 
                                           name="FechaNacimiento" 
                                           value="{{ old('FechaNacimiento') }}">
                                    @error('FechaNacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Fecha Ingreso -->
                                <div class="col-md-6 mb-3">
                                    <label for="FechaIngreso" class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Fecha de Ingreso
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('FechaIngreso') is-invalid @enderror" 
                                           id="FechaIngreso" 
                                           name="FechaIngreso" 
                                           value="{{ old('FechaIngreso', now()->format('Y-m-d')) }}">
                                    @error('FechaIngreso')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Dirección -->
                                <div class="col-md-12 mb-3">
                                    <label for="Direccion" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Dirección
                                    </label>
                                    <textarea class="form-control @error('Direccion') is-invalid @enderror" 
                                              id="Direccion" 
                                              name="Direccion" 
                                              rows="2">{{ old('Direccion') }}</textarea>
                                    @error('Direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Sucursal -->
                                <div class="col-md-6 mb-3">
                                    <label for="SucursalId" class="form-label">
                                        <i class="fas fa-store me-2"></i>Sucursal
                                    </label>
                                    <select class="form-select @error('SucursalId') is-invalid @enderror" 
                                            id="SucursalId" 
                                            name="SucursalId">
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->ID }}" 
                                                {{ old('SucursalId') == $sucursal->ID ? 'selected' : '' }}>
                                                {{ $sucursal->Nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('SucursalId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Rol -->
                                <div class="col-md-6 mb-3">
                                    <label for="rol_id" class="form-label">
                                        <i class="fas fa-user-tag me-2"></i>Cargo *
                                    </label>
                                    <select class="form-select @error('rol_id') is-invalid @enderror" 
                                            id="rol_id" 
                                            name="rol_id" 
                                            required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->Id }}" 
                                                {{ old('rol_id') == $rol->Id ? 'selected' : '' }}>
                                                {{ $rol->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('rol_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Vendedor ID (opcional) -->
                                <div class="col-md-6 mb-3">
                                    <label for="VendedorId" class="form-label">
                                        <i class="fas fa-id-badge me-2"></i>ID de Vendedor (POS)
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('VendedorId') is-invalid @enderror" 
                                           id="VendedorId" 
                                           name="VendedorId" 
                                           value="{{ old('VendedorId') }}"
                                           placeholder="Ej: VDD001">
                                    <small class="text-muted">Si el empleado también es vendedor en POS</small>
                                    @error('VendedorId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Estado Activo -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="EsActivo" 
                                               name="EsActivo" 
                                               value="1"
                                               {{ old('EsActivo', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="EsActivo">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Usuario Activo
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Medidor de fortaleza de contraseña -->
                                <div class="col-md-12 mb-3" id="strengthMeter" style="display: none;">
                                    <label>Fortaleza de la contraseña:</label>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small class="text-muted" id="strengthText"></small>
                                </div>
                                
                                <!-- Requisitos de contraseña -->
                                <div class="col-md-12 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body py-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                La contraseña debe tener al menos:
                                            </small>
                                            <ul class="mb-0 mt-1 small">
                                                <li id="req-length" class="text-muted">
                                                    <i class="fas fa-circle me-1"></i>8 caracteres
                                                </li>
                                                <li id="req-lower" class="text-muted">
                                                    <i class="fas fa-circle me-1"></i>Una letra minúscula
                                                </li>
                                                <li id="req-upper" class="text-muted">
                                                    <i class="fas fa-circle me-1"></i>Una letra mayúscula
                                                </li>
                                                <li id="req-number" class="text-muted">
                                                    <i class="fas fa-circle me-1"></i>Un número
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Guardar Empleado
                    </button>
                    <a href="{{ route('cpanel.empleados.personal') }}" class="btn btn-secondary">
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
    // Toggle para mostrar/ocultar contraseña
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = fieldId === 'password' 
            ? document.getElementById('togglePasswordIcon')
            : document.getElementById('toggleConfirmIcon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Previsualización de foto
    document.getElementById('foto').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Validación de contraseña en tiempo real
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        // Requisitos
        const hasLength = password.length >= 8;
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        
        // Actualizar colores de requisitos
        document.getElementById('req-length').className = hasLength ? 'text-success' : 'text-muted';
        document.getElementById('req-lower').className = hasLower ? 'text-success' : 'text-muted';
        document.getElementById('req-upper').className = hasUpper ? 'text-success' : 'text-muted';
        document.getElementById('req-number').className = hasNumber ? 'text-success' : 'text-muted';
        
        if (password.length > 0) {
            strengthMeter.style.display = 'block';
            
            // Calcular fortaleza
            let strength = 0;
            if (hasLength) strength += 25;
            if (hasLower) strength += 25;
            if (hasUpper) strength += 25;
            if (hasNumber) strength += 25;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 50) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Contraseña débil';
            } else if (strength < 75) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Contraseña media';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Contraseña fuerte';
            }
        } else {
            strengthMeter.style.display = 'none';
        }
    });

    // Validar formulario antes de enviar
    document.getElementById('empleadoForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        
        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
            return;
        }
        
        // Validar requisitos mínimos de contraseña
        if (password.length < 8) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña debe tener al menos 8 caracteres'
            });
        }
    });
</script>
@endsection