@extends('layout.layout_dashboard')

@section('title', 'Editar Empleado Interno')

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
      <div class="col-sm-6"><h3 class="mb-0">Empleados Internos</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Empleados Internos</li>
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
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit me-2"></i>Formulario de Edición
                </h3>
            </div>
            
            <form action="{{ route('cpanel.empleados.internos.actualizar', $usuarioInterno->Id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">
                        <!-- Foto -->
                        <div class="col-md-3 text-center">
                            @php
                                $fotoPerfil = $usuarioInterno->FotoPerfil ?? '';
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/usuarios/',
                                    $fotoPerfil,
                                    'assets/img/adminlte/img/default.png'
                                );
                            @endphp
                            
                            <div class="mb-3">
                                <img src="{{ $imgSrc }}" 
                                     class="img-fluid rounded-circle mb-3 border border-info shadow"
                                     style="width: 180px; height: 180px; object-fit: cover;"
                                     id="previewFoto">
                                
                                <div>
                                    <label for="foto" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-camera me-1"></i>Cambiar foto
                                    </label>
                                    <input type="file" class="d-none" id="foto" name="foto" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Datos -->
                        <div class="col-md-9">
                            <div class="row">
                                <!-- ID (solo lectura) -->
                                <div class="col-md-12 mb-3">
                                    <label>ID de Usuario</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $usuarioInterno->Id }}" disabled>
                                </div>
                                
                                <!-- Nombre Completo -->
                                <div class="col-md-12 mb-3">
                                    <label>Nombre Completo *</label>
                                    <input type="text" name="NombreCompleto" class="form-control" 
                                           value="{{ old('NombreCompleto', $usuarioInterno->NombreCompleto) }}" required>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="Email" class="form-control" 
                                           value="{{ old('Email', $usuarioInterno->Email) }}" required readonly>
                                </div>
                                
                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label>Teléfono</label>
                                    <input type="text" name="PhoneNumber" class="form-control" 
                                           value="{{ old('PhoneNumber', $usuarioInterno->PhoneNumber) }}">
                                </div>
                                
                                <!-- Fecha Nacimiento -->
                                <div class="col-md-6 mb-3">
                                    <label>Fecha de Nacimiento</label>
                                    <input type="date" name="FechaNacimiento" class="form-control" 
                                           value="{{ old('FechaNacimiento', $usuarioInterno->FechaNacimiento ? \Carbon\Carbon::parse($usuarioInterno->FechaNacimiento)->format('Y-m-d') : '') }}">
                                </div>
                                
                                <!-- Fecha Ingreso -->
                                <div class="col-md-6 mb-3">
                                    <label>Fecha de Ingreso</label>
                                    <input type="date" name="FechaIngreso" class="form-control" 
                                           value="{{ old('FechaIngreso', $usuarioInterno->FechaCreacion ? \Carbon\Carbon::parse($usuarioInterno->FechaCreacion)->format('Y-m-d') : '') }}">
                                </div>
                                
                                <!-- Dirección -->
                                <div class="col-md-12 mb-3">
                                    <label>Dirección</label>
                                    <textarea name="Direccion" class="form-control" rows="2">{{ old('Direccion', $usuarioInterno->Direccion) }}</textarea>
                                </div>
                                
                                <!-- Sucursal -->
                                <div class="col-md-6 mb-3">
                                    <label>Sucursal</label>
                                    <select name="SucursalId" class="form-select">
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->ID }}" 
                                                {{ old('SucursalId', $usuarioInterno->SucursalId) == $sucursal->ID ? 'selected' : '' }}>
                                                {{ $sucursal->Nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Rol -->
                                <div class="col-md-6 mb-3">
                                    <label>Cargo</label>
                                    <select name="rol_id" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->Id }}" 
                                                {{ old('rol_id', $userRoleId) == $rol->Id ? 'selected' : '' }}>
                                                {{ $rol->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Estado Activo -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="EsActivo" class="form-check-input" 
                                               id="EsActivo" value="1" 
                                               {{ old('EsActivo', $usuarioInterno->EsActivo) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="EsActivo">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Usuario Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
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
    document.getElementById('foto').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>
@endsection