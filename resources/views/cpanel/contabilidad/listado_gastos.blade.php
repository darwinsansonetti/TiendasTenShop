@extends('layout.layout_dashboard')

@section('title', 'Listado de Gastos')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon = 'list-ul';
    $hdrTitle = 'Listado de Gastos';
    $hdrSubtitle = 'Gestión de gastos y egresos';
    
    $formasPago = [
        0 => 'Efectivo',
        1 => 'Cheque',
        2 => 'Depósito',
        3 => 'Transferencia',
        4 => 'Zelle',
        5 => 'Paypal',
        6 => 'Otro'
    ];
    
    $estatusLabels = [
        0 => 'Anulada',
        1 => 'En Proceso',
        2 => 'Pagada',
        3 => 'Cancelada'
    ];
    
    $estatusBadge = [
        0 => 'danger',
        1 => 'warning',
        2 => 'success',
        3 => 'secondary'
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
                    <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Listado de Gastos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- FILTROS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.contabilidad.lista_gastos') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#ef4444;"></i>Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#ef4444;"></i>Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-3">
                            <label for="ttmnd" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-currency-exchange me-1" style="color:#ef4444;"></i>Moneda
                            </label>
                            <select name="ttmnd" id="ttmnd" class="form-select">
                                <option value="1" {{ $verEnDivisas ? 'selected' : '' }}>Divisas</option>
                                <option value="0" {{ !$verEnDivisas ? 'selected' : '' }}>Bolívares</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn w-100 fw-semibold text-white"
                                    style="background:{{ $hdrBg }};border:none;">
                                <i class="bi bi-search me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(239,68,68,0.1);">
                                <i class="bi bi-receipt text-danger" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Gastos</p>
                                <h5 class="fw-bold mb-0">{{ number_format($totales->cantidad, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(16,185,129,0.1);">
                                <i class="bi bi-cash text-success" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Divisas</p>
                                <h5 class="fw-bold mb-0">$ {{ number_format($totales->total_divisa, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(59,130,246,0.1);">
                                <i class="bi bi-cash-stack text-primary" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Bs.</p>
                                <h5 class="fw-bold mb-0">Bs. {{ number_format($totales->total_bs, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE GASTOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Registro de Gastos
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#dc2626;">
                            {{ $listadoGastos->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.contabilidad.registrar_gastos') }}" 
                           class="btn btn-sm fw-semibold text-white"
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Gasto
                        </a>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelGastos()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFGastos()">
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
                    <table class="table table-hover align-middle mb-0" id="tablaGastos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    FECHA
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:120px;">
                                    NÚMERO
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:150px;">
                                    CATEGORÍA
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:200px;">
                                    DESCRIPCIÓN
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    {{ $verEnDivisas ? 'MONTO (USD)' : 'MONTO (Bs.)' }}
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    TASA
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    FORMA PAGO
                                </th>
                                {{-- ✅ NUEVA COLUMNA ESTATUS --}}
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    ESTATUS
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listadoGastos as $gasto)
                                @php
                                    $estatus = $gasto->Estatus ?? 2;
                                    $esEnProceso = ($estatus == 1);
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span style="font-size:0.85rem;">{{ $gasto->FechaFormateada ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#dc2626;font-size:0.8rem;font-weight:bold;">
                                            {{ $gasto->NumeroOperacion ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $gasto->categoria_nombre ?? 'Sin categoría' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $gasto->Descripcion ?? 'N/A' }}</span>
                                        @if($gasto->Observacion ?? false)
                                            <small class="text-muted d-block" style="font-size:0.7rem;">{{ $gasto->Observacion }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            {{ $verEnDivisas ? '$ ' : 'Bs. ' }}{{ number_format($verEnDivisas ? $gasto->MontoDivisaAbonado : $gasto->MontoAbonado, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span style="font-size:0.85rem;">{{ number_format($gasto->TasaDeCambio ?? 0, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary" style="font-size:0.75rem;">
                                            {{ $formasPago[$gasto->FormaDePago ?? 0] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $estatusBadge[$estatus] ?? 'secondary' }}" style="font-size:0.75rem;">
                                            {{ $estatusLabels[$estatus] ?? 'Desconocido' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            {{-- Ver Detalle (siempre) --}}
                                            <a href="{{ route('cpanel.contabilidad.detalle_gasto', $gasto->ID) }}" 
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                            title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>
                                            
                                            {{-- Editar (solo En Proceso) --}}
                                            @if($esEnProceso)
                                                <a href="{{ route('cpanel.contabilidad.editar_gasto', $gasto->ID) }}" 
                                                class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                title="Editar gasto" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                            @endif
                                            
                                            {{-- Eliminar (solo En Proceso) --}}
                                            @if($esEnProceso)
                                                <button type="button"
                                                    class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                    onclick="eliminarGasto({{ $gasto->ID }}, '{{ $gasto->NumeroOperacion }}')"
                                                    title="Eliminar gasto" data-bs-toggle="tooltip">
                                                <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox me-2"></i>
                                        No hay gastos registrados en el período seleccionado
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
                        {{ $listadoGastos->count() }} gasto{{ $listadoGastos->count() != 1 ? 's' : '' }}
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
    // ELIMINAR GASTO
    // ================================================
    function eliminarGasto(id, numeroOperacion) {
        Swal.fire({
            title: '¿Eliminar gasto?',
            html: `Estás a punto de eliminar el gasto <strong>${numeroOperacion}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
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
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // ✅ Usar url() de Laravel como en la otra vista
                fetch(`{{ url('cpanel/contabilidad/eliminar/gasto') }}/${id}`, {
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
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el gasto', 'error');
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
    // EXPORTAR EXCEL
    // ================================================
    function exportarExcelGastos() {
        const tabla = document.getElementById('tablaGastos');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todos los gastos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Gastos" });
                XLSX.utils.book_append_sheet(wb, ws, 'Gastos');
                XLSX.writeFile(wb, `Gastos_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // EXPORTAR PDF
    // ================================================
    function exportarPDFGastos() {
        const tabla = document.getElementById('tablaGastos');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todos los gastos?',
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
                doc.setTextColor(239, 68, 68);
                doc.text('Listado de Gastos', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaGastos',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [239, 68, 68] }
                });

                doc.save(`Gastos_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
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

        @if(session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        @endif
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
</style>
@endpush