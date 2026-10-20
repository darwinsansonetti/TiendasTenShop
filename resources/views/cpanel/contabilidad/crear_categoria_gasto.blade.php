@extends('layout.layout_dashboard')

@section('title', 'Nueva Categoría de Gasto')

@php
    $hdrBg       = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon     = 'tags';
    $hdrTitle    = 'Nueva Categoría';
    $hdrSubtitle = 'Registrar una nueva categoría de gasto';
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:{{ $hdrBg }};">
                        <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.contabilidad.gastos_categoria_listado') }}">Categorías de Gastos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nueva</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <form action="{{ route('cpanel.contabilidad.categoria.guardar') }}" method="POST" id="formNuevaCategoria">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-plus-circle me-2"></i>Datos de la Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nombre" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1" style="color:#dc2626;"></i>
                                Nombre de la categoría <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   class="form-control" 
                                   maxlength="150" 
                                   required
                                   autofocus
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Mantenimiento">
                            <small class="text-muted">
                                El nombre debe ser único. No puede coincidir con una categoría existente.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="submit" 
                                class="btn px-4 fw-semibold text-white"
                                style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-save me-1"></i>Guardar Categoría
                        </button>
                        <a href="{{ route('cpanel.contabilidad.gastos_categoria_listado') }}" 
                           class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('formNuevaCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre').value.trim();
    
    if (nombre.length === 0) {
        e.preventDefault();
        Swal.fire('Error', 'Debe ingresar un nombre para la categoría.', 'warning');
        return;
    }
    
    // Deshabilitar botón al enviar
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';
});

@if(session('error'))
    Swal.fire({
        title: 'Error',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonColor: '#dc2626'
    });
@endif
</script>
@endsection