@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Asignar Bono')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    Asignar Bono -
                    <small class="text-muted ms-2">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ $empleado->NombreCompleto ?? 'Empleado' }}
                    </small>
                </h3>
                <div class="mt-1">
                    <small class="text-muted">
                        <i class="fas fa-store me-1"></i>{{ $sucursal->Nombre ?? 'N/A' }}
                    </small>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.dashboard') }}">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active">Asignar Bono</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Filtro de fechas -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtro de Bonos
                    </h3>
                    <button type="button" 
                            class="btn btn-sm btn-success" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAsignarBono">
                        <i class="fas fa-plus me-1"></i> + Asignar Bono
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('cpanel.empleados.bonos.asignar', ['tipo' => $tipo, 'id' => $empleadoId ?? $usuarioId]) }}" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Período</label>
                        <input type="month" 
                            name="periodo" 
                            class="form-control"
                            value="{{ request('periodo', sprintf('%04d-%02d', $anioSeleccionado, $mesSeleccionado)) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de bonos existentes -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list me-2"></i>
                    Bonos Asignados
                </h3>
                <div class="card-tools">
                    <span class="badge bg-secondary">
                        Período: {{ $meses[$mesSeleccionado] }} {{ $anioSeleccionado }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha Asignación</th>
                                <th>Motivo</th>
                                <th>Tipo</th>
                                <th>Monto VES</th>
                                <th>Monto USD</th>
                                <th>Tasa</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bonos as $bono)
                                @php
                                    $bonoFecha = \Carbon\Carbon::parse($bono->FechaCreacion);
                                @endphp
                                <tr>
                                    <td>{{ $bonoFecha->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="text-muted">
                                            <i class="fas fa-comment me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($bono->Motivo ?? 'Sin motivo', 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($bono->TipoBono == 'BS')
                                            <span class="badge bg-primary">Bolívares</span>
                                        @else
                                            <span class="badge bg-info">Divisas</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($bono->MontoBs, 2) }}</td>
                                    <td>{{ number_format($bono->MontoDivisa, 2) }}</td>
                                    <td>{{ number_format($bono->Tasa, 2) }}</td>
                                    <td class="text-center">
                                        @if($bono->EsPagado == 0)
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-success">Pagado</span>
                                        @endif
                                    </td>
                                    <!-- Acción -->
                                    <td class="text-center">
                                        @if($bono->EsPagado == 0)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarBono({{ $bono->ID }})"
                                                    title="Eliminar bono"
                                                    data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay bonos registrados en este período
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

<!-- Modal Asignar Bono -->
<div class="modal fade" id="modalAsignarBono" tabindex="-1" aria-labelledby="modalAsignarBonoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAsignarBonoLabel">
                    <i class="fas fa-plus-circle me-2"></i>Asignar Nuevo Bono
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cpanel.empleados.bonos.guardar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="hidden" name="empleado_id" value="{{ $empleadoId }}">
                    <input type="hidden" name="usuario_id" value="{{ $usuarioId }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Período del Bono</label>
                                <input type="month" 
                                       name="periodo_bono" 
                                       class="form-control"
                                       value="{{ sprintf('%04d-%02d', $anioSeleccionado, $mesSeleccionado) }}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tasa de Cambio (VES)</label>
                                <input type="number" 
                                       name="tasa" 
                                       class="form-control" 
                                       step="0.01" 
                                       value="{{ $tasa->Valor ?? 0 }}"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Bono</label>
                                <select name="tipo_bono" id="tipo_bono_modal" class="form-control" required>
                                    <option value="BS">Bolívares (VES)</option>
                                    <option value="USD">Divisas (USD)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" id="label_monto_modal">Monto</label>
                                <input type="number" 
                                       name="monto" 
                                       id="monto_modal"
                                       class="form-control" 
                                       step="0.01" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Motivo del Bono</label>
                                <textarea name="motivo" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Ej: Bonificacion..."
                                          required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Guardar Bono
                    </button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const tasa = {{ $tasa->Valor ?? 0 }};

        // Para el modal
        const tipoBonoModal = document.getElementById('tipo_bono_modal');
        const labelMontoModal = document.getElementById('label_monto_modal');
        const inputMontoModal = document.getElementById('monto_modal');
        
        function actualizarLabelModal() {
            if (tipoBonoModal.value === 'BS') {
                labelMontoModal.innerHTML = 'Monto en Bolívares (VES)';
                inputMontoModal.placeholder = 'Ej: 500.00';
            } else {
                labelMontoModal.innerHTML = 'Monto en Divisas (USD)';
                inputMontoModal.placeholder = 'Ej: 100.00';
            }
        }
        
        if (tipoBonoModal) {
            tipoBonoModal.addEventListener('change', actualizarLabelModal);
            actualizarLabelModal();
        }
    });

    function eliminarBono(id) {
        Swal.fire({
            title: '¿Eliminar bono?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("cpanel.empleados.bonos.eliminar") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Eliminado!', data.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ocurrió un error al eliminar el bono', 'error');
                });
            }
        });
    }
</script>
@endsection