@extends('layout.layout_dashboard')

@section('title', 'Auditorías de Inventario')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Auditorías de Inventario</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Revisión de productos pendientes</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Auditorías de Inventario</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Auditorías Activas
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        Últimos 6 meses
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° AUDITORÍA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUCURSAL</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PENDIENTES</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">RESUELTOS</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">RECHAZADOS</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">TOTAL</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditorias as $auditoria)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark" style="font-size:0.88rem;font-family:monospace;">
                                        {{ $auditoria->Numero }}
                                    </span>
                                </td>
                                <td>{{ $auditoria->sucursal_nombre ?? 'N/A' }}</td>
                                <td class="text-muted" style="font-size:0.85rem;">
                                    {{ \Carbon\Carbon::parse($auditoria->Fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill"
                                          style="background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25);font-size:0.78rem;">
                                        {{ $auditoria->total_pendientes ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill"
                                          style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.78rem;">
                                        {{ $auditoria->total_resueltos ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill"
                                          style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.78rem;">
                                        {{ $auditoria->total_rechazados ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">
                                    {{ $auditoria->total_detalles ?? 0 }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $estatusInfo = $estatusMap[$auditoria->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'bg-secondary'];
                                        $claseEstatus = $estatusInfo['clase'] ?? '';
                                        $badgeStyle = str_contains($claseEstatus, 'success')
                                            ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)'
                                            : 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                    @endphp
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                        style="{{ $badgeStyle }};font-size:0.75rem;">
                                        {{ $estatusInfo['texto'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('cpanel.inventario.auditoria.detalle', $auditoria->AuditoriaInventarioId) }}"
                                       class="btn btn-sm btn-outline-primary rounded-2"
                                       style="font-size:0.75rem;">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-clipboard-check text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay auditorías de inventario activas</p>
                                    <small class="text-muted">Las auditorías aparecerán aquí cuando se carguen inventarios con productos pendientes</small>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function cerrarAuditoria(auditoriaId) {
        Swal.fire({
            title: '¿Cerrar auditoría?',
            text: 'Esta acción marcará la auditoría como cerrada. Los productos pendientes quedarán como no resueltos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Cerrando auditoría',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/cpanel/inventario/auditoria/${auditoriaId}/cerrar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Auditoría cerrada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al cerrar la auditoría', 'error');
                });
            }
        });
    }
</script>
@endsection