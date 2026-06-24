@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Productos con Alta Demanda')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-graph-up-arrow text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Productos con Alta Demanda</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Productos más vendidos en el período seleccionado</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Alta Demanda</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        
        <!-- Card de filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-0 py-3" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-funnel me-2"></i>Filtros de búsqueda
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.alta.ventas') }}" method="GET" id="filtroForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <input type="hidden" id="fecha_inicio" name="fecha_inicio"
                            value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}">
                        
                        <div class="col-md-8">
                            <label for="fecha_fin" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>Seleccionar Fecha
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar-day text-muted"></i>
                                </span>
                                <input type="date" class="form-control border-start-0 ps-0" id="fecha_fin" name="fecha_fin"
                                    value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                                    style="border-left: none;" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold invisible">
                                <i class="fas fa-search me-1"></i>Acción
                            </label>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        @if($productosAltaDemanda && $productosAltaDemanda->count() > 0)
        <!-- Card de tabla -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center g-2">
                    <div class="col-md-3">
                        <select id="filtroValoracion" class="form-select form-select-sm" onchange="filtrarTabla()">
                            <option value="">⭐ Todas</option>
                            <option value="5">★★★★★ Muy alta</option>
                            <option value="4">★★★★☆ Alta</option>
                            <option value="3">★★★☆☆ Media</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="buscarProducto"
                            placeholder="Buscar código o descripción..." onkeyup="filtrarTabla()">
                    </div>

                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportarExcelAltaDemanda()">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pdfAltaDemanda()">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaAltaDemanda">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="checkAllAltaDemanda">
                                </th>
                                <th width="80" class="text-center">Imagen</th>
                                <th width="100">Código</th>
                                <th>Descripción</th>
                                <th width="100" class="text-center">Existencia</th>
                                <th width="120" class="text-center">Ventas</th>
                                <th width="100" class="text-center">Costo</th>
                                <th width="150" class="text-center">PVP</th>
                                <th width="120" class="text-center">Estrellas</th>
                                <th width="80" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        @php
                            $totalInventarioUSD = 0;
                        @endphp
                        <tbody>
                            @foreach($productosAltaDemanda as $detalle)
                            @php
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle['url_foto'] ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                
                                $existencia = $detalle['existencia'] ?? 0;
                                $estrellas = $detalle['estrellas'] ?? 1;
                                $costo = $detalle['costo'] ?? 0;
                                $pvpActual = $detalle['pvp_actual'] ?? 0;
                                $nuevoPvp = $detalle['nuevo_pvp'] ?? 0;
                                $porcentajeSubida = $detalle['porcentaje_subida'] ?? 0;
                                
                                // Determinar color de existencia
                                $colorExistencia = 'text-dark';
                                if ($existencia == 0) {
                                    $colorExistencia = 'text-danger fw-bold';
                                } elseif ($existencia <= 5) {
                                    $colorExistencia = 'text-warning';
                                }
                                
                                // Determinar color de estrellas
                                if ($estrellas >= 5) {
                                    $colorEstrellas = 'text-success';
                                } elseif ($estrellas >= 4) {
                                    $colorEstrellas = 'text-primary';
                                } else {
                                    $colorEstrellas = 'text-info';
                                }
                                
                                // Utilidad y Margen Actual
                                $utilidadActual = ($costo > 0 && $pvpActual > 0) ? round($pvpActual - $costo, 2) : 0;
                                $margenActual = ($costo > 0 && $pvpActual > 0) ? round((($pvpActual * 100) / $costo) - 100, 2) : 0;
                                
                                // Utilidad y Margen Nuevo
                                $utilidadNueva = ($costo > 0 && $nuevoPvp > 0) ? round($nuevoPvp - $costo, 2) : 0;
                                $margenNuevo = ($costo > 0 && $nuevoPvp > 0) ? round((($nuevoPvp * 100) / $costo) - 100, 2) : 0;
                                
                                // Tasas
                                $tasaBCV = $tasa['DivisaValor']['Valor'] ?? 0;
                                $tasaParalelo = $paralelo ?? 0;
                                
                                // Monto en dólares paralelos
                                $montoParaleloActual = ($pvpActual > 0 && $tasaBCV > 0 && $tasaParalelo > 0)
                                    ? round(($pvpActual * $tasaParalelo) / $tasaBCV, 2)
                                    : 0;
                                
                                $montoParaleloNuevo = ($nuevoPvp > 0 && $tasaBCV > 0 && $tasaParalelo > 0)
                                    ? round(($nuevoPvp * $tasaParalelo) / $tasaBCV, 2)
                                    : 0;
                                
                                $totalInventarioUSD += ($existencia * $costo);
                            @endphp
                            <tr class="align-middle" data-id="{{ $detalle['id'] }}" data-rating="{{ $estrellas }}">
                                <td class="text-center">
                                    <input type="checkbox" 
                                        name="productosSeleccionados[]" 
                                        class="checkProductoAltaDemanda"
                                        value="{{ $detalle['id'] }}"
                                        data-sucursal="{{ $detalle['sucursal_id'] }}">
                                </td>
                                
                                <!-- Foto -->
                                <td class="text-center">
                                    <div class="position-relative">
                                        <img src="{{ $urlImagen }}" 
                                            class="img-thumbnail rounded img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit:cover; cursor: zoom-in;"
                                            alt="{{ $detalle['descripcion'] ?? '' }}"
                                            data-full-image="{{ $urlImagen }}"
                                            data-description="{{ $detalle['descripcion'] }}"
                                            title="{{ $detalle['descripcion'] ?? '' }}"
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
                                    <span class="badge bg-light text-dark border">{{ $detalle['codigo'] }}</span>
                                </td>
                                
                                <!-- Descripción -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" data-bs-toggle="tooltip" title="{{ $detalle['descripcion'] ?? '' }}">
                                            {{ Str::limit($detalle['descripcion'] ?? 'Sin descripción', 40) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Existencia -->
                                <td class="text-center {{ $colorExistencia }}">
                                    {{ $existencia }}
                                </td>
                                
                                <!-- Ventas -->
                                <td class="text-center fw-bold text-primary">
                                    {{ $detalle['total_unidades'] }}
                                </td>
                                
                                <!-- Costo -->
                                <td class="text-center text-muted">
                                    ${{ number_format($costo, 2) }}
                                </td>
                                
                                <!-- PVP -->
                                <td class="text-center celdaPVP align-middle">
                                    <div class="d-flex flex-column">
                                        <!-- Precio Actual -->
                                        <div class="fw-bold text-danger">
                                            ${{ number_format($pvpActual, 2) }}
                                        </div>
                                        
                                        <!-- Utilidad Actual vs Nueva -->
                                        <div class="d-flex justify-content-center gap-2 mt-1">
                                            <span class="badge badge-utilidad {{ $utilidadActual >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                                U: ${{ number_format($utilidadActual, 2) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Margen Actual vs Nuevo -->
                                        <div class="d-flex justify-content-center gap-2">
                                            <span class="badge badge-margen {{ $margenActual >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                                                M: {{ number_format($margenActual, 2) }}%
                                            </span>
                                        </div>
                                        
                                        <!-- Paralelo Actual vs Nuevo -->
                                        <div class="d-flex justify-content-center gap-2">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                P: {{ number_format($montoParaleloActual, 2) }}$
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Estrellas -->
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <!-- Estrellas en amarillo -->
                                        <div class="text-warning mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $estrellas)
                                                    <i class="bi bi-star-fill"></i>
                                                @else
                                                    <i class="bi bi-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <!-- Texto debajo con fondo sólido -->
                                        <span class="badge 
                                            @if($estrellas >= 5) bg-success text-white
                                            @elseif($estrellas >= 4) bg-primary text-white
                                            @elseif($estrellas >= 3) bg-info text-dark
                                            @else bg-secondary text-white
                                            @endif
                                        ">
                                            {{ $detalle['categoria'] }}
                                        </span>
                                    </div>
                                
                                <!-- Acciones -->
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="tooltip" 
                                            title="Ver detalles"
                                            onclick="verDetalleProducto({{ $detalle['id'] }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
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
                        Mostrando <strong>{{ $productosAltaDemanda->count() }}</strong> productos con alta demanda (3, 4 y 5 estrellas)
                    </div>
                </div>
            </div>
        </div>
        
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-line fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay productos con alta demanda</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron productos con alta demanda (3, 4 o 5 estrellas) para el período seleccionado.
                    </p>
                </div>
            </div>
        </div>
        @endif
        
    </div>
</div>

<!-- Botón flotante de automatización -->
<div class="position-fixed" style="bottom: 30px; right: 30px; z-index: 1000;">
    <button type="button" 
            class="btn btn-success btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center"
            id="btnEjecutarAutomatizacionAlta"
            style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #20c997); border: none; box-shadow: 0 5px 20px rgba(40,167,69,0.4);"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Ejecutar Subida de Precios (Alta Demanda)">
        <i class="bi bi-robot" style="font-size: 40px; color: white;"></i>
        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle animate-pulse">
            <span class="visually-hidden">Nuevo</span>
        </span>
    </button>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let ejecutandoAutomatizacionAlta = false;
    
    // Variables de ejecución reciente (desde PHP)
    const ejecucionRecienteAlta = {{ $ejecucionReciente ? 'true' : 'false' }};
    const fechaUltimaEjecucionAlta = '{{ $fechaUltimaEjecucion ?? '' }}';
    const ultimoReporteAlta = @json($ultimoReporte ?? null);
    
    // ============================================
    // FUNCIONES DE UTILIDAD
    // ============================================
    function verDetalleProducto(id) {
        window.location.href = '{{ url("/") }}' + '/productos/' + id;
    }
    
    // ============================================
    // FILTRADO DE TABLA
    // ============================================
    function filtrarTabla() {
        var input = document.getElementById("buscarProducto");
        var filter = input.value.toUpperCase();
        var table = document.getElementById("tablaAltaDemanda");
        var tr = table.getElementsByTagName("tr");
        
        for (var i = 1; i < tr.length; i++) {
            var tdCodigo = tr[i].getElementsByTagName("td")[2];
            var tdDescripcion = tr[i].getElementsByTagName("td")[3];
            if (tdCodigo && tdDescripcion) {
                var txtValueCodigo = tdCodigo.textContent || tdCodigo.innerText;
                var txtValueDescripcion = tdDescripcion.textContent || tdDescripcion.innerText;
                if (txtValueCodigo.toUpperCase().indexOf(filter) > -1 || 
                    txtValueDescripcion.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    
    document.getElementById('filtroValoracion').addEventListener('change', function () {
        var valor = this.value;
        var filas = document.querySelectorAll('#tablaAltaDemanda tbody tr');
        filas.forEach(fila => {
            var rating = fila.getAttribute('data-rating');
            if (!valor || rating === valor) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
    
    document.getElementById('checkAllAltaDemanda')?.addEventListener('change', function() {
        document.querySelectorAll('.checkProductoAltaDemanda').forEach(chk => chk.checked = this.checked);
    });
    
    // ============================================
    // EXPORTAR EXCEL
    // ============================================
    function exportarExcelAltaDemanda() {
        const tabla = document.getElementById('tablaAltaDemanda');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];
        const headers = ['ID', 'Sucursal'];

        tabla.querySelectorAll('thead th').forEach((th, index) => {
            if (index < 2) return;
            const texto = th.textContent.trim();
            if (!texto.toLowerCase().includes('accion') && !texto.toLowerCase().includes('acción')) {
                headers.push(texto);
                if (texto.toLowerCase().includes('pvp')) {
                    headers.push('Utilidad');
                    headers.push('Margen');
                    headers.push('Paralelo');
                }
            }
        });

        datos.push(headers);

        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;
            const rowData = [];
            const checkbox = fila.querySelector('.checkProductoAltaDemanda');
            const productoId = checkbox ? checkbox.value : '';
            const sucursal = checkbox ? checkbox.dataset.sucursal : '';
            rowData.push(productoId);
            rowData.push(sucursal);

            let pvpActual = '', utilidad = '', margen = '', paralelo = '';
            const celdaPVP = fila.querySelector('td:nth-child(8)');
            if (celdaPVP) {
                const precioSpan = celdaPVP.querySelector('.text-danger');
                if (precioSpan) pvpActual = precioSpan.textContent.replace('$', '').trim();
                const badges = celdaPVP.querySelectorAll('.badge');
                if (badges[0]) utilidad = badges[0].textContent.replace('U: $', '').trim();
                if (badges[1]) margen = badges[1].textContent.replace('M: ', '').replace('%', '').trim();
                const paraleloSpan = celdaPVP.querySelector('[id^="paralelo-"]');
                if (paraleloSpan) paralelo = paraleloSpan.textContent.replace('P:', '').replace('$', '').trim();
            }

            let pvpNuevo = '', porcentajeSubida = '';
            const nuevoDiv = fila.querySelector('td:nth-child(8) .text-success');
            if (nuevoDiv) {
                const textoNuevo = nuevoDiv.textContent.trim();
                const match = textoNuevo.match(/\$([0-9.]+)/);
                if (match) pvpNuevo = match[1];
                const subidaMatch = textoNuevo.match(/\+([0-9]+)%/);
                if (subidaMatch) porcentajeSubida = subidaMatch[1];
            }

            let estrellas = '';
            const estrellasDiv = fila.querySelector('td:nth-child(9) .text-warning');
            if (estrellasDiv) estrellas = estrellasDiv.textContent.trim();

            let categoria = '';
            const categoriaSpan = fila.querySelector('td:nth-child(9) .badge');
            if (categoriaSpan) categoria = categoriaSpan.textContent.trim();

            fila.querySelectorAll('td').forEach((td, index) => {
                if (index < 2) return;
                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;
                const textoTh = th.textContent.trim();
                if (textoTh.toLowerCase().includes('accion') || textoTh.toLowerCase().includes('acción')) return;

                if (textoTh.toLowerCase().includes('pvp')) {
                    rowData.push(parseFloat(pvpActual) || 0);
                    rowData.push(parseFloat(utilidad) || 0);
                    rowData.push(parseFloat(margen) || 0);
                    rowData.push(parseFloat(paralelo) || 0);
                    return;
                }

                if (textoTh.toLowerCase().includes('estrellas')) {
                    rowData.push(estrellas);
                    rowData.push(categoria);
                    rowData.push(porcentajeSubida + '%');
                    rowData.push(parseFloat(pvpNuevo) || 0);
                    return;
                }

                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                const badge = td.querySelector('.badge');
                if (badge) texto = badge.textContent.trim();
                if (textoTh.toLowerCase().includes('costo')) texto = texto.replace('$', '').trim();
                const numero = parseFloat(texto.replace(',', '.'));
                if (!isNaN(numero)) texto = numero;
                rowData.push(texto);
            });

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);
        const maxColLengths = [];
        datos.forEach(row => {
            row.forEach((cell, colIndex) => {
                const length = String(cell).length;
                maxColLengths[colIndex] = Math.max(maxColLengths[colIndex] || 10, length);
            });
        });
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 50) }));
        XLSX.utils.book_append_sheet(wb, ws, 'Productos Alta Demanda');
        XLSX.writeFile(wb, `Productos_Alta_Demanda_${new Date().toISOString().split('T')[0]}.xlsx`);
    }
    
    // ============================================
    // EXPORTAR PDF
    // ============================================
    function pdfAltaDemanda() {
        const tabla = document.getElementById('tablaAltaDemanda');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        const loading = document.createElement('div');
        loading.style.cssText = `
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 9999;
        `;
        loading.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light"></div><p class="mt-2">Generando PDF...</p></div>';
        document.body.appendChild(loading);
        
        setTimeout(async () => {
            try {
                await pdfTablaAltaDemanda();
            } catch (error) {
                console.error('Error generando PDF:', error);
                alert('Error generando PDF. Intente nuevamente.');
            } finally {
                document.body.removeChild(loading);
            }
        }, 1000);
    }

    async function pdfTablaAltaDemanda() {
        const tabla = document.getElementById('tablaAltaDemanda');
        if (!tabla) return;
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.text('Productos con Alta Demanda - ' + new Date().toLocaleDateString('es-ES'), 14, 15);
        doc.setFontSize(9);
        doc.text('Generado: ' + new Date().toLocaleString('es-ES'), 14, 25);
        
        const productos = [];
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;
            const codigo = fila.querySelector('td:nth-child(3)')?.innerText || '';
            const descripcion = fila.querySelector('td:nth-child(4)')?.innerText || '';
            const existencia = fila.querySelector('td:nth-child(5)')?.innerText || '';
            const ventas = fila.querySelector('td:nth-child(6)')?.innerText || '';
            const costo = fila.querySelector('td:nth-child(7)')?.innerText || '';
            
            let pvpActual = '', pvpNuevo = '';
            const celdaPVP = fila.querySelector('td:nth-child(8)');
            if (celdaPVP) {
                const actualSpan = celdaPVP.querySelector('.text-danger');
                const nuevoSpan = celdaPVP.querySelector('.text-success');
                if (actualSpan) pvpActual = actualSpan.textContent.trim();
                if (nuevoSpan) pvpNuevo = nuevoSpan.textContent.trim();
            }
            
            let estrellas = '';
            const estrellasDiv = fila.querySelector('td:nth-child(9) .text-warning');
            if (estrellasDiv) estrellas = estrellasDiv.textContent.trim();
            
            productos.push([
                codigo, descripcion.substring(0, 40), existencia, ventas, costo,
                pvpActual, pvpNuevo, estrellas
            ]);
        });
        
        if (productos.length === 0) return;
        
        doc.autoTable({
            head: [['Código', 'Descripción', 'Exist.', 'Ventas', 'Costo', 'PVP Actual', 'PVP Nuevo', 'Estrellas']],
            body: productos,
            startY: 30,
            theme: 'striped',
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 9 },
            bodyStyles: { fontSize: 8 },
            columnStyles: { 0: { cellWidth: 25 }, 1: { cellWidth: 60 }, 2: { cellWidth: 15 }, 3: { cellWidth: 20 }, 4: { cellWidth: 20 }, 5: { cellWidth: 20 }, 6: { cellWidth: 20 }, 7: { cellWidth: 20 } }
        });
        
        for (let i = 1; i <= doc.internal.getNumberOfPages(); i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${doc.internal.getNumberOfPages()}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
        }
        
        doc.save(`Productos_Alta_Demanda_${new Date().toISOString().split('T')[0]}.pdf`);
    }
    
    // ============================================
    // AUTOMATIZACIÓN DE ALTA DEMANDA
    // ============================================
    
    function mostrarModalConfirmacionAlta() {
        const sucursalNombre = '{{ session('sucursal_nombre', 'Todas las sucursales') }}';
        
        Swal.fire({
            title: '<i class="bi bi-arrow-up-circle me-2" style="font-size: 28px;"></i> Subir Precios - Alta Demanda',
            html: `
                <div class="container-fluid px-0">
                    <p class="mb-3 text-muted" style="font-size: 14px;">
                        Se aplicarán aumentos automáticos según las estrellas de los productos.
                        Solo se procesarán productos con 3, 4 o 5 estrellas.
                    </p>
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <div class="border rounded p-2 text-center bg-success bg-opacity-10">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <h5 class="mb-0 text-success mt-1">+10%</h5>
                                <small>★★★★★ (5 estrellas)</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-2 text-center bg-primary bg-opacity-10">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star text-muted"></i>
                                <h5 class="mb-0 text-primary mt-1">+8%</h5>
                                <small>★★★★☆ (4 estrellas)</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-2 text-center bg-info bg-opacity-10">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star text-muted"></i>
                                <i class="bi bi-star text-muted"></i>
                                <h5 class="mb-0 text-info mt-1">+5%</h5>
                                <small>★★★☆☆ (3 estrellas)</small>
                            </div>
                        </div>
                    </div>
                    <div class="small text-center text-muted">
                        <i class="bi bi-building me-1"></i> ${sucursalNombre}
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Ejecutar',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancelar',
            width: '420px'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarAutomatizacionAlta();
            }
        });
    }
    
    async function ejecutarAutomatizacionAlta() {
        const sucursalId = {{ session('sucursal_id', 0) }};
        const sucursalNombre = '{{ session('sucursal_nombre', '') }}';
        
        if (sucursalId === 0 || sucursalId === '0') {
            Swal.fire({
                title: '<i class="bi bi-exclamation-triangle me-2"></i> Sucursal no seleccionada',
                html: '<p>Para ejecutar la automatización, debes seleccionar una sucursal específica.</p>',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        if (![3,4,5,7].includes(parseInt(sucursalId))) {
            Swal.fire({
                title: '<i class="bi bi-exclamation-triangle me-2"></i> Sucursal no válida',
                html: '<p>La sucursal seleccionada no es válida para ejecutar la automatización.</p>',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        ejecutandoAutomatizacionAlta = true;
        
        Swal.fire({
            title: '<i class="bi bi-arrow-up-circle me-2"></i> Ejecutando Subida de Precios',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p id="mensajeProgresoAlta" class="mb-2">Iniciando análisis de productos con alta demanda...</p>
                    <div class="alert alert-info py-1 mb-2">
                        <small><i class="bi bi-shop me-1"></i> Sucursal: <strong>${sucursalNombre}</strong></small>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        <div id="barraProgresoAlta" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 0%"></div>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { simularProgresoAlta(); }
        });
        
        try {
            const fechaFin = document.getElementById('fecha_fin').value;
            
            const response = await fetch('{{ route("cpanel.automatizacion.subir") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    sucursal_id: sucursalId,
                    fecha_fin: fechaFin
                })
            });
            
            const data = await response.json();
            if (window.progresoIntervalAlta) clearInterval(window.progresoIntervalAlta);
            
            if (data.success) {
                mostrarResultadoExitosoAlta(data);
            } else {
                Swal.fire('Error', data.mensaje, 'error');
            }
        } catch (error) {
            if (window.progresoIntervalAlta) clearInterval(window.progresoIntervalAlta);
            Swal.fire('Error', 'Error de conexión', 'error');
        } finally {
            ejecutandoAutomatizacionAlta = false;
        }
    }
    
    function simularProgresoAlta() {
        let progreso = 0;
        window.progresoIntervalAlta = setInterval(() => {
            if (progreso < 95) {
                progreso += Math.random() * 10;
                if (progreso > 95) progreso = 95;
                const barra = document.getElementById('barraProgresoAlta');
                if (barra) barra.style.width = progreso + '%';
            }
        }, 1000);
    }
    
    function mostrarResultadoExitosoAlta(data) {
        if (window.progresoIntervalAlta) clearInterval(window.progresoIntervalAlta);
        
        const totalAnalizados = data.total_analizados || 0;
        const productosAfectados = data.productos_afectados || 0;
        const productosMantenidos = data.productos_mantenidos || 0;
        const productosSaltados = data.productos_saltados_reproceso || 0;
        const categorias = data.categorias || {};
        const sucursalNombre = data.sucursal_nombre || 'N/A';
        
        Swal.fire({
            title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Subida de Precios Completada!',
            html: `
                <div class="text-start">
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        ${data.mensaje || 'La automatización se ejecutó correctamente'}
                    </div>
                    
                    <!-- Tarjetas de resumen -->
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="card bg-light text-center py-2">
                                <h4 class="text-primary">${totalAnalizados}</h4>
                                <small>Productos analizados</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light text-center py-2">
                                <h4 class="text-success">${productosAfectados}</h4>
                                <small>Productos afectados</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light text-center py-2">
                                <h4 class="text-warning">${productosMantenidos}</h4>
                                <small>Precios mantenidos</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light text-center py-2">
                                <h4 class="text-secondary">${productosSaltados}</h4>
                                <small>Saltados (reproceso)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desglose por categoría -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-1">
                                    <small class="fw-bold">Desglose por categoría</small>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-4">
                                            <small class="text-muted">★★★★★ Alta Demanda (+10%):</small>
                                            <h6 class="mb-0">${categorias.altaDemanda || 0}</h6>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">★★★★☆ Buena Demanda (+8%):</small>
                                            <h6 class="mb-0">${categorias.buenaDemanda || 0}</h6>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">★★★☆☆ Demanda Media (+5%):</small>
                                            <h6 class="mb-0">${categorias.demandaMedia || 0}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Productos Actualizados (primeros 10) -->
                    ${data.detalles && data.detalles.length > 0 ? `
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Productos Actualizados (${data.detalles.length}):
                            </h6>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th class="text-end">Precio Actual</th>
                                            <th class="text-end">Nuevo Precio</th>
                                            <th class="text-center">Subida</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.detalles.slice(0, 10).map(d => `
                                            <tr>
                                                <td><small>${d.codigo || 'N/A'}</small></td>
                                                <td><small>${(d.descripcion || '').substring(0, 30)}...</small></td>
                                                <td class="text-end text-danger">$${d.precio_anterior || 0}</td>
                                                <td class="text-end text-success fw-bold">$${d.nuevo_precio || 0}</td>
                                                <td class="text-center"><span class="badge bg-success">+${d.porcentaje_subida || 0}%</span></td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            ${data.detalles.length > 10 ? `<p class="text-muted small text-center mt-1">... y ${data.detalles.length - 10} productos más</p>` : ''}
                        </div>
                    ` : '<p class="text-muted text-center">No hay productos actualizados</p>'}
                    
                    <!-- Botones de exportación -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <button onclick="exportarResultadosAltaDemandaExcel(${JSON.stringify(data).replace(/"/g, '&quot;')})" 
                                    class="btn btn-success btn-sm w-100">
                                <i class="bi bi-file-excel me-2"></i>Exportar a Excel
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="exportarResultadosAltaDemandaPDF(${JSON.stringify(data).replace(/"/g, '&quot;')})" 
                                    class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-file-pdf me-2"></i>Exportar a PDF
                            </button>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-calendar-clock me-2"></i>
                        <strong>Días de gracia:</strong> ${data.dias_gracia || 30} días
                        <span class="mx-2">|</span>
                        <i class="bi bi-shop me-2"></i>
                        <strong>Sucursal:</strong> ${sucursalNombre}
                        <span class="mx-2">|</span>
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Fecha:</strong> ${new Date().toLocaleString()}
                    </div>
                </div>
            `,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Aceptar',
            width: '850px'
        }).then((result) => {
            location.reload();
        });
        
        // ✅ Exportación automática silenciosa a Excel
        setTimeout(() => {
            exportarResultadosAltaDemandaExcel(data);
        }, 500);
    }
    
    // ============================================
    // EVENTO PRINCIPAL DEL BOTÓN
    // ============================================
    document.getElementById('btnEjecutarAutomatizacionAlta')?.addEventListener('click', function() {
        if (ejecutandoAutomatizacionAlta) {
            showToast('Ya hay una automatización en ejecución', 'warning');
            return;
        }
        
        if (ejecucionRecienteAlta) {
            if (ultimoReporteAlta && ultimoReporteAlta.detalles && ultimoReporteAlta.detalles.length > 0) {
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Ejecución reciente detectada',
                    html: `
                        <div class="text-start">
                            <p>Ya se ejecutó una automatización de subida de precios en esta sucursal hace menos de 30 días.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Última ejecución:</strong> ${fechaUltimaEjecucionAlta}
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Productos analizados:</strong> ${ultimoReporteAlta.total_analizados || 0}<br>
                                <strong>Productos afectados:</strong> ${ultimoReporteAlta.productos_afectados || 0}
                            </div>
                            <p class="mt-2 mb-0 fw-bold">¿Deseas exportar el reporte de la última ejecución?</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: false,
                    showConfirmButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-file-pdf me-2"></i>Exportar a PDF',
                    denyButtonText: '<i class="bi bi-file-excel me-2"></i>Exportar a Excel',
                    confirmButtonColor: '#dc3545',
                    denyButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Generando PDF...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarResultadosAltaDemandaPDF(ultimoReporteAlta);
                                    Swal.close();
                                }, 500);
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: 'Generando Excel...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarResultadosAltaDemandaExcel(ultimoReporteAlta);
                                    Swal.close();
                                }, 500);
                            }
                        });
                    }
                });
                return;
            } else {
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Ejecución reciente detectada',
                    html: `
                        <div class="text-start">
                            <p>Ya se ejecutó una automatización de subida de precios en esta sucursal hace menos de 30 días.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Última ejecución:</strong> ${fechaUltimaEjecucionAlta}
                            </div>
                            <p class="mt-2 mb-0 fw-bold">¿Deseas exportar la información actual?</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: false,
                    showConfirmButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-file-pdf me-2"></i>Exportar a PDF',
                    denyButtonText: '<i class="bi bi-file-excel me-2"></i>Exportar a Excel',
                    confirmButtonColor: '#dc3545',
                    denyButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Generando PDF...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    pdfAltaDemanda();
                                    Swal.close();
                                }, 500);
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: 'Generando Excel...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarExcelAltaDemanda();
                                    Swal.close();
                                }, 500);
                            }
                        });
                    }
                });
                return;
            }
        } else {
            // Validar que la fecha seleccionada sea la fecha actual
            const fechaFinSeleccionada = document.getElementById('fecha_fin').value;
            const fechaActual = new Date().toISOString().split('T')[0];
            
            if (fechaFinSeleccionada !== fechaActual) {
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Fecha no válida',
                    html: `
                        <div class="text-start">
                            <p>La automatización de precios solo puede ejecutarse para la fecha actual.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-date me-2"></i>
                                <strong>Fecha seleccionada:</strong> ${fechaFinSeleccionada}<br>
                                <i class="bi bi-calendar-date me-2"></i>
                                <strong>Fecha actual:</strong> ${fechaActual}
                            </div>
                            <p class="mt-2 mb-0">Por favor, selecciona la fecha actual para ejecutar la automatización.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Entendido'
                });
                return;
            }
        }
        
        mostrarModalConfirmacionAlta();
    });
    
    // Funciones de exportación para reportes guardados (placeholder - adaptar después)
    function exportarResultadosAutomatizacionAltaPDF(data) {
        console.log('Exportar PDF desde datos guardados', data);
        Swal.fire('Info', 'Funcionalidad en desarrollo', 'info');
    }
    
    function exportarResultadosAutomatizacionAltaExcel(data) {
        console.log('Exportar Excel desde datos guardados', data);
        Swal.fire('Info', 'Funcionalidad en desarrollo', 'info');
    }

    function exportarResultadosAltaDemandaExcel(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toISOString().split('T')[0];
        const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
        
        const wb = XLSX.utils.book_new();
        
        // Hoja Resumen
        const resumenData = [
            ['REPORTE SUBIDA DE PRECIOS - ALTA DEMANDA'],
            ['Sucursal:', sucursalNombre],
            ['Fecha:', new Date().toLocaleString()],
            ['Días de gracia:', data.dias_gracia || 30],
            [],
            ['RESUMEN GENERAL'],
            ['Total productos analizados', data.total_analizados || 0],
            ['Productos afectados (actualizados)', data.productos_afectados || 0],
            ['Precios mantenidos', data.productos_mantenidos || 0],
            ['Saltados por reproceso', data.productos_saltados_reproceso || 0],
            [],
            ['DESGLOSE POR CATEGORÍA'],
            ['★★★★★ Alta Demanda (+10%)', data.categorias?.altaDemanda || 0],
            ['★★★★☆ Buena Demanda (+8%)', data.categorias?.buenaDemanda || 0],
            ['★★★☆☆ Demanda Media (+5%)', data.categorias?.demandaMedia || 0]
        ];
        
        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');
        
        // Hoja Productos Actualizados
        if (data.detalles && data.detalles.length > 0) {
            const productosData = [
                ['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Subida', 'Costo', 'Existencia']
            ];
            
            data.detalles.forEach(d => {
                productosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.categoria || '',
                    d.precio_anterior || 0,
                    d.nuevo_precio || 0,
                    d.porcentaje_subida || 0,
                    d.costo || 0,
                    d.existencia || 0
                ]);
            });
            
            const wsProductos = XLSX.utils.aoa_to_sheet(productosData);
            XLSX.utils.book_append_sheet(wb, wsProductos, 'Productos Actualizados');
        }
        
        const nombreArchivo = `SubidaPrecios_${sucursalNombre}_${fecha}_${hora}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);
    }

    function exportarResultadosAltaDemandaPDF(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toLocaleString();
        const categorias = data.categorias || {};
        
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');
        
        // Título principal
        doc.setFontSize(16);
        doc.setTextColor(40, 167, 69);
        doc.text('REPORTE SUBIDA DE PRECIOS - ALTA DEMANDA', 14, 20);
        
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(`Sucursal: ${sucursalNombre}`, 14, 30);
        doc.text(`Fecha: ${fecha}`, 14, 36);
        doc.text(`Días de gracia: ${data.dias_gracia || 30} días`, 14, 42);
        
        let startY = 50;
        
        // ============================================
        // TABLA DE RESUMEN GENERAL
        // ============================================
        doc.setFontSize(12);
        doc.setTextColor(255, 255, 255);
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('RESUMEN GENERAL', 16, startY + 6);
        
        startY += 10;
        
        const resumenData = [
            ['Total productos analizados', data.total_analizados || 0],
            ['Productos afectados (actualizados)', data.productos_afectados || 0],
            ['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0],
            ['Saltados por reproceso', data.productos_saltados_reproceso || 0]
        ];
        
        doc.autoTable({
            startY: startY,
            head: [['Concepto', 'Cantidad']],
            body: resumenData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 5;
        
        // TABLA DE DESGLOSE POR CATEGORÍA
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('DESGLOSE POR CATEGORÍA', 16, startY + 6);

        startY += 10;

        const categoriaData = [
            ['5 Estrellas (Alta Demanda +10%)', categorias.altaDemanda || 0],
            ['4 Estrellas (Buena Demanda +8%)', categorias.buenaDemanda || 0],
            ['3 Estrellas (Demanda Media +5%)', categorias.demandaMedia || 0],
            ['Precios mantenidos', categorias.preciosMantenidos || 0]
        ];

        doc.autoTable({
            startY: startY,
            head: [['Categoría', 'Cantidad']],
            body: categoriaData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 10;
        
        // ============================================
        // PRODUCTOS ACTUALIZADOS
        // ============================================
        if (data.detalles && data.detalles.length > 0) {
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(40, 167, 69);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS ACTUALIZADOS (${data.detalles.length})`, 16, startY + 6);
            
            startY += 10;
            
            const productosData = data.detalles.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                d.categoria || '',
                `$${parseFloat(d.precio_anterior).toFixed(2)}`,
                `$${parseFloat(d.nuevo_precio).toFixed(2)}`,
                `+${d.porcentaje_subida || 0}%`
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Subida']],
                body: productosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 30 },
                    3: { cellWidth: 25 },
                    4: { cellWidth: 25 },
                    5: { cellWidth: 15 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // ============================================
        // PRODUCTOS MANTENIDOS
        // ============================================
        if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(255, 152, 0);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS MANTENIDOS (${data.detalles_mantenidos.length}) - Sin cambios`, 16, startY + 6);
            
            startY += 10;
            
            const mantenidosData = data.detalles_mantenidos.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                `$${parseFloat(d.pvp_actual || 0).toFixed(2)}`,
                `$${parseFloat(d.costo || 0).toFixed(2)}`,
                `$${parseFloat(d.ganancia || 0).toFixed(2)}`,
                d.razon || 'Producto en pérdida o sin ganancia'
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']],
                body: mantenidosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 20 },
                    3: { cellWidth: 20 },
                    4: { cellWidth: 20 },
                    5: { cellWidth: 40 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(
                `Reporte generado automáticamente - TiendasTenShop | Página ${i} de ${totalPaginas}`,
                doc.internal.pageSize.width / 2,
                doc.internal.pageSize.height - 10,
                { align: 'center' }
            );
        }
        
        Swal.close();
        
        const nombreArchivo = `SubidaPrecios_${sucursalNombre}_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(nombreArchivo);
        
        Swal.fire({
            title: 'Exportación completada',
            text: `Archivo PDF generado para ${sucursalNombre}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
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

    /* Animación de pulso para el botón */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            transform: scale(1);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(40, 167, 69, 0);
            transform: scale(1.05);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            transform: scale(1);
        }
    }

    .animate-pulse {
        animation: pulse 1.5s infinite;
    }

    /* Hover efecto para el botón */
    #btnEjecutarAutomatizacion:hover {
        transform: scale(1.1);
        transition: transform 0.3s ease;
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.5);
    }

    /* Al final de tu style */
    .rounded-4 {
        border-radius: 16px !important;
    }

    /* Animar el botón de confirmación */
    .swal2-confirm {
        transition: all 0.3s ease;
    }

    .swal2-confirm:hover {
        transform: scale(1.02);
    }
</style>
@endsection