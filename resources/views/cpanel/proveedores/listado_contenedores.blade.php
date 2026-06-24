@extends('layout.layout_dashboard')

@section('title', 'Listado de Contenedores')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                  <i class="bi bi-boxes text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Listado de Contenedores</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Gestión de contenedores de mercancía</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Contenedores</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Tabla de Contenedores -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-box-seam me-2"></i>Contenedores Activos
                        <span class="badge bg-white text-primary ms-2 fw-semibold" style="font-size:0.75rem;">
                            {{ $contenedores->count() }}
                        </span>
                    </h6>
                    <a href="{{ route('cpanel.contenedores.crear') }}"
                       class="btn btn-light btn-sm fw-semibold"
                       style="font-size:0.8rem;">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Contenedor
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaContenedores">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NOMBRE</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FLETE ($)</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ADUANA ($)</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ORIGEN</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PORCENTAJE</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:140px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contenedores as $contenedor)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:32px;height:32px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                            <i class="bi bi-box-seam text-white" style="font-size:0.8rem;"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $contenedor->Nombre }}</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    $ {{ number_format($contenedor->Flete ?? 0, 2) }}
                                </td>
                                <td class="text-end fw-semibold text-warning">
                                    $ {{ number_format($contenedor->Aduana ?? 0, 2) }}
                                </td>
                                <td>
                                    @if($contenedor->Origen)
                                        <span class="badge rounded-pill text-bg-light border" style="font-size:0.78rem;">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $contenedor->Origen }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.85rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill"
                                          style="background:rgba(59,130,246,0.1);color:#1d4ed8;font-size:0.82rem;font-weight:600;">
                                        {{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }}%
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Ver Detalle -->
                                        <a href="{{ route('cpanel.contenedores.detalle', $contenedor->Id) }}"
                                           class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);"
                                           title="Ver detalle" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                        <!-- Editar -->
                                        <a href="{{ route('cpanel.contenedores.editar', $contenedor->Id) }}"
                                           class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                           title="Editar" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                        </a>
                                        <!-- Eliminar -->
                                        <button type="button"
                                                class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                onclick="eliminarContenedor({{ $contenedor->Id }}, '{{ $contenedor->Nombre }}')"
                                                title="Eliminar" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                                             style="width:56px;height:56px;background:rgba(59,130,246,0.08);">
                                            <i class="bi bi-boxes text-primary" style="font-size:1.6rem;opacity:.5;"></i>
                                        </div>
                                        <p class="text-muted mb-0" style="font-size:0.9rem;">No hay contenedores registrados</p>
                                        <a href="{{ route('cpanel.contenedores.crear') }}" class="btn btn-primary btn-sm mt-1">
                                            <i class="bi bi-plus-circle me-1"></i>Crear primero
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contenedores->count() > 0)
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ $contenedores->count() }} contenedor{{ $contenedores->count() != 1 ? 'es' : '' }} registrado{{ $contenedores->count() != 1 ? 's' : '' }}
                </small>
            </div>
            @endif
        </div>
        
    </div>
</div>

@endsection

@section('js')
<script>
    // ============================================
    // ELIMINAR CONTENEDOR
    // ============================================
    function eliminarContenedor(contenedorId, contenedorNombre) {
        Swal.fire({
            title: '¿Eliminar contenedor?',
            html: `Estás a punto de eliminar el contenedor <strong>${contenedorNombre}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch(`/cpanel/contenedores/${contenedorId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El contenedor ha sido eliminado correctamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el contenedor', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaContenedores tbody tr:hover {
        background-color: #f8fafc;
    }
    #tablaContenedores .btn:hover {
        filter: brightness(0.92);
    }
</style>
@endpush