@extends('layout.layout_dashboard')

@section('title', 'Lista de Inventarios')

@php
    use Carbon\Carbon;
    
    // Calcular estadísticas
    $totalInventarios = $listaInventarios->count();
    $nuevos = $listaInventarios->where('Estatus', 0)->count();
    $enConteo = $listaInventarios->where('Estatus', 1)->count();
    $enAuditoria = $listaInventarios->where('Estatus', 2)->count();
    $cerrados = $listaInventarios->where('Estatus', 3)->count();
    
    $estatusLabels = [
        0 => 'Nuevo',
        1 => 'En Conteo',
        2 => 'En Auditoría',
        3 => 'Cerrado'
    ];
    
    $estatusColors = [
        0 => '#6366f1',
        1 => '#22c55e',
        2 => '#eab308',
        3 => '#ef4444'
    ];
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Inventarios Planificados</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Muestra los inventarios planificados activos
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventarios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card border-0 shadow-sm">
                    {{-- Header con gradiente verde (como el icono) --}}
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h2 class="mb-0 fw-bold text-white" style="font-size:1.15rem;">
                                    <i class="bi bi-clipboard-check me-2"></i>Inventarios Planificados
                                    <small class="d-block text-white-50" style="font-size:0.7rem;font-weight:400;">
                                        Muestra los inventarios planificados activos
                                    </small>
                                </h2>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{-- Filtro de fecha --}}
                                <form method="GET" action="{{ route('cpanel.inventario.listado') }}" class="d-flex align-items-center gap-2">
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                                           style="width:130px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;border-radius:6px;font-size:0.78rem;"
                                           value="{{ request('fecha_inicio') }}">
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                                           style="width:130px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;border-radius:6px;font-size:0.78rem;"
                                           value="{{ request('fecha_fin') }}">
                                    <button type="submit" class="btn btn-sm text-white" 
                                            style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </form>
                                
                                @php
                                    $estatusLabelsFiltro = [
                                        3 => 'CERRADO',
                                        1 => 'EN CONTEO',
                                        2 => 'EN AUDITORIA',
                                        0 => 'TODOS'
                                    ];
                                    $currentTtmnd = (int) (request('ttmnd') ?? 1);
                                    $currentLabel = $estatusLabelsFiltro[$currentTtmnd] ?? 'EN CONTEO';
                                @endphp

                                {{-- Botones header --}}
                                <a href="{{ route('cpanel.inventario.crear') }}" class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;font-size:0.78rem;">
                                    <i class="bi bi-plus-circle me-1"></i> Nuevo
                                </a>

                                <div class="dropdown">
                                    <button class="btn btn-sm text-white dropdown-toggle" 
                                            style="background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;font-size:0.78rem;"
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-filter me-1"></i> {{ $currentLabel }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:8px;padding:0.25rem;">
                                        <li>
                                            <a class="dropdown-item {{ $currentTtmnd === 3 ? 'active' : '' }}" 
                                               href="{{ route('cpanel.inventario.listado', ['ttmnd' => 3]) }}"
                                               style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                CERRADO
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ $currentTtmnd === 1 ? 'active' : '' }}" 
                                               href="{{ route('cpanel.inventario.listado', ['ttmnd' => 1]) }}"
                                               style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                EN CONTEO
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ $currentTtmnd === 2 ? 'active' : '' }}" 
                                               href="{{ route('cpanel.inventario.listado', ['ttmnd' => 2]) }}"
                                               style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                EN AUDITORIA
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ $currentTtmnd === 0 ? 'active' : '' }}" 
                                               href="{{ route('cpanel.inventario.listado', ['ttmnd' => 0]) }}"
                                               style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                TODOS
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body p-0">
                        {{-- Mensajes --}}
                        @if(session('success'))
                            <div class="alert alert-success m-3">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger m-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Tabla de inventarios --}}
                        <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                            <table id="datatable-listadoInventarios" class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">CÓDIGO</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:180px;">SUCURSAL</th>
                                        <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:220px;">FECHA</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">ITEMS</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">UNIDADES</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">EXACTITUD</th>
                                        <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ESTATUS</th>
                                        <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:200px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($listaInventarios && count($listaInventarios) > 0)
                                        @foreach($listaInventarios->sortByDesc('FechaInicio') as $item)
                                        @php
                                            $estatus = (int)($item->Estatus ?? 0);
                                            
                                            // Calcular porcentajes
                                            $itemsParaContar = (int)($item->ItemsParaContar ?? 0);
                                            $itemsContados = (int)($item->ItemsContadosReales ?? $item->ItemsContados ?? 0);
                                            $porcentajeItems = $itemsParaContar > 0 ? round(($itemsContados / $itemsParaContar) * 100, 2) : 0;
                                            
                                            $cantidadParaContar = (float)($item->CantidadParaContar ?? 0);
                                            $cantidadContada = (float)($item->CantidadContada ?? 0);
                                            $porcentajeCantidad = $cantidadParaContar > 0 ? round(($cantidadContada / $cantidadParaContar) * 100, 2) : 0;
                                            
                                            $coincidencias = (int)($item->CoincidenciasReales ?? 0);
                                            $exactitud = $itemsParaContar > 0 ? round(($coincidencias / $itemsParaContar) * 100, 2) : 0;
                                            
                                            // Estatus y colores
                                            $estatusTexto = $estatusLabels[$estatus] ?? 'Desconocido';
                                            $badgeColor = $estatusColors[$estatus] ?? '#6b7280';
                                        @endphp
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            {{-- Código --}}
                                            <td class="ps-4">
                                                <code class="px-2 py-1 rounded-2"
                                                      style="background:#f1f5f9;color:#22c55e;font-size:0.8rem;font-weight:bold;">
                                                    {{ $item->Codigo ?? 'N/A' }}
                                                </code>
                                            </td>
                                            
                                            {{-- Sucursal --}}
                                            <td>
                                                <span class="fw-semibold text-dark" style="font-size:0.85rem;">
                                                    {{ $item->Sucursal->Nombre ?? 'N/A' }}
                                                </span>
                                            </td>
                                            
                                            {{-- Fechas --}}
                                            <td>
                                                <ul class="list-unstyled mb-0" style="font-size:0.75rem;color:#4b5563;">
                                                    <li>
                                                        <span class="text-muted">Creado:</span>
                                                        <span class="fw-semibold">{{ $item->FechaInicio ? date('d/m/Y H:i', strtotime($item->FechaInicio)) : 'N/A' }}</span>
                                                    </li>
                                                    @if($estatus != 0)
                                                        <li>
                                                            <span class="text-muted">Conteo:</span>
                                                            <span class="fw-semibold">{{ $item->FechaConteo ? date('d/m/Y H:i', strtotime($item->FechaConteo)) : 'N/A' }}</span>
                                                        </li>
                                                        @if($estatus == 3)
                                                            <li>
                                                                <span class="text-muted">Cierre:</span>
                                                                <span class="fw-semibold">{{ $item->FechaCierre ? date('d/m/Y H:i', strtotime($item->FechaCierre)) : 'N/A' }}</span>
                                                            </li>
                                                        @endif
                                                    @endif
                                                    <li>
                                                        <span class="text-muted">Últ. día:</span>
                                                        <span class="fw-semibold">{{ $item->FechaFin ? date('d/m/Y H:i', strtotime($item->FechaFin)) : 'N/A' }}</span>
                                                    </li>
                                                </ul>
                                            </td>
                                            
                                            {{-- Items --}}
                                            <td class="text-center">
                                                <span class="fw-bold text-primary">{{ $porcentajeItems }}%</span>
                                                <div class="progress" style="height:4px;border-radius:9999px;background:#e5e7eb;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $porcentajeItems }}%; background: linear-gradient(90deg, #84cc16, #22c55e);"
                                                         aria-valuenow="{{ $porcentajeItems }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted" style="font-size:0.7rem;">
                                                    {{ number_format($itemsContados, 0) }} / {{ number_format($itemsParaContar, 0) }}
                                                </small>
                                            </td>
                                            
                                            {{-- Unidades --}}
                                            <td class="text-center">
                                                <span class="fw-bold text-primary">{{ $porcentajeCantidad }}%</span>
                                                <div class="progress" style="height:4px;border-radius:9999px;background:#e5e7eb;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $porcentajeCantidad }}%; background: linear-gradient(90deg, #22c55e, #16a34a);"
                                                         aria-valuenow="{{ $porcentajeCantidad }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted" style="font-size:0.7rem;">
                                                    {{ number_format($cantidadContada, 0) }} / {{ number_format($cantidadParaContar, 0) }}
                                                </small>
                                            </td>
                                            
                                            {{-- Exactitud --}}
                                            <td class="text-center">
                                                <span class="fw-bold text-teal">{{ $exactitud }}%</span>
                                                <div class="progress" style="height:4px;border-radius:9999px;background:#e5e7eb;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $exactitud }}%; background: linear-gradient(90deg, #14b8a6, #0d9488);"
                                                         aria-valuenow="{{ $exactitud }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            {{-- Estatus --}}
                                            <td class="text-center">
                                                <span class="badge rounded-pill px-3 py-2"
                                                      style="background:{{ $badgeColor }}; color: {{ $estatus == 2 ? '#000' : '#fff' }}; font-size:0.7rem;font-weight:500;">
                                                    {{ $estatusTexto }}
                                                </span>
                                            </td>
                                            
                                            {{-- Acciones --}}
                                            <td class="pe-4 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-1">
                                                    @switch($estatus)
                                                        @case(0) {{-- Nuevo --}}
                                                            <a href="{{ route('cpanel.inventario.editar', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                               title="Editar" data-bs-toggle="tooltip">
                                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.iniciar-conteo', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(34,197,94,0.1);color:#16a34a;border:1px solid rgba(34,197,94,0.25);"
                                                               title="Iniciar Conteo" data-bs-toggle="tooltip">
                                                                <i class="bi bi-play-fill" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.generar-plantilla-conteo', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);"
                                                               title="Plantilla de conteo" data-bs-toggle="tooltip">
                                                                <i class="bi bi-arrow-return-left" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            @break
                                                            
                                                        @case(1) {{-- En Conteo --}}
                                                        @case(2) {{-- En Auditoria --}}
                                                            <a href="{{ route('cpanel.inventario.iniciar-conteo', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                                               title="Tomar Conteo" data-bs-toggle="tooltip">
                                                                <i class="bi bi-list-ol" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.monitorear-conteo', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);"
                                                               title="Monitor de conteo" data-bs-toggle="tooltip">
                                                                <i class="bi bi-display" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.generar-plantilla-conteo', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);"
                                                               title="Plantilla de conteo" data-bs-toggle="tooltip">
                                                                <i class="bi bi-arrow-return-left" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.generar-resultados-excel', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(236,72,153,0.1);color:#db2777;border:1px solid rgba(236,72,153,0.25);"
                                                               title="Resultados" data-bs-toggle="tooltip">
                                                                <i class="bi bi-file-earmark-excel" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            @break
                                                            
                                                        @case(3) {{-- Cerrado --}}
                                                            <a href="" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);"
                                                               title="Detalles" data-bs-toggle="tooltip">
                                                                <i class="bi bi-book" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            <a href="{{ route('cpanel.inventario.generar-resultados-excel', $item->InventarioId) }}" 
                                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                               style="width:30px;height:30px;background:rgba(236,72,153,0.1);color:#db2777;border:1px solid rgba(236,72,153,0.25);"
                                                               title="Resultados" data-bs-toggle="tooltip">
                                                                <i class="bi bi-file-earmark-excel" style="font-size:0.8rem;"></i>
                                                            </a>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width:72px;height:72px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                                                    <i class="bi bi-clipboard-check text-muted" style="font-size:2rem;opacity:.5;"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark mb-1">No hay inventarios planificados</h5>
                                                <p class="text-muted mb-0" style="font-size:0.9rem;">
                                                    Los inventarios aparecerán aquí cuando sean creados
                                                </p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer con resumen --}}
                        @if($listaInventarios && count($listaInventarios) > 0)
                        <div class="card-footer bg-white py-2 px-4" style="border-top:1px solid #f1f5f9;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="bi bi-clipboard-check me-1"></i>
                                        Total: <strong>{{ $totalInventarios }}</strong> inventario(s)
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        <span class="badge rounded-pill me-1" style="background:#6366f1;color:#fff;font-size:0.65rem;">
                                            <i class="bi bi-plus-circle me-1"></i> Nuevos: {{ $nuevos }}
                                        </span>
                                        <span class="badge rounded-pill me-1" style="background:#22c55e;color:#fff;font-size:0.65rem;">
                                            <i class="bi bi-play-fill me-1"></i> Conteo: {{ $enConteo }}
                                        </span>
                                        <span class="badge rounded-pill me-1" style="background:#eab308;color:#000;font-size:0.65rem;">
                                            <i class="bi bi-search me-1"></i> Auditoría: {{ $enAuditoria }}
                                        </span>
                                        <span class="badge rounded-pill" style="background:#ef4444;color:#fff;font-size:0.65rem;">
                                            <i class="bi bi-check-circle me-1"></i> Cerrados: {{ $cerrados }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Inicializar DataTable
    $('#datatable-listadoInventarios').DataTable({
        order: [[0, "desc"]],
        searching: false,
        info: false,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });
});

function GenerarResultadosConteo(eventJS, inventarioId) {
    Swal.fire({
        title: 'Generando resultados...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: "",
        type: 'POST',
        data: {
            InventarioId: inventarioId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Resultados generados',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });
            $('#btnDescargarResultados' + inventarioId).prop('disabled', false);
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Error al generar resultados'
            });
        }
    });
}
</script>
@endsection

@push('styles')
<style>
    #datatable-listadoInventarios tbody tr:hover { background: #f8fafc; }
    
    .text-teal { color: #14b8a6; }
    
    .progress {
        border-radius: 9999px;
        overflow: hidden;
        background: #e5e7eb;
    }
    .progress-bar {
        transition: width 0.6s ease;
        border-radius: 9999px;
    }
    
    .table-hover tbody tr:hover {
        background: #f8fafc;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.02em;
    }
</style>
@endpush