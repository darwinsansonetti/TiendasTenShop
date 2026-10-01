@extends('layout.layout_dashboard')

@section('title', 'Configuración Pago Móvil')

@php
    $hdrBg = 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)';
    $hdrIcon = 'credit-card';
    $hdrTitle = 'Configuración Pago Móvil';
    $hdrSubtitle = 'Pago Móvil del Banco de Venezuela por sucursal';
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
                    <li class="breadcrumb-item active">Configuración Pago Móvil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>Configuraciones Registradas
                    </h6>
                    <a href="{{ route('cpanel.configuracion.pago.movil.create') }}"
                       class="btn btn-light btn-sm fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i>Nueva Configuración
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body py-3 border-bottom">
                <form method="GET" action="{{ route('cpanel.configuracion.pago.movil') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Sucursal</label>
                        <select name="sucursal_id" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->ID }}" {{ $sucursalId == $suc->ID ? 'selected' : '' }}>
                                    {{ $suc->Nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:0.8rem;">Estado</label>
                        <select name="activo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ $activo === '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ $activo === '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-sm text-white" style="background:{{ $hdrBg }};border:none;">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('cpanel.configuracion.pago.movil') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;">ID</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">SUCURSAL</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">ALIAS</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">RIF</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">TELÉFONO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">API KEY</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">ESTADO</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($configuraciones as $cfg)
                            <tr>
                                <td class="ps-4">
                                    <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#0369a1;font-size:0.75rem;font-weight:bold;">
                                        #{{ $cfg->SucursalPagoMovilId }}
                                    </code>
                                </td>
                                <td>{{ $cfg->sucursal_nombre ?? 'N/A' }}</td>
                                <td><span class="fw-semibold">{{ $cfg->Alias }}</span></td>
                                <td>{{ $cfg->Rif }}</td>
                                <td>{{ $cfg->Telefono }}</td>
                                <td>
                                    <code style="font-size:0.72rem;">
                                        {{ \Illuminate\Support\Str::limit($cfg->ApiKey, 12, '...') }}
                                    </code>
                                </td>
                                <td class="text-center">
                                    @if($cfg->Activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('cpanel.configuracion.pago.movil.edit', $cfg->SucursalPagoMovilId) }}"
                                           class="btn btn-sm"
                                           style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                           title="Editar">
                                            <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                                        </a>
                                        <form action="{{ route('cpanel.configuracion.pago.movil.toggle', $cfg->SucursalPagoMovilId) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm"
                                                    style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                                    title="{{ $cfg->Activo ? 'Desactivar' : 'Activar' }}">
                                                <i class="bi bi-{{ $cfg->Activo ? 'toggle-on' : 'toggle-off' }}" style="font-size:0.75rem;"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('cpanel.configuracion.pago.movil.destroy', $cfg->SucursalPagoMovilId) }}"
                                              method="POST" class="d-inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-delete"
                                                    style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                    title="Eliminar">
                                                <i class="bi bi-trash" style="font-size:0.75rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay configuraciones registradas
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        Swal.fire({
            title: '¿Eliminar configuración?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush