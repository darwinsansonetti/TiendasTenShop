@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Vendedores')

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
      <div class="col-sm-6"><h3 class="mb-0">Vendedores</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Vendedores</li>
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

    @if($vendedores && $vendedores->count() > 0)
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Listado de Vendedores
                </h3>
                
                <!-- Contenedor derecho con ms-auto para empujar a la derecha -->
                <div class="d-flex gap-2 ms-auto">
                    <!-- Botón Agregar Vendedor -->
                    <a href="{{ route('cpanel.empleados.agregar') }}" 
                    class="btn btn-success btn-sm">
                        <i class="fas fa-plus-circle me-1"></i>+ Nuevo Vendedor
                    </a>
                    
                    <!-- Botones de exportación -->
                    <div class="btn-group">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                onclick="pdfTablaVendedores()">
                            <i class="fas fa-print me-1"></i>PDF
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                onclick="exportarExcelVendedores()">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaVendedores">
                        <thead class="table-light">
                            <tr>
                                <th width="80" class="text-center">Foto</th>
                                <th width="250">Vendedor</th>
                                <th width="200">Sucursal</th>
                                <th width="250">Dirección</th>
                                <th width="120" class="text-center">Ingreso</th>
                                <th width="120" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendedores as $index => $vendedor)
                                @php
                                    // Usar sintaxis de objeto (->) en lugar de array ([])
                                    $id = $vendedor->id ?? '';
                                    $vendedorId = $vendedor->vendedor_id ?? $vendedor->VendedorId ?? '';
                                    $nombre = $vendedor->nombre ?? $vendedor->NombreCompleto ?? 'N/A';
                                    $email = $vendedor->email ?? $vendedor->Email ?? '';
                                    $direccion = $vendedor->direccion ?? $vendedor->Direccion ?? 'N/A';
                                    $sucursalNombre = $vendedor->sucursal_nombre ?? 'N/A';
                                    $fotoPerfil = $vendedor->foto ?? $vendedor->FotoPerfil ?? '';
                                    $fechaIngreso = $vendedor->fecha_creacion ?? $vendedor->FechaCreacion ?? null;
                                    $origen = $vendedor->origen ?? 'pos';
                                    
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );
                                @endphp
                                <tr class="align-middle">
                                    <!-- Foto -->
                                    <td class="text-center">
                                        <img src="{{ $imgSrc }}" 
                                            alt="{{ $nombre }}"
                                            class="rounded-circle border border-success img-zoomable" 
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: zoom-in;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ $imgSrc }}"
                                            data-description="{{ $nombre }}">
                                    </td>

                                    <!-- Vendedor -->
                                    <td>
                                        <strong>{{ $nombre }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-id-badge me-1"></i>{{ $vendedorId }}
                                        </small>
                                    </td>

                                    <!-- Sucursal -->
                                    <td>
                                        <span class="badge bg-warning text-white p-2">
                                            <i class="fas fa-store me-1"></i>{{ $sucursalNombre }}
                                        </span>
                                    </td>

                                    <!-- Dirección -->
                                    <td>
                                        <span class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1 text-secondary"></i>
                                            {{ $direccion }}
                                        </span>
                                    </td>

                                    <!-- Ingreso -->
                                    <td class="text-center">
                                        @if($fechaIngreso)
                                            @php
                                                $fecha = is_string($fechaIngreso) 
                                                    ? \Carbon\Carbon::parse($fechaIngreso) 
                                                    : \Carbon\Carbon::instance($fechaIngreso);
                                            @endphp
                                            <span class="badge bg-light text-dark p-2">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $fecha->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <!-- Acción -->
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Botón Editar -->
                                            <a href="{{ route('cpanel.empleados.vendedor.editar', ['id' => $id]) }}"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Editar vendedor"
                                            data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <!-- Botón Historial -->
                                            <a href="{{ route('cpanel.empleados.ventas.vendedor', ['id' => $id]) }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="Historial del vendedor"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Resumen -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            Total Vendedores: {{ $vendedores->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-store me-1"></i>
                            POS: {{ $vendedores->where('origen', 'pos')->count() }} | 
                            Identity: {{ $vendedores->where('origen', 'identity')->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Actualizado: {{ now()->format('d/m/Y H:i') }}
                        </small>
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
                        <i class="fas fa-users fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay vendedores para mostrar</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron vendedores activos en el sistema.
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
            const tabla = document.getElementById('tablaVentasEmpleados');
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

    // EXPORTAR A EXCEL - Ranking de Vendedores (optimizado)
    function exportarExcelRanking() {
        const tabla = document.getElementById('tablaRankingVendedores');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // Encabezados - SOLO las columnas que queremos
        const headers = ['Nro', 'Vendedor', 'Sucursal', 'Unidades', 'Ventas USD'];
        datos.push(headers);

        // Filas
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Extraer Nro Ranking (columna 0)
            let nroRanking = '';
            const rankingCell = celdas[0];
            const rankingNumero = rankingCell.querySelector('h5, h6, .badge');
            nroRanking = rankingNumero ? rankingNumero.textContent.trim() : rankingCell.textContent.trim();
            
            // Extraer Vendedor (columna 2 - nombre + código)
            const vendedorCell = celdas[2];
            const nombreVendedor = vendedorCell.querySelector('strong')?.textContent.trim() || '';
            const codigoVendedor = vendedorCell.querySelector('small')?.textContent.replace('Vendedor ID:', '').trim() || '';
            const vendedorCompleto = `${nombreVendedor} (${codigoVendedor})`;
            
            // Extraer Sucursal (columna 3)
            const sucursalCell = celdas[3];
            const sucursalTexto = sucursalCell.querySelector('.badge')?.textContent.replace('Tienda:', '').trim() || 
                                sucursalCell.textContent.trim();
            
            // Extraer Unidades (columna 4)
            const unidadesCell = celdas[4];
            const unidadesTexto = unidadesCell.querySelector('.badge')?.textContent || unidadesCell.textContent.trim();
            const unidades = parseInt(unidadesTexto.replace(/\./g, '')) || 0;
            
            // Extraer Ventas USD (columna 5)
            const ventasCell = celdas[5];
            const ventasTexto = ventasCell.textContent.replace('$', '').replace(/\./g, '').replace(',', '.').trim();
            const ventas = parseFloat(ventasTexto) || 0;
            
            // Agregar fila SOLO con las columnas seleccionadas
            datos.push([
                nroRanking,
                vendedorCompleto,
                sucursalTexto,
                unidades,
                ventas
            ]);
        });

        // Crear Excel
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
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 40) }));

        XLSX.utils.book_append_sheet(wb, ws, 'RankingVendedores');
        
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Ranking_Vendedores_${fecha}.xlsx`);
    }

    // EXPORTAR A PDF - Ranking de Vendedores (optimizado)
    function pdfTablaRanking() {
        const tabla = document.getElementById('tablaRankingVendedores');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título
        const fechaActual = new Date().toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Ranking de Vendedores', 14, 15);
        
        // Subtítulo con período (extraer del footer)
        const periodoText = document.querySelector('.card-footer small i.fa-calendar-alt')?.parentElement?.textContent?.trim() || 
                        'Período seleccionado';
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(periodoText, 14, 22);

        // Preparar datos para la tabla
        const headers = [['#', 'Vendedor', 'Sucursal', 'Unidades', 'Ventas USD']];
        const datos = [];

        // Variables para totales
        let totalUnidades = 0;
        let totalVentas = 0;

        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Nro Ranking
            const rankingCell = celdas[0];
            const rankingNumero = rankingCell.querySelector('h5, h6, .badge')?.textContent.trim() || 
                                rankingCell.textContent.trim();
            
            // Vendedor
            const vendedorCell = celdas[2];
            const nombreVendedor = vendedorCell.querySelector('strong')?.textContent.trim() || '';
            const codigoVendedor = vendedorCell.querySelector('small')?.textContent.replace(/[^0-9A-Za-z]/g, '') || '';
            
            // Sucursal
            const sucursalCell = celdas[3];
            const sucursalTexto = sucursalCell.querySelector('.badge')?.textContent.replace('Tienda:', '').trim() || 
                                sucursalCell.textContent.trim();
            
            // Unidades
            const unidadesCell = celdas[4];
            const unidadesTexto = unidadesCell.querySelector('.badge')?.textContent || unidadesCell.textContent.trim();
            const unidades = parseInt(unidadesTexto.replace(/\./g, '')) || 0;
            
            // Ventas
            const ventasCell = celdas[5];
            const ventasTexto = ventasCell.textContent.replace('$', '').replace(/\./g, '').replace(',', '.').trim();
            const ventas = parseFloat(ventasTexto) || 0;
            
            // Acumular totales
            totalUnidades += unidades;
            totalVentas += ventas;
            
            // Agregar fila
            datos.push([
                rankingNumero,
                `${nombreVendedor}\n${codigoVendedor}`,
                sucursalTexto,
                unidades.toLocaleString('es-VE'),
                `$ ${ventas.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
            ]);
        });

        // Generar tabla en PDF
        doc.autoTable({
            head: headers,
            body: datos,
            startY: 30,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 10,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 9,
                cellPadding: 3
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                0: { cellWidth: 20, halign: 'center' },
                1: { cellWidth: 80 },
                2: { cellWidth: 60 },
                3: { cellWidth: 30, halign: 'center' },
                4: { cellWidth: 40, halign: 'right' }
            },
            margin: { left: 14, right: 14 },
            didDrawPage: function(data) {
                // Número de página
                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text(
                    `Página ${data.pageNumber}`,
                    data.settings.margin.left,
                    doc.internal.pageSize.height - 10
                );
            }
        });

        // Agregar fila de totales
        const finalY = doc.lastAutoTable.finalY + 10;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0);
        
        doc.text('TOTALES:', 14, finalY);
        doc.text(totalUnidades.toLocaleString('es-VE'), 94, finalY, { align: 'center' });
        doc.text(`$ ${totalVentas.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 124, finalY, { align: 'right' });
        
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(100);
        doc.text(`Total Vendedores: ${datos.length}`, 14, finalY + 8);
        
        // Fecha de generación
        const fechaGeneracion = new Date().toLocaleString('es-VE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        doc.text(`Generado: ${fechaGeneracion}`, 14, doc.internal.pageSize.height - 10);

        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Ranking_Vendedores_${fecha}.pdf`);
    }

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

    .bg-bronze {
        background-color: #cd7f32 !important;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem !important;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .btn-group .btn i {
        font-size: 0.9rem;
    }
    
    .text-muted small {
        font-size: 0.75rem;
    }
</style>
@endsection