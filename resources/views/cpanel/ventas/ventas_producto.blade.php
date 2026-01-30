@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Ventas por producto')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Ventas por producto</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Ventas por producto</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
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
                <form action="{{ route('cpanel.ventas.producto') }}" method="GET" id="filtroForm">
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
        
        @if($ventas)
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header">
                <div class="row g-2">
                    <div class="col-12 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pdfTablaVentasProducto()">
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
                    <table class="table table-hover mb-0" id="tablaProductosAgrupados">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="60" class="text-center">Imagen</th>
                                <th width="100" class="text-center">Producto</th>
                                <th>Descripción</th>
                                <th width="150" class="text-center">Cantidad</th>
                                <th width="120" class="text-center">Costo ($)</th>
                                <th width="120" class="text-end">PVP ($)</th>
                                <th width="120" class="text-end">Total ($)</th>
                                <th width="120" class="text-end">Margen %</th>
                                <th width="120" class="text-end">Utilidad ($)</th>
                                <th width="80" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalCosto = 0;
                                $totalVentas = 0;
                                $totalUtilidad = 0;
                            @endphp
                            
                            @foreach($ventas['ProductosAgrupados'] as $productoId => $producto)
                                @php
                                    $productoInfo = $producto['Producto'];
                                    $cantidad = $producto['CantidadTotal'] ?? 0;
                                    $montoTotal = $producto['MontoTotal'] ?? 0;
                                    $costoTotal = $producto['CostoTotalDivisa'] ?? 0;
                                    $pvpUnitario = $cantidad > 0 ? $montoTotal / $cantidad : 0;
                                    $costoUnitario = $cantidad > 0 ? $costoTotal / $cantidad : 0;
                                    $utilidad = $producto['UtilidadProducto'] ?? 0;
                                    $margen = $producto['MargenProducto'] ?? 0;
                                    $porcentaje = $producto['PorcentajeParticipacion'] ?? 0;
                                    
                                    // Acumular totales
                                    $totalCosto += $costoTotal;
                                    $totalVentas += $montoTotal;
                                    $totalUtilidad += $utilidad;

                                    $urlImagen = FileHelper::getOrDownloadFile(
                                        'images/items/thumbs/',
                                        $productoInfo->UrlFoto ?? '',
                                        'assets/img/adminlte/img/produc_default.jfif'
                                    );
                                    
                                    $descripcion = $productoInfo->Descripcion ?? 'Sin descripción';
                                    $codigo = $productoInfo->Codigo ?? 'N/A';
                                    $referencia = $productoInfo->Referencia ?? 'N/A';
                                    $existencia = $productoInfo->Existencia ?? 0;
                                    
                                    // Determinar color del margen
                                    if ($margen >= 100) {
                                        $margenColor = 'bg-success';
                                        $margenIcon = 'fas fa-rocket';
                                    } elseif ($margen >= 50) {
                                        $margenColor = 'bg-info';
                                        $margenIcon = 'fas fa-chart-line';
                                    } elseif ($margen >= 0) {
                                        $margenColor = 'bg-primary';
                                        $margenIcon = 'fas fa-arrow-up';
                                    } else {
                                        $margenColor = 'bg-danger';
                                        $margenIcon = 'fas fa-arrow-down';
                                    }
                                @endphp
                                
                                <tr class="align-middle">
                                    <!-- Columna Imagen -->
                                    <td class="text-center">
                                        <div class="position-relative">
                                            <img src="{{ $urlImagen }}" 
                                                alt="{{ $descripcion }}"
                                                class="img-thumbnail rounded img-zoomable" 
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                                data-full-image="{{ $urlImagen }}"
                                                data-description="{{ $descripcion }}"
                                                title="{{ $descripcion }}"
                                                onerror="this.onerror=null; this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}';">
                                        </div>
                                    </td>               

                                    <!-- Columna Código / Referencia (combinada) -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-dark text-white mb-1" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-title="Código del producto">
                                                {{ $codigo }}
                                            </span>
                                            @if($referencia && $referencia != 'N/A' && $referencia != $codigo)
                                                <small class="text-muted" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-title="Referencia del fabricante">
                                                    <i class="fas fa-barcode me-1"></i>Ref. {{ $referencia }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Columna Descripción -->
                                    <td>
                                        <div>
                                            <strong class="d-block mb-1" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-title="{{ $descripcion }}">
                                                {{ $descripcion }}
                                            </strong>
                                        </div>
                                    </td>
                                    
                                    <!-- Columna Cantidad -->
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-2 py-1"
                                            data-bs-toggle="tooltip" 
                                            data-bs-title="{{ number_format($cantidad) }} unidades vendidas">
                                            {{ number_format($cantidad) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Columna Costo -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column">
                                            <strong>${{ number_format($costoUnitario, 2, ',', '.') }}</strong>
                                        </div>
                                    </td>
                                    
                                    <!-- Columna PVP -->
                                    <td class="text-end">
                                        <strong class="text-success fs-6"
                                                data-bs-toggle="tooltip" 
                                                data-bs-title="Precio de venta por unidad">
                                            ${{ number_format($pvpUnitario, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    
                                    <!-- Columna Total -->
                                    <td class="text-end">
                                        <div>
                                            <strong class="text-primary fs-6"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-title="Total ventas de este producto">
                                                ${{ number_format($montoTotal, 2, ',', '.') }}
                                            </strong>
                                        </div>
                                    </td>
                                    
                                    <!-- Columna Margen -->
                                    <td class="text-end">
                                        <span class="badge {{ $margenColor }} text-white px-2 py-1" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-title="Margen de ganancia">
                                            <i class="{{ $margenIcon }} me-1"></i>
                                            {{ number_format($margen, 1, ',', '.') }}%
                                        </span>
                                    </td>
                                    
                                    <!-- Columna Utilidad -->
                                    <td class="text-end">
                                        <span class="badge {{ $utilidad >= 0 ? 'bg-success' : 'bg-danger' }} text-white px-2 py-1"
                                            data-bs-toggle="tooltip" 
                                            data-bs-title="Utilidad generada">
                                            ${{ number_format($utilidad, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    
                                    <!-- Columna Acción -->
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Ver detalles"
                                                onclick="verDetalleProducto({{ $productoId ?? '0' }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">TOTALES:</td>
                                <td class="text-center">
                                    <span class="badge bg-primary px-3 py-2">
                                        {{ number_format($ventas['TotalProductosVendidos'] ?? 0) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column align-items-end">
                                        <small class="text-muted">Total Costo:</small>
                                        <span class="text-dark fs-6">${{ number_format($totalCosto, 2, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="text-end">-</td>
                                <td class="text-end">
                                    <span class="text-primary fs-6">
                                        ${{ number_format($totalVentas, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ ($ventas['MargenProductos'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }} text-white px-3 py-2">
                                        {{ number_format($ventas['MargenProductos'] ?? 0, 2, ',', '.') }}%
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fs-6">
                                        ${{ number_format($totalUtilidad, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <i class="fas fa-cube me-1"></i>
                        {{ $ventas['TotalProductosUnicos'] ?? 0 }} productos únicos
                    </div>
                    
                    <div class="fw-bold text-success">
                        <i class="fas fa-chart-line me-1"></i>
                        Ventas Totales: ${{ number_format($ventas['TotalVentasProductosDivisa'] ?? 0, 2, ',', '.') }}
                    </div>
                    
                    <div class="fw-bold text-primary">
                        <i class="fas fa-percentage me-1"></i>
                        Margen Global: {{ $ventas['MargenProductos'] ?? 0 }}%
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
                        No se encontraron registros para el período seleccionado o la sucursal.
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
    // Definir el array global al inicio de tu script
    let productosActualizados = [];

    // Muestra un toast si no hay ventas
    @if(is_null($ventas))
        showToast('Seleccione una sucursal', 'danger');
    @endif

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
        });

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaProductosAgrupados');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true; // alterna asc/desc

            ths.forEach((th, index) => {
                const texto = th.textContent.trim().toLowerCase();

                // Evitar columnas que no queremos ordenar
                if (texto.includes('accion') || th.querySelector('input[type="checkbox"]') || texto.includes('imagen')) return;

                th.style.cursor = 'pointer';

                th.addEventListener('click', () => {
                    ordenarTabla(tabla, index, ordenAscendente);
                    ordenAscendente = !ordenAscendente;
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr'));

                filas.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];

                    if (!tdA || !tdB) return 0;

                    const textoA = extraerValorCelda(tdA);
                    const textoB = extraerValorCelda(tdB);

                    const numA = parseFloat(textoA.replace(/[^\d.-]/g, ''));
                    const numB = parseFloat(textoB.replace(/[^\d.-]/g, ''));

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    } else {
                        return asc ? textoA.localeCompare(textoB) : textoB.localeCompare(textoA);
                    }
                });

                filas.forEach(fila => tbody.appendChild(fila));
            }

            function extraerValorCelda(td) {

                // 1️⃣ PRIORIDAD ABSOLUTA: data-order (fechas, valores ocultos)
                if (td.dataset && td.dataset.order) {
                    return td.dataset.order;
                }

                // 2️⃣ Paralelo
                const paralelo = td.querySelector('[id^="paralelo-"]');
                if (paralelo) {
                    return paralelo.textContent.replace('P:', '').replace('$','').trim();
                }

                // 3️⃣ Precio
                const precio = td.querySelector('.precioPVP');
                if (precio) {
                    return precio.textContent.replace('$','').trim();
                }

                // 4️⃣ Badge (texto)
                const badge = td.querySelector('.badge');
                if (badge) {
                    return badge.textContent.trim();
                }

                // 5️⃣ Texto plano
                return td.textContent.trim();
            }

        })();

    });

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

    function obtenerTasasActuales() {
        const bcv = parseFloat(
            document.querySelector('#tasa-actual-texto')?.dataset.tasa ?? 0
        );

        const paralelo = parseFloat(
            document.querySelector('#tasa-actual-texto-paralelo')?.dataset.tasa ?? 0
        );

        return { bcv, paralelo };
    }
    
    function exportarExcel() {
        const tabla = document.getElementById('tablaProductosAgrupados');

        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // =========================
        // ENCABEZADOS
        // =========================
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th) => {
            const texto = th.textContent.trim().toLowerCase();

            // Ignorar columnas
            if (
                texto.includes('imagen') ||
                texto.includes('acción') ||
                texto.includes('accion')
            ) {
                return;
            }

            headers.push(th.textContent.trim());
        });
        datos.push(headers);

        // =========================
        // FILAS
        // =========================
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const rowData = [];

            fila.querySelectorAll('td').forEach((td, index) => {
                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;

                const textoTh = th.textContent.trim().toLowerCase();

                // Saltar columnas de imagen y acción
                if (
                    textoTh.includes('imagen') ||
                    textoTh.includes('acción') ||
                    textoTh.includes('accion')
                ) {
                    return;
                }

                let texto = td.textContent.trim();

                // Si hay badge se usa el texto interno del badge
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }

                // normalizar varios formatos
                texto = texto.replace(/\n/g, ' ').replace(/\s+/g, ' ');

                // Convertir a número si aplica
                if (
                    textoTh.includes('cantidad') ||
                    textoTh.includes('costo') ||
                    textoTh.includes('pvp') ||
                    textoTh.includes('total') ||
                    textoTh.includes('margen') ||
                    textoTh.includes('utilidad')
                ) {
                    texto = texto.replace('$', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
                    const numero = parseFloat(texto);
                    texto = isNaN(numero) ? texto : numero;
                }

                rowData.push(texto);
            });

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        // =========================
        // CREAR EXCEL
        // =========================
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);

        // Auto ancho columnas
        const maxColLengths = [];
        datos.forEach(row => {
            row.forEach((cell, colIndex) => {
                const length = String(cell).length;
                maxColLengths[colIndex] = Math.max(maxColLengths[colIndex] || 10, length);
            });
        });
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 50) }));

        XLSX.utils.book_append_sheet(wb, ws, 'Productos Vendidos');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `VentasPorProducto_${fecha}.xlsx`);
    }
    
    // Función para ordenar tabla (opcional)
    function ordenarTabla(columna, direccion) {
        // Implementar ordenamiento de tabla si es necesario
    }
    
    // Función principal para generar PDF de Ventas Diarias
    function pdfTablaVentasProducto() {
        const tabla = document.getElementById('tablaProductosAgrupados');

        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        const titulo = 'Productos Vendidos - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);

        // =========================
        // ENCABEZADOS
        // =========================
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th) => {
            const texto = th.textContent.trim().toLowerCase();

            if (
                texto.includes('imagen') ||
                texto.includes('acción') ||
                texto.includes('accion')
            ) {
                return;
            }

            headers.push(th.textContent.trim());
        });

        // =========================
        // FILAS
        // =========================
        const datos = [];
        tabla.querySelectorAll('tbody tr').forEach((fila) => {
            if (fila.style.display === 'none') return;

            const filaData = [];

            fila.querySelectorAll('td').forEach((td, index) => {
                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;

                const textoTh = th.textContent.trim().toLowerCase();

                if (
                    textoTh.includes('imagen') ||
                    textoTh.includes('acción') ||
                    textoTh.includes('accion')
                ) {
                    return;
                }

                let texto = td.textContent.trim()
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ');

                // Si hay badge, usar el texto del badge
                const badge = td.querySelector('.badge');
                if (badge) texto = badge.textContent.trim();

                // Normalizar números
                if (
                    textoTh.includes('cantidad') ||
                    textoTh.includes('costo') ||
                    textoTh.includes('pvp') ||
                    textoTh.includes('total') ||
                    textoTh.includes('utilidad') ||
                    textoTh.includes('margen')
                ) {
                    let value = texto.replace('$', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
                    let numero = parseFloat(value);

                    if (!isNaN(numero)) {
                        // Formato final según tipo
                        if (textoTh.includes('margen')) {
                            texto = numero.toFixed(1) + '%';
                        } else if (textoTh.includes('cantidad')) {
                            texto = numero.toFixed(0);
                        } else {
                            texto = numero.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }

                filaData.push(texto);
            });

            datos.push(filaData);
        });

        if (datos.length === 0) {
            alert('No hay datos para exportar');
            return;
        }

        // =========================
        // GENERAR TABLA PDF
        // =========================
        doc.autoTable({
            head: [headers],
            body: datos,
            startY: 30,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 10,
                fontStyle: 'bold'
            },
            bodyStyles: {
                fontSize: 9,
                cellPadding: 2
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            margin: { top: 35 },
            columnStyles: headers.reduce((acc, header, index) => {
                const h = header.toLowerCase();
                if (h.includes('costo') || h.includes('pvp') || h.includes('total') || h.includes('utilidad') || h.includes('margen')) {
                    acc[index] = { halign: 'right' };
                } else if (h.includes('cantidad')) {
                    acc[index] = { halign: 'center' };
                }
                return acc;
            }, {})
        });

        // =========================
        // FOOTER
        // =========================
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
            doc.text(`Generado: ${new Date().toLocaleString('es-ES')}`, 14, doc.internal.pageSize.height - 10);
        }

        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Productos_Vendidos_${fecha}.pdf`);
    }

    function verDetalleProducto(id) {
        var ruta = '{{ url("/") }}' + '/productos/' + id;
        window.location.href = ruta;
    }

    // Función para mostrar detalles en un modal con Bootstrap
    function mostrarDetallesVentaModal(detalles) {
        if (!detalles || detalles.length === 0) {
            showToast('No hay detalles para esta venta', 'warning');
            return;
        }
        
        // Calcular totales
        const totalDivisa = detalles.reduce((sum, d) =>
            sum + ((d.monto_divisa_unitario || 0) * (d.cantidad || 1)), 0);

        const totalVenta = detalles.reduce((sum, d) =>
            sum + ((d.precio || 0) * (d.cantidad || 1)), 0);

        const totalUtilidad = detalles.reduce((sum, d) =>
            sum + (d.utilidad_divisa || 0), 0);
            
        const promedioMargen = detalles.reduce((sum, d) => sum + (d.margen || 0), 0) / detalles.length;
        
        // Crear el contenido del modal
        const modalContent = `
            <div class="modal fade" id="detalleVentaModal" tabindex="-1" aria-labelledby="detalleVentaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="detalleVentaModalLabel">
                                <i class="bi bi-receipt"></i> Detalles de Venta
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <!-- Resumen de la venta -->
                            <div class="p-3 bg-light">
                                <div class="row text-center">
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                        <small class="text-muted">Total Items</small>
                                        <h4 class="mb-0">${detalles.length}</h4>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                        <small class="text-muted">Total Venta $</small>
                                        <h4 class="mb-0 text-primary">${formatCurrency(totalDivisa, '$')}</h4>
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                        <small class="text-muted">Utilidad Total</small>
                                        <h4 class="mb-0 ${totalUtilidad >= 0 ? 'text-success' : 'text-danger'}">
                                            ${formatCurrency(totalUtilidad, '$')}
                                        </h4>
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                        <small class="text-muted">Margen Promedio</small>
                                        <h4 class="mb-0 ${promedioMargen >= 0 ? 'text-success' : 'text-danger'}">
                                            ${promedioMargen.toFixed(2)}%
                                        </h4>
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-center align-items-center">
                                        <small class="text-muted">Productos con utilidad positiva</small>
                                        <h4 class="mb-0 text-success">
                                            ${detalles.filter(d => (d.utilidad_divisa || 0) > 0).length} / ${detalles.length}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de detalles -->
                            <div class="table-responsive" style="max-height: 400px;">
                                <table id="tablaDetalleVenta" class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="40%">Producto</th>
                                            <th class="text-center" width="10%">Cant.</th>
                                            <th class="text-end" width="15%">Precio $</th>
                                            <th class="text-end" width="15%">Total $</th>
                                            <th class="text-end" width="10%">Utilidad $</th>
                                            <th class="text-end" width="10%">Margen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${detalles.map((d, index) => {
                                            const producto = d.producto || {};
                                            const total = d.monto_divisa || 0;
                                            const precioUnitario = d.monto_divisa_unitario || 0;
                                            const utilidad = d.utilidad_divisa || 0;
                                            const margen = d.margen || 0;
                                            const margenClass = margen >= 0 ? 'text-success' : 'text-danger';
                                            
                                            return `
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <span class="badge bg-secondary">${index + 1}</span>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <small class="text-muted d-block">${producto.Codigo || 'N/A'}</small>
                                                                <strong class="d-block text-truncate" style="max-width: 250px;" title="${producto.Descripcion || 'Sin descripción'}">
                                                                    ${producto.Descripcion || 'Producto no encontrado'}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary rounded-pill">${d.cantidad}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>${formatCurrency(precioUnitario, '$')}</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>${formatCurrency(total, '$')}</strong>
                                                    </td>
                                                    <td class="text-end ${utilidad >= 0 ? 'text-success' : 'text-danger'}">
                                                        <strong>${formatCurrency(utilidad, '$')}</strong>
                                                    </td>
                                                    <td class="text-end ${margenClass}">
                                                        <span class="badge ${margen >= 0 ? 'bg-success' : 'bg-danger'}">
                                                            ${margen.toFixed(2)}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-warning" onclick='exportarPDF(${JSON.stringify(detalles)})'>
                                <i class="bi bi-download"></i> PDF
                            </button>
                            <button type="button" class="btn btn-primary" onclick="exportarDetallesVenta()">
                                <i class="bi bi-download"></i> Exportar
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Eliminar modal anterior si existe
        const existingModal = document.getElementById('detalleVentaModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Agregar nuevo modal al body
        document.body.insertAdjacentHTML('beforeend', modalContent);
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('detalleVentaModal'));
        modal.show();
    }

    // Función auxiliar para formatear moneda
    function formatCurrency(amount, symbol = '') {
        if (amount === null || amount === undefined) return `${symbol}0.00`;
        return `${symbol}${parseFloat(amount).toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }

    // Función para exportar detalles a Excel/CSV (opcional)
    function exportarDetallesVenta() {
        const tabla = document.querySelector('#detalleVentaModal table');

        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // =========================
        // ENCABEZADOS
        // =========================
        const headers = ['Código', 'Producto', 'Cantidad', 'Precio $', 'Total $', 'Utilidad $', 'Margen %'];
        datos.push(headers);

        // =========================
        // FILAS
        // =========================
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const rowData = [];

            // Código y Producto
            const productoTd = fila.querySelector('td:first-child');
            const codigo = productoTd.querySelector('small')?.textContent.trim() || '';
            const descripcion = productoTd.querySelector('strong')?.textContent.trim() || '';
            rowData.push(codigo);
            rowData.push(descripcion);

            // Cantidad
            const cantidad = fila.querySelector('td:nth-child(2) .badge')?.textContent.trim() || '';
            rowData.push(cantidad);

            // Precio $
            const precioText = fila.querySelector('td:nth-child(3)')?.textContent.trim().replace('$', '') || '0';
            rowData.push(parseFloat(precioText.replace(',', '.')).toFixed(2));

            // Total $
            const totalText = fila.querySelector('td:nth-child(4)')?.textContent.trim().replace('$', '') || '0';
            rowData.push(parseFloat(totalText.replace(',', '.')).toFixed(2));

            // Utilidad $
            const utilidadText = fila.querySelector('td:nth-child(5)')?.textContent.trim().replace('$', '') || '0';
            rowData.push(parseFloat(utilidadText.replace(',', '.')).toFixed(2));

            // Margen %
            const margenText = fila.querySelector('td:nth-child(6)')?.textContent.trim().replace('%', '') || '0';
            rowData.push(parseFloat(margenText.replace(',', '.')).toFixed(2));

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        // =========================
        // CREAR EXCEL
        // =========================
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);

        // Auto ancho columnas
        const maxColLengths = [];
        datos.forEach(row => {
            row.forEach((cell, colIndex) => {
                const length = String(cell).length;
                maxColLengths[colIndex] = Math.max(maxColLengths[colIndex] || 10, length);
            });
        });
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 50) }));

        XLSX.utils.book_append_sheet(wb, ws, 'Detalle Venta');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Detalle_Venta_${fecha}.xlsx`);
    }

    // Función para exportar detalles a PDF
    function exportarPDF(detalles) {
        if (!detalles || detalles.length === 0) {
            alert('No hay datos para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Nombre de la sucursal
        const nombreSucursal = detalles[0]?.producto?.Sucursal || 'Sucursal';
        const fecha = new Date().toLocaleDateString('es-ES');
        const titulo = `Detalle de Venta - ${nombreSucursal} - ${fecha}`;

        doc.setFontSize(16);
        doc.text(titulo, 14, 15);

        // Encabezados
        const headers = ['Código', 'Producto', 'Cantidad', 'Precio $', 'Total $', 'Utilidad $', 'Margen'];

        // Datos
        const datos = detalles.map(d => {
            const producto = d.producto || {};
            return [
                producto.Codigo || '',
                producto.Descripcion || '',
                d.cantidad || 0,
                parseFloat(d.monto_divisa_unitario || 0),
                parseFloat(d.monto_divisa || 0),
                parseFloat(d.utilidad_divisa || 0),
                (d.margen || 0).toFixed(2) + '%'
            ];
        });

        doc.autoTable({
            head: [headers],
            body: datos,
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9, cellPadding: 2 },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            margin: { top: 35 }
        });

        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
            doc.text(`Generado: ${new Date().toLocaleString('es-ES')}`, 14, doc.internal.pageSize.height - 10);
        }

        // Descargar PDF
        const fechaArchivo = new Date().toISOString().split('T')[0];
        doc.save(`Detalle_Venta_${nombreSucursal}_${fechaArchivo}.pdf`);
    }

    // Si no tienes Bootstrap, aquí hay una versión alternativa más simple:
    function mostrarDetallesVentaSimple(detalles) {
        if (!detalles || detalles.length === 0) {
            alert('No hay detalles para esta venta');
            return;
        }
        
        // Crear ventana/modal simple
        const modalHtml = `
            <div id="simpleVentaModal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1050;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            ">
                <div style="
                    background: white;
                    border-radius: 8px;
                    max-width: 900px;
                    width: 100%;
                    max-height: 90vh;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                ">
                    <div style="
                        background: #2c3e50;
                        color: white;
                        padding: 15px 20px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    ">
                        <h4 style="margin: 0; font-size: 1.2rem;">Detalles de Venta</h4>
                        <button onclick="document.getElementById('simpleVentaModal').remove()" style="
                            background: none;
                            border: none;
                            color: white;
                            font-size: 1.5rem;
                            cursor: pointer;
                        ">×</button>
                    </div>
                    
                    <div style="padding: 20px; overflow-y: auto; max-height: calc(90vh - 70px);">
                        <div style="
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 10px;
                            margin-bottom: 20px;
                            padding-bottom: 15px;
                            border-bottom: 1px solid #eee;
                        ">
                            <div>
                                <small style="color: #666;">Total Items</small>
                                <div style="font-size: 1.5rem; font-weight: bold;">${detalles.length}</div>
                            </div>
                            <div>
                                <small style="color: #666;">Total Venta $</small>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">
                                    ${formatCurrency(detalles.reduce((sum, d) => sum + (d.monto_divisa || 0), 0), '$')}
                                </div>
                            </div>
                            <div>
                                <small style="color: #666;">Utilidad Total</small>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">
                                    ${formatCurrency(detalles.reduce((sum, d) => sum + (d.utilidad_divisa || 0), 0), '$')}
                                </div>
                            </div>
                            <div>
                                <small style="color: #666;">Margen Prom.</small>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #e74c3c;">
                                    ${(detalles.reduce((sum, d) => sum + (d.margen || 0), 0) / detalles.length).toFixed(2)}%
                                </div>
                            </div>
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="background: #f8f9fa; position: sticky; top: 0;">
                                <tr>
                                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;">Producto</th>
                                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #dee2e6;">Cant.</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;">Precio $</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;">Total $</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;">Margen</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${detalles.map((d, index) => {
                                    const producto = d.producto || {};
                                    const margen = d.margen || 0;
                                    const margenColor = margen >= 0 ? '#27ae60' : '#e74c3c';
                                    
                                    return `
                                        <tr style="${index % 2 === 0 ? 'background: #f9f9f9;' : ''}">
                                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                                <div><small style="color: #666;">${producto.Codigo || 'N/A'}</small></div>
                                                <div style="font-weight: 500;">${producto.Descripcion || 'Sin descripción'}</div>
                                            </td>
                                            <td style="padding: 10px; text-align: center; border-bottom: 1px solid #eee;">
                                                <span style="
                                                    background: #3498db;
                                                    color: white;
                                                    padding: 2px 8px;
                                                    border-radius: 12px;
                                                    font-size: 0.9rem;
                                                ">${d.cantidad}</span>
                                            </td>
                                            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">
                                                ${formatCurrency(d.monto_divisa_unitario || 0, '$')}
                                            </td>
                                            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee;">
                                                <strong>${formatCurrency(d.monto_divisa || 0, '$')}</strong>
                                            </td>
                                            <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eee; color: ${margenColor};">
                                                <strong>${margen.toFixed(2)}%</strong>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        // Eliminar modal anterior si existe
        const existingModal = document.getElementById('simpleVentaModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Agregar nuevo modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

</script>

<style>
    /* Mejoras para la tabla */
    .table th {
        white-space: nowrap;
        font-size: 0.9rem;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .product-description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .avatar-sm img {
        transition: transform 0.2s;
    }
    
    .avatar-sm img:hover {
        transform: scale(1.1);
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    
    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* Mejora para badges en el footer */
    tfoot .badge {
        font-size: 0.9rem;
    }
    
    /* Responsive */
    @media (max-width: 1400px) {
        .product-description {
            max-width: 300px;
        }
    }
    
    @media (max-width: 1200px) {
        .product-description {
            max-width: 250px;
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
</style>
@endsection