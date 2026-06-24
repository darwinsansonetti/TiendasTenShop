@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Proveedores de Mercancía')

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
                  <i class="bi bi-shop text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Proveedores de Mercancía</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Gestión de proveedores y facturas</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Proveedores de Mercancía</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- FILTROS / BUSCADOR --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="buscadorProveedor" class="form-label fw-semibold text-dark" style="font-size:0.82rem;">
                            <i class="bi bi-search me-1 text-primary"></i>Buscar Proveedor
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-building text-primary"></i>
                            </span>
                            <input type="text"
                                   id="buscadorProveedor"
                                   class="form-control border-start-0 border-end-0"
                                   placeholder="Nombre del proveedor..."
                                   autocomplete="off">
                            <button class="btn btn-light border" type="button" id="limpiarBuscador" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}"
                           class="btn btn-light border w-100">
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
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="row align-items-center">
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white">
                            <i class="bi bi-truck me-2"></i>Listado de Proveedores
                        </h6>
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.2);color:#fff;font-size:0.75rem;">
                            {{ count($proveedoresMercancia) }}
                        </span>
                        <a href="{{ route('cpanel.proveedores.crear') }}"
                           class="btn btn-sm fw-semibold ms-1"
                           style="background:rgba(16,185,129,0.18);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.8rem;">
                            <i class="bi bi-plus-circle me-1"></i>Nuevo Proveedor
                        </a>
                    </div>
                    <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="pdfTablaProveedores()">
                            <i class="bi bi-printer me-1"></i>PDF
                        </button>
                        <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:0.78rem;"
                                onclick="exportarExcelProveedores()">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0" id="tablaProveedores">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:80px;">LOGO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="nombre" style="font-size:0.75rem;letter-spacing:.06em;">NOMBRE</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="direccion" style="font-size:0.75rem;letter-spacing:.06em;width:260px;">DIRECCIÓN</th>
                                <th class="py-3 text-center text-muted fw-semibold sortable" data-col="telefono" style="font-size:0.75rem;letter-spacing:.06em;width:160px;">TELÉFONO</th>
                                <th class="py-3 text-muted fw-semibold sortable" data-col="email" style="font-size:0.75rem;letter-spacing:.06em;width:220px;">CORREO</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:110px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedoresMercancia as $proveedor)
                                @php
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/proveedores/',
                                        $proveedor->UrlImagen,
                                        'assets/img/adminlte/img/proveedor_default.png'
                                    );
                                    $telefono = $proveedor->TelefonoMovil ?: $proveedor->TelefonoFijo;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    {{-- Logo --}}
                                    <td class="ps-4 text-center">
                                        <img src="{{ $imgSrc }}"
                                             alt="{{ $proveedor->Nombre }}"
                                             class="rounded-circle img-zoomable"
                                             style="width:46px;height:46px;object-fit:cover;border:2px solid #e2e8f0;cursor:zoom-in;"
                                             onclick="zoomImagen(this)"
                                             data-full-image="{{ $imgSrc }}"
                                             data-description="{{ $proveedor->Nombre }}">
                                    </td>

                                    {{-- Nombre --}}
                                    <td data-order="{{ $proveedor->Nombre }}">
                                        <p class="mb-0 fw-bold text-dark">{{ $proveedor->Nombre }}</p>
                                    </td>

                                    {{-- Dirección --}}
                                    <td data-order="{{ $proveedor->Direccion }}">
                                        <span class="text-muted" style="font-size:0.85rem;">
                                            {{ \Illuminate\Support\Str::limit($proveedor->Direccion, 60) }}
                                        </span>
                                    </td>

                                    {{-- Teléfono --}}
                                    <td class="text-center" data-order="{{ $telefono }}">
                                        @if($telefono)
                                            <code class="px-2 py-1 rounded-2"
                                                  style="background:#f1f5f9;color:#3b82f6;font-size:0.8rem;">{{ $telefono }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Correo --}}
                                    <td data-order="{{ $proveedor->CorreoElectronico }}">
                                        @if($proveedor->CorreoElectronico)
                                            <a href="mailto:{{ $proveedor->CorreoElectronico }}"
                                               class="text-decoration-none d-flex align-items-center gap-1"
                                               style="color:#0891b2;font-size:0.85rem;">
                                                <i class="bi bi-envelope" style="font-size:0.8rem;"></i>
                                                {{ $proveedor->CorreoElectronico }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="pe-4 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                               title="Ver detalle" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                            </a>
                                            <a href="{{ route('cpanel.proveedores.editar', $proveedor->ProveedorId) }}"
                                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                               style="width:30px;height:30px;background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.25);"
                                               title="Editar proveedor" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);"
                                                    onclick="eliminarProveedor({{ $proveedor->ProveedorId }})"
                                                    title="Desactivar proveedor" data-bs-toggle="tooltip">
                                                <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-2 px-4" style="border-top:1px solid #f1f5f9;">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-building me-1"></i>
                            Total: <strong>{{ count($proveedoresMercancia) }}</strong> proveedor(es)
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Actualizado: {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        @else

        {{-- Estado vacío --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    <i class="bi bi-truck text-muted" style="font-size:2rem;opacity:.5;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No hay proveedores registrados</h5>
                <p class="text-muted mb-4" style="font-size:0.9rem;">
                    No se encontraron proveedores de mercancía activos en el sistema.
                </p>
                <a href="{{ route('cpanel.proveedores.crear') }}" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-plus-circle me-2"></i>Registrar Proveedor
                </a>
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

            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    buscador.value = '';
                    filtrarTabla();
                }
            });
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

                    document.querySelectorAll('.sort-icon').forEach(icon => {
                        icon.innerHTML = '↕️';
                    });

                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }

                    const icono = th.querySelector('.sort-icon');
                    if (icono) {
                        icono.innerHTML = ordenAscendente ? '⬆️' : '⬇️';
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

    function pdfTablaProveedores() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Listado de Proveedores de Mercancía', 14, 15);

        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

        doc.autoTable({
            html: '#tablaProveedores',
            startY: 35,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [41, 128, 185] }
        });

        doc.save(`Proveedores_Mercancia_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarExcelProveedores() {
        const tabla = document.getElementById('tablaProveedores');
        if (!tabla) return;

        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Proveedores"});
        XLSX.writeFile(wb, `Proveedores_Mercancia_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function eliminarProveedor(id) {
        Swal.fire({
            title: '¿Desactivar proveedor?',
            text: 'El proveedor quedará como inactivo. Puede reactivarlo más tarde.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Desactivando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("cpanel.proveedores.eliminar") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Desactivado!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al desactivar el proveedor', 'error');
                });
            }
        });
    }
</script>

@endsection

@push('styles')
<style>
    #tablaProveedores tbody tr:hover { background: #f8fafc; }
    .img-zoomable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .img-zoomable:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    thead th.sortable:hover { background: #eef2f7 !important; cursor: pointer; }
</style>
@endpush
