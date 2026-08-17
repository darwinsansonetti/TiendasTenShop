@extends('layout.layout_dashboard')

@section('title', 'Registrar Pago Proveedor - ' . ($proveedor->Nombre ?? ''))

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#10b981,#059669)';
    $hdrIcon = 'cash-coin';
    
    // Determinar el máximo permitido (mínimo entre saldo pendiente y utilidad disponible)
    $maximoPermitido = min($balanceFacturas->saldoPendiente ?? 0, $utilidadRealDisponible ?? 0);
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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Registrar Pago - {{ $proveedor->Nombre ?? 'Proveedor' }}
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Pago con utilidad disponible desde Rentabilidad
                            <span class="badge bg-success ms-1">
                                {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}">Rentabilidad</a>
                    </li>
                    <li class="breadcrumb-item active">Pago</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- ALERTA DE UTILIDAD REAL DISPONIBLE --}}
        {{-- ================================================ --}}
        <div class="alert alert-success border-0 shadow-sm" style="border-left:4px solid #059669;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:rgba(16,185,129,0.15);">
                    <i class="bi bi-graph-up-arrow text-success" style="font-size:1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        Utilidad Real Disponible en el Período
                    </h6>
                    <p class="mb-0 text-muted" style="font-size:0.9rem;">
                        <span class="fw-bold text-success" style="font-size:1.2rem;">
                            ${{ number_format($utilidadRealDisponible, 2) }}
                        </span>
                        <span class="text-muted ms-2">
                            ({{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }})
                        </span>
                        <br>
                        <small class="text-muted">
                            Utilidad: <span class="fw-semibold">${{ number_format($utilidad, 2) }}</span>
                            | Pagos realizados: <span class="fw-semibold text-warning">${{ number_format($pagosRealizados, 2) }}</span>
                            | <span class="fw-bold text-success">Disponible: ${{ number_format($utilidadRealDisponible, 2) }}</span>
                        </small>
                    </p>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Información del Proveedor
                    </h6>
                    <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Rentabilidad
                    </a>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc }}"
                             alt="{{ $proveedor->Nombre }}"
                             class="rounded-circle"
                             style="width:90px;height:90px;object-fit:cover;border:3px solid #e2e8f0;">
                        <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                            @if($proveedor->Estatus == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Activo</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.72rem;">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.72rem;">Mercancía</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Servicio</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                                <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->Rif_Cedula ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Correo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->CorreoElectronico ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono Móvil</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoMovil ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Número de Cuenta</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->NumeroDeCuenta ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Registro</p>
                                <p class="mb-0 fw-semibold text-dark">{{ Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- KPI: RESUMEN DE BALANCE --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Facturas</p>
                                <h3 class="fw-bold text-dark mb-0">$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</h3>
                            </div>
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                <i class="bi bi-file-earmark-text text-white" style="font-size:1.1rem;"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:4px;background:#e2e8f0;">
                            <div class="progress-bar rounded-pill" style="width:100%;background:linear-gradient(90deg,#3b82f6,#1d4ed8);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Total acumulado de todas las facturas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Total Pagado</p>
                                <h3 class="fw-bold text-dark mb-0">$ {{ number_format($balanceFacturas->totalPagado, 2) }}</h3>
                            </div>
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:linear-gradient(135deg,#10b981,#059669);">
                                <i class="bi bi-check-circle text-white" style="font-size:1.1rem;"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:4px;background:#e2e8f0;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->totalPagado / $balanceFacturas->totalFacturas) * 100 : 0 }}%;background:linear-gradient(90deg,#10b981,#059669);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            {{ number_format($balanceFacturas->porcentajePagado ?? 0, 1) }}% del total facturado
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Saldo Pendiente</p>
                                <h3 class="fw-bold text-dark mb-0">$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</h3>
                            </div>
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                <i class="bi bi-clock text-white" style="font-size:1.1rem;"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:4px;background:#e2e8f0;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->saldoPendiente / $balanceFacturas->totalFacturas) * 100 : 0 }}%;background:linear-gradient(90deg,#f59e0b,#d97706);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Monto pendiente por pagar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Máximo a Pagar</p>
                                <h3 class="fw-bold text-dark mb-0">$ {{ number_format($maximoPermitido, 2) }}</h3>
                            </div>
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:44px;height:44px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                                <i class="bi bi-cash-stack text-white" style="font-size:1.1rem;"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:4px;background:#e2e8f0;">
                            <div class="progress-bar rounded-pill"
                                 style="width:{{ $balanceFacturas->totalFacturas > 0 ? ($maximoPermitido / $balanceFacturas->totalFacturas) * 100 : 0 }}%;background:linear-gradient(90deg,#8b5cf6,#7c3aed);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <span class="text-success">Utilidad real disponible</span> | 
                            <span class="text-muted">Saldo: ${{ number_format($balanceFacturas->saldoPendiente, 2) }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: FORMULARIO DE PAGO --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Nuevo Pago
                    </h6>
                    <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body pt-4">
                <form action="#" method="POST" enctype="multipart/form-data" id="formRegistrarPago">
                    @csrf
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">
                    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                    <input type="hidden" name="utilidad_real_disponible" value="{{ $utilidadRealDisponible }}">
                    <input type="hidden" name="maximo_permitido" value="{{ $maximoPermitido }}">
                    <input type="hidden" name="estatus" value="2">
                    <input type="hidden" name="tipo_transaccion" value="0">
                    <input type="hidden" name="sucursal_id" value="{{ session('sucursal_id', 1) }}">
                    <input type="hidden" name="redirect_to" value="rentabilidad">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Fecha <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar text-success"></i>
                                </span>
                                <input type="date" name="fecha" id="fecha"
                                       class="form-control border-start-0"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="tasa_cambio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Tasa de Cambio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="font-size:0.82rem;">$ 1 = Bs</span>
                                <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio"
                                       class="form-control border-start-0"
                                       value="{{ $tasaCambioActual }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="monto_divisa" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Monto en Divisas (USD) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="monto_divisa" id="monto_divisa"
                                       class="form-control border-start-0"
                                       placeholder="0.00" max="{{ $maximoPermitido }}" required>
                                <span class="input-group-text bg-white border-start-0 text-muted" style="font-size:0.75rem;">
                                    Máx: ${{ number_format($maximoPermitido, 2) }}
                                </span>
                            </div>
                            <div class="form-text" id="saldoInfo">
                                <span class="text-success">✅ Utilidad real disponible: ${{ number_format($utilidadRealDisponible, 2) }}</span>
                                <span class="text-muted ms-2">| Saldo pendiente: ${{ number_format($balanceFacturas->saldoPendiente, 2) }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="monto_bs" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Monto en Bolívares (Bs)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-semibold text-muted" style="font-size:0.82rem;">Bs</span>
                                <input type="number" step="0.01" name="monto_bs" id="monto_bs"
                                       class="form-control border-start-0" value="0">
                            </div>
                            <div class="form-text">Se calcula automáticamente (Monto USD × Tasa)</div>
                        </div>

                        <div class="col-md-12">
                            <label for="descripcion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-card-text text-success"></i>
                                </span>
                                <textarea name="descripcion" id="descripcion" rows="2"
                                          class="form-control border-start-0"
                                          placeholder="Pago con utilidad del período {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}" required>Pago con utilidad del período {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="forma_pago" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Forma de Pago <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-credit-card text-success"></i>
                                </span>
                                <select name="forma_pago" id="forma_pago"
                                        class="form-select border-start-0" required>
                                    <option value="">Seleccione</option>
                                    <option value="1">Efectivo</option>
                                    <option value="2">Transferencia</option>
                                    <option value="3">Cheque</option>
                                    <option value="4">Otros</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4" id="campo_numero_operacion" style="display:none;">
                            <label for="numero_operacion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Número de Operación
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-hash text-success"></i>
                                </span>
                                <input type="text" name="numero_operacion" id="numero_operacion"
                                       class="form-control border-start-0" placeholder="Ej: 12345678">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="comprobante" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Comprobante de Pago
                            </label>
                            <input type="file" name="comprobante" id="comprobante"
                                   class="form-control" accept="image/*,.pdf">
                            <div class="form-text">Formatos: JPG, PNG, PDF (Max 5MB)</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-success px-4 fw-semibold" id="btnGuardarPago">
                            <i class="bi bi-save me-2"></i>Registrar Pago
                        </button>
                        <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}"
                           class="btn btn-light border px-4">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Overlay para zoom de imágenes -->
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display: none;" onclick="closeZoom()">
    <div class="image-zoom-container" onclick="event.stopPropagation()">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="Zoom">
        <div class="image-description" id="imageDescription"></div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // FUNCIONES DE ZOOM
    // ============================================
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');

        document.getElementById('zoomedImage').src = fullImage;
        document.getElementById('imageDescription').textContent = description;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // ============================================
    // CÁLCULOS DEL FORMULARIO DE PAGO
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {

        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Zoom de imágenes
        document.querySelectorAll('.img-zoomable').forEach(img => {
            img.addEventListener('click', function() {
                const fullImage = this.getAttribute('data-full-image');
                const description = this.getAttribute('data-description');

                document.getElementById('zoomedImage').src = fullImage;
                document.getElementById('imageDescription').textContent = description;
                document.getElementById('imageZoomOverlay').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        var saldoPendiente = {{ $balanceFacturas->saldoPendiente ?? 0 }};
        var utilidadRealDisponible = {{ $utilidadRealDisponible ?? 0 }};
        var maximoPermitido = {{ $maximoPermitido }};
        var calculando = false;

        // Obtener elementos del DOM
        var montoDivisaInput = document.getElementById('monto_divisa');
        var montoBsInput = document.getElementById('monto_bs');
        var tasaCambioInput = document.getElementById('tasa_cambio');
        var formaPagoSelect = document.getElementById('forma_pago');
        var campoNumeroOperacion = document.getElementById('campo_numero_operacion');
        var numeroOperacionInput = document.getElementById('numero_operacion');
        var btnGuardarPago = document.getElementById('btnGuardarPago');
        var saldoInfoSpan = document.getElementById('saldoInfo');
        var formRegistrarPago = document.getElementById('formRegistrarPago');

        // Calcular monto en Bs
        function calcularMontoBs() {
            if (calculando) return;
            calculando = true;

            var montoDivisa = parseFloat(montoDivisaInput ? montoDivisaInput.value : 0) || 0;
            var tasaCambio = parseFloat(tasaCambioInput ? tasaCambioInput.value : 0) || 0;
            var montoBs = montoDivisa * tasaCambio;

            if (montoBsInput) {
                montoBsInput.value = montoBs.toFixed(2);
            }
            calculando = false;
        }

        // Validar monto contra máximo permitido
        function validarMonto() {
            var montoPago = parseFloat(montoDivisaInput ? montoDivisaInput.value : 0) || 0;
            var mensaje = '';

            if (montoPago > 0) {
                if (montoPago > maximoPermitido) {
                    mensaje = '<span class="text-danger">⚠️ El monto excede el máximo permitido ($' + maximoPermitido.toFixed(2) + ')</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = true;
                } else if (montoPago > saldoPendiente && saldoPendiente > 0) {
                    mensaje = '<span class="text-warning">⚠️ El monto excede el saldo pendiente ($' + saldoPendiente.toFixed(2) + ')</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = false;
                } else if (montoPago > utilidadRealDisponible) {
                    mensaje = '<span class="text-warning">⚠️ El monto excede la utilidad real disponible ($' + utilidadRealDisponible.toFixed(2) + ')</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = false;
                } else {
                    mensaje = '<span class="text-success">✅ Monto permitido (Máximo: $' + maximoPermitido.toFixed(2) + ')</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = false;
                }
            } else {
                mensaje = '<span class="text-muted">Utilidad real disponible: $' + utilidadRealDisponible.toFixed(2) + ' | Saldo pendiente: $' + saldoPendiente.toFixed(2) + '</span>';
                if (btnGuardarPago) btnGuardarPago.disabled = true;
            }

            if (saldoInfoSpan) saldoInfoSpan.innerHTML = mensaje;
        }

        // Mostrar/ocultar campo número de operación
        function toggleNumeroOperacion() {
            var formaPago = formaPagoSelect ? formaPagoSelect.value : '';
            if (formaPago == '2') {
                if (campoNumeroOperacion) campoNumeroOperacion.style.display = 'block';
            } else {
                if (campoNumeroOperacion) campoNumeroOperacion.style.display = 'none';
                if (numeroOperacionInput) numeroOperacionInput.value = '';
            }
        }

        // Eventos
        if (montoDivisaInput) {
            montoDivisaInput.addEventListener('keyup', function() { calcularMontoBs(); validarMonto(); });
            montoDivisaInput.addEventListener('change', function() { calcularMontoBs(); validarMonto(); });
            montoDivisaInput.max = maximoPermitido;
            montoDivisaInput.placeholder = 'Máximo: $' + maximoPermitido.toFixed(2);
        }

        if (montoBsInput) {
            montoBsInput.addEventListener('keyup', function() { calcularMontoDivisa(); validarMonto(); });
            montoBsInput.addEventListener('change', function() { calcularMontoDivisa(); validarMonto(); });
        }

        if (tasaCambioInput) {
            tasaCambioInput.addEventListener('keyup', calcularMontoBs);
            tasaCambioInput.addEventListener('change', calcularMontoBs);
        }

        if (formaPagoSelect) {
            formaPagoSelect.addEventListener('change', toggleNumeroOperacion);
        }

        // Inicializar cálculos
        calcularMontoBs();
        toggleNumeroOperacion();
        validarMonto();

        // Hacer editable el campo monto_bs
        if (montoBsInput) {
            montoBsInput.removeAttribute('readonly');
        }

        // ============================================
        // ENVÍO DEL FORMULARIO
        // ============================================
        if (formRegistrarPago) {
            formRegistrarPago.addEventListener('submit', function(e) {
                e.preventDefault();

                let fecha = document.getElementById('fecha')?.value || '';
                let descripcion = document.getElementById('descripcion')?.value || '';
                let tasaDeCambio = document.getElementById('tasa_cambio')?.value || 0;
                let montoDivisaAbonado = document.getElementById('monto_divisa')?.value || 0;
                let montoAbonado = document.getElementById('monto_bs')?.value || 0;
                let formaDePago = document.getElementById('forma_pago')?.value || '';
                let numeroOperacion = document.getElementById('numero_operacion')?.value || '';
                let proveedorId = document.querySelector('input[name="proveedor_id"]')?.value || '';
                let estatus = document.getElementById('estatus')?.value || 2;
                let tipoTransaccion = document.getElementById('tipo_transaccion')?.value || 0;
                let sucursalId = document.getElementById('sucursal_id')?.value || 1;
                let comprobante = document.getElementById('comprobante')?.files[0] || null;

                if (!fecha) {
                    Swal.fire('Error', 'La fecha es requerida', 'error');
                    return false;
                }

                if (!descripcion) {
                    Swal.fire('Error', 'La descripción es requerida', 'error');
                    return false;
                }

                if (parseFloat(montoDivisaAbonado) <= 0) {
                    Swal.fire('Error', 'El monto debe ser mayor a 0', 'error');
                    return false;
                }

                if (!formaDePago) {
                    Swal.fire('Error', 'La forma de pago es requerida', 'error');
                    return false;
                }

                if (parseFloat(montoDivisaAbonado) > maximoPermitido) {
                    Swal.fire('Error', 'El monto no puede exceder el máximo permitido ($' + maximoPermitido.toFixed(2) + ')', 'error');
                    return false;
                }

                var formData = new FormData();
                formData.append('proveedor_id', proveedorId);
                formData.append('fecha', fecha);
                formData.append('descripcion', descripcion);
                formData.append('tasa_cambio', tasaDeCambio);
                formData.append('monto_divisa', montoDivisaAbonado);
                formData.append('monto_bs', montoAbonado);
                formData.append('forma_pago', formaDePago);
                formData.append('numero_operacion', numeroOperacion);
                formData.append('estatus', estatus);
                formData.append('tipo_transaccion', tipoTransaccion);
                formData.append('sucursal_id', sucursalId);
                formData.append('redirect_to', 'rentabilidad');

                if (comprobante) {
                    formData.append('comprobante', comprobante);
                }

                if (btnGuardarPago) {
                    btnGuardarPago.disabled = true;
                    btnGuardarPago.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Procesando...';
                }

                fetch('{{ route("cpanel.pagos.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("cpanel.proveedor.mercancia.rentabilidad") }}';
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        if (btnGuardarPago) {
                            btnGuardarPago.disabled = false;
                            btnGuardarPago.innerHTML = '<i class="bi bi-save me-1"></i> Registrar Pago';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al procesar el pago', 'error');
                    if (btnGuardarPago) {
                        btnGuardarPago.disabled = false;
                        btnGuardarPago.innerHTML = '<i class="bi bi-save me-1"></i> Registrar Pago';
                    }
                });
            });
        }
    });

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeZoom();
        }
    });

    // Cerrar al hacer clic fuera de la imagen
    document.getElementById('imageZoomOverlay')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeZoom();
        }
    });
</script>
@endsection

@push('styles')
<style>
    .image-zoom-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; animation: fadeInOverlay 0.3s ease-out; }
    .image-zoom-container { position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; }
    .image-zoom-container img { max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); animation: zoomInImage 0.3s ease-out; }
    .image-zoom-close { position: absolute; top: -40px; right: -10px; color: white; font-size: 40px; cursor: pointer; background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .image-zoom-close:hover { color: #ff6b6b; background: rgba(0,0,0,0.7); }
    .image-description { color: white; text-align: center; margin-top: 20px; font-size: 1.1rem; background: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 8px; max-width: 80%; }
    .img-zoomable { transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: zoom-in; }
    .img-zoomable:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    .input-group-text { border-color: #dee2e6; }
    .form-control:focus, .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 0.2rem rgba(16,185,129,.15); }
    @keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }
    @keyframes zoomInImage { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
@endpush