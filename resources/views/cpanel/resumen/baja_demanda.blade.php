@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Productos con Baja Demanda')

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
      <div class="col-sm-6"><h3 class="mb-0">Productos con Baja Demanda</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Productos con Baja Demanda</li>
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
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filtros de búsqueda
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cpanel.baja.ventas') }}" method="GET" id="filtroForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <!-- Campo oculto fecha_inicio -->
                        <input type="hidden" 
                            id="fecha_inicio" 
                            name="fecha_inicio"
                            value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}">
                        
                        <!-- Campo Fecha Fin visible -->
                        <div class="col-md-8">
                            <label for="fecha_fin" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>Seleccionar Fecha
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar-day text-muted"></i>
                                </span>
                                <input type="date" 
                                    class="form-control border-start-0 ps-0" 
                                    id="fecha_fin" 
                                    name="fecha_fin"
                                    value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                                    style="border-left: none;"
                                    required>
                            </div>
                        </div>
                        
                        <!-- Botón Buscar -->
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        @if($indices)
        <!-- Card de tabla -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center g-2">

                    <!-- Filtro de valoración -->
                    <div class="col-md-3">
                        <select id="filtroValoracion" 
                                class="form-select form-select-sm"
                                onchange="filtrarTabla()">
                            <option value="">⭐ Todas</option>
                            <option value="5">★★★★★ Muy alta</option>
                            <option value="4">★★★★☆ Alta</option>
                            <option value="3">★★★☆☆ Media</option>
                            <option value="2">★★☆☆☆ Baja</option>
                            <option value="1">★☆☆☆☆ Muy baja</option>
                        </select>
                    </div>

                    <!-- Buscador -->
                    <div class="col-md-3">
                        <input type="text" 
                            class="form-control form-control-sm"
                            id="buscarProducto"
                            placeholder="Buscar código o descripción..."
                            onkeyup="filtrarTabla()">
                    </div>

                    <!-- Botones -->
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="ingresarPorcentaje({{ session('sucursal_id', 0) }}, '{{ session('sucursal_nombre') }}')">
                                <i class="fas fa-print me-1"></i>Porcentaje
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="descargarSeleccionadas({{ session('sucursal_id', 0) }}, '{{ session('sucursal_nombre') }}')">
                                <i class="fas fa-print me-1"></i>Seleccionadas
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="cambiosPVP({{ session('sucursal_id', 0) }}, '{{ session('sucursal_nombre') }}')">
                                <i class="fas fa-print me-1"></i>Descargar PVP
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="pdfTablaConImagenes()">
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
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="checkAllRotacion">
                                </th>
                                <th width="80" class="text-center">Imagen</th>
                                <th width="100">Código</th>
                                <th>Descripción</th>
                                <th width="100" class="text-center">Existencia</th>
                                <th width="120" class="text-center">Ventas</th>
                                <th width="100" class="text-center">Costo</th>
                                <th width="100" class="text-center">PVP</th>
                                <th width="120" class="text-center">F. Creación</th>
                                <th width="120" class="text-center">Última Venta</th>
                                <th width="80" class="text-center">Acciones</th> <!-- Columna movida aquí -->
                            </tr>
                        </thead>
                        @php
                            $totalInventarioUSD = 0;
                        @endphp
                        <tbody>
                            @foreach($indices->detalles as $detalle)
                            @php
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle->producto['url_foto'] ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                
                                // Determinar color del índice
                                $colorIndice = 'text-dark';
                                if ($detalle->indice_rotacion >= $indices->indice_promedio * 1.5) {
                                    $colorIndice = 'text-success fw-bold';
                                } elseif ($detalle->indice_rotacion < $indices->indice_promedio * 0.5) {
                                    $colorIndice = 'text-danger';
                                }
                                
                                // Determinar color de existencia
                                $colorExistencia = 'text-dark';
                                $existencia = $detalle->producto['existencia'] ?? 0;
                                if ($existencia == 0) {
                                    $colorExistencia = 'text-danger fw-bold';
                                } elseif ($existencia <= 5) {
                                    $colorExistencia = 'text-warning';
                                }
                                
                                // Determinar si mostrar alertas
                                $tieneAlertas = false;
                                $alertas = [];
                                
                                if ($existencia == 0) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Sin existencias';
                                } elseif ($existencia <= 5) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Stock bajo';
                                }
                                
                                if ($detalle->indice_rotacion < $indices->indice_promedio * 0.5) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Baja rotación';
                                }
                                
                                // Fecha última venta (si es muy antigua)
                                $fechaUltimaVenta = $detalle->producto['fecha_ultima_venta'] ?? null;
                                if ($fechaUltimaVenta && $fechaUltimaVenta->diffInDays(now()) > 90) {
                                    $tieneAlertas = true;
                                    $alertas[] = 'Sin ventas recientes';
                                }

                                $mayorIndice = $indices->mayor_indice ?? 0;
                                $porcentaje = $mayorIndice > 0
                                    ? ($detalle->indice_rotacion / $mayorIndice) * 100
                                    : 0;

                                if ($porcentaje >= 90) {
                                    $estrellas = 5;
                                } elseif ($porcentaje >= 70) {
                                    $estrellas = 4;
                                } elseif ($porcentaje >= 50) {
                                    $estrellas = 3;
                                } elseif ($porcentaje >= 30) {
                                    $estrellas = 2;
                                } else {
                                    $estrellas = 1;
                                }

                                // Valores base
                                $costo = $detalle->producto['costo_divisa'] ?? 0;

                                $pvpActual = ($detalle->producto['nuevo_pvp'] ?? 0) > 0
                                    ? ($detalle->producto['nuevo_pvp'] ?? 0)
                                    : ($detalle->producto['pvp_divisa'] ?? 0);

                                // Utilidad
                                $utilidad = ($costo > 0 && $pvpActual > 0)
                                    ? round($pvpActual - $costo, 2)
                                    : 0;

                                // Margen
                                $margen = ($costo > 0 && $pvpActual > 0)
                                    ? round((($pvpActual * 100) / $costo) - 100, 2)
                                    : 0;

                                // Paralelo
                                $pvpBase = (($detalle->producto['nuevo_pvp'] ?? 0) > 0)
                                    ? ($detalle->producto['nuevo_pvp'] ?? 0)
                                    : ($detalle->producto['pvp_divisa'] ?? 0);

                                // Tasas
                                $tasaBCV = $tasa['DivisaValor']['Valor'] ?? 0;
                                $tasaParalelo = $paralelo ?? 0;

                                // Monto en dólares paralelos
                                $montoParalelo = ($pvpBase > 0 && $tasaBCV > 0 && $tasaParalelo > 0)
                                    ? round(($pvpBase * $tasaParalelo) / $tasaBCV, 2)
                                    : 0;

                                // Acumular total inventario en dólares
                                $existenciaCalc = $detalle->producto['existencia'] ?? 0;

                                $totalInventarioUSD += ($existenciaCalc * $costo);

                            @endphp
                            <tr class="align-middle" data-id="{{ $detalle->producto['id'] }}" data-rating="{{ $estrellas }}">
                                <td class="text-center">
                                    <input type="checkbox" 
                                        name="productosSeleccionados[]" 
                                        class="checkProductoRotacion"
                                        value="{{ $detalle->producto_id }}"
                                        data-sucursal="{{ session('sucursal_id', 0) }}">
                                </td>
                                <!-- Foto -->
                                <td class="text-center">
                                    <div class="position-relative">
                                        <img src="{{ $urlImagen }}" 
                                            class="img-thumbnail rounded img-zoomable" 
                                            style="width: 50px; height: 50px; object-fit:cover; cursor: zoom-in;"
                                            alt="{{ $detalle->producto['descripcion'] ?? '' }}"
                                            data-full-image="{{ $urlImagen }}"
                                            data-description="{{ $detalle->producto['descripcion'] }}"
                                            title="{{ $detalle->producto['descripcion'] ?? '' }}"
                                            onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                        @if($existencia == 0)
                                        <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5em;">
                                            0
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Código -->
                                <td>
                                    <span class="badge bg-light text-dark border" title="{{ $detalle->producto['sucursal_nombre'] ?? '' }}">{{ $detalle->producto['codigo'] }}</span>
                                </td>
                                
                                <!-- Descripción -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" data-bs-toggle="tooltip" title="{{ $detalle->producto['sucursal_nombre'] ?? '' }}">
                                            {{ Str::limit($detalle->producto['descripcion'] ?? 'Sin descripción', 40) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Existencia -->
                                <td class="text-center {{ $colorExistencia }}">
                                    {{ $existencia }}
                                </td>
                                
                                <!-- Ventas -->
                                <td class="text-center fw-bold text-primary">
                                    {{ $detalle->total_unidades }}
                                </td>
                                
                                <!-- Costo -->
                                <td class="text-center text-muted">
                                    ${{ number_format($detalle->producto['costo_divisa'] ?? 0, 2) }}
                                </td>
                                
                                <!-- PVP -->
                                <td class="text-center celdaPVP align-middle">
                                    <div class="d-flex flex-column">
                                        <!-- Precio -->
                                        <div class="fw-bold text-success precioPVP">
                                            ${{ number_format((($detalle->producto['nuevo_pvp'] ?? 0) > 0) ? ($detalle->producto['nuevo_pvp'] ?? 0) : ($detalle->producto['pvp_divisa'] ?? 0), 2) }}
                                        </div>
                                        
                                        <!-- Utilidad y Margen -->
                                        <span class="badge badge-utilidad {{ $utilidad >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                            U: ${{ number_format($utilidad, 2) }}
                                        </span>

                                        <!-- Margen -->
                                        <span class="badge badge-margen {{ $margen >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                                            M: {{ number_format($margen, 2) }}%
                                        </span>

                                        <!-- Paralelo -->
                                        <span id="paralelo-{{ $detalle->producto['id'] }}">
                                            P: {{ number_format($montoParalelo, 2) }}$
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Fecha de Creacion del Producto -->
                                @php
                                    $fechaCreacion = $detalle->producto['fecha_creacion'];
                                    $esUrgente = $fechaCreacion && $fechaCreacion->lt(now()->subMonths(18)); // 1 año y medio
                                @endphp

                                <td class="text-center"
                                    data-order="{{ $fechaCreacion ? $fechaCreacion->format('Y-m-d') : '1900-01-01' }}">
                                    
                                    <div class="d-flex flex-column align-items-center">
                                        <!-- Fecha normal / roja si urgente -->
                                        <span class="badge {{ $esUrgente ? 'bg-danger text-white' : 'bg-light text-dark' }}">
                                            {{ $fechaCreacion ? $fechaCreacion->format('d/m/Y') : 'N/A' }}
                                        </span>
                                        
                                        <!-- Etiqueta Urgente con icono -->
                                        @if($esUrgente)
                                            <small class="text-danger mt-1">
                                                <i class="bi bi-fire"></i> Urgente
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Última Venta -->
                                <td class="text-center"
                                    data-order="{{ optional($detalle->producto['fecha_ultima_venta'])->format('Y-m-d') ?? '1900-01-01' }}">
                                    @if($detalle->producto['fecha_ultima_venta'])
                                        <span class="badge bg-light text-dark">
                                            {{ $detalle->producto['fecha_ultima_venta']->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">No Vendido</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <!-- Botón para actualizar PVP -->
                                        <button type="button" 
                                            class="btn btn-sm btn-outline-warning ms-1" 
                                            data-bs-toggle="tooltip" 
                                            title="Actualizar PVP"
                                            onclick="abrirModalActualizarPVP({{ $detalle->producto['id'] }}, {{ session('sucursal_id', 0) }}, 
                                            {{ json_encode([
                                                'codigo' => $detalle->producto['codigo'],
                                                'descripcion' => $detalle->producto['descripcion'],
                                                
                                                // CORREGIDO: Mostrar pvp_divisa si nuevo_pvp es 0
                                                'pvp_actual' => (floatval($detalle->producto['nuevo_pvp'] ?? 0) > 0) 
                                                                ? $detalle->producto['nuevo_pvp'] 
                                                                : ($detalle->producto['pvp_divisa'] ?? 0),
                                                
                                                // pvp_anterior: similar lógica si es necesario
                                                'pvp_anterior' => $detalle->producto['pvp_anterior'] ?? 0,
                                                
                                                // nuevo_pvp: mostrar el valor real (aunque sea 0)
                                                'nuevo_pvp' => $detalle->producto['nuevo_pvp'] ?? 0,
                                                
                                                'fecha_nuevo_precio' => ($detalle->producto['fecha_nuevo_precio'] ?? null)
                                                                        ? $detalle->producto['fecha_nuevo_precio']->format('Y-m-d H:i:s')
                                                                        : null,
                                                'existencia' => $detalle->producto['existencia'] ?? 0,
                                                'costo_divisa' => $detalle->producto['costo_divisa'] ?? 0,
                                                'url_foto' => $urlImagen
                                            ]) }})">
                                            <i class="bi bi-currency-dollar"></i>
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="tooltip" 
                                                title="Ver detalles"
                                                onclick="verDetalleProducto({{ $detalle->producto['id'] }})">
                                            <i class="bi bi-eye"></i> <!-- Bootstrap Icon -->
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando <strong>{{ $indices->detalles->count() }}</strong> productos
                    </div>

                    <div class="fw-bold text-success">
                        Total inventario: $
                        {{ number_format($totalInventarioUSD, 2) }}
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

<!-- Modal/Overlay para la imagen en zoom -->
<div id="imageZoomOverlay" class="image-zoom-overlay" style="display: none;">
    <div class="image-zoom-container">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="">
        <div id="imageDescription" class="image-description"></div>
    </div>
</div>
<!--end::App Content-->

<!-- Modal para Actualizar PVP -->
<div class="modal fade" id="modalActualizarPVP" tabindex="-1" aria-labelledby="modalActualizarPVPLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalActualizarPVPLabel">
                    <i class="bi bi-currency-dollar me-2"></i>Actualizar Precio de Venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información del producto -->
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <img id="productoImagen" src="" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" alt="Producto" onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                    </div>
                    <div class="col-md-9">
                        <h5 id="productoDescripcion" class="fw-bold"></h5>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>Código:</strong> <span id="productoCodigo" class="badge bg-light text-dark"></span></p>
                                <p class="mb-1"><strong>Existencia:</strong> <span id="productoExistencia" class="fw-bold"></span></p>
                                <p class="mb-1"><strong>Costo:</strong> <span id="productoCosto" class="text-muted"></span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>PVP Actual:</strong> <span id="productoPVPActual" class="text-success fw-bold"></span></p>
                                <p class="mb-1" style="display: none;">
                                    <strong>PVP Anterior:</strong> <span id="productoPVPAnterior" class="text-muted"></span>
                                </p>
                                <p class="mb-1"><strong>Último Cambio:</strong> <span id="productoFechaCambio" class="text-info"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Formulario de actualización -->
                <form id="formActualizarPVP" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="productoId" name="producto_id">
                    <input type="hidden" id="sucursalId" name="sucursal_id" value="{{ $sucursalId ?? 0 }}">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="nuevoPVP" class="form-label fw-bold">
                                <i class="bi bi-arrow-up-circle me-1"></i>Nuevo PVP ($)
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-warning text-dark fw-bold">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="nuevoPVP" 
                                       name="nuevo_pvp" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       placeholder="0.00"
                                       style="font-size: 1.25rem;">
                            </div>
                            <div class="form-text">Ingrese el nuevo precio de venta al público</div>
                        </div>
                    </div>
                    
                    <!-- Alerta de validación -->
                    <div class="alert alert-danger d-none" id="alertaValidacion">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="mensajeValidacion"></span>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning fw-bold" id="btnGuardarCambio">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambio de Precio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para aplicar porcentaje -->
<div class="modal fade" id="modalPorcentaje" tabindex="-1" aria-labelledby="modalPorcentajeLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPorcentajeLabel">Aplicar porcentaje a productos seleccionados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Input para porcentaje -->
        <div class="mb-3">
          <label for="inputPorcentaje" class="form-label">Porcentaje (%):</label>
          <input type="number" class="form-control" id="inputPorcentaje" placeholder="Ej: 10 para +10%, -5 para -5%">
        </div>

        <!-- Tabla de productos seleccionados -->
        <div style="max-height:400px; overflow-y:auto;">
          <table class="table table-hover table-sm" id="tablaPorcentaje">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Sucursal</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Existencia</th>
                <th>Ventas</th>
                <th>Costo</th>
                <th>PVP</th>
                <th>Índice Rotación</th>
                <th>Última Venta</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="aplicarPorcentajeConfirmado()">Aplicar</button>
      </div>
    </div>
  </div>
</div>

<!-- Botón flotante de automatización -->
<div class="position-fixed" style="bottom: 30px; right: 30px; z-index: 1000;">
    <button type="button" 
            class="btn btn-success btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center"
            id="btnEjecutarAutomatizacion"
            style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #20c997); border: none; box-shadow: 0 5px 20px rgba(40,167,69,0.4);"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Ejecutar Automatización de Precios">
        <i class="bi bi-robot" style="font-size: 40px; color: white;"></i>
        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle animate-pulse">
            <span class="visually-hidden">Nuevo</span>
        </span>
    </button>
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
    // Definir el array global al inicio de tu script
    let productosActualizados = [];

    // Función para filtrar la tabla
    function filtrarTabla() {
        var input, filter, table, tr, tdCodigo, tdDescripcion, i, txtValueCodigo, txtValueDescripcion;
        input = document.getElementById("buscarProducto");
        filter = input.value.toUpperCase();
        table = document.getElementById("tablaIndiceRotacion");
        tr = table.getElementsByTagName("tr");
        
        // Recorrer todas las filas de la tabla, empezando desde la 1 (omitir encabezado)
        for (i = 1; i < tr.length; i++) {
            // Obtener celdas de código (columna 2) y descripción (columna 3)
            tdCodigo = tr[i].getElementsByTagName("td")[2]; // Índice 1 para código
            tdDescripcion = tr[i].getElementsByTagName("td")[3]; // Índice 2 para descripción
            
            if (tdCodigo && tdDescripcion) {
                txtValueCodigo = tdCodigo.textContent || tdCodigo.innerText;
                txtValueDescripcion = tdDescripcion.textContent || tdDescripcion.innerText;
                
                // Mostrar la fila si coincide con código O descripción
                if (txtValueCodigo.toUpperCase().indexOf(filter) > -1 || 
                    txtValueDescripcion.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
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

            // Agregar evento Enter en el campo de búsqueda
            document.getElementById("buscarProducto").addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });
        });

        // Manejar envío del formulario
        document.getElementById('formActualizarPVP').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nuevoPVP = parseFloat(document.getElementById('nuevoPVP').value);
            const pvpActual = parseFloat(productoActual.pvp_actual);
            const mensajeValidacion = document.getElementById('mensajeValidacion');
            
            // Resetear mensaje previo
            mensajeValidacion.innerText = '';
            
            if (nuevoPVP <= 0) {
                mensajeValidacion.innerText = 'El PVP debe ser mayor a 0';
                return;
            }

            // Si pasa las validaciones, ya no hay mensajes
            mensajeValidacion.innerText = '';

            // if (!confirm(`¿Está seguro de cambiar el PVP de $${pvpActual.toFixed(2)} a $${nuevoPVP.toFixed(2)}?`)) return;
            Swal.fire({
                title: '¿Confirmar cambio de precio?',
                // html: `
                //     <p class="mb-1">PVP actual: <b>$${pvpActual.toFixed(2)}</b></p>
                //     <p class="mb-1">Nuevo PVP: <b class="text-success">$${nuevoPVP.toFixed(2)}</b></p>
                // `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (!result.isConfirmed) return;

                // Preparar datos
                const data = {
                    producto_id: productoActual.id,
                    sucursal_id: sucursalIdActual,
                    nuevo_pvp: nuevoPVP,
                    _token: '{{ csrf_token() }}' // Laravel CSRF
                };

                console.log(data);

                fetch('{{ url("/ruta/actualizar-pvp") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({
                        producto_id: productoActual.id,
                        sucursal_id: sucursalIdActual,
                        nuevo_pvp: parseFloat(document.getElementById('nuevoPVP').value),
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;

                        const nuevoPvp = parseFloat(data.pvp_actual);
                        const utilidad = parseFloat(data.utilidad_nuevo_pvp);
                        const margen = parseFloat(data.margen_nuevo_precio);
                        
                        const { bcv, paralelo } = obtenerTasasActuales();

                        // Actualizar celda PVP
                        const fila = document.querySelector(`#tablaIndiceRotacion tr[data-id="${data.producto_id}"]`);
                        if (fila) {
                            // Precio
                            fila.querySelector('.precioPVP').textContent = `$${nuevoPvp.toFixed(2)}`;

                            // Utilidad
                            const badgeUtilidad = fila.querySelector('.badge-utilidad');
                            if (badgeUtilidad) {
                                badgeUtilidad.textContent = `U: $${utilidad.toFixed(2)}`;
                                badgeUtilidad.className = utilidad >= 0
                                    ? 'badge badge-utilidad bg-success bg-opacity-10 text-success'
                                    : 'badge badge-utilidad bg-danger bg-opacity-10 text-danger';
                            }

                            // Margen
                            const badgeMargen = fila.querySelector('.badge-margen');
                            if (badgeMargen) {
                                badgeMargen.textContent = `M: ${margen.toFixed(2)}%`;
                                badgeMargen.className = margen >= 0
                                    ? 'badge badge-margen bg-success bg-opacity-10 text-success'
                                    : 'badge badge-margen bg-warning bg-opacity-10 text-warning';
                            }

                            let montoParalelo = 0;

                            if (nuevoPvp > 0 && bcv > 0 && paralelo > 0) {
                                montoParalelo = (nuevoPvp * paralelo) / bcv;
                            }

                            const paraleloEl = fila.querySelector('#paralelo-' + data.producto_id);
                            if (paraleloEl) {
                                paraleloEl.innerText = 'P: ' + montoParalelo.toFixed(2) + '$';
                            }
                        }

                        // Actualizar array global
                        const index = productosActualizados.findIndex(p => p.Id === data.producto_id);
                        const productoDTO = {
                            Id: data.producto_id,
                            Codigo: data.codigo,
                            Descripcion: data.descripcion,
                            PvpAnterior: data.pvp_anterior,
                            NuevoPvp: nuevoPvp,
                            CostoDivisa: parseFloat(data.costo_divisa)
                        };
                        
                        if (index >= 0) {
                            productosActualizados[index] = productoDTO; // actualizar
                        } else {
                            productosActualizados.push(productoDTO); // agregar
                        }

                        // Cerrar modal
                        bootstrap.Modal.getInstance(document.getElementById('modalActualizarPVP')).hide();
                        showToast('El precio se ha registrado', 'success');
                    } else {
                        showToast('Error al actualizar el PVP', 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Error en la petición', 'danger');
                });

            });
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

    function obtenerTasasActuales() {
        const bcv = parseFloat(
            document.querySelector('#tasa-actual-texto')?.dataset.tasa ?? 0
        );

        const paralelo = parseFloat(
            document.querySelector('#tasa-actual-texto-paralelo')?.dataset.tasa ?? 0
        );

        return { bcv, paralelo };
    }
    
    function exportarExcel() {
        const tabla = document.getElementById('tablaIndiceRotacion');

        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        /* =========================
        ENCABEZADOS
        ========================== */
        const headers = ['ID', 'Sucursal'];

        tabla.querySelectorAll('thead th').forEach((th, index) => {
            // Saltar Check (0) e Imagen (1)
            if (index < 2) return;

            const texto = th.textContent.trim();

            if (!texto.toLowerCase().includes('accion') &&
                !texto.toLowerCase().includes('acción')) {

                headers.push(texto);

                // 👉 Insertar "Paralelo" justo después de PVP
                if (texto.toLowerCase().includes('pvp')) {
                    headers.push('Paralelo');
                }
            }
        });

        datos.push(headers);

        /* =========================
        FILAS
        ========================== */
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const rowData = [];

            // --- Columna A: ID producto y Sucursal ---
            const checkbox = fila.querySelector('.checkProductoRotacion');
            const productoId = checkbox ? checkbox.value : '';
            const sucursal = checkbox ? checkbox.dataset.sucursal : '';

            rowData.push(productoId);
            rowData.push(sucursal);

            // --- Valor Paralelo desde el DOM ---
            const paralelo = (() => {
                const paraleloEl = fila.querySelector('#paralelo-' + productoId);
                if (!paraleloEl) return '';
                return parseFloat(
                    paraleloEl.innerText
                        .replace('P:', '')
                        .replace('$', '')
                        .trim()
                ) || '';
            })();

            // --- Resto de columnas ---
            fila.querySelectorAll('td').forEach((td, index) => {
                if (index < 2) return;

                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;

                const textoTh = th.textContent.trim();

                if (textoTh.toLowerCase().includes('accion') ||
                    textoTh.toLowerCase().includes('acción')) {
                    return;
                }

                let texto = td.textContent
                    .trim()
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ');

                // Badge
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }

                // Índice
                if (textoTh.includes('Índice') || textoTh.includes('Indice')) {
                    const numero = parseFloat(texto.replace(',', '.'));
                    if (!isNaN(numero)) texto = numero;
                }

                // Costo / PVP / Precio
                if (textoTh.includes('Costo') || textoTh.includes('PVP') || textoTh.includes('Precio')) {
                    texto = texto.replace('$', '').trim();
                    const numero = parseFloat(texto.replace(',', ''));
                    if (!isNaN(numero)) texto = numero;
                }

                // 👉 Agregar valor de la celda
                rowData.push(texto);

                // 👉 Justo después de PVP, agregar Paralelo
                if (textoTh.toLowerCase().includes('pvp')) {
                    rowData.push(paralelo);
                }
            });

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        /* =========================
        EXCEL
        ========================== */
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

        XLSX.utils.book_append_sheet(wb, ws, 'Indice Rotacion');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Productos_Baja_Demanda_${fecha}.xlsx`);
    }
    
    // Función para resetear filtros
    function resetearFiltros() {
        var hoy = new Date().toISOString().split('T')[0];
        var inicioMes = new Date();
        inicioMes.setDate(1);
        var inicioMesStr = inicioMes.toISOString().split('T')[0];
        
        document.getElementById('fecha_inicio').value = inicioMesStr;
        document.getElementById('fecha_fin').value = hoy;
        document.getElementById('filtroForm').submit();
    }
    
    // Función para ordenar tabla (opcional)
    function ordenarTabla(columna, direccion) {
        // Implementar ordenamiento de tabla si es necesario
    }
    
    // Función para generar PDF con imágenes
    async function pdfTabla() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Crear documento PDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        
        // Título del documento
        const titulo = 'Productos_Baja_Demanda - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);
        
        // Configuración de columnas
        const columnas = [];
        const datos = [];
        
        // Obtener encabezados (excluir columna de acciones)
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            const texto = th.textContent.trim();
            // Excluir columnas de acciones
            if (!texto.toLowerCase().includes('accion') && 
                !texto.toLowerCase().includes('acción') &&
                texto !== '#') {
                columnas.push({
                    header: texto,
                    dataKey: `col${index}`
                });
            }
        });
        
        // Preparar datos para la tabla
        let filaNumero = 0;
        const promesasImagenes = [];
        
        tabla.querySelectorAll('tbody tr').forEach((fila, filaIndex) => {
            if (fila.style.display !== 'none') {
                filaNumero++;
                const filaData = {};
                let colIndex = 0;
                
                fila.querySelectorAll('td').forEach((td, tdIndex) => {
                    const thCorrespondiente = tabla.querySelector(`thead th:nth-child(${tdIndex + 1})`);
                    if (thCorrespondiente) {
                        const textoTh = thCorrespondiente.textContent.trim();
                        if (!textoTh.toLowerCase().includes('accion') && 
                            !textoTh.toLowerCase().includes('acción') &&
                            textoTh !== '#') {
                            
                            // Si es la columna de foto, guardar la URL de la imagen
                            if (textoTh.toLowerCase().includes('foto')) {
                                const imgElement = td.querySelector('img');
                                if (imgElement && imgElement.src) {
                                    // Crear promesa para cargar la imagen
                                    promesasImagenes.push({
                                        filaIndex: filaNumero - 1,
                                        colIndex: colIndex,
                                        imgUrl: imgElement.src,
                                        filaData: filaData,
                                        dataKey: `col${colIndex}`
                                    });
                                    filaData[`col${colIndex}`] = ''; // Espacio reservado
                                } else {
                                    filaData[`col${colIndex}`] = '';
                                }
                            } else {
                                // Procesar otras columnas normalmente
                                filaData[`col${colIndex}`] = procesarContenidoParaPDF(td, textoTh);
                            }
                            
                            colIndex++;
                        }
                    }
                });
                
                // Agregar número de fila
                filaData.col0 = filaNumero.toString();
                datos.push(filaData);
            }
        });
        
        // Agregar columna de número de fila
        if (columnas[0] && columnas[0].header !== '#') {
            columnas.unshift({
                header: '#',
                dataKey: 'col0'
            });
        }
        
        // Crear tabla básica primero
        doc.autoTable({
            head: [columnas.map(col => col.header)],
            body: datos.map(fila => columnas.map(col => fila[col.dataKey] || '')),
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
            columnStyles: {
                col0: { cellWidth: 15, halign: 'center' },
                col1: { cellWidth: 25, halign: 'center' }, // Foto
                col2: { cellWidth: 30 }, // Código
                col3: { cellWidth: 60, fontStyle: 'bold' }, // Descripción
                col4: { cellWidth: 25, halign: 'center' }, // Existencia
                col5: { cellWidth: 30, halign: 'center' }, // Ventas
                col6: { cellWidth: 25, halign: 'right' }, // Costo
                col7: { cellWidth: 25, halign: 'right' }, // PVP
                col8: { cellWidth: 30, halign: 'center' }, // Índice
                col9: { cellWidth: 35, halign: 'center' }  // Última Venta
            },
            margin: { top: 35 }
        });
        
        // Obtener información de las celdas para agregar imágenes después
        const table = doc.autoTable.previous;
        
        // Función para cargar y agregar imágenes
        async function agregarImagenes() {
            for (const imgInfo of promesasImagenes) {
                try {
                    const base64 = await cargarImagenABase64(imgInfo.imgUrl);
                    if (base64) {
                        // Encontrar la celda correspondiente
                        if (table && table.cells && table.cells[imgInfo.filaIndex]) {
                            const cell = table.cells[imgInfo.filaIndex][imgInfo.colIndex + 1]; // +1 por columna #
                            if (cell) {
                                // Calcular posición centrada
                                const x = cell.x + (cell.width - 20) / 2;
                                const y = cell.y + (cell.height - 20) / 2;
                                
                                doc.addImage(
                                    base64,
                                    'JPEG',
                                    x,
                                    y,
                                    20,
                                    20
                                );
                            }
                        }
                    }
                } catch (error) {
                    console.log('Error cargando imagen:', imgInfo.imgUrl, error);
                }
            }
        }
        
        // Agregar imágenes
        await agregarImagenes();
        
        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(
                `Página ${i} de ${totalPaginas}`,
                doc.internal.pageSize.width - 30,
                doc.internal.pageSize.height - 10
            );
            doc.text(
                `Generado: ${new Date().toLocaleString('es-ES')}`,
                14,
                doc.internal.pageSize.height - 10
            );
        }
        
        // Descargar PDF
        const fecha = new Date().toISOString().split('T')[0];
        doc.save(`Productos_Baja_Demanda_${fecha}.pdf`);
    }

    // Función para cargar imagen a base64
    function cargarImagenABase64(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Redimensionar para PDF
                const maxSize = 50;
                let width = img.width;
                let height = img.height;
                
                // Mantener proporción
                if (width > height) {
                    if (width > maxSize) {
                        height = (height * maxSize) / width;
                        width = maxSize;
                    }
                } else {
                    if (height > maxSize) {
                        width = (width * maxSize) / height;
                        height = maxSize;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convertir a JPEG con calidad media
                const dataURL = canvas.toDataURL('image/jpeg', 0.7);
                resolve(dataURL);
            };
            
            img.onerror = function() {
                console.log('No se pudo cargar la imagen:', url);
                // Usar imagen por defecto si hay error
                const imagenDefault = document.querySelector('img[onerror]')?.getAttribute('onerror')?.match(/src='([^']+)'/)?.[1];
                if (imagenDefault) {
                    // Intentar con imagen por defecto
                    const imgDefault = new Image();
                    imgDefault.onload = function() {
                        const canvas = document.createElement('canvas');
                        canvas.width = 50;
                        canvas.height = 50;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(imgDefault, 0, 0, 50, 50);
                        resolve(canvas.toDataURL('image/jpeg'));
                    };
                    imgDefault.onerror = () => resolve(null);
                    imgDefault.src = imagenDefault;
                } else {
                    resolve(null);
                }
            };
            
            img.src = url;
        });
    }

    // Función para procesar contenido
    function procesarContenidoParaPDF(td, tituloColumna) {
        let texto = '';
        
        // Obtener texto del badge si existe
        const badge = td.querySelector('.badge');
        if (badge) {
            texto = badge.textContent.trim();
        } else {
            // Obtener texto limpio
            texto = td.textContent.trim()
                .replace(/\n/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }
        
        // Procesar según tipo de columna
        if (tituloColumna.includes('Índice') || tituloColumna.includes('Indice')) {
            const numero = parseFloat(texto.replace(',', '.'));
            return isNaN(numero) ? texto : numero.toFixed(2);
        }
        
        if (tituloColumna.includes('Costo') || tituloColumna.includes('PVP') || 
            tituloColumna.includes('Precio')) {
            const numero = parseFloat(texto.replace('$', '').replace(',', '').trim());
            return isNaN(numero) ? texto : '$' + numero.toFixed(2);
        }
        
        // Limitar descripción
        if (tituloColumna.includes('Descripción')) {
            if (texto.length > 40) {
                return texto.substring(0, 37) + '...';
            }
        }
        
        return texto;
    }

    // Versión más simple pero que SÍ muestra imágenes
    function pdfTablaConImagenes() {
        const tabla = document.getElementById('tablaIndiceRotacion');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Mostrar mensaje de carga
        const loading = document.createElement('div');
        loading.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 9999;
        `;
        loading.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light"></div><p class="mt-2">Generando PDF con imágenes...</p></div>';
        document.body.appendChild(loading);
        
        // Generar PDF después de un breve delay para que se carguen las imágenes
        setTimeout(async () => {
            try {
                await pdfTabla();
            } catch (error) {
                console.error('Error generando PDF:', error);
                alert('Error generando PDF. Intente nuevamente.');
            } finally {
                document.body.removeChild(loading);
            }
        }, 1000);
    }   

    // Abrir zoom al hacer clic
    document.querySelectorAll('.img-zoomable').forEach(img => {
        img.addEventListener('click', function() {
            const fullImage = this.getAttribute('data-full-image');
            const description = this.getAttribute('data-description');
            
            document.getElementById('zoomedImage').src = fullImage;
            document.getElementById('imageDescription').textContent = description;
            document.getElementById('imageZoomOverlay').style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        });
    });

    // Cerrar zoom
    function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeZoom();
        }
    });

    // Cerrar al hacer clic fuera de la imagen
    document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeZoom();
        }
    });
    
    // Variable global para almacenar datos del producto
    let productoActual = null;
    let sucursalIdActual = 0;
    
    function abrirModalActualizarPVP(productoId, sucursalId, datos) {
        // Almacenar datos globalmente
        productoActual = datos;
        productoActual.id = productoId;
        sucursalIdActual = sucursalId;

        // Verificar si se puede modificar (sucursalId debe ser diferente de 0)
        if (sucursalId === 0 || sucursalId === '0') {
            mostrarAlertaSucursalNoSeleccionada();
            return; // No abrir el modal
        }
        
        document.getElementById('nuevoPVP').value = '';
        const alerta = document.getElementById('alertaValidacion');
        const mensaje = document.getElementById('mensajeValidacion');
        mensaje.textContent = '';
        alerta.classList.add('d-none');

        const fila = document.querySelector(`#tablaIndiceRotacion tr[data-id="${productoId}"]`);
        // Leer valores dinámicamente desde la fila
        const pvpActualnew = parseFloat(fila.querySelector('.precioPVP').textContent.replace(/[^\d.-]/g, '')) || 0;
        document.getElementById('productoPVPActual').textContent = `$${pvpActualnew.toFixed(2)}`;
        
        // Establecer la ruta del formulario
        const form = document.getElementById('formActualizarPVP');
        
        // Llenar información del producto
        document.getElementById('productoId').value = productoId;
        document.getElementById('sucursalId').value = sucursalId;
        document.getElementById('productoCodigo').textContent = datos.codigo;
        document.getElementById('productoDescripcion').textContent = datos.descripcion;
        document.getElementById('productoExistencia').textContent = datos.existencia;
        document.getElementById('productoCosto').textContent = `$${parseFloat(datos.costo_divisa).toFixed(2)}`;
        //document.getElementById('productoPVPActual').textContent = `$${parseFloat(datos.pvp_actual).toFixed(2)}`;
        document.getElementById('productoPVPAnterior').textContent = datos.pvp_anterior ? `$${parseFloat(datos.pvp_anterior).toFixed(2)}` : 'N/A';
        document.getElementById('productoFechaCambio').textContent = datos.fecha_nuevo_precio ? 
            new Date(datos.fecha_nuevo_precio).toLocaleDateString('es-ES') : 'Nunca';
        
        // Cargar imagen del producto - ¡USAR LA URL PASADA!
        const productoImagen = document.getElementById('productoImagen');
        if (datos.url_foto && datos.url_foto.trim() !== '') {
            // Usar la imagen del producto
            productoImagen.src = datos.url_foto;
            productoImagen.onerror = function() {
                // Si falla, cargar imagen por defecto
                this.src = '{{ asset("assets/img/adminlte/img/produc_default.jfif") }}';
                this.onerror = null; // Prevenir bucles infinitos
            };
        } else {
            // Imagen por defecto
            productoImagen.src = '{{ asset("assets/img/adminlte/img/produc_default.jfif") }}';
        }
        
        // Establecer el nuevo PVP sugerido (actual + 10%)
        const pvpActual = parseFloat(datos.pvp_actual) || 0;
        const nuevoPVPSugerido = 0.00; // 10% más
        document.getElementById('nuevoPVP').value = nuevoPVPSugerido.toFixed(2);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalActualizarPVP'));
        modal.show();
    }

    // Función para mostrar alerta cuando no hay sucursal seleccionada
    function mostrarAlertaSucursalNoSeleccionada() {
        Swal.fire({
            icon: 'warning',
            title: 'Sucursal no seleccionada',
            html: `
                <div class="text-start">
                    <p>No se puede modificar el PVP porque no hay una sucursal específica seleccionada.</p>
                    <p class="mb-0"><strong>Para modificar precios:</strong></p>
                    <ol class="mt-2">
                        <li>Seleccione una sucursal específica en el filtro superior</li>
                        <li>Vuelva a intentar modificar el precio</li>
                    </ol>
                </div>
            `,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Entendido',
        }).then((result) => {
            if (result.isDismissed) {
                // Opcional: Redirigir o realizar alguna acción cuando el usuario elige "Ver todas"
                console.log('Usuario eligió mantener vista de todas las sucursales');
            }
        });
    }

    // Función para cerrar modal
    function cerrarModalActualizarPVP() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalActualizarPVP'));
        modal.hide();
    }

    // Seleccionar o Deseleccionar todos
    document.getElementById('checkAllRotacion')?.addEventListener('change', function() {
        const marcados = document.querySelectorAll('.checkProductoRotacion');
        marcados.forEach(chk => chk.checked = this.checked);
    });

    document.getElementById('filtroValoracion').addEventListener('change', function () {
        const valor = this.value;
        const filas = document.querySelectorAll('#tablaIndiceRotacion tbody tr');

        filas.forEach(fila => {
            const rating = fila.getAttribute('data-rating');

            if (!valor || rating === valor) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

    function cambiosPVP(sucursalId, _sucursalNombre) {
        sucursalIdActual = sucursalId;
        sucursalNombre = _sucursalNombre;

        if (sucursalId === 0 || sucursalId === '0') {
            mostrarAlertaSucursalNoSeleccionada();
            return;
        }

        if (productosActualizados.length === 0) {
            showToast('No hay productos con cambios de PVP para descargar', 'info');
            return;
        }

        // Crear libro
        const wb = XLSX.utils.book_new();
        const nombreArchivo = `CambioDePrecios-${new Date().toISOString().slice(0,10)}.xlsx`;

        // Construir datos: encabezados + productos
        const dataExcel = [];

        // Encabezados tipo .NET
        dataExcel.push(['CAMBIO DE PRECIOS']);
        dataExcel.push([`Sucursal: ${sucursalNombre}`]);
        dataExcel.push([`Fecha: ${new Date().toLocaleDateString()}`]);
        dataExcel.push([]); // fila vacía
        dataExcel.push(['Productos']);
        dataExcel.push(['CODIGO', 'REFERENCIA', 'DESCRIPCION', 'PVP ANTERIOR', 'NUEVO PVP']);

        // Agregar productos
        productosActualizados.forEach(prod => {
            dataExcel.push([
                prod.Codigo,
                prod.Referencia ?? 'N/A',
                prod.Descripcion,
                prod.PvpAnterior ?? 0,
                prod.NuevoPvp
            ]);
        });

        // Crear hoja
        const ws = XLSX.utils.aoa_to_sheet(dataExcel);
        XLSX.utils.book_append_sheet(wb, ws, 'Cambio de Precios');

        // Descargar archivo
        XLSX.writeFile(wb, nombreArchivo);

        showToast(`Se ha generado el Excel con ${productosActualizados.length} productos`, 'success');
    }

    function descargarSeleccionadas(sucursalId, _sucursalNombre) {
        sucursalIdActual = sucursalId;
        sucursalNombre = _sucursalNombre;

        if (sucursalId === 0 || sucursalId === '0') {
            mostrarAlertaSucursalNoSeleccionada();
            return;
        }

        const tabla = document.getElementById('tablaIndiceRotacion');

        if (!tabla) {
            showToast('No se encontró la tabla para exportar', 'info');
            return;
        }

        const datos = [];

        // Encabezados
        const headers = ['ID', 'Sucursal'];
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            if (index < 2) return; // Saltar Check e Imagen
            const texto = th.textContent.trim();
            if (!texto.toLowerCase().includes('accion') && !texto.toLowerCase().includes('acción')) {
                headers.push(texto);

                // Si es PVP, agregar columna Paralelo justo después
                if (texto.toLowerCase().includes('pvp')) {
                    headers.push('Paralelo');
                }
            }
        });
        datos.push(headers);

        // Recorrer filas seleccionadas
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            const checkbox = fila.querySelector('.checkProductoRotacion');
            if (checkbox && checkbox.checked) {
                const rowData = [];

                // Columna ID
                rowData.push(fila.dataset.id || '');

                // Columna Sucursal
                rowData.push(sucursalIdActual);

                const productoId = fila.dataset.id || '';

                fila.querySelectorAll('td').forEach((td, index) => {
                    if (index < 2) return; // Saltar Check e Imagen
                    const thCorrespondiente = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                    if (!thCorrespondiente) return;
                    const textoTh = thCorrespondiente.textContent.trim();
                    if (!textoTh.toLowerCase().includes('accion') && !textoTh.toLowerCase().includes('acción')) {
                        let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');

                        // Si tiene badge, tomar texto
                        const badge = td.querySelector('.badge');
                        if (badge) texto = badge.textContent.trim();

                        // Convertir índice a número
                        if (textoTh.includes('Índice') || textoTh.includes('Indice')) {
                            const numero = parseFloat(texto.replace(',', '.'));
                            if (!isNaN(numero)) texto = numero;
                        }

                        // Limpiar moneda
                        if (textoTh.includes('Costo') || textoTh.includes('PVP') || textoTh.includes('Precio')) {
                            texto = texto.replace('$', '').trim();
                            const numero = parseFloat(texto.replace(',', ''));
                            if (!isNaN(numero)) texto = numero;
                        }

                        rowData.push(texto);

                        // Insertar Paralelo justo después de PVP
                        if (textoTh.toLowerCase().includes('pvp')) {
                            const paraleloEl = fila.querySelector('#paralelo-' + productoId);
                            let paralelo = '';
                            if (paraleloEl) {
                                paralelo = paraleloEl.innerText.replace('P:', '').replace('$', '').trim();
                            }
                            rowData.push(paralelo);
                        }
                    }
                });

                datos.push(rowData);
            }
        });

        if (datos.length <= 1) {
            showToast('No hay productos seleccionados para exportar', 'warning');
            return;
        }

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);

        // Ajustar anchos de columna
        const maxColLengths = [];
        datos.forEach(row => {
            row.forEach((cell, colIndex) => {
                const cellLength = String(cell).length;
                if (!maxColLengths[colIndex] || cellLength > maxColLengths[colIndex]) {
                    maxColLengths[colIndex] = cellLength;
                }
            });
        });
        ws['!cols'] = maxColLengths.map(length => ({ wch: Math.min(Math.max(length, 10), 50) }));

        XLSX.utils.book_append_sheet(wb, ws, 'Productos Seleccionados');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Productos_Baja_Demanda_${fecha}.xlsx`);

        showToast(`Se ha generado el Excel con ${datos.length - 1} productos`, 'success');
    }

    function ingresarPorcentaje(sucursalId, _sucursalNombre) {

        if (sucursalId === 0 || sucursalId === '0') {
            mostrarAlertaSucursalNoSeleccionada();
            return;
        }

        const tablaPrincipal = document.getElementById('tablaIndiceRotacion');
        if (!tablaPrincipal) return;

        const filasSeleccionadas = [];
        tablaPrincipal.querySelectorAll('tbody tr').forEach(fila => {
            const checkbox = fila.querySelector('.checkProductoRotacion');
            if (checkbox && checkbox.checked) {
                filasSeleccionadas.push(fila);
            }
        });

        if (filasSeleccionadas.length === 0) {
            showToast('No hay productos seleccionados', 'warning');
            return;
        }

        // Vaciar la tabla del modal
        const tbodyModal = document.querySelector('#tablaPorcentaje tbody');
        tbodyModal.innerHTML = '';

        // Recorrer productos seleccionados y agregarlos al modal
        filasSeleccionadas.forEach(fila => {
            const checkbox = fila.querySelector('.checkProductoRotacion');
            const productoId = checkbox.value;
            const sucursalIdFila = checkbox.dataset.sucursal || sucursalId;

            const cols = fila.querySelectorAll('td');
            const codigo = cols[2].textContent.trim();
            const descripcion = cols[3].textContent.trim();
            const existencia = cols[4].textContent.trim();
            const ventas = cols[5].textContent.trim();
            const costo = cols[6].textContent.trim();
            const pvp = cols[7].textContent.trim();
            const indice = cols[8].textContent.trim();
            const ultimaVenta = cols[9].textContent.trim();

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${productoId}</td>
                <td>${sucursalIdFila}</td>
                <td>${codigo}</td>
                <td>${descripcion}</td>
                <td>${existencia}</td>
                <td>${ventas}</td>
                <td>${costo}</td>
                <td class="pvp">${pvp}</td>
                <td>${indice}</td>
                <td>${ultimaVenta}</td>
            `;
            tbodyModal.appendChild(tr);
        });

        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalPorcentaje'));
        modal.show();
    }

    // Versión CORREGIDA de aplicarPorcentajeConfirmado:
    async function aplicarPorcentajeConfirmado() {
        const porcentaje = parseFloat(document.getElementById('inputPorcentaje').value);
        if (isNaN(porcentaje) || porcentaje === 0) {
            showToast('Ingrese un porcentaje válido distinto de 0', 'warning');
            return;
        }

        const filas = document.querySelectorAll('#tablaPorcentaje tbody tr');
        if (filas.length === 0) {
            showToast('No hay productos seleccionados', 'info');
            return;
        }

        const result = await Swal.fire({
            title: '¿Confirmar cambios de precios?',
            html: `<p>Porcentaje: <b>${porcentaje}%</b></p>
                <p>Productos: <b>${filas.length}</b></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        // 🔄 Mostrar cargando
        Swal.fire({
            title: 'Aplicando porcentaje...',
            text: `0 de ${filas.length} productos procesados`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let exitosos = 0;
        let errores = [];

        try {
            for (let i = 0; i < filas.length; i++) {
                const fila = filas[i];
                
                // Actualizar progreso
                Swal.update({
                    text: `${i + 1} de ${filas.length} productos procesados`
                });

                // OBTENER DATOS DE LAS CELDAS (CORRECCIÓN)
                const celdas = fila.querySelectorAll('td');
                
                // Las celdas están en este orden según tu HTML:
                // 0: ID, 1: Sucursal, 2: Código, 3: Descripción, 4: Existencia, 
                // 5: Ventas, 6: Costo, 7: PVP (con clase .pvp), 8: Índice, 9: Última Venta
                
                const productoId = celdas[0].textContent.trim();
                const sucursalId = celdas[1].textContent.trim();
                const tdPvp = celdas[7]; // Esto es el elemento td con clase .pvp
                
                // Extraer PVP actual (quitar símbolos)
                const pvpTexto = tdPvp.textContent.trim();
                const pvpActual = parseFloat(
                    pvpTexto.replace(/[^\d.-]/g, '')
                );

                if (isNaN(pvpActual) || pvpActual <= 0) {
                    errores.push(`Producto ${productoId}: PVP inválido (${pvpTexto})`);
                    continue;
                }

                // Calcular nuevo PVP
                const nuevoPvp = parseFloat(
                    (pvpActual * (1 + porcentaje / 100)).toFixed(2)
                );

                if (nuevoPvp <= 0) {
                    errores.push(`Producto ${productoId}: Nuevo PVP inválido (${nuevoPvp})`);
                    continue;
                }

                console.log(`Enviando request para producto ${productoId}:`, {
                    producto_id: productoId,
                    sucursal_id: sucursalId,
                    nuevo_pvp: nuevoPvp
                });

                // Hacer la petición
                const res = await fetch('{{ url("/ruta/actualizar-pvp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        producto_id: productoId,
                        sucursal_id: sucursalId,
                        nuevo_pvp: nuevoPvp
                    })
                });

                // DEBUG: Ver qué responde el servidor
                const responseText = await res.text();
                console.log('Respuesta raw:', responseText);
                        
                const { bcv, paralelo } = obtenerTasasActuales();

                try {
                    const data = JSON.parse(responseText);
                    
                    if (data.success) {
                        // 1. Actualizar celda en tabla principal
                        const filaPrincipal = document.querySelector(
                            `#tablaIndiceRotacion tr[data-id="${productoId}"]`
                        );
                        if (filaPrincipal) {
                            const celdaPVP = filaPrincipal.querySelector('.celdaPVP');
                            if (celdaPVP) {
                                celdaPVP.querySelector('.precioPVP').textContent = `$${parseFloat(data.data.pvp_actual).toFixed(2)}`;

                                // Actualizar badges
                                const badgeUtilidad = celdaPVP.querySelector('.badge:nth-child(1)');
                                const badgeMargen = celdaPVP.querySelector('.badge:nth-child(2)');

                                const utilidad = parseFloat(data.data.utilidad_nuevo_pvp);
                                const margen = parseFloat(data.data.margen_nuevo_precio);

                                if (badgeUtilidad) {
                                    badgeUtilidad.textContent = `U: $${utilidad.toFixed(2)}`;
                                    badgeUtilidad.className = utilidad >= 0
                                        ? 'badge bg-success bg-opacity-10 text-success'
                                        : 'badge bg-danger bg-opacity-10 text-danger';
                                }

                                if (badgeMargen) {
                                    badgeMargen.textContent = `M: ${margen.toFixed(2)}%`;
                                    badgeMargen.className = margen >= 0
                                        ? 'badge bg-success bg-opacity-10 text-success'
                                        : 'badge bg-warning bg-opacity-10 text-warning';
                                }

                                let montoParalelo = 0;

                                if (nuevoPvp > 0 && bcv > 0 && paralelo > 0) {
                                    montoParalelo = (nuevoPvp * paralelo) / bcv;
                                }

                                const paraleloEl = filaPrincipal.querySelector('#paralelo-' + productoId);
                                if (paraleloEl) {
                                    paraleloEl.innerText = 'P: ' + montoParalelo.toFixed(2) + '$';
                                }
                            }
                        }

                        // 2. Actualizar tabla del modal
                        tdPvp.textContent = `$${nuevoPvp.toFixed(2)}`;

                        // 3. Actualizar array global
                        const productoDTO = {
                            Id: parseInt(productoId),
                            Codigo: data.data.codigo,
                            Descripcion: data.data.descripcion,
                            PvpAnterior: data.data.pvp_anterior,
                            NuevoPvp: nuevoPvp,
                            CostoDivisa: data.data.costo_divisa
                        };

                        const index = productosActualizados.findIndex(p => p.Id == productoId);
                        if (index >= 0) {
                            productosActualizados[index] = productoDTO;
                        } else {
                            productosActualizados.push(productoDTO);
                        }

                        exitosos++;
                    } else {
                        errores.push(`Producto ${productoId}: ${data.message || 'Error del servidor'}`);
                    }
                } catch (jsonError) {
                    console.error('Error parseando JSON:', jsonError);
                    console.error('Respuesta del servidor:', responseText);
                    errores.push(`Producto ${productoId}: Respuesta no válida del servidor`);
                }

                // Pequeña pausa para no saturar
                await new Promise(resolve => setTimeout(resolve, 100));
            }

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(
                document.getElementById('modalPorcentaje')
            );
            if (modal) modal.hide();

            // Mostrar resultado
            Swal.fire({
                title: exitosos === filas.length ? '¡Completado!' : 'Proceso finalizado',
                html: `<div class="text-start">
                        <p>✅ <b>${exitosos}</b> productos actualizados correctamente</p>
                        ${errores.length > 0 ? `
                            <p class="mt-2">❌ <b>${errores.length}</b> errores:</p>
                            <ul class="small text-danger">
                                ${errores.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        ` : ''}
                    </div>`,
                icon: exitosos > 0 ? 'success' : 'error',
                confirmButtonText: 'Aceptar'
            });

        } catch (error) {
            console.error('Error general:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error inesperado: ' + error.message,
                icon: 'error'
            });
        }
    }

    function mostrarCargando(texto = 'Procesando...') {
        const loading = document.createElement('div');
        loading.id = 'overlayLoading';
        loading.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        loading.innerHTML = `
            <div style="text-align:center; color:white">
                <div class="spinner-border text-light"></div>
                <p class="mt-2">${texto}</p>
            </div>
        `;
        document.body.appendChild(loading);
    }

    function ocultarCargando() {
        const loading = document.getElementById('overlayLoading');
        if (loading) loading.remove();
    }

    function verDetalleProducto(id) {
        var ruta = '{{ url("/") }}' + '/productos/' + id;
        window.location.href = ruta;
    }

    // ============================================
    // AUTOMATIZACIÓN DE BAJA DEMANDA
    // ============================================

    // Variable para controlar si ya está ejecutando
    let ejecutandoAutomatizacion = false;

    // Variables de ejecución reciente
    const ejecucionReciente = {{ $ejecucionReciente ? 'true' : 'false' }};
    const fechaUltimaEjecucion = '{{ $fechaUltimaEjecucion ?? '' }}';
    const ultimoReporte = @json($ultimoReporte ?? null); 

    // Evento del botón de automatización
    document.getElementById('btnEjecutarAutomatizacion')?.addEventListener('click', function() {
        if (ejecutandoAutomatizacion) {
            showToast('Ya hay una automatización en ejecución', 'warning');
            return;
        }
        
        // Verificar si ya se ejecutó recientemente (desde variable de PHP)
        if (ejecucionReciente) {
            // ✅ CORREGIDO: Verificar si tenemos el reporte guardado (tiene detalles)
            if (ultimoReporte && ultimoReporte.detalles && ultimoReporte.detalles.length > 0) {
                // Usar los datos guardados para exportar
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Ejecución reciente detectada',
                    html: `
                        <div class="text-start">
                            <p>Ya se ejecutó una automatización en esta sucursal hace menos de 30 días.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Última ejecución:</strong> ${fechaUltimaEjecucion}
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Productos analizados:</strong> ${ultimoReporte.total_analizados || 0}<br>
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Productos afectados:</strong> ${ultimoReporte.productos_afectados || 0}
                            </div>
                            <p class="mt-2 mb-0 fw-bold">¿Deseas exportar el reporte de la última ejecución?</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: false,
                    showConfirmButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-file-pdf me-2"></i>Exportar a PDF',
                    denyButtonText: '<i class="bi bi-file-excel me-2"></i>Exportar a Excel',
                    confirmButtonColor: '#dc3545',
                    denyButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ✅ Usar exportarResultadosAutomatizacionPDF con ultimoReporte directamente
                        Swal.fire({
                            title: 'Generando PDF...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarResultadosAutomatizacionPDF(ultimoReporte);
                                    Swal.close();
                                }, 500);
                            }
                        });
                    } else if (result.isDenied) {
                        // ✅ Usar exportarResultadosAutomatizacionExcel con ultimoReporte directamente
                        Swal.fire({
                            title: 'Generando Excel...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarResultadosAutomatizacionExcel(ultimoReporte);
                                    Swal.close();
                                }, 500);
                            }
                        });
                    }
                });
                return;
            } else {
                // Si no hay datos guardados, exportar la tabla actual
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Ejecución reciente detectada',
                    html: `
                        <div class="text-start">
                            <p>Ya se ejecutó una automatización en esta sucursal hace menos de 30 días.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-clock me-2"></i>
                                <strong>Última ejecución:</strong> ${fechaUltimaEjecucion}
                            </div>
                            <p class="mt-2 mb-0 fw-bold">¿Deseas exportar la información actual?</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: false,
                    showConfirmButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-file-pdf me-2"></i>Exportar a PDF',
                    denyButtonText: '<i class="bi bi-file-excel me-2"></i>Exportar a Excel',
                    confirmButtonColor: '#dc3545',
                    denyButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Generando PDF...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    pdfTablaConImagenes();
                                    Swal.close();
                                }, 500);
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: 'Generando Excel...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => {
                                    exportarExcelBajaDemanda();
                                    Swal.close();
                                }, 500);
                            }
                        });
                    }
                });
                return;
            }
        } else {
            // ✅ NO hay ejecución reciente (más de 30 días o nunca)
        
            // ✅ Validar que la fecha seleccionada sea la fecha actual
            const fechaFinSeleccionada = document.getElementById('fecha_fin').value;
            const fechaActual = new Date().toISOString().split('T')[0];
            
            if (fechaFinSeleccionada !== fechaActual) {
                // La fecha seleccionada no es hoy, mostrar advertencia
                Swal.fire({
                    title: '<i class="bi bi-exclamation-triangle me-2"></i> Fecha no válida',
                    html: `
                        <div class="text-start">
                            <p>La automatización de precios solo puede ejecutarse para la fecha actual.</p>
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-calendar-date me-2"></i>
                                <strong>Fecha seleccionada:</strong> ${fechaFinSeleccionada}<br>
                                <i class="bi bi-calendar-date me-2"></i>
                                <strong>Fecha actual:</strong> ${fechaActual}
                            </div>
                            <p class="mt-2 mb-0">Por favor, selecciona la fecha actual para ejecutar la automatización.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }
        }
        
        // Si no hay ejecución reciente, mostrar el modal normal
        mostrarModalConfirmacion();
    });

    // Función para mostrar modal de confirmación con información detallada
    function mostrarModalConfirmacion() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const sucursalNombre = '{{ session('sucursal_nombre', 'Todas las sucursales') }}';
        
        Swal.fire({
            title: '<i class="bi bi-robot me-2" style="font-size: 28px;"></i> Automatización de Precios',
            html: `
                <div class="container-fluid px-0">
                    <!-- Descripción breve -->
                    <p class="mb-3 text-muted" style="font-size: 14px;">
                        Se aplicarán descuentos automáticos según la antigüedad de los productos.
                        Solo se procesarán productos con más de 2 meses de creación.
                    </p>
                    
                    <!-- Tabla de descuentos -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <small class="text-muted" style="display: block; margin-bottom: 1rem;">2 - 5 meses</small>
                                <small class="text-muted">Rotación Lenta</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <small class="text-muted" style="display: block; margin-bottom: 1rem;">5 - 8 meses</small>
                                <small class="text-muted">Riesgo Estancamiento</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <small class="text-muted" style="display: block; margin-bottom: 1rem;">8 - 12 meses</small>
                                <small class="text-muted">Mercancía Crítica</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <small class="text-muted" style="display: block; margin-bottom: 1rem;">12 - 18 meses</small>
                                <small class="text-muted">Remate Total</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-2 text-center" style="background: #fff3cd; border-color: #ff9800;">
                                <small class="text-muted" style="display: block; margin-bottom: 1rem;">&gt; 18 meses</small>
                                <small class="text-muted fw-bold">Super Remate Total</small>
                                <small class="d-block text-muted" style="font-size: 10px;">⚠️ Puede quedar por debajo del costo</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="small text-center text-muted">
                        <i class="bi bi-building me-1 ms-2"></i> ${sucursalNombre}
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Ejecutar',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancelar',
            width: '450px',
            customClass: {
                popup: 'rounded-4',
                title: 'fs-4 fw-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarAutomatizacion();
            }
        });
    }

    // Función para ejecutar la automatización
    async function ejecutarAutomatizacion() {
        // Validar que haya una sucursal seleccionada
        const sucursalId = {{ session('sucursal_id', 0) }};
        const sucursalNombre = '{{ session('sucursal_nombre', '') }}';
        
        if (sucursalId === 0 || sucursalId === '0') {
            Swal.fire({
                title: '<i class="bi bi-exclamation-triangle me-2"></i> Sucursal no seleccionada',
                html: `
                    <div class="text-start">
                        <p>Para ejecutar la automatización, debes seleccionar una sucursal específica.</p>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>¿Cómo seleccionar una sucursal?</strong>
                            <ul class="mt-2 mb-0">
                                <li>Ve al filtro superior de la página</li>
                                <li>Selecciona una sucursal específica (3, 4, 5 o 7)</li>
                                <li>Luego vuelve a intentar ejecutar la automatización</li>
                            </ul>
                        </div>
                        <p class="small text-muted mt-3">
                            <i class="bi bi-shield-check"></i> 
                            La automatización solo puede ejecutarse por sucursal para evitar sobrecarga del sistema.
                        </p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Entendido',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Verificar que la sucursal sea válida (3,4,5,7 - excluyendo almacén)
        const sucursalesValidas = [3, 4, 5, 7];
        if (!sucursalesValidas.includes(parseInt(sucursalId))) {
            Swal.fire({
                title: '<i class="bi bi-exclamation-triangle me-2"></i> Sucursal no válida',
                html: `
                    <div class="text-start">
                        <p>La sucursal seleccionada no es válida para ejecutar la automatización.</p>
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-shop me-2"></i>
                            <strong>Sucursal actual:</strong> ${sucursalNombre || 'ID: ' + sucursalId}
                        </div>
                        <p class="mt-2">Las sucursales válidas para automatización son:</p>
                        <ul>
                            <li>Sucursal 3</li>
                            <li>Sucursal 4</li>
                            <li>Sucursal 5</li>
                            <li>Sucursal 7</li>
                        </ul>
                        <p class="small text-muted mt-2">
                            <i class="bi bi-info-circle"></i> 
                            La sucursal 6 (Almacén) no está disponible para ventas.
                        </p>
                    </div>
                `,
                icon: 'error',
                confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Entendido',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        ejecutandoAutomatizacion = true;
        
        // Mostrar progreso
        Swal.fire({
            title: '<i class="bi bi-robot me-2"></i> Ejecutando Automatización',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p id="mensajeProgreso" class="mb-2">Iniciando análisis de productos...</p>
                    <div class="alert alert-info py-1 mb-2">
                        <small><i class="bi bi-shop me-1"></i> Sucursal: <strong>${sucursalNombre}</strong></small>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        <div id="barraProgreso" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                            role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="mt-2">
                        <small id="detalleProgreso" class="text-muted">Preparando datos...</small>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                simularProgreso();
            }
        });
        
        try {
            // Obtener datos del filtro
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            // Actualizar mensaje
            actualizarMensajeProgreso('Analizando productos con baja demanda...', 20);
            
            // Llamada AJAX a la automatización
            const response = await fetch('{{ route("cpanel.automatizacion.ejecutar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    sucursal_id: sucursalId
                })
            });
            
            actualizarMensajeProgreso('Aplicando ajustes de precio...', 60);
            
            const data = await response.json();
            
            actualizarMensajeProgreso('Finalizando y generando reporte...', 90);
            
            await new Promise(resolve => setTimeout(resolve, 500));
            
            if (window.progresoInterval) clearInterval(window.progresoInterval);
            
            if (data.success) {
                mostrarResultadoExitoso(data);
            } else {
                mostrarResultadoError(data);
            }
            
        } catch (error) {
            console.error('Error en automatización:', error);
            if (window.progresoInterval) clearInterval(window.progresoInterval);
            
            Swal.fire({
                title: '<i class="bi bi-exclamation-triangle me-2"></i> Error',
                html: `
                    <div class="text-start">
                        <p>Ocurrió un error al ejecutar la automatización:</p>
                        <div class="alert alert-danger">
                            <i class="bi bi-bug me-2"></i>
                            ${error.message || 'Error de conexión con el servidor'}
                        </div>
                        <p class="mb-0 small">Por favor, intente nuevamente o contacte al administrador.</p>
                    </div>
                `,
                icon: 'error',
                confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Cerrar'
            });
        } finally {
            ejecutandoAutomatizacion = false;
        }
    }

    // Función para simular progreso
    function simularProgreso() {
        let progreso = 0;
        const interval = setInterval(() => {
            if (progreso < 95) {
                progreso += Math.random() * 10;
                if (progreso > 95) progreso = 95;
                const barra = document.getElementById('barraProgreso');
                if (barra) barra.style.width = progreso + '%';
            }
        }, 1000);
        
        // Guardar interval para limpiar después
        window.progresoInterval = interval;
    }

    // Función para actualizar mensaje de progreso
    function actualizarMensajeProgreso(mensaje, porcentaje) {
        const mensajeEl = document.getElementById('mensajeProgreso');
        const barraEl = document.getElementById('barraProgreso');
        const detalleEl = document.getElementById('detalleProgreso');
        
        if (mensajeEl) mensajeEl.textContent = mensaje;
        if (barraEl) barraEl.style.width = porcentaje + '%';
        if (detalleEl) {
            const mensajesDetalle = [
                'Procesando información...',
                'Calculando...',
                'Aplicando reglas de negocio...',
                'Generando sugerencias...',
                'Actualizando base de datos...'
            ];
            const idx = Math.floor(Math.random() * mensajesDetalle.length);
            detalleEl.textContent = mensajesDetalle[idx];
        }
    }

    // Función para mostrar resultado exitoso
    function mostrarResultadoExitoso(data) {
        // Limpiar interval de progreso
        if (window.progresoInterval) clearInterval(window.progresoInterval);
        
        // Calcular productos omitidos
        const productosMantenidos = data.productos_mantenidos || 0;
        const productosSaltados = data.productos_saltados_reproceso || 0;
        const totalAnalizados = data.total_analizados || 0;
        const productosAfectados = data.productos_afectados || 0;
        const categorias = data.categorias || {};
        const sucursalNombre = data.sucursal_nombre || 'N/A';
        
        // Mostrar el modal
        Swal.fire({
            title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Automatización Completada!',
            html: `
                <div class="text-start">
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        ${data.mensaje || 'La automatización se ejecutó correctamente'}
                    </div>
                    
                    <!-- Tarjetas de resumen -->
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <h4 class="mb-0 text-primary">${totalAnalizados}</h4>
                                    <small>Productos analizados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <h4 class="mb-0 text-success">${productosAfectados}</h4>
                                    <small>Productos afectados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <h4 class="mb-0 text-warning">${productosMantenidos}</h4>
                                    <small>Precios mantenidos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-2">
                                    <h4 class="mb-0 text-secondary">${productosSaltados}</h4>
                                    <small>Saltados (reproceso)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desglose por categoría -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-1">
                                    <small class="fw-bold">Desglose por categoría</small>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-4">
                                            <small class="text-muted">Rotación Lenta:</small>
                                            <h6 class="mb-0">${categorias.rotacionLenta || 0}</h6>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Riesgo Estancamiento:</small>
                                            <h6 class="mb-0">${categorias.riesgoEstancamiento || 0}</h6>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Mercancía Crítica:</small>
                                            <h6 class="mb-0">${categorias.mercanciaCritica || 0}</h6>
                                        </div>
                                        <div class="col-4 mt-1">
                                            <small class="text-muted">Remate Total:</small>
                                            <h6 class="mb-0">${categorias.remateTotal || 0}</h6>
                                        </div>
                                        <div class="col-4 mt-1">
                                            <small class="text-muted">Nueva Colección:</small>
                                            <h6 class="mb-0">${categorias.nuevaColeccion || 0}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Productos Actualizados -->
                    ${data.detalles && data.detalles.length > 0 ? `
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Productos Actualizados (${data.detalles.length}):
                            </h6>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th class="text-end">Precio Actual</th>
                                            <th class="text-end">Nuevo Precio</th>
                                            <th class="text-center">Descuento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.detalles.slice(0, 10).map(d => `
                                            <tr>
                                                <td><small>${d.codigo || 'N/A'}</small></td>
                                                <td><small>${(d.descripcion || '').substring(0, 30)}...</small></td>
                                                <td class="text-end text-danger">$${d.precio_anterior || 0}</td>
                                                <td class="text-end text-success fw-bold">$${d.nuevo_precio || 0}</td>
                                                <td class="text-center"><span class="badge bg-success">-${d.porcentaje_descuento || 0}%</span></td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            ${data.detalles.length > 10 ? `<p class="text-muted small text-center mt-1">... y ${data.detalles.length - 10} productos más</p>` : ''}
                        </div>
                    ` : '<p class="text-muted text-center">No hay productos actualizados</p>'}
                    
                    <!-- Botones de exportación -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <button onclick="exportarResultadosActualizacionExcel(${JSON.stringify(data).replace(/"/g, '&quot;')})" 
                                    class="btn btn-success btn-sm w-100">
                                <i class="bi bi-file-excel me-2"></i>Exportar a Excel
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="exportarResultadosActualizacionPDF(${JSON.stringify(data).replace(/"/g, '&quot;')})" 
                                    class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-file-pdf me-2"></i>Exportar a PDF
                            </button>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-calendar-clock me-2"></i>
                        <strong>Días de gracia:</strong> ${data.dias_gracia || 30} días
                        <span class="mx-2">|</span>
                        <i class="bi bi-shop me-2"></i>
                        <strong>Sucursal:</strong> ${sucursalNombre}
                        <span class="mx-2">|</span>
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Fecha:</strong> ${new Date().toLocaleString()}
                    </div>
                </div>
            `,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Aceptar',
            width: '850px'
        }).then((result) => {
            // Recargar en cualquier caso: click en Aceptar, click en Recargar, o cerrar modal
            location.reload();
        });
        
        // ✅ Exportación automática SILENCIOSA (sin ningún tipo de alerta)
        setTimeout(() => {
            // const sucursalNombre = data.sucursal_nombre || 'Todas';
            // const fecha = new Date().toISOString().split('T')[0];
            // const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
            
            // // Preparar datos para el Excel
            // const excelData = [];
            
            // excelData.push(['REPORTE DE AUTOMATIZACIÓN DE PRECIOS']);
            // excelData.push(['Sucursal:', sucursalNombre]);
            // excelData.push(['Fecha:', new Date().toLocaleString()]);
            // excelData.push(['Días de gracia:', data.dias_gracia || 30]);
            // excelData.push([]);
            
            // excelData.push(['RESUMEN GENERAL']);
            // excelData.push(['Total productos analizados', data.total_analizados || 0]);
            // excelData.push(['Productos afectados', data.productos_afectados || 0]);
            // excelData.push(['Precios mantenidos', data.productos_mantenidos || 0]);
            // excelData.push(['Saltados por reproceso', data.productos_saltados_reproceso || 0]);
            // excelData.push([]);
            
            // const categorias = data.categorias || {};
            // excelData.push(['DESGLOSE POR CATEGORÍA']);
            // excelData.push(['Rotación Lenta', categorias.rotacionLenta || 0]);
            // excelData.push(['Riesgo Estancamiento', categorias.riesgoEstancamiento || 0]);  // ✅ Corregido: 35%
            // excelData.push(['Mercancía Crítica', categorias.mercanciaCritica || 0]);
            // excelData.push(['Remate Total', categorias.remateTotal || 0]);
            // excelData.push(['Super Remate Total', categorias.superRemateTotal || 0]);  // ✅ Agregado
            // excelData.push(['Nueva Colección', categorias.nuevaColeccion || 0]);
            // excelData.push([]);
            
            // if (data.detalles && data.detalles.length > 0) {
            //     excelData.push(['PRODUCTOS ACTUALIZADOS']);
            //     excelData.push(['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento', 'Costo', 'Existencia']);
                
            //     data.detalles.forEach(d => {
            //         excelData.push([
            //             d.codigo || 'N/A',
            //             d.descripcion || '',
            //             d.categoria || '',
            //             d.precio_anterior || 0,
            //             d.nuevo_precio || 0,
            //             d.porcentaje_descuento || 0,
            //             d.costo || 0,
            //             d.existencia || 0
            //         ]);
            //     });
            // }
            
            // // Crear y descargar Excel SILENCIOSAMENTE
            // const wb = XLSX.utils.book_new();
            // const ws = XLSX.utils.aoa_to_sheet(excelData);
            // ws['!cols'] = [{ wch: 25 }, { wch: 50 }, { wch: 25 }, { wch: 18 }, { wch: 18 }, { wch: 12 }, { wch: 15 }, { wch: 12 }];
            // XLSX.utils.book_append_sheet(wb, ws, 'Automatizacion');
            
            // const nombreArchivo = `Automatizacion_${sucursalNombre}_${fecha}_${hora}.xlsx`;
            // XLSX.writeFile(wb, nombreArchivo);

            exportarResultadosAutomatizacionExcel(data);
            
            // Sin ningún tipo de alerta
        }, 500);
    }

    // Función para mostrar error
    function mostrarResultadoError(data) {
        if (window.progresoInterval) clearInterval(window.progresoInterval);
        
        Swal.fire({
            title: '<i class="bi bi-exclamation-triangle text-warning me-2"></i> Automatización Parcial',
            html: `
                <div class="text-start">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        ${data.mensaje || 'La automatización se completó con advertencias'}
                    </div>
                    
                    ${data.errores && data.errores.length > 0 ? `
                        <h6><i class="bi bi-exclamation-circle text-danger me-2"></i>Productos con error:</h6>
                        <div style="max-height: 200px; overflow-y: auto;">
                            <ul class="list-group list-group-flush small">
                                ${data.errores.map(e => `
                                    <li class="list-group-item text-danger">
                                        <i class="bi bi-x-circle me-2"></i>
                                        ${e}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            `,
            icon: 'warning',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Cerrar'
        });
    }

    // Función para descargar reporte
    function descargarReporteAutomatizacion() {
        // Implementar descarga de Excel con los resultados
        showToast('Descargando reporte...', 'info');
        
        // Aquí puedes llamar a tu función de exportación
        setTimeout(() => {
            exportarExcelBajaDemanda();
        }, 500);
    }

    function exportarExcelBajaDemanda() {
        const tabla = document.getElementById('tablaIndiceRotacion');

        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }

        const datos = [];

        /* =========================
        ENCABEZADOS
        ========================== */
        const headers = ['ID', 'Sucursal'];

        tabla.querySelectorAll('thead th').forEach((th, index) => {
            // Saltar Check (0) e Imagen (1)
            if (index < 2) return;

            const texto = th.textContent.trim();

            if (!texto.toLowerCase().includes('accion') &&
                !texto.toLowerCase().includes('acción')) {

                headers.push(texto);

                // 👉 Insertar "Paralelo" justo después de PVP
                if (texto.toLowerCase().includes('pvp')) {
                    headers.push('Paralelo');
                }
            }
        });

        datos.push(headers);

        /* =========================
        FILAS
        ========================== */
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display === 'none') return;

            const rowData = [];

            // --- Columna A: ID producto y Sucursal ---
            const checkbox = fila.querySelector('.checkProductoRotacion');
            const productoId = checkbox ? checkbox.value : '';
            const sucursal = checkbox ? checkbox.dataset.sucursal : '';

            rowData.push(productoId);
            rowData.push(sucursal);

            // --- Valor Paralelo desde el DOM ---
            const paralelo = (() => {
                const paraleloEl = fila.querySelector('#paralelo-' + productoId);
                if (!paraleloEl) return '';
                return parseFloat(
                    paraleloEl.innerText
                        .replace('P:', '')
                        .replace('$', '')
                        .trim()
                ) || '';
            })();

            // --- Resto de columnas ---
            fila.querySelectorAll('td').forEach((td, index) => {
                if (index < 2) return;

                const th = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                if (!th) return;

                const textoTh = th.textContent.trim();

                if (textoTh.toLowerCase().includes('accion') ||
                    textoTh.toLowerCase().includes('acción')) {
                    return;
                }

                let texto = '';

                // ============================================
                // MANEJO ESPECIAL PARA LA COLUMNA PVP
                // ============================================
                if (textoTh.toLowerCase().includes('pvp')) {
                    // Buscar el precio real dentro de .precioPVP
                    const precioSpan = td.querySelector('.precioPVP');
                    if (precioSpan) {
                        texto = precioSpan.textContent.replace('$', '').trim();
                    } else {
                        // Fallback: buscar cualquier número con $
                        const pvpMatch = td.textContent.match(/\$([0-9.]+)/);
                        if (pvpMatch) {
                            texto = pvpMatch[1];
                        } else {
                            texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                        }
                    }
                    
                    // Convertir a número
                    const numero = parseFloat(texto.replace(',', '.'));
                    if (!isNaN(numero)) texto = numero;
                    
                    // Agregar el valor
                    rowData.push(texto);
                    
                    // Agregar Paralelo justo después
                    rowData.push(paralelo);
                    return; // Salir porque ya procesamos esta columna
                }
                
                // ============================================
                // MANEJO ESPECIAL PARA LA COLUMNA COSTO
                // ============================================
                if (textoTh.toLowerCase().includes('costo')) {
                    // Buscar el costo (es texto plano con $)
                    const costoMatch = td.textContent.match(/\$([0-9.]+)/);
                    if (costoMatch) {
                        texto = costoMatch[1];
                    } else {
                        texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                    }
                    
                    const numero = parseFloat(texto.replace(',', '.'));
                    if (!isNaN(numero)) texto = numero;
                    
                    rowData.push(texto);
                    return;
                }
                
                // ============================================
                // MANEJO GENERAL PARA OTRAS COLUMNAS
                // ============================================
                
                // Verificar si tiene badge (pero NO para PVP porque ya lo procesamos)
                const badge = td.querySelector('.badge');
                if (badge && !textoTh.toLowerCase().includes('pvp')) {
                    texto = badge.textContent.trim();
                } else {
                    texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                }

                // Índice
                if (textoTh.includes('Índice') || textoTh.includes('Indice')) {
                    const numero = parseFloat(texto.replace(',', '.'));
                    if (!isNaN(numero)) texto = numero;
                }

                rowData.push(texto);
            });

            datos.push(rowData);
        });

        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        /* =========================
        EXCEL
        ========================== */
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

        XLSX.utils.book_append_sheet(wb, ws, 'Productos Baja Demanda');

        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Productos_Baja_Demanda_${fecha}.xlsx`);
    }

    // // Función para exportar resultados a Excel
    // function exportarResultadosActualizacionExcel(data) {
    //     const sucursalNombre = data.sucursal_nombre || 'Todas';
    //     const fecha = new Date().toISOString().split('T')[0];
    //     const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
        
    //     // Preparar datos para el Excel
    //     const excelData = [];
        
    //     // Encabezados principales
    //     excelData.push(['REPORTE DE AUTOMATIZACIÓN DE PRECIOS']);
    //     excelData.push(['Sucursal:', sucursalNombre]);
    //     excelData.push(['Fecha:', new Date().toLocaleString()]);
    //     excelData.push(['Días de gracia:', data.dias_gracia || 30]);  // ✅ Corregido: 30 días
    //     excelData.push([]);
        
    //     // Resumen
    //     excelData.push(['RESUMEN GENERAL']);
    //     excelData.push(['Total productos analizados', data.total_analizados || 0]);
    //     excelData.push(['Productos afectados (actualizados)', data.productos_afectados || 0]);
    //     excelData.push(['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0]);
    //     excelData.push(['Saltados por reproceso', data.productos_saltados_reproceso || 0]);
    //     excelData.push([]);
        
    //     // Desglose por categoría (con Super Remate Total)
    //     const categorias = data.categorias || {};
    //     excelData.push(['DESGLOSE POR CATEGORÍA']);
    //     excelData.push(['Rotación Lenta (20%)', categorias.rotacionLenta || 0]);
    //     excelData.push(['Riesgo Estancamiento (35%)', categorias.riesgoEstancamiento || 0]);  // ✅ Corregido: 35%
    //     excelData.push(['Mercancía Crítica (50%)', categorias.mercanciaCritica || 0]);
    //     excelData.push(['Remate Total (100%)', categorias.remateTotal || 0]);
    //     excelData.push(['Super Remate Total (140%)', categorias.superRemateTotal || 0]);  // ✅ Agregado
    //     excelData.push(['Nueva Colección', categorias.nuevaColeccion || 0]);
    //     excelData.push(['Precios mantenidos', categorias.preciosMantenidos || 0]);
    //     excelData.push([]);
        
    //     // ============================================
    //     // PRODUCTOS ACTUALIZADOS
    //     // ============================================
    //     if (data.detalles && data.detalles.length > 0) {
    //         excelData.push(['PRODUCTOS ACTUALIZADOS (' + data.detalles.length + ')']);
    //         excelData.push(['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento', 'Costo', 'Existencia']);
            
    //         data.detalles.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.categoria || '',
    //                 d.precio_anterior || 0,
    //                 d.nuevo_precio || 0,
    //                 d.porcentaje_descuento || 0,
    //                 d.costo || 0,
    //                 d.existencia || 0
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // ============================================
    //     // PRODUCTOS MANTENIDOS
    //     // ============================================
    //     if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
    //         excelData.push(['PRODUCTOS MANTENIDOS (' + data.detalles_mantenidos.length + ') - Sin cambios por pérdida']);
    //         excelData.push(['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']);
            
    //         data.detalles_mantenidos.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.pvp_actual || d.precio_actual || 0,
    //                 d.costo || 0,
    //                 d.ganancia || 0,
    //                 d.razon || 'Producto en pérdida o sin ganancia'
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // ============================================
    //     // PRODUCTOS SALTADOS POR REPROCESO
    //     // ============================================
    //     if (data.detalles_saltados && data.detalles_saltados.length > 0) {
    //         excelData.push(['PRODUCTOS SALTADOS POR REPROCESO (' + data.detalles_saltados.length + ')']);
    //         excelData.push(['Código', 'Descripción', 'Fecha Último Cambio']);
            
    //         data.detalles_saltados.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.fecha_ultimo_cambio || 'N/A'
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // Crear y descargar Excel
    //     const wb = XLSX.utils.book_new();
    //     const ws = XLSX.utils.aoa_to_sheet(excelData);
        
    //     // Ajustar anchos de columna
    //     ws['!cols'] = [
    //         { wch: 25 }, { wch: 50 }, { wch: 25 }, { wch: 18 }, 
    //         { wch: 18 }, { wch: 12 }, { wch: 15 }, { wch: 12 }, { wch: 30 }
    //     ];
        
    //     XLSX.utils.book_append_sheet(wb, ws, 'Automatizacion');
        
    //     const nombreArchivo = `Automatizacion_${sucursalNombre}_${fecha}_${hora}.xlsx`;
    //     XLSX.writeFile(wb, nombreArchivo);
        
    //     Swal.fire({
    //         title: 'Exportación completada',
    //         text: `Archivo Excel generado: ${nombreArchivo}`,
    //         icon: 'success',
    //         timer: 2000,
    //         showConfirmButton: false
    //     });
    // }

    // Función para exportar resultados a Excel
    function exportarResultadosActualizacionExcel(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toISOString().split('T')[0];
        const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
        
        // Crear libro de Excel
        const wb = XLSX.utils.book_new();
        
        // ============================================
        // HOJA 1: RESUMEN GENERAL
        // ============================================
        const resumenData = [];
        
        resumenData.push(['REPORTE DE AUTOMATIZACIÓN DE PRECIOS']);
        resumenData.push(['Sucursal:', sucursalNombre]);
        resumenData.push(['Fecha:', new Date().toLocaleString()]);
        resumenData.push(['Días de gracia:', data.dias_gracia || 30]);
        resumenData.push([]);
        
        resumenData.push(['RESUMEN GENERAL']);
        resumenData.push(['Total productos analizados', data.total_analizados || 0]);
        resumenData.push(['Productos afectados (actualizados)', data.productos_afectados || 0]);
        resumenData.push(['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0]);
        resumenData.push(['Saltados por reproceso', data.productos_saltados_reproceso || 0]);
        resumenData.push([]);
        
        const categorias = data.categorias || {};
        resumenData.push(['DESGLOSE POR CATEGORÍA']);
        resumenData.push(['Rotación Lenta', categorias.rotacionLenta || 0]);
        resumenData.push(['Riesgo Estancamiento', categorias.riesgoEstancamiento || 0]);
        resumenData.push(['Mercancía Crítica', categorias.mercanciaCritica || 0]);
        resumenData.push(['Remate Total', categorias.remateTotal || 0]);
        resumenData.push(['Super Remate Total', categorias.superRemateTotal || 0]);
        resumenData.push(['Nueva Colección', categorias.nuevaColeccion || 0]);
        resumenData.push(['Precios mantenidos', categorias.preciosMantenidos || 0]);
        
        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        wsResumen['!cols'] = [{ wch: 30 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');
        
        // ============================================
        // HOJA 2: PRODUCTOS ACTUALIZADOS
        // ============================================
        if (data.detalles && data.detalles.length > 0) {
            const actualizadosData = [
                ['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento', 'Costo', 'Existencia']
            ];
            
            data.detalles.forEach(d => {
                actualizadosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.categoria || '',
                    d.precio_anterior || 0,
                    d.nuevo_precio || 0,
                    d.porcentaje_descuento || 0,
                    d.costo || 0,
                    d.existencia || 0
                ]);
            });
            
            const wsActualizados = XLSX.utils.aoa_to_sheet(actualizadosData);
            wsActualizados['!cols'] = [
                { wch: 15 }, { wch: 40 }, { wch: 25 }, 
                { wch: 15 }, { wch: 15 }, { wch: 10 }, 
                { wch: 12 }, { wch: 10 }
            ];
            XLSX.utils.book_append_sheet(wb, wsActualizados, 'Productos Actualizados');
        }
        
        // ============================================
        // HOJA 3: PRODUCTOS MANTENIDOS
        // ============================================
        if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
            const mantenidosData = [
                ['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']
            ];
            
            data.detalles_mantenidos.forEach(d => {
                mantenidosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.pvp_actual || d.precio_actual || 0,
                    d.costo || 0,
                    d.ganancia || 0,
                    d.razon || 'Producto en pérdida o sin ganancia'
                ]);
            });
            
            const wsMantenidos = XLSX.utils.aoa_to_sheet(mantenidosData);
            wsMantenidos['!cols'] = [
                { wch: 15 }, { wch: 40 }, { wch: 15 }, 
                { wch: 12 }, { wch: 12 }, { wch: 35 }
            ];
            XLSX.utils.book_append_sheet(wb, wsMantenidos, 'Productos Mantenidos');
        }
        
        // ============================================
        // HOJA 4: PRODUCTOS SALTADOS (opcional)
        // ============================================
        if (data.detalles_saltados && data.detalles_saltados.length > 0) {
            const saltadosData = [
                ['Código', 'Descripción', 'Fecha Último Cambio']
            ];
            
            data.detalles_saltados.forEach(d => {
                saltadosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.fecha_ultimo_cambio || 'N/A'
                ]);
            });
            
            const wsSaltados = XLSX.utils.aoa_to_sheet(saltadosData);
            wsSaltados['!cols'] = [{ wch: 15 }, { wch: 40 }, { wch: 20 }];
            XLSX.utils.book_append_sheet(wb, wsSaltados, 'Productos Saltados');
        }
        
        // Descargar Excel
        const nombreArchivo = `Automatizacion_${sucursalNombre}_${fecha}_${hora}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);
        
        Swal.fire({
            title: 'Exportación completada',
            text: `Archivo Excel generado: ${nombreArchivo}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Función para exportar resultados a PDF
    function exportarResultadosActualizacionPDF(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toLocaleString();
        const categorias = data.categorias || {};
        
        // Mostrar loading
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Crear documento PDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');
        
        // Título principal
        doc.setFontSize(16);
        doc.setTextColor(40, 167, 69);
        doc.text('REPORTE DE AUTOMATIZACIÓN DE PRECIOS', 14, 20);
        
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(`Sucursal: ${sucursalNombre}`, 14, 30);
        doc.text(`Fecha: ${fecha}`, 14, 36);
        doc.text(`Días de gracia: ${data.dias_gracia || 30} días`, 14, 42);
        
        let startY = 50;
        
        // ============================================
        // TABLA DE RESUMEN GENERAL
        // ============================================
        doc.setFontSize(12);
        doc.setTextColor(255, 255, 255);
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('RESUMEN GENERAL', 16, startY + 6);
        
        startY += 10;
        
        const resumenData = [
            ['Total productos analizados', data.total_analizados || 0],
            ['Productos afectados (actualizados)', data.productos_afectados || 0],
            ['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0],
            ['Saltados por reproceso', data.productos_saltados_reproceso || 0]
        ];
        
        doc.autoTable({
            startY: startY,
            head: [['Concepto', 'Cantidad']],
            body: resumenData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 5;
        
        // ============================================
        // TABLA DE DESGLOSE POR CATEGORÍA (CON SUPER REMATE TOTAL)
        // ============================================
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('DESGLOSE POR CATEGORÍA', 16, startY + 6);
        
        startY += 10;
        
        const categoriaData = [
            ['Rotación Lenta', categorias.rotacionLenta || 0],
            ['Riesgo Estancamiento', categorias.riesgoEstancamiento || 0],
            ['Mercancía Crítica', categorias.mercanciaCritica || 0],
            ['Remate Total', categorias.remateTotal || 0],
            ['Super Remate Total', categorias.superRemateTotal || 0],
            ['Nueva Colección', categorias.nuevaColeccion || 0]
        ];
        
        doc.autoTable({
            startY: startY,
            head: [['Categoría', 'Cantidad']],
            body: categoriaData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 10;
        
        // ============================================
        // PRODUCTOS ACTUALIZADOS
        // ============================================
        if (data.detalles && data.detalles.length > 0) {
            // Verificar si necesitamos nueva página
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(40, 167, 69);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS ACTUALIZADOS (${data.detalles.length})`, 16, startY + 6);
            
            startY += 10;
            
            // Limitar a 100 productos para evitar problemas de ancho
            const productosData = data.detalles.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                d.categoria || '',
                `$${d.precio_anterior || 0}`,
                `$${d.nuevo_precio || 0}`,
                `-${d.porcentaje_descuento || 0}%`
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento']],
                body: productosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 30 },
                    3: { cellWidth: 25 },
                    4: { cellWidth: 25 },
                    5: { cellWidth: 20 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // ============================================
        // PRODUCTOS MANTENIDOS
        // ============================================
        if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
            // Verificar si necesitamos nueva página
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(255, 152, 0);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS MANTENIDOS (${data.detalles_mantenidos.length}) - Sin cambios por pérdida`, 16, startY + 6);
            
            startY += 10;
            
            const mantenidosData = data.detalles_mantenidos.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                `$${d.pvp_actual || d.precio_actual || 0}`,
                `$${d.costo || 0}`,
                `$${d.ganancia || 0}`,
                d.razon || 'Producto en pérdida'
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']],
                body: mantenidosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 20 },
                    3: { cellWidth: 20 },
                    4: { cellWidth: 20 },
                    5: { cellWidth: 40 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(
                `Reporte generado automáticamente - TiendasTenShop | Página ${i} de ${totalPaginas}`,
                doc.internal.pageSize.width / 2,
                doc.internal.pageSize.height - 10,
                { align: 'center' }
            );
        }
        
        // Cerrar loading y descargar
        Swal.close();
        
        const nombreArchivo = `Automatizacion_${sucursalNombre}_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(nombreArchivo);
        
        Swal.fire({
            title: 'Exportación completada',
            text: `Archivo PDF generado para ${sucursalNombre}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // ============================================
    // EXPORTAR RESULTADOS DE AUTOMATIZACIÓN (usando datos guardados)
    // ============================================

    // // Función para exportar resultados a Excel desde datos guardados
    // function exportarResultadosAutomatizacionExcel(data) {
    //     const sucursalNombre = data.sucursal_nombre || 'Todas';
    //     const fecha = new Date().toISOString().split('T')[0];
    //     const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
        
    //     // Preparar datos para el Excel
    //     const excelData = [];
        
    //     // Encabezados principales
    //     excelData.push(['REPORTE DE AUTOMATIZACIÓN DE PRECIOS']);
    //     excelData.push(['Sucursal:', sucursalNombre]);
    //     excelData.push(['Fecha:', new Date().toLocaleString()]);
    //     excelData.push(['Días de gracia:', data.dias_gracia || 30]);
    //     excelData.push([]);
        
    //     // Resumen
    //     excelData.push(['RESUMEN GENERAL']);
    //     excelData.push(['Total productos analizados', data.total_analizados || 0]);
    //     excelData.push(['Productos afectados (actualizados)', data.productos_afectados || 0]);
    //     excelData.push(['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0]);
    //     excelData.push(['Saltados por reproceso', data.productos_saltados_reproceso || 0]);
    //     excelData.push([]);
        
    //     // Desglose por categoría
    //     const categorias = data.categorias || {};
    //     excelData.push(['DESGLOSE POR CATEGORÍA']);
    //     excelData.push(['Rotación Lenta (20%)', categorias.rotacionLenta || 0]);
    //     excelData.push(['Riesgo Estancamiento (35%)', categorias.riesgoEstancamiento || 0]);
    //     excelData.push(['Mercancía Crítica (50%)', categorias.mercanciaCritica || 0]);
    //     excelData.push(['Remate Total (100%)', categorias.remateTotal || 0]);
    //     excelData.push(['Super Remate Total (140%)', categorias.superRemateTotal || 0]);
    //     excelData.push(['Nueva Colección', categorias.nuevaColeccion || 0]);
    //     excelData.push(['Precios mantenidos', categorias.preciosMantenidos || 0]);
    //     excelData.push([]);
        
    //     // Productos actualizados
    //     if (data.detalles && data.detalles.length > 0) {
    //         excelData.push(['PRODUCTOS ACTUALIZADOS (' + data.detalles.length + ')']);
    //         excelData.push(['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento', 'Costo', 'Existencia']);
            
    //         data.detalles.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.categoria || '',
    //                 d.precio_anterior || 0,
    //                 d.nuevo_precio || 0,
    //                 d.porcentaje_descuento || 0,
    //                 d.costo || 0,
    //                 d.existencia || 0
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // Productos mantenidos
    //     if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
    //         excelData.push(['PRODUCTOS MANTENIDOS (' + data.detalles_mantenidos.length + ') - Sin cambios por pérdida']);
    //         excelData.push(['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']);
            
    //         data.detalles_mantenidos.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.pvp_actual || d.precio_actual || 0,
    //                 d.costo || 0,
    //                 d.ganancia || 0,
    //                 d.razon || 'Producto en pérdida o sin ganancia'
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // Productos saltados
    //     if (data.detalles_saltados && data.detalles_saltados.length > 0) {
    //         excelData.push(['PRODUCTOS SALTADOS POR REPROCESO (' + data.detalles_saltados.length + ')']);
    //         excelData.push(['Código', 'Descripción', 'Fecha Último Cambio']);
            
    //         data.detalles_saltados.forEach(d => {
    //             excelData.push([
    //                 d.codigo || 'N/A',
    //                 d.descripcion || '',
    //                 d.fecha_ultimo_cambio || 'N/A'
    //             ]);
    //         });
    //         excelData.push([]);
    //     }
        
    //     // Crear y descargar Excel
    //     const wb = XLSX.utils.book_new();
    //     const ws = XLSX.utils.aoa_to_sheet(excelData);
    //     ws['!cols'] = [{ wch: 25 }, { wch: 50 }, { wch: 25 }, { wch: 18 }, { wch: 18 }, { wch: 12 }, { wch: 15 }, { wch: 12 }];
    //     XLSX.utils.book_append_sheet(wb, ws, 'Automatizacion');
        
    //     const nombreArchivo = `Automatizacion_${sucursalNombre}_${fecha}_${hora}.xlsx`;
    //     XLSX.writeFile(wb, nombreArchivo);
        
    //     Swal.fire({
    //         title: 'Exportación completada',
    //         text: `Archivo Excel generado: ${nombreArchivo}`,
    //         icon: 'success',
    //         timer: 2000,
    //         showConfirmButton: false
    //     });
    // }

    // Función para exportar resultados a Excel desde datos guardados
    function exportarResultadosAutomatizacionExcel(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toISOString().split('T')[0];
        const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
        
        // Crear libro de Excel
        const wb = XLSX.utils.book_new();
        
        // ============================================
        // HOJA 1: RESUMEN GENERAL
        // ============================================
        const resumenData = [];
        
        resumenData.push(['REPORTE DE AUTOMATIZACIÓN DE PRECIOS']);
        resumenData.push(['Sucursal:', sucursalNombre]);
        resumenData.push(['Fecha:', new Date().toLocaleString()]);
        resumenData.push(['Días de gracia:', data.dias_gracia || 30]);
        resumenData.push([]);
        
        resumenData.push(['RESUMEN GENERAL']);
        resumenData.push(['Total productos analizados', data.total_analizados || 0]);
        resumenData.push(['Productos afectados (actualizados)', data.productos_afectados || 0]);
        resumenData.push(['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0]);
        resumenData.push(['Saltados por reproceso', data.productos_saltados_reproceso || 0]);
        resumenData.push([]);
        
        const categorias = data.categorias || {};
        resumenData.push(['DESGLOSE POR CATEGORÍA']);
        resumenData.push(['Rotación Lenta', categorias.rotacionLenta || 0]);
        resumenData.push(['Riesgo Estancamiento', categorias.riesgoEstancamiento || 0]);
        resumenData.push(['Mercancía Crítica', categorias.mercanciaCritica || 0]);
        resumenData.push(['Remate Total ', categorias.remateTotal || 0]);
        resumenData.push(['Super Remate Total ', categorias.superRemateTotal || 0]);
        resumenData.push(['Nueva Colección', categorias.nuevaColeccion || 0]);
        resumenData.push(['Precios mantenidos', categorias.preciosMantenidos || 0]);
        
        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        wsResumen['!cols'] = [{ wch: 30 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');
        
        // ============================================
        // HOJA 2: PRODUCTOS ACTUALIZADOS
        // ============================================
        if (data.detalles && data.detalles.length > 0) {
            const actualizadosData = [
                ['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento', 'Costo', 'Existencia']
            ];
            
            data.detalles.forEach(d => {
                actualizadosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.categoria || '',
                    d.precio_anterior || 0,
                    d.nuevo_precio || 0,
                    d.porcentaje_descuento || 0,
                    d.costo || 0,
                    d.existencia || 0
                ]);
            });
            
            const wsActualizados = XLSX.utils.aoa_to_sheet(actualizadosData);
            wsActualizados['!cols'] = [
                { wch: 15 }, { wch: 40 }, { wch: 25 }, 
                { wch: 15 }, { wch: 15 }, { wch: 10 }, 
                { wch: 12 }, { wch: 10 }
            ];
            XLSX.utils.book_append_sheet(wb, wsActualizados, 'Productos Actualizados');
        }
        
        // ============================================
        // HOJA 3: PRODUCTOS MANTENIDOS
        // ============================================
        if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
            const mantenidosData = [
                ['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']
            ];
            
            data.detalles_mantenidos.forEach(d => {
                mantenidosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.pvp_actual || d.precio_actual || 0,
                    d.costo || 0,
                    d.ganancia || 0,
                    d.razon || 'Producto en pérdida o sin ganancia'
                ]);
            });
            
            const wsMantenidos = XLSX.utils.aoa_to_sheet(mantenidosData);
            wsMantenidos['!cols'] = [
                { wch: 15 }, { wch: 40 }, { wch: 15 }, 
                { wch: 12 }, { wch: 12 }, { wch: 35 }
            ];
            XLSX.utils.book_append_sheet(wb, wsMantenidos, 'Productos Mantenidos');
        }
        
        // ============================================
        // HOJA 4: PRODUCTOS SALTADOS (opcional)
        // ============================================
        if (data.detalles_saltados && data.detalles_saltados.length > 0) {
            const saltadosData = [
                ['Código', 'Descripción', 'Fecha Último Cambio']
            ];
            
            data.detalles_saltados.forEach(d => {
                saltadosData.push([
                    d.codigo || 'N/A',
                    d.descripcion || '',
                    d.fecha_ultimo_cambio || 'N/A'
                ]);
            });
            
            const wsSaltados = XLSX.utils.aoa_to_sheet(saltadosData);
            wsSaltados['!cols'] = [{ wch: 15 }, { wch: 40 }, { wch: 20 }];
            XLSX.utils.book_append_sheet(wb, wsSaltados, 'Productos Saltados');
        }
        
        // Descargar Excel
        const nombreArchivo = `Automatizacion_${sucursalNombre}_${fecha}_${hora}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);
        
        Swal.fire({
            title: 'Exportación completada',
            text: `Archivo Excel generado: ${nombreArchivo}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Función para exportar resultados a PDF desde datos guardados (CORREGIDA)
    function exportarResultadosAutomatizacionPDF(data) {
        const sucursalNombre = data.sucursal_nombre || 'Todas';
        const fecha = new Date().toLocaleString();
        const categorias = data.categorias || {};
        
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'mm', 'a4');
        
        // Título principal
        doc.setFontSize(16);
        doc.setTextColor(40, 167, 69);
        doc.text('REPORTE DE AUTOMATIZACIÓN DE PRECIOS', 14, 20);
        
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(`Sucursal: ${sucursalNombre}`, 14, 30);
        doc.text(`Fecha: ${fecha}`, 14, 36);
        doc.text(`Días de gracia: ${data.dias_gracia || 30} días`, 14, 42);
        
        let startY = 50;
        
        // ============================================
        // TABLA DE RESUMEN GENERAL
        // ============================================
        doc.setFontSize(12);
        doc.setTextColor(255, 255, 255);
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('RESUMEN GENERAL', 16, startY + 6);
        
        startY += 10;
        
        const resumenData = [
            ['Total productos analizados', data.total_analizados || 0],
            ['Productos afectados (actualizados)', data.productos_afectados || 0],
            ['Precios mantenidos (sin cambios)', data.productos_mantenidos || 0],
            ['Saltados por reproceso', data.productos_saltados_reproceso || 0]
        ];
        
        doc.autoTable({
            startY: startY,
            head: [['Concepto', 'Cantidad']],
            body: resumenData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 5;
        
        // ============================================
        // TABLA DE DESGLOSE POR CATEGORÍA
        // ============================================
        doc.setFillColor(40, 167, 69);
        doc.rect(14, startY, 180, 8, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('DESGLOSE POR CATEGORÍA', 16, startY + 6);
        
        startY += 10;
        
        const categoriaData = [
            ['Rotación Lenta', categorias.rotacionLenta || 0],
            ['Riesgo Estancamiento', categorias.riesgoEstancamiento || 0],
            ['Mercancía Crítica', categorias.mercanciaCritica || 0],
            ['Remate Total', categorias.remateTotal || 0],
            ['Super Remate Total', categorias.superRemateTotal || 0],
            ['Nueva Colección', categorias.nuevaColeccion || 0]
        ];
        
        doc.autoTable({
            startY: startY,
            head: [['Categoría', 'Cantidad']],
            body: categoriaData,
            theme: 'striped',
            headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            margin: { left: 14 },
            tableWidth: 90
        });
        
        startY = doc.lastAutoTable.finalY + 10;
        
        // ============================================
        // PRODUCTOS ACTUALIZADOS (TODOS - SIN LÍMITE)
        // ============================================
        if (data.detalles && data.detalles.length > 0) {
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(40, 167, 69);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS ACTUALIZADOS (${data.detalles.length})`, 16, startY + 6);
            
            startY += 10;
            
            // ✅ SIN LÍMITE - todos los productos
            const productosData = data.detalles.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                d.categoria || '',
                `$${parseFloat(d.precio_anterior).toFixed(2)}`,
                `$${parseFloat(d.nuevo_precio).toFixed(2)}`,
                `-${d.porcentaje_descuento || 0}%`
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Categoría', 'Precio Anterior', 'Nuevo Precio', 'Descuento']],
                body: productosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 30 },
                    3: { cellWidth: 25 },
                    4: { cellWidth: 25 },
                    5: { cellWidth: 20 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // ============================================
        // PRODUCTOS MANTENIDOS (TODOS - SIN LÍMITE)
        // ============================================
        if (data.detalles_mantenidos && data.detalles_mantenidos.length > 0) {
            if (startY > 250) {
                doc.addPage();
                startY = 20;
            }
            
            doc.setFillColor(255, 152, 0);
            doc.rect(14, startY, 260, 8, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text(`PRODUCTOS MANTENIDOS (${data.detalles_mantenidos.length}) - Sin cambios por pérdida`, 16, startY + 6);
            
            startY += 10;
            
            // ✅ SIN LÍMITE - todos los productos
            const mantenidosData = data.detalles_mantenidos.map(d => ([
                d.codigo || 'N/A',
                (d.descripcion || '').substring(0, 30),
                `$${parseFloat(d.pvp_actual || d.precio_actual || 0).toFixed(2)}`,
                `$${parseFloat(d.costo || 0).toFixed(2)}`,
                `$${parseFloat(d.ganancia || 0).toFixed(2)}`,
                d.razon || 'Producto en pérdida'
            ]));
            
            doc.autoTable({
                startY: startY,
                head: [['Código', 'Descripción', 'Precio Actual', 'Costo', 'Ganancia', 'Razón']],
                body: mantenidosData,
                theme: 'striped',
                headStyles: { fillColor: [52, 58, 64], textColor: 255, fontSize: 9 },
                bodyStyles: { fontSize: 8 },
                columnStyles: {
                    0: { cellWidth: 20 },
                    1: { cellWidth: 50 },
                    2: { cellWidth: 20 },
                    3: { cellWidth: 20 },
                    4: { cellWidth: 20 },
                    5: { cellWidth: 40 }
                },
                margin: { left: 14 }
            });
            
            startY = doc.lastAutoTable.finalY + 10;
        }
        
        // Pie de página
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(
                `Reporte generado automáticamente - TiendasTenShop | Página ${i} de ${totalPaginas}`,
                doc.internal.pageSize.width / 2,
                doc.internal.pageSize.height - 10,
                { align: 'center' }
            );
        }
        
        Swal.close();
        const nombreArchivo = `Automatizacion_${sucursalNombre}_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(nombreArchivo);
        
        Swal.fire({
            title: 'Exportación completada',
            text: `Archivo PDF generado para ${sucursalNombre}`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
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

    /* Animación de pulso para el botón */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            transform: scale(1);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(40, 167, 69, 0);
            transform: scale(1.05);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            transform: scale(1);
        }
    }

    .animate-pulse {
        animation: pulse 1.5s infinite;
    }

    /* Hover efecto para el botón */
    #btnEjecutarAutomatizacion:hover {
        transform: scale(1.1);
        transition: transform 0.3s ease;
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.5);
    }

    /* Al final de tu style */
    .rounded-4 {
        border-radius: 16px !important;
    }

    /* Animar el botón de confirmación */
    .swal2-confirm {
        transition: all 0.3s ease;
    }

    .swal2-confirm:hover {
        transform: scale(1.02);
    }
</style>
@endsection