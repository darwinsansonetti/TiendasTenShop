@extends('layout.layout_dashboard')

@section('title', 'Estadísticas - ' . ($proveedor->Nombre ?? 'Proveedor') . ' - ' . ($sucursal->Nombre ?? 'Sucursal'))

@php
    use Carbon\Carbon;
    use App\Helpers\FileHelper;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = 'graph-up-arrow';
    
    $rentabilidadColor = ($rentabilidadGlobal ?? 0) >= 30 ? '#22c55e' : (($rentabilidadGlobal ?? 0) >= 10 ? '#eab308' : '#ef4444');
    
    $imgSrc = FileHelper::getOrDownloadFile(
        'images/proveedores/',
        $proveedor->UrlImagen ?? '',
        'assets/img/adminlte/img/proveedor_default.png'
    );
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
                            Estadísticas: {{ $proveedor->Nombre ?? 'N/A' }}
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $sucursal->Nombre ?? 'Sucursal' }} - Análisis de rentabilidad
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}">Rentabilidad</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle', $proveedor->ProveedorId) }}">Detalle</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Estadísticas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- POSICIONAMIENTO DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
            <div class="card-body text-center py-4">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <div class="rounded-circle bg-white bg-opacity-25 p-3 d-flex align-items-center justify-content-center"
                                 style="width:70px;height:70px;">
                                <i class="bi bi-trophy text-white" style="font-size:2rem;"></i>
                            </div>
                            <div class="text-white text-start">
                                <small class="text-white-50 d-block">Posición General</small>
                                <h3 class="mb-0 fw-bold text-white">
                                    #{{ $posicionProveedor ?? 'N/A' }}
                                    <small class="text-white-50" style="font-size:0.8rem;">
                                        de {{ $totalProveedores ?? 0 }}
                                    </small>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-white">
                            <small class="text-white-50 d-block">Rentabilidad</small>
                            <h3 class="mb-0 fw-bold text-white">
                                {{ number_format($rentabilidadGlobal ?? 0, 2) }}%
                            </h3>
                            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;font-size:0.7rem;">
                                {{ $rentabilidadGlobal >= 30 ? 'Excelente' : ($rentabilidadGlobal >= 10 ? 'Buena' : 'Baja') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-white">
                            <small class="text-white-50 d-block">Total de Productos</small>
                            <h3 class="mb-0 fw-bold text-white">{{ number_format($totalProductos ?? 0, 0) }}</h3>
                            <small class="text-white-50">
                                {{ number_format($totalCantidad ?? 0, 0) }} unidades vendidas
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS GENERALES --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(20,184,166,0.12);">
                                <i class="bi bi-cart-check text-teal" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">COMPRAS (USD)</small>
                                <h6 class="mb-0 fw-bold text-dark">${{ number_format($totalCosto ?? 0, 2) }}</h6>
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
                                 style="width:44px;height:44px;background:rgba(34,197,94,0.12);">
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
                                 style="width:44px;height:44px;background:rgba(245,158,11,0.12);">
                                <i class="bi bi-graph-up-arrow text-warning" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.7rem;">UTILIDAD (USD)</small>
                                <h6 class="mb-0 fw-bold" style="color:{{ ($totalUtilidad ?? 0) >= 0 ? '#22c55e' : '#ef4444' }};">
                                    ${{ number_format($totalUtilidad ?? 0, 2) }}
                                </h6>
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
                                    {{ number_format($rentabilidadGlobal ?? 0, 2) }}%
                                </h6>
                                <small class="text-muted">Promedio: {{ number_format($promedioRentabilidad ?? 0, 2) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE CLASIFICACIÓN DE PRODUCTOS --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-2 p-2" style="background:rgba(34,197,94,0.12);">
                                <i class="bi bi-arrow-up-circle text-success" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Alta Rentabilidad (&gt;30%)</small>
                                <span class="fw-bold fs-5">{{ $productosAltaRentabilidad->count() ?? 0 }}</span>
                                <small class="text-muted"> productos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-2 p-2" style="background:rgba(245,158,11,0.12);">
                                <i class="bi bi-arrow-right-circle text-warning" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Baja Rentabilidad (&lt;10%)</small>
                                <span class="fw-bold fs-5">{{ $productosBajaRentabilidad->count() ?? 0 }}</span>
                                <small class="text-muted"> productos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-2 p-2" style="background:rgba(239,68,68,0.12);">
                                <i class="bi bi-arrow-down-circle text-danger" style="font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Con Pérdida</small>
                                <span class="fw-bold fs-5">{{ $productosConPerdida->count() ?? 0 }}</span>
                                <small class="text-muted"> productos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- DESTACADOS DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <h5 class="fw-bold mb-3">
            <i class="bi bi-star me-2 text-warning"></i>Productos Destacados
        </h5>

        <div class="row g-3 mb-4">
            {{-- Producto Más Vendido --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:#f8fafc;">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-trophy text-warning me-1"></i> MÁS VENDIDO
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($productoMasVendido)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ FileHelper::getOrDownloadFile('images/items/thumbs/', $productoMasVendido->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif') }}"
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                <div>
                                    <strong>{{ $productoMasVendido->Codigo ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">{{ $productoMasVendido->Referencia ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted d-block">Unidades</small>
                                    <span class="fw-bold">{{ number_format($productoMasVendido->TotalCantidad ?? 0, 0) }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Ventas</small>
                                    <span class="fw-bold">${{ number_format($productoMasVendido->TotalVentas ?? 0, 2) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="bi bi-info-circle text-muted" style="font-size:1.5rem;"></i>
                                <p class="text-muted mb-0" style="font-size:0.85rem;">Sin datos</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Producto Más Rentable --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:#f8fafc;">
                        <h6 class="mb-0 fw-bold text-success">
                            <i class="bi bi-graph-up-arrow text-success me-1"></i> MÁS RENTABLE
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($productoMasRentable)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ FileHelper::getOrDownloadFile('images/items/thumbs/', $productoMasRentable->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif') }}"
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                <div>
                                    <strong>{{ $productoMasRentable->Codigo ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">{{ $productoMasRentable->Referencia ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted d-block">Rentabilidad</small>
                                    <span class="fw-bold" style="color:#22c55e;">{{ number_format($productoMasRentable->Rentabilidad ?? 0, 2) }}%</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Utilidad</small>
                                    <span class="fw-bold">${{ number_format($productoMasRentable->Utilidad ?? 0, 2) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="bi bi-info-circle text-muted" style="font-size:1.5rem;"></i>
                                <p class="text-muted mb-0" style="font-size:0.85rem;">Sin datos</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Producto Menos Rentable --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:#f8fafc;">
                        <h6 class="mb-0 fw-bold text-danger">
                            <i class="bi bi-graph-down-arrow text-danger me-1"></i> MENOS RENTABLE
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($productoMenosRentable)
                            @php
                                $rentabilidad = $productoMenosRentable->Rentabilidad ?? 0;
                                $utilidad = $productoMenosRentable->Utilidad ?? 0;
                                $esPerdida = $utilidad < 0;
                            @endphp
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ FileHelper::getOrDownloadFile('images/items/thumbs/', $productoMenosRentable->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif') }}"
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                <div>
                                    <strong>{{ $productoMenosRentable->Codigo ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">{{ $productoMenosRentable->Referencia ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted d-block">Rentabilidad</small>
                                    <span class="fw-bold" style="color:{{ $esPerdida ? '#ef4444' : ($rentabilidad < 10 ? '#eab308' : '#22c55e') }};">
                                        {{ number_format($rentabilidad, 2) }}%
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Utilidad</small>
                                    <span class="fw-bold" style="color:{{ $esPerdida ? '#ef4444' : '#22c55e' }};">
                                        ${{ number_format($utilidad, 2) }}
                                    </span>
                                </div>
                            </div>
                            @if($esPerdida)
                                <div class="mt-2">
                                    <span class="badge bg-danger">¡Pérdida!</span>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-3">
                                <i class="bi bi-info-circle text-muted" style="font-size:1.5rem;"></i>
                                <p class="text-muted mb-0" style="font-size:0.85rem;">No hay productos con ventas</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Producto que Más Factura --}}
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:#f8fafc;">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-cash-stack text-primary me-1"></i> MÁS FACTURA
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($productoMasFactura)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ FileHelper::getOrDownloadFile('images/items/thumbs/', $productoMasFactura->UrlFoto ?? '', 'assets/img/adminlte/img/produc_default.jfif') }}"
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                <div>
                                    <strong>{{ $productoMasFactura->Codigo ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">{{ $productoMasFactura->Referencia ?? 'N/A' }}</small>
                                </div>
                            </div>
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted d-block">Ventas</small>
                                    <span class="fw-bold">${{ number_format($productoMasFactura->TotalVentas ?? 0, 2) }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Unidades</small>
                                    <span class="fw-bold">{{ number_format($productoMasFactura->TotalCantidad ?? 0, 0) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="bi bi-info-circle text-muted" style="font-size:1.5rem;"></i>
                                <p class="text-muted mb-0" style="font-size:0.85rem;">Sin datos</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PRODUCTOS CON RENTABILIDAD --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-box-seam me-2"></i>
                    Detalle de Productos
                    <span class="badge bg-white ms-2 fw-semibold"
                          style="font-size:0.75rem;color:#7c3aed;">
                        {{ $productosData->count() }}
                    </span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;width:50px;">#</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;width:60px;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">REFERENCIA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">UNIDADES</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">VENTAS (USD)</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">COSTO (USD)</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">UTILIDAD</th>
                                <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">RENTABILIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productosData as $index => $producto)
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
                                    
                                    $prodColor = ($producto->Rentabilidad ?? 0) >= 30 ? '#22c55e' : (($producto->Rentabilidad ?? 0) >= 10 ? '#eab308' : '#ef4444');
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 text-center fw-semibold">{{ $index + 1 }}</td>
                                    <td class="text-center">
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
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:0.8rem;font-weight:bold;">
                                            {{ $producto->Codigo ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td>{{ $producto->Referencia ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <span class="fw-semibold">{{ number_format($producto->TotalCantidad ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        ${{ number_format($producto->TotalVentas ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        ${{ number_format($producto->TotalCosto ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span style="color:{{ ($producto->Utilidad ?? 0) >= 0 ? '#22c55e' : '#ef4444' }};">
                                            ${{ number_format($producto->Utilidad ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <span class="badge rounded-pill px-2 py-1" 
                                              style="background:{{ $prodColor }};color:#fff;font-size:0.75rem;">
                                            {{ number_format($producto->Rentabilidad ?? 0, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size:2rem;"></i>
                                        <p class="mt-2">No hay productos vendidos en el período seleccionado</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-box-seam me-1"></i>
                        {{ $productosData->count() }} producto{{ $productosData->count() != 1 ? 's' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Período: {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- BOTONES DE NAVEGACIÓN --}}
        {{-- ================================================ --}}
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle.sucursal', [
                'proveedorId' => $proveedor->ProveedorId, 
                'sucursalId' => $sucursal->ID
            ]) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
               class="btn btn-light border fw-semibold">
                <i class="bi bi-box-seam me-1"></i> Ver Productos
            </a>
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle', $proveedor->ProveedorId) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" 
               class="btn btn-light border fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver al Detalle
            </a>
            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}" 
               class="btn btn-light border fw-semibold">
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

        // Inicializar DataTable
        if ($.fn.DataTable && document.getElementById('tablaProductos')) {
            $('#tablaProductos').DataTable({
                searching: true,
                lengthChange: true,
                pageLength: 30,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[4, "desc"]],
                columnDefs: [
                    { orderable: false, targets: [0, 1] }
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
    .flex-1 { flex: 1; }
</style>
@endpush