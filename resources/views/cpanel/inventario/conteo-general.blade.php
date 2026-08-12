@extends('layout.layout_dashboard')

@section('title', 'Conteo General de Inventario')

@php
    use App\Helpers\FileHelper;

    $inventario = $inventario ?? (object)[];
    $detalles = $detalles ?? [];
    $sucursal = $sucursal ?? (object)[];
    
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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Conteo General de Inventario
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Realiza un conteo de inventario de tipo General
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.inventario.listado') }}">Inventarios</a></li>
                    <li class="breadcrumb-item active">Conteo</li>
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
                    {{-- Header --}}
                    <div class="card-header border-0 py-3"
                         style="background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h2 class="mb-0 fw-bold text-white" style="font-size:1.15rem;">
                                    <i class="bi bi-clipboard-check me-2"></i>Conteo General de Inventario
                                    <small class="d-block text-white-50" style="font-size:0.7rem;font-weight:400;">
                                        Realiza un conteo de inventario de tipo General
                                    </small>
                                </h2>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('cpanel.inventario.generar-plantilla-conteo', $inventario->InventarioId ?? 0) }}" 
                                class="btn btn-sm text-white" 
                                style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Arch.Conteo
                                </a>
                                <button class="btn btn-sm text-white" 
                                        style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;"
                                        data-bs-toggle="modal" data-bs-target="#modalCargarConteoExcel">
                                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Cargar conteo
                                </button>
                                @if(isset($inventario->Estatus) && $inventario->Estatus == 0)
                                <a href="{{ route('cpanel.inventario.iniciar-conteo', $inventario->InventarioId) }}" 
                                   class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-play-circle me-1"></i> Iniciar conteo
                                </a>
                                @endif
                                @if(isset($inventario->Estatus) && $inventario->Estatus == 1)
                                <div class="dropdown">
                                    <button class="btn btn-sm text-white dropdown-toggle" 
                                            style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;"
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-list me-1"></i> Auditar
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:8px;padding:0.25rem;">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cpanel.inventario.auditar-conteo', ['id' => $inventario->InventarioId, 'tipo' => 'diferencias']) }}" 
                                            style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                <i class="bi bi-exclamation-triangle text-warning me-2"></i> Diferencias
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cpanel.inventario.auditar-conteo', ['id' => $inventario->InventarioId, 'tipo' => 'exactos']) }}" 
                                            style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                <i class="bi bi-check-circle text-success me-2"></i> Exactos
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cpanel.inventario.auditar-conteo', ['id' => $inventario->InventarioId, 'tipo' => 'novendible']) }}" 
                                            style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                <i class="bi bi-trash text-danger me-2"></i> No vendibles
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cpanel.inventario.generar-resultados-excel', $inventario->InventarioId) }}" 
                                            style="border-radius:6px;font-size:0.8rem;padding:0.35rem 0.9rem;">
                                                <i class="bi bi-file-earmark-excel text-success me-2"></i> Resultados
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                                {{-- Finalizar (solo si es EnConteo o EnAuditoria) --}}
                                @if(isset($inventario->Estatus) && ($inventario->Estatus == 1 || $inventario->Estatus == 2))
                                    <a href="#" 
                                    class="btn btn-sm text-white" 
                                    style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;"
                                    onclick="confirmarFinalizar(event, {{ $inventario->InventarioId ?? 0 }})">
                                        <i class="bi bi-stop-circle me-1"></i> Finalizar
                                    </a>
                                @endif
                                <a href="{{ route('cpanel.inventario.listado') }}" 
                                   class="btn btn-sm text-white" 
                                   style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                                    <i class="bi bi-list me-1"></i> Listado
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body p-4">
                        {{-- Panel 1: Información del inventario --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header" style="background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="mb-0 text-white fw-semibold">
                                                    <i class="bi bi-info-circle me-2"></i> INVENTARIO
                                                    <small class="text-white-50 ms-2" style="font-size:0.7rem;font-weight:400;">
                                                        Ver los datos de un inventario planificado
                                                    </small>
                                                </h5>
                                            </div>
                                            <div>
                                                <span class="badge bg-white text-dark rounded-pill px-3 py-2">
                                                    <i class="bi bi-hash me-1"></i> {{ $inventario->InventarioId ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-upc-scan text-primary" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">CÓDIGO</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->Codigo ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-info bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-file-text text-info" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">DESCRIPCIÓN</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->Descripcion ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-success bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-building text-success" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">SUCURSAL</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $sucursal->Nombre ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-warning bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-circle-fill text-warning" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">ESTATUS</small>
                                                        <span class="badge rounded-pill px-2 py-1" 
                                                              style="background:{{ $estatusColors[$inventario->Estatus ?? 0] ?? '#6b7280' }}; color: {{ ($inventario->Estatus ?? 0) == 2 ? '#000' : '#fff' }}; font-size:0.85rem;">
                                                            {{ $estatusLabels[$inventario->Estatus ?? 0] ?? 'Desconocido' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-2 pt-3 border-top">
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-calendar-event text-primary" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">FECHA INICIO</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->FechaInicio ? date('d/m/Y', strtotime($inventario->FechaInicio)) : 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-success bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-calendar-check text-success" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">FECHA FIN</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->FechaFin ? date('d/m/Y', strtotime($inventario->FechaFin)) : 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-warning bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-clock-history text-warning" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">FECHA CONTEO</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->FechaConteo ? date('d/m/Y', strtotime($inventario->FechaConteo)) : 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-danger bg-opacity-10 rounded p-2" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-clock text-danger" style="font-size:1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size:0.65rem;">FECHA CIERRE</small>
                                                        <strong class="text-dark" style="font-size:0.95rem;">{{ $inventario->FechaCierre ? date('d/m/Y', strtotime($inventario->FechaCierre)) : 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel 2: Tarjetas de estadísticas --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:0.7rem;">TOTAL PRODUCTOS</small>
                                                <h4 class="mb-0 fw-bold text-primary">{{ $totalProductos ?? 0 }}</h4>
                                            </div>
                                            <div class="bg-primary rounded p-2" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi bi-boxes text-white" style="font-size:1.4rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:0.7rem;">CONTADOS</small>
                                                <h4 class="mb-0 fw-bold text-success">{{ $contados ?? 0 }}</h4>
                                            </div>
                                            <div class="bg-success rounded p-2" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi bi-check2-circle text-white" style="font-size:1.4rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:0.7rem;">PENDIENTES</small>
                                                <h4 class="mb-0 fw-bold text-warning">{{ $pendientes ?? 0 }}</h4>
                                            </div>
                                            <div class="bg-warning rounded p-2" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi bi-hourglass-split text-white" style="font-size:1.4rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:0.7rem;">AVANCE</small>
                                                <h4 class="mb-0 fw-bold text-info">{{ $porcentaje ?? 0 }}%</h4>
                                            </div>
                                            <div class="bg-info rounded p-2" style="width:45px;height:45px;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi bi-graph-up-arrow text-white" style="font-size:1.4rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel 3: Tabs --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <ul class="nav nav-tabs card-header-tabs" id="conteoTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-agregar-conteo" data-bs-toggle="tab" 
                                                data-bs-target="#tabAgregarConteo" type="button" role="tab">
                                            <i class="bi bi-pencil-square me-1"></i> Agregar conteo
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-sin-contar" data-bs-toggle="tab" 
                                                data-bs-target="#tabSinContar" type="button" role="tab">
                                            <i class="bi bi-hourglass-split me-1"></i> Sin contar
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-diferencias" data-bs-toggle="tab" 
                                                data-bs-target="#tabDiferencias" type="button" role="tab">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Con diferencias
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-exactos" data-bs-toggle="tab" 
                                                data-bs-target="#tabExactos" type="button" role="tab">
                                            <i class="bi bi-check-circle me-1"></i> Exactos
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-no-vendibles" data-bs-toggle="tab" 
                                                data-bs-target="#tabNoVendibles" type="button" role="tab">
                                            <i class="bi bi-trash me-1"></i> No vendibles
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-comparacion" data-bs-toggle="tab" 
                                                data-bs-target="#tabComparacion" type="button" role="tab">
                                            <i class="bi bi-arrow-left-right me-1"></i> Comparación
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="conteoTabsContent">
                                    
                                    {{-- ========================================== --}}
                                    {{-- TAB 1: AGREGAR CONTEO (FORMULARIO MANUAL) --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade show active" id="tabAgregarConteo" role="tabpanel">
                                        <div class="row">
                                            {{-- Columna izquierda: Buscador e información del producto --}}
                                            <div class="col-md-5">
                                                <input type="hidden" id="inProductoIdConteo" value="">
                                                <input type="hidden" id="inInventarioId" value="{{ $inventario->InventarioId ?? 0 }}">
                                                <input type="hidden" id="sucursalId" value="{{ $inventario->SucursalId ?? 0 }}">
                                                
                                                <ul class="list-unstyled">
                                                    {{-- Buscador --}}
                                                    <li class="mb-3">
                                                        <div class="form-group">
                                                            <label class="control-label fw-semibold" style="font-size:0.85rem;">Código:</label>
                                                            <div class="input-group">
                                                                <input type="text" maxlength="8" required class="form-control" 
                                                                    placeholder="Código de producto" id="inCodigoProductoConteo"
                                                                    style="border-radius:6px 0 0 6px;font-size:0.85rem;padding:0.45rem 0.75rem;">
                                                                <button type="button" id="btnCodigoProductoConteo"
                                                                        class="btn" style="background:#06b6d4;color:#fff;border-radius:0 6px 6px 0;padding:0.45rem 0.75rem;">
                                                                    <i class="bi bi-search"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>

                                                    {{-- Información del producto --}}
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-lg-7 col-md-7">
                                                                <div id="urlFotoProductoConteo" class="img-thumbnail text-center p-2"
                                                                    style="width:100%;height:150px;display:flex;align-items:center;justify-content:center;background:#f8fafc;">
                                                                    <img src="{{ asset('assets/img/adminlte/img/produc_default.jfif') }}" 
                                                                        alt="Sin imagen" 
                                                                        style="max-width:100%;max-height:100%;object-fit:contain;">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-5 col-md-5">
                                                                <div class="form-group">
                                                                    <label id="lblCodigoProductoConteo" class="control-label fw-semibold" style="font-size:0.85rem;">Código: <span class="text-muted fw-normal">---</span></label>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label id="lblDescripcionProductoConteo" class="control-label fw-semibold" style="font-size:0.85rem;">Descripción: <span class="text-muted fw-normal">---</span></label>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label id="lblReferenciaProductoConteo" class="control-label fw-semibold" style="font-size:0.85rem;">Ref.: <span class="text-muted fw-normal">---</span></label>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label id="lblExistenciaProductoConteo" class="control-label fw-semibold" style="font-size:0.85rem;">Existencia: <span class="text-muted fw-normal">---</span></label>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label id="lblCostoProductoConteo" class="control-label fw-semibold" style="font-size:0.85rem;">Costo: $<span class="text-muted fw-normal">---</span></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>

                                            {{-- Columna derecha: Formulario de conteo --}}
                                            <div class="col-md-7">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        {{-- Fila 1: Contado (más grande y destacado) --}}
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <label class="control-label fw-semibold text-success" style="font-size:0.9rem;">Contado:</label>
                                                                <input type="number" min="0" id="inCantidadContada"
                                                                    class="form-control font-bold"
                                                                    style="border-radius:6px;font-size:1.1rem;padding:0.5rem 0.75rem;text-align:center;font-weight:bold;"
                                                                    onkeypress="ContarProductoConteoEvent(event)">
                                                            </div>
                                                        </div>

                                                        {{-- Fila 2: Pie Solo + Pie Invertido (2 columnas) --}}
                                                        <div class="row g-2 mb-2">
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Pie solo:</label>
                                                                <input type="number" min="0" id="inCantidadPieSolo"
                                                                    class="form-control form-control-sm"
                                                                    style="border-radius:6px;font-size:0.85rem;padding:0.3rem 0.5rem;text-align:center;"
                                                                    onkeypress="ContarProductoConteoEvent(event)">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Pie invertido:</label>
                                                                <input type="number" min="0" id="inCantidadPieInvertido"
                                                                    class="form-control form-control-sm"
                                                                    style="border-radius:6px;font-size:0.85rem;padding:0.3rem 0.5rem;text-align:center;"
                                                                    onkeypress="ContarProductoConteoEvent(event)">
                                                            </div>
                                                        </div>

                                                        {{-- Fila 3: Pieza dañada + Caja vacía (2 columnas) --}}
                                                        <div class="row g-2 mb-2">
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Pieza dañada:</label>
                                                                <input type="number" min="0" id="inCantidadDanado"
                                                                    class="form-control form-control-sm"
                                                                    style="border-radius:6px;font-size:0.85rem;padding:0.3rem 0.5rem;text-align:center;"
                                                                    onkeypress="ContarProductoConteoEvent(event)">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Caja vacía:</label>
                                                                <input type="number" min="0" id="inCantidadCajaVacia"
                                                                    class="form-control form-control-sm"
                                                                    style="border-radius:6px;font-size:0.85rem;padding:0.3rem 0.5rem;text-align:center;"
                                                                    onkeypress="ContarProductoConteoEvent(event)">
                                                            </div>
                                                        </div>

                                                        {{-- Fila 4: Total + Diferencia (2 columnas) --}}
                                                        <div class="row g-2 mt-2">
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Total:</label>
                                                                <div class="form-control" style="border-radius:6px;font-size:1rem;padding:0.35rem 0.5rem;background:#f8fafc;font-weight:bold;text-align:center;">
                                                                    <span id="lblTotalDetalleConteo">0.00</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="control-label fw-semibold" style="font-size:0.78rem;">Diferencia:</label>
                                                                <div class="form-control" style="border-radius:6px;font-size:1rem;padding:0.35rem 0.5rem;background:#f8fafc;font-weight:bold;text-align:center;">
                                                                    <span id="lblDiferenciaConteo" style="color:#000;">0</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Botón Guardar --}}
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <button type="button" class="btn w-100" id="btnSaveProductoConteo"
                                                                        style="background:#14b8a6;color:#fff;border-radius:6px;padding:0.5rem 1.5rem;font-weight:bold;"
                                                                        onclick="ContarProductoConteo()">
                                                                    <i class="bi bi-save me-1"></i> GUARDAR
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ========================================== --}}
                                    {{-- TAB 2: SIN CONTAR (TABLA COMPLETA CON INPUTS Y BUSCADOR) --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade" id="tabSinContar" role="tabpanel">
                                        {{-- Buscador --}}
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-text" style="background:#f8fafc;border:1px solid #d1d5db;">
                                                        <i class="bi bi-search"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="buscadorSinContar" 
                                                        placeholder="Buscar por código..."
                                                        style="font-size:0.85rem;padding:0.45rem 0.75rem;border:1px solid #d1d5db;">
                                                </div>
                                            </div>
                                            <div class="col-md-8 text-end">
                                                <span class="badge bg-secondary" id="totalProductosSinContar">{{ count($detalles) }}</span>
                                                <span class="text-muted" style="font-size:0.85rem;">productos</span>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover" id="tablaSinContar">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th>Costo</th>
                                                        <th>Existencia</th>
                                                        <th style="min-width:80px;">Contado</th>
                                                        <th style="min-width:70px;">Pie Solo</th>
                                                        <th style="min-width:70px;">Pie Inv.</th>
                                                        <th style="min-width:70px;">Dañado</th>
                                                        <th style="min-width:80px;">Diferencia</th>
                                                        <th style="min-width:80px;">Total</th>
                                                        <th style="min-width:80px;">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($detalles as $index => $detalle)
                                                    @php
                                                        $imgSrc = FileHelper::getOrDownloadFile(
                                                            'images/items/thumbs/',
                                                            $detalle->UrlFoto ?? '',
                                                            'assets/img/adminlte/img/produc_default.jfif'
                                                        );
                                                        $imgFull = FileHelper::getOrDownloadFile(
                                                            'images/items/',
                                                            $detalle->UrlFoto ?? '',
                                                            'assets/img/adminlte/img/produc_default.jfif'
                                                        );
                                                        $diferencia = ($detalle->CantidadContada ?? 0) - ($detalle->Existencia ?? 0);
                                                        $color = $diferencia < 0 ? '#ef4444' : ($diferencia > 0 ? '#22c55e' : '#000');
                                                        $texto = $diferencia < 0 ? 'Falta ' . abs($diferencia) : ($diferencia > 0 ? 'Sobra ' . $diferencia : 'Exacto');
                                                    @endphp
                                                    <tr data-codigo="{{ strtoupper($detalle->Codigo ?? '') }}">
                                                        <td class="text-center">
                                                            <img src="{{ $imgSrc }}" 
                                                                loading="lazy" 
                                                                alt="{{ $detalle->Codigo }}"
                                                                class="img-thumbnail img-zoomable"
                                                                style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                                                data-full-image="{{ $imgFull }}"
                                                                data-description="{{ $detalle->Codigo }} - {{ $detalle->Referencia }}"
                                                                onclick="zoomImagen(this)"
                                                                onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                        </td>
                                                        <td><strong>{{ $detalle->Codigo ?? 'N/A' }}</strong></td>
                                                        <td>{{ $detalle->Referencia ?? 'N/A' }}</td>
                                                        <td class="text-end">{{ number_format($detalle->CostoDivisa ?? 0, 2) }}</td>
                                                        <td class="text-center">{{ $detalle->Existencia }}</td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                id="_inputCantContada{{ $detalle->ProductoId }}"
                                                                style="width:75px;display:inline-block;font-size:0.8rem;text-align:center;"
                                                                value="{{ $detalle->CantidadContada ?? 0 }}" min="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                id="_inputCantPieSolo{{ $detalle->ProductoId }}"
                                                                style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                                                value="{{ $detalle->CantidadPieSolo ?? 0 }}" min="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                id="_inputCantPieInvertido{{ $detalle->ProductoId }}"
                                                                style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                                                value="{{ $detalle->CantidadPieInvertido ?? 0 }}" min="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                id="_inputCantDanado{{ $detalle->ProductoId }}"
                                                                style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                                                value="{{ $detalle->CantidadPiezaDanada ?? 0 }}" min="0">
                                                        </td>
                                                        <td class="text-center" id="_lblDiferencia{{ $detalle->ProductoId }}">
                                                            <span style="color: {{ $color }}; font-weight:bold;">
                                                                {{ $texto }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center" id="_lblTotalDetalle{{ $detalle->ProductoId }}">
                                                            <span class="badge bg-info text-dark">
                                                                {{ number_format(($detalle->CantidadContada ?? 0) - ($detalle->CantidadPieSolo ?? 0) - ($detalle->CantidadPieInvertido ?? 0) - ($detalle->CantidadPiezaDanada ?? 0), 2) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button" class="btn btn-sm btn-success" 
                                                                        id="_btnAdd{{ $detalle->ProductoId }}"
                                                                        style="width:28px;height:28px;padding:0;border-radius:4px;font-size:0.7rem;"
                                                                        onclick="ContarProductoSeleccionado({{ $detalle->ProductoId }}, {{ $detalle->InventarioDetalleId }})">
                                                                    <i class="bi bi-check"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="12" class="text-center text-muted py-4">
                                                            <i class="bi bi-inbox me-2" style="font-size:1.2rem;"></i>
                                                            No hay productos para contar
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ========================================== --}}
                                    {{-- TAB 3: CON DIFERENCIAS --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade" id="tabDiferencias" role="tabpanel">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-sm btn-orange" onclick="cargarDiferencias()">
                                                <i class="bi bi-arrow-repeat me-1"></i> Ver productos
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaDiferencias">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th>Costo</th>
                                                        <th>Existencia</th>
                                                        <th>Contado</th>
                                                        <th>Pie Solo</th>
                                                        <th>Pie Inv.</th>
                                                        <th>Dañado</th>
                                                        <th>Total</th>
                                                        <th>Diferencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="11" class="text-center text-muted py-3">
                                                            <i class="bi bi-info-circle me-1"></i> Presione "Ver productos" para cargar
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ========================================== --}}
                                    {{-- TAB 4: EXACTOS --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade" id="tabExactos" role="tabpanel">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-sm btn-indigo" onclick="cargarExactos()">
                                                <i class="bi bi-arrow-repeat me-1"></i> Ver productos
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaExactos">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th>Costo</th>
                                                        <th>Existencia</th>
                                                        <th>Contado</th>
                                                        <th>Pie Solo</th>
                                                        <th>Pie Inv.</th>
                                                        <th>Dañado</th>
                                                        <th>Total</th>
                                                        <th>Diferencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="11" class="text-center text-muted py-3">
                                                            <i class="bi bi-info-circle me-1"></i> Presione "Ver productos" para cargar
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ========================================== --}}
                                    {{-- TAB 5: NO VENDIBLES --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade" id="tabNoVendibles" role="tabpanel">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-sm btn-danger" onclick="cargarNoVendibles()">
                                                <i class="bi bi-arrow-repeat me-1"></i> Ver productos
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaNoVendibles">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th>Costo</th>
                                                        <th>Existencia</th>
                                                        <th>Contado</th>
                                                        <th>Pie Solo</th>
                                                        <th>Pie Inv.</th>
                                                        <th>Dañado</th>
                                                        <th>Total</th>
                                                        <th>Diferencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="11" class="text-center text-muted py-3">
                                                            <i class="bi bi-info-circle me-1"></i> Presione "Ver productos" para cargar
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ========================================== --}}
                                    {{-- TAB 6: COMPARACIÓN --}}
                                    {{-- ========================================== --}}
                                    <div class="tab-pane fade" id="tabComparacion" role="tabpanel">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-sm btn-purple" onclick="cargarComparacion()">
                                                <i class="bi bi-arrow-repeat me-1"></i> Ver productos
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaComparacion">
                                                <thead style="background:#f8fafc;">
                                                    <tr>
                                                        <th style="width:60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Referencia</th>
                                                        <th colspan="2" class="text-center" style="background:#e5e7eb;">Inventario Actual</th>
                                                        <th colspan="2" class="text-center" style="background:#e5e7eb;">Inventario Anterior</th>
                                                        <th class="text-center">Diferencia</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width:60px;"></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th class="text-center" style="background:#f3f4f6;font-weight:400;">Existencia</th>
                                                        <th class="text-center" style="background:#f3f4f6;font-weight:400;">Contado</th>
                                                        <th class="text-center" style="background:#f3f4f6;font-weight:400;">Existencia</th>
                                                        <th class="text-center" style="background:#f3f4f6;font-weight:400;">Contado</th>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-3">
                                                            <i class="bi bi-info-circle me-1"></i> Presione "Ver productos" para cargar
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- Totales --}}
                        <div id="divTotalesRecepcion" class="mt-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Total Productos</small>
                                            <h5 class="mb-0">{{ $totalProductos ?? 0 }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Contados</small>
                                            <h5 class="mb-0 text-success">{{ $contados ?? 0 }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Pendientes</small>
                                            <h5 class="mb-0 text-warning">{{ $pendientes ?? 0 }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body py-2">
                                            <small class="text-muted d-block">Avance</small>
                                            <h5 class="mb-0 text-primary">{{ $porcentaje ?? 0 }}%</h5>
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
</div>

{{-- Modal: Cargar Conteo Excel --}}
<div class="modal fade" id="modalCargarConteoExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#f59e0b;color:#fff;">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar conteo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <p class="text-muted">Seleccione el archivo Excel con los datos del conteo</p>
                        <form id="formUploadConteo" enctype="multipart/form-data">
                            <input type="hidden" name="InventarioId" value="{{ $inventario->InventarioId }}">
                            <div class="row">
                                <div class="col-md-9">
                                    <input type="file" name="conteoProductosExcelFile" class="form-control" id="conteoProductosExcelFile">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary" id="btnUploadConteoInventario" disabled>
                                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Cargar conteo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> CERRAR
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// Definir la URL base de las imágenes para usar en JavaScript
var IMAGE_BASE_URL = "{{ asset('images/items/thumbs/') }}";
var IMAGE_DEFAULT = "{{ asset('assets/img/adminlte/img/produc_default.jfif') }}";

// ============================================
// CONFIRMAR FINALIZAR CONTEO
// ============================================
function confirmarFinalizar(event, inventarioId) {
    event.preventDefault();
    
    if (!inventarioId || inventarioId === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el inventario',
            confirmButtonColor: '#dc2626'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Finalizar inventario?',
        text: 'Esta acción cambiará el estatus a "Cerrado" y no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("cpanel.inventario.finalizar-conteo", "") }}/' + inventarioId;
        }
    });
}

// ============================================
// ZOOM DE IMAGEN - Función GLOBAL
// ============================================
function zoomImagen(element) {
    const imgSrc = element.getAttribute('data-full-image') || element.src;
    const descripcion = element.getAttribute('data-description') || 'Producto';
    
    Swal.fire({
        title: descripcion,
        imageUrl: imgSrc,
        imageWidth: 400,
        imageHeight: 400,
        imageAlt: descripcion,
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            image: 'rounded-3 shadow-lg'
        }
    });
}

// ============================================
// FUNCIONES PARA CARGAR TABS (GLOBALES)
// ============================================
function cargarSinContar() {
    cargarTablaConteo('SinContar', 'tablaSinContar');
}

function cargarDiferencias() {
    cargarTablaConteo('Diferencias', 'tablaDiferencias');
}

function cargarExactos() {
    cargarTablaConteo('Exactos', 'tablaExactos');
}

function cargarNoVendibles() {
    cargarTablaConteo('NoVendible', 'tablaNoVendibles');
}

function cargarComparacion() {
    cargarTablaConteo('Comparacion', 'tablaComparacion');
}

// ============================================
// FUNCIÓN PRINCIPAL PARA CARGAR TABLAS (GLOBAL)
// ============================================
function cargarTablaConteo(tipo, nombreTabla) {
    var inventarioId = {{ $inventario->InventarioId ?? 0 }};
    
    if (!inventarioId) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin inventario',
            text: 'No se ha seleccionado un inventario',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    Swal.fire({
        title: 'Cargando productos...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var data = {
        TipoProductos: tipo,
        InventarioId: inventarioId
    };

    var URL = "{{ route('cpanel.inventario.buscar-detalle-json') }}";

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(resultado => {
        Swal.close();
        
        var table = document.getElementById(nombreTabla);
        if (!table) return;
        
        var tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (!resultado || resultado.length === 0) {
            var colspan = 0;
            var thead = table.querySelector('thead');
            if (thead) {
                var headers = thead.querySelectorAll('th');
                colspan = headers.length;
            }
            tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-muted py-3">No hay productos para mostrar</td></tr>';
            return;
        }
        
        resultado.forEach(obj => {
            var row = document.createElement('tr');
            var producto = obj.producto || {};
            var imgDefault = "{{ asset('assets/img/adminlte/img/produc_default.jfif') }}";
            
            var imgSrc = producto.thumbUrl || imgDefault;
            var imgFull = producto.fullUrl || imgDefault;
            
            var columns = [];
            columns.push(''); // Imagen
            
            // ============================================
            // LÓGICA PARA COMPARACION
            // ============================================
            if (tipo === 'Comparacion') {
                columns.push(producto.codigo || 'N/A');
                columns.push(producto.referencia || 'N/A');
                columns.push(obj.inventarioActual ? obj.inventarioActual.Existencia : 'N/A');
                columns.push(obj.inventarioActual ? obj.inventarioActual.CantidadContada : 'N/A');
                columns.push(obj.inventarioAnterior ? obj.inventarioAnterior.Existencia : 'N/A');
                columns.push(obj.inventarioAnterior ? obj.inventarioAnterior.CantidadContada : 'N/A');
                
                var diffActual = obj.diferenciaActual || 0;
                var diffColor = diffActual < 0 ? '#ef4444' : (diffActual > 0 ? '#22c55e' : '#000');
                var diffTexto = diffActual < 0 ? diffActual.toString() : (diffActual > 0 ? '+' + diffActual : '0');
                columns.push('<span style="color:' + diffColor + ';font-weight:bold;">' + diffTexto + '</span>');
                
            } else {
                // ============================================
                // LÓGICA PARA DIFERENCIAS, EXACTOS, NO VENDIBLES
                // ============================================
                columns.push(producto.codigo || 'N/A');
                columns.push(producto.referencia || 'N/A');
                columns.push((producto.costoDivisa || 0).toFixed(2));
                columns.push(obj.existencia || 0);
                columns.push(obj.cantidadContada || 0);
                columns.push(obj.cantidadPieSolo || 0);
                columns.push(obj.cantidadPieInvertido || 0);
                columns.push(obj.cantidadPiezaDanada || 0);
                
                var totalUnidades = (obj.cantidadContada || 0) - (obj.cantidadPieSolo || 0) - (obj.cantidadPieInvertido || 0) - (obj.cantidadPiezaDanada || 0);
                var totalCosto = totalUnidades * (producto.costoDivisa || 0);
                columns.push(totalCosto.toFixed(2));
                
                var diferencia = (obj.cantidadContada || 0) - (obj.existencia || 0);
                var color = diferencia < 0 ? '#ef4444' : (diferencia > 0 ? '#22c55e' : '#000');
                var texto = diferencia < 0 ? diferencia.toString() : (diferencia > 0 ? '+' + diferencia : '0');
                columns.push('<span style="color:' + color + ';font-weight:bold;">' + texto + '</span>');
            }
            
            // ============================================
            // RENDERIZAR
            // ============================================
            columns.forEach((col, index) => {
                var td = document.createElement('td');
                
                if (index === 0) {
                    var img = document.createElement('img');
                    img.src = imgSrc;
                    img.alt = producto.codigo || '';
                    img.loading = 'lazy';
                    img.className = 'img-thumbnail img-zoomable';
                    img.style.cssText = 'width:40px;height:40px;object-fit:cover;cursor:pointer;display:block;';
                    img.dataset.fullImage = imgFull;
                    img.dataset.description = (producto.codigo || '') + ' - ' + (producto.referencia || '');
                    img.onclick = function() { zoomImagen(this); };
                    img.onerror = function() { this.src = imgDefault; };
                    td.appendChild(img);
                } else if (typeof col === 'string' && col.includes('<span')) {
                    td.innerHTML = col;
                } else {
                    td.textContent = col;
                }
                row.appendChild(td);
            });
            
            tbody.appendChild(row);
        });
    })
    .catch(error => {
        Swal.close();
        console.error('❌ Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los productos',
            confirmButtonColor: '#dc2626'
        });
    });
}
// ============================================
// GUARDAR CONTEO MANUAL (GLOBAL)
// ============================================
window.ContarProductoConteo = function() {
    var productoId = document.getElementById('inProductoIdConteo').value;
    var inventarioId = document.getElementById('inInventarioId').value;
    var sucursalId = document.getElementById('sucursalId').value;
    
    var cantContada = parseInt(document.getElementById('inCantidadContada').value) || 0;
    var cantPieSolo = parseInt(document.getElementById('inCantidadPieSolo').value) || 0;
    var cantPieInvertido = parseInt(document.getElementById('inCantidadPieInvertido').value) || 0;
    var cantDanado = parseInt(document.getElementById('inCantidadDanado').value) || 0;
    var cantCajaVacia = parseInt(document.getElementById('inCantidadCajaVacia').value) || 0;

    var costoSpan = document.getElementById('lblCostoProductoConteo');
    var costoTexo = costoSpan ? costoSpan.textContent : '0';
    var costoMatch = costoTexo.match(/[\d.]+/);
    var costoDivisa = costoMatch ? parseFloat(costoMatch[0]) : 0;
    
    var existenciaText = document.getElementById('lblExistenciaProductoConteo').innerHTML;
    var existenciaMatch = existenciaText.match(/\d+/);
    var existencia = existenciaMatch ? parseInt(existenciaMatch[0]) : 0;

    if (!productoId) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'Primero busque un producto',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    if (cantContada <= 0 && cantPieSolo <= 0 && cantPieInvertido <= 0 && cantDanado <= 0 && cantCajaVacia <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'Ingrese al menos una cantidad para contar',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    var costoTotal = (cantContada - cantPieSolo - cantPieInvertido - cantDanado) * costoDivisa;
    costoTotal = costoTotal.toFixed(2);
    var diferencia = cantContada - existencia;

    Swal.fire({
        title: 'Guardando conteo...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var data = {
        ProductoId: productoId,
        InventarioId: inventarioId,
        SucursalId: sucursalId,
        CantidadContada: cantContada,
        CantidadPieSolo: cantPieSolo,
        CantidadPieInvertido: cantPieInvertido,
        CantidadDanado: cantDanado,
        CantidadCajaVacia: cantCajaVacia
    };

    var URL = "{{ route('cpanel.inventario.guardar-conteo-manual') }}";

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(resultado => {
        Swal.close();
        
        if (resultado.success) {
            document.getElementById('lblTotalDetalleConteo').textContent = costoTotal;
            
            var lblDiferencia = document.getElementById('lblDiferenciaConteo');
            if (diferencia < 0) {
                lblDiferencia.style.color = '#ef4444';
                lblDiferencia.textContent = 'Falta ' + Math.abs(diferencia);
            } else if (diferencia > 0) {
                lblDiferencia.style.color = '#22c55e';
                lblDiferencia.textContent = 'Sobra ' + diferencia;
            } else {
                lblDiferencia.style.color = '#000';
                lblDiferencia.textContent = 'Exacto';
            }
            
            Swal.fire({
                icon: 'success',
                title: '¡Conteo registrado!',
                text: resultado.message || 'El conteo se ha registrado correctamente',
                timer: 1500,
                showConfirmButton: false
            });
            
            document.getElementById('inCodigoProductoConteo').value = '';
            document.getElementById('inProductoIdConteo').value = '';
            document.getElementById('lblCodigoProductoConteo').innerHTML = 'Código: <span class="text-muted fw-normal">---</span>';
            document.getElementById('lblDescripcionProductoConteo').innerHTML = 'Descripción: <span class="text-muted fw-normal">---</span>';
            document.getElementById('lblReferenciaProductoConteo').innerHTML = 'Ref.: <span class="text-muted fw-normal">---</span>';
            document.getElementById('lblExistenciaProductoConteo').innerHTML = 'Existencia: <span class="text-muted fw-normal">---</span>';
            document.getElementById('lblCostoProductoConteo').innerHTML = 'Costo: $<span class="text-muted fw-normal">---</span>';
            document.getElementById('inCantidadContada').value = 0;
            document.getElementById('inCantidadPieSolo').value = 0;
            document.getElementById('inCantidadPieInvertido').value = 0;
            document.getElementById('inCantidadDanado').value = 0;
            document.getElementById('inCantidadCajaVacia').value = 0;
            document.getElementById('lblTotalDetalleConteo').textContent = '0.00';
            document.getElementById('lblDiferenciaConteo').textContent = '0';
            document.getElementById('lblDiferenciaConteo').style.color = '#000';
            
            document.getElementById('inCodigoProductoConteo').focus();
            
            setTimeout(() => {
                location.reload();
            }, 500);
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message || 'No se pudo guardar el conteo',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar el conteo',
            confirmButtonColor: '#dc2626'
        });
    });
};

// ============================================
// CONTAR PRODUCTO DESDE LA TABLA (SIN CONTAR)
// ============================================
window.ContarProductoSeleccionado = function(productoId, detalleId) {
    var cantContada = document.getElementById('_inputCantContada' + productoId).value || 0;
    var cantPieSolo = document.getElementById('_inputCantPieSolo' + productoId).value || 0;
    var cantPieInvertido = document.getElementById('_inputCantPieInvertido' + productoId).value || 0;
    var cantDanado = document.getElementById('_inputCantDanado' + productoId).value || 0;
    var inventarioId = document.getElementById('inInventarioId').value;
    var sucursalId = document.getElementById('sucursalId').value;

    if (parseInt(cantContada) <= 0 && parseInt(cantPieSolo) <= 0 && 
        parseInt(cantPieInvertido) <= 0 && parseInt(cantDanado) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'Ingrese al menos una cantidad para contar',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    Swal.fire({
        title: 'Guardando conteo...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var data = {
        ProductoId: parseInt(productoId),
        InventarioId: parseInt(inventarioId) || 0,
        SucursalId: parseInt(sucursalId) || 0,
        CantidadContada: parseInt(cantContada) || 0,
        CantidadPieSolo: parseInt(cantPieSolo) || 0,
        CantidadPieInvertido: parseInt(cantPieInvertido) || 0,
        CantidadDanado: parseInt(cantDanado) || 0,
        CantidadCajaVacia: 0
    };

    var URL = "{{ route('cpanel.inventario.guardar-conteo-manual') }}";

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(resultado => {
        Swal.close();
        
        if (resultado.success) {
            var existenciaEl = document.getElementById('_lblExistencia' + productoId);
            var existencia = existenciaEl ? parseInt(existenciaEl.textContent) || 0 : 0;
            var diferencia = parseInt(cantContada) - existencia;
            
            var lblDiferencia = document.getElementById('_lblDiferencia' + productoId);
            if (lblDiferencia) {
                if (diferencia < 0) {
                    lblDiferencia.innerHTML = '<span style="color:#ef4444;font-weight:bold;">Falta ' + Math.abs(diferencia) + '</span>';
                } else if (diferencia > 0) {
                    lblDiferencia.innerHTML = '<span style="color:#22c55e;font-weight:bold;">Sobra ' + diferencia + '</span>';
                } else {
                    lblDiferencia.innerHTML = '<span style="color:#000;font-weight:bold;">Exacto</span>';
                }
            }

            var btnRem = document.getElementById('_btnRem' + productoId);
            if (btnRem) btnRem.disabled = false;

            var lblTotal = document.getElementById('_lblTotalDetalle' + productoId);
            if (lblTotal) {
                var total = parseInt(cantContada) - parseInt(cantPieSolo) - parseInt(cantPieInvertido) - parseInt(cantDanado);
                lblTotal.textContent = total.toFixed(2);
            }

            Swal.fire({
                icon: 'success',
                title: '¡Conteo registrado!',
                text: 'El conteo del producto se ha registrado correctamente',
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 500);

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message || 'No se pudo guardar el conteo',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar el conteo: ' + error.message,
            confirmButtonColor: '#dc2626'
        });
    });
};

// ============================================
// REMOVER CONTEO (GLOBAL)
// ============================================
window.RemoverConteo = function(buttonId, productoId, detalleId) {
    var inventarioId = document.getElementById('inInventarioId').value;
    var sucursalId = document.getElementById('sucursalId').value;

    if (!inventarioId) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'No se encontró el inventario',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    Swal.fire({
        title: 'Removiendo conteo...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var data = {
        ProductoId: productoId,
        InventarioId: parseInt(inventarioId) || 0,
        SucursalId: parseInt(sucursalId) || 0,
        CantidadContada: 0,
        CantidadPieSolo: 0,
        CantidadPieInvertido: 0,
        CantidadDanado: 0,
        CantidadCajaVacia: 0
    };

    var URL = "{{ route('cpanel.inventario.guardar-conteo-manual') }}";

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(resultado => {
        Swal.close();
        
        if (resultado.success) {
            ['_inputCantContada', '_inputCantPieSolo', '_inputCantPieInvertido', '_inputCantDanado'].forEach(function(prefix) {
                var input = document.getElementById(prefix + productoId);
                if (input) input.value = 0;
            });

            var lblDiferencia = document.getElementById('_lblDiferencia' + productoId);
            if (lblDiferencia) {
                lblDiferencia.innerHTML = '<span style="color:#000;font-weight:bold;">0</span>';
            }

            var btnRem = document.getElementById(buttonId);
            if (btnRem) btnRem.disabled = true;

            Swal.fire({
                icon: 'info',
                title: 'Conteo removido',
                text: 'El conteo del producto ha sido removido',
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 500);

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message || 'No se pudo remover el conteo',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al remover el conteo',
            confirmButtonColor: '#dc2626'
        });
    });
};

// ============================================
// EVENTOS AL CARGAR EL DOM
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Habilitar botón de upload
    var fileInput = document.getElementById('conteoProductosExcelFile');
    var btnUpload = document.getElementById('btnUploadConteoInventario');
    var form = document.getElementById('formUploadConteo');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            btnUpload.disabled = (this.value == '');
        });
    }

    // Enviar formulario vía AJAX
    if (btnUpload) {
        btnUpload.addEventListener('click', function() {
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Aviso',
                    text: 'Seleccione un archivo Excel primero',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            
            var formData = new FormData(form);
            
            Swal.fire({
                title: 'Cargando archivo...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            fetch('{{ route("cpanel.inventario.upload-conteo-excel") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(resultado => {
                Swal.close();
                
                if (resultado.success) {
                    var modal = document.getElementById('modalCargarConteoExcel');
                    var modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Archivo cargado!',
                        text: resultado.message,
                        confirmButtonColor: '#22c55e',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        location.reload();
                    });
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resultado.message || 'Error al cargar el archivo',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar el archivo',
                    confirmButtonColor: '#dc2626'
                });
            });
        });
    }

    // ============================================
    // BUSCAR PRODUCTO POR CÓDIGO
    // ============================================
    document.getElementById('btnCodigoProductoConteo').addEventListener('click', function() {
        buscarProductoConteo();
    });

    document.getElementById('inCodigoProductoConteo').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarProductoConteo();
        }
    });

    function buscarProductoConteo() {
        var codigo = document.getElementById('inCodigoProductoConteo').value.trim();
        var inventarioId = document.getElementById('inInventarioId').value;
        var sucursalId = document.getElementById('sucursalId').value;

        if (!codigo) {
            Swal.fire({
                icon: 'warning',
                title: 'Aviso',
                text: 'Ingrese un código de producto',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        Swal.fire({
            title: 'Buscando producto...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var data = {
            Codigo: codigo,
            InventarioId: inventarioId,
            SucursalId: sucursalId
        };

        var URL = "{{ route('cpanel.inventario.buscar-producto-conteo') }}";

        fetch(URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(resultado => {
            Swal.close();
            
            if (resultado.success && resultado.producto) {
                var p = resultado.producto;
                var detalle = resultado.detalle || {};
                
                document.getElementById('inProductoIdConteo').value = p.ID;
                document.getElementById('lblCodigoProductoConteo').innerHTML = 'Código: <span class="text-muted fw-normal">' + (p.Codigo || '---') + '</span>';
                document.getElementById('lblDescripcionProductoConteo').innerHTML = 'Descripción: <span class="text-muted fw-normal">' + (p.Descripcion || '---') + '</span>';
                document.getElementById('lblReferenciaProductoConteo').innerHTML = 'Ref.: <span class="text-muted fw-normal">' + (p.Referencia || '---') + '</span>';
                document.getElementById('lblExistenciaProductoConteo').innerHTML = 'Existencia: <span class="text-muted fw-normal">' + (detalle.Existencia || 0) + '</span>';
                
                var costo = parseFloat(p.CostoDivisa) || 0;
                document.getElementById('lblCostoProductoConteo').innerHTML = 'Costo: $<span class="text-muted fw-normal">' + costo.toFixed(2) + '</span>';
                
                var img = document.querySelector('#urlFotoProductoConteo img');
                if (p.thumbUrl) {
                    img.src = p.thumbUrl;
                } else {
                    img.src = "{{ asset('assets/img/adminlte/img/produc_default.jfif') }}";
                }
                img.dataset.fullImage = p.fullUrl || img.src;
                
                document.getElementById('inCantidadContada').value = detalle.CantidadContada || 0;
                document.getElementById('inCantidadPieSolo').value = detalle.CantidadPieSolo || 0;
                document.getElementById('inCantidadPieInvertido').value = detalle.CantidadPieInvertido || 0;
                document.getElementById('inCantidadDanado').value = detalle.CantidadPiezaDanada || 0;
                document.getElementById('inCantidadCajaVacia').value = detalle.CantidadCajaVacia || 0;
                
                calcularTotales();
                
                document.getElementById('inCantidadContada').focus();
                
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Producto no encontrado',
                    text: resultado.message || 'No se encontró el producto con el código ingresado',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al buscar el producto',
                confirmButtonColor: '#dc2626'
            });
        });
    }

    // ============================================
    // CALCULAR TOTAL Y DIFERENCIA EN TIEMPO REAL
    // ============================================
    function calcularTotales() {
        var cantContada = parseFloat(document.getElementById('inCantidadContada').value) || 0;
        var cantPieSolo = parseFloat(document.getElementById('inCantidadPieSolo').value) || 0;
        var cantPieInvertido = parseFloat(document.getElementById('inCantidadPieInvertido').value) || 0;
        var cantDanado = parseFloat(document.getElementById('inCantidadDanado').value) || 0;
        
        var total = cantContada - cantPieSolo - cantPieInvertido - cantDanado;
        document.getElementById('lblTotalDetalleConteo').textContent = total.toFixed(2);
        
        var existenciaText = document.getElementById('lblExistenciaProductoConteo').innerHTML;
        var existenciaMatch = existenciaText.match(/\d+/);
        var existencia = existenciaMatch ? parseInt(existenciaMatch[0]) : 0;
        
        var diferencia = cantContada - existencia;
        var lblDiferencia = document.getElementById('lblDiferenciaConteo');
        
        if (diferencia < 0) {
            lblDiferencia.style.color = '#ef4444';
            lblDiferencia.textContent = 'Falta ' + Math.abs(diferencia);
        } else if (diferencia > 0) {
            lblDiferencia.style.color = '#22c55e';
            lblDiferencia.textContent = 'Sobra ' + diferencia;
        } else {
            lblDiferencia.style.color = '#000';
            lblDiferencia.textContent = 'Exacto';
        }
    }

    document.querySelectorAll('#inCantidadContada, #inCantidadPieSolo, #inCantidadPieInvertido, #inCantidadDanado').forEach(function(input) {
        input.addEventListener('input', calcularTotales);
        input.addEventListener('keyup', calcularTotales);
    });

    // ============================================
    // BUSCADOR EN TAB "SIN CONTAR"
    // ============================================
    var buscador = document.getElementById('buscadorSinContar');
    var tabla = document.getElementById('tablaSinContar');
    
    if (buscador && tabla) {
        buscador.addEventListener('keyup', function() {
            var filter = this.value.toUpperCase().trim();
            var rows = tabla.querySelectorAll('tbody tr');
            var visibles = 0;
            
            rows.forEach(function(row) {
                var codigo = (row.getAttribute('data-codigo') || '').toUpperCase();
                
                if (filter === '' || codigo.includes(filter)) {
                    row.style.display = '';
                    visibles++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            var totalBadge = document.getElementById('totalProductosSinContar');
            if (totalBadge) {
                totalBadge.textContent = visibles;
            }
        });
    }
});
</script>
@endsection

@push('styles')
<style>
    .bg-indigo { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .btn { transition: all 0.15s ease; }
    .btn:hover { transform: scale(1.02); opacity: 0.9; }
    .table-bordered { border: 1px solid #e2e8f0; }
    .table-bordered th, .table-bordered td { border: 1px solid #e2e8f0; }
    .table-striped tbody tr:nth-of-type(odd) { background-color: #f8fafc; }
    .table-hover tbody tr:hover { background-color: #f1f5f9; }
    .card { border-radius: 8px; }
    input[type="number"] { text-align: center; }
    input[type="number"]:focus { border-color: #f59e0b; box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25); }
    
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .img-zoomable:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
        position: relative;
    }
    
    .btn-teal { background: #14b8a6; color: #fff; }
    .btn-teal:hover { background: #0d9488; color: #fff; }
    
    .btn-orange { background: #f59e0b; color: #fff; }
    .btn-orange:hover { background: #d97706; color: #fff; }
    
    .btn-indigo { background: #6366f1; color: #fff; }
    .btn-indigo:hover { background: #4f46e5; color: #fff; }
    
    .btn-purple { background: #8b5cf6; color: #fff; }
    .btn-purple:hover { background: #7c3aed; color: #fff; }
    
    .img-thumbnail {
        padding: 4px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
    }
</style>
@endpush