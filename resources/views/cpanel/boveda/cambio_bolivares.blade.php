@extends('layout.layout_dashboard')

@section('title', 'Cambio de Bolívares')

@php
    $hdrBg = 'linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%)';
    $hdrIcon = 'cash-stack';
    $hdrTitle = 'Cambio de Bolívares';
    $hdrSubtitle = 'Intercambio de denominaciones en bolívares';
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
                    <li class="breadcrumb-item active">Cambio Bolívares</li>
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

            {{-- ============================================ --}}
            {{-- FORMULARIO DE CAMBIO --}}
            {{-- ============================================ --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-arrow-left-right me-2"></i>Nuevo Cambio
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cpanel.billetera.cambio.bolivares.guardar') }}" method="POST" id="formCambio">
                            @csrf

                            {{-- BILLETE A CAMBIAR --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Billete que se Cambia (entra a la bóveda)
                                </h6>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size:0.8rem;">Denominación</label>
                                        <select name="denominacion_cambiada" id="denominacion_cambiada" class="form-select" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($disponibles->sortByDesc('Denominacion') as $d)
                                                <option value="{{ $d->Denominacion }}">
                                                    Bs. {{ number_format($d->Denominacion, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" style="font-size:0.8rem;">Cantidad</label>
                                        <input type="number" name="cantidad_cambiada" id="cantidad_cambiada"
                                               class="form-control" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" style="font-size:0.8rem;">Subtotal</label>
                                        <div class="form-control bg-light fw-bold text-primary" id="subtotal_cambiado">
                                            Bs. 0.00
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- BILLETES A RECIBIR --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold text-success mb-0" style="font-size:0.9rem;">
                                        <i class="bi bi-arrow-up-circle me-1"></i>Billetes que se Entregan (salen de la bóveda)
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarRecibida">
                                        <i class="bi bi-plus-lg"></i> Agregar
                                    </button>
                                </div>

                                <div id="recibidasContainer">
                                    {{-- Las filas se agregan dinámicamente con JS --}}
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- TOTALES --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between p-2 bg-light rounded">
                                        <span class="text-muted fw-semibold" style="font-size:0.85rem;">Total a Cambiar:</span>
                                        <strong class="text-primary" id="total_cambiar">Bs. 0.00</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between p-2 bg-light rounded">
                                        <span class="text-muted fw-semibold" style="font-size:0.85rem;">Total a Recibir:</span>
                                        <strong class="text-success" id="total_recibir">Bs. 0.00</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between p-2 rounded" id="diffContainer">
                                        <span class="text-muted fw-semibold" style="font-size:0.85rem;">Diferencia:</span>
                                        <strong id="diferencia">Bs. 0.00</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Observación --}}
                            <div class="mb-3">
                                <label for="observacion" class="form-label" style="font-size:0.8rem;">Observación (opcional)</label>
                                <textarea name="observacion" id="observacion" class="form-control" rows="2"
                                          placeholder="Motivo del cambio, cliente, etc."></textarea>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex gap-2 justify-content-end">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Limpiar
                                </button>
                                <button type="submit" class="btn text-white fw-semibold" id="btnGuardar"
                                        style="background:{{ $hdrBg }};border:none;" disabled>
                                    <i class="bi bi-arrow-left-right me-1"></i>Cambiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">

                {{-- ============================================ --}}
                {{-- DISPONIBLE POR DENOMINACIÓN --}}
                {{-- ============================================ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-cash-stack me-2"></i>Disponible en Bóvedas
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tablaDisponibles">
                                <thead style="background:#eff6ff;">
                                    <tr>
                                        <th class="ps-4 py-2 text-primary fw-semibold" style="font-size:0.75rem;">DENOMINACIÓN</th>
                                        <th class="pe-4 py-2 text-end text-primary fw-semibold" style="font-size:0.75rem;">DISPONIBLE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($disponibles->sortByDesc('Denominacion') as $d)
                                    <tr>
                                        <td class="ps-4 fw-semibold">Bs. {{ number_format($d->Denominacion, 2) }}</td>
                                        <td class="pe-4 text-end fw-bold text-primary">
                                            {{ number_format($d->Disponible, 0) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            No hay denominaciones
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- HISTORIAL DE CAMBIOS --}}
                {{-- ============================================ --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-clock-history me-2"></i>Últimos Cambios
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                            @forelse($historial as $cambio)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $cambio->FechaFormateada }}
                                    </small>
                                    <span class="badge bg-secondary">#{{ $cambio->CambioId }}</span>
                                </div>

                                <ul class="list-unstyled mb-2" style="font-size:0.8rem;">
                                    @foreach($cambio->Detalles as $det)
                                        <li class="d-flex justify-content-between">
                                            <span>
                                                @if($det->Tipo === 'CAMBIADO')
                                                    <i class="bi bi-arrow-down-circle text-primary me-1"></i>
                                                    Se cambió:
                                                @else
                                                    <i class="bi bi-arrow-up-circle text-danger me-1"></i>
                                                    Se entregó:
                                                @endif
                                                {{ $det->Cantidad }} × Bs. {{ number_format($det->Denominacion, 2) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if($cambio->Observacion)
                                    <div class="text-muted mt-1" style="font-size:0.75rem;">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $cambio->Observacion }}
                                    </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay cambios registrados
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

{{-- Datos para el JS --}}
<script>
    // Todas las denominaciones (para el select "Billete que se Cambia")
    window.DENOMINACIONES_TODAS = @json($disponibles->map(fn($d) => [
        'denominacion' => (float) $d->Denominacion,
        'disponible'   => (int) $d->Disponible,
    ])->values());

    // Solo las que tienen disponible > 0 (para "Billetes que se Entregan")
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
    const recibidasContainer = document.getElementById('recibidasContainer');

    // ============================================
    // CREAR UNA FILA DE "RECIBIDA"
    // ============================================
    function crearFilaRecibida() {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 fila-recibida';

        row.innerHTML = `
            <div class="col-md-6">
                <select class="form-select select-denominacion" required>
                    <option value="">Seleccione...</option>
                    ${denominacionesDisponibles.map(d =>
                        `<option value="${d.denominacion}" data-disponible="${d.disponible}">Bs. ${d.denominacion.toFixed(2)} (Disponible: ${d.disponible})</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control input-cantidad" min="1" value="1" required>
                <div class="invalid-feedback" style="font-size:0.75rem;"></div>
            </div>
            <div class="col-md-2">
                <div class="form-control bg-light text-end subtotal-recibida">Bs. 0.00</div>
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

        recibidasContainer.appendChild(row);
        recalcular();
    }

    // ============================================
    // RECALCULAR TOTALES Y VALIDAR
    // ============================================
    function recalcular() {
        // --- Total a cambiar ---
        const denCambiada  = parseFloat(document.getElementById('denominacion_cambiada').value) || 0;
        const cantCambiada = parseInt(document.getElementById('cantidad_cambiada').value) || 0;
        const totalCambiar = denCambiada * cantCambiada;

        document.getElementById('subtotal_cambiado').textContent = 'Bs. ' + totalCambiar.toFixed(2);
        document.getElementById('total_cambiar').textContent = 'Bs. ' + totalCambiar.toFixed(2);

        // --- Total a recibir + validación ---
        let totalRecibir = 0;
        let hayExceso = false;

        document.querySelectorAll('.fila-recibida').forEach(fila => {
            const selectDen = fila.querySelector('.select-denominacion');
            const inputCant = fila.querySelector('.input-cantidad');
            const subtotalEl = fila.querySelector('.subtotal-recibida');

            const den = parseFloat(selectDen.value) || 0;
            const cant = parseInt(inputCant.value) || 0;
            const subtotal = den * cant;

            subtotalEl.textContent = 'Bs. ' + subtotal.toFixed(2);
            totalRecibir += subtotal;

            const opcionSel = selectDen.options[selectDen.selectedIndex];
            const disponible = opcionSel ? parseInt(opcionSel.dataset.disponible) || 0 : 0;

            if (den > 0 && cant > disponible) {
                inputCant.classList.add('is-invalid');
                inputCant.nextElementSibling.textContent = `Solo hay ${disponible} disponibles`;
                hayExceso = true;
            } else {
                inputCant.classList.remove('is-invalid');
                if (inputCant.nextElementSibling) inputCant.nextElementSibling.textContent = '';
            }
        });

        document.getElementById('total_recibir').textContent = 'Bs. ' + totalRecibir.toFixed(2);

        // --- Diferencia ---
        const diferencia = totalCambiar - totalRecibir;
        const diffEl = document.getElementById('diferencia');
        const diffContainer = document.getElementById('diffContainer');
        const btnGuardar = document.getElementById('btnGuardar');

        if (Math.abs(diferencia) < 0.01 && totalCambiar > 0 && !hayExceso) {
            diffEl.textContent = 'Bs. 0.00 ✅';
            diffEl.className = 'text-success fw-bold';
            diffContainer.className = 'd-flex justify-content-between p-2 rounded bg-success-subtle';
            btnGuardar.disabled = false;
        } else {
            if (hayExceso) {
                diffEl.textContent = '⚠️ Excede el disponible';
            } else {
                diffEl.textContent = 'Bs. ' + diferencia.toFixed(2);
            }
            diffEl.className = 'text-danger fw-bold';
            diffContainer.className = 'd-flex justify-content-between p-2 rounded bg-danger-subtle';
            btnGuardar.disabled = true;
        }
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.getElementById('denominacion_cambiada').addEventListener('change', recalcular);
    document.getElementById('cantidad_cambiada').addEventListener('input', recalcular);
    document.getElementById('btnAgregarRecibida').addEventListener('click', crearFilaRecibida);

    crearFilaRecibida();

    // ============================================
    // ANTES DE ENVIAR: Empaquetar recibidas en el form
    // ============================================
    document.getElementById('formCambio').addEventListener('submit', function(e) {
        this.querySelectorAll('input[name^="recibidas["]').forEach(i => i.remove());

        let index = 0;
        let hayError = false;

        document.querySelectorAll('.fila-recibida').forEach(fila => {
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
                inputDen.name = `recibidas[${index}][denominacion]`;
                inputDen.value = den;
                this.appendChild(inputDen);

                const inputCantH = document.createElement('input');
                inputCantH.type = 'hidden';
                inputCantH.name = `recibidas[${index}][cantidad]`;
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