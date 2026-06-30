@extends('layout.layout_dashboard')

@section('title', 'Detalle de Recepción')

@php
    use App\Helpers\FileHelper;
@endphp

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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Recepción</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Información completa de la recepción #{{ $recepcion->Numero }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.recepciones.finalizadas') }}">Recepciones Finalizadas</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DE LA RECEPCIÓN --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Recepción
                        <span class="ms-2 badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            #{{ $recepcion->Numero }}
                        </span>
                    </h6>
                    <a href="{{ url()->previous() }}"
                       class="btn btn-light btn-sm fw-semibold"
                       style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-3">

                    {{-- Columna izquierda --}}
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">N° Recepción</p>
                                <p class="mb-0 fw-bold text-dark">#{{ $recepcion->Numero }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Creación</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($recepcion->FechaCreacion)->format('d/m/Y H:i') }}</p>
                            </div>
                            @if(isset($recepcion->FechaRecepcion))
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Recepción</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($recepcion->FechaRecepcion)->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Tipo</p>
                                <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;">
                                    {{ $tipoTexto ?? 'Desconocido' }}
                                </span>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Estatus</p>
                                @php
                                    $claseEstatus = $estatus['clase'] ?? '';
                                    $badgeStyle = str_contains($claseEstatus, 'success')
                                        ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)'
                                        : (str_contains($claseEstatus, 'warning')
                                            ? 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)'
                                            : (str_contains($claseEstatus, 'danger')
                                                ? 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)'
                                                : 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)'));
                                @endphp
                                <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                      style="{{ $badgeStyle }};font-size:0.78rem;">
                                    {{ $estatus['texto'] ?? 'Desconocido' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Divisor vertical --}}
                    <div class="col-md-1 d-none d-md-flex justify-content-center">
                        <div style="width:1px;background:#e2e8f0;height:100%;"></div>
                    </div>

                    {{-- Columna derecha --}}
                    <div class="col-md-5">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Proveedor</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $recepcion->proveedor_nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 text-muted" style="font-size:0.88rem;">{{ $recepcion->proveedor_rif ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal Destino</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $recepcion->sucursal_destino ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Factura</p>
                                <p class="mb-0 text-muted" style="font-size:0.88rem;">{{ $recepcion->factura_numero ?? 'N/A' }}</p>
                            </div>
                            @if(isset($recepcion->sucursal_origen))
                            <div class="col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal Origen</p>
                                <p class="mb-0 text-muted" style="font-size:0.88rem;">{{ $recepcion->sucursal_origen ?? 'N/A' }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: PRODUCTOS RECIBIDOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-box-seam me-2"></i>Productos Recibidos
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ isset($detalles) ? $detalles->count() : 0 }} items
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:60px;">
                                    FOTO
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    CÓDIGO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    REFERENCIA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    PRODUCTO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    CANT. PEDIDA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    CANT. RECIBIDA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    COSTO UNIT. <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PIE SOLO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PIE INV.</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DAÑADO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">VACÍO</th>
                                <th class="pe-4 py-3 text-end text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    TOTAL <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalles ?? [] as $detalle)
                            @php
                                $total = ($detalle->CantidadRecibida ?? 0) * ($detalle->CostoDivisa ?? 0);
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-center">
                                    <img src="{{ $imgSrc }}" 
                                        alt="{{ $detalle->Codigo ?? 'Producto' }}"
                                        class="img-thumbnail img-zoomable"
                                        style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                        data-full-image="{{ $imgSrc }}"
                                        data-description="{{ $detalle->producto_nombre ?? 'Producto' }}"
                                        onclick="zoomImagen(this)"
                                        onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td class="ps-4">
                                    <span class="badge rounded-2 fw-semibold"
                                        style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;font-family:monospace;">
                                        {{ $detalle->Codigo ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark" style="font-size:0.88rem;">{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadPedida ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadRecibida ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">${{ number_format($detalle->CostoDivisa ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadPieSolo ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadPieInvertido ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadPiezaDanada ?? 0, 2) }}</td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">{{ number_format($detalle->CantidadCajaVacia ?? 0, 2) }}</td>
                                <td class="pe-4 text-end">
                                    <span class="fw-bold text-dark" style="font-size:0.88rem;">${{ number_format($total, 2) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#10b981,#059669);opacity:0.5;">
                                        <i class="bi bi-box-seam text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en esta recepción</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;">
                                <td colspan="10" class="pe-3 py-3 text-end fw-bold text-muted" style="font-size:0.82rem;letter-spacing:.04em;">
                                    TOTAL RECEPCIÓN
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <span class="fw-bold" style="color:#059669;font-size:1rem;">
                                        ${{ number_format($totalRecepcion ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Footer paginación --}}
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
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'rounded-3 shadow-lg'
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', function () {

        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;

        const ths   = tabla.querySelectorAll('thead th.sortable');
        const tbody = tabla.querySelector('tbody');

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
                const numA = parseFloat(valA.replace(/[$,]/g, ''));
                const numB = parseFloat(valB.replace(/[$,]/g, ''));
                const isNum = !isNaN(numA) && !isNaN(numB);
                const cmp  = isNum ? numA - numB : valA.localeCompare(valB, 'es', { sensitivity: 'base' });
                return sortDir === 'asc' ? cmp : -cmp;
            });

            rows.forEach(r => tbody.appendChild(r));

            ths.forEach((th, i) => {
                const icon = th.querySelector('i');
                if (!icon) return;
                if (i === colIdx) {
                    icon.className   = sortDir === 'asc' ? 'bi bi-arrow-up ms-1' : 'bi bi-arrow-down ms-1';
                    icon.style.opacity = '1';
                    icon.style.color   = '#3b82f6';
                } else {
                    icon.className     = 'bi bi-arrow-down-up ms-1';
                    icon.style.opacity = '0.5';
                    icon.style.color   = '';
                }
            });

            currentPage = 1;
            renderPage();
        }

        ths.forEach((th, i) => th.addEventListener('click', () => sortTable(i)));

        renderPage();
    });
</script>
@endsection

@push('styles')
<style>
    #tablaProductos tbody tr:hover { background: #f8fafc; }
    #tablaProductos thead th.sortable:hover { background: #f1f5f9; }
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