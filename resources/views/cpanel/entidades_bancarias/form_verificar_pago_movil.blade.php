@extends('layout.layout_dashboard')

@section('title', 'Verificar Pago Móvil')

@php
    $hdrBg = 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)';
    $hdrIcon = 'credit-card';
    $hdrTitle = 'Verificar Pago Móvil';
    $hdrSubtitle = 'Consulta contra el Banco de Venezuela';
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
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.pago.movil.index') }}">Pago Móvil</a></li>
                    <li class="breadcrumb-item active">Verificar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if($configuraciones->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                No hay configuraciones de Pago Móvil activas.
                <a href="{{ route('cpanel.configuracion.pago.movil') }}" class="fw-semibold">Registrar una</a>.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-search me-2"></i>Datos del Pago
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formPagoMovil">
                            @csrf
                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Pago Móvil (Sucursal / Alias) <span class="text-danger">*</span>
                                    </label>
                                    <select name="SucursalPagoMovilId" id="SucursalPagoMovilId" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($configuraciones as $cfg)
                                            <option value="{{ $cfg->SucursalPagoMovilId }}"
                                                    data-sucursal="{{ $cfg->sucursal_nombre }}"
                                                    data-alias="{{ $cfg->Alias }}"
                                                    data-rif="{{ $cfg->Rif }}"
                                                    data-telefono="{{ $cfg->Telefono }}">
                                                {{ $cfg->sucursal_nombre }} — {{ $cfg->Alias }} ({{ $cfg->Telefono }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">RIF del Comercio</label>
                                    <input type="text" id="rifDestino" class="form-control" readonly style="background:#f8fafc;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">Teléfono Destino</label>
                                    <input type="text" id="telefonoDestino" class="form-control" readonly style="background:#f8fafc;">
                                </div>

                                <div class="col-12"><hr class="my-2"></div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Cédula del Pagador <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select id="tipoCedula" class="form-select" style="max-width:90px;" required>
                                            <option value="V">V</option>
                                            <option value="E">E</option>
                                            <option value="P">P</option>
                                        </select>
                                        <input type="text" id="numeroCedula" class="form-control"
                                            placeholder="Ej: 27037606" required
                                            inputmode="numeric" pattern="[0-9]+"
                                            title="Solo números, sin puntos ni guiones">
                                    </div>
                                    <small class="text-muted">Solo números, sin puntos ni guiones.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Teléfono del Pagador <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="telefonoPagador" class="form-control"
                                           placeholder="Ej: 04127141363" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Referencia <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="referencia" class="form-control"
                                           placeholder="Ej: 123112313" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Fecha del Pago <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="fechaPago" class="form-control"
                                           value="{{ now()->format('Y-m-d') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Monto (Bs.) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0.01" name="importe" class="form-control"
                                           placeholder="Ej: 120.00" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Banco Origen <span class="text-danger">*</span>
                                    </label>
                                    <select name="bancoOrigen" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="0102">0102 - Banco de Venezuela</option>
                                        <option value="0104">0104 - Venezolano de Crédito</option>
                                        <option value="0105">0105 - Mercantil</option>
                                        <option value="0108">0108 - Provincial</option>
                                        <option value="0114">0114 - Bancaribe</option>
                                        <option value="0115">0115 - Banco Exterior</option>
                                        <option value="0128">0128 - Banco Caroní</option>
                                        <option value="0134">0134 - Banesco</option>
                                        <option value="0137">0137 - Sofitasa</option>
                                        <option value="0138">0138 - Banco Plaza</option>
                                        <option value="0146">0146 - Bangente</option>
                                        <option value="0151">0151 - BFC Banco Fondo Común</option>
                                        <option value="0156">0156 - 100% Banco</option>
                                        <option value="0157">0157 - DelSur</option>
                                        <option value="0163">0163 - Banco del Tesoro</option>
                                        <option value="0166">0166 - Banco Agrícola de Venezuela</option>
                                        <option value="0168">0168 - Bancrecer</option>
                                        <option value="0169">0169 - Mi Banco</option>
                                        <option value="0171">0171 - Banco Activo</option>
                                        <option value="0172">0172 - Bancamiga</option>
                                        <option value="0174">0174 - Banplus</option>
                                        <option value="0175">0175 - Banco Bicentenario</option>
                                        <option value="0177">0177 - Banfanb</option>
                                        <option value="0191">0191 - BNC</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-3">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('cpanel.pago.movil.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Volver
                                </a>
                                <button type="submit" class="btn fw-semibold text-white"
                                        style="background:{{ $hdrBg }};border:none;" id="btnConsultar">
                                    <i class="bi bi-search me-1"></i>Consultar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" id="cardResultado" style="display:none;">
                    <div class="card-header border-0 py-3" id="cardResultadoHeader">
                        <h6 class="mb-0 fw-bold text-white" id="cardResultadoTitulo">
                            <i class="bi bi-info-circle me-2"></i>Resultado
                        </h6>
                    </div>
                    <div class="card-body" id="cardResultadoBody"></div>
                </div>

                <div class="card border-0 shadow-sm h-100" id="cardPlaceholder">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                        <i class="bi bi-search" style="font-size:3rem;opacity:0.3;"></i>
                        <p class="mb-0 mt-3">Ingresa los datos del pago y presiona <strong>Consultar</strong></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('SucursalPagoMovilId').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('rifDestino').value = opt.dataset.rif || '';
    document.getElementById('telefonoDestino').value = opt.dataset.telefono || '';
});

document.getElementById('formPagoMovil').addEventListener('submit', async function(e) {
    e.preventDefault();

    // === Construir la cédula: letra + número ===
    const tipo   = document.getElementById('tipoCedula').value;
    const numero = document.getElementById('numeroCedula').value.trim();
    const cedula = (tipo + numero).toUpperCase();

    // Validar que el número tenga solo dígitos
    if (!/^\d+$/.test(numero)) {
        Swal.fire({
            icon: 'warning',
            title: 'Cédula inválida',
            text: 'El número de cédula debe contener solo dígitos.',
        });
        return;
    }

    const btn = document.getElementById('btnConsultar');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Consultando...';

    const formData = Object.fromEntries(new FormData(this).entries());
    delete formData._token;
    delete formData.tipoCedula;
    delete formData.numeroCedula;

    // Agregar la cédula ya compuesta
    formData.cedulaPagador = cedula;

    try {
        const res = await fetch('{{ route('cpanel.pago.movil.consultar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        });

        const data = await res.json();
        mostrarResultado(data);

    } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo comunicar con el servidor.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});

function mostrarResultado(data) {
    const cardRes = document.getElementById('cardResultado');
    const cardPh  = document.getElementById('cardPlaceholder');
    const header  = document.getElementById('cardResultadoHeader');
    const titulo  = document.getElementById('cardResultadoTitulo');
    const body    = document.getElementById('cardResultadoBody');

    cardPh.style.display = 'none';
    cardRes.style.display = 'block';

    const exitoso = data.success === true;
    header.style.background = exitoso
        ? 'linear-gradient(135deg,#10b981 0%,#059669 100%)'
        : 'linear-gradient(135deg,#ef4444 0%,#dc2626 100%)';
    titulo.innerHTML = exitoso
        ? '<i class="bi bi-check-circle-fill me-2"></i>Pago Verificado y Registrado'
        : '<i class="bi bi-x-circle-fill me-2"></i>Pago No Verificado';

    const amount = data.data?.amount ?? '—';
    const reason = data.mensaje_amigable ?? data.data?.reason ?? data.message ?? 'Sin información';
    const status = data.data?.status ?? data.code ?? '—';

    body.innerHTML = `
        <div class="text-center mb-3">
            <i class="bi bi-${exitoso ? 'check-circle-fill' : 'x-circle-fill'}"
               style="font-size:3rem;color:${exitoso ? '#10b981' : '#ef4444'};"></i>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                <span class="text-muted">Estado</span>
                <strong class="${exitoso ? 'text-success' : 'text-danger'}">
                    ${exitoso ? 'Transacción realizada' : 'No conciliada'}
                </strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="text-muted">Monto confirmado</span>
                <strong>${amount}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="text-muted">Código</span>
                <code>${status}</code>
            </li>
            <li class="list-group-item">
                <div class="text-muted mb-1" style="font-size:0.8rem;">Mensaje del banco</div>
                <div>${reason}</div>
            </li>
        </ul>
        ${exitoso ? `
            <div class="alert alert-success mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                El pago fue registrado correctamente.
                <a href="{{ route('cpanel.pago.movil.index') }}" class="fw-semibold">Ver listado</a>
            </div>
        ` : ''}
    `;
}
</script>
@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
</style>
@endpush