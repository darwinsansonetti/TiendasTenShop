@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Listado Cierre Diario')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Cierres de Caja - {{ session('sucursal_nombre') }}</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Cierres de Caja</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

    
<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid"> 
        <!-- Tabs de navegación -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="cierre-tab" data-bs-toggle="tab" 
                                data-bs-target="#cierre" type="button" role="tab" aria-controls="cierre" 
                                aria-selected="true">
                            <i class="fas fa-calculator me-2"></i>Cierre Diario
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="gastos-tab" data-bs-toggle="tab" 
                                data-bs-target="#gastos" type="button" role="tab" aria-controls="gastos" 
                                aria-selected="false">
                            <i class="fas fa-receipt me-2"></i>Gastos Diarios
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <!-- TAB 1: CIERRE DIARIO -->
                    <div class="tab-pane fade show active" id="cierre" role="tabpanel" aria-labelledby="cierre-tab">
                        <form id="form-cierre-diario" method="POST" action="">
                            @csrf
                            <input type="hidden" name="cierre_diario_id" value="{{ $datosVista['cierre_id'] }}">
                            
                            <!-- Fila 1: Información Principal -->
                            <!-- Fila 1: Resumen Principal -->
                            <div class="row g-3 mb-4">
                                <!-- Información Básica -->
                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <div class="card h-100 border border-1 border-primary shadow-sm card-stat">
                                        <div class="card-header bg-primary border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <!-- Fecha -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Fecha</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input type="date" class="form-control" name="fecha" 
                                                        value="{{ $datosVista['fecha']['iso'] }}" required>
                                                </div>
                                            </div>
                                            
                                            <!-- Tasa BCV -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Tasa BCV (Bs/USD)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs</span>
                                                    <input type="number" class="form-control" name="tasa_cambio" step="0.01" min="0" 
                                                        value="{{ $datosVista['tasa_bcv'] }}" required readonly>
                                                </div>
                                            </div>
                                            
                                            <!-- Número Zeta -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Número Zeta</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                    <input type="text" class="form-control" name="numero_zeta" 
                                                        value="{{ $datosVista['cierre']->NumeroZeta ?? '' }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Total Ventas Sistema -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Ventas Sistema (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs</span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="venta_sistema"
                                                        value="{{ number_format($datosVista['cierre']->VentaSistema, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ventas Especiales -->
                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <div class="card h-100 border border-1 border-warning shadow-sm card-stat">
                                        <div class="card-header bg-warning border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Ventas Especiales</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <!-- Cashea -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Cashea (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="cashea_bs"
                                                        value="{{ number_format($datosVista['cierre']->CasheaBs, 2, ',', '.') }}">
                                                </div>
                                            </div>

                                            <!-- Biopago -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Biopago (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="biopago_bs"
                                                        value="{{ number_format($datosVista['cierre']->Biopago, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Zelle -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Zelle (USD)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">USD</span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="zelle_divisas" step="0.01" min="0"
                                                        value="{{ number_format($datosVista['cierre']->ZelleDivisas, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                               

                                <!-- Puntos de Venta -->
                                <div class="col-xl-4 col-lg-12">
                                    <div class="card h-100 border border-1 border-success shadow-sm card-stat">
                                        <div class="card-header bg-success border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Puntos de Venta</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                                            <div id="puntos-venta-container">
                                                @if(isset($datosVista['cierre']->pagosPuntoDeVenta) && $datosVista['cierre']->pagosPuntoDeVenta->count() > 0)
                                                    @foreach($datosVista['cierre']->pagosPuntoDeVenta as $index => $pago)
                                                        <div class="punto-venta-item mb-3 p-2 border rounded">
                                                            <!-- Fila superior: Logo y nombre -->
                                                            <div class="d-flex align-items-center mb-2">
                                                                <div class="flex-shrink-0">
                                                                    <img src="{{ asset('assets/img/bancos/' . $pago->puntoDeVenta->banco->Logo) }}" 
                                                                        alt="Logo" class="banco-logo-small me-2" 
                                                                        style="width: 35px; height: 35px; object-fit: contain;">
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.9rem;">
                                                                        {{ $pago->puntoDeVenta->banco->Nombre ?? 'Sin Banco' }}
                                                                    </h6>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Fila inferior: Input -->
                                                            <div>
                                                                <label class="form-label small text-muted mb-1">Monto (Bs)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text" style="min-width: 45px;">Bs</span>
                                                                    <input type="text" class="form-control amount-input text-end" 
                                                                        name="puntos_venta[{{ $index }}][monto]" 
                                                                        value="{{ number_format($pago->Monto, 2, ',', '.') }}">
                                                                    <input type="hidden" name="puntos_venta[{{ $index }}][id]" 
                                                                        value="{{ $pago->PagoPuntoDeVentaId }}">
                                                                    <input type="hidden" name="puntos_venta[{{ $index }}][punto_venta_id]" 
                                                                        value="{{ $pago->puntoDeVenta->PuntoDeVentaId }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-center py-5">
                                                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No hay puntos de venta configurados</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="hidden" class="form-control fw-bold text-purple" 
                                                        id="total_puntos_venta" readonly value="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 2: Métodos de Pago y Resumen -->
                            <div class="row g-3 mb-4">
                                <!-- Ventas en Bolívares -->
                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <div class="card h-100 border border-1 border-success shadow-sm card-stat">
                                        <div class="card-header bg-success border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Ventas en Bolívares</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <!-- Efectivo -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Efectivo (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="efectivo_bs" step="0.01" min="0" 
                                                        value="{{ number_format($datosVista['cierre']->EfectivoBs, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Transferencias -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Transferencias (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="transferencia_bs" step="0.01" min="0"
                                                        value="{{ number_format($datosVista['cierre']->TransferenciaBs, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Pagos Móviles -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Pagos Móviles (Bs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Bs </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="pago_movil_bs" step="0.01" min="0"
                                                        value="{{ number_format($datosVista['cierre']->PagoMovilBs, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ventas en Divisas -->
                                <div class="col-xl-4 col-lg-6 col-md-12">
                                    <div class="card h-100 border border-1 border-info shadow-sm card-stat">
                                        <div class="card-header bg-info border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Ventas en Divisas</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <!-- Efectivo USD -->
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Efectivo (USD)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">USD </span>
                                                    <input type="text" class="form-control amount-input" 
                                                        name="efectivo_divisas" step="0.01" min="0"
                                                        value="{{ number_format($datosVista['cierre']->EfectivoDivisas, 2, ',', '.') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen Total (NUEVA POSICIÓN - Fila 2) -->
                                <div class="col-xl-4 col-lg-12 col-md-12">
                                    <div class="card h-100 border border-1 border-success shadow-sm card-stat">
                                        <div class="card-header bg-success border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen Totales</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <!-- Total Ventas Bs -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label small text-muted mb-0">Total Ventas Bs</label>
                                                        <span class="badge bg-success bg-opacity-20 text-success small">Bs</span>
                                                    </div>
                                                    <input type="text" class="form-control fw-bold text-success text-end fs-5" 
                                                        id="total_ventas_bs" readonly value="0.00">
                                                </div>
                                                
                                                <!-- Total Ventas USD -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label small text-muted mb-0">Total Ventas USD</label>
                                                        <span class="badge bg-info bg-opacity-20 text-info small">USD</span>
                                                    </div>
                                                    <input type="text" class="form-control fw-bold text-info text-end fs-5" 
                                                        id="total_ventas_usd" readonly value="0.00">
                                                </div>
                                                
                                                <!-- Total General Bs -->
                                                <div class="pt-2 border-top">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label small text-muted mb-0">Total General (USD)</label>
                                                        <span class="badge bg-primary bg-opacity-20 text-primary small">Bs</span>
                                                    </div>
                                                    <input type="text" class="form-control fw-bold text-primary text-end fs-5" 
                                                        id="total_general_bs" readonly value="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 3: Acciones y Observaciones -->
                            <div class="row">
                                <div class="col-xl-8">
                                    <div class="card border border-2 shadow-sm">
                                        <div class="card-header bg-light border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Observaciones</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <textarea class="form-control" name="observacion" rows="3" 
                                                        placeholder="Ingrese observaciones importantes sobre el cierre de caja..."
                                                        style="resize: none;">{{ $datosVista['cierre']->Observacion ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-light border-0 py-3">
                                            <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h6>
                                        </div>
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-info" id="btn-limpiar">
                                                    <i class="fas fa-eraser me-2"></i>Limpiar Campos
                                                </button>
                                                <button type="submit" class="btn btn-primary" id="btn-guardar">
                                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                                </button>
                                                <button type="button" class="btn btn-success" id="btn-finalizar">
                                                    <i class="fas fa-check-circle me-2"></i>Finalizar Cierre
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- TAB 2: GASTOS DIARIOS -->
                    <div class="tab-pane fade" id="gastos" role="tabpanel" aria-labelledby="gastos-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Formulario de Gastos -->
                                <div class="card mb-4">
                                    <div class="card-header card-header-custom">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Nuevo Gasto Diario</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-gasto-diario">
                                            @csrf
                                            <input type="hidden" name="cierre_diario_id" value="{{ $datosVista['cierre_id'] }}">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="descripcion" 
                                                            placeholder="Ej: Compra de material de oficina" >
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto USD</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="text" class="form-control amount-input" name="monto_usd" 
                                                                 value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto BsF</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Bs</span>
                                                            <input type="text" class="form-control amount-input" name="monto_bsf" 
                                                                value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Forma de Pago <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="forma_pago" required>
                                                            <option value="">Seleccionar...</option>
                                                            <option value="0">Efectivo</option>
                                                            <option value="1">Cheque</option>
                                                            <option value="2">Depósito</option>
                                                            <option value="3">Transferencia</option>
                                                            <option value="4">Zelle</option>
                                                            <option value="5">Paypal</option>
                                                            <option value="6">Otro</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Observación</label>
                                                        <textarea class="form-control" name="observacion_gasto" 
                                                                rows="2" placeholder="Observaciones del gasto..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-limpiar-gasto">
                                                        <i class="fas fa-eraser me-2"></i>Limpiar
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-primary w-100" id="btn-guardar-gasto">
                                                        <i class="fas fa-save me-2"></i>Guardar Gasto
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Lista de Gastos Registrados -->
                                <div class="card">
                                    <div class="card-header card-header-custom">
                                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Gastos del Dia</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="tabla-gastos">
                                                <thead>
                                                    <tr>
                                                        <th>Descripción</th>
                                                        <th  class="text-center">USD</th>
                                                        <th  class="text-center">BsF</th>
                                                        <th class="text-center">Forma Pago</th>
                                                        <th class="text-center">Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-gastos">
                                                    <!-- Los gastos se cargarán aquí via AJAX -->
                                                    <tr id="sin-gastos">
                                                        <td colspan="5" class="text-center py-4 text-muted">
                                                            <i class="fas fa-receipt fa-2x mb-2"></i>
                                                            <p>No hay gastos registrados</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Convertimos la lista de gastos PHP a JSON para JS
    const gastosIniciales = @json($datosVista['gastos'] ?? []);
    
    document.addEventListener('DOMContentLoaded', function() {
        // ======================
        // CONFIGURACIÓN INICIAL
        // ======================
        
        // Formatear números con separadores de miles
        function formatNumber(number, decimals = 2) {
            if (isNaN(number) || number === null || number === '') return '';
            return new Intl.NumberFormat('es-VE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }
        
        // Parsear número con formato español a float
        function parseNumber(str) {
            if (!str || str === '') return 0;
            // Remover todos los puntos (separadores de miles) y cambiar coma por punto
            return parseFloat(
                str.toString()
                    .replace(/\./g, '')  // Eliminar puntos de miles
                    .replace(',', '.')   // Cambiar coma decimal por punto
            ) || 0;
        }
        
        // Limpiar valor para cálculo (sin formato)
        function cleanNumber(str) {
            if (!str || str === '') return '';
            return str.toString()
                .replace(/\./g, '')
                .replace(',', '.');
        }
        
        // Formatear en tiempo real mientras se escribe
        function formatRealTime(input) {
            // Obtener cursor position
            const cursorPos = input.selectionStart;
            const originalLength = input.value.length;
            
            // Limpiar el valor actual
            let cleanValue = input.value
                .replace(/[^0-9,]/g, '')  // Solo números y coma
                .replace(/,/g, '.');      // Cambiar coma por punto temporalmente
            
            // Separar parte entera y decimal
            let parts = cleanValue.split('.');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? '.' + parts[1] : '';
            
            // Limitar decimales a 2
            if (decimalPart.length > 3) {
                decimalPart = decimalPart.substring(0, 3);
            }
            
            // Formatear parte entera con puntos de miles
            if (integerPart) {
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            
            // Unir partes y cambiar punto decimal por coma
            let formattedValue = integerPart + decimalPart.replace('.', ',');
            
            // Actualizar valor
            input.value = formattedValue;
            
            // Restaurar cursor position
            const newLength = formattedValue.length;
            const cursorOffset = newLength - originalLength;
            input.setSelectionRange(cursorPos + cursorOffset, cursorPos + cursorOffset);
            
            return formattedValue;
        }
        
        // ======================
        // MANEJO DE INPUTS CON FORMATO
        // ======================
        
        document.querySelectorAll('.amount-input').forEach(input => {
            // Al enfocar, mostrar valor limpio para edición
            input.addEventListener('focus', function() {
                if (!this.readOnly) {
                    const value = parseNumber(this.value);
                    if (!isNaN(value)) {
                        this.value = value.toFixed(2).replace('.', ',');
                        // Mover cursor al final
                        this.setSelectionRange(this.value.length, this.value.length);
                    }
                }
            });
            
            // Al perder foco, aplicar formato completo
            input.addEventListener('blur', function() {
                if (!this.readOnly) {
                    const value = parseNumber(this.value);
                    if (!isNaN(value)) {
                        this.value = formatNumber(value);
                        calcularTotales();
                    }
                }
            });
            
            // Formato en tiempo real mientras se escribe
            input.addEventListener('input', function() {
                if (!this.readOnly) {
                    formatRealTime(this);
                    calcularTotales();
                }
            });
            
            // Permitir solo números, coma y teclas de control
            input.addEventListener('keydown', function(e) {
                // Permitir: números, coma, punto (se convertirá a coma), teclas de control
                const allowedKeys = [
                    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                    ',', '.', 
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Tab', 'Home', 'End'
                ];
                
                // Si es Ctrl/Cmd + (A, C, V, X, Z) permitir
                if (e.ctrlKey || e.metaKey) {
                    if (['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) {
                        return true;
                    }
                }
                
                // Bloquear otras teclas
                if (!allowedKeys.includes(e.key) && 
                    !(e.ctrlKey || e.metaKey) && 
                    e.key.length === 1) {
                    e.preventDefault();
                }
                
                // Convertir punto a coma
                if (e.key === '.') {
                    e.preventDefault();
                    // Insertar coma en la posición actual
                    const cursorPos = this.selectionStart;
                    const currentValue = this.value;
                    this.value = currentValue.substring(0, cursorPos) + ',' + currentValue.substring(cursorPos);
                    this.setSelectionRange(cursorPos + 1, cursorPos + 1);
                    calcularTotales();
                }
            });
            
            // Prevenir pegar texto no numérico
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const cleaned = pastedText.replace(/[^0-9,\.]/g, '').replace('.', ',');
                
                // Insertar texto limpio en la posición del cursor
                const cursorPos = this.selectionStart;
                const currentValue = this.value;
                this.value = currentValue.substring(0, cursorPos) + cleaned + currentValue.substring(this.selectionEnd);
                this.setSelectionRange(cursorPos + cleaned.length, cursorPos + cleaned.length);
                
                formatRealTime(this);
                calcularTotales();
            });
        });
        
        // ======================
        // CÁLCULO DE TOTALES (sin cambios)
        // ======================
        
        function calcularTotales() {
            // Obtener tasa de cambio
            const tasaCambio = parseFloat(document.querySelector('input[name="tasa_cambio"]').value) || 0;
            
            // ========== VENTAS EN BOLÍVARES ==========
            const efectivoBs = parseNumber(document.querySelector('input[name="efectivo_bs"]').value) || 0;
            const transferenciaBs = parseNumber(document.querySelector('input[name="transferencia_bs"]').value) || 0;
            const pagoMovilBs = parseNumber(document.querySelector('input[name="pago_movil_bs"]').value) || 0;
            const casheaBs = parseNumber(document.querySelector('input[name="cashea_bs"]').value) || 0;
            const biopagoBs = parseNumber(document.querySelector('input[name="biopago_bs"]').value) || 0;
            
            // ========== PUNTOS DE VENTA ==========
            let totalPuntosVenta = 0;
            document.querySelectorAll('input[name^="puntos_venta"][name$="[monto]"]').forEach(input => {
                totalPuntosVenta += parseNumber(input.value) || 0;
            });
            document.getElementById('total_puntos_venta').value = formatNumber(totalPuntosVenta);
            
            // Subtotal Bs (sin Cashea)
            const subtotalBs = efectivoBs + transferenciaBs + pagoMovilBs + totalPuntosVenta;
            
            // Total Ventas Bs (con Cashea)
            const totalVentasBs = subtotalBs + casheaBs + biopagoBs;
            document.getElementById('total_ventas_bs').value = formatNumber(totalVentasBs);
            
            // ========== VENTAS EN DIVISAS ==========
            const efectivoUsd = parseNumber(document.querySelector('input[name="efectivo_divisas"]').value) || 0;
            const zelleUsd = parseNumber(document.querySelector('input[name="zelle_divisas"]').value) || 0;
            
            // Total Ventas USD
            const totalVentasUsd = efectivoUsd + zelleUsd;
            document.getElementById('total_ventas_usd').value = formatNumber(totalVentasUsd);
            
            // ========== TOTAL GENERAL ==========
            // Ventas Sistema (ya en Bs)
            const ventaSistema = parseNumber(document.querySelector('input[name="venta_sistema"]').value) || 0;
            
            // Total General en Bs
            const totalGeneralBs = (totalVentasBs / tasaCambio) + totalVentasUsd;
            document.getElementById('total_general_bs').value = formatNumber(totalGeneralBs);
            
            // ========== VALIDACIÓN ==========
            if (ventaSistema > 0 && Math.abs(totalGeneralBs - ventaSistema) > 0.01) {
                const diferencia = totalGeneralBs - ventaSistema;
                const porcentaje = ((diferencia / ventaSistema) * 100).toFixed(2);
                
                if (Math.abs(diferencia) > 0.01) {
                    console.log(`Diferencia detectada: ${formatNumber(diferencia)} Bs (${porcentaje}%)`);
                    // Actualizar badge de diferencia si existe
                    const diferenciaBadge = document.getElementById('diferencia-badge');
                    if (diferenciaBadge) {
                        diferenciaBadge.textContent = formatNumber(diferencia) + ' Bs';
                        diferenciaBadge.className = diferencia > 0 ? 
                            'badge bg-success bg-opacity-20 text-success small' : 
                            'badge bg-danger bg-opacity-20 text-danger small';
                    }
                }
            }
        }
        
        // Calcular al cargar
        calcularTotales();
        
        // Recalcular al cambiar cualquier input
        document.querySelectorAll('input[name], input[name^="puntos_venta"]').forEach(input => {
            input.addEventListener('input', calcularTotales);
        });
        
        // ======================
        // BOTONES DE ACCIÓN (sin cambios)
        // ======================
        
        document.getElementById('btn-limpiar').addEventListener('click', function() {
            if (confirm('¿Está seguro de limpiar todos los campos? Los datos no guardados se perderán.')) {
                document.querySelectorAll('.amount-input').forEach(input => {
                    if (!input.readOnly) {
                        input.value = '';
                    }
                });
                document.querySelector('textarea[name="observacion"]').value = '';
                calcularTotales();
                showToast('Campos limpiados correctamente', 'success');
            }
        });
        
        // ======================
        // FORMULARIO (sin cambios)
        // ======================
        
        document.getElementById('form-cierre-diario').addEventListener('submit', function(e) {
            e.preventDefault();

            // Verificar si estamos en modo finalizar
            const esFinalizar = window.esFinalizar || false;

            // Si es finalizar, agregar campo hidden al formulario
            if (esFinalizar) {
                // Crear o actualizar campo hidden
                let finalizarField = document.querySelector('input[name="es_finalizar"]');
                if (!finalizarField) {
                    finalizarField = document.createElement('input');
                    finalizarField.type = 'hidden';
                    finalizarField.name = 'es_finalizar';
                    this.appendChild(finalizarField);
                }
                finalizarField.value = '3';
            }



            
            // Limpiar formatos antes de enviar y validar
            let isValid = true;
            const errorMessages = [];
            
            // Validar campos obligatorios
            const fecha = document.querySelector('input[name="fecha"]').value;
            const tasa = document.querySelector('input[name="tasa_cambio"]').value;
            
            if (!fecha) {
                errorMessages.push('La fecha es obligatoria');
                isValid = false;
                highlightError(document.querySelector('input[name="fecha"]'));
            } else {
                removeHighlight(document.querySelector('input[name="fecha"]'));
            }
            
            if (!tasa || parseFloat(tasa) <= 0) {
                errorMessages.push('La tasa de cambio debe ser mayor a 0');
                isValid = false;
                highlightError(document.querySelector('input[name="tasa_cambio"]'));
            } else {
                removeHighlight(document.querySelector('input[name="tasa_cambio"]'));
            }
            
            // Función para limpiar formato español a decimal
            function limpiarNumeroParaEnvio(valor) {
                if (!valor || valor === '') return '0.00';
                
                // Remover todos los puntos (separadores de miles) y cambiar coma por punto
                const limpio = valor.toString()
                    .replace(/\./g, '')  // Eliminar puntos de miles
                    .replace(',', '.');  // Cambiar coma decimal por punto
                
                // Asegurar que tenga 2 decimales
                const num = parseFloat(limpio);
                if (isNaN(num)) return '0.00';
                
                return num.toFixed(2);
            }

            // Función específica para puntos de venta que maneja múltiples formatos
            function limpiarNumeroParaEnvioPuntosVenta(valor) {
                if (!valor || valor === '') return '0.00';
                
                console.log('limpiarNumeroParaEnvioPuntosVenta - INPUT:', `"${valor}"`);
                
                // Convertir a string
                const strValor = valor.toString().trim();
                
                // Caso 1: Ya está en formato inglés correcto (ej: "1000.50")
                if (/^\d+\.\d{2}$/.test(strValor)) {
                    console.log('Caso 1: Ya en formato inglés con 2 decimales');
                    return strValor;
                }
                
                // Caso 2: Formato español con separadores de miles (ej: "1.000,50", "100.050,00")
                if (strValor.includes(',') && strValor.includes('.')) {
                    console.log('Caso 2: Formato español con separadores de miles');
                    const limpio = strValor
                        .replace(/\./g, '')      // Eliminar puntos de miles
                        .replace(',', '.');      // Cambiar coma por punto decimal
                    
                    const num = parseFloat(limpio);
                    if (isNaN(num)) return '0.00';
                    
                    const resultado = num.toFixed(2);
                    console.log('Resultado caso 2:', resultado);
                    return resultado;
                }
                
                // Caso 3: Solo coma decimal (ej: "1000,50")
                if (strValor.includes(',') && !strValor.includes('.')) {
                    console.log('Caso 3: Solo coma decimal');
                    const limpio = strValor.replace(',', '.');
                    const num = parseFloat(limpio);
                    if (isNaN(num)) return '0.00';
                    
                    const resultado = num.toFixed(2);
                    console.log('Resultado caso 3:', resultado);
                    return resultado;
                }
                
                // Caso 4: Solo punto decimal pero puede tener decimales incorrectos (ej: "1000.5", "100050.00")
                if (strValor.includes('.') && !strValor.includes(',')) {
                    console.log('Caso 4: Punto decimal');
                    const partes = strValor.split('.');
                    
                    // Si tiene más de 2 partes, es formato de miles (ej: "100.050.00")
                    if (partes.length > 2) {
                        console.log('Caso 4a: Posible formato de miles con punto');
                        // Unir todas las partes excepto la última, luego agregar decimales
                        const parteEntera = partes.slice(0, -1).join('');
                        const parteDecimal = partes[partes.length - 1];
                        
                        // Asegurar que la parte decimal tenga 2 dígitos
                        const decimales = parteDecimal.padEnd(2, '0').substring(0, 2);
                        const resultado = `${parteEntera}.${decimales}`;
                        
                        console.log('Resultado caso 4a:', resultado);
                        return resultado;
                    }
                    
                    // Formato normal con punto decimal
                    const num = parseFloat(strValor);
                    if (isNaN(num)) return '0.00';
                    
                    const resultado = num.toFixed(2);
                    console.log('Resultado caso 4b:', resultado);
                    return resultado;
                }
                
                // Caso 5: Solo números enteros (ej: "1000")
                const num = parseFloat(strValor);
                if (isNaN(num)) return '0.00';
                
                console.log('Caso 5: Número entero');
                const resultado = num.toFixed(2);
                console.log('Resultado caso 5:', resultado);
                return resultado;
            }
            
            // Validar y limpiar todos los campos amount-input
            document.querySelectorAll('.amount-input').forEach(input => {
                if (!input.readOnly) {
                    const rawValue = input.value.trim();
                    const parsedValue = parseNumber(rawValue);
                    
                    // Si el campo está vacío, asignar 0
                    if (rawValue === '') {
                        input.value = '0.00';
                        // Crear campo hidden con valor limpio en formato inglés
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name; // Usar el mismo nombre, sobreescribirá el valor
                        hiddenInput.value = '0.00';
                        this.appendChild(hiddenInput);
                        removeHighlight(input);
                    } 
                    // Si tiene valor, validar que sea número válido y no negativo
                    else if (isNaN(parsedValue)) {
                        errorMessages.push(`El valor en "${input.previousElementSibling?.textContent || input.placeholder || 'campo'}" no es válido`);
                        isValid = false;
                        highlightError(input);
                    } 
                    else if (parsedValue < 0) {
                        errorMessages.push(`El valor en "${input.previousElementSibling?.textContent || input.placeholder || 'campo'}" no puede ser negativo`);
                        isValid = false;
                        highlightError(input);
                    }
                    else {
                        // Crear campo hidden con valor limpio en formato inglés
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name; // Usar el mismo nombre
                        hiddenInput.value = limpiarNumeroParaEnvio(rawValue);
                        this.appendChild(hiddenInput);
                        removeHighlight(input);
                    }
                }
            });
            
            // Validar y limpiar puntos de venta
            const puntosVentaInputs = document.querySelectorAll('input[name^="puntos_venta"][name$="[monto]"]');
            puntosVentaInputs.forEach((input, index) => {
                const rawValue = input.value.trim();
                
                if (rawValue === '') {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = '0.00';
                    this.appendChild(hiddenInput);
                    removeHighlight(input);
                } 
                else {
                    // Usar la NUEVA función específica
                    const cleanedValue = limpiarNumeroParaEnvioPuntosVenta(rawValue);
                    
                    const numericValue = parseFloat(cleanedValue);
                    
                    if (isNaN(numericValue)) {
                        errorMessages.push(`El monto del punto de venta "${rawValue}" no es válido`);
                        isValid = false;
                        highlightError(input);
                    } 
                    else if (numericValue < 0) {
                        errorMessages.push('Los montos de puntos de venta no pueden ser negativos');
                        isValid = false;
                        highlightError(input);
                    }
                    else {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        
                        // Usar el valor ya formateado
                        hiddenInput.value = cleanedValue;
                        
                        this.appendChild(hiddenInput);
                        removeHighlight(input);
                    }
                }
            });
            
            // Si hay errores, mostrarlos y detener el envío
            if (!isValid) {
                showToast(errorMessages.join('<br>'), 'error');
                
                // Hacer scroll al primer error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                
                return;
            }
            
            // Preparar datos para envío
            const formData = new FormData(this);
            const cierreId = document.querySelector('input[name="cierre_diario_id"]').value;
            
            // Mostrar loading
            const btnGuardar = document.getElementById('btn-guardar');
            const originalText = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            btnGuardar.disabled = true;
            
            // Enviar datos via AJAX
            fetch("{{ route('cierres-diarios.actualizar', ['cierre' => $datosVista['cierre_id']]) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(async response => {
                // Intentar parsear la respuesta como JSON primero
                let data;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    // Si no es JSON, obtener el texto de la respuesta
                    const text = await response.text();
                    console.error('Respuesta no es JSON:', text.substring(0, 500));
                    throw new Error(`Error del servidor (${response.status}): ${text.substring(0, 200)}`);
                }
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${data.message || 'Error desconocido'}`);
                }
                
                return data;
            })
            .then(data => {
                btnGuardar.innerHTML = originalText;
                btnGuardar.disabled = false;
                
                if (data.success) {
                    // showToast(data.message || 'Cierre guardado correctamente', 'success');
                    
                    // // Redirigir después de 2 segundos
                    // setTimeout(() => {
                    //     // window.history.back();
                    //     window.location.href = document.referrer + '?refresh=' + new Date().getTime();
                    // }, 2000);

                    if (esFinalizar) {
                        showToast('¡Cierre finalizado correctamente!', 'success');                        
                        
                    } else {
                        showToast('Cambios guardados correctamente', 'success');
                    }

                    // Redirigir a la lista
                    setTimeout(() => {
                        window.location.href = document.referrer + '?refresh=' + new Date().getTime();
                    }, 2000);
                    
                } else {
                    showToast(data.message || 'Error al guardar el cierre', 'error');
                    
                    if (data.errors) {
                        const allErrors = Object.values(data.errors).flat();
                        showToast(allErrors.join('<br>'), 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                console.error('Stack trace:', error.stack);
                
                btnGuardar.innerHTML = originalText;
                btnGuardar.disabled = false;
                
                // Mostrar mensaje más detallado
                const errorMsg = error.message.includes('Error del servidor') 
                    ? error.message 
                    : 'Error de conexión. Intente nuevamente.';
                
                showToast(errorMsg, 'error');
            })
            .finally(() => {
                // Limpiar el flag
                window.esFinalizar = false;

                // Eliminar campo hidden si existe
                const finalizarField = document.querySelector('input[name="es_finalizar"]');
                if (finalizarField) {
                    finalizarField.remove();
                }

                // Limpiar campos hidden creados dinámicamente
                document.querySelectorAll('input[name$="_clean"]').forEach(input => {
                    input.remove();
                });
            });
        });

        // Funciones auxiliares para resaltar errores
        function highlightError(element) {
            element.classList.add('is-invalid');
            element.classList.remove('is-valid');
            
            // Agregar feedback visual si no existe
            if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Por favor, corrige este campo';
                element.parentNode.appendChild(feedback);
            }
        }

        function removeHighlight(element) {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
            
            // Remover feedback si existe
            const feedback = element.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.remove();
            }
        }

        
        // ======================
        // FUNCIONALIDADES EXTRAS (sin cambios)
        // ======================
        
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Función para mostrar notificaciones
        function showToast(message, type = 'info') {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }
            
            const toastId = 'toast-' + Date.now();
            const bgColor = type === 'success' ? 'bg-success' : 
                        type === 'error' ? 'bg-danger' : 
                        type === 'warning' ? 'bg-warning' : 'bg-info';
            
            const toastHTML = `
                <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }
        
        // ======================
        // SHORTCUTS DE TECLADO
        // ======================
        
        document.addEventListener('keydown', function(e) {
            // Ctrl + S para guardar
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('btn-guardar').click();
            }
            
            // Ctrl + C para calcular
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                e.preventDefault();
                calcularTotales();
                showToast('Totales recalculados', 'info');
            }
            
            // Ctrl + L para limpiar
            if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                e.preventDefault();
                document.getElementById('btn-limpiar').click();
            }
        });
        
        // ======================
        // INICIALIZACIÓN FINAL
        // ======================
        
        console.log('Sistema de Cierre Diario cargado correctamente');
    });

    // Funciones auxiliares para resaltar errores
    function highlightErrorEnd(element) {
        element.classList.add('is-invalid');
        element.classList.remove('is-valid');
        
        // Agregar feedback visual si no existe
        if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Por favor, corrige este campo';
            element.parentNode.appendChild(feedback);
        }
    }
    
    // Evento para el botón Finalizar
    document.getElementById('btn-finalizar').addEventListener('click', async function() {

        const ventasSistema = document.querySelector('input[name="venta_sistema"]').value;
        const ventasingresadas = document.getElementById('total_ventas_bs').value;

        if (!ventasSistema || parseFloat(ventasSistema) <= 0) {
            showToast('Monto invalido para las Ventas en Sistema', 'success');
            highlightErrorEnd(document.querySelector('input[name="venta_sistema"]'));
        }else{
            if (!ventasingresadas || parseFloat(ventasingresadas) <= 0) {
                showToast('Debe ingresar ventas por algun metodo de pago.', 'success');
            }else{
                // Mostrar confirmación
                const confirmacion = await Swal.fire({
                    title: '¿Finalizar Cierre?',
                    text: 'Esta acción marcará el cierre como completado. ¿Deseas continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, finalizar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirmacion.isConfirmed) {
                    // 1. Crear un flag global para indicar que es finalizar
                    window.esFinalizar = true;
                    
                    // 2. Crear y disparar un evento submit personalizado
                    const form = document.getElementById('form-cierre-diario');
                    const submitEvent = new Event('submit', {
                        bubbles: true,
                        cancelable: true
                    });
                    
                    // 3. Disparar el evento (esto ejecutará tu event listener existente)
                    form.dispatchEvent(submitEvent);
                }
            }    
        }        
    });



    //-------------------------------------//
    // Gastos Diarios
    //-------------------------------------//

    // Manejar el formulario de gastos
    // document.getElementById('form-gasto-diario').addEventListener('submit', function(e) {
    //     e.preventDefault();
        
    //     // Validaciones básicas
    //     const descripcion = this.querySelector('[name="descripcion"]').value.trim();
    //     const formaPago = this.querySelector('[name="forma_pago"]').value;
        
    //     if (!descripcion) {
    //         showToast('La descripción es obligatoria', 'error');
    //         this.querySelector('[name="descripcion"]').focus();
    //         return;
    //     }
        
    //     if (!formaPago) {
    //         showToast('La forma de pago es obligatoria', 'error');
    //         this.querySelector('[name="forma_pago"]').focus();
    //         return;
    //     }
        
    //     // Preparar datos
    //     const formData = new FormData(this);
    //     const cierreId = "{{ $datosVista['cierre_id'] }}";
        
    //     // Mostrar loading
    //     const btnGuardar = document.getElementById('btn-guardar-gasto');
    //     const originalText = btnGuardar.innerHTML;
    //     btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
    //     btnGuardar.disabled = true;
        
    //     // Enviar via AJAX
    //     fetch("{{ route('gastos-diarios.guardar') }}", {
    //         method: "POST",
    //         headers: {
    //             "X-CSRF-TOKEN": "{{ csrf_token() }}",
    //             "X-Requested-With": "XMLHttpRequest",
    //             "Accept": "application/json"
    //         },
    //         body: formData
    //     })
    //     .then(async response => {
    //         const data = await response.json();
            
    //         if (!response.ok) {
    //             throw new Error(data.message || 'Error del servidor');
    //         }
            
    //         return data;
    //     })
    //     .then(data => {
    //         if (data.success) {
    //             showToast('Gasto guardado correctamente', 'success');
                
    //             // Limpiar formulario
    //             this.reset();
    //             this.querySelector('[name="monto_usd"]').value = 0;
    //             this.querySelector('[name="monto_bsf"]').value = 0;
                
    //             // Recargar lista de gastos
    //             // cargarGastos(cierreId);

    //             actualizarTablaGastos(data.gastos);
    //             // actualizarTotalesGastos(data.totales);
    //         } else {
    //             showToast(data.message || 'Error al guardar el gasto', 'error');
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //         showToast(error.message || 'Error de conexión', 'error');
    //     })
    //     .finally(() => {
    //         // Restaurar botón
    //         btnGuardar.innerHTML = originalText;
    //         btnGuardar.disabled = false;
    //     });
    // });

    document.getElementById('form-gasto-diario').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const descripcion = this.querySelector('[name="descripcion"]').value.trim();
        const formaPago = this.querySelector('[name="forma_pago"]').value;
        
        if (!descripcion) {
            showToast('La descripción es obligatoria', 'error');
            this.querySelector('[name="descripcion"]').focus();
            return;
        }
        
        if (!formaPago) {
            showToast('La forma de pago es obligatoria', 'error');
            this.querySelector('[name="forma_pago"]').focus();
            return;
        }

        // Limpiar montos antes de enviar
        const montoUsdInput = this.querySelector('[name="monto_usd"]');
        const montoBsfInput = this.querySelector('[name="monto_bsf"]');

        // Creamos hidden con valor limpio
        [montoUsdInput, montoBsfInput].forEach(input => {
            const cleaned = limpiarNumeroParaGastos(input.value);
            let hidden = this.querySelector(`input[name="${input.name}-hidden"]`);
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = input.name; // este es el que se enviará
                hidden.classList.add('hidden-amount-input');
                this.appendChild(hidden);
            }
            hidden.value = cleaned;
        });

        // Preparar FormData usando los hidden limpios
        const formData = new FormData(this);
        
        // Mostrar loading
        const btnGuardar = document.getElementById('btn-guardar-gasto');
        const originalText = btnGuardar.innerHTML;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
        btnGuardar.disabled = true;
        
        fetch("{{ route('gastos-diarios.guardar') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Error del servidor');
            return data;
        })
        .then(data => {
            if (data.success) {
                showToast('Gasto guardado correctamente', 'success');

                // Limpiar formulario
                this.reset();
                this.querySelector('[name="monto_usd"]').value = 0;
                this.querySelector('[name="monto_bsf"]').value = 0;

                actualizarTablaGastos(data.gastos);
            } else {
                showToast(data.message || 'Error al guardar el gasto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Error de conexión', 'error');
        })
        .finally(() => {
            btnGuardar.innerHTML = originalText;
            btnGuardar.disabled = false;
        });
    });

    function limpiarNumeroParaGastos(valor) {
        if (!valor || valor === '') return '0.00';
        
        console.log('limpiarNumeroParaGastos - INPUT:', `"${valor}"`);
        
        // Convertir a string
        const strValor = valor.toString().trim();
        
        // Caso 1: Ya está en formato inglés correcto (ej: "1000.50")
        if (/^\d+\.\d{2}$/.test(strValor)) {
            console.log('Caso 1: Ya en formato inglés con 2 decimales');
            return strValor;
        }
        
        // Caso 2: Formato español con separadores de miles (ej: "1.000,50", "100.050,00")
        if (strValor.includes(',') && strValor.includes('.')) {
            console.log('Caso 2: Formato español con separadores de miles');
            const limpio = strValor
                .replace(/\./g, '')      // Eliminar puntos de miles
                .replace(',', '.');      // Cambiar coma por punto decimal
            
            const num = parseFloat(limpio);
            if (isNaN(num)) return '0.00';
            
            const resultado = num.toFixed(2);
            console.log('Resultado caso 2:', resultado);
            return resultado;
        }
        
        // Caso 3: Solo coma decimal (ej: "1000,50")
        if (strValor.includes(',') && !strValor.includes('.')) {
            console.log('Caso 3: Solo coma decimal');
            const limpio = strValor.replace(',', '.');
            const num = parseFloat(limpio);
            if (isNaN(num)) return '0.00';
            
            const resultado = num.toFixed(2);
            console.log('Resultado caso 3:', resultado);
            return resultado;
        }
        
        // Caso 4: Solo punto decimal pero puede tener decimales incorrectos (ej: "1000.5", "100050.00")
        if (strValor.includes('.') && !strValor.includes(',')) {
            console.log('Caso 4: Punto decimal');
            const partes = strValor.split('.');
            
            // Si tiene más de 2 partes, es formato de miles (ej: "100.050.00")
            if (partes.length > 2) {
                console.log('Caso 4a: Posible formato de miles con punto');
                // Unir todas las partes excepto la última, luego agregar decimales
                const parteEntera = partes.slice(0, -1).join('');
                const parteDecimal = partes[partes.length - 1];
                
                // Asegurar que la parte decimal tenga 2 dígitos
                const decimales = parteDecimal.padEnd(2, '0').substring(0, 2);
                const resultado = `${parteEntera}.${decimales}`;
                
                console.log('Resultado caso 4a:', resultado);
                return resultado;
            }
            
            // Formato normal con punto decimal
            const num = parseFloat(strValor);
            if (isNaN(num)) return '0.00';
            
            const resultado = num.toFixed(2);
            console.log('Resultado caso 4b:', resultado);
            return resultado;
        }
        
        // Caso 5: Solo números enteros (ej: "1000")
        const num = parseFloat(strValor);
        if (isNaN(num)) return '0.00';
        
        console.log('Caso 5: Número entero');
        const resultado = num.toFixed(2);
        console.log('Resultado caso 5:', resultado);
        return resultado;
    }

    // Botón limpiar gasto
    document.getElementById('btn-limpiar-gasto').addEventListener('click', function() {
        const form = document.getElementById('form-gasto-diario');
        form.reset();
        form.querySelector('[name="monto_usd"]').value = 0;
        form.querySelector('[name="monto_bsf"]').value = 0;
        form.querySelector('[name="descripcion"]').focus();
    });

    // Actualizar tabla de gastos
    function actualizarTablaGastos(gastos) {
        const tbody = document.getElementById('tbody-gastos');
        const totalUSD = document.getElementById('total-usd');
        const totalBSF = document.getElementById('total-bsf');

        // Si no hay gastos, mostrar mensaje "sin gastos"
        if (!gastos || gastos.length === 0) {
            tbody.innerHTML = `
                <tr id="sin-gastos">
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <p>No hay gastos registrados</p>
                    </td>
                </tr>
            `;
            totalUSD.textContent = '$0.00';
            totalBSF.textContent = 'Bs 0.00';
            return;
        }

        // Generar filas
        let html = '';
        let sumaUSD = 0;
        let sumaBSF = 0;

        gastos.forEach(gasto => {
            const formaPagoTexto = obtenerTextoFormaPago(gasto.forma_pago);

            html += `
                <tr>
                    <td>${gasto.descripcion}</td>
                    <td  class="text-center">$${parseFloat(gasto.monto_usd || 0).toFixed(2)}</td>
                    <td  class="text-center">Bs ${parseFloat(gasto.monto_bsf || 0).toFixed(2)}</td>
                    <td class="text-center">${formaPagoTexto}</td>
                    <td class="text-center">${gasto.observacion || ''}</td>
                </tr>
            `;

            sumaUSD += parseFloat(gasto.monto_usd || 0);
            sumaBSF += parseFloat(gasto.monto_bsf || 0);
        });

        tbody.innerHTML = html;

        // Actualizar totales
        totalUSD.textContent = `$${sumaUSD.toFixed(2)}`;
        totalBSF.textContent = `Bs ${sumaBSF.toFixed(2)}`;
    }

    // Eliminar gasto
    function eliminarGasto(gastoId) {
        Swal.fire({
            title: '¿Eliminar gasto?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/gastos-diarios/eliminar/${gastoId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Gasto eliminado correctamente', 'success');
                        
                        // Remover fila
                        const fila = document.getElementById(`gasto-${gastoId}`);
                        if (fila) fila.remove();
                        
                        // Actualizar totales
                        const cierreId = "{{ $datosVista['cierre_id'] }}";
                        cargarGastos(cierreId);
                    } else {
                        showToast(data.message || 'Error al eliminar', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error de conexión', 'error');
                });
            }
        });
    }

    // Helper para forma de pago
    function obtenerTextoFormaPago(valor) {
        const formas = {
            0: 'Efectivo',
            1: 'Cheque',
            2: 'Depósito',
            3: 'Transferencia',
            4: 'Zelle',
            5: 'Paypal',
            6: 'Otro'
        };
        return formas[valor] || 'Desconocido';
    }

    // Cargar gastos al abrir el tab
    document.getElementById('gastos-tab').addEventListener('click', function() {       

        //Cargar Gastos si se tienen
        actualizarTablaGastos(gastosIniciales);
    });

    ////////////////////////////////
    // CALCULO DE INPUT DEPENDIENTES
    ////////////////////////////////

    const tasaBCV = parseFloat("{{ $datosVista['tasa_bcv'] }}");

    // Tomar referencias a los inputs
    const inputUSD = document.querySelector('input[name="monto_usd"]');
    const inputBSF = document.querySelector('input[name="monto_bsf"]');

    // Evitar loops infinitos
    let isUpdating = false;

    // Función para limpiar y convertir string a número
    function parseAmountDependientes(valor) {
        if (!valor) return 0;
        const limpio = valor.toString().replace(/\./g,'').replace(',','.');
        const num = parseFloat(limpio);
        return isNaN(num) ? 0 : num;
    }

    // Escucha cuando escriben en USD
    inputUSD.addEventListener('input', () => {
        if (isUpdating) return;
        isUpdating = true;

        const usd = parseAmountDependientes(inputUSD.value);
        const bs = usd * tasaBCV;

        // Actualizar BSF con formato español
        inputBSF.value = bs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        isUpdating = false;
    });

    // Escucha cuando escriben en BsF
    inputBSF.addEventListener('input', () => {
        if (isUpdating) return;
        isUpdating = true;

        const bs = parseAmountDependientes(inputBSF.value);
        const usd = bs / tasaBCV;

        // Actualizar USD con formato español
        inputUSD.value = usd.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        isUpdating = false;
    });


</script>

<style>
    /* Estilos personalizados para Cierre de Caja */

    /* Colores personalizados */
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    /* Tarjetas de estadísticas */
    .card-stat {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.1); /* Sin !important */
    }


    .card-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }

    .card-stat .card-header {
        border-bottom: 2px solid transparent;
    }

    /* Métodos de pago */
    .payment-method-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        height: 100%;
    }

    .payment-method-card:hover {
        background: #fff;
        border-color: #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .payment-icon {
        width: 40px;
        height: 40px;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .payment-icon i {
        font-size: 1.2rem;
    }

    /* Puntos de venta */
    /* Estilos para puntos de venta */
    .punto-venta-item {
        background: #fff;
        border-left: 3px solid #6f42c1 !important;
        transition: all 0.2s ease;
    }

    .punto-venta-item:hover {
        background: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .banco-logo-small {
        background: white;
        border-radius: 6px;
        padding: 4px;
        border: 1px solid #dee2e6;
    }

    /* Asegurar tamaño consistente de inputs */
    .punto-venta-item .input-group {
        width: 100%;
        min-width: 180px;
    }

    .punto-venta-item .form-control {
        min-height: 32px;
        text-align: right;
        font-weight: 500;
    }

    .punto-venta-item .input-group-text {
        min-width: 45px;
        justify-content: center;
        font-weight: 500;
        background-color: #f8f9fa;
    }

    /* Centrado vertical para nombres de bancos */
    .punto-venta-item .align-items-center {
        min-height: 60px; /* Altura mínima consistente */
    }

    /* Para nombres largos */
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .punto-venta-item .d-flex {
            flex-wrap: wrap;
        }
        
        .punto-venta-item .flex-grow-1 {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .punto-venta-item .flex-shrink-0.ms-3 {
            width: 100%;
            margin-left: 0 !important;
        }
        
        .punto-venta-item .flex-shrink-0.ms-3 > div {
            width: 100% !important;
        }
    }

    /* Animaciones */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-stat, .payment-method-card {
        animation: fadeIn 0.3s ease-out;
    }

    /* Estados de los inputs */
    input:read-only {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    input:read-only:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }

    /* Estilos para los totales */
    #total_ventas_bs, #total_ventas_usd, #total_general_bs {
        font-size: 1.1rem;
    }

    #subtotal_bs, #subtotal_usd, #equivalente_bs {
        font-size: 0.95rem;
    }

    /* Tabs personalizados */
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1rem;
    }

    .nav-tabs-custom .nav-link:hover {
        border-color: #dee2e6;
        color: #495057;
    }

    .nav-tabs-custom .nav-link.active {
        color: #6f42c1;
        border-color: #6f42c1;
        background-color: rgba(111, 66, 193, 0.05);
    }

    /* Botones mejorados */
    .btn {
        border-radius: 6px;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Alertas personalizadas */
    .alert {
        border: none;
        border-radius: 8px;
        border-left: 4px solid transparent;
    }

    .alert-success {
        border-left-color: #198754;
    }

    .alert-info {
        border-left-color: #0dcaf0;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    /* Tooltips */
    [data-bs-toggle="tooltip"] {
        cursor: help;
    }

    /* Sombra suave para cards */
    .shadow-sm {
        box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
    }

    /* Estilos para validación */
    .is-invalid {
        border-color: #dc3545 !important;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .is-valid {
        border-color: #198754 !important;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-valid:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    /* Para los inputs de solo lectura */
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

    /* Toast personalizado */
    .toast-container {
        z-index: 9999;
    }

    .toast {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
</style>
@endsection