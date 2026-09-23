@extends('layout.layout_dashboard')

@section('title', 'Cierre Diario Bóveda')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'safe';
    $hdrTitle = 'Cierre Diario Bóveda';
    $hdrSubtitle = 'Conciliación diaria de sucursales';
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
                    <li class="breadcrumb-item active" aria-current="page">Cierre Diario Bóveda</li>
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
                                <i class="bi bi-calendar-check" style="font-size:1.5rem;color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Días con Cierre</p>
                                <h5 class="fw-bold mb-0">{{ $totalDias }}</h5>
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
                                 style="width:48px;height:48px;background:rgba(108,117,125,0.1);">
                                <i class="bi bi-dash-circle text-secondary" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Sin Bóveda</p>
                                <h5 class="fw-bold mb-0 text-secondary">{{ $totalDiasSinBoveda }}</h5>
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
                                <i class="bi bi-unlock text-warning" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Con Bóveda Abierta</p>
                                <h5 class="fw-bold mb-0 text-warning">{{ $totalDiasConAbierta }}</h5>
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
                                <i class="bi bi-lock-fill text-success" style="font-size:1.5rem;"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">Con Bóveda Finalizada</p>
                                <h5 class="fw-bold mb-0 text-success">{{ $totalDiasConFinal }}</h5>
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
                <form method="GET" action="{{ route('cpanel.boveda.index') }}" id="formFiltros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="fecha_inicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Inicio
                            </label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                   class="form-control"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_fin" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                <i class="bi bi-calendar me-1" style="color:#8b5cf6;"></i>Fecha Fin
                            </label>
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn w-100 fw-semibold text-white"
                                        style="background:{{ $hdrBg }};border:none;">
                                    <i class="bi bi-search me-1"></i> Filtrar
                                </button>
                                <a href="{{ route('cpanel.boveda.index') }}" 
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

        {{-- ================================================ --}}
        {{-- TABLA DE CIERRES DIARIOS AGRUPADOS POR FECHA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Cierres Diarios por Fecha
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#7c3aed;">
                            {{ $totalDias }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelBoveda()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-clock-history me-1"></i>
                            {{ Carbon::now()->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaBoveda">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:110px;">
                                    FECHA
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">
                                    SUCURSALES
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:140px;">
                                    VENTA SISTEMA
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:140px;">
                                    EFECTIVO BS.
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:180px;">
                                    BÓVEDA
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listado as $fila)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span class="fw-semibold">{{ $fila->FechaFormateada }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $fila->CantidadSucursales }} suc.
                                        </span>
                                        <small class="d-block text-muted mt-1" style="font-size:0.7rem;">
                                            {{ $fila->Sucursales->take(3)->implode(', ') }}
                                            @if($fila->Sucursales->count() > 3)
                                                <span class="text-primary">+{{ $fila->Sucursales->count() - 3 }} más</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold">Bs. {{ number_format($fila->VentaSistemaTotal, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold">Bs. {{ number_format($fila->EfectivoBsTotal, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $fila->EstadoBadge }}" style="font-size:0.75rem;">
                                            {{ $fila->EstadoBoveda }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        @if($fila->Accion == 'crear')
                                            <a href="{{ route('cpanel.boveda.crear', ['fecha_cierre' => $fila->Fecha]) }}"
                                               class="btn btn-sm btn-success fw-semibold"
                                               style="font-size:0.75rem;"
                                               title="Crear bóveda para esta fecha">
                                                <i class="bi bi-plus-circle me-1"></i> Crear
                                            </a>
                                        @elseif($fila->Accion == 'retomar')
                                            <a href="{{ route('cpanel.boveda.detalle', $fila->BovedaId) }}"
                                               class="btn btn-sm btn-warning fw-semibold"
                                               style="font-size:0.75rem;"
                                               title="Retomar bóveda abierta">
                                                <i class="bi bi-pencil me-1"></i> Retomar
                                            </a>
                                        @else
                                            <a href="{{ route('cpanel.boveda.detalle', $fila->BovedaId) }}"
                                               class="btn btn-sm btn-info fw-semibold"
                                               style="font-size:0.75rem;"
                                               title="Ver bóveda finalizada">
                                                <i class="bi bi-eye me-1"></i> Ver
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size:2rem;opacity:0.5;"></i>
                                        <p class="mb-0 mt-2">No hay cierres diarios en el rango seleccionado</p>
                                        <small class="text-muted">Prueba ampliando el rango de fechas</small>
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
                        <i class="bi bi-calendar-check me-1"></i>
                        {{ $totalDias }} día{{ $totalDias != 1 ? 's' : '' }} con cierre
                        ({{ $totalCierres }} cierre{{ $totalCierres != 1 ? 's' : '' }} en total)
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
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
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
    // EXPORTAR EXCEL
    // ============================================
    function exportarExcelBoveda() {
        const tabla = document.getElementById('tablaBoveda');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar el listado de cierres diarios?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Cierres" });
                XLSX.utils.book_append_sheet(wb, ws, 'Cierres Diarios');
                XLSX.writeFile(wb, `Cierres_Diarios_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // ============================================
    // VALIDACIÓN DE FECHAS Y MENSAJES
    // ============================================
    document.addEventListener("DOMContentLoaded", function() {
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

        // Mensajes
        @if(session('success'))
            Swal.fire({ title: '¡Éxito!', text: '{{ session('success') }}', icon: 'success', timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ title: 'Error', text: '{{ session('error') }}', icon: 'error', confirmButtonColor: '#dc2626' });
        @endif
        @if(session('info'))
            Swal.fire({ title: 'Info', text: '{{ session('info') }}', icon: 'info', timer: 3000, showConfirmButton: false });
        @endif
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