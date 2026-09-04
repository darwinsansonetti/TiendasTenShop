@extends('layout.layout_dashboard')

@section('title', 'Gastos por Categoría')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = 'pie-chart';
    $hdrTitle = 'Gastos por Categoría';
    $hdrSubtitle = 'Resumen de gastos agrupados por categoría';
    
    $colores = [
        'primary',
        'success',
        'info',
        'warning',
        'danger',
        'secondary',
        'dark',
        'purple'
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
                    <li class="breadcrumb-item active" aria-current="page">Gastos por Categoría</li>
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
                <form method="GET" action="{{ route('cpanel.contabilidad.gastos_categoria') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-3">
                            <label for="ttmnd" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-currency-exchange me-1" style="color:#8b5cf6;"></i>Moneda
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
                                 style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                <i class="bi bi-tags" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Categorías</p>
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
        {{-- TABLA DE GASTOS POR CATEGORÍA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Gastos por Categoría
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#7c3aed;">
                            {{ $listadoGastos->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelGastosCategoria()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFGastosCategoria()">
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
                    <table class="table table-hover align-middle mb-0" id="tablaGastosCategoria">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:200px;">
                                    CATEGORÍA
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    CANTIDAD
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">
                                    TOTAL {{ $verEnDivisas ? '(USD)' : '(Bs.)' }}
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">
                                    PORCENTAJE
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalGeneral = $listadoGastos->sum('MontoDivisaAbonado');
                                $colores = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark', 'purple'];
                                $i = 0;
                            @endphp
                            @forelse($listadoGastos as $gasto)
                                @php
                                    $porcentaje = $totalGeneral > 0 ? ($gasto->MontoDivisaAbonado / $totalGeneral) * 100 : 0;
                                    $color = $colores[$i % count($colores)];
                                    $i++;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span class="fw-semibold text-dark">
                                            <i class="bi bi-tag-fill me-2" style="color:#8b5cf6;"></i>
                                            {{ $gasto->categoria_nombre ?? 'Sin categoría' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary" style="font-size:0.75rem;">
                                            {{ $gasto->CantidadGastos ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            {{ $verEnDivisas ? '$ ' : 'Bs. ' }}{{ number_format($verEnDivisas ? $gasto->MontoDivisaAbonado : $gasto->MontoAbonado, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress w-75" style="height:8px;">
                                                <div class="progress-bar bg-{{ $color }}"
                                                     role="progressbar"
                                                     style="width: {{ $porcentaje }}%;"
                                                     aria-valuenow="{{ $porcentaje }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="fw-semibold" style="font-size:0.85rem;min-width:50px;">
                                                {{ number_format($porcentaje, 1) }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
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
                        <i class="bi bi-tags me-1"></i>
                        {{ $listadoGastos->count() }} categoría{{ $listadoGastos->count() != 1 ? 's' : '' }}
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
    function exportarExcelGastosCategoria() {
        const tabla = document.getElementById('tablaGastosCategoria');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todos los gastos por categoría?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Gastos por Categoría" });
                XLSX.utils.book_append_sheet(wb, ws, 'Gastos por Categoría');
                XLSX.writeFile(wb, `Gastos_Categoria_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ================================================
    // EXPORTAR PDF
    // ================================================
    function exportarPDFGastosCategoria() {
        const tabla = document.getElementById('tablaGastosCategoria');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todos los gastos por categoría?',
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
                doc.text('Gastos por Categoría', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaGastosCategoria',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [139, 92, 246] }
                });

                doc.save(`Gastos_Categoria_${new Date().toISOString().slice(0,10)}.pdf`);
                
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
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
    .progress { background-color: #e9ecef; border-radius: 4px; }
    .text-purple { color: #8b5cf6; }
</style>
@endpush