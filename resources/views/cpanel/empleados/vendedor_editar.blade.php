@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Vendedor')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Empleado</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Empleado</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<div class="app-content">
    <div class="container-fluid">  

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit me-2"></i>Formulario de Edición
                </h3>
                <div class="card-tools">
                    <span class="badge bg-{{ $origen == 'pos' ? 'primary' : 'info' }} p-2">
                        <i class="fas fa-{{ $origen == 'pos' ? 'store' : 'cloud' }} me-1"></i>
                        Empleado
                    </span>
                </div>
            </div>
            
            <form action="{{ route('cpanel.empleados.vendedor.actualizar', $vendedor->UsuarioId ?? $vendedor->Id) }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda - Foto -->
                        <div class="col-md-3 text-center">
                            @php
                                $fotoPerfil = $vendedor->FotoPerfil ?? '';
                                $imgSrc = App\Helpers\FileHelper::getOrDownloadFile(
                                    'images/usuarios/',
                                    $fotoPerfil,
                                    'assets/img/adminlte/img/default.png'
                                );
                            @endphp
                            
                            <div class="mb-3">
                                <div class="image-preview-container">
                                    <img src="{{ $imgSrc }}" 
                                         class="img-fluid rounded-circle mb-3 border border-warning shadow img-zoomable"
                                         style="width: 180px; height: 180px; object-fit: cover;"
                                         id="previewFoto"
                                         onclick="zoomImagen(this)"
                                         data-full-image="{{ $imgSrc }}"
                                         data-description="{{ $vendedor->NombreCompleto ?? 'Foto de perfil' }}">
                                </div>
                                
                                <div class="mt-3">
                                    <label for="foto" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-camera me-1"></i>Cambiar foto
                                    </label>
                                    <input type="file" 
                                           class="d-none" 
                                           id="foto" 
                                           name="foto"
                                           accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-2">Formatos: JPG, PNG, GIF</small>
                            </div>
                            
                            @if($origen == 'identity')
                                <div class="alert alert-info mt-3 p-2 small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Usuario de Identity
                                </div>
                            @endif
                        </div>
                        
                        <!-- Columna derecha - Datos -->
                        <div class="col-md-9">
                            <div class="row">
                                <!-- ID (solo lectura) -->
                                <div class="col-md-6 mb-3">
                                    <label for="id" class="form-label">ID de Usuario</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           id="id" 
                                           value="{{ $vendedor->UsuarioId ?? $vendedor->Id ?? '' }}"
                                           disabled>
                                </div>
                                
                                <!-- Vendedor ID -->
                                <div class="col-md-6 mb-3">
                                    <label for="VendedorId" class="form-label">Código de Vendedor</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           id="VendedorId" 
                                           value="{{ $vendedor->VendedorId ?? '' }}"
                                           disabled>
                                </div>
                                
                                <!-- Nombre Completo -->
                                <div class="col-md-12 mb-3">
                                    <label>Nombre Completo *</label>
                                    <input type="text" 
                                           class="form-control @error('NombreCompleto') is-invalid @enderror" 
                                           id="NombreCompleto" 
                                           name="NombreCompleto"
                                           value="{{ old('NombreCompleto', $vendedor->NombreCompleto ?? '') }}"
                                           required>
                                    @error('NombreCompleto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Email (NO EDITABLE) -->
                                <div class="col-md-6 mb-3">
                                    <label for="Email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email
                                    </label>
                                    <input type="email" 
                                           class="form-control bg-light" 
                                           id="Email" 
                                           value="{{ $vendedor->Email ?? '' }}"
                                           disabled
                                           readonly>
                                    <small class="text-muted">El email no puede ser modificado</small>
                                </div>
                                
                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label for="PhoneNumber" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Teléfono
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('PhoneNumber') is-invalid @enderror" 
                                           id="PhoneNumber" 
                                           name="PhoneNumber"
                                           value="{{ old('PhoneNumber', $vendedor->PhoneNumber ?? '') }}">
                                    @error('PhoneNumber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Fecha de Nacimiento (EDITABLE) -->
                                <div class="col-md-6 mb-3">
                                    <label for="FechaNacimiento" class="form-label">
                                        <i class="fas fa-birthday-cake me-1"></i>Fecha de Nacimiento
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('FechaNacimiento') is-invalid @enderror" 
                                           id="FechaNacimiento" 
                                           name="FechaNacimiento"
                                           value="{{ old('FechaNacimiento', $vendedor->FechaNacimiento ? \Carbon\Carbon::parse($vendedor->FechaNacimiento)->format('Y-m-d') : '') }}">
                                    @error('FechaNacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Fecha de Ingreso (EDITABLE) -->
                                <div class="col-md-6 mb-3">
                                    <label for="FechaIngreso" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Fecha de Ingreso
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('FechaIngreso') is-invalid @enderror" 
                                           id="FechaIngreso" 
                                           name="FechaIngreso"
                                           value="{{ old('FechaIngreso', $vendedor->FechaCreacion ? \Carbon\Carbon::parse($vendedor->FechaCreacion)->format('Y-m-d') : '') }}">
                                    @error('FechaIngreso')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Dirección -->
                                <div class="col-md-12 mb-3">
                                    <label for="Direccion" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Dirección
                                    </label>
                                    <textarea class="form-control @error('Direccion') is-invalid @enderror" 
                                              id="Direccion" 
                                              name="Direccion" 
                                              rows="2">{{ old('Direccion', $vendedor->Direccion ?? '') }}</textarea>
                                    @error('Direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Sucursal -->
                                <div class="col-md-6 mb-3">
                                    <label for="SucursalId" class="form-label">
                                        <i class="fas fa-store me-1"></i>Sucursal
                                    </label>
                                    <select class="form-select @error('SucursalId') is-invalid @enderror" 
                                            id="SucursalId" 
                                            name="SucursalId">
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->ID }}" 
                                                {{ old('SucursalId', $vendedor->SucursalId) == $sucursal->ID ? 'selected' : '' }}>
                                                {{ $sucursal->Nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('SucursalId')
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
                                               {{ old('EsActivo', $vendedor->EsActivo ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="EsActivo">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                            <a href="{{ route('cpanel.empleados.vendedores') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Previsualización de imagen
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Simular click en el input file
        document.querySelector('label[for="foto"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('foto').click();
        });
    });

    function zoomImagen(img) {
        Swal.fire({
            imageUrl: img.src,
            imageAlt: img.alt,
            title: img.alt,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '2em',
            background: '#fff',
            customClass: {
                image: 'img-fluid rounded'
            }
        });
    }

</script>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .empty-state {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .table th {
        white-space: nowrap;
    }
    
    .badge.bg-opacity-10 {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    
    @media print {
        .card-header, .card-footer, .btn-group, .app-content-header, .breadcrumb {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 11px;
        }
    }

    /* ===== ESTILOS PARA ZOOM DE IMAGENES ===== */
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }

    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Overlay para zoom */
    .image-zoom-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeInOverlay 0.3s ease-out;
    }

    .image-zoom-container {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .image-zoom-container img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        animation: zoomInImage 0.3s ease-out;
    }

    .image-zoom-close {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        background: rgba(0, 0, 0, 0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .image-zoom-close:hover {
        color: #ff6b6b;
        background: rgba(0, 0, 0, 0.7);
    }

    .image-description {
        color: white;
        text-align: center;
        margin-top: 20px;
        font-size: 1.1rem;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 20px;
        border-radius: 8px;
        max-width: 80%;
    }

    /* Animaciones */
    @keyframes fadeInOverlay {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes zoomInImage {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Para tablets y móviles */
    @media (max-width: 768px) {
        .image-zoom-container {
            max-width: 95%;
        }
        
        .image-zoom-container img {
            max-height: 70vh;
        }
        
        .image-zoom-close {
            top: -35px;
            right: 0;
            font-size: 35px;
            width: 45px;
            height: 45px;
        }
        
        .image-description {
            font-size: 1rem;
            padding: 8px 16px;
            max-width: 90%;
        }
    }

    @media (max-width: 576px) {
        .image-zoom-container img {
            max-height: 60vh;
        }
        
        .image-zoom-close {
            top: -30px;
            font-size: 30px;
            width: 40px;
            height: 40px;
        }
        
        .image-description {
            font-size: 0.9rem;
            margin-top: 15px;
        }
    }

    /* Para impresión */
    @media print {
        .image-zoom-overlay {
            display: none !important;
        }
        
        .img-zoomable {
            cursor: default !important;
        }
    }

    /* Estilos para el modal de actualización */
    #modalActualizarPVP .modal-header {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }

    #resumenCambio {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .input-group-text {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .form-control-lg {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .badge.bg-light {
        border: 1px solid #dee2e6;
    }

    /* Estilo para el botón de actualizar */
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }

    .bg-bronze {
        background-color: #cd7f32 !important;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem !important;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .btn-group .btn i {
        font-size: 0.9rem;
    }
    
    .text-muted small {
        font-size: 0.75rem;
    }
</style>
@endsection