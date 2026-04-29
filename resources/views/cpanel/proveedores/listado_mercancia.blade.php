@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Proveedores de Mercancía')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Proveedores de Mercancía</h3>
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

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid"> 
        
        <!-- Card de filtros / buscador -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- Buscador por nombre -->
                    <div class="col-md-6">
                        <label for="buscadorProveedor" class="form-label">
                            <i class="fas fa-search me-1"></i>Buscar Proveedor
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                id="buscadorProveedor" 
                                class="form-control" 
                                placeholder="Nombre del proveedor..."
                                autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="limpiarBuscador">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botón Limpiar Filtros -->
                    <div class="col-md-2">
                        <a href="#" class="btn btn-secondary w-100">
                            <i class="fas fa-undo me-2"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de proveedores -->
        @if($proveedoresMercancia && count($proveedoresMercancia) > 0)
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <!-- Título y Botón Nuevo (Izquierda) -->
                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-truck me-2"></i>Listado de Proveedores
                        </h3>
                        
                        <!-- Botón Nuevo Proveedor -->
                        <a href="{{ route('cpanel.proveedores.crear') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus-circle me-1"></i>Nuevo Proveedor
                        </a>
                    </div>
                    
                    <!-- Botones (Derecha) -->
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pdfTablaProveedores()">
                                <i class="fas fa-print me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcelProveedores()">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaProveedores">
                        <thead class="table-light">
                            <tr>
                                <th width="80" class="text-center">Logo</th>
                                <th class="sortable" data-col="nombre">Nombre</th>
                                <th width="250" class="sortable" data-col="direccion">Dirección</th>
                                <th width="150" class="text-center sortable" data-col="telefono">Teléfono</th>
                                <th width="200" class="sortable" data-col="email">Correo Electrónico</th>
                                <th width="100" class="text-center">Acciones</th>
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
                                <tr class="align-middle">
                                    <!-- Logo -->
                                    <td class="text-center">
                                        <img src="{{ $imgSrc }}" 
                                            alt="{{ $proveedor->Nombre }}"
                                            class="rounded-circle border border-secondary img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ $imgSrc }}"
                                            data-description="{{ $proveedor->Nombre }}">
                                    </td>
                                    
                                    <!-- Nombre -->
                                    <td data-order="{{ $proveedor->Nombre }}">
                                        <strong>{{ $proveedor->Nombre }}</strong>
                                    </td>
                                    
                                    <!-- Dirección -->
                                    <td data-order="{{ $proveedor->Direccion }}">
                                        <span class="text-muted">
                                            {{ \Illuminate\Support\Str::limit($proveedor->Direccion, 60) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Teléfono -->
                                    <td class="text-center" data-order="{{ $telefono }}">
                                        @if($telefono)
                                            <span class="badge bg-light text-dark p-2">
                                                {{ $telefono }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Correo Electrónico -->
                                    <td data-order="{{ $proveedor->CorreoElectronico }}">
                                        @if($proveedor->CorreoElectronico)
                                            <a href="mailto:{{ $proveedor->CorreoElectronico }}" class="text-info">
                                                {{ $proveedor->CorreoElectronico }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Ver detalle del proveedor"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('cpanel.proveedores.editar', $proveedor->ProveedorId) }}"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Editar proveedor"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="eliminarProveedor({{ $proveedor->ProveedorId }})"
                                                    title="Eliminar proveedor"
                                                    data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                 </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-truck me-1"></i>
                            Total Proveedores: {{ count($proveedoresMercancia) }}
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Actualizado: {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-truck fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay proveedores registrados</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron proveedores de mercancía activos en el sistema.
                    </p>
                </div>
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
                                <i class="fas fa-search me-2"></i>
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

<style>
    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    
    .badge.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .btn-group .btn i {
        font-size: 0.9rem;
    }
    
    .empty-state {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }
    
    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .card-body.p-0::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .card-body.p-0::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .card-body.p-0::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .card-body.p-0::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endsection