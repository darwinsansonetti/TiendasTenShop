@extends('layout.layout_dashboard')

@section('title', 'Cierre Diario Bóveda')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'safe';
    $hdrTitle = 'Cierre Diario Bóveda';
    $hdrSubtitle = 'Registro y conciliación por sucursal';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.boveda.index') }}">Bóveda</a></li>
                    <li class="breadcrumb-item active">Nuevo Cierre</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        {{-- Si no hay sucursales con cierre --}}
        @if($datosPorSucursal->isEmpty())
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>No hay cierres diarios para esta fecha.</strong>
                    <p class="mb-0">No se puede crear un Cierre Diario Bóveda porque no hay sucursales con cierre del día 
                        <strong>{{ Carbon::parse($fechaCierre)->format('d/m/Y') }}</strong>.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('cpanel.boveda.guardar') }}" method="POST" id="formBoveda">
            @csrf

            {{-- INFORMACIÓN GENERAL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información General
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Fecha del cierre (NO editable, solo informativa + hidden) --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha del Cierre <span class="text-danger">*</span>
                            </label>
                            
                            {{-- Campo hidden que SIEMPRE envía el valor correcto --}}
                            <input type="hidden" name="fecha" value="{{ $fechaCierre }}">
                            
                            {{-- Solo mostramos el texto (no input editable) --}}
                            <p class="form-control bg-light mb-0 fw-bold text-dark">
                                {{ Carbon::parse($fechaCierre)->format('d/m/Y') }}
                            </p>
                            <small class="text-muted">Fecha del cierre diario a conciliar</small>
                        </div>

                        {{-- Tasa de cambio --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-arrow-left-right me-1" style="color:#8b5cf6;"></i>Tasa de Cambio
                            </label>
                            <p class="fw-bold text-dark fs-5 mb-0">Bs. {{ number_format($tasaCambio ?? 0, 2) }}</p>
                        </div>

                        {{-- Cantidad de sucursales --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-building me-1" style="color:#8b5cf6;"></i>Cantidad de Sucursales
                            </label>
                            <p class="fw-bold text-dark fs-5 mb-0">{{ $datosPorSucursal->count() }} sucursal(es)</p>
                        </div>

                        {{-- Observación --}}
                        <div class="col-12">
                            <label for="observacion" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1" style="color:#8b5cf6;"></i>Observación General
                            </label>
                            <textarea name="observacion" id="observacion" class="form-control" rows="2"
                                      placeholder="Observaciones generales">{{ old('observacion') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RESUMEN DE TOTALES GLOBALES --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                     style="width:48px;height:48px;background:rgba(16,185,129,0.1);">
                                    <i class="bi bi-cash text-success" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0" style="font-size:0.75rem;">Total Divisas</p>
                                    <h5 class="fw-bold mb-0" id="totalGlobalDivisa">$ 0.00</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                     style="width:48px;height:48px;background:rgba(59,130,246,0.1);">
                                    <i class="bi bi-cash-stack text-primary" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0" style="font-size:0.75rem;">Total Bs.</p>
                                    <h5 class="fw-bold mb-0" id="totalGlobalBs">Bs. 0.00</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                     style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                    <i class="bi bi-credit-card" style="font-size:1.5rem;color:#8b5cf6;"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0" style="font-size:0.75rem;">Total PDV Sistema</p>
                                    <h5 class="fw-bold mb-0" id="totalGlobalPdvSistema">$ 0.00</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                     style="width:48px;height:48px;background:rgba(245,158,11,0.1);">
                                    <i class="bi bi-wallet2 text-warning" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0" style="font-size:0.75rem;">Diferencia Total</p>
                                    <h5 class="fw-bold mb-0" id="totalGlobalDiferencia">$ 0.00</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACORDEÓN POR SUCURSAL --}}
            <div class="accordion" id="acordeonSucursales">
                @foreach($datosPorSucursal as $sucIndex => $sucursal)
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="heading{{ $sucursal->SucursalId }}">
                        <button class="accordion-button {{ $sucIndex == 0 ? '' : 'collapsed' }}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $sucursal->SucursalId }}"
                                style="background:{{ $hdrBg }};color:#fff;">
                            <div class="d-flex align-items-center w-100">
                                <i class="bi bi-building me-2" style="font-size:1.2rem;"></i>
                                <strong>{{ $sucursal->SucursalNombre }}</strong>
                                <span class="badge bg-success ms-3">Con Cierre</span>
                                <span class="ms-auto me-3 badge bg-white text-dark">
                                    PDV: {{ $sucursal->PuntosVenta->count() }}
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $sucursal->SucursalId }}" 
                        class="accordion-collapse collapse {{ $sucIndex == 0 ? 'show' : '' }}"
                        data-bs-parent="#acordeonSucursales">
                        <div class="accordion-body">

                            {{-- ============================================ --}}
                            {{-- CONCILIACIÓN EFECTIVO (Denominaciones) --}}
                            {{-- ============================================ --}}
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                                        <i class="bi bi-cash-stack me-2"></i>Conciliación de Efectivo
                                    </h6>
                                </div>
                                <div class="card-body">
                                    {{-- Efectivo Divisas --}}
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <h6 class="fw-bold text-success mb-2" style="font-size:0.85rem;">
                                                <i class="bi bi-cash me-1"></i>Efectivo en Divisas (USD)
                                            </h6>
                                            <div class="row g-2">
                                                @foreach($denominacionesDivisa as $denIndex => $denominacion)
                                                <div class="col-md-2 col-4">
                                                    <div class="card border">
                                                        <div class="card-body p-2 text-center">
                                                            <label class="fw-bold text-success d-block" style="font-size:0.75rem;">
                                                                $ {{ number_format($denominacion, 2) }}
                                                            </label>
                                                            <input type="hidden" 
                                                                name="denominaciones[{{ $sucursal->SucursalId }}][divisa][{{ $denIndex }}][denominacion]" 
                                                                value="{{ $denominacion }}">
                                                            <input type="number" 
                                                                name="denominaciones[{{ $sucursal->SucursalId }}][divisa][{{ $denIndex }}][cantidad]" 
                                                                class="form-control form-control-sm text-center cantidad-divisa"
                                                                data-sucursal="{{ $sucursal->SucursalId }}"
                                                                data-denominacion="{{ $denominacion }}"
                                                                min="0" value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <div class="col-md-2 col-4">
                                                    <div class="card border h-100" style="background:#f0fdf4;">
                                                        <div class="card-body p-2 text-center">
                                                            <label class="fw-bold text-success d-block" style="font-size:0.7rem;">TOTAL CONTADO</label>
                                                            <p class="h6 fw-bold text-success mb-0 subtotal-divisa-{{ $sucursal->SucursalId }}">
                                                                $ 0.00
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Conciliación efectivo divisas --}}
                                            <div class="row mt-2">
                                                <div class="col-md-12">
                                                    <div class="card border">
                                                        <div class="card-body p-2">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Sistema (Cierre):</label>
                                                                    <p class="fw-bold text-dark mb-0">$ {{ number_format($sucursal->EfectivoDivisas, 2) }}</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Contado (Bóveda):</label>
                                                                    <p class="fw-bold text-dark mb-0 total-contado-divisa-{{ $sucursal->SucursalId }}">$ 0.00</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Diferencia:</label>
                                                                    <p class="fw-bold mb-0 diferencia-efectivo-divisa-{{ $sucursal->SucursalId }} text-success">$ 0.00</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_1][sucursal_id]" value="{{ $sucursal->SucursalId }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_1][cierre_diario_id]" value="{{ $sucursal->CierreDiarioId }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_1][tipo]" value="1">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_1][monto_sistema]" value="{{ $sucursal->EfectivoDivisas }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_1][monto_depositado]" class="monto-contado-divisa-{{ $sucursal->SucursalId }}" value="0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Efectivo Bolívares --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="fw-bold text-primary mb-2" style="font-size:0.85rem;">
                                                <i class="bi bi-cash-stack me-1"></i>Efectivo en Bolívares (Bs.)
                                            </h6>
                                            <div class="row g-2">
                                                @foreach($denominacionesBs as $denIndex => $denominacion)
                                                <div class="col-md-2 col-4">
                                                    <div class="card border">
                                                        <div class="card-body p-2 text-center">
                                                            <label class="fw-bold text-primary d-block" style="font-size:0.75rem;">
                                                                Bs. {{ number_format($denominacion, 0) }}
                                                            </label>
                                                            <input type="hidden" 
                                                                name="denominaciones[{{ $sucursal->SucursalId }}][bs][{{ $denIndex }}][denominacion]" 
                                                                value="{{ $denominacion }}">
                                                            <input type="number" 
                                                                name="denominaciones[{{ $sucursal->SucursalId }}][bs][{{ $denIndex }}][cantidad]" 
                                                                class="form-control form-control-sm text-center cantidad-bs"
                                                                data-sucursal="{{ $sucursal->SucursalId }}"
                                                                data-denominacion="{{ $denominacion }}"
                                                                min="0" value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                                <div class="col-md-2 col-4">
                                                    <div class="card border h-100" style="background:#eff6ff;">
                                                        <div class="card-body p-2 text-center">
                                                            <label class="fw-bold text-primary d-block" style="font-size:0.7rem;">TOTAL CONTADO</label>
                                                            <p class="h6 fw-bold text-primary mb-0 subtotal-bs-{{ $sucursal->SucursalId }}">
                                                                Bs. 0.00
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Conciliación efectivo Bs --}}
                                            <div class="row mt-2">
                                                <div class="col-md-12">
                                                    <div class="card border">
                                                        <div class="card-body p-2">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Sistema (Cierre):</label>
                                                                    <p class="fw-bold text-dark mb-0">Bs. {{ number_format($sucursal->EfectivoBs, 2) }}</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Contado (Bóveda):</label>
                                                                    <p class="fw-bold text-dark mb-0 total-contado-bs-{{ $sucursal->SucursalId }}">Bs. 0.00</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="fw-semibold mb-0" style="font-size:0.75rem;">Diferencia:</label>
                                                                    <p class="fw-bold mb-0 diferencia-efectivo-bs-{{ $sucursal->SucursalId }} text-success">Bs. 0.00</p>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_2][sucursal_id]" value="{{ $sucursal->SucursalId }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_2][cierre_diario_id]" value="{{ $sucursal->CierreDiarioId }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_2][tipo]" value="2">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_2][monto_sistema]" value="{{ $sucursal->EfectivoBs }}">
                                                                    <input type="hidden" name="conciliacion_efectivo[{{ $sucursal->SucursalId }}_2][monto_depositado]" class="monto-contado-bs-{{ $sucursal->SucursalId }}" value="0">
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

                            {{-- PUNTOS DE VENTA (en Bs.) --}}
                            @if($sucursal->PuntosVenta->count() > 0)
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                                        <i class="bi bi-credit-card me-2"></i>Puntos de Venta (Bs.)
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead style="background:#f8fafc;">
                                                <tr>
                                                    <th class="ps-3 py-2 text-muted fw-semibold" style="font-size:0.7rem;">PUNTO</th>
                                                    <th class="py-2 text-muted fw-semibold" style="font-size:0.7rem;">BANCO</th>
                                                    <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">SISTEMA (Bs.)</th>
                                                    <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">DEPOSITADO (Bs.)</th>
                                                    <th class="pe-3 py-2 text-center text-muted fw-semibold" style="font-size:0.7rem;">DIF.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sucursal->PuntosVenta as $pdv)
                                                <tr>
                                                    <td class="ps-3">
                                                        <span class="fw-semibold" style="font-size:0.85rem;">{{ $pdv->pdv_descripcion }}</span>
                                                        <small class="d-block text-muted">{{ $pdv->pdv_codigo ?? '-' }}</small>
                                                    </td>
                                                    <td>{{ $pdv->banco_nombre ?? 'N/A' }}</td>
                                                    <td class="text-end fw-semibold">Bs. {{ number_format($pdv->monto_sistema, 2) }}</td>
                                                    <td class="text-end">
                                                        <input type="hidden" name="conciliacion_pdv[{{ $sucursal->SucursalId }}_{{ $pdv->PuntoDeVentaId }}][sucursal_id]" value="{{ $sucursal->SucursalId }}">
                                                        <input type="hidden" name="conciliacion_pdv[{{ $sucursal->SucursalId }}_{{ $pdv->PuntoDeVentaId }}][punto_venta_id]" value="{{ $pdv->PuntoDeVentaId }}">
                                                        <input type="hidden" name="conciliacion_pdv[{{ $sucursal->SucursalId }}_{{ $pdv->PuntoDeVentaId }}][cierre_diario_id]" value="{{ $sucursal->CierreDiarioId }}">
                                                        <input type="hidden" name="conciliacion_pdv[{{ $sucursal->SucursalId }}_{{ $pdv->PuntoDeVentaId }}][monto_sistema]" value="{{ $pdv->monto_sistema }}">
                                                        <input type="number" 
                                                                name="conciliacion_pdv[{{ $sucursal->SucursalId }}_{{ $pdv->PuntoDeVentaId }}][monto_depositado]" 
                                                                class="form-control form-control-sm text-end monto-depositado"
                                                                step="0.01" min="0" 
                                                                value="{{ number_format($pdv->monto_sistema, 2, '.', '') }}"
                                                                data-monto-sistema="{{ number_format($pdv->monto_sistema, 2, '.', '') }}"
                                                                data-sucursal="{{ $sucursal->SucursalId }}">
                                                    </td>
                                                    <td class="pe-3 text-center">
                                                        <span class="badge bg-success diferencia-badge" style="font-size:0.7rem;">Bs. 0.00</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- OTROS CONCEPTOS --}}
                            @if($sucursal->OtrosConceptos->count() > 0)
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                                        <i class="bi bi-wallet2 me-2"></i>Otros Conceptos
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead style="background:#f8fafc;">
                                                <tr>
                                                    <th class="ps-3 py-2 text-muted fw-semibold" style="font-size:0.7rem;">TIPO</th>
                                                    <th class="py-2 text-muted fw-semibold" style="font-size:0.7rem;">MONEDA</th>
                                                    <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">SISTEMA</th>
                                                    <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.7rem;">DEPOSITADO</th>
                                                    <th class="pe-3 py-2 text-center text-muted fw-semibold" style="font-size:0.7rem;">DIF.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sucursal->OtrosConceptos as $otro)
                                                <tr>
                                                    <td class="ps-3">
                                                        <span class="badge bg-{{ $otro->Tipo == 1 ? 'info' : ($otro->Tipo == 2 ? 'primary' : ($otro->Tipo == 3 ? 'warning' : 'success')) }}">
                                                            {{ $otro->TipoNombre }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $otro->Moneda == 'USD' ? 'success' : 'primary' }}">
                                                            {{ $otro->Moneda }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end fw-semibold">
                                                        {{ $otro->Moneda == 'USD' ? '$' : 'Bs.' }} {{ number_format($otro->MontoSistema, 2) }}
                                                    </td>
                                                    <td class="text-end">
                                                        <input type="hidden" name="conciliacion_otros[{{ $sucursal->SucursalId }}_{{ $otro->Tipo }}][sucursal_id]" value="{{ $sucursal->SucursalId }}">
                                                        <input type="hidden" name="conciliacion_otros[{{ $sucursal->SucursalId }}_{{ $otro->Tipo }}][tipo]" value="{{ $otro->Tipo }}">
                                                        <input type="hidden" name="conciliacion_otros[{{ $sucursal->SucursalId }}_{{ $otro->Tipo }}][monto_sistema]" value="{{ $otro->MontoSistema }}">
                                                        <input type="number" 
                                                                name="conciliacion_otros[{{ $sucursal->SucursalId }}_{{ $otro->Tipo }}][monto_depositado]" 
                                                                class="form-control form-control-sm text-end monto-depositado-otros"
                                                                step="0.01" min="0" 
                                                                value="{{ number_format($otro->MontoSistema, 2, '.', '') }}"
                                                                data-monto-sistema="{{ number_format($otro->MontoSistema, 2, '.', '') }}"
                                                                data-moneda="{{ $otro->Moneda }}"
                                                                data-sucursal="{{ $sucursal->SucursalId }}">
                                                    </td>
                                                    <td class="pe-3 text-center">
                                                        <span class="badge bg-success diferencia-badge-otros" style="font-size:0.7rem;">
                                                            {{ $otro->Moneda == 'USD' ? '$' : 'Bs.' }} 0.00
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- BOTONES --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn px-4 fw-semibold text-white" 
                                style="background:{{ $hdrBg }};border:none;" id="btnGuardar"
                                {{ $datosPorSucursal->isEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-save me-1"></i> Guardar Cierre Diario
                        </button>
                        <a href="{{ route('cpanel.boveda.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ============================================
        // CÁLCULO DE SUBTOTALES POR SUCURSAL
        // ============================================
        function calcularSubtotales() {
            // Divisas por sucursal
            const subtotalesDivisa = {};
            document.querySelectorAll('.cantidad-divisa').forEach(input => {
                const sucursalId = input.dataset.sucursal;
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                subtotalesDivisa[sucursalId] = (subtotalesDivisa[sucursalId] || 0) + (cantidad * denom);
            });
            
            Object.keys(subtotalesDivisa).forEach(sucId => {
                const el = document.querySelector('.subtotal-divisa-' + sucId);
                if (el) el.textContent = '$ ' + subtotalesDivisa[sucId].toFixed(2);
            });

            // Total global divisas
            const totalGlobalDivisa = Object.values(subtotalesDivisa).reduce((a, b) => a + b, 0);
            document.getElementById('totalGlobalDivisa').textContent = '$ ' + totalGlobalDivisa.toFixed(2);

            // Bolívares por sucursal
            const subtotalesBs = {};
            document.querySelectorAll('.cantidad-bs').forEach(input => {
                const sucursalId = input.dataset.sucursal;
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                subtotalesBs[sucursalId] = (subtotalesBs[sucursalId] || 0) + (cantidad * denom);
            });
            
            Object.keys(subtotalesBs).forEach(sucId => {
                const el = document.querySelector('.subtotal-bs-' + sucId);
                if (el) el.textContent = 'Bs. ' + subtotalesBs[sucId].toFixed(2);
            });

            // Total global Bs
            const totalGlobalBs = Object.values(subtotalesBs).reduce((a, b) => a + b, 0);
            document.getElementById('totalGlobalBs').textContent = 'Bs. ' + totalGlobalBs.toFixed(2);
        }

        // ============================================
        // CÁLCULO DE SUBTOTALES Y CONCILIACIÓN DE EFECTIVO
        // ============================================
        function calcularEfectivo() {
            // ===== DIVISAS =====
            const subtotalesDivisa = {};
            document.querySelectorAll('.cantidad-divisa').forEach(input => {
                const sucursalId = input.dataset.sucursal;
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                subtotalesDivisa[sucursalId] = (subtotalesDivisa[sucursalId] || 0) + (cantidad * denom);
            });
            
            Object.keys(subtotalesDivisa).forEach(sucId => {
                const total = subtotalesDivisa[sucId];
                const elSubtotal = document.querySelector('.subtotal-divisa-' + sucId);
                const elContado = document.querySelector('.total-contado-divisa-' + sucId);
                const elDiferencia = document.querySelector('.diferencia-efectivo-divisa-' + sucId);
                const elHidden = document.querySelector('.monto-contado-divisa-' + sucId);
                
                if (elSubtotal) elSubtotal.textContent = '$ ' + total.toFixed(2);
                if (elContado) elContado.textContent = '$ ' + total.toFixed(2);
                if (elHidden) elHidden.value = total;
                
                // Diferencia
                if (elDiferencia) {
                    const montoSistema = parseFloat(elHidden.closest('.row').querySelector('input[name$="[monto_sistema]"]').value) || 0;
                    const diferencia = montoSistema - total;
                    elDiferencia.textContent = '$ ' + diferencia.toFixed(2);
                    elDiferencia.className = 'fw-bold mb-0 ' + (Math.abs(diferencia) < 0.01 ? 'text-success' : 'text-danger');
                }
            });

            // ===== BOLÍVARES =====
            const subtotalesBs = {};
            document.querySelectorAll('.cantidad-bs').forEach(input => {
                const sucursalId = input.dataset.sucursal;
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                subtotalesBs[sucursalId] = (subtotalesBs[sucursalId] || 0) + (cantidad * denom);
            });
            
            Object.keys(subtotalesBs).forEach(sucId => {
                const total = subtotalesBs[sucId];
                const elSubtotal = document.querySelector('.subtotal-bs-' + sucId);
                const elContado = document.querySelector('.total-contado-bs-' + sucId);
                const elDiferencia = document.querySelector('.diferencia-efectivo-bs-' + sucId);
                const elHidden = document.querySelector('.monto-contado-bs-' + sucId);
                
                if (elSubtotal) elSubtotal.textContent = 'Bs. ' + total.toFixed(2);
                if (elContado) elContado.textContent = 'Bs. ' + total.toFixed(2);
                if (elHidden) elHidden.value = total;
                
                if (elDiferencia) {
                    const montoSistema = parseFloat(elHidden.closest('.row').querySelector('input[name$="[monto_sistema]"]').value) || 0;
                    const diferencia = montoSistema - total;
                    elDiferencia.textContent = 'Bs. ' + diferencia.toFixed(2);
                    elDiferencia.className = 'fw-bold mb-0 ' + (Math.abs(diferencia) < 0.01 ? 'text-success' : 'text-danger');
                }
            });
        }

        // ============================================
        // CÁLCULO DE DIFERENCIAS PDV (en Bs.)
        // ============================================
        function calcularDiferenciasPDV() {
            document.querySelectorAll('.monto-depositado').forEach(input => {
                const montoSistema = parseFloat(input.dataset.montoSistema) || 0;
                const montoDepositado = parseFloat(input.value) || 0;
                const diferencia = montoSistema - montoDepositado;

                const badge = input.closest('tr').querySelector('.diferencia-badge');
                
                if (Math.abs(diferencia) < 0.01) {
                    badge.className = 'badge bg-success diferencia-badge';
                    badge.textContent = 'Bs. 0.00';
                } else {
                    badge.className = 'badge bg-danger diferencia-badge';
                    badge.textContent = 'Bs. ' + diferencia.toFixed(2);
                }
            });
        }

        // ============================================
        // CÁLCULO DE DIFERENCIAS OTROS
        // ============================================
        function calcularDiferenciasOtros() {
            document.querySelectorAll('.monto-depositado-otros').forEach(input => {
                const montoSistema = parseFloat(input.dataset.montoSistema) || 0;
                const montoDepositado = parseFloat(input.value) || 0;
                const diferencia = montoSistema - montoDepositado;
                const moneda = input.dataset.moneda || 'Bs.';
                const simbolo = moneda === 'USD' ? '$' : 'Bs.';

                const badge = input.closest('tr').querySelector('.diferencia-badge-otros');
                
                if (Math.abs(diferencia) < 0.01) {
                    badge.className = 'badge bg-success diferencia-badge-otros';
                    badge.textContent = simbolo + ' 0.00';
                } else {
                    badge.className = 'badge bg-danger diferencia-badge-otros';
                    badge.textContent = simbolo + ' ' + diferencia.toFixed(2);
                }
            });
        }

        // Event listeners
        document.querySelectorAll('.cantidad-divisa, .cantidad-bs').forEach(i => {
            i.addEventListener('input', calcularSubtotales);
        });
        document.querySelectorAll('.cantidad-divisa, .cantidad-bs').forEach(i => {
            i.addEventListener('input', calcularEfectivo);
        });
        document.querySelectorAll('.monto-depositado').forEach(i => {
            i.addEventListener('input', calcularDiferenciasPDV);
        });
        document.querySelectorAll('.monto-depositado-otros').forEach(i => {
            i.addEventListener('input', calcularDiferenciasOtros);
        });

        // Inicializar
        calcularSubtotales();
        calcularEfectivo();
        calcularDiferenciasPDV();
        calcularDiferenciasOtros();

        // Deshabilitar botón al enviar
        document.getElementById('formBoveda').addEventListener('submit', function() {
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.15);
    }
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);
        color: #fff;
    }
    .accordion-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.25);
    }
    .accordion-button::after {
        filter: brightness(0) invert(1);
    }
</style>
@endpush