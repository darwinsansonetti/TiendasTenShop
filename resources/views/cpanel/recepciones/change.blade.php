@extends('layout.layout_dashboard')

@section('title', 'Detalle de Recepción - Gestión de Precios')

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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Gestión de Precios - Recepción</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Actualiza los precios de los productos recibidos #{{ $recepcion->Numero }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.recepciones.finalizadas') }}">Recepciones Finalizadas</a></li>
                    <li class="breadcrumb-item active">Gestión de Precios</li>
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
                    <div class="d-flex gap-2">
                        <a href="{{ url()->previous() }}"
                           class="btn btn-light btn-sm fw-semibold"
                           style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
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
        {{-- CARD 2: GESTIÓN DE PRECIOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-tags me-2"></i>Gestión de Precios
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ isset($detalles) ? $detalles->count() : 0 }} productos
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                {{-- ============================================ --}}
                {{-- BARRA DE ACCIONES (STICKY) --}}
                {{-- ============================================ --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-2"
                    style="background:#fafbfc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:100;">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fw-semibold text-dark" style="font-size:0.82rem;">
                            <i class="bi bi-tags me-1"></i>Gestión de Precios
                        </span>
                        <span class="badge rounded-pill bg-secondary" style="font-size:0.7rem;">
                            {{ isset($detalles) ? $detalles->count() : 0 }} productos
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnDescargarExcel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Descargar Excel
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btnCargarExcel">
                            <i class="bi bi-upload me-1"></i>Cargar Excel
                        </button>
                        <input type="file" id="excelFileInput" accept=".xlsx,.xls" style="display:none;">
                        <span class="text-muted" style="font-size:0.78rem;" id="archivoCargado"></span>
                        <div class="vr" style="height:24px;"></div>
                        <button type="button" class="btn btn-warning btn-sm fw-semibold text-white" id="btnGuardarPrecios">
                            <i class="bi bi-save me-1"></i>Guardar Precios
                        </button>
                    </div>
                </div>

                <form id="formPrecios" method="POST" action="#">
                    @csrf
                    @method('PUT')
                    
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
                                    <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">
                                        COSTO DIVISA
                                    </th>
                                    <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:140px;">
                                        NUEVO PVP (USD)
                                    </th>
                                    {{-- ✅ NUEVA COLUMNA: PORCENTAJE --}}
                                    <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:80px;">
                                        INCREMENTO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detalles ?? [] as $detalle)
                                @php
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/items/thumbs/',
                                        $detalle->UrlFoto ?? '',
                                        'assets/img/adminlte/img/produc_default.jfif'
                                    );
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 text-center">
                                        <img src="{{ $imgSrc }}" 
                                            loading="lazy" 
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
                                    <td class="text-center">
                                        <span class="fw-semibold" style="color:#059669;font-size:0.88rem;">
                                            ${{ number_format($detalle->CostoDivisa ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <input type="number" 
                                            step="0.01" 
                                            min="0"
                                            name="precios[{{ $detalle->ProductoId ?? $detalle->ID }}]"
                                            class="form-control form-control-sm precio-input"
                                            style="width:130px;display:inline-block;text-align:right;font-weight:600;"
                                            value="0.00"
                                            placeholder="PVP actual: ${{ number_format($detalle->PvpDivisa ?? 0, 2) }}"
                                            data-producto="{{ $detalle->Codigo }}"
                                            data-costo="{{ $detalle->CostoDivisa ?? 0 }}"
                                            data-pvp-actual="{{ $detalle->PvpDivisa ?? 0 }}">
                                    </td>
                                    
                                    {{-- ✅ NUEVA CELDA: PORCENTAJE --}}
                                    <td class="pe-4 text-center">
                                        <span class="porcentaje-label fw-semibold" 
                                            style="font-size:0.82rem;color:#6b7280;"
                                            data-producto="{{ $detalle->Codigo }}">
                                            0%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                             style="width:52px;height:52px;background:linear-gradient(135deg,#f59e0b,#d97706);opacity:0.5;">
                                            <i class="bi bi-box-seam text-white" style="font-size:1.4rem;"></i>
                                        </div>
                                        <p class="mb-0 text-muted fw-semibold">No hay productos en esta recepción</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            {{-- Footer con paginación --}}
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
    // Función para zoom de imagen
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

    // ==========================
    // CALCULAR PORCENTAJE DE INCREMENTO (FUNCIÓN GLOBAL)
    // ==========================
    function calcularPorcentaje(input) {
        const tr = input.closest('tr');
        const porcentajeLabel = tr.querySelector('.porcentaje-label');
        
        if (!porcentajeLabel) return;
        
        const costo = parseFloat(input.getAttribute('data-costo')) || 0;
        const nuevoPvp = parseFloat(input.value) || 0;
        
        if (costo <= 0 || nuevoPvp <= 0) {
            porcentajeLabel.textContent = '0%';
            porcentajeLabel.style.color = '#6b7280';
            return;
        }
        
        const incremento = ((nuevoPvp - costo) / costo) * 100;
        const incrementoRedondeado = Math.round(incremento * 100) / 100;
        
        porcentajeLabel.textContent = `${incrementoRedondeado > 0 ? '+' : ''}${incrementoRedondeado}%`;
        
        // Cambiar color según el porcentaje
        if (incrementoRedondeado > 20) {
            porcentajeLabel.style.color = '#dc2626';
        } else if (incrementoRedondeado > 10) {
            porcentajeLabel.style.color = '#d97706';
        } else if (incrementoRedondeado > 0) {
            porcentajeLabel.style.color = '#059669';
        } else if (incrementoRedondeado < 0) {
            porcentajeLabel.style.color = '#dc2626';
        } else {
            porcentajeLabel.style.color = '#6b7280';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {

        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;

        const ths   = tabla.querySelectorAll('thead th.sortable');
        const tbody = tabla.querySelector('tbody');

        // ==========================
        // CALCULAR PORCENTAJE AL ESCRIBIR MANUALMENTE
        // ==========================
        document.querySelectorAll('.precio-input').forEach(input => {
            input.addEventListener('input', function () {
                calcularPorcentaje(this);
            });
        });

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

        // ==========================
        // GUARDAR PRECIOS - SOLO VALORES > 0
        // ==========================
        document.getElementById('btnGuardarPrecios').addEventListener('click', function() {
            const inputs = document.querySelectorAll('.precio-input');
            const precios = {};
            let productosConPrecio = 0;
            let errores = [];

            inputs.forEach(input => {
                const valor = parseFloat(input.value);
                const producto = input.getAttribute('data-producto');
                const costo = parseFloat(input.getAttribute('data-costo'));
                const pvpActual = parseFloat(input.getAttribute('data-pvp-actual'));
                
                if (valor > 0) {
                    if (valor < costo) {
                        errores.push(`⚠️ ${producto}: Precio ($${valor.toFixed(2)}) menor que costo ($${costo.toFixed(2)})`);
                    }
                    
                    const name = input.getAttribute('name');
                    const match = name.match(/\[(\d+)\]/);
                    if (match) {
                        const productoId = match[1];
                        precios[productoId] = valor;
                        productosConPrecio++;
                    }
                }
            });

            if (productosConPrecio === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin cambios',
                    text: 'No hay productos con nuevo PVP para guardar',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            let mensaje = `Se guardarán ${productosConPrecio} productos`;
            if (errores.length > 0) {
                mensaje += '\n\n⚠️ Advertencias:\n' + errores.join('\n');
            }

            Swal.fire({
                icon: 'question',
                title: '¿Guardar precios?',
                html: mensaje.replace(/\n/g, '<br>'),
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    guardarPrecios(precios);
                }
            });
        });

        // ==========================
        // FUNCIÓN PARA GUARDAR PRECIOS
        // ==========================
        function guardarPrecios(precios) {
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const url = '{{ route('cpanel.recepciones.actualizar-precios', $recepcion->RecepcionId) }}';

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfInput = document.querySelector('#formPrecios input[name="_token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : (csrfInput ? csrfInput.value : '');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    precios: precios,
                    sucursal_id: {{ $recepcion->SucursalDestinoId ?? 'null' }}
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ✅ Generar Excel con los productos modificados
                    const productosModificados = data.productos_modificados || [];

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message || 'Precios actualizados correctamente',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        // ✅ Descargar Excel si hay productos modificados
                        if (productosModificados.length > 0) {
                            descargarReportePrecios(productosModificados);
                        }
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al guardar los precios',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar los precios. Verifica la conexión.',
                    confirmButtonColor: '#dc2626'
                });
            });
        }

        // ==========================
        // FUNCIÓN PARA DESCARGAR REPORTE DE PRECIOS MODIFICADOS
        // ==========================
        function descargarReportePrecios(productos) {
            try {
                const data = [];
                
                data.push(['REPORTE DE ACTUALIZACIÓN DE PRECIOS']);
                data.push([]);
                data.push(['Fecha', new Date().toLocaleString('es-ES')]);
                data.push(['Recepción', '{{ $recepcion->Numero ?? "N/A" }}']);
                data.push(['Sucursal', '{{ $recepcion->sucursal_destino ?? "N/A" }}']);
                data.push(['Proveedor', '{{ $recepcion->proveedor_nombre ?? "N/A" }}']);
                data.push([]);
                
                data.push(['PRODUCTOS MODIFICADOS']);
                data.push([]);
                
                data.push([
                    'CÓDIGO', 
                    'REFERENCIA', 
                    'PRODUCTO', 
                    'PVP ANTERIOR (USD)', 
                    'NUEVO PVP (USD)', 
                    'INCREMENTO (%)'
                ]);
                
                let totalIncremento = 0;
                let totalProductos = 0;
                
                productos.forEach(producto => {
                    const incremento = producto.nuevo_pvp - producto.pvp_anterior;
                    const porcentaje = producto.pvp_anterior > 0 
                        ? ((incremento / producto.pvp_anterior) * 100) 
                        : 0;
                    
                    data.push([
                        producto.codigo || 'N/A',
                        producto.referencia || 'N/A',
                        producto.nombre || 'N/A',
                        parseFloat(producto.pvp_anterior || 0).toFixed(2),
                        parseFloat(producto.nuevo_pvp || 0).toFixed(2),
                        porcentaje.toFixed(2) + '%'
                    ]);
                    
                    totalIncremento += incremento;
                    totalProductos++;
                });
                
                data.push([]);
                data.push(['RESUMEN']);
                data.push(['Total Productos Modificados', totalProductos]);
                data.push(['Incremento Total (USD)', totalIncremento.toFixed(2)]);
                
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(data);
                
                ws['!cols'] = [
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 35 },
                    { wch: 18 },
                    { wch: 18 },
                    { wch: 18 }
                ];
                
                XLSX.utils.book_append_sheet(wb, ws, 'Reporte Precios');
                
                const fecha = new Date().toISOString().slice(0,10);
                const nombreArchivo = `reporte_precios_${fecha}.xlsx`;
                XLSX.writeFile(wb, nombreArchivo);
                
            } catch (error) {
                console.error('Error al generar reporte Excel:', error);
                Swal.fire({
                    icon: 'warning',
                    title: 'Reporte no generado',
                    text: 'Los precios se guardaron pero no se pudo generar el reporte Excel.',
                    confirmButtonColor: '#d97706'
                });
            }
        }

        // ==========================
        // DESCARGAR EXCEL - CON INFORMACIÓN DE RECEPCIÓN
        // ==========================
        document.getElementById('btnDescargarExcel').addEventListener('click', function() {
            try {
                const numeroRecepcion = '{{ $recepcion->Numero ?? "N/A" }}';
                const fechaRecepcion = '{{ isset($recepcion->FechaRecepcion) ? \Carbon\Carbon::parse($recepcion->FechaRecepcion)->format("d/m/Y H:i") : "N/A" }}';
                const fechaCreacion = '{{ isset($recepcion->FechaCreacion) ? \Carbon\Carbon::parse($recepcion->FechaCreacion)->format("d/m/Y H:i") : "N/A" }}';
                const sucursalDestino = '{{ $recepcion->sucursal_destino ?? "N/A" }}';
                const proveedor = '{{ $recepcion->proveedor_nombre ?? "N/A" }}';
                const factura = '{{ $recepcion->factura_numero ?? "N/A" }}';
                const estatus = '{{ $estatus["texto"] ?? "N/A" }}';
                
                const data = [];
                
                data.push(['INFORMACIÓN DE LA RECEPCIÓN']);
                data.push([]);
                data.push(['N° Recepción', numeroRecepcion]);
                data.push(['Fecha Creación', fechaCreacion]);
                data.push(['Fecha Recepción', fechaRecepcion]);
                data.push(['Sucursal Destino', sucursalDestino]);
                data.push(['Proveedor', proveedor]);
                data.push(['Factura', factura]);
                data.push(['Estatus', estatus]);
                data.push([]);
                
                data.push(['LISTA DE PRODUCTOS']);
                data.push([]);
                
                data.push(['CÓDIGO', 'REFERENCIA', 'PRODUCTO', 'COSTO DIVISA', 'NUEVO PVP (USD)']);
                
                const rows = document.querySelectorAll('#tablaProductos tbody tr');
                let contadorProductos = 0;
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length < 6) return;
                    
                    const codigo = cells[1]?.textContent?.trim() || '';
                    const referencia = cells[2]?.textContent?.trim() || '';
                    const producto = cells[3]?.textContent?.trim() || '';
                    const costoDivisa = cells[4]?.textContent?.trim().replace('$', '') || '0';
                    
                    const input = cells[5]?.querySelector('.precio-input');
                    const nuevoPvp = input ? input.value : '0.00';
                    
                    if (codigo) {
                        data.push([codigo, referencia, producto, costoDivisa, nuevoPvp]);
                        contadorProductos++;
                    }
                });
                
                if (data.length <= 12) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin datos',
                        text: 'No hay productos para exportar',
                        confirmButtonColor: '#d97706'
                    });
                    return;
                }
                
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(data);
                
                ws['!cols'] = [
                    { wch: 20 },
                    { wch: 30 },
                    { wch: 35 },
                    { wch: 15 },
                    { wch: 18 }
                ];
                
                XLSX.utils.book_append_sheet(wb, ws, 'Actualización Precios');
                
                const nombreArchivo = `actualizacion_precios_${numeroRecepcion}_${new Date().toISOString().slice(0,10)}.xlsx`;
                XLSX.writeFile(wb, nombreArchivo);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Excel descargado',
                    text: `Se exportaron ${contadorProductos} productos de la recepción #${numeroRecepcion}`,
                    timer: 3000,
                    showConfirmButton: false
                });
                
            } catch (error) {
                console.error('Error al descargar Excel:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al generar el Excel: ' + error.message,
                    confirmButtonColor: '#dc2626'
                });
            }
        });

        // ==========================
        // CARGAR EXCEL
        // ==========================
        document.getElementById('btnCargarExcel').addEventListener('click', function() {
            document.getElementById('excelFileInput').click();
        });

        document.getElementById('excelFileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            document.getElementById('archivoCargado').textContent = `📄 ${file.name}`;
            
            const reader = new FileReader();
            reader.onload = function(event) {
                try {
                    const data = new Uint8Array(event.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                    
                    const inputs = document.querySelectorAll('.precio-input');
                    
                    let actualizados = 0;
                    
                    function limpiarCodigo(codigo) {
                        return String(codigo).trim().toUpperCase();
                    }
                    
                    function esCodigoProducto(texto) {
                        if (!texto || typeof texto !== 'string') return false;
                        const trimmed = texto.trim();
                        return /^[A-Z]{2,3}\d+/.test(trimmed);
                    }
                    
                    jsonData.forEach(row => {
                        let codigo = null;
                        let nuevoPvp = null;
                        
                        const keys = Object.keys(row);
                        
                        for (const key of keys) {
                            const value = row[key];
                            if (value && typeof value === 'string' && value.trim() !== '') {
                                const trimmed = value.trim();
                                if (esCodigoProducto(trimmed)) {
                                    codigo = trimmed;
                                    break;
                                }
                            }
                        }
                        
                        if (!codigo) {
                            const primeraColumna = row['INFORMACIÓN DE LA RECEPCIÓN'];
                            if (primeraColumna && typeof primeraColumna === 'string') {
                                const trimmed = primeraColumna.trim();
                                if (esCodigoProducto(trimmed)) {
                                    codigo = trimmed;
                                }
                            }
                        }
                        
                        if (!codigo) return;
                        
                        for (const key of keys) {
                            if (key.includes('EMPTY') && key !== '__EMPTY' && key !== '__EMPTY_1' && key !== '__EMPTY_2') {
                                const value = row[key];
                                if (value !== undefined && value !== null && value !== '') {
                                    const num = parseFloat(value);
                                    if (!isNaN(num)) {
                                        nuevoPvp = num;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (nuevoPvp === null) {
                            for (const key of keys) {
                                const value = row[key];
                                if (value !== undefined && value !== null && value !== '' && typeof value === 'number') {
                                    if (key !== '__rowNum__') {
                                        nuevoPvp = value;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (isNaN(nuevoPvp)) return;
                        
                        const codigoLimpio = limpiarCodigo(codigo);
                        
                        inputs.forEach(input => {
                            const producto = input.getAttribute('data-producto');
                            if (producto && limpiarCodigo(producto) === codigoLimpio) {
                                input.value = nuevoPvp.toFixed(2);
                                calcularPorcentaje(input);
                                actualizados++;
                            }
                        });
                    });
                    
                    let mensaje = `✅ Excel cargado correctamente`;
                    if (actualizados > 0) {
                        mensaje += `\n📦 ${actualizados} productos actualizados`;
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Excel procesado',
                        text: mensaje,
                        confirmButtonColor: '#d97706'
                    });
                    
                    e.target.value = '';
                    
                } catch (error) {
                    console.error('Error al leer el Excel:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al leer el Excel',
                        text: 'Verifica que el archivo tenga el formato correcto',
                        confirmButtonColor: '#dc2626'
                    });
                }
            };
            
            reader.readAsArrayBuffer(file);
        });

        // ==========================
        // RENDERIZAR PÁGINA
        // ==========================
        renderPage();

    }); // FIN DEL DOMContentLoaded
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
    .precio-input:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
    }
    .precio-input::-webkit-inner-spin-button,
    .precio-input::-webkit-outer-spin-button {
        opacity: 1;
    }
</style>
@endpush