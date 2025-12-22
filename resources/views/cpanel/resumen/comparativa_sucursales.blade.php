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
        'umbral_transferencia' => 0.3, // 30% de diferencia en ratio ventas/existencia
        'umbral_reposicion' => 3,      // Stock por debajo del cual reponer urgente
        'dias_para_analisis' => $fechaInicio->diffInDays($fechaFin) + 1, // Días del período
    ];
    
    // Estadísticas mejoradas
    $estadisticas = [
        'necesita_reposicion' => 0,
        'puede_transferir'    => 0,
        'revisar'             => 0,
        'con_diferencias'     => 0,
        'equilibrado'         => 0,
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
            // Calcular ratio ventas/existencia por sucursal
            $ratios = [];
            $sucursalesConDatos = [];
            
            foreach($sucursales as $suc) {
                $nombre = $suc['nombre'];
                $existencia = $existencias[$nombre] ?? 0;
                $ventas = $ventas[$nombre] ?? 0;
                
                if ($existencia > 0 && $ventas > 0) {
                    // Ratio: ventas por día / existencia
                    $ratio = ($ventas / $config['dias_para_analisis']) / $existencia;
                    $ratios[$nombre] = $ratio;
                    $sucursalesConDatos[$nombre] = [
                        'ratio' => $ratio,
                        'existencia' => $existencia,
                        'ventas' => $ventas,
                        'dias_inventario' => $existencia / ($ventas / $config['dias_para_analisis'])
                    ];
                }
            }
            
            if (count($sucursalesConDatos) >= 2) {
                // Encontrar sucursal con mayor ratio (más ventas por unidad)
                $sucursalAltaDemanda = null;
                $maxRatio = 0;
                
                // Encontrar sucursal con menor ratio (menos ventas por unidad)
                $sucursalBajaDemanda = null;
                $minRatio = PHP_FLOAT_MAX;
                
                foreach ($sucursalesConDatos as $sucursal => $datos) {
                    if ($datos['ratio'] > $maxRatio) {
                        $maxRatio = $datos['ratio'];
                        $sucursalAltaDemanda = $sucursal;
                    }
                    if ($datos['ratio'] < $minRatio) {
                        $minRatio = $datos['ratio'];
                        $sucursalBajaDemanda = $sucursal;
                    }
                }
                
                // Calcular diferencia de ratios
                if ($sucursalAltaDemanda && $sucursalBajaDemanda && $maxRatio > 0) {
                    $diferenciaRatio = ($maxRatio - $minRatio) / $maxRatio; // Diferencia porcentual
                    
                    // Verificar si hay diferencia significativa (ej: 30% o más)
                    if ($diferenciaRatio >= $config['umbral_transferencia']) {
                        $existenciaAlta = $sucursalesConDatos[$sucursalAltaDemanda]['existencia'];
                        $existenciaBaja = $sucursalesConDatos[$sucursalBajaDemanda]['existencia'];
                        
                        // Calcular días de inventario
                        $diasInventarioAlta = $sucursalesConDatos[$sucursalAltaDemanda]['dias_inventario'];
                        $diasInventarioBaja = $sucursalesConDatos[$sucursalBajaDemanda]['dias_inventario'];
                        
                        // Sugerir cantidad a transferir (hasta equilibrar días de inventario)
                        $cantidadSugerida = 0;
                        
                        if ($diasInventarioBaja > $diasInventarioAlta * 1.5) {
                            // La sucursal con baja demanda tiene mucho inventario vs ventas
                            $cantidadSugerida = min(
                                floor($existenciaBaja * 0.3), // Máximo 30% del stock
                                max(1, floor(($diasInventarioBaja - $diasInventarioAlta) * 
                                    ($ventas[$sucursalAltaDemanda] / $config['dias_para_analisis'])))
                            );
                            
                            if ($cantidadSugerida >= 3) { // Solo sugerir si son al menos 3 unidades
                                $estadoProducto = 'puede_transferir';
                                $claseFila = 'table-warning';
                                $mensajeEstado = "🔄 Transferir {$cantidadSugerida} uds de $sucursalBajaDemanda a $sucursalAltaDemanda";
                                $iconoEstado = 'fa-exchange-alt text-warning';
                                $estadisticas['puede_transferir']++;
                            }
                        }
                    }
                }
                
                // Si no hay transferencia sugerida pero hay desbalance de existencias
                if ($estadoProducto === 'equilibrado') {
                    // Calcular desbalance simple de existencias
                    $existenciasPositivas = array_filter($existencias, function($e) {
                        return $e > 0;
                    });
                    
                    if (count($existenciasPositivas) >= 2) {
                        $max = max($existenciasPositivas);
                        $min = min($existenciasPositivas);
                        $diferencia = $max - $min;
                        
                        if ($diferencia >= 10) { // Umbral antiguo para mantener compatibilidad
                            $sucursalMax = array_search($max, $existencias);
                            $sucursalMin = array_search($min, $existencias);
                            
                            // Verificar si la sucursal con poco stock tiene ventas
                            $ventaSucursalMin = $ventas[$sucursalMin] ?? 0;
                            
                            if ($ventaSucursalMin > 0) {
                                $estadoProducto = 'puede_transferir';
                                $claseFila = 'table-info';
                                $mensajeEstado = "📦 Considerar mover de $sucursalMax a $sucursalMin";
                                $iconoEstado = 'fa-box text-info';
                                $estadisticas['puede_transferir']++;
                            } else {
                                $estadoProducto = 'con_diferencias';
                                $claseFila = 'table-secondary';
                                $mensajeEstado = "⚖️ Desbalance: {$diferencia} uds";
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
            } else {
                // Verificar sucursales con stock alto pero sin ventas
                $sucursalesStockAltoSinVentas = [];
                $sucursalesConStockYBajasVentas = []; // Nuevo: para stock alto con BAJAS ventas

                foreach($existencias as $sucursal => $existencia) {
                    $ventasSucursal = $ventas[$sucursal] ?? 0;
                    
                    // Stock alto y CERO ventas
                    if ($existencia >= 10 && $ventasSucursal == 0) {
                        $sucursalesStockAltoSinVentas[] = $sucursal;
                    }
                    // Stock alto y BAJAS ventas (ratio ventas/existencia < 0.1)
                    elseif ($existencia >= 10 && $ventasSucursal > 0) {
                        $ratio = $ventasSucursal / $existencia;
                        if ($ratio < 0.1) { // Menos de 1 venta por cada 10 unidades
                            $sucursalesConStockYBajasVentas[] = $sucursal . " (" . $ventasSucursal . "v)";
                        }
                    }
                }

                if (count($sucursalesStockAltoSinVentas) > 0) {
                    $estadoProducto = 'revisar';
                    $claseFila = 'table-info';
                    $mensajeEstado = "📊 Revisar: " . implode(', ', $sucursalesStockAltoSinVentas) . " sin ventas";
                    $iconoEstado = 'fa-chart-line text-info';
                    $estadisticas['revisar']++;
                } elseif (count($sucursalesConStockYBajasVentas) > 0) {
                    // Si hay stock alto con BAJAS ventas (no cero)
                    $estadoProducto = 'revisar';
                    $claseFila = 'table-info';
                    $mensajeEstado = "📊 Revisar: " . implode(', ', $sucursalesConStockYBajasVentas) . " con bajas ventas";
                    $iconoEstado = 'fa-chart-line text-info';
                    $estadisticas['revisar']++;
                } else {
                    $estadisticas['equilibrado']++;
                }
            }
        }
        
        // Guardar análisis del producto
        $productosAnalizados[] = [
            'detalle' => $detalle,
            'estado' => $estadoProducto,
            'data_estado'    => $estadoProducto,
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
        <!-- Card de filtros -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filtros de búsqueda
                </h5>
            </div>
            <div class="card-body">
                @php
                    $filtroEstado = request('filtro_estado', 'todos');
                    $fechaInicioInput = request('fecha_inicio', $fechaInicio->format('Y-m-d'));
                    $fechaFinInput = request('fecha_fin', $fechaFin->format('Y-m-d'));
                @endphp
                
                <form method="GET" action="{{ route('cpanel.comparativa.sucursales') }}" id="filtroForm">
                    <div class="row g-3">
                        <!-- Filtro de estado -->
                        <div class="col-md-3">
                            <label for="filtro_estado" class="form-label">
                                <i class="fas fa-filter me-1"></i>Filtrar por estado
                            </label>
                            <select id="filtro_estado" name="filtro_estado" class="form-select" style="min-width: 100%;">
                                <option value="todos" {{ $filtroEstado == 'todos' ? 'selected' : '' }}>📋 Todos los registros</option>
                                <option value="reponer" {{ $filtroEstado == 'reponer' ? 'selected' : '' }}>🔴 Reponer urgente</option>
                                <option value="transferir" {{ $filtroEstado == 'transferir' ? 'selected' : '' }}>🟡 Transferir stock</option>
                                <option value="revisar" {{ $filtroEstado == 'revisar' ? 'selected' : '' }}>🔵 Revisar stock</option>
                                <option value="desbalance" {{ $filtroEstado == 'desbalance' ? 'selected' : '' }}>⚪ Solo desbalance</option>
                                <option value="equilibrado" {{ $filtroEstado == 'equilibrado' ? 'selected' : '' }}>✅ Equilibrado</option>
                            </select>
                        </div>
                        
                        <!-- Fecha Inicio -->
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date" 
                                    class="form-control" 
                                    id="fecha_inicio" 
                                    name="fecha_inicio"
                                    value="{{ $fechaInicioInput }}"
                                    required>
                            </div>
                        </div>
                        
                        <!-- Fecha Fin -->
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Fin
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date" 
                                    class="form-control" 
                                    id="fecha_fin" 
                                    name="fecha_fin"
                                    value="{{ $fechaFinInput }}"
                                    required>
                            </div>
                        </div>
                        
                        <!-- Botón Buscar -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
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

        <!-- Tabla de Comparación -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center g-2">
                    <!-- Título -->
                    <div class="col-md-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Comparativa Inteligente
                        </h5>
                    </div>
                    
                    <!-- Campo de búsqueda -->
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <input type="text" 
                                class="form-control" 
                                id="buscarComparativa"
                                placeholder="Buscar por código o descripción..."
                                onkeyup="filtrarTablaComparativa()">
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="col-md-4 text-md-end">
                        <div class="btn-group">
                            <!-- <button type="button" class="btn btn-outline-secondary btn-sm" onclick="pdfComparativa()">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button> -->
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="exportExcel" onclick="exportarExcelComparativa()">
                                <i class="fas fa-file-excel me-1"></i>Exportar
                            </button>
                        </div>
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
                                
                                <!-- VENTAS (Cantidad) - Con borde derecho grueso -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-primary bg-opacity-10" style="border-right: 3px solid #dee2e6;">
                                    <div class="text-primary fw-bold">
                                        <i class="fas fa-chart-bar me-1"></i> VENTAS (Cantidad)
                                    </div>
                                </th>
                                
                                <!-- EXISTENCIAS - Con borde derecho grueso -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-success bg-opacity-10" style="border-right: 3px solid #dee2e6;">
                                    <div class="text-success fw-bold">
                                        <i class="fas fa-warehouse me-1"></i> EXISTENCIAS
                                    </div>
                                </th>
                                
                                <!-- VENTAS ($) - Con borde derecho grueso -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-info bg-opacity-10" style="border-right: 3px solid #dee2e6;">
                                    <div class="text-info fw-bold">
                                        <i class="fas fa-money-bill-wave me-1"></i> VENTAS ($)
                                    </div>
                                </th>
                                
                                <!-- PVP ($) - Sin borde derecho (última columna) -->
                                <th colspan="{{ count($sucursales) }}" class="text-center bg-warning bg-opacity-10">
                                    <div class="text-warning fw-bold">
                                        <i class="fas fa-tag me-1"></i> PVP ($)
                                    </div>
                                </th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                
                                <!-- Encabezados de sucursales para Ventas Cantidad - con borde derecho -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <i class="fas fa-store me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para Existencias - con borde derecho -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <i class="fas fa-box me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para Ventas $ - con borde derecho -->
                                @foreach($sucursales as $sucursal)
                                <th class="text-center small" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <i class="fas fa-dollar-sign me-1" style="color: {{ $sucursal['color'] }}"></i>
                                    {{ $sucursal['nombre'] }}
                                </th>
                                @endforeach
                                
                                <!-- Encabezados de sucursales para PVP $ - sin borde derecho -->
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
                            
                            <tr data-estado="{{ $producto['estado'] }}">
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
                                                
                                                <!-- Segunda línea: mensaje de estado (solo si existe) -->
                                                @if($producto['mensaje_estado'])
                                                <div class="mt-1">
                                                    @php
                                                        // Verificar si el mensaje contiene información incorrecta
                                                        $mensaje = $producto['mensaje_estado'];
                                                        // Opcional: puedes hacer algún ajuste aquí si detectas patrones incorrectos
                                                    @endphp
                                                    <span class="badge {{ $producto['estado'] == 'necesita_reposicion' ? 'bg-danger' : ($producto['estado'] == 'puede_transferir' ? 'bg-warning' : 'bg-secondary') }}"
                                                        title="Estado basado en análisis de stock y ventas">
                                                        {{ $mensaje }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- VENTAS Cantidad - con borde derecho en la última celda del grupo -->
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
                                <td class="text-center align-middle" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <div class="{{ $tieneVentas ? 'bg-primary bg-opacity-10 rounded py-1 px-2' : '' }}">
                                        {{ $cantidad }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- EXISTENCIAS - con borde derecho en la última celda del grupo -->
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
                                <td class="text-center align-middle" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <div class="rounded py-1 px-2">
                                        {{ $existencia }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- VENTAS $ - con borde derecho en la última celda del grupo -->
                                @foreach($sucursales as $sucursal)
                                @php
                                    $nombreCol = str_replace(' ', '', $sucursal['nombre']);
                                    $montoVenta = $detalle->{'TotalDivisas'.$nombreCol} ?? 0;
                                @endphp
                                <td class="text-center align-middle fw-bold" style="color: #10b981; border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    <div class="bg-success bg-opacity-10 rounded py-1 px-2">
                                        ${{ number_format($montoVenta, 2) }}
                                    </div>
                                </td>
                                @endforeach
                                
                                <!-- PVP $ - sin borde derecho -->
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
                                
                                <!-- Totales Ventas Cantidad - con borde derecho -->
                                @foreach($totales['cantidades'] as $index => $total)
                                <td class="text-center fw-bold bg-primary bg-opacity-25 text-white" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    {{ $total }}
                                </td>
                                @endforeach
                                
                                <!-- Totales Existencias - con borde derecho -->
                                @foreach($totales['existencias'] as $index => $total)
                                <td class="text-center fw-bold bg-success bg-opacity-25 text-white" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    {{ $total }}
                                </td>
                                @endforeach
                                
                                <!-- Totales Ventas $ - con borde derecho -->
                                @foreach($totales['ventas'] as $index => $total)
                                <td class="text-center fw-bold bg-info bg-opacity-25 text-white" style="border-right: {{ $loop->last ? '3px solid #dee2e6' : 'none' }};">
                                    ${{ number_format($total, 2) }}
                                </td>
                                @endforeach
                                
                                <!-- Promedios PVP $ - sin borde derecho -->
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

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

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

            filasOriginales.forEach(fila => fila.style.display = 'none');

            if (tipoFiltro === 'todos') {
                filasOriginales.forEach(fila => {
                    fila.style.display = '';
                });
                filasFiltradas = totalRegistros;
            } else {
                filasOriginales.forEach(fila => {
                    const estado = fila.getAttribute('data-estado');
                    let mostrar = false;

                    switch(tipoFiltro) {
                        case 'reponer':
                            mostrar = (estado === 'necesita_reposicion');
                            break;
                        case 'transferir':
                            mostrar = (estado === 'puede_transferir');
                            break;
                        case 'revisar':
                            mostrar = (estado === 'revisar');
                            break;
                        case 'desbalance':
                            mostrar = (estado === 'solo_desbalance');
                            break;
                        case 'equilibrado':
                            mostrar = (estado === 'equilibrado');
                            break;
                    }

                    if (mostrar) {
                        fila.style.display = '';
                        filasFiltradas++;
                    }
                });
            }

            if (contadorMostrando) {
                contadorMostrando.textContent = `Mostrando ${filasFiltradas} de ${totalRegistros} productos`;
            }

            actualizarURL(tipoFiltro);
        }

        
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

    // Función para filtrar la tabla de comparativa
    // Función para filtrar la tabla de comparativa por código o descripción
    function filtrarTablaComparativa() {
        try {
            const input = document.getElementById("buscarComparativa");
            if (!input) {
                console.error('No se encontró el input de búsqueda');
                return;
            }
            
            const filter = input.value.trim().toUpperCase();
            const table = document.getElementById("comparativaTable");
            
            if (!table) {
                console.error('No se encontró la tabla con ID "comparativaTable"');
                return;
            }
            
            const tbody = table.querySelector('tbody');
            if (!tbody) {
                console.error('No se encontró el tbody en la tabla');
                return;
            }
            
            const rows = tbody.querySelectorAll('tr');
            let filasVisibles = 0;
            
            // Recorrer todas las filas del cuerpo (no incluir footer)
            rows.forEach(row => {
                if (row.style.display === 'none' && filter === '') {
                    row.style.display = ''; // Mostrar si está vacío
                }
                
                // Obtener la celda de producto (columna 1, índice 1)
                const cells = row.querySelectorAll('td');
                if (cells.length < 2) return; // Si no tiene suficientes celdas, saltar
                
                const celdaProducto = cells[1]; // Segunda columna (índice 1) donde está el producto
                
                // Extraer código y descripción del contenido de la celda
                let codigo = '';
                let descripcion = '';
                
                // Buscar el código (está en un div con <i class="fas fa-barcode">)
                const barcodeElement = celdaProducto.querySelector('.fa-barcode');
                if (barcodeElement && barcodeElement.parentElement) {
                    const textoBarcode = barcodeElement.parentElement.textContent || '';
                    // El código está después del icono de barcode
                    codigo = textoBarcode.replace('', '').trim(); // Remover el icono si existe
                }
                
                // Buscar la descripción (está en un h6 con clase fw-bold)
                const descripcionElement = celdaProducto.querySelector('h6.fw-bold');
                if (descripcionElement) {
                    descripcion = descripcionElement.textContent || '';
                }
                
                // También buscar en el texto completo de la celda por si acaso
                const textoCompleto = celdaProducto.textContent || '';
                
                // Verificar coincidencia
                const coincide = filter === '' || 
                                (codigo && codigo.toUpperCase().includes(filter)) ||
                                (descripcion && descripcion.toUpperCase().includes(filter)) ||
                                textoCompleto.toUpperCase().includes(filter);
                
                row.style.display = coincide ? '' : 'none';
                if (coincide) filasVisibles++;
            });
            
            // Actualizar contador de resultados si existe
            // actualizarContadorResultados(filasVisibles, rows.length);
            
        } catch (error) {
            console.error('Error al filtrar la tabla:', error);
        }
    }

    // Función para generar PDF de la comparativa
    async function pdfComparativa() {
        const tabla = document.getElementById('comparativaTable');
        
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
        loading.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light"></div><p class="mt-2">Generando PDF de comparativa...</p></div>';
        document.body.appendChild(loading);
        
        try {
            await generarPDFComparativa();
        } catch (error) {
            console.error('Error generando PDF:', error);
            alert('Error generando PDF. Intente nuevamente.');
        } finally {
            document.body.removeChild(loading);
        }
    }

    // Función principal para generar el PDF de comparativa
    async function generarPDFComparativa() {
        const tabla = document.getElementById('comparativaTable');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        
        // Título del documento
        const titulo = 'Comparativa de Productos - ' + new Date().toLocaleDateString('es-ES');
        doc.setFontSize(16);
        doc.text(titulo, 14, 15);
        
        // Información de fechas si está disponible
        const fechaInicio = document.querySelector('input[name="fecha_inicio"]')?.value;
        const fechaFin = document.querySelector('input[name="fecha_fin"]')?.value;
        
        if (fechaInicio && fechaFin) {
            doc.setFontSize(10);
            doc.text(`Período: ${fechaInicio} a ${fechaFin}`, 14, 22);
        }
        
        // Preparar datos para la tabla
        // En la comparativa, tenemos una estructura compleja con encabezados anidados
        // Vamos a crear una versión simplificada para el PDF
        
        const datos = [];
        const promesasImagenes = [];
        
        // Obtener sucursales de los headers
        const sucursales = [];
        const encabezadosSucursales = tabla.querySelectorAll('thead tr:nth-child(2) th');
        let columnaInicioSucursales = 2; // Después de # y Producto
        
        // Procesar encabezados de sucursales
        for (let i = columnaInicioSucursales; i < encabezadosSucursales.length; i++) {
            const th = encabezadosSucursales[i];
            const nombreSucursal = th.textContent.trim();
            if (nombreSucursal) {
                sucursales.push(nombreSucursal);
            }
        }
        
        // Dividir las sucursales en grupos (Ventas, Existencias, Ventas$, PVP$)
        const sucursalesPorGrupo = sucursales.length / 4; // 4 grupos de métricas
        const grupos = ['VENTAS (Cantidad)', 'EXISTENCIAS', 'VENTAS ($)', 'PVP ($)'];
        
        // Crear encabezados simplificados para el PDF
        const encabezadosPDF = ['#', 'Producto'];
        
        // Agregar métricas por sucursal
        grupos.forEach((grupo, grupoIndex) => {
            sucursales.slice(0, sucursalesPorGrupo).forEach((sucursal, sucIndex) => {
                let nombreColumna = `${grupo}`;
                if (grupos.length > 1) {
                    nombreColumna = `${sucursal} - ${grupo}`;
                }
                encabezadosPDF.push(nombreColumna);
            });
        });
        
        // Procesar filas de datos
        let filaNumero = 0;
        const filasVisibles = Array.from(tabla.querySelectorAll('tbody tr')).filter(fila => 
            fila.style.display !== 'none'
        );
        
        filasVisibles.forEach((fila, filaIndex) => {
            filaNumero++;
            const filaData = [filaNumero.toString()];
            
            // Obtener datos del producto (columna 1)
            const celdaProducto = fila.cells[1];
            if (celdaProducto) {
                // Extraer descripción
                const descripcionElement = celdaProducto.querySelector('h6.fw-bold');
                const descripcion = descripcionElement ? descripcionElement.textContent.trim() : '';
                
                // Extraer código
                const codigoElement = celdaProducto.querySelector('.fa-barcode');
                const codigo = codigoElement ? codigoElement.parentElement.textContent.replace('', '').trim() : '';
                
                // Combinar para mostrar en el PDF
                const textoProducto = `${descripcion}\nCódigo: ${codigo}`;
                filaData.push(textoProducto);
                
                // Guardar imagen si existe
                const imgElement = celdaProducto.querySelector('img');
                if (imgElement && imgElement.src) {
                    promesasImagenes.push({
                        filaIndex: filaNumero - 1,
                        imgUrl: imgElement.src,
                        descripcion: descripcion
                    });
                }
            } else {
                filaData.push('');
            }
            
            // Obtener datos de métricas por sucursal
            // Las columnas 2 en adelante contienen las métricas
            for (let i = 2; i < fila.cells.length; i++) {
                const celda = fila.cells[i];
                if (celda) {
                    // Extraer el valor numérico
                    const texto = celda.textContent.trim();
                    // Limpiar y formatear
                    let valor = texto;
                    
                    // Si contiene $, es moneda
                    if (texto.includes('$')) {
                        valor = texto;
                    }
                    // Si es solo número, mantenerlo
                    
                    filaData.push(valor);
                } else {
                    filaData.push('');
                }
            }
            
            datos.push(filaData);
        });
        
        // Crear tabla en PDF
        doc.autoTable({
            head: [encabezadosPDF],
            body: datos,
            startY: fechaInicio ? 28 : 25,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontSize: 7,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 6,
                cellPadding: 1,
                lineWidth: 0.1
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' }, // #
                1: { cellWidth: 60, fontStyle: 'bold' }, // Producto
                // Las demás columnas se ajustarán automáticamente
            },
            margin: { top: fechaInicio ? 30 : 27 },
            styles: {
                overflow: 'linebreak',
                cellWidth: 'wrap'
            },
            didParseCell: function(data) {
                // Si es la celda de producto, ajustar para múltiples líneas
                if (data.column.index === 1 && data.cell.section === 'body') {
                    data.cell.styles.fontSize = 5;
                    data.cell.styles.cellPadding = { top: 1, right: 1, bottom: 1, left: 1 };
                }
                
                // Si son columnas de datos numéricos, alinear a la derecha
                if (data.column.index >= 2) {
                    data.cell.styles.halign = 'right';
                    data.cell.styles.fontSize = 6;
                }
            }
        });
        
        // Agregar imágenes de productos si las hay
        if (promesasImagenes.length > 0) {
            const table = doc.autoTable.previous;
            
            for (const imgInfo of promesasImagenes) {
                try {
                    const base64 = await cargarImagenABase64(imgInfo.imgUrl);
                    if (base64 && table && table.cells && table.cells[imgInfo.filaIndex]) {
                        const cell = table.cells[imgInfo.filaIndex][1]; // Columna de producto
                        if (cell) {
                            // Calcular posición para la imagen (en la parte superior de la celda)
                            const x = cell.x + 2;
                            const y = cell.y + 2;
                            
                            doc.addImage(
                                base64,
                                'JPEG',
                                x,
                                y,
                                15,
                                15
                            );
                            
                            // Mover el texto para que no se superponga con la imagen
                            // Actualizar posición del texto en la celda
                            const textoX = x + 18; // Después de la imagen
                            const textoY = y + 4;
                            
                            // Agregar texto al lado de la imagen
                            doc.setFontSize(5);
                            doc.text(imgInfo.descripcion.substring(0, 30) + '...', textoX, textoY);
                        }
                    }
                } catch (error) {
                    console.log('Error cargando imagen para PDF:', error);
                }
            }
        }
        
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
        doc.save(`Comparativa_Productos_${fecha}.pdf`);
    }

    // Función para cargar imagen a base64 (reutilizable)
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
                resolve(null);
            };
            
            // Agregar timestamp para evitar cache
            const timestamp = new Date().getTime();
            const urlConTimestamp = url.includes('?') ? 
                `${url}&t=${timestamp}` : 
                `${url}?t=${timestamp}`;
            
            img.src = urlConTimestamp;
        });
    }

    // Función simplificada para PDF (sin imágenes, más rápida)
    function pdfComparativaSimple() {
        const tabla = document.getElementById('comparativaTable');
        
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
        loading.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light"></div><p class="mt-2">Generando PDF...</p></div>';
        document.body.appendChild(loading);
        
        setTimeout(() => {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                
                // Título
                doc.setFontSize(16);
                doc.text('Comparativa de Productos', 14, 15);
                doc.setFontSize(10);
                doc.text(`Fecha: ${new Date().toLocaleDateString('es-ES')}`, 14, 22);
                
                // Obtener datos simplificados
                const datos = [];
                
                // Solo tomar columnas clave: #, Producto, y algunas métricas
                const filasVisibles = Array.from(tabla.querySelectorAll('tbody tr')).filter(fila => 
                    fila.style.display !== 'none'
                );
                
                let filaNumero = 0;
                filasVisibles.forEach((fila) => {
                    filaNumero++;
                    const filaData = [filaNumero.toString()];
                    
                    // Producto (columna 1)
                    const celdaProducto = fila.cells[1];
                    if (celdaProducto) {
                        const descripcion = celdaProducto.querySelector('h6.fw-bold')?.textContent?.trim() || '';
                        const codigo = celdaProducto.querySelector('.fa-barcode')?.parentElement?.textContent?.replace('', '').trim() || '';
                        filaData.push(`${descripcion.substring(0, 30)}...\n${codigo}`);
                    } else {
                        filaData.push('');
                    }
                    
                    // Tomar algunas métricas de ejemplo (primeras 4 sucursales de cada grupo)
                    for (let i = 2; i < Math.min(10, fila.cells.length); i++) {
                        const celda = fila.cells[i];
                        filaData.push(celda ? celda.textContent.trim() : '');
                    }
                    
                    datos.push(filaData);
                });
                
                // Encabezados simplificados
                const encabezados = ['#', 'Producto'];
                for (let i = 1; i <= Math.min(8, datos[0]?.length - 2 || 0); i++) {
                    encabezados.push(`Métrica ${i}`);
                }
                
                // Crear tabla
                doc.autoTable({
                    head: [encabezados],
                    body: datos,
                    startY: 28,
                    theme: 'striped',
                    headStyles: {
                        fillColor: [41, 128, 185],
                        textColor: 255,
                        fontSize: 8
                    },
                    bodyStyles: {
                        fontSize: 7
                    },
                    columnStyles: {
                        0: { cellWidth: 10, halign: 'center' },
                        1: { cellWidth: 50 }
                    }
                });
                
                // Pie de página
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.text(
                        `Página ${i} de ${pageCount}`,
                        doc.internal.pageSize.width - 30,
                        doc.internal.pageSize.height - 10
                    );
                }
                
                const fecha = new Date().toISOString().split('T')[0];
                doc.save(`Comparativa_Simple_${fecha}.pdf`);
                
            } catch (error) {
                console.error('Error generando PDF simple:', error);
                alert('Error generando PDF. Intente nuevamente.');
            } finally {
                document.body.removeChild(loading);
            }
        }, 500);
    }

    // Función para exportar a Excel de la comparativa
    function exportarExcelComparativa() {
        const tabla = document.getElementById('comparativaTable');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Implementar lógica de exportación a Excel
        // Similar a tu función exportarExcel() pero adaptada para la comparativa
        console.log('Exportando comparativa a Excel...');
        alert('Función de exportar a Excel para comparativa (pendiente de implementar)');
        
        // Puedes reutilizar tu función exportarExcel() si es genérica
        // o crear una específica para la comparativa
    }
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