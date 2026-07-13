@extends('layout.layout_dashboard')

@section('title', 'Editar Recepción')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                  <i class="bi bi-pencil-square text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Recepción</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Recepción #{{ $recepcion->Numero }}</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.recepciones.proveedor') }}">Recepciones</a>
                    </li>
                    <li class="breadcrumb-item active">Editar #{{ $recepcion->Numero }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                        <h6 class="card-title mb-0 fw-bold text-white">
                            <i class="bi bi-info-circle me-2"></i>Recepción de Mercancía
                        </h6>
                        <div class="card-tools">
                            <a href="{{ route('cpanel.recepciones.proveedor') }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <!-- ========================================== -->
                        <!-- PANEL 1: PROVEEDOR -->
                        <!-- ========================================== -->
                        <div class="accordion" id="accordionRecepcion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingProveedor">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapseProveedor" aria-expanded="true">
                                        <i class="bi bi-person-badge me-2"></i>Proveedor
                                        <small class="ms-2 text-muted">Proveedor seleccionado para la recepción</small>
                                    </button>
                                </h2>
                                <div id="collapseProveedor" class="accordion-collapse collapse show" 
                                    data-bs-parent="#accordionRecepcion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-info">
                                                    <div class="row">
                                                        <div class="col-md-12 mb-2">
                                                            <i class="bi bi-building me-2"></i>
                                                            <strong>{{ $recepcion->proveedor_nombre }}</strong>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small>
                                                                <i class="bi bi-card-list me-1"></i>
                                                                <strong>RIF/Cédula:</strong> {{ $recepcion->proveedor_rif ?? 'N/A' }}
                                                            </small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small>
                                                                <i class="bi bi-telephone me-1"></i>
                                                                <strong>Teléfono:</strong> {{ $recepcion->proveedor_telefono ?? 'N/A' }}
                                                            </small>
                                                        </div>
                                                        <div class="col-md-12 mt-2">
                                                            <small>
                                                                <i class="bi bi-envelope me-1"></i>
                                                                <strong>Email:</strong> {{ $recepcion->proveedor_email ?? 'N/A' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ========================================== -->
                            <!-- PANEL 2: FACTURA -->
                            <!-- ========================================== -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFactura">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapseFactura" aria-expanded="false">
                                        <i class="bi bi-file-text me-2"></i>Factura
                                        <small class="ms-2 text-muted">Asociar factura a la recepción</small>
                                    </button>
                                </h2>
                                <div id="collapseFactura" class="accordion-collapse collapse" 
                                     data-bs-parent="#accordionRecepcion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="tipoFactura" 
                                                               id="radioConFactura" value="1" checked>
                                                        <label class="form-check-label" for="radioConFactura">
                                                            CON FACTURA
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="tipoFactura" 
                                                               id="radioSinFactura" value="0">
                                                        <label class="form-check-label" for="radioSinFactura">
                                                            SIN FACTURA
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div id="divUsarFactura">
                                                    @if($facturaDTO)
                                                        <div class="alert alert-info">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            Esta recepción ya tiene una factura asociada.
                                                        </div>
                                                        <div class="alert alert-secondary">
                                                            <strong>Factura asociada:</strong> {{ $facturaDTO->Numero }}
                                                        </div>
                                                        <select class="form-select" disabled>
                                                            <option value="">-- Recepción ya tiene factura asociada --</option>
                                                        </select>
                                                        <input type="hidden" name="factura_id" value="{{ $facturaDTO->ID }}">
                                                    @else
                                                        <label for="factura_id" class="form-label">Seleccionar Factura</label>
                                                        <select name="factura_id" id="factura_id" class="form-select">
                                                            <option value="">-- Seleccione una factura --</option>
                                                            @foreach($facturasPendientes as $factura)
                                                                <option value="{{ $factura->ID }}" 
                                                                    data-saldo="{{ $factura->saldo_pendiente }}">
                                                                    {{ $factura->Numero }} - Saldo: ${{ number_format($factura->saldo_pendiente, 2) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($facturasPendientes->count() == 0)
                                                            <div class="alert alert-warning mt-2">
                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                No hay facturas pendientes (En Proceso) para este proveedor.
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ========================================== -->
                            <!-- PANEL 3: PRODUCTOS -->
                            <!-- ========================================== -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingProductos">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapseProductos" aria-expanded="false">
                                        <i class="bi bi-box-seam me-2"></i>Productos
                                        <small class="ms-2 text-muted">Cargar productos desde Excel</small>
                                    </button>
                                </h2>
                                <div id="collapseProductos" class="accordion-collapse collapse" 
                                    data-bs-parent="#accordionRecepcion">
                                    <div class="accordion-body">
                                        
                                        <!-- Sección de carga por Excel -->
                                        <div class="card mb-3 border-info">
                                            <div class="card-header bg-info text-white">
                                                <strong><i class="bi bi-file-excel me-2"></i>Cargar Productos desde Excel</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Subir archivo de recepción</label>
                                                        <div class="input-group">
                                                            <input type="file" name="recepcion_excel" id="recepcion_excel" 
                                                                class="form-control" accept=".xlsx,.xls">
                                                            <button type="button" class="btn btn-primary" id="btnUploadExcel" disabled>
                                                                <i class="bi bi-upload me-1"></i>Cargar recepción
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">&nbsp;</label>
                                                        <div>
                                                            <button type="button" class="btn btn-success" onclick="descargarExcelRecepcion()">
                                                                <i class="bi bi-download me-1"></i>Descargar Plantilla Excel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            La plantilla debe contener las columnas: Código, Descripción, Cantidad, Costo USD
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tabla de productos -->
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped" id="tablaProductos">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th style="width: 60px;">Foto</th>
                                                                <th>Código</th>
                                                                <th>Producto</th>
                                                                <th class="text-end">Costo</th>
                                                                <th class="text-end">Disponible</th>
                                                                <th class="text-end">Recibido</th>
                                                                <th class="text-end">Pie Solo</th>
                                                                <th class="text-end">Pie Inv.</th>
                                                                <th class="text-end">Dañado</th>
                                                                <th class="text-end">Vacío</th>
                                                                <th class="text-end">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($detalles as $detalle)
                                                            @php
                                                                // Obtener la URL de la foto del producto
                                                                $imgSrc = FileHelper::getOrDownloadFile(
                                                                    'images/items/thumbs/',
                                                                    $detalle->UrlFoto ?? '',
                                                                    'assets/img/adminlte/img/produc_default.jfif'
                                                                );
                                                                
                                                                $codigo = $detalle->Codigo ?? 'N/A';
                                                                $productoNombre = $detalle->producto_nombre ?? 'N/A';
                                                                $cantidadPedida = $detalle->CantidadPedida ?? 0;
                                                                $cantidadRecibida = $detalle->CantidadRecibida ?? 0;
                                                                $costoUnitario = $detalle->CostoDivisa ?? 0;
                                                                
                                                                // Valores de los tipos de piezas
                                                                $pieSolo = $detalle->CantidadPieSolo ?? 0;
                                                                $pieInvertido = $detalle->CantidadPieInvertido ?? 0;
                                                                $danado = $detalle->CantidadPiezaDanada ?? 0;
                                                                $vacio = $detalle->CantidadCajaVacia ?? 0;
                                                                
                                                                // Total = (Recibido - (PieSolo+PieInvertido+Danado+Vacio)) * CostoUnitario
                                                                // O según tu lógica de negocio
                                                                $total = $detalle->CostoDivisa * $cantidadPedida;

                                                                // ✅ Calcular unidades totales
                                                                $totalUnidades = ($cantidadPedida ?? 0) * ($detalle->factura_uxe ?? 1);
                                                                
                                                                // ✅ Costo total = unidades totales × costo por unidad
                                                                $costoTotal = $totalUnidades * ($detalle->factura_costo_divisa ?? 0);
                                                            @endphp

                                                            <tr>
                                                                <td class="text-center">
                                                                    <img src="{{ $imgSrc }}" 
                                                                        loading="lazy" 
                                                                        alt="{{ $codigo }}"
                                                                        class="img-thumbnail"
                                                                        style="width: 40px; height: 40px; object-fit: cover;"
                                                                        onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                                </td>
                                                                <td><code>{{ $codigo }}</code></td>
                                                                <td>{{ $productoNombre }}</td>
                                                                <td class="text-end">${{ number_format($costoUnitario, 2) }}</td>
                                                                <td class="text-end">{{ number_format($cantidadPedida, 2) }}</td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end cantidad-recibida"
                                                                        style="width: 100px; display: inline-block;"
                                                                        data-id="{{ $detalle->RecepcionesDetallesId }}"
                                                                        data-costo="{{ $costoUnitario }}"
                                                                        value="{{ number_format($cantidadRecibida, 2) }}">
                                                                </td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end pie-solo"
                                                                        style="width: 80px; display: inline-block;"
                                                                        data-id="{{ $detalle->RecepcionesDetallesId }}"
                                                                        value="{{ number_format($pieSolo, 2) }}">
                                                                </td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end pie-invertido"
                                                                        style="width: 80px; display: inline-block;"
                                                                        data-id="{{ $detalle->RecepcionesDetallesId }}"
                                                                        value="{{ number_format($pieInvertido, 2) }}">
                                                                </td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end danado"
                                                                        style="width: 80px; display: inline-block;"
                                                                        data-id="{{ $detalle->RecepcionesDetallesId }}"
                                                                        value="{{ number_format($danado, 2) }}">
                                                                </td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end vacio"
                                                                        style="width: 80px; display: inline-block;"
                                                                        data-id="{{ $detalle->RecepcionesDetallesId }}"
                                                                        value="{{ number_format($vacio, 2) }}">
                                                                </td>
                                                                <td class="text-end subtotal-cell">${{ number_format($total, 2) }}</td>
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="11" class="text-center py-4">
                                                                    <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                                                    No hay productos agregados
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
                            </div>
                            
                            <!-- ========================================== -->
                            <!-- PANEL 4: TOTAL RECEPCIÓN -->
                            <!-- ========================================== -->

                            <input type="hidden" id="recepcion_id" value="{{ $recepcion->RecepcionId }}">

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTotal">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapseTotal" aria-expanded="false">
                                        <i class="bi bi-calculator me-2"></i>Total Recepción
                                    </button>
                                </h2>
                                <div id="collapseTotal" class="accordion-collapse collapse" 
                                    data-bs-parent="#accordionRecepcion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-8 offset-md-2">
                                                <div class="card">
                                                    <div class="card-header bg-success text-white">
                                                        <strong><i class="bi bi-receipt me-2"></i>RESUMEN DE RECEPCIÓN</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <td><strong>Subtotal:</strong></td>
                                                                <td class="text-end">
                                                                    ${{ number_format($subtotalRecepcion ?? 0, 2) }}
                                                                </td>
                                                            </tr>
                                                            @if($recepcion->EsConFactura == 1 && isset($facturaDTO))
                                                            <tr>
                                                                <td><strong>Flete:</strong></td>
                                                                <td class="text-end">
                                                                    ${{ number_format($facturaDTO->Flete ?? 0, 2) }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Aduana:</strong></td>
                                                                <td class="text-end">
                                                                    ${{ number_format($facturaDTO->Aduana ?? 0, 2) }}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr class="table-secondary">
                                                                <td><strong>Total recepción Bs.:</strong></td>
                                                                <td class="text-end">
                                                                    Bs. {{ number_format($totalRecepcionBs ?? 0, 2) }}
                                                                </td>
                                                            </tr>
                                                            <tr class="table-success">
                                                                <td><strong>Total recepción divisas:</strong></td>
                                                                <td class="text-end fw-bold">
                                                                    ${{ number_format($totalRecepcion ?? 0, 2) }}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Contenido (Items y Unidades) -->
                                                <div class="card mt-3">
                                                    <div class="card-header bg-info text-white">
                                                        <strong><i class="bi bi-box-seam me-2"></i>CONTENIDO</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row text-center">
                                                            <div class="col-6">
                                                                <h4>ITEMS</h4>
                                                                <h2 class="text-primary">{{ $totalItems ?? 0 }}</h2>
                                                            </div>
                                                            <div class="col-6">
                                                                <h4>UNIDADES</h4>
                                                                <h2 class="text-primary">{{ $totalUnidades ?? 0 }}</h2>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Botones -->
                                                <div class="row mt-3">
                                                    <div class="col-6">
                                                        <button class="btn btn-success w-100" id="btnConfirmarRecepcion" 
                                                                onclick="confirmarRecepcion()">
                                                            <i class="bi bi-check-circle me-1"></i>GUARDAR
                                                        </button>
                                                    </div>
                                                    <div class="col-6">
                                                        <button class="btn btn-danger w-100" id="btnCancelarRecepcion">
                                                            <i class="bi bi-x-circle me-1"></i>CANCELAR RECEPCIÓN
                                                        </button>
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
        </div>
        
    </div>
</div>

@endsection

@section('js')

<!-- Scripts para exportar Excel y PDF -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Definir la URL para asociar factura
    const recuperarFacturaBaseUrl = '{{ route("cpanel.recepciones.asociar-factura", ["id" => $recepcion->RecepcionId]) }}';
    console.log('URL asociar factura:', recuperarFacturaBaseUrl);
    
    // ============================================
    // HABILITAR BOTÓN DE UPLOAD (sin jQuery)
    // ============================================
    var recepcionExcel = document.getElementById('recepcion_excel');
    if (recepcionExcel) {
        recepcionExcel.addEventListener('change', function() {
            var btnUpload = document.getElementById('btnUploadExcel');
            if (btnUpload) {
                btnUpload.disabled = this.value === '';
            }
        });
    }
    
    // ============================================
    // CANCELAR RECEPCIÓN (sin jQuery)
    // ============================================
    var btnCancelar = document.getElementById('btnCancelarRecepcion');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            Swal.fire({
                title: '¿Cancelar recepción?',
                text: 'Esta acción anulará la recepción actual',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    var recepcionId = document.getElementById('recepcion_id')?.value || '{{ $recepcion->RecepcionId ?? '' }}';
                    window.location.href = '/cpanel/recepciones/' + recepcionId + '/cancelar';
                }
            });
        });
    }
    
    // ============================================
    // ASOCIAR FACTURA
    // ============================================
    console.log('=== Inicializando evento de factura ===');

    var facturaSelect = document.getElementById('factura_id');
    console.log('Elemento factura_id encontrado:', facturaSelect ? 'Sí' : 'No');

    if (facturaSelect) {
        console.log('Agregando event listener change...');
        
        facturaSelect.addEventListener('change', function() {
            console.log('=== Evento change disparado ===');
            var facturaId = this.value;
            var recepcionId = document.getElementById('recepcion_id')?.value || '{{ $recepcion->RecepcionId ?? '' }}';
            
            console.log('FacturaId seleccionado:', facturaId);
            console.log('RecepcionId:', recepcionId);
            
            if (!facturaId) {
                console.log('No se seleccionó factura, saliendo...');
                return;
            }
            
            Swal.fire({
                title: '¿Asociar esta factura?',
                text: 'Se cargarán los productos de la factura a la recepción',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, asociar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                console.log('Resultado de confirmación:', result);
                
                if (result.isConfirmed) {
                    console.log('Usuario confirmó, enviando petición...');
                    
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Asociando factura a la recepción',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // ✅ Usar la URL generada por Laravel
                    const url = recuperarFacturaBaseUrl;
                    console.log('URL de la petición:', url);
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            factura_id: facturaId
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            Swal.fire('Éxito', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                            facturaSelect.value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch:', error);
                        Swal.fire('Error', 'Error al asociar la factura: ' + error.message, 'error');
                        facturaSelect.value = '';
                    });
                } else {
                    console.log('Usuario canceló, reseteando select');
                    facturaSelect.value = '';
                }
            });
        });
        
        console.log('Event listener agregado correctamente');
    } else {
        console.error('No se encontró el elemento con id "factura_id"');
    }

        // ============================================
    // ELIMINAR PRODUCTO DE LA RECEPCIÓN
    // ============================================
    function eliminarProducto(recepcionesDetallesId) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: 'Esta acción eliminará el producto de la recepción',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                var recepcionId = document.getElementById('recepcion_id')?.value || '{{ $recepcion->RecepcionId ?? '' }}';
                
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch(`/cpanel/recepciones/${recepcionId}/eliminar-producto/${recepcionesDetallesId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', 'Producto eliminado correctamente', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el producto', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al eliminar el producto', 'error');
                });
            }
        });
    }

    // ============================================
    // SUBIR EXCEL (solo botón)
    // ============================================

    // Definir la URL usando el nombre de la ruta
    const uploadExcelUrl = '{{ route("cpanel.recepciones.upload-excel", ["id" => $recepcion->RecepcionId]) }}';
    console.log('URL upload:', uploadExcelUrl);

    var btnUploadExcel = document.getElementById('btnUploadExcel');
    if (btnUploadExcel) {
        btnUploadExcel.addEventListener('click', function() {
            var fileInput = document.getElementById('recepcion_excel');
            var file = fileInput.files[0];
            
            if (!file) {
                Swal.fire('Error', 'Seleccione un archivo Excel', 'warning');
                return;
            }
            
            var formData = new FormData();
            formData.append('excel_file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            Swal.fire({
                title: 'Cargando...',
                text: 'Procesando archivo Excel',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            var recepcionId = document.getElementById('recepcion_id')?.value || '{{ $recepcion->RecepcionId ?? '' }}';
            
            fetch(uploadExcelUrl, {
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
                    Swal.fire('Éxito', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al cargar el archivo', 'error');
            });
        });
    }

    function generarExcelRecepcion(datos, nombreArchivo = 'Recepcion.xlsx') {
        // Crear libro de trabajo
        const workbook = XLSX.utils.book_new();
        
        // Preparar las filas del Excel (formato específico)
        const filas = [];
        
        // Fila 1: Título "Recepcion"
        filas.push(['Recepcion', '', '', '', '', '', '', '', '']);
        
        // Fila 2: Subtítulo
        filas.push(['ENTRADA DE RECEPCION', '', '', '', '', '', '', '', '']);
        
        // Fila 3: Empresa
        filas.push(['Empresa', datos.encabezado.empresa, '', '', '', '', '', '', '']);
        
        // Fila 4: Vacía
        filas.push(['', '', '', '', '', '', '', '', '']);
        
        // Fila 5: Fecha
        filas.push(['Fecha', datos.encabezado.fecha, '', '', '', '', '', '', '']);
        
        // Fila 6: Vacía
        filas.push(['', '', '', '', '', '', '', '', '']);
        
        // Fila 7: Proveedor
        filas.push([
            'Proveedor', 
            'CodigoProveedor', 
            datos.proveedor.codigo, 
            'Nombre', 
            datos.proveedor.nombre, 
            '', '', '', ''
        ]);
        
        // Fila 8: Vacía
        filas.push(['', '', '', '', '', '', '', '', '']);
        
        // Fila 9: Productos
        filas.push(['Productos', '', '', '', '', '', '', '', '']);
        
        // ✅ Fila 10: Encabezados de columnas (SIN Costo Unitario)
        filas.push([
            'Codigo', 'Referencia', 'Descripcion', 
            'Cantidad', 'Pie Solo', 'Pie Invertdo', 'Dañado', 'Vacío'
        ]);
        
        // ✅ Filas 11+: Datos de productos (SIN Costo Unitario)
        datos.productos.forEach(producto => {
            filas.push([
                producto.codigo,
                producto.referencia,
                producto.descripcion,
                producto.cantidad,
                producto.pie_solo || 0,
                producto.pie_invertido || 0,
                producto.danado || 0,
                producto.vacio || 0
            ]);
        });
        
        // Crear hoja de cálculo
        const worksheet = XLSX.utils.aoa_to_sheet(filas);
        
        // ✅ Ajustar anchos de columna (8 columnas ahora)
        worksheet['!cols'] = [
            {wch: 15}, // Codigo
            {wch: 15}, // Referencia
            {wch: 40}, // Descripcion
            {wch: 12}, // Cantidad
            {wch: 10}, // Pie Solo
            {wch: 12}, // Pie Invertido
            {wch: 10}, // Dañado
            {wch: 8}   // Vacío
        ];
        
        // Agregar hoja al libro
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Recepcion');
        
        // Exportar y descargar
        XLSX.writeFile(workbook, nombreArchivo);
    }

    function descargarExcelRecepcion() {
        if (!facturaData) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin factura asociada',
                text: 'Esta recepción no tiene una factura asociada para exportar',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        if (!facturaData.Detalles || facturaData.Detalles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'La factura no tiene productos para exportar',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // ✅ FILTRAR: Solo productos con cantidad PENDIENTE > 0 (en unidades reales)
        const productosPendientes = facturaData.Detalles.filter(detalle => {
            const uxe = detalle.UxE ?? 1;
            const cantidadEmitidaReal = (detalle.CantidadEmitida ?? 0) * uxe;
            const cantidadRecibida = detalle.CantidadRecibida ?? 0;
            const pendiente = cantidadEmitidaReal - cantidadRecibida;
            
            return pendiente > 0;  // Solo si falta por recibir
        });
        
        if (productosPendientes.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Factura completada',
                text: 'Todos los productos de esta factura ya han sido recibidos completamente',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        Swal.fire({
            title: 'Generando Excel...',
            text: `Se exportarán ${productosPendientes.length} productos pendientes`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        // Obtener datos del proveedor desde la recepción
        const proveedorNombre = '{{ $recepcion->proveedor_nombre ?? "N/A" }}';
        const proveedorRif = '{{ $recepcion->proveedor_rif ?? "N/A" }}';
        const proveedorTelefono = '{{ $recepcion->proveedor_telefono ?? "N/A" }}';
        const proveedorEmail = '{{ $recepcion->proveedor_email ?? "N/A" }}';
        const proveedorId = '{{ $recepcion->proveedor_id ?? "N/A" }}';
        
        // Preparar datos en el formato del Excel
        const datosExcel = {
            encabezado: {
                empresa: 'Tiendas TenShop',
                fecha: facturaData.FechaCreacion ? new Date(facturaData.FechaCreacion).toLocaleString() : new Date().toLocaleString(),
                factura_numero: facturaData.Numero || 'N/A',
                contenedor: facturaData.Contenedor?.Nombre || 'N/A'
            },
            proveedor: {
                codigo: proveedorId,
                nombre: proveedorNombre,
                rif: proveedorRif,
                telefono: proveedorTelefono,
                email: proveedorEmail
            },
            productos: []
        };
        
        // ✅ Convertir SOLO los productos pendientes (en unidades reales)
        productosPendientes.forEach(detalle => {
            const uxe = detalle.UxE ?? 1;
            const cantidadEmitidaReal = (detalle.CantidadEmitida ?? 0) * uxe;
            const cantidadRecibida = detalle.CantidadRecibida ?? 0;
            const pendiente = cantidadEmitidaReal - cantidadRecibida;
            
            datosExcel.productos.push({
                codigo: detalle.Codigo || '',
                referencia: detalle.Referencia || '',
                descripcion: detalle.producto_nombre || detalle.Descripcion || '',
                cantidad: pendiente,  // ✅ Cantidad PENDIENTE en unidades
                pie_solo: 0,
                pie_invertido: 0,
                danado: 0,
                vacio: 0
            });
        });
        
        // Generar el Excel
        const nombreArchivo = `Factura_${facturaData.Numero}_Pendientes_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.xlsx`;
        generarExcelRecepcion(datosExcel, nombreArchivo);
        
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Excel generado',
            text: `Se exportaron ${datosExcel.productos.length} productos pendientes de la factura ${facturaData.Numero}`,
            timer: 3000,
            showConfirmButton: false
        });
    }

    /**
     * Descargar plantilla vacía
     */
    function descargarPlantillaVacia() {
        fetch('/cpanel/recepciones/plantilla-vacia-datos', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                generarExcelRecepcion(result.data, 'Plantilla_Recepcion.xlsx');
            } else {
                Swal.fire('Error', 'Error al generar la plantilla', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al generar la plantilla', 'error');
        });
    }

    // Pasar datos de la factura a JavaScript
    var facturaData = null;
    @if($recepcion->EsConFactura == 1 && isset($facturaDTO))
        facturaData = @json($facturaDTO);
        console.log('Factura cargada:', facturaData?.Numero);
    @else
        console.log('No hay factura asociada');
    @endif

    // Función para guardar los cambios de los inputs en la base de datos
    function guardarCambiosProductos() {
        var productosActualizados = [];
        
        document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
            var detalleId = row.querySelector('.cantidad-recibida')?.dataset.id;
            if (detalleId) {
                var cantidadRecibida = parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;
                var pieSolo = parseFloat(row.querySelector('.pie-solo')?.value) || 0;
                var pieInvertido = parseFloat(row.querySelector('.pie-invertido')?.value) || 0;
                var danado = parseFloat(row.querySelector('.danado')?.value) || 0;
                var vacio = parseFloat(row.querySelector('.vacio')?.value) || 0;
                
                productosActualizados.push({
                    id: detalleId,
                    cantidad_recibida: cantidadRecibida,
                    pie_solo: pieSolo,
                    pie_invertido: pieInvertido,
                    danado: danado,
                    vacio: vacio
                });
            }
        });
        
        if (productosActualizados.length === 0) {
            return Promise.resolve();
        }
        
        // Enviar los cambios al servidor
        return fetch('/cpanel/recepciones/guardar-productos', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ productos: productosActualizados })
        }).then(response => response.json());
    }

    function confirmarRecepcion() {
        var recepcionId = document.getElementById('recepcion_id')?.value || '{{ $recepcion->RecepcionId ?? '' }}';
        
        // Recoger todos los valores de los inputs de la tabla
        var detalles = [];
        var hayProductos = false;
        
        document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
            var cantidadRecibida = parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;
            
            if (cantidadRecibida > 0) {
                hayProductos = true;
            }
            
            var danadoValue = parseFloat(row.querySelector('.danado')?.value) || 0;
            
            detalles.push({
                id: row.querySelector('.cantidad-recibida')?.dataset.id || null,
                cantidad_recibida: cantidadRecibida,
                pie_solo: parseFloat(row.querySelector('.pie-solo')?.value) || 0,
                pie_invertido: parseFloat(row.querySelector('.pie-invertido')?.value) || 0,
                danado: danadoValue,
                vacio: parseFloat(row.querySelector('.vacio')?.value) || 0
            });
        });
        
        // Validar que haya al menos un producto con cantidad > 0
        if (!hayProductos) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'Debe recibir al menos un producto',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        const finalizarUrl = '{{ route("cpanel.recepciones.finalizar", ["id" => $recepcion->RecepcionId]) }}';
        const actualizarUrl = '{{ route("cpanel.recepciones.actualizar-detalles", ["id" => $recepcion->RecepcionId]) }}';
        
        Swal.fire({
            title: '¿Confirmar recepción?',
            text: 'Una vez confirmada, no se podrán modificar los productos',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Finalizando recepción',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(actualizarUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ detalles: detalles })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return fetch(finalizarUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => {
                            window.location.href = '{{ route("cpanel.recepciones.proveedor") }}';
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al finalizar la recepción', 'error');
                });
            }
        });
    }

    // Actualizar total cuando cambie Cantidad Recibida
    document.querySelectorAll('.cantidad-recibida').forEach(input => {
        input.addEventListener('change', function() {
            var cantidad = parseFloat(this.value) || 0;
            var costo = parseFloat(this.dataset.costo) || 0;
            var subtotal = cantidad * costo;
            
            // Actualizar el subtotal de la fila
            var row = this.closest('tr');
            row.querySelector('.subtotal-cell').innerText = '$' + subtotal.toFixed(2);
            
            // Recalcular el total general
            recalcularTotalGeneral();
        });
    });

    // ✅ Nuevo: Actualizar cuando cambien Pie Solo, Pie Inv., Dañado, Vacío
    document.querySelectorAll('.pie-solo, .pie-invertido, .danado, .vacio').forEach(input => {
        input.addEventListener('change', function() {
            var row = this.closest('tr');
            var cantidadRecibida = parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;
            var costo = parseFloat(row.querySelector('.cantidad-recibida')?.dataset.costo) || 0;
            
            // Obtener los valores de los diferentes tipos
            var pieSolo = parseFloat(row.querySelector('.pie-solo')?.value) || 0;
            var pieInvertido = parseFloat(row.querySelector('.pie-invertido')?.value) || 0;
            var danado = parseFloat(row.querySelector('.danado')?.value) || 0;
            var vacio = parseFloat(row.querySelector('.vacio')?.value) || 0;
            
            // Calcular cantidad neta (Recibido - dañados - vacíos - etc.)
            // O según tu lógica de negocio
            var cantidadNeta = cantidadRecibida - (pieSolo + pieInvertido + danado + vacio);
            var subtotal = cantidadNeta * costo;
            
            row.querySelector('.subtotal-cell').innerText = '$' + subtotal.toFixed(2);
            recalcularTotalGeneral();
        });
    });

    function recalcularTotalGeneral() {
        var total = 0;
        document.querySelectorAll('.subtotal-cell').forEach(cell => {
            var valor = parseFloat(cell.innerText.replace('$', '').replace(',', '')) || 0;
            total += valor;
        });
        document.getElementById('totalRecepcion').innerText = '$' + total.toFixed(2);
    }
</script>
@endsection