@extends('layout.layout_dashboard')

@section('title', 'Detalle de Auditoría')

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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Auditoría</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Auditoría #{{ $auditoria->Numero }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.inventario.auditoria.listado') }}">Auditorías</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Información de la auditoría --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" 
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Auditoría
                        <span class="ms-2 badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            #{{ $auditoria->Numero }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        @if($auditoria->Estatus == 1)
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background:rgba(16,185,129,0.2);color:#fff;border:1px solid rgba(16,185,129,0.4);font-size:0.78rem;">
                                <i class="bi bi-check-circle me-1"></i>Activa
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background:rgba(107,114,128,0.2);color:#fff;border:1px solid rgba(107,114,128,0.4);font-size:0.78rem;">
                                <i class="bi bi-x-circle me-1"></i>Cerrada
                            </span>
                        @endif
                        <a href="{{ route('cpanel.inventario.auditoria.listado') }}"
                           class="btn btn-light btn-sm fw-semibold"
                           style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">N° Auditoría</p>
                        <p class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">{{ $auditoria->Numero }}</p>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $auditoria->sucursal_nombre }}</p>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha</p>
                        <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($auditoria->Fecha)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-4 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Pendientes</p>
                        <p class="mb-0 fw-bold text-warning">{{ $pendientes }} productos</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de productos --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-list-check me-2"></i>Productos en Auditoría
                    <span class="ms-2 badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $detalles->count() }} productos
                    </span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PRODUCTO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANTIDAD</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalles as $detalle)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    @if($detalle->Codigo || $detalle->producto_codigo)
                                        <span class="badge rounded-2 fw-semibold"
                                              style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;font-family:monospace;">
                                            {{ $detalle->Codigo ?? $detalle->producto_codigo }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.78rem;">Sin código</span>
                                    @endif
                                </td>
                                <td>{{ $detalle->Referencia ?? $detalle->producto_referencia ?? 'N/A' }}</td>
                                <td>{{ $detalle->Descripcion ?? $detalle->producto_nombre ?? 'N/A' }}</td>
                                <td class="text-end fw-bold">{{ $detalle->Cantidad }}</td>
                                <td class="text-center">
                                    @php
                                        $estatusInfo = $detalleEstatusMap[$detalle->detalle_estatus] ?? ['texto' => 'Desconocido', 'clase' => 'bg-secondary'];
                                        $claseEstatus = $estatusInfo['clase'] ?? '';
                                        $badgeStyle = str_contains($claseEstatus, 'success')
                                            ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)'
                                            : (str_contains($claseEstatus, 'warning')
                                                ? 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)'
                                                : 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)');
                                    @endphp
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $badgeStyle }};font-size:0.75rem;">
                                        {{ $estatusInfo['texto'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($auditoria->Estatus == 1 && $detalle->detalle_estatus == 1)
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="btn btn-sm fw-semibold rounded-2"
                                                    onclick="aceptarProducto({{ $auditoria->AuditoriaInventarioId }}, {{ $detalle->AuditoriaInventarioDetalleId }})"
                                                    title="Aceptar este producto"
                                                    data-bs-toggle="tooltip"
                                                    style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.78rem;">
                                                <i class="bi bi-check-circle" style="font-size:0.8rem;"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm fw-semibold rounded-2"
                                                    onclick="rechazarProducto({{ $auditoria->AuditoriaInventarioId }}, {{ $detalle->AuditoriaInventarioDetalleId }})"
                                                    title="Rechazar este producto"
                                                    data-bs-toggle="tooltip"
                                                    style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.78rem;">
                                                <i class="bi bi-x-circle" style="font-size:0.8rem;"></i>
                                            </button>
                                        </div>
                                    @elseif($detalle->detalle_estatus == 0)
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                              style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.75rem;">
                                            <i class="bi bi-check-circle me-1"></i>Aceptado
                                        </span>
                                    @elseif($detalle->detalle_estatus == 2)
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                              style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.75rem;">
                                            <i class="bi bi-x-circle me-1"></i>Rechazado
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.75rem;">Procesado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-clipboard-check text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en esta auditoría</p>
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
    // ============================================
    // ACEPTAR PRODUCTO
    // ============================================
    function aceptarProducto(auditoriaId, detalleId) {
        Swal.fire({
            title: '¿Aceptar este producto?',
            text: 'Se actualizará la existencia del producto con la cantidad indicada',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aceptar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Actualizando producto',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // ✅ Usar route() de Laravel
                const url = '{{ route("cpanel.inventario.auditoria.aceptar", ["id" => "AUDITORIA_ID", "detalleId" => "DETALLE_ID"]) }}'
                    .replace('AUDITORIA_ID', auditoriaId)
                    .replace('DETALLE_ID', detalleId);

                fetch(url, {
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
                            title: '¡Producto aceptado!',
                            text: data.message,
                            timer: 2000,
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
                    Swal.fire('Error', 'Error al aceptar el producto', 'error');
                });
            }
        });
    }

    // ============================================
    // RECHAZAR PRODUCTO
    // ============================================
    function rechazarProducto(auditoriaId, detalleId) {
        Swal.fire({
            title: '¿Rechazar este producto?',
            text: 'No se actualizará la existencia del producto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Rechazando producto',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // ✅ Usar route() de Laravel
                const url = '{{ route("cpanel.inventario.auditoria.rechazar", ["id" => "AUDITORIA_ID", "detalleId" => "DETALLE_ID"]) }}'
                    .replace('AUDITORIA_ID', auditoriaId)
                    .replace('DETALLE_ID', detalleId);

                fetch(url, {
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
                            title: '¡Producto rechazado!',
                            text: data.message,
                            timer: 2000,
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
                    Swal.fire('Error', 'Error al rechazar el producto', 'error');
                });
            }
        });
    }
</script>
@endsection