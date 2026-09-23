@extends('layout.layout_dashboard')

@section('title', 'Consolidado Bóveda')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'safe';
    $hdrTitle = 'Consolidado Bóveda';
    $hdrSubtitle = 'Totales generales de la bóveda (Oficina Principal)';
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
                    <li class="breadcrumb-item active">Consolidado Bóveda</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- FILTROS DE FECHA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.boveda.consolidado') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-3">
                            <label for="sucursal_id" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-building me-1" style="color:#8b5cf6;"></i>Sucursal
                            </label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select">
                                <option value="">Todas las sucursales</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->ID }}" {{ $sucursalId == $suc->ID ? 'selected' : '' }}>
                                        {{ $suc->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn w-100 fw-semibold text-white"
                                        style="background:{{ $hdrBg }};border:none;">
                                    <i class="bi bi-search me-1"></i> Filtrar
                                </button>
                                <a href="{{ route('cpanel.boveda.consolidado') }}" 
                                   class="btn btn-outline-secondary" 
                                   title="Limpiar filtros">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Fechas rápidas --}}
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted me-2">Rango rápido:</small>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="setRango('hoy')">Hoy</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="setRango('semana')">Última semana</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="setRango('mes')">Este mes</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="setRango('mes_anterior')">Mes anterior</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="setRango('anio')">Este año</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info del rango --}}
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>
                Mostrando datos desde <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</strong>
                hasta <strong>{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong>
                @if($sucursalId != '')
                    - Sucursal: <strong>{{ $sucursales->where('ID', $sucursalId)->first()->Nombre ?? 'N/A' }}</strong>
                @endif
                <span class="d-block text-muted small mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Los montos en Bs se convierten a USD usando la <strong>tasa del día del cierre de cada bóveda</strong>.
                </span>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TARJETAS DE TOTALES GENERALES --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">TOTAL DIVISAS EN BÓVEDA</p>
                                <h3 class="fw-bold mb-0">$ {{ number_format($totalDivisas, 2) }}</h3>
                            </div>
                            <i class="bi bi-cash" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">TOTAL BS. EN BÓVEDA</p>
                                <h3 class="fw-bold mb-0">Bs. {{ number_format($totalBs, 2) }}</h3>
                                <small class="text-white-50 d-block" style="font-size:0.7rem;">
                                    ≈ $ {{ number_format($totalBsUSD, 2) }}
                                </small>
                            </div>
                            <i class="bi bi-cash-stack" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">PRÉSTAMOS PENDIENTES USD</p>
                                <h3 class="fw-bold mb-0">$ {{ number_format($totalPrestamosPendientesUSD, 2) }}</h3>
                            </div>
                            <i class="bi bi-arrow-left-right" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">PRÉSTAMOS PENDIENTES BS.</p>
                                <h3 class="fw-bold mb-0">Bs. {{ number_format($totalPrestamosPendientesBs, 2) }}</h3>
                            </div>
                            <i class="bi bi-arrow-left-right" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- DISPONIBLE REAL EN BÓVEDA (FÍSICO - PRÉSTAMOS) --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#06b6d4 0%,#0891b2 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">DISPONIBLE EN DIVISAS</p>
                                <h3 class="fw-bold mb-0">$ {{ number_format($disponibleDivisas, 2) }}</h3>
                                <small class="text-white-50" style="font-size:0.7rem;">
                                    ${{ number_format($totalDivisasContadas, 2) }} físico − ${{ number_format($totalPrestamosPendientesUSD, 2) }} prestado
                                </small>
                            </div>
                            <i class="bi bi-wallet2" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#06b6d4 0%,#0891b2 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">DISPONIBLE EN BOLÍVARES</p>
                                <h3 class="fw-bold mb-0">Bs. {{ number_format($disponibleBs, 2) }}</h3>
                                <small class="text-white-50 d-block" style="font-size:0.7rem;">
                                    ≈ $ {{ number_format($disponibleBsUSD, 2) }}
                                </small>
                                <small class="text-white-50" style="font-size:0.65rem;">
                                    Bs. {{ number_format($totalBsContados, 2) }} físico − Bs. {{ number_format($totalPrestamosPendientesBs, 2) }} prestado
                                </small>
                            </div>
                            <i class="bi bi-wallet2" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TOTAL CONSOLIDADO EN BOLÍVARES (NETO) --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white-50" style="font-size:0.75rem;">TOTAL CONSOLIDADO EN BOLÍVARES</p>
                                <h3 class="fw-bold mb-0">Bs. {{ number_format($totalConsolidadoBsFinal, 2) }}</h3>
                                <small class="text-white-50 d-block" style="font-size:0.8rem;">
                                    ≈ $ {{ number_format($totalConsolidadoBsUSDFinal, 2) }}
                                </small>
                                <small class="text-white-50 d-block mt-1" style="font-size:0.7rem;">
                                    Bs. {{ number_format($disponibleBs, 2) }} físico
                                    + Bs. {{ number_format($totalPdvSistema, 2) }} PDV
                                    + Bs. {{ number_format($totalOtrosBs, 2) }} otros
                                    + Bs. {{ number_format($totalEfectivoBs, 2) }} efectivo
                                    − Bs. {{ number_format($totalGastosBs, 2) }} gastos
                                </small>
                            </div>
                            <i class="bi bi-cash-coin" style="font-size:2.5rem;opacity:0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- RESUMEN DE EFECTIVO (DIVISAS + BS) --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash me-2"></i>Efectivo en Divisas
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-success mb-0">$ {{ number_format($totalDivisasContadas, 2) }}</h2>
                        <small class="text-muted d-block">Total contado en denominaciones</small>

                        @if($totalPrestamosPendientesUSD > 0)
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted d-block">Prestado: −$ {{ number_format($totalPrestamosPendientesUSD, 2) }}</small>
                                <h4 class="fw-bold text-info mb-0">Disponible: $ {{ number_format($disponibleDivisas, 2) }}</h4>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash-stack me-2"></i>Efectivo en Bolívares
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="fw-bold text-primary mb-0">Bs. {{ number_format($totalBsContados, 2) }}</h2>
                        <small class="text-muted d-block">Total contado en denominaciones</small>
                        <small class="text-info fw-semibold d-block">≈ $ {{ number_format($totalBsContadosUSD, 2) }}</small>

                        @if($totalPrestamosPendientesBs > 0)
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted d-block">Prestado: −Bs. {{ number_format($totalPrestamosPendientesBs, 2) }}</small>
                                <h4 class="fw-bold text-info mb-0">Disponible: Bs. {{ number_format($disponibleBs, 2) }}</h4>
                                <small class="text-info fw-semibold d-block">≈ $ {{ number_format($disponibleBsUSD, 2) }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- ================================================ --}}
            {{-- DIVISAS POR DENOMINACIÓN --}}
            {{-- ================================================ --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                        <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                            <i class="bi bi-cash me-2"></i>Divisas por Denominación
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background:#f0fdf4;">
                                    <tr>
                                        <th class="ps-4 py-2 text-success fw-semibold" style="font-size:0.75rem;">DENOMINACIÓN</th>
                                        <th class="py-2 text-center text-success fw-semibold" style="font-size:0.75rem;">CONTADOS</th>
                                        <th class="py-2 text-center text-danger fw-semibold" style="font-size:0.75rem;">PRESTADOS</th>
                                        <th class="py-2 text-end text-success fw-semibold" style="font-size:0.75rem;">CONTADO</th>
                                        <th class="pe-4 py-2 text-end text-info fw-semibold" style="font-size:0.75rem;">NETO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($divisasPorDenominacion as $den)
                                    <tr>
                                        <td class="ps-4 fw-semibold">$ {{ number_format($den->Denominacion, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ number_format($den->CantidadContada, 0) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($den->CantidadPrestada > 0)
                                                <span class="badge bg-danger">−{{ number_format($den->CantidadPrestada, 0) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">$ {{ number_format($den->MontoContado, 2) }}</td>
                                        <td class="pe-4 text-end fw-bold text-info">$ {{ number_format($den->MontoNeto, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Sin registros</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($divisasPorDenominacion->count() > 0)
                                <tfoot style="background:#f0fdf4;border-top:2px solid #10b981;">
                                    <tr>
                                        <th class="ps-4 py-2 text-end" colspan="3">TOTAL:</th>
                                        <th class="py-2 text-end text-success">$ {{ number_format($totalDivisasContadas, 2) }}</th>
                                        <th class="pe-4 py-2 text-end text-info fw-bold">$ {{ number_format($totalDivisas, 2) }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================================================ --}}
            {{-- BOLÍVARES POR DENOMINACIÓN --}}
            {{-- ================================================ --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                            <i class="bi bi-cash-stack me-2"></i>Bolívares por Denominación
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background:#eff6ff;">
                                    <tr>
                                        <th class="ps-4 py-2 text-primary fw-semibold" style="font-size:0.75rem;">DENOMINACIÓN</th>
                                        <th class="py-2 text-center text-primary fw-semibold" style="font-size:0.75rem;">CONTADOS</th>
                                        <th class="py-2 text-center text-danger fw-semibold" style="font-size:0.75rem;">PRESTADOS</th>
                                        <th class="py-2 text-end text-primary fw-semibold" style="font-size:0.75rem;">CONTADO</th>
                                        <th class="pe-4 py-2 text-end text-info fw-semibold" style="font-size:0.75rem;">NETO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bsPorDenominacion as $den)
                                    <tr>
                                        <td class="ps-4 fw-semibold">Bs. {{ number_format($den->Denominacion, 0) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ number_format($den->CantidadContada, 0) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($den->CantidadPrestada > 0)
                                                <span class="badge bg-danger">−{{ number_format($den->CantidadPrestada, 0) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-semibold">Bs. {{ number_format($den->MontoContado, 2) }}</div>
                                            <small class="text-info">≈ $ {{ number_format($den->MontoContadoUSD, 2) }}</small>
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-info">
                                            <div>Bs. {{ number_format($den->MontoNeto, 2) }}</div>
                                            <small class="text-info">≈ $ {{ number_format($den->MontoNetoUSD, 2) }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Sin registros</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($bsPorDenominacion->count() > 0)
                                <tfoot style="background:#eff6ff;border-top:2px solid #3b82f6;">
                                    <tr>
                                        <th class="ps-4 py-2 text-end" colspan="3">TOTAL:</th>
                                        <th class="py-2 text-end text-primary">
                                            <div>Bs. {{ number_format($totalBsContados, 2) }}</div>
                                            <small class="text-info">≈ $ {{ number_format($totalBsContadosUSD, 2) }}</small>
                                        </th>
                                        <th class="pe-4 py-2 text-end text-info fw-bold">
                                            <div>Bs. {{ number_format($totalBs, 2) }}</div>
                                            <small class="text-info">≈ $ {{ number_format($totalBsUSD, 2) }}</small>
                                        </th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        

        {{-- ================================================ --}}
        {{-- RESUMEN DE GASTOS DEL PERÍODO --}}
        {{-- ================================================ --}}
        @if($gastosPeriodo->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                        <i class="bi bi-receipt-cutoff me-2"></i>Gastos del Período
                    </h6>
                    <span class="badge bg-white text-dark">
                        {{ $gastosPeriodo->count() }} Registros
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#fef2f2;">
                            <tr>
                                <th class="ps-4 py-2 text-danger fw-semibold" style="font-size:0.75rem;">CATEGORÍA</th>
                                <th class="py-2 text-center text-danger fw-semibold" style="font-size:0.75rem;">CANTIDAD</th>
                                <th class="py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">TOTAL USD</th>
                                <th class="pe-4 py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">TOTAL BS.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastosPorCategoria as $gasto)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-semibold">{{ $gasto->Categoria }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $gasto->Cantidad }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-danger">$ {{ number_format($gasto->TotalUSD, 2) }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <span class="text-danger">Bs. {{ number_format($gasto->TotalBs, 2) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#fef2f2;border-top:2px solid #ef4444;">
                            <tr>
                                <th colspan="2" class="ps-4 py-2 text-end">TOTAL GASTOS:</th>
                                <th class="py-2 text-end text-danger">$ {{ number_format($totalGastosUSD, 2) }}</th>
                                <th class="pe-4 py-2 text-end text-danger">Bs. {{ number_format($totalGastosBs, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- TOTALES POR PUNTO DE VENTA --}}
        {{-- ================================================ --}}
        @if($puntosVentaTotales->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                        <i class="bi bi-credit-card me-2"></i>Totales por Punto de Venta (Bs. + estimado USD)
                    </h6>
                    <span class="badge bg-white text-dark">
                        {{ $puntosVentaTotales->count() }} Puntos
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">PUNTO DE VENTA</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">BANCO</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">SISTEMA</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">DEPOSITADO</th>
                                <th class="pe-4 py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($puntosVentaTotales as $pdv)
                            <tr>
                                <td class="ps-4">{{ $pdv->sucursal_nombre ?? 'N/A' }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $pdv->pdv_descripcion }}</span>
                                    <small class="d-block text-muted">{{ $pdv->pdv_codigo ?? '' }}</small>
                                </td>
                                <td>{{ $pdv->banco_nombre ?? 'N/A' }}</td>
                                <td class="text-end">
                                    <div>Bs. {{ number_format($pdv->TotalSistema, 2) }}</div>
                                    <small class="text-info">≈ $ {{ number_format($pdv->TotalSistemaUSD, 2) }}</small>
                                </td>
                                <td class="text-end">
                                    <div>Bs. {{ number_format($pdv->TotalDepositado, 2) }}</div>
                                    <small class="text-info">≈ $ {{ number_format($pdv->TotalDepositadoUSD, 2) }}</small>
                                </td>
                                <td class="pe-4 text-end fw-bold">
                                    @if(abs($pdv->TotalDiferencia) < 0.01)
                                        <div class="text-success">Bs. 0.00</div>
                                        <small class="text-success">≈ $ 0.00</small>
                                    @else
                                        <div class="text-danger">Bs. {{ number_format($pdv->TotalDiferencia, 2) }}</div>
                                        <small class="text-danger">≈ $ {{ number_format($pdv->TotalDiferenciaUSD, 2) }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#f8fafc;border-top:2px solid #8b5cf6;">
                            <tr>
                                <th colspan="3" class="ps-4 py-2 text-end">TOTALES:</th>
                                <th class="py-2 text-end">
                                    <div>Bs. {{ number_format($totalPdvSistema, 2) }}</div>
                                    <small class="text-info">≈ $ {{ number_format($totalPdvSistemaUSD, 2) }}</small>
                                </th>
                                <th class="py-2 text-end">
                                    <div>Bs. {{ number_format($totalPdvDepositado, 2) }}</div>
                                    <small class="text-info">≈ $ {{ number_format($totalPdvDepositadoUSD, 2) }}</small>
                                </th>
                                <th class="pe-4 py-2 text-end fw-bold">
                                    @php $difTotal = $totalPdvSistema - $totalPdvDepositado; @endphp
                                    @php $difTotalUSD = $totalPdvSistemaUSD - $totalPdvDepositadoUSD; @endphp
                                    @if(abs($difTotal) < 0.01)
                                        <div class="text-success">Bs. 0.00</div>
                                        <small class="text-success">≈ $ 0.00</small>
                                    @else
                                        <div class="text-danger">Bs. {{ number_format($difTotal, 2) }}</div>
                                        <small class="text-danger">≈ $ {{ number_format($difTotalUSD, 2) }}</small>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- OTROS CONCEPTOS (Biopago, Transferencia, etc.) --}}
        {{-- ================================================ --}}
        @if($otrosTotales->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                    <i class="bi bi-wallet2 me-2"></i>Totales por Otros Conceptos
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#fef3c7;">
                            <tr>
                                <th class="ps-4 py-2 text-warning fw-semibold" style="font-size:0.75rem;">CONCEPTO</th>
                                <th class="py-2 text-center text-warning fw-semibold" style="font-size:0.75rem;">MONEDA</th>
                                <th class="py-2 text-end text-warning fw-semibold" style="font-size:0.75rem;">SISTEMA</th>
                                <th class="py-2 text-end text-warning fw-semibold" style="font-size:0.75rem;">DEPOSITADO</th>
                                <th class="pe-4 py-2 text-end text-warning fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otrosTotales as $otro)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-{{ $otro->Tipo == 1 ? 'info' : ($otro->Tipo == 2 ? 'primary' : ($otro->Tipo == 3 ? 'warning' : 'success')) }}">
                                        {{ $otro->TipoNombre }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $otro->Moneda == 'USD' ? 'success' : 'primary' }}">
                                        {{ $otro->Moneda }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div>{{ $otro->Simbolo }} {{ number_format($otro->TotalSistema, 2) }}</div>
                                    @if($otro->Moneda == 'Bs')
                                        <small class="text-info">≈ $ {{ number_format($otro->TotalSistemaUSD, 2) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div>{{ $otro->Simbolo }} {{ number_format($otro->TotalDepositado, 2) }}</div>
                                    @if($otro->Moneda == 'Bs')
                                        <small class="text-info">≈ $ {{ number_format($otro->TotalDepositadoUSD, 2) }}</small>
                                    @endif
                                </td>
                                <td class="pe-4 text-end fw-bold">
                                    @if(abs($otro->TotalDiferencia) < 0.01)
                                        <div class="text-success">{{ $otro->Simbolo }} 0.00</div>
                                        @if($otro->Moneda == 'Bs')
                                            <small class="text-success">≈ $ 0.00</small>
                                        @endif
                                    @else
                                        <div class="text-danger">{{ $otro->Simbolo }} {{ number_format($otro->TotalDiferencia, 2) }}</div>
                                        @if($otro->Moneda == 'Bs')
                                            <small class="text-danger">≈ $ {{ number_format($otro->TotalDiferenciaUSD, 2) }}</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- PRÉSTAMOS PENDIENTES POR SUCURSAL --}}
        {{-- ================================================ --}}
        @if($prestamosPendientes->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                        <i class="bi bi-arrow-left-right me-2"></i>Préstamos Pendientes por Sucursal
                    </h6>
                    <a href="{{ route('cpanel.boveda.prestamos') }}" 
                       class="btn btn-sm text-white"
                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.75rem;">
                        <i class="bi bi-list-ul me-1"></i> Ver Todos
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#fef2f2;">
                            <tr>
                                <th class="ps-4 py-2 text-danger fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-2 text-center text-danger fw-semibold" style="font-size:0.75rem;">PRÉSTAMOS</th>
                                <th class="py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">PENDIENTE USD</th>
                                <th class="pe-4 py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">PENDIENTE BS.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prestamosPendientes as $prestamo)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-semibold">{{ $prestamo->sucursal_nombre }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $prestamo->CantidadPrestamos }}</span>
                                </td>
                                <td class="text-end">
                                    @if($prestamo->TotalPendienteUSD > 0)
                                        <span class="fw-bold text-danger">$ {{ number_format($prestamo->TotalPendienteUSD, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    @if($prestamo->TotalPendienteBs > 0)
                                        <span class="fw-bold text-danger">Bs. {{ number_format($prestamo->TotalPendienteBs, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#fef2f2;border-top:2px solid #ef4444;">
                            <tr>
                                <th colspan="2" class="ps-4 py-2 text-end">TOTAL PENDIENTE:</th>
                                <th class="py-2 text-end text-danger">$ {{ number_format($totalPrestamosPendientesUSD, 2) }}</th>
                                <th class="pe-4 py-2 text-end text-danger">Bs. {{ number_format($totalPrestamosPendientesBs, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ================================================ --}}
        {{-- HISTORIAL DE BÓVEDAS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                        <i class="bi bi-clock-history me-2"></i>Últimos Cierres de Bóveda
                    </h6>
                    <a href="{{ route('cpanel.boveda.index') }}" 
                       class="btn btn-sm text-white"
                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.75rem;">
                        <i class="bi bi-list-ul me-1"></i> Ver Todos
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">ID</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">FECHA</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;">ESTATUS</th>
                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;">CONCILIACIÓN</th>
                                <th class="pe-4 py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historialBovedas as $boveda)
                            <tr>
                                <td class="ps-4">
                                    <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:0.75rem;font-weight:bold;">
                                        #{{ $boveda->BovedaId }}
                                    </code>
                                </td>
                                <td>{{ $boveda->FechaFormateada }}</td>
                                <td>{{ $boveda->sucursal_nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $boveda->EstatusBadge }}" style="font-size:0.7rem;">
                                        {{ $boveda->EstatusTexto }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $boveda->ConciliacionBadge }}" style="font-size:0.7rem;">
                                        {{ $boveda->ConciliacionTexto }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('cpanel.boveda.detalle', $boveda->BovedaId) }}" 
                                       class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                       style="width:28px;height:28px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                       title="Ver detalle">
                                        <i class="bi bi-eye" style="font-size:0.75rem;"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox me-2"></i>
                                    No hay cierres de bóveda registrados
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

@endsection

@section('js')
<script>
    function setRango(tipo) {
        const hoy = new Date();
        let fechaInicio, fechaFin;

        switch(tipo) {
            case 'hoy':
                fechaInicio = fechaFin = hoy.toISOString().split('T')[0];
                break;
            case 'semana':
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - 7);
                fechaInicio = inicioSemana.toISOString().split('T')[0];
                fechaFin = hoy.toISOString().split('T')[0];
                break;
            case 'mes':
                fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                fechaFin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                break;
            case 'mes_anterior':
                const mesAnterior = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
                fechaInicio = new Date(mesAnterior.getFullYear(), mesAnterior.getMonth(), 1).toISOString().split('T')[0];
                fechaFin = new Date(mesAnterior.getFullYear(), mesAnterior.getMonth() + 1, 0).toISOString().split('T')[0];
                break;
            case 'anio':
                fechaInicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                fechaFin = new Date(hoy.getFullYear(), 11, 31).toISOString().split('T')[0];
                break;
        }

        document.getElementById('fecha_inicio').value = fechaInicio;
        document.getElementById('fecha_fin').value = fechaFin;
        document.getElementById('formFiltros').submit();
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            const fechaFin = document.getElementById('fecha_fin');
            if (this.value > fechaFin.value) {
                fechaFin.value = this.value;
            }
        });

        document.getElementById('fecha_fin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            if (this.value < fechaInicio.value) {
                fechaInicio.value = this.value;
            }
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 500px; overflow-y: auto; }
</style>
@endpush