@extends('layout.layout_dashboard')

@section('title', 'Registrar Pago Proveedor')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-cash-coin text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Registrar Pago Proveedor</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Registrar abono o pago de factura</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
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
        {{-- CARD 1: INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Información del Proveedor
                    </h6>
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-eye me-1"></i>Ver Detalles
                    </a>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-center">
                    {{-- Imagen + badges --}}
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
                    {{-- Datos del proveedor --}}
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
                                <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') }}</p>
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
            {{-- Total Facturas --}}
            <div class="col-md-4">
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
            {{-- Total Pagado --}}
            <div class="col-md-4">
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
            {{-- Saldo Pendiente --}}
            <div class="col-md-4">
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
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: FORMULARIO DE PAGO --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Nuevo Pago
                    </h6>
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body pt-4">
                <form action="#" method="POST" enctype="multipart/form-data" id="formRegistrarPago">
                    @csrf
                    {{-- Campos ocultos --}}
                    <input type="hidden" name="estatus" id="estatus" value="2">
                    <input type="hidden" name="tipo_transaccion" id="tipo_transaccion" value="0">
                    <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ session('sucursal_id', 1) }}">
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">

                    <div class="row g-3">
                        {{-- Fecha --}}
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Fecha <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar text-success"></i>
                                </span>
                                <input type="date" name="fecha" id="fecha"
                                       class="form-control border-start-0 @error('fecha') is-invalid @enderror"
                                       value="{{ old('fecha', date('Y-m-d')) }}" required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Tasa de Cambio --}}
                        <div class="col-md-6">
                            <label for="tasa_cambio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Tasa de Cambio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="font-size:0.82rem;">$ 1 = Bs</span>
                                <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio"
                                       class="form-control border-start-0 @error('tasa_cambio') is-invalid @enderror"
                                       value="{{ old('tasa_cambio', $tasaCambioActual ?? 40.00) }}" required>
                                @error('tasa_cambio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Se carga automáticamente según la fecha</div>
                        </div>

                        {{-- Monto en Divisas --}}
                        <div class="col-md-6">
                            <label for="monto_divisa" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Monto en Divisas (USD) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="monto_divisa" id="monto_divisa"
                                       class="form-control border-start-0 @error('monto_divisa') is-invalid @enderror"
                                       value="{{ old('monto_divisa') }}" placeholder="0.00" required>
                                @error('monto_divisa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text" id="saldoInfo">Saldo pendiente total: $ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</div>
                        </div>

                        {{-- Monto en Bolívares --}}
                        <div class="col-md-6">
                            <label for="monto_bs" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Monto en Bolívares (Bs)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-semibold text-muted" style="font-size:0.82rem;">Bs</span>
                                <input type="number" step="0.01" name="monto_bs" id="monto_bs"
                                       class="form-control border-start-0"
                                       value="{{ old('monto_bs', 0) }}">
                            </div>
                            <div class="form-text">Se calcula automáticamente (Monto USD × Tasa)</div>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-md-12">
                            <label for="descripcion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-card-text text-success"></i>
                                </span>
                                <textarea name="descripcion" id="descripcion" rows="2"
                                          class="form-control border-start-0 @error('descripcion') is-invalid @enderror"
                                          placeholder="Ej: Pago parcial de facturas..." required>{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Forma de Pago --}}
                        <div class="col-md-4">
                            <label for="forma_pago" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Forma de Pago <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-credit-card text-success"></i>
                                </span>
                                <select name="forma_pago" id="forma_pago"
                                        class="form-select border-start-0 @error('forma_pago') is-invalid @enderror" required>
                                    <option value="">Seleccione</option>
                                    <option value="1" {{ old('forma_pago') == 1 ? 'selected' : '' }}>Efectivo</option>
                                    <option value="2" {{ old('forma_pago') == 2 ? 'selected' : '' }}>Transferencia</option>
                                    <option value="3" {{ old('forma_pago') == 3 ? 'selected' : '' }}>Cheque</option>
                                    <option value="4" {{ old('forma_pago') == 4 ? 'selected' : '' }}>Otros</option>
                                </select>
                                @error('forma_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Número de Operación (solo transferencias) --}}
                        <div class="col-md-4" id="campo_numero_operacion" style="display:none;">
                            <label for="numero_operacion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Número de Operación
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-hash text-success"></i>
                                </span>
                                <input type="text" name="numero_operacion" id="numero_operacion"
                                       class="form-control border-start-0"
                                       value="{{ old('numero_operacion') }}" placeholder="Ej: 12345678">
                            </div>
                        </div>

                        {{-- Comprobante --}}
                        <div class="col-md-4">
                            <label for="comprobante" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Comprobante de Pago
                            </label>
                            <input type="file" name="comprobante" id="comprobante"
                                   class="form-control @error('comprobante') is-invalid @enderror"
                                   accept="image/*,.pdf">
                            <div class="form-text">Formatos: JPG, PNG, PDF (Max 5MB)</div>
                            @error('comprobante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-success px-4 fw-semibold" id="btnGuardarPago">
                            <i class="bi bi-save me-2"></i>Registrar Pago
                        </button>
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
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
    // FUNCIONES DE EXPORTACIÓN
    // ============================================
    function exportarExcelEstadoCuenta() {
        const tabla = document.getElementById('tablaEstadoCuenta');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Estado de Cuenta" });
        XLSX.utils.book_append_sheet(wb, ws, 'Estado de Cuenta');
        XLSX.writeFile(wb, `Estado_Cuenta_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaEstadoCuenta() {
        const tabla = document.getElementById('tablaEstadoCuenta');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Estado de Cuenta', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaEstadoCuenta', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Estado_Cuenta_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarExcelFacturas() {
        const tabla = document.getElementById('tablaFacturas');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Facturas" });
        XLSX.utils.book_append_sheet(wb, ws, 'Facturas');
        XLSX.writeFile(wb, `Facturas_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaFacturas() {
        const tabla = document.getElementById('tablaFacturas');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Facturas', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaFacturas', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Facturas_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarExcelPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Pagos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Pagos');
        XLSX.writeFile(wb, `Pagos_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Pagos', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaPagos', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Pagos_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarExcelProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Productos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Productos');
        XLSX.writeFile(wb, `Productos_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function pdfTablaProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Productos del Proveedor', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ html: '#tablaProductos', startY: 35, theme: 'grid', headStyles: { fillColor: [41, 128, 185] } });
        doc.save(`Productos_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // ============================================
    // CÁLCULOS AUTOMÁTICOS DEL FORMULARIO DE PAGO
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

        // ============================================
        // CÁLCULOS DEL FORMULARIO DE PAGO
        // ============================================

        var saldoPendienteTotal = {{ $balanceFacturas->saldoPendiente ?? 0 }};
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

        // Calcular monto en Bs a partir del monto en Divisas
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

        // Calcular monto en Divisas a partir del monto en Bs
        function calcularMontoDivisa() {
            if (calculando) return;
            calculando = true;

            var montoBs = parseFloat(montoBsInput ? montoBsInput.value : 0) || 0;
            var tasaCambio = parseFloat(tasaCambioInput ? tasaCambioInput.value : 0) || 0;

            if (tasaCambio > 0 && montoBsInput) {
                var montoDivisa = montoBs / tasaCambio;
                if (montoDivisaInput) {
                    montoDivisaInput.value = montoDivisa.toFixed(2);
                }
            }
            calculando = false;
        }

        // Validar que el monto no exceda el saldo pendiente total
        function validarMontoContraSaldo() {
            var montoPago = parseFloat(montoDivisaInput ? montoDivisaInput.value : 0) || 0;

            if (saldoInfoSpan) {
                if (montoPago > saldoPendienteTotal && saldoPendienteTotal > 0) {
                    saldoInfoSpan.innerHTML = '<span class="text-danger">⚠️ El monto excede el saldo pendiente total ($' + saldoPendienteTotal.toFixed(2) + ')</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = true;
                } else {
                    saldoInfoSpan.innerHTML = '<span class="text-muted">Saldo pendiente total: $' + saldoPendienteTotal.toFixed(2) + '</span>';
                    if (btnGuardarPago) btnGuardarPago.disabled = false;
                }
            }
        }

        // Mostrar/ocultar campo número de operación
        function toggleNumeroOperacion() {
            var formaPago = formaPagoSelect ? formaPagoSelect.value : '';
            if (formaPago == '2') { // Transferencia
                if (campoNumeroOperacion) campoNumeroOperacion.style.display = 'block';
            } else {
                if (campoNumeroOperacion) campoNumeroOperacion.style.display = 'none';
                if (numeroOperacionInput) numeroOperacionInput.value = '';
            }
        }

        // Eventos
        if (montoDivisaInput) {
            montoDivisaInput.addEventListener('keyup', function() { calcularMontoBs(); validarMontoContraSaldo(); });
            montoDivisaInput.addEventListener('change', function() { calcularMontoBs(); validarMontoContraSaldo(); });
        }

        if (montoBsInput) {
            montoBsInput.addEventListener('keyup', function() { calcularMontoDivisa(); validarMontoContraSaldo(); });
            montoBsInput.addEventListener('change', function() { calcularMontoDivisa(); validarMontoContraSaldo(); });
        }

        if (tasaCambioInput) {
            tasaCambioInput.addEventListener('keyup', calcularMontoBs);
            tasaCambioInput.addEventListener('change', calcularMontoBs);
        }

        if (formaPagoSelect) {
            formaPagoSelect.addEventListener('change', toggleNumeroOperacion);
        }

        // ============================================
        // ENVÍO DEL FORMULARIO (como en .NET)
        // ============================================
        if (formRegistrarPago) {
            formRegistrarPago.addEventListener('submit', function(e) {
                e.preventDefault();

                // Recolectar datos como en .NET
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

                // Validaciones
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

                if (parseFloat(montoDivisaAbonado) > saldoPendienteTotal && saldoPendienteTotal > 0) {
                    Swal.fire('Error', 'El monto no puede exceder el saldo pendiente total', 'error');
                    return false;
                }

                // Crear FormData para enviar archivo
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

                if (comprobante) {
                    formData.append('comprobante', comprobante);
                }

                // Mostrar loading
                if (btnGuardarPago) {
                    btnGuardarPago.disabled = true;
                    btnGuardarPago.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Procesando...';
                }

                // Enviar por AJAX/Fetch
                fetch('{{ route("cpanel.pagos.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',  // ← Igual que en tu otra vista
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
                            // Redirigir al detalle del proveedor
                            window.location.href = '{{ route("cpanel.proveedores.detalle", $proveedor->ProveedorId) }}';
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

        // Inicializar cálculos
        calcularMontoBs();
        toggleNumeroOperacion();

        // Hacer editable el campo monto_bs
        if (montoBsInput) {
            montoBsInput.removeAttribute('readonly');
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
