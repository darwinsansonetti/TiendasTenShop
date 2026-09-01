@extends('layout.layout_dashboard')

@section('title', 'Cuentas por Pagar - Servicios')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = 'wallet2';
    $hdrTitle = 'Cuentas por Pagar';
    $hdrSubtitle = 'Servicios - Facturas activas';
    
    $estatusLabels = [
        0 => 'Anulada',
        1 => 'En Proceso',
        2 => 'Recibiendo',
        3 => 'Pagada',
        4 => 'Recibida'
    ];
    
    $estatusBadge = [
        0 => 'danger',
        1 => 'warning',
        2 => 'info',
        3 => 'success',
        4 => 'primary'
    ];
    
    $monedaLabels = [
        0 => 'Divisas',
        1 => 'Bolívares'
    ];
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:{{ $hdrBg }};">
                        <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="#">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Cuentas por Pagar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                <i class="bi bi-receipt text-purple" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Facturas</p>
                                <h5 class="fw-bold mb-0">{{ $estadisticas['total_facturas'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(16,185,129,0.1);">
                                <i class="bi bi-cash text-success" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Monto Total</p>
                                <h5 class="fw-bold mb-0">{{ $estadisticas['total_general'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(239,68,68,0.1);">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Saldo Pendiente</p>
                                <h5 class="fw-bold mb-0 text-danger">{{ $estadisticas['total_pendiente'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(59,130,246,0.1);">
                                <i class="bi bi-clock text-primary" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Facturas Vencidas</p>
                                <h5 class="fw-bold mb-0 text-danger">{{ $estadisticas['facturas_vencidas'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE FACTURAS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Facturas de Servicios
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#7c3aed;">
                            {{ $listadoFacturas->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.proveedor.servicios.registrar_facturas') }}" 
                           class="btn btn-sm fw-semibold text-white"
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo
                        </a>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelCuentasPagar()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFCuentasPagar()">
                            <i class="bi bi-printer me-1"></i> PDF
                        </button>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaCuentasPagar">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:180px;">
                                    NÚMERO
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:150px;">
                                    PROVEEDOR
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:120px;">
                                    SUCURSAL
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    FECHA
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    DÍAS
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    MONTO (USD)
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    PAGADO
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    SALDO
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    ESTADO
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listadoFacturas as $factura)
                                @php
                                    $monto = (float) ($factura->MontoDivisa ?? 0);
                                    $pagado = (float) ($factura->total_pagado ?? 0);
                                    $saldo = (float) ($factura->saldo_pendiente ?? 0);
                                    $dias = (int) ($factura->DiasTranscurridos ?? 0);
                                    $estatus = $factura->Estatus ?? 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:0.8rem;font-weight:bold;">
                                            {{ $factura->Numero ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $factura->proveedor_nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $factura->sucursal_nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span style="font-size:0.85rem;">{{ $factura->FechaCreacionFormateada ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $factura->dias_color ?? 'secondary' }}" style="font-size:0.75rem;">
                                            {{ $dias }} días
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            $ {{ number_format($monto, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold text-success">
                                            $ {{ number_format($pagado, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold {{ $saldo > 0 ? 'text-danger' : 'text-success' }}">
                                            $ {{ number_format($saldo, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $factura->estado_badge ?? 'secondary' }}" style="font-size:0.75rem;">
                                            {{ $factura->estado ?? 'Desconocido' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            {{-- Ver Detalle --}}
                                            <a href="{{ route('cpanel.facturas.detalle.servicio', ['id' => $factura->ID, 'origen' => 'cuentas_pagar_servicios']) }}" 
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                            title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>
                                            {{-- Editar --}}
                                            <a href="{{ route('cpanel.facturas.editar.servicio', ['id' => $factura->ID, 'origen' => 'cuentas_pagar_servicios']) }}" 
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                            title="Editar estatus" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </a>
                                            {{-- Eliminar/Desactivar --}}
                                            <button type="button"
                                                    class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                    onclick="eliminarFactura({{ $factura->ID }})"
                                                    title="Eliminar factura" data-bs-toggle="tooltip">
                                                <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox me-2"></i>
                                        No hay facturas de servicios activas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-receipt me-1"></i>
                        {{ $listadoFacturas->count() }} factura{{ $listadoFacturas->count() != 1 ? 's' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </small>
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
    // ================================================
    // EXPORTAR EXCEL
    // ================================================
    function exportarExcelCuentasPagar() {
        const tabla = document.getElementById('tablaCuentasPagar');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todas las facturas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Cuentas por Pagar" });
                XLSX.utils.book_append_sheet(wb, ws, 'Cuentas por Pagar');
                XLSX.writeFile(wb, `Cuentas_Pagar_Servicios_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // EXPORTAR PDF
    // ================================================
    function exportarPDFCuentasPagar() {
        const tabla = document.getElementById('tablaCuentasPagar');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todas las facturas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');

                doc.setFontSize(16);
                doc.setTextColor(139, 92, 246);
                doc.text('Cuentas por Pagar - Servicios', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaCuentasPagar',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [139, 92, 246] }
                });

                doc.save(`Cuentas_Pagar_Servicios_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // ELIMINAR FACTURA
    // ================================================
    function eliminarFactura(id) {
        Swal.fire({
            title: '¿Anular factura?',
            text: 'La factura quedará como anulada. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Anulando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Usar POST con _method=DELETE
                fetch(`{{ url('cpanel/facturas') }}/${id}`, {
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
                            title: '¡Eliminada!',
                            text: 'La factura ha sido eliminada correctamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar la factura', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // ================================================
    // TOOLTIPS
    // ================================================
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
    .text-purple { color: #8b5cf6; }
</style>
@endpush