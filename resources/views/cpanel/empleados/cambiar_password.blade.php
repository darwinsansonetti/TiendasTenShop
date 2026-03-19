@extends('layout.layout_dashboard')

@section('title', 'Cambiar Contraseña')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Cambiar Contraseña</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Cambiar Contraseña</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-shield me-2"></i>
                    {{ $usuarioInterno->NombreCompleto }}
                </h3>
                <div class="card-tools">
                    <span class="badge bg-{{ $usuarioInterno->EsActivo ? 'success' : 'danger' }}">
                        {{ $usuarioInterno->EsActivo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Información del usuario -->
                <div class="row mb-4">
                    <div class="col-md-2 text-center">
                        @php
                            $imgSrc = FileHelper::getOrDownloadFile(
                                'images/usuarios/',
                                $usuarioInterno->FotoPerfil,
                                'assets/img/adminlte/img/default.png'
                            );
                        @endphp
                        <img src="{{ $imgSrc }}" 
                             class="img-fluid rounded-circle border border-danger"
                             style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <div class="col-md-10">
                        <h4>{{ $usuarioInterno->NombreCompleto }}</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-envelope me-2"></i>{{ $usuarioInterno->Email }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-id-card me-2"></i>ID: {{ $usuarioInterno->Id }}
                        </p>
                    </div>
                </div>
                
                <!-- Formulario de cambio de contraseña -->
                <form action="{{ route('cpanel.empleados.internos.password.update', $usuarioInterno->Id) }}" 
                      method="POST" 
                      id="passwordForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Nueva Contraseña
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       placeholder="Repite la contraseña"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Medidor de fortaleza -->
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
                    
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-2"></i>Cambiar Contraseña
                            </button>
                            <a href="{{ route('cpanel.empleados.personal') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
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

    // Validación en tiempo real
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

    // Validar que las contraseñas coincidan antes de enviar
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        
        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
        }
    });
</script>
@endsection