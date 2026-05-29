@extends('layout.layout_dashboard')

@section('title', 'Detalle del Proveedor')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="fas fa-truck me-2"></i>Registrar Pago Proveedor
                </h3>
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
        
        <!-- Tarjeta de Información del Proveedor -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>Información del Proveedor
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}" 
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i>Ver Detalles
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $proveedor->Nombre }}"
                             class="img-fluid rounded-circle border border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <div class="mt-2">
                            @if($proveedor->Estatus == 0)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge bg-primary">Mercancía</span>
                            @else
                                <span class="badge bg-info">Servicio</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="120">Nombre:</th>
                                        <td><strong>{{ $proveedor->Nombre }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Rif/Cédula:</th>
                                        <td>{{ $proveedor->Rif_Cedula ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono Móvil:</th>
                                        <td>{{ $proveedor->TelefonoMovil ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono Fijo:</th>
                                        <td>{{ $proveedor->TelefonoFijo ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="120">Email:</th>
                                        <td>{{ $proveedor->CorreoElectronico ?: 'N/A' }}</td>
                                    </tr>
                                    </tr>
                                        <th>Fecha Registro:</th>
                                        <td>{{ \Carbon\Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dirección:</th>
                                        <td>{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 50) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Número Cuenta:</th>
                                        <td>{{ $proveedor->NumeroDeCuenta ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </br>

        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="text-start">
                                <h6 class="text-muted text-uppercase fw-bold small mb-2">Total Facturas</h6>
                                <h3 class="fw-bold text-info mb-0">$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-file-invoice-dollar text-info fs-3"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Total acumulado de todas las facturas</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="text-start">
                                <h6 class="text-muted text-uppercase fw-bold small mb-2">Total Pagado</h6>
                                <h3 class="fw-bold text-success mb-0">$ {{ number_format($balanceFacturas->totalPagado, 2) }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-money-bill-wave text-success fs-3"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->totalPagado / $balanceFacturas->totalFacturas) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                {{ number_format($balanceFacturas->porcentajePagado ?? 0, 1) }}% del total facturado
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="text-start">
                                <h6 class="text-muted text-uppercase fw-bold small mb-2">Saldo Pendiente</h6>
                                <h3 class="fw-bold text-warning mb-0">$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fs-3"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: {{ $balanceFacturas->totalFacturas > 0 ? ($balanceFacturas->saldoPendiente / $balanceFacturas->totalFacturas) * 100 : 0 }}%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Monto pendiente por pagar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Registro de Pago -->
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-cash-stack me-2"></i>Registrar Nuevo Pago
                </h3>
            </div>
            <div class="card-body">
                <form action="#" method="POST" enctype="multipart/form-data" id="formRegistrarPago">
                    @csrf
                    <!-- Campos ocultos que se enviarán al controlador -->
                    <input type="hidden" name="estatus" id="estatus" value="2">
                    <input type="hidden" name="tipo_transaccion" id="tipo_transaccion" value="0">
                    <input type="hidden" name="sucursal_id" id="sucursal_id" value="{{ session('sucursal_id', 1) }}">
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">
                    
                    <div class="row">
                        <!-- Fecha -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha *</label>
                            <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror" 
                                value="{{ old('fecha', date('Y-m-d')) }}" required>
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Tasa de Cambio -->
                        <div class="col-md-6 mb-3">
                            <label for="tasa_cambio" class="form-label">Tasa de Cambio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$ 1 = Bs</span>
                                <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio" 
                                    class="form-control @error('tasa_cambio') is-invalid @enderror" 
                                    value="{{ old('tasa_cambio', $tasaCambioActual ?? 40.00) }}" required>
                            </div>
                            <small class="text-muted">Se carga automáticamente según la fecha</small>
                            @error('tasa_cambio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Monto en Divisas -->
                        <div class="col-md-6 mb-3">
                            <label for="monto_divisa" class="form-label">Monto en Divisas (USD) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="monto_divisa" id="monto_divisa" 
                                    class="form-control @error('monto_divisa') is-invalid @enderror" 
                                    value="{{ old('monto_divisa') }}" placeholder="0.00" required>
                            </div>
                            <small class="text-muted" id="saldoInfo">Saldo pendiente total: $ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</small>
                            @error('monto_divisa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Monto en Bolívares (calculado automáticamente) -->
                        <div class="col-md-6 mb-3">
                            <label for="monto_bs" class="form-label">Monto en Bolívares (Bs)</label>
                            <div class="input-group">
                                <span class="input-group-text">Bs</span>
                                <input type="number" step="0.01" name="monto_bs" id="monto_bs" 
                                    class="form-control" 
                                    value="{{ old('monto_bs', 0) }}">
                            </div>
                            <small class="text-muted">Se calcula automáticamente (Monto USD × Tasa)</small>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea name="descripcion" id="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror" 
                                    placeholder="Ej: Pago parcial de facturas..." required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Forma de Pago -->
                        <div class="col-md-4 mb-3">
                            <label for="forma_pago" class="form-label">Forma de Pago *</label>
                            <select name="forma_pago" id="forma_pago" class="form-select @error('forma_pago') is-invalid @enderror" required>
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
                        
                        <!-- Número de Operación (solo para transferencias) -->
                        <div class="col-md-4 mb-3" id="campo_numero_operacion" style="display: none;">
                            <label for="numero_operacion" class="form-label">Número de Operación</label>
                            <input type="text" name="numero_operacion" id="numero_operacion" class="form-control" 
                                value="{{ old('numero_operacion') }}" placeholder="Ej: 12345678">
                        </div>
                        
                        <!-- Comprobante -->
                        <div class="col-md-4 mb-3">
                            <label for="comprobante" class="form-label">Comprobante de Pago</label>
                            <input type="file" name="comprobante" id="comprobante" class="form-control @error('comprobante') is-invalid @enderror" 
                                accept="image/*,.pdf">
                            <small class="text-muted">Formatos: JPG, PNG, PDF (Max 5MB)</small>
                            @error('comprobante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success" id="btnGuardarPago">
                                <i class="bi bi-save me-1"></i> Registrar Pago
                            </button>
                            <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar
                            </a>
                        </div>
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
    .small-box {
        border-radius: 8px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }
    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    }
    .small-box .inner {
        padding: 10px 15px;
    }
    .small-box h3 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0 0 5px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
        margin-bottom: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 5px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(255,255,255,0.3);
        transition: transform 0.3s ease;
    }
    .small-box:hover .icon {
        transform: scale(1.05);
    }
    .table-dark {
        background-color: #343a40 !important;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,0.02);
    }

    .table-sm td, .table-sm th {
        padding: 0.4rem 0.5rem;
        vertical-align: middle;
    }

    .text-wrap {
        word-wrap: break-word;
        white-space: normal !important;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-responsive {
        scrollbar-width: thin;
    }

    /* Overlay para zoom */
    .image-zoom-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeInOverlay 0.3s ease-out;
    }

    .image-zoom-container {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .image-zoom-container img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        animation: zoomInImage 0.3s ease-out;
    }

    .image-zoom-close {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        background: rgba(0, 0, 0, 0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .image-zoom-close:hover {
        color: #ff6b6b;
        background: rgba(0, 0, 0, 0.7);
    }

    .image-description {
        color: white;
        text-align: center;
        margin-top: 20px;
        font-size: 1.1rem;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 20px;
        border-radius: 8px;
        max-width: 80%;
    }

    @keyframes fadeInOverlay {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes zoomInImage {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    /* Animaciones para la tabla de productos */
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }

    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush