@extends('layout.layout_dashboard')

@section('title', 'Listado de Contenedores')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>Listado de Contenedores
                </h3>
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
        
        <!-- Botón Nuevo Contenedor -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('cpanel.contenedores.crear') }}" 
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Contenedor
                </a>
            </div>
        </div>

        <!-- Tabla de Contenedores -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-box-seam me-2"></i>Contenedores Activos
                </h3>
                <div class="card-tools">
                    <span class="badge bg-primary">{{ $contenedores->count() }} contenedores</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tablaContenedores">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-end">Flete ($)</th>
                                <th class="text-end">Aduana ($)</th>
                                <th>Origen</th>
                                <th class="text-end">Porcentaje (%)</th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contenedores as $contenedor)
                            <tr>
                                <td>{{ $contenedor->Nombre }}</td>
                                <td class="text-end">$ {{ number_format($contenedor->Flete ?? 0, 2) }}</td>
                                <td class="text-end">$ {{ number_format($contenedor->Aduana ?? 0, 2) }}</td>
                                <td>{{ $contenedor->Origen ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }} %</td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Ver Detalle -->
                                        <a href="{{ route('cpanel.contenedores.detalle', $contenedor->Id) }}" 
                                           class="btn btn-sm btn-outline-info"
                                           title="Ver detalle del contenedor"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        <!-- Editar -->
                                        <a href="{{ route('cpanel.contenedores.editar', $contenedor->Id) }}" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Editar contenedor"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <!-- Eliminar -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="eliminarContenedor({{ $contenedor->Id }}, '{{ $contenedor->Nombre }}')"
                                                title="Eliminar contenedor"
                                                data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                    No hay contenedores registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
    .table-dark {
        background-color: #343a40 !important;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,0.02);
    }
    .btn-group-sm .btn {
        margin: 0 2px;
    }
</style>
@endpush