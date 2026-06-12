@extends('layout.layout_dashboard')

@section('title', 'Editar Factura')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>Editar Factura
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}">
                            Detalle Proveedor
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Factura #{{ $facturaDTO->Numero }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- ========================================== -->
        <!-- TARJETA DE INFORMACIÓN GENERAL -->
        <!-- ========================================== -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline shadow-sm h-100">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle-fill me-2"></i>Información de la Factura
                        </h3>
                        <div class="card-tools">
                            @if($facturaDTO->Estatus == 1)
                            <a href="{{ route('cpanel.facturas.editar', $facturaDTO->ID) }}" 
                            class="btn btn-sm btn-light text-warning">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                            @endif
                            <a href="{{ route('cpanel.proveedores.detalle', $facturaDTO->ProveedorId) }}" 
                            class="btn btn-sm btn-light text-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-stretch">
                        <div class="row w-100">
                            <div class="col-md-6 d-flex">
                                <div class="info-box p-3 flex-fill">
                                    <div class="d-flex flex-column h-100">
                                        <div class="flex-grow-1">
                                            <div class="mb-2">
                                                <span class="text-muted">Número:</span>
                                                <strong class="fs-5 d-block">{{ $facturaDTO->Numero }}</strong>
                                                @if($facturaDTO->Serie) 
                                                    <small class="text-muted">Serie: {{ $facturaDTO->Serie }}</small>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted">Fecha Emisión:</span>
                                                <span class="d-block">{{ \Carbon\Carbon::parse($facturaDTO->FechaCreacion)->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted">Fecha Despacho:</span>
                                                <span class="d-block">{{ $facturaDTO->FechaDespacho ? \Carbon\Carbon::parse($facturaDTO->FechaDespacho)->format('d/m/Y') : 'Pendiente' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted">Estatus:</span>
                                                <div class="mt-1">
                                                    @php
                                                        $estatusActual = (int)$facturaDTO->Estatus;
                                                        $estadoTexto = match($estatusActual) {
                                                            1 => 'En Proceso',
                                                            2 => 'Recibiendo',
                                                            4 => 'Recibida',
                                                            default => 'Desconocido'
                                                        };
                                                        $estadoColor = match($estatusActual) {
                                                            1 => 'warning',
                                                            2 => 'info',
                                                            4 => 'success',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $estadoColor }} fs-6 px-3 py-2">{{ $estadoTexto }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex">
                                <div class="info-box p-3 flex-fill">
                                    <div class="d-flex flex-column h-100">
                                        <div class="flex-grow-1">
                                            <div class="mb-2">
                                                <span class="text-muted">Proveedor:</span>
                                                <strong class="d-block">{{ $facturaDTO->proveedor_nombre }}</strong>
                                                <small>RIF: {{ $facturaDTO->proveedor_rif ?? 'N/A' }}</small>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted">Sucursal:</span>
                                                <span class="d-block">{{ $facturaDTO->sucursal_nombre }}</span>
                                                <small>{{ $facturaDTO->sucursal_direccion ?? '' }}</small>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted">Contenedor:</span>
                                                <span class="d-block">{{ $facturaDTO->Contenedor->Nombre ?? 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted">Descripción:</span>
                                                <span class="d-block">{{ $facturaDTO->Descripcion ?? 'Sin descripción' }}</span>
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
        </br>

        <!-- ========================================== -->
        <!-- RESUMEN FINANCIERO (2 columnas) -->
        <!-- ========================================== -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-info h-100 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-calculator-fill me-2"></i>RESUMEN DE LA FACTURA
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Subtotal productos:</span>
                            <strong>$ {{ number_format($facturaDTO->Subtotal ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Flete contenedor:</span>
                            <strong>$ {{ number_format($facturaDTO->Flete ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Costo traspaso:</span>
                            <strong>$ {{ number_format($facturaDTO->CostoTraspaso ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <span class="fw-bold fs-5">TOTAL FACTURA:</span>
                            <strong class="fw-bold text-success fs-5">$ {{ number_format($facturaDTO->TotalFactura ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-warning h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-cash-stack me-2"></i>GASTOS Y ADUANA
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Aduana:</span>
                            <strong>$ {{ number_format($facturaDTO->Aduana ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Porcentaje de gastos:</span>
                            <strong>{{ number_format($facturaDTO->PorcentajeGastos ?? 0, 2) }} %</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FORMULARIO DE EDICIÓN DE FACTURA -->
        <!-- ========================================== -->
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Editar Factura
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.facturas.actualizar', $facturaDTO->ID) }}" method="POST" id="formEditarFactura">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="proveedor_id" value="{{ $facturaDTO->ProveedorId }}">
                    <input type="hidden" name="tipo" value="{{ $facturaDTO->Tipo }}">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contenedor_id" class="form-label fw-bold">Contenedor</label>
                            <div class="input-group">
                                <select name="contenedor_id" id="contenedor_id" class="form-select">
                                    <option value="0">Seleccione un valor</option>
                                    @foreach($contenedores as $contenedor)
                                        <option value="{{ $contenedor->Id }}" 
                                            {{ old('contenedor_id', $facturaDTO->ContenedorId) == $contenedor->Id ? 'selected' : '' }}>
                                            {{ $contenedor->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('cpanel.contenedores.crear') }}" 
                                   class="btn btn-outline-primary"
                                   title="Crear nuevo contenedor">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" 
                                    name="es_cargar_flete" id="es_cargar_flete" 
                                    value="1" {{ old('es_cargar_flete', $facturaDTO->EsCargarFleteEnFactura) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="es_cargar_flete">
                                    Sumar flete en Factura
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="fecha_creacion" class="form-label fw-bold">Fecha *</label>
                            <input type="date" name="fecha_creacion" id="fecha_creacion" 
                                class="form-control" 
                                value="{{ old('fecha_creacion', date('Y-m-d', strtotime($facturaDTO->FechaCreacion))) }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="traspaso" class="form-label fw-bold">Traspaso</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="traspaso" id="traspaso" 
                                    class="form-control" value="{{ old('traspaso', $facturaDTO->Traspaso ?? 0) }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="estatus" class="form-label fw-bold">Estatus *</label>
                            <select name="estatus" id="estatus" class="form-select" required>
                                <option value="" {{ old('estatus', $facturaDTO->Estatus) == '' ? 'selected' : '' }}>Seleccione un valor</option>
                                <option value="1" {{ old('estatus', $facturaDTO->Estatus) == 1 ? 'selected' : '' }}>En Proceso</option>
                                <option value="2" {{ old('estatus', $facturaDTO->Estatus) == 2 ? 'selected' : '' }}>Recibiendo</option>
                                <option value="4" {{ old('estatus', $facturaDTO->Estatus) == 4 ? 'selected' : '' }}>Recibida</option>
                                <option value="3" {{ old('estatus', $facturaDTO->Estatus) == 3 ? 'selected' : '' }}>Pagada</option>
                                <option value="0" {{ old('estatus', $facturaDTO->Estatus) == 0 ? 'selected' : '' }}>Anulada</option>
                            </select>
                            <small class="text-muted">El estatus "En Proceso" permite editar la factura</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Los detalles de la factura (productos) se pueden agregar en la sección de productos.
                    </div>
                    
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnActualizarFactura">
                            <i class="bi bi-save me-1"></i> Actualizar Factura
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCargarProductos">
                            <i class="bi bi-box-seam me-1"></i> Cargar Productos
                        </button>
                        <a href="{{ route('cpanel.facturas.detalle', $facturaDTO->ID) }}" 
                           class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TABS: PRODUCTOS | PAGOS -->
        <!-- ========================================== -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="facturaTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="productos-tab" data-bs-toggle="tab" 
                                data-bs-target="#productos" type="button">
                            <i class="bi bi-box-seam me-1"></i>Productos 
                            <span class="badge bg-primary ms-1">{{ $facturaDTO->Detalles->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" 
                                data-bs-target="#pagos" type="button">
                            <i class="bi bi-cash-stack me-1"></i>Pagos 
                            <span class="badge bg-success ms-1">{{ $facturaDTO->Pagos->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB: PRODUCTOS -->
                    <div class="tab-pane fade show active" id="productos">
                        <div class="d-flex justify-content-end mb-3 gap-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-danger" onclick="exportarPDFProductos()">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="exportarExcelProductos()">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" id="tablaProductos">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th style="width: 80px;">Imagen</th>
                                        <th>Código</th>
                                        <th>Referencia</th>
                                        <th>Producto</th>
                                        <th class="text-end">Cantidad (Unidades)</th>
                                        <th class="text-end">Costo Unitario USD</th>
                                        <th class="text-end">Total USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($facturaDTO->Detalles as $detalle)
                                    @php
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/items/thumbs/',
                                            $detalle->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                        
                                        // ✅ Calcular correctamente
                                        $totalUnidades = $detalle->CantidadRecibida ?? 0;
                                        $costoUnitario = $detalle->CostoDivisa / ($totalUnidades > 0 ? $totalUnidades : 1);
                                        $totalUSD = $detalle->CostoDivisa;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <img src="{{ $imgSrc }}" 
                                                alt="{{ $detalle->Codigo ?? 'producto' }}"
                                                class="img-thumbnail img-zoomable"
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                data-full-image="{{ $imgSrc }}"
                                                data-description="{{ $detalle->producto_nombre ?? 'Sin descripción' }}"
                                                onclick="zoomImagen(this)">
                                        </td>
                                        <td class="align-middle"><code>{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                        <td class="align-middle">{{ $detalle->Referencia ?? 'N/A' }}</td>
                                        <td class="align-middle">{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td class="text-end align-middle">{{ number_format($totalUnidades, 2) }}</td>
                                        <td class="text-end align-middle">$ {{ number_format($costoUnitario, 2) }}</td>
                                        <td class="text-end align-middle fw-bold">$ {{ number_format($totalUSD, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                            No hay productos registrados en esta factura
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- TAB: PAGOS -->
                    <div class="tab-pane fade" id="pagos">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold text-success">
                                <i class="bi bi-cash-stack me-1"></i>
                                Total Pagado: $ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}
                                <span class="text-danger ms-3">
                                    Saldo Pendiente: $ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cpanel.facturas.recibo-pagos', $facturaDTO->ID) }}" 
                                   class="btn btn-outline-danger btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </a>
                                <button type="button" class="btn btn-outline-success" onclick="exportarExcelPagos()">
                                    <i class="bi bi-file-excel me-1"></i>Excel
                                </button>
                                <a href="{{ route('cpanel.proveedores.pagar', $facturaDTO->ProveedorId) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i>Registrar Pago
                                </a>
                            </div>
                        </div>
                        
                        @if($facturaDTO->Pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaPagos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>N° Operación</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Monto USD</th>
                                        <th>Estatus</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturaDTO->Pagos as $pago)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $pago->NumeroOperacion ?? 'N/A' }}</td>
                                        <td>{{ $pago->Descripcion ?? 'Abono factura' }}</td>
                                        <td class="text-end">$ {{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                        <td class="text-center">
                                            @php
                                                $estatusPago = match((int)$pago->Estatus) {
                                                    2 => ['texto' => 'Pagada', 'clase' => 'success'],
                                                    4 => ['texto' => 'Cerrada', 'clase' => 'secondary'],
                                                    1 => ['texto' => 'Pendiente', 'clase' => 'warning'],
                                                    default => ['texto' => 'Desconocido', 'clase' => 'secondary']
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $estatusPago['clase'] }}">
                                                {{ $estatusPago['texto'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                @if(in_array((int)$pago->Estatus, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $pago->ID) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @endif
                                                
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarPago({{ $pago->ID }}, '{{ $pago->NumeroOperacion }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('cpanel.pagos.imprimir', $pago->ID) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">TOTAL PAGADO:</td>
                                        <td class="text-end fw-bold text-success">$ {{ number_format($facturaDTO->TotalPagado ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-danger">SALDO PENDIENTE:</td>
                                        <td class="text-end fw-bold text-danger">$ {{ number_format($facturaDTO->SaldoPendiente ?? 0, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay pagos registrados para esta factura
                        </div>
                        @endif
                    </div>
                    
                </div>
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

<!-- Modal para Cargar Productos -->
<div class="modal fade" id="modalCargarProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i>Cargar Productos a la Factura
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <!-- MÓDULO 1: BUSCAR Y GESTIONAR PRODUCTO -->
                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary text-white">
                        <strong><i class="bi bi-search me-2"></i>Buscar y Gestionar Producto</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Código</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="codigo_producto" placeholder="Ingrese código">
                                    <button class="btn btn-primary" type="button" id="btnBuscarProducto">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Descripción</label>
                                <input type="text" class="form-control bg-light" id="descripcion_producto" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Empaque</label>
                                <select class="form-select" id="empaque">
                                    <option value="1">Unidad</option>
                                    <option value="12">Docena</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Costo (USD)</label>
                                <input type="number" step="0.01" class="form-control" id="costo" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cantidad</label>
                                <input type="number" step="0.01" class="form-control" id="cantidad" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Total (USD)</label>
                                <input type="text" class="form-control bg-light" id="total" readonly>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-outline-warning" id="btnEditarProducto">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="btnBorrarProducto">
                                <i class="bi bi-trash me-1"></i> Borrar
                            </button>
                            <button type="button" class="btn btn-success" id="btnGuardarProducto">
                                <i class="bi bi-save me-1"></i> Guardar Producto
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- MÓDULO 2: GESTIÓN DE EXCEL -->
                <div class="card mb-3 border-info">
                    <div class="card-header bg-info text-white">
                        <strong><i class="bi bi-file-excel me-2"></i>Gestión de Excel</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" id="btnDescargarFormato">
                                <i class="bi bi-download me-1"></i> Descargar Formato
                            </button>
                            <input type="file" id="excel_file_input" style="display: none;" accept=".xlsx,.xls">
                            <button type="button" class="btn btn-outline-success" id="btnCargarExcel">
                                <i class="bi bi-upload me-1"></i> Cargar Excel
                            </button>
                            <button type="button" class="btn btn-primary" id="btnGuardarExcel">
                                <i class="bi bi-save me-1"></i> Guardar Excel
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- TABLA DE PRODUCTOS -->
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <strong><i class="bi bi-table me-2"></i>Lista de Productos</strong>
                        <span class="badge bg-light text-dark ms-2" id="totalProductosCount">0 productos</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-bordered table-striped mb-0" id="tablaProductosModal">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th style="width: 30px;"><input type="checkbox" id="seleccionarTodos"></th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Empaque</th>
                                        <th class="text-end">Costo Unitario USD</th>  <!-- Cambiado -->
                                        <th class="text-end">Cantidad (Unidades)</th>  <!-- Cambiado -->
                                        <th class="text-end">Total USD</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosBody">
                                    @foreach($facturaDTO->Detalles as $detalle)
                                    @php
                                        // Calcular valores como en .NET
                                        $costoUnitario = $detalle->CostoDivisa / ($detalle->UxE > 0 ? $detalle->UxE : 1);
                                        $totalUnidades = $detalle->CantidadEmitida * $detalle->UxE;
                                        $subtotal = $detalle->CantidadEmitida * $detalle->CostoDivisa;
                                        
                                        $empaqueTexto = '';
                                        if ($detalle->UxE == 1) $empaqueTexto = 'Unidad';
                                        elseif ($detalle->UxE == 12) $empaqueTexto = 'Docena';
                                        else $empaqueTexto = "Empaque x{$detalle->UxE}";
                                    @endphp
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="select-producto" value="{{ $detalle->ID }}"></td>
                                        <td><code>{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                        <td>{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                        <td>{{ $empaqueTexto }}</td>
                                        <td class="text-end">$ {{ number_format($costoUnitario, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalUnidades, 2) }}</td>
                                        <td class="text-end">$ {{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    // ============================================
    // ZOOM DE IMÁGENES
    // ============================================
    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');
        document.getElementById('zoomedImage').src = fullImage;
        document.getElementById('imageDescription').textContent = description;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeZoom();
    });
    
    // ============================================
    // EXPORTAR PRODUCTOS
    // ============================================
    function exportarExcelProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Productos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Productos');
        XLSX.writeFile(wb, `Productos_Factura_${new Date().toISOString().slice(0,10)}.xlsx`);
    }
    
    function exportarPDFProductos() {
        const tabla = document.getElementById('tablaProductos');
        if (!tabla) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Productos de la Factura', 14, 15);
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        doc.autoTable({ 
            html: '#tablaProductos', 
            startY: 35, 
            theme: 'grid', 
            headStyles: { fillColor: [41, 128, 185] },
            styles: { fontSize: 8 }
        });
        doc.save(`Productos_Factura_${new Date().toISOString().slice(0,10)}.pdf`);
    }
    
    // ============================================
    // EXPORTAR PAGOS
    // ============================================
    function exportarExcelPagos() {
        const tabla = document.getElementById('tablaPagos');
        if (!tabla) return;
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Pagos" });
        XLSX.utils.book_append_sheet(wb, ws, 'Pagos');
        XLSX.writeFile(wb, `Pagos_Factura_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportarPDFPagos() {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        const facturaId = '{{ $facturaDTO->ID }}';
        const url = '/cpanel/facturas/' + facturaId + '/recibo-pagos';
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) generarPDFReciboPagos(data);
            else Swal.fire('Error', data.message || 'Error al generar el PDF', 'error');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error de conexión al servidor: ' + error.message, 'error');
        });
    }

    function generarPDFReciboPagos(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 20;
        
        doc.setFillColor(43, 140, 94);
        doc.rect(0, 0, pageWidth, 45, 'F');
        doc.setFontSize(18);
        doc.setTextColor(255, 255, 255);
        doc.text('TENSHOP', pageWidth / 2, 25, { align: 'center' });
        doc.setFontSize(10);
        doc.text('Comprobante de pagos', pageWidth / 2, 35, { align: 'center' });
        
        yPos = 60;
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('REGISTRO DE PAGOS', pageWidth / 2, yPos, { align: 'center' });
        yPos += 10;
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Factura N°: ${data.factura_numero}`, 20, yPos);
        doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, pageWidth - 20, yPos, { align: 'right' });
        yPos += 10;
        
        const totalPagado = parseFloat(data.total_pagado) || 0;
        const saldoPendiente = parseFloat(data.saldo_pendiente) || 0;
        
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.setFillColor(240, 240, 240);
        doc.rect(20, yPos, pageWidth - 40, 35, 'F');
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text(`Total Pagado:`, 25, yPos + 10);
        doc.setFontSize(12);
        doc.setTextColor(43, 140, 94);
        doc.text(`$ ${totalPagado.toFixed(2)}`, 100, yPos + 10);
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text(`Saldo Pendiente:`, 25, yPos + 22);
        doc.setFontSize(12);
        doc.setTextColor(220, 53, 69);
        doc.text(`$ ${saldoPendiente.toFixed(2)}`, 100, yPos + 22);
        
        yPos += 45;
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text('Detalle de pagos realizados:', 20, yPos);
        yPos += 5;
        
        const tableColumnas = ['Fecha', 'N° Operación', 'Descripción', 'Monto USD', 'Estatus'];
        const tableData = [];
        data.pagos.forEach(pago => {
            const monto = parseFloat(pago.MontoDivisaAbonado) || 0;
            tableData.push([
                new Date(pago.Fecha).toLocaleDateString('es-VE'),
                pago.NumeroOperacion || 'N/A',
                pago.Descripcion || 'Abono factura',
                `$ ${monto.toFixed(2)}`,
                pago.EstatusTexto
            ]);
        });
        
        doc.autoTable({
            head: [tableColumnas],
            body: tableData,
            startY: yPos,
            margin: { left: 20, right: 20 },
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: [255, 255, 255], fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            columnStyles: {
                0: { cellWidth: 35 },
                1: { cellWidth: 45 },
                2: { cellWidth: 60 },
                3: { cellWidth: 35, halign: 'right' },
                4: { cellWidth: 30, halign: 'center' }
            }
        });
        
        yPos = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(9);
        doc.setTextColor(128, 128, 128);
        doc.text('Este documento es un comprobante de pagos válido para efectos contables.', pageWidth / 2, yPos, { align: 'center' });
        doc.text(`Generado el ${new Date().toLocaleString('es-VE')}`, pageWidth / 2, yPos + 7, { align: 'center' });
        
        yPos += 20;
        doc.line(30, yPos, 80, yPos);
        doc.line(pageWidth - 80, yPos, pageWidth - 30, yPos);
        doc.setFontSize(9);
        doc.text('FIRMA DEL PROVEEDOR', 55, yPos + 5, { align: 'center' });
        doc.text('FIRMA DEL RECIBIDOR', pageWidth - 55, yPos + 5, { align: 'center' });
        
        doc.save(`Recibo_Pagos_Factura_${data.factura_numero}_${new Date().toISOString().slice(0,10)}.pdf`);
        Swal.close();
    }
    
    // ============================================
    // REGISTRAR PAGO
    // ============================================
    function registrarPago(facturaId) {
        Swal.fire({
            title: 'Registrar Pago',
            text: 'Funcionalidad en desarrollo',
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // ============================================
    // ELIMINAR PAGO
    // ============================================
    function eliminarPago(pagoId, numeroOperacion) {
        Swal.fire({
            title: '¿Eliminar pago?',
            html: `Estás a punto de eliminar el pago <strong>${numeroOperacion}</strong><br><span style="color: red;">Esta acción no se puede deshacer.</span>`,
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
                    didOpen: () => { Swal.showLoading(); }
                });
                fetch(`{{ url('pagos') }}/${pagoId}`, {
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
                        Swal.fire({ title: '¡Eliminado!', text: 'El pago ha sido eliminado correctamente', icon: 'success', timer: 2000, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el pago', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // ============================================
    // EVENTOS DEL MODAL
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initModalEventos();
    });
    
    function initModalEventos() {
        // Buscar Producto
        const btnBuscar = document.getElementById('btnBuscarProducto');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', function() {
                let codigo = document.getElementById('codigo_producto')?.value;
                let proveedorId = '{{ $facturaDTO->ProveedorId }}';
                let facturaId = '{{ $facturaDTO->ID }}';
                if (!codigo) {
                    Swal.fire('Error', 'Ingrese un código de producto', 'warning');
                    return;
                }
                const url = '{{ url("/cpanel/buscar-producto") }}?codigo=' + encodeURIComponent(codigo) + '&proveedor_id=' + proveedorId + '&factura_id=' + facturaId;
                fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        document.getElementById('descripcion_producto').value = response.producto.Descripcion;
                        document.getElementById('costo').value = response.producto.CostoDivisa;
                        let productoIdInput = document.getElementById('producto_id');
                        if (!productoIdInput) {
                            let hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.id = 'producto_id';
                            document.getElementById('codigo_producto').parentElement.appendChild(hiddenInput);
                        }
                        document.getElementById('producto_id').value = response.producto.ID;
                        let cantidadEnFactura = response.producto.CantidadEnFactura || 0;
                        let cantidadDisponible = response.producto.CantidadDisponible || 0;
                        if (cantidadEnFactura > 0) {
                            document.getElementById('cantidad').value = cantidadEnFactura;
                            document.getElementById('cantidad').placeholder = `Cantidad actual en factura: ${cantidadEnFactura}`;
                        } else {
                            document.getElementById('cantidad').value = '';
                            document.getElementById('cantidad').placeholder = `Disponible: ${cantidadDisponible}`;
                            Swal.fire({ title: 'Producto encontrado', html: `Producto: <strong>${response.producto.Descripcion}</strong><br>Cantidad disponible: <strong>${cantidadDisponible}</strong>`, icon: 'success', timer: 2000, showConfirmButton: false });
                        }
                        calcularTotalModal();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        limpiarFormularioProductoModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al buscar el producto', 'error');
                });
            });
        }
        
        // Guardar Producto Individual
        document.getElementById('btnGuardarProducto')?.addEventListener('click', guardarProductoModal);
        document.getElementById('btnEditarProducto')?.addEventListener('click', editarProductoModal);
        document.getElementById('btnBorrarProducto')?.addEventListener('click', borrarProductoModal);
        
        // Gestión Excel
        document.getElementById('btnDescargarFormato')?.addEventListener('click', descargarFormatoExcel);
        document.getElementById('btnCargarExcel')?.addEventListener('click', function() {
            let input = document.createElement('input');
            input.type = 'file';
            input.accept = '.xlsx, .xls';
            input.onchange = function(e) {
                let file = e.target.files[0];
                if (!file) return;
                let formData = new FormData();
                formData.append('excel_file', file);
                formData.append('_token', '{{ csrf_token() }}');
                Swal.fire({ title: 'Cargando archivo...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                const facturaId = '{{ $facturaDTO->ID }}';
                const url = '{{ route("cpanel.facturas.upload.excel", $facturaDTO->ID) }}';
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        llenarTablaModalConProductos(data.detalles);
                        Swal.fire({ title: 'Éxito', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al cargar el archivo', 'error');
                });
            };
            input.click();
        });
        
        document.getElementById('btnGuardarExcel')?.addEventListener('click', guardarExcelModal);
        
        // Seleccionar todos
        document.getElementById('seleccionarTodos')?.addEventListener('change', function() {
            document.querySelectorAll('.select-producto').forEach(cb => cb.checked = this.checked);
            actualizarTotalProductos();
        });
        
        actualizarTotalProductos();
        document.getElementById('costo')?.addEventListener('input', calcularTotalModal);
        document.getElementById('cantidad')?.addEventListener('input', calcularTotalModal);
    }
    
    function llenarTablaModalConProductos(productos) {
        let tbody = document.getElementById('tablaProductosBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        productos.forEach(producto => {
            let uxe = producto.UxE || producto.uxe || 1;
            let cantidadEmpaques = producto.Cantidad || producto.cantidad || 0;
            let costoTotal = producto.Costo || producto.costo || 0;  // ← Este es el costo TOTAL del Excel
            
            // ✅ Calcular costo unitario solo para mostrar
            let totalUnidades = cantidadEmpaques * uxe;
            let costoUnitario = costoTotal / totalUnidades;
            
            let empaqueTexto = uxe == 1 ? 'Unidad' : (uxe == 12 ? 'Docena' : `Empaque x${uxe}`);
            
            let row = tbody.insertRow();
            row.innerHTML = `
                <td class="text-center"><input type="checkbox" class="select-producto"></td>
                <td><code>${producto.Codigo || producto.codigo}</code></td>
                <td>${producto.Descripcion || producto.descripcion}</td>
                <td>${empaqueTexto}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>      <!-- Costo unitario para mostrar -->
                <td class="text-end">${totalUnidades.toFixed(2)}</td>       <!-- Total unidades -->
                <td class="text-end">$${costoTotal.toFixed(2)}</td>         <!-- Costo TOTAL a guardar -->
            `;
            
            // ✅ Guardar también el costo total en un data attribute o campo oculto
            row.setAttribute('data-costo-total', costoTotal);
        });
        
        actualizarTotalProductos();
    }
    
    function actualizarTotalProductos() {
        let tbody = document.getElementById('tablaProductosBody');
        let total = tbody.querySelectorAll('tr').length;
        let span = document.getElementById('totalProductosCount');
        if (span) span.innerText = total + ' productos';
    }
    
    function descargarFormatoExcel() {
        const url = '{{ asset("formato_excel/EntradaFactura.xlsx") }}';
        const link = document.createElement('a');
        link.href = url;
        link.download = 'EntradaFactura.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    function calcularTotalModal() {
        let costo = parseFloat(document.getElementById('costo')?.value) || 0;
        let cantidad = parseFloat(document.getElementById('cantidad')?.value) || 0;
        document.getElementById('total').value = (costo * cantidad).toFixed(2);
    }
    
    function limpiarFormularioProductoModal() {
        document.getElementById('codigo_producto').value = '';
        document.getElementById('descripcion_producto').value = '';
        document.getElementById('costo').value = '';
        document.getElementById('cantidad').value = '';
        document.getElementById('total').value = '';
    }
    
    function guardarProductoModal() {
        let producto = {
            codigo: document.getElementById('codigo_producto').value,
            descripcion: document.getElementById('descripcion_producto').value,
            producto_id: document.getElementById('producto_id')?.value || null,
            empaque: document.getElementById('empaque').value,
            costo: parseFloat(document.getElementById('costo').value) || 0,
            cantidad: parseFloat(document.getElementById('cantidad').value) || 0
        };
        if (!producto.codigo || !producto.descripcion) {
            Swal.fire('Error', 'Debe buscar un producto válido', 'warning');
            return;
        }
        if (!producto.producto_id) {
            Swal.fire('Error', 'Producto no válido, búsquelo nuevamente', 'warning');
            return;
        }
        if (producto.cantidad <= 0) {
            Swal.fire('Error', 'La cantidad debe ser mayor a 0', 'warning');
            return;
        }
        Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        const facturaId = '{{ $facturaDTO->ID }}';
        const url = '{{ route("cpanel.facturas.agregar.producto", $facturaDTO->ID) }}';
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ producto_id: producto.producto_id, cantidad: producto.cantidad, costo: producto.costo, empaque: producto.empaque })
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                actualizarTablaProductosFactura(response.detalles, response.total_factura);
                Swal.fire('Éxito', response.message, 'success').then(() => {
                    limpiarFormularioProductoModal();
                    cargarProductosEnModal(response.detalles);
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al guardar el producto', 'error');
        });
    }
    
    function actualizarTablaProductosFactura(detalles, totalFactura) {
        let tbody = document.querySelector('#tablaProductos tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        detalles.forEach(detalle => {
            // ✅ Calcular correctamente
            let totalUnidades = (detalle.CantidadEmitida || 0) * (detalle.UxE || 1);
            let costoUnitario = (detalle.CostoDivisa || 0) / (totalUnidades > 0 ? totalUnidades : 1);
            let totalUSD = detalle.CostoDivisa || 0;
            
            let row = tbody.insertRow();
            row.innerHTML = `
                <td><img src="${detalle.UrlFoto ? '/storage/images/items/thumbs/' + detalle.UrlFoto : '/assets/img/adminlte/img/produc_default.jfif'}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td>${detalle.Codigo || 'N/A'}</td>
                <td>${detalle.Referencia || 'N/A'}</td>
                <td>${detalle.producto_nombre || 'N/A'}</td>
                <td class="text-end">${totalUnidades.toFixed(2)}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>
                <td class="text-end">$${totalUSD.toFixed(2)}</td>
            `;
        });
        
        let totalSpan = document.querySelector('#tablaProductos tfoot .text-end:last-child');
        if (totalSpan) totalSpan.innerText = `$${parseFloat(totalFactura).toFixed(2)}`;
    }
    
    function cargarProductosEnModal(detalles) {
        let tbody = document.getElementById('tablaProductosBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        detalles.forEach(detalle => {
            // ✅ Calcular correctamente para el modal
            let totalUnidades = (detalle.CantidadEmitida || 0) * (detalle.UxE || 1);
            let costoUnitario = (detalle.CostoDivisa || 0) / (totalUnidades > 0 ? totalUnidades : 1);
            let totalUSD = detalle.CostoDivisa || 0;
            
            let empaqueTexto = '';
            if (detalle.UxE == 1) empaqueTexto = 'Unidad';
            else if (detalle.UxE == 12) empaqueTexto = 'Docena';
            else empaqueTexto = `Empaque x${detalle.UxE}`;
            
            let row = tbody.insertRow();
            row.innerHTML = `
                <td class="text-center"><input type="checkbox" class="select-producto" value="${detalle.ID}"></td>
                <td><code>${detalle.Codigo || 'N/A'}</code></td>
                <td>${detalle.producto_nombre || 'N/A'}</td>
                <td>${empaqueTexto}</td>
                <td class="text-end">$${costoUnitario.toFixed(2)}</td>
                <td class="text-end">${totalUnidades.toFixed(2)}</td>
                <td class="text-end">$${totalUSD.toFixed(2)}</td>
            `;
        });
        
        actualizarTotalProductos();
    }
    
    function editarProductoModal() {
        let selected = document.querySelectorAll('.select-producto:checked');
        if (selected.length !== 1) {
            Swal.fire('Error', 'Seleccione un solo producto para editar', 'warning');
            return;
        }
        let row = selected[0].closest('tr');
        let cells = row.cells;
        document.getElementById('codigo_producto').value = cells[1].innerText;
        document.getElementById('descripcion_producto').value = cells[2].innerText;
        document.getElementById('empaque').value = cells[3].innerText;
        document.getElementById('costo').value = parseFloat(cells[4].innerText.replace('$', ''));
        document.getElementById('cantidad').value = parseFloat(cells[5].innerText);
        calcularTotalModal();
        row.remove();
    }
    
    function borrarProductoModal() {
        let selected = document.querySelectorAll('.select-producto:checked');
        if (selected.length === 0) {
            Swal.fire('Error', 'Seleccione al menos un producto para eliminar', 'warning');
            return;
        }
        Swal.fire({
            title: '¿Eliminar producto(s)?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                selected.forEach(cb => cb.closest('tr').remove());
                Swal.fire('Eliminado', 'Producto(s) eliminados', 'success');
                actualizarTotalProductos();
            }
        });
    }
    
    function guardarExcelModal() {
        let productos = [];
        let tbody = document.getElementById('tablaProductosBody');
        let rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            let cells = row.cells;
            if (cells.length >= 7) {
                let producto = {
                    codigo: cells[1].innerText,
                    descripcion: cells[2].innerText,
                    empaque: cells[3].innerText,  // ← Ver qué valor tiene
                    costo: parseFloat(cells[6].innerText.replace('$', '')),  // Total USD
                    costo_unitario: parseFloat(cells[4].innerText.replace('$', '').trim()),  // Costo Unitario
                    cantidad: parseFloat(cells[5].innerText)  // Cantidad (Unidades)
                };
                productos.push(producto);
                console.log('Producto enviado:', producto);  // ← Ver en consola
            }
        });
        if (productos.length === 0) {
            Swal.fire('Error', 'No hay productos para guardar', 'warning');
            return;
        }
        Swal.fire({ title: 'Guardando productos...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        const url = '{{ route("cpanel.facturas.guardar.excel", $facturaDTO->ID) }}';
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ productos: productos, factura_id: '{{ $facturaDTO->ID }}' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    window.location.href = '{{ route("cpanel.proveedores.detalle", $facturaDTO->ProveedorId) }}';
                });
            } else {
                Swal.fire('Error', data.message || 'Error al guardar productos', 'error');
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    .sticky-top { position: sticky; top: 0; z-index: 10; }
    .img-thumbnail { border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-thumbnail:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0,0,0,0.2); cursor: pointer; }
    .image-zoom-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; }
    .image-zoom-container { position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; }
    .image-zoom-container img { max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .image-zoom-close { position: absolute; top: -40px; right: -10px; color: white; font-size: 40px; cursor: pointer; background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .image-zoom-close:hover { color: #ff6b6b; }
    .image-description { color: white; text-align: center; margin-top: 20px; background: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 8px; }
    .nav-tabs .nav-link { font-weight: 500; }
    .nav-tabs .nav-link.active { border-top: 3px solid #007bff; }
    .card-header-tabs { margin-right: -1rem; margin-left: -1rem; border-bottom: 0; }
    .bg-gradient-primary { background: linear-gradient(135deg, #1e5799 0%, #2b8c5e 100%); }
</style>
@endpush