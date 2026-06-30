@extends('layout.layout_dashboard')

@section('title', 'Recepciones de Proveedores')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                  <i class="bi bi-truck text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Recepciones de Proveedores</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Historial de recepciones registradas</p>
                </div>
              </div>
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

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-list-check me-2"></i>Listado de Recepciones
                        </h6>
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            {{ $listaRecepciones->count() }}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="exportarPDFRecepciones()">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </button>
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="exportarExcelRecepciones()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <a href="{{ route('cpanel.recepciones.nuevo') }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(16,185,129,0.85);color:#fff;border:1px solid rgba(16,185,129,0.4);font-size:0.8rem;">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Recepción
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaRecepciones">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">N° RECEPCIÓN</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FECHA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PROVEEDOR</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FACTURA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUCURSAL</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:90px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listaRecepciones as $recepcion)
                            @php
                                $estatusActual = (int)$recepcion->Estatus;
                                $estatusTexto = match($estatusActual) {
                                    1 => 'En Proceso',
                                    2 => 'Completada',
                                    3 => 'Anulada',
                                    default => 'Desconocido'
                                };
                                $estatusBadge = match($estatusActual) {
                                    1 => 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)',
                                    2 => 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)',
                                    3 => 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)',
                                    default => 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)',
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">#{{ $recepcion->Numero ?? $recepcion->RecepcionId }}</span>
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ \Carbon\Carbon::parse($recepcion->FechaRecepcion)->format('d/m/Y') }}
                                </td>
                                <td class="fw-semibold text-dark">{{ $recepcion->Proveedor->Nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($recepcion->Factura->Numero ?? null)
                                        <code class="px-2 py-1 rounded-2"
                                              style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">
                                            {{ $recepcion->Factura->Numero }}
                                        </code>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $recepcion->SucursalDestino->Nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $estatusBadge }};font-size:0.75rem;">
                                        {{ $estatusTexto }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($recepcion->Estatus == 1)
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('cpanel.recepciones.editar', $recepcion->RecepcionId) }}"
                                           class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                           title="Editar recepción" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                onclick="eliminarRecepcion({{ $recepcion->RecepcionId }})"
                                                title="Eliminar recepción" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);opacity:0.5;">
                                        <i class="bi bi-inbox text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay recepciones registradas</p>
                                    <small class="text-muted">Las nuevas recepciones aparecerán aquí</small>
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
                const url = `{{ route('cpanel.recepciones.eliminar', '') }}/${id}`;
                
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', 'Recepción cancelada correctamente', 'success').then(() => {
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

    function exportarExcelRecepciones() {
        const tabla = document.getElementById('tablaRecepciones');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla);
        XLSX.utils.book_append_sheet(wb, ws, 'Recepciones');
        XLSX.writeFile(wb, `Recepciones_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportarPDFRecepciones() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Recepciones de Proveedores', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({
            html: '#tablaRecepciones',
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185] },
            styles: { fontSize: 8 }
        });
        doc.save(`Recepciones_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection

@push('styles')
<style>
    #tablaRecepciones tbody tr:hover { background: #f8fafc; }
</style>
@endpush
