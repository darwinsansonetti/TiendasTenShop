@extends('layout.layout_dashboard')

@section('title', 'Recepciones Finalizadas')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="bi bi-check2-all text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Recepciones Finalizadas</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Historial de recepciones completadas</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Recepciones Finalizadas</li>
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
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.recepciones.finalizadas') }}">
                    <div class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label fw-semibold" style="font-size:0.85rem;">Fecha Inicio</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#f8fafc;border-color:#e2e8f0;">
                                    <i class="bi bi-calendar3 text-muted" style="font-size:0.85rem;"></i>
                                </span>
                                <input type="date" name="fecha_inicio" id="fecha_inicio"
                                       class="form-control border-start-0"
                                       style="border-color:#e2e8f0;font-size:0.88rem;"
                                       value="{{ $fechaInicio ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label fw-semibold" style="font-size:0.85rem;">Fecha Fin</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#f8fafc;border-color:#e2e8f0;">
                                    <i class="bi bi-calendar3-range text-muted" style="font-size:0.85rem;"></i>
                                </span>
                                <input type="date" name="fecha_fin" id="fecha_fin"
                                       class="form-control border-start-0"
                                       style="border-color:#e2e8f0;font-size:0.88rem;"
                                       value="{{ $fechaFin ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn w-100 fw-semibold"
                                    style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;font-size:0.85rem;">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('cpanel.recepciones.finalizadas') }}"
                               class="btn w-100 fw-semibold"
                               style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:0.85rem;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD TABLA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Listado de Recepciones Finalizadas
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ count($recepciones) }} registros
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaRecepciones">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    F. CREACIÓN <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    NÚM. RECEPCIÓN <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    FACT. / TRANSF. <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    PROVE. / ORIGEN <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    DESTINO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    TIPO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    ESTATUS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recepciones as $recepcion)
                            @php
                                $estatus = $estatusMap[$recepcion->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary'];
                                $claseEstatus = $estatus['clase'] ?? '';
                                $badgeStyle = str_contains($claseEstatus, 'success')
                                    ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)'
                                    : (str_contains($claseEstatus, 'warning')
                                        ? 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)'
                                        : (str_contains($claseEstatus, 'danger')
                                            ? 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)'
                                            : 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)'));

                                if ($recepcion->Tipo == 0) {
                                    $tipoTexto = 'De proveedor';
                                    $tipoBadge = 'background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25)';
                                } elseif ($recepcion->Tipo == 1) {
                                    $tipoTexto = 'Dist. almacén';
                                    $tipoBadge = 'background:rgba(6,182,212,0.1);color:#0e7490;border:1px solid rgba(6,182,212,0.25)';
                                } elseif ($recepcion->Tipo == 2) {
                                    $tipoTexto = 'Transferencia';
                                    $tipoBadge = 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)';
                                } else {
                                    $tipoTexto = 'Desconocido';
                                    $tipoBadge = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }

                                $proveedorOrigen = $recepcion->proveedor_nombre ?? $recepcion->sucursal_origen ?? 'N/A';
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-muted" style="font-size:0.88rem;"
                                    data-order="{{ $recepcion->FechaCreacion }}">
                                    {{ \Carbon\Carbon::parse($recepcion->FechaCreacion)->format('d/m/Y') }}
                                </td>
                                <td data-order="{{ $recepcion->Numero }}">
                                    <span class="fw-bold text-dark">#{{ $recepcion->Numero }}</span>
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $recepcion->documento_numero ?? 'N/A' }}
                                </td>
                                <td class="fw-semibold text-dark" style="font-size:0.88rem;">
                                    {{ $proveedorOrigen }}
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $recepcion->sucursal_destino ?? 'N/A' }}
                                </td>
                                <td class="text-center" data-order="{{ $recepcion->Tipo }}">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $tipoBadge }};font-size:0.75rem;">
                                        {{ $tipoTexto }}
                                    </span>
                                </td>
                                <td class="text-center" data-order="{{ $recepcion->Estatus }}">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $badgeStyle }};font-size:0.75rem;">
                                        {{ $estatus['texto'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('cpanel.recepciones.detalle', $recepcion->RecepcionId) }}"
                                           class="btn btn-sm fw-semibold rounded-2"
                                           style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;"
                                           title="Ver detalle" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                        @if(isset($recepcion->sucursal_destino) && $recepcion->sucursal_destino !== 'ALMACEN')
                                        <a href="{{ route('cpanel.recepciones.precios', $recepcion->RecepcionId) }}"
                                        class="btn btn-sm fw-semibold rounded-2"
                                        style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;"
                                        title="Cambiar PVP" data-bs-toggle="tooltip">
                                            <i class="bi bi-graph-up-arrow"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('cpanel.recepciones.exportar.excel', $recepcion->RecepcionId) }}"
                                           class="btn btn-sm fw-semibold rounded-2"
                                           style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.78rem;"
                                           title="Exportar a Excel" data-bs-toggle="tooltip">
                                            <i class="bi bi-file-earmark-excel" style="font-size:0.8rem;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#10b981,#059669);opacity:0.5;">
                                        <i class="bi bi-check2-all text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay recepciones finalizadas</p>
                                    <small class="text-muted">Las recepciones completadas aparecerán aquí</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER: paginación --}}
            <div class="card-footer border-0 py-3 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">Mostrar</small>
                        <select id="rowsPerPage" class="form-select form-select-sm"
                                style="width:72px;font-size:0.82rem;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="9999">Todos</option>
                        </select>
                        <small class="text-muted" id="paginationInfo">registros</small>
                    </div>
                    <nav aria-label="Paginación">
                        <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    const tabla = document.getElementById('tablaRecepciones');
    if (!tabla) return;

    const ths   = tabla.querySelectorAll('thead th.sortable');
    const tbody = tabla.querySelector('tbody');

    // ==========================
    // ESTADO
    // ==========================
    let currentPage = 1;
    let rowsPerPage = 10;
    let sortCol     = -1;
    let sortDir     = 'asc';

    function getDataRows() {
        return Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
    }

    // ==========================
    // PAGINACIÓN
    // ==========================
    function renderPage() {
        const rows       = getDataRows();
        const total      = rows.length;
        const limit      = rowsPerPage >= 9999 ? total : rowsPerPage;
        const totalPages = Math.max(1, Math.ceil(total / limit));

        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * limit;
        const end   = start + limit;

        rows.forEach((row, i) => {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });

        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        document.getElementById('paginationInfo').textContent =
            `de ${from}–${to} de ${total} registros`;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const ul = document.getElementById('paginationControls');
        ul.innerHTML = '';

        const mkLi = (label, page, disabled, active) => {
            const li  = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
            const btn = document.createElement(disabled ? 'span' : 'button');
            btn.className = 'page-link';
            btn.innerHTML = label;
            if (!disabled && !active) btn.addEventListener('click', () => { currentPage = page; renderPage(); });
            li.appendChild(btn);
            ul.appendChild(li);
        };

        mkLi('&laquo;', currentPage - 1, currentPage === 1, false);

        const range = 2;
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= range) {
                mkLi(p, p, false, p === currentPage);
            } else if (Math.abs(p - currentPage) === range + 1) {
                mkLi('…', null, true, false);
            }
        }

        mkLi('&raquo;', currentPage + 1, currentPage === totalPages, false);
    }

    document.getElementById('rowsPerPage').addEventListener('change', function () {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderPage();
    });

    // ==========================
    // ORDENAMIENTO
    // ==========================
    function getCellValue(row, idx) {
        const cell = row.children[idx];
        return cell ? (cell.dataset.order ?? cell.textContent.trim()) : '';
    }

    function sortTable(colIdx) {
        if (sortCol === colIdx) {
            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            sortCol = colIdx;
            sortDir = 'asc';
        }

        const rows = getDataRows();
        rows.sort((a, b) => {
            const valA = getCellValue(a, colIdx);
            const valB = getCellValue(b, colIdx);
            const numA = parseFloat(valA);
            const numB = parseFloat(valB);
            const isNum = !isNaN(numA) && !isNaN(numB);
            const cmp  = isNum ? numA - numB : valA.localeCompare(valB, 'es', { sensitivity: 'base' });
            return sortDir === 'asc' ? cmp : -cmp;
        });

        rows.forEach(r => tbody.appendChild(r));

        ths.forEach((th, i) => {
            const icon = th.querySelector('i');
            if (!icon) return;
            if (i === colIdx) {
                icon.className = sortDir === 'asc'
                    ? 'bi bi-arrow-up ms-1'
                    : 'bi bi-arrow-down ms-1';
                icon.style.opacity = '1';
                icon.style.color   = '#3b82f6';
            } else {
                icon.className   = 'bi bi-arrow-down-up ms-1';
                icon.style.opacity = '0.5';
                icon.style.color   = '';
            }
        });

        currentPage = 1;
        renderPage();
    }

    ths.forEach((th, i) => th.addEventListener('click', () => sortTable(i)));

    // Render inicial
    renderPage();
});

// ============================================
// EXPORTAR RECEPCIÓN A EXCEL
// ============================================
function exportarRecepcion(recepcionId) {
    Swal.fire({
        title: 'Generando Excel...',
        text: 'Consultando datos de la recepción',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`/cpanel/recepciones/${recepcionId}/datos-exportacion`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            const nombreArchivo = `Recepcion_${data.data.recepcion_numero}_${new Date().toISOString().slice(0,19).replace(/:/g,'-')}.xlsx`;
            generarExcelRecepcion(data.data, nombreArchivo);
            Swal.fire({
                icon: 'success',
                title: 'Excel generado',
                text: 'El archivo se ha descargado correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', data.message || 'Error al obtener los datos', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire('Error', 'Error al generar el Excel: ' + error.message, 'error');
    });
}

// ============================================
// GENERAR EXCEL CON SHEETJS
// ============================================
function generarExcelRecepcion(datos, nombreArchivo = 'Recepcion.xlsx') {
    const workbook = XLSX.utils.book_new();
    const filas = [];

    filas.push(['RECEPCIÓN DE MERCANCÍA', '', '', '', '', '', '', '', '', '']);
    filas.push(['', '', '', '', '', '', '', '', '', '']);
    filas.push(['N° Recepción:', datos.recepcion_numero, '', '', '', '', '', '', '', '']);
    filas.push(['Fecha:', datos.fecha, '', '', '', '', '', '', '', '']);
    filas.push(['Proveedor:', datos.proveedor_nombre || 'N/A', '', '', '', '', '', '', '', '']);
    filas.push(['Sucursal Destino:', datos.sucursal_destino || 'N/A', '', '', '', '', '', '', '', '']);
    filas.push(['', '', '', '', '', '', '', '', '', '']);
    filas.push([
        'Código', 'Producto', 'Cantidad Pedida', 'Cantidad Recibida',
        'Costo Unitario', 'Pie Solo', 'Pie Inv.', 'Dañado', 'Vacío', 'Total'
    ]);

    let totalRecepcion = 0;
    datos.detalles.forEach(detalle => {
        const total = (detalle.CantidadRecibida || 0) * (detalle.CostoDivisa || 0);
        totalRecepcion += total;
        filas.push([
            detalle.Codigo || 'N/A',
            detalle.producto_nombre || 'N/A',
            detalle.CantidadPedida || 0,
            detalle.CantidadRecibida || 0,
            detalle.CostoDivisa || 0,
            detalle.CantidadPieSolo || 0,
            detalle.CantidadPieInvertido || 0,
            detalle.CantidadPiezaDanada || 0,
            detalle.CantidadCajaVacia || 0,
            total.toFixed(2)
        ]);
    });

    filas.push(['', '', '', '', '', '', '', '', 'TOTAL:', totalRecepcion.toFixed(2)]);

    const worksheet = XLSX.utils.aoa_to_sheet(filas);
    worksheet['!cols'] = [
        {wch:15},{wch:35},{wch:18},{wch:18},{wch:18},{wch:12},{wch:12},{wch:12},{wch:12},{wch:15}
    ];

    XLSX.utils.book_append_sheet(workbook, worksheet, 'Recepcion');
    XLSX.writeFile(workbook, nombreArchivo);
}
</script>
@endsection

@push('styles')
<style>
    #tablaRecepciones tbody tr:hover { background: #f8fafc; }
    #tablaRecepciones thead th.sortable:hover { background: #f1f5f9; }
    .pagination .page-link {
        color: #3b82f6;
        border-color: #e2e8f0;
        font-size: 0.82rem;
    }
    .pagination .page-item.active .page-link {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
    }
</style>
@endpush