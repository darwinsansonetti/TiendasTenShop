@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Préstamos de Empleados')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Préstamos de Empleados</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Préstamos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">  

    @if($empleados && $empleados->count() > 0)
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-hand-holding-usd me-2"></i>Listado de Empleados
                        </h3>
                        
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                id="buscadorEmpleados" 
                                class="form-control" 
                                placeholder="Buscar empleado..."
                                autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="limpiarBuscadorEmpleados">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <div class="btn-group">
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="pdfTablaEmpleados()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="exportarExcelEmpleados()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="tablaEmpleados">
                        <thead class="table-light">
                            <tr>
                                <th width="60" class="text-center">Foto</th>
                                <th width="200" class="sortable" data-col="nombre">Empleado <span class="sort-icon">↕️</span></th>
                                <th width="120" class="sortable" data-col="sucursal">Sucursal <span class="sort-icon">↕️</span></th>
                                <th width="120" class="text-center sortable" data-col="total_prestamo">Total Prestamos <span class="sort-icon">↕️</span></th>
                                <th width="120" class="text-center sortable" data-col="total_abonado">Abonado <span class="sort-icon">↕️</span></th>
                                <th width="120" class="text-center sortable" data-col="total_pendiente">Pendiente <span class="sort-icon">↕️</span></th>
                                <th width="100" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empleados as $index => $empleado)
                                @php
                                    // Propiedades del empleado
                                    $id = $empleado->id ?? '';
                                    $nombre = $empleado->nombre_completo ?? 'N/A';
                                    $email = $empleado->email ?? '';
                                    $rol = $empleado->rol_nombre ?? 'N/A';
                                    $sucursalNombre = $empleado->sucursal_nombre ?? 'N/A';
                                    $fotoPerfil = $empleado->foto_perfil ?? '';
                                    $origen = $empleado->origen ?? 'Usuario';
                                    $vendedorId = $empleado->vendedor_id ?? 'N/A';
                                    
                                    // Totales de préstamos
                                    $totalPrestamos = $empleado->total_prestamos ?? 0;
                                    $totalDivisa = $empleado->monto_total_prestamo ?? 0;
                                    
                                    // Calcular total abonado y pendiente
                                    $totalAbonado = 0;
                                    if ($empleado->prestamos && $empleado->prestamos->count() > 0) {
                                        foreach ($empleado->prestamos as $prestamo) {
                                            $totalAbonado += $prestamo->total_pagado_divisa ?? 0;
                                        }
                                    }
                                    $totalPendiente = $totalDivisa - $totalAbonado;
                                    
                                    // Determinar color según el rol
                                    $rolColor = 'secondary';
                                    $rolIcon = 'user';
                                    $rolNombreMostrar = $rol;
                                    
                                    if ($rol == 'ADMIN') {
                                        $rolColor = 'danger';
                                        $rolIcon = 'crown';
                                    } elseif ($rol == 'SUPERVISOR') {
                                        $rolColor = 'info';
                                        $rolIcon = 'user-cog';
                                    } elseif ($rol == 'DEPOSITARIO') {
                                        $rolColor = 'primary';
                                        $rolIcon = 'warehouse';
                                    } elseif ($rol == 'VENDEDOR' || $rol == 'VENDEDORES') {
                                        $rolColor = 'success';
                                        $rolIcon = 'user-tie';
                                    } elseif ($origen == 'Usuario' && $rol == 'VENDEDOR') {
                                        $rolColor = 'success';
                                        $rolIcon = 'user-tie';
                                    } elseif ($origen == 'Usuario') {
                                        $rolColor = 'secondary';
                                        $rolIcon = 'user';
                                        $rolNombreMostrar = 'Vendedor Temporal';
                                    }
                                    
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );
                                @endphp
                                <tr class="align-middle" data-origen="{{ $origen }}" data-vendedor-id="{{ $vendedorId }}">
                                    <!-- Foto -->
                                    <td class="text-center">
                                        <img src="{{ $imgSrc }}" 
                                            alt="{{ $nombre }}"
                                            class="rounded-circle border border-success img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                            onclick="zoomImagen(this)"
                                            data-full-image="{{ $imgSrc }}"
                                            data-description="{{ $nombre }}">
                                    </td>

                                    <!-- Empleado -->
                                    <td data-order="{{ $nombre }}">
                                        <strong>{{ $nombre }}</strong>
                                        <br>
                                        <small class="badge bg-{{ $rolColor }} mt-1">
                                            <i class="fas fa-{{ $rolIcon }} me-1"></i>{{ $rolNombreMostrar }}
                                        </small>
                                        @if($origen == 'Usuario' && $vendedorId != 'N/A')
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-id-badge me-1"></i>{{ $vendedorId }}
                                            </small>
                                        @endif
                                    </td>

                                    <!-- Sucursal -->
                                    <td data-order="{{ $sucursalNombre }}">
                                        <span class="badge bg-warning text-dark p-2">
                                            <i class="fas fa-store me-1"></i>{{ $sucursalNombre }}
                                        </span>
                                    </td>

                                    <!-- Total Divisa -->
                                    <td class="text-center" data-order="{{ $totalDivisa }}">
                                        @if($totalDivisa > 0)
                                            <strong class="text-success">
                                                ${{ number_format($totalDivisa, 2) }}
                                            </strong>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Total Abonado -->
                                    <td class="text-center" data-order="{{ $totalAbonado }}">
                                        @if($totalAbonado > 0)
                                            <span class="text-primary">
                                                ${{ number_format($totalAbonado, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Total Pendiente -->
                                    <td class="text-center" data-order="{{ $totalPendiente }}">
                                        @if($totalPendiente > 0)
                                            <span class="text-danger fw-bold">
                                                ${{ number_format($totalPendiente, 2) }}
                                            </span>
                                        @elseif($totalPendiente == 0 && $totalPrestamos > 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Pagado
                                            </span>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Acción Ver Detalles -->
                                    <td class="text-center">
                                        @if($totalPrestamos > 0)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    onclick="verDetallesPrestamos('{{ $id }}', '{{ addslashes($nombre) }}')"
                                                    title="Ver detalles de préstamos"
                                                    data-bs-toggle="tooltip">
                                                <i class="fas fa-eye me-1"></i> Ver
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    disabled
                                                    title="Sin préstamos">
                                                <i class="fas fa-eye me-1"></i> Ver
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            Total Empleados: {{ $empleados->count() }}
                        </small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-hand-holding-usd me-1"></i>
                            Con préstamos: {{ $empleados->where('total_prestamos', '>', 0)->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="fas fa-check-circle me-1"></i>
                            Sin préstamos: {{ $empleados->where('total_prestamos', 0)->count() }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users fa-4x text-muted"></i>
                    </div>
                    <h3 class="empty-state-title mt-3">No hay empleados para mostrar</h3>
                    <p class="empty-state-subtitle">
                        No se encontraron empleados activos en el sistema.
                    </p>
                </div>
            </div>
        </div>
    @endif
    </div>
</div>

<!-- Modal para ver detalles de préstamos -->
<div class="modal fade" id="modalDetallesPrestamos" tabindex="-1" aria-labelledby="modalDetallesPrestamosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetallesPrestamosLabel">
                    <i class="fas fa-hand-holding-usd me-2"></i>Detalles de Préstamos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetallesPrestamosBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando información de préstamos...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
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
    let prestamosData = @json($empleados);

    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // BUSCADOR DE EMPLEADOS
        // ==========================
        const buscadorEmpleados = document.getElementById('buscadorEmpleados');
        const tablaEmpleados = document.getElementById('tablaEmpleados');
        const limpiarBtnEmpleados = document.getElementById('limpiarBuscadorEmpleados');
        
        if (buscadorEmpleados && tablaEmpleados) {
            function filtrarTablaEmpleados() {
                const textoBusqueda = buscadorEmpleados.value.toLowerCase().trim();
                const tbody = tablaEmpleados.querySelector('tbody');
                const filas = tbody.querySelectorAll('tr:not(.no-results-message)');
                let filasVisibles = 0;
                
                filas.forEach(fila => {
                    const celdaEmpleado = fila.children[1];
                    
                    if (celdaEmpleado) {
                        const textoEmpleado = celdaEmpleado.textContent.toLowerCase();
                        
                        if (textoBusqueda === '' || textoEmpleado.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });
                
                let mensajeNoResultados = document.getElementById('mensajeNoResultadosEmpleados');
                
                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultadosEmpleados';
                        mensajeNoResultados.className = 'no-results-message';
                        const colspan = tablaEmpleados.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="fas fa-search me-2"></i>
                                No se encontraron empleados con el nombre "${buscadorEmpleados.value}"
                            </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }
            
            buscadorEmpleados.addEventListener('input', filtrarTablaEmpleados);
            
            if (limpiarBtnEmpleados) {
                limpiarBtnEmpleados.addEventListener('click', function() {
                    buscadorEmpleados.value = '';
                    filtrarTablaEmpleados();
                    buscadorEmpleados.focus();
                });
            }
            
            buscadorEmpleados.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    buscadorEmpleados.value = '';
                    filtrarTablaEmpleados();
                }
            });
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaEmpleados');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';
                
                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);
                    
                    document.querySelectorAll('.sort-icon').forEach(icon => {
                        icon.innerHTML = '↕️';
                    });
                    
                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }
                    
                    const icono = th.querySelector('.sort-icon');
                    if (icono) {
                        icono.innerHTML = ordenAscendente ? '⬆️' : '⬇️';
                    } else {
                        ths.forEach(t => t.classList.remove('sort-asc', 'sort-desc'));
                        th.classList.add(ordenAscendente ? 'sort-asc' : 'sort-desc');
                    }
                    
                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"]):not(.no-results-message)'));
                
                filas.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];
                    
                    if (!tdA || !tdB) return 0;

                    const valorA = tdA.dataset.order || parseFloat(tdA.textContent.replace(/[^0-9.-]/g, '')) || tdA.textContent;
                    const valorB = tdB.dataset.order || parseFloat(tdB.textContent.replace(/[^0-9.-]/g, '')) || tdB.textContent;

                    const numA = parseFloat(valorA);
                    const numB = parseFloat(valorB);

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    } else {
                        return asc 
                            ? valorA.toString().localeCompare(valorB.toString())
                            : valorB.toString().localeCompare(valorA.toString());
                    }
                });

                const mensajeNoResultados = document.getElementById('mensajeNoResultadosEmpleados');
                
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                
                filas.forEach(fila => tbody.appendChild(fila));
                
                if (mensajeNoResultados) {
                    tbody.appendChild(mensajeNoResultados);
                }
            }
        })();
    });

    function exportarExcelEmpleados() {
        const tabla = document.getElementById('tablaEmpleados');
        if (!tabla) return;

        const datos = [['Empleado', 'Cargo', 'Sucursal', 'Total Préstamos', 'Total Divisa', 'Total Abonado', 'Total Pendiente']];

        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            const nombre = celdas[1]?.querySelector('strong')?.textContent.trim() || '';
            const cargo = celdas[1]?.querySelector('.badge')?.textContent.trim() || '';
            const sucursal = celdas[2]?.querySelector('.badge')?.textContent.trim() || '';
            const totalPrestamos = celdas[3]?.textContent.trim().replace(/[^0-9]/g, '') || '0';
            const totalDivisa = celdas[4]?.textContent.trim().replace('$', '').replace(',', '') || '0';
            const totalAbonado = celdas[5]?.textContent.trim().replace('$', '').replace(',', '') || '0';
            const totalPendiente = celdas[6]?.textContent.trim().replace('$', '').replace(',', '') || '0';
            
            datos.push([nombre, cargo, sucursal, totalPrestamos, totalDivisa, totalAbonado, totalPendiente]);
        });

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);
        XLSX.utils.book_append_sheet(wb, ws, 'Prestamos');
        XLSX.writeFile(wb, `Prestamos_${new Date().toISOString().split('T')[0]}.xlsx`);
    }

    function pdfTablaEmpleados() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Listado de Préstamos por Empleado', 14, 15);
        
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

        const headers = [['Empleado', 'Cargo', 'Sucursal', 'Total Préstamos', 'Total Divisa', 'Total Abonado', 'Total Pendiente']];
        const datos = [];

        const tabla = document.getElementById('tablaEmpleados');
        if (tabla) {
            tabla.querySelectorAll('tbody tr').forEach(fila => {
                const celdas = fila.querySelectorAll('td');
                const nombre = celdas[1]?.querySelector('strong')?.textContent.trim() || '';
                const cargo = celdas[1]?.querySelector('.badge')?.textContent.trim() || '';
                const sucursal = celdas[2]?.querySelector('.badge')?.textContent.trim() || '';
                const totalPrestamos = celdas[3]?.textContent.trim() || '0';
                const totalDivisa = celdas[4]?.textContent.trim() || '$0.00';
                const totalAbonado = celdas[5]?.textContent.trim() || '$0.00';
                const totalPendiente = celdas[6]?.textContent.trim() || '$0.00';
                
                datos.push([nombre, cargo, sucursal, totalPrestamos, totalDivisa, totalAbonado, totalPendiente]);
            });
        }

        doc.autoTable({
            head: headers,
            body: datos,
            startY: 30,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9, cellPadding: 3 },
            alternateRowStyles: { fillColor: [245, 245, 245] }
        });

        const finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(`Total Empleados: ${datos.length}`, 14, finalY);

        doc.save(`Prestamos_${new Date().toISOString().split('T')[0]}.pdf`);
    }

    function verDetallesPrestamos(usuarioId, nombreEmpleado) {
        const empleado = prestamosData.find(e => e.id === usuarioId);
        
        if (!empleado || !empleado.prestamos || empleado.prestamos.length === 0) {
            Swal.fire('Sin préstamos', `${nombreEmpleado} no tiene préstamos activos`, 'info');
            return;
        }

        let html = `
            <div class="table-responsive">
                <h5 class="mb-3">Empleado: <strong>${nombreEmpleado}</strong></h5>
                <table class="table table-sm table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Préstamo</th>
                            <th>Fecha</th>
                            <th>Monto Divisa</th>
                            <th>Productos</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        empleado.prestamos.forEach(prestamo => {
            const fecha = new Date(prestamo.Fecha).toLocaleDateString('es-VE');
            const montoDivisa = parseFloat(prestamo.MontoDivisa || 0).toFixed(2);
            const totalProductos = prestamo.total_detalles_divisa || 0;
            const totalPagado = prestamo.total_pagado_divisa || 0;
            const saldoPendiente = prestamo.saldo_pendiente_divisa || 0;
            const estatusTexto = prestamo.Estatus == 1 ? 'Nuevo' : (prestamo.Estatus == 2 ? 'En Proceso' : 'Incluido');
            const estatusColor = prestamo.Estatus == 1 ? 'warning' : (prestamo.Estatus == 2 ? 'info' : 'secondary');
            
            html += `
                <tr>
                    <td><strong>${prestamo.PrestamoId}</strong></td>
                    <td>${fecha}</td>
                    <td class="text-end">$${montoDivisa}</td>
                    <td class="text-end">$${totalProductos.toFixed(2)}</td>
                    <td class="text-end">$${totalPagado.toFixed(2)}</td>
                    <td class="text-end"><strong>$${saldoPendiente.toFixed(2)}</strong></td>
                    <td class="text-center"><span class="badge bg-${estatusColor}">${estatusTexto}</span></td>
                </tr>
            `;
        });

        // Totales generales
        const totalGeneralDivisa = empleado.monto_total_prestamo || 0;
        const totalGeneralPagado = empleado.prestamos.reduce((sum, p) => sum + (p.total_pagado_divisa || 0), 0);
        const totalGeneralPendiente = totalGeneralDivisa - totalGeneralPagado;

        html += `
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">TOTALES:</td>
                            <td class="text-end">$${totalGeneralDivisa.toFixed(2)}</td>
                            <td class="text-end"></td>
                            <td class="text-end">$${totalGeneralPagado.toFixed(2)}</td>
                            <td class="text-end">$${totalGeneralPendiente.toFixed(2)}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        document.getElementById('modalDetallesPrestamosLabel').innerHTML = `<i class="fas fa-hand-holding-usd me-2"></i>Préstamos de ${nombreEmpleado}`;
        document.getElementById('modalDetallesPrestamosBody').innerHTML = html;
        
        const modal = new bootstrap.Modal(document.getElementById('modalDetallesPrestamos'));
        modal.show();
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
            customClass: { image: 'img-fluid rounded' }
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

    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }

    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
</style>
@endsection