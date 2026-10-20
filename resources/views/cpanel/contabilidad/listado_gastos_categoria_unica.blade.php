@extends('layout.layout_dashboard')

@section('title', 'Categorías de Gastos')

@php
    $hdrBg       = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon     = 'tags';
    $hdrTitle    = 'Categorías de Gastos';
    $hdrSubtitle = 'Gestión de categorías activas';
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
                    <li class="breadcrumb-item"><a href="#">Contabilidad</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorías de Gastos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- TABLA DE CATEGORÍAS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>
                        Listado de Categorías
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#dc2626;">
                            {{ $categorias->count() }}
                        </span>
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cpanel.contabilidad.categoria.crear') }}" 
                        class="btn btn-sm fw-semibold text-white"
                        style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                            <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
                        </a>
                        <span class="badge bg-white text-dark" style="font-size:0.7rem;align-self:center;">
                            <i class="bi bi-calendar-range me-1"></i>
                            {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaCategorias">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">
                                    NOMBRE
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:120px;">
                                    ESTATUS
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:130px;">
                                    ACCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categorias as $cat)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span class="fw-semibold text-dark">{{ $cat->Nombre }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success" style="font-size:0.75rem;">
                                            Activa
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('cpanel.contabilidad.categoria.editar', $cat->CategoriaId) }}" 
                                            class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                            style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                            title="Editar categoría" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox me-2"></i>
                                        No hay categorías activas registradas
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
                        <i class="bi bi-tags me-1"></i>
                        {{ $categorias->count() }} categoría{{ $categorias->count() != 1 ? 's' : '' }} activa{{ $categorias->count() != 1 ? 's' : '' }}
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // TOOLTIPS
    // ============================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ============================================
    // MENSAJES FLASH
    // ============================================
    @if(session('success'))
        Swal.fire({
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Error',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
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