@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Balance General')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Balance General</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Balance General</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="row g-2 justify-content-end align-items-center">
                            <div class="col-auto">
                                <span class="badge badge-primary mt-2">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ $fecha_inicio }} al {{ $fecha_fin }}
                                </span>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="pdfBalanceGeneral()">
                                        <i class="fas fa-print me-1"></i>PDF
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="exportarExcel()">
                                        <i class="fas fa-file-excel me-1"></i>Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- OFICINA PRINCIPAL --}}
                        @if($oficina)
                        <div class="card card-light mb-4">
                            <div class="card-header bg-light border-bottom">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-building text-primary mr-2"></i> OFICINA PRINCIPAL
                                </h4>
                            </div>
                            <div class="card-body p-3">
                                {{-- Métricas principales --}}
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="info-box bg-white border shadow-sm">
                                            <span class="info-box-icon text-primary"><i class="bi bi-cash-stack"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Cuentas por Cobrar</span>
                                                <span class="info-box-number font-weight-bold" data-tipo="cuentas-cobrar">$ {{ number_format($oficina['CuentasPorCobrar'], 2) }}</span>
                                                <small class="text-muted">De sucursales</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="info-box bg-white border shadow-sm">
                                            <span class="info-box-icon text-warning"><i class="bi bi-receipt"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Facturas por Pagar</span>
                                                <span class="info-box-number font-weight-bold" data-tipo="facturas-pagar">$ {{ number_format($oficina['FacturasPorPagar']['Total'], 2) }}</span>
                                                <small class="text-muted">{{ $oficina['FacturasPorPagar']['CantidadTotal'] }} facturas</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="info-box bg-white border shadow-sm">
                                            <span class="info-box-icon text-danger"><i class="bi bi-credit-card"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Deuda Gastos</span>
                                                <span class="info-box-number font-weight-bold" data-tipo="deuda-gastos">$ {{ number_format($oficina['DeudaGastos'], 2) }}</span>
                                                <small class="text-muted">Gastos pendientes</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="info-box bg-white border shadow-sm">
                                            <span class="info-box-icon {{ $oficina['TotalPatrimonio'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="bi bi-pie-chart"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Patrimonio</span>
                                                <span class="info-box-number font-weight-bold {{ $oficina['TotalPatrimonio'] >= 0 ? 'text-success' : 'text-danger' }}" data-tipo="patrimonio-oficina">
                                                    $ {{ number_format($oficina['TotalPatrimonio'], 2) }}
                                                </span>
                                                <small class="text-muted">Activos - Pasivos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Resumen de balance --}}
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="bg-light p-3 rounded">
                                            <div class="row text-center">
                                                <div class="col-md-4 border-right">
                                                    <span class="text-muted d-block">Total Activos</span>
                                                    <h5 class="font-weight-bold mb-0" data-tipo="activos-oficina">$ {{ number_format($oficina['TotalActivos'], 2) }}</h5>
                                                </div>
                                                <div class="col-md-4 border-right">
                                                    <span class="text-muted d-block">Total Pasivos</span>
                                                    <h5 class="font-weight-bold mb-0" data-tipo="pasivos-oficina">$ {{ number_format($oficina['TotalPasivos'], 2) }}</h5>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="text-muted d-block">Patrimonio Neto</span>
                                                    <h5 class="font-weight-bold mb-0 {{ $oficina['TotalPatrimonio'] >= 0 ? 'text-success' : 'text-danger' }}" data-tipo="patrimonio-neto-oficina">
                                                        $ {{ number_format($oficina['TotalPatrimonio'], 2) }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- TABLA DE SUCURSALES --}}
                        <div class="card card-light">
                            <div class="card-header bg-light border-bottom">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-store-alt text-primary mr-2"></i> Sucursales
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped mb-0" id="tablaBalanceGeneral">
                                        <thead class="thead-light">
                                            <tr>
                                                <th rowspan="2" class="align-middle text-center">Sucursal</th>
                                                <th colspan="2" class="text-center bg-light">ACTIVOS</th>
                                                <th colspan="3" class="text-center bg-light">PASIVOS</th>
                                                <th rowspan="2" class="text-center align-middle">PATRIMONIO</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">Inventario</th>
                                                <th class="text-center">Ventas</th>
                                                <th class="text-center">Deuda Recepciones</th>
                                                <th class="text-center">Deuda Gastos</th>
                                                <th class="text-center">Total Pasivos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sucursales as $s)
                                            <tr>
                                                <td class="align-middle"><strong>{{ $s['SucursalNombre'] }}</strong></td>
                                                <td class="text-right align-middle">
                                                    $ {{ number_format($s['Inventario']['Monto'], 2) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-cubes"></i> {{ number_format($s['Inventario']['Unidades']) }} | 
                                                        <i class="fas fa-tag"></i> {{ number_format($s['Inventario']['Referencias']) }}
                                                    </small>
                                                </td>
                                                <td class="text-right align-middle">
                                                    $ {{ number_format($s['VentasPorCobrar']['Monto'], 2) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-receipt"></i> {{ $s['VentasPorCobrar']['Cantidad'] }} ventas
                                                    </small>
                                                </td>
                                                <td class="text-right align-middle">
                                                    $ {{ number_format($s['DeudaRecepciones']['Monto'], 2) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-truck-loading"></i> {{ $s['DeudaRecepciones']['Cantidad'] }} recepciones
                                                    </small>
                                                </td>
                                                <td class="text-right align-middle">
                                                    $ {{ number_format($s['DeudaGastos']['Monto'], 2) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-credit-card"></i> {{ number_format($s['DeudaGastos']['Cantidad']) }} gastos
                                                    </small>
                                                </td>
                                                <td class="text-right align-middle">
                                                    <strong>$ {{ number_format($s['TotalPasivos'], 2) }}</strong>
                                                </td>
                                                <td class="text-right align-middle {{ $s['Patrimonio'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <strong>$ {{ number_format($s['Patrimonio'], 2) }}</strong>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td class="text-center">TOTALES</td>
                                                <td class="text-right" colspan="2">$ {{ number_format($resumen['TotalActivos'], 2) }}</td>
                                                <td class="text-right" colspan="3">$ {{ number_format($resumen['TotalPasivos'], 2) }}</td>
                                                <td class="text-right {{ $resumen['TotalPatrimonio'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    $ {{ number_format($resumen['TotalPatrimonio'], 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    function exportarExcel() {
        const tabla = document.getElementById('tablaBalanceGeneral');
        
        if (!tabla) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró la tabla para exportar'
            });
            return;
        }

        const datos = [];

        // =========================
        // ENCABEZADOS (ignorando rowspan/colspan complejos)
        // =========================
        const headers = [];
        
        // Primera fila de encabezados (Sucursal, ACTIVOS, PASIVOS, PATRIMONIO)
        const headerRow1 = tabla.querySelectorAll('thead tr:first-child th');
        headerRow1.forEach((th, index) => {
            const texto = th.textContent.trim();
            // Solo tomar los que no son "ACTIVOS" y "PASIVOS" como columnas individuales
            if (index === 0) headers.push(texto); // Sucursal
            if (index === 4) headers.push('PATRIMONIO'); // El último
        });

        // Segunda fila de encabezados (Inventario, Ventas, Deuda Recepciones, Deuda Gastos, Total Pasivos)
        const headerRow2 = tabla.querySelectorAll('thead tr:last-child th');
        const subHeaders = [];
        headerRow2.forEach(th => {
            subHeaders.push(th.textContent.trim());
        });
        
        // Insertar subHeaders después de Sucursal
        const finalHeaders = [headers[0], ...subHeaders, headers[1]];
        datos.push(finalHeaders);

        // =========================
        // FILAS DE DATOS
        // =========================
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const rowData = [];
            const celdas = fila.querySelectorAll('td');
            
            // Sucursal (columna 0)
            let sucursal = celdas[0]?.innerText.trim() || '';
            sucursal = sucursal.split('\n')[0]; // Tomar solo el nombre
            rowData.push(sucursal);
            
            // Inventario (columna 1)
            let inventario = celdas[1]?.innerText.trim() || '';
            let inventarioMatch = inventario.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(inventarioMatch ? inventarioMatch[1].replace(',', '') : '0');
            
            // Ventas (columna 2)
            let ventas = celdas[2]?.innerText.trim() || '';
            let ventasMatch = ventas.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(ventasMatch ? ventasMatch[1].replace(',', '') : '0');
            
            // Deuda Recepciones (columna 3)
            let deudaRec = celdas[3]?.innerText.trim() || '';
            let deudaRecMatch = deudaRec.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(deudaRecMatch ? deudaRecMatch[1].replace(',', '') : '0');
            
            // Deuda Gastos (columna 4)
            let deudaGas = celdas[4]?.innerText.trim() || '';
            let deudaGasMatch = deudaGas.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(deudaGasMatch ? deudaGasMatch[1].replace(',', '') : '0');
            
            // Total Pasivos (columna 5)
            let totalPasivos = celdas[5]?.innerText.trim() || '';
            let totalPasivosMatch = totalPasivos.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(totalPasivosMatch ? totalPasivosMatch[1].replace(',', '') : '0');
            
            // Patrimonio (columna 6)
            let patrimonio = celdas[6]?.innerText.trim() || '';
            let patrimonioMatch = patrimonio.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(patrimonioMatch ? patrimonioMatch[1].replace(',', '') : '0');

            datos.push(rowData);
        });

        // =========================
        // PIE DE TABLA (TOTALES)
        // =========================
        const footerRow = [];
        const footCells = tabla.querySelectorAll('tfoot td');
        footCells.forEach((td, index) => {
            let texto = td.innerText.trim();
            if (index === 0) {
                footerRow.push('TOTALES');
            } else {
                let match = texto.match(/\$?([\d,]+\.?\d*)/);
                footerRow.push(match ? match[1].replace(',', '') : texto);
            }
        });
        
        // Reorganizar footer para que coincida con el orden de headers
        const footerOrdenado = [
            footerRow[0], // TOTALES
            footerRow[1], // Inventario
            footerRow[2]?.split(' ')[0] || '0', // Ventas (tomar solo el número)
            footerRow[3]?.split(' ')[0] || '0', // Deuda Recepciones
            footerRow[4]?.split(' ')[0] || '0', // Deuda Gastos
            footerRow[5]?.split(' ')[0] || '0', // Total Pasivos
            footerRow[6] || '0' // Patrimonio
        ];
        datos.push(footerOrdenado);

        if (datos.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin datos',
                text: 'No hay datos para exportar'
            });
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
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 20) }));

        XLSX.utils.book_append_sheet(wb, ws, 'BalanceGeneral');
        
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `BalanceGeneral_${fecha}.xlsx`);
        
        Swal.fire({
            icon: 'success',
            title: 'Exportado',
            text: 'Archivo Excel generado correctamente',
            timer: 2000,
            showConfirmButton: false
        });
    }

    function pdfBalanceGeneral() {
        const tabla = document.getElementById('tablaBalanceGeneral');
        
        if (!tabla) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró la tabla para exportar'
            });
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título
        const titulo = 'Balance General - {{ $fecha_inicio }} al {{ $fecha_fin }}';
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);
        doc.setFontSize(10);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

        // Encabezados
        const headers = ['Sucursal', 'Inventario', 'Ventas', 'Deuda Recepciones', 'Deuda Gastos', 'Total Pasivos', 'Patrimonio'];

        // Datos
        const datos = [];
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const rowData = [];
            const celdas = fila.querySelectorAll('td');
            
            // Sucursal
            let sucursal = celdas[0]?.innerText.trim() || '';
            rowData.push(sucursal.split('\n')[0]);
            
            // Inventario
            let inventario = celdas[1]?.innerText.trim() || '';
            let inventarioMatch = inventario.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(inventarioMatch ? inventarioMatch[1] : '0');
            
            // Ventas
            let ventas = celdas[2]?.innerText.trim() || '';
            let ventasMatch = ventas.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(ventasMatch ? ventasMatch[1] : '0');
            
            // Deuda Recepciones
            let deudaRec = celdas[3]?.innerText.trim() || '';
            let deudaRecMatch = deudaRec.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(deudaRecMatch ? deudaRecMatch[1] : '0');
            
            // Deuda Gastos
            let deudaGas = celdas[4]?.innerText.trim() || '';
            let deudaGasMatch = deudaGas.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(deudaGasMatch ? deudaGasMatch[1] : '0');
            
            // Total Pasivos
            let totalPasivos = celdas[5]?.innerText.trim() || '';
            let totalPasivosMatch = totalPasivos.match(/\$?([\d,]+\.?\d*)/);
            rowData.push(totalPasivosMatch ? totalPasivosMatch[1] : '0');
            
            // Patrimonio
            let patrimonio = celdas[6]?.innerText.trim() || '';
            let patrimonioMatch = patrimonio.match(/\$?(-?[\d,]+\.?\d*)/);
            rowData.push(patrimonioMatch ? patrimonioMatch[1] : '0');

            datos.push(rowData);
        });

        // Totales
        const footData = [];
        const footCells = tabla.querySelectorAll('tfoot td');
        footCells.forEach((td, index) => {
            if (index === 0) {
                footData.push('TOTALES');
            } else {
                let texto = td.innerText.trim();
                let match = texto.match(/\$?([\d,]+\.?\d*)/);
                footData.push(match ? match[1] : texto);
            }
        });

        // Generar tabla PDF
        doc.autoTable({
            head: [headers],
            body: datos,
            foot: [footData],
            startY: 30,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 9,
                fontStyle: 'bold',
                halign: 'center'
            },
            footStyles: {
                fillColor: [240, 240, 240],
                textColor: [0, 0, 0],
                fontSize: 9,
                fontStyle: 'bold',
                halign: 'right'
            },
            bodyStyles: {
                fontSize: 8,
                cellPadding: 2
            },
            columnStyles: {
                0: { fontStyle: 'bold', cellWidth: 40 }, // Sucursal
                1: { halign: 'right', cellWidth: 25 }, // Inventario
                2: { halign: 'right', cellWidth: 25 }, // Ventas
                3: { halign: 'right', cellWidth: 30 }, // Deuda Recepciones
                4: { halign: 'right', cellWidth: 25 }, // Deuda Gastos
                5: { halign: 'right', cellWidth: 25 }, // Total Pasivos
                6: { halign: 'right', cellWidth: 25 }, // Patrimonio
            },
            margin: { top: 35, bottom: 20 }
        });

        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
        }

        // Descargar PDF
        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`BalanceGeneral_${fecha}.pdf`);
        
        Swal.fire({
            icon: 'success',
            title: 'Exportado',
            text: 'Archivo PDF generado correctamente',
            timer: 2000,
            showConfirmButton: false
        });
    }
</script>
@endsection