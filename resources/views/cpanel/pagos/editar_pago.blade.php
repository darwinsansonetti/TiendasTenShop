@extends('layout.layout_dashboard')

@section('title', 'Editar Pago')

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
                  <i class="bi bi-pencil-square text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Pago</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Pago #{{ $pago->NumeroOperacion }}</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    @if($proveedor)
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}">
                            {{ $proveedor->Nombre }}
                        </a>
                    </li>
                    @endif
                    <li class="breadcrumb-item active">Editar Pago #{{ $pago->NumeroOperacion }}</li>
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
                        <i class="bi bi-cash-stack me-2"></i>Editar Pago
                    </h6>
                    <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}"
                       class="btn btn-light btn-sm fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </div>
            <div class="card-body pt-4">

                <form action="{{ route('cpanel.pagos.actualizar', $pago->ID) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Número de Operación --}}
                        <div class="col-md-6">
                            <label for="numero_operacion" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Número de Operación</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-hash" style="color:#0891b2;"></i>
                                </span>
                                <input type="text" name="numero_operacion" id="numero_operacion"
                                       class="form-control border-start-0"
                                       value="{{ old('numero_operacion', $pago->NumeroOperacion) }}">
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Fecha <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar" style="color:#7c3aed;"></i>
                                </span>
                                <input type="date" name="fecha" id="fecha"
                                       class="form-control border-start-0"
                                       value="{{ old('fecha', date('Y-m-d', strtotime($pago->Fecha))) }}"
                                       required>
                            </div>
                        </div>

                        {{-- Monto USD --}}
                        <div class="col-md-4">
                            <label for="monto_divisa" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Monto USD <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="monto_divisa" id="monto_divisa"
                                       class="form-control border-start-0"
                                       value="{{ old('monto_divisa', $pago->MontoDivisaAbonado) }}"
                                       required>
                            </div>
                        </div>

                        {{-- Tasa de Cambio --}}
                        <div class="col-md-4">
                            <label for="tasa_cambio" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Tasa de Cambio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-arrow-left-right" style="color:#d97706;"></i>
                                </span>
                                <input type="number" step="0.01" name="tasa_cambio" id="tasa_cambio"
                                       class="form-control border-start-0"
                                       value="{{ old('tasa_cambio', $pago->TasaDeCambio) }}"
                                       required>
                            </div>
                        </div>

                        {{-- Monto Bs (readonly, calculado) --}}
                        <div class="col-md-4">
                            <label for="monto_bs" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Monto Bs</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 fw-bold text-primary"
                                      style="background:#f1f5f9;">Bs</span>
                                <input type="number" step="0.01" name="monto_bs" id="monto_bs"
                                       class="form-control border-start-0"
                                       style="background:#f8fafc;"
                                       value="{{ old('monto_bs', $pago->MontoAbonado) }}"
                                       readonly>
                            </div>
                            <div class="form-text">Se calcula automáticamente</div>
                        </div>

                        {{-- Forma de Pago (solo lectura) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Forma de Pago</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#f1f5f9;">
                                    <i class="bi bi-credit-card text-muted"></i>
                                </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       style="background:#f8fafc;"
                                       value="{{ $formaPagoMap[$pago->FormaDePago] ?? 'Desconocido' }}"
                                       readonly disabled>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="2"
                                      class="form-control">{{ old('descripcion', $pago->Descripcion) }}</textarea>
                        </div>

                        {{-- Comprobante actual + Nuevo comprobante --}}
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Comprobante actual</label>
                            @if($pago->UrlComprobante)
                                @php
                                    $comprobanteSrc = FileHelper::getOrDownloadFile(
                                        'images/comprobantes/',
                                        $pago->UrlComprobante,
                                        'assets/img/adminlte/img/no-image.png'
                                    );
                                @endphp
                                <div class="d-flex align-items-center gap-3 rounded-3 p-3"
                                     style="background:#f5f3ff;border:1px solid #ddd6fe;">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                                        <i class="bi bi-file-earmark-image text-white" style="font-size:0.9rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold text-dark" style="font-size:0.85rem;">Comprobante adjunto</p>
                                    </div>
                                    <a href="{{ $comprobanteSrc }}" target="_blank"
                                       class="btn btn-sm rounded-2 fw-semibold flex-shrink-0"
                                       style="background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);font-size:0.8rem;">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                </div>
                            @else
                                <div class="rounded-3 p-3 text-muted"
                                     style="background:#f8fafc;border:1px dashed #cbd5e1;font-size:0.88rem;">
                                    <i class="bi bi-file-earmark-x me-2"></i>Sin comprobante asociado
                                </div>
                            @endif
                        </div>

                        <div class="col-md-7">
                            <label for="comprobante" class="form-label fw-semibold text-dark"
                                   style="font-size:0.85rem;">Nuevo Comprobante</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-upload" style="color:#7c3aed;"></i>
                                </span>
                                <input type="file" name="comprobante" id="comprobante"
                                       class="form-control border-start-0"
                                       accept="image/*,.pdf">
                            </div>
                            <div class="form-text">JPG, PNG, PDF (máx. 5 MB). Reemplazará al comprobante anterior.</div>
                        </div>

                        {{-- Aviso importante --}}
                        <div class="col-12">
                            <div class="d-flex align-items-start gap-3 rounded-3 p-3"
                                 style="background:#fffbeb;border:1px solid #fde68a;">
                                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;background:linear-gradient(135deg,#f59e0b,#d97706);margin-top:1px;">
                                    <i class="bi bi-exclamation-triangle text-white" style="font-size:0.85rem;"></i>
                                </div>
                                <p class="mb-0 text-dark" style="font-size:0.88rem;">
                                    <strong>Importante:</strong> Al modificar el monto o la tasa de cambio,
                                    el pago se redistribuirá automáticamente entre las facturas pendientes del proveedor.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 mt-4 pt-2" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-success px-4 fw-semibold">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}"
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
    const montoDivisaInput = document.getElementById('monto_divisa');
    const tasaCambioInput  = document.getElementById('tasa_cambio');
    const montoBsInput     = document.getElementById('monto_bs');

    function calcularMontoBs() {
        const montoDivisa = parseFloat(montoDivisaInput.value) || 0;
        const tasaCambio  = parseFloat(tasaCambioInput.value)  || 0;
        const montoBs     = montoDivisa * tasaCambio;
        montoBsInput.value = montoBs.toFixed(2);
    }

    montoDivisaInput.addEventListener('keyup',  calcularMontoBs);
    montoDivisaInput.addEventListener('change', calcularMontoBs);
    tasaCambioInput.addEventListener('keyup',   calcularMontoBs);
    tasaCambioInput.addEventListener('change',  calcularMontoBs);

    calcularMontoBs();
</script>
@endsection

@push('styles')
<style>
    .input-group-text { border-color: #dee2e6; }
    .form-control:focus, .form-select:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 0.2rem rgba(139,92,246,.15);
    }
</style>
@endpush
