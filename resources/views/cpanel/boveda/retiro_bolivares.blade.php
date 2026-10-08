@extends('layout.layout_dashboard')

@section('title', 'Retiro de Bolívares')

@php
    $hdrBg = 'linear-gradient(135deg,#ef4444 0%,#dc2626 100%)';
    $hdrIcon = 'cash';
    $hdrTitle = 'Retiro de Bolívares';
    $hdrSubtitle = 'Salida de efectivo en bolívares de la bóveda';
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
                    <li class="breadcrumb-item active">Retiro Bolívares</li>
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

        <div class="row g-3">

            {{-- FORMULARIO --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-box-arrow-up me-2"></i>Registrar Retiro
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cpanel.billetera.retiro.bolivares.guardar') }}" method="POST" id="formRetiro">
                            @csrf

                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold text-danger mb-0" style="font-size:0.9rem;">
                                        <i class="bi bi-cash-stack me-1"></i>Billetes a Retirar
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnAgregarRetirada">
                                        <i class="bi bi-plus-lg"></i> Agregar
                                    </button>
                                </div>

                                <div id="retiradasContainer"></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between p-2 bg-light rounded">
                                        <span class="text-muted fw-semibold" style="font-size:0.85rem;">Total a Retirar:</span>
                                        <strong class="text-danger" id="total_retirar">Bs. 0.00</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observacion" class="form-label fw-semibold" style="font-size:0.85rem;">
                                    Motivo / Destino del Retiro <span class="text-danger">*</span>
                                </label>
                                <textarea name="observacion" id="observacion" class="form-control" rows="3"
                                          placeholder="Ej: Retiro para gastos de personal, envío a banco, pago a proveedor..." required>{{ old('observacion') }}</textarea>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex gap-2 justify-content-end">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Limpiar
                                </button>
                                <button type="submit" class="btn text-white fw-semibold" id="btnGuardar"
                                        style="background:{{ $hdrBg }};border:none;" disabled>
                                    <i class="bi bi-box-arrow-up me-1"></i>Registrar Retiro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- DISPONIBLE + HISTORIAL --}}
            <div class="col-lg-5">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash-stack me-2"></i>Disponible en Bóvedas
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background:#fef2f2;">
                                    <tr>
                                        <th class="ps-4 py-2 text-danger fw-semibold" style="font-size:0.75rem;">DENOMINACIÓN</th>
                                        <th class="pe-4 py-2 text-end text-danger fw-semibold" style="font-size:0.75rem;">DISPONIBLE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($disponibles->sortByDesc('Denominacion') as $d)
                                    <tr>
                                        <td class="ps-4 fw-semibold">Bs. {{ number_format($d->Denominacion, 0) }}</td>
                                        <td class="pe-4 text-end fw-bold text-danger">
                                            {{ number_format($d->Disponible, 0) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No hay denominaciones</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-clock-history me-2"></i>Últimos Retiros
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                            @forelse($historial as $retiro)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $retiro->FechaFormateada }}
                                    </small>
                                    <span class="badge bg-danger">#{{ $retiro->CambioId }}</span>
                                </div>

                                <ul class="list-unstyled mb-2" style="font-size:0.8rem;">
                                    @foreach($retiro->Detalles as $det)
                                        <li class="d-flex justify-content-between">
                                            <span>
                                                <i class="bi bi-box-arrow-up text-danger me-1"></i>
                                                Se retiró:
                                                {{ $det->Cantidad }} × Bs. {{ number_format($det->Denominacion, 0) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if($retiro->Observacion)
                                    <div class="text-muted mt-1" style="font-size:0.75rem;">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $retiro->Observacion }}
                                    </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay retiros registrados
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    window.DENOMINACIONES_DISPONIBLES = @json(
        $disponibles
            ->filter(fn($d) => $d->Disponible > 0)
            ->map(fn($d) => [
                'denominacion' => (float) $d->Denominacion,
                'disponible'   => (int) $d->Disponible,
            ])
            ->values()
    );
</script>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const denominacionesDisponibles = window.DENOMINACIONES_DISPONIBLES || [];
    const retiradasContainer = document.getElementById('retiradasContainer');

    function crearFilaRetirada() {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 fila-retirada';

        row.innerHTML = `
            <div class="col-md-6">
                <select class="form-select select-denominacion" required>
                    <option value="">Seleccione...</option>
                    ${denominacionesDisponibles.map(d =>
                        `<option value="${d.denominacion}" data-disponible="${d.disponible}">Bs. ${d.denominacion.toFixed(0)} (Disponible: ${d.disponible})</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control input-cantidad" min="0" value="0" required>
                <div class="invalid-feedback" style="font-size:0.75rem;"></div>
            </div>
            <div class="col-md-2">
                <div class="form-control bg-light text-end subtotal-retirada">Bs. 0.00</div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;

        row.querySelector('.select-denominacion').addEventListener('change', recalcular);
        row.querySelector('.input-cantidad').addEventListener('input', recalcular);
        row.querySelector('.btn-eliminar').addEventListener('click', function() {
            row.remove();
            recalcular();
        });

        retiradasContainer.appendChild(row);
        recalcular();
    }

    function recalcular() {
        let totalRetirar = 0;
        let hayExceso = false;
        let hayAlMenosUna = false;

        document.querySelectorAll('.fila-retirada').forEach(fila => {
            const selectDen = fila.querySelector('.select-denominacion');
            const inputCant = fila.querySelector('.input-cantidad');
            const subtotalEl = fila.querySelector('.subtotal-retirada');

            const den = parseFloat(selectDen.value) || 0;
            const cant = parseInt(inputCant.value) || 0;
            const subtotal = den * cant;

            subtotalEl.textContent = 'Bs. ' + subtotal.toFixed(2);
            totalRetirar += subtotal;

            const opcionSel = selectDen.options[selectDen.selectedIndex];
            const disponible = opcionSel ? parseInt(opcionSel.dataset.disponible) || 0 : 0;

            if (den > 0 && cant > disponible) {
                inputCant.classList.add('is-invalid');
                if (inputCant.nextElementSibling) {
                    inputCant.nextElementSibling.textContent = `Solo hay ${disponible} disponibles`;
                }
                hayExceso = true;
            } else {
                inputCant.classList.remove('is-invalid');
                if (inputCant.nextElementSibling) inputCant.nextElementSibling.textContent = '';
            }

            if (den > 0 && cant > 0) {
                hayAlMenosUna = true;
            }
        });

        document.getElementById('total_retirar').textContent = 'Bs. ' + totalRetirar.toFixed(2);

        const btnGuardar = document.getElementById('btnGuardar');
        const observacion = document.getElementById('observacion').value.trim();

        btnGuardar.disabled = !(hayAlMenosUna && !hayExceso && observacion.length > 0);
    }

    document.getElementById('btnAgregarRetirada').addEventListener('click', crearFilaRetirada);
    document.getElementById('observacion').addEventListener('input', recalcular);

    crearFilaRetirada();

    document.getElementById('formRetiro').addEventListener('submit', function(e) {
        this.querySelectorAll('input[name^="retiradas["]').forEach(i => i.remove());

        let index = 0;
        let hayError = false;

        document.querySelectorAll('.fila-retirada').forEach(fila => {
            const selectDen = fila.querySelector('.select-denominacion');
            const inputCant = fila.querySelector('.input-cantidad');
            const den = selectDen.value;
            const cant = parseInt(inputCant.value) || 0;

            const opcionSel = selectDen.options[selectDen.selectedIndex];
            const disponible = opcionSel ? parseInt(opcionSel.dataset.disponible) || 0 : 0;

            if (den && cant > disponible) {
                hayError = true;
            }

            if (den && cant > 0) {
                const inputDen = document.createElement('input');
                inputDen.type = 'hidden';
                inputDen.name = `retiradas[${index}][denominacion]`;
                inputDen.value = den;
                this.appendChild(inputDen);

                const inputCantH = document.createElement('input');
                inputCantH.type = 'hidden';
                inputCantH.name = `retiradas[${index}][cantidad]`;
                inputCantH.value = cant;
                this.appendChild(inputCantH);

                index++;
            }
        });

        if (hayError) {
            e.preventDefault();
            alert('Hay denominaciones que exceden el disponible.');
        }
    });

});
</script>
@endsection