{{-- resources/views/cpanel/distribucion/transferencia_crear.blade.php --}}

@extends('layout.layout_dashboard')

@section('title', 'Crear transferencia')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

@php
    $isNueva = ($transferenciaDTO->TransferenciaId ?? 0) == 0;
    $tituloBoton = $isNueva ? 'Iniciar Transferencia' : 'Agregar Productos';
    $idTransferencia = $transferenciaDTO->TransferenciaId ?? null;
    $mostrarProductos = $mostrarProductos ?? false;
    $mostrarTotales = $mostrarProductos && $idTransferencia;
    $sucursalDestinoNombre = $sucursalDestinoNombre ?? '';
    
    // Determinar qué panel está activo
    $panelActivo = $isNueva ? 'sucursales' : 'productos';
    if ($mostrarTotales) {
        $panelActivo = 'totales';
    }
@endphp

{{-- Mensajes de la plantilla --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="bi bi-arrow-left-right text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Transferencia de Mercancía</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $isNueva ? 'Crea una nueva transferencia entre sucursales' : 'Editando transferencia N° ' . ($transferenciaDTO->Numero ?? '') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.distribucion.transferencia') }}">Transferencias</a></li>
                    <li class="breadcrumb-item active">{{ $isNueva ? 'Crear' : 'Editar' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-arrow-left-right me-2"></i>TRANSFERENCIA DE MERCANCÍA
                        <small class="ms-2" style="color:rgba(255,255,255,0.8);font-size:0.72rem;font-weight:400;">
                            {{ $isNueva ? 'Nueva transferencia' : 'Editando transferencia N° ' . ($transferenciaDTO->Numero ?? '') }}
                        </small>
                    </h6>
                    <a href="{{ route('cpanel.distribucion.transferencia') }}" 
                       class="btn btn-light btn-sm fw-semibold"
                       style="font-size:0.78rem;background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-list-ul me-1"></i>LISTADO
                    </a>
                </div>
            </div>
            <div class="card-body">

                {{-- ========================================== --}}
                {{-- ACORDEÓN DE SECCIONES --}}
                {{-- ========================================== --}}
                <div class="accordion" id="accordionTransferencia">

                    {{-- ========================================== --}}
                    {{-- SECCIÓN 1: SUCURSALES --}}
                    {{-- ========================================== --}}
                    <div class="accordion-item border mb-2">
                        <h2 class="accordion-header" id="headingSucursales">
                            <button class="accordion-button {{ $panelActivo != 'sucursales' ? 'collapsed' : '' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseSucursales" 
                                    aria-expanded="{{ $panelActivo == 'sucursales' ? 'true' : 'false' }}"
                                    aria-controls="collapseSucursales"
                                    style="background:#ff9800;color:white;font-size:0.85rem;font-weight:600;">
                                <i class="bi bi-building me-2"></i>
                                <span>SUCURSALES</span>
                                <small style="color:rgba(255,255,255,0.8);font-weight:400;font-size:0.72rem;margin-left:10px;">
                                    Indique las sucursales origen y destino de la transferencia
                                </small>
                            </button>
                        </h2>
                        <div id="collapseSucursales" 
                             class="accordion-collapse collapse {{ $panelActivo == 'sucursales' ? 'show' : '' }}"
                             aria-labelledby="headingSucursales"
                             data-bs-parent="#accordionTransferencia">
                            <div class="accordion-body">
                                <form id="formTransferencia" method="POST" 
                                      action="{{ route('cpanel.distribucion.transferencia-iniciar') }}">
                                    @csrf
                                    @if($idTransferencia)
                                        <input type="hidden" name="transferencia_id" value="{{ $idTransferencia }}">
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            {{-- Sucursal Origen --}}
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">SUC. ORIGEN:</label>
                                                <div class="col-md-9">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-store"></i></span>
                                                        <select name="sucursal_origen" id="sucursalOrigen" class="form-select" 
                                                                {{ $idTransferencia ? 'disabled' : '' }} required>
                                                            <option value="">Seleccione un valor</option>
                                                            @foreach($sucursales as $sucursal)
                                                                <option value="{{ $sucursal->ID }}"
                                                                    {{ ($transferenciaDTO->SucursalOrigenId ?? '') == $sucursal->ID ? 'selected' : '' }}>
                                                                    {{ $sucursal->Nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($idTransferencia)
                                                            <input type="hidden" name="sucursal_origen" value="{{ $transferenciaDTO->SucursalOrigenId }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Sucursal Destino --}}
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">SUC. DESTINO:</label>
                                                <div class="col-md-9">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-store"></i></span>
                                                        <select name="sucursal_destino" id="sucursalDestino" class="form-select"
                                                                {{ $idTransferencia ? 'disabled' : '' }} required>
                                                            <option value="">Seleccione un valor</option>
                                                            @foreach($sucursales as $sucursal)
                                                                <option value="{{ $sucursal->ID }}"
                                                                    {{ ($transferenciaDTO->SucursalDestinoId ?? '') == $sucursal->ID ? 'selected' : '' }}>
                                                                    {{ $sucursal->Nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if($idTransferencia)
                                                            <input type="hidden" name="sucursal_destino" value="{{ $transferenciaDTO->SucursalDestinoId }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            {{-- Fecha --}}
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">Fecha: *</label>
                                                <div class="col-md-9">
                                                    <input type="date" name="fecha" id="fechaTransferencia" class="form-control"
                                                           value="{{ $transferenciaDTO->Fecha ?? date('Y-m-d') }}" 
                                                           {{ $idTransferencia ? 'disabled' : '' }} required>
                                                    @if($idTransferencia)
                                                        <input type="hidden" name="fecha" value="{{ $transferenciaDTO->Fecha }}">
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Estatus (solo lectura) --}}
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">Estatus:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" value="NUEVO" disabled>
                                                </div>
                                            </div>

                                            {{-- Número de transferencia (si existe) --}}
                                            @if($transferenciaDTO->Numero ?? false)
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">Número:</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control" value="{{ $transferenciaDTO->Numero }}" disabled>
                                                </div>
                                            </div>
                                            @endif

                                            {{-- Observación --}}
                                            <div class="row mb-3">
                                                <label class="col-md-3 col-form-label fw-semibold" style="font-size:0.8rem;">Observación:</label>
                                                <div class="col-md-9">
                                                    <textarea name="observacion" class="form-control" rows="2"
                                                              placeholder="Escriba la observación..."
                                                              {{ $idTransferencia ? 'disabled' : '' }}>{{ $transferenciaDTO->Observacion ?? '' }}</textarea>
                                                    @if($idTransferencia)
                                                        <input type="hidden" name="observacion" value="{{ $transferenciaDTO->Observacion ?? '' }}">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botón Iniciar Transferencia --}}
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            @if($isNueva)
                                                <button type="submit" class="btn btn-success" id="btnIniciarTransferencia">
                                                    <i class="bi bi-play-fill me-1"></i>
                                                    <span>Iniciar Transferencia</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary" disabled style="opacity:0.6;cursor:not-allowed;">
                                                    <i class="bi bi-play-fill me-1"></i>
                                                    <span>Iniciar Transferencia</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SECCIÓN 2: PRODUCTOS --}}
                    {{-- ========================================== --}}
                    @if($mostrarProductos && $idTransferencia)
                    <div class="accordion-item border mb-2">
                        <h2 class="accordion-header" id="headingProductos">
                            <button class="accordion-button {{ $panelActivo != 'productos' ? 'collapsed' : '' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseProductos" 
                                    aria-expanded="{{ $panelActivo == 'productos' ? 'true' : 'false' }}"
                                    aria-controls="collapseProductos"
                                    style="background:#3f51b5;color:white;font-size:0.85rem;font-weight:600;">
                                <i class="bi bi-box-seam me-2"></i>
                                <span>PRODUCTOS</span>
                                <small style="color:rgba(255,255,255,0.8);font-weight:400;font-size:0.72rem;margin-left:10px;">
                                    Productos disponibles en la sucursal origen
                                </small>
                            </button>
                        </h2>
                        <div id="collapseProductos" 
                            class="accordion-collapse collapse {{ $panelActivo == 'productos' ? 'show' : '' }}"
                            aria-labelledby="headingProductos"
                            data-bs-parent="#accordionTransferencia">
                            <div class="accordion-body">
                                
                                {{-- BUSCADOR Y BOTONES --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" 
                                                class="form-control" 
                                                id="buscadorProductos" 
                                                placeholder="Buscar por código o descripción...">
                                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                            <span class="text-muted" style="font-size:0.8rem;" id="contadorProductos">
                                                Mostrando <span id="productosVisibles">0</span> de <span id="productosTotales">{{ $listaProductos->count() }}</span> productos
                                            </span>
                                            {{-- Botón Subir Plantilla --}}
                                            <button type="button" class="btn btn-success btn-sm" id="btnSubirPlantilla" title="Subir plantilla con cantidades">
                                                <i class="bi bi-upload me-1"></i>Subir Plantilla
                                            </button>
                                            {{-- Botón Descargar Plantilla --}}
                                            <a href="{{ route('cpanel.distribucion.transferencia.descargar-plantilla', $idTransferencia) }}" 
                                            class="btn btn-primary btn-sm"
                                            target="_blank"
                                            title="Descargar plantilla de productos">
                                                <i class="bi bi-download me-1"></i>Descargar Plantilla
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Formulario oculto para subir la plantilla --}}
                                <form id="formSubirPlantilla" action="{{ route('cpanel.distribucion.transferencia.subir-plantilla', $idTransferencia) }}" 
                                    method="POST" enctype="multipart/form-data" style="display:none;">
                                    @csrf
                                    <input type="file" name="archivo_excel" id="inputSubirPlantilla" accept=".xlsx,.xls">
                                </form>


                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tablaProductos">
                                        <thead>
                                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                                <th class="py-2 text-muted fw-semibold text-center" style="font-size:0.75rem;letter-spacing:.06em;width:60px;" data-order="foto">Foto</th>
                                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="codigo">Código</th>
                                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="descripcion">Descripción</th>
                                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;cursor:pointer;" data-order="existencia">Exist.</th>
                                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;cursor:pointer;" data-order="costo">Costo $</th>
                                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;min-width:150px;">
                                                    {{ $sucursalDestinoNombre }}
                                                </th>
                                                <th class="py-2 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:140px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaProductosBody">
                                            @forelse($listaProductos as $producto)
                                            @php
                                                $imgSrc = FileHelper::getOrDownloadFile(
                                                    'images/items/thumbs/',
                                                    $producto->UrlFoto ?? '',
                                                    'assets/img/adminlte/img/produc_default.jfif'
                                                );
                                                
                                                // ✅ Obtener la cantidad guardada para este producto
                                                $cantidadGuardada = 0;
                                                if (isset($detallesExistentes[$producto->ID])) {
                                                    $cantidadGuardada = (float) $detallesExistentes[$producto->ID]->CantidadEmitida;
                                                }
                                                $tieneCantidad = $cantidadGuardada > 0;
                                            @endphp
                                            <tr data-producto-id="{{ $producto->ID }}" style="border-bottom:1px solid #f1f5f9;">
                                                <td class="text-center">
                                                    <img src="{{ $imgSrc }}" 
                                                        alt="{{ $producto->Codigo }}"
                                                        loading="lazy"
                                                        class="img-thumbnail img-zoomable"
                                                        style="width: 35px; height: 35px; object-fit: cover; cursor: pointer; border-radius:4px;"
                                                        data-full-image="{{ $imgSrc }}"
                                                        data-description="{{ $producto->Descripcion }}"
                                                        onclick="zoomImagen(this)"
                                                        onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                </td>
                                                <td class="codigo-cell">
                                                    <span class="fw-bold text-dark" style="font-size:0.85rem;font-family:monospace;">
                                                        {{ $producto->Codigo }}
                                                    </span>
                                                </td>
                                                <td class="descripcion-cell" style="font-size:0.85rem;">{{ $producto->Descripcion }}</td>
                                                <td class="text-center existencia-cell">
                                                    <span class="badge rounded-pill px-2 py-1 fw-semibold {{ $producto->Existencia > 0 ? 'bg-success' : 'bg-danger' }}"
                                                        style="font-size:0.75rem;">
                                                        {{ $producto->Existencia }}
                                                    </span>
                                                </td>
                                                <td class="text-center costo-cell" style="font-size:0.85rem;">
                                                    {{ number_format($producto->CostoDivisa ?? 0, 2) }}
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm cantidad-producto"
                                                        style="width:100%;font-size:0.85rem;"
                                                        min="0" max="{{ $producto->Existencia }}"
                                                        value="{{ $cantidadGuardada }}"
                                                        data-producto-id="{{ $producto->ID }}">
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" 
                                                                class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center btn-guardar-producto"
                                                                style="width:28px;height:28px;background:{{ $tieneCantidad ? 'rgba(16,185,129,0.2)' : 'rgba(16,185,129,0.1)' }};color:#059669;border:1px solid {{ $tieneCantidad ? 'rgba(16,185,129,0.3)' : 'rgba(16,185,129,0.25)' }};"
                                                                data-producto-id="{{ $producto->ID }}"
                                                                data-producto-codigo="{{ $producto->Codigo }}"
                                                                title="{{ $tieneCantidad ? 'Editar cantidad' : 'Guardar producto' }}">
                                                            <i class="bi {{ $tieneCantidad ? 'bi-pencil' : 'bi-save' }}" style="font-size:0.75rem;"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center btn-eliminar-producto"
                                                                style="width:28px;height:28px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);{{ !$tieneCantidad ? 'display:none;' : '' }}"
                                                                data-producto-id="{{ $producto->ID }}"
                                                                title="Eliminar producto">
                                                            <i class="bi bi-trash" style="font-size:0.75rem;"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                    No hay productos con existencia en la sucursal origen
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- PAGINACIÓN --}}
                                <div class="row mt-3 align-items-center">
                                    <div class="col-md-6">
                                        <span class="text-muted" style="font-size:0.8rem;">
                                            Mostrando <span id="paginaActualInfo">1</span> - <span id="paginaFinInfo">20</span> 
                                            de <span id="totalRegistrosInfo">0</span> productos
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <nav>
                                            <ul class="pagination pagination-sm justify-content-end mb-0" id="paginacionLista">
                                                <li class="page-item" id="prevPage">
                                                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                                </li>
                                                <li class="page-item active" id="page1"><a class="page-link" href="#" data-page="1">1</a></li>
                                                <li class="page-item" id="nextPage">
                                                    <a class="page-link" href="#">Siguiente</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ========================================== --}}
                    {{-- SECCIÓN 3: TOTAL RECEPCIÓN --}}
                    {{-- ========================================== --}}
                    @if($mostrarTotales)
                    <div class="accordion-item border mb-2">
                        <h2 class="accordion-header" id="headingTotales">
                            <button class="accordion-button {{ $panelActivo != 'totales' ? 'collapsed' : '' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseTotales" 
                                    aria-expanded="{{ $panelActivo == 'totales' ? 'true' : 'false' }}"
                                    aria-controls="collapseTotales"
                                    style="background:#4caf50;color:white;font-size:0.85rem;font-weight:600;">
                                <i class="bi bi-calculator me-2"></i>
                                <span>TOTAL RECEPCIÓN</span>
                                <small style="color:rgba(255,255,255,0.8);font-weight:400;font-size:0.72rem;margin-left:10px;">
                                    Resumen de productos a recibir
                                </small>
                            </button>
                        </h2>
                        <div id="collapseTotales" 
                            class="accordion-collapse collapse {{ $panelActivo == 'totales' ? 'show' : '' }}"
                            aria-labelledby="headingTotales"
                            data-bs-parent="#accordionTransferencia">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    {{-- Total Productos --}}
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h5 class="mb-0 fw-bold" id="totalProductos">{{ $totalesData->CantidadItems ?? 0 }}</h5>
                                            <small class="text-muted" style="font-size:0.75rem;">Total Productos</small>
                                        </div>
                                    </div>
                                    {{-- Total Unidades --}}
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h5 class="mb-0 fw-bold" id="totalUnidades">{{ $totalesData->CantidadEmitida ?? 0 }}</h5>
                                            <small class="text-muted" style="font-size:0.75rem;">Total Unidades</small>
                                        </div>
                                    </div>
                                    {{-- Total Costo Divisa --}}
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <h5 class="mb-0 fw-bold" id="totalCostoDivisa">${{ number_format($totalesData->CostoDivisaTotal ?? 0, 2) }}</h5>
                                            <small class="text-muted" style="font-size:0.75rem;">Total Costo Divisa</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-success" id="btnFinalizarTransferencia">
                                        <i class="bi bi-check-circle me-1"></i>Finalizar Transferencia
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ==========================================
    // FUNCIÓN ZOOM IMAGEN
    // ==========================================
    function zoomImagen(elemento) {
        const imgSrc = elemento.getAttribute('data-full-image') || elemento.src;
        const descripcion = elemento.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Cerrar',
            showCloseButton: true
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formTransferencia');
        const btnSubmit = document.getElementById('btnIniciarTransferencia');

        @if($idTransferencia)
        // ==========================================
        // MODO EDICIÓN - AGREGAR PRODUCTOS
        // ==========================================
        let productosAgregados = [];

        // ==========================================
        // BUSCADOR DE PRODUCTOS
        // ==========================================
        const buscador = document.getElementById('buscadorProductos');
        const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
        const tablaBody = document.getElementById('tablaProductosBody');
        
        // Obtener todas las filas de productos (excluyendo la fila de "no hay productos")
        const filas = tablaBody ? Array.from(tablaBody.querySelectorAll('tr:not(.no-result)')) : [];
        const contadorVisibles = document.getElementById('productosVisibles');
        const contadorTotales = document.getElementById('productosTotales');

        function filtrarProductos() {
            if (!buscador) return;
            
            const termino = buscador.value.toLowerCase().trim();
            let visibles = 0;

            filas.forEach(function(fila) {
                const codigo = fila.querySelector('.codigo-cell')?.textContent?.toLowerCase() || '';
                const descripcion = fila.querySelector('.descripcion-cell')?.textContent?.toLowerCase() || '';
                
                const coincide = codigo.includes(termino) || descripcion.includes(termino);
                
                if (coincide) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Actualizar contador
            if (contadorVisibles) contadorVisibles.textContent = visibles;
            if (contadorTotales) contadorTotales.textContent = filas.length;

            // Mostrar mensaje si no hay resultados
            let noResult = tablaBody?.querySelector('.no-result');
            if (visibles === 0 && filas.length > 0) {
                if (!noResult) {
                    noResult = document.createElement('tr');
                    noResult.className = 'no-result';
                    noResult.innerHTML = `
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-search fs-3 d-block mb-2"></i>
                            No se encontraron productos que coincidan con "${termino}"
                        </td>
                    `;
                    if (tablaBody) tablaBody.appendChild(noResult);
                } else {
                    noResult.style.display = '';
                    noResult.querySelector('td').innerHTML = `
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No se encontraron productos que coincidan con "${termino}"
                    `;
                }
            } else if (noResult) {
                noResult.style.display = 'none';
            }
        }

        // Evento del buscador
        if (buscador) {
            buscador.addEventListener('input', filtrarProductos);
            buscador.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    filtrarProductos();
                }
            });
        }

        // Botón limpiar búsqueda
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function() {
                if (buscador) {
                    buscador.value = '';
                    filtrarProductos();
                    buscador.focus();
                }
            });
        }

        // ==========================================
        // ORDENAMIENTO DE TABLA
        // ==========================================
        let ordenActual = { columna: null, direccion: 'asc' };

        document.querySelectorAll('.sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                const columna = this.dataset.order;
                if (!columna) return;

                // Cambiar dirección
                if (ordenActual.columna === columna) {
                    ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
                } else {
                    ordenActual.columna = columna;
                    ordenActual.direccion = 'asc';
                }

                // Actualizar iconos
                document.querySelectorAll('.sortable .sort-icon').forEach(function(icon) {
                    icon.className = 'bi bi-arrow-down-up sort-icon';
                });
                
                const icono = this.querySelector('.sort-icon');
                if (icono) {
                    icono.className = ordenActual.direccion === 'asc' 
                        ? 'bi bi-arrow-up sort-icon' 
                        : 'bi bi-arrow-down sort-icon';
                }

                ordenarTabla(columna, ordenActual.direccion);
            });
        });

        function ordenarTabla(columna, direccion) {
            if (!tablaBody) return;

            // Obtener solo las filas de productos (no la de "no hay resultados")
            const filasArray = Array.from(tablaBody.querySelectorAll('tr:not(.no-result)'));
            if (filasArray.length === 0) return;

            // Mapeo de columnas a su índice en la tabla
            const indices = {
                'codigo': 1,
                'descripcion': 2,
                'existencia': 3,
                'costo': 4
            };
            const index = indices[columna] || 0;

            filasArray.sort(function(a, b) {
                let valorA, valorB;

                if (columna === 'foto') {
                    return 0; // No ordenar por foto
                }

                if (columna === 'codigo') {
                    valorA = a.querySelector('.codigo-cell')?.textContent?.trim() || '';
                    valorB = b.querySelector('.codigo-cell')?.textContent?.trim() || '';
                } else if (columna === 'descripcion') {
                    valorA = a.querySelector('.descripcion-cell')?.textContent?.trim() || '';
                    valorB = b.querySelector('.descripcion-cell')?.textContent?.trim() || '';
                } else if (columna === 'existencia') {
                    const badgeA = a.querySelector('.existencia-cell .badge');
                    const badgeB = b.querySelector('.existencia-cell .badge');
                    valorA = parseInt(badgeA?.textContent?.trim() || 0);
                    valorB = parseInt(badgeB?.textContent?.trim() || 0);
                } else if (columna === 'costo') {
                    const textA = a.querySelector('.costo-cell')?.textContent?.trim() || '0';
                    const textB = b.querySelector('.costo-cell')?.textContent?.trim() || '0';
                    valorA = parseFloat(textA.replace(/,/g, '.')) || 0;
                    valorB = parseFloat(textB.replace(/,/g, '.')) || 0;
                }

                // Comparar
                if (typeof valorA === 'string') {
                    valorA = valorA.toLowerCase();
                    valorB = valorB.toLowerCase();
                    if (direccion === 'asc') {
                        return valorA.localeCompare(valorB);
                    } else {
                        return valorB.localeCompare(valorA);
                    }
                } else {
                    if (direccion === 'asc') {
                        return valorA - valorB;
                    } else {
                        return valorB - valorA;
                    }
                }
            });

            // Reordenar en el DOM
            filasArray.forEach(function(fila) {
                if (tablaBody) tablaBody.appendChild(fila);
            });
        }

        // ==========================================
        // SUBIR PLANTILLA
        // ==========================================
        const btnSubirPlantilla = document.getElementById('btnSubirPlantilla');
        const inputSubirPlantilla = document.getElementById('inputSubirPlantilla');
        const formSubirPlantilla = document.getElementById('formSubirPlantilla');

        if (btnSubirPlantilla) {
            btnSubirPlantilla.addEventListener('click', function() {
                inputSubirPlantilla.click();
            });
        }

        if (inputSubirPlantilla) {
            inputSubirPlantilla.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const extension = file.name.split('.').pop().toLowerCase();
                    
                    if (!['xlsx', 'xls'].includes(extension)) {
                        Swal.fire('Error', 'Solo se permiten archivos .xlsx o .xls', 'error');
                        this.value = '';
                        return;
                    }

                    Swal.fire({
                        title: '¿Subir plantilla?',
                        text: `Se procesará el archivo "${file.name}"`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, subir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formSubirPlantilla.submit();
                        } else {
                            this.value = '';
                        }
                    });
                }
            });
        }

        // ==========================================
        // PROCESAR PRODUCTOS DE PLANTILLA SUBIDA
        // ==========================================
        function procesarProductosPlantilla() {
            const productosPlantilla = @json(session('productos_plantilla', []));
            const transferenciaIdPlantilla = @json(session('transferencia_id_plantilla', 0));

            if (productosPlantilla.length === 0 || transferenciaIdPlantilla != {{ $idTransferencia ?? 0 }}) {
                return;
            }

            console.log('📦 Procesando ' + productosPlantilla.length + ' productos de la plantilla');

            let asignados = 0;
            let noEncontrados = [];

            productosPlantilla.forEach(function(item) {
                const filas = document.querySelectorAll('#tablaProductosBody tr:not(.no-result)');
                let encontrado = false;

                filas.forEach(function(tr) {
                    const codigoCell = tr.querySelector('.codigo-cell');
                    if (!codigoCell) return;
                    
                    const codigo = codigoCell.textContent.trim();
                    if (codigo === item.codigo) {
                        const input = tr.querySelector('.cantidad-producto');
                        if (input) {
                            const existencia = parseInt(input.getAttribute('max')) || 0;
                            const cantidad = Math.min(item.cantidad, existencia);
                            input.value = cantidad;
                            encontrado = true;
                            asignados++;
                            input.dispatchEvent(new Event('change'));
                        }
                    }
                });

                if (!encontrado) {
                    noEncontrados.push(item.codigo);
                }
            });

            let mensaje = `<strong>✅ ${asignados} productos asignados correctamente.</strong>`;
            if (noEncontrados.length > 0) {
                const mostrar = noEncontrados.slice(0, 10).join(', ');
                mensaje += `<br><br><strong>⚠️ ${noEncontrados.length} productos no encontrados:</strong><br>${mostrar}${noEncontrados.length > 10 ? '...' : ''}`;
            }

            Swal.fire({
                icon: 'success',
                title: 'Plantilla procesada',
                html: mensaje,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Aceptar'
            });

            fetch('#', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(err => console.error('Error al limpiar sesión:', err));
        }

        // ==========================================
        // AGREGAR PRODUCTO
        // ==========================================
        document.querySelectorAll('.btn-agregar-producto').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productoId = this.dataset.productoId;
                const codigo = this.dataset.productoCodigo;
                const tr = this.closest('tr');
                const cantidadInput = tr.querySelector('.cantidad-producto');
                const cantidad = cantidadInput.value;
                const existencia = parseInt(cantidadInput.getAttribute('max'));
                
                const costoDivisaTd = tr.querySelector('.costo-cell');
                const costoDivisa = parseFloat(costoDivisaTd?.textContent?.replace(',', '.').replace('$', '').trim()) || 0;

                if (parseFloat(cantidad) <= 0) {
                    Swal.fire('Error', 'Ingrese una cantidad mayor a 0', 'error');
                    return;
                }

                if (parseFloat(cantidad) > existencia) {
                    Swal.fire('Error', 'La cantidad no puede ser mayor a la existencia (' + existencia + ')', 'error');
                    return;
                }

                const yaAgregado = productosAgregados.find(p => p.id == productoId);
                if (yaAgregado) {
                    Swal.fire('Advertencia', 'El producto ya fue agregado', 'warning');
                    return;
                }

                productosAgregados.push({
                    id: productoId,
                    codigo: codigo,
                    cantidad: parseFloat(cantidad),
                    costoDivisa: costoDivisa
                });

                this.classList.remove('btn-success');
                this.classList.add('btn-danger');
                this.innerHTML = '<i class="bi bi-trash"></i>';
                this.title = 'Eliminar producto';
                
                const btnEliminar = tr.querySelector('.btn-eliminar-producto');
                btnEliminar.style.display = 'inline-block';

                cantidadInput.disabled = true;
                cantidadInput.style.backgroundColor = '#e9ecef';

                actualizarTotales();

                Swal.fire({
                    icon: 'success',
                    title: 'Producto agregado',
                    text: `Producto ${codigo} agregado con ${cantidad} unidades`,
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });

        // ==========================================
        // GUARDAR PRODUCTO (CON RE-EDICIÓN)
        // ==========================================
        document.querySelectorAll('.btn-guardar-producto').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productoId = this.dataset.productoId;
                const codigo = this.dataset.productoCodigo;
                const tr = this.closest('tr');
                const input = tr.querySelector('.cantidad-producto');
                const cantidad = parseFloat(input.value) || 0;
                
                const existenciaCell = tr.querySelector('.existencia-cell .badge');
                const existenciaTotal = parseInt(existenciaCell?.textContent?.trim() || 0);
                
                const estaGuardado = this.classList.contains('btn-info');
                
                if (cantidad <= 0) {
                    Swal.fire('Advertencia', 'Ingrese una cantidad mayor a 0', 'warning');
                    return;
                }
                
                if (cantidad > existenciaTotal) {
                    Swal.fire('Error', `La cantidad (${cantidad}) es mayor a la existencia disponible (${existenciaTotal})`, 'error');
                    return;
                }
                
                const titulo = estaGuardado ? '¿Actualizar cantidad?' : 'Confirmar cantidad';
                const texto = estaGuardado 
                    ? `Producto: ${codigo}\nNueva cantidad: ${cantidad}`
                    : `Producto: ${codigo}\nCantidad: ${cantidad}`;
                
                Swal.fire({
                    title: titulo,
                    text: texto,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: estaGuardado ? 'Sí, actualizar' : 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        guardarProducto(this, productoId, codigo, input, cantidad);
                    }
                });
            });
        });

        function guardarProducto(btn, productoId, codigo, input, cantidad) {
            const transferenciaId = {{ $transferenciaDTO->TransferenciaId ?? 0 }};
            const sucursalId = {{ $transferenciaDTO->SucursalDestinoId ?? 0 }};
            const tr = btn.closest('tr');
            const btnEliminar = tr.querySelector('.btn-eliminar-producto');
            
            const detalles = [{
                producto_id: productoId,
                sucursal_id: sucursalId,
                cantidad: cantidad
            }];
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch('{{ route("cpanel.distribucion.transferencia.detalle.guardar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transferencia_id: transferenciaId,
                    detalles: detalles
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                
                if (data.success) {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-info');
                    btn.innerHTML = '<i class="bi bi-pencil"></i>';
                    btn.title = 'Editar cantidad';
                    
                    if (btnEliminar) {
                        btnEliminar.style.display = 'inline-flex';
                    }
                    
                    input.disabled = false;
                    input.style.backgroundColor = '';
                    input.style.borderColor = '#10b981';
                    input.style.borderWidth = '2px';
                    
                    if (data.totales) {
                        actualizarTotales(data.totales);
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto guardado',
                        text: `Producto ${codigo} guardado con ${cantidad} unidades`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                btn.disabled = false;
                console.error('Error:', error);
                Swal.fire('Error', 'Error al guardar el producto', 'error');
            });
        }

        // ==========================================
        // ELIMINAR PRODUCTO
        // ==========================================
        document.querySelectorAll('.btn-eliminar-producto').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productoId = this.dataset.productoId;
                const codigo = this.dataset.productoCodigo;
                const tr = this.closest('tr');
                const input = tr.querySelector('.cantidad-producto');
                const btnGuardar = tr.querySelector('.btn-guardar-producto');
                
                const cantidad = parseFloat(input.value) || 0;
                
                if (cantidad <= 0) {
                    Swal.fire('Advertencia', 'El producto no tiene cantidades asignadas', 'warning');
                    return;
                }
                
                Swal.fire({
                    title: '¿Eliminar producto?',
                    text: `Se eliminará la cantidad (${cantidad}) del producto ${codigo}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eliminarProducto(this, productoId, codigo, input, btnGuardar);
                    }
                });
            });
        });

        function eliminarProducto(btn, productoId, codigo, input, btnGuardar) {
            const transferenciaId = {{ $transferenciaDTO->TransferenciaId ?? 0 }};
            const sucursalId = {{ $transferenciaDTO->SucursalDestinoId ?? 0 }};
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch('{{ route("cpanel.distribucion.transferencia.detalle.eliminar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transferencia_id: transferenciaId,
                    producto_id: productoId,
                    sucursal_id: sucursalId
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                
                if (data.success) {
                    input.value = 0;
                    input.disabled = false;
                    input.style.backgroundColor = '';
                    input.style.borderColor = '';
                    input.style.borderWidth = '';
                    
                    btnGuardar.classList.remove('btn-info');
                    btnGuardar.classList.add('btn-success');
                    btnGuardar.innerHTML = '<i class="bi bi-save"></i>';
                    btnGuardar.title = 'Guardar producto';
                    
                    btn.style.display = 'none';
                    btn.innerHTML = '<i class="bi bi-trash"></i>';
                    
                    if (data.totales) {
                        actualizarTotales(data.totales);
                    }
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Producto eliminado',
                        text: `Producto ${codigo} eliminado correctamente`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    btn.innerHTML = '<i class="bi bi-trash"></i>';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash"></i>';
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el producto', 'error');
            });
        }

        // ==========================================
        // ACTUALIZAR TOTALES (desde el servidor)
        // ==========================================
        function actualizarTotales(totales) {
            if (!totales) return;
            
            // ✅ Convertir a enteros para eliminar decimales
            const totalProductos = parseInt(totales.CantidadItems) || 0;
            const totalUnidades = parseInt(totales.CantidadEmitida) || 0;
            const totalCostoDivisa = parseFloat(totales.CostoDivisaTotal) || 0;
            
            const totalProductosEl = document.getElementById('totalProductos');
            const totalUnidadesEl = document.getElementById('totalUnidades');
            const totalCostoDivisaEl = document.getElementById('totalCostoDivisa');
            
            if (totalProductosEl) totalProductosEl.textContent = totalProductos;
            if (totalUnidadesEl) totalUnidadesEl.textContent = totalUnidades;
            if (totalCostoDivisaEl) totalCostoDivisaEl.textContent = '$' + totalCostoDivisa.toFixed(2);
        }

        // ==========================================
        // AGREGAR PRODUCTOS SELECCIONADOS
        // ==========================================
        const btnAgregarProductos = document.getElementById('btnAgregarProductos');
        if (btnAgregarProductos) {
            btnAgregarProductos.addEventListener('click', function() {
                if (productosAgregados.length === 0) {
                    Swal.fire('Advertencia', 'No ha seleccionado ningún producto', 'warning');
                    return;
                }

                let resumen = '<div style="text-align:left;max-height:300px;overflow-y:auto;">';
                resumen += '<table class="table table-sm table-bordered">';
                resumen += '<thead><tr><th>Código</th><th>Cantidad</th><th>Costo $</th><th>Total $</th></tr></thead>';
                resumen += '<tbody>';
                let totalGeneral = 0;
                productosAgregados.forEach(function(p) {
                    const total = p.cantidad * p.costoDivisa;
                    totalGeneral += total;
                    resumen += `<tr>
                        <td>${p.codigo}</td>
                        <td>${p.cantidad}</td>
                        <td>$${p.costoDivisa.toFixed(2)}</td>
                        <td>$${total.toFixed(2)}</td>
                    </tr>`;
                });
                resumen += `<tr class="table-success fw-bold">
                    <td colspan="3" class="text-end">TOTAL</td>
                    <td>$${totalGeneral.toFixed(2)}</td>
                </tr>`;
                resumen += '</tbody></table>';
                resumen += '</div>';

                Swal.fire({
                    title: 'Confirmar productos',
                    html: resumen,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, agregar',
                    cancelButtonText: 'Cancelar',
                    width: 600
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Productos agregados',
                            text: `Se agregaron ${productosAgregados.length} productos a la transferencia`,
                            confirmButtonColor: '#10b981'
                        });
                    }
                });
            });
        }

        // ==========================================
        // FINALIZAR TRANSFERENCIA
        // ==========================================
        const btnFinalizar = document.getElementById('btnFinalizarTransferencia');
        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', function() {
                const transferenciaId = {{ $transferenciaDTO->TransferenciaId ?? 0 }};

                // Verificar si hay productos guardados
                fetch('{{ route("cpanel.distribucion.transferencia.verificar-productos", ["id" => $transferenciaDTO->TransferenciaId]) }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.has_productos) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sin productos',
                            text: 'La transferencia no tiene productos asignados. Agregue al menos un producto antes de finalizar.',
                            confirmButtonColor: '#10b981'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Finalizar transferencia?',
                        text: `Se marcará la transferencia como finalizada con ${data.total_items} productos y ${data.total_unidades} unidades.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, finalizar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            finalizarTransferencia(transferenciaId);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al verificar los productos de la transferencia',
                        confirmButtonColor: '#dc2626'
                    });
                });
            });
        }

        function finalizarTransferencia(transferenciaId) {
            // ✅ Construir URL con route() y reemplazo
            const url = '{{ route("cpanel.distribucion.transferencia.finalizar", ["id" => ":id"]) }}'
                .replace(':id', transferenciaId);
            
            console.log('📍 URL:', url);
            console.log('🆔 Transferencia ID:', transferenciaId);

            Swal.fire({
                title: 'Finalizando transferencia...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                console.log('📥 Status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Data:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Transferencia finalizada!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '{{ route("cpanel.distribucion.transferencia") }}';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('💥 Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al finalizar la transferencia: ' + error.message,
                    confirmButtonColor: '#dc2626'
                });
            });
        }

        // ==========================================
        // PROCESAR PRODUCTOS DE PLANTILLA AL CARGAR
        // ==========================================
        procesarProductosPlantilla();

        @else
        // ==========================================
        // MODO NUEVO - INICIAR TRANSFERENCIA
        // ==========================================
        const origenSelect = document.getElementById('sucursalOrigen');
        const destinoSelect = document.getElementById('sucursalDestino');

        if (origenSelect) {
            origenSelect.addEventListener('change', validarSucursales);
        }
        if (destinoSelect) {
            destinoSelect.addEventListener('change', validarSucursales);
        }

        function validarSucursales() {
            const origen = document.getElementById('sucursalOrigen');
            const destino = document.getElementById('sucursalDestino');

            if (origen.value && destino.value && origen.value === destino.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sucursales iguales',
                    text: 'La sucursal Origen y Destino no pueden ser iguales',
                    confirmButtonColor: '#ff9800'
                });
                if (document.activeElement === origen) {
                    destino.value = '';
                } else {
                    origen.value = '';
                }
            }
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const origen = document.getElementById('sucursalOrigen');
                const destino = document.getElementById('sucursalDestino');

                if (!origen.value || !destino.value) {
                    Swal.fire('Error', 'Complete todos los campos requeridos', 'error');
                    return;
                }

                if (origen.value === destino.value) {
                    Swal.fire('Error', 'La sucursal Origen y Destino no pueden ser iguales', 'error');
                    return;
                }

                Swal.fire({
                    title: '¿Iniciar transferencia?',
                    text: 'Se creará la transferencia con los datos ingresados',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        guardarTransferencia();
                    }
                });
            });
        }

        function guardarTransferencia() {
            if (!btnSubmit) return;
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-play-fill me-1"></i> Iniciar Transferencia';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-play-fill me-1"></i> Iniciar Transferencia';
            });
        }
        @endif
    });
</script>
@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,0.125);
    }
    .accordion-item {
        border-radius: 0.375rem;
        overflow: hidden;
    }
    .accordion-button {
        font-weight: 600;
        padding: 0.65rem 1.25rem;
    }
    .accordion-button i {
        font-size: 1.1rem;
    }
    .accordion-button .bi {
        font-size: 1.1rem;
    }
    .table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.85rem;
    }
    #tablaProductos tbody tr:hover {
        background: #f8fafc;
    }
    .cantidad-producto:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 0.2rem rgba(16,185,129,0.25);
    }
    .btn-circle-sm {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .page-item.active .page-link {
        background-color: #10b981;
        border-color: #10b981;
    }
    .page-link {
        color: #10b981;
    }
    .page-link:hover {
        color: #059669;
    }
</style>
@endpush