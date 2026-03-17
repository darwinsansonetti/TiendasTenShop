@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Ranking Vendedores')

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
      <div class="col-sm-6"><h3 class="mb-0">Ranking Vendedores</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Ranking Vendedores</li>
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
                <form action="{{ route('cpanel.empleados.ranking') }}" method="GET" id="filtroForm">
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
                                    value="{{ request('fecha_inicio', $fecha_inicio ?? now()->startOfMonth()->format('Y-m-d')) }}"
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
                                    value="{{ request('fecha_fin', $fecha_fin ?? now()->format('Y-m-d')) }}"
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

        @if($rankingVendedor && $rankingVendedor->count() > 0)
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Ranking Vendedores (Ordenado por Unidades Vendidas)
                </h3>
                
                <div class="btn-group ms-auto">  <!-- ms-auto empuja a la derecha -->
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="pdfTablaRanking()">
                        <i class="fas fa-print me-1"></i>PDF
                    </button>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="exportarExcelRanking()">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaRankingVendedores">
                        <thead class="table-light">
                            <tr>
                                <th width="60" class="text-center">Pos.</th>
                                <th width="80" class="text-center">Foto</th>
                                <th>Vendedor</th>
                                <th width="200">Sucursal</th>
                                <th width="120" class="text-center">Unidades</th>
                                <th width="140" class="text-end">Ventas USD</th>
                                <th width="120" class="text-center">Valoración</th>
                                <th width="100" class="text-center">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankingVendedor as $index => $vendedor)
                                @php
                                    $fotoPerfil = $vendedor->Vendedor['FotoPerfil'] ?? '';
                                    $promedioVenta = $vendedor->total_ventas / $vendedor->total_unidades;

                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );
                                @endphp
                                <tr class="align-middle">
                                    <!-- Ranking estilo tarjeta -->
                                <td class="text-center">
                                    @if($vendedor->ranking == 1)
                                        <div class="card border-0 shadow-lg d-inline-block" style="width: 60px; background: linear-gradient(145deg, #ffd700, #ffb347);">
                                            <div class="card-body p-2 text-center">
                                                <i class="fas fa-crown fa-2x text-dark"></i>
                                                <h5 class="mb-0 text-dark fw-bold">1</h5>
                                            </div>
                                        </div>
                                    @elseif($vendedor->ranking == 2)
                                        <div class="card border-0 shadow d-inline-block" style="width: 60px; background: linear-gradient(145deg, #e8e8e8, #c0c0c0);">
                                            <div class="card-body p-2 text-center">
                                                <i class="fas fa-medal fa-2x" style="color: #cd7f32;"></i>
                                                <h5 class="mb-0 text-dark fw-bold">2</h5>
                                            </div>
                                        </div>
                                    @elseif($vendedor->ranking == 3)
                                        <div class="card border-0 shadow d-inline-block" style="width: 60px; background: linear-gradient(145deg, #b06d2e, #cd7f32);">
                                            <div class="card-body p-2 text-center">
                                                <i class="fas fa-medal fa-2x" style="color: #ffd700;"></i>
                                                <h5 class="mb-0 text-white fw-bold">3</h5>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark fs-5 p-3 rounded-circle shadow-sm" 
                                            style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center;">
                                            {{ $vendedor->ranking }}
                                        </span>
                                    @endif
                                </td>

                                    <!-- Foto -->
                                    <td class="text-center">
                                        <img src="{{ $imgSrc }}" 
                                            alt="{{ $vendedor->Vendedor['NombreCompleto'] ?? 'N/A' }}"
                                            class="rounded-circle border border-success img-zoomable" 
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: zoom-in;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ $imgSrc }}"
                                            data-description="{{ $vendedor->SucursalNombre }}">
                                    </td>

                                    <!-- Nombre y código -->
                                    <td>
                                        <strong>{{ $vendedor->Vendedor['NombreCompleto'] ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-id-badge me-1"></i>{{ $vendedor->Vendedor['VendedorId'] ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <!-- Sucursal -->
                                    <td>
                                        <span class="badge bg-warning text-white p-2">
                                            <i class="fas fa-store me-1"></i>{{ $vendedor->SucursalNombre ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <!-- Total Unidades -->
                                    <td class="text-center fw-bold">
                                        <span class="badge bg-primary text-white fs-7">
                                            {{ number_format($vendedor->total_unidades, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <!-- Total Ventas USD -->
                                    <td class="text-end fw-bold text-success fs-6">
                                        $ {{ number_format($vendedor->total_ventas, 2, ',', '.') }}
                                    </td>

                                    <!-- Valoración (estrellas según ranking) -->
                                    <td class="text-center">
                                        @php
                                            // Calcular estrellas según ranking
                                            $estrellas = 0;
                                            if ($vendedor->ranking == 1) {
                                                $estrellas = 5;
                                            } elseif ($vendedor->ranking == 2) {
                                                $estrellas = 4.5;
                                            } elseif ($vendedor->ranking == 3) {
                                                $estrellas = 4;
                                            } elseif ($vendedor->ranking <= 5) {
                                                $estrellas = 3.5;
                                            } elseif ($vendedor->ranking <= 10) {
                                                $estrellas = 3;
                                            } elseif ($vendedor->ranking <= 15) {
                                                $estrellas = 2.5;
                                            } elseif ($vendedor->ranking <= 20) {
                                                $estrellas = 2;
                                            } else {
                                                $estrellas = 1;
                                            }
                                        @endphp
                                        
                                        <!-- Estrellas con Bootstrap Icons -->
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($estrellas))
                                                    <i class="bi bi-star-fill"></i>
                                                @elseif($i == ceil($estrellas) && $estrellas - floor($estrellas) >= 0.5)
                                                    <i class="bi bi-star-half"></i>
                                                @else
                                                    <i class="bi bi-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>

                                    <!-- Detalle (botón para ver ventas del vendedor) -->
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Botón Ver (existente) -->
                                            <a href="{{ route('cpanel.empleados.ventas.vendedor', [
                                                    'id' => $vendedor->UsuarioId
                                                ]) }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Ver ventas del vendedor"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <!-- Botón Editar (nuevo) -->
                                            <a href="#"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Editar ventas del vendedor"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Resumen del período -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Período: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} 
                            al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                        </small>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            Total Vendedores: {{ $rankingVendedor->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="fas fa-chart-line me-1"></i>
                            Vendedor destacado: {{ $rankingVendedor->first()->Vendedor['NombreCompleto'] ?? 'N/A' }}
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
                        <i class="fas fa-chart-bar fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay ventas para mostrar</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron ventas para el período seleccionado.
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
</style>
@endsection