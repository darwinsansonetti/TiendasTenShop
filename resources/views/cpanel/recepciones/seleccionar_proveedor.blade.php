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
                <h3 class="mb-0">
                    <i class="bi bi-truck me-2"></i>Seleccionar Proveedor
                </h3>
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
        
        <!-- Card de filtros / buscador -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- Buscador por nombre -->
                    <div class="col-md-6">
                        <label for="buscadorProveedor" class="form-label">
                            <i class="bi bi-search me-1"></i>Buscar Proveedor
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                id="buscadorProveedor" 
                                class="form-control" 
                                placeholder="Nombre o código del proveedor..."
                                autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="limpiarBuscador">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botón Limpiar Filtros -->
                    <div class="col-md-2">
                        <a href="#" class="btn btn-secondary w-100" id="btnLimpiar">
                            <i class="bi bi-arrow-repeat me-2"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de proveedores -->
        @if($proveedores && count($proveedores) > 0)
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-truck me-2"></i>Proveedores de Mercancía
                        </h3>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-primary">{{ count($proveedores) }} proveedores</span>
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
                                <th width="180" class="sortable" data-col="documento">Rif/Cédula</th>
                                <th width="250" class="sortable" data-col="email">Correo Electrónico</th>
                                <th width="120" class="text-center">Acción</th>
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
                                <tr class="align-middle">
                                    <!-- Logo -->
                                    <td class="text-center">
                                        <img src="{{ $imgSrc }}" 
                                            alt="{{ $nombre }}"
                                            class="rounded-circle border border-secondary img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ $imgSrc }}"
                                            data-description="{{ $nombre }}">
                                    </td>
                                    
                                    <!-- Nombre con ID debajo -->
                                    <td data-order="{{ $nombre }}">
                                        <strong>{{ $nombre }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Código: {{ $proveedorId }}
                                        </small>
                                    </td>
                                    
                                    <!-- Rif/Cédula -->
                                    <td data-order="{{ $rifCedula ?: 'Sin RIF' }}">
                                        @if(!empty($rifCedula))
                                            <code>{{ $rifCedula }}</code>
                                        @else
                                            <span class="text-muted">No Ingresado</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Correo Electrónico -->
                                    <td data-order="{{ $email }}">
                                        @if($email && $email != 'N/A')
                                            <a href="mailto:{{ $email }}" class="text-info">
                                                {{ $email }}
                                            </a>
                                        @else
                                            <span class="text-muted">No Ingresado</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acción: Botón Seleccionar -->
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary btn-seleccionar"
                                                data-proveedor-id="{{ $proveedorId }}"
                                                data-proveedor-nombre="{{ $nombre }}"
                                                title="Seleccionar proveedor">
                                            <i class="bi bi-check-circle me-1"></i>Seleccionar
                                        </button>
                                    </td>
                                </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-truck me-1"></i>
                            Total Proveedores: {{ count($proveedores) }}
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
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-truck fs-1 text-muted"></i>
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

<!-- Scripts para exportar Excel y PDF -->
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