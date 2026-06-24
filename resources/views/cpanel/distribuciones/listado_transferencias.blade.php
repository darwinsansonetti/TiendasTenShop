@extends('layout.layout_dashboard')

@section('title', 'Listado de Distribuciones y Transferencias')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-list-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Listado de Distribuciones y Transferencias
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Transferencias pendientes por recibir
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Listado de Distribuciones y Transferencias</li>
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
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Transferencias Pendientes
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $transferencias->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NÚM. OPERACIÓN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. ORIGEN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. DESTINO</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ITEMS</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">UND. ENVIADAS</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">UND. DISP.</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transferencias as $transferencia)
                            @php
                                $estatus = $estatusMap[$transferencia->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary text-white'];
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-muted" style="font-size:0.88rem;">
                                    {{ \Carbon\Carbon::parse($transferencia->Fecha)->format('d/m/Y') }}
                                </td>
                                <td class="fw-bold text-dark">{{ $transferencia->Numero }}</td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $transferencia->Origen ?? 'N/A' }}
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $transferencia->Destino ?? 'N/A' }}
                                </td>
                                <td class="text-center fw-semibold">{{ $transferencia->CantidadItems ?? 0 }}</td>
                                <td class="text-end fw-semibold">{{ number_format($transferencia->CantidadEmitida ?? 0, 0) }}</td>
                                <td class="text-end">{{ number_format($transferencia->CantidadDisponible ?? 0, 0) }}</td>
                                <td class="text-center">
                                    <span class="{{ $estatus['clase'] }} rounded-pill px-2 py-1 fw-semibold" style="font-size:0.75rem;">
                                        {{ $estatus['texto'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Botón Ver Detalle --}}
                                        <a href="{{ route('cpanel.transferencias.detalle', $transferencia->TransferenciaId) }}"
                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                        style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                        title="Ver detalle">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                        
                                        {{-- Botón Cancelar Transferencia --}}
                                        <button type="button"
                                            class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                            onclick="cancelarTransferencia({{ $transferencia->TransferenciaId }})"
                                            title="Cancelar transferencia">
                                            <i class="bi bi-x-circle" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                        style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-inbox text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay transferencias pendientes</p>
                                    <small class="text-muted">Las nuevas transferencias aparecerán aquí</small>
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
<!-- ✅ SOLO SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function cancelarTransferencia(id) {
        console.log('🔍 ID recibido:', id);
        
        // Validar ID
        if (!id || isNaN(id)) {
            console.error('❌ ID inválido');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'ID de transferencia inválido'
            });
            return;
        }
        
        // ✅ Generar URL usando route() (como en la vista que funciona)
        const url = '{{ route("cpanel.distribuciones.cancelar", ":id") }}'.replace(':id', id);
        console.log('🌐 URL:', url);
        
        Swal.fire({
            title: '¿Cancelar transferencia?',
            text: 'Esta acción devolverá los productos a la sucursal de origen',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Cancelando transferencia',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                // ✅ Obtener CSRF con fallback (como en la vista que funciona)
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    console.log('📡 Status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('📦 Datos:', data);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Transferencia cancelada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al cancelar la transferencia'
                        });
                    }
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor: ' + error.message
                    });
                });
            } else {
                console.log('❌ Usuario canceló la operación');
            }
        });
    }
</script>
@endsection