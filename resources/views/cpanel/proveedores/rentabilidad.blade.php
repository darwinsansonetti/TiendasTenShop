@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Rentabilidad de Proveedores')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    // Colores para Rentabilidad (Teal/Verde)
    $hdrBg = 'linear-gradient(135deg,#14b8a6,#0d9488)';
    $hdrIcon = 'cash-stack';
    $hdrTitle = 'Rentabilidad de Proveedores';
    $hdrSubtitle = 'Análisis de rentabilidad por proveedor';
    
    // Fechas desde el controlador
    $fechaInicio = $fechaInicio ?? Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = $fechaFin ?? Carbon::now()->format('Y-m-d');
    
    // Totalizar
    $totalCompras = 0;
    $totalVentas = 0;
    $totalUtilidad = 0;
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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Rentabilidad</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD FILTROS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        {{-- Buscador --}}
                        <div class="col-md-4">
                            <label for="buscadorProveedor" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-search me-1"></i>Buscar Proveedor
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text"
                                       id="buscadorProveedor"
                                       class="form-control border-start-0"
                                       placeholder="Nombre o código del proveedor..."
                                       autocomplete="off">
                                <button class="btn btn-light border" type="button" id="limpiarBuscador"
                                        style="font-size:0.85rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Fecha Inicio --}}
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar-start me-1"></i>Desde
                            </label>
                            <input type="date" 
                                   name="fecha_inicio" 
                                   id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>

                        {{-- Fecha Fin --}}
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar-end me-1"></i>Hasta
                            </label>
                            <input type="date" 
                                   name="fecha_fin" 
                                   id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-2">
                            <button type="submit" class="btn w-100 fw-semibold text-white"
                                    style="background:{{ $hdrBg }};font-size:0.85rem;border:none;">
                                <i class="bi bi-filter me-1"></i>Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad') }}" 
                               class="btn btn-light border w-100 fw-semibold"
                               style="font-size:0.85rem;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- COLLAPSIBLE DE ESTADÍSTICAS GENERALES --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};cursor:pointer;" 
                 data-bs-toggle="collapse" data-bs-target="#estadisticasCollapse" aria-expanded="false" 
                 aria-controls="estadisticasCollapse" id="btnEstadisticas">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        📊 Estadísticas Generales
                        <span class="badge bg-white ms-2 fw-semibold" style="font-size:0.7rem;color:#0d9488;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                        </span>
                    </h6>
                    <div class="text-white">
                        <i class="bi bi-chevron-down" id="chevronIcon" style="transition: transform 0.3s ease;"></i>
                        <span class="ms-2" style="font-size:0.8rem;">Mostrar estadísticas</span>
                    </div>
                </div>
            </div>
            <div class="collapse" id="estadisticasCollapse">
                <div class="card-body" style="background:#f8fafc;">
                    {{-- Fechas del período --}}
                    <div class="alert alert-info mb-4" style="border-left:4px solid #14b8a6;">
                        <i class="bi bi-calendar-range me-2"></i>
                        <strong>Período analizado:</strong> 
                        {{ Carbon::parse($fechaInicio)->format('d/m/Y H:i') }} - 
                        {{ Carbon::parse($fechaFin)->format('d/m/Y H:i') }}
                        <span class="badge bg-info ms-2">
                            {{ ceil(Carbon::parse($fechaFin)->diffInDays(Carbon::parse($fechaInicio))) }} días
                        </span>
                    </div>

                    {{-- ================================================ --}}
                    {{-- TOP PROVEEDORES --}}
                    {{-- ================================================ --}}
                    <div class="row g-3 mb-4">
                        {{-- Top 3 por Utilidad --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="bi bi-cash-stack me-2" style="color:#198754;"></i>
                                        Top 3 Proveedores por Utilidad ($)
                                        <span class="badge bg-success ms-2">💰 Mayor impacto</span>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    @if($topPorUtilidad && count($topPorUtilidad) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                                        <th class="ps-3 py-2 text-muted fw-semibold" style="font-size:0.7rem;">#</th>
                                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.7rem;">PROVEEDOR</th>
                                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">VENTAS</th>
                                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">UTILIDAD</th>
                                                        <th class="pe-3 py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">RENTABILIDAD</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topPorUtilidad as $index => $item)
                                                        @php
                                                            $medalla = $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉');
                                                            $colorFila = $index == 0 ? 'rgba(255,215,0,0.08)' : ($index == 1 ? 'rgba(192,192,192,0.08)' : 'rgba(205,127,50,0.08)');
                                                        @endphp
                                                        <tr style="border-bottom:1px solid #f1f5f9;background:{{ $colorFila }};">
                                                            <td class="ps-3 text-center" style="font-size:1.2rem;">{{ $medalla }}</td>
                                                            <td>
                                                                <span class="fw-semibold text-dark">{{ $item->proveedor->Nombre ?? 'N/A' }}</span>
                                                                <br>
                                                                <small class="text-muted" style="font-size:0.6rem;">
                                                                    Código: {{ $item->proveedor->ProveedorId ?? 'N/A' }}
                                                                </small>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-semibold">${{ number_format($item->ventas, 2) }}</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold" style="color:#198754;font-size:1.05rem;">
                                                                    ${{ number_format($item->utilidad, 2) }}
                                                                </span>
                                                            </td>
                                                            <td class="pe-3 text-end">
                                                                <span class="badge rounded-pill px-2 py-1" 
                                                                    style="background:{{ $item->rentabilidad >= 30 ? '#22c55e' : ($item->rentabilidad >= 10 ? '#eab308' : '#ef4444') }};color:#fff;font-size:0.7rem;">
                                                                    {{ number_format($item->rentabilidad, 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <p class="text-muted mb-0">No hay datos disponibles</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Top 3 por Rentabilidad % --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="bi bi-percent me-2" style="color:#ffc107;"></i>
                                        Top 3 Proveedores por Rentabilidad (%)
                                        <span class="badge bg-warning ms-2">📈 Mayor eficiencia</span>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    @if($topPorRentabilidad && count($topPorRentabilidad) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                                        <th class="ps-3 py-2 text-muted fw-semibold" style="font-size:0.7rem;">#</th>
                                                        <th class="py-2 text-muted fw-semibold" style="font-size:0.7rem;">PROVEEDOR</th>
                                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">VENTAS</th>
                                                        <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">RENTABILIDAD</th>
                                                        <th class="pe-3 py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">UTILIDAD</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($topPorRentabilidad as $index => $item)
                                                        @php
                                                            $medalla = $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉');
                                                            $colorFila = $index == 0 ? 'rgba(255,215,0,0.08)' : ($index == 1 ? 'rgba(192,192,192,0.08)' : 'rgba(205,127,50,0.08)');
                                                        @endphp
                                                        <tr style="border-bottom:1px solid #f1f5f9;background:{{ $colorFila }};">
                                                            <td class="ps-3 text-center" style="font-size:1.2rem;">{{ $medalla }}</td>
                                                            <td>
                                                                <span class="fw-semibold text-dark">{{ $item->proveedor->Nombre ?? 'N/A' }}</span>
                                                                <br>
                                                                <small class="text-muted" style="font-size:0.6rem;">
                                                                    Código: {{ $item->proveedor->ProveedorId ?? 'N/A' }}
                                                                </small>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-semibold">${{ number_format($item->ventas, 2) }}</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold" style="color:#22c55e;font-size:1.05rem;">
                                                                    {{ number_format($item->rentabilidad, 2) }}%
                                                                </span>
                                                            </td>
                                                            <td class="pe-3 text-end">
                                                                <span class="fw-semibold" style="color:#198754;">
                                                                    ${{ number_format($item->utilidad, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <p class="text-muted mb-0">No hay datos disponibles</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas Avanzadas --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-4">
                                    <h4 class="display-6 fw-bold" style="color:#6c757d;">
                                        {{ $estadisticas['totalProveedores'] ?? 0 }}
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-building me-1"></i>
                                        Total Proveedores
                                    </p>
                                    <div class="progress mt-2" style="height:4px;">
                                        <div class="progress-bar bg-secondary" style="width:100%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-4">
                                    <h4 class="display-6 fw-bold" style="color:#198754;">
                                        {{ $estadisticas['proveedoresConVentas'] ?? 0 }}
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Proveedores con Ventas
                                    </p>
                                    <div class="progress mt-2" style="height:4px;">
                                        <div class="progress-bar bg-success" 
                                             style="width:{{ ($estadisticas['totalProveedores'] ?? 1) > 0 ? (($estadisticas['proveedoresConVentas'] ?? 0) / ($estadisticas['totalProveedores'] ?? 1)) * 100 : 0 }}%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-4">
                                    <h4 class="display-6 fw-bold" style="color:#dc3545;">
                                        {{ $estadisticas['proveedoresSinVentas'] ?? 0 }}
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Proveedores sin Ventas
                                    </p>
                                    <div class="progress mt-2" style="height:4px;">
                                        <div class="progress-bar bg-danger" 
                                             style="width:{{ ($estadisticas['totalProveedores'] ?? 1) > 0 ? (($estadisticas['proveedoresSinVentas'] ?? 0) / ($estadisticas['totalProveedores'] ?? 1)) * 100 : 0 }}%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas de Rentabilidad --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm" style="border-top:4px solid #198754;">
                                <div class="card-body">
                                    <p class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="bi bi-arrow-up me-1"></i>Máxima Rentabilidad
                                    </p>
                                    <h4 class="fw-bold text-success">
                                        {{ $estadisticas['maxRentabilidad'] ?? 0 }}%
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm" style="border-top:4px solid #dc3545;">
                                <div class="card-body">
                                    <p class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="bi bi-arrow-down me-1"></i>Mínima Rentabilidad
                                    </p>
                                    <h4 class="fw-bold text-danger">
                                        {{ $estadisticas['minRentabilidad'] ?? 0 }}%
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm" style="border-top:4px solid #ffc107;">
                                <div class="card-body">
                                    <p class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="bi bi-calculator me-1"></i>Promedio Rentabilidad
                                    </p>
                                    <h4 class="fw-bold text-warning">
                                        {{ $estadisticas['promedioRentabilidad'] ?? 0 }}%
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm" style="border-top:4px solid #0dcaf0;">
                                <div class="card-body">
                                    <p class="text-muted mb-1" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.5px;">
                                        <i class="bi bi-bar-chart-line me-1"></i>Mediana Rentabilidad
                                    </p>
                                    <h4 class="fw-bold text-info">
                                        {{ $estadisticas['medianaRentabilidad'] ?? 0 }}%
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gráfico --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="bi bi-bar-chart-fill me-2" style="color:#14b8a6;"></i>
                                        Distribución de Rentabilidad por Proveedor
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="rentabilidadChart" style="height:300px;max-height:300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PROVEEDORES CON RENTABILIDAD --}}
        {{-- ================================================ --}}
        @if($proveedoresMercancia && count($proveedoresMercancia) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>
                        Rentabilidad por Proveedor
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;color:#0d9488;">
                            {{ count($proveedoresMercancia) }}
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
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaProveedores">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:70px;">LOGO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="nombre"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:180px;">
                                    PROVEEDOR <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="compras"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    COMPRAS (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="ventas"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    VENTAS (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="utilidad"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    UTILIDAD (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="rentabilidad"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:120px;">
                                    RENTABILIDAD <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold"
                                    style="font-size:0.75rem;letter-spacing:.06em;width:80px;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedoresMercancia as $proveedor)
                                @php
                                    $proveedorId = $proveedor->ProveedorId;
                                    $nombre      = $proveedor->Nombre ?? 'N/A';
                                    $rifCedula   = $proveedor->RifCedula ?? '';
                                    $email       = $proveedor->CorreoElectronico ?? 'N/A';
                                    $urlImagen   = $proveedor->UrlImagen ?? '';
                                    
                                    // Datos de rentabilidad desde el controlador
                                    $datos = $datosRentabilidad[$proveedorId] ?? (object) [
                                        'compras' => 0,
                                        'ventas' => 0,
                                        'utilidad' => 0,
                                        'rentabilidad' => 0
                                    ];
                                    
                                    $compras = $datos->compras ?? 0;
                                    $ventas = $datos->ventas ?? 0;
                                    $utilidad = $datos->utilidad ?? 0;
                                    $rentabilidad = $datos->rentabilidad ?? 0;
                                    
                                    // Acumular totales
                                    $totalCompras += $compras;
                                    $totalVentas += $ventas;
                                    $totalUtilidad += $utilidad;
                                    
                                    // Color según rentabilidad
                                    $rentabilidadColor = $rentabilidad >= 30 ? '#22c55e' : ($rentabilidad >= 10 ? '#eab308' : '#ef4444');
                                    $rentabilidadText = $rentabilidad >= 30 ? 'Alta' : ($rentabilidad >= 10 ? 'Media' : 'Baja');

                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/proveedores/',
                                        $urlImagen,
                                        'assets/img/adminlte/img/proveedor_default.png'
                                    );
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    {{-- Logo con zoom --}}
                                    <td class="ps-4 py-3 text-center">
                                        <img src="{{ $imgSrc }}"
                                             loading="lazy" 
                                             alt="{{ $nombre }}"
                                             class="img-zoomable"
                                             style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid #e2e8f0;cursor:zoom-in;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgSrc }}"
                                             data-description="{{ $nombre }}">
                                    </td>

                                    {{-- Nombre + código --}}
                                    <td class="py-3" data-order="{{ $nombre }}">
                                        <div>
                                            <p class="mb-0 fw-semibold text-dark">{{ $nombre }}</p>
                                            <small class="text-muted" style="font-size:0.7rem;">
                                                Código: <code style="font-size:0.7rem;color:#14b8a6;">{{ $proveedorId }}</code>
                                            </small>
                                        </div>
                                    </td>

                                    {{-- Compras --}}
                                    <td class="py-3 text-end" data-order="{{ $compras }}">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            ${{ number_format($compras, 2) }}
                                        </span>
                                    </td>

                                    {{-- Ventas --}}
                                    <td class="py-3 text-end" data-order="{{ $ventas }}">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            ${{ number_format($ventas, 2) }}
                                        </span>
                                    </td>

                                    {{-- Utilidad --}}
                                    <td class="py-3 text-end" data-order="{{ $utilidad }}">
                                        <span class="fw-semibold" style="color:{{ $utilidad >= 0 ? '#22c55e' : '#ef4444' }};">
                                            ${{ number_format($utilidad, 2) }}
                                        </span>
                                    </td>

                                    {{-- Rentabilidad --}}
                                    <td class="py-3 text-end" data-order="{{ $rentabilidad }}">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="fw-bold" style="color:{{ $rentabilidadColor }};">
                                                {{ number_format($rentabilidad, 2) }}%
                                            </span>
                                            <span class="badge rounded-pill px-2 py-1"
                                                  style="background:{{ $rentabilidadColor }};color:#fff;font-size:0.6rem;">
                                                {{ $rentabilidadText }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Acción --}}
                                    <td class="pe-4 py-3 text-center">
                                        <a href="{{ route('cpanel.proveedor.mercancia.rentabilidad.detalle', [
                                            'id' => $proveedorId,  // Cambiar de 'proveedorId' a 'id'
                                            'fecha_inicio' => $fechaInicio,
                                            'fecha_fin' => $fechaFin
                                        ]) }}"
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                            title="Ver detalle" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-building me-1"></i>
                        {{ count($proveedoresMercancia) }} proveedor{{ count($proveedoresMercancia) != 1 ? 'es' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Período: {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>

        @else

        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="d-flex flex-column align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:64px;height:64px;background:rgba(20,184,166,0.08);">
                        <i class="bi bi-building"
                           style="font-size:1.8rem;opacity:.5;color:#0d9488;"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-0">No hay proveedores registrados</p>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        No se encontraron proveedores de mercancía activos en el sistema.
                    </p>
                </div>
            </div>
        </div>

        @endif

    </div>
</div>

@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // COLLAPSIBLE - CAMBIAR ICONO
        // ==========================
        const collapseElement = document.getElementById('estadisticasCollapse');
        const chevronIcon = document.getElementById('chevronIcon');
        
        if (collapseElement && chevronIcon) {
            collapseElement.addEventListener('show.bs.collapse', function () {
                chevronIcon.style.transform = 'rotate(180deg)';
                // Cambiar texto del botón
                const btnText = document.querySelector('#btnEstadisticas .text-white span.ms-2');
                if (btnText) btnText.textContent = 'Ocultar estadísticas';
            });
            
            collapseElement.addEventListener('hide.bs.collapse', function () {
                chevronIcon.style.transform = 'rotate(0deg)';
                const btnText = document.querySelector('#btnEstadisticas .text-white span.ms-2');
                if (btnText) btnText.textContent = 'Mostrar estadísticas';
            });
        }

        // ==========================
        // BUSCADOR DE PROVEEDORES
        // ==========================
        const buscador = document.getElementById('buscadorProveedor');
        const tabla = document.getElementById('tablaProveedores');
        const limpiarBtn = document.getElementById('limpiarBuscador');

        if (buscador && tabla) {
            function filtrarTabla() {
                const textoBusqueda = buscador.value.toLowerCase().trim();
                const filas = tabla.querySelectorAll('tbody tr');
                let filasVisibles = 0;

                filas.forEach(fila => {
                    const celdaNombre = fila.children[1];
                    if (celdaNombre) {
                        const textoNombre = celdaNombre.textContent.toLowerCase();

                        if (textoBusqueda === '' || textoNombre.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });

                const tbody = tabla.querySelector('tbody');
                let mensajeNoResultados = document.getElementById('mensajeNoResultados');

                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultados';
                        const colspan = tabla.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="bi bi-search me-2"></i>
                                No se encontraron proveedores con el nombre "${buscador.value}"
                            </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }

            buscador.addEventListener('input', filtrarTabla);

            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function() {
                    buscador.value = '';
                    filtrarTabla();
                    buscador.focus();
                });
            }
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaProveedores');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }

                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
                const filasReales = filas.filter(fila => fila.id !== 'mensajeNoResultados');

                filasReales.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];

                    if (!tdA || !tdB) return 0;

                    const valorA = tdA.dataset.order || tdA.innerText.trim();
                    const valorB = tdB.dataset.order || tdB.innerText.trim();

                    // Detectar si es numérico (tiene $ o %)
                    const esNumero = !isNaN(parseFloat(valorA.replace(/[$,%]/g, ''))) && 
                                     !isNaN(parseFloat(valorB.replace(/[$,%]/g, '')));
                    
                    if (esNumero) {
                        const numA = parseFloat(valorA.replace(/[$,%]/g, '')) || 0;
                        const numB = parseFloat(valorB.replace(/[$,%]/g, '')) || 0;
                        return asc ? numA - numB : numB - numA;
                    }

                    return asc
                        ? valorA.toString().localeCompare(valorB.toString())
                        : valorB.toString().localeCompare(valorA.toString());
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));

                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }
        })();

        // ==========================
        // GRÁFICO DE RENTABILIDAD
        // ==========================
        const ctx = document.getElementById('rentabilidadChart');
        if (ctx) {
            // Preparar datos desde PHP
            const proveedores = @json($proveedoresMercancia);
            const datosRentabilidad = @json($datosRentabilidad);
            
            // Extraer labels y data
            const labels = proveedores.map(function(p) {
                return p.Nombre || 'N/A';
            });
            
            const data = proveedores.map(function(p) {
                return datosRentabilidad[p.ProveedorId] ? datosRentabilidad[p.ProveedorId].rentabilidad : 0;
            });

            const colors = data.map(function(v) {
                return v >= 30 ? 'rgba(34, 197, 94, 0.7)' : 
                       (v >= 10 ? 'rgba(234, 179, 8, 0.7)' : 'rgba(239, 68, 68, 0.7)');
            });

            const borderColors = data.map(function(v) {
                return v >= 30 ? '#22c55e' : 
                       (v >= 10 ? '#eab308' : '#ef4444');
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Rentabilidad (%)',
                        data: data,
                        backgroundColor: colors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.parsed.y || 0;
                                    return label + ': ' + value.toFixed(2) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // ==========================
    // ZOOM DE IMAGEN
    // ==========================
    function zoomImagen(img) {
        Swal.fire({
            imageUrl: img.src,
            imageAlt: img.alt,
            title: img.alt,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '2em',
            background: '#fff',
            customClass: {
                image: 'img-fluid rounded'
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaProveedores tbody tr:hover { background-color: #f8fafc; }
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.18); }
    thead th.sortable:hover { background-color: #f1f5f9; }
    
    .text-teal { color: #14b8a6; }
    .badge { font-weight: 500; }

    /* Estilos para el collapsible */
    #btnEstadisticas {
        transition: background-color 0.3s ease;
    }
    #btnEstadisticas:hover {
        filter: brightness(1.05);
    }
    #btnEstadisticas .bi-chevron-down {
        transition: transform 0.3s ease;
    }
    .collapse {
        transition: all 0.3s ease;
    }
    
    /* Scroll en el contenido del collapse */
    .collapse .card-body {
        max-height: 80vh;
        overflow-y: auto;
    }
    .collapse .card-body::-webkit-scrollbar {
        width: 6px;
    }
    .collapse .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    .collapse .card-body::-webkit-scrollbar-thumb {
        background: #14b8a6;
        border-radius: 3px;
    }
    .collapse .card-body::-webkit-scrollbar-thumb:hover {
        background: #0d9488;
    }
</style>
@endpush