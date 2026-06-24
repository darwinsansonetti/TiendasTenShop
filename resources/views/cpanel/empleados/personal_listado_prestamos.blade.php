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
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                  <i class="bi bi-bank2 text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Préstamos de Empleados</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Control de préstamos y adelantos</p>
                </div>
              </div>
            </div>
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

                                    <!-- Acción -->
                                    <td class="text-center">
                                        <div class="btn-group" role="group">                                            
                                            <!-- Botón Solicitar Préstamo (siempre visible) -->
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="window.location.href='{{ route("cpanel.empleados.prestamos.solicitar.form", $id) }}'"
                                                title="Solicitar nuevo préstamo"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>
                                            
                                            <!-- Botón Pagar Préstamo (solo si tiene préstamos activos) -->
                                            @if($totalPrestamos > 0 && $totalPendiente > 0)
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="pagarPrestamos('{{ $id }}', '{{ addslashes($nombre) }}')"
                                                    title="Registrar pago de préstamos"
                                                    data-bs-toggle="tooltip">
                                                    <i class="bi bi-cash-stack"></i>
                                                </button>
                                            @endif

                                            <!-- Botón Ver Detalles -->
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info"
                                                onclick="window.location.href='{{ route("cpanel.empleados.prestamos.detalle", $id) }}'"
                                                title="Ver detalles de préstamos"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
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
    let empleadosData = @json($empleados);

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

    function pagarPrestamos(usuarioId, nombreEmpleado) {
        const url = '{{ route("cpanel.empleados.prestamos.bonos_disponibles", ":usuarioId") }}';
        const finalUrl = url.replace(':usuarioId', usuarioId);

        const empleadoActual = empleadosData.find(e => e.id === usuarioId);
        
        fetch(finalUrl)
            .then(response => response.json())
            .then(data => {
                let tieneBonos = data.success && data.data.total_bonos_disponibles > 0;
                let tieneLiberalidad = data.data.liberalidad && data.data.liberalidad.tiene_liberalidad;
                let liberalidadDisponible = data.data.liberalidad?.disponible || 0;
                let montoLiberalidad = data.data.liberalidad?.monto || 0;
                let montoDescuento = data.data.liberalidad?.monto_descuento || 0;
                
                let bonosHtml = '';
                let liberalidadHtml = '';
                
                if (tieneBonos) {
                    bonosHtml = `
                        <div class="alert alert-success">
                            <strong>🎁 Bonos disponibles:</strong> $${data.data.total_bonos_disponibles.toFixed(2)} USD
                            <br>
                            <small>Seleccione "Transferencia (Bono)" para usar sus bonos</small>
                        </div>
                    `;
                }
                
                if (tieneLiberalidad) {
                    liberalidadHtml = `
                        <div class="alert alert-info">
                            <strong>💰 Liberalidad disponible:</strong> $${liberalidadDisponible.toFixed(2)} USD
                            <br>
                            <small>Seleccione "Transferencia (Liberalidad)" para usar su liberalidad</small>
                        </div>
                    `;
                }
                
                Swal.fire({
                    title: `Registrar Pago - ${nombreEmpleado}`,
                    html: `
                        <div class="text-start">
                            ${bonosHtml}
                            ${liberalidadHtml}
                            
                            <div class="mb-3">
                                <label class="form-label">Fecha del pago *</label>
                                <input type="date" id="fechaPago" class="form-control" value="">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción *</label>
                                <input type="text" id="descripcion" class="form-control" placeholder="Escriba la descripción...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Forma de pago *</label>
                                <select id="formaPago" class="form-select">
                                    <option value="">Seleccione un valor</option>
                                    <option value="0">💵 Efectivo</option>
                                    <option value="2">🏦 Depósito</option>
                                    <option value="3">📱 Transferencia</option>
                                    ${tieneBonos ? '<option value="bono">🎁 Transferencia (Bono)</option>' : ''}
                                    ${tieneLiberalidad ? '<option value="liberalidad">💰 Transferencia (Liberalidad)</option>' : ''}
                                </select>
                            </div>
                            
                            <div id="montoContainer">
                                <div class="mb-3">
                                    <label class="form-label">Monto a pagar (USD)</label>
                                    <input type="number" id="montoPago" class="form-control" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            
                            <div id="bonosContainer" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Seleccionar bono</label>
                                    <select id="bonoSelect" class="form-select">
                                        ${data.data.bonos.map(bono => 
                                            `<option value="${bono.ID}" data-monto="${bono.MontoDivisa}">
                                                Bono #${bono.ID} - $${bono.MontoDivisa} - ${bono.Motivo || 'Sin motivo'}
                                            </option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Monto a pagar con bono (USD)</label>
                                    <input type="number" id="montoBono" class="form-control" step="0.01" 
                                        max="${data.data.total_bonos_disponibles}" placeholder="0.00">
                                    <small class="text-muted">Máximo disponible: $${data.data.total_bonos_disponibles.toFixed(2)}</small>
                                </div>
                            </div>
                            
                            <div id="liberalidadContainer" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Monto a pagar con liberalidad (USD)</label>
                                    <input type="number" id="montoLiberalidad" class="form-control" step="0.01" 
                                        max="${liberalidadDisponible}" placeholder="0.00">
                                    <small class="text-muted">Máximo disponible: $${liberalidadDisponible.toFixed(2)}</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Número de operación</label>
                                <input type="text" id="numeroOperacion" class="form-control" placeholder="Número de operación / referencia">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observación</label>
                                <textarea id="observacion" class="form-control" rows="2" 
                                        placeholder="Observación del pago..."></textarea>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Registrar Pago',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    didOpen: () => {
                        // Establecer fecha actual del cliente
                        const fechaInput = document.getElementById('fechaPago');
                        if (fechaInput) {
                            const hoy = new Date();
                            const año = hoy.getFullYear();
                            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                            const dia = String(hoy.getDate()).padStart(2, '0');
                            fechaInput.value = `${año}-${mes}-${dia}`;
                        }
                        
                        const formaPagoSelect = document.getElementById('formaPago');
                        const montoContainer = document.getElementById('montoContainer');
                        const bonosContainer = document.getElementById('bonosContainer');
                        const liberalidadContainer = document.getElementById('liberalidadContainer');
                        const montoBono = document.getElementById('montoBono');
                        const montoLiberalidadInput = document.getElementById('montoLiberalidad');
                        const bonoSelect = document.getElementById('bonoSelect');
                        
                        if (formaPagoSelect) {
                            formaPagoSelect.addEventListener('change', function() {
                                if (this.value === 'bono') {
                                    if (montoContainer) montoContainer.style.display = 'none';
                                    if (bonosContainer) bonosContainer.style.display = 'block';
                                    if (liberalidadContainer) liberalidadContainer.style.display = 'none';
                                } else if (this.value === 'liberalidad') {
                                    if (montoContainer) montoContainer.style.display = 'none';
                                    if (bonosContainer) bonosContainer.style.display = 'none';
                                    if (liberalidadContainer) liberalidadContainer.style.display = 'block';
                                } else {
                                    if (montoContainer) montoContainer.style.display = 'block';
                                    if (bonosContainer) bonosContainer.style.display = 'none';
                                    if (liberalidadContainer) liberalidadContainer.style.display = 'none';
                                }
                            });
                        }
                        
                        if (bonoSelect && bonoSelect.options && bonoSelect.options.length > 0) {
                            const updateMontoMax = () => {
                                const selectedIndex = bonoSelect.selectedIndex;
                                if (selectedIndex >= 0 && selectedIndex < bonoSelect.options.length) {
                                    const selectedOption = bonoSelect.options[selectedIndex];
                                    if (selectedOption && selectedOption.dataset && selectedOption.dataset.monto) {
                                        const montoDisponible = parseFloat(selectedOption.dataset.monto);
                                        if (montoBono && !isNaN(montoDisponible)) {
                                            montoBono.max = montoDisponible;
                                            montoBono.placeholder = `Máximo: $${montoDisponible.toFixed(2)}`;
                                        }
                                    }
                                }
                            };
                            
                            bonoSelect.addEventListener('change', updateMontoMax);
                            updateMontoMax();
                        }
                    },
                    preConfirm: () => {
                        const formaPago = document.getElementById('formaPago').value;
                        const fechaPago = document.getElementById('fechaPago').value;
                        const descripcion = document.getElementById('descripcion').value;
                        const observacion = document.getElementById('observacion').value;
                        const numeroOperacion = document.getElementById('numeroOperacion').value;
                        
                        if (!fechaPago) {
                            Swal.showValidationMessage('Debe seleccionar una fecha');
                            return false;
                        }
                        
                        if (!descripcion) {
                            Swal.showValidationMessage('Debe ingresar una descripción');
                            return false;
                        }
                        
                        if (!formaPago) {
                            Swal.showValidationMessage('Debe seleccionar una forma de pago');
                            return false;
                        }
                        
                        if (formaPago === 'bono') {
                            const bonoId = document.getElementById('bonoSelect').value;
                            const monto = document.getElementById('montoBono').value;
                            
                            if (!monto || parseFloat(monto) <= 0) {
                                Swal.showValidationMessage('Debe ingresar un monto válido');
                                return false;
                            }
                            
                            const bonoSeleccionado = data.data.bonos.find(b => b.ID == bonoId);
                            if (!bonoSeleccionado) {
                                Swal.showValidationMessage('Bono no encontrado');
                                return false;
                            }
                            
                            if (parseFloat(monto) > bonoSeleccionado.MontoDivisa) {
                                Swal.showValidationMessage(`El monto no puede exceder $${bonoSeleccionado.MontoDivisa.toFixed(2)}`);
                                return false;
                            }
                            
                            return {
                                tipo: 'bono',
                                usuarioId: usuarioId,
                                empleado: empleadoActual,
                                bono_id: parseInt(bonoId),
                                monto: parseFloat(monto),
                                fecha: fechaPago,
                                descripcion: descripcion,
                                observacion: observacion,
                                numero_operacion: numeroOperacion
                            };
                        } else if (formaPago === 'liberalidad') {
                            const monto = document.getElementById('montoLiberalidad').value;
                            
                            if (!monto || parseFloat(monto) <= 0) {
                                Swal.showValidationMessage('Debe ingresar un monto válido');
                                return false;
                            }
                            
                            if (parseFloat(monto) > liberalidadDisponible) {
                                Swal.showValidationMessage(`El monto no puede exceder $${liberalidadDisponible.toFixed(2)}`);
                                return false;
                            }
                            
                            return {
                                tipo: 'liberalidad',
                                usuarioId: usuarioId,
                                empleado: empleadoActual,
                                monto: parseFloat(monto),
                                fecha: fechaPago,
                                descripcion: descripcion,
                                observacion: observacion,
                                numero_operacion: numeroOperacion
                            };
                        } else {
                            const monto = document.getElementById('montoPago').value;
                            
                            if (!monto || parseFloat(monto) <= 0) {
                                Swal.showValidationMessage('Debe ingresar un monto válido');
                                return false;
                            }
                            
                            return {
                                tipo: 'normal',
                                usuarioId: usuarioId,
                                empleado: empleadoActual,
                                monto: parseFloat(monto),
                                formaPago: parseInt(formaPago),
                                fecha: fechaPago,
                                descripcion: descripcion,
                                observacion: observacion,
                                numero_operacion: numeroOperacion
                            };
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (result.value.tipo === 'bono') {
                            registrarPagoConBono(result.value);
                        } else if (result.value.tipo === 'liberalidad') {
                            registrarPagoConLiberalidad(result.value);
                        } else {
                            registrarPagoNormal(result.value);
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarModalPagoNormal(usuarioId, nombreEmpleado);
            });
    }

    function mostrarModalPagoNormal(usuarioId, nombreEmpleado) {
        const empleadoActual = empleadosData.find(e => e.id === usuarioId);
        
        Swal.fire({
            title: `Registrar Pago - ${nombreEmpleado}`,
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Fecha del pago *</label>
                        <input type="date" id="fechaPago" class="form-control" value="">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción *</label>
                        <input type="text" id="descripcion" class="form-control" placeholder="Escriba la descripción...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monto a pagar (USD) *</label>
                        <input type="number" id="montoPago" class="form-control" step="0.01" placeholder="0.00">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Forma de pago *</label>
                        <select id="formaPago" class="form-select">
                            <option value="0">Efectivo</option>
                            <option value="2">Depósito</option>
                            <option value="3">Transferencia</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Número de operación</label>
                        <input type="text" id="numeroOperacion" class="form-control" placeholder="Número de operación / referencia">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <textarea id="observacion" class="form-control" rows="2" 
                                placeholder="Observación del pago..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Pago',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            didOpen: () => {
                // Establecer fecha actual del cliente
                const fechaInput = document.getElementById('fechaPago');
                if (fechaInput) {
                    const hoy = new Date();
                    const año = hoy.getFullYear();
                    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                    const dia = String(hoy.getDate()).padStart(2, '0');
                    fechaInput.value = `${año}-${mes}-${dia}`;
                }
            },
            preConfirm: () => {
                const fecha = document.getElementById('fechaPago').value;
                const descripcion = document.getElementById('descripcion').value;
                const monto = document.getElementById('montoPago').value;
                const formaPago = document.getElementById('formaPago').value;
                const numeroOperacion = document.getElementById('numeroOperacion').value;
                const observacion = document.getElementById('observacion').value;
                
                if (!fecha) {
                    Swal.showValidationMessage('Debe seleccionar una fecha');
                    return false;
                }
                
                if (!descripcion) {
                    Swal.showValidationMessage('Debe ingresar una descripción');
                    return false;
                }
                
                if (!monto || parseFloat(monto) <= 0) {
                    Swal.showValidationMessage('Debe ingresar un monto válido');
                    return false;
                }
                
                if (!formaPago) {
                    Swal.showValidationMessage('Debe seleccionar una forma de pago');
                    return false;
                }
                
                return {
                    usuarioId: usuarioId,
                    empleado: empleadoActual,
                    monto: parseFloat(monto),
                    formaPago: parseInt(formaPago),
                    fecha: fecha,
                    descripcion: descripcion,
                    observacion: observacion,
                    numero_operacion: numeroOperacion
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                registrarPagoNormal(result.value);
            }
        });
    }

    function registrarPagoNormal(datos) {
        Swal.fire({
            title: 'Procesando pago...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("cpanel.empleados.prestamos.registrar_pago") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                usuarioId: datos.usuarioId,
                Fecha: datos.fecha,
                Descripcion: datos.descripcion,
                MontoDivisaAbonado: datos.monto,
                FormaDePago: datos.formaPago,
                Observacion: datos.observacion,
                NumeroOperacion: datos.numero_operacion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Preparar datos para el PDF
                const datosPago = {
                    tipo: 'normal',
                    fecha: datos.fecha,
                    formaPago: datos.formaPago,
                    numero_operacion: datos.numero_operacion,
                    monto: datos.monto,
                    descripcion: datos.descripcion,
                    observacion: datos.observacion
                };
                
                // Generar PDF del comprobante
                generarComprobantePagoPDF(datosPago, data.data.prestamos_afectados, datos.empleado);
                
                Swal.fire({
                    title: '¡Pago registrado!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un error al procesar el pago', 'error');
        });
    }

    function registrarPagoConBono(datos) {
        Swal.fire({
            title: 'Procesando pago con bono...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("cpanel.empleados.prestamos.registrar_pago_bono") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                usuarioId: datos.usuarioId,
                monto: datos.monto,
                bono_id: datos.bono_id,
                fecha: datos.fecha,
                descripcion: datos.descripcion,
                observacion: datos.observacion,
                numero_operacion: datos.numero_operacion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Preparar datos para el PDF
                const datosPago = {
                    tipo: 'bono',
                    fecha: datos.fecha,
                    formaPago: 3,
                    numero_operacion: datos.numero_operacion,
                    monto: datos.monto,
                    bono_id: datos.bono_id,
                    descripcion: datos.descripcion,
                    observacion: datos.observacion
                };
                
                // Generar PDF del comprobante
                generarComprobantePagoPDF(datosPago, data.data.prestamos_afectados, datos.empleado);
                
                Swal.fire({
                    title: '¡Pago registrado!',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                            <p class="mt-2">${data.message}</p>
                            <hr>
                            <small>Bono utilizado: #${datos.bono_id}</small>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un error al procesar el pago con bono', 'error');
        });
    }

    function registrarPagoConLiberalidad(datos) {
        Swal.fire({
            title: 'Procesando pago con liberalidad...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('{{ route("cpanel.empleados.prestamos.registrar_pago_liberalidad") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                usuarioId: datos.usuarioId,
                monto: datos.monto,
                fecha: datos.fecha,
                descripcion: datos.descripcion,
                observacion: datos.observacion,
                numero_operacion: datos.numero_operacion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Pago registrado!',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                            <p class="mt-2">${data.message}</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un error al procesar el pago con liberalidad', 'error');
        });
    }

    function generarComprobantePagoPDF(datosPago, prestamosAfectados, empleado) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 15;
        
        // Formatear la fecha correctamente
        let fechaPago = datosPago.fecha;
        let fechaFormateada = '';
        
        if (fechaPago) {
            if (fechaPago.includes('-')) {
                const partes = fechaPago.split('-');
                fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
            } else {
                const fechaObj = new Date(fechaPago);
                if (!isNaN(fechaObj.getTime())) {
                    fechaFormateada = fechaObj.toLocaleDateString('es-VE');
                } else {
                    fechaFormateada = new Date().toLocaleDateString('es-VE');
                }
            }
        } else {
            fechaFormateada = new Date().toLocaleDateString('es-VE');
        }
        
        // ============================================
        // ENCABEZADO CON LOGO
        // ============================================
        doc.setFillColor(41, 128, 185);
        doc.rect(0, 0, pageWidth, 45, 'F');
        
        doc.setFontSize(22);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text('TIENDAS TEN SHOP', pageWidth / 2, 20, { align: 'center' });
        
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.text('Comprobante de Pago de Préstamo', pageWidth / 2, 32, { align: 'center' });
        
        doc.setFontSize(9);
        doc.setTextColor(230, 230, 230);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, pageWidth / 2, 40, { align: 'center' });
        
        yPos = 55;
        
        // ============================================
        // INFORMACIÓN DEL EMPLEADO (VERSIÓN CORREGIDA)
        // ============================================
        doc.setFillColor(245, 245, 245);
        doc.roundedRect(14, yPos, pageWidth - 28, 65, 3, 3, 'F');

        doc.setFontSize(11);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('INFORMACIÓN DEL EMPLEADO', 20, yPos + 7);

        doc.setDrawColor(200, 200, 200);
        doc.line(20, yPos + 12, pageWidth - 20, yPos + 12);

        doc.setFontSize(9);
        doc.setTextColor(60, 60, 60);
        doc.setFont('helvetica', 'normal');

        // Fila 1: Nombre
        doc.text('Nombre completo:', 20, yPos + 22);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.nombre_completo || 'N/A', 70, yPos + 22);

        // Fila 2: Vendedor ID y Teléfono
        doc.setFont('helvetica', 'normal');
        doc.text('Vendedor ID:', 20, yPos + 32);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.vendedor_id || 'N/A', 70, yPos + 32);

        doc.setFont('helvetica', 'normal');
        doc.text('Teléfono:', pageWidth - 65, yPos + 32);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.telefono || 'N/A', pageWidth - 45, yPos + 32);

        // Fila 3: Sucursal
        doc.setFont('helvetica', 'normal');
        doc.text('Sucursal:', 20, yPos + 42);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.sucursal_nombre || 'N/A', 70, yPos + 42);

        // Fila 4: Email (en línea completa)
        doc.setFont('helvetica', 'normal');
        doc.text('Email:', 20, yPos + 52);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 0, 150);
        // Limitar email a un ancho máximo
        let emailTexto = empleado.email || 'N/A';
        if (emailTexto.length > 35) {
            emailTexto = emailTexto.substring(0, 32) + '...';
        }
        doc.text(emailTexto, 70, yPos + 52);

        // Fila 5: Fecha pago
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(60, 60, 60);
        doc.text('Fecha pago:', pageWidth - 65, yPos + 42);
        doc.setFont('helvetica', 'bold');
        doc.text(fechaFormateada, pageWidth - 45, yPos + 42);

        yPos += 75;
        
        // ============================================
        // INFORMACIÓN DEL PAGO
        // ============================================
        doc.setFillColor(41, 128, 185);
        doc.roundedRect(14, yPos, pageWidth - 28, 10, 3, 3, 'F');
        doc.setFontSize(10);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text('INFORMACIÓN DEL PAGO', pageWidth / 2, yPos + 7, { align: 'center' });
        
        yPos += 15;
        
        // Tarjeta de resumen de pago
        doc.setFillColor(240, 248, 255);
        doc.roundedRect(14, yPos, pageWidth - 28, 55, 3, 3, 'F');
        
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(60, 60, 60);
        
        let formaPagoTexto = '';
        let formaPagoColor = [60, 60, 60];
        if (datosPago.tipo === 'bono') {
            formaPagoTexto = 'Transferencia (Bono)';
            formaPagoColor = [40, 167, 69];
        } else {
            switch (datosPago.formaPago) {
                case 0: formaPagoTexto = 'Efectivo'; break;
                case 2: formaPagoTexto = 'Depósito'; break;
                case 3: formaPagoTexto = 'Transferencia'; break;
                default: formaPagoTexto = 'Desconocido';
            }
        }
        
        doc.text('Forma de pago:', 20, yPos + 10);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(formaPagoColor[0], formaPagoColor[1], formaPagoColor[2]);
        doc.text(formaPagoTexto, 65, yPos + 10);
        
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(60, 60, 60);
        doc.text('Monto pagado:', 20, yPos + 22);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(40, 167, 69);
        doc.text(`$${datosPago.monto.toFixed(2)} USD`, 65, yPos + 22);
        
        if (datosPago.numero_operacion) {
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('N° Operación:', 20, yPos + 34);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(datosPago.numero_operacion, 65, yPos + 34);
        }
        
        if (datosPago.tipo === 'bono') {
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('Bono utilizado:', pageWidth - 70, yPos + 10);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(`#${datosPago.bono_id}`, pageWidth - 45, yPos + 10);
        }
        
        yPos += 70;
        
        // ============================================
        // DETALLE DE PRÉSTAMOS PAGADOS
        // ============================================
        if (prestamosAfectados && prestamosAfectados.length > 0) {
            doc.setFillColor(41, 128, 185);
            doc.roundedRect(14, yPos, pageWidth - 28, 10, 3, 3, 'F');
            doc.setFontSize(10);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.text('DETALLE DE PRÉSTAMOS', pageWidth / 2, yPos + 7, { align: 'center' });
            
            yPos += 15;
            
            const headers = [['Préstamo ID', 'Monto Pagado (USD)', 'Saldo Anterior (USD)', 'Nuevo Saldo (USD)']];
            const body = prestamosAfectados.map(p => [
                p.PrestamoId.toString(),
                `$${p.montoPagado.toFixed(2)}`,
                `$${p.saldoAnterior.toFixed(2)}`,
                `$${p.nuevoSaldo.toFixed(2)}`
            ]);
            
            doc.autoTable({
                head: headers,
                body: body,
                startY: yPos,
                theme: 'grid',
                headStyles: { 
                    fillColor: [41, 128, 185], 
                    textColor: 255, 
                    fontSize: 9, 
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: { fontSize: 8, cellPadding: 4 },
                alternateRowStyles: { fillColor: [245, 245, 245] },
                columnStyles: {
                    0: { cellWidth: 35, halign: 'center' },
                    1: { cellWidth: 40, halign: 'right' },
                    2: { cellWidth: 45, halign: 'right' },
                    3: { cellWidth: 45, halign: 'right' }
                },
                margin: { left: 14, right: 14 }
            });
            
            yPos = doc.lastAutoTable.finalY + 10;
            
            // Resumen de totales
            const totalPagado = prestamosAfectados.reduce((sum, p) => sum + p.montoPagado, 0);
            const totalSaldoAnterior = prestamosAfectados.reduce((sum, p) => sum + p.saldoAnterior, 0);
            const totalNuevoSaldo = prestamosAfectados.reduce((sum, p) => sum + p.nuevoSaldo, 0);
            
            doc.setFillColor(240, 248, 255);
            doc.roundedRect(14, yPos, pageWidth - 28, 35, 3, 3, 'F');
            
            doc.setFontSize(9);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text('RESUMEN DEL PAGO', pageWidth / 2, yPos + 7, { align: 'center' });
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('Total pagado:', 20, yPos + 18);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(40, 167, 69);
            doc.text(`$${totalPagado.toFixed(2)} USD`, 65, yPos + 18);
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('Saldo anterior total:', 20, yPos + 28);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(`$${totalSaldoAnterior.toFixed(2)} USD`, 65, yPos + 28);
            
            doc.setFont('helvetica', 'normal');
            doc.text('Nuevo saldo total:', pageWidth - 70, yPos + 18);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(220, 53, 69);
            doc.text(`$${totalNuevoSaldo.toFixed(2)} USD`, pageWidth - 45, yPos + 18);
            
            yPos += 45;
        }
        
        // ============================================
        // OBSERVACIÓN
        // ============================================
        if (datosPago.observacion) {
            doc.setFillColor(255, 248, 225);
            doc.roundedRect(14, yPos, pageWidth - 28, 25, 3, 3, 'F');
            
            doc.setFontSize(9);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(200, 100, 0);
            doc.text('Observación:', 20, yPos + 7);
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            const observacionLines = doc.splitTextToSize(datosPago.observacion, pageWidth - 48);
            doc.text(observacionLines, 20, yPos + 15);
            
            yPos += 35;
        } else {
            yPos += 5;
        }
        
        // ============================================
        // FIRMAS
        // ============================================
        yPos += 10;
        
        doc.setDrawColor(150, 150, 150);
        doc.setLineWidth(0.5);
        
        doc.line(25, yPos, 85, yPos);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(100, 100, 100);
        doc.text('Firma del empleado', 55, yPos + 4, { align: 'center' });
        
        doc.line(pageWidth - 85, yPos, pageWidth - 25, yPos);
        doc.text('Firma del cajero', pageWidth - 55, yPos + 4, { align: 'center' });
        
        // ============================================
        // PIE DE PÁGINA
        // ============================================
        const añoActual = new Date().getFullYear();
        doc.setFillColor(41, 128, 185);
        doc.rect(0, doc.internal.pageSize.getHeight() - 20, pageWidth, 20, 'F');
        
        doc.setFontSize(7);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(255, 255, 255);
        doc.text('Este comprobante es un documento válido de pago', pageWidth / 2, doc.internal.pageSize.getHeight() - 12, { align: 'center' });
        doc.text(`© ${añoActual} TiendasTenShop - Todos los derechos reservados`, pageWidth / 2, doc.internal.pageSize.getHeight() - 6, { align: 'center' });
        
        // ============================================
        // GUARDAR PDF
        // ============================================
        const nombreArchivo = `Comprobante_Pago_${empleado.nombre_completo.replace(/\s/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(nombreArchivo);
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