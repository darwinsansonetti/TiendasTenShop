@extends('layout.layout_dashboard')

@section('title', $modo == 'pagos' ? 'TiendasTenShop | Registrar Pagos' : 'TiendasTenShop | Registrar Facturas')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            @php
              $hdrBg    = $modo == 'pagos' ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#8b5cf6,#7c3aed)';
              $hdrIcon  = $modo == 'pagos' ? 'cash-coin' : 'file-earmark-text';
              $hdrTitle = $modo == 'pagos' ? 'Registrar Pagos a Proveedores' : 'Registrar Facturas';
            @endphp
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:{{ $hdrBg }};">
                  <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Gestión de proveedores de mercancía</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if($modo == 'pagos') Registrar Pagos @else Registrar Facturas @endif
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD BUSCADOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="buscadorProveedor" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                            <i class="bi bi-search me-1"></i>Buscar Proveedor
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                   id="buscadorProveedor"
                                   class="form-control border-start-0"
                                   placeholder="Nombre o código del proveedor..."
                                   autocomplete="off">
                            <button class="btn btn-light border" type="button" id="limpiarBuscador"
                                    style="font-size:0.85rem;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="#" class="btn btn-light border w-100 fw-semibold" id="btnLimpiar"
                           style="font-size:0.85rem;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- TABLA DE PROVEEDORES --}}
        {{-- ================================================ --}}
        @if($proveedoresMercancia && count($proveedoresMercancia) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>
                        @if($modo == 'pagos') Proveedores de Mercancía @else Proveedores de Facturas @endif
                        <span class="badge bg-white ms-2 fw-semibold"
                              style="font-size:0.75rem;{{ $modo == 'pagos' ? 'color:#059669;' : 'color:#7c3aed;' }}">
                            {{ count($proveedoresMercancia) }}
                        </span>
                    </h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaProveedores">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">LOGO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="nombre"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    NOMBRE <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="documento"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:180px;">
                                    RIF / CÉDULA <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="email"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;width:260px;">
                                    CORREO ELECTRÓNICO <i class="bi bi-chevron-expand ms-1" style="font-size:0.65rem;opacity:.5;"></i>
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold"
                                    style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedoresMercancia as $proveedor)
                                @php
                                    $proveedorId = $proveedor->ProveedorId;
                                    $nombre      = $proveedor->Nombre ?? 'N/A';
                                    $rifCedula   = $proveedor->RifCedula ?? '';
                                    $email       = $proveedor->CorreoElectronico ?? 'N/A';
                                    $urlImagen   = $proveedor->UrlImagen ?? '';

                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/proveedores/',
                                        $urlImagen,
                                        'assets/img/adminlte/img/proveedor_default.png'
                                    );
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    {{-- Logo con zoom --}}
                                    <td class="ps-4 py-3 text-center">
                                        <img src="{{ $imgSrc }}"
                                             alt="{{ $nombre }}"
                                             class="img-zoomable"
                                             style="width:46px;height:46px;object-fit:cover;border-radius:50%;border:2px solid #e2e8f0;cursor:zoom-in;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgSrc }}"
                                             data-description="{{ $nombre }}">
                                    </td>

                                    {{-- Nombre + código --}}
                                    <td class="py-3" data-order="{{ $nombre }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <div>
                                                <p class="mb-0 fw-semibold text-dark">{{ $nombre }}</p>
                                                <small class="text-muted" style="font-size:0.75rem;">
                                                    Código: <code style="font-size:0.72rem;color:#3b82f6;">{{ $proveedorId }}</code>
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- RIF / Cédula --}}
                                    <td class="py-3" data-order="{{ $rifCedula ?: 'Sin RIF' }}">
                                        @if(!empty($rifCedula))
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.78rem;">{{ $rifCedula }}</code>
                                        @else
                                            <span class="text-muted" style="font-size:0.85rem;">—</span>
                                        @endif
                                    </td>

                                    {{-- Correo --}}
                                    <td class="py-3" data-order="{{ $email }}">
                                        @if($email && $email != 'N/A')
                                            <a href="mailto:{{ $email }}"
                                               class="text-decoration-none"
                                               style="color:#3b82f6;font-size:0.88rem;">
                                                <i class="bi bi-envelope me-1" style="font-size:0.8rem;"></i>{{ $email }}
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size:0.85rem;">—</span>
                                        @endif
                                    </td>

                                    {{-- Acción --}}
                                    <td class="pe-4 py-3 text-center">
                                        @if($modo == 'pagos')
                                            <a href="{{ route('cpanel.proveedores.pagar', $proveedor->ProveedorId) }}"
                                               class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                               title="Registrar Pago" data-bs-toggle="tooltip">
                                                <i class="bi bi-cash-stack" style="font-size:0.8rem;"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('cpanel.proveedores.nueva.factura', $proveedor->ProveedorId) }}"
                                               class="btn btn-sm rounded-2 d-inline-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);"
                                               title="Registrar Factura" data-bs-toggle="tooltip">
                                                <i class="bi bi-file-text" style="font-size:0.8rem;"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4" style="background:#f8fafc;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-building me-1"></i>
                        {{ count($proveedoresMercancia) }} proveedor{{ count($proveedoresMercancia) != 1 ? 'es' : '' }} registrado{{ count($proveedoresMercancia) != 1 ? 's' : '' }}
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        @else

        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="d-flex flex-column align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:64px;height:64px;background:{{ $modo == 'pagos' ? 'rgba(16,185,129,0.08)' : 'rgba(139,92,246,0.08)' }};">
                        <i class="bi bi-building"
                           style="font-size:1.8rem;opacity:.5;color:{{ $modo == 'pagos' ? '#059669' : '#7c3aed' }};"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-0">No hay proveedores registrados</p>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        @if($modo == 'pagos')
                            No se encontraron proveedores de mercancía con deuda pendiente.
                        @else
                            No se encontraron proveedores de mercancía activos en el sistema.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @endif

    </div>
</div>

{{-- ================================================ --}}
{{-- MODAL: REGISTRAR FACTURA --}}
{{-- ================================================ --}}
<div class="modal fade" id="modalRegistrarFactura" tabindex="-1" aria-labelledby="modalRegistrarFacturaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <h5 class="modal-title fw-bold text-white" id="modalRegistrarFacturaLabel">
                    <i class="bi bi-file-earmark-plus me-2"></i>Registrar Factura
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <input type="hidden" name="proveedor_id" id="factura_proveedor_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Proveedor</p>
                        <p class="form-control-static fw-bold text-dark mb-0" id="factura_proveedor_nombre"></p>
                    </div>
                    <hr style="border-color:#f1f5f9;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="numero_factura" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Número de Factura <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="numero_factura" id="numero_factura"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_emision" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Fecha Emisión <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha_emision" id="fecha_emision"
                                   class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="monto_total" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Monto Total (USD) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold text-success">$</span>
                                <input type="number" step="0.01" name="monto_total" id="monto_total"
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_factura" class="form-label fw-semibold text-dark" style="font-size:0.85rem;">
                                Descripción / Concepto
                            </label>
                            <textarea name="descripcion" id="descripcion_factura"
                                      class="form-control" rows="3"
                                      placeholder="Detalle de la factura (opcional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i>Registrar Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // BUSCADOR DE PROVEEDORES
        // ==========================
        const buscador = document.getElementById('buscadorProveedor');
        const tabla = document.getElementById('tablaProveedores');
        const limpiarBtn = document.getElementById('limpiarBuscador');
        const btnLimpiar = document.getElementById('btnLimpiar');

        if (buscador && tabla) {
            function filtrarTabla() {
                const textoBusqueda = buscador.value.toLowerCase().trim();
                const filas = tabla.querySelectorAll('tbody tr');
                let filasVisibles = 0;

                filas.forEach(fila => {
                    const celdaNombre = fila.children[1];
                    if (celdaNombre) {
                        const textoNombre = celdaNombre.textContent.toLowerCase();

                        if (textoBusqueda === '' || textoNombre.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });

                const tbody = tabla.querySelector('tbody');
                let mensajeNoResultados = document.getElementById('mensajeNoResultados');

                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultados';
                        const colspan = tabla.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="bi bi-search me-2"></i>
                                No se encontraron proveedores con el nombre "${buscador.value}"
                              </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }

            buscador.addEventListener('input', filtrarTabla);

            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function() {
                    buscador.value = '';
                    filtrarTabla();
                    buscador.focus();
                });
            }

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function(e) {
                    e.preventDefault();
                    buscador.value = '';
                    filtrarTabla();
                    buscador.focus();
                });
            }
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaProveedores');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }

                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
                const filasReales = filas.filter(fila => fila.id !== 'mensajeNoResultados');

                filasReales.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];

                    if (!tdA || !tdB) return 0;

                    const valorA = tdA.dataset.order || tdA.innerText.trim();
                    const valorB = tdB.dataset.order || tdB.innerText.trim();

                    return asc
                        ? valorA.toString().localeCompare(valorB.toString())
                        : valorB.toString().localeCompare(valorA.toString());
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));

                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }
        })();

        // ==========================
        // MODAL REGISTRAR FACTURA
        // ==========================
        const modalFactura = document.getElementById('modalRegistrarFactura');
        if (modalFactura) {
            modalFactura.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const proveedorId = button.getAttribute('data-proveedor-id');
                const proveedorNombre = button.getAttribute('data-proveedor-nombre');

                document.getElementById('factura_proveedor_id').value = proveedorId;
                document.getElementById('factura_proveedor_nombre').textContent = proveedorNombre;
            });
        }

        // ==========================
        // FUNCIONES AUXILIARES
        // ==========================
        function cargarFacturasPendientes(proveedorId) {
            const facturaSelect = document.getElementById('factura_id');
            facturaSelect.innerHTML = '<option value="">Cargando facturas...</option>';

            fetch(`{{ url("cpanel/proveedor") }}/${proveedorId}/facturas/pendientes`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.facturas && data.facturas.length > 0) {
                        facturaSelect.innerHTML = '<option value="">Seleccione una factura</option>';
                        data.facturas.forEach(factura => {
                            const option = document.createElement('option');
                            option.value = factura.FacturaId;
                            option.setAttribute('data-saldo', factura.saldo_pendiente);
                            option.textContent = `${factura.Numero} - Saldo: $${factura.saldo_pendiente.toFixed(2)}`;
                            facturaSelect.appendChild(option);
                        });

                        facturaSelect.dispatchEvent(new Event('change'));
                    } else {
                        facturaSelect.innerHTML = '<option value="">No hay facturas pendientes</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    facturaSelect.innerHTML = '<option value="">Error al cargar facturas</option>';
                });
        }

        function obtenerTasaDia() {

        }

        const facturaSelect = document.getElementById('factura_id');
        const montoInput = document.getElementById('monto_divisa');
        const maxMontoSpan = document.getElementById('max_monto');

        if (facturaSelect) {
            facturaSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const saldo = selectedOption.getAttribute('data-saldo') || 0;
                maxMontoSpan.textContent = parseFloat(saldo).toFixed(2);
                if (montoInput) {
                    montoInput.max = saldo;
                    montoInput.value = '';
                }
            });
        }

        if (montoInput) {
            montoInput.addEventListener('input', function() {
                const montoUSD = parseFloat(this.value) || 0;
                const tasa = parseFloat(document.getElementById('tasa_dia')?.value) || 0;
                const montoBs = montoUSD * tasa;
                document.getElementById('monto_bs').value = montoBs.toFixed(2);
            });
        }
    });

    function zoomImagen(img) {
        Swal.fire({
            imageUrl: img.src,
            imageAlt: img.alt,
            title: img.alt,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '2em',
            background: '#fff',
            customClass: {
                image: 'img-fluid rounded'
            }
        });
    }
</script>
@endsection

@push('styles')
<style>
    #tablaProveedores tbody tr:hover { background-color: #f8fafc; }
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.18); }
    thead th.sortable:hover { background-color: #f1f5f9; }
</style>
@endpush
