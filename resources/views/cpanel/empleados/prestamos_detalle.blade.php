@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Detalle de Préstamos')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                  <i class="bi bi-bank2 text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Préstamos</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">
                    <i class="bi bi-person me-1"></i>{{ $empleado->NombreCompleto }}
                    &nbsp;·&nbsp;<i class="bi bi-shop me-1"></i>{{ $sucursal->Nombre ?? 'N/A' }}
                    &nbsp;·&nbsp;ID: {{ $empleado->VendedorId ?? 'N/A' }}
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.dashboard') }}">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.empleados.lista_empleados_prestamos') }}">Préstamos</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 class="text-white">{{ $resumen['total_prestamos'] }}</h3>
                        <p class="text-white">Total Préstamos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 class="text-white">${{ number_format($resumen['total_monto'], 2) }}</h3>
                        <p class="text-white">Monto Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 class="text-white">${{ number_format($resumen['total_pagado'], 2) }}</h3>
                        <p class="text-white">Total Abonado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 class="text-white">${{ number_format($resumen['total_pendiente'], 2) }}</h3>
                        <p class="text-white">Total Pendiente</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="prestamosTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab">
                            <i class="fas fa-list me-1"></i>Todos ({{ $resumen['total_prestamos'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nuevos-tab" data-bs-toggle="tab" data-bs-target="#nuevos" type="button" role="tab">
                            <i class="fas fa-spinner me-1"></i>Nuevos ({{ $resumen['prestamos_nuevos'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="proceso-tab" data-bs-toggle="tab" data-bs-target="#proceso" type="button" role="tab">
                            <i class="fas fa-sync-alt me-1"></i>En Proceso ({{ $resumen['prestamos_proceso'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pagados-tab" data-bs-toggle="tab" data-bs-target="#pagados" type="button" role="tab">
                            <i class="fas fa-check-circle me-1"></i>Pagados ({{ $resumen['prestamos_pagados'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="productos-tab" data-bs-toggle="tab" data-bs-target="#productos" type="button" role="tab">
                            <i class="fas fa-box me-1"></i>Productos Pendientes ({{ $productosPendientes->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab">
                            <i class="fas fa-chart-pie me-1"></i>Resumen General
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="prestamosTabsContent">
                    
                    <!-- Tab: Todos los préstamos -->
                    <div class="tab-pane fade show active" id="todos" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-sm btn-primary" onclick="exportarTablaPDF('todos')">
                                <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablaTodos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Monto Divisa</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                        <th>Saldo</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prestamos as $prestamo)
                                    <tr>
                                        <td>{{ $prestamo->PrestamoId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa + $prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_pagado, 2) }}</td>
                                        <td class="text-end"><strong>${{ number_format($prestamo->saldo_pendiente, 2) }}</strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $prestamo->estatus_color }}">{{ $prestamo->estatus_texto }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-info" onclick="verDetallePrestamo({{ $prestamo->PrestamoId }})" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab: Nuevos -->
                    <div class="tab-pane fade" id="nuevos" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Monto Divisa</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prestamos->where('Estatus', 1) as $prestamo)
                                    <tr>
                                        <td>{{ $prestamo->PrestamoId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa + $prestamo->total_productos, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-warning">Nuevo</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab: En Proceso -->
                    <div class="tab-pane fade" id="proceso" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Monto Divisa</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prestamos->where('Estatus', 2) as $prestamo)
                                    <tr>
                                        <td>{{ $prestamo->PrestamoId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa + $prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_pagado, 2) }}</td>
                                        <td class="text-end"><strong>${{ number_format($prestamo->saldo_pendiente, 2) }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab: Pagados -->
                    <div class="tab-pane fade" id="pagados" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Monto Divisa</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prestamos->where('Estatus', 3) as $prestamo)
                                    <tr>
                                        <td>{{ $prestamo->PrestamoId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prestamo->Fecha)->format('d/m/Y') }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->MontoDivisa + $prestamo->total_productos, 2) }}</td>
                                        <td class="text-end">${{ number_format($prestamo->total_pagado, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab: Productos Pendientes -->
                    <div class="tab-pane fade" id="productos" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th></th>
                                        <th>Préstamo ID</th>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio USD</th>
                                        <th>Total USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalCosto = 0;
                                        $totalVentas = 0;
                                        $totalUtilidad = 0;
                                    @endphp
                                    @foreach($prestamos->where('Estatus', 2) as $prestamo)
                                        @foreach($prestamo->detalles as $detalle)

                                        @php
                                            $urlImagen = FileHelper::getOrDownloadFile(
                                                'images/items/thumbs/',
                                                $detalle->ProductoImagen ?? '',
                                                'assets/img/adminlte/img/produc_default.jfif'
                                            );
                                        @endphp

                                        <tr>
                                            <td class="text-center">
                                                <div class="position-relative">
                                                    <img src="{{ $urlImagen }}" 
                                                        alt="{{ $detalle->ProductoDescripcion }}"
                                                        class="img-thumbnail rounded img-zoomable" 
                                                        style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                                        data-full-image="{{ $urlImagen }}"
                                                        data-description="{{ $detalle->ProductoDescripcion }}"
                                                        title="{{ $detalle->ProductoDescripcion }}"
                                                        onerror="this.onerror=null; this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}';">
                                                </div>
                                            </td>
                                            <td>{{ $prestamo->PrestamoId }}</td>
                                            <td>{{ $detalle->ProductoCodigo ?? 'N/A' }}</td>
                                            <td>{{ $detalle->ProductoDescripcion ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $detalle->Cantidad }}</td>
                                            <td class="text-end">${{ number_format($detalle->PvpDivisa, 2) }}</td>
                                            <td class="text-end">${{ number_format($detalle->Cantidad * $detalle->PvpDivisa, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Resumen General -->
                    <div class="tab-pane fade" id="resumen" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-sm btn-danger" onclick="exportarResumenPDF()">
                                <i class="fas fa-file-pdf me-1"></i>Exportar Resumen PDF
                            </button>
                        </div>
                        
                        <div class="row" id="resumenContenido">
                            <!-- Información del Empleado -->
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <strong><i class="fas fa-user me-2"></i>Información del Empleado</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="35%">Nombre:</th><td>{{ $empleado->NombreCompleto }}</td></tr>
                                                    <tr><th>Vendedor ID:</th><td>{{ $empleado->VendedorId ?? 'N/A' }}</td></tr>
                                                    <tr><th>Email:</th><td>{{ $empleado->Email ?? 'N/A' }}</td></tr>
                                                    <tr><th>Teléfono:</th><td>{{ $empleado->PhoneNumber ?? 'N/A' }}</td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm table-borderless">
                                                    <tr><th width="35%">Sucursal:</th><td>{{ $sucursal->Nombre ?? 'N/A' }}</td></tr>
                                                    <tr><th>Dirección:</th><td>{{ $empleado->Direccion ?? 'N/A' }}</td></tr>
                                                    <tr><th>Fecha Registro:</th><td>{{ \Carbon\Carbon::parse($empleado->FechaCreacion)->format('d/m/Y') }}</td></tr>
                                                    <tr><th>Estatus:</th><td><span class="badge bg-success">Activo</span></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen de Préstamos -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <strong><i class="fas fa-chart-bar me-2"></i>Resumen de Préstamos</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-bordered">
                                            <tbody>
                                                <tr><th>Total Préstamos Solicitados:</th>
                                                    <td class="text-end fw-bold">{{ $resumen['total_prestamos'] }}</td>
                                                </tr>
                                                <tr><th>Préstamos Nuevos:</th>
                                                    <td class="text-end">{{ $resumen['prestamos_nuevos'] }}</td>
                                                </tr>
                                                <tr><th>Préstamos en Proceso:</th>
                                                    <td class="text-end">{{ $resumen['prestamos_proceso'] }}</td>
                                                </tr>
                                                <tr><th>Préstamos Pagados:</th>
                                                    <td class="text-end">{{ $resumen['prestamos_pagados'] }}</td>
                                                </tr>
                                                <tr><th>Préstamos Incluidos:</th>
                                                    <td class="text-end">{{ $resumen['prestamos_incluidos'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen de Montos -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <strong><i class="fas fa-dollar-sign me-2"></i>Resumen de Montos</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-bordered">
                                            <tbody>
                                                <tr><th>Monto Total Préstamos:</th>
                                                    <td class="text-end fw-bold text-success">${{ number_format($resumen['total_monto'], 2) }}</td>
                                                </tr>
                                                <tr><th>Total Abonado:</th>
                                                    <td class="text-end text-primary">${{ number_format($resumen['total_pagado'], 2) }}</td>
                                                </tr>
                                                <tr><th>Total Pendiente:</th>
                                                    <td class="text-end fw-bold text-danger">${{ number_format($resumen['total_pendiente'], 2) }}</td>
                                                </tr>
                                                <tr><th>Porcentaje Pagado:</th>
                                                    <td class="text-end">{{ $resumen['total_monto'] > 0 ? number_format(($resumen['total_pagado'] / $resumen['total_monto']) * 100, 1) : 0 }}%</td>
                                                </tr>
                                                <tr><th>Porcentaje Pendiente:</th>
                                                    <td class="text-end">{{ $resumen['total_monto'] > 0 ? number_format(($resumen['total_pendiente'] / $resumen['total_monto']) * 100, 1) : 0 }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen de Productos -->
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <strong><i class="fas fa-boxes me-2"></i>Resumen de Productos</strong>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $totalProductos = 0;
                                            $totalProductosPagados = 0;
                                            $totalProductosPendientes = 0;
                                            $montoProductos = 0;
                                            $montoProductosPagados = 0;
                                            $montoProductosPendientes = 0;
                                            
                                            foreach($prestamos as $prestamo) {
                                                foreach($prestamo->detalles as $detalle) {
                                                    $totalProductos++;
                                                    $montoProductos += $detalle->Cantidad * $detalle->PvpDivisa;
                                                    
                                                    if($prestamo->Estatus == 3) {
                                                        $totalProductosPagados++;
                                                        $montoProductosPagados += $detalle->Cantidad * $detalle->PvpDivisa;
                                                    } elseif($prestamo->Estatus == 2) {
                                                        $totalProductosPendientes++;
                                                        $montoProductosPendientes += $detalle->Cantidad * $detalle->PvpDivisa;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-4">
                                                <table class="table table-sm table-bordered">
                                                    <tbody>
                                                        <tr><th>Total Productos:</th>
                                                            <td class="text-end fw-bold">{{ $totalProductos }}</td>
                                                        </tr>
                                                        <tr><th>Productos Pagados:</th>
                                                            <td class="text-end text-success">{{ $totalProductosPagados }}</td>
                                                        </tr>
                                                        <tr><th>Productos Pendientes:</th>
                                                            <td class="text-end text-warning">{{ $totalProductosPendientes }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-4">
                                                <table class="table table-sm table-bordered">
                                                    <tbody>
                                                        <tr><th>Monto Productos:</th>
                                                            <td class="text-end fw-bold">${{ number_format($montoProductos, 2) }}</td>
                                                        </tr>
                                                        <tr><th>Monto Pagado Productos:</th>
                                                            <td class="text-end text-success">${{ number_format($montoProductosPagados, 2) }}</td>
                                                        </tr>
                                                        <tr><th>Monto Pendiente Productos:</th>
                                                            <td class="text-end text-warning">${{ number_format($montoProductosPendientes, 2) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="progress mb-2" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                        style="width: {{ $totalProductos > 0 ? ($totalProductosPagados / $totalProductos) * 100 : 0 }}%">
                                                        Productos Pagados: {{ $totalProductos > 0 ? number_format(($totalProductosPagados / $totalProductos) * 100, 1) : 0 }}%
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" 
                                                        style="width: {{ $totalProductos > 0 ? ($totalProductosPendientes / $totalProductos) * 100 : 0 }}%">
                                                        Productos Pendientes: {{ $totalProductos > 0 ? number_format(($totalProductosPendientes / $totalProductos) * 100, 1) : 0 }}%
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

<!-- Modal para ver detalles del préstamo -->
<div class="modal fade" id="modalDetallePrestamo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle del Préstamo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallePrestamoBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info"></div>
                    <p>Cargando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetallePrestamo()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    let prestamosData = @json($prestamos);
    
    function verDetallePrestamo(prestamoId) {
        const prestamo = prestamosData.find(p => p.PrestamoId == prestamoId);
        
        if (!prestamo) {
            Swal.fire('Error', 'Préstamo no encontrado', 'error');
            return;
        }
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <strong>Información del Préstamo</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr><th>ID Préstamo:</th><td>${prestamo.PrestamoId}</td></tr>
                                <tr><th>Fecha:</th><td>${new Date(prestamo.Fecha).toLocaleDateString('es-VE')}</td></tr>
                                <tr><th>Monto Divisa:</th><td>$${parseFloat(prestamo.MontoDivisa || 0).toFixed(2)}</td></tr>
                                <tr><th>Total Productos:</th><td>$${(prestamo.total_productos || 0).toFixed(2)}</td></tr>
                                <tr><th>Total Préstamo:</th><td>$${(parseFloat(prestamo.MontoDivisa || 0) + (prestamo.total_productos || 0)).toFixed(2)}</td></tr>
                                <tr><th>Total Pagado:</th><td>$${(prestamo.total_pagado || 0).toFixed(2)}</td></tr>
                                <tr><th>Saldo Pendiente:</th><td><strong>$${(prestamo.saldo_pendiente || 0).toFixed(2)}</strong></td></tr>
                                <tr><th>Estatus:</th><td><span class="badge bg-${prestamo.estatus_color}">${prestamo.estatus_texto}</span></td></tr>
                                <tr><th>Observación:</th><td>${prestamo.Observacion || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <strong>Productos del Préstamo</strong>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr><th>Producto</th><th>Cantidad</th><th>Precio USD</th><th>Total</th></tr>
                                </thead>
                                <tbody>
                                    ${prestamo.detalles && prestamo.detalles.length > 0 ? prestamo.detalles.map(d => `
                                        <tr>
                                            <td>${d.ProductoDescripcion || 'N/A'}</td>
                                            <td class="text-center">${d.Cantidad}</td>
                                            <td class="text-end">$${parseFloat(d.PvpDivisa || 0).toFixed(2)}</td>
                                            <td class="text-end">$${(d.Cantidad * d.PvpDivisa).toFixed(2)}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" class="text-center">Sin productos</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <strong>Historial de Pagos</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Fecha</th><th>Monto USD</th><th>Forma de Pago</th><th>N° Operación</th><th>Observación</th></tr>
                        </thead>
                        <tbody>
                            ${prestamo.pagos && prestamo.pagos.length > 0 ? prestamo.pagos.map(p => {
                                let formaPago = '';
                                switch(p.FormaDePago) {
                                    case 0: formaPago = 'Efectivo'; break;
                                    case 2: formaPago = 'Depósito'; break;
                                    case 3: formaPago = 'Transferencia'; break;
                                    default: formaPago = 'Desconocido';
                                }
                                return `
                                    <tr>
                                        <td>${new Date(p.Fecha).toLocaleDateString('es-VE')}</td>
                                        <td class="text-end">$${parseFloat(p.MontoDivisaAbonado || 0).toFixed(2)}</td>
                                        <td>${formaPago}</td>
                                        <td>${p.NumeroOperacion || 'N/A'}</td>
                                        <td>${p.Observacion || 'N/A'}</td>
                                    </tr>
                                `;
                            }).join('') : '<tr><td colspan="5" class="text-center">Sin pagos registrados</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        document.getElementById('modalDetallePrestamoBody').innerHTML = html;
        const modal = new bootstrap.Modal(document.getElementById('modalDetallePrestamo'));
        modal.show();
    }
    
    function exportarTablaPDF(tabId) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        
        let titulo = '';
        switch(tabId) {
            case 'todos': titulo = 'Todos los Préstamos'; break;
            case 'nuevos': titulo = 'Préstamos Nuevos'; break;
            case 'proceso': titulo = 'Préstamos en Proceso'; break;
            case 'pagados': titulo = 'Préstamos Pagados'; break;
        }
        
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text(`${titulo} - {{ $empleado->NombreCompleto }}`, 14, 15);
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);
        
        const tabla = document.querySelector(`#${tabId} table`);
        if (tabla) {
            doc.autoTable({
                html: tabla,
                startY: 30,
                theme: 'grid',
                headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 9, fontStyle: 'bold' },
                bodyStyles: { fontSize: 8, cellPadding: 2 }
            });
        }
        
        doc.save(`Prestamos_${titulo.replace(/\s/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`);
    }
    
    function imprimirDetallePrestamo() {
        window.print();
    }

    function exportarResumenPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 20;
        
        // Título
        doc.setFontSize(18);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('RESUMEN GENERAL DE PRÉSTAMOS', pageWidth / 2, yPos, { align: 'center' });
        
        yPos += 10;
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.setFont('helvetica', 'normal');
        doc.text(`Empleado: {{ $empleado->NombreCompleto }}`, pageWidth / 2, yPos, { align: 'center' });
        
        yPos += 6;
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, pageWidth / 2, yPos, { align: 'center' });
        
        yPos += 15;
        doc.setDrawColor(200, 200, 200);
        doc.line(14, yPos, pageWidth - 14, yPos);
        yPos += 10;
        
        // Información del Empleado
        doc.setFontSize(12);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('INFORMACIÓN DEL EMPLEADO', 14, yPos);
        
        yPos += 7;
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.setFont('helvetica', 'normal');
        
        doc.text(`Nombre: {{ $empleado->NombreCompleto }}`, 14, yPos);
        yPos += 5;
        doc.text(`Vendedor ID: {{ $empleado->VendedorId ?? 'N/A' }}`, 14, yPos);
        yPos += 5;
        doc.text(`Sucursal: {{ $sucursal->Nombre ?? 'N/A' }}`, 14, yPos);
        yPos += 5;
        doc.text(`Teléfono: {{ $empleado->PhoneNumber ?? 'N/A' }}`, 14, yPos);
        
        yPos += 10;
        
        // Resumen de Préstamos
        doc.setFontSize(12);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('RESUMEN DE PRÉSTAMOS', 14, yPos);
        
        yPos += 7;
        
        const prestamosHeaders = [['Concepto', 'Cantidad / Monto']];
        const prestamosBody = [
            ['Total Préstamos Solicitados', '{{ $resumen['total_prestamos'] }}'],
            ['Préstamos Nuevos', '{{ $resumen['prestamos_nuevos'] }}'],
            ['Préstamos en Proceso', '{{ $resumen['prestamos_proceso'] }}'],
            ['Préstamos Pagados', '{{ $resumen['prestamos_pagados'] }}'],
            ['Préstamos Incluidos', '{{ $resumen['prestamos_incluidos'] }}'],
            ['Monto Total Préstamos', '${{ number_format($resumen['total_monto'], 2) }}'],
            ['Total Abonado', '${{ number_format($resumen['total_pagado'], 2) }}'],
            ['Total Pendiente', '${{ number_format($resumen['total_pendiente'], 2) }}'],
            ['Porcentaje Pagado', '{{ $resumen['total_monto'] > 0 ? number_format(($resumen['total_pagado'] / $resumen['total_monto']) * 100, 1) : 0 }}%'],
            ['Porcentaje Pendiente', '{{ $resumen['total_monto'] > 0 ? number_format(($resumen['total_pendiente'] / $resumen['total_monto']) * 100, 1) : 0 }}%']
        ];
        
        doc.autoTable({
            head: prestamosHeaders,
            body: prestamosBody,
            startY: yPos,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9, cellPadding: 3 },
            margin: { left: 14, right: 14 }
        });
        
        yPos = doc.lastAutoTable.finalY + 10;
        
        // Resumen de Productos
        @php
            $totalProductos = 0;
            $totalProductosPagados = 0;
            $totalProductosPendientes = 0;
            $montoProductos = 0;
            $montoProductosPagados = 0;
            $montoProductosPendientes = 0;
            
            foreach($prestamos as $prestamo) {
                foreach($prestamo->detalles as $detalle) {
                    $totalProductos++;
                    $montoProductos += $detalle->Cantidad * $detalle->PvpDivisa;
                    
                    if($prestamo->Estatus == 3) {
                        $totalProductosPagados++;
                        $montoProductosPagados += $detalle->Cantidad * $detalle->PvpDivisa;
                    } elseif($prestamo->Estatus == 2) {
                        $totalProductosPendientes++;
                        $montoProductosPendientes += $detalle->Cantidad * $detalle->PvpDivisa;
                    }
                }
            }
        @endphp
        
        doc.setFontSize(12);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('RESUMEN DE PRODUCTOS', 14, yPos);
        
        yPos += 7;
        
        const productosHeaders = [['Concepto', 'Cantidad / Monto']];
        const productosBody = [
            ['Total Productos', '{{ $totalProductos }}'],
            ['Productos Pagados', '{{ $totalProductosPagados }}'],
            ['Productos Pendientes', '{{ $totalProductosPendientes }}'],
            ['Monto Total Productos', '${{ number_format($montoProductos, 2) }}'],
            ['Monto Pagado Productos', '${{ number_format($montoProductosPagados, 2) }}'],
            ['Monto Pendiente Productos', '${{ number_format($montoProductosPendientes, 2) }}']
        ];
        
        doc.autoTable({
            head: productosHeaders,
            body: productosBody,
            startY: yPos,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9, cellPadding: 3 },
            margin: { left: 14, right: 14 }
        });
        
        // Pie de página
        const añoActual = new Date().getFullYear();
        const finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(8);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(150, 150, 150);
        doc.text('Este documento es un resumen general de préstamos del empleado', pageWidth / 2, finalY, { align: 'center' });
        doc.text(`Copyright © ${añoActual} TiendasTenShop - Todos los derechos reservados`, pageWidth / 2, finalY + 5, { align: 'center' });
        
        // Guardar PDF
        const nombreArchivo = `Resumen_Prestamos_{{ $empleado->NombreCompleto }}_{{ date('Y-m-d') }}.pdf`;
        doc.save(nombreArchivo);
    }
</script>

<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 20px;
        position: relative;
        display: block;
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
    }
    .small-box .icon {
        position: absolute;
        right: 10px;
        top: 10px;
        color: rgba(0,0,0,.15);
    }
    .small-box .icon i {
        font-size: 70px;
    }
    @media print {
        .app-content-header, .breadcrumb, .card-header, .small-box, .modal-footer {
            display: none !important;
        }
        .card {
            border: none !important;
        }
        .tab-pane {
            display: block !important;
        }
        .nav-tabs {
            display: none !important;
        }

        .small-box.bg-warning h3,
        .small-box.bg-warning p {
            color: #fff !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
    }
</style>
@endsection