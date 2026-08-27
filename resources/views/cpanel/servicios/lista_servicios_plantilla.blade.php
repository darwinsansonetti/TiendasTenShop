@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Registrar Servicios')

@php
    use App\Helpers\FileHelper;
    use Carbon\Carbon;
    
    // Colores para Servicios (Púrpura)
    $hdrBg = 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
    $hdrIcon = 'tools';
    $hdrTitle = 'Registrar Servicios';
    $hdrSubtitle = 'Gestión de servicios plantilla';
@endphp

@section('content')

{{-- ================================================ --}}
{{-- HEADER --}}
{{-- ================================================ --}}
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.servicios.listado') }}">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Servicios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- ================================================ --}}
{{-- CONTENIDO PRINCIPAL --}}
{{-- ================================================ --}}
<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD 1: INFORMACIÓN DEL PROVEEDOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Información del Proveedor
                    </h6>
                    <div>
                        <a href="{{ route('cpanel.proveedor.servicios.listado') }}" 
                           class="btn btn-sm text-white" 
                           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-start">
                    {{-- Imagen + badges --}}
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc ?? asset('assets/img/adminlte/img/proveedor_default.png') }}"
                                alt="{{ $proveedor->Nombre ?? 'Proveedor' }}"
                                class="rounded-circle img-zoomable"
                                style="width:120px;height:120px;object-fit:cover;border:3px solid #e2e8f0;cursor:zoom-in;"
                                onclick="zoomImagen(this)"
                                data-full-image="{{ $imgSrc ?? asset('assets/img/adminlte/img/proveedor_default.png') }}"
                                data-description="{{ $proveedor->Nombre ?? 'Proveedor' }}">
                        <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                            @if(isset($proveedor) && $proveedor->Estatus == 0)
                                <span class="badge rounded-pill"
                                        style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Activo</span>
                            @else
                                <span class="badge rounded-pill"
                                        style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.72rem;">Inactivo</span>
                            @endif
                            @if(isset($proveedor) && $proveedor->Tipo == 0)
                                <span class="badge rounded-pill"
                                        style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.72rem;">Mercancía</span>
                            @else
                                <span class="badge rounded-pill"
                                        style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Servicio</span>
                            @endif
                        </div>
                    </div>
                    {{-- Datos --}}
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                                <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->Rif_Cedula ?? $proveedor->Rif ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha Registro</p>
                                <p class="mb-0 fw-semibold text-dark">{{ isset($proveedor->FechaCreacion) ? Carbon::parse($proveedor->FechaCreacion)->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono Móvil</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoMovil ?? $proveedor->Telefono1 ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono Fijo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoFijo ?? $proveedor->Telefono2 ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Correo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->CorreoElectronico ?? $proveedor->Email ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-8 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Dirección</p>
                                <p class="mb-0 fw-semibold text-dark">{{ isset($proveedor->Direccion) ? Str::limit($proveedor->Direccion, 80) : 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Número de Cuenta</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->NumeroDeCuenta ?? 'N/A' }}</p>
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
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="buscadorServicio" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            <i class="bi bi-search me-1" style="color:#8b5cf6;"></i>Buscar Servicio
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search" style="color:#8b5cf6;"></i>
                            </span>
                            <input type="text"
                                   id="buscadorServicio"
                                   class="form-control border-start-0 border-end-0"
                                   placeholder="Número, proveedor o descripción..."
                                   autocomplete="off">
                            <button class="btn btn-light border" type="button" id="limpiarBuscador" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label for="filtroProveedor" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            <i class="bi bi-building me-1" style="color:#8b5cf6;"></i>Proveedor
                        </label>
                        <select id="filtroProveedor" class="form-select">
                            <option value="">Todos los proveedores</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->Nombre }}">{{ $prov->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('cpanel.proveedores.servicios.detalle.seleccion', $proveedor->ProveedorId ?? 0) }}"
                           class="btn w-100 fw-semibold text-white"
                           style="background:{{ $hdrBg }};font-size:0.85rem;border:none;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE SERVICIOS --}}
        {{-- ================================================ --}}
        @if($servicios && $servicios->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Servicios Plantilla
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#7c3aed;">
                            {{ $servicios->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        {{-- Botón Nuevo Servicio - Redirige a la página de creación --}}
                        <a href="{{ route('cpanel.servicios.nuevo', $proveedor->ProveedorId ?? 0) }}"
                           class="btn btn-sm fw-semibold"
                           style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                            <i class="bi bi-plus-circle me-1"></i>Nuevo
                        </a>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelServicios()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarPDFServicios()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                {{-- CONTENEDOR CON SCROLL CORREGIDO --}}
                <div class="table-responsive-scroll">
                    <table class="table table-hover align-middle mb-0" id="tablaServicios">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold sortable" data-col="numero" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:180px;">
                                    NÚMERO <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="descripcion" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:200px;">
                                    DESCRIPCIÓN <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold sortable" data-col="monto" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:130px;">
                                    MONTO (USD) <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="proveedor" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:150px;">
                                    PROVEEDOR <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="sucursal" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;min-width:150px;">
                                    SUCURSAL <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    ESTADO
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyServicios">
                            @foreach($servicios as $servicio)
                                @php
                                    $monto = floatval($servicio->MontoDivisa ?? 0);
                                    $estatus = $servicio->Estatus ?? 0;
                                    $estatusTexto = $estatus == 0 ? 'Activo' : 'Inactivo';
                                    $estatusBadge = $estatus == 0 ? 'success' : 'danger';
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;"
                                    data-numero="{{ $servicio->Numero ?? '' }}"
                                    data-descripcion="{{ $servicio->Descripcion ?? '' }}"
                                    data-proveedor="{{ $servicio->proveedor_nombre ?? '' }}"
                                    data-sucursal="{{ $servicio->sucursal_nombre ?? '' }}">
                                    <td class="ps-4" data-order="{{ $servicio->Numero ?? '' }}">
                                        <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#7c3aed;font-size:0.8rem;font-weight:bold;">
                                            {{ $servicio->Numero ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td data-order="{{ $servicio->Descripcion ?? '' }}">
                                        <span class="fw-semibold text-dark">{{ $servicio->Descripcion ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end" data-order="{{ $monto }}">
                                        <span class="fw-semibold" style="color:#4b5563;">
                                            ${{ number_format($monto, 2) }}
                                        </span>
                                    </td>
                                    <td data-order="{{ $servicio->proveedor_nombre ?? '' }}">
                                        <span>{{ $servicio->proveedor_nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td data-order="{{ $servicio->sucursal_nombre ?? '' }}">
                                        <span>{{ $servicio->sucursal_nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $estatusBadge }}" style="font-size:0.75rem;">
                                            {{ $estatusTexto }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            {{-- Ver Detalle --}}
                                            <a href="{{ route('cpanel.servicios.detalle', $servicio->ServiciosPlantillaId) }}"
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(20,184,166,0.1);color:#0d9488;border:1px solid rgba(20,184,166,0.25);"
                                            title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>
                                            {{-- Editar --}}
                                            <a href="{{ route('cpanel.servicios.editar', $servicio->ServiciosPlantillaId) }}"
                                               class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                               title="Editar servicio" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </a>
                                            @if($estatus == 0)
                                                {{-- Desactivar --}}
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                        onclick="eliminarServicio({{ $servicio->ServiciosPlantillaId }})"
                                                        title="Desactivar servicio" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                            @else
                                                {{-- Activar --}}
                                                <button type="button"
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                        onclick="activarServicio({{ $servicio->ServiciosPlantillaId }})"
                                                        title="Activar servicio" data-bs-toggle="tooltip">
                                                    <i class="bi bi-check-circle" style="font-size:0.8rem;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-tools me-1"></i>
                        {{ $servicios->count() }} servicio{{ $servicios->count() != 1 ? 's' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        @else

        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    <i class="bi bi-tools text-muted" style="font-size:2rem;opacity:.5;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No hay servicios registrados</h5>
                <p class="text-muted mb-4" style="font-size:0.9rem;">
                    Este proveedor no tiene servicios registrados.
                </p>
                <a href="{{ route('cpanel.servicios.nuevo', $proveedor->ProveedorId ?? 0) }}" 
                   class="btn px-4 fw-semibold" 
                   style="background:{{ $hdrBg }};color:#fff;border:none;">
                    <i class="bi bi-plus-circle me-2"></i>Crear primer servicio
                </a>
            </div>
        </div>

        @endif

    </div>
</div>

{{-- ================================================ --}}
{{-- MODAL: VER DETALLE DEL SERVICIO --}}
{{-- ================================================ --}}
<div class="modal fade" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:{{ $hdrBg }};color:#fff;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle me-2"></i>Detalle del Servicio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalleServicioContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================ --}}
{{-- MODAL: ZOOM DE IMAGEN --}}
{{-- ================================================ --}}
<div class="modal fade" id="modalZoomImagen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img id="imagenZoom" src="" alt="Zoom" style="max-width:100%;max-height:80vh;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <p id="zoomDescripcion" class="text-white mt-3 mb-0" style="font-weight:500;text-shadow:0 2px 10px rgba(0,0,0,0.5);"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    // Función para zoom de imagen
    function zoomImagen(element) {
        const modal = new bootstrap.Modal(document.getElementById('modalZoomImagen'));
        const fullImage = element.dataset.fullImage || element.src;
        const description = element.dataset.description || 'Imagen';
        
        document.getElementById('imagenZoom').src = fullImage;
        document.getElementById('zoomDescripcion').textContent = description;
        modal.show();
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // BUSCADOR
        const buscador = document.getElementById('buscadorServicio');
        const limpiarBtn = document.getElementById('limpiarBuscador');
        const filtroProveedor = document.getElementById('filtroProveedor');

        function filtrarTabla() {
            const textoBusqueda = buscador.value.toLowerCase().trim();
            const proveedorFiltro = filtroProveedor.value.toLowerCase();
            const filas = document.querySelectorAll('#tbodyServicios tr');
            let filasVisibles = 0;

            filas.forEach(fila => {
                const numero = (fila.dataset.numero || '').toLowerCase();
                const descripcion = (fila.dataset.descripcion || '').toLowerCase();
                const proveedor = (fila.dataset.proveedor || '').toLowerCase();
                const sucursal = (fila.dataset.sucursal || '').toLowerCase();

                const coincideBusqueda = !textoBusqueda || 
                    numero.includes(textoBusqueda) || 
                    descripcion.includes(textoBusqueda) || 
                    proveedor.includes(textoBusqueda) ||
                    sucursal.includes(textoBusqueda);

                const coincideProveedor = !proveedorFiltro || proveedor.includes(proveedorFiltro);

                if (coincideBusqueda && coincideProveedor) {
                    fila.style.display = '';
                    filasVisibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            const tbody = document.getElementById('tbodyServicios');
            let mensajeNoResultados = document.getElementById('mensajeNoResultados');

            if (filasVisibles === 0 && (textoBusqueda || proveedorFiltro)) {
                if (!mensajeNoResultados) {
                    mensajeNoResultados = document.createElement('tr');
                    mensajeNoResultados.id = 'mensajeNoResultados';
                    const colspan = document.querySelector('#tablaServicios thead tr').children.length;
                    mensajeNoResultados.innerHTML = `
                        <td colspan="${colspan}" class="text-center text-muted py-4">
                            <i class="bi bi-search me-2"></i>
                            No se encontraron servicios con los filtros seleccionados
                        </td>
                    `;
                    tbody.appendChild(mensajeNoResultados);
                }
            } else if (mensajeNoResultados) {
                mensajeNoResultados.remove();
            }
        }

        buscador.addEventListener('input', filtrarTabla);
        filtroProveedor.addEventListener('change', filtrarTabla);

        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', function() {
                buscador.value = '';
                filtroProveedor.value = '';
                filtrarTabla();
                buscador.focus();
            });
        }

        buscador.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                buscador.value = '';
                filtroProveedor.value = '';
                filtrarTabla();
            }
        });

        // ORDENAR TABLA
        (function() {
            const tabla = document.getElementById('tablaServicios');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
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

                    const valorA = tdA.dataset.order || tdA.innerText.trim();
                    const valorB = tdB.dataset.order || tdB.innerText.trim();

                    const esNumero = !isNaN(parseFloat(valorA.replace(/[$,%]/g, ''))) && 
                                     !isNaN(parseFloat(valorB.replace(/[$,%]/g, '')));
                    
                    if (esNumero) {
                        const numA = parseFloat(valorA.replace(/[$,%]/g, '')) || 0;
                        const numB = parseFloat(valorB.replace(/[$,%]/g, '')) || 0;
                        return asc ? numA - numB : numB - numA;
                    }

                    return asc
                        ? valorA.toString().localeCompare(valorB.toString())
                        : valorB.toString().localeCompare(valorA.toString());
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));

                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }
        })();
    });

    // ELIMINAR SERVICIO
    function eliminarServicio(id) {
        cambiarEstatusServicio(id, 1, 'desactivar');
    }

    // ACTIVAR SERVICIO
    function activarServicio(id) {
        cambiarEstatusServicio(id, 0, 'activar');
    }

    function cambiarEstatusServicio(id, estatus, accion) {
        // Determinar mensajes según la acción
        let titulo, texto, icono, confirmButtonColor, confirmButtonText;
        
        if (estatus === 0) {
            // Activar
            titulo = '¿Activar servicio?';
            texto = 'El servicio volverá a estar disponible.';
            icono = 'question';
            confirmButtonColor = '#059669';
            confirmButtonText = 'Sí, activar';
        } else {
            // Desactivar
            titulo = '¿Desactivar servicio?';
            texto = 'El servicio quedará como inactivo. Puede reactivarlo más tarde.';
            icono = 'warning';
            confirmButtonColor = '#dc3545';
            confirmButtonText = 'Sí, desactivar';
        }

        Swal.fire({
            title: titulo,
            text: texto,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud al servidor
                fetch('{{ route("cpanel.servicios.cambiar.estatus") }}', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        estatus: estatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: data.titulo || (estatus === 0 ? '¡Activado!' : '¡Desactivado!'),
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al procesar la solicitud', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión al servidor', 'error');
                });
            }
        });
    }

    // EXPORTAR EXCEL
    function exportarExcelServicios() {
        const tabla = document.getElementById('tablaServicios');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a Excel',
            text: '¿Deseas exportar todos los servicios?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(tabla, { sheet: "Servicios" });
                XLSX.utils.book_append_sheet(wb, ws, 'Servicios');
                XLSX.writeFile(wb, `Servicios_${new Date().toISOString().slice(0,10)}.xlsx`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }

    // EXPORTAR PDF
    function exportarPDFServicios() {
        const tabla = document.getElementById('tablaServicios');
        if (!tabla) return;

        Swal.fire({
            title: 'Exportando a PDF',
            text: '¿Deseas exportar todos los servicios?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');

                doc.setFontSize(16);
                doc.setTextColor(139, 92, 246);
                doc.text('Listado de Servicios', 14, 15);

                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

                doc.autoTable({
                    html: '#tablaServicios',
                    startY: 35,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [139, 92, 246] }
                });

                doc.save(`Servicios_${new Date().toISOString().slice(0,10)}.pdf`);
                
                Swal.fire('¡Éxito!', 'Archivo exportado correctamente', 'success');
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaServicios tbody tr:hover { background-color: #f8fafc; }
    .card-header { border-radius: 8px 8px 0 0; }
    thead th.sortable:hover { background-color: #f1f5f9; }
    .img-zoomable { 
        transition: transform 0.2s ease, box-shadow 0.2s ease; 
        cursor: pointer;
    }
    .img-zoomable:hover { 
        transform: scale(1.08); 
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
    }
    #modalZoomImagen .modal-content {
        background: transparent;
    }

    /* ================================================ */
    /* ESTILOS PARA EL SCROLL DE LA TABLA */
    /* ================================================ */
    .table-responsive-scroll {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: auto;
        position: relative;
    }
    
    .table-responsive-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Personalizar la barra de scroll */
    .table-responsive-scroll::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .table-responsive-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 4px;
    }
    
    .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: #a0a7ae;
    }
</style>
@endpush