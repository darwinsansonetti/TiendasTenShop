@extends('layout.layout_dashboard')

@section('title', 'Cuentas por pagar - Proveedores')

@php
    use Carbon\Carbon;
    
    // Colores para Cuentas por pagar (Rojo/Vino)
    $hdrBg = 'linear-gradient(135deg,#e11d48,#be123c)';
    $hdrIcon = 'wallet2';
    $hdrTitle = 'Cuentas por pagar';
    $hdrSubtitle = 'Facturas pendientes de pago a proveedores';
    
    // Fechas desde el controlador
    $fechaInicio = $fechaInicio ?? Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = $fechaFin ?? Carbon::now()->format('Y-m-d');
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
                    <li class="breadcrumb-item active" aria-current="page">Cuentas por pagar</li>
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
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-file-text me-1"></i>Total Facturas
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#0d6efd;font-size:1.2rem;">
                                    {{ $estadisticas['total_facturas'] ?? '0' }}
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
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="bi bi-clock-history me-1"></i>Saldo Pendiente
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#dc3545;font-size:1.2rem;">
                                    {{ $estadisticas['total_pendiente'] ?? '$0.00' }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(220,53,69,0.1);">
                                <i class="bi bi-clock-history" style="font-size:1.2rem;color:#dc3545;"></i>
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
                                    <i class="bi bi-credit-card me-1"></i>Total Pagado
                                </p>
                                <h5 class="mb-0 fw-bold" style="color:#198754;font-size:1.2rem;">
                                    {{ $estadisticas['total_pagado'] ?? '$0.00' }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(25,135,84,0.1);">
                                <i class="bi bi-credit-card" style="font-size:1.2rem;color:#198754;"></i>
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
                                    {{ $estadisticas['total_general'] ?? '$0.00' }}
                                </h5>
                            </div>
                            <div class="rounded-circle p-2" style="background:rgba(255,193,7,0.1);">
                                <i class="bi bi-cash-stack" style="font-size:1.2rem;color:#ffc107;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE FACTURAS --}}
        {{-- ================================================ --}}
        @if($listadoFacturas && $listadoFacturas->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-file-text me-2"></i>
                        Facturas Activas
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;color:#be123c;">
                            {{ $listadoFacturas->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.proveedor.mercancia.registrar_facturas') }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                            <i class="bi bi-plus-circle me-1"></i>Nueva
                        </a>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelCuentas()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFCuentas()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaCuentas">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:180px;">
                                    PROVEEDOR
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">
                                    FACTURA
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">
                                    FECHA
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    TOTAL (USD)
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    PAGADO
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    SALDO
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    DÍAS
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listadoFacturas as $factura)
                                @php
                                    $saldo = $factura->saldo_pendiente ?? 0;
                                    $dias = $factura->DiasTranscurridos ?? 0;
                                    
                                    // Color según días
                                    $diasColor = $dias > 90 ? 'danger' : ($dias > 60 ? 'warning' : ($dias > 30 ? 'info' : 'secondary'));
                                    
                                    // Estado
                                    if ($saldo <= 0) {
                                        $estado = 'PAGADO';
                                        $estadoBadge = 'success';
                                    } elseif ($dias > 30) {
                                        $estado = 'VENCIDO';
                                        $estadoBadge = 'danger';
                                    } elseif ($dias > 15) {
                                        $estado = 'PRÓXIMO A VENCER';
                                        $estadoBadge = 'warning';
                                    } else {
                                        $estado = 'AL DÍA';
                                        $estadoBadge = 'info';
                                    }
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 py-3">
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark">{{ $factura->proveedor_nombre ?? 'N/A' }}</p>
                                            <small class="text-muted" style="font-size:0.7rem;">
                                                Código: <code style="font-size:0.7rem;color:#be123c;">{{ $factura->ProveedorId ?? 'N/A' }}</code>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#be123c;font-weight:bold;">
                                            {{ $factura->Numero ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td class="py-3">
                                        <span style="font-size:0.85rem;">
                                            {{ $factura->FechaCreacionFormateada ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            ${{ number_format($factura->MontoDivisa ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end">
                                        <span class="fw-semibold" style="color:#22c55e;">
                                            ${{ number_format($factura->total_pagado ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end">
                                        <span class="fw-bold" style="color:{{ $saldo > 0 ? '#dc3545' : '#22c55e' }};font-size:1.05rem;">
                                            ${{ number_format($saldo, 2) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge rounded-pill bg-{{ $diasColor }}" style="font-size:0.75rem;">
                                            {{ $dias }} días
                                        </span>
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @php
                                                $saldo = $factura->saldo_pendiente ?? 0;
                                                $estatus = $factura->Estatus ?? 0;
                                                $facturaId = $factura->ID ?? $factura->FacturaId ?? 0;
                                                $numeroFactura = $factura->Numero ?? 'N/A';
                                                $estaPagada = $saldo <= 0;
                                                
                                                // Condición para mostrar el botón de eliminar habilitado
                                                // ========================================================
                                                // Se puede eliminar si: saldo == 0 (pagada) O estatus == 1 (En proceso)
                                                // ========================================================
                                                $puedeEliminar = ($saldo == 0 || $estatus == 1);
                                            @endphp

                                            {{-- DETALLES - Siempre visible --}}
                                            <a href="{{ route('cpanel.facturas.detalle', ['id' => $facturaId, 'origen' => 'cuentas_pagar']) }}"
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                            title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>

                                            {{-- EDITAR - Solo si NO está pagada --}}
                                            @if(!$estaPagada)
                                                <a href="{{ route('cpanel.facturas.editar', ['id' => $facturaId, 'origen' => 'cuentas_pagar']) }}"
                                                class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;background:rgba(13,110,253,0.1);color:#0d6efd;border:1px solid rgba(13,110,253,0.25);"
                                                title="Editar factura" data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                </a>
                                            @endif

                                            {{-- ELIMINAR - Solo si saldo == 0 O estatus == 1 --}}
                                            @if($puedeEliminar)
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarFactura({{ $factura->ID }}, '{{ $factura->Numero }}')"
                                                        title="Eliminar factura" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(107,114,128,0.06);color:#9ca3af;border:1px solid rgba(107,114,128,0.15);cursor:not-allowed;"
                                                        disabled
                                                        title="No se puede eliminar (tiene saldo pendiente)" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;opacity:0.5;"></i>
                                                </button>
                                            @endif
                                        </div>
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
                        <i class="bi bi-file-text me-1"></i>
                        {{ $listadoFacturas->count() }} factura{{ $listadoFacturas->count() != 1 ? 's' : '' }} activas
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-credit-card me-1"></i>
                        Saldo pendiente total: 
                        <span class="fw-bold text-danger">{{ $estadisticas['total_pendiente'] }}</span>
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
                         style="width:64px;height:64px;background:rgba(225,29,72,0.08);">
                        <i class="bi bi-file-text"
                           style="font-size:1.8rem;opacity:.5;color:#be123c;"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-0">No hay facturas activas</p>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        No se encontraron facturas activas en el período seleccionado.
                    </p>
                    <a href="{{ route('cpanel.proveedor.mercancia.registrar_facturas') }}" 
                       class="btn mt-2" style="background:{{ $hdrBg }};color:#fff;border:none;">
                        <i class="bi bi-plus-circle me-1"></i>Crear nueva factura
                    </a>
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
        // BUSCADOR DE PROVEEDORES
        // ==========================
        const buscador = document.getElementById('buscadorProveedor');
        const tabla = document.getElementById('tablaCuentas');
        const limpiarBtn = document.getElementById('limpiarBuscador');

        if (buscador && tabla) {
            function filtrarTabla() {
                const textoBusqueda = buscador.value.toLowerCase().trim();
                const filas = tabla.querySelectorAll('tbody tr');
                let filasVisibles = 0;

                filas.forEach(fila => {
                    const celdaNombre = fila.children[0];
                    if (celdaNombre) {
                        const textoNombre = celdaNombre.textContent.toLowerCase();

                        if (textoBusqueda === '' || textoNombre.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });

                const tbody = tabla.querySelector('tbody');
                let mensajeNoResultados = document.getElementById('mensajeNoResultados');

                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultados';
                        const colspan = tabla.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="bi bi-search me-2"></i>
                                No se encontraron facturas del proveedor "${buscador.value}"
                            </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }

            buscador.addEventListener('input', filtrarTabla);

            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function() {
                    buscador.value = '';
                    filtrarTabla();
                    buscador.focus();
                });
            }
        }
    });

    // ==========================
    // EXPORTAR EXCEL
    // ==========================
    function exportarExcelCuentas() {
        const tabla = document.getElementById('tablaCuentas');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todas las facturas activas?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Cuentas por pagar" });
                XLSX.utils.book_append_sheet(wb, ws, 'Cuentas por pagar');
                XLSX.writeFile(wb, `Cuentas_por_pagar_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ==========================
    // EXPORTAR PDF
    // ==========================
    function exportarPDFCuentas() {
        const tabla = document.getElementById('tablaCuentas');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todas las facturas activas?',
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
                doc.setTextColor(225, 29, 72);
                doc.text('Cuentas por pagar', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Período: {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}`, 14, 22);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 29);

                doc.autoTable({
                    html: '#tablaCuentas',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [225, 29, 72] }
                });

                doc.save(`Cuentas_por_pagar_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ============================================
    // FUNCIÓN PARA ELIMINAR FACTURA
    // ============================================
    function eliminarFactura(facturaId, facturaNumero) {
        Swal.fire({
            title: '¿Eliminar factura?',
            html: `Estás a punto de eliminar la factura <strong>${facturaNumero}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
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

                // ✅ Usar la misma ruta que en la otra vista
                fetch(`{{ url('cpanel/facturas') }}/${facturaId}`, {
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
</script>
@endsection

@push('styles')
<style>
    #tablaCuentas tbody tr:hover { background-color: #f8fafc; }
    .card-header { border-radius: 8px 8px 0 0; }
    
    /* Tooltip en botón deshabilitado */
    .d-inline-block[data-bs-toggle="tooltip"] {
        cursor: help;
    }

    /* Estilos para botones de exportación en el header */
    .card-header .btn {
        transition: background 0.3s ease;
    }
    .card-header .btn:hover {
        background: rgba(255,255,255,0.25) !important;
    }
</style>
@endpush