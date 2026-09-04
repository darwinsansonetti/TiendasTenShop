@extends('layout.layout_dashboard')

@section('title', 'Detalle del Gasto')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon = 'eye';
    $hdrTitle = 'Detalle del Gasto';
    $hdrSubtitle = 'Información completa del gasto';
    
    $formasPago = [
        0 => 'Efectivo',
        1 => 'Cheque',
        2 => 'Depósito',
        3 => 'Transferencia',
        4 => 'Zelle',
        5 => 'Paypal',
        6 => 'Otro'
    ];
    
    $estatusLabels = [
        0 => 'Anulada',
        1 => 'En Proceso',
        2 => 'Pagada',
        3 => 'Cancelada'
    ];
    
    $estatusBadge = [
        0 => 'danger',
        1 => 'warning',
        2 => 'success',
        3 => 'secondary'
    ];
    
    $esEnProceso = ($gasto->Estatus ?? 2) == 1;
    
    // Verificar si tiene comprobante
    $tieneComprobante = isset($gasto->UrlComprobante) && !empty($gasto->UrlComprobante);
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.contabilidad.lista_gastos') }}">Gastos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-3">
            {{-- Datos del gasto --}}
            <div class="col-md-{{ $tieneComprobante ? '6' : '12' }}">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-info-circle me-2"></i>Información del Gasto
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Número de Operación --}}
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Número de Operación</p>
                                <p class="fw-bold text-dark fs-5">
                                    <code class="px-3 py-2 rounded-2" style="background:#f1f5f9;color:#dc2626;font-size:1rem;">
                                        {{ $gasto->NumeroOperacion ?? 'N/A' }}
                                    </code>
                                </p>
                            </div>

                            {{-- Estatus --}}
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Estatus</p>
                                <p>
                                    <span class="badge bg-{{ $estatusBadge[$gasto->Estatus ?? 2] ?? 'secondary' }}" style="font-size:0.9rem;">
                                        {{ $estatusLabels[$gasto->Estatus ?? 2] ?? 'Desconocido' }}
                                    </span>
                                </p>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Fecha</p>
                                <p class="fw-bold text-dark">{{ $gasto->FechaFormateada ?? 'N/A' }}</p>
                            </div>

                            {{-- Categoría --}}
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Categoría</p>
                                <p class="fw-bold text-dark">{{ $gasto->categoria_nombre ?? 'Sin categoría' }}</p>
                            </div>

                            {{-- Forma de Pago --}}
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Forma de Pago</p>
                                <p class="fw-bold text-dark">{{ $gasto->FormaDePagoTexto ?? 'N/A' }}</p>
                            </div>

                            {{-- Descripción --}}
                            <div class="col-md-12">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Descripción</p>
                                <p class="fw-bold text-dark">{{ $gasto->Descripcion ?? 'N/A' }}</p>
                            </div>

                            {{-- Observación --}}
                            <div class="col-md-12">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Observación</p>
                                <p class="fw-bold text-dark">{{ $gasto->Observacion ?? 'N/A' }}</p>
                            </div>

                            {{-- Monto en Divisas --}}
                            <div class="col-md-4">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Monto en Divisas</p>
                                <p class="fw-bold text-dark text-success">$ {{ number_format($gasto->MontoDivisaAbonado ?? 0, 2) }}</p>
                            </div>

                            {{-- Monto en Bs. --}}
                            <div class="col-md-4">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Monto en Bs.</p>
                                <p class="fw-bold text-dark text-primary">Bs. {{ number_format($gasto->MontoAbonado ?? 0, 2) }}</p>
                            </div>

                            {{-- Tasa de Cambio --}}
                            <div class="col-md-4">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Tasa de Cambio</p>
                                <p class="fw-bold text-dark">{{ number_format($gasto->TasaDeCambio ?? 0, 2) }}</p>
                            </div>

                            {{-- Nombre --}}
                            @if($gasto->Nombre)
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Nombre</p>
                                <p class="fw-bold text-dark">{{ $gasto->Nombre }}</p>
                            </div>
                            @endif

                            {{-- Cédula --}}
                            @if($gasto->Cedula)
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Cédula</p>
                                <p class="fw-bold text-dark">{{ $gasto->Cedula }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                            <a href="{{ route('cpanel.contabilidad.lista_gastos') }}" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                            
                            {{-- Editar SOLO si está En Proceso --}}
                            @if($esEnProceso)
                                <a href="{{ route('cpanel.contabilidad.editar_gasto', $gasto->ID) }}" 
                                   class="btn px-4 fw-semibold text-white" 
                                   style="background:{{ $hdrBg }};border:none;">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Comprobante --}}
            @if($tieneComprobante)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-image me-2"></i>Comprobante
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        @if($imgSrc)
                            <div class="product-image" style="max-width:280px;max-height:250px;">
                                <img src="{{ $imgSrc }}" 
                                    alt="Comprobante de gasto"
                                    class="img-thumbnail img-zoomable"
                                    style="max-width:280px;max-height:250px;cursor:pointer;"
                                    onclick="zoomImagen(this)"
                                    data-full-image="{{ $imgSrc }}"
                                    data-description="Comprobante de gasto - {{ $gasto->NumeroOperacion ?? '' }}">
                            </div>
                            <small class="text-muted">Archivo: {{ $gasto->UrlComprobante }}</small>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-image" style="font-size:3rem;opacity:0.3;"></i>
                                <p>No hay comprobante adjunto</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Modal para zoom de imagen --}}
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
<script>
    // ================================================
    // ZOOM DE IMAGEN
    // ================================================
    function zoomImagen(element) {
        const modal = new bootstrap.Modal(document.getElementById('modalZoomImagen'));
        const fullImage = element.dataset.fullImage || element.src;
        const description = element.dataset.description || 'Imagen';
        
        document.getElementById('imagenZoom').src = fullImage;
        document.getElementById('zoomDescripcion').textContent = description;
        modal.show();
    }
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .img-zoomable:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #modalZoomImagen .modal-content {
        background: transparent;
    }
</style>
@endpush