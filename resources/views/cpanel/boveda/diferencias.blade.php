@extends('layout.layout_dashboard')

@section('title', 'Diferencias Bóveda')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#dc2626 0%,#991b1b 100%)';
    $hdrIcon = 'exclamation-triangle';
    $hdrTitle = 'Diferencias Bóveda';
    $hdrSubtitle = 'Bóvedas con diferencias en conciliación';
    
    $tiposOtros = [
        1 => 'Biopago',
        2 => 'Transferencia',
        3 => 'Cashea',
        4 => 'Zelle'
    ];
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
                    <li class="breadcrumb-item active" aria-current="page">Diferencias</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Resumen --}}
        @if($bovedasConDiferencias->count() > 0)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(220,38,38,0.1);">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Bóvedas con Diferencias</p>
                                <h5 class="fw-bold mb-0">{{ $bovedasConDiferencias->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(245,158,11,0.1);">
                                <i class="bi bi-clock-history text-warning" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Bóvedas Abiertas</p>
                                <h5 class="fw-bold mb-0">{{ $bovedasConDiferencias->where('Estatus', 0)->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                <i class="bi bi-calendar text-purple" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Última Diferencia</p>
                                <h5 class="fw-bold mb-0">
                                    {{ $bovedasConDiferencias->first() ? Carbon::parse($bovedasConDiferencias->first()->Fecha)->format('d/m/Y') : 'N/A' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Lista de bóvedas con diferencias --}}
        @forelse($bovedasConDiferencias as $index => $boveda)
        @php
            $totalDiferencias = $boveda->diferenciasPDV->count() + 
                                $boveda->diferenciasOtros->count() + 
                                $boveda->diferenciasEfectivo->count();
            $montoTotal = $boveda->diferenciasPDV->sum('Diferencia') + 
                          $boveda->diferenciasOtros->sum('Diferencia') + 
                          $boveda->diferenciasEfectivo->sum('Diferencia');
        @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0 py-2" style="background:linear-gradient(135deg,#dc2626 0%,#991b1b 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-safe me-2"></i>
                            Bóveda #{{ $boveda->BovedaId }}
                        </h6>
                        <span class="badge bg-white text-dark">
                            {{ Carbon::parse($boveda->Fecha)->format('d/m/Y') }}
                        </span>
                        <span class="badge bg-warning text-dark">
                            {{ $boveda->sucursal_nombre ?? 'N/A' }}
                        </span>
                        @if($boveda->Estatus == 0)
                            <span class="badge bg-success">
                                <i class="bi bi-unlock me-1"></i>Abierta
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-lock me-1"></i>Cerrada
                            </span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-danger">
                            {{ $totalDiferencias }} diferencia(s)
                        </span>
                        <a href="{{ route('cpanel.boveda.detalle', $boveda->BovedaId) }}" 
                           class="btn btn-sm fw-semibold text-white"
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-eye me-1"></i> Ver Detalle
                        </a>
                        @if($boveda->Estatus == 0)
                            <a href="{{ route('cpanel.boveda.editar', $boveda->BovedaId) }}" 
                               class="btn btn-sm fw-semibold text-white"
                               style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#fef2f2;">
                            <tr>
                                <th class="ps-4 py-2 text-danger fw-semibold" style="font-size:0.75rem;">TIPO</th>
                                <th class="py-2 text-danger fw-semibold" style="font-size:0.75rem;">CONCEPTO</th>
                                <th class="py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">SISTEMA</th>
                                <th class="py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">DEPOSITADO</th>
                                <th class="pe-4 py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Diferencias de Efectivo --}}
                            @foreach($boveda->diferenciasEfectivo as $dif)
                            <tr class="table-danger">
                                <td class="ps-4">
                                    <span class="badge bg-danger">Efectivo</span>
                                </td>
                                <td>
                                    Efectivo {{ $dif->Tipo == 1 ? 'Divisas (USD)' : 'Bolívares (Bs.)' }}
                                </td>
                                <td class="text-end">
                                    {{ $dif->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($dif->MontoSistema, 2) }}
                                </td>
                                <td class="text-end">
                                    {{ $dif->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($dif->MontoDepositado, 2) }}
                                </td>
                                <td class="pe-4 text-end fw-bold text-danger">
                                    {{ $dif->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($dif->Diferencia, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- Diferencias de PDV --}}
                            @foreach($boveda->diferenciasPDV as $dif)
                            <tr class="table-danger">
                                <td class="ps-4">
                                    <span class="badge bg-purple" style="background:#8b5cf6;">PDV</span>
                                </td>
                                <td>
                                    {{ $dif->pdv_descripcion ?? 'N/A' }}
                                    <small class="d-block text-muted">{{ $dif->pdv_codigo ?? '' }}</small>
                                </td>
                                <td class="text-end">Bs. {{ number_format($dif->MontoSistema, 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($dif->MontoDepositado, 2) }}</td>
                                <td class="pe-4 text-end fw-bold text-danger">
                                    Bs. {{ number_format($dif->Diferencia, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- Diferencias de Otros --}}
                            @foreach($boveda->diferenciasOtros as $dif)
                            @php
                                $moneda = $dif->Tipo == 4 ? '$' : 'Bs.';
                                $color = $dif->Tipo == 1 ? 'info' : ($dif->Tipo == 2 ? 'primary' : ($dif->Tipo == 3 ? 'warning' : 'success'));
                            @endphp
                            <tr class="table-danger">
                                <td class="ps-4">
                                    <span class="badge bg-{{ $color }}">
                                        {{ $tiposOtros[$dif->Tipo] ?? 'Otro' }}
                                    </span>
                                </td>
                                <td>{{ $tiposOtros[$dif->Tipo] ?? 'N/A' }}</td>
                                <td class="text-end">{{ $moneda }} {{ number_format($dif->MontoSistema, 2) }}</td>
                                <td class="text-end">{{ $moneda }} {{ number_format($dif->MontoDepositado, 2) }}</td>
                                <td class="pe-4 text-end fw-bold text-danger">
                                    {{ $moneda }} {{ number_format($dif->Diferencia, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- Fila de total --}}
                            @if($totalDiferencias > 0)
                            <tr style="background:#fef2f2;border-top:2px solid #dc2626;">
                                <td colspan="4" class="ps-4 py-2 text-end fw-bold">
                                    TOTAL DIFERENCIAS ({{ $totalDiferencias }}):
                                </td>
                                <td class="pe-4 py-2 text-end fw-bold text-danger">
                                    {{ number_format($montoTotal, 2) }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @empty
        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width:80px;height:80px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                    <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">¡No hay diferencias!</h5>
                <p class="text-muted mb-4" style="font-size:0.9rem;">
                    Todas las bóvedas están conciliadas correctamente.
                </p>
                <a href="{{ route('cpanel.boveda.index') }}" class="btn px-4 fw-semibold text-white"
                   style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);border:none;">
                    <i class="bi bi-list-ul me-2"></i>Ver Bóvedas
                </a>
            </div>
        </div>
        @endforelse

    </div>
</div>

@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .text-purple { color: #8b5cf6; }
</style>
@endpush