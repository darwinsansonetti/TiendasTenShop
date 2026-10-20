@extends('layout.layout_dashboard')

@section('title', 'Editar Categoría de Gasto')

@php
    $hdrBg       = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon     = 'pencil-square';
    $hdrTitle    = 'Editar Categoría';
    $hdrSubtitle = 'Actualizar datos de la categoría de gasto';
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
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <form action="{{ route('cpanel.contabilidad.categoria.actualizar', $categoria->CategoriaId) }}" 
              method="POST" 
              id="formEditarCategoria">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-pencil-square me-2"></i>Datos de la Categoría
                        </h6>
                        <span class="badge bg-white text-dark">
                            <i class="bi bi-hash me-1"></i>{{ $categoria->CategoriaId }}
                        </span>
                    </div>
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
                                   value="{{ old('nombre', $categoria->Nombre) }}"
                                   placeholder="Ej: Mantenimiento">
                            <small class="text-muted">
                                El nombre debe ser único. No puede coincidir con otra categoría existente.
                            </small>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-info-circle me-1" style="color:#dc2626;"></i>
                                Estatus actual
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                @if($categoria->EsActivo == 1)
                                    <span class="badge bg-success" style="font-size:0.85rem;">
                                        <i class="bi bi-check-circle me-1"></i>Activa
                                    </span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:0.85rem;">
                                        <i class="bi bi-pause-circle me-1"></i>Inactiva
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================================================ --}}
            {{-- ACCIONES --}}
            {{-- ================================================ --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-2">
                            <button type="submit" 
                                    class="btn px-4 fw-semibold text-white"
                                    style="background:{{ $hdrBg }};border:none;">
                                <i class="bi bi-save me-1"></i>Actualizar Categoría
                            </button>
                            <a href="{{ route('cpanel.contabilidad.gastos_categoria_listado') }}" 
                               class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            @if($categoria->EsActivo == 1)
                                <button type="button" 
                                        class="btn btn-warning fw-semibold"
                                        onclick="toggleCategoria({{ $categoria->CategoriaId }}, 'desactivar')">
                                    <i class="bi bi-pause-circle me-1"></i>Desactivar Categoría
                                </button>
                            @else
                                <button type="button" 
                                        class="btn btn-success fw-semibold"
                                        onclick="toggleCategoria({{ $categoria->CategoriaId }}, 'activar')">
                                    <i class="bi bi-play-circle me-1"></i>Activar Categoría
                                </button>
                            @endif
                        </div>
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
document.getElementById('formEditarCategoria').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre').value.trim();
    
    if (nombre.length === 0) {
        e.preventDefault();
        Swal.fire('Error', 'Debe ingresar un nombre para la categoría.', 'warning');
        return;
    }
    
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Actualizando...';
});

// ============================================
// TOGGLE ACTIVAR/DESACTIVAR
// ============================================
function toggleCategoria(id, accion) {
    const esDesactivar = accion === 'desactivar';
    
    Swal.fire({
        title: `¿${esDesactivar ? 'Desactivar' : 'Activar'} categoría?`,
        html: esDesactivar 
            ? 'La categoría dejará de aparecer en el listado de gastos.<br><strong>Podés reactivarla desde SQL si es necesario.</strong>'
            : 'La categoría volverá a estar disponible en el listado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: esDesactivar ? '#f59e0b' : '#10b981',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch(`{{ url('cpanel/contabilidad/categoria/toggle') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (data.redirigir) {
                            window.location.href = "{{ route('cpanel.contabilidad.gastos_categoria_listado') }}";
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
        }
    });
}

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