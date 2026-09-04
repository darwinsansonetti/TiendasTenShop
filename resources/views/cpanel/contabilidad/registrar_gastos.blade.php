@extends('layout.layout_dashboard')

@section('title', $modo == 'editar' ? 'Editar Gasto' : 'Registrar Gasto')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#ef4444,#dc2626)';
    $hdrIcon = $modo == 'editar' ? 'pencil' : 'cash-stack';
    $hdrTitle = $modo == 'editar' ? 'Editar Gasto' : 'Registrar Gasto';
    $hdrSubtitle = $modo == 'editar' ? 'Modificar los datos del gasto' : 'Registro de gastos y egresos';
    $isEdit = $modo == 'editar';
    $actionUrl = $isEdit ? route('cpanel.contabilidad.actualizar_gasto', $transaccion->ID ?? 0) : route('cpanel.contabilidad.guardar_gasto');
    $formMethod = $isEdit ? 'PUT' : 'POST';
    $buttonText = $isEdit ? 'Actualizar Gasto' : 'Guardar Gasto';
    
    $formasPago = [
        0 => 'Efectivo',
        1 => 'Cheque',
        2 => 'Depósito',
        3 => 'Transferencia',
        4 => 'Zelle',
        5 => 'Paypal',
        6 => 'Otro'
    ];
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
                    <li class="breadcrumb-item active" aria-current="page">{{ $hdrTitle }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-3">
            {{-- Formulario --}}
            <div class="col-md-{{ $isEdit ? '6' : '12' }}">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-white">
                                <i class="bi bi-{{ $hdrIcon }} me-2"></i>{{ $hdrTitle }}
                            </h6>
                            <div class="d-flex gap-2">
                                @if($isEdit)
                                    <a href="{{ route('cpanel.contabilidad.registrar_gastos') }}" 
                                       class="btn btn-sm fw-semibold text-white"
                                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Nuevo
                                    </a>
                                @endif
                                <a href="{{ route('cpanel.contabilidad.lista_gastos') }}" class="btn btn-sm fw-semibold text-white"
                                   style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;">
                                    <i class="bi bi-list-ul me-1"></i> Listado
                                </a>
                                @if($isEdit)
                                    <button type="button" class="btn btn-sm fw-semibold text-white"
                                            style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                            onclick="eliminarGasto({{ $transaccion->ID ?? 0 }})">
                                        <i class="bi bi-trash me-1"></i> Borrar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ $actionUrl }}" method="POST" id="formRegistrarGasto" enctype="multipart/form-data">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif
                            <input type="hidden" name="id" value="{{ $transaccion->ID ?? 0 }}">

                            <div class="row g-3">

                                {{-- Fecha --}}
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label fw-semibold">
                                        <i class="bi bi-calendar me-1" style="color:#ef4444;"></i>Fecha <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="fecha" id="fecha"
                                           class="form-control @error('fecha') is-invalid @enderror"
                                           value="{{ old('fecha', $transaccion->Fecha ?? Carbon::now()->format('Y-m-d')) }}" required>
                                    @error('fecha')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Sucursal --}}
                                <div class="col-md-6">
                                    <label for="sucursal_id" class="form-label fw-semibold">
                                        <i class="bi bi-building me-1" style="color:#ef4444;"></i>Sucursal <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select name="sucursal_id" id="sucursal_id"
                                                class="form-select @error('sucursal_id') is-invalid @enderror" required>
                                            <option value="">Seleccione una sucursal</option>
                                            @foreach($sucursales as $sucursal)
                                                <option value="{{ $sucursal->ID }}" 
                                                    {{ old('sucursal_id', $transaccion->SucursalId ?? '') == $sucursal->ID ? 'selected' : '' }}>
                                                    {{ $sucursal->Nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a href="#" class="btn btn-light border fw-semibold" title="Crear nueva sucursal" data-bs-toggle="tooltip">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    </div>
                                    @error('sucursal_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Categoría --}}
                                <div class="col-md-6">
                                    <label for="categoria_id" class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1" style="color:#ef4444;"></i>Categoría <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                            <option value="">Seleccione una categoría</option>
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->CategoriaId }}" 
                                                    {{ old('categoria_id', $transaccion->CategoriaId ?? '') == $categoria->CategoriaId ? 'selected' : '' }}>
                                                    {{ $categoria->Nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <a href="#" class="btn btn-light border fw-semibold" title="Crear nueva categoría" data-bs-toggle="tooltip">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    </div>
                                    @error('categoria_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Cédula --}}
                                <div class="col-md-6">
                                    <label for="cedula" class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1" style="color:#ef4444;"></i>Cédula
                                    </label>
                                    <input type="text" name="cedula" id="cedula"
                                           class="form-control @error('cedula') is-invalid @enderror"
                                           placeholder="Cédula de la persona"
                                           value="{{ old('cedula', $transaccion->Cedula ?? '') }}">
                                    @error('cedula')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Nombre --}}
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label fw-semibold">
                                        <i class="bi bi-person me-1" style="color:#ef4444;"></i>Nombre
                                    </label>
                                    <input type="text" name="nombre" id="nombre"
                                           class="form-control @error('nombre') is-invalid @enderror"
                                           placeholder="Nombre de la persona"
                                           value="{{ old('nombre', $transaccion->Nombre ?? '') }}">
                                    @error('nombre')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Tasa de Cambio --}}
                                <div class="col-md-6">
                                    <label for="tasa_cambio" class="form-label fw-semibold">
                                        <i class="bi bi-arrow-left-right me-1" style="color:#ef4444;"></i>Tasa de Cambio <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="tasa_cambio" id="tasa_cambio"
                                           class="form-control @error('tasa_cambio') is-invalid @enderror"
                                           step="0.01" min="0.01" required
                                           value="{{ old('tasa_cambio', $transaccion->TasaDeCambio ?? 0) }}">
                                    @error('tasa_cambio')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Monto en Divisas --}}
                                <div class="col-md-6">
                                    <label for="monto_divisa" class="form-label fw-semibold">
                                        <i class="bi bi-cash me-1" style="color:#ef4444;"></i>Divisas <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" name="monto_divisa" id="monto_divisa"
                                               class="form-control border-start-0 @error('monto_divisa') is-invalid @enderror"
                                               step="0.01" min="0.01" required
                                               placeholder="Monto en divisas"
                                               value="{{ old('monto_divisa', $transaccion->MontoDivisaAbonado ?? 0) }}">
                                    </div>
                                    @error('monto_divisa')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Monto en Bs --}}
                                <div class="col-md-6">
                                    <label for="monto_bs" class="form-label fw-semibold">
                                        <i class="bi bi-cash-stack me-1" style="color:#ef4444;"></i>Bs.
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">Bs.</span>
                                        <input type="number" name="monto_bs" id="monto_bs"
                                               class="form-control border-start-0 @error('monto_bs') is-invalid @enderror"
                                               step="0.01" min="0"
                                               placeholder="Monto en bolívares"
                                               value="{{ old('monto_bs', $transaccion->MontoAbonado ?? 0) }}">
                                    </div>
                                    @error('monto_bs')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Se calcula automáticamente según la tasa de cambio</div>
                                </div>

                                {{-- Forma de Pago --}}
                                <div class="col-md-6">
                                    <label for="forma_pago" class="form-label fw-semibold">
                                        <i class="bi bi-credit-card me-1" style="color:#ef4444;"></i>Forma de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select name="forma_pago" id="forma_pago"
                                            class="form-select @error('forma_pago') is-invalid @enderror" required>
                                        <option value="">Seleccione una forma de pago</option>
                                        @foreach($formasPago as $key => $label)
                                            <option value="{{ $key }}" 
                                                {{ old('forma_pago', $transaccion->FormaDePago ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('forma_pago')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Observación --}}
                                <div class="col-12">
                                    <label for="observacion" class="form-label fw-semibold">
                                        <i class="bi bi-chat-text me-1" style="color:#ef4444;"></i>Observación
                                    </label>
                                    <textarea name="observacion" id="observacion"
                                              class="form-control @error('observacion') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Escriba la observación...">{{ old('observacion', $transaccion->Observacion ?? '') }}</textarea>
                                    @error('observacion')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ================================================ --}}
                                {{-- COMPROBANTE --}}
                                {{-- ================================================ --}}
                                <div class="col-12">
                                    <label for="comprobante" class="form-label fw-semibold">
                                        <i class="bi bi-image me-1" style="color:#ef4444;"></i>Comprobante
                                    </label>
                                    <input type="file" name="comprobante" id="comprobante" 
                                           class="form-control @error('comprobante') is-invalid @enderror"
                                           accept="image/*,.pdf">
                                    @error('comprobante')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Formatos permitidos: JPG, JPEG, PNG, PDF (máx. 5MB)</div>
                                </div>

                            </div>

                            {{-- Botones --}}
                            <div class="mt-4 pt-2 d-flex gap-2" style="border-top:1px solid #f1f5f9;">
                                <button type="submit" class="btn px-4 fw-semibold text-white" style="background:{{ $hdrBg }};border:none;" id="btnGuardarGasto">
                                    <i class="bi bi-save me-1"></i> {{ $buttonText }}
                                </button>
                                <a href="{{ route('cpanel.contabilidad.lista_gastos') }}" class="btn btn-secondary px-4">
                                    <i class="bi bi-arrow-left me-1"></i> Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Comprobante actual (solo en edición) --}}
            @if($isEdit)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-image me-2"></i>Comprobante Actual
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mt-2">
                            @if(isset($transaccion->UrlComprobante) && $transaccion->UrlComprobante)
                                <div class="product-image" style="max-width:280px;max-height:250px;">
                                    <img src="{{ asset('storage/images/comprobantes/' . $transaccion->UrlComprobante) }}" 
                                        alt="Comprobante"
                                        class="img-thumbnail img-zoomable"
                                        style="max-width:280px;max-height:250px;cursor:pointer;"
                                        onclick="zoomImagen(this)"
                                        data-full-image="{{ asset('storage/images/comprobantes/' . $transaccion->UrlComprobante) }}"
                                        data-description="Comprobante de gasto - {{ $transaccion->NumeroOperacion ?? '' }}">
                                </div>
                                <small class="text-muted">Archivo: {{ $transaccion->UrlComprobante }}</small>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-image" style="font-size:3rem;opacity:0.3;"></i>
                                    <p>No hay comprobante adjunto</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Modal para zoom de imagen (solo en edición) --}}
@if($isEdit)
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
@endif

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        // CALCULAR MONTO EN BOLÍVARES AUTOMÁTICAMENTE
        // ================================================
        const montoDivisa = document.getElementById('monto_divisa');
        const montoBs = document.getElementById('monto_bs');
        const tasaCambio = document.getElementById('tasa_cambio');

        function calcularMontoBs() {
            const divisa = parseFloat(montoDivisa.value) || 0;
            const tasa = parseFloat(tasaCambio.value) || 0;
            
            if (tasa > 0 && divisa > 0) {
                montoBs.value = (divisa * tasa).toFixed(2);
            } else {
                montoBs.value = '';
            }
        }

        function calcularMontoDivisa() {
            const bs = parseFloat(montoBs.value) || 0;
            const tasa = parseFloat(tasaCambio.value) || 0;
            
            if (tasa > 0 && bs > 0) {
                montoDivisa.value = (bs / tasa).toFixed(2);
            }
        }

        if (montoDivisa && montoBs && tasaCambio) {
            montoDivisa.addEventListener('input', calcularMontoBs);
            montoBs.addEventListener('input', calcularMontoDivisa);
            tasaCambio.addEventListener('input', function() {
                if (montoDivisa.value) {
                    calcularMontoBs();
                }
                if (montoBs.value) {
                    calcularMontoDivisa();
                }
            });
        }

        // ================================================
        // DESHABILITAR BOTÓN AL ENVIAR
        // ================================================
        document.getElementById('formRegistrarGasto')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnGuardarGasto');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
            }
        });

        // ================================================
        // ELIMINAR GASTO (solo en edición)
        // ================================================
        @if($isEdit)
        function eliminarGasto(id) {
            Swal.fire({
                title: '¿Eliminar gasto?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('/cpanel/contabilidad/eliminar/gasto/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route('cpanel.contabilidad.lista_gastos') }}';
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error de conexión al servidor', 'error');
                    });
                }
            });
        }
        @endif

        // ================================================
        // MOSTRAR MENSAJES DE ÉXITO/ERROR
        // ================================================
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

        @if($errors->any())
            Swal.fire({
                title: 'Error de validación',
                text: '{{ $errors->first() }}',
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
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.15);
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