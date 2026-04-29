@extends('layout.layout_dashboard')

@section('title', $modo == 'pagos' ? 'TiendasTenShop | Registrar Pagos' : 'TiendasTenShop | Registrar Facturas')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    @if($modo == 'pagos')
                        <i class="fas fa-money-bill-wave me-2"></i>Registrar Pagos a Proveedores
                    @else
                        <i class="fas fa-file-invoice me-2"></i>Registrar Facturas
                    @endif
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if($modo == 'pagos')
                            Registrar Pagos
                        @else
                            Registrar Facturas
                        @endif
                    </li>
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
                                placeholder="Nombre o código del proveedor..."
                                autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="limpiarBuscador">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botón Limpiar Filtros -->
                    <div class="col-md-2">
                        <a href="#" class="btn btn-secondary w-100" id="btnLimpiar">
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
                    <!-- Título (Izquierda) -->
                    <div class="col-md-6">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-truck me-2"></i>
                            @if($modo == 'pagos')
                                Proveedores de Mercancía
                            @else
                                Proveedores de Facturas
                            @endif
                        </h3>
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
                                <th width="150" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedoresMercancia as $proveedor)
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
                                            <span class="text-muted">
                                                No Ingresado
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Correo Electrónico -->
                                    <td data-order="{{ $email }}">
                                        @if($email && $email != 'N/A')
                                            <a href="mailto:{{ $email }}" class="text-info">
                                                {{ $email }}
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                No Ingresado
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acción -->
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Botón según modo (Pago o Factura) -->
                                            @if($modo == 'pagos')
                                                <a href="{{ route('cpanel.proveedores.pagar', $proveedor->ProveedorId) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Registrar Pago"
                                                    data-bs-toggle="tooltip">
                                                        <i class="bi bi-cash-stack"></i>
                                                </a>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalRegistrarFactura"
                                                        data-proveedor-id="{{ $proveedorId }}"
                                                        data-proveedor-nombre="{{ $nombre }}"
                                                        title="Registrar Factura"
                                                        data-bs-toggle="tooltip">
                                                    <i class="bi bi-file-text me-1"></i>Factura
                                                </button>
                                            @endif
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

<!-- ============================================ -->
<!-- MODAL: REGISTRAR FACTURA -->
<!-- ============================================ -->
<div class="modal fade" id="modalRegistrarFactura" tabindex="-1" aria-labelledby="modalRegistrarFacturaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRegistrarFacturaLabel">
                    <i class="fas fa-file-invoice me-2"></i>Registrar Factura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <input type="hidden" name="proveedor_id" id="factura_proveedor_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Proveedor</label>
                        <p class="form-control-static fw-bold text-primary" id="factura_proveedor_nombre"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_factura" class="form-label">Número de Factura *</label>
                            <input type="text" name="numero_factura" id="numero_factura" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_emision" class="form-label">Fecha Emisión *</label>
                            <input type="date" name="fecha_emision" id="fecha_emision" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="monto_total" class="form-label">Monto Total (USD) *</label>
                        <input type="number" step="0.01" name="monto_total" id="monto_total" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_factura" class="form-label">Descripción / Concepto</label>
                        <textarea name="descripcion" id="descripcion_factura" class="form-control" rows="3" placeholder="Detalle de la factura (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Registrar Factura
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