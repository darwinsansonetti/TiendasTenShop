@extends('layout.layout_dashboard')

@section('title', 'Inventario de Almacén')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-box-seam text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Inventario de Almacén</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Productos disponibles en <strong>{{ $sucursal->Nombre ?? 'Almacén' }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventario de Almacén</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-box-seam me-2"></i>Listado de Productos
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        {{-- Botón Exportar Excel --}}
                        <button onclick="exportarExcel()" 
                                class="btn btn-sm text-white"
                                style="background:rgba(16,185,129,0.3);border:1px solid rgba(16,185,129,0.4);">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </button>
                        
                        {{-- Botón Exportar PDF --}}
                        <button onclick="exportarPDF()" 
                                class="btn btn-sm text-white"
                                style="background:rgba(239,68,68,0.3);border:1px solid rgba(239,68,68,0.4);">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </button>
                        
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            {{ $productos->count() }} productos
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaInventario">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:60px;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">EXISTENCIA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos ?? [] as $producto)
                            @php
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $producto->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                
                                $existencia = (float)($producto->Existencia ?? 0);
                                if ($existencia <= 0) {
                                    $estatus = ['texto' => 'Agotado', 'clase' => 'badge bg-danger text-white'];
                                } elseif ($existencia <= 5) {
                                    $estatus = ['texto' => 'Bajo Stock', 'clase' => 'badge bg-warning text-dark'];
                                } else {
                                    $estatus = ['texto' => 'Disponible', 'clase' => 'badge bg-success text-white'];
                                }
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    <img src="{{ $imgSrc }}" 
                                         loading="lazy"
                                         alt="{{ $producto->Codigo ?? 'Producto' }}"
                                         class="img-thumbnail img-zoomable"
                                         style="width:40px;height:40px;object-fit:cover;cursor:pointer;"
                                         data-full-image="{{ $imgSrc }}"
                                         data-description="{{ $producto->Descripcion ?? 'Producto' }}"
                                         onclick="zoomImagen(this)"
                                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td class="fw-semibold text-dark">{{ $producto->Codigo ?? 'N/A' }}</td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $producto->Referencia ?? 'N/A' }}</td>
                                <td class="text-muted" style="font-size:0.88rem;">{{ $producto->Descripcion ?? 'N/A' }}</td>
                                <td class="text-center fw-semibold">
                                    <span class="badge {{ $existencia <= 0 ? 'bg-danger' : ($existencia <= 5 ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill px-2 py-1">
                                        {{ number_format($existencia, 0) }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold" style="color:#059669;">
                                    $ {{ number_format((float)($producto->CostoDivisa ?? 0), 2) }}
                                </td>
                                <td class="pe-4 text-center">
                                    <span class="{{ $estatus['clase'] }} rounded-pill px-2 py-1 fw-semibold" style="font-size:0.75rem;">
                                        {{ $estatus['texto'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-box-seam text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en el inventario</p>
                                    <small class="text-muted">Los productos aparecerán aquí cuando se agreguen al almacén</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if(isset($productos) && $productos->count() > 0)
            <div class="card-footer border-0 bg-transparent py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="text-muted" style="font-size:0.85rem;">
                            Mostrando <strong>{{ $productos->count() }}</strong> productos
                        </span>
                    </div>
                    <div>
                        <span class="text-muted" style="font-size:0.85rem;">
                            Total en existencia: 
                            <strong>{{ number_format($productos->sum('Existencia') ?? 0, 0) }}</strong> unidades
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para buscar en la tabla
    function buscarProducto() {
        const input = document.getElementById('buscarInput');
        if (!input) return;
        
        const filter = input.value.toUpperCase();
        const table = document.querySelector('table tbody');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 1; j < cells.length; j++) {
                const textValue = cells[j].textContent || cells[j].innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    }

    // Función para ordenar la tabla
    function ordenarTabla(columna) {
        const table = document.querySelector('table');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));
        
        const isAscending = table.dataset.sortAsc === 'true';
        table.dataset.sortAsc = !isAscending;
        
        rows.sort((a, b) => {
            const aValue = a.getElementsByTagName('td')[columna].textContent.trim();
            const bValue = b.getElementsByTagName('td')[columna].textContent.trim();
            
            const aNum = parseFloat(aValue.replace(/,/g, ''));
            const bNum = parseFloat(bValue.replace(/,/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            return isAscending 
                ? aValue.localeCompare(bValue) 
                : bValue.localeCompare(aValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    // Función para hacer zoom en la imagen
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const description = element.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            imageUrl: imgSrc,
            imageAlt: description,
            title: description,
            imageWidth: 400,
            imageHeight: 400,
            imageClass: 'rounded',
            confirmButtonColor: '#7c3aed',
            confirmButtonText: 'Cerrar'
        });
    }

    // ✅ EXPORTAR A EXCEL
    function exportarExcel() {
        // Mostrar loading
        Swal.fire({
            title: 'Generando Excel...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            // Obtener datos de la tabla
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            // Crear array de datos
            const data = [];
            
            // Encabezados
            data.push(['CÓDIGO', 'REFERENCIA', 'DESCRIPCIÓN', 'EXISTENCIA', 'COSTO', 'ESTATUS']);
            
            // Datos
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    // Omitir columna de foto (índice 0)
                    const codigo = cells[1]?.textContent.trim() || 'N/A';
                    const referencia = cells[2]?.textContent.trim() || 'N/A';
                    const descripcion = cells[3]?.textContent.trim() || 'N/A';
                    const existencia = cells[4]?.textContent.trim() || '0';
                    const costo = cells[5]?.textContent.trim() || '$ 0.00';
                    const estatus = cells[6]?.textContent.trim() || 'N/A';
                    
                    data.push([codigo, referencia, descripcion, existencia, costo, estatus]);
                }
            });

            // Crear libro de Excel
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Ajustar ancho de columnas
            ws['!cols'] = [
                { wch: 15 }, // CÓDIGO
                { wch: 15 }, // REFERENCIA
                { wch: 40 }, // DESCRIPCIÓN
                { wch: 12 }, // EXISTENCIA
                { wch: 15 }, // COSTO
                { wch: 15 }  // ESTATUS
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Inventario');
            
            // Generar archivo
            const filename = `Inventario_Almacen_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
            
            Swal.fire({
                icon: 'success',
                title: '¡Excel generado!',
                text: `Archivo: ${filename}`,
                timer: 2000,
                showConfirmButton: false
            });
            
        } catch (error) {
            console.error('Error al exportar Excel:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar el archivo Excel'
            });
        }
    }

    // ✅ EXPORTAR A PDF
    function exportarPDF() {
        // Mostrar loading
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            // Obtener datos de la tabla
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            // Crear array de datos para el PDF
            const data = [];
            
            // Datos
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    // Omitir columna de foto (índice 0)
                    const codigo = cells[1]?.textContent.trim() || 'N/A';
                    const referencia = cells[2]?.textContent.trim() || 'N/A';
                    const descripcion = cells[3]?.textContent.trim() || 'N/A';
                    const existencia = cells[4]?.textContent.trim() || '0';
                    const costo = cells[5]?.textContent.trim() || '$ 0.00';
                    const estatus = cells[6]?.textContent.trim() || 'N/A';
                    
                    data.push([codigo, referencia, descripcion, existencia, costo, estatus]);
                }
            });

            // Crear PDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape', 'mm', 'a4');
            
            // Título
            doc.setFontSize(16);
            doc.setTextColor(124, 58, 237);
            doc.text('Inventario de Almacén', 14, 20);
            
            // Subtítulo
            doc.setFontSize(10);
            doc.setTextColor(100);
            const fecha = new Date().toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            doc.text(`Fecha: ${fecha}`, 14, 28);
            doc.text(`Total productos: ${data.length}`, 14, 34);
            
            // Configurar tabla
            doc.autoTable({
                startY: 40,
                head: [['CÓDIGO', 'REFERENCIA', 'DESCRIPCIÓN', 'EXISTENCIA', 'COSTO', 'ESTATUS']],
                body: data,
                theme: 'striped',
                headStyles: {
                    fillColor: [124, 58, 237],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 25 },
                    1: { cellWidth: 25 },
                    2: { cellWidth: 70 },
                    3: { cellWidth: 20, halign: 'center' },
                    4: { cellWidth: 25, halign: 'right' },
                    5: { cellWidth: 25, halign: 'center' }
                },
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                },
                didDrawPage: function(data) {
                    // Pie de página
                    const pageCount = doc.internal.getNumberOfPages();
                    for (let i = 1; i <= pageCount; i++) {
                        doc.setPage(i);
                        doc.setFontSize(7);
                        doc.setTextColor(150);
                        doc.text(
                            `Página ${i} de ${pageCount} - Generado: ${fecha}`,
                            doc.internal.pageSize.width / 2,
                            doc.internal.pageSize.height - 10,
                            { align: 'center' }
                        );
                    }
                }
            });

            // Guardar PDF
            const filename = `Inventario_Almacen_${new Date().toISOString().slice(0,10)}.pdf`;
            doc.save(filename);
            
            Swal.fire({
                icon: 'success',
                title: '¡PDF generado!',
                text: `Archivo: ${filename}`,
                timer: 2000,
                showConfirmButton: false
            });
            
        } catch (error) {
            console.error('Error al exportar PDF:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar el archivo PDF'
            });
        }
    }
</script>
@endsection