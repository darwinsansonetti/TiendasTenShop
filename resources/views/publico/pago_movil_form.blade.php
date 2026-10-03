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
            padding: 30px 20px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }
        /* Header de la página */
        .page-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 16px;
            padding: 20px 28px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .page-header h3 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-header small {
            color: rgba(255,255,255,0.8);
            display: block;
            margin-top: 4px;
            font-size: 0.8rem;
        }
        .user-info {
            color: #fff;
            text-align: right;
            line-height: 1.2;
        }
        .user-info .label {
            font-size: 0.72rem;
            opacity: 0.8;
        }
        .user-info .name {
            font-size: 0.9rem;
            font-weight: 600;
        }
        .btn-logout {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }
        /* Cards */
        .card-section {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            height: 100%;
        }
        .card-section .section-header {
            background: #f8fafc;
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            font-size: 0.95rem;
        }
        .section-title i {
            color: #7c3aed;
            font-size: 1.1rem;
        }
        .card-section .section-body {
            padding: 24px;
        }
        /* Badges */
        .stat-badge {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .stat-badge.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        /* Tabla */
        .table-pagos thead th {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 12px;
            background: #f8fafc;
            white-space: nowrap;
        }
        .table-pagos tbody td {
            font-size: 0.82rem;
            padding: 12px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-pagos tbody tr:hover {
            background: #f8fafc;
        }
        .table-pagos code {
            background: #f1f5f9;
            color: #7c3aed;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        /* Estado vacío */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 3rem;
            opacity: 0.4;
            display: block;
            margin-bottom: 12px;
        }
        /* Tabla responsive con scroll */
        .table-scroll {
            max-height: 500px;
            overflow-y: auto;
        }
        /* Responsive */
        @media (max-width: 992px) {
            .card-section {
                margin-bottom: 20px;
            }
            .user-info {
                text-align: left;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- ============================================ --}}
    {{-- HEADER CON USUARIO Y LOGOUT --}}
    {{-- ============================================ --}}
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3>
                    <i class="bi bi-credit-card"></i>
                    Verificación de Pago Móvil
                </h3>
                <small>Consulta contra el Banco de Venezuela</small>
            </div>

            @auth
            <div class="d-flex align-items-center gap-3">
                <div class="user-info">
                    <div class="label">Sesión activa</div>
                    <div class="name">{{ Auth::user()->NombreCompleto }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- CONTENIDO: DOS COLUMNAS --}}
    {{-- ============================================ --}}
    <div class="row g-4">

        {{-- ============================================ --}}
        {{-- COLUMNA IZQUIERDA: FORMULARIO --}}
        {{-- ============================================ --}}
        <div class="col-lg-6">
            <div class="card-section">
                <div class="section-header">
                    <h6 class="section-title">
                        <i class="bi bi-search"></i>
                        Consultar Pago Móvil
                    </h6>
                </div>

                <div class="section-body">
                    <form id="formPagoMovil">
                        @csrf
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
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
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">RIF del Comercio</label>
                                <input type="text" id="rifDestino" class="form-control" readonly style="background:#f8fafc;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Teléfono Destino</label>
                                <input type="text" id="telefonoDestino" class="form-control" readonly style="background:#f8fafc;">
                            </div>

                            <div class="col-12"><hr class="my-2"></div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                    Cédula del Pagador <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <select id="tipoCedula" class="form-select" style="max-width:80px;">
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                        <option value="P">P</option>
                                    </select>
                                    <input type="text" id="numeroCedula" class="form-control"
                                           placeholder="Ej: 27037606" inputmode="numeric" pattern="[0-9]+" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                    Teléfono del Pagador <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="telefonoPagador" class="form-control"
                                       placeholder="Ej: 04127141363" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                    Referencia <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="referencia" class="form-control"
                                       placeholder="Ej: 123112313" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                    Fecha del Pago <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="fechaPago" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
                                    Monto (Bs.) <span class="text-danger">*</span>
                                </label>
                                <input type="text" inputmode="numeric" name="importe" id="importe" class="form-control"
                                    placeholder="0.00" value="0.00" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.82rem;">
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
                            <button type="submit" class="btn text-white fw-semibold" id="btnConsultar"
                                    style="background:#7c3aed;border-color:#7c3aed;">
                                <i class="bi bi-search"></i> Consultar
                            </button>
                        </div>
                    </form>

                    {{-- Resultado de la consulta --}}
                    <div id="resultado" class="mt-4" style="display:none;">
                        <div id="resultadoCard"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- COLUMNA DERECHA: LISTADO DE PAGOS DEL DÍA --}}
        {{-- ============================================ --}}
        <div class="col-lg-6">
            <div class="card-section">
                <div class="section-header">
                    <h6 class="section-title">
                        <i class="bi bi-list-check"></i>
                        Pagos Verificados Hoy
                    </h6>

                    @if(isset($pagosHoy) && $pagosHoy->count() > 0)
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="stat-badge">
                            <i class="bi bi-receipt"></i> {{ $totalPagosHoy }}
                        </span>
                        <span class="stat-badge success">
                            <i class="bi bi-cash-coin"></i> Bs. {{ number_format($totalMontoHoy, 2, ',', '.') }}
                        </span>
                    </div>
                    @endif
                </div>

                <div class="section-body p-0">
                    @if(isset($pagosHoy) && $pagosHoy->count() > 0)
                    <div class="table-scroll">
                        <table class="table table-pagos align-middle mb-0">
                            <thead style="position:sticky;top:0;z-index:1;">
                                <tr>
                                    <th>REFERENCIA</th>
                                    <th>PAGADOR</th>
                                    <th>BANCO</th>
                                    <th class="text-end">MONTO</th>
                                    <th>HORA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagosHoy as $p)
                                <tr>
                                    <td>
                                        <code>{{ $p->Referencia }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->CedulaPagador }}</div>
                                        <small class="text-muted">{{ $p->TelefonoPagador }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $p->BancoOrigen }}</div>
                                        <small class="text-muted">{{ $p->BancoOrigenNombre }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Bs. {{ number_format($p->Importe, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <small>{{ $p->FechaVerificacionFormateada }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0">Aún no hay pagos verificados hoy</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ============================================
// FORMATEO TIPO CAJA REGISTRADORA
// ============================================
const inputImporte = document.getElementById('importe');
let centavos = 0;

inputImporte.addEventListener('input', function(e) {
    // 1. Quitar cualquier caracter que no sea dígito
    const soloDigitos = this.value.replace(/\D/g, '');

    // 2. Convertir a número de centavos
    centavos = soloDigitos ? parseInt(soloDigitos, 10) : 0;

    // 3. Limitar a un máximo razonable (evitar overflow)
    //    Ejemplo: máximo 999,999,999.99 → 99999999999 centavos
    if (centavos > 99999999999) {
        centavos = 99999999999;
    }

    // 4. Formatear y mostrar
    this.value = formatearMonto(centavos);
});

// Al enfocar, si está en "0.00" lo seleccionamos para que empiece limpio
inputImporte.addEventListener('focus', function() {
    this.select();
});

// Al perder foco, si está vacío o es "0.00", dejarlo en "0.00"
inputImporte.addEventListener('blur', function() {
    if (!this.value || this.value === '0.00' || this.value === '0,00') {
        centavos = 0;
        this.value = '0.00';
    }
});

// Función que convierte centavos a string con formato "1234.56"
function formatearMonto(centavos) {
    const entero  = Math.floor(centavos / 100);
    const decimal = (centavos % 100).toString().padStart(2, '0');
    return entero + '.' + decimal;
}

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

    // ✅ Convertir el importe formateado (ej. "25450.40") a número limpio
    formData.importe = (centavos / 100).toFixed(2);

    // Validar que el monto sea mayor a 0
    if (parseFloat(formData.importe) < 0.01) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto debe ser mayor a 0.',
            confirmButtonColor: '#7c3aed'
        });
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        return;
    }

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

        if (data.success === true) {
            setTimeout(() => {
                window.location.reload();
            }, 2500);
        }
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
        <div class="card border-0" style="border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
            <div class="card-header text-white" style="background:${bg};padding:14px 18px;border:none;">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-${icono}"></i>
                    ${exitoso ? 'Pago Verificado' : 'Pago No Verificado'}
                </h6>
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%;font-size:0.85rem;">Estado:</td>
                        <td class="fw-bold ${exitoso ? 'text-success' : 'text-danger'}" style="font-size:0.85rem;">
                            ${exitoso ? 'Transacción realizada' : 'No conciliada'}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:0.85rem;">Monto:</td>
                        <td class="fw-bold" style="font-size:0.85rem;">Bs. ${amount}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:0.85rem;">Código:</td>
                        <td><code style="font-size:0.75rem;">${status}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:0.85rem;">Mensaje:</td>
                        <td style="font-size:0.85rem;">${reason}</td>
                    </tr>
                </table>

                <details class="mt-2">
                    <summary class="text-muted" style="cursor:pointer;font-size:0.78rem;">
                        Ver respuesta completa
                    </summary>
                    <pre class="mt-2 p-2 bg-light" style="border-radius:6px;font-size:0.68rem;overflow-x:auto;">${JSON.stringify(data, null, 2)}</pre>
                </details>
            </div>
        </div>
    `;

    if (exitoso) {
        Swal.fire({
            icon: 'success',
            title: '¡Pago Verificado!',
            html: `
                <p class="mb-2">${reason}</p>
                <p class="mb-0"><strong>Monto:</strong> Bs. ${amount}</p>
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