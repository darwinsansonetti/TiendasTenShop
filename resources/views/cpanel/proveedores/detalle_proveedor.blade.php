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
                    <i class="fas fa-truck me-2"></i>Detalle del Proveedor
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
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
                    <a href="{{ route('cpanel.proveedores.editar', $proveedor->ProveedorId) }}" 
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <a href="{{ route('cpanel.proveedor.mercancia.listado') }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
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
                        @if($banco)
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <small class="text-muted">
                                    <i class="fas fa-university me-1"></i>
                                    Banco: {{ $banco->Nombre }}
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- NUEVO CARD-BODY PARA BOTONES -->
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cpanel.proveedores.pagar', $proveedor->ProveedorId) }}" 
                        class="btn btn-outline-success btn-sm" 
                        title="Registrar Pago"
                        data-bs-toggle="tooltip">
                            <i class="bi bi-cash-stack me-1"></i> Registrar Pago
                    </a>
                    <button type="button" 
                            class="btn btn-primary btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalCrearFactura">
                        <i class="fas fa-file-invoice me-1"></i>Crear Factura
                    </button>
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

        <!-- TABS -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estado-cuenta-tab" data-bs-toggle="tab" 
                                data-bs-target="#estado-cuenta" type="button" role="tab">
                            <i class="fas fa-chart-line me-1"></i>Estado de Cuenta
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="facturas-tab" data-bs-toggle="tab" 
                                data-bs-target="#facturas" type="button" role="tab">
                            <i class="fas fa-file-invoice me-1"></i>Facturas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" 
                                data-bs-target="#pagos" type="button" role="tab">
                            <i class="fas fa-hand-holding-usd me-1"></i>Pagos
                        </button>
                    </li>
                    @if($proveedor->Tipo == 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="productos-tab" data-bs-toggle="tab" 
                                data-bs-target="#productos" type="button" role="tab">
                            <i class="fas fa-boxes me-1"></i>Productos
                        </button>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    
                    <!-- ============================================ -->
                    <!-- TAB: Estado de Cuenta -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade show active" id="estado-cuenta" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="pdfTablaEstadoCuenta()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportarExcelEstadoCuenta()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaEstadoCuenta">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Referencia</th>
                                        <th class="text-end">Monto USD</th>
                                        <th class="text-end">Pago USD</th>
                                        <th class="text-end">Saldo USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($estadoCuenta['operaciones'] as $op)
                                    <tr>
                                        <td>{{ $op->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            @if($op->tipo == 'factura')
                                                <i class="fas fa-file-invoice text-primary me-1"></i>
                                            @else
                                                <i class="fas fa-money-bill-wave text-success me-1"></i>
                                            @endif
                                            {{ $op->descripcion }}
                                        </td>
                                        <td>{{ $op->referencia }}</td>
                                        <td class="text-end">
                                            @if($op->monto_divisa > 0)
                                                $ {{ number_format($op->monto_divisa, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($op->pago_divisa > 0)
                                                $ {{ number_format($op->pago_divisa, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            $ {{ number_format($op->saldo_divisa, 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay operaciones registradas</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">TOTALES:</td>
                                        <td class="text-end">$ {{ number_format($balanceFacturas->totalFacturas, 2) }}</td>
                                        <td class="text-end">$ {{ number_format($balanceFacturas->totalPagado, 2) }}</td>
                                        <td class="text-end">$ {{ number_format($balanceFacturas->saldoPendiente, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- TAB: Facturas -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade" id="facturas" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('cpanel.proveedores.recibo-facturas', $proveedor->ProveedorId) }}" 
                                class="btn btn-outline-secondary btn-sm"
                                title="Generar recibo de facturas en PDF"
                                target="_blank"
                                data-bs-toggle="tooltip">
                                    <i class="fas fa-print me-1"></i>PDF
                                </a>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportarExcelFacturas()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaFacturas">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha Emisión</th>
                                        <th class="text-end">Total USD</th>
                                        <th class="text-end">Pagado USD</th>
                                        <th class="text-end">Saldo USD</th>
                                        <th>Estatus</th>
                                        <th class="text-center" style="width: 150px;">Acciones</th> <!-- NUEVA COLUMNA -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($facturasVigentes as $factura)
                                    <tr>
                                        <td>{{ $factura->Numero }}</td>
                                        <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}</td>
                                        <td class="text-end">$ {{ number_format($factura->MontoDivisa, 2) }}</td>
                                        <td class="text-end">$ {{ number_format($factura->total_pagado, 2) }}</td>
                                        <td class="text-end fw-bold">$ {{ number_format($factura->saldo_pendiente, 2) }}</td>
                                        <td>
                                            @php
                                                $estatusTexto = '';
                                                $estatusColor = '';
                                                switch($factura->Estatus) {
                                                    case 1: $estatusTexto = 'En Proceso'; $estatusColor = 'warning'; break;
                                                    case 2: $estatusTexto = 'Recibiendo'; $estatusColor = 'info'; break;
                                                    case 4: $estatusTexto = 'Recibida'; $estatusColor = 'success'; break;
                                                    default: $estatusTexto = 'Desconocido'; $estatusColor = 'secondary';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $estatusColor }}">{{ $estatusTexto }}</span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botones de Acción (siguiendo el mismo estilo que proveedores) -->
                                            <div class="btn-group btn-group-sm" role="group">
                                                
                                                <!-- Botón Detalle -->
                                                <a href="{{ route('cpanel.facturas.detalle', $factura->ID) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="Ver detalle de factura"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <a href="{{ route('cpanel.facturas.editar', $factura->ID) }}"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Editar factura"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <!-- Botón Eliminar (solo si tiene saldo pendiente = 0 o está en proceso) -->
                                                @if($factura->saldo_pendiente == 0 || $factura->Estatus == 1)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="eliminarFactura({{ $factura->ID }}, '{{ $factura->Numero }}')"
                                                        title="Eliminar factura"
                                                        data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @else
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary" 
                                                        disabled
                                                        title="No se puede eliminar (Tiene saldo pendiente)"
                                                        data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @endif
                                                
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay facturas registradas</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- TAB: Pagos -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade" id="pagos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="pdfTablaPagos()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportarExcelPagos()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaPagos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Número Operación</th>
                                        <th class="text-end">Monto USD</th>
                                        <th class="text-end">Monto Bs</th>
                                        <th class="text-end">Tasa</th>
                                        <th class="text-center">Estatus</th>
                                        <th class="text-center" style="width: 180px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transaccionesVigentes as $transaccion)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($transaccion->Fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $transaccion->Descripcion ?? 'Pago registrado' }}</td>
                                        <td>{{ $transaccion->NumeroOperacion ?? 'N/A' }}</td>
                                        <td class="text-end">$ {{ number_format($transaccion->MontoDivisa, 2) }}</td>
                                        <td class="text-end">Bs {{ number_format($transaccion->MontoBs, 2) }}</td>
                                        <td class="text-end">{{ number_format($transaccion->Tasa, 2) }}</td>
                                        <td class="text-center">
                                            @php
                                                $estatusTexto = '';
                                                $estatusColor = '';
                                                $estatusNumero = (int)($transaccion->Estatus ?? 0);
                                                switch($estatusNumero) {
                                                    case 2: $estatusTexto = 'Pagada'; $estatusColor = 'success'; break;
                                                    case 4: $estatusTexto = 'Cerrada'; $estatusColor = 'secondary'; break;
                                                    default: $estatusTexto = 'Pendiente'; $estatusColor = 'warning';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $estatusColor }}">{{ $estatusTexto }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- Ver Detalle -->
                                                <a href="{{ route('cpanel.pagos.detalle', $transaccion->TransaccionId) }}" 
                                                class="btn btn-sm btn-outline-info"
                                                title="Ver detalle del pago"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <!-- Editar (solo si Estatus es 1 o 2) -->
                                                @if(in_array($estatusNumero, [1, 2]))
                                                <a href="{{ route('cpanel.pagos.editar', $transaccion->TransaccionId) }}" 
                                                class="btn btn-sm btn-outline-warning"
                                                title="Editar pago"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @else
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary" 
                                                        disabled
                                                        title="No se puede editar (Pago {{ $estatusTexto }})"
                                                        data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @endif
                                                
                                                <!-- Imprimir Recibo -->
                                                <a href="{{ route('cpanel.pagos.imprimir', $transaccion->TransaccionId) }}" 
                                                class="btn btn-sm btn-outline-success"
                                                title="Imprimir recibo de pago"
                                                target="_blank"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                
                                                <!-- Eliminar -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="eliminarPago({{ $transaccion->TransaccionId }}, '{{ $transaccion->NumeroOperacion }}')"
                                                        title="Eliminar pago"
                                                        data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No hay pagos registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- TAB: Productos -->
                    <!-- ============================================ -->
                    @if($proveedor->Tipo == 0)
                    <div class="tab-pane fade" id="productos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="pdfTablaProductos()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportarExcelProductos()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-sm" id="tablaProductos">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th style="width: 80px;">Foto</th>
                                        <th style="width: 120px;">Código</th>
                                        <th style="width: 120px;">Referencia</th>
                                        <th style="min-width: 300px;">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productos as $producto)
                                    @php
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/items/thumbs/',
                                            $producto->UrlFoto ?? '',
                                            'assets/img/adminlte/img/produc_default.jfif'
                                        );
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <img src="{{ $imgSrc }}" 
                                                alt="{{ $producto->Codigo }}"
                                                class="rounded border img-zoomable"
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                                onclick="zoomImagen(this)"
                                                data-full-image="{{ $imgSrc }}"
                                                data-description="{{ $producto->Nombre }}">
                                        </td>
                                        <td><code>{{ $producto->Codigo ?? 'N/A' }}</code></td>
                                        <td>{{ $producto->Referencia ?? 'N/A' }}</td>
                                        <td class="text-wrap" style="word-wrap: break-word; white-space: normal;">
                                            {{ $producto->Nombre }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay productos registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-boxes me-1"></i>
                                Total productos: {{ $productos->count() }}
                            </small>
                        </div>
                    </div>
                    @endif
                    
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

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // ============================================
        // ZOOM DE IMÁGENES
        // ============================================
        document.querySelectorAll('.img-zoomable').forEach(img => {
            img.addEventListener('click', function() {
                const fullImage = this.getAttribute('data-full-image');
                const description = this.getAttribute('data-description');
                
                document.getElementById('zoomedImage').src = fullImage;
                document.getElementById('imageDescription').textContent = description;
                document.getElementById('imageZoomOverlay').style.display = 'flex';
                
                // Prevenir scroll del body
                document.body.style.overflow = 'hidden';
            });
        });
    });
    
    // ============================================
    // FUNCIONES DE ZOOM
    // ============================================
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeZoom();
        }
    });
    
    // Cerrar al hacer clic fuera de la imagen
    document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeZoom();
        }
    });
    
    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');
        
        document.getElementById('zoomedImage').src = fullImage;
        document.getElementById('imageDescription').textContent = description;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // ============================================
    // ESTADO DE CUENTA
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

    // ============================================
    // FACTURAS
    // ============================================
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

    // ============================================
    // PAGOS
    // ============================================
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

    // ============================================
    // PRODUCTOS
    // ============================================
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
    // FUNCIONES PARA FACTURAS
    // ============================================

    // Ver detalle de factura
    function verDetalleFactura(facturaId) {
        // Puedes abrir un modal con los detalles o redirigir a otra página
        Swal.fire({
            title: 'Cargando...',
            text: 'Obteniendo detalles de la factura',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                // Llamada AJAX para obtener los detalles
                fetch(`/cpanel/facturas/${facturaId}/detalle`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar modal con los detalles
                        mostrarModalDetalleFactura(data.factura);
                    } else {
                        Swal.fire('Error', data.message || 'Error al cargar los detalles', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // Editar factura
    function editarFactura(facturaId) {
        Swal.fire({
            title: '¿Editar factura?',
            text: "Podrás modificar los datos de la factura",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, editar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir a la página de edición
                window.location.href = `/cpanel/facturas/${facturaId}/editar`;
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
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Llamada AJAX para eliminar
                fetch(`/cpanel/facturas/${facturaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
                            // Recargar la página para actualizar la lista
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

    // Modal para mostrar detalle de factura (opcional)
    function mostrarModalDetalleFactura(factura) {
        // Puedes implementar un modal bonito con Bootstrap
        // Por ahora usamos SweetAlert2 con HTML
        let htmlContent = `
            <div style="text-align: left;">
                <p><strong>Número:</strong> ${factura.Numero}</p>
                <p><strong>Fecha:</strong> ${new Date(factura.FechaCreacion).toLocaleDateString('es-VE')}</p>
                <p><strong>Total USD:</strong> $ ${Number(factura.MontoDivisa).toFixed(2)}</p>
                <p><strong>Pagado:</strong> $ ${Number(factura.total_pagado).toFixed(2)}</p>
                <p><strong>Saldo Pendiente:</strong> $ ${Number(factura.saldo_pendiente).toFixed(2)}</p>
                <p><strong>Estatus:</strong> ${getEstatusTexto(factura.Estatus)}</p>
            </div>
        `;
        
        Swal.fire({
            title: 'Detalle de Factura',
            html: htmlContent,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // Helper para obtener texto de estatus
    function getEstatusTexto(estatus) {
        switch(estatus) {
            case 1: return 'En Proceso';
            case 2: return 'Recibiendo';
            case 4: return 'Recibida';
            default: return 'Desconocido';
        }
    }

    // ============================================
    // FUNCIÓN PARA ELIMINAR PAGO
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
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch(`/cpanel/pagos/${pagoId}`, {
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
                            title: '¡Eliminado!',
                            text: 'El pago ha sido eliminado correctamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
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

    /* Tus estilos existentes ya incluyen estas clases */
    .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
    }

    .btn-outline-info:hover {
        color: #000;
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }

    /* Para botones deshabilitados */
    .btn-outline-secondary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush