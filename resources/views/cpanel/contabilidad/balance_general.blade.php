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
                    <div class="card card-light mb-3">
                        <div class="card-header bg-light border-bottom py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-building text-primary mr-2"></i> OFICINA PRINCIPAL - RESUMEN FACTURAS
                            </h5>
                        </div>
                        <div class="card-body p-2">
                            
                            {{-- Tabla de 5 columnas --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center align-middle" style="width: 15%">Tipo</th>
                                            <th class="text-center align-middle" style="width: 15%">Facturas</th>
                                            <th class="text-center align-middle" style="width: 25%">Monto General</th>
                                            <th class="text-center align-middle" style="width: 25%">Monto Abonado</th>
                                            <th class="text-center align-middle" style="width: 20%">Monto Pendiente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Fila Mercancía --}}
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold text-primary">
                                                Mercancía
                                            </td>
                                            <td class="text-center align-middle font-weight-bold">
                                                {{ $oficina['FacturasMercancia']['Cantidad'] }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold">
                                                $ {{ number_format($oficina['FacturasMercancia']['Total'], 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold text-success">
                                                $ {{ number_format($oficina['FacturasMercancia']['Pagado'], 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold {{ $oficina['FacturasMercancia']['Pendiente'] > 0 ? 'text-danger' : 'text-dark' }}">
                                                $ {{ number_format($oficina['FacturasMercancia']['Pendiente'], 2) }}
                                            </td>
                                        </tr>
                                        
                                        {{-- Fila Servicios --}}
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold text-warning">
                                                </i> Servicios
                                            </td>
                                            <td class="text-center align-middle font-weight-bold">
                                                {{ $oficina['FacturasServicios']['Cantidad'] }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold">
                                                $ {{ number_format($oficina['FacturasServicios']['Total'], 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold text-success">
                                                $ {{ number_format($oficina['FacturasServicios']['Pagado'], 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold {{ $oficina['FacturasServicios']['Pendiente'] > 0 ? 'text-danger' : 'text-dark' }}">
                                                $ {{ number_format($oficina['FacturasServicios']['Pendiente'], 2) }}
                                            </td>
                                        </tr>
                                        
                                        {{-- Fila Totales --}}
                                        @php
                                            $totalFacturas = $oficina['FacturasMercancia']['Cantidad'] + $oficina['FacturasServicios']['Cantidad'];
                                            $totalGeneral = $oficina['FacturasMercancia']['Total'] + $oficina['FacturasServicios']['Total'];
                                            $totalAbonado = $oficina['FacturasMercancia']['Pagado'] + $oficina['FacturasServicios']['Pagado'];
                                            $totalPendiente = $oficina['FacturasMercancia']['Pendiente'] + $oficina['FacturasServicios']['Pendiente'];
                                        @endphp
                                        <tr class="bg-secondary text-white font-weight-bold">
                                            <td class="text-center align-middle font-weight-bold text-dark">
                                                -
                                            </td>
                                            <td class="text-center align-middle font-weight-bold">
                                                {{ $totalFacturas }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold">
                                                $ {{ number_format($totalGeneral, 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold text-success">
                                                $ {{ number_format($totalAbonado, 2) }}
                                            </td>
                                            <td class="text-end align-middle font-weight-bold {{ $totalPendiente > 0 ? 'text-danger' : 'text-dark' }}">
                                                $ {{ number_format($totalPendiente, 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Información adicional destacada --}}
                            <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between">
                                <div class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Resumen de facturas de Mercancía y Servicios
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning rounded-pill me-1">!</span>
                                        <span class="small text-muted">Cuentas x Cobrar:</span>
                                        <strong class="text-warning ms-1">$ {{ number_format($oficina['CuentasPorCobrar'] ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-danger rounded-pill me-1">!</span>
                                        <span class="small text-muted">Deudas Gastos:</span>
                                        {{-- CORREGIDO: Cambié 'DeudasGastos' por 'DeudaGastos' (singular) --}}
                                        <strong class="text-danger ms-1">$ {{ number_format($oficina['DeudaGastos'] ?? 0, 2) }}</strong>
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
                                                <th rowspan="2" class="align-middle text-center" style="vertical-align: middle;">Sucursal</th>
                                                <th colspan="2" class="text-center bg-light">ACTIVOS</th>
                                                <th colspan="4" class="text-center bg-light">PASIVOS</th>
                                                <th rowspan="2" class="text-center align-middle" style="vertical-align: middle;">PATRIMONIO</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">Inventario</th>
                                                <th class="text-center">Ventas No Totalizadas</th>
                                                <th class="text-center">Deuda Recepciones</th>
                                                <th class="text-center bg-warning" style="background-color: #fff3cd !important;">⚠️ Pendiente</th>
                                                <th class="text-center">Deuda Gastos</th>
                                                <th class="text-center">Transferencias</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sucursales as $s)
                                            <tr>
                                                <td class="align-middle"><strong>{{ $s['SucursalNombre'] }}</strong></td>
                                                
                                                {{-- ACTIVOS --}}
                                                <td class="text-center align-middle">
                                                    <strong>$ {{ number_format($s['Inventario']['Monto'], 2) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-cubes"></i> {{ number_format($s['Inventario']['Unidades']) }} | 
                                                        <i class="fas fa-tag"></i> {{ number_format($s['Inventario']['Referencias']) }}
                                                    </small>
                                                </td>
                                                <td class="text-center align-middle">
                                                    @if(number_format($s['VentasSinTotalizar']['Monto'], 2) > 0)
                                                        <strong>$ {{ number_format($s['VentasSinTotalizar']['Monto'], 2) }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-receipt"></i> {{ $s['VentasSinTotalizar']['Cantidad'] }} ventas
                                                        </small>
                                                    @else
                                                        <strong>$ {{ number_format($s['VentasSinTotalizar']['Monto'], 2) }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-receipt"></i>
                                                            @if($s['UltimaVentaTotalizada']['UltimaVenta'])
                                                                {{ \Carbon\Carbon::parse($s['UltimaVentaTotalizada']['UltimaVenta']->Fecha)->format('d/m/Y H:i') }} — 
                                                                ${{ number_format($s['UltimaVentaTotalizada']['UltimaVenta']->TotalDivisa, 2) }}
                                                            @else
                                                                Sin ventas totalizadas
                                                            @endif
                                                        </small>
                                                    @endif
                                                </td>
                                                
                                                {{-- PASIVOS --}}
                                                <td class="text-center align-middle">
                                                    <strong>$ {{ number_format($s['DeudaRecepciones']['Monto'], 2) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-truck-loading"></i> {{ $s['DeudaRecepciones']['Cantidad'] ?? 0 }} recepciones
                                                    </small>
                                                </td>
                                                
                                                {{-- COLUMNA DESTACADA: Deuda Pendiente --}}
                                                <td class="text-center align-middle" style="background-color: #fff3cd; border-left: 3px solid #ffc107;">
                                                    <strong class="text-warning" style="color: #856404 !important;">$ {{ number_format($s['DeudaRecepciones']['MontoPendiene'], 2) }}</strong>
                                                    <br>
                                                    <small class="text-warning" style="color: #856404 !important;">
                                                        <i class="fas fa-exclamation-triangle"></i> {{ $s['DeudaRecepciones']['CantidadPendiente'] ?? 0 }} pendientes
                                                    </small>
                                                </td>
                                                
                                                <td class="text-center align-middle">
                                                    <strong>$ {{ number_format($s['DeudaGastos']['Monto'], 2) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-credit-card"></i> {{ number_format($s['DeudaGastos']['Cantidad']) }} gastos
                                                    </small>
                                                </td>
                                                
                                                <td class="text-center align-middle">
                                                    @if(isset($s['Transferencias']) && $s['Transferencias']['MontoAcumulado'] > 0)
                                                        <strong>$ {{ number_format($s['Transferencias']['MontoAcumulado'] ?? 0, 2) }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-exchange-alt"></i> {{ $s['Transferencias']['Cantidad'] ?? 0 }} transferencias
                                                        </small>
                                                    @else
                                                        <span class="text-muted">$ 0.00</span>
                                                        <br>
                                                        <small class="text-muted">0 transferencias</small>
                                                    @endif
                                                </td>
                                                
                                                {{-- PATRIMONIO --}}
                                                <td class="text-center align-middle {{ $s['Patrimonio'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-weight: bold; background-color: {{ $s['Patrimonio'] >= 0 ? '#e8f5e9' : '#ffebee' }};">
                                                    $ {{ number_format($s['Patrimonio'], 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td class="text-center"><strong>TOTALES</strong></td>
                                                <td class="text-right"><strong>$ {{ number_format($resumen['TotalActivos'], 2) }}</strong></td>
                                                <td class="text-right"></td>
                                                <td class="text-right"><strong>$ {{ number_format($resumen['TotalPasivos'] ?? 0, 2) }}</strong></td>
                                                <td class="text-right"></td>
                                                <td class="text-right"></td>
                                                <td class="text-right"></td>
                                                <td class="text-right {{ $resumen['TotalPatrimonio'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <strong>$ {{ number_format($resumen['TotalPatrimonio'], 2) }}</strong>
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