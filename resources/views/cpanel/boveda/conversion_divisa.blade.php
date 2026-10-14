@extends('layout.layout_dashboard')

@section('title', 'Conversión Divisa')

@php
    use Carbon\Carbon;

    $hdrBg       = 'linear-gradient(135deg,#10b981 0%,#059669 100%)';
    $hdrIcon     = 'arrow-left-right';
    $hdrTitle    = 'Conversión Divisa';
    $hdrSubtitle = 'Compra de divisas con saldo disponible en Bs.';
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
                    <li class="breadcrumb-item">Billetera</li>
                    <li class="breadcrumb-item active">Conversión Divisa</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD: DISPONIBLE --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-2" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                    <i class="bi bi-wallet2 me-2"></i>Disponible para Conversión
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border" style="background:#eff6ff;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block" style="font-size:0.7rem;">PDV DEPOSITADO</small>
                                <p class="fw-bold text-primary mb-0" style="font-size:0.95rem;">
                                    Bs. {{ number_format($disponible['pdv'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border" style="background:#f0fdf4;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block" style="font-size:0.7rem;">BIOPAGO</small>
                                <p class="fw-bold text-success mb-0" style="font-size:0.95rem;">
                                    Bs. {{ number_format($disponible['biopago'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border" style="background:#fef3c7;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block" style="font-size:0.7rem;">CASHEA</small>
                                <p class="fw-bold text-warning mb-0" style="font-size:0.95rem;">
                                    Bs. {{ number_format($disponible['cashea'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border" style="background:#fef2f2;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted d-block" style="font-size:0.7rem;">GASTOS</small>
                                <p class="fw-bold text-danger mb-0" style="font-size:0.95rem;">
                                    − Bs. {{ number_format($disponible['gastos'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Ingresos:</small>
                        <span class="fw-semibold">Bs. {{ number_format($disponible['ingresos'], 2) }}</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">DISPONIBLE:</small>
                        <h3 class="fw-bold text-success mb-0" style="font-size:1.6rem;">
                            Bs. {{ number_format($disponible['disponible'], 2) }}
                        </h3>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0 d-flex align-items-start" role="alert">
                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                    <div>
                        <strong>¿Cómo se calcula el disponible?</strong>
                        <p class="mb-1 mt-1" style="font-size:0.85rem;">
                            <strong>Ingresos:</strong> PDV depositado + Biopago depositado + Cashea depositado.
                        </p>
                        <p class="mb-0" style="font-size:0.85rem;">
                            <strong>Se restan:</strong> gastos operativos (tipos 2, 3, 5) que salieron del banco (transferencias, depósitos, cheques).
                        </p>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-x-circle me-1"></i>
                            <strong>No se consideran:</strong>
                            los pagos a proveedores de mercancía (capital de trabajo)
                            ni los gastos pagados en efectivo (salen de la bóveda física, no del banco).
                        </small>
                    </div>
                </div>

                @if($disponible['disponible'] <= 0)
                <div class="alert alert-warning mt-3 mb-0 d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>No hay disponible para conversión.</strong>
                        El saldo actual es 0 o negativo. No se puede realizar ninguna operación.
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- FORMULARIO DE CONVERSIÓN --}}
        {{-- ================================================ --}}
        <form action="{{ route('cpanel.billetera.conversion.divisa.guardar') }}" method="POST" id="formConversion">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-2" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                        <i class="bi bi-cash-coin me-2"></i>Datos de la Conversión
                    </h6>
                </div>
                <div class="card-body">

                    {{-- Tasa --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="tasa" class="form-label fw-semibold">
                                <i class="bi bi-arrow-left-right me-1" style="color:#10b981;"></i>
                                Tasa de Compra (Bs/$) <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="tasa" 
                                   id="tasa" 
                                   class="form-control fw-bold" 
                                   step="0.01" 
                                   min="0.01"
                                   value="{{ old('tasa', $tasaActual) }}"
                                   required>
                            <small class="text-muted">
                                Tasa a la que se está comprando la divisa.
                                Tasa oficial actual: <strong>Bs. {{ number_format($tasaActual, 2) }}</strong>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-building me-1" style="color:#10b981;"></i>
                                Sucursal
                            </label>
                            <p class="form-control bg-light mb-0 fw-bold text-dark">
                                Oficina Principal
                            </p>
                        </div>
                    </div>

                    {{-- Denominaciones USD --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-currency-dollar me-1" style="color:#10b981;"></i>
                                Denominaciones a Recibir (USD)
                            </label>
                            <div class="row g-2">
                                @foreach($denominaciones as $denIndex => $denominacion)
                                <div class="col-md-2 col-4">
                                    <div class="card border">
                                        <div class="card-body p-2 text-center">
                                            <label class="fw-bold text-success d-block" style="font-size:0.75rem;">
                                                $ {{ number_format($denominacion, 0) }}
                                            </label>
                                            <input type="hidden"
                                                   name="denominaciones[{{ $denIndex }}][denominacion]"
                                                   value="{{ $denominacion }}">
                                            <input type="number"
                                                   name="denominaciones[{{ $denIndex }}][cantidad]"
                                                   class="form-control form-control-sm text-center cantidad-divisa"
                                                   data-denominacion="{{ $denominacion }}"
                                                   min="0"
                                                   step="1"
                                                   value="{{ old('denominaciones.' . $denIndex . '.cantidad', 0) }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Resumen de la operación --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border" style="background:#f0fdf4;">
                                <div class="card-body p-3">
                                    <small class="text-muted d-block">TOTAL A RECIBIR</small>
                                    <h4 class="fw-bold text-success mb-0" id="totalUSD">$ 0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border" style="background:#fef3c7;">
                                <div class="card-body p-3">
                                    <small class="text-muted d-block">TOTAL A ENTREGAR EN Bs.</small>
                                    <h4 class="fw-bold text-warning mb-0" id="totalBs">Bs. 0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border" style="background:#eff6ff;">
                                <div class="card-body p-3">
                                    <small class="text-muted d-block">DISPONIBLE RESTANTE</small>
                                    <h4 class="fw-bold text-primary mb-0" id="disponibleRestante">
                                        Bs. {{ number_format($disponible['disponible'], 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alerta de exceso --}}
                    <div class="alert alert-danger mt-3 mb-0 d-none" id="alertExceso">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>El monto excede el disponible.</strong>
                        <span id="mensajeExceso"></span>
                    </div>

                    {{-- Observación --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="observacion" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1" style="color:#10b981;"></i>
                                Observación
                            </label>
                            <textarea name="observacion" 
                                      id="observacion" 
                                      class="form-control" 
                                      rows="2"
                                      maxlength="500"
                                      placeholder="Observaciones de la operación">{{ old('observacion') }}</textarea>
                        </div>
                    </div>

                    {{-- Hidden: disponible --}}
                    <input type="hidden" id="disponibleTotal" value="{{ $disponible['disponible'] }}">

                </div>
            </div>

            {{-- Botones --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="submit" 
                                class="btn px-4 fw-semibold text-white" 
                                style="background:{{ $hdrBg }};border:none;"
                                id="btnGuardar"
                                {{ $disponible['disponible'] <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle me-1"></i> Registrar Conversión
                        </button>
                        <a href="{{ route('cpanel.dashboard') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>

        {{-- ================================================ --}}
        {{-- HISTORIAL --}}
        {{-- ================================================ --}}
        @if($historial->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-2" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white" style="font-size:0.85rem;">
                    <i class="bi bi-clock-history me-2"></i>Últimas Conversiones
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">ID</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">FECHA</th>
                                <th class="py-2 text-end text-muted fw-semibold" style="font-size:0.75rem;">TOTAL USD</th>
                                <th class="py-2 text-muted fw-semibold" style="font-size:0.75rem;">DENOMINACIONES</th>
                                <th class="pe-4 py-2 text-muted fw-semibold" style="font-size:0.75rem;">OBSERVACIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historial as $conv)
                            <tr>
                                <td class="ps-4">
                                    <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;color:#10b981;font-size:0.75rem;font-weight:bold;">
                                        #{{ $conv->CambioId }}
                                    </code>
                                </td>
                                <td>{{ $conv->FechaFormateada }}</td>
                                <td class="text-end fw-bold text-success">
                                    $ {{ number_format($conv->TotalUSD, 2) }}
                                </td>
                                <td>
                                    @foreach($conv->Detalles as $d)
                                        <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:0.7rem;">
                                            {{ $d->Cantidad }} × ${{ number_format($d->Denominacion, 0) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="pe-4">
                                    <small class="text-muted">{{ $conv->Observacion ?? '—' }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const disponibleTotal = parseFloat(document.getElementById('disponibleTotal').value) || 0;
    const inputTasa       = document.getElementById('tasa');
    const elTotalUSD      = document.getElementById('totalUSD');
    const elTotalBs       = document.getElementById('totalBs');
    const elDisponible    = document.getElementById('disponibleRestante');
    const alertExceso     = document.getElementById('alertExceso');
    const mensajeExceso   = document.getElementById('mensajeExceso');
    const btnGuardar      = document.getElementById('btnGuardar');

    // ============================================
    // FORMATEO DE MONTOS
    // ============================================
    function formatearMonto(n, decimales = 2) {
        return n.toLocaleString('es-VE', {
            minimumFractionDigits: decimales,
            maximumFractionDigits: decimales
        });
    }

    // ============================================
    // CÁLCULO EN VIVO
    // ============================================
    function calcular() {
        const tasa = parseFloat(inputTasa.value) || 0;

        // Sumar denominaciones
        let totalUSD = 0;
        document.querySelectorAll('.cantidad-divisa').forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            const denom    = parseFloat(input.dataset.denominacion) || 0;
            totalUSD += cantidad * denom;
        });

        const totalBs = totalUSD * tasa;

        // Actualizar display
        elTotalUSD.textContent = '$ ' + formatearMonto(totalUSD);
        elTotalBs.textContent  = 'Bs. ' + formatearMonto(totalBs);

        // Disponible restante
        const restante = disponibleTotal - totalBs;
        elDisponible.textContent = 'Bs. ' + formatearMonto(Math.max(0, restante));

        // Validar exceso
        if (totalBs > disponibleTotal) {
            alertExceso.classList.remove('d-none');
            mensajeExceso.textContent = 'Excede en Bs. ' + formatearMonto(totalBs - disponibleTotal);
            elDisponible.className = 'fw-bold mb-0 text-danger';
            if (btnGuardar) btnGuardar.disabled = true;
        } else {
            alertExceso.classList.add('d-none');
            elDisponible.className = 'fw-bold mb-0 text-primary';
            if (btnGuardar && disponibleTotal > 0) btnGuardar.disabled = false;
        }

        // Validar que haya al menos 1 USD
        if (totalUSD <= 0) {
            if (btnGuardar) btnGuardar.disabled = true;
        }
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    inputTasa.addEventListener('input', calcular);
    document.querySelectorAll('.cantidad-divisa').forEach(i => {
        i.addEventListener('input', calcular);
    });

    // Inicializar
    calcular();

    // ============================================
    // SUBMIT
    // ============================================
    document.getElementById('formConversion').addEventListener('submit', function(e) {
        // Validar de nuevo
        const tasa = parseFloat(inputTasa.value) || 0;
        let totalUSD = 0;
        document.querySelectorAll('.cantidad-divisa').forEach(input => {
            totalUSD += (parseInt(input.value) || 0) * (parseFloat(input.dataset.denominacion) || 0);
        });

        if (totalUSD <= 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe ingresar al menos una denominación con cantidad mayor a 0.', 'warning');
            return;
        }

        if (tasa <= 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe ingresar una tasa mayor a 0.', 'warning');
            return;
        }

        if (totalUSD * tasa > disponibleTotal) {
            e.preventDefault();
            Swal.fire('Error', 'El monto excede el disponible.', 'error');
            return;
        }

        // Confirmación
        e.preventDefault();
        const totalBs = totalUSD * tasa;

        Swal.fire({
            title: '¿Confirmar conversión?',
            html: `
                <p>Vas a convertir:</p>
                <h3 style="color:#10b981;">$ ${formatearMonto(totalUSD)}</h3>
                <p>Pagando:</p>
                <h4 style="color:#f59e0b;">Bs. ${formatearMonto(totalBs)}</h4>
                <p style="font-size:0.85rem;color:#666;">Tasa: ${formatearMonto(tasa)} Bs/$</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, convertir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Procesando...';
                document.getElementById('formConversion').submit();
            }
        });
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
    .form-label { font-size: 0.85rem; }
    .form-control:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
    }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endpush