@extends('layout.layout_dashboard')

@section('title', 'Banners')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)'; // Azul para banners
    $hdrIcon = 'images';
    $hdrTitle = 'Gestión de Banners';
    $hdrSubtitle = 'Administra las imágenes del carrusel principal';
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
                    <li class="breadcrumb-item"><a href="#">Diseño</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Banners</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Listado de Banners
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#0369a1;">
                            {{ $banners->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" 
                                class="btn btn-sm fw-semibold text-white"
                                style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                data-bs-toggle="modal" data-bs-target="#modalCrearBanner">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Banner
                        </button>
                        
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaBanners">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">
                                    IMAGEN
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    ESTATUS
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:150px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                            @php
                                $urlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $banner->UrlFoto ?? '',
                                    'assets/img/products/product_placeholder_square_medium.jpg'
                                );
                            @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4 text-center">
                                        <img src="{{ $urlImagen }}"
                                                alt="Banner"
                                                class="rounded img-zoomable"
                                                style="width:120px;height:60px;object-fit:cover;border:1px solid #e2e8f0;cursor:pointer;background:#fff;padding:2px;"
                                                onclick="zoomImagen(this)"
                                                data-full-image="{{ $urlImagen }}"
                                                data-description="Banner #{{ $banner->Id }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $banner->EsActivo ? 'success' : 'secondary' }}" style="font-size:0.75rem;">
                                            {{ $banner->EsActivo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            {{-- Botón Editar (Abre Modal) --}}
                                            <button type="button" 
                                                    class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                    title="Editar" data-bs-toggle="tooltip"
                                                    onclick="abrirModalEditar({{ $banner->Id }}, {{ $banner->EsActivo ? 'true' : 'false' }})">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </button>

                                            {{-- Botón Activar/Desactivar --}}
                                            <button type="button"
                                                    class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba({{ $banner->EsActivo ? '239,68,68' : '20,184,166' }},0.1);color:{{ $banner->EsActivo ? '#dc2626' : '#0d9488' }};border:1px solid rgba({{ $banner->EsActivo ? '239,68,68' : '20,184,166' }},0.25);"
                                                    title="{{ $banner->EsActivo ? 'Desactivar' : 'Activar' }}" data-bs-toggle="tooltip"
                                                    onclick="toggleBanner({{ $banner->Id }}, {{ $banner->EsActivo ? 'true' : 'false' }})">
                                                <i class="bi bi-{{ $banner->EsActivo ? 'pause-circle' : 'play-circle' }}" style="font-size:0.8rem;"></i>
                                            </button>

                                            {{-- Botón Eliminar --}}
                                            <form action="{{ route('cpanel.banner.destroy', $banner->Id) }}" method="POST" class="d-inline m-0"
                                                  onsubmit="confirmarEliminar(event, this);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width:30px;height:30px;background:rgba(107,114,128,0.1);color:#4b5563;border:1px solid rgba(107,114,128,0.25);"
                                                        title="Eliminar" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox me-2"></i>
                                        No hay banners registrados
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
                        <i class="bi bi-images me-1"></i>
                        {{ $banners->count() }} banner{{ $banners->count() != 1 ? 's' : '' }}
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

{{-- MODAL CREAR BANNER --}}
<div class="modal fade" id="modalCrearBanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('cpanel.banner.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 text-white" style="background:{{ $hdrBg }};">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Nuevo Banner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imagen (1920 x 782 px) *</label>
                        <input type="file" name="imagen" class="form-control" required accept="image/*">
                        <small class="text-muted" style="font-size:0.75rem;">Resolución recomendada para el carrusel principal.</small>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="es_activo" id="esActivoCrear">
                        <label class="form-check-label fw-semibold" for="esActivoCrear">Activar inmediatamente</label>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR BANNER --}}
<div class="modal fade" id="modalEditarBanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 text-white" style="background:{{ $hdrBg }};">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Editar Banner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nueva Imagen (Opcional)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                        <small class="text-muted" style="font-size:0.75rem;">Si no subes nada, se mantendrá la actual.</small>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="es_activo" id="edit_es_activo">
                        <label class="form-check-label fw-semibold" for="edit_es_activo">Activar este banner</label>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Guardar Cambios</button>
                </div>
            </form>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function zoomImagen(element) {
        const modal = new bootstrap.Modal(document.getElementById('modalZoomImagen'));
        const fullImage = element.dataset.fullImage || element.src;
        const description = element.dataset.description || 'Imagen';
        document.getElementById('imagenZoom').src = fullImage;
        document.getElementById('zoomDescripcion').textContent = description;
        modal.show();
    }

    function abrirModalEditar(id, esActivo) {
        document.getElementById('formEditar').action = `{{ url('cpanel/banner') }}/${id}`;
        document.getElementById('edit_es_activo').checked = esActivo;
        const modal = new bootstrap.Modal(document.getElementById('modalEditarBanner'));
        modal.show();
    }

    function toggleBanner(id, estaActivo) {
        Swal.fire({
            title: estaActivo ? '¿Desactivar banner?' : '¿Activar banner?',
            text: estaActivo ? "El banner dejará de mostrarse en el Landing." : "Este banner se mostrará en el Landing.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: estaActivo ? '#dc2626' : '#0d9488',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('cpanel/banner') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('¡Hecho!', data.message, 'success').then(() => { location.reload(); });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => { Swal.fire('Error', 'Error de conexión', 'error'); });
            }
        });
    }

    function confirmarEliminar(event, form) {
        event.preventDefault();
        Swal.fire({
            title: '¿Eliminar banner?',
            text: "Esta acción no se puede deshacer.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { form.submit(); }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    .table-responsive thead th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    #modalZoomImagen .modal-content { background: transparent; }
    .form-switch .form-check-input:checked { background-color: #0ea5e9; border-color: #0ea5e9; }
</style>
@endpush