@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Comparativa entre Sucursales')

@php
    use App\Helpers\FileHelper;
    
    $sucursales = [];

    // Limitar a primeros 10 registros
    $detallesMostrar = $detalles;
    $totalRegistros = count($detalles);
    
    // Configuración para análisis
    $config = [
        'stock_minimo' => 5,           // Stock mínimo de seguridad
        'stock_optimo' => 15,          // Stock óptimo
        'umbral_transferencia' => 10,  // Diferencia para sugerir transferencia
        'umbral_reposicion' => 3,      // Stock por debajo del cual reponer urgente
    ];
    
    // Estadísticas mejoradas
    $estadisticas = [
        'necesita_reposicion' => 0,
        'puede_transferir' => 0,
        'con_diferencias' => 0,
        'equilibrado' => 0,
    ];
    
    $totalVentas = 0;
    
    // Analizar cada producto
    $productosAnalizados = [];
    
    foreach($detallesMostrar as $detalle) {
        // Recorrer todas las propiedades del detalle
        foreach(get_object_vars($detalle) as $prop => $value) {
            // Solo nos interesan las propiedades que empiezan con 'Cantidad'
            if (str_starts_with($prop, 'Cantidad')) {
                $nombre = substr($prop, 8); // Quita 'Cantidad' para obtener 'Calzatodo'

                // Verifica si ya existe para no duplicar
                if (!isset($sucursales[$nombre])) {
                    $sucursales[$nombre] = [
                        'nombre' => $nombre,
                        'color' => '#'.substr(md5($nombre),0,6), // color automático
                        'icon' => 'store' // o asignar según lógica
                    ];
                }
            }
        }

        // Solo nos interesan las propiedades que empiezan con 'Cantidad'
        if (str_starts_with($prop, 'Cantidad')) {
            $nombre = substr($prop, 8); // Quita 'Cantidad' para obtener 'Calzatodo'

            // Verifica si ya existe para no duplicar
            if (!isset($sucursales[$nombre])) {
                $sucursales[$nombre] = [
                    'nombre' => $nombre,
                    'color' => '#'.substr(md5($nombre),0,6), // color automático
                    'icon' => 'store' // o asignar según lógica
                ];
            }
        }
        
        // Datos del producto
        $existencias = [];
        $ventas = [];
        
        foreach($sucursales as $suc) {
            $nombreCol = str_replace(' ', '', $suc['nombre']);
            $existencias[$suc['nombre']] = $detalle->{'Existencia'.$nombreCol} ?? 0;
            $ventas[$suc['nombre']] = $detalle->{'Cantidad'.$nombreCol} ?? 0;
        }
        
        // Calcular estado del producto
        $totalExistencia = array_sum($existencias);
        $totalVentasProducto = 0;
        foreach($sucursales as $suc) {
            $nombreCol = str_replace(' ', '', $suc['nombre']);
            $totalVentasProducto += $detalle->{'TotalDivisas'.$nombreCol} ?? 0;
        }
        
        $totalVentas += $totalVentasProducto;
        
        // Determinar estado del producto
        $estadoProducto = 'equilibrado';
        $claseFila = '';
        $mensajeEstado = '';
        $iconoEstado = '';
        
        // 1. Verificar si necesita reposición urgente (ROJO)
        $sucursalesBajoStock = [];
        foreach($existencias as $sucursal => $existencia) {
            if ($existencia <= $config['umbral_reposicion']) {
                $sucursalesBajoStock[] = $sucursal;
            }
        }
        
        if (count($sucursalesBajoStock) >= 2 || $totalExistencia <= $config['stock_minimo']) {
            $estadoProducto = 'necesita_reposicion';
            $claseFila = 'table-danger';
            $mensajeEstado = '⚠️ Reponer urgente';
            $iconoEstado = 'fa-exclamation-circle text-danger';
            $estadisticas['necesita_reposicion']++;
        }
        // 2. Verificar si puede transferir (AMARILLO/INFO)
        else {
            // Calcular desbalance de inventario
            $existenciasPositivas = array_filter($existencias, function($e) {
                return $e > 0;
            });
            
            if (count($existenciasPositivas) >= 2) {
                $max = max($existenciasPositivas);
                $min = min($existenciasPositivas);
                $diferencia = $max - $min;
                
                // Encontrar sucursales con más y menos stock
                $sucursalMax = array_search($max, $existencias);
                $sucursalMin = array_search($min, $existencias);
                
                // Verificar si hay ventas en la sucursal con poco stock
                $ventaSucursalMin = $ventas[$sucursalMin] ?? 0;
                $ventaSucursalMax = $ventas[$sucursalMax] ?? 0;
                
                if ($diferencia >= $config['umbral_transferencia']) {
                    if ($ventaSucursalMin > 0) {
                        // La sucursal con poco stock SÍ vende → necesita transferencia
                        $estadoProducto = 'puede_transferir';
                        $claseFila = 'table-warning';
                        $mensajeEstado = "🔄 Transferir de $sucursalMax a $sucursalMin";
                        $iconoEstado = 'fa-exchange-alt text-warning';
                        $estadisticas['puede_transferir']++;
                    } elseif ($ventaSucursalMax == 0) {
                        // La sucursal con mucho stock NO vende → considerar devolución
                        $estadoProducto = 'puede_transferir';
                        $claseFila = 'table-info';
                        $mensajeEstado = "📦 $sucursalMax no vende pero tiene stock";
                        $iconoEstado = 'fa-box text-info';
                        $estadisticas['puede_transferir']++;
                    } else {
                        // Solo desbalance sin criterio claro
                        $estadoProducto = 'con_diferencias';
                        $claseFila = 'table-secondary';
                        $mensajeEstado = "⚖️ Desbalance: {$diferencia} unidades";
                        $iconoEstado = 'fa-balance-scale text-secondary';
                        $estadisticas['con_diferencias']++;
                    }
                } else {
                    $estadisticas['equilibrado']++;
                }
            } else {
                $estadisticas['equilibrado']++;
            }
        }
        
        // Guardar análisis del producto
        $productosAnalizados[] = [
            'detalle' => $detalle,
            'estado' => $estadoProducto,
            'clase_fila' => $claseFila,
            'mensaje_estado' => $mensajeEstado,
            'icono_estado' => $iconoEstado,
            'existencias' => $existencias,
            'ventas' => $ventas,
            'total_existencia' => $totalExistencia,
        ];
    }
    
    // Totales para pie de tabla
    $totales = [
        'cantidades' => [],
        'existencias' => [],
        'ventas' => []
    ];
    
    foreach($sucursales as $sucursal) {
        $nombreCol = str_replace(' ', '', $sucursal['nombre']);
        $totales['cantidades'][$sucursal['nombre']] = array_sum(array_column($detallesMostrar, 'Cantidad'.$nombreCol));
        $totales['existencias'][$sucursal['nombre']] = array_sum(array_column($detallesMostrar, 'Existencia'.$nombreCol));
        $totales['ventas'][$sucursal['nombre']] = array_sum(array_column($detallesMostrar, 'TotalDivisas'.$nombreCol));
    }
    
    // Promedios PVP
    $promediosPvp = [];
    foreach($sucursales as $sucursal) {
        $columna = 'PvpDivisa' . str_replace(' ', '', $sucursal['nombre']);
        $valores = array_column($detallesMostrar, $columna);
        $promediosPvp[$sucursal['nombre']] = count($valores) > 0 ? array_sum($valores) / count($valores) : 0;
    }
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Comparativa entre Sucursales</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Comparativa</li>
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
    <!--begin::Container-->
    <div class="container-fluid">
        
        <!-- Panel de Filtros Simplificado -->
        <!-- En la sección del panel de filtros -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="card-title mb-0">
                            <strong>Comparativa entre Sucursales</strong>
                        </h5>
                    </div>
                    <div class="col-md-8">
                        <div class="card-tools">
                            <div class="d-flex align-items-center">
                                @php
                                    $filtroEstado = request('filtro_estado', 'todos');
                                @endphp

                                <!-- Filtro de estado (JavaScript) -->
                                <select id="filtro_estado" class="form-select form-select-sm me-2" style="min-width: 180px;">
                                    <option value="todos">📋 Todos los registros</option>
                                    <option value="reponer">🔴 Reponer urgente</option>
                                    <option value="transferir">🟡 Transferir stock</option>
                                    <option value="revisar">🔵 Revisar stock</option>
                                    <option value="desbalance">⚪ Solo desbalance</option>
                                    <option value="equilibrado">✅ Equilibrado</option>
                                </select>

                                <!-- Formulario para fechas (solo para fechas) -->
                                <form method="GET" action="{{ route('cpanel.comparativa.sucursales') }}" class="d-flex align-items-center">
                                    @php
                                        $fechaInicioInput = request('fecha_inicio', $fechaInicio->format('Y-m-d'));
                                        $fechaFinInput = request('fecha_fin', $fechaFin->format('Y-m-d'));
                                    @endphp

                                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                                        class="form-control form-control-sm me-1" 
                                        value="{{ $fechaInicioInput }}"
                                        style="max-width: 150px;">
                                    
                                    <input type="date" id="fecha_fin" name="fecha_fin" 
                                        class="form-control form-control-sm me-2" 
                                        value="{{ $fechaFinInput }}"
                                        style="max-width: 150px;">

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-search me-1"></i> Buscar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estado Mejoradas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-danger border-3 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0 small">Reponer Urgente</h6>
                                <h2 class="mb-0 mt-2 fw-bold text-danger">{{ $estadisticas['necesita_reposicion'] }}</h2>
                                <p class="text-muted small mb-0">Stock ≤ {{ $config['umbral_reposicion'] }} unidades</p>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-warning border-3 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0 small">Transferir Stock</h6>
                                <h2 class="mb-0 mt-2 fw-bold text-warning">{{ $estadisticas['puede_transferir'] }}</h2>
                                <p class="text-muted small mb-0">Desbalance ≥ {{ $config['umbral_transferencia'] }} unidades</p>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-exchange-alt fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-success border-3 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0 small">Productos Equilibrados</h6>
                                <h2 class="mb-0 mt-2 fw-bold text-success">{{ $estadisticas['equilibrado'] }}</h2>
                                <p class="text-muted small mb-0">Situación óptima</p>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-primary border-3 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0 small">Total Ventas</h6>
                                <h2 class="mb-0 mt-2 fw-bold text-primary">${{ number_format($totalVentas, 2) }}</h2>
                                <p class="text-muted small mb-0">En el período</p>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda de Estados -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger me-2" style="width: 20px; height: 20px;"></span>
                        <span class="small">Reponer Urgente (Stock muy bajo)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning me-2" style="width: 20px; height: 20px;"></span>
                        <span class="small">Transferir (Desbalance con ventas)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-2" style="width: 20px; height: 20px;"></span>
                        <span class="small">Revisar (Stock alto sin ventas)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2" style="width: 20px; height: 20px;"></span>
                        <span class="small">Solo desbalance</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Sucursales -->
        <div class="row mb-4">
            @foreach($sucursales as $id => $sucursal)
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $sucursal['color'] }} !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle p-3" style="background-color: {{ $sucursal['color'] }}20;">
                                    <i class="fas fa-{{ $sucursal['icon'] }} fa-lg" style="color: {{ $sucursal['color'] }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 fw-bold">{{ $sucursal['nombre'] }}</h6>
                                <div class="text-muted small">
                                    @php
                                        $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                        $totalVentasSuc = array_sum(array_column($detallesMostrar, 'TotalDivisas' . $nombreCol));
                                        $totalExistencias = array_sum(array_column($detallesMostrar, 'Existencia' . $nombreCol));
                                    @endphp
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Ventas:</span>
                                        <span class="fw-bold text-success">${{ number_format($totalVentasSuc, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Inventario:</span>
                                        <span class="fw-bold" style="color: {{ $sucursal['color'] }}">{{ $totalExistencias }} und.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tabla de Comparación -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Comparativa de Productos con Análisis Inteligente
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" id="exportExcel">
                            <i class="fas fa-file-excel me-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="comparativaTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center align-middle" style="min-width: 50px;">#</th>
                                <th class="align-middle" style="min-width: 350px;">
                                    <i class="fas fa-box me-1"></i> Producto
                                </th>
                                
                                <!-- Ventas Cantidad -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-primary bg-opacity-10">
                                    <div class="text-primary fw-bold">
                                        <i class="fas fa-chart-bar me-1"></i> VENTAS (Cantidad)
                                    </div>
                                </th>
                                
                                <!-- Existencias -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-success bg-opacity-10">
                                    <div class="text-success fw-bold">
                                        <i class="fas fa-warehouse me-1"></i> EXISTENCIAS
                                    </div>
                                </th>
                                
                                <!-- Ventas $ -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-info bg-opacity-10">
                                    <div class="text-info fw-bold">
                                        <i class="fas fa-money-bill-wave me-1"></i> VENTAS ($)
                                    </div>
                                </th>
                                
                                <!-- PVP $ -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-warning bg-opacity-10">
                                    <div class="text-warning fw-bold">
                                        <i class="fas fa-tag me-1"></i> PVP ($)
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                
                                <!-- Encabezados de sucursales para Ventas Cantidad -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small">
                                    <i class="fas fa-store me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para Existencias -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small">
                                    <i class="fas fa-box me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para Ventas $ -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small">
                                    <i class="fas fa-dollar-sign me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para PVP $ -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small">
                                    <i class="fas fa-tag me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productosAnalizados as $index => $producto)
                            @php
                                $detalle = $producto['detalle'];
                                
                                // Obtener imagen con FileHelper
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle->producto['UrlFoto'] ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                            @endphp
                            
                            <tr class="{{ $producto['clase_fila'] }}">
                                <td class="text-center fw-bold align-middle">
                                    {{ $index + 1 }}
                                    @if($producto['icono_estado'])
                                        <br>
                                        <i class="fas {{ $producto['icono_estado'] }} small" title="{{ $producto['mensaje_estado'] }}"></i>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <img src="{{ $urlImagen }}" 
                                                class="img-thumbnail rounded img-zoomable" 
                                                style="width: 60px; height: 60px; object-fit:cover; cursor: zoom-in;"
                                                alt="{{ $detalle->producto['Descripcion'] }}"
                                                data-full-image="{{ $urlImagen }}"
                                                data-description="{{ $detalle->producto['Descripcion'] }} - Código: {{ $detalle->producto['Codigo'] }}">
                                            @if($producto['total_existencia'] == 0)
                                                <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;">
                                                    0
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">{{ $detalle->producto['Descripcion'] }}</h6>
                                            <div class="text-muted small">
                                                <div class="mb-1">
                                                    <i class="fas fa-barcode me-1"></i> {{ $detalle->producto['Codigo'] }}
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                                        <i class="fas fa-dollar-sign me-1"></i> 
                                                        ${{ number_format($detalle->producto['CostoDivisa'], 2) }}
                                                    </span>
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-box me-1"></i> 
                                                        {{ $producto['total_existencia'] }} und.
                                                    </span>
                                                    @if($producto['mensaje_estado'])
                                                        <span class="badge {{ $producto['estado'] == 'necesita_reposicion' ? 'bg-danger' : ($producto['estado'] == 'puede_transferir' ? 'bg-warning' : 'bg-secondary') }}">
                                                            {{ $producto['mensaje_estado'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- VENTAS Cantidad -->
                                @foreach($sucursales as $sucursal)
                                @php
                                    $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                    $cantidad = $detalle->{'Cantidad'.$nombreCol} ?? 0;
                                    $tieneVentas = $cantidad > 0;
                                    $existencia = $detalle->{'Existencia'.$nombreCol} ?? 0;
                                    $claseCelda = $tieneVentas ? 'fw-bold text-primary' : 'text-muted';
                                    
                                    // Resaltar si hay ventas pero poco stock
                                    if ($tieneVentas && $existencia <= $config['umbral_reposicion']) {
                                        $claseCelda .= ' bg-danger bg-opacity-25';
                                    }
                                @endphp
                                <td class="text-center align-middle {{ $claseCelda }}">
                                    <div class="{{ $tieneVentas ? 'bg-primary bg-opacity-10 rounded py-1 px-2' : '' }}">
                                        {{ $cantidad }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- EXISTENCIAS -->
                                @foreach($sucursales as $sucursal)
                                @php
                                    $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                    $existencia = $detalle->{'Existencia'.$nombreCol} ?? 0;
                                    $tieneExistencia = $existencia > 0;
                                    $claseCelda = $tieneExistencia ? 'fw-bold text-success' : 'text-muted';
                                    
                                    // Resaltar según nivel de stock
                                    if ($existencia == 0) {
                                        $claseCelda = 'bg-light text-muted';
                                    } elseif ($existencia <= $config['umbral_reposicion']) {
                                        $claseCelda = 'bg-danger bg-opacity-25 fw-bold';
                                    } elseif ($existencia <= $config['stock_minimo']) {
                                        $claseCelda = 'bg-warning bg-opacity-25 fw-bold text-dark';
                                    }
                                @endphp
                                <td class="text-center align-middle {{ $claseCelda }}">
                                    <div class="rounded py-1 px-2">
                                        {{ $existencia }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- VENTAS $ -->
                                @foreach($sucursales as $sucursal)
                                @php
                                    $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                    $montoVenta = $detalle->{'TotalDivisas'.$nombreCol} ?? 0;
                                @endphp
                                <td class="text-center align-middle fw-bold" style="color: #10b981;">
                                    <div class="bg-success bg-opacity-10 rounded py-1 px-2">
                                        ${{ number_format($montoVenta, 2) }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- PVP $ -->
                                @foreach($sucursales as $sucursal)
                                @php
                                    $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                    $pvp = $detalle->{'PvpDivisa'.$nombreCol} ?? 0;
                                @endphp
                                <td class="text-center align-middle text-primary">
                                    <div class="bg-primary bg-opacity-10 rounded py-1 px-2">
                                        ${{ number_format($pvp, 2) }}
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr class="table-active">
                                <td colspan="2" class="text-end fw-bold">
                                    <div class="text-nowrap">TOTALES / PROMEDIOS:</div>
                                </td>
                                
                                <!-- Totales Ventas Cantidad -->
                                @foreach($totales['cantidades'] as $total)
                                <td class="text-center fw-bold bg-primary bg-opacity-25 text-white">
                                    {{ $total }}
                                </td>
                                @endforeach
                                
                                <!-- Totales Existencias -->
                                @foreach($totales['existencias'] as $total)
                                <td class="text-center fw-bold bg-success bg-opacity-25 text-white">
                                    {{ $total }}
                                </td>
                                @endforeach
                                
                                <!-- Totales Ventas $ -->
                                @foreach($totales['ventas'] as $total)
                                <td class="text-center fw-bold bg-info bg-opacity-25 text-white">
                                    ${{ number_format($total, 2) }}
                                </td>
                                @endforeach
                                
                                <!-- Promedios PVP $ -->
                                @foreach($promediosPvp as $promedio)
                                <td class="text-center fw-bold bg-warning bg-opacity-25 text-white">
                                    ${{ number_format($promedio, 2) }}
                                </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Pie de tabla con información -->
            <div class="card-footer bg-white border-0">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            <strong>Análisis:</strong> 
                            Rojo = Reponer urgente (≤ {{ $config['umbral_reposicion'] }} unidades) | 
                            Amarillo = Transferir (≥ {{ $config['umbral_transferencia'] }} unidades diferencia) |
                            Azul = Revisar (Stock alto sin ventas) |
                            Gris = Solo desbalance
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="text-muted small">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Período: {{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen por Sucursal -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-dark">
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            Resumen por Sucursal
                        </h6>
                        <div class="row">
                            @foreach($sucursales as $sucursal)
                            @php
                                $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                $totalVentasSuc = array_sum(array_column($detallesMostrar, 'TotalDivisas' . $nombreCol));
                                $totalCantidad = array_sum(array_column($detallesMostrar, 'Cantidad' . $nombreCol));
                                $totalExistencia = array_sum(array_column($detallesMostrar, 'Existencia' . $nombreCol));
                                
                                // Calcular productos con bajo stock en esta sucursal
                                $productosBajoStock = 0;
                                foreach($detallesMostrar as $detalle) {
                                    $existencia = $detalle->{'Existencia'.$nombreCol} ?? 0;
                                    if ($existencia > 0 && $existencia <= $config['stock_minimo']) {
                                        $productosBajoStock++;
                                    }
                                }
                            @endphp
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="border rounded p-3 h-100" style="border-left: 3px solid {{ $sucursal['color'] }} !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold">{{ $sucursal['nombre'] }}</h6>
                                        <span class="badge rounded-pill" style="background-color: {{ $sucursal['color'] }}20; color: {{ $sucursal['color'] }}">
                                            <i class="fas fa-{{ $sucursal['icon'] }} me-1"></i>
                                        </span>
                                    </div>
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Ventas Totales:</span>
                                            <span class="fw-bold text-success">${{ number_format($totalVentasSuc, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Productos Vendidos:</span>
                                            <span class="fw-bold text-primary">{{ $totalCantidad }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Inventario:</span>
                                            <span class="fw-bold" style="color: {{ $sucursal['color'] }}">{{ $totalExistencia }}</span>
                                        </div>
                                        @if($productosBajoStock > 0)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Con stock bajo:</span>
                                            <span class="fw-bold text-danger">{{ $productosBajoStock }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--end::Container-->
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

@endsection

@section('js')
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script>
    // Solo función básica para exportar a Excel
    document.getElementById('exportExcel').addEventListener('click', function() {
        const table = document.getElementById('comparativaTable');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Comparativa"});
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Comparativa_Sucursales_${fecha}.xlsx`);
    });

    

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

    document.addEventListener('DOMContentLoaded', function() {
        const filtroEstado = document.getElementById('filtro_estado');
        const tablaBody = document.querySelector('#comparativaTable tbody');
        const filasOriginales = Array.from(tablaBody.querySelectorAll('tr'));
        const contadorMostrando = document.getElementById('mostrandoRegistros');
        const totalRegistros = filasOriginales.length;
        
        // Configurar evento para el filtro
        if (filtroEstado) {
            filtroEstado.addEventListener('change', function() {
                aplicarFiltro(this.value);
            });
            
            // Aplicar filtro inicial si hay valor en URL
            const urlParams = new URLSearchParams(window.location.search);
            const filtroInicial = urlParams.get('filtro_estado');
            if (filtroInicial) {
                filtroEstado.value = filtroInicial;
                aplicarFiltro(filtroInicial);
            }
        }
        
        function aplicarFiltro(tipoFiltro) {
            let filasFiltradas = 0;
            
            // Ocultar todas las filas primero
            filasOriginales.forEach(fila => {
                fila.style.display = 'none';
            });
            
            // Mostrar solo las filas que coincidan con el filtro
            if (tipoFiltro === 'todos') {
                filasOriginales.forEach(fila => {
                    fila.style.display = '';
                });
                filasFiltradas = totalRegistros;
            } else {
                filasOriginales.forEach(fila => {
                    const claseFila = fila.className;
                    let mostrar = false;
                    
                    switch(tipoFiltro) {
                        case 'reponer':
                            mostrar = claseFila.includes('table-danger');
                            break;
                        case 'transferir':
                            mostrar = claseFila.includes('table-warning');
                            break;
                        case 'revisar':
                            mostrar = claseFila.includes('table-info');
                            break;
                        case 'desbalance':
                            mostrar = claseFila.includes('table-secondary');
                            break;
                        case 'equilibrado':
                            // Las filas equilibradas no tienen clase especial
                            mostrar = !claseFila.includes('table-danger') && 
                                    !claseFila.includes('table-warning') &&
                                    !claseFila.includes('table-info') &&
                                    !claseFila.includes('table-secondary');
                            break;
                    }
                    
                    if (mostrar) {
                        fila.style.display = '';
                        filasFiltradas++;
                    }
                });
            }
            
            // Actualizar contador
            if (contadorMostrando) {
                contadorMostrando.textContent = `Mostrando ${filasFiltradas} de ${totalRegistros} productos`;
            }
            
            // // Agregar efecto visual
            // if (tipoFiltro !== 'todos') {
            //     mostrarNotificacionFiltro(tipoFiltro, filasFiltradas);
            // }
            
            // Actualizar URL sin recargar (opcional)
            actualizarURL(tipoFiltro);
        }
        
        // function mostrarNotificacionFiltro(tipo, cantidad) {
        //     // Crear o actualizar notificación
        //     let notificacion = document.getElementById('notificacionFiltro');
        //     if (!notificacion) {
        //         notificacion = document.createElement('div');
        //         notificacion.id = 'notificacionFiltro';
        //         notificacion.className = 'alert alert-info alert-dismissible fade show mb-3';
        //         notificacion.style.position = 'fixed';
        //         notificacion.style.top = '70px';
        //         notificacion.style.right = '20px';
        //         notificacion.style.zIndex = '1000';
        //         notificacion.style.maxWidth = '300px';
                
        //         const btnCerrar = document.createElement('button');
        //         btnCerrar.type = 'button';
        //         btnCerrar.className = 'btn-close';
        //         btnCerrar.setAttribute('data-bs-dismiss', 'alert');
                
        //         notificacion.appendChild(btnCerrar);
        //         document.body.appendChild(notificacion);
        //     }
            
        //     const nombresFiltro = {
        //         'reponer': 'Reponer urgente',
        //         'transferir': 'Transferir stock',
        //         'revisar': 'Revisar stock',
        //         'desbalance': 'Solo desbalance',
        //         'equilibrado': 'Equilibrado'
        //     };
            
        //     notificacion.innerHTML = `
        //         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        //         <strong>Filtro aplicado:</strong> ${nombresFiltro[tipo]}<br>
        //         <small>${cantidad} productos encontrados</small>
        //         <div class="mt-2">
        //             <button onclick="limpiarFiltro()" class="btn btn-sm btn-outline-secondary">
        //                 <i class="fas fa-times me-1"></i> Limpiar filtro
        //             </button>
        //         </div>
        //     `;
            
        //     // Auto-ocultar después de 5 segundos
        //     setTimeout(() => {
        //         if (notificacion && notificacion.classList.contains('show')) {
        //             notificacion.classList.remove('show');
        //             setTimeout(() => {
        //                 if (notificacion && notificacion.parentNode) {
        //                     notificacion.parentNode.removeChild(notificacion);
        //                 }
        //             }, 300);
        //         }
        //     }, 5000);
        // }
        
        // window.limpiarFiltro = function() {
        //     const filtroEstado = document.getElementById('filtro_estado');
        //     if (filtroEstado) {
        //         filtroEstado.value = 'todos';
        //         aplicarFiltro('todos');
        //     }
            
        //     const notificacion = document.getElementById('notificacionFiltro');
        //     if (notificacion && notificacion.parentNode) {
        //         notificacion.parentNode.removeChild(notificacion);
        //     }
        // };
        
        function actualizarURL(tipoFiltro) {
            // Actualizar URL sin recargar la página
            const url = new URL(window.location);
            
            if (tipoFiltro === 'todos') {
                url.searchParams.delete('filtro_estado');
            } else {
                url.searchParams.set('filtro_estado', tipoFiltro);
            }
            
            window.history.pushState({}, '', url);
        }
        
        // Manejar el botón de retroceso/avance del navegador
        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const filtro = urlParams.get('filtro_estado') || 'todos';
            
            const filtroEstado = document.getElementById('filtro_estado');
            if (filtroEstado) {
                filtroEstado.value = filtro;
                aplicarFiltro(filtro);
            }
        });
    });
</script>

<style>
    /* Estilos básicos y mejoras visuales */
    .card {
        transition: box-shadow 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.10) !important;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    .table-warning {
        background-color: #fff3cd !important;
    }
    
    .table-danger {
        background-color: #f8d7da !important;
    }
    
    .table-info {
        background-color: #d1ecf1 !important;
    }
    
    .table-secondary {
        background-color: #e2e3e5 !important;
    }
    
    .table-success {
        background-color: #d1e7dd !important;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .img-thumbnail {
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .badge:not(.navbar-badge) {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .img-thumbnail {
            width: 50px !important;
            height: 50px !important;
        }
        
        .card-body h6 {
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    }
    
    /* Scroll para tablas grandes */
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .table-responsive thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }
    
    /* Mejoras de impresión */
    @media print {
        .card-header .btn-group,
        .card-footer,
        .btn {
            display: none !important;
        }
        
        .table th, .table td {
            border: 1px solid #ddd !important;
        }
        
        .table-warning {
            background-color: #ffffcc !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table-danger {
            background-color: #ffcccc !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table-info {
            background-color: #cceeff !important;
            -webkit-print-color-adjust: exact;
        }
        
        .table-secondary {
            background-color: #eeeeee !important;
            -webkit-print-color-adjust: exact;
        }
        
        .bg-opacity-25 {
            background-color: rgba(0,0,0,0.1) !important;
        }
    }
</style>
@endsection