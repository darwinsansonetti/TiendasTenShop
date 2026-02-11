@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Listado Cierre Diario - Auditoria')

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
      <div class="col-sm-6"><h3 class="mb-0">Cierres Diarios (Disponible para Auditar)</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Listado Cierre Diario - Auditoria</li>
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
            <div class="card-body">
                <form action="{{ route('cpanel.cuadre.auditar_cierre') }}" method="GET" id="filtroForm">
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

        @if($cierreDiario)
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header">
                <div class="row g-2 justify-content-end">

                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="pdfTablaVentasDiarias()">
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

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaIndiceRotacion">
                        <thead class="table-light">
                            <tr>
                                <th width="120" class="text-center">Fecha</th>
                                <th width="240">Sucursal</th>
                                <th width="140" class="text-center">Total $</th>
                                <th width="120" class="text-center">Total Bs</th>
                                <th width="100" class="text-center">Tasa Bs</th> 
                                <th width="140" class="text-center">Venta Sistema Bs</th> 
                                <th width="140" class="text-center">Estatus</th> 
                                <th width="120" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cierreDiario as $item)
                                <tr id="fila-{{ $item->CierreDiarioId }}" class="align-middle">
                                    <!-- Fecha -->
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $item->Fecha->format('d/m/Y') }}
                                        </span>
                                    </td>

                                    <!-- Egreso Divisa -->
                                    <td class="fw-bold">
                                        {{ $item->SucursalNombre }}
                                    </td>

                                    <!-- Egreso Bs -->
                                    <td class="text-center fw-bold text-warning">
                                        {{ number_format((float)$item->EgresoBs, 2, ',', '.') }}
                                    </td>

                                    <!-- Total Bs (EfectivoBs + PagoMovilBs + TransferenciaBs + PuntoDeVentaBs - EgresoBs) -->
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format(
                                            ((float)$item->EfectivoBs + (float)$item->PagoMovilBs + (float)$item->TransferenciaBs + (float)$item->PuntoDeVentaBs + (float)$item->CasheaBs + (float)$item->Biopago) - (float)$item->EgresoBs,
                                            2, ',', '.'
                                        ) }} Bs
                                    </td>

                                    <!-- Valor de la divisa -->
                                    <td class="text-center fw-bold">
                                        {{ number_format((float)$item->DivisaValor, 2, ',', '.') }}
                                    </td>

                                    <!-- Venta en Sistema -->
                                    <td class="text-center fw-bold">
                                        {{ number_format((float)$item->VentaSistema, 2, ',', '.') }}
                                    </td>

                                    <!-- Diferencia (Total Bs - VentaSistema) -->
                                    <td class="text-center">
                                        <span class="badge bg-light text-success">
                                            @switch($item->Estatus)
                                                @case(0)
                                                    Edición
                                                    @break

                                                @case(1)
                                                    Nuevo
                                                    @break

                                                @case(2)
                                                    Auditoría
                                                    @break

                                                @case(3)
                                                    Contabilizado
                                                    @break

                                                @case(4)
                                                    Cerrado
                                                    @break

                                                @default
                                                    Desconocido
                                            @endswitch
                                        </span>
                                    </td>
                                    
                                    <!-- Acción -->
                                    <td class="text-center">
                                        @if($item->Estatus != 4)
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cierre.editar_auditoria', $item->CierreDiarioId) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                        @endif

                                        @if($item->Estatus == 4)
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('cierre.detalle', $item->CierreDiarioId) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>

                    </table>
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
    document.addEventListener("DOMContentLoaded", function() {

        // // Verificar si hay alguna sucursal seleccionada
        // const sucursalId = {{ session('sucursal_id', 0) }};

        // if (sucursalId === 0) {
        //     showToast('Seleccione una Sucursal', 'danger');
        // }

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
                if (textoTh.toLowerCase().includes('accion') || textoTh.toLowerCase().includes('acción')) return;

                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');

                // Si hay badge (como fecha), usar su contenido
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }

                // Convertir a número si es monto, divisa, utilidad o margen
                if (
                    textoTh.toLowerCase().includes('monto') ||
                    textoTh.toLowerCase().includes('divisa') ||
                    textoTh.toLowerCase().includes('bs') ||
                    textoTh.toLowerCase().includes('utilidad') ||
                    textoTh.toLowerCase().includes('margen') ||
                    textoTh.toLowerCase().includes('diferencia')
                ) {
                    texto = texto.replace('$', '').replace('Bs', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
                    const numero = parseFloat(texto);
                    texto = isNaN(numero) ? '' : numero;
                }

                rowData.push(texto);
            });

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            showToast('Seleccione una Sucursal', 'danger');
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

        XLSX.utils.book_append_sheet(wb, ws, 'CierreDiario');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `CierreDiario_${fecha}.xlsx`);
    }

    function pdfTablaVentasDiarias() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título del documento
        const titulo = 'Cierre Diario - ' + new Date().toLocaleDateString('es-ES');
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
                if (textoTh.toLowerCase().includes('accion') || textoTh.toLowerCase().includes('acción')) return;

                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');

                // Badge (fecha)
                const badge = td.querySelector('.badge');
                if (badge) texto = badge.textContent.trim();

                // Formatear montos, divisas y porcentajes
                if (
                    textoTh.toLowerCase().includes('monto') ||
                    textoTh.toLowerCase().includes('divisa') ||
                    textoTh.toLowerCase().includes('bs') ||
                    textoTh.toLowerCase().includes('utilidad') ||
                    textoTh.toLowerCase().includes('margen') ||
                    textoTh.toLowerCase().includes('diferencia')
                ) {
                    texto = texto.replace('$', '').replace('Bs', '').replace('%', '').replace(/\./g, '').replace(',', '.').trim();
                    const numero = parseFloat(texto);
                    texto = isNaN(numero) ? '' : numero.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                filaData.push(texto);
            });

            datos.push(filaData);
        });

        if (datos.length === 0) {
            showToast('Seleccione una Sucursal', 'danger');
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
                    header.toLowerCase().includes('monto') ||
                    header.toLowerCase().includes('divisa') ||
                    header.toLowerCase().includes('bs') ||
                    header.toLowerCase().includes('utilidad') ||
                    header.toLowerCase().includes('margen') ||
                    header.toLowerCase().includes('diferencia')
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
            doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, doc.internal.pageSize.height - 10);
        }

        // =========================
        // DESCARGAR PDF
        // =========================
        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`CierreDiario_${fecha}.pdf`);
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