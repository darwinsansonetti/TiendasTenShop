@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Detalles de Venta')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Detalles de la Venta</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="#">Ventas por vendedores</a>
                    </li>
                    <li class="breadcrumb-item active">Detalles</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        
        <!-- Información del vendedor y venta -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-primary card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user me-2"></i>Información del Vendedor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                @php
                                    $fotoPerfil = $vendedor->FotoPerfil ?? '';
                                    $imgSrc = App\Helpers\FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );
                                @endphp
                                <img src="{{ $imgSrc }}" 
                                     alt="{{ $vendedor->NombreCompleto }}"
                                     class="rounded-circle border border-success"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <h4>{{ $vendedor->NombreCompleto }}</h4>
                                <p class="text-muted mb-1">
                                    <strong>Vendedor ID:</strong> {{ $vendedor->VendedorId }}
                                </p>
                                <p class="text-muted mb-1">
                                    <strong>Sucursal:</strong> {{ $sucursal->Nombre ?? 'N/A' }}
                                </p>
                                <p class="text-muted mb-0">
                                    <strong>Usuario ID:</strong> {{ $vendedor->UsuarioId }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-success card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-cart me-2"></i>Resumen de la Venta
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Fecha:</strong></td>
                                <td class="text-end">
                                    {{ $detallesVenta->first()->Fecha ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Productos:</strong></td>
                                <td class="text-end">{{ $totales->totalCantidad }} unidades</td>
                            </tr>
                            <tr>
                                <td><strong>Total USD:</strong></td>
                                <td class="text-end text-success fw-bold">
                                    $ {{ number_format($totales->totalDivisa, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Bs:</strong></td>
                                <td class="text-end text-success fw-bold">
                                    Bs. {{ number_format($totales->totalBs, 2, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de productos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Productos Vendidos
                </h3>
                
                <!-- Botones alineados a la derecha -->
                <div class="btn-group">
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="pdfTablaProductos()">
                        <i class="fas fa-print me-1"></i>PDF
                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="exportarExcelProductos()">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="tablaProductosVenta">
                        <thead class="table-light">
                            <tr>
                                <th width="80" class="text-center">Producto</th>
                                <th width="100">Código</th>
                                <th>Descripción</th>
                                <th width="100" class="text-center">Venta (Cant)</th>
                                <th width="120" class="text-end">Costo USD</th>
                                <th width="120" class="text-end">PVP USD</th>
                                <th width="120" class="text-end">PVP Bs</th>
                                <th width="140" class="text-end">Total USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detallesVenta as $index => $producto)
                            @php
                                $totalUsd = $producto->Cantidad * $producto->MontoDivisa;
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $producto->ProductoUrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                            @endphp
                            <tr>
                                <!-- Foto del producto -->
                                <td class="text-center">
                                    <img src="{{ $urlImagen }}" 
                                        alt="{{ $producto->ProductoDescripcion }}"
                                        class="rounded img-thumbnail img-zoomable"
                                        style="width: 60px; height: 60px; object-fit: cover; cursor: zoom-in;"
                                        onclick="zoomImagen(this)"
                                        data-full-image="{{ $urlImagen }}"
                                        data-description="{{ $producto->ProductoDescripcion }}">
                                </td>
                                
                                <!-- Código -->
                                <td>
                                    <span class="badge bg-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                                        {{ $producto->ProductoCodigo }}
                                    </span>
                                </td>
                                
                                <!-- Descripción -->
                                <td>
                                    <strong>{{ $producto->ProductoNombre }}</strong>
                                    @if(!empty($producto->ProductoDescripcion))
                                        <br><small class="text-muted">{{ $producto->ProductoDescripcion }}</small>
                                    @endif
                                    @if(!empty($producto->ProductoMarca))
                                        <br><small class="text-info">Marca: {{ $producto->ProductoMarca }}</small>
                                    @endif
                                </td>
                                
                                <!-- Cantidad -->
                                <td class="text-center fw-bold" data-order="{{ $producto->Cantidad }}">
                                    {{ number_format($producto->Cantidad, 0, ',', '.') }}
                                </td>
                                
                                <!-- Costo USD -->
                                <td class="text-end text-danger" data-order="{{ $producto->CostoDivisa }}">
                                    $ {{ number_format($producto->CostoDivisa, 2, ',', '.') }}
                                </td>
                                
                                <!-- PVP USD -->
                                <td class="text-end text-primary" data-order="{{ $producto->MontoDivisa }}">
                                    $ {{ number_format($producto->MontoDivisa, 2, ',', '.') }}
                                </td>
                                
                                <!-- PVP Bs -->
                                <td class="text-end text-success" data-order="{{ $producto->PrecioVenta }}">
                                    Bs. {{ number_format($producto->PrecioVenta, 2, ',', '.') }}
                                </td>
                                
                                <!-- Total USD -->
                                <td class="text-end fw-bold text-warning" data-order="{{ $totalUsd }}">
                                    $ {{ number_format($totalUsd, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // ORDENAR TABLA DE PRODUCTOS
        (function() {
            const tabla = document.getElementById('tablaProductosVenta');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;

            ths.forEach((th, index) => {
                const texto = th.textContent.trim().toLowerCase();

                // No ordenar columna de imagen
                if (texto.includes('producto') && index === 0) return;

                th.style.cursor = 'pointer';
                th.setAttribute('title', 'Click para ordenar');

                th.addEventListener('click', () => {
                    ordenarTablaProductos(tabla, index, ordenAscendente);
                    ordenAscendente = !ordenAscendente;
                });
            });

            function ordenarTablaProductos(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr'));

                filas.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];

                    if (!tdA || !tdB) return 0;

                    const valorA = extraerValorCeldaProducto(tdA);
                    const valorB = extraerValorCeldaProducto(tdB);

                    const numA = parseFloat(valorA);
                    const numB = parseFloat(valorB);

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    } else {
                        return asc ? valorA.localeCompare(valorB) : valorB.localeCompare(valorA);
                    }
                });

                filas.forEach(fila => tbody.appendChild(fila));
            }

            function extraerValorCeldaProducto(td) {
                // Prioridad: data-order
                if (td.dataset && td.dataset.order) {
                    return td.dataset.order;
                }

                // Badge
                const badge = td.querySelector('.badge');
                if (badge) {
                    return badge.textContent.trim();
                }

                // Texto plano
                return td.textContent.trim().replace(/[$,Bs.]/g, '').trim();
            }
        })();
    });

    // EXPORTAR A EXCEL
    function exportarExcelProductos() {
        const tabla = document.getElementById('tablaProductosVenta');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // Encabezados
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th) => {
            const texto = th.textContent.trim();
            headers.push(texto);
        });
        datos.push(headers);

        // Filas
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const rowData = [];
            fila.querySelectorAll('td').forEach(td => {
                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                
                // Si hay imagen, no exportamos la imagen en sí
                if (td.querySelector('img')) {
                    rowData.push('IMAGEN');
                } else {
                    // Limpiar formato de moneda para números
                    texto = texto.replace('$', '').replace('Bs.', '').replace(/\./g, '').replace(',', '.').trim();
                    rowData.push(texto);
                }
            });
            datos.push(rowData);
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
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 50) }));

        XLSX.utils.book_append_sheet(wb, ws, 'ProductosVenta');
        
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Productos_Venta_${fecha}.xlsx`);
    }

    // EXPORTAR A PDF
    function pdfTablaProductos() {
        const tabla = document.getElementById('tablaProductosVenta');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título
        const titulo = 'Productos Vendidos - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);

        // Encabezados
        const headers = [];
        tabla.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });

        // Datos
        const datos = [];
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const filaData = [];
            fila.querySelectorAll('td').forEach((td, index) => {
                if (index === 0) {
                    filaData.push('IMAGEN'); // Columna de imagen
                } else {
                    let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                    filaData.push(texto);
                }
            });
            datos.push(filaData);
        });

        // Generar PDF
        doc.autoTable({
            head: [headers],
            body: datos,
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
            margin: { top: 35 },
            columnStyles: {
                0: { cellWidth: 20 }, // Imagen
                1: { cellWidth: 25 }, // Código
                3: { halign: 'center' }, // Cantidad
                4: { halign: 'right' }, // Costo USD
                5: { halign: 'right' }, // PVP USD
                6: { halign: 'right' }, // PVP Bs
                7: { halign: 'right' }, // Total USD
            }
        });

        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
            doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, doc.internal.pageSize.height - 10);
        }

        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Productos_Venta_${fecha}.pdf`);
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
    .card-header.d-flex {
        display: flex !important;
    }
    
    .card-header .btn-group {
        margin-left: auto !important;
    }
</style>
@endsection