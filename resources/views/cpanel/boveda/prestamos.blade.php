@extends('layout.layout_dashboard')

@section('title', 'Préstamos a Sucursal')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)';
    $hdrIcon = 'arrow-left-right';
    $hdrTitle = 'Préstamos a Sucursal';
    $hdrSubtitle = 'Gestión de préstamos de bóveda a sucursales';
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
                    <li class="breadcrumb-item"><a href="#">Bóveda</a></li>
                    <li class="breadcrumb-item active">Préstamos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- TARJETAS DE ESTADÍSTICAS --}}
        {{-- ================================================ --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 d-flex align-items-center justify-content-center me-3"
                                 style="width:48px;height:48px;background:rgba(139,92,246,0.1);">
                                <i class="bi bi-list-ul" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Total Préstamos</p>
                                <h5 class="fw-bold mb-0">{{ $totales['total_prestamos'] }}</h5>
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
                                <i class="bi bi-clock-history text-warning" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Pendientes</p>
                                <h5 class="fw-bold mb-0 text-warning">{{ $totales['prestamos_pendientes'] }}</h5>
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
                                 style="width:48px;height:48px;background:rgba(16,185,129,0.1);">
                                <i class="bi bi-cash text-success" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Pendiente USD</p>
                                <h5 class="fw-bold mb-0 text-success">$ {{ number_format($totales['total_pendiente_divisa'], 2) }}</h5>
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
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Pendiente Bs.</p>
                                <h5 class="fw-bold mb-0 text-primary">Bs. {{ number_format($totales['total_pendiente_bs'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- FILTROS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('cpanel.boveda.prestamos') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#d97706;"></i>Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#d97706;"></i>Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-2">
                            <label for="estatus" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-toggle-on me-1" style="color:#d97706;"></i>Estatus
                            </label>
                            <select name="estatus" id="estatus" class="form-select">
                                <option value="todos" {{ $estatusFiltro == 'todos' ? 'selected' : '' }}>Todos</option>
                                <option value="0" {{ $estatusFiltro === '0' ? 'selected' : '' }}>Pendientes</option>
                                <option value="1" {{ $estatusFiltro === '1' ? 'selected' : '' }}>Devueltos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sucursal_id" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-building me-1" style="color:#d97706;"></i>Sucursal
                            </label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->ID }}" {{ $sucursalFiltro == $suc->ID ? 'selected' : '' }}>
                                        {{ $suc->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo_moneda" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-currency-exchange me-1" style="color:#d97706;"></i>Moneda
                            </label>
                            <select name="tipo_moneda" id="tipo_moneda" class="form-select">
                                <option value="todos" {{ $tipoMonedaFiltro == 'todos' ? 'selected' : '' }}>Todas</option>
                                <option value="0" {{ $tipoMonedaFiltro === '0' ? 'selected' : '' }}>Divisas</option>
                                <option value="1" {{ $tipoMonedaFiltro === '1' ? 'selected' : '' }}>Bolívares</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn w-100 fw-semibold text-white"
                                        style="background:{{ $hdrBg }};border:none;">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="{{ route('cpanel.boveda.prestamos') }}" 
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
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="setRango('hoy')">Hoy</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="setRango('semana')">Última semana</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="setRango('mes')">Este mes</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="setRango('mes_anterior')">Mes anterior</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="setRango('anio')">Este año</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info del rango --}}
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>
                Mostrando préstamos desde <strong>{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</strong>
                hasta <strong>{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</strong>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PRÉSTAMOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Préstamos Registrados
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#d97706;">
                            {{ $prestamos->count() }}
                        </span>
                    </h6>
                    <a href="{{ route('cpanel.boveda.crear_prestamo') }}" 
                       class="btn btn-sm fw-semibold text-white"
                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Préstamo
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaPrestamos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;">ID</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">FECHA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">TIPO MONEDA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">MONTO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">SALDO</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">ESTATUS</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestamos as $prestamo)
                                <tr>
                                    <td class="ps-4">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#d97706;font-size:0.8rem;font-weight:bold;">
                                            #{{ $prestamo->BovedaPrestamoId }}
                                        </code>
                                    </td>
                                    <td>{{ $prestamo->sucursal_nombre ?? 'N/A' }}</td>
                                    <td>{{ $prestamo->FechaFormateada }}</td>
                                    <td>
                                        <span class="badge bg-{{ $prestamo->TipoMoneda == 0 ? 'success' : 'primary' }}">
                                            {{ $prestamo->MonedaTexto }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        {{ $prestamo->TipoMoneda == 0 ? '$' : 'Bs.' }} 
                                        {{ number_format($prestamo->TipoMoneda == 0 ? $prestamo->MontoDivisa : $prestamo->MontoBs, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $prestamo->SaldoPendiente > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $prestamo->TipoMoneda == 0 ? '$' : 'Bs.' }} 
                                            {{ number_format($prestamo->SaldoPendiente, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $prestamo->EstatusBadge }}">
                                            {{ $prestamo->EstatusTexto }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('cpanel.boveda.detalle_prestamo', $prestamo->BovedaPrestamoId) }}" 
                                               class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                               title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>
                                            @if($prestamo->Estatus == 0)
                                                <button type="button" 
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                        onclick="devolverPrestamo({{ $prestamo->BovedaPrestamoId }})"
                                                        title="Marcar como devuelto" data-bs-toggle="tooltip">
                                                    <i class="bi bi-check-circle" style="font-size:0.8rem;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox me-2"></i>
                                        No hay préstamos registrados en el rango seleccionado
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
                        <i class="bi bi-arrow-left-right me-1"></i>
                        {{ $prestamos->count() }} préstamo(s)
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // RANGOS RÁPIDOS
    // ============================================
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

    // ============================================
    // DEVOLVER PRÉSTAMO
    // ============================================
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

    // ============================================
    // TOOLTIPS Y VALIDACIÓN
    // ============================================
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });

        // Validar fechas
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
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
</style>
@endpush