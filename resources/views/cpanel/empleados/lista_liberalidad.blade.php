@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Liberalidad')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-heart text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Liberalidad</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Registro de beneficios y liberalidades</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liberalidad</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- FILTRO DE PERÍODO --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form action="{{ route('cpanel.empleados.lista_liberalidad') }}" method="GET" id="filtroForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label for="periodo" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1 text-success"></i>Período
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar-month text-success"></i>
                                </span>
                                <input type="month"
                                       name="periodo"
                                       id="periodo"
                                       class="form-control border-start-0"
                                       value="{{ request('periodo', sprintf('%04d-%02d', $filtroMesAnio['anio'], $filtroMesAnio['mes'])) }}">
                            </div>
                        </div>
                        <div class="col-auto" style="padding-top:1.9rem;">
                            <button type="submit" class="btn btn-success fw-semibold px-4">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                            <a href="{{ route('cpanel.empleados.lista_liberalidad') }}"
                               class="btn btn-light border fw-semibold ms-2">
                                <i class="bi bi-arrow-repeat me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- BRANCH A: LIBERALIDAD CERRADA (DTO guardado) --}}
        {{-- ================================================ --}}
        @if($liberalidadDTO && $liberalidadDTO->detalles && $liberalidadDTO->detalles->count() > 0)

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-chart-line me-2"></i>Reporte de Liberalidad
                        </h6>
                        <span class="badge rounded-pill fw-semibold"
                              style="background:rgba(239,68,68,0.85);color:#fff;font-size:0.75rem;">
                            <i class="bi bi-lock me-1"></i>Cerrada
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group input-group-sm" style="width:220px;">
                            <span class="input-group-text"
                                  style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.3);">
                                <i class="bi bi-search text-white" style="font-size:0.8rem;"></i>
                            </span>
                            <input type="text"
                                   id="buscadorEmpleado"
                                   class="form-control"
                                   style="background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.3);color:#fff;font-size:0.82rem;"
                                   placeholder="Buscar empleado..."
                                   autocomplete="off">
                            <button class="btn" type="button" id="limpiarBuscador"
                                    style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.3);color:#fff;">
                                <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="pdfTablaLiberalidad()">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </button>
                        <button type="button" class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                onclick="exportarExcelLiberalidad()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaLiberalidad">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:70px;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="empleado" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    EMPLEADO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="sucursal" style="font-size:0.75rem;letter-spacing:.06em;width:180px;cursor:pointer;">
                                    SUCURSAL <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" data-col="unidades" style="font-size:0.75rem;letter-spacing:.06em;width:110px;cursor:pointer;">
                                    UNIDADES <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" data-col="ventas" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                    VENTAS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" data-col="bonos" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                    BONOS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" data-col="deducciones" style="font-size:0.75rem;letter-spacing:.06em;width:140px;cursor:pointer;">
                                    DEDUCCIONES <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="liberalidad" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                    LIBERALIDAD <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="neto" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                    NETO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="pe-3 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:90px;">DETALLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liberalidadDTO->detalles as $index => $detalle)
                                @php
                                    $usuario = $detalle->Usuario ?? null;
                                    $empleado = $detalle->Empleado ?? null;
                                    $entidad = $usuario ?: $empleado;
                                    $usuarioId = $detalle->UsuarioId ?? $detalle->EmpleadoId ?? '';
                                    $nombre = 'Empleado';
                                    $vendedorId = '';
                                    $fotoPerfil = '';
                                    $sucursalId = null;

                                    if ($usuario) {
                                        $nombre = $usuario->NombreCompleto ?? 'Empleado';
                                        $vendedorId = $usuario->VendedorId ?? '';
                                        $fotoPerfil = $usuario->FotoPerfil ?? '';
                                        $sucursalId = $usuario->SucursalId ?? null;
                                    } elseif ($empleado) {
                                        $nombre = $empleado->NombreCompleto ?? 'Empleado';
                                        $vendedorId = $empleado->VendedorId ?? '';
                                        $fotoPerfil = $empleado->FotoPerfil ?? '';
                                        $sucursalId = $empleado->SucursalId ?? null;
                                    }

                                    $sucursalNombre = 'N/A';
                                    if (isset($detalle->Sucursal) && $detalle->Sucursal && isset($detalle->Sucursal->Nombre)) {
                                        $sucursalNombre = $detalle->Sucursal->Nombre;
                                    }
                                    elseif ($entidad && isset($entidad->Sucursal) && $entidad->Sucursal && isset($entidad->Sucursal->Nombre)) {
                                        $sucursalNombre = $entidad->Sucursal->Nombre;
                                    }
                                    elseif ($sucursalId) {
                                        $sucursalNombre = Cache::remember("sucursal_nombre_{$sucursalId}", 3600, function() use ($sucursalId) {
                                            $sucursal = \App\Models\Sucursal::find($sucursalId);
                                            return $sucursal ? $sucursal->Nombre : 'N/A';
                                        });
                                    }

                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );

                                    $bonosUSD = $detalle->OtraLiberalidad ?? 0;
                                    $liberalidadUSD = $detalle->MontoLiberalidad ?? 0;
                                    $netoUSD = $detalle->TotalPagado ?? 0;
                                    $deduccionesUSD = max(0, $liberalidadUSD + $bonosUSD - $netoUSD);
                                    $abonoPrestamo = $detalle->AbonoPrestamo ?? 0;
                                    $deudaPrestamo = $detalle->DeudaPrestamo ?? 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-3">
                                        <img src="{{ $imgSrc }}"
                                             alt="{{ $nombre }}"
                                             class="rounded-circle img-zoomable"
                                             style="width:46px;height:46px;object-fit:cover;border:2px solid #e2e8f0;cursor:zoom-in;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgSrc }}"
                                             data-description="{{ $nombre }}">
                                    </td>
                                    <td data-order="{{ $nombre }}">
                                        <p class="mb-0 fw-bold text-dark">{{ $nombre }}</p>
                                        @if($vendedorId)
                                            <small class="text-muted">
                                                <i class="bi bi-person-badge me-1"></i>{{ $vendedorId }}
                                            </small>
                                        @endif
                                        @if($detalle->EsVendedor ?? false)
                                            <span class="badge rounded-pill ms-1"
                                                  style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Vendedor</span>
                                        @else
                                            <span class="badge rounded-pill ms-1"
                                                  style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Interno</span>
                                        @endif
                                    </td>
                                    <td data-order="{{ $sucursalNombre }}">
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                              style="background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25);font-size:0.78rem;">
                                            <i class="bi bi-shop me-1"></i>{{ $sucursalNombre }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold" data-order="{{ $detalle->Unidades ?? 0 }}">
                                        <span class="badge rounded-pill px-2 py-1"
                                              style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.82rem;">
                                            {{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold text-success" data-order="{{ $detalle->Venta ?? 0 }}">
                                        $ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center" data-order="{{ $bonosUSD }}">
                                        @if($bonosUSD > 0)
                                            <span class="text-success fw-semibold">
                                                <i class="bi bi-gift me-1"></i>$ {{ number_format($bonosUSD, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">$ 0,00</span>
                                        @endif
                                    </td>
                                    <td class="text-center" data-order="{{ $deduccionesUSD }}">
                                        @if($deduccionesUSD > 0)
                                            <span class="text-danger fw-semibold">
                                                <i class="bi bi-dash-circle me-1"></i>- $ {{ number_format($deduccionesUSD, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">$ 0,00</span>
                                        @endif
                                        @if($abonoPrestamo > 0)
                                            <br><small class="text-muted">
                                                <i class="bi bi-credit-card me-1"></i>Préstamo: ${{ number_format($abonoPrestamo, 2) }}
                                            </small>
                                        @endif
                                        @if($deudaPrestamo > 0)
                                            <br><small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>Deuda: ${{ number_format($deudaPrestamo, 2) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark" data-order="{{ $liberalidadUSD }}">
                                        $ {{ number_format($liberalidadUSD, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end" data-order="{{ $netoUSD }}">
                                        <span class="badge rounded-pill px-2 py-1 fw-bold"
                                              style="background:rgba(17,24,39,0.85);color:#fff;font-size:0.82rem;">
                                            <i class="bi bi-calculator me-1"></i>$ {{ number_format($netoUSD, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-center">
                                        <a href="{{ route('liberalidad.detalle', ['id' => $detalle->LiberalidadDetalleId]) }}"
                                           class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                           style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                           title="Ver detalles del empleado" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Período: {{ $liberalidadDTO->FechaInicio->format('d/m/Y') }} — {{ $liberalidadDTO->FechaFinal->format('d/m/Y') }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>
                        Total empleados: {{ $liberalidadDTO->detalles->count() }}
                    </small>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- BRANCH B: LIBERALIDAD ACTIVA (cálculo en tiempo real) --}}
        {{-- ================================================ --}}
        @else
            @if($liberalidad && $liberalidad->detalles && $liberalidad->detalles->count() > 0)

                {{-- Resumen por Sucursal --}}
                @if(isset($liberalidadPorSucursal) && $liberalidadPorSucursal->count() > 0)
                @php
                    $totalGeneral = $liberalidadPorSucursal->sum('MontoLiberalidadSucursal');
                    $totalVendedores = $liberalidadPorSucursal->sum('CantidadVendedores');
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    {{-- Mantener bg-gradient-info para el selector JS: cerrarLiberalidad() busca .card.mb-4:has(.bg-gradient-info) --}}
                    <div class="card-header bg-gradient-info border-0 py-3"
                         style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%) !important;">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-shop me-2"></i>Resumen de Liberalidad por Sucursal
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">SUCURSAL</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:200px;">CANT. VENDEDORES</th>
                                        <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:220px;">MONTO LIBERALIDAD (USD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($liberalidadPorSucursal as $sucursal)
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-4 fw-semibold text-dark">
                                            <i class="bi bi-shop me-2 text-warning"></i>{{ $sucursal['SucursalNombre'] }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill"
                                                  style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.82rem;">
                                                {{ $sucursal['CantidadVendedores'] }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end fw-bold text-success">
                                            $ {{ number_format($sucursal['MontoLiberalidadSucursal'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f0fdf4;border-top:2px solid #bbf7d0;">
                                        <td class="ps-4 py-2 fw-bold text-dark text-end">TOTAL GENERAL:</td>
                                        <td class="py-2 text-center">
                                            <span class="badge rounded-pill fw-bold"
                                                  style="background:rgba(17,24,39,0.85);color:#fff;font-size:0.82rem;">
                                                {{ $totalVendedores }} vendedores
                                            </span>
                                        </td>
                                        <td class="pe-4 py-2 text-end fw-bold text-success">
                                            $ {{ number_format($totalGeneral, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Tabla principal de liberalidad activa --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold text-white">
                                <i class="bi bi-chart-line me-2"></i>Reporte de Liberalidad
                            </h6>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width:220px;">
                                    <span class="input-group-text"
                                          style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.3);">
                                        <i class="bi bi-search text-white" style="font-size:0.8rem;"></i>
                                    </span>
                                    <input type="text"
                                           id="buscadorEmpleado"
                                           class="form-control"
                                           style="background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.3);color:#fff;font-size:0.82rem;"
                                           placeholder="Buscar empleado..."
                                           autocomplete="off">
                                    <button class="btn" type="button" id="limpiarBuscador"
                                            style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.3);color:#fff;">
                                        <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                                    </button>
                                </div>
                                <button type="button"
                                        class="btn btn-sm fw-semibold"
                                        id="btnCerrarLiberalidad"
                                        onclick="cerrarLiberalidad()"
                                        style="background:rgba(255,255,255,0.25);color:#fff;border:1px solid rgba(255,255,255,0.5);font-size:0.8rem;">
                                    <i class="bi bi-lock me-1"></i>Cerrar
                                </button>
                                <button type="button" class="btn btn-sm fw-semibold"
                                        style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                        onclick="pdfTablaLiberalidad()">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-sm fw-semibold"
                                        style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.8rem;"
                                        onclick="exportarExcelLiberalidad()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0" id="tablaLiberalidad">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                        <th class="ps-3 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:70px;">FOTO</th>
                                        <th class="py-3 text-muted fw-semibold sortable" data-col="empleado" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                            EMPLEADO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-muted fw-semibold sortable" data-col="sucursal" style="font-size:0.75rem;letter-spacing:.06em;width:180px;cursor:pointer;">
                                            SUCURSAL <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-center text-muted fw-semibold sortable" data-col="unidades" style="font-size:0.75rem;letter-spacing:.06em;width:110px;cursor:pointer;">
                                            UNIDADES <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-center text-muted fw-semibold sortable" data-col="ventas" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                            VENTAS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-center text-muted fw-semibold sortable" data-col="bonos" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                            BONOS <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-center text-muted fw-semibold sortable" data-col="deducciones" style="font-size:0.75rem;letter-spacing:.06em;width:140px;cursor:pointer;">
                                            DEDUCCIONES <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="py-3 text-end text-muted fw-semibold sortable" data-col="liberalidad" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                            LIBERALIDAD <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                        <th class="pe-3 py-3 text-end text-muted fw-semibold sortable" data-col="neto" style="font-size:0.75rem;letter-spacing:.06em;width:130px;cursor:pointer;">
                                            NETO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($liberalidad->detalles as $index => $detalle)
                                        @php
                                            $usuario = $detalle->Usuario ?? null;
                                            $usuarioId = $detalle->UsuarioId ?? $detalle->EmpleadoId ?? '';
                                            $nombre = $usuario->NombreCompleto ?? 'Empleado';
                                            $vendedorId = $usuario->VendedorId ?? '';

                                            $sucursalNombre = 'N/A';
                                            if ($usuario) {
                                                if (isset($usuario->Sucursal) && is_object($usuario->Sucursal)) {
                                                    $sucursalNombre = $usuario->Sucursal->Nombre ?? 'N/A';
                                                } elseif (isset($usuario->sucursal) && is_object($usuario->sucursal)) {
                                                    $sucursalNombre = $usuario->sucursal->Nombre ?? 'N/A';
                                                } elseif (isset($usuario->SucursalNombre)) {
                                                    $sucursalNombre = $usuario->SucursalNombre;
                                                }
                                            }

                                            $fotoPerfil = $usuario->FotoPerfil ?? '';
                                            $imgSrc = FileHelper::getOrDownloadFile(
                                                'images/usuarios/',
                                                $fotoPerfil,
                                                'assets/img/adminlte/img/default.png'
                                            );

                                            $bonosPendientes = $detalle->bonos_pendientes ?? 0;
                                            $totalBonosUSD = 0;
                                            if (isset($detalle->bonos)) {
                                                $totalBonosUSD = $detalle->bonos->where('EsPagado', 0)->sum('MontoDivisa');
                                            }

                                            $deduccionesPendientes = $detalle->deducciones_pendientes ?? 0;
                                            $totalDeduccionesUSD = 0;
                                            if (isset($detalle->deducciones)) {
                                                $totalDeduccionesUSD = $detalle->deducciones->where('EsPagado', 0)->sum('MontoDivisa');
                                            }

                                            $liberalidadUSD = $detalle->MontoLiberalidad ?? 0;
                                            $netoUSD = $liberalidadUSD + $totalBonosUSD - $totalDeduccionesUSD;
                                        @endphp
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td class="ps-3">
                                                <img src="{{ $imgSrc }}"
                                                     alt="{{ $nombre }}"
                                                     class="rounded-circle img-zoomable"
                                                     style="width:46px;height:46px;object-fit:cover;border:2px solid #e2e8f0;cursor:zoom-in;"
                                                     onclick="zoomImagen(this)"
                                                     data-full-image="{{ $imgSrc }}"
                                                     data-description="{{ $nombre }}">
                                            </td>
                                            <td data-order="{{ $nombre }}">
                                                <p class="mb-0 fw-bold text-dark">{{ $nombre }}</p>
                                                @if($vendedorId)
                                                    <small class="text-muted">
                                                        <i class="bi bi-person-badge me-1"></i>{{ $vendedorId }}
                                                    </small>
                                                @endif
                                                @if($detalle->EsVendedor ?? false)
                                                    <span class="badge rounded-pill ms-1"
                                                          style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Vendedor</span>
                                                @else
                                                    <span class="badge rounded-pill ms-1"
                                                          style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Interno</span>
                                                @endif
                                            </td>
                                            <td data-order="{{ $sucursalNombre }}">
                                                <span class="badge rounded-pill px-2 py-1 fw-semibold"
                                                      style="background:rgba(245,158,11,0.1);color:#92400e;border:1px solid rgba(245,158,11,0.25);font-size:0.78rem;">
                                                    <i class="bi bi-shop me-1"></i>{{ $sucursalNombre }}
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold" data-order="{{ $detalle->Unidades ?? 0 }}">
                                                <span class="badge rounded-pill px-2 py-1"
                                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.82rem;">
                                                    {{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center fw-semibold text-success" data-order="{{ $detalle->Venta ?? 0 }}">
                                                $ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="text-center" data-order="{{ $totalBonosUSD }}">
                                                @if($totalBonosUSD > 0)
                                                    <span class="text-success fw-semibold">
                                                        <i class="bi bi-gift me-1"></i>$ {{ number_format($totalBonosUSD, 2, ',', '.') }}
                                                    </span>
                                                    @if($bonosPendientes > 0)
                                                        <br><small class="text-muted">({{ $bonosPendientes }} pendiente{{ $bonosPendientes != 1 ? 's' : '' }})</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">$ 0,00</span>
                                                @endif
                                            </td>
                                            <td class="text-center" data-order="{{ $totalDeduccionesUSD }}">
                                                @if($totalDeduccionesUSD > 0)
                                                    <span class="text-danger fw-semibold">
                                                        <i class="bi bi-dash-circle me-1"></i>- $ {{ number_format($totalDeduccionesUSD, 2, ',', '.') }}
                                                    </span>
                                                    @if($deduccionesPendientes > 0)
                                                        <br><small class="text-muted">({{ $deduccionesPendientes }} pendiente{{ $deduccionesPendientes != 1 ? 's' : '' }})</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">$ 0,00</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-success" data-order="{{ $liberalidadUSD }}">
                                                <i class="bi bi-gift me-1"></i>$ {{ number_format($liberalidadUSD, 2, ',', '.') }}
                                            </td>
                                            <td class="pe-3 text-end" data-order="{{ $netoUSD }}">
                                                <span class="badge rounded-pill px-2 py-1 fw-bold"
                                                      style="background:rgba(17,24,39,0.85);color:#fff;font-size:0.82rem;">
                                                    <i class="bi bi-calculator me-1"></i>$ {{ number_format($netoUSD, 2, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-0 py-2 px-4"
                         style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                Período: {{ $fechaInicio->format('d/m/Y') }} — {{ $fechaFin->format('d/m/Y') }}
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>
                                Total empleados: {{ $liberalidad->detalles->count() }}
                            </small>
                        </div>
                    </div>
                </div>

            @else
            {{-- Empty state --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                         style="width:56px;height:56px;background:linear-gradient(135deg,#10b981,#059669);opacity:0.5;">
                        <i class="bi bi-chart-line text-white" style="font-size:1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">No hay datos para mostrar</h5>
                    <p class="text-muted mb-0">No se encontraron ventas o empleados para el período seleccionado.</p>
                </div>
            </div>
            @endif
        @endif

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

    const liberalidadPorSucursal = @json($liberalidadPorSucursal ?? []);
    const periodoActual = '{{ $periodo ?? date("Y-m") }}';

    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // BUSCADOR DE EMPLEADOS
        // ==========================
        const buscador = document.getElementById('buscadorEmpleado');
        const tabla = document.getElementById('tablaLiberalidad');
        const limpiarBtn = document.getElementById('limpiarBuscador');

        if (buscador && tabla) {
            function filtrarTabla() {
                const textoBusqueda = buscador.value.toLowerCase().trim();
                const filas = tabla.querySelectorAll('tbody tr');
                let filasVisibles = 0;

                filas.forEach(fila => {
                    const celdaEmpleado = fila.children[1];
                    if (celdaEmpleado) {
                        const textoEmpleado = celdaEmpleado.textContent.toLowerCase();

                        if (textoBusqueda === '' || textoEmpleado.includes(textoBusqueda)) {
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
                                <i class="fas fa-search me-2"></i>
                                No se encontraron empleados con el nombre "${buscador.value}"
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

            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    buscador.value = '';
                    filtrarTabla();
                }
            });
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaLiberalidad');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);

                    document.querySelectorAll('.sort-icon').forEach(icon => {
                        icon.innerHTML = '↕️';
                    });

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }

                    const icono = th.querySelector('.sort-icon');
                    if (icono) {
                        icono.innerHTML = ordenAscendente ? '⬆️' : '⬇️';
                    } else {
                        ths.forEach(t => t.classList.remove('sort-asc', 'sort-desc'));
                        th.classList.add(ordenAscendente ? 'sort-asc' : 'sort-desc');
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

                    const valorA = tdA.dataset.order || extraerValorCelda(tdA);
                    const valorB = tdB.dataset.order || extraerValorCelda(tdB);

                    const numA = parseFloat(valorA);
                    const numB = parseFloat(valorB);

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    } else {
                        return asc
                            ? valorA.toString().localeCompare(valorB.toString())
                            : valorB.toString().localeCompare(valorA.toString());
                    }
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));

                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }

            function extraerValorCelda(td) {
                const badge = td.querySelector('.badge');
                if (badge) {
                    return badge.textContent.trim().replace(/[$,]/g, '');
                }

                const strong = td.querySelector('strong');
                if (strong) {
                    return strong.textContent.trim();
                }

                return td.textContent.trim().replace(/[$,]/g, '');
            }
        })();

        if (sessionStorage.getItem('generarPDF') === 'true') {
            const periodo = sessionStorage.getItem('periodoPDF');
            sessionStorage.removeItem('generarPDF');
            sessionStorage.removeItem('periodoPDF');

            // Esperar a que la tabla se actualice completamente
            setTimeout(() => {
                generarPDFLiberalidadConDatos(periodo);
            }, 1000);
        }
    });

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

    function pdfTablaLiberalidad() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        let yPosition = 20;

        // Título principal
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Reporte de Liberalidad', 14, yPosition);
        yPosition += 8;

        // Información del período
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Período: ${periodoActual}`, 14, yPosition);
        yPosition += 7;
        doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
        yPosition += 10;

        // ============================================
        // AGREGAR RESUMEN POR SUCURSAL SI EXISTE
        // ============================================
        if (liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            // Preparar datos del resumen
            const resumenHeaders = [['Sucursal', 'Cantidad Vendedores', 'Monto Liberalidad (USD)']];
            const resumenBody = [];
            let totalVendedores = 0;
            let totalMonto = 0;

            liberalidadPorSucursal.forEach(sucursal => {
                totalVendedores += sucursal.CantidadVendedores;
                totalMonto += sucursal.MontoLiberalidadSucursal;
                resumenBody.push([
                    sucursal.SucursalNombre,
                    sucursal.CantidadVendedores.toString(),
                    `$ ${sucursal.MontoLiberalidadSucursal.toFixed(2)}`
                ]);
            });

            // Agregar fila de totales
            resumenBody.push([
                'TOTAL GENERAL',
                totalVendedores.toString(),
                `$ ${totalMonto.toFixed(2)}`
            ]);

            // Título del resumen
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text('Resumen por Sucursal', 14, yPosition);
            yPosition += 5;

            // Tabla de resumen
            doc.autoTable({
                head: resumenHeaders,
                body: resumenBody,
                startY: yPosition,
                theme: 'grid',
                headStyles: {
                    fillColor: [52, 152, 219],
                    textColor: [255, 255, 255],
                    fontSize: 9,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    fontSize: 8,
                    cellPadding: 3
                },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { cellWidth: 40, halign: 'center' },
                    2: { cellWidth: 45, halign: 'right' }
                },
                didDrawPage: function(data) {
                    yPosition = data.cursor.y + 10;
                }
            });

            yPosition = doc.lastAutoTable.finalY + 10;
        }

        // ============================================
        // AGREGAR TABLA DE EMPLEADOS
        // ============================================
        const tabla = document.getElementById('tablaLiberalidad');
        if (tabla) {
            // Clonar la tabla para no modificar la original
            const tablaClone = tabla.cloneNode(true);

            // OBTENER TODAS LAS FILAS (incluyendo thead y tbody)
            const todasLasFilas = tablaClone.querySelectorAll('tr');

            // Ocultar la primera columna (Foto) en TODAS las filas
            todasLasFilas.forEach(row => {
                const primeraCelda = row.querySelector('th:first-child, td:first-child');
                if (primeraCelda) {
                    primeraCelda.style.display = 'none';
                }
            });

            // También eliminar específicamente el texto "Foto" del encabezado si quedara
            const thead = tablaClone.querySelector('thead');
            if (thead) {
                const thumbs = thead.querySelectorAll('th');
                if (thumbs.length > 0 && thumbs[0].innerText.trim() === 'Foto') {
                    thumbs[0].style.display = 'none';
                }
            }

            // Opcional: Ocultar columna de Detalle si existe (última columna)
            const headers = thead ? thead.querySelectorAll('th') : [];
            if (headers.length > 0) {
                const lastHeader = headers[headers.length - 1];
                if (lastHeader && lastHeader.innerText.includes('Detalle')) {
                    const lastColIndex = headers.length - 1;
                    tablaClone.querySelectorAll('tr').forEach(row => {
                        const celdas = row.children;
                        if (celdas[lastColIndex]) {
                            celdas[lastColIndex].style.display = 'none';
                        }
                    });
                }
            }

            // Generar tabla de empleados
            doc.autoTable({
                html: tablaClone,
                startY: yPosition,
                theme: 'grid',
                styles: {
                    fontSize: 7,
                    cellPadding: 2
                },
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: [255, 255, 255],
                    fontSize: 8,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                didDrawPage: function(data) {
                    // Número de página
                    const pageCount = doc.internal.getNumberOfPages();
                    doc.setFontSize(8);
                    doc.setTextColor(150, 150, 150);
                    doc.text(
                        `Página ${data.pageNumber} de ${pageCount}`,
                        doc.internal.pageSize.getWidth() - 30,
                        doc.internal.pageSize.getHeight() - 10
                    );
                }
            });
        } else {
            // Si no hay tabla, mostrar mensaje
            doc.setFontSize(10);
            doc.setTextColor(150, 150, 150);
            doc.text('No hay datos de empleados para mostrar', 14, yPosition);
        }

        // Guardar PDF
        const fecha = new Date().toISOString().slice(0, 10);
        doc.save(`Liberalidad_${periodoActual}_${fecha}.pdf`);
    }

    function exportarExcelLiberalidad() {
        const tabla = document.getElementById('tablaLiberalidad');
        if (!tabla) return;

        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Liberalidad"});
        XLSX.writeFile(wb, `Liberalidad_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    let pdfEnMemoria = null;
    let nombreArchivo = null;

    function generarPDFLiberalidadConDatos(periodo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        let yPosition = 20;

        // ============================================
        // FUNCIÓN AUXILIAR PARA LIMPIAR NÚMEROS
        // ============================================
        const limpiarNumero = (texto) => {
            if (!texto) return 0;
            let limpio = texto.replace('$', '').replace('-', '').trim();
            if (limpio.includes('(')) limpio = limpio.split('(')[0].trim();
            limpio = limpio.replace(/\./g, '');
            limpio = limpio.replace(',', '.');
            return parseFloat(limpio) || 0;
        };

        const limpiarEntero = (texto) => {
            if (!texto) return 0;
            return parseInt(texto.replace(/\./g, '')) || 0;
        };

        const limpiarNombre = (texto) => {
            if (!texto) return '';
            return texto.replace('Vendedor', '').replace('Interno', '').trim();
        };

        // ============================================
        // OBTENER RESUMEN POR SUCURSAL
        // ============================================
        let resumenData = null;

        // Intentar obtener de la variable global
        if (typeof liberalidadPorSucursal !== 'undefined' && liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            resumenData = liberalidadPorSucursal;
        }
        // Intentar obtener del sessionStorage (para después del reload)
        else if (sessionStorage.getItem('resumenPorSucursal')) {
            resumenData = JSON.parse(sessionStorage.getItem('resumenPorSucursal'));
            // Limpiar después de usar
            sessionStorage.removeItem('resumenPorSucursal');
        }
        // Intentar obtener del DOM
        else {
            const resumenCard = document.querySelector('.card.mb-4:has(.bg-gradient-info)');
            if (resumenCard) {
                const tablaResumen = resumenCard.querySelector('table');
                if (tablaResumen) {
                    resumenData = [];
                    const filasResumen = tablaResumen.querySelectorAll('tbody tr');
                    filasResumen.forEach(fila => {
                        const celdas = fila.querySelectorAll('td');
                        if (celdas.length >= 3) {
                            resumenData.push({
                                SucursalNombre: celdas[0]?.innerText.trim() || '',
                                CantidadVendedores: parseInt(celdas[1]?.innerText.replace(/[^0-9]/g, '')) || 0,
                                MontoLiberalidadSucursal: parseFloat(celdas[2]?.innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0
                            });
                        }
                    });
                }
            }
        }

        // ============================================
        // AGREGAR RESUMEN POR SUCURSAL SI EXISTE
        // ============================================
        if (resumenData && resumenData.length > 0) {
            // Título principal
            doc.setFontSize(16);
            doc.setTextColor(41, 128, 185);
            doc.text('Reporte de Liberalidad', 14, yPosition);
            yPosition += 8;

            // Información del período
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Período: ${periodo}`, 14, yPosition);
            yPosition += 7;
            doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
            yPosition += 10;

            // Preparar datos del resumen
            const resumenHeaders = [['Sucursal', 'Cantidad Vendedores', 'Monto Liberalidad (USD)']];
            const resumenBody = [];
            let totalVendedores = 0;
            let totalMonto = 0;

            resumenData.forEach(sucursal => {
                totalVendedores += sucursal.CantidadVendedores;
                totalMonto += sucursal.MontoLiberalidadSucursal;
                resumenBody.push([
                    sucursal.SucursalNombre,
                    sucursal.CantidadVendedores.toString(),
                    `$ ${sucursal.MontoLiberalidadSucursal.toFixed(2)}`
                ]);
            });

            // Agregar fila de totales
            resumenBody.push([
                'TOTAL GENERAL',
                totalVendedores.toString(),
                `$ ${totalMonto.toFixed(2)}`
            ]);

            // Título del resumen
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text('Resumen por Sucursal', 14, yPosition);
            yPosition += 5;

            // Tabla de resumen
            doc.autoTable({
                head: resumenHeaders,
                body: resumenBody,
                startY: yPosition,
                theme: 'grid',
                headStyles: {
                    fillColor: [52, 152, 219],
                    textColor: [255, 255, 255],
                    fontSize: 9,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    fontSize: 8,
                    cellPadding: 3
                },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { cellWidth: 40, halign: 'center' },
                    2: { cellWidth: 45, halign: 'right' }
                }
            });

            yPosition = doc.lastAutoTable.finalY + 15;
        } else {
            // Si no hay resumen, mostrar solo el encabezado normal
            doc.setFontSize(16);
            doc.setTextColor(41, 128, 185);
            doc.text('Reporte de Liberalidad', 14, yPosition);
            yPosition += 8;

            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Período: ${periodo}`, 14, yPosition);
            yPosition += 7;
            doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
            yPosition += 10;
        }

        // ============================================
        // EXTRAER DATOS DE LA TABLA
        // ============================================
        const tabla = document.getElementById('tablaLiberalidad');
        if (!tabla) {
            console.error('No se encontró la tabla');
            return;
        }

        const headers = [
            ['Empleado', 'Sucursal', 'Unidades', 'Ventas USD', 'Bonos USD', 'Deducciones USD', 'Liberalidad USD', 'Neto USD']
        ];
        const rows = [];

        const filas = tabla.querySelectorAll('tbody tr');

        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 9) {
                rows.push([
                    limpiarNombre(celdas[1]?.innerText || ''),
                    celdas[2]?.innerText.trim() || '',
                    limpiarEntero(celdas[3]?.innerText),
                    limpiarNumero(celdas[4]?.innerText),
                    limpiarNumero(celdas[5]?.innerText),
                    limpiarNumero(celdas[6]?.innerText),
                    limpiarNumero(celdas[7]?.innerText),
                    limpiarNumero(celdas[8]?.innerText)
                ]);
            }
        });

        // ============================================
        // CALCULAR TOTALES
        // ============================================
        let totales = {
            unidades: 0,
            ventas: 0,
            bonos: 0,
            deducciones: 0,
            liberalidad: 0,
            neto: 0
        };

        rows.forEach(row => {
            totales.unidades += row[2];
            totales.ventas += row[3];
            totales.bonos += row[4];
            totales.deducciones += row[5];
            totales.liberalidad += row[6];
            totales.neto += row[7];
        });

        // Agregar fila de totales
        rows.push([
            'TOTALES',
            '',
            totales.unidades.toLocaleString('es-VE', { minimumFractionDigits: 0 }),
            `$ ${totales.ventas.toFixed(2)}`,
            `$ ${totales.bonos.toFixed(2)}`,
            `$ ${totales.deducciones.toFixed(2)}`,
            `$ ${totales.liberalidad.toFixed(2)}`,
            `$ ${totales.neto.toFixed(2)}`
        ]);

        // Formatear filas para visualización
        const filasFormateadas = rows.map((row, index) => {
            if (index === rows.length - 1) return row; // Totales
            return [
                row[0],
                row[1],
                row[2].toLocaleString('es-VE', { minimumFractionDigits: 0 }),
                `$ ${row[3].toFixed(2)}`,
                `$ ${row[4].toFixed(2)}`,
                `$ ${row[5].toFixed(2)}`,
                `$ ${row[6].toFixed(2)}`,
                `$ ${row[7].toFixed(2)}`
            ];
        });

        // ============================================
        // GENERAR TABLA DE EMPLEADOS
        // ============================================
        doc.autoTable({
            head: headers,
            body: filasFormateadas,
            startY: yPosition,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: [255, 255, 255],
                fontSize: 9,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 8,
                cellPadding: 3
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                0: { cellWidth: 35 },  // Empleado
                1: { cellWidth: 30 },  // Sucursal
                2: { cellWidth: 20, halign: 'center' },  // Unidades
                3: { cellWidth: 25, halign: 'right' },   // Ventas
                4: { cellWidth: 25, halign: 'right' },   // Bonos
                5: { cellWidth: 25, halign: 'right' },   // Deducciones
                6: { cellWidth: 25, halign: 'right' },   // Liberalidad
                7: { cellWidth: 25, halign: 'right' }    // Neto
            },
            didDrawPage: function(data) {
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(150, 150, 150);
                doc.text(
                    `Página ${data.pageNumber} de ${pageCount}`,
                    doc.internal.pageSize.getWidth() - 30,
                    doc.internal.pageSize.getHeight() - 10
                );
            }
        });

        // Guardar PDF
        doc.save(`Liberalidad_${periodo}_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarPDFMemoria() {
        if (pdfEnMemoria) {
            const link = document.createElement('a');
            const url = URL.createObjectURL(pdfEnMemoria);
            link.href = url;
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            // Limpiar memoria
            pdfEnMemoria = null;
            nombreArchivo = null;
        }
    }

    function cerrarLiberalidad() {
        const periodo = document.getElementById('periodo').value;

        // Guardar el resumen por sucursal si existe antes de cerrar
        if (typeof liberalidadPorSucursal !== 'undefined' && liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            sessionStorage.setItem('resumenPorSucursal', JSON.stringify(liberalidadPorSucursal));
        } else {
            // Intentar extraer el resumen del DOM si no está en la variable
            const resumenCard = document.querySelector('.card.mb-4:has(.bg-gradient-info)');
            if (resumenCard) {
                const tablaResumen = resumenCard.querySelector('table');
                if (tablaResumen) {
                    const resumenData = [];
                    const filasResumen = tablaResumen.querySelectorAll('tbody tr');
                    filasResumen.forEach(fila => {
                        const celdas = fila.querySelectorAll('td');
                        if (celdas.length >= 3) {
                            resumenData.push({
                                SucursalNombre: celdas[0]?.innerText.trim() || '',
                                CantidadVendedores: parseInt(celdas[1]?.innerText.replace(/[^0-9]/g, '')) || 0,
                                MontoLiberalidadSucursal: parseFloat(celdas[2]?.innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0
                            });
                        }
                    });
                    if (resumenData.length > 0) {
                        sessionStorage.setItem('resumenPorSucursal', JSON.stringify(resumenData));
                    }
                }
            }
        }

        Swal.fire({
            title: '¿Cerrar Liberalidad?',
            html: `Esta acción no se puede deshacer.<br>
                La liberalidad del período <strong>${periodo}</strong> quedará registrada.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando liberalidad...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("cpanel.empleados.liberalidad.cerrar") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        periodo: periodo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Guardar flag para generar PDF después de la recarga
                        sessionStorage.setItem('generarPDF', 'true');
                        sessionStorage.setItem('periodoPDF', periodo);
                        // Recargar la página
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al cerrar la liberalidad', 'error');
                });
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    #tablaLiberalidad tbody tr:hover { background: #f8fafc; }
    #tablaLiberalidad thead th.sortable:hover { background: #ecfdf5; color: #059669; }

    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 0.2rem rgba(16,185,129,.15); }

    /* Input buscador sobre fondo verde — placeholder blanco */
    #buscadorEmpleado::placeholder { color: rgba(255,255,255,0.6); }
    #buscadorEmpleado:focus { box-shadow: none; border-color: rgba(255,255,255,0.6); }
</style>
@endpush
