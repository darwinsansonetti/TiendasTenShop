@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Asignar Deducción')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#ef4444,#dc2626);">
                  <i class="bi bi-dash-circle text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Asignar Deducción</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">
                    <i class="bi bi-person me-1"></i>{{ $empleado->NombreCompleto ?? 'Empleado' }}
                    &nbsp;·&nbsp;<i class="bi bi-shop me-1"></i>{{ $sucursal->Nombre ?? 'N/A' }}
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.dashboard') }}">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active">Asignar Deducción</li>
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
                        Filtro de Deducciones
                    </h3>
                    <button type="button" 
                            class="btn btn-sm btn-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAsignarDeduccion">
                        <i class="fas fa-plus me-1"></i> + Asignar Deducción
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('cpanel.empleados.deduccion.asignar', ['tipo' => $tipo, 'id' => $empleadoId ?? $usuarioId]) }}" class="row g-3">
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
        
        <!-- Tabla de deducciones existentes -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list me-2"></i>
                    Deducciones Asignadas
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
                            @forelse($deducciones as $deduccion)
                                @php
                                    $deduccionFecha = \Carbon\Carbon::parse($deduccion->FechaCreacion);
                                @endphp
                                <tr>
                                    <td>{{ $deduccionFecha->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="text-muted">
                                            <i class="fas fa-comment me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($deduccion->Motivo ?? 'Sin motivo', 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($deduccion->TipoDeduccion == 'BS')
                                            <span class="badge bg-primary">Bolívares</span>
                                        @else
                                            <span class="badge bg-info">Divisas</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($deduccion->MontoBs, 2) }}</td>
                                    <td>{{ number_format($deduccion->MontoDivisa, 2) }}</td>
                                    <td>{{ number_format($deduccion->Tasa, 2) }}</td>
                                    <td class="text-center">
                                        @if($deduccion->EsPagado == 0)
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-success">Deducido</span>
                                        @endif
                                    </td>
                                    <!-- Acción -->
                                    <td class="text-center">
                                        @if($deduccion->EsPagado == 0)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarDeduccion({{ $deduccion->ID }})"
                                                    title="Eliminar deducción"
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
                                        No hay deducciones registradas en este período
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

<!-- Modal Asignar Deducción -->
<div class="modal fade" id="modalAsignarDeduccion" tabindex="-1" aria-labelledby="modalAsignarDeduccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalAsignarDeduccionLabel">
                    <i class="fas fa-minus-circle me-2"></i>Asignar Nueva Deducción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cpanel.empleados.deducciones.guardar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="hidden" name="empleado_id" value="{{ $empleadoId }}">
                    <input type="hidden" name="usuario_id" value="{{ $usuarioId }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Período de la Deducción</label>
                                <input type="month" 
                                       name="periodo_deduccion" 
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
                                <label class="form-label">Tipo de Deducción</label>
                                <select name="tipo_deduccion" id="tipo_deduccion_modal" class="form-control" required>
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
                                <label class="form-label">Motivo de la Deducción</label>
                                <textarea name="motivo" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Ej: Descuento por préstamo, multa, ajuste, etc."
                                          required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i>Guardar Deducción
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
        const tipoDeduccionModal = document.getElementById('tipo_deduccion_modal');
        const labelMontoModal = document.getElementById('label_monto_modal');
        const inputMontoModal = document.getElementById('monto_modal');
        
        function actualizarLabelModal() {
            if (tipoDeduccionModal.value === 'BS') {
                labelMontoModal.innerHTML = 'Monto en Bolívares (VES)';
                inputMontoModal.placeholder = 'Ej: 500.00';
            } else {
                labelMontoModal.innerHTML = 'Monto en Divisas (USD)';
                inputMontoModal.placeholder = 'Ej: 100.00';
            }
        }
        
        if (tipoDeduccionModal) {
            tipoDeduccionModal.addEventListener('change', actualizarLabelModal);
            actualizarLabelModal();
        }
    });

    function eliminarDeduccion(id) {
        Swal.fire({
            title: '¿Eliminar deducción?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("cpanel.empleados.deducciones.eliminar") }}', {
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
                        Swal.fire('¡Eliminada!', data.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Ocurrió un error al eliminar la deducción', 'error');
                });
            }
        });
    }
</script>

<style>
    /* Estilos adicionales si son necesarios */
    textarea {
        resize: vertical;
    }
</style>
@endsection