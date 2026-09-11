@extends('layout.layout_dashboard')

@section('title', 'Detalle del Banco')

@php
    $hdrBg = 'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)';
    $hdrIcon = 'eye';
    $hdrTitle = 'Detalle del Banco';
    $hdrSubtitle = 'Información de la entidad bancaria';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.bancos.index') }}">Bancos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-info-circle me-2"></i>Información del Banco
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <img src="{{ $banco->LogoUrl }}"
                             alt="{{ $banco->Nombre ?? 'Banco' }}"
                             class="img-fluid rounded border p-3 img-zoomable"
                             style="max-width:200px;max-height:200px;object-fit:contain;background:#fff;cursor:pointer;"
                             onclick="zoomImagen(this)"
                             data-full-image="{{ $banco->LogoUrl }}"
                             data-description="{{ $banco->Nombre ?? 'Banco' }}"
                             onerror="this.src='{{ asset('assets/img/bancos/sinbanca.png') }}'">
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">ID</p>
                                <p class="fw-bold text-dark">
                                    <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#1d4ed8;">
                                        {{ $banco->ID }}
                                    </code>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Estatus</p>
                                <p>
                                    <span class="badge bg-{{ $banco->EstatusBadge }}" style="font-size:0.9rem;">
                                        {{ $banco->EstatusTexto }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Nombre</p>
                                <p class="fw-bold text-dark fs-5">{{ $banco->Nombre ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted mb-1" style="font-size:0.75rem;">Logo</p>
                                <p class="fw-bold text-dark">{{ $banco->Logo ?? 'Sin logo' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                    <a href="{{ route('cpanel.bancos.index') }}" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <a href="{{ route('cpanel.bancos.editar', $banco->ID) }}" 
                       class="btn px-4 fw-semibold text-white" 
                       style="background:{{ $hdrBg }};border:none;">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                </div>
            </div>
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
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #modalZoomImagen .modal-content {
        background: transparent;
    }
</style>
@endpush