@extends('layout.layout_dashboard')

@section('title', 'Recibir Distribución')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-box-arrow-in-down text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Recibir Distribución</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Confirmar recepción de productos en tu sucursal
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.recepciones.sucursal') }}">Listado Dist. / Trans.</a></li>
                    <li class="breadcrumb-item active">Recibir Distribución</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-receipt me-2"></i>Recibir Distribución de Mercancía
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $transferencia->Numero }}
                    </span>
                </div>
            </div>
            <div class="card-body">

                {{-- ================================================ --}}
                {{-- ACCORDION CON 3 PASOS --}}
                {{-- ================================================ --}}
                <div class="accordion" id="accordionRecepcion">

                    {{-- ============================================ --}}
                    {{-- PASO 1: CREAR DATOS DE LA DISTRIBUCIÓN --}}
                    {{-- ============================================ --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapsePaso1" aria-expanded="true">
                                <i class="bi bi-folder me-2"></i>Crear datos de la distribución
                            </button>
                        </h2>
                        <div id="collapsePaso1" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row">
                                    {{-- Información de la transferencia --}}
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-primary">DISTRIBUCIÓN</h5>
                                        <div class="border rounded p-3 bg-light">
                                            <ul class="list-unstyled mb-0">
                                                <li class="py-1 border-bottom">
                                                    <strong>Número:</strong>
                                                    <span class="float-end fw-bold">{{ $transferencia->Numero }}</span>
                                                </li>
                                                <li class="py-1 border-bottom">
                                                    <strong>Fecha:</strong>
                                                    <span class="float-end fw-bold">{{ \Carbon\Carbon::parse($transferencia->Fecha)->format('d/m/Y H:i') }}</span>
                                                </li>
                                                <li class="py-1 border-bottom">
                                                    <strong>Estatus:</strong>
                                                    <span class="float-end">
                                                        <span class="{{ $estatus['clase'] }} rounded-pill px-2 py-1">
                                                            {{ $estatus['texto'] }}
                                                        </span>
                                                    </span>
                                                </li>
                                                <li class="py-1 border-bottom">
                                                    <strong>Total Items:</strong>
                                                    <span class="float-end fw-bold">{{ $totalItems }} U</span>
                                                </li>
                                                <li class="py-1 border-bottom">
                                                    <strong>Total Unidades:</strong>
                                                    <span class="float-end fw-bold">{{ number_format($totalUnidades, 0) }} U</span>
                                                </li>
                                                <li class="py-1">
                                                    <strong>Total Costo:</strong>
                                                    <span class="float-end fw-bold text-success">
                                                        $ {{ number_format($detalles->sum(function($d) { return ($d->CostoDivisa ?? 0) * ($d->CantidadEmitida ?? 0); }), 2) }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    {{-- Formulario para Nueva Recepción --}}
                                    <div class="col-md-6">
                                        <form id="formNuevaRecepcion" method="POST" 
                                            action="{{ route('cpanel.recepcion.crear-recepcion', $transferencia->TransferenciaId) }}">
                                            @csrf
                                            
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Origen:</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" 
                                                        value="{{ $transferencia->sucursal_origen ?? 'N/A' }}" disabled>
                                                </div>
                                            </div>

                                            {{-- ✅ FECHA - HABILITADA cuando botonNuevaRecepcionActivo es true --}}
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold text-danger">Fecha: *</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="date" 
                                                        name="fecha_recepcion" 
                                                        class="form-control"
                                                        value="{{ old('fecha_recepcion', isset($recepcionData->FechaCreacion) ? \Carbon\Carbon::parse($recepcionData->FechaCreacion)->format('Y-m-d') : date('Y-m-d')) }}"
                                                        {{ !$botonNuevaRecepcionActivo ? 'disabled' : '' }}
                                                        required>
                                                </div>
                                            </div>

                                            {{-- ✅ OBSERVACIÓN - HABILITADA cuando botonNuevaRecepcionActivo es true --}}
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Observación:</label>
                                                </div>
                                                <div class="col-8">
                                                    <textarea name="observacion" 
                                                            class="form-control" 
                                                            rows="2"
                                                            placeholder="Escriba la observación..."
                                                            {{ !$botonNuevaRecepcionActivo ? 'disabled' : '' }}>{{ old('observacion', $recepcionData->Observacion ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            {{-- ✅ BOTÓN CORREGIDO --}}
                                            <div class="row">
                                                <div class="col-12">
                                                    @if($botonNuevaRecepcionActivo)
                                                        <button type="submit" class="btn btn-primary" id="btnNuevaRecepcion">
                                                            <i class="bi bi-plus-circle me-1"></i>Nueva Recepción
                                                        </button>
                                                        <small class="text-muted ms-2">
                                                            <i class="bi bi-info-circle"></i> Crear nueva recepción
                                                        </small>
                                                    @elseif($existeRecepcion && $transferencia->Estatus == 4)
                                                        <button type="button" class="btn btn-warning" disabled>
                                                            <i class="bi bi-clock me-1"></i>Recepción en Proceso
                                                        </button>
                                                        <small class="text-muted ms-2">
                                                            <i class="bi bi-info-circle"></i> 
                                                            Recepción #{{ $recepcionData->RecepcionId }} en proceso
                                                        </small>
                                                    @elseif($existeRecepcion && $transferencia->Estatus == 5)
                                                        <button type="button" class="btn btn-info" disabled>
                                                            <i class="bi bi-check-circle me-1"></i>Recepción Disponible
                                                        </button>
                                                        <small class="text-muted ms-2">
                                                            <i class="bi bi-info-circle"></i> 
                                                            Recepción #{{ $recepcionData->RecepcionId }} disponible
                                                        </small>
                                                    @elseif($transferencia->Estatus == 6)
                                                        <button type="button" class="btn btn-success" disabled>
                                                            <i class="bi bi-check-circle me-1"></i>Recepción Finalizada
                                                        </button>
                                                        <small class="text-muted ms-2">
                                                            <i class="bi bi-info-circle"></i> Todos los productos han sido recibidos
                                                        </small>
                                                    @else
                                                        <button type="button" class="btn btn-secondary" disabled>
                                                            <i class="bi bi-lock me-1"></i>Recepción no disponible
                                                        </button>
                                                        <small class="text-muted ms-2">
                                                            <i class="bi bi-info-circle"></i> 
                                                            @if($detalles->isEmpty())
                                                                No hay productos pendientes
                                                            @else
                                                                Consulte con el administrador
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Mostrar mensajes de éxito/error --}}
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- PASO 2: PRODUCTOS --}}
                    {{-- ============================================ --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapsePaso2" aria-expanded="false">
                                <i class="bi bi-box-seam me-2"></i>Productos
                                <small class="ms-2 text-muted">Muestra los productos de la recepción</small>
                            </button>
                        </h2>
                        <div id="collapsePaso2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                
                                {{-- Botones de carga/descarga Excel --}}
                                <div class="card mb-3 border-info">
                                    <div class="card-header bg-info text-white">
                                        <strong><i class="bi bi-file-excel me-2"></i>Cargar/Descargar Productos</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Subir archivo de recepción</label>
                                                <div class="input-group">
                                                    <input type="file" name="recepcion_excel" id="recepcion_excel"
                                                           class="form-control" accept=".xlsx,.xls">
                                                    <button type="button" class="btn btn-primary" id="btnUploadExcel" disabled>
                                                        <i class="bi bi-upload me-1"></i>Cargar Recepción
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-success" id="btnDescargarPlantilla">
                                                        <i class="bi bi-download me-1"></i>Descargar archivo recepción
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tabla de productos --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 60px;">Foto</th>
                                                <th>Código</th>
                                                <th>Descripción</th>
                                                <th class="text-center" style="width: 100px;">Disponible</th>
                                                <th class="text-center" style="width: 110px;">Recibida</th>
                                                <th class="text-center" style="width: 90px;">Pie Solo</th>
                                                <th class="text-center" style="width: 90px;">Pie Invertido</th>
                                                <th class="text-center" style="width: 90px;">Caja Vacía</th>
                                                <th class="text-center" style="width: 90px;">Pieza Dañada</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($detalles as $detalle)
                                            @php
                                                $imgSrc = FileHelper::getOrDownloadFile(
                                                    'images/items/thumbs/',
                                                    $detalle->UrlFoto ?? '',
                                                    'assets/img/adminlte/img/produc_default.jfif'
                                                );
                                                
                                                $cantidadEmitida = (float)($detalle->CantidadEmitida ?? 0);
                                                $cantidadRecibida = (float)($detalle->CantidadRecibida ?? 0);
                                                $cantidadDisponible = (float)($detalle->CantidadDisponible ?? 0); // ✅ NUEVO
                                            @endphp
                                            <tr data-producto-id="{{ $detalle->ProductoId }}">
                                                <td class="text-center">
                                                    <img src="{{ $imgSrc }}" 
                                                        alt="{{ $detalle->Codigo ?? 'Producto' }}"
                                                        class="img-thumbnail img-zoomable"
                                                        style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                                        data-full-image="{{ $imgSrc }}"
                                                        data-description="{{ $detalle->producto_nombre ?? 'Producto' }}"
                                                        onclick="zoomImagen(this)"
                                                        onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                </td>
                                                <td class="fw-semibold">{{ $detalle->Codigo ?? 'N/A' }}</td>
                                                <td>{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                                <td class="text-center fw-bold">
                                                    <span class="badge bg-{{ $cantidadDisponible > 0 ? 'warning' : 'success' }} rounded-pill px-2 py-1">
                                                        {{ number_format($cantidadDisponible, 0) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm text-center cantidad-recibida"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        data-cantidad-disponible="{{ $cantidadDisponible }}"
                                                        value=""
                                                        min="0" 
                                                        max="{{ $cantidadDisponible }}"
                                                        placeholder="0"
                                                        style="width: 100px; margin: 0 auto;">
                                                    <small class="text-muted d-block text-center" style="font-size: 0.7rem;">
                                                        Máx: {{ number_format($cantidadDisponible, 0) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm text-center cantidad-pie-solo"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        value="0" 
                                                        min="0"
                                                        placeholder="0"
                                                        style="width: 80px; margin: 0 auto;">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm text-center cantidad-pie-invertido"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        value="0" 
                                                        min="0"
                                                        placeholder="0"
                                                        style="width: 80px; margin: 0 auto;">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm text-center cantidad-caja-vacia"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        value="0" 
                                                        min="0"
                                                        placeholder="0"
                                                        style="width: 80px; margin: 0 auto;">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control form-control-sm text-center cantidad-pieza-danada"
                                                        data-producto-id="{{ $detalle->ProductoId }}"
                                                        value="0" 
                                                        min="0"
                                                        placeholder="0"
                                                        style="width: 80px; margin: 0 auto;">
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="bi bi-check-circle fs-1 text-success"></i><br>
                                                    <strong class="text-success">Todos los productos han sido recibidos</strong>
                                                    <p class="text-muted">No hay productos pendientes por recibir</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- PASO 3: TOTAL RECEPCIÓN --}}
                    {{-- ============================================ --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapsePaso3" aria-expanded="false">
                                <i class="bi bi-calculator me-2"></i>Total recepción
                            </button>
                        </h2>
                        <div id="collapsePaso3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                @php
                                    $totalCosto = $detalles->sum(function($d) { 
                                        return ($d->CostoDivisa ?? 0) * ($d->CantidadEmitida ?? 0); 
                                    });
                                    $porcentajeGlobal = $totalUnidades > 0 ? ($totalRecibido / $totalUnidades) * 100 : 0;
                                    
                                    // ✅ El botón "Finalizar" solo está activo cuando NO está activo el botón "Nueva Recepción"
                                    // Es decir, cuando estatus es 4 o 5 (Recibiendo/Disponible)
                                    $mostrarBotonFinalizar = !$botonNuevaRecepcionActivo && in_array($transferencia->Estatus, [4, 5]);
                                @endphp
                                
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 class="fw-bold text-primary">Resumen de la Recepción</h4>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-4">
                                                        <strong>Items:</strong>
                                                        <h3 class="text-info">{{ $totalItems }}</h3>
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Unidades:</strong>
                                                        <h3 class="text-primary">{{ number_format($totalUnidades, 0) }}</h3>
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Recibido:</strong>
                                                        <h3 class="text-success" id="totalRecibidoDisplay">
                                                            {{ number_format($totalRecibido, 0) }}
                                                        </h3>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="progress" style="height: 25px;">
                                                            <div class="progress-bar bg-{{ $porcentajeGlobal >= 100 ? 'success' : ($porcentajeGlobal >= 50 ? 'warning' : 'info') }}" 
                                                                id="barraProgresoGlobal"
                                                                role="progressbar" 
                                                                style="width: {{ $porcentajeGlobal }}%;" 
                                                                aria-valuenow="{{ $porcentajeGlobal }}" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                                <strong id="textoProgresoGlobal">{{ number_format($porcentajeGlobal, 0) }}%</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-12">
                                                        @if($mostrarBotonFinalizar)
                                                            {{-- ✅ Estatus 4 o 5 - Botón ACTIVO --}}
                                                            <button class="btn btn-success w-100" id="btnFinalizarRecepcion">
                                                                <i class="bi bi-check-circle me-1"></i>Finalizar Recepción
                                                            </button>
                                                        @else
                                                            {{-- ❌ Estatus 3 - Botón INACTIVO --}}
                                                            <button class="btn btn-secondary w-100" disabled>
                                                                <i class="bi bi-lock me-1"></i>
                                                                @if($transferencia->Estatus == 3)
                                                                    Primero debe crear la recepción
                                                                @elseif($transferencia->Estatus == 6)
                                                                    Recepción ya finalizada
                                                                @else
                                                                    Recepción no disponible
                                                                @endif
                                                            </button>
                                                        @endif
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
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Habilitar botón de upload cuando se selecciona un archivo
        $('#recepcion_excel').on('change', function() {
            $('#btnUploadExcel').prop('disabled', this.value === '');
        });

        // Eventos para actualizar porcentajes al cambiar cantidades
        $(document).on('input', '.cantidad-recibida', function() {
            actualizarPorcentajeFila(this);
            actualizarTotalesGlobales();
        });
    });

    // ============================================
    // ZOOM DE IMAGEN
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
    // ACTUALIZAR PORCENTAJE POR FILA
    // ============================================
    function actualizarPorcentajeFila(input) {
        const row = input.closest('tr');
        const cantidadDisponible = parseFloat(input.dataset.cantidadDisponible) || 0;
        const cantidadRecibida = parseFloat(input.value) || 0;
        
        // ✅ El porcentaje se calcula sobre lo disponible
        const porcentaje = cantidadDisponible > 0 ? (cantidadRecibida / cantidadDisponible) * 100 : 0;
        const porcentajeRedondeado = Math.min(porcentaje, 100);
        
        const barra = row.querySelector('.barra-progreso');
        const texto = row.querySelector('.texto-porcentaje');
        
        if (barra) {
            barra.style.width = porcentajeRedondeado + '%';
            barra.setAttribute('aria-valuenow', porcentajeRedondeado);
            barra.className = 'progress-bar barra-progreso';
            if (porcentajeRedondeado >= 100) {
                barra.classList.add('bg-success');
            } else if (porcentajeRedondeado >= 50) {
                barra.classList.add('bg-warning');
            } else {
                barra.classList.add('bg-info');
            }
        }
        
        if (texto) {
            texto.textContent = porcentajeRedondeado.toFixed(0) + '%';
        }
    }

    // ============================================
    // ACTUALIZAR TOTALES GLOBALES
    // ============================================
    function actualizarTotalesGlobales() {
        let totalRecibido = 0;
        let totalPieSolo = 0;
        let totalPieInvertido = 0;
        let totalCajaVacia = 0;
        let totalPiezaDanada = 0;
        
        document.querySelectorAll('#tbodyProductosRecibir tr, table tbody tr').forEach(row => {
            totalRecibido += parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;
            totalPieSolo += parseFloat(row.querySelector('.cantidad-pie-solo')?.value) || 0;
            totalPieInvertido += parseFloat(row.querySelector('.cantidad-pie-invertido')?.value) || 0;
            totalCajaVacia += parseFloat(row.querySelector('.cantidad-caja-vacia')?.value) || 0;
            totalPiezaDanada += parseFloat(row.querySelector('.cantidad-pieza-danada')?.value) || 0;
        });
        
        const displays = {
            'totalRecibidoDisplay': totalRecibido,
            'totalPieSoloDisplay': totalPieSolo,
            'totalPieInvertidoDisplay': totalPieInvertido,
            'totalCajaVaciaDisplay': totalCajaVacia,
            'totalPiezaDanadaDisplay': totalPiezaDanada
        };
        
        Object.keys(displays).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = displays[id].toFixed(0);
        });
    }

    // ============================================
    // EVENTOS (VERSIÓN SIMPLIFICADA)
    // ============================================
    $(document).ready(function() {
        // Un solo evento para todos los inputs
        $(document).on('input', '.cantidad-recibida, .cantidad-pie-solo, .cantidad-pie-invertido, .cantidad-caja-vacia, .cantidad-pieza-danada', function() {
            // Si es cantidad-recibida, actualizar porcentaje
            if ($(this).hasClass('cantidad-recibida')) {
                actualizarPorcentajeFila(this);
            }
            // Siempre actualizar totales globales
            actualizarTotalesGlobales();
        });
    });

    // ============================================
    // DESCARGAR PLANTILLA EXCEL
    // ============================================
    document.getElementById('btnDescargarPlantilla')?.addEventListener('click', function() {
        const transferenciaId = {{ $transferencia->TransferenciaId }};
        
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Preparando plantilla de recepción',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        // ✅ Usar la ruta con nombre de Laravel (MEJOR PRÁCTICA)
        const url = '{{ route("cpanel.recibir-sucursal.download-template", ":id") }}'.replace(':id', transferenciaId);
        window.location.href = url;
        
        setTimeout(() => {
            Swal.close();
        }, 2000);
    });

    // ============================================
    // SUBIR EXCEL DE RECEPCIÓN (SOLO LECTURA)
    // ============================================
    document.getElementById('btnUploadExcel')?.addEventListener('click', function() {
        const fileInput = document.getElementById('recepcion_excel');
        const file = fileInput.files[0];

        if (!file) {
            Swal.fire('Error', 'Seleccione un archivo Excel', 'warning');
            return;
        }

        const transferenciaId = {{ $transferencia->TransferenciaId }};
        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');

        Swal.fire({
            title: 'Cargando...',
            text: 'Procesando archivo Excel',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const url = '{{ route("cpanel.recibir-sucursal.upload-excel", ":id") }}'.replace(':id', transferenciaId);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ SOLO actualizar los inputs en la tabla (NO guarda en BD)
                if (data.data && data.data.productos) {
                    data.data.productos.forEach(producto => {
                        // Buscar el input de cantidad recibida
                        const inputRecibido = document.querySelector(
                            `.cantidad-recibida[data-producto-id="${producto.producto_id}"]`
                        );
                        if (inputRecibido) {
                            inputRecibido.value = producto.recibido;
                            actualizarPorcentajeFila(inputRecibido);
                        }
                        
                        // Actualizar Pie Solo
                        const inputPieSolo = document.querySelector(
                            `.cantidad-pie-solo[data-producto-id="${producto.producto_id}"]`
                        );
                        if (inputPieSolo) {
                            inputPieSolo.value = producto.pie_solo;
                        }
                        
                        // Actualizar Pie Invertido
                        const inputPieInvertido = document.querySelector(
                            `.cantidad-pie-invertido[data-producto-id="${producto.producto_id}"]`
                        );
                        if (inputPieInvertido) {
                            inputPieInvertido.value = producto.pie_invertido;
                        }
                        
                        // Actualizar Dañado
                        const inputDanado = document.querySelector(
                            `.cantidad-pieza-danada[data-producto-id="${producto.producto_id}"]`
                        );
                        if (inputDanado) {
                            inputDanado.value = producto.danado;
                        }
                        
                        // Actualizar Vacío (Caja Vacía)
                        const inputVacio = document.querySelector(
                            `.cantidad-caja-vacia[data-producto-id="${producto.producto_id}"]`
                        );
                        if (inputVacio) {
                            inputVacio.value = producto.vacio;
                        }
                    });
                }
                
                // Actualizar totales globales (solo frontend)
                actualizarTotalesGlobales();
                
                // Mostrar mensaje de éxito
                let mensaje = data.message;
                if (data.data && data.data.productos_no_encontrados && data.data.productos_no_encontrados.length > 0) {
                    mensaje += ` Productos no encontrados: ${data.data.productos_no_encontrados.join(', ')}`;
                }
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Excel cargado!',
                    text: mensaje,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al procesar el archivo'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al conectar con el servidor: ' + error.message
            });
        });
    });

    // ============================================
    // FINALIZAR RECEPCIÓN
    // ============================================
    document.getElementById('btnFinalizarRecepcion')?.addEventListener('click', function() {
        const transferenciaId = {{ $transferencia->TransferenciaId }};
        
        // ✅ RECOGER TODOS LOS CAMPOS
        const cantidades = {};
        let totalRecibido = 0;
        let productosConError = false;
        let mensajesError = [];
        let hayProductos = false;
        
        document.querySelectorAll('#tbodyProductosRecibir tr, table tbody tr').forEach(row => {
            const productoId = row.dataset.productoId;
            if (!productoId) return;
            
            // ✅ Recoger todos los campos
            const pieSolo = parseFloat(row.querySelector('.cantidad-pie-solo')?.value) || 0;
            const pieInvertido = parseFloat(row.querySelector('.cantidad-pie-invertido')?.value) || 0;
            const vacio = parseFloat(row.querySelector('.cantidad-caja-vacia')?.value) || 0;
            const danado = parseFloat(row.querySelector('.cantidad-pieza-danada')?.value) || 0;
            const cantidadDisponible = parseFloat(row.querySelector('.cantidad-recibida')?.dataset?.cantidadDisponible) || 0;
            const recibido = parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;

            if (recibido > cantidadDisponible) {
                productosConError = true;
                const codigo = row.querySelector('td:nth-child(2)')?.textContent || 'N/A';
                mensajesError.push(`• ${codigo}: solo hay ${cantidadDisponible} unidad(es) disponible(s)`);
            }
            
            // ✅ Guardar TODOS los campos (aunque sean 0)
            cantidades[productoId] = {
                recibido: recibido,
                pie_solo: pieSolo,
                pie_invertido: pieInvertido,
                vacio: vacio,
                danado: danado
            };
            
            if (recibido > 0) {
                hayProductos = true;
                totalRecibido += recibido;
            }
        });
        
        // Validar que haya al menos un producto recibido
        if (!hayProductos) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'No se ha registrado la recepción de ningún producto'
            });
            return;
        }
        
        // Validar que ninguna cantidad exceda lo enviado
        if (productosConError) {
            Swal.fire({
                icon: 'error',
                title: 'Cantidades excedidas',
                html: `Los siguientes productos exceden la cantidad enviada:<br><br>${mensajesError.join('<br>')}`,
                confirmButtonText: 'Revisar'
            });
            return;
        }
        
        // Confirmar finalización
        Swal.fire({
            title: '¿Finalizar recepción?',
            html: `Se confirmará la recepción de <strong>${Object.keys(cantidades).length}</strong> productos<br>
                Total de unidades: <strong>${totalRecibido}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Finalizando recepción',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                const url = '{{ route("cpanel.recibir-sucursal.confirmar", ":id") }}'.replace(':id', transferenciaId);
                console.log('🌐 URL:', url);
                console.log('📦 Datos enviados:', {
                    cantidades: cantidades,
                    fecha_recepcion: document.querySelector('input[name="fecha_recepcion"]')?.value,
                    observacion: document.querySelector('textarea[name="observacion"]')?.value
                });
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        cantidades: cantidades,
                        fecha_recepcion: document.querySelector('input[name="fecha_recepcion"]')?.value,
                        observacion: document.querySelector('textarea[name="observacion"]')?.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Recepción finalizada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("cpanel.recepciones.sucursal") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al finalizar la recepción'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    });
</script>
@endsection