@extends('layout.layout_dashboard')

@section('title', 'Historial de facturas - Proveedores')

@php
    use Carbon\Carbon;
    
    // Colores para Historial (Azul/Índigo)
    $hdrBg = 'linear-gradient(135deg,#4f46e5,#7c3aed)';
    $hdrIcon = 'clock-history';
    $hdrTitle = 'Historial de facturas';
    $hdrSubtitle = 'Facturas pagadas';
    
    // Fechas
    $fechaInicio = $fechaInicio ?? Carbon::now()->subMonth()->startOfDay();
    $fechaFin = $fechaFin ?? Carbon::now()->endOfDay();
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Historial de facturas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD FILTROS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.proveedor.mercancia.historial_facturas') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        {{-- Buscador --}}
                        <div class="col-md-4">
                            <label for="busqueda" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-search me-1"></i>Buscar Factura o Proveedor
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text"
                                       name="busqueda"
                                       id="busqueda"
                                       class="form-control border-start-0"
                                       placeholder="Número de factura o nombre del proveedor..."
                                       value="{{ $busqueda ?? '' }}">
                                <button class="btn btn-light border" type="button" id="limpiarBuscador"
                                        style="font-size:0.85rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Fecha Inicio --}}
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar-start me-1"></i>Desde
                            </label>
                            <input type="date" 
                                   name="fecha_inicio" 
                                   id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio instanceof Carbon\Carbon ? $fechaInicio->format('Y-m-d') : $fechaInicio }}">
                        </div>

                        {{-- Fecha Fin --}}
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar-end me-1"></i>Hasta
                            </label>
                            <input type="date" 
                                   name="fecha_fin" 
                                   id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin instanceof Carbon\Carbon ? $fechaFin->format('Y-m-d') : $fechaFin }}">
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-2">
                            <button type="submit" class="btn w-100 fw-semibold text-white"
                                    style="background:{{ $hdrBg }};font-size:0.85rem;border:none;">
                                <i class="bi bi-filter me-1"></i>Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('cpanel.proveedor.mercancia.historial_facturas') }}" 
                               class="btn btn-light border w-100 fw-semibold"
                               style="font-size:0.85rem;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ================================================ --}}
        @php
            $totalFacturas = $facturas->count();
            $totalGeneral = $facturas->sum('MontoDivisa');
            $totalPagado = $facturas->sum('total_pagado');
            $proveedoresUnicos = $facturas->pluck('ProveedorId')->unique()->count();
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-file-text me-1"></i>Total Facturas
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#0d6efd;font-size:1.2rem;">
                                    {{ number_format($totalFacturas, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(13,110,253,0.1);">
                                <i class="bi bi-file-text" style="font-size:1.2rem;color:#0d6efd;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #198754;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-building me-1"></i>Proveedores
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#198754;font-size:1.2rem;">
                                    {{ number_format($proveedoresUnicos, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(25,135,84,0.1);">
                                <i class="bi bi-building" style="font-size:1.2rem;color:#198754;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-cash-stack me-1"></i>Total General
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#ffc107;font-size:1.2rem;">
                                    ${{ number_format($totalGeneral, 2) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(255,193,7,0.1);">
                                <i class="bi bi-cash-stack" style="font-size:1.2rem;color:#ffc107;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #14b8a6;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-credit-card me-1"></i>Total Pagado
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#14b8a6;font-size:1.2rem;">
                                    ${{ number_format($totalPagado, 2) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(20,184,166,0.1);">
                                <i class="bi bi-credit-card" style="font-size:1.2rem;color:#14b8a6;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE FACTURAS --}}
        {{-- ================================================ --}}
        @if($facturas && $facturas->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-clock-history me-2"></i>
                        Facturas Pagadas
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;color:#7c3aed;">
                            {{ $facturas->count() }}
                        </span>
                    </h6>
                    <div>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelHistorial()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFHistorial()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaHistorial">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" data-col="numero"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:150px;">
                                    FACTURA <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="proveedor"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:180px;">
                                    PROVEEDOR <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="fecha"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:120px;">
                                    FECHA <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="total"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    TOTAL (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="pagado"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    PAGADO (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    ESTADO
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturas as $factura)
                                @php
                                    $facturaId = $factura->ID;
                                    $numero = trim($factura->Numero ?? 'N/A');
                                    $proveedor = $factura->proveedor_nombre ?? 'N/A';
                                    $fecha = $factura->FechaCreacion ? Carbon::parse($factura->FechaCreacion)->format('d/m/Y') : 'N/A';
                                    $total = $factura->MontoDivisa ?? 0;
                                    $pagado = $factura->total_pagado ?? 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 py-3" data-order="{{ $numero }}">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#7c3aed;font-weight:bold;">
                                            {{ $numero }}
                                        </code>
                                    </td>
                                    <td class="py-3" data-order="{{ $proveedor }}">
                                        <span class="fw-semibold text-dark">{{ $proveedor }}</span>
                                    </td>
                                    <td class="py-3" data-order="{{ $factura->FechaCreacion ?? '' }}">
                                        <span style="font-size:0.85rem;">{{ $fecha }}</span>
                                    </td>
                                    <td class="py-3 text-end" data-order="{{ $total }}">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            ${{ number_format($total, 2) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end" data-order="{{ $pagado }}">
                                        <span class="fw-semibold" style="color:#22c55e;">
                                            ${{ number_format($pagado, 2) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-success" style="font-size:0.75rem;">
                                            <i class="bi bi-check-circle me-1"></i>Pagada
                                        </span>
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <a href="{{ route('cpanel.facturas.detalle', ['id' => $facturaId, 'origen' => 'historial']) }}"
                                           class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(79,70,229,0.1);color:#4f46e5;border:1px solid rgba(79,70,229,0.25);"
                                           title="Ver detalle" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Footer de la tabla --}}
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-clock-history me-1"></i>
                        {{ $facturas->count() }} factura{{ $facturas->count() != 1 ? 's' : '' }} pagadas
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar-range me-1"></i>
                        @if(!empty($fechaInicio) && !empty($fechaFin))
                            {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                        @else
                            <span class="fw-semibold">Todo el historial</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>

        @else

        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="d-flex flex-column align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:64px;height:64px;background:rgba(79,70,229,0.08);">
                        <i class="bi bi-clock-history"
                           style="font-size:1.8rem;opacity:.5;color:#4f46e5;"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-0">No hay facturas pagadas</p>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        No se encontraron facturas pagadas para el período seleccionado.
                    </p>
                </div>
            </div>
        </div>

        @endif

    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // BUSCADOR - Limpiar
        // ==========================
        const buscador = document.getElementById('busqueda');
        const limpiarBtn = document.getElementById('limpiarBuscador');

        if (limpiarBtn && buscador) {
            limpiarBtn.addEventListener('click', function() {
                buscador.value = '';
                document.getElementById('formFiltros').submit();
            });
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaHistorial');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }

                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr'));
                const filasReales = filas.filter(fila => fila.id !== 'mensajeNoResultados');

                filasReales.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];

                    if (!tdA || !tdB) return 0;

                    const valorA = tdA.dataset.order || tdA.innerText.trim();
                    const valorB = tdB.dataset.order || tdB.innerText.trim();

                    const esNumero = !isNaN(parseFloat(valorA.replace(/[$,%]/g, ''))) && 
                                     !isNaN(parseFloat(valorB.replace(/[$,%]/g, '')));
                    
                    if (esNumero) {
                        const numA = parseFloat(valorA.replace(/[$,%]/g, '')) || 0;
                        const numB = parseFloat(valorB.replace(/[$,%]/g, '')) || 0;
                        return asc ? numA - numB : numB - numA;
                    }

                    return asc
                        ? valorA.toString().localeCompare(valorB.toString())
                        : valorB.toString().localeCompare(valorA.toString());
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));

                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }
        })();
    });

    // ==========================
    // EXPORTAR EXCEL
    // ==========================
    function exportarExcelHistorial() {
        const tabla = document.getElementById('tablaHistorial');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todas las facturas pagadas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Historial facturas" });
                XLSX.utils.book_append_sheet(wb, ws, 'Historial facturas');
                XLSX.writeFile(wb, `Historial_facturas_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ==========================
    // EXPORTAR PDF
    // ==========================
    function exportarPDFHistorial() {
        const tabla = document.getElementById('tablaHistorial');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todas las facturas pagadas?',
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
                doc.setTextColor(79, 70, 229);
                doc.text('Historial de facturas', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaHistorial',
                    startY: 30,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [79, 70, 229] }
                });

                doc.save(`Historial_facturas_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaHistorial tbody tr:hover { background-color: #f8fafc; }
    .card-header { border-radius: 8px 8px 0 0; }
    thead th.sortable:hover { background-color: #f1f5f9; }
</style>
@endpush