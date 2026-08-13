@extends('layout.layout_dashboard')

@section('title', 'Productos - ' . ($proveedor->Nombre ?? 'Proveedor') . ' - ' . ($sucursal->Nombre ?? 'Sucursal'))

@php
    use Carbon\Carbon;
    use App\Helpers\FileHelper;
    
    $hdrBg = 'linear-gradient(135deg,#14b8a6,#0d9488)';
    $hdrIcon = 'box-seam';
    
    $rentabilidadColor = ($rentabilidadSucursal ?? 0) >= 30 ? '#22c55e' : (($rentabilidadSucursal ?? 0) >= 10 ? '#eab308' : '#ef4444');
    
    // Calcular variación total solo para mostrar informativamente
    $variacionTotal = ($totalCostoActual ?? 0) - ($totalCostoHistorico ?? 0);
    $variacionPorcentaje = ($totalCostoHistorico ?? 0) > 0 
        ? round(($variacionTotal / ($totalCostoHistorico ?? 1)) * 100, 2) 
        : 0;
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
                            Productos: {{ $proveedor->Nombre ?? 'N/A' }}
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $sucursal->Nombre ?? 'Sucursal' }} - Detalle de productos con análisis de costo
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}">Rentabilidad</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle', $proveedor->ProveedorId) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}">Detalle</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $sucursal->Nombre ?? 'Sucursal' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- RESUMEN DE LA SUCURSAL --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(13,110,253,0.12);">
                                <i class="bi bi-clock-history text-primary" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">COSTO HISTÓRICO</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalCostoHistorico ?? 0, 2) }}</h6>
                                <small class="text-muted" style="font-size:0.6rem;">Costo al momento de la venta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(108,117,125,0.12);">
                                <i class="bi bi-tag text-secondary" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">COSTO ACTUAL</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalCostoActual ?? 0, 2) }}</h6>
                                <small class="text-muted" style="font-size:0.6rem;">Según tabla Productos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(40,167,69,0.12);">
                                <i class="bi bi-cash-stack text-success" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">VENTAS (USD)</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalVentas ?? 0, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(20,184,166,0.12);">
                                <i class="bi bi-percent text-teal" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">RENTABILIDAD</small>
                                <h6 class="mb-0 fw-bold" style="color:{{ $rentabilidadColor }};">
                                    {{ number_format($rentabilidadSucursal ?? 0, 2) }}%
                                </h6>
                                <small class="text-muted" style="font-size:0.6rem;">
                                    Calculada con costo histórico
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PRODUCTOS --}}
        {{-- ================================================ --}}
        @if($productosData && count($productosData) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-box-seam me-2"></i>
                        Productos Vendidos
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;color:#0d9488;">
                            {{ count($productosData) }}
                        </span>
                    </h6>
                    <div>
                        <span class="badge bg-white text-dark me-2" style="font-size:0.7rem;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;width:60px;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">DESCRIPCIÓN</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">UNIDADES</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">VENTAS (USD)</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;" title="Costo al momento de la venta">COSTO HISTÓRICO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;" title="Costo según tabla Productos">COSTO ACTUAL</th>
                                <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">RENTABILIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productosData as $producto)
                                @php
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/items/thumbs/',
                                        $producto->UrlFoto ?? '',
                                        'assets/img/adminlte/img/produc_default.jfif'
                                    );
                                    $imgFull = FileHelper::getOrDownloadFile(
                                        'images/items/',
                                        $producto->UrlFoto ?? '',
                                        'assets/img/adminlte/img/produc_default.jfif'
                                    );
                                    
                                    // Colores según rentabilidad (usando rentabilidad histórica)
                                    $rentabilidadProducto = $producto->RentabilidadHistorica ?? 0;
                                    $prodColor = $rentabilidadProducto >= 30 ? '#22c55e' : ($rentabilidadProducto >= 10 ? '#eab308' : '#ef4444');
                                    
                                    // Utilidad (usando costo histórico)
                                    $utilidad = $producto->UtilidadHistorica ?? 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 text-center">
                                        <img src="{{ $imgSrc }}"
                                             loading="lazy"
                                             alt="{{ $producto->Codigo }}"
                                             class="img-thumbnail img-zoomable"
                                             style="width:40px;height:40px;object-fit:cover;cursor:pointer;border-radius:4px;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgFull }}"
                                             data-description="{{ $producto->Codigo }} - {{ $producto->Referencia }}"
                                             onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#14b8a6;font-size:0.8rem;font-weight:bold;">
                                            {{ $producto->Codigo ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td>{{ $producto->Referencia ?? 'N/A' }}</td>
                                    <td>
                                        <span style="font-size:0.85rem;">{{ $producto->Descripcion ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold">{{ number_format($producto->TotalCantidad ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        ${{ number_format($producto->TotalVentas ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span title="Costo histórico unitario: ${{ number_format($producto->CostoHistoricoUnitario ?? 0, 2) }}">
                                            ${{ number_format($producto->CostoHistoricoTotal ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span title="Costo actual unitario: ${{ number_format($producto->CostoActualUnitario ?? 0, 2) }}">
                                            ${{ number_format($producto->CostoActualTotal ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div>
                                            <span class="badge rounded-pill px-2 py-1" 
                                                  style="background:{{ $prodColor }};color:#fff;font-size:0.75rem;">
                                                {{ number_format($rentabilidadProducto, 2) }}%
                                            </span>
                                            <br>
                                            <small class="text-muted" style="font-size:0.6rem;">
                                                Utilidad: ${{ number_format($utilidad, 2) }}
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:600;">
                                <td colspan="5" class="ps-4 py-2 text-muted">TOTALES</td>
                                <td class="text-end py-2">${{ number_format($totalVentas ?? 0, 2) }}</td>
                                <td class="text-end py-2">${{ number_format($totalCostoHistorico ?? 0, 2) }}</td>
                                <td class="text-end py-2">${{ number_format($totalCostoActual ?? 0, 2) }}</td>
                                <td class="pe-4 text-end py-2">
                                    <span class="badge rounded-pill px-2 py-1" 
                                          style="background:{{ $rentabilidadColor }};color:#fff;font-size:0.8rem;">
                                        {{ number_format($rentabilidadSucursal ?? 0, 2) }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-box-seam me-1"></i>
                            {{ count($productosData) }} producto{{ count($productosData) != 1 ? 's' : '' }}
                        </small>
                        <small class="text-muted ms-3">
                            <i class="bi bi-tag me-1"></i>
                            <span title="Diferencia entre costo actual y costo histórico">
                                Diferencia de costo: 
                                <span style="color:{{ $variacionTotal > 0 ? '#dc3545' : ($variacionTotal < 0 ? '#28a745' : '#6c757d') }};font-weight:600;">
                                    ${{ number_format($variacionTotal, 2) }}
                                    ({{ $variacionPorcentaje >= 0 ? '+' : '' }}{{ $variacionPorcentaje }}%)
                                </span>
                            </span>
                        </small>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Período: {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
                <p class="text-muted mt-2">No hay productos vendidos para este proveedor en esta sucursal</p>
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- BOTÓN VOLVER --}}
        {{-- ================================================ --}}
        <div class="mt-3">
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle', $proveedor->ProveedorId) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" 
               class="btn btn-light border fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver al Detalle
            </a>
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" 
               class="btn btn-light border fw-semibold ms-2">
                <i class="bi bi-list me-1"></i> Volver al Listado
            </a>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Inicializar DataTable (si existe)
        if (typeof $.fn !== 'undefined' && $.fn.DataTable && document.getElementById('tablaProductos')) {
            $('#tablaProductos').DataTable({
                searching: true,
                lengthChange: true,
                pageLength: 30,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[8, "desc"]], // Ordenar por rentabilidad (columna 8)
                columnDefs: [
                    { orderable: false, targets: [0] }
                ]
            });
        }
    });

    // Zoom de imagen
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        document.getElementById('modalZoomImage').src = imgSrc;
        document.getElementById('modalZoomTitle').textContent = descripcion;
        
        var modal = new bootstrap.Modal(document.getElementById('modalZoomImagen'));
        modal.show();
    }
</script>
@endsection

@push('styles')
<style>
    .text-teal { color: #14b8a6; }
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .table-hover tbody tr:hover { background-color: #f8fafc; }
    .card-header { border-radius: 8px 8px 0 0; }
    
    /* Tooltip personalizado para datos de costo */
    [data-bs-toggle="tooltip"] {
        cursor: help;
    }
    
    /* Estilo para el footer de la tabla */
    tfoot td {
        font-weight: 600;
        border-top: 2px solid #e2e8f0;
    }
</style>
@endpush