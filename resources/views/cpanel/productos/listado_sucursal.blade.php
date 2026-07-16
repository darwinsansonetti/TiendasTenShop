@extends('layout.layout_dashboard')

@section('title', 'Listado de Productos por Sucursal')

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
                         style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="bi bi-box-seam text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Listado de Productos por Sucursal</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Consulta y gestión de productos por sucursal</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Productos por Sucursal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Tabla de productos --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-ul me-2"></i>Listado de Productos
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        @if($sucursalId > 0 && $productos->count() > 0)
                        <button type="button" class="btn btn-success btn-sm fw-semibold" onclick="exportarExcel()" style="font-size:0.78rem;">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm fw-semibold" onclick="exportarPDF()" style="font-size:0.78rem;">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </button>
                        @endif
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            {{ $productos->count() }} productos
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PRODUCTO</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO USD</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">PVP USD</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">EXISTENCIA</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">ESTATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $producto)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-center">
                                    @php
                                        $imgSrc = $producto->UrlFoto ?? 'assets/img/adminlte/img/produc_default.jfif';
                                        $imgAlt = $producto->Codigo ?? 'Producto';
                                        $imgDesc = $producto->producto_nombre ?? 'Producto';
                                    @endphp
                                    <img src="{{ asset($imgSrc) }}"
                                         loading="lazy"
                                         alt="{{ $imgAlt }}"
                                         class="img-thumbnail img-zoomable"
                                         style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                                         data-full-image="{{ asset($imgSrc) }}"
                                         data-description="{{ $imgDesc }}"
                                         onclick="zoomImagen(this)"
                                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td>
                                    <span class="badge rounded-2 fw-semibold"
                                          style="background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);font-size:0.78rem;font-family:monospace;">
                                        {{ $producto->Codigo ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.85rem;">{{ $producto->Referencia ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark" style="font-size:0.88rem;">{{ $producto->producto_nombre ?? 'N/A' }}</td>
                                <td class="text-end fw-semibold" style="color:#059669;">${{ number_format($producto->CostoDivisa ?? 0, 2) }}</td>
                                <td class="text-end fw-semibold" style="color:#1d4ed8;">${{ number_format($producto->PvpDivisa ?? 0, 2) }}</td>
                                <td class="text-end fw-bold">{{ $producto->Existencia ?? 0 }}</td>
                                <td class="text-center">
                                    @if($producto->producto_estatus == 1)
                                        <span class="badge rounded-pill bg-success">Activo</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger">Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    @if($sucursalId > 0)
                                        No hay productos en esta sucursal
                                    @else
                                        Seleccione una sucursal para ver los productos
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($productos->count() > 0)
            <div class="card-footer border-0 py-3 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <small class="text-muted">
                        Mostrando {{ $productos->count() }} productos
                    </small>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // ZOOM DE IMAGEN
    // ============================================
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'rounded-3 shadow-lg'
            }
        });
    }

    // ============================================
    // EXPORTAR A EXCEL
    // ============================================
    function exportarExcel() {
        try {
            const tabla = document.getElementById('tablaProductos');
            const rows = tabla.querySelectorAll('tbody tr');
            
            if (!rows.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'No hay productos para exportar',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            const data = [];
            
            data.push([
                'CÓDIGO',
                'REFERENCIA',
                'PRODUCTO',
                'COSTO USD',
                'PVP USD',
                'EXISTENCIA',
                'ESTATUS'
            ]);

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 8) return;

                const codigo = cells[1]?.textContent?.trim() || '';
                const referencia = cells[2]?.textContent?.trim() || '';
                const producto = cells[3]?.textContent?.trim() || '';
                const costo = cells[4]?.textContent?.trim().replace('$', '').replace(',', '') || '0';
                const pvp = cells[5]?.textContent?.trim().replace('$', '').replace(',', '') || '0';
                const existencia = cells[6]?.textContent?.trim() || '0';
                const estatus = cells[7]?.textContent?.trim() || '';

                data.push([codigo, referencia, producto, parseFloat(costo), parseFloat(pvp), parseInt(existencia), estatus]);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);

            ws['!cols'] = [
                { wch: 15 },
                { wch: 15 },
                { wch: 40 },
                { wch: 15 },
                { wch: 15 },
                { wch: 12 },
                { wch: 12 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Productos');

            const sucursalNombre = '{{ $sucursalNombre }}';
            const fecha = new Date().toISOString().slice(0,10);
            const nombreArchivo = `productos_${sucursalNombre}_${fecha}.xlsx`;
            XLSX.writeFile(wb, nombreArchivo);

            Swal.fire({
                icon: 'success',
                title: 'Excel generado',
                text: `Se exportaron ${rows.length} productos`,
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar el Excel: ' + error.message,
                confirmButtonColor: '#dc2626'
            });
        }
    }

    // ============================================
    // EXPORTAR A PDF
    // ============================================
    function exportarPDF() {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape', 'mm', 'a4');

            const sucursalNombre = '{{ $sucursalNombre }}';
            const fecha = new Date().toLocaleString('es-ES');
            const titulo = `Listado de Productos - ${sucursalNombre}`;

            doc.setFontSize(14);
            doc.text(titulo, 14, 15);
            doc.setFontSize(9);
            doc.text(`Fecha: ${fecha}`, 14, 22);

            const tabla = document.getElementById('tablaProductos');
            const rows = tabla.querySelectorAll('tbody tr');

            if (!rows.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'No hay productos para exportar',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            const headers = [
                'CÓDIGO',
                'REFERENCIA',
                'PRODUCTO',
                'COSTO USD',
                'PVP USD',
                'EXISTENCIA',
                'ESTATUS'
            ];

            const data = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 8) return;

                data.push([
                    cells[1]?.textContent?.trim() || '',
                    cells[2]?.textContent?.trim() || '',
                    cells[3]?.textContent?.trim() || '',
                    cells[4]?.textContent?.trim().replace('$', '') || '0',
                    cells[5]?.textContent?.trim().replace('$', '') || '0',
                    cells[6]?.textContent?.trim() || '0',
                    cells[7]?.textContent?.trim() || ''
                ]);
            });

            doc.autoTable({
                head: [headers],
                body: data,
                startY: 30,
                theme: 'grid',
                headStyles: {
                    fillColor: [59, 130, 246],
                    textColor: 255,
                    fontSize: 8,
                    fontStyle: 'bold'
                },
                bodyStyles: {
                    fontSize: 7,
                    cellPadding: 2
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                margin: { top: 35 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 25 },
                    2: { cellWidth: 45 },
                    3: { halign: 'right' },
                    4: { halign: 'right' },
                    5: { halign: 'right' },
                    6: { cellWidth: 20 }
                }
            });

            const totalPaginas = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPaginas; i++) {
                doc.setPage(i);
                doc.setFontSize(7);
                doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 25, doc.internal.pageSize.height - 10);
                doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, doc.internal.pageSize.height - 10);
            }

            const nombreArchivo = `productos_${sucursalNombre}_${new Date().toISOString().slice(0,10)}.pdf`;
            doc.save(nombreArchivo);

            Swal.fire({
                icon: 'success',
                title: 'PDF generado',
                text: `Se exportaron ${rows.length} productos`,
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar el PDF: ' + error.message,
                confirmButtonColor: '#dc2626'
            });
        }
    }
</script>
@endsection

@push('styles')
<style>
    #tablaProductos tbody tr:hover { background: #f8fafc; }
    
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .img-zoomable:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
        position: relative;
    }
</style>
@endpush