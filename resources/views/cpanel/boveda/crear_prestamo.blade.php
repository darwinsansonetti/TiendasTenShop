@extends('layout.layout_dashboard')

@section('title', 'Nuevo Préstamo a Sucursal')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)';
    $hdrIcon = 'plus-circle';
    $hdrTitle = 'Nuevo Préstamo a Sucursal';
    $hdrSubtitle = 'Registrar préstamo de bóveda';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.boveda.prestamos') }}">Préstamos</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <form action="{{ route('cpanel.boveda.guardar_prestamo') }}" method="POST" id="formPrestamo">
            @csrf

            {{-- INFORMACIÓN GENERAL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Datos del Préstamo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sucursal_id" class="form-label fw-semibold">
                                <i class="bi bi-building me-1" style="color:#d97706;"></i>Sucursal <span class="text-danger">*</span>
                            </label>
                            <select name="sucursal_id" id="sucursal_id" 
                                    class="form-select @error('sucursal_id') is-invalid @enderror" required>
                                <option value="">Seleccione una sucursal</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->ID }}" {{ old('sucursal_id') == $sucursal->ID ? 'selected' : '' }}>
                                        {{ $sucursal->Nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sucursal_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_prestamo" class="form-label fw-semibold">
                                <i class="bi bi-calendar me-1" style="color:#d97706;"></i>Fecha del Préstamo <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha_prestamo" id="fecha_prestamo"
                                   class="form-control @error('fecha_prestamo') is-invalid @enderror"
                                   value="{{ old('fecha_prestamo', Carbon::now()->format('Y-m-d')) }}" required>
                            @error('fecha_prestamo')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_moneda" class="form-label fw-semibold">
                                <i class="bi bi-currency-exchange me-1" style="color:#d97706;"></i>Tipo de Moneda <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_moneda" id="tipo_moneda" class="form-select" required>
                                <option value="0" {{ old('tipo_moneda', 0) == 0 ? 'selected' : '' }}>Divisas (USD)</option>
                                <option value="1" {{ old('tipo_moneda') == 1 ? 'selected' : '' }}>Bolívares (Bs.)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-arrow-left-right me-1" style="color:#d97706;"></i>Tasa de Cambio
                            </label>
                            <p class="fw-bold text-dark fs-5 mb-0">Bs. {{ number_format($tasaCambio ?? 0, 2) }}</p>
                        </div>

                        <div class="col-12">
                            <label for="observacion" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1" style="color:#d97706;"></i>Observación
                            </label>
                            <textarea name="observacion" id="observacion" class="form-control" rows="2"
                                      placeholder="Motivo del préstamo (ej: cambio para cliente)">{{ old('observacion') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DENOMINACIONES --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-cash-stack me-2"></i>Denominaciones Entregadas
                    </h6>
                </div>
                <div class="card-body">

                    {{-- DENOMINACIONES DIVISAS --}}
                    <div id="seccionDivisas">
                        <h6 class="fw-bold text-success mb-2" style="font-size:0.9rem;">
                            <i class="bi bi-cash me-1"></i>Divisas (USD)
                        </h6>
                        <div class="row g-2 mb-4">
                            @foreach($denominacionesDivisa as $index => $denominacion)
                            <div class="col-md-2 col-4">
                                <div class="card border">
                                    <div class="card-body p-2 text-center">
                                        <label class="fw-bold text-success d-block" style="font-size:0.8rem;">
                                            $ {{ number_format($denominacion, 2) }}
                                        </label>
                                        <input type="hidden" 
                                               name="denominaciones[divisa_{{ $index }}][denominacion]" 
                                               value="{{ $denominacion }}">
                                        <input type="number" 
                                               name="denominaciones[divisa_{{ $index }}][cantidad]" 
                                               class="form-control form-control-sm text-center cantidad-divisa"
                                               data-denominacion="{{ $denominacion }}"
                                               data-tipo="divisa"
                                               min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-2 col-4">
                                <div class="card border h-100" style="background:#f0fdf4;">
                                    <div class="card-body p-2 text-center">
                                        <label class="fw-bold text-success d-block" style="font-size:0.7rem;">TOTAL USD</label>
                                        <p class="h6 fw-bold text-success mb-0" id="totalDenominacionesDivisa">$ 0.00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DENOMINACIONES BOLÍVARES --}}
                    <div id="seccionBs">
                        <h6 class="fw-bold text-primary mb-2" style="font-size:0.9rem;">
                            <i class="bi bi-cash-stack me-1"></i>Bolívares (Bs.)
                        </h6>
                        <div class="row g-2">
                            @foreach($denominacionesBs as $index => $denominacion)
                            <div class="col-md-2 col-4">
                                <div class="card border">
                                    <div class="card-body p-2 text-center">
                                        <label class="fw-bold text-primary d-block" style="font-size:0.8rem;">
                                            Bs. {{ number_format($denominacion, 0) }}
                                        </label>
                                        <input type="hidden" 
                                               name="denominaciones[bs_{{ $index }}][denominacion]" 
                                               value="{{ $denominacion }}">
                                        <input type="number" 
                                               name="denominaciones[bs_{{ $index }}][cantidad]" 
                                               class="form-control form-control-sm text-center cantidad-bs"
                                               data-denominacion="{{ $denominacion }}"
                                               data-tipo="bs"
                                               min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-2 col-4">
                                <div class="card border h-100" style="background:#eff6ff;">
                                    <div class="card-body p-2 text-center">
                                        <label class="fw-bold text-primary d-block" style="font-size:0.7rem;">TOTAL Bs.</label>
                                        <p class="h6 fw-bold text-primary mb-0" id="totalDenominacionesBs">Bs. 0.00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- MONTO TOTAL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-calculator me-2"></i>Monto Total del Préstamo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="monto" class="form-label fw-semibold">
                                <i class="bi bi-cash-coin me-1" style="color:#d97706;"></i>Monto <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" id="simboloMoneda">$</span>
                                <input type="number" name="monto" id="monto"
                                       class="form-control @error('monto') is-invalid @enderror"
                                       step="0.01" min="0.01" required
                                       placeholder="0.00"
                                       value="{{ old('monto') }}">
                            </div>
                            @error('monto')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100" 
                                    onclick="usarTotalDenominaciones()">
                                <i class="bi bi-arrow-down-circle me-1"></i> Usar total de denominaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn px-4 fw-semibold text-white" 
                                style="background:{{ $hdrBg }};border:none;" id="btnGuardar">
                            <i class="bi bi-save me-1"></i> Guardar Préstamo
                        </button>
                        <a href="{{ route('cpanel.boveda.prestamos') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tipoMoneda = document.getElementById('tipo_moneda');
        const simboloMoneda = document.getElementById('simboloMoneda');
        const seccionDivisas = document.getElementById('seccionDivisas');
        const seccionBs = document.getElementById('seccionBs');

        // ============================================
        // CAMBIAR VISTA SEGÚN TIPO DE MONEDA
        // ============================================
        function actualizarVistaMoneda() {
            const tipo = tipoMoneda.value;
            
            if (tipo === '0') {
                // Divisas
                simboloMoneda.textContent = '$';
                seccionDivisas.style.display = 'block';
                seccionBs.style.display = 'none';
                // Deshabilitar inputs de Bs
                document.querySelectorAll('.cantidad-bs').forEach(i => i.disabled = true);
                document.querySelectorAll('.cantidad-divisa').forEach(i => i.disabled = false);
            } else {
                // Bolívares
                simboloMoneda.textContent = 'Bs.';
                seccionDivisas.style.display = 'none';
                seccionBs.style.display = 'block';
                document.querySelectorAll('.cantidad-divisa').forEach(i => i.disabled = true);
                document.querySelectorAll('.cantidad-bs').forEach(i => i.disabled = false);
            }
            
            calcularTotales();
        }

        // ============================================
        // CALCULAR TOTALES DE DENOMINACIONES
        // ============================================
        function calcularTotales() {
            // Divisas
            let totalDivisa = 0;
            document.querySelectorAll('.cantidad-divisa').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                totalDivisa += cantidad * denom;
            });
            document.getElementById('totalDenominacionesDivisa').textContent = '$ ' + totalDivisa.toFixed(2);

            // Bolívares
            let totalBs = 0;
            document.querySelectorAll('.cantidad-bs').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const denom = parseFloat(input.dataset.denominacion) || 0;
                totalBs += cantidad * denom;
            });
            document.getElementById('totalDenominacionesBs').textContent = 'Bs. ' + totalBs.toFixed(2);
        }

        // ============================================
        // USAR TOTAL DE DENOMINACIONES COMO MONTO
        // ============================================
        window.usarTotalDenominaciones = function() {
            const tipo = tipoMoneda.value;
            const montoInput = document.getElementById('monto');
            
            if (tipo === '0') {
                let totalDivisa = 0;
                document.querySelectorAll('.cantidad-divisa').forEach(input => {
                    const cantidad = parseInt(input.value) || 0;
                    const denom = parseFloat(input.dataset.denominacion) || 0;
                    totalDivisa += cantidad * denom;
                });
                montoInput.value = totalDivisa.toFixed(2);
            } else {
                let totalBs = 0;
                document.querySelectorAll('.cantidad-bs').forEach(input => {
                    const cantidad = parseInt(input.value) || 0;
                    const denom = parseFloat(input.dataset.denominacion) || 0;
                    totalBs += cantidad * denom;
                });
                montoInput.value = totalBs.toFixed(2);
            }
        };

        // Event listeners
        tipoMoneda.addEventListener('change', actualizarVistaMoneda);
        document.querySelectorAll('.cantidad-divisa, .cantidad-bs').forEach(i => {
            i.addEventListener('input', calcularTotales);
        });

        // Inicializar
        actualizarVistaMoneda();
        calcularTotales();

        // Deshabilitar botón al enviar
        document.getElementById('formPrestamo').addEventListener('submit', function() {
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
        });
    });
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .form-label { font-size: 0.85rem; }
    .form-control:focus, .form-select:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 0.2rem rgba(217, 119, 6, 0.15);
    }
</style>
@endpush