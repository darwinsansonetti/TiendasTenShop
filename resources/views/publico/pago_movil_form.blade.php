<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificación de Pago Móvil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .card-form {
            max-width: 900px;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card-form .card-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            padding: 24px;
            border: none;
        }
        .card-form .card-header h3 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }
        .card-form .card-header small {
            color: rgba(255,255,255,0.8);
        }
        .card-form .card-body {
            padding: 32px;
            background: #fff;
        }
        .badge-test {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fbbf24;
            color: #000;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
        }
    </style>
</head>
<body>

<div class="badge-test">
    <i class="bi bi-exclamation-triangle-fill"></i> RUTA PÚBLICA TEMPORAL
</div>

<div class="card card-form">
    <div class="card-header">
        <h3><i class="bi bi-credit-card"></i> Verificación de Pago Móvil</h3>
        <small>Consulta contra el Banco de Venezuela</small>
    </div>
    <div class="card-body">
        <form id="formPagoMovil">
            @csrf
            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Pago Móvil (Sucursal / Alias) <span class="text-danger">*</span>
                    </label>
                    <select name="SucursalPagoMovilId" id="SucursalPagoMovilId" class="form-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($configuraciones as $cfg)
                            <option value="{{ $cfg->SucursalPagoMovilId }}"
                                    data-rif="{{ $cfg->Rif }}"
                                    data-telefono="{{ $cfg->Telefono }}">
                                {{ $cfg->sucursal_nombre }} — {{ $cfg->Alias }} ({{ $cfg->Telefono }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">RIF del Comercio</label>
                    <input type="text" id="rifDestino" class="form-control" readonly style="background:#f8fafc;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono Destino</label>
                    <input type="text" id="telefonoDestino" class="form-control" readonly style="background:#f8fafc;">
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Cédula del Pagador <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <select id="tipoCedula" class="form-select" style="max-width:90px;">
                            <option value="V">V</option>
                            <option value="E">E</option>
                            <option value="P">P</option>
                        </select>
                        <input type="text" id="numeroCedula" class="form-control"
                               placeholder="Ej: 27037606" inputmode="numeric" pattern="[0-9]+" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Teléfono del Pagador <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="telefonoPagador" class="form-control"
                           placeholder="Ej: 04127141363" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Referencia <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="referencia" class="form-control"
                           placeholder="Ej: 123112313" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Fecha del Pago <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fechaPago" class="form-control"
                           value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Monto (Bs.) <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="importe" class="form-control"
                           placeholder="Ej: 120.00" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
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

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Limpiar
                </button>
                <button type="submit" class="btn btn-primary fw-semibold" id="btnConsultar">
                    <i class="bi bi-search"></i> Consultar
                </button>
            </div>
        </form>

        {{-- Resultado --}}
        <div id="resultado" class="mt-4" style="display:none;">
            <div id="resultadoCard"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('SucursalPagoMovilId').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('rifDestino').value = opt.dataset.rif || '';
    document.getElementById('telefonoDestino').value = opt.dataset.telefono || '';
});

document.getElementById('formPagoMovil').addEventListener('submit', async function(e) {
    e.preventDefault();

    const tipo   = document.getElementById('tipoCedula').value;
    const numero = document.getElementById('numeroCedula').value.trim();

    if (!/^\d+$/.test(numero)) {
        Swal.fire({
            icon: 'warning',
            title: 'Cédula inválida',
            text: 'El número de cédula debe contener solo dígitos.',
            confirmButtonColor: '#7c3aed'
        });
        return;
    }

    const btn = document.getElementById('btnConsultar');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Consultando...';

    const formData = Object.fromEntries(new FormData(this).entries());
    delete formData._token;
    formData.cedulaPagador = (tipo + numero).toUpperCase();

    try {
        const res = await fetch('{{ route('pago.movil.publico.consultar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });

        const data = await res.json();
        mostrarResultado(data);
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: err.message,
            confirmButtonColor: '#dc2626'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});

function mostrarResultado(data) {
    const wrapper = document.getElementById('resultado');
    const card = document.getElementById('resultadoCard');
    wrapper.style.display = 'block';

    const exitoso = data.success === true;
    const bg = exitoso
        ? 'linear-gradient(135deg,#10b981 0%,#059669 100%)'
        : 'linear-gradient(135deg,#ef4444 0%,#dc2626 100%)';
    const icono = exitoso ? 'check-circle-fill' : 'x-circle-fill';

    const amount = data.data?.amount ?? '—';
    const reason = data.mensaje_amigable ?? data.data?.reason ?? data.message ?? 'Sin información';
    const status = data.data?.status ?? data.code ?? '—';

    card.innerHTML = `
        <div class="card border-0" style="border-radius:12px;overflow:hidden;">
            <div class="card-header text-white" style="background:${bg};padding:16px 20px;">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-${icono}"></i>
                    ${exitoso ? 'Pago Verificado' : 'Pago No Verificado'}
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%;">Estado:</td>
                        <td class="fw-bold ${exitoso ? 'text-success' : 'text-danger'}">
                            ${exitoso ? 'Transacción realizada' : 'No conciliada'}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Monto confirmado:</td>
                        <td class="fw-bold">${amount}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Código:</td>
                        <td><code>${status}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Mensaje:</td>
                        <td>${reason}</td>
                    </tr>
                </table>

                <details class="mt-3">
                    <summary class="text-muted" style="cursor:pointer;font-size:0.85rem;">
                        Ver respuesta completa del banco
                    </summary>
                    <pre class="mt-2 p-3 bg-light" style="border-radius:8px;font-size:0.75rem;overflow-x:auto;">${JSON.stringify(data, null, 2)}</pre>
                </details>
            </div>
        </div>
    `;

    wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // ✅ SweetAlert según el resultado
    if (exitoso) {
        Swal.fire({
            icon: 'success',
            title: '¡Pago Verificado!',
            html: `
                <p class="mb-2">${reason}</p>
                <p class="mb-0"><strong>Monto:</strong> ${amount}</p>
            `,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#10b981',
            timer: 6000,
            timerProgressBar: true,
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Pago No Verificado',
            html: `
                <p class="mb-2">${reason}</p>
                <p class="mb-0" style="font-size:0.85rem;color:#6b7280;">
                    <strong>Código del banco:</strong> ${status}
                </p>
            `,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc2626',
        });
    }
}
</script>

</body>
</html>