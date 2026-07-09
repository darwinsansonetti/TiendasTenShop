@extends('layout.layout_dashboard')

@section('title', 'Procesar Auditoría')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Procesar Auditoría</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Revisión y aprobación de productos en auditoría</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.recepciones.auditorias') }}">Auditar Recepciones</a></li>
                    <li class="breadcrumb-item active">Procesar Auditoría</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DE LA AUDITORÍA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Auditoría
                        <span class="ms-2 badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            #{{ $auditoria->Numero }}
                        </span>
                    </h6>
                    @php
                        $estatusInfo = $estatusMap[$auditoria->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'bg-secondary'];
                        $claseEstatus = $estatusInfo['clase'] ?? '';
                        $badgeStyle = str_contains($claseEstatus, 'success')
                            ? 'background:rgba(16,185,129,0.2);color:#fff;border:1px solid rgba(16,185,129,0.4)'
                            : (str_contains($claseEstatus, 'warning')
                                ? 'background:rgba(245,158,11,0.2);color:#fff;border:1px solid rgba(245,158,11,0.4)'
                                : (str_contains($claseEstatus, 'danger')
                                    ? 'background:rgba(239,68,68,0.2);color:#fff;border:1px solid rgba(239,68,68,0.4)'
                                    : 'background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3)'));
                    @endphp
                    <span class="badge rounded-pill px-3 py-1 fw-semibold"
                          style="{{ $badgeStyle }};font-size:0.78rem;">
                        {{ $estatusInfo['texto'] }}
                    </span>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Recepción</p>
                        <p class="mb-0 fw-bold text-dark">{{ $auditoria->recepcion_numero }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Proveedor</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $auditoria->proveedor_nombre }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal Destino</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $auditoria->sucursal_destino }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha</p>
                        <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($auditoria->Fecha)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: PRODUCTOS EN AUDITORÍA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Productos en Auditoría
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($auditoria->Estatus < 2)
                            <button class="btn btn-sm fw-semibold"
                                    onclick="aprobarAuditoria({{ $auditoria->AuditoriaId }})"
                                    style="background:#059669;color:#fff;border:1px solid #047857;font-size:0.78rem;">
                                <i class="bi bi-check-circle me-1"></i>Aprobar Todo
                            </button>
                            <button class="btn btn-sm fw-semibold"
                                    onclick="rechazarAuditoria({{ $auditoria->AuditoriaId }})"
                                    style="background:#dc2626;color:#fff;border:1px solid #b91c1c;font-size:0.78rem;">
                                <i class="bi bi-x-circle me-1"></i>Rechazar Todo
                            </button>
                        @else
                            <span class="badge rounded-pill fw-semibold px-3 py-2"
                                  style="background:rgba(16,185,129,0.2);color:#fff;border:1px solid rgba(16,185,129,0.4);font-size:0.78rem;">
                                <i class="bi bi-check-circle me-1"></i>Auditoría Finalizada
                            </span>
                        @endif
                        <a href="{{ route('cpanel.recepciones.auditorias') }}"
                           class="btn btn-light btn-sm fw-semibold"
                           style="font-size:0.8rem;">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PRODUCTO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. PEDIDA (FACTURA)</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. RECIBIDA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DIF. CANT.</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PIE SOLO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PIE INV.</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DAÑADO</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detallesConDiferencia as $detalle)
                            @php
                                $uxe = $detalle->factura_uxe ?? 1;
                                $cantidadFacturaReal = ($detalle->factura_cantidad_empaques ?? 0) * $uxe;
                                $cantidadRecibida = $detalle->CantidadRecibida ?? 0;
                                $diferenciaCantidad = $cantidadRecibida - $cantidadFacturaReal;

                                // Diferencias en Pie Solo, Pie Inv., Dañado
                                $diferenciaPieSolo = $detalle->diferencia_pie_solo ?? 0;
                                $diferenciaPieInvertido = $detalle->diferencia_pie_invertido ?? 0;
                                $diferenciaDanado = $detalle->diferencia_danado ?? 0;

                                // Badge para Diferencia Cantidad
                                if ($diferenciaCantidad > 0) {
                                    $signoCantidad = '+';
                                    $badgeDifCantidad = 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)';
                                } elseif ($diferenciaCantidad < 0) {
                                    $signoCantidad = '';
                                    $badgeDifCantidad = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                } else {
                                    $signoCantidad = '';
                                    $badgeDifCantidad = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }

                                // Badge para Pie Solo
                                if ($diferenciaPieSolo > 0) {
                                    $signoPieSolo = '+';
                                    $badgePieSolo = 'background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25)';
                                } elseif ($diferenciaPieSolo < 0) {
                                    $signoPieSolo = '';
                                    $badgePieSolo = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                } else {
                                    $signoPieSolo = '';
                                    $badgePieSolo = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }

                                // Badge para Pie Invertido
                                if ($diferenciaPieInvertido > 0) {
                                    $signoPieInvertido = '+';
                                    $badgePieInvertido = 'background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25)';
                                } elseif ($diferenciaPieInvertido < 0) {
                                    $signoPieInvertido = '';
                                    $badgePieInvertido = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                } else {
                                    $signoPieInvertido = '';
                                    $badgePieInvertido = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }

                                // Badge para Dañado
                                if ($diferenciaDanado > 0) {
                                    $signoDanado = '+';
                                    $badgeDanado = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                } elseif ($diferenciaDanado < 0) {
                                    $signoDanado = '';
                                    $badgeDanado = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                } else {
                                    $signoDanado = '';
                                    $badgeDanado = 'background:rgba(107,114,128,0.1);color:#374151;border:1px solid rgba(107,114,128,0.25)';
                                }

                                $tieneAccion = !is_null($detalle->Accion);
                                $accionTexto = '';
                                $accionStyle = '';
                                if ($tieneAccion) {
                                    if ($detalle->Accion == 1) {
                                        $accionTexto = 'Aprobado';
                                        $accionStyle = 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25)';
                                    } elseif ($detalle->Accion == 2) {
                                        $accionTexto = 'Rechazado';
                                        $accionStyle = 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25)';
                                    }
                                }
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    <span class="badge rounded-2 fw-semibold"
                                        style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;font-family:monospace;">
                                        {{ $detalle->Codigo }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark" style="font-size:0.88rem;">
                                    {{ $detalle->producto_nombre }}
                                </td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">
                                    {{ number_format($detalle->cantidad_factura_real, 2) }}
                                </td>
                                <td class="text-end text-muted" style="font-size:0.88rem;">
                                    {{ number_format($detalle->CantidadRecibida, 2) }}
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill fw-bold px-2 py-1"
                                        style="{{ $badgeDifCantidad }};font-size:0.82rem;">
                                        {{ $signoCantidad }}{{ number_format(abs($diferenciaCantidad), 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill fw-bold px-2 py-1"
                                        style="{{ $badgePieSolo }};font-size:0.82rem;">
                                        {{ $signoPieSolo }}{{ number_format(abs($diferenciaPieSolo), 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill fw-bold px-2 py-1"
                                        style="{{ $badgePieInvertido }};font-size:0.82rem;">
                                        {{ $signoPieInvertido }}{{ number_format(abs($diferenciaPieInvertido), 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill fw-bold px-2 py-1"
                                        style="{{ $badgeDanado }};font-size:0.82rem;">
                                        {{ $signoDanado }}{{ number_format(abs($diferenciaDanado), 2) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($tieneAccion)
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                            style="{{ $accionStyle }};font-size:0.75rem;">
                                            <i class="bi bi-{{ $detalle->Accion == 1 ? 'check' : 'x' }}-circle me-1"></i>
                                            {{ $accionTexto }}
                                        </span>
                                    @else
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="btn btn-sm fw-semibold rounded-2"
                                                    onclick="editarRecibido(
                                                        {{ $detalle->AuditoriaDetalleId }}, 
                                                        '{{ $detalle->Codigo }}', 
                                                        {{ $detalle->CantidadRecibida }}, 
                                                        {{ $detalle->cantidad_factura_real }},
                                                        '{{ $detalle->producto_nombre }}'
                                                    )"
                                                    title="Editar cantidad recibida"
                                                    data-bs-toggle="tooltip"
                                                    style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm fw-semibold rounded-2"
                                                    onclick="aprobarProducto({{ $detalle->AuditoriaDetalleId }}, '{{ $detalle->Codigo }}')"
                                                    title="Aprobar este producto"
                                                    data-bs-toggle="tooltip"
                                                    style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.78rem;">
                                                <i class="bi bi-check-circle" style="font-size:0.8rem;"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm fw-semibold rounded-2"
                                                    onclick="rechazarProducto({{ $detalle->AuditoriaDetalleId }}, '{{ $detalle->Codigo }}')"
                                                    title="Rechazar este producto"
                                                    data-bs-toggle="tooltip"
                                                    style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.78rem;">
                                                <i class="bi bi-x-circle" style="font-size:0.8rem;"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                        style="width:52px;height:52px;background:linear-gradient(135deg,#f59e0b,#d97706);opacity:0.5;">
                                        <i class="bi bi-clipboard-check text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en auditoría</p>
                                    <small class="text-muted">Los productos pendientes de revisión aparecerán aquí</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ================================================ --}}
{{-- MODAL: EDITAR CANTIDAD RECIBIDA --}}
{{-- ================================================ --}}
<div class="modal fade" id="modalEditarRecibido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            {{-- HEADER --}}
            <div class="modal-header border-0 py-2"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <h6 class="modal-title text-white fw-bold" style="font-size:0.9rem;">
                    <i class="bi bi-pencil-square me-1"></i>Editar Cantidad Recibida
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size:0.7rem;"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body py-3">
                <form id="formEditarRecibido">
                    @csrf
                    <input type="hidden" id="edit_auditoria_detalle_id" name="auditoria_detalle_id">
                    <input type="hidden" id="edit_codigo" name="codigo">
                    <input type="hidden" id="edit_cantidad_factura" name="cantidad_factura">
                    
                    {{-- Producto --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-muted" style="font-size:0.7rem;letter-spacing:.05em;margin-bottom:2px;">
                            <i class="bi bi-box me-1"></i>PRODUCTO
                        </label>
                        <div class="p-2 rounded-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <p class="mb-0 fw-bold text-dark" id="edit_producto_nombre" style="font-size:0.85rem;">-</p>
                            <small class="text-muted" id="edit_producto_codigo" style="font-size:0.7rem;"></small>
                        </div>
                    </div>

                    {{-- Cantidad Factura (PEDIDA) --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-muted" style="font-size:0.7rem;letter-spacing:.05em;margin-bottom:2px;">
                            <i class="bi bi-file-text me-1"></i>CANT. PEDIDA (FACTURA)
                        </label>
                        <div class="p-2 rounded-2" style="background:#eff6ff;border:1px solid #bfdbfe;">
                            <p class="mb-0 fw-bold" id="edit_cantidad_factura_label" style="font-size:0.9rem;color:#1e40af;">-</p>
                        </div>
                    </div>

                    {{-- Cantidad Actual --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-muted" style="font-size:0.7rem;letter-spacing:.05em;margin-bottom:2px;">
                            <i class="bi bi-database me-1"></i>CANTIDAD RECIBIDA ACTUAL
                        </label>
                        <div class="p-2 rounded-2" style="background:#fefce8;border:1px solid #fde68a;">
                            <p class="mb-0 fw-bold" id="edit_cantidad_actual" style="font-size:0.9rem;color:#92400e;">-</p>
                        </div>
                    </div>

                    {{-- Cantidad Nueva --}}
                    <div class="mb-2">
                        <label for="edit_cantidad_nueva" class="form-label fw-semibold text-muted" style="font-size:0.7rem;letter-spacing:.05em;margin-bottom:2px;">
                            <i class="bi bi-pencil me-1"></i>NUEVA CANTIDAD
                        </label>
                        <div class="input-group" style="height:32px;">
                            <span class="input-group-text" style="background:#f8fafc;border-color:#d1d5db;padding:0 8px;font-size:0.75rem;">
                                <i class="bi bi-hash text-muted"></i>
                            </span>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_cantidad_nueva" 
                                   name="cantidad_nueva" 
                                   step="1" 
                                   min="0"
                                   placeholder="Nueva cantidad"
                                   style="font-weight:600;font-size:0.9rem;border-color:#d1d5db;height:32px;padding:2px 8px;">
                        </div>
                    </div>

                    {{-- Información adicional --}}
                    <div class="mt-2 p-2 rounded-2" style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <div class="d-flex align-items-start gap-1">
                            <i class="bi bi-info-circle text-primary mt-1" style="font-size:0.7rem;"></i>
                            <div>
                                <ul class="mb-0 ps-3" style="font-size:0.65rem;color:#1e40af;margin:0;">
                                    <li>Se actualizará la recepción y el stock</li>
                                    <li>Se recalcularán las diferencias</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 py-2" style="background:#f8fafc;border-radius:0 0 12px 12px;">
                <button type="button" class="btn btn-light btn-sm fw-semibold px-3" data-bs-dismiss="modal" style="font-size:0.75rem;">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm fw-semibold px-3" onclick="guardarEdicionRecibido()" style="font-size:0.75rem;background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border:none;">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
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
    // Tooltips
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });

    // ============================================
    // APROBAR TODA LA AUDITORÍA
    // ============================================
    function aprobarAuditoria(auditoriaId) {
        Swal.fire({
            title: '¿Aprobar toda la auditoría?',
            text: 'Se aprobarán todos los productos de esta auditoría',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aprobar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Aprobando auditoría',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`{{ url('cpanel/auditorias') }}/${auditoriaId}/aprobar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Auditoría aprobada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.href = '{{ route("cpanel.recepciones.auditorias") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    }

    // ============================================
    // RECHAZAR TODA LA AUDITORÍA
    // ============================================
    function rechazarAuditoria(auditoriaId) {
        Swal.fire({
            title: '¿Rechazar toda la auditoría?',
            text: 'Se rechazarán todos los productos de esta auditoría',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, rechazar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Rechazando auditoría',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`{{ url('cpanel/auditorias') }}/${auditoriaId}/rechazar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Auditoría rechazada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.href = '{{ route("cpanel.recepciones.auditorias") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    }

    // ============================================
    // APROBAR PRODUCTO INDIVIDUAL
    // ============================================
    function aprobarProducto(auditoriaDetalleId, codigoProducto) {
        Swal.fire({
            title: '¿Aprobar este producto?',
            text: 'Se aprobará la diferencia de este producto (Código: ' + codigoProducto + ')',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Aprobando producto',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`{{ url('cpanel/auditorias/producto') }}/${auditoriaDetalleId}/aprobar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        codigo: codigoProducto  // ✅ Enviamos el código
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Producto aprobado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    }

    // ============================================
    // RECHAZAR PRODUCTO INDIVIDUAL
    // ============================================
    function rechazarProducto(auditoriaDetalleId, codigoProducto) {
        Swal.fire({
            title: '¿Rechazar este producto?',
            text: 'Se rechazará la diferencia de este producto (Código: ' + codigoProducto + ')',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Rechazando producto',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`{{ url('cpanel/auditorias/producto') }}/${auditoriaDetalleId}/rechazar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        codigo: codigoProducto  // ✅ Enviamos el código
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Producto rechazado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    }

    // ============================================
    // ABRIR MODAL PARA EDITAR CANTIDAD RECIBIDA
    // ============================================
    function editarRecibido(auditoriaDetalleId, codigo, cantidadActual, cantidadFactura, nombreProducto) {
        // Llenar los campos del modal
        document.getElementById('edit_auditoria_detalle_id').value = auditoriaDetalleId;
        document.getElementById('edit_codigo').value = codigo;
        document.getElementById('edit_cantidad_factura').value = cantidadFactura;
        document.getElementById('edit_producto_nombre').textContent = nombreProducto;
        document.getElementById('edit_producto_codigo').textContent = codigo;
        document.getElementById('edit_cantidad_actual').textContent = cantidadActual;
        document.getElementById('edit_cantidad_factura_label').textContent = cantidadFactura;
        document.getElementById('edit_cantidad_nueva').value = cantidadActual;
        
        // Abrir el modal
        const modal = new bootstrap.Modal(document.getElementById('modalEditarRecibido'));
        modal.show();
    }

    // ============================================
    // GUARDAR EDICIÓN DE CANTIDAD RECIBIDA
    // ============================================
    function guardarEdicionRecibido() {
        const auditoriaDetalleId = document.getElementById('edit_auditoria_detalle_id').value;
        const codigo = document.getElementById('edit_codigo').value;
        const cantidadActual = parseFloat(document.getElementById('edit_cantidad_actual').textContent);
        const cantidadFactura = parseFloat(document.getElementById('edit_cantidad_factura').value);
        const cantidadNueva = parseFloat(document.getElementById('edit_cantidad_nueva').value);
        
        // ✅ Validación 1: Campo vacío o inválido
        if (!cantidadNueva || isNaN(cantidadNueva) || cantidadNueva < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'Por favor ingrese una cantidad válida (número positivo)'
            });
            return;
        }
        
        // ✅ Validación 2: Nueva cantidad igual a la cantidad de la FACTURA (PEDIDA)
        if (cantidadNueva === cantidadFactura) {
            Swal.fire({
                icon: 'info',
                title: 'Sin cambios necesarios',
                html: `La nueva cantidad (${cantidadNueva}) es igual a la cantidad pedida en factura (${cantidadFactura}).<br><br>No se requieren ajustes.`,
                confirmButtonColor: '#3b82f6'
            });
            return;
        }
        
        // ✅ Validación 3: Nueva cantidad igual a la actual
        if (cantidadNueva === cantidadActual) {
            Swal.fire({
                icon: 'info',
                title: 'Sin cambios',
                text: 'La nueva cantidad es igual a la cantidad actual. No se realizaron cambios.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }
        
        // ✅ Confirmación con SweetAlert
        const diferenciaFactura = cantidadNueva - cantidadFactura;
        const diferenciaActual = cantidadNueva - cantidadActual;
        
        const textoDiferenciaFactura = diferenciaFactura > 0 
            ? `<span style="color:#059669;">+${diferenciaFactura} unidades (sobrante)</span>` 
            : `<span style="color:#dc2626;">${diferenciaFactura} unidades (faltante)</span>`;
        
        const textoDiferenciaActual = diferenciaActual > 0 
            ? `<span style="color:#059669;">+${diferenciaActual} unidades (aumento)</span>` 
            : `<span style="color:#dc2626;">${diferenciaActual} unidades (disminución)</span>`;
        
        Swal.fire({
            title: '¿Confirmar cambios?',
            html: `
                <div style="text-align:left;">
                    <p><strong>Producto:</strong> <span id="swal_producto_nombre"></span></p>
                    <p><strong>Cantidad pedida (factura):</strong> ${cantidadFactura} unidades</p>
                    <p><strong>Cantidad actual:</strong> ${cantidadActual} unidades</p>
                    <p><strong>Nueva cantidad:</strong> ${cantidadNueva} unidades</p>
                    <hr>
                    <p><strong>Diferencia vs Factura:</strong> ${textoDiferenciaFactura}</p>
                    <p><strong>Diferencia vs Actual:</strong> ${textoDiferenciaActual}</p>
                    <hr>
                    <small class="text-muted">Esta acción actualizará la recepción y el stock del producto.</small>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, guardar cambios',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                const nombreProducto = document.getElementById('edit_producto_nombre').textContent;
                document.getElementById('swal_producto_nombre').textContent = nombreProducto;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Actualizando cantidad',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`{{ url('cpanel/auditorias/producto') }}/${auditoriaDetalleId}/editar-recibido`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        codigo: codigo,
                        cantidad_nueva: cantidadNueva
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRecibido'));
                        modal.hide();
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    }

</script>
@endsection

@push('styles')
<style>
    #tablaProductos tbody tr:hover { background: #f8fafc; }
    #tablaProductos thead th { transition: background 0.15s; }
</style>
@endpush
