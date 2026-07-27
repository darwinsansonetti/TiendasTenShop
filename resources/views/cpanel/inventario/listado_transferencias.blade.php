@extends('layout.layout_dashboard')

@section('title', 'Transferencias')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="bi bi-arrow-left-right text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Transferencias</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Muestra el listado de transferencias entre sucursales</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Transferencias</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>TRANSFERENCIAS
                        <small class="ms-2" style="color:rgba(255,255,255,0.8);font-size:0.72rem;font-weight:400;">
                            Muestra el listado de transferencias entre sucursales
                        </small>
                    </h6>
                    <a href="{{ route('cpanel.distribucion.transferencia-crear') }}" 
                       class="btn btn-light btn-sm fw-semibold"
                       style="font-size:0.78rem;background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-plus-circle me-1"></i>Nueva
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tablaTransferencias">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NÚM. TRANSACCIÓN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. ORIGEN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. DESTINO</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ITEMS</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">UNIDADES</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:90px;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transferencias as $transferencia)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-muted" style="font-size:0.85rem;">
                                    {{ $transferencia->FechaFormateada ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="fw-bold text-dark" style="font-size:0.88rem;font-family:monospace;">
                                        {{ $transferencia->Numero ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $transferencia->sucursal_origen ?? 'N/A' }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @php
                                            $destinos = explode(', ', $transferencia->sucursales_destino ?? '');
                                        @endphp
                                        @foreach($destinos as $destino)
                                            <li><small>{{ $destino }}</small></li>
                                        @endforeach
                                        @if(empty($destinos) || (count($destinos) == 1 && empty($destinos[0])))
                                            <li><small class="text-muted">Sin destino</small></li>
                                        @endif
                                    </ul>
                                </td>
                                <td class="text-center fw-bold">{{ $transferencia->CantidadItems ?? 0 }}</td>
                                <td class="text-center">{{ number_format($transferencia->CantidadEmitida ?? 0, 0) }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $transferencia->EstatusBadgeStyle ?? 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)' }};font-size:0.75rem;">
                                        {{ $transferencia->EstatusTexto ?? 'Desconocido' }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center" style="width:90px;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('cpanel.distribucion.transferencia-crear', ['id' => $transferencia->TransferenciaId]) }}" 
                                        class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:28px;height:28px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                        title="Editar"
                                        data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                style="width:28px;height:28px;background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25);"
                                                onclick="eliminarTransferencia({{ $transferencia->TransferenciaId }})"
                                                title="Eliminar"
                                                data-bs-toggle="tooltip">
                                            <i class="bi bi-trash" style="font-size:0.75rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay transferencias en edición
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transferencias->count() > 0)
            <div class="card-footer border-0 py-3 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <small class="text-muted">
                        Mostrando {{ $transferencias->count() }} transferencias en edición
                    </small>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // FINALIZAR TRANSFERENCIA
    // ============================================
    function finalizarTransferencia(transferenciaId) {
        Swal.fire({
            title: '¿Finalizar transferencia?',
            text: 'Esta acción marcará la transferencia como finalizada',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Finalizando transferencia',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(`/cpanel/inventario/transferencias/${transferenciaId}/finalizar`, {
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
                            title: '¡Transferencia finalizada!',
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
                    Swal.fire('Error', 'Error al finalizar la transferencia', 'error');
                });
            }
        });
    }

    // ============================================
    // ELIMINAR TRANSFERENCIA
    // ============================================
    function eliminarTransferencia(transferenciaId) {
        Swal.fire({
            title: '¿Eliminar transferencia?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // ✅ Construir URL con route() y reemplazo
                const url = '{{ route("cpanel.distribucion.transferencia.eliminar", ["id" => ":id"]) }}'
                    .replace(':id', transferenciaId);

                fetch(url, {
                    method: 'DELETE',
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
                            title: '¡Eliminada!',
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
                    Swal.fire('Error', 'Error al eliminar la transferencia', 'error');
                });
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaTransferencias tbody tr:hover { background: #f8fafc; }
    .btn-circle-sm {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush