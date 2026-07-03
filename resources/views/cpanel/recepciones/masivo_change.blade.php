@extends('layout.layout_dashboard')

@section('title', 'Gestión de Precios')

@section('content')

@php
    $sucursalId = (int) session('sucursal_id', 0);
    $sucursalNombre = session('sucursal_nombre', 'Sin sucursal');
@endphp

<!-- Campos ocultos para JavaScript -->
<input type="hidden" id="sucursalIdHidden" value="{{ $sucursalId }}">
<input type="hidden" id="sucursalNombreHidden" value="{{ $sucursalNombre }}">

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-tags text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Gestión de Precios</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Actualiza precios de productos de forma manual mediante Excel</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Gestión de Precios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @php
            $sucursalId = (int) session('sucursal_id', 0);
        @endphp

        {{-- ================================================ --}}
        {{-- AVISO: SIN SUCURSAL SELECCIONADA --}}
        {{-- ================================================ --}}
        @if($sucursalId <= 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:1.2rem;"></i>
            <div>
                <strong>Selecciona una sucursal</strong> en el panel principal para poder descargar la plantilla,
                cargar el Excel y guardar precios.
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- CARD: GESTIÓN DE PRECIOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-tags me-2"></i>Gestión de Precios
                    </h6>
                    <span class="badge rounded-pill" id="badgeContador"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        0 productos
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
                        <span class="badge rounded-pill bg-secondary" id="badgeContador2" style="font-size:0.7rem;">
                            0 productos
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnDescargarExcel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Descargar Plantilla
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

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    CÓDIGO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    REFERENCIA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    PRODUCTO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:140px;">
                                    NUEVO PVP (USD)
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:60px;">
                                    &nbsp;
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyProductos">
                            <tr id="filaVacia">
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#f59e0b,#d97706);opacity:0.5;">
                                        <i class="bi bi-box-seam text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos cargados</p>
                                    <p class="mb-0 text-muted" style="font-size:0.8rem;">Descarga la plantilla, complétala y cárgala para comenzar</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer con paginación --}}
            <div class="card-footer border-0 py-3 px-4"
                style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" style="font-size:0.8rem;">Mostrar</span>
                        <select id="rowsPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="9999">Todos</option>
                        </select>
                        <span class="text-muted" id="paginationInfo" style="font-size:0.8rem;">de 0–0 de 0 registros</span>
                    </div>
                    <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
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
    const SUCURSAL_ID = parseInt(document.getElementById('sucursalIdHidden').value) || 0;
    const SUCURSAL_NOMBRE = document.getElementById('sucursalNombreHidden').value || 'Sin sucursal';

    function waitForXLSX(callback, retries = 0) {
        if (typeof XLSX !== 'undefined' && XLSX.version) {
            console.log('✅ XLSX cargado correctamente, versión:', XLSX.version);
            callback();
            return;
        }
        if (retries < 20) {
            setTimeout(() => waitForXLSX(callback, retries + 1), 100);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error de librería',
                text: 'La librería XLSX no se cargó correctamente. Recarga la página.',
                confirmButtonColor: '#dc2626'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tabla = document.getElementById('tablaProductos');
        const tbody = document.getElementById('tbodyProductos');
        const ths = tabla.querySelectorAll('thead th.sortable');

        let currentPage = 1;
        let rowsPerPage = 10;
        let sortCol = -1;
        let sortDir = 'asc';
        let rowSeq = 0;

        function actualizarEstadoBotones() {
            const botones = ['btnDescargarExcel', 'btnCargarExcel', 'btnGuardarPrecios'];
            botones.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    if (SUCURSAL_ID > 0) {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                    } else {
                        btn.disabled = true;
                        btn.style.opacity = '0.6';
                        btn.style.cursor = 'not-allowed';
                    }
                }
            });
        }

        function actualizarInfoSucursal() {
            const infoSucursal = document.getElementById('infoSucursal');
            if (infoSucursal) {
                if (SUCURSAL_ID > 0) {
                    infoSucursal.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="fw-semibold">Sucursal: ${SUCURSAL_NOMBRE}</span>
                            <span class="badge bg-success">Activa</span>
                        </div>
                    `;
                } else {
                    infoSucursal.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <span class="fw-semibold text-warning">No hay sucursal seleccionada</span>
                            <span class="badge bg-danger">Inactiva</span>
                        </div>
                    `;
                }
            }
        }

        function descargarPlantilla() {
            if (SUCURSAL_ID <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sucursal no seleccionada',
                    text: 'Debes seleccionar una sucursal para descargar la plantilla',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            try {
                if (typeof XLSX === 'undefined' || !XLSX.version) {
                    throw new Error('La librería XLSX no está cargada correctamente');
                }

                const data = [
                    ['INFORMACIÓN PRINCIPAL'],
                    [],
                    ['Sucursal', SUCURSAL_NOMBRE],
                    ['Fecha', new Date().toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' })],
                    [],
                    ['LISTA DE PRODUCTOS'],
                    [],
                    ['CÓDIGO', 'REFERENCIA', 'PRODUCTO', 'NUEVO PVP (USD) ⭐']
                ];

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(data);
                ws['!cols'] = [{ wch: 25 }, { wch: 30 }, { wch: 40 }, { wch: 20 }];
                XLSX.utils.book_append_sheet(wb, ws, 'Actualización Precios');

                const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `plantilla_precios_${new Date().toISOString().slice(0,10)}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(link.href), 100);

                Swal.fire({
                    icon: 'success',
                    title: 'Plantilla descargada',
                    timer: 3000,
                    showConfirmButton: false
                });

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al generar la plantilla',
                    text: error.message,
                    confirmButtonColor: '#dc2626'
                });
            }
        }

        actualizarEstadoBotones();
        actualizarInfoSucursal();

        waitForXLSX(() => console.log('✅ XLSX listo'));

        document.addEventListener('sucursalActualizada', function(e) {
            const nuevaSucursalId = e.detail.sucursalId || 0;
            const nuevaSucursalNombre = e.detail.sucursalNombre || 'Sin sucursal';
            window.SUCURSAL_ID = nuevaSucursalId;
            window.SUCURSAL_NOMBRE = nuevaSucursalNombre;
            document.getElementById('sucursalIdHidden').value = nuevaSucursalId;
            document.getElementById('sucursalNombreHidden').value = nuevaSucursalNombre;
            actualizarEstadoBotones();
            actualizarInfoSucursal();
        });

        function getDataRows() {
            return Array.from(tbody.querySelectorAll('tr[data-row]'));
        }

        function actualizarContador() {
            const total = getDataRows().length;
            document.getElementById('badgeContador').textContent = `${total} producto${total === 1 ? '' : 's'}`;
            document.getElementById('badgeContador2').textContent = `${total} producto${total === 1 ? '' : 's'}`;
        }

        function mostrarFilaVaciaSiAplica() {
            const filaVacia = document.getElementById('filaVacia');
            if (filaVacia) filaVacia.style.display = getDataRows().length > 0 ? 'none' : '';
        }

        function crearFila({ codigo, referencia, producto, pvp }) {
            rowSeq++;
            const tr = document.createElement('tr');
            tr.setAttribute('data-row', rowSeq);
            tr.style.borderBottom = '1px solid #f1f5f9';

            tr.innerHTML = `
                <td class="ps-4">
                    <span class="badge rounded-2 fw-semibold codigo-cell"
                        style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;font-family:monospace;">
                        ${codigo}
                    </span>
                </td>
                <td class="text-muted referencia-cell" style="font-size:0.88rem;">${referencia || 'N/A'}</td>
                <td class="fw-semibold text-dark producto-cell" style="font-size:0.88rem;">${producto || 'N/A'}</td>
                <td class="text-end">
                    <input type="number" step="0.01" min="0"
                        class="form-control form-control-sm precio-input"
                        style="width:130px;display:inline-block;text-align:right;font-weight:600;"
                        value="${(pvp || 0).toFixed(2)}">
                </td>
                <td class="pe-4 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila" title="Quitar producto">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tr.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
                tr.remove();
                actualizarContador();
                mostrarFilaVaciaSiAplica();
                renderPage();
            });

            return tr;
        }

        function renderPage() {
            const rows = getDataRows();
            const total = rows.length;
            const limit = rowsPerPage >= 9999 ? Math.max(total, 1) : rowsPerPage;
            const totalPages = Math.max(1, Math.ceil(total / limit));

            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * limit;
            const end = start + limit;

            rows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });

            const from = total === 0 ? 0 : start + 1;
            const to = Math.min(end, total);
            document.getElementById('paginationInfo').textContent = `de ${from}–${to} de ${total} registros`;

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            const ul = document.getElementById('paginationControls');
            ul.innerHTML = '';

            const mkLi = (label, page, disabled, active) => {
                const li = document.createElement('li');
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

        function getCellValue(row, idx) {
            const cell = row.children[idx];
            if (!cell) return '';
            const input = cell.querySelector('input');
            return input ? input.value : cell.textContent.trim();
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
                const cmp = isNum ? numA - numB : valA.localeCompare(valB, 'es', { sensitivity: 'base' });
                return sortDir === 'asc' ? cmp : -cmp;
            });

            rows.forEach(r => tbody.appendChild(r));

            ths.forEach((th, i) => {
                const icon = th.querySelector('i');
                if (!icon) return;
                if (i === colIdx) {
                    icon.className = sortDir === 'asc' ? 'bi bi-arrow-up ms-1' : 'bi bi-arrow-down ms-1';
                    icon.style.opacity = '1';
                    icon.style.color = '#3b82f6';
                } else {
                    icon.className = 'bi bi-arrow-down-up ms-1';
                    icon.style.opacity = '0.5';
                    icon.style.color = '';
                }
            });

            currentPage = 1;
            renderPage();
        }

        ths.forEach((th, i) => th.addEventListener('click', () => sortTable(i)));

        // ==========================
        // BOTÓN: DESCARGAR PLANTILLA
        // ==========================
        document.getElementById('btnDescargarExcel').addEventListener('click', function() {
            if (this.disabled) return;
            waitForXLSX(descargarPlantilla);
        });

        // ==========================
        // BOTÓN: CARGAR EXCEL
        // ==========================
        document.getElementById('btnCargarExcel').addEventListener('click', function() {
            if (this.disabled) return;
            if (SUCURSAL_ID <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sucursal no seleccionada',
                    text: 'Debes seleccionar una sucursal para cargar el Excel',
                    confirmButtonColor: '#d97706'
                });
                return;
            }
            document.getElementById('excelFileInput').click();
        });

        // ==========================
        // INPUT FILE: PROCESAR EXCEL
        // ==========================
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
                    
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { 
                        defval: '',
                        blankrows: false,
                        header: 1
                    });

                    let startRow = 0;
                    let foundProducts = false;
                    
                    for (let i = 0; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (row && row[0] && String(row[0]).trim() === 'LISTA DE PRODUCTOS') {
                            startRow = i + 2;
                            foundProducts = true;
                            break;
                        }
                    }

                    if (!foundProducts) {
                        for (let i = 0; i < jsonData.length; i++) {
                            const row = jsonData[i];
                            if (row && row[0] && String(row[0]).trim() === 'CÓDIGO') {
                                startRow = i + 1;
                                break;
                            }
                        }
                    }

                    getDataRows().forEach(r => r.remove());

                    let cargados = 0;
                    let omitidos = 0;

                    for (let i = startRow; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (!row || row.length === 0) continue;
                        
                        const codigo = String(row[0] || '').trim();
                        const referencia = String(row[1] || '').trim();
                        const producto = String(row[2] || '').trim();
                        
                        let pvp = 0;
                        if (row[3] !== undefined && row[3] !== null && row[3] !== '') {
                            pvp = parseFloat(String(row[3]).replace(',', '.')) || 0;
                        }

                        if (!codigo || pvp <= 0) {
                            omitidos++;
                            continue;
                        }

                        const tr = crearFila({ codigo, referencia, producto, pvp });
                        tbody.appendChild(tr);
                        cargados++;
                    }

                    actualizarContador();
                    mostrarFilaVaciaSiAplica();
                    currentPage = 1;
                    renderPage();

                    let mensaje = `✅ ${cargados} producto${cargados === 1 ? '' : 's'} cargado${cargados === 1 ? '' : 's'}`;
                    if (omitidos > 0) {
                        mensaje += `\n⚠️ ${omitidos} fila${omitidos === 1 ? '' : 's'} omitida${omitidos === 1 ? '' : 's'} (sin código o sin PVP válido)`;
                    }

                    Swal.fire({
                        icon: cargados > 0 ? 'success' : 'warning',
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
        // BOTÓN: GUARDAR PRECIOS
        // ==========================
        document.getElementById('btnGuardarPrecios').addEventListener('click', function() {
            if (this.disabled) return;
            
            if (SUCURSAL_ID <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sucursal no seleccionada',
                    text: 'Debes seleccionar una sucursal para guardar los precios',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            const rows = getDataRows();
            const productos = [];

            rows.forEach(tr => {
                const codigo = tr.querySelector('.codigo-cell').textContent.trim();
                const pvp = parseFloat(tr.querySelector('.precio-input').value) || 0;

                if (!codigo || pvp <= 0) return;

                productos.push({ 
                    codigo: codigo, 
                    nuevo_pvp: pvp,
                    costo_divisa: 0
                });
            });

            if (productos.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin productos',
                    text: 'No hay productos con precio válido para guardar',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: '¿Guardar precios?',
                text: `Se guardarán ${productos.length} producto${productos.length === 1 ? '' : 's'}`,
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    guardarPrecios(productos);
                }
            });
        });

        // ==========================
        // FUNCIÓN: GUARDAR PRECIOS
        // ==========================
        function guardarPrecios(productos) {
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

            // Obtener token CSRF
            let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) {
                const tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput) token = tokenInput.value;
            }

            console.log('📤 Enviando productos:', productos);

            fetch('{{ route("cpanel.precios.guardar-manual") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    sucursal_id: SUCURSAL_ID,
                    productos: productos
                })
            })
            .then(async response => {
                const text = await response.text();
                console.log('📥 Respuesta:', text);
                
                if (!response.ok) {
                    let errorMsg = `Error ${response.status}`;
                    try {
                        const json = JSON.parse(text);
                        errorMsg = json.message || errorMsg;
                        if (json.errors) errorMsg += '\n' + JSON.stringify(json.errors);
                    } catch (e) {
                        errorMsg = text || errorMsg;
                    }
                    throw new Error(errorMsg);
                }
                return JSON.parse(text);
            })
            .then(data => {
                if (data.success) {
                    let mensaje = data.message;
                    if (data.no_encontrados > 0) {
                        mensaje += `<br><br>⚠️ Productos no encontrados: ${data.no_encontrados}`;
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Completado!',
                        html: mensaje,
                        confirmButtonColor: '#10b981'
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
                console.error('❌ Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: error.message || 'Error al guardar los precios. Verifica la conexión.',
                    confirmButtonColor: '#dc2626'
                });
            });
        }

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
    .precio-input:focus, .costo-input:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.25);
    }
</style>
@endpush