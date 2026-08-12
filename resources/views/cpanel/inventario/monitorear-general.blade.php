{{-- resources/views/cpanel/inventario/monitorear-general.blade.php --}}

@extends('layout.layout_dashboard')

@section('title', 'Monitor del conteo')

@php
    use App\Helpers\FileHelper;
    
    $estatusLabels = [
        0 => 'Nuevo',
        1 => 'En Conteo',
        2 => 'En Auditoría',
        3 => 'Cerrado'
    ];
    
    $estatusColors = [
        0 => '#6366f1',
        1 => '#22c55e',
        2 => '#eab308',
        3 => '#ef4444'
    ];
    
    // Calcular exactitud (productos exactos / total contados * 100)
    $exactitud = 0;
    if ($contados > 0) {
        $exactos = $detalles->filter(function($detalle) {
            return ($detalle->CantidadContada ?? 0) == ($detalle->Existencia ?? 0);
        })->count();
        $exactitud = round(($exactos / $contados) * 100, 2);
    }
    
    // Determinar el primer producto para mostrar en la cabecera
    $primerProducto = $detalles->first();
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="bi bi-display text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Monitor de Inventario
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Muestra la actividad del conteo de inventario
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.inventario.listado') }}">Inventarios</a></li>
                    <li class="breadcrumb-item active">Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card border-0 shadow-sm">
                    {{-- Header --}}
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h2 class="mb-0 fw-bold text-white" style="font-size:1.15rem;">
                                    <i class="bi bi-display me-2"></i>Monitor de Inventario
                                    <small class="d-block text-white-50" style="font-size:0.7rem;font-weight:400;">
                                        Muestra la actividad del conteo de inventario
                                    </small>
                                </h2>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{-- Arch.Conteo --}}
                                <a href="{{ route('cpanel.inventario.generar-plantilla-conteo', $inventario->InventarioId ?? 0) }}" 
                                   class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Arch.Conteo
                                </a>
                                
                                {{-- Listado --}}
                                <a href="{{ route('cpanel.inventario.listado') }}" 
                                   class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-list me-1"></i> Listado
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- ========================================== --}}
                        {{-- ACCORDION: INVENTARIO --}}
                        {{-- ========================================== --}}
                        <div class="accordion" id="accordionMonitor">
                            {{-- Panel 1: INVENTARIO (Cabecera) --}}
                            <div class="accordion-item mb-3 border-0 shadow-sm">
                                <div class="accordion-header" id="headingInventario">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapseInventario" 
                                            aria-expanded="true" aria-controls="collapseInventario"
                                            style="background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);color:#fff;border-radius:8px;">
                                        <i class="bi bi-cloud-download me-2"></i> 
                                        <strong>INVENTARIO</strong>
                                        <small class="ms-2" style="color:rgba(255,255,255,0.8);font-weight:400;">
                                            Auditar los datos de un inventario planificado
                                        </small>
                                    </button>
                                </div>
                                <div id="collapseInventario" class="accordion-collapse collapse show" 
                                     aria-labelledby="headingInventario" data-bs-parent="#accordionMonitor">
                                    <div class="accordion-body">
                                        <div class="row">
                                            {{-- Columna izquierda: Información del inventario --}}
                                            <div class="col-md-12 col-lg-6">
                                                <div class="card border-0">
                                                    <div class="card-body p-3">
                                                        {{-- Información general --}}
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Código</small>
                                                                <strong>{{ $inventario->Codigo ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Sucursal</small>
                                                                <strong>{{ $sucursal->Nombre ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Fecha Inicio</small>
                                                                <strong>{{ $inventario->FechaInicio ? date('d/m/Y', strtotime($inventario->FechaInicio)) : 'N/A' }}</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block">Estatus</small>
                                                                <span class="badge rounded-pill px-2 py-1" 
                                                                      style="background:{{ $estatusColors[$inventario->Estatus ?? 0] ?? '#6b7280' }}; color: {{ ($inventario->Estatus ?? 0) == 2 ? '#000' : '#fff' }};">
                                                                    {{ $estatusLabels[$inventario->Estatus ?? 0] ?? 'Desconocido' }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {{-- Totales --}}
                                                        <div class="row g-2">
                                                            <div class="col-4">
                                                                <div class="bg-primary bg-opacity-10 rounded p-2 text-center">
                                                                    <small class="text-muted d-block">Total</small>
                                                                    <strong class="text-primary">{{ $totalProductos ?? 0 }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="bg-success bg-opacity-10 rounded p-2 text-center">
                                                                    <small class="text-muted d-block">Contados</small>
                                                                    <strong class="text-success">{{ $contados ?? 0 }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="bg-warning bg-opacity-10 rounded p-2 text-center">
                                                                    <small class="text-muted d-block">Pendientes</small>
                                                                    <strong class="text-warning">{{ $pendientes ?? 0 }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Columna derecha: Detalles del producto seleccionado --}}
                                            <div class="col-md-12 col-lg-6">
                                                <div class="card border-0">
                                                    <div class="card-body p-3">
                                                        <h6 class="text-muted mb-3">
                                                            <i class="bi bi-box me-1"></i> Producto seleccionado
                                                            <small class="text-muted" style="font-size:0.7rem;">(Haz clic en una fila)</small>
                                                        </h6>
                                                        
                                                        @if($primerProducto)
                                                            @php
                                                                $diffInicial = ($primerProducto->CantidadContada ?? 0) - ($primerProducto->Existencia ?? 0);
                                                                $diffTextInicial = $diffInicial < 0 ? 'Falta ' . abs($diffInicial) : ($diffInicial > 0 ? 'Sobra ' . $diffInicial : 'Exacto');
                                                                $diffColorInicial = $diffInicial < 0 ? '#ef4444' : ($diffInicial > 0 ? '#22c55e' : '#000');
                                                            @endphp
                                                            <form class="form-horizontal" id="formProductoSeleccionado">
                                                                <div class="row g-2">
                                                                    {{-- Código --}}
                                                                    <div class="col-12">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold">Código:</span>
                                                                            <span id="lblCodigoProducto" class="badge bg-secondary">
                                                                                {{ $primerProducto->Codigo ?? 'N/A' }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Referencia --}}
                                                                    <div class="col-12">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold">Referencia:</span>
                                                                            <span id="lblReferenciaProducto" class="text-dark">
                                                                                {{ $primerProducto->Referencia ?? 'N/A' }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Contado --}}
                                                                    <div class="col-12">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold text-success">Contado:</span>
                                                                            <span class="badge bg-success" id="lblCantidadContada">
                                                                                {{ $primerProducto->CantidadContada ?? 0 }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Existencia --}}
                                                                    <div class="col-12">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold">Existencia:</span>
                                                                            <span class="badge bg-info" id="lblExistenciaProducto">
                                                                                {{ $primerProducto->Existencia ?? 0 }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Diferencia --}}
                                                                    <div class="col-12">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold">Diferencia:</span>
                                                                            <span class="badge" id="lblDiferenciaConteo" style="background:#f59e0b;color:#000;">
                                                                                {{ $diffTextInicial }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Pie solo --}}
                                                                    <div class="col-6">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold" style="font-size:0.85rem;">Pie solo:</span>
                                                                            <span id="lblPieSolo">{{ $primerProducto->CantidadPieSolo ?? 0 }}</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Pie invertido --}}
                                                                    <div class="col-6">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold" style="font-size:0.85rem;">Pie inv.:</span>
                                                                            <span id="lblPieInvertido">{{ $primerProducto->CantidadPieInvertido ?? 0 }}</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Pieza dañada --}}
                                                                    <div class="col-6">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold" style="font-size:0.85rem;">Dañada:</span>
                                                                            <span id="lblPiezaDanada">{{ $primerProducto->CantidadPiezaDanada ?? 0 }}</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Caja vacía --}}
                                                                    <div class="col-6">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                            <span class="fw-bold" style="font-size:0.85rem;">Caja vacía:</span>
                                                                            <span id="lblCajaVacia">{{ $primerProducto->CantidadCajaVacia ?? 0 }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        @else
                                                            <p class="text-muted text-center py-3">
                                                                <i class="bi bi-inbox me-2"></i> No hay productos
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Fila de Avance y Exactitud --}}
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="row">
                                                    {{-- Avance --}}
                                                    <div class="col-md-6">
                                                        <div class="tile_stats_count border-end">
                                                            <span class="count_top">
                                                                <i class="bi bi-speedometer2 me-1"></i> <b>Avance</b>
                                                            </span>
                                                            <div class="count col-indigo">
                                                                {{ $porcentaje ?? 0 }}%
                                                            </div>
                                                            <span class="count_bottom">
                                                                <i class="bi bi-arrow-up text-success"></i> {{ $contados ?? 0 }} de {{ $totalProductos ?? 0 }} productos contados
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Exactitud --}}
                                                    <div class="col-md-6">
                                                        <div class="tile_stats_count">
                                                            <span class="count_top">
                                                                <i class="bi bi-check-circle me-1"></i> <b>Exactitud</b>
                                                            </span>
                                                            <div class="count col-indigo">
                                                                {{ $exactitud }}%
                                                            </div>
                                                            <span class="count_bottom">
                                                                <i class="bi bi-check-circle text-success"></i> {{ $exactos ?? 0 }} exactos de {{ $contados ?? 0 }} contados
                                                                @if($conDiferencias > 0)
                                                                    <span class="text-danger">({{ $conDiferencias }} con diferencia)</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Panel 2: PRODUCTOS --}}
                            <div class="accordion-item border-0 shadow-sm">
                                <div class="accordion-header" id="headingProductos">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapseProductos" 
                                            aria-expanded="false" aria-controls="collapseProductos"
                                            style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);color:#fff;border-radius:8px;">
                                        <i class="bi bi-box-seam me-2"></i> 
                                        <strong>PRODUCTOS</strong>
                                        <small class="ms-2" style="color:rgba(255,255,255,0.8);font-weight:400;">
                                            Ver los productos del inventario
                                        </small>
                                    </button>
                                </div>
                                <div id="collapseProductos" class="accordion-collapse collapse show" 
                                     aria-labelledby="headingProductos" data-bs-parent="#accordionMonitor">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover" id="tablaMonitorConteo">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:50px;">#</th>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th class="text-end">Costo</th>
                                                        <th class="text-center">Existencia</th>
                                                        <th class="text-center">Contado</th>
                                                        <th class="text-center">Pie Solo</th>
                                                        <th class="text-center">Pie Inv.</th>
                                                        <th class="text-center">Dañado</th>
                                                        <th class="text-center">Caja Vacía</th>
                                                        <th class="text-end">Total $</th>
                                                        <th class="text-center">Diferencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($detalles as $index => $detalle)
                                                    @php
                                                        $imgSrc = FileHelper::getOrDownloadFile(
                                                            'images/items/thumbs/',
                                                            $detalle->UrlFoto ?? '',
                                                            'assets/img/adminlte/img/produc_default.jfif'
                                                        );
                                                        $imgFull = FileHelper::getOrDownloadFile(
                                                            'images/items/',
                                                            $detalle->UrlFoto ?? '',
                                                            'assets/img/adminlte/img/produc_default.jfif'
                                                        );
                                                        
                                                        $cantContada = $detalle->CantidadContada ?? 0;
                                                        $cantPieSolo = $detalle->CantidadPieSolo ?? 0;
                                                        $cantPieInv = $detalle->CantidadPieInvertido ?? 0;
                                                        $cantDanado = $detalle->CantidadPiezaDanada ?? 0;
                                                        $cantCajaVacia = $detalle->CantidadCajaVacia ?? 0;
                                                        $existencia = $detalle->Existencia ?? 0;
                                                        $costo = $detalle->CostoDivisa ?? 0;
                                                        
                                                        $totalUnidades = $cantContada - $cantPieSolo - $cantPieInv - $cantDanado;
                                                        $totalCosto = $totalUnidades * $costo;
                                                        $diferencia = $cantContada - $existencia;
                                                        
                                                        $diffColor = $diferencia < 0 ? '#ef4444' : ($diferencia > 0 ? '#22c55e' : '#000');
                                                        $diffText = $diferencia < 0 ? 'Falta ' . abs($diferencia) : ($diferencia > 0 ? 'Sobra ' . $diferencia : 'Exacto');
                                                    @endphp
                                                    <tr data-detalle-id="{{ $detalle->InventarioDetalleId }}"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        data-codigo="{{ $detalle->Codigo ?? 'N/A' }}"
                                                        data-referencia="{{ $detalle->Referencia ?? 'N/A' }}"
                                                        data-contado="{{ $cantContada }}"
                                                        data-existencia="{{ $existencia }}"
                                                        data-pie-solo="{{ $cantPieSolo }}"
                                                        data-pie-invertido="{{ $cantPieInv }}"
                                                        data-danado="{{ $cantDanado }}"
                                                        data-caja-vacia="{{ $cantCajaVacia }}"
                                                        data-costo="{{ $costo }}"
                                                        data-diferencia="{{ $diferencia }}"
                                                        data-diff-text="{{ $diffText }}"
                                                        data-diff-color="{{ $diffColor }}">
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td class="text-center">
                                                            <img src="{{ $imgSrc }}" 
                                                                 loading="lazy" 
                                                                 alt="{{ $detalle->Codigo }}"
                                                                 class="img-thumbnail img-zoomable"
                                                                 style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                                                 data-full-image="{{ $imgFull }}"
                                                                 data-description="{{ $detalle->Codigo }} - {{ $detalle->Referencia }}"
                                                                 onclick="event.stopPropagation(); zoomImagen(this);"
                                                                 onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                        </td>
                                                        <td><strong>{{ $detalle->Codigo ?? 'N/A' }}</strong></td>
                                                        <td>{{ $detalle->Referencia ?? 'N/A' }}</td>
                                                        <td class="text-end">{{ number_format($costo, 2) }}</td>
                                                        <td class="text-center">{{ $existencia }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $cantContada }}</span>
                                                        </td>
                                                        <td class="text-center">{{ $cantPieSolo }}</td>
                                                        <td class="text-center">{{ $cantPieInv }}</td>
                                                        <td class="text-center">{{ $cantDanado }}</td>
                                                        <td class="text-center">{{ $cantCajaVacia }}</td>
                                                        <td class="text-end">
                                                            <span class="badge bg-info text-dark">
                                                                ${{ number_format($totalCosto, 2) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span style="color: {{ $diffColor }}; font-weight:bold;">
                                                                {{ $diffText }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="13" class="text-center text-muted py-4">
                                                            <i class="bi bi-inbox me-2" style="font-size:1.2rem;"></i>
                                                            No hay productos en este inventario
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
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para zoom de imagen --}}
<div class="modal fade" id="modalZoomImagen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalZoomTitle">Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalZoomImage" src="" alt="Producto" style="max-width:100%;max-height:500px;border-radius:8px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Verificar que la tabla existe
        var tabla = document.getElementById('tablaMonitorConteo');
        if (tabla) {
            var tbody = tabla.querySelector('tbody');
            if (tbody) {
                var filas = tbody.querySelectorAll('tr');
                
                // Verificar datos de la primera fila
                if (filas.length > 0) {
                    var primeraFila = filas[0];
                }
                
                // Agregar evento click a cada fila individualmente (más confiable)
                filas.forEach(function(row, index) {
                    row.addEventListener('click', function(e) {
                        
                        // Si es la imagen, no hacer nada
                        if (e.target.tagName === 'IMG') {
                            return;
                        }
                        
                        // Remover selección anterior
                        var filasSeleccionadas = tbody.querySelectorAll('tr.seleccionado');
                        filasSeleccionadas.forEach(function(f) {
                            f.classList.remove('seleccionado');
                        });
                        
                        // Seleccionar la fila actual
                        this.classList.add('seleccionado');
                        
                        // Obtener datos desde los atributos data-*
                        var codigo = this.dataset.codigo || 'N/A';
                        var referencia = this.dataset.referencia || 'N/A';
                        var contado = this.dataset.contado || 0;
                        var existencia = this.dataset.existencia || 0;
                        var pieSolo = this.dataset.pieSolo || 0;
                        var pieInvertido = this.dataset.pieInvertido || 0;
                        var danado = this.dataset.danado || 0;
                        var cajaVacia = this.dataset.cajaVacia || 0;
                        var diffText = this.dataset.diffText || '0';
                        var diffColor = this.dataset.diffColor || '#000';
                        var diferencia = parseInt(this.dataset.diferencia) || 0;
                        
                        // Actualizar labels
                        var lblCodigo = document.getElementById('lblCodigoProducto');
                        var lblReferencia = document.getElementById('lblReferenciaProducto');
                        var lblContado = document.getElementById('lblCantidadContada');
                        var lblExistencia = document.getElementById('lblExistenciaProducto');
                        var lblPieSolo = document.getElementById('lblPieSolo');
                        var lblPieInvertido = document.getElementById('lblPieInvertido');
                        var lblDanado = document.getElementById('lblPiezaDanada');
                        var lblCajaVacia = document.getElementById('lblCajaVacia');
                        var lblDiferencia = document.getElementById('lblDiferenciaConteo');
                        
                        if (lblCodigo) lblCodigo.textContent = codigo;
                        if (lblReferencia) lblReferencia.textContent = referencia;
                        if (lblContado) lblContado.textContent = contado;
                        if (lblExistencia) lblExistencia.textContent = existencia;
                        if (lblPieSolo) lblPieSolo.textContent = pieSolo;
                        if (lblPieInvertido) lblPieInvertido.textContent = pieInvertido;
                        if (lblDanado) lblDanado.textContent = danado;
                        if (lblCajaVacia) lblCajaVacia.textContent = cajaVacia;
                        
                        // Actualizar diferencia con color
                        if (lblDiferencia) {
                            lblDiferencia.textContent = diffText;
                            lblDiferencia.style.color = diffColor;
                            
                            // Cambiar estilo del badge según diferencia
                            if (diferencia === 0) {
                                lblDiferencia.style.background = '#f59e0b';
                                lblDiferencia.style.color = '#000';
                            } else if (diferencia < 0) {
                                lblDiferencia.style.background = '#ef4444';
                                lblDiferencia.style.color = '#fff';
                            } else {
                                lblDiferencia.style.background = '#22c55e';
                                lblDiferencia.style.color = '#fff';
                            }
                        }
                        
                    });
                });
            } else {
                console.error('❌ Tbody no encontrado');
            }
        } else {
            console.error('❌ Tabla no encontrada');
        }
    });

    // Función para zoom de imagen
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        var modalImage = document.getElementById('modalZoomImage');
        var modalTitle = document.getElementById('modalZoomTitle');
        
        if (modalImage) {
            modalImage.src = imgSrc;
        }
        if (modalTitle) {
            modalTitle.textContent = descripcion;
        }
        
        // Usar Bootstrap 5 modal
        var modalElement = document.getElementById('modalZoomImagen');
        if (modalElement) {
            try {
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            } catch (error) {
                console.error('❌ Error al mostrar modal:', error);
            }
        } else {
            console.error('❌ Modal no encontrado');
        }
    }
</script>
@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }
    .accordion-button::after {
        filter: brightness(0) invert(1);
    }
    .accordion-item {
        border-radius: 8px !important;
        overflow: hidden;
    }
    
    .tile_stats_count {
        padding: 8px 0;
    }
    .tile_stats_count .count_top {
        font-size: 0.8rem;
        color: #6b7280;
    }
    .tile_stats_count .count {
        font-size: 2rem;
        font-weight: bold;
    }
    .col-indigo {
        color: #4f46e5;
    }
    .border-end {
        border-right: 1px solid #e5e7eb !important;
    }
    
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 4px;
    }
    .img-zoomable:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
        position: relative;
    }
    
    .table-bordered {
        border: 1px solid #e2e8f0;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #e2e8f0;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8fafc;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f5f9;
    }
    .table tbody tr.seleccionado {
        background-color: #dbeafe !important;
    }
    
    .badge {
        font-weight: 500;
    }
    
    #lblDiferenciaConteo {
        transition: all 0.3s ease;
    }
</style>
@endpush