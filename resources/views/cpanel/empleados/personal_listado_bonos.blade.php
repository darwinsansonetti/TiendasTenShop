@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Empleados')

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
                     style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                  <i class="bi bi-gift text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Bonos</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">Registro de bonos por empleado</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bonos</li>
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
                            <i class="fas fa-users me-2"></i>Listado de Empleados
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
                                <th width="80" class="text-center">Foto</th>
                                <th width="200" class="sortable" data-col="nombre">Empleado <span class="sort-icon">↕️</span></th>
                                <th width="150" class="sortable" data-col="sucursal">Sucursal <span class="sort-icon">↕️</span></th>
                                <th width="180" class="text-center sortable" data-col="ultimo_bono">Último Bono <span class="sort-icon">↕️</span></th>
                                <th width="100" class="text-center">Asignar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empleados as $index => $empleado)
                                @php
                                    // 🔧 CORREGIDO: usar las propiedades correctas
                                    $id = $empleado->id ?? '';
                                    $nombre = $empleado->nombre_completo ?? 'N/A';
                                    $email = $empleado->email ?? '';
                                    $rol = $empleado->rol_nombre ?? 'N/A';
                                    $sucursalNombre = $empleado->sucursal_nombre ?? 'N/A';
                                    $fotoPerfil = $empleado->foto_perfil ?? '';
                                    $fechaIngreso = $empleado->fecha_creacion ?? null;
                                    $origen = $empleado->origen ?? 'Usuario';
                                    $vendedorId = $empleado->vendedor_id ?? 'N/A';
                                    
                                    // Información del último bono
                                    $ultimoBonoFecha = $empleado->ultimo_bono_fecha ?? null;
                                    $ultimoBonoMontoDivisa = $empleado->ultimo_bono_monto_divisa ?? null;
                                    $ultimoBonoMontoBs = $empleado->ultimo_bono_monto_bs ?? null;
                                    $ultimoBonoTipo = $empleado->ultimo_bono_tipo ?? null;
                                    $ultimoBonoPagado = $empleado->ultimo_bono_pagado ?? null;
                                    
                                    // Determinar estado del bono
                                    $bonoEstatus = 'Sin bono';
                                    $bonoEstatusColor = 'secondary';
                                    if ($ultimoBonoFecha) {
                                        if ($ultimoBonoPagado == 0) {
                                            $bonoEstatus = 'Pendiente';
                                            $bonoEstatusColor = 'warning';
                                        } else {
                                            $bonoEstatus = 'Pagado';
                                            $bonoEstatusColor = 'success';
                                        }
                                    }
                                    
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
                                            loading="lazy"  
                                            alt="{{ $nombre }}"
                                            class="rounded-circle border border-success img-zoomable" 
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: zoom-in;"
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
                                        @if($origen == 'Usuario')
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

                                    <!-- Último Bono -->
                                    <td class="text-center" data-order="{{ $ultimoBonoFecha ? strtotime($ultimoBonoFecha) : 0 }}">
                                        @if($ultimoBonoFecha)
                                            @php
                                                $fechaBono = \Carbon\Carbon::parse($ultimoBonoFecha);
                                                $bonoEstatusColor = $ultimoBonoPagado == 0 ? 'warning' : 'success';
                                            @endphp

                                            <div class="card bg-light p-1 m-0" style="border-radius: 8px;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="text-start ps-2">
                                                        <small class="text-muted">Último bono</small>
                                                        <div class="fw-bold">{{ number_format($ultimoBonoMontoDivisa, 2) }} USD</div>
                                                    </div>
                                                    <div class="text-end pe-2">
                                                        <small class="text-muted">{{ $fechaBono->format('d/m/Y') }}</small>
                                                        <div>
                                                            <span class="badge bg-{{ $bonoEstatusColor }}">{{ $bonoEstatus }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-gift me-1"></i>
                                                Sin bonos asignados
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Acción -->
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            
                                            <!-- Botón Asignar Bono -->
                                            <a href="{{ route('cpanel.empleados.bonos.asignar', [
                                                'tipo' => $origen == 'AspNetUser' ? 'sistema' : 'temporal',
                                                'id' => $id
                                            ]) }}"
                                            class="btn btn-sm btn-outline-success"
                                            title="Asignar bono"
                                            data-bs-toggle="tooltip">
                                                <i class="bi bi-cash-stack"></i>
                                            </a>
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
                            <i class="fas fa-desktop me-1"></i>
                            Sistema: {{ $empleados->where('origen', 'AspNetUser')->count() }}
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Temporales: {{ $empleados->where('origen', 'Usuario')->count() }}
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

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
                    // Buscar en la columna de empleado (índice 1 o 2, ajusta según tu tabla)
                    // Por lo general el nombre del empleado está en la segunda columna
                    const celdaEmpleado = fila.children[1]; // Ajusta el índice según tu estructura
                    
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
                
                // Mostrar mensaje si no hay resultados
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
            
            // Evento de búsqueda mientras escribe
            buscadorEmpleados.addEventListener('input', filtrarTablaEmpleados);
            
            // Botón limpiar
            if (limpiarBtnEmpleados) {
                limpiarBtnEmpleados.addEventListener('click', function() {
                    buscadorEmpleados.value = '';
                    filtrarTablaEmpleados();
                    buscadorEmpleados.focus();
                });
            }
            
            // Tecla ESC para limpiar
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
                    
                    // Resetear íconos
                    document.querySelectorAll('.sort-icon').forEach(icon => {
                        icon.innerHTML = '↕️';
                    });
                    
                    // Cambiar dirección si es la misma columna
                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }
                    
                    // Actualizar ícono
                    const icono = th.querySelector('.sort-icon');
                    if (icono) {
                        icono.innerHTML = ordenAscendente ? '⬆️' : '⬇️';
                    } else {
                        // Si no hay icono, agregar clases visuales
                        ths.forEach(t => t.classList.remove('sort-asc', 'sort-desc'));
                        th.classList.add(ordenAscendente ? 'sort-asc' : 'sort-desc');
                    }
                    
                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                // Obtener solo las filas visibles (no ocultas por el buscador)
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"]):not(.no-results-message)'));
                
                filas.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];
                    
                    if (!tdA || !tdB) return 0;

                    // Usar data-order si existe
                    const valorA = tdA.dataset.order || extraerValorCelda(tdA);
                    const valorB = tdB.dataset.order || extraerValorCelda(tdB);

                    // Detectar si es número
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

                // Guardar el mensaje de "no resultados" si existe
                const mensajeNoResultados = document.getElementById('mensajeNoResultadosEmpleados');
                
                // Limpiar tbody
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                
                // Agregar filas ordenadas
                filas.forEach(fila => tbody.appendChild(fila));
                
                // Reagregar el mensaje de no resultados si existía
                if (mensajeNoResultados) {
                    tbody.appendChild(mensajeNoResultados);
                }
            }

            function extraerValorCelda(td) {
                // Para badges
                const badge = td.querySelector('.badge');
                if (badge) {
                    return badge.textContent.trim().replace(/[$,]/g, '');
                }
                
                // Para texto con strong
                const strong = td.querySelector('strong');
                if (strong) {
                    return strong.textContent.trim();
                }
                
                return td.textContent.trim().replace(/[$,]/g, '');
            }
        })();
    });

    // ============================================
    // EXPORTAR A EXCEL
    // ============================================
    function exportarExcelEmpleados() {
        const tabla = document.getElementById('tablaEmpleados');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // Encabezados
        const headers = ['Empleado', 'Email', 'Cargo', 'Sucursal', 'Ingreso'];
        datos.push(headers);

        // Filas
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Empleado y Email (columna 1)
            const empleadoCell = celdas[1];
            const nombreEmpleado = empleadoCell.querySelector('strong')?.textContent.trim() || '';
            const emailEmpleado = empleadoCell.querySelector('small')?.textContent.replace('@', '').trim() || '';
            
            // Cargo (columna 2)
            const cargoCell = celdas[2];
            const cargo = cargoCell.querySelector('.badge')?.textContent.trim() || '';
            
            // Sucursal (columna 3)
            const sucursalCell = celdas[3];
            const sucursal = sucursalCell.querySelector('.badge')?.textContent.trim() || '';
            
            // Ingreso (columna 4)
            const ingresoCell = celdas[4];
            const ingreso = ingresoCell.querySelector('.badge')?.textContent.trim() || 'N/A';
            
            datos.push([
                nombreEmpleado,
                emailEmpleado,
                cargo,
                sucursal,
                ingreso
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
        ws['!cols'] = maxColLengths.map(l => ({ wch: Math.min(l, 50) }));

        XLSX.utils.book_append_sheet(wb, ws, 'Empleados');
        
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Empleados_${fecha}.xlsx`);
    }

    // ============================================
    // EXPORTAR A PDF
    // ============================================
    function pdfTablaEmpleados() {
        const tabla = document.getElementById('tablaEmpleados');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        // Título
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Listado de Empleados', 14, 15);
        
        // Fecha
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, 14, 22);

        // Preparar datos
        const headers = [['Empleado', 'Email', 'Cargo', 'Sucursal', 'Ingreso']];
        const datos = [];

        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Empleado y Email
            const empleadoCell = celdas[1];
            const nombreEmpleado = empleadoCell.querySelector('strong')?.textContent.trim() || '';
            const emailEmpleado = empleadoCell.querySelector('small')?.textContent.replace('@', '').trim() || '';
            
            // Cargo
            const cargoCell = celdas[2];
            const cargo = cargoCell.querySelector('.badge')?.textContent.trim() || '';
            
            // Sucursal
            const sucursalCell = celdas[3];
            const sucursal = sucursalCell.querySelector('.badge')?.textContent.trim() || '';
            
            // Ingreso
            const ingresoCell = celdas[4];
            const ingreso = ingresoCell.querySelector('.badge')?.textContent.trim() || 'N/A';
            
            datos.push([
                nombreEmpleado,
                emailEmpleado,
                cargo,
                sucursal,
                ingreso
            ]);
        });

        // Generar PDF
        doc.autoTable({
            head: headers,
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
                cellPadding: 3
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                0: { cellWidth: 60 },
                1: { cellWidth: 70 },
                2: { cellWidth: 40, halign: 'center' },
                3: { cellWidth: 50 },
                4: { cellWidth: 35, halign: 'center' }
            },
            margin: { left: 14, right: 14 }
        });

        // Total de empleados
        const finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(`Total Empleados: ${datos.length}`, 14, finalY);

        doc.save(`Empleados_${new Date().toISOString().split('T')[0]}.pdf`);
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