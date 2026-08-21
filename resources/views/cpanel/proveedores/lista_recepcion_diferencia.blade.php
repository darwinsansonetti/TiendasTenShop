@extends('layout.layout_dashboard')

@section('title', 'Diferencia en recepción - Proveedores')

@php
    use Carbon\Carbon;
    
    // Colores para Diferencia (Naranja/Ámbar)
    $hdrBg = 'linear-gradient(135deg,#f59e0b,#d97706)';
    $hdrIcon = 'arrow-left-right';
    $hdrTitle = 'Diferencia en recepción';
    $hdrSubtitle = 'Facturas con diferencias entre lo facturado y lo recibido';
    
    $fechaInicio = $fechaInicio ?? null;
    $fechaFin = $fechaFin ?? null;
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
                    <li class="breadcrumb-item active" aria-current="page">Diferencia en recepción</li>
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
                <form method="GET" action="{{ route('cpanel.proveedor.mercancia.diferencia') }}" id="formFiltros">
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
                                   value="{{ $fechaInicio ?? '' }}">
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
                                   value="{{ $fechaFin ?? '' }}">
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-2">
                            <button type="submit" class="btn w-100 fw-semibold text-white"
                                    style="background:{{ $hdrBg }};font-size:0.85rem;border:none;">
                                <i class="bi bi-filter me-1"></i>Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('cpanel.proveedor.mercancia.diferencia') }}" 
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
            $totalDiferencia = $facturas->sum('total_diferencia');
            $totalProductosConDiferencia = $facturas->sum('cantidad_productos_con_diferencia');
            $totalProveedores = $facturas->pluck('ProveedorId')->unique()->count();
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-file-text me-1"></i>Facturas con Diferencia
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#dc3545;font-size:1.2rem;">
                                    {{ number_format($totalFacturas, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(220,53,69,0.1);">
                                <i class="bi bi-file-text" style="font-size:1.2rem;color:#dc3545;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-building me-1"></i>Proveedores
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#f59e0b;font-size:1.2rem;">
                                    {{ number_format($totalProveedores, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(245,158,11,0.1);">
                                <i class="bi bi-building" style="font-size:1.2rem;color:#f59e0b;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-box-seam me-1"></i>Diferencias Totales
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#dc3545;font-size:1.2rem;">
                                    {{ number_format($totalDiferencia, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(220,53,69,0.1);">
                                <i class="bi bi-box-seam" style="font-size:1.2rem;color:#dc3545;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #8b5cf6;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-box me-1"></i>Productos con Error
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#8b5cf6;font-size:1.2rem;">
                                    {{ number_format($totalProductosConDiferencia, 0) }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(139,92,246,0.1);">
                                <i class="bi bi-box" style="font-size:1.2rem;color:#8b5cf6;"></i>
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
                        <i class="bi bi-arrow-left-right me-2"></i>
                        Facturas con Diferencia
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;color:#d97706;">
                            {{ $facturas->count() }}
                        </span>
                    </h6>
                    <div>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelDiferencia()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFDiferencia()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaDiferencia">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" data-col="factura"
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
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="saldo"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    SALDO (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
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
                                    $saldo = $factura->saldo_pendiente ?? 0;
                                    $tieneDiferencia = $factura->tiene_diferencia ?? false;
                                    $totalErrores = $factura->errores_recepcion ? $factura->errores_recepcion->count() : 0;
                                    $estatus = $factura->Estatus ?? 0;
                                    
                                    $estatusTexto = $estatus == 3 ? 'Pagada' : ($estatus == 4 ? 'Recibida' : 'Desconocido');
                                    $estatusColor = $estatus == 3 ? 'success' : ($estatus == 4 ? 'info' : 'secondary');
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;{{ $tieneDiferencia ? 'background:rgba(220,53,69,0.05);' : '' }}">
                                    <td class="ps-4 py-3" data-order="{{ $numero }}">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#d97706;font-weight:bold;">
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
                                    <td class="py-3 text-end" data-order="{{ $saldo }}">
                                        <span class="fw-bold" style="color:{{ $saldo > 0 ? '#dc3545' : '#22c55e' }};">
                                            ${{ number_format($saldo, 2) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <a href="{{ route('cpanel.proveedor.mercancia.diferencia.detalle', $facturaId) }}"
                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                        style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
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
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        {{ $facturas->count() }} factura{{ $facturas->count() != 1 ? 's' : '' }} analizadas
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
                            style="width:64px;height:64px;background:rgba(34,197,94,0.08);">
                            <i class="bi bi-check-circle"
                            style="font-size:1.8rem;opacity:.5;color:#22c55e;"></i>
                        </div>
                        <p class="fw-semibold text-dark mb-0">No hay diferencias</p>
                        <p class="text-muted mb-0" style="font-size:0.9rem;">
                            Todas las facturas pagadas y recibidas coinciden con lo facturado.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Modal para ver errores de recepción --}}
<div class="modal fade" id="modalErroresRecepcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:{{ $hdrBg }};color:#fff;border-bottom:2px solid #d97706;">
                <h5 class="modal-title fw-bold" id="modalErroresTitle">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Errores de recepción
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#f8fafc;">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Factura:</strong> <span id="modalFacturaNumero"></span>
                </div>
                <div id="modalTablaErrores">
                    <!-- Tabla generada por JavaScript -->
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;border-top:2px solid #e2e8f0;">
                <button type="button" class="btn" style="background:{{ $hdrBg }};color:#fff;border:none;" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cerrar
                </button>
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
            const tabla = document.getElementById('tablaDiferencia');
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
    // VER ERRORES DE RECEPCIÓN (VERSIÓN AGRUPADA)
    // ==========================
    function verErrores(errores, facturaNumero) {
        document.getElementById('modalFacturaNumero').textContent = facturaNumero;
        
        // 🔥 Agrupar por ProductoId
        const productosAgrupados = {};
        let totalDiferencia = 0;
        
        errores.forEach(function(error) {
            const key = error.ProductoId;
            if (!productosAgrupados[key]) {
                productosAgrupados[key] = {
                    ProductoId: error.ProductoId,
                    ProductoCodigo: error.ProductoCodigo || 'N/A',
                    ProductoDescripcion: error.ProductoDescripcion || 'N/A',
                    ProductoReferencia: error.ProductoReferencia || 'N/A',
                    CantidadPedida: 0,
                    CantidadRecibida: 0,
                    CantidadCajaVacia: 0,
                    CantidadPieInvertido: 0,
                    CantidadPieSolo: 0,
                    CantidadPiezaDanada: 0,
                    Diferencia: 0
                };
            }
            
            productosAgrupados[key].CantidadPedida += error.CantidadPedida || 0;
            productosAgrupados[key].CantidadRecibida += error.CantidadRecibida || 0;
            productosAgrupados[key].CantidadCajaVacia += error.CantidadCajaVacia || 0;
            productosAgrupados[key].CantidadPieInvertido += error.CantidadPieInvertido || 0;
            productosAgrupados[key].CantidadPieSolo += error.CantidadPieSolo || 0;
            productosAgrupados[key].CantidadPiezaDanada += error.CantidadPiezaDanada || 0;
            productosAgrupados[key].Diferencia += error.Diferencia || 0;
            totalDiferencia += error.Diferencia || 0;
        });
        
        // Convertir a array y filtrar solo los que tienen Diferencia > 0
        const erroresAgrupados = Object.values(productosAgrupados)
            .filter(function(item) {
                return item.Diferencia > 0;
            })
            .sort(function(a, b) {
                return b.Diferencia - a.Diferencia;
            });
        
        // Limitar a 100 productos para no sobrecargar
        const limite = 100;
        const totalProductos = erroresAgrupados.length;
        const productosMostrados = erroresAgrupados.slice(0, limite);
        const hayMas = totalProductos > limite;
        
        let html = `
            <div class="table-responsive">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Mostrando <strong>${productosMostrados.length}</strong> de <strong>${totalProductos}</strong> productos con diferencias.
                    ${hayMas ? `<span class="badge bg-warning ms-2">${totalProductos - limite} productos adicionales no mostrados</span>` : ''}
                </div>
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                            <th class="text-muted fw-semibold" style="font-size:0.75rem;">CÓDIGO</th>
                            <th class="text-muted fw-semibold" style="font-size:0.75rem;">PRODUCTO</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">PEDIDO</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">RECIBIDO</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">CAJA VACÍA</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE INV.</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE SOLO</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">DAÑADO</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        let totalDiferenciaMostrada = 0;
        
        productosMostrados.forEach(function(error) {
            totalDiferenciaMostrada += error.Diferencia || 0;
            html += `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td>
                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#d97706;font-weight:bold;">
                            ${error.ProductoCodigo || 'N/A'}
                        </code>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">${error.ProductoDescripcion || 'N/A'}</span>
                        <br>
                        <small class="text-muted" style="font-size:0.6rem;">
                            Ref: ${error.ProductoReferencia || 'N/A'}
                        </small>
                    </td>
                    <td class="text-end fw-semibold">${error.CantidadPedida || 0}</td>
                    <td class="text-end fw-semibold">${error.CantidadRecibida || 0}</td>
                    <td class="text-end">${error.CantidadCajaVacia || 0}</td>
                    <td class="text-end">${error.CantidadPieInvertido || 0}</td>
                    <td class="text-end">${error.CantidadPieSolo || 0}</td>
                    <td class="text-end">${error.CantidadPiezaDanada || 0}</td>
                    <td class="text-end fw-bold text-danger">${error.Diferencia || 0}</td>
                </tr>
            `;
        });
        
        // Si hay más productos, mostrar un mensaje
        if (hayMas) {
            html += `
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">
                        <i class="bi bi-three-dots me-2"></i>
                        ${totalProductos - limite} productos adicionales no mostrados
                    </td>
                </tr>
            `;
        }
        
        html += `
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:600;">
                            <td colspan="8" class="text-end">TOTAL DIFERENCIA:</td>
                            <td class="text-end fw-bold text-danger">${totalDiferencia}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        document.getElementById('modalTablaErrores').innerHTML = html;
        
        const modal = new bootstrap.Modal(document.getElementById('modalErroresRecepcion'));
        modal.show();
    }

    // ==========================
    // EXPORTAR EXCEL
    // ==========================
    function exportarExcelDiferencia() {
        const tabla = document.getElementById('tablaDiferencia');
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
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Diferencia recepción" });
                XLSX.utils.book_append_sheet(wb, ws, 'Diferencia recepción');
                XLSX.writeFile(wb, `Diferencia_recepcion_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ==========================
    // EXPORTAR PDF
    // ==========================
    function exportarPDFDiferencia() {
        const tabla = document.getElementById('tablaDiferencia');
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
                doc.setTextColor(245, 158, 11);
                doc.text('Diferencia en recepción', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaDiferencia',
                    startY: 30,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [245, 158, 11] }
                });

                doc.save(`Diferencia_recepcion_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaDiferencia tbody tr:hover { background-color: #f8fafc; }
    .card-header { border-radius: 8px 8px 0 0; }
    thead th.sortable:hover { background-color: #f1f5f9; }
    
    /* Estilo para el modal */
    .modal-header {
        border-radius: 8px 8px 0 0;
    }
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: #d97706;
        border-radius: 3px;
    }
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #b45309;
    }
    
    /* Tooltip en botón deshabilitado */
    .d-inline-block[data-bs-toggle="tooltip"] {
        cursor: help;
    }
</style>
@endpush