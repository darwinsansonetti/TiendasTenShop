@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Indice de Rotación')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Índice de Rotación</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Índice de Rotación</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        
        <!-- Card de filtros -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filtros de búsqueda
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.indice.rotacion') }}" method="GET" id="filtroForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio
                            </label>
                            <div class="input-group">
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_inicio" 
                                       name="fecha_inicio"
                                       value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="fecha_fin" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Fin
                            </label>
                            <div class="input-group">
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_fin" 
                                       name="fecha_fin"
                                       value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        @if($indices)
        <!-- Card de resumen -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h6 class="card-subtitle mb-2 text-muted">Período Analizado</h6>
                              <h4 class="card-title mb-0 d-flex flex-wrap align-items-center gap-1">
                                  <span>{{ $indices->fecha_inicio ? $indices->fecha_inicio->format('d/m/Y') : '-' }}</span>
                                  <span class="text-muted">-</span>
                                  <span>{{ $indices->fecha_fin ? $indices->fecha_fin->format('d/m/Y') : '-' }}</span>
                              </h4>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Productos</h6>
                                <h4 class="card-title mb-0">{{ $indices->detalles->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Mayor Índice</h6>
                                <h4 class="card-title mb-0">{{ number_format($indices->mayor_indice, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Índice Promedio</h6>
                                <h4 class="card-title mb-0">{{ number_format($indices->indice_promedio, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header">
              <div class="row align-items-center g-2">
                <!-- Título más compacto -->
                <div class="col-md-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Índice de Rotación
                    </h5>
                </div>
                
                <!-- Campo de búsqueda -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <input type="text" 
                              class="form-control" 
                              id="buscarProducto"
                              placeholder="Buscar por código o descripción..."
                              onkeyup="filtrarTabla()">
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="col-md-4 text-md-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pdfTablaConImagenes()">
                            <i class="fas fa-print me-1"></i>PDF
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportarExcel()">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                      <table class="table table-hover mb-0" id="tablaIndiceRotacion">
                        <thead class="table-light">
                            <tr>
                                <th width="80" class="text-center">Imagen</th>
                                <th width="100">Código</th>
                                <th>Descripción</th>
                                <th width="100" class="text-center">Existencia</th>
                                <th width="120" class="text-center">Ventas (Uds)</th>
                                <th width="100" class="text-center">Costo</th>
                                <th width="100" class="text-center">PVP</th>
                                <th width="120" class="text-center">Índice Rotación</th>
                                <th width="120" class="text-center">Última Venta</th>
                                <th width="80" class="text-center">Acciones</th> <!-- Columna movida aquí -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($indices->detalles as $index => $detalle)
                            @php
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle->producto['url_foto'] ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                
                                // Determinar color del índice
                                $colorIndice = 'text-dark';
                                if ($detalle->indice_rotacion >= $indices->indice_promedio * 1.5) {
                                    $colorIndice = 'text-success fw-bold';
                                } elseif ($detalle->indice_rotacion < $indices->indice_promedio * 0.5) {
                                    $colorIndice = 'text-danger';
                                }
                                
                                // Determinar color de existencia
                                $colorExistencia = 'text-dark';
                                $existencia = $detalle->producto['existencia'] ?? 0;
                                if ($existencia == 0) {
                                    $colorExistencia = 'text-danger fw-bold';
                                } elseif ($existencia <= 5) {
                                    $colorExistencia = 'text-warning';
                                }
                                
                                // Determinar si mostrar alertas
                                $tieneAlertas = false;
                                $alertas = [];
                                
                                if ($existencia == 0) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Sin existencias';
                                } elseif ($existencia <= 5) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Stock bajo';
                                }
                                
                                if ($detalle->indice_rotacion < $indices->indice_promedio * 0.5) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Baja rotación';
                                }
                                
                                // Fecha última venta (si es muy antigua)
                                $fechaUltimaVenta = $detalle->producto['fecha_ultima_venta'] ?? null;
                                if ($fechaUltimaVenta && $fechaUltimaVenta->diffInDays(now()) > 90) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Sin ventas recientes';
                                }
                            @endphp
                            <tr>
                                <!-- Foto -->
                                <td class="text-center">
                                    <div class="position-relative">
                                        <img src="{{ $urlImagen }}" 
                                            class="img-thumbnail rounded img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit:cover; cursor: zoom-in;"
                                            alt="{{ $detalle->producto['descripcion'] ?? '' }}"
                                            data-full-image="{{ $urlImagen }}"
                                            data-description="{{ $detalle->producto['descripcion'] }}"
                                            title="{{ $detalle->producto['descripcion'] ?? '' }}"
                                            onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                        @if($existencia == 0)
                                        <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5em;">
                                            0
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Código -->
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $detalle->producto['codigo'] }}</span>
                                </td>
                                
                                <!-- Descripción -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" data-bs-toggle="tooltip" title="{{ $detalle->producto['descripcion'] ?? '' }}">
                                            {{ Str::limit($detalle->producto['descripcion'] ?? 'Sin descripción', 40) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Existencia -->
                                <td class="text-center {{ $colorExistencia }}">
                                    {{ $existencia }}
                                </td>
                                
                                <!-- Ventas -->
                                <td class="text-center fw-bold text-primary">
                                    {{ $detalle->total_unidades }}
                                </td>
                                
                                <!-- Costo -->
                                <td class="text-center text-muted">
                                    ${{ number_format($detalle->producto['costo_divisa'] ?? 0, 2) }}
                                </td>
                                
                                <!-- PVP -->
                                <td class="text-center fw-bold text-success">
                                    ${{ number_format($detalle->producto['pvp_divisa'] ?? 0, 2) }}
                                </td>
                                
                                <!-- Índice Rotación -->
                                <td class="text-center {{ $colorIndice }}">
                                    <span class="badge {{ $detalle->indice_rotacion >= $indices->indice_promedio ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-dark' }}">
                                        {{ number_format($detalle->indice_rotacion, 2) }}
                                    </span>
                                </td>
                                
                                <!-- Última Venta -->
                                <td class="text-center">
                                    @if($detalle->producto['fecha_ultima_venta'])
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $detalle->producto['fecha_ultima_venta']->format('d/m/Y') }}
                                    </span>
                                    @else
                                    <span class="badge bg-light text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <!-- Botón para actualizar PVP -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning ms-1" 
                                                data-bs-toggle="tooltip" 
                                                title="Actualizar PVP"
                                                onclick="abrirModalActualizarPVP({{ $detalle->producto['id'] }}, {{ session('sucursal_id', 0) }}, 
                                                {{ json_encode([
                                                    'codigo' => $detalle->producto['codigo'],
                                                    'descripcion' => $detalle->producto['descripcion'],
                                                    'pvp_actual' => $detalle->producto['pvp_divisa'] ?? 0,
                                                    'pvp_anterior' => $detalle->producto['pvp_anterior'] ?? 0,
                                                    'nuevo_pvp' => $detalle->producto['nuevo_pvp'] ?? 0,
                                                    'fecha_nuevo_precio' => $detalle->producto['fecha_nuevo_precio'] ? $detalle->producto['fecha_nuevo_precio']->format('Y-m-d H:i:s') : null,
                                                    'existencia' => $detalle->producto['existencia'] ?? 0,
                                                    'costo_divisa' => $detalle->producto['costo_divisa'] ?? 0,
                                                    'url_foto' => $urlImagen  // ¡Agregar esta línea!
                                                ]) }})">
                                            <i class="bi bi-currency-dollar"></i> <!-- Icono de moneda/dólar -->
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="tooltip" 
                                                title="Ver detalles"
                                                onclick="verDetalleProducto({{ $detalle->producto['id'] }})">
                                            <i class="bi bi-eye"></i> <!-- Bootstrap Icon -->
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando <strong>{{ $indices->detalles->count() }}</strong> productos
                    </div>
                    <div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Aquí puedes agregar paginación si es necesario -->
                                <li class="page-item disabled">
                                    <span class="page-link">Página 1 de 1</span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        @else
        <!-- Card vacío -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-bar fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay datos para mostrar</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron registros para el período seleccionado.
                    </p>
                </div>
            </div>
        </div>
        @endif
        
    </div>
</div>

<!-- Modal/Overlay para la imagen en zoom -->
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display: none;">
    <div class="image-zoom-container">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="">
        <div id="imageDescription" class="image-description"></div>
    </div>
</div>
<!--end::App Content-->

<!-- Modal para Actualizar PVP -->
<div class="modal fade" id="modalActualizarPVP" tabindex="-1" aria-labelledby="modalActualizarPVPLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalActualizarPVPLabel">
                    <i class="bi bi-currency-dollar me-2"></i>Actualizar Precio de Venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información del producto -->
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <img id="productoImagen" src="" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" alt="Producto" onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                    </div>
                    <div class="col-md-9">
                        <h5 id="productoDescripcion" class="fw-bold"></h5>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>Código:</strong> <span id="productoCodigo" class="badge bg-light text-dark"></span></p>
                                <p class="mb-1"><strong>Existencia:</strong> <span id="productoExistencia" class="fw-bold"></span></p>
                                <p class="mb-1"><strong>Costo:</strong> <span id="productoCosto" class="text-muted"></span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>PVP Actual:</strong> <span id="productoPVPActual" class="text-success fw-bold"></span></p>
                                <p class="mb-1"><strong>PVP Anterior:</strong> <span id="productoPVPAnterior" class="text-muted"></span></p>
                                <p class="mb-1"><strong>Último Cambio:</strong> <span id="productoFechaCambio" class="text-info"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Formulario de actualización -->
                <form id="formActualizarPVP" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="productoId" name="producto_id">
                    <input type="hidden" id="sucursalId" name="sucursal_id" value="{{ $sucursalId ?? 0 }}">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="nuevoPVP" class="form-label fw-bold">
                                <i class="bi bi-arrow-up-circle me-1"></i>Nuevo PVP ($)
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-warning text-dark fw-bold">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="nuevoPVP" 
                                       name="nuevo_pvp" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       placeholder="0.00"
                                       style="font-size: 1.25rem;">
                            </div>
                            <div class="form-text">Ingrese el nuevo precio de venta al público</div>
                        </div>
                    </div>
                    
                    <!-- Alerta de validación -->
                    <div class="alert alert-danger d-none" id="alertaValidacion">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="mensajeValidacion"></span>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning fw-bold" id="btnGuardarCambio">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambio de Precio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para filtrar la tabla
    function filtrarTabla() {
        var input, filter, table, tr, tdCodigo, tdDescripcion, i, txtValueCodigo, txtValueDescripcion;
        input = document.getElementById("buscarProducto");
        filter = input.value.toUpperCase();
        table = document.getElementById("tablaIndiceRotacion");
        tr = table.getElementsByTagName("tr");
        
        // Recorrer todas las filas de la tabla, empezando desde la 1 (omitir encabezado)
        for (i = 1; i < tr.length; i++) {
            // Obtener celdas de código (columna 2) y descripción (columna 3)
            tdCodigo = tr[i].getElementsByTagName("td")[1]; // Índice 1 para código
            tdDescripcion = tr[i].getElementsByTagName("td")[2]; // Índice 2 para descripción
            
            if (tdCodigo && tdDescripcion) {
                txtValueCodigo = tdCodigo.textContent || tdCodigo.innerText;
                txtValueDescripcion = tdDescripcion.textContent || tdDescripcion.innerText;
                
                // Mostrar la fila si coincide con código O descripción
                if (txtValueCodigo.toUpperCase().indexOf(filter) > -1 || 
                    txtValueDescripcion.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Validación de fechas
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            var fechaFin = document.getElementById('fecha_fin');
            if (this.value > fechaFin.value) {
                fechaFin.value = this.value;
            }
        });
        
        document.getElementById('fecha_fin').addEventListener('change', function() {
            var fechaInicio = document.getElementById('fecha_inicio');
            if (this.value < fechaInicio.value) {
                fechaInicio.value = this.value;
            }

            // Agregar evento Enter en el campo de búsqueda
            document.getElementById("buscarProducto").addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });
        });

        // Manejar envío del formulario
        document.getElementById('formActualizarPVP').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nuevoPVP = parseFloat(document.getElementById('nuevoPVP').value);
            const pvpActual = parseFloat(productoActual.pvp_actual);
            
            if (nuevoPVP <= 0) {
                alert('El PVP debe ser mayor a 0');
                return;
            }
            
            if (nuevoPVP === pvpActual) {
                alert('El nuevo PVP no puede ser igual al actual');
                return;
            }
            
            // Confirmar cambio
            if (confirm(`¿Está seguro de cambiar el PVP de $${pvpActual.toFixed(2)} a $${nuevoPVP.toFixed(2)}?`)) {
                // Mostrar loading
                const btn = document.getElementById('btnGuardarCambio');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Procesando...';
                btn.disabled = true;
                
                // Enviar formulario
                this.submit();
            }
        });
    });
    
    function exportarExcel() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Obtener datos de la tabla
        const datos = [];
        
        // Encabezados (excluir columna de acciones si existe)
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            // Excluir la última columna si es "Acciones" o similar
            const texto = th.textContent.trim();
            if (!texto.toLowerCase().includes('accion') && 
                !texto.toLowerCase().includes('acción') &&
                texto !== '#') {
                headers.push(texto);
            }
        });
        datos.push(headers);
        
        // Filas del cuerpo de la tabla (solo las visibles)
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display !== 'none') {
                const rowData = [];
                fila.querySelectorAll('td').forEach((td, index) => {
                    // Omitir la última columna (acciones)
                    const thCorrespondiente = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                    if (thCorrespondiente) {
                        const textoTh = thCorrespondiente.textContent.trim();
                        if (!textoTh.toLowerCase().includes('accion') && 
                            !textoTh.toLowerCase().includes('acción') &&
                            textoTh !== '#') {
                            
                            // Obtener texto limpio
                            let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                            
                            // Si tiene badge, tomar el texto del badge
                            const badge = td.querySelector('.badge');
                            if (badge) {
                                texto = badge.textContent.trim();
                            }
                            
                            // Si es la columna de índice, asegurar formato numérico
                            if (textoTh.includes('Índice') || textoTh.includes('Indice')) {
                                // Convertir a número si es posible
                                const numero = parseFloat(texto.replace(',', '.'));
                                if (!isNaN(numero)) {
                                    texto = numero;
                                }
                            }
                            
                            // Si es la columna de costo o PVP, limpiar formato de moneda
                            if (textoTh.includes('Costo') || textoTh.includes('PVP') || textoTh.includes('Precio')) {
                                // Remover símbolo $ y convertir a número
                                texto = texto.replace('$', '').trim();
                                const numero = parseFloat(texto.replace(',', ''));
                                if (!isNaN(numero)) {
                                    texto = numero;
                                }
                            }
                            
                            rowData.push(texto);
                        }
                    }
                });
                datos.push(rowData);
            }
        });
        
        // Verificar que hay datos
        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }
        
        // Crear workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);
        
        // Ajustar anchos de columna automáticamente
        const maxColLengths = [];
        datos.forEach(row => {
            row.forEach((cell, colIndex) => {
                const cellLength = String(cell).length;
                if (!maxColLengths[colIndex] || cellLength > maxColLengths[colIndex]) {
                    maxColLengths[colIndex] = cellLength;
                }
            });
        });
        
        const colWidths = maxColLengths.map(length => ({ 
            wch: Math.min(Math.max(length, 10), 50) // Mínimo 10, máximo 50 caracteres
        }));
        ws['!cols'] = colWidths;
        
        // Agregar hoja al workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Indice Rotacion');
        
        // Generar y descargar archivo
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Indice_Rotacion_${fecha}.xlsx`);
    }
    
    // Función para resetear filtros
    function resetearFiltros() {
        var hoy = new Date().toISOString().split('T')[0];
        var inicioMes = new Date();
        inicioMes.setDate(1);
        var inicioMesStr = inicioMes.toISOString().split('T')[0];
        
        document.getElementById('fecha_inicio').value = inicioMesStr;
        document.getElementById('fecha_fin').value = hoy;
        document.getElementById('filtroForm').submit();
    }
    
    // Función para ordenar tabla (opcional)
    function ordenarTabla(columna, direccion) {
        // Implementar ordenamiento de tabla si es necesario
    }
    
    // Función para generar PDF con imágenes
    async function pdfTabla() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Crear documento PDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        
        // Título del documento
        const titulo = 'Índice de Rotación - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);
        
        // Configuración de columnas
        const columnas = [];
        const datos = [];
        
        // Obtener encabezados (excluir columna de acciones)
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            const texto = th.textContent.trim();
            // Excluir columnas de acciones
            if (!texto.toLowerCase().includes('accion') && 
                !texto.toLowerCase().includes('acción') &&
                texto !== '#') {
                columnas.push({
                    header: texto,
                    dataKey: `col${index}`
                });
            }
        });
        
        // Preparar datos para la tabla
        let filaNumero = 0;
        const promesasImagenes = [];
        
        tabla.querySelectorAll('tbody tr').forEach((fila, filaIndex) => {
            if (fila.style.display !== 'none') {
                filaNumero++;
                const filaData = {};
                let colIndex = 0;
                
                fila.querySelectorAll('td').forEach((td, tdIndex) => {
                    const thCorrespondiente = tabla.querySelector(`thead th:nth-child(${tdIndex + 1})`);
                    if (thCorrespondiente) {
                        const textoTh = thCorrespondiente.textContent.trim();
                        if (!textoTh.toLowerCase().includes('accion') && 
                            !textoTh.toLowerCase().includes('acción') &&
                            textoTh !== '#') {
                            
                            // Si es la columna de foto, guardar la URL de la imagen
                            if (textoTh.toLowerCase().includes('foto')) {
                                const imgElement = td.querySelector('img');
                                if (imgElement && imgElement.src) {
                                    // Crear promesa para cargar la imagen
                                    promesasImagenes.push({
                                        filaIndex: filaNumero - 1,
                                        colIndex: colIndex,
                                        imgUrl: imgElement.src,
                                        filaData: filaData,
                                        dataKey: `col${colIndex}`
                                    });
                                    filaData[`col${colIndex}`] = ''; // Espacio reservado
                                } else {
                                    filaData[`col${colIndex}`] = '';
                                }
                            } else {
                                // Procesar otras columnas normalmente
                                filaData[`col${colIndex}`] = procesarContenidoParaPDF(td, textoTh);
                            }
                            
                            colIndex++;
                        }
                    }
                });
                
                // Agregar número de fila
                filaData.col0 = filaNumero.toString();
                datos.push(filaData);
            }
        });
        
        // Agregar columna de número de fila
        if (columnas[0] && columnas[0].header !== '#') {
            columnas.unshift({
                header: '#',
                dataKey: 'col0'
            });
        }
        
        // Crear tabla básica primero
        doc.autoTable({
            head: [columnas.map(col => col.header)],
            body: datos.map(fila => columnas.map(col => fila[col.dataKey] || '')),
            startY: 30,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold'
            },
            bodyStyles: {
                fontSize: 8,
                cellPadding: 2
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                col0: { cellWidth: 15, halign: 'center' },
                col1: { cellWidth: 25, halign: 'center' }, // Foto
                col2: { cellWidth: 30 }, // Código
                col3: { cellWidth: 60, fontStyle: 'bold' }, // Descripción
                col4: { cellWidth: 25, halign: 'center' }, // Existencia
                col5: { cellWidth: 30, halign: 'center' }, // Ventas
                col6: { cellWidth: 25, halign: 'right' }, // Costo
                col7: { cellWidth: 25, halign: 'right' }, // PVP
                col8: { cellWidth: 30, halign: 'center' }, // Índice
                col9: { cellWidth: 35, halign: 'center' }  // Última Venta
            },
            margin: { top: 35 }
        });
        
        // Obtener información de las celdas para agregar imágenes después
        const table = doc.autoTable.previous;
        
        // Función para cargar y agregar imágenes
        async function agregarImagenes() {
            for (const imgInfo of promesasImagenes) {
                try {
                    const base64 = await cargarImagenABase64(imgInfo.imgUrl);
                    if (base64) {
                        // Encontrar la celda correspondiente
                        if (table && table.cells && table.cells[imgInfo.filaIndex]) {
                            const cell = table.cells[imgInfo.filaIndex][imgInfo.colIndex + 1]; // +1 por columna #
                            if (cell) {
                                // Calcular posición centrada
                                const x = cell.x + (cell.width - 20) / 2;
                                const y = cell.y + (cell.height - 20) / 2;
                                
                                doc.addImage(
                                    base64,
                                    'JPEG',
                                    x,
                                    y,
                                    20,
                                    20
                                );
                            }
                        }
                    }
                } catch (error) {
                    console.log('Error cargando imagen:', imgInfo.imgUrl, error);
                }
            }
        }
        
        // Agregar imágenes
        await agregarImagenes();
        
        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(
                `Página ${i} de ${totalPaginas}`,
                doc.internal.pageSize.width - 30,
                doc.internal.pageSize.height - 10
            );
            doc.text(
                `Generado: ${new Date().toLocaleString('es-ES')}`,
                14,
                doc.internal.pageSize.height - 10
            );
        }
        
        // Descargar PDF
        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Indice_Rotacion_${fecha}.pdf`);
    }

    // Función para cargar imagen a base64
    function cargarImagenABase64(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Redimensionar para PDF
                const maxSize = 50;
                let width = img.width;
                let height = img.height;
                
                // Mantener proporción
                if (width > height) {
                    if (width > maxSize) {
                        height = (height * maxSize) / width;
                        width = maxSize;
                    }
                } else {
                    if (height > maxSize) {
                        width = (width * maxSize) / height;
                        height = maxSize;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convertir a JPEG con calidad media
                const dataURL = canvas.toDataURL('image/jpeg', 0.7);
                resolve(dataURL);
            };
            
            img.onerror = function() {
                console.log('No se pudo cargar la imagen:', url);
                // Usar imagen por defecto si hay error
                const imagenDefault = document.querySelector('img[onerror]')?.getAttribute('onerror')?.match(/src='([^']+)'/)?.[1];
                if (imagenDefault) {
                    // Intentar con imagen por defecto
                    const imgDefault = new Image();
                    imgDefault.onload = function() {
                        const canvas = document.createElement('canvas');
                        canvas.width = 50;
                        canvas.height = 50;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(imgDefault, 0, 0, 50, 50);
                        resolve(canvas.toDataURL('image/jpeg'));
                    };
                    imgDefault.onerror = () => resolve(null);
                    imgDefault.src = imagenDefault;
                } else {
                    resolve(null);
                }
            };
            
            img.src = url;
        });
    }

    // Función para procesar contenido
    function procesarContenidoParaPDF(td, tituloColumna) {
        let texto = '';
        
        // Obtener texto del badge si existe
        const badge = td.querySelector('.badge');
        if (badge) {
            texto = badge.textContent.trim();
        } else {
            // Obtener texto limpio
            texto = td.textContent.trim()
                .replace(/\n/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }
        
        // Procesar según tipo de columna
        if (tituloColumna.includes('Índice') || tituloColumna.includes('Indice')) {
            const numero = parseFloat(texto.replace(',', '.'));
            return isNaN(numero) ? texto : numero.toFixed(2);
        }
        
        if (tituloColumna.includes('Costo') || tituloColumna.includes('PVP') || 
            tituloColumna.includes('Precio')) {
            const numero = parseFloat(texto.replace('$', '').replace(',', '').trim());
            return isNaN(numero) ? texto : '$' + numero.toFixed(2);
        }
        
        // Limitar descripción
        if (tituloColumna.includes('Descripción')) {
            if (texto.length > 40) {
                return texto.substring(0, 37) + '...';
            }
        }
        
        return texto;
    }

    // Versión más simple pero que SÍ muestra imágenes
    function pdfTablaConImagenes() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Mostrar mensaje de carga
        const loading = document.createElement('div');
        loading.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 9999;
        `;
        loading.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light"></div><p class="mt-2">Generando PDF con imágenes...</p></div>';
        document.body.appendChild(loading);
        
        // Generar PDF después de un breve delay para que se carguen las imágenes
        setTimeout(async () => {
            try {
                await pdfTabla();
            } catch (error) {
                console.error('Error generando PDF:', error);
                alert('Error generando PDF. Intente nuevamente.');
            } finally {
                document.body.removeChild(loading);
            }
        }, 1000);
    }   

    // Abrir zoom al hacer clic
    document.querySelectorAll('.img-zoomable').forEach(img => {
        img.addEventListener('click', function() {
            const fullImage = this.getAttribute('data-full-image');
            const description = this.getAttribute('data-description');
            
            document.getElementById('zoomedImage').src = fullImage;
            document.getElementById('imageDescription').textContent = description;
            document.getElementById('imageZoomOverlay').style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        });
    });

    // Cerrar zoom
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeZoom();
        }
    });

    // Cerrar al hacer clic fuera de la imagen
    document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeZoom();
        }
    });
    
    // Variable global para almacenar datos del producto
    let productoActual = null;
    let sucursalIdActual = 0;
    
    function abrirModalActualizarPVP(productoId, sucursalId, datos) {
        // Almacenar datos globalmente
        productoActual = datos;
        productoActual.id = productoId;
        sucursalIdActual = sucursalId;

        // Verificar si se puede modificar (sucursalId debe ser diferente de 0)
        if (sucursalId === 0 || sucursalId === '0') {
            mostrarAlertaSucursalNoSeleccionada();
            return; // No abrir el modal
        }
        
        // Establecer la ruta del formulario
        const form = document.getElementById('formActualizarPVP');
        
        // Llenar información del producto
        document.getElementById('productoId').value = productoId;
        document.getElementById('productoCodigo').textContent = datos.codigo;
        document.getElementById('productoDescripcion').textContent = datos.descripcion;
        document.getElementById('productoExistencia').textContent = datos.existencia;
        document.getElementById('productoCosto').textContent = `$${parseFloat(datos.costo_divisa).toFixed(2)}`;
        document.getElementById('productoPVPActual').textContent = `$${parseFloat(datos.pvp_actual).toFixed(2)}`;
        document.getElementById('productoPVPAnterior').textContent = datos.pvp_anterior ? `$${parseFloat(datos.pvp_anterior).toFixed(2)}` : 'N/A';
        document.getElementById('productoFechaCambio').textContent = datos.fecha_nuevo_precio ? 
            new Date(datos.fecha_nuevo_precio).toLocaleDateString('es-ES') : 'Nunca';
        
        // Cargar imagen del producto - ¡USAR LA URL PASADA!
        const productoImagen = document.getElementById('productoImagen');
        if (datos.url_foto && datos.url_foto.trim() !== '') {
            // Usar la imagen del producto
            productoImagen.src = datos.url_foto;
            productoImagen.onerror = function() {
                // Si falla, cargar imagen por defecto
                this.src = '{{ asset("assets/img/adminlte/img/produc_default.jfif") }}';
                this.onerror = null; // Prevenir bucles infinitos
            };
        } else {
            // Imagen por defecto
            productoImagen.src = '{{ asset("assets/img/adminlte/img/produc_default.jfif") }}';
        }
        
        // Establecer el nuevo PVP sugerido (actual + 10%)
        const pvpActual = parseFloat(datos.pvp_actual) || 0;
        const nuevoPVPSugerido = 0.00; // 10% más
        document.getElementById('nuevoPVP').value = nuevoPVPSugerido.toFixed(2);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalActualizarPVP'));
        modal.show();
    }

    // Función para mostrar alerta cuando no hay sucursal seleccionada
    function mostrarAlertaSucursalNoSeleccionada() {
        Swal.fire({
            icon: 'warning',
            title: 'Sucursal no seleccionada',
            html: `
                <div class="text-start">
                    <p>No se puede modificar el PVP porque no hay una sucursal específica seleccionada.</p>
                    <p class="mb-0"><strong>Para modificar precios:</strong></p>
                    <ol class="mt-2">
                        <li>Seleccione una sucursal específica en el filtro superior</li>
                        <li>Vuelva a intentar modificar el precio</li>
                    </ol>
                </div>
            `,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Entendido',
        }).then((result) => {
            if (result.isDismissed) {
                // Opcional: Redirigir o realizar alguna acción cuando el usuario elige "Ver todas"
                console.log('Usuario eligió mantener vista de todas las sucursales');
            }
        });
    }

    // Función para cerrar modal
    function cerrarModalActualizarPVP() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalActualizarPVP'));
        modal.hide();
    }
</script>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .empty-state {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .table th {
        white-space: nowrap;
    }
    
    .badge.bg-opacity-10 {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    
    @media print {
        .card-header, .card-footer, .btn-group, .app-content-header, .breadcrumb {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 11px;
        }
    }

    /* ===== ESTILOS PARA ZOOM DE IMAGENES ===== */
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }

    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Overlay para zoom */
    .image-zoom-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeInOverlay 0.3s ease-out;
    }

    .image-zoom-container {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .image-zoom-container img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        animation: zoomInImage 0.3s ease-out;
    }

    .image-zoom-close {
        position: absolute;
        top: -40px;
        right: -10px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 10000;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        background: rgba(0, 0, 0, 0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .image-zoom-close:hover {
        color: #ff6b6b;
        background: rgba(0, 0, 0, 0.7);
    }

    .image-description {
        color: white;
        text-align: center;
        margin-top: 20px;
        font-size: 1.1rem;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 20px;
        border-radius: 8px;
        max-width: 80%;
    }

    /* Animaciones */
    @keyframes fadeInOverlay {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes zoomInImage {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Para tablets y móviles */
    @media (max-width: 768px) {
        .image-zoom-container {
            max-width: 95%;
        }
        
        .image-zoom-container img {
            max-height: 70vh;
        }
        
        .image-zoom-close {
            top: -35px;
            right: 0;
            font-size: 35px;
            width: 45px;
            height: 45px;
        }
        
        .image-description {
            font-size: 1rem;
            padding: 8px 16px;
            max-width: 90%;
        }
    }

    @media (max-width: 576px) {
        .image-zoom-container img {
            max-height: 60vh;
        }
        
        .image-zoom-close {
            top: -30px;
            font-size: 30px;
            width: 40px;
            height: 40px;
        }
        
        .image-description {
            font-size: 0.9rem;
            margin-top: 15px;
        }
    }

    /* Para impresión */
    @media print {
        .image-zoom-overlay {
            display: none !important;
        }
        
        .img-zoomable {
            cursor: default !important;
        }
    }

    /* Estilos para el modal de actualización */
    #modalActualizarPVP .modal-header {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }

    #resumenCambio {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .input-group-text {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .form-control-lg {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .badge.bg-light {
        border: 1px solid #dee2e6;
    }

    /* Estilo para el botón de actualizar */
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }
</style>
@endsection