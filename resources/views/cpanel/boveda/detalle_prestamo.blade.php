@extends('layout.layout_dashboard')

@section('title', 'Detalle del Préstamo')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)';
    $hdrIcon = 'info-circle';
    $hdrTitle = 'Detalle del Préstamo';
    $hdrSubtitle = 'Préstamo #' . $prestamo->BovedaPrestamoId;
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.boveda.prestamos') }}">Préstamos</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- INFORMACIÓN GENERAL --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información del Préstamo
                    </h6>
                    <div class="d-flex gap-2">
                        @if($prestamo->Estatus == 0)
                            <button type="button" class="btn btn-sm fw-semibold text-white"
                                    style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);"
                                    onclick="devolverPrestamo({{ $prestamo->BovedaPrestamoId }})">
                                <i class="bi bi-check-circle me-1"></i> Marcar como Devuelto
                            </button>
                        @endif
                        <a href="{{ route('cpanel.boveda.prestamos') }}" 
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
                        <p class="text-muted mb-1" style="font-size:0.75rem;">ID Préstamo</p>
                        <p class="fw-bold text-dark">
                            <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#d97706;">
                                #{{ $prestamo->BovedaPrestamoId }}
                            </code>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Sucursal</p>
                        <p class="fw-bold text-dark">{{ $prestamo->sucursal_nombre ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Fecha Préstamo</p>
                        <p class="fw-bold text-dark">{{ $prestamo->FechaFormateada }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Estatus</p>
                        <span class="badge bg-{{ $prestamo->EstatusBadge }}" style="font-size:0.9rem;">
                            {{ $prestamo->EstatusTexto }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Tipo Moneda</p>
                        <span class="badge bg-{{ $prestamo->TipoMoneda == 0 ? 'success' : 'primary' }}" style="font-size:0.9rem;">
                            {{ $prestamo->MonedaTexto }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Monto Original</p>
                        <p class="fw-bold text-dark fs-5">
                            {{ $prestamo->MonedaSimbolo }} {{ number_format($prestamo->TipoMoneda == 0 ? $prestamo->MontoDivisa : $prestamo->MontoBs, 2) }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Saldo Pendiente</p>
                        <p class="fw-bold fs-5 {{ $prestamo->SaldoPendiente > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $prestamo->MonedaSimbolo }} {{ number_format($prestamo->SaldoPendiente, 2) }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Tasa de Cambio</p>
                        <p class="fw-bold text-dark">Bs. {{ number_format($prestamo->TasaCambio ?? 0, 2) }}</p>
                    </div>

                    @if($prestamo->TipoMoneda == 0)
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Equivalente en Bolívares</p>
                        <p class="fw-semibold text-primary">Bs. {{ number_format($prestamo->MontoBs, 2) }}</p>
                    </div>
                    @else
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Equivalente en Divisas</p>
                        <p class="fw-semibold text-success">$ {{ number_format($prestamo->MontoDivisa, 2) }}</p>
                    </div>
                    @endif

                    @if($prestamo->FechaDevolucion)
                    <div class="col-md-6">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Fecha Devolución</p>
                        <p class="fw-bold text-success">{{ $prestamo->FechaDevolucionFormateada }}</p>
                    </div>
                    @endif

                    <div class="col-12">
                        <p class="text-muted mb-1" style="font-size:0.75rem;">Observación</p>
                        <p class="fw-semibold text-dark">{{ $prestamo->Observacion ?? 'Sin observación' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- DENOMINACIONES --}}
        @if($denominaciones->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-cash-stack me-2"></i>Denominaciones Entregadas
                    </h6>
                    <span class="badge bg-white text-primary fw-bold">
                        Total: {{ $prestamo->MonedaSimbolo }} {{ number_format($totalDenominaciones, 2) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;">DENOMINACIÓN</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">CANTIDAD</th>
                                <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($denominaciones as $den)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    {{ $prestamo->MonedaSimbolo }} {{ number_format($den->Denominacion, 2) }}
                                </td>
                                <td class="text-center">{{ $den->Cantidad }}</td>
                                <td class="pe-4 text-end fw-bold">
                                    {{ $prestamo->MonedaSimbolo }} {{ number_format($den->MontoTotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#eff6ff;">
                            <tr>
                                <th colspan="2" class="ps-4 py-3 text-end">TOTAL:</th>
                                <th class="pe-4 py-3 text-end text-primary">
                                    {{ $prestamo->MonedaSimbolo }} {{ number_format($totalDenominaciones, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function devolverPrestamo(id) {
        Swal.fire({
            title: '¿Marcar como devuelto?',
            text: 'Confirma que la sucursal devolvió el préstamo.',
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

                fetch('{{ url("cpanel/boveda/prestamos/devolver") }}/' + id, {
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
                        Swal.fire({
                            title: '¡Devuelto!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush