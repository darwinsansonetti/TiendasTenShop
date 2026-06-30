@extends('layout.layout_dashboard')

@section('title', 'Recibir de Sucursal')

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
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Recibir de Sucursal</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Transferencias pendientes por recibir</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Recibir de Sucursal</li>
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
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-list-check me-2"></i>Listado de Transferencias
                        </h6>
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            {{ $transferencias->count() }}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="exportarPDFTransferencias()">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </button>
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="exportarExcelTransferencias()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <a href="{{ route('cpanel.recepciones.nuevo') }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(255,255,255,0.85);color:#059669;border:1px solid rgba(255,255,255,0.4);font-size:0.8rem;">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Transferencia
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaTransferencias">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">NÚM. OPERACIÓN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. ORIGEN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUC. DESTINO</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ITEMS</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">UND. ENVIADAS</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">UND. DISP.</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">AVANCE</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transferencias as $item)
                            @php
                                $porcentaje = $item->PorcentajeRecibido ?? 0;
                                if ($porcentaje < 25) $color = 'bg-danger';
                                elseif ($porcentaje < 50) $color = 'bg-warning';
                                elseif ($porcentaje < 75) $color = 'bg-info';
                                else $color = 'bg-success';
                                
                                $estatusTexto = '';
                                $estatusBadge = '';
                                switch($item->Estatus) {
                                    case 3:
                                        $estatusTexto = 'Registrada';
                                        $estatusBadge = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                        break;
                                    case 4:
                                        $estatusTexto = 'Recibiendo';
                                        $estatusBadge = 'background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25)';
                                        break;
                                    case 5:
                                        $estatusTexto = 'Disponible';
                                        $estatusBadge = 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)';
                                        break;
                                    default:
                                        $estatusTexto = 'Desconocido';
                                        $estatusBadge = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-muted" style="font-size:0.88rem;">
                                    {{ \Carbon\Carbon::parse($item->Fecha)->format('d/m/Y') }}
                                </td>
                                <td class="fw-bold text-dark">{{ $item->Numero }}</td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $item->Origen ?? 'N/A' }}</td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $item->Destino ?? 'N/A' }}</td>
                                <td class="text-center fw-semibold">{{ $item->CantidadItems ?? 0 }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item->CantidadEmitida ?? 0, 0) }}</td>
                                <td class="text-end">{{ number_format($item->CantidadDisponible ?? 0, 0) }}</td>
                                <td style="min-width: 160px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $color }}" role="progressbar"
                                                 style="width: {{ $porcentaje }}%;"
                                                 aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted" style="font-size:0.7rem;white-space:nowrap;">
                                            {{ number_format($porcentaje, 1) }}%
                                        </small>
                                    </div>
                                    <small class="text-muted" style="font-size:0.7rem;">
                                        {{ number_format($item->CantidadRecibida ?? 0) }} / {{ number_format($item->CantidadEmitida ?? 0) }} Und.
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $estatusBadge }};font-size:0.75rem;">
                                        {{ $estatusTexto }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('cpanel.transferencias.detallesucursal', $item->TransferenciaId) }}"
                                           class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                           title="Recibir Distribución" data-bs-toggle="tooltip">
                                            <i class="bi bi-arrow-down-circle" style="font-size:0.8rem;"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                            onclick="cancelarTransferencia({{ $item->TransferenciaId }})"
                                            title="Cancelar transferencia">
                                            <i class="bi bi-x-circle" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#10b981,#059669);opacity:0.5;">
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
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tablaTransferencias').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 10
        });
        
        // Tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    });

    function finalizarTransferencia(id) {
        Swal.fire({
            title: '¿Finalizar transferencia?',
            text: 'Esta acción marcará la transferencia como finalizada',
            icon: 'warning',
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
                
                fetch(`{{ url("cpanel/transferencias") }}/${id}/finalizar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => {
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

    function exportarExcelTransferencias() {
        const tabla = document.getElementById('tablaTransferencias');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla);
        XLSX.utils.book_append_sheet(wb, ws, 'Transferencias');
        XLSX.writeFile(wb, `Transferencias_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportarPDFTransferencias() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(16, 185, 129);
        doc.text('Transferencias Pendientes', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({
            html: '#tablaTransferencias',
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [16, 185, 129] },
            styles: { fontSize: 8 }
        });
        doc.save(`Transferencias_${new Date().toISOString().slice(0,10)}.pdf`);
    }
</script>
@endsection

@push('styles')
<style>
    #tablaTransferencias tbody tr:hover { background: #f8fafc; }
</style>
@endpush