@extends('layout.layout_dashboard')

@section('title', 'Recepciones de Proveedores')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-truck me-2"></i>Recepciones de Proveedores
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Recepciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Botón Nueva Recepción -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('cpanel.recepciones.nuevo') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Nueva Recepción
                </a>
            </div>
        </div>

        <!-- Tabla de Recepciones -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-list-check me-2"></i>Listado de Recepciones
                </h3>
                <div class="card-tools">
                    <span class="badge bg-primary">{{ $listaRecepciones->count() }} recepciones</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tablaRecepciones">
                        <thead class="table-dark">
                            <tr>
                                <th>N° Recepción</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Factura</th>
                                <th>Sucursal</th>
                                <th>Estatus</th>
                                <th class="text-center" style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listaRecepciones as $recepcion)
                            <tr>
                                <td><strong>{{ $recepcion->Numero ?? $recepcion->RecepcionId }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($recepcion->FechaRecepcion)->format('d/m/Y') }}</td>
                                <td>{{ $recepcion->Proveedor->Nombre ?? 'N/A' }}</td>
                                <td>{{ $recepcion->Factura->Numero ?? 'N/A' }}</td>
                                <td>{{ $recepcion->SucursalDestino->Nombre ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $estatusActual = (int)$recepcion->Estatus;
                                        
                                        $estatusTexto = match($estatusActual) {
                                            1 => 'En Proceso',
                                            2 => 'Completada',
                                            3 => 'Anulada',
                                            default => 'Desconocido'
                                        };
                                        $estatusColor = match($estatusActual) {
                                            1 => 'warning',
                                            2 => 'success',
                                            3 => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $estatusColor }}">{{ $estatusTexto }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if($recepcion->Estatus == 1)
                                        <a href="{{ route('cpanel.recepciones.editar', $recepcion->RecepcionId) }}" 
                                            class="btn btn-sm btn-outline-warning"
                                            title="Editar recepción">
                                                <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="eliminarRecepcion({{ $recepcion->RecepcionId }})"
                                                title="Eliminar recepción">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                    No hay recepciones registradas en el período seleccionado
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

<!-- Scripts para exportar Excel y PDF -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function eliminarRecepcion(id) {
        Swal.fire({
            title: '¿Eliminar recepción?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url("cpanel/recepciones") }}/${id}`, {
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
                        Swal.fire('Eliminado', 'Recepción eliminada correctamente', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al eliminar la recepción', 'error');
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
</style>
@endpush