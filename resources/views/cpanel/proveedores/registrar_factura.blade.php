@extends('layout.layout_dashboard')

@section('title', 'Registrar Factura')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-file-plus text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Registrar Factura</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Nueva factura de proveedor</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    <li class="breadcrumb-item active">Registrar Factura</li>
                </ol>
            </div>
        </div>
    </div>
</div>

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
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4 align-items-center">
                    {{-- Imagen + badges --}}
                    <div class="col-md-2 text-center">
                        <img src="{{ $imgSrc }}"
                             alt="{{ $proveedor->Nombre }}"
                             class="rounded-circle"
                             style="width:90px;height:90px;object-fit:cover;border:3px solid #e2e8f0;">
                        <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap">
                            @if($proveedor->Estatus == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);font-size:0.72rem;">Activo</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);font-size:0.72rem;">Inactivo</span>
                            @endif
                            @if($proveedor->Tipo == 0)
                                <span class="badge rounded-pill"
                                      style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.72rem;">Mercancía</span>
                            @else
                                <span class="badge rounded-pill"
                                      style="background:rgba(6,182,212,0.1);color:#0891b2;border:1px solid rgba(6,182,212,0.25);font-size:0.72rem;">Servicio</span>
                            @endif
                        </div>
                    </div>
                    {{-- Datos del proveedor --}}
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Nombre</p>
                                <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">RIF / Cédula</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->Rif_Cedula ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Teléfono</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->TelefonoMovil ?: $proveedor->TelefonoFijo ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-4 col-6">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Correo</p>
                                <p class="mb-0 fw-semibold text-dark">{{ $proveedor->CorreoElectronico ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-8 col-12">
                                <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Dirección</p>
                                <p class="mb-0 fw-semibold text-dark">{{ \Illuminate\Support\Str::limit($proveedor->Direccion ?? 'N/A', 60) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD 2: FORMULARIO DE FACTURA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-file-text me-2"></i>Nueva Factura
                </h6>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('cpanel.facturas.guardar') }}" method="POST" id="formRegistrarFactura">
                    @csrf
                    <input type="hidden" name="proveedor_id" value="{{ $proveedor->ProveedorId }}">
                    <input type="hidden" name="tipo" value="{{ $proveedor->Tipo }}">

                    <div class="row g-3">

                        {{-- Contenedor --}}
                        <div class="col-md-6">
                            <label for="contenedor_id" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Contenedor
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-box-seam text-success"></i>
                                </span>
                                <select name="contenedor_id" id="contenedor_id" class="form-select border-start-0">
                                    <option value="0">Seleccione un valor</option>
                                    @foreach($contenedores as $contenedor)
                                        <option value="{{ $contenedor->Id }}"
                                                {{ old('contenedor_id') == $contenedor->Id ? 'selected' : '' }}>
                                            {{ $contenedor->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('cpanel.contenedores.crear') }}"
                                   class="btn btn-light border fw-semibold"
                                   title="Crear nuevo contenedor" data-bs-toggle="tooltip"
                                   style="font-size:0.82rem;">
                                    <i class="bi bi-plus-circle me-1"></i>Nuevo
                                </a>
                            </div>
                            <div class="form-text">Seleccione un contenedor existente o cree uno nuevo</div>
                        </div>

                        {{-- Switch: Sumar flete --}}
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="rounded-3 p-3 w-100" style="background:#f8fafc;border:1px solid #e2e8f0;margin-top:1.6rem;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           name="es_cargar_flete" id="es_cargar_flete"
                                           value="1" {{ old('es_cargar_flete') ? 'checked' : '' }}
                                           style="width:2.4em;height:1.3em;">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="es_cargar_flete">
                                        Sumar flete en Factura
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha de Creación --}}
                        <div class="col-md-4">
                            <label for="fecha_creacion" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Fecha <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar text-success"></i>
                                </span>
                                <input type="date" name="fecha_creacion" id="fecha_creacion"
                                       class="form-control border-start-0 @error('fecha_creacion') is-invalid @enderror"
                                       value="{{ old('fecha_creacion', date('Y-m-d')) }}" required>
                                @error('fecha_creacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Traspaso --}}
                        <div class="col-md-4">
                            <label for="traspaso" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Traspaso
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="traspaso" id="traspaso"
                                       class="form-control border-start-0"
                                       value="{{ old('traspaso', 0) }}">
                            </div>
                        </div>

                        {{-- Estatus --}}
                        <div class="col-md-4">
                            <label for="estatus" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Estatus <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-toggle-on text-success"></i>
                                </span>
                                <select name="estatus" id="estatus"
                                        class="form-select border-start-0 @error('estatus') is-invalid @enderror" required>
                                    <option value="" selected>Seleccione un valor</option>
                                    <option value="1" {{ old('estatus') == 1 ? 'selected' : '' }}>En Proceso</option>
                                    <option value="2" {{ old('estatus') == 2 ? 'selected' : '' }}>Recibiendo</option>
                                    <option value="4" {{ old('estatus') == 4 ? 'selected' : '' }}>Recibida</option>
                                    <option value="3" {{ old('estatus') == 3 ? 'selected' : '' }}>Pagada</option>
                                    <option value="0" {{ old('estatus') === '0' ? 'selected' : '' }}>Anulada</option>
                                </select>
                                @error('estatus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">El estatus "En Proceso" permite editar la factura</div>
                        </div>

                        {{-- Nota informativa --}}
                        <div class="col-12">
                            <div class="d-flex align-items-start gap-3 rounded-3 p-3"
                                 style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);margin-top:1px;">
                                    <i class="bi bi-info-circle text-white" style="font-size:0.85rem;"></i>
                                </div>
                                <p class="mb-0 text-dark" style="font-size:0.88rem;">
                                    Los detalles de la factura (productos) se podrán agregar después de crear la factura.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-success px-4 fw-semibold" id="btnGuardarFactura">
                            <i class="bi bi-save me-2"></i>Generar Factura
                        </button>
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                           class="btn btn-light border px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script>
    document.getElementById('formRegistrarFactura')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnGuardarFactura');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        }
    });
</script>
@endsection

@push('styles')
<style>
    .input-group-text { border-color: #dee2e6; }
    .form-control:focus, .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 0.2rem rgba(16,185,129,.15); }
</style>
@endpush
