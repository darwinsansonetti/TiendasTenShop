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
        
        <div class="card card-primary card-outline mb-4">
            <div class="card-body">
                <form action="{{ route('cpanel.empleados.vendedores') }}" method="GET" id="filtroForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio
                            </label>
                            <input type="date" 
                                class="form-control" 
                                id="fecha_inicio" 
                                name="fecha_inicio"
                                value="{{ request('fecha_inicio', $fechaInicio ? $fechaInicio->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Fin
                            </label>
                            <input type="date" 
                                class="form-control" 
                                id="fecha_fin" 
                                name="fecha_fin"
                                value="{{ request('fecha_fin', $fechaFin ? $fechaFin->format('Y-m-d') : now()->format('Y-m-d')) }}">
                        </div>
                        
                        <!-- 🔴 NUEVO: Selector de Estatus -->
                        <div class="col-md-2">
                            <label for="estatus" class="form-label">
                                <i class="fas fa-user-check me-1"></i>Estatus
                            </label>
                            <select name="estatus" class="form-select" id="estatus">
                                <option value="">Todos</option>
                                <option value="1" {{ request('estatus') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('estatus') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('cpanel.empleados.vendedores') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-undo me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($vendedores && $vendedores->count() > 0)
            <!-- Card de tabla -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <!-- Título y Buscador (Izquierda) -->
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Listado de Vendedores
                            </h3>
                            
                            <!-- Buscador -->
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                    id="buscadorVendedores" 
                                    class="form-control" 
                                    placeholder="Buscar vendedor..."
                                    autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="limpiarBuscadorVendedores">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Botones (Derecha) -->
                        <div class="col-md-6 text-end">
                            <div class="d-flex gap-2 justify-content-end">
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
                                    <th width="100" class="text-center">Estatus</th>
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

                                        <!-- Estatus -->
                                        <td class="text-center">
                                            @if($vendedor->EsActivo == 1)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
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
                                                
                                                <!-- Botón Crear/Reactivar en Identity -->
                                                @if(isset($vendedor->mostrar_boton_crear) && $vendedor->mostrar_boton_crear)
                                                    @if($vendedor->existe_en_identity == 0)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="crearUsuarioIdentity('{{ $id }}', '{{ addslashes($nombre) }}', 'crear')"
                                                                title="Crear usuario en el sistema Identity"
                                                                data-bs-toggle="tooltip">
                                                            <i class="bi bi-person-plus"></i>
                                                        </button>
                                                    @elseif($vendedor->identity_activo == 0)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                onclick="crearUsuarioIdentity('{{ $id }}', '{{ addslashes($nombre) }}', 'reactivar')"
                                                                title="Reactivar usuario en Identity"
                                                                data-bs-toggle="tooltip">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    @endif
                                                @endif
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

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Validación de fechas
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        
        if (fechaInicio && fechaFin) {
            fechaInicio.addEventListener('change', function() {
                if (this.value > fechaFin.value) {
                    fechaFin.value = this.value;
                }
            });
            
            fechaFin.addEventListener('change', function() {
                if (this.value < fechaInicio.value) {
                    fechaInicio.value = this.value;
                }
            });
        }

        // ==========================
        // BUSCADOR DE VENDEDORES
        // ==========================
        const buscadorVendedores = document.getElementById('buscadorVendedores');
        const tablaVendedores = document.getElementById('tablaVendedores');
        const limpiarBtnVendedores = document.getElementById('limpiarBuscadorVendedores');
        
        if (buscadorVendedores && tablaVendedores) {
            function filtrarTablaVendedores() {
                const textoBusqueda = buscadorVendedores.value.toLowerCase().trim();
                const tbody = tablaVendedores.querySelector('tbody');
                const filas = tbody.querySelectorAll('tr:not(.no-results-message)');
                let filasVisibles = 0;
                
                filas.forEach(fila => {
                    // Buscar en la columna de vendedor (índice 1)
                    const celdaVendedor = fila.children[1];
                    
                    if (celdaVendedor) {
                        const textoVendedor = celdaVendedor.textContent.toLowerCase();
                        
                        if (textoBusqueda === '' || textoVendedor.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });
                
                // Mostrar mensaje si no hay resultados
                let mensajeNoResultados = document.getElementById('mensajeNoResultadosVendedores');
                
                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultadosVendedores';
                        mensajeNoResultados.className = 'no-results-message';
                        const colspan = tablaVendedores.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="fas fa-search me-2"></i>
                                No se encontraron vendedores con el nombre "${buscadorVendedores.value}"
                            </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }
            
            // Evento de búsqueda mientras escribe
            buscadorVendedores.addEventListener('input', filtrarTablaVendedores);
            
            // Botón limpiar
            if (limpiarBtnVendedores) {
                limpiarBtnVendedores.addEventListener('click', function() {
                    buscadorVendedores.value = '';
                    filtrarTablaVendedores();
                    buscadorVendedores.focus();
                });
            }
            
            // Tecla ESC para limpiar
            buscadorVendedores.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    buscadorVendedores.value = '';
                    filtrarTablaVendedores();
                }
            });
        }

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
        
        // ==========================
        // ORDENAR TABLA DE VENDEDORES (si existe)
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaVendedores');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            if (ths.length > 0) {
                ths.forEach(th => {
                    th.style.cursor = 'pointer';
                    
                    th.addEventListener('click', () => {
                        const colIndex = Array.from(th.parentNode.children).indexOf(th);
                        
                        // Cambiar dirección si es la misma columna
                        if (columnaActual === colIndex) {
                            ordenAscendente = !ordenAscendente;
                        } else {
                            ordenAscendente = true;
                            columnaActual = colIndex;
                        }
                        
                        // Eliminar clases de otros th
                        ths.forEach(t => t.classList.remove('sort-asc', 'sort-desc'));
                        // Agregar clase al actual
                        th.classList.add(ordenAscendente ? 'sort-asc' : 'sort-desc');
                        
                        ordenarTablaVendedores(tabla, colIndex, ordenAscendente);
                    });
                });
            }

            function ordenarTablaVendedores(tabla, index, asc = true) {
                // Obtener solo las filas visibles (no ocultas por el buscador)
                const tbody = tabla.querySelector('tbody');
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"]):not(.no-results-message)'));
                
                filas.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];
                    
                    if (!tdA || !tdB) return 0;

                    // Usar data-order si existe
                    const valorA = tdA.dataset.order || extraerValorCeldaVendedores(tdA);
                    const valorB = tdB.dataset.order || extraerValorCeldaVendedores(tdB);

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
                const mensajeNoResultados = document.getElementById('mensajeNoResultadosVendedores');
                
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

            function extraerValorCeldaVendedores(td) {
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
                
                // Para texto con span
                const span = td.querySelector('span');
                if (span && !span.querySelector('i')) {
                    return span.textContent.trim();
                }
                
                return td.textContent.trim().replace(/[$,]/g, '');
            }
        })();
    });

    // ==========================
    // EXPORTAR TABLA DE VENDEDORES A EXCEL
    // ==========================
    function exportarExcelVendedores() {
        const tabla = document.getElementById('tablaVendedores');
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        // Encabezados - SOLO las columnas que queremos
        const headers = ['Vendedor', 'Sucursal', 'Dirección', 'Ingreso', 'Estatus'];
        datos.push(headers);

        // Filas
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Extraer Vendedor (columna 1 - nombre + código)
            const vendedorCell = celdas[1];
            const nombreVendedor = vendedorCell.querySelector('strong')?.textContent.trim() || '';
            const codigoVendedor = vendedorCell.querySelector('small')?.textContent.trim() || '';
            const vendedorCompleto = `${nombreVendedor} ${codigoVendedor}`;
            
            // Extraer Sucursal (columna 2)
            const sucursalCell = celdas[2];
            const sucursalTexto = sucursalCell.querySelector('.badge')?.textContent.trim() || 
                                sucursalCell.textContent.trim();
            
            // Extraer Dirección (columna 3)
            const direccionCell = celdas[3];
            const direccionTexto = direccionCell.textContent.trim();
            
            // Extraer Ingreso (columna 4)
            const ingresoCell = celdas[4];
            const ingresoTexto = ingresoCell.querySelector('.badge')?.textContent.trim() || 
                                ingresoCell.textContent.trim();
            
            // Extraer Estatus (columna 5)
            const estatusCell = celdas[5];
            const estatusTexto = estatusCell.querySelector('.badge')?.textContent.trim() || 
                                estatusCell.textContent.trim();
            
            // Agregar fila SOLO con las columnas seleccionadas
            datos.push([
                vendedorCompleto,
                sucursalTexto,
                direccionTexto,
                ingresoTexto,
                estatusTexto
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

        XLSX.utils.book_append_sheet(wb, ws, 'Vendedores');
        
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Vendedores_${fecha}.xlsx`);
    }

    // ==========================
    // EXPORTAR TABLA DE VENDEDORES A PDF
    // ==========================
    function pdfTablaVendedores() {
        const tabla = document.getElementById('tablaVendedores');
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
        doc.text('Listado de Vendedores', 14, 15);
        
        // Subtítulo con fecha actual
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Fecha: ${fechaActual}`, 14, 22);

        // Preparar datos para la tabla
        const headers = [['Vendedor', 'Sucursal', 'Dirección', 'Ingreso', 'Estatus']];
        const datos = [];

        // Variables para totales
        let totalActivos = 0;
        let totalInactivos = 0;

        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            
            // Vendedor (columna 1)
            const vendedorCell = celdas[1];
            const nombreVendedor = vendedorCell.querySelector('strong')?.textContent.trim() || '';
            const codigoVendedor = vendedorCell.querySelector('small')?.textContent.trim() || '';
            
            // Sucursal (columna 2)
            const sucursalCell = celdas[2];
            const sucursalTexto = sucursalCell.querySelector('.badge')?.textContent.trim() || 
                                sucursalCell.textContent.trim();
            
            // Dirección (columna 3)
            const direccionCell = celdas[3];
            const direccionTexto = direccionCell.textContent.trim();
            
            // Ingreso (columna 4)
            const ingresoCell = celdas[4];
            const ingresoTexto = ingresoCell.querySelector('.badge')?.textContent.trim() || 
                                ingresoCell.textContent.trim();
            
            // Estatus (columna 5)
            const estatusCell = celdas[5];
            const estatusTexto = estatusCell.querySelector('.badge')?.textContent.trim() || 
                                estatusCell.textContent.trim();
            
            // Contar activos/inactivos
            if (estatusTexto === 'Activo') {
                totalActivos++;
            } else if (estatusTexto === 'Inactivo') {
                totalInactivos++;
            }
            
            // Agregar fila
            datos.push([
                `${nombreVendedor}\n${codigoVendedor}`,
                sucursalTexto,
                direccionTexto,
                ingresoTexto,
                estatusTexto
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
                0: { cellWidth: 70 },
                1: { cellWidth: 50 },
                2: { cellWidth: 80 },
                3: { cellWidth: 35, halign: 'center' },
                4: { cellWidth: 30, halign: 'center' }
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
        const totalVendedores = datos.length;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0);
        
        doc.text('RESUMEN:', 14, finalY);
        doc.setFont('helvetica', 'normal');
        doc.text(`Total Vendedores: ${totalVendedores}`, 14, finalY + 6);
        doc.text(`Activos: ${totalActivos}`, 14, finalY + 12);
        doc.text(`Inactivos: ${totalInactivos}`, 14, finalY + 18);
        
        // Fecha de generación
        const fechaGeneracion = new Date().toLocaleString('es-VE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        doc.setFontSize(8);
        doc.setTextColor(100);
        doc.text(`Generado: ${fechaGeneracion}`, 14, doc.internal.pageSize.height - 10);

        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Vendedores_${fecha}.pdf`);
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

    function crearUsuarioIdentity(id, nombre, accion) {
        let titulo = accion === 'reactivar' ? 'Reactivar empleado' : 'Crear empleado interno';
        let texto = accion === 'reactivar' 
            ? `¿Desea reactivar al vendedor <strong>${nombre}</strong> en el sistema?<br>
            <small class="text-muted">El usuario existe pero está inactivo. Se reactivará y actualizarán sus datos.</small>`
            : `¿Desea crear el vendedor <strong>${nombre}</strong> como empleado interno?<br>
            <small class="text-muted">Se creará un registro en AspNetUsers con rol VENDEDORES.</small>`;
        
        Swal.fire({
            title: titulo,
            html: texto,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: accion === 'reactivar' ? '#ffc107' : '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: accion === 'reactivar' ? 'Sí, reactivar' : 'Sí, crear',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: accion === 'reactivar' ? 'Reactivando...' : 'Creando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch('{{ route("cpanel.vendedores.crear-identity") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, accion: accion })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Completado!',
                            text: data.message,
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
                    Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
                });
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