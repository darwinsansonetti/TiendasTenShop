@extends('layout.layout_dashboard')

@section('title', 'Seleccionar Proveedor')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                  <i class="bi bi-truck text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Seleccionar Proveedor</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Seleccione el proveedor para la recepción</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.recepciones.proveedor') }}">Recepciones</a>
                    </li>
                    <li class="breadcrumb-item active">Seleccionar Proveedor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Filtro de búsqueda --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-building text-muted"></i>
                            </span>
                            <input type="text"
                                   id="buscadorProveedor"
                                   class="form-control border-start-0 border-end-0"
                                   placeholder="Nombre o código del proveedor..."
                                   autocomplete="off">
                            <button class="btn btn-light border" type="button" id="limpiarBuscador"
                                    title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="#" class="btn btn-light border fw-semibold" id="btnLimpiar"
                           style="font-size:0.88rem;">
                            <i class="bi bi-arrow-repeat me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de proveedores --}}
        @if($proveedores && count($proveedores) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-truck me-2"></i>Proveedores de Mercancía
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ count($proveedores) }} proveedores
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaProveedores">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:10;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:76px;">LOGO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="nombre"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    NOMBRE <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="documento"
                                    style="font-size:0.75rem;letter-spacing:.06em;width:180px;cursor:pointer;">
                                    RIF / CÉDULA <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="email"
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;">
                                    CORREO ELECTRÓNICO <i class="bi bi-arrow-down-up ms-1" style="font-size:0.7rem;opacity:.5;"></i>
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold"
                                    style="font-size:0.75rem;letter-spacing:.06em;width:130px;">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedores as $proveedor)
                                @php
                                    $proveedorId = $proveedor->ProveedorId;
                                    $nombre = $proveedor->Nombre ?? 'N/A';
                                    $rifCedula = $proveedor->RifCedula ?? '';
                                    $email = $proveedor->CorreoElectronico ?? 'N/A';
                                    $urlImagen = $proveedor->UrlImagen ?? '';

                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/proveedores/',
                                        $urlImagen,
                                        'assets/img/adminlte/img/proveedor_default.png'
                                    );
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <img src="{{ $imgSrc }}"
                                             alt="{{ $nombre }}"
                                             class="rounded-circle img-zoomable"
                                             style="width:46px;height:46px;object-fit:cover;border:2px solid #e2e8f0;cursor:zoom-in;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgSrc }}"
                                             data-description="{{ $nombre }}">
                                    </td>
                                    <td data-order="{{ $nombre }}">
                                        <p class="mb-0 fw-bold text-dark">{{ $nombre }}</p>
                                        <small class="text-muted">Código: {{ $proveedorId }}</small>
                                    </td>
                                    <td data-order="{{ $rifCedula ?: 'Sin RIF' }}">
                                        @if(!empty($rifCedula))
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $rifCedula }}</code>
                                        @else
                                            <span class="text-muted" style="font-size:0.88rem;">No ingresado</span>
                                        @endif
                                    </td>
                                    <td data-order="{{ $email }}">
                                        @if($email && $email != 'N/A')
                                            <a href="mailto:{{ $email }}"
                                               class="text-decoration-none"
                                               style="color:#0891b2;font-size:0.88rem;">
                                                <i class="bi bi-envelope me-1" style="font-size:0.8rem;"></i>{{ $email }}
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size:0.88rem;">No ingresado</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button"
                                                class="btn btn-sm fw-semibold btn-seleccionar"
                                                style="background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.3);font-size:0.82rem;"
                                                data-proveedor-id="{{ $proveedorId }}"
                                                data-proveedor-nombre="{{ $nombre }}"
                                                title="Seleccionar proveedor">
                                            <i class="bi bi-check-circle me-1"></i>Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-2 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-truck me-1"></i>Total: {{ count($proveedores) }} proveedores
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                     style="width:56px;height:56px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);opacity:0.5;">
                    <i class="bi bi-truck text-white" style="font-size:1.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No hay proveedores registrados</h5>
                <p class="text-muted mb-0">No se encontraron proveedores de mercancía activos en el sistema.</p>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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
                            <td colspan="5" class="text-center text-muted py-4">
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
        // BOTÓN SELECCIONAR PROVEEDOR
        // ==========================
        const botonesSeleccionar = document.querySelectorAll('.btn-seleccionar');
        botonesSeleccionar.forEach(btn => {
            btn.addEventListener('click', function() {
                const proveedorId = this.getAttribute('data-proveedor-id');
                const proveedorNombre = this.getAttribute('data-proveedor-nombre');

                Swal.fire({
                    title: '¿Continuar con este proveedor?',
                    html: `Seleccionaste <strong>${proveedorNombre}</strong><br>Se creará una nueva recepción para este proveedor.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ url("recepciones/crear") }}/' + proveedorId;
                    }
                });
            });
        });
    });

    // ==========================
    // ZOOM DE IMAGEN
    // ==========================
    function zoomImagen(img) {
        const fullImage = img.getAttribute('data-full-image');
        const description = img.getAttribute('data-description');

        Swal.fire({
            imageUrl: fullImage,
            imageAlt: description,
            title: description,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '2em',
            background: '#fff'
        });
    }
</script>
@endsection

@push('styles')
<style>
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    #tablaProveedores tbody tr:hover { background: #f8fafc; }
    #tablaProveedores thead th.sortable:hover { background: #eef2ff; color: #1d4ed8; }

    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 0.2rem rgba(59,130,246,.15); }
</style>
@endpush
