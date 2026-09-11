@extends('layout.layout_dashboard')

@section('title', 'Crear Banco')

@php
    $hdrBg = 'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)';
    $hdrIcon = 'plus-circle';
    $hdrTitle = 'Crear Banco';
    $hdrSubtitle = 'Registrar una nueva entidad bancaria';
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
                    <li class="breadcrumb-item active" aria-current="page">Crear</li>
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
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Banco
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.bancos.guardar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">

                        {{-- Nombre --}}
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">
                                <i class="bi bi-bank me-1" style="color:#3b82f6;"></i>Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   placeholder="Nombre del banco"
                                   value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estatus --}}
                        <div class="col-md-6">
                            <label for="es_activo" class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1" style="color:#3b82f6;"></i>Estatus
                            </label>
                            <select name="es_activo" id="es_activo" class="form-select @error('es_activo') is-invalid @enderror">
                                <option value="1" {{ old('es_activo', 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('es_activo') == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('es_activo')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Logo --}}
                        <div class="col-md-12">
                            <label for="logo" class="form-label fw-semibold">
                                <i class="bi bi-image me-1" style="color:#3b82f6;"></i>Logo
                            </label>
                            <div class="mb-3">
                                <div class="image-preview-container">
                                    <img src="{{ asset('assets/img/bancos/sinbanca.png') }}" 
                                         class="img-fluid rounded mb-3 border border-primary shadow img-zoomable"
                                         style="width: 150px; height: 150px; object-fit: contain; background: #fff; padding: 10px;"
                                         id="previewLogo"
                                         onclick="zoomImagen(this)"
                                         data-full-image="{{ asset('assets/img/bancos/sinbanca.png') }}"
                                         data-description="Vista previa del logo">
                                </div>
                                
                                <div>
                                    <label for="logo" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i>Seleccionar logo
                                    </label>
                                    <input type="file" 
                                           class="d-none" 
                                           id="logo" 
                                           name="logo"
                                           accept="image/*">
                                </div>
                                <small class="text-muted d-block mt-2">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                                @error('logo')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="mt-4 pt-2 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-save me-1"></i> Guardar Banco
                        </button>
                        <a href="{{ route('cpanel.bancos.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
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
    document.addEventListener("DOMContentLoaded", function() {
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

        // ================================================
        // PREVISUALIZACIÓN DE LOGO
        // ================================================
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewLogo');
                    preview.src = e.target.result;
                    preview.dataset.fullImage = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Simular click en el input file
        document.querySelector('label[for="logo"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('logo').click();
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
    }
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