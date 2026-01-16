@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Ventas Diarias')

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
      <div class="col-sm-6"><h3 class="mb-0">Ventas Diarias</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Ventas Diarias</li>
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
                <form action="{{ route('cpanel.ventas.diarias') }}" method="GET" id="filtroForm">
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
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pdfTablaVentasDiarias()">
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
                                <th width="100">Fecha</th>
                                <th width="140">Sucursal</th>
                                <th width="100" class="text-center">Unidades</th>
                                <th width="140" class="text-center">Monto Divisa</th>
                                <th width="140" class="text-center">Monto Bs</th>
                                <th width="120" class="text-center">Utilidad $</th> <!-- Nueva columna -->
                                <th width="120" class="text-center">Margen %</th>   <!-- Nueva columna -->
                                <th width="120" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventas['listaVentasDiarias'] as $item)
                                <tr id="fila-{{ $item->id }}" class="align-middle">
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $item->fecha->format('d/m/Y') }}
                                        </span>
                                    </td>

                                    <td class="fw-bold">
                                        {{ $item->nombreSucursal }}
                                    </td>

                                    <td class="text-center fw-bold text-primary">
                                        {{ $item->cantidad }}
                                    </td>

                                    <td class="text-center text-success">
                                        ${{ number_format($item->totalDivisa, 2, ',', '.') }}
                                    </td>

                                    <td class="text-center text-muted">
                                        {{ number_format($item->totalBs, 2, ',', '.') }} Bs
                                    </td>

                                    <!-- Nueva columna: Utilidad $ -->
                                    <td class="text-center text-muted">
                                        ${{ number_format($item->utilidadDivisaDiario, 2, ',', '.') }}
                                    </td>

                                    <!-- Nueva columna: Margen % -->
                                    <td class="text-center text-muted">
                                        {{ number_format($item->margenDivisaDiario, 2, ',', '.') }} %
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Ver detalles"
                                                    onclick="verDetalleVenta({{ $item->id ?? '0' }}, {{ $item->sucursalId ?? '0' }})">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar"
                                                    onclick="eliminarVenta({{ $item->id ?? '0' }})">
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
                <div class="d-flex justify-content-between align-items-center">

                    <div class="fw-bold text-success">
                        Total período: ${{ number_format($ventas['MontoDivisaTotalPeriodo'], 2, ',', '.') }}
                    </div>

                    <div class="fw-bold text-primary">
                        Utilidad: {{ $ventas['UtilidadNetaPeriodoDsp'] }}
                    </div>

                    <div class="fw-bold text-dark">
                        Margen: {{ $ventas['MargenNetoPeriodoDsp'] }}%
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
            const tabla = document.getElementById('tablaIndiceRotacion');
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
        const tabla = document.getElementById('tablaIndiceRotacion');

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
            const texto = th.textContent.trim();

            // Ignorar columna Acción
            if (!texto.toLowerCase().includes('accion') && !texto.toLowerCase().includes('acción')) {
                headers.push(texto);
            }
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

                const textoTh = th.textContent.trim();

                // Ignorar columna Acción
                if (textoTh.toLowerCase().includes('accion') || textoTh.toLowerCase().includes('acción')) {
                    return;
                }

                let texto = td.textContent
                    .trim()
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ');

                // Si hay badge, usar su contenido
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }

                // Convertir a número si corresponde
                if (
                    textoTh.toLowerCase().includes('unidades') ||
                    textoTh.toLowerCase().includes('monto') ||
                    textoTh.toLowerCase().includes('utilidad') ||   // NUEVO
                    textoTh.toLowerCase().includes('margen')       // NUEVO
                ) {
                    texto = texto.replace('$', '').replace('Bs', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
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

        XLSX.utils.book_append_sheet(wb, ws, 'Ventas Diarias');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Ventas_Diarias_${fecha}.xlsx`);
    }
    
    // Función para ordenar tabla (opcional)
    function ordenarTabla(columna, direccion) {
        // Implementar ordenamiento de tabla si es necesario
    }
    
    // Función principal para generar PDF de Ventas Diarias
    function pdfTablaVentasDiarias() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título del documento
        const titulo = 'Ventas Diarias - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);

        // =========================
        // ENCABEZADOS
        // =========================
        const headers = [];
        tabla.querySelectorAll('thead th').forEach(th => {
            const texto = th.textContent.trim();
            if (!texto.toLowerCase().includes('accion') && !texto.toLowerCase().includes('acción')) {
                headers.push(texto);
            }
        });

        // =========================
        // DATOS DE FILAS
        // =========================
        const datos = [];
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const filaData = [];
            fila.querySelectorAll('td').forEach((td, index) => {
                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;
                const textoTh = th.textContent.trim();

                // Ignorar columna Acción
                if (textoTh.toLowerCase().includes('accion') || textoTh.toLowerCase().includes('acción')) return;

                let texto = td.textContent
                    .trim()
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ');

                // Si hay badge (fecha o cantidad), tomar su texto
                const badge = td.querySelector('.badge');
                if (badge) texto = badge.textContent.trim();

                // Formatear números de montos, utilidades y margen
                if (
                    textoTh.toLowerCase().includes('unidades') ||
                    textoTh.toLowerCase().includes('monto') ||
                    textoTh.toLowerCase().includes('utilidad') ||
                    textoTh.toLowerCase().includes('margen')
                ) {
                    // Limpiar texto
                    texto = texto.replace('$', '').replace('Bs', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
                    const numero = parseFloat(texto);
                    texto = isNaN(numero) ? '' : numero.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                if (
                    header.toLowerCase().includes('unidades') ||
                    header.toLowerCase().includes('monto') ||
                    header.toLowerCase().includes('utilidad') ||
                    header.toLowerCase().includes('margen')
                ) {
                    acc[index] = { halign: 'right' }; // números alineados a la derecha
                }
                return acc;
            }, {})
        });

        // =========================
        // PIE DE PÁGINA
        // =========================
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
            doc.text(`Generado: ${new Date().toLocaleString('es-ES')}`, 14, doc.internal.pageSize.height - 10);
        }

        // =========================
        // DESCARGAR PDF
        // =========================
        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Ventas_Diarias_${fecha}.pdf`);
    }

    function verDetalleVenta(id, sucursal) {
        
        // Mostrar indicador de carga
        // showToast('⌛ Cargando detalles de la venta...', 'info', 3000);

    //    alert(id);
    //    alert(sucursal);
        
        fetch(`{{ url('/ventas-diarias/detalle') }}/${id}/${sucursal}`)
        .then(async response => {
            
            const text = await response.text();
            
            if (!response.ok) {
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.msg || `Error ${response.status}: ${errorData.message || 'Error del servidor'}`);
                } catch {
                    throw new Error(`Error ${response.status}: ${text.substring(0, 200)}`);
                }
            }
            
            return JSON.parse(text);
        })
        .then(res => {
            
            if (res.ok && res.data) {
                
                // Mostrar en modal con tabla
                mostrarDetallesVentaModal(res.data);
                
                //showToast(`${res.data.length} productos cargados`, 'success');
            } else {
                showToast(res.msg || 'Error al cargar detalles', 'danger');
            }
        })
        .catch((err) => {
            showToast('❌ Error: ' + err.message, 'danger');
        });
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

    function eliminarVenta(id) {
        if(!confirm('¿Eliminar esta venta?')) return;

        // Crear FormData en lugar de JSON
        const formData = new FormData();
        formData.append('venta_id', id);
        formData.append('_token', '{{ csrf_token() }}'); // Si no usas el header

        fetch('{{ url("/ventas-diarias/eliminar") }}', {
            method: 'POST',
            headers: { 
                // Removemos 'Content-Type': 'application/json'
                // El navegador lo establecerá automáticamente con el boundary correcto
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Mantenemos el header
            },
            body: formData // Usamos FormData en lugar de JSON.stringify
        })
        .then(r => {
            if (!r.ok) {
                // Si la respuesta no es OK, intentar leer como texto primero
                return r.text().then(text => {
                    throw new Error(`HTTP ${r.status}: ${text.substring(0, 200)}`);
                });
            }
            return r.json();
        })
        .then(resp => {
            if (resp.ok) {
                const fila = document.querySelector(`#fila-${id}`);
                if (fila) fila.remove();
                showToast('Venta eliminada correctamente', 'success');

                const tbody = document.querySelector('#tablaIndiceRotacion tbody');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No hay ventas registradas
                            </td>
                        </tr>`;
                }
            } else {
                console.error('Error del servidor:', resp);
                showToast(resp.message || 'Error eliminando venta', 'danger');
            }
        })
        .catch(err => {
            console.error('Error completo:', err);
            showToast('Error eliminando venta: ' + err.message, 'danger');
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
</style>
@endsection