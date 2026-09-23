@extends('layout.layout_dashboard')

@section('title', 'Detalle de Bóveda')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'safe';
    $hdrTitle = 'Detalle de Bóveda';
    $hdrSubtitle = 'Registro y conciliación';
    
    $estatusPrestamo = [0 => 'Pendiente', 1 => 'Devuelto', 2 => 'Anulado'];
    $estatusPrestamoBadge = [0 => 'warning', 1 => 'success', 2 => 'danger'];
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
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ALERTA DE CONCILIACIÓN --}}
        @if($boveda->EstatusConciliacion == 2)
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>¡Atención!</strong> Se encontraron diferencias en la conciliación.
                    Revise los montos depositados vs los montos del sistema.
                </div>
            </div>
        @elseif($boveda->EstatusConciliacion == 1)
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                <div>
                    <strong>¡Conciliado!</strong> Todos los montos coinciden correctamente.
                </div>
            </div>
        @endif

        {{-- INFORMACIÓN GENERAL --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información General
                    </h6>
                    <div class="d-flex gap-2">
                        @if($puedeCerrar)
                            <a href="{{ route('cpanel.boveda.editar', $boveda->BovedaId) }}" 
                            class="btn btn-warning fw-semibold">
                                <i class="bi bi-pencil me-1"></i> Editar Cierre
                            </a>
                            <button type="button" class="btn btn-sm fw-semibold text-white"
                                    style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);"
                                    onclick="cerrarBoveda({{ $boveda->BovedaId }})">
                                <i class="bi bi-lock me-1"></i> Cerrar Bóveda
                            </button>
                        @endif
                        <a href="{{ route('cpanel.boveda.index') }}" 
                        class="btn btn-sm fw-semibold text-white"
                        style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">ID</p>
                        <p class="fw-bold text-dark">
                            <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;">
                                {{ $boveda->BovedaId }}
                            </code>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Fecha</p>
                        <p class="fw-bold text-dark">{{ $boveda->FechaFormateada }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Estatus</p>
                        <span class="badge bg-{{ $boveda->EstatusBadge }}" style="font-size:0.9rem;">
                            {{ $boveda->EstatusTexto }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Tasa de Cambio</p>
                        <p class="fw-bold text-dark">Bs. {{ number_format($boveda->tasa_cambio ?? 0, 2) }}</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Observación</p>
                        <p class="fw-bold text-dark">{{ $boveda->Observacion ?? 'Sin observación' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTALES --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(16,185,129,0.1);">
                                <i class="bi bi-cash text-success" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Divisas</p>
                                <h5 class="fw-bold mb-0">$ {{ number_format($totalDivisa, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(59,130,246,0.1);">
                                <i class="bi bi-cash-stack text-primary" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Bs.</p>
                                <h5 class="fw-bold mb-0">Bs. {{ number_format($totalBs, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                <i class="bi bi-credit-card" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total PDV</p>
                                <h5 class="fw-bold mb-0">$ {{ number_format($totalPDVDepositado, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(239,68,68,0.1);">
                                <i class="bi bi-arrow-left-right text-danger" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Préstamos Pendientes</p>
                                <h5 class="fw-bold mb-0 text-danger">$ {{ number_format($prestamosPendientes, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CONCILIACIÓN PDV --}}
        @if($conciliacionPDV->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-credit-card me-2"></i>Conciliación Puntos de Venta
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-white text-dark">
                            Total Sistema: $ {{ number_format($totalPDVSistema, 2) }}
                        </span>
                        <span class="badge bg-white text-dark">
                            Total Depositado: $ {{ number_format($totalPDVDepositado, 2) }}
                        </span>
                        <span class="badge bg-{{ abs($diferenciaPDV) < 0.01 ? 'success' : 'danger' }} text-white">
                            Diferencia: $ {{ number_format($diferenciaPDV, 2) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">PUNTO</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">BANCO</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">SISTEMA</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">DEPOSITADO</th>
                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                                <th class="pe-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">OBSERVACIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conciliacionPDV as $item)
                            <tr class="{{ $item->TieneDiferencia ? 'table-danger' : '' }}">
                                <td class="ps-4">{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $item->pdv_descripcion ?? 'N/A' }}</span>
                                    <small class="d-block text-muted">{{ $item->pdv_codigo ?? '' }}</small>
                                </td>
                                <td>{{ $item->banco_nombre ?? 'N/A' }}</td>
                                <td class="text-end">$ {{ number_format($item->MontoSistema, 2) }}</td>
                                <td class="text-end">$ {{ number_format($item->MontoDepositado, 2) }}</td>
                                <td class="text-center">
                                    @if(abs($item->Diferencia) < 0.01)
                                        <span class="badge bg-success">$ 0.00</span>
                                    @else
                                        <span class="badge bg-danger">$ {{ number_format($item->Diferencia, 2) }}</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <small>{{ $item->Observacion ?? '-' }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- CONCILIACIÓN OTROS --}}
        @if($conciliacionOtros->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-wallet2 me-2"></i>Conciliación Otros Conceptos
                    </h6>
                    <span class="badge bg-{{ abs($diferenciaOtros) < 0.01 ? 'success' : 'danger' }} text-white">
                        Diferencia: Bs. {{ number_format($diferenciaOtros, 2) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">TIPO</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">SISTEMA</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">DEPOSITADO</th>
                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conciliacionOtros as $item)
                            <tr class="{{ $item->TieneDiferencia ? 'table-danger' : '' }}">
                                <td class="ps-4">{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->Tipo == 1 ? 'info' : ($item->Tipo == 2 ? 'primary' : ($item->Tipo == 3 ? 'warning' : 'success')) }}">
                                        {{ $tiposOtros[$item->Tipo] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-end">Bs. {{ number_format($item->MontoSistema, 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($item->MontoDepositado, 2) }}</td>
                                <td class="text-center">
                                    @if(abs($item->Diferencia) < 0.01)
                                        <span class="badge bg-success">Bs. 0.00</span>
                                    @else
                                        <span class="badge bg-danger">Bs. {{ number_format($item->Diferencia, 2) }}</span>
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

        {{-- DENOMINACIONES --}}
        <div class="row g-3 mb-4">
            @if($denominacionesDivisa->count() > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash me-2"></i>Denominaciones Divisas
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4 py-2 text-muted fw-semibold">DENOMINACIÓN</th>
                                    <th class="py-2 text-center text-muted fw-semibold">CANTIDAD</th>
                                    <th class="pe-4 py-2 text-end text-muted fw-semibold">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($denominacionesDivisa as $item)
                                <tr>
                                    <td class="ps-4">$ {{ number_format($item->Denominacion, 2) }}</td>
                                    <td class="text-center">{{ $item->Cantidad }}</td>
                                    <td class="pe-4 text-end fw-bold">$ {{ number_format($item->MontoTotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:#f0fdf4;">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL:</th>
                                    <th class="pe-4 text-end text-success">$ {{ number_format($totalDivisa, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($denominacionesBs->count() > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash-stack me-2"></i>Denominaciones Bolívares
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4 py-2 text-muted fw-semibold">DENOMINACIÓN</th>
                                    <th class="py-2 text-center text-muted fw-semibold">CANTIDAD</th>
                                    <th class="pe-4 py-2 text-end text-muted fw-semibold">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($denominacionesBs as $item)
                                <tr>
                                    <td class="ps-4">Bs. {{ number_format($item->Denominacion, 0) }}</td>
                                    <td class="text-center">{{ $item->Cantidad }}</td>
                                    <td class="pe-4 text-end fw-bold">Bs. {{ number_format($item->MontoTotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:#eff6ff;">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL:</th>
                                    <th class="pe-4 text-end text-primary">Bs. {{ number_format($totalBs, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- ==========================================
        {{-- PRÉSTAMOS --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-arrow-left-right me-2"></i>Préstamos de Bóveda
                    </h6>
                    @if($puedeCerrar)
                        <button type="button" class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                data-bs-toggle="modal" data-bs-target="#modalPrestamo">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Préstamo
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($prestamos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold">SUCURSAL</th>
                                <th class="py-2 text-end text-muted fw-semibold">MONTO (USD)</th>
                                <th class="py-2 text-end text-muted fw-semibold">MONTO (Bs.)</th>
                                <th class="py-2 text-muted fw-semibold">FECHA PRÉSTAMO</th>
                                <th class="py-2 text-center text-muted fw-semibold">ESTATUS</th>
                                <th class="pe-4 py-2 text-center text-muted fw-semibold">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prestamos as $item)
                            <tr>
                                <td class="ps-4">{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                                <td class="text-end fw-bold">$ {{ number_format($item->MontoDivisa, 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($item->MontoBs, 2) }}</td>
                                <td>{{ Carbon::parse($item->FechaPrestamo)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $estatusPrestamoBadge[$item->Estatus] ?? 'secondary' }}">
                                        {{ $estatusPrestamo[$item->Estatus] ?? 'Desconocido' }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($item->Estatus == 0 && $puedeCerrar)
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="devolverPrestamo({{ $item->BovedaPrestamoId }})">
                                            <i class="bi bi-check-circle"></i> Devolver
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-info-circle me-2"></i>No hay préstamos registrados
                </div>
                @endif
            </div>
        </div>
        ========================================== -->

    </div>
</div>

{{-- MODAL NUEVO PRÉSTAMO --}}
@if($puedeCerrar)
<div class="modal fade" id="modalPrestamo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:{{ $hdrBg }};color:#fff;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-arrow-left-right me-2"></i>Nuevo Préstamo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cpanel.boveda.guardar_prestamo') }}" method="POST">
                @csrf
                <input type="hidden" name="boveda_id" value="{{ $boveda->BovedaId }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="sucursal_id" class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select" required>
                                <option value="">Seleccione una sucursal</option>
                                @foreach($conciliacionPDV->pluck('sucursal_nombre', 'SucursalId')->unique() as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="monto_divisa" class="form-label fw-semibold">Monto en Divisas (USD) <span class="text-danger">*</span></label>
                            <input type="number" name="monto_divisa" id="monto_divisa" 
                                   class="form-control" step="0.01" min="0.01" required
                                   placeholder="0.00">
                        </div>
                        <div class="col-md-12">
                            <label for="observacion_prestamo" class="form-label fw-semibold">Observación</label>
                            <textarea name="observacion" id="observacion_prestamo" class="form-control" rows="2"
                                      placeholder="Observaciones del préstamo"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background:{{ $hdrBg }};color:#fff;border:none;">
                        <i class="bi bi-save me-1"></i> Guardar Préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function cerrarBoveda(id) {
        Swal.fire({
            title: '¿Cerrar bóveda?',
            text: 'Una vez cerrada no se podrá modificar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8b5cf6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // 👇 Mismo patrón que usas en otras vistas
                fetch("{{ route('cpanel.boveda.cerrar', ['id' => ':id']) }}".replace(':id', id), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        const text = await response.text();
                        console.error('Respuesta no JSON:', text.substring(0, 500));
                        throw new Error(`Error HTTP ${response.status}`);
                    }
                    if (!response.ok) {
                        throw new Error(data.message || `Error HTTP ${response.status}`);
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Cerrada!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    Swal.fire('Error', error.message || 'Error de conexión', 'error');
                });
            }
        });
    }

    function devolverPrestamo(id) {
        Swal.fire({
            title: '¿Devolver préstamo?',
            text: 'Confirma que el préstamo ha sido devuelto.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, devolver',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('/cpanel/boveda/prestamo/devolver/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ title: '¡Devuelto!', text: data.message, icon: 'success', timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            Swal.fire({ title: '¡Éxito!', text: '{{ session('success') }}', icon: 'success', timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ title: 'Error', text: '{{ session('error') }}', icon: 'error', confirmButtonColor: '#dc2626' });
        @endif
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
</style>
@endpush