@extends('layout.layout_dashboard')

@section('title', 'Auditar Recepciones')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Auditar Recepciones</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Revisión y aprobación de recepciones en auditoría</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Auditar Recepciones</li>
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
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Recepciones en Auditoría
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $auditorias->count() }} registros
                    </span>
                </div>
            </div>
            <div class="card-body p-0" id="tablaWrapper">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaAuditorias">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    N° AUDITORÍA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    RECEPCIÓN <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    SUCURSAL DESTINO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    FECHA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    ESTATUS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    DIFERENCIA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditorias as $auditoria)
                            @php
                                $estatus = $estatusMap[$auditoria->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary'];
                                $claseEstatus = $estatus['clase'] ?? '';
                                $badgeStyle = str_contains($claseEstatus, 'success')
                                    ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)'
                                    : (str_contains($claseEstatus, 'warning')
                                        ? 'background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25)'
                                        : (str_contains($claseEstatus, 'danger')
                                            ? 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)'
                                            : 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)'));
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4" data-order="{{ $auditoria->Numero }}">
                                    <span class="fw-bold text-dark">#{{ $auditoria->Numero }}</span>
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $auditoria->recepcion_numero ?? 'N/A' }}
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $auditoria->sucursal_destino ?? 'N/A' }}</td>
                                <td class="text-muted" style="font-size:0.88rem;" data-order="{{ $auditoria->Fecha }}">
                                    {{ \Carbon\Carbon::parse($auditoria->Fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center" data-order="{{ $auditoria->Estatus }}">
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                          style="{{ $badgeStyle }};font-size:0.75rem;">
                                        {{ $estatus['texto'] }}
                                    </span>
                                </td>
                                <td class="text-center" data-order="{{ $auditoria->detalles->count() }}">
                                    <span class="badge rounded-pill"
                                          style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.82rem;">
                                        {{ $auditoria->detalles->count() }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('cpanel.auditorias.procesar', $auditoria->AuditoriaId) }}"
                                    class="btn btn-sm fw-semibold rounded-2"
                                    style="background:rgba(251,191,36,0.1);color:#d97706;border:1px solid rgba(251,191,36,0.25);font-size:0.78rem;"
                                    title="Procesar auditoría" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil me-1" style="font-size:0.8rem;"></i>Procesar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);opacity:0.5;">
                                        <i class="bi bi-clipboard-check text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay recepciones en auditoría</p>
                                    <small class="text-muted">Las recepciones pendientes de auditoría aparecerán aquí</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        const tabla = document.getElementById('tablaAuditorias');
        if (!tabla) return;

        const ths   = tabla.querySelectorAll('thead th.sortable');
        const tbody = tabla.querySelector('tbody');

        // ==========================
        // ESTADO DE PAGINACIÓN
        // ==========================
        let currentPage  = 1;
        let rowsPerPage  = 10;

        function getDataRows() {
            return Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
        }

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

            // Info
            const from = total === 0 ? 0 : start + 1;
            const to   = Math.min(end, total);
            document.getElementById('paginationInfo').textContent =
                total === 0
                    ? 'Sin registros'
                    : `de ${total} registros — mostrando ${from}–${to}`;

            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            const ul = document.getElementById('paginationControls');
            ul.innerHTML = '';
            if (totalPages <= 1) return;

            ul.appendChild(makePageItem('&laquo;', currentPage - 1, currentPage === 1));

            getPageRange(currentPage, totalPages).forEach(p => {
                if (p === '…') {
                    const li = document.createElement('li');
                    li.className = 'page-item disabled';
                    li.innerHTML = '<span class="page-link" style="font-size:0.82rem;">…</span>';
                    ul.appendChild(li);
                } else {
                    ul.appendChild(makePageItem(p, p, false, p === currentPage));
                }
            });

            ul.appendChild(makePageItem('&raquo;', currentPage + 1, currentPage === totalPages));
        }

        function makePageItem(label, page, disabled, active = false) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            const a = document.createElement(disabled ? 'span' : 'a');
            a.className = 'page-link';
            a.style.fontSize = '0.82rem';
            a.innerHTML = label;
            if (!disabled) {
                a.href = '#';
                a.addEventListener('click', e => {
                    e.preventDefault();
                    currentPage = page;
                    renderPage();
                });
            }
            li.appendChild(a);
            return li;
        }

        function getPageRange(current, total) {
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
            if (current <= 4) return [1, 2, 3, 4, 5, '…', total];
            if (current >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total];
            return [1, '…', current - 1, current, current + 1, '…', total];
        }

        // Selector de filas por página
        document.getElementById('rowsPerPage').addEventListener('change', function () {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderPage();
        });

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        let ordenAscendente = true;
        let columnaActual   = null;

        ths.forEach(th => {
            th.addEventListener('click', () => {
                const colIndex = Array.from(th.parentNode.children).indexOf(th);

                if (columnaActual === colIndex) {
                    ordenAscendente = !ordenAscendente;
                } else {
                    ordenAscendente = true;
                    columnaActual   = colIndex;
                }

                // Actualizar iconos
                ths.forEach(t => {
                    const icon = t.querySelector('i.bi');
                    if (icon) { icon.className = 'bi bi-arrow-down-up ms-1'; icon.style.opacity = '.5'; }
                });
                const activeIcon = th.querySelector('i.bi');
                if (activeIcon) {
                    activeIcon.className = ordenAscendente ? 'bi bi-arrow-up ms-1' : 'bi bi-arrow-down ms-1';
                    activeIcon.style.opacity = '1';
                }

                // Ordenar
                const filas = getDataRows();
                filas.sort((a, b) => {
                    const tdA = a.children[colIndex];
                    const tdB = b.children[colIndex];
                    if (!tdA || !tdB) return 0;

                    const vA = tdA.dataset.order ?? tdA.innerText.trim();
                    const vB = tdB.dataset.order ?? tdB.innerText.trim();
                    const nA = parseFloat(vA);
                    const nB = parseFloat(vB);

                    if (!isNaN(nA) && !isNaN(nB)) return ordenAscendente ? nA - nB : nB - nA;
                    return ordenAscendente
                        ? vA.toString().localeCompare(vB.toString())
                        : vB.toString().localeCompare(vA.toString());
                });

                filas.forEach(fila => tbody.appendChild(fila));

                // Volver a página 1 tras ordenar
                currentPage = 1;
                renderPage();
            });
        });

        // Render inicial
        renderPage();
    });
</script>
@endsection

@push('styles')
<style>
    #tablaAuditorias tbody tr:hover { background: #f8fafc; }
    #tablaAuditorias thead th.sortable:hover { background: #eef2ff; color: #1d4ed8; }
    #tablaAuditorias thead th.sortable { transition: background 0.15s; }
</style>
@endpush
