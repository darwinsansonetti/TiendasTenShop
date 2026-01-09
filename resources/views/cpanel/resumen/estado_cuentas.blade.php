@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Estado de Cuentas')

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
      <div class="col-sm-6"><h3 class="mb-0">Estado de Cuentas</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Estado de Cuentas</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">

        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="bi bi-cart-fill"></i>
                    </span>
                    <div class="info-box-content">
                    <span class="info-box-text">Ventas del Mes</span>
                    <span class="info-box-number">
                        @if(!empty($Ventas['listaVentasDiarias']) && count($Ventas['listaVentasDiarias']) > 0)
                            {{ number_format($Ventas['listaVentasDiarias'][0]->montoDivisaGlobal, 2, ',', '.') }}
                        @else
                            0
                        @endif
                    </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-danger shadow-sm">
                    <i class="bi bi-currency-exchange"></i>
                    </span>
                    <div class="info-box-content">
                    <span class="info-box-text">Gastos del Mes</span>
                    <span class="info-box-number">
                        {{ number_format($GastosDivisaPeriodo ?? 0, 2, ',', '.') }}
                    </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <!-- fix for small devices only -->
            <!-- <div class="clearfix hidden-md-up"></div> -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="bi bi-clipboard-data"></i>
                    </span>
                    <div class="info-box-content">
                    <span class="info-box-text">Utilidad Venta</span>
                    <span class="info-box-number">
                        {{ $Ventas['UtilidadDivisaPeriodoDsp'] }}
                    </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="bi bi-arrow-up-right-square"></i>
                    </span>
                    <div class="info-box-content">
                    <span class="info-box-text">Margen Promedio</span>
                    <span class="info-box-number">
                        {{ $Ventas['MargenNetoPeriodoDsp'] }}
                        <small>%</small>
                    </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row Info Boxes-->

    </div>
    <!--end::Container-->

    <!-- Nueva sección de estadísticas detalladas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <!-- Header con selector de fecha -->
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title mb-0">
                                <i class="bi bi-bar-chart-line me-2"></i>Estadísticas Detalladas
                            </h3>
                        </div>
                        <div class="col-md-4">

                            <!-- Formulario para seleccionar mes y año -->
                            <form id="form-periodo" 
                                method="GET" 
                                action="{{ route('cpanel.estado.cuentas') }}" 
                                class="d-flex align-items-center">

                                @php
                                    $mesFormateado = str_pad($Mes ?? date('m'), 2, '0', STR_PAD_LEFT);
                                    $periodoActual = ($Anio ?? date('Y')) . '-' . $mesFormateado;
                                @endphp
                                
                                <input type="month" 
                                    id="periodoEstadisticas" 
                                    name="periodo"
                                    class="form-control form-control-sm me-1"
                                    value="{{ $periodoActual }}">

                                <!-- Inputs ocultos para enviar mes y año -->
                                <input type="hidden" name="mes" id="mes">
                                <input type="hidden" name="anio" id="anio">

                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
                
                <!-- Contenido de tabs -->
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="estadisticasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" 
                                    id="ventas-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#ventas" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="ventas" 
                                    aria-selected="true">
                                <i class="bi bi-cart-check me-1"></i> Resumen mensual
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    id="productos-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#productos" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="productos" 
                                    aria-selected="false">
                                <i class="bi bi-box-seam me-1"></i> Estado de Cuenta
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    id="clientes-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#clientes" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="clientes" 
                                    aria-selected="false">
                                <i class="bi bi-people me-1"></i> Ventas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    id="sucursales-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#sucursales" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="sucursales" 
                                    aria-selected="false">
                                <i class="bi bi-shop me-1"></i> Gastos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" 
                                    id="metodos-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#metodos" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="metodos" 
                                    aria-selected="false">
                                <i class="bi bi-credit-card me-1"></i> Cuentas por pagar
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="estadisticasTabContent">
                        <!-- Tab 1: Ventas -->
                        
                        <div class="tab-pane fade show active" 
                            id="ventas" 
                            role="tabpanel" 
                            aria-labelledby="ventas-tab">
                            
                            <!-- Fila de 3 cards principales -->
                            <div class="row mb-4">
                                <!-- Card 1: Resumen de Ventas -->
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-cart-check me-2"></i>Resumen de Ventas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Ventas totales -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Ventas totales:</span>
                                                <h4 class="text-primary mb-0">
                                                    @php
                                                        $ventasTotales = $Ventas['listaVentasDiarias'][0]->montoDivisaGlobal ?? 0;
                                                    @endphp
                                                    $ {{ number_format($ventasTotales, 2, ',', '.') }}
                                                </h4>
                                            </div>
                                            
                                            <!-- Costo total -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Costo total:</span>
                                                <h5 class="text-danger mb-0">
                                                    @php
                                                        $costoTotal = $Ventas['CostoDivisaPeriodo'];
                                                    @endphp
                                                    $ {{ $Ventas['CostoDivisaPeriodo'] }}
                                                </h5>
                                            </div>
                                            
                                            <!-- Utilidad bruta -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Utilidad bruta:</span>
                                                <h5 class="text-success mb-0">
                                                    @php
                                                        $utilidadBruta = $Ventas['UtilidadDivisaPeriodo'];
                                                    @endphp
                                                    $ {{ $Ventas['UtilidadDivisaPeriodo'] }}
                                                </h5>
                                            </div>
                                            
                                            <!-- Margen bruto -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Margen bruto:</span>
                                                <span class="badge bg-{{ ($ventasTotales > 0 && ($utilidadBruta/$ventasTotales)*100 > 30) ? 'success' : (($ventasTotales > 0 && ($utilidadBruta/$ventasTotales)*100 > 20) ? 'warning' : 'danger') }}">
                                                    @php
                                                        $margenBruto = $Ventas['MargenBrutoPeriodoDsp'];
                                                    @endphp
                                                    {{ $Ventas['MargenBrutoPeriodoDsp'] }}%
                                                </span>
                                            </div>
                                            
                                            <!-- Utilidad neta -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Utilidad neta:</span>
                                                <h5 class="text-info mb-0">
                                                    @php
                                                        $utilidadNeta = $utilidadBruta - ($GastosDivisaPeriodo ?? 0);
                                                    @endphp
                                                    $ {{ $Ventas['UtilidadNetaPeriodoDsp'] }}
                                                </h5>
                                            </div>
                                            
                                            <!-- Margen neto -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Margen neto:</span>
                                                <span class="badge bg-{{ ($ventasTotales > 0 && ($utilidadNeta/$ventasTotales)*100 > 20) ? 'success' : (($ventasTotales > 0 && ($utilidadNeta/$ventasTotales)*100 > 10) ? 'warning' : 'danger') }}">
                                                    @php
                                                        $margenNeto = $ventasTotales > 0 ? ($utilidadNeta / $ventasTotales) * 100 : 0;
                                                    @endphp
                                                    {{ $Ventas['MargenNetoPeriodoDsp'] }}%
                                                </span>
                                            </div>
                                            
                                            <!-- Productos vendidos -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Productos vendidos:</span>
                                                <span class="badge bg-primary">
                                                    @if(!empty($Ventas['listaVentasDiarias']))
                                                        @php
                                                            $listaVentas = $Ventas['listaVentasDiarias'];
                                                            $totalCantidad = 0;
                                                            
                                                            foreach ($listaVentas as $venta) {
                                                                $totalCantidad += $venta->cantidad ?? 0;
                                                            }
                                                        @endphp
                                                        
                                                        {{ $totalCantidad }} U
                                                    @else
                                                        0 U
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <!-- Mini gráfico de composición -->
                                            <div class="mt-4 pt-3 border-top">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="bi bi-pie-chart me-1"></i> Composición
                                                </small>
                                                <div class="progress" style="height: 10px;">
                                                    @php
                                                        $porcentajeCosto = $ventasTotales > 0 ? ($costoTotal / $ventasTotales) * 100 : 0;
                                                        $porcentajeUtilidadBruta = $ventasTotales > 0 ? ($utilidadBruta / $ventasTotales) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-danger" style="width: {{ $porcentajeCosto }}%" 
                                                        title="Costo: {{ number_format($porcentajeCosto, 1) }}%"></div>
                                                    <div class="progress-bar bg-success" style="width: {{ $porcentajeUtilidadBruta }}%" 
                                                        title="Utilidad Bruta: {{ number_format($porcentajeUtilidadBruta, 1) }}%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <small class="text-danger">Costo</small>
                                                    <small class="text-success">Utilidad</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-muted">
                                                <i class="bi {{ $margenNeto > 20 ? 'bi-arrow-up-circle text-success' : ($margenNeto > 10 ? 'bi-dash-circle text-warning' : 'bi-arrow-down-circle text-danger') }} me-1"></i>
                                                Margen {{ $margenNeto > 20 ? 'alto' : ($margenNeto > 10 ? 'medio' : 'bajo') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card 2: Balance del Mes -->
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-calculator me-2"></i>Balance del Mes
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Ventas del Periodo -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Ventas del Periodo:</span>
                                                <h4 class="text-primary mb-0">
                                                    $ {{ number_format($ventasTotales, 2, ',', '.') }}
                                                </h4>
                                            </div>
                                            
                                            <!-- Costo del Periodo -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Costo del Periodo:</span>
                                                <h5 class="text-danger mb-0">
                                                    $ {{ number_format($costoTotal, 2, ',', '.') }}
                                                </h5>
                                            </div>
                                            
                                            <!-- Gastos del Periodo -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Gastos del Periodo:</span>
                                                <h5 class="text-danger mb-0">
                                                    $ {{ number_format($GastosDivisaPeriodo ?? 0, 2, ',', '.') }}
                                                </h5>
                                            </div>
                                            
                                            <!-- Utilidad Neta -->
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <span class="text-muted">Utilidad Neta:</span>
                                                <h4 class="text-success mb-0">
                                                    $ {{ number_format($utilidadNeta, 2, ',', '.') }}
                                                </h4>
                                            </div>
                                            
                                            <!-- Resumen gráfico -->
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-2">Distribución de Ingresos</small>
                                                <div class="progress" style="height: 20px;">
                                                    @php
                                                        $porcentajeCosto = $ventasTotales > 0 ? ($costoTotal / $ventasTotales) * 100 : 0;
                                                        $porcentajeGastos = $ventasTotales > 0 ? (($GastosDivisaPeriodo ?? 0) / $ventasTotales) * 100 : 0;
                                                        $porcentajeUtilidad = $ventasTotales > 0 ? ($utilidadNeta / $ventasTotales) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-danger" style="width: {{ $porcentajeCosto }}%">
                                                        Costo
                                                    </div>
                                                    <div class="progress-bar bg-warning" style="width: {{ $porcentajeGastos }}%">
                                                        Gastos
                                                    </div>
                                                    <div class="progress-bar bg-success" style="width: {{ $porcentajeUtilidad }}%">
                                                        Utilidad
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Ratios clave -->
                                            <div class="row mt-4 pt-3 border-top">
                                                <div class="col-6 text-center">
                                                    <small class="text-muted d-block">ROI</small>
                                                    <strong class="text-{{ $utilidadNeta > ($costoTotal + ($GastosDivisaPeriodo ?? 0)) ? 'success' : 'danger' }}">
                                                        @php
                                                            $inversionTotal = $costoTotal + ($GastosDivisaPeriodo ?? 0);
                                                            $roi = $inversionTotal > 0 ? ($utilidadNeta / $inversionTotal) * 100 : 0;
                                                        @endphp
                                                        {{ number_format($roi, 1) }}%
                                                    </strong>
                                                </div>
                                                <div class="col-6 text-center">
                                                    <small class="text-muted d-block">Rentabilidad</small>
                                                    <strong class="text-{{ $margenNeto > 15 ? 'success' : ($margenNeto > 5 ? 'warning' : 'danger') }}">
                                                        {{ number_format($margenNeto, 1) }}%
                                                    </strong>
                                                </div>
                                            </div>
                                            
                                            <!-- Estado -->
                                            <div class="mt-3">
                                                <div class="alert alert-{{ $utilidadNeta > 0 ? 'success' : 'danger' }} py-2 mb-0 text-center">
                                                    <i class="bi {{ $utilidadNeta > 0 ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                                                    <strong>{{ $utilidadNeta > 0 ? 'GANANCIA' : 'PÉRDIDA' }}</strong>
                                                    <div class="small">
                                                        $ {{ number_format(abs($utilidadNeta), 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-muted">
                                                <i class="bi bi-graph-up-arrow me-1"></i>
                                                {{ $utilidadNeta > 0 ? 'Resultado positivo' : 'Resultado negativo' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card 3: Resumen de Gastos -->
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-currency-exchange me-2"></i>Resumen de Gastos
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Total en gastos -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Total en gastos:</span>
                                                <h4 class="text-danger mb-0">
                                                    @php
                                                        $totalGastos = $GastosDivisaPeriodo ?? 0;
                                                    @endphp
                                                    $ {{ number_format($totalGastos, 2, ',', '.') }}
                                                </h4>
                                            </div>
                                            
                                            <!-- Cantidad de gastos -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Cantidad de gastos:</span>
                                                <span class="badge bg-danger">
                                                    {{ count($ListadoGastosPeriodo ?? []) }}
                                                </span>
                                            </div>
                                            
                                            <!-- Porcentaje sobre ventas -->
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <span class="text-muted">% sobre ventas:</span>
                                                <span class="badge bg-{{ $ventasTotales > 0 && (($totalGastos/$ventasTotales)*100 < 20) ? 'success' : ($ventasTotales > 0 && (($totalGastos/$ventasTotales)*100 < 30) ? 'warning' : 'danger') }}">
                                                    @php
                                                        $porcentajeSobreVentas = $ventasTotales > 0 ? ($totalGastos / $ventasTotales) * 100 : 0;
                                                    @endphp
                                                    {{ number_format($porcentajeSobreVentas, 1) }}%
                                                </span>
                                            </div>
                                            
                                            <!-- Top 5 Gastos detallado -->
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-2">
                                                    <i class="bi bi-list-ol me-1"></i> Principales Gastos
                                                </small>
                                                <div class="list-group list-group-flush">
                                                    @if(!empty($ListadoGastosPeriodo))
                                                        @php
                                                            // 1. Agrupar y sumar los gastos por Nombre
                                                            $gastosAgrupados = [];
                                                            
                                                            foreach ($ListadoGastosPeriodo as $gasto) {
                                                                $nombre = $gasto->Nombre ?? 'Sin nombre';
                                                                $monto = $gasto->MontoDivisaAbonado ?? 0;
                                                                
                                                                if (!isset($gastosAgrupados[$nombre])) {
                                                                    $gastosAgrupados[$nombre] = [
                                                                        'nombre' => $nombre,
                                                                        'total' => 0,
                                                                        'contador' => 0
                                                                    ];
                                                                }
                                                                
                                                                $gastosAgrupados[$nombre]['total'] += $monto;
                                                                $gastosAgrupados[$nombre]['contador']++;
                                                            }
                                                            
                                                            // 2. Ordenar por total descendente
                                                            usort($gastosAgrupados, function($a, $b) {
                                                                return $b['total'] <=> $a['total'];
                                                            });
                                                            
                                                            // 3. Calcular total general de gastos
                                                            $totalGastos = array_sum(array_column($gastosAgrupados, 'total'));
                                                            
                                                            // 4. Contar total de categorías únicas
                                                            $totalCategorias = count($gastosAgrupados);
                                                        @endphp
                                                        
                                                        <!-- Contenedor con scroll para la lista de gastos -->
                                                        <div class="gastos-list-scroll" style="max-height: 300px; overflow-y: auto;">
                                                            @foreach($gastosAgrupados as $index => $gasto)
                                                                @php
                                                                    $porcentajeGasto = $totalGastos > 0 
                                                                        ? ($gasto['total'] / $totalGastos) * 100 
                                                                        : 0;
                                                                @endphp
                                                                
                                                                <div class="list-group-item px-0 py-2 border-0">
                                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                                        <div style="width: 60%;">
                                                                            <small class="text-truncate d-block">
                                                                                {{ $index + 1 }}. {{ $gasto['nombre'] }}
                                                                                @if($gasto['contador'] > 1)
                                                                                    <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">
                                                                                        {{ $gasto['contador'] }} trans.
                                                                                    </span>
                                                                                @endif
                                                                            </small>
                                                                            <div class="progress" style="height: 4px; margin-top: 2px;">
                                                                                <div class="progress-bar bg-danger" 
                                                                                    style="width: {{ min($porcentajeGasto, 100) }}%"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-end" style="min-width: 100px;">
                                                                            <small class="text-danger d-block">
                                                                                $ {{ number_format($gasto['total'], 2, ',', '.') }}
                                                                            </small>
                                                                            <small class="text-muted">
                                                                                {{ number_format($porcentajeGasto, 1) }}%
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        
                                                        <!-- Resumen TOTAL (siempre visible, fuera del scroll) -->
                                                        <div class="list-group-item px-0 py-2 border-0 bg-light mt-2">
                                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                                <div>
                                                                    <small><strong>Total {{ $totalCategorias }} categorías:</strong></small>
                                                                    <small class="text-muted ms-2">
                                                                        {{ count($ListadoGastosPeriodo) }} transacciones
                                                                    </small>
                                                                </div>
                                                                <div class="text-end">
                                                                    <small class="text-danger">
                                                                        <strong>$ {{ number_format($totalGastos, 2, ',', '.') }}</strong>
                                                                    </small>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        100%
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    @else
                                                        <div class="list-group-item px-0 py-2 border-0">
                                                            <small class="text-muted">No hay datos de gastos</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-muted">
                                                <i class="bi {{ $porcentajeSobreVentas < 20 ? 'bi-check-circle text-success' : ($porcentajeSobreVentas < 30 ? 'bi-exclamation-triangle text-warning' : 'bi-x-circle text-danger') }} me-1"></i>
                                                {{ $porcentajeSobreVentas < 20 ? 'Gastos controlados' : ($porcentajeSobreVentas < 30 ? 'Gastos moderados' : 'Gastos elevados') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- Tab 2: Estado de cuentas -->
                        <div class="tab-pane fade" 
                            id="productos" 
                            role="tabpanel" 
                            aria-labelledby="productos-tab">
                            
                            <!-- Filtros y exportación -->
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="d-flex gap-2">
                                        <!-- Filtro -->
                                        <div style="width: 200px;">
                                            <select class="form-select form-select-sm" id="filtroTipoMovimiento">
                                                <option value="">Todos los movimientos</option>
                                                <option value="ingreso">Solo ingresos</option>
                                                <option value="egreso">Solo egresos</option>
                                            </select>
                                        </div>

                                        <!-- Botón de exportar PDF -->
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="exportarPDF()">
                                                <i class="bi bi-file-pdf me-1"></i> PDF
                                            </button>
                                        </div>
                                        
                                        <!-- Botón de exportar -->
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="exportarEDCOperaciones()">
                                                <i class="bi bi-download me-1"></i> Exportar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contenido de las pestañas internas -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped" id="tablaEDC">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th class="text-end">Ingreso</th>
                                            <th class="text-end">Egreso</th>
                                            <th class="text-end">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $detallesEDC = isset($EDCOperaciones) && isset($EDCOperaciones->Detalles)
                                                ? array_filter((array) $EDCOperaciones->Detalles, fn($d) => is_object($d))
                                                : [];

                                            usort($detallesEDC, function ($a, $b) {
                                                $timeA = $a->Fecha instanceof \Carbon\Carbon
                                                    ? $a->Fecha->timestamp
                                                    : strtotime($a->Fecha ?? '');

                                                $timeB = $b->Fecha instanceof \Carbon\Carbon
                                                    ? $b->Fecha->timestamp
                                                    : strtotime($b->Fecha ?? '');

                                                return $timeB <=> $timeA;
                                            });                                        

                                            $totalIngreso = 0;
                                            $totalEgreso = 0;
                                            $ultimoSaldo = 0;
                                        @endphp

                                        @forelse ($detallesEDC as $detalle)
                                            @php
                                                $ingreso = (float) ($detalle->MontoDivisa ?? 0);
                                                $egreso  = (float) ($detalle->MontoPagoDivisa ?? 0);
                                                $saldo   = (float) ($detalle->SaldoDivisa ?? 0);

                                                $fecha = $detalle->Fecha instanceof \Carbon\Carbon
                                                    ? $detalle->Fecha->format('d/m/Y')
                                                    : \Carbon\Carbon::parse($detalle->Fecha)->format('d/m/Y');

                                                // Acumular totales
                                                $totalIngreso += $ingreso;
                                                $totalEgreso += $egreso;
                                                $ultimoSaldo = $saldo; // El último saldo será el final
                                            @endphp

                                            <tr class="fila-movimiento" 
                                                data-tipo="{{ $ingreso > 0 ? 'ingreso' : ($egreso > 0 ? 'egreso' : 'neutro') }}"
                                                data-fecha="{{ $detalle->Fecha instanceof \Carbon\Carbon ? $detalle->Fecha->format('Y-m-d') : $detalle->Fecha }}">
                                                <td><span class="badge bg-secondary">{{ $fecha }}</span></td>
                                                <td>
                                                    <div class="fw-semibold">{{ $detalle->Descripcion }}</div>
                                                    @if($detalle->Referencia)
                                                        <div class="text-muted small">
                                                            <i class="bi bi-hash me-1"></i>{{ $detalle->Referencia }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end text-success">
                                                    {{ $ingreso > 0 ? '$ '.number_format($ingreso,2,',','.') : '-' }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ $egreso > 0 ? '$ '.number_format($egreso,2,',','.') : '-' }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    $ {{ number_format($saldo,2,',','.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No hay registros de EDC Operaciones para mostrar
                                                </td>
                                            </tr>
                                        @endforelse

                                        <!-- Mostrar totales si hay registros -->
                                        @if(count($detallesEDC) > 0)
                                            <tr class="table-light fw-bold">
                                                <td colspan="2" class="text-end">TOTALES:</td>
                                                <td class="text-end text-success">
                                                    $ {{ number_format($totalIngreso, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    $ {{ number_format($totalEgreso, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-info">
                                                    $ {{ number_format($ultimoSaldo, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab 3: Ventas -->
                        <div class="tab-pane fade" 
                            id="clientes" 
                            role="tabpanel" 
                            aria-labelledby="clientes-tab">
                            
                            <!-- Encabezado con filtros -->
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="bi bi-cart-check text-primary me-2"></i>Ventas Diarias
                                        </h5>
                                        <p class="text-muted mb-0 small">
                                            {{ count($Ventas['listaVentasDiarias'] ?? []) }} registros encontrados
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <!-- Filtro por sucursal -->
                                        @if(!empty($Ventas['listaVentasDiarias']))
                                            @php
                                                $sucursalesUnicas = collect($Ventas['listaVentasDiarias'])
                                                    ->map(function ($venta) {
                                                        return [
                                                            'id' => $venta->sucursalId,
                                                            'nombre' => $venta->nombreSucursal ?? 'Sucursal ' . $venta->sucursalId
                                                        ];
                                                    })
                                                    ->unique('id')
                                                    ->sortBy('nombre')
                                                    ->values();
                                            @endphp
                                            
                                            @if($sucursalesUnicas->count() > 1)
                                            <div style="width: 200px;">
                                                <select class="form-select form-select-sm" id="filtroSucursalVentasDiarias">
                                                    <option value="">Todas las sucursales</option>
                                                    @foreach($sucursalesUnicas as $sucursal)
                                                        <option value="{{ $sucursal['id'] }}">
                                                            {{ $sucursal['nombre'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endif
                                        @endif

                                        <!-- Botón de exportar PDF -->
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="exportarPDFventas()">
                                                <i class="bi bi-file-pdf me-1"></i> PDF
                                            </button>
                                        </div>
                                        
                                        <!-- Botón exportar -->
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="exportarVentasDiarias()">
                                            <i class="bi bi-download me-1"></i> Exportar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de ventas -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="tablaVentasDiarias">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;">Fecha</th>
                                            <th style="width: 120px;">Sucursal</th>
                                            <th class="text-end" style="width: 100px;">Cantidad</th>
                                            <th class="text-end" style="width: 120px;">Costo Divisa</th>
                                            <th class="text-end" style="width: 120px;">Total Divisa</th>
                                            <th class="text-end" style="width: 120px;">Total Bs</th>
                                            <th class="text-end" style="width: 120px;">Tasa Cambio</th>
                                            <th style="width: 100px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $ventasDiarias = $Ventas['listaVentasDiarias'] ?? [];
                                            $totalCantidad = 0;
                                            $totalCostoDivisa = 0;
                                            $totalTotalDivisa = 0;
                                            $totalTotalBs = 0;
                                        @endphp
                                        
                                        @forelse($ventasDiarias as $venta)
                                            @php
                                                // Acumular totales
                                                $totalCantidad += $venta->cantidad ?? 0;
                                                $totalCostoDivisa += (float)($venta->costoDivisa ?? 0);
                                                $totalTotalDivisa += (float)($venta->totalDivisa ?? 0);
                                                $totalTotalBs += (float)($venta->totalBs ?? 0);
                                                
                                                // Formatear fecha
                                                $fecha = $venta->fecha instanceof \Carbon\Carbon
                                                    ? $venta->fecha->format('d/m/Y')
                                                    : \Carbon\Carbon::parse($venta->fecha ?? now())->format('d/m/Y');
                                                
                                                // Determinar color de sucursal
                                                $colorSucursal = match($venta->sucursalId) {
                                                    1 => 'primary',
                                                    2 => 'success',
                                                    3 => 'warning',
                                                    4 => 'info',
                                                    5 => 'danger',
                                                    6 => 'secondary',
                                                    7 => 'dark',
                                                    default => 'secondary'
                                                };
                                                
                                                // Calcular utilidad
                                                $costo = (float)($venta->costoDivisa ?? 0);
                                                $ventaDivisa = (float)($venta->totalDivisa ?? 0);
                                                $utilidad = $ventaDivisa - $costo;
                                                $margen = $costo > 0 ? ($utilidad / $costo) * 100 : 0;
                                            @endphp
                                            
                                            <tr class="fila-venta" data-sucursal="{{ $venta->sucursalId }}">
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $fecha }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $colorSucursal }}">
                                                        {{ $venta->nombreSucursal }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($venta->cantidad ?? 0, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-danger">
                                                        $ {{ number_format($venta->costoDivisa ?? 0, 2, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-success">
                                                        $ {{ number_format($venta->totalDivisa ?? 0, 2, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-primary">
                                                        Bs {{ number_format($venta->totalBs ?? 0, 2, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <small class="text-muted">
                                                        {{ number_format($venta->tasaDeCambio ?? 0, 2, ',', '.') }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" 
                                                                class="btn btn-outline-info"
                                                                data-bs-toggle="tooltip"
                                                                title="Ver detalles"
                                                                onclick="verDetalleVenta({{ $loop->index }})">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="bi bi-cart-x display-6 d-block mb-2"></i>
                                                    No hay registros de ventas para mostrar
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    
                                    <!-- Totales -->
                                    @if(count($ventasDiarias) > 0)
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td colspan="2" class="text-end">TOTALES:</td>
                                                <td class="text-end">
                                                    {{ number_format($totalCantidad, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    $ {{ number_format($totalCostoDivisa, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-success">
                                                    $ {{ number_format($totalTotalDivisa, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end text-primary">
                                                    Bs {{ number_format($totalTotalBs, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <small class="text-muted">Promedio:</small><br>
                                                    {{ number_format(collect($ventasDiarias)->avg('tasaDeCambio') ?? 0, 2, ',', '.') }}
                                                </td>
                                                <td></td>
                                            </tr>
                                            
                                            <!-- Resumen -->
                                            <tr style="background-color: #f8f9fa;">
                                                <td colspan="2" class="text-end">
                                                    <i class="bi bi-graph-up me-1"></i>RESUMEN:
                                                </td>
                                                <td colspan="2" class="text-center">
                                                    @php
                                                        $utilidadTotal = $totalTotalDivisa - $totalCostoDivisa;
                                                        $margenTotal = $totalCostoDivisa > 0 ? ($utilidadTotal / $totalCostoDivisa) * 100 : 0;
                                                        $colorUtilidad = $utilidadTotal >= 0 ? 'success' : 'danger';
                                                        $iconoUtilidad = $utilidadTotal >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                                    @endphp
                                                    <span class="text-{{ $colorUtilidad }}">
                                                        <i class="bi {{ $iconoUtilidad }} me-1"></i>
                                                        Utilidad: $ {{ number_format(abs($utilidadTotal), 2, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td colspan="2" class="text-center">
                                                    <span class="text-{{ $margenTotal >= 20 ? 'success' : ($margenTotal >= 10 ? 'warning' : 'danger') }}">
                                                        Margen: {{ number_format($margenTotal, 1) }}%
                                                    </span>
                                                </td>
                                                <td colspan="2">
                                                    <small class="text-muted">
                                                        {{ count($ventasDiarias) }} días
                                                    </small>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                            
                            <!-- Estadísticas adicionales -->
                            @if(count($ventasDiarias) > 0)
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card border-primary">
                                            <div class="card-body text-center py-3">
                                                <h6 class="card-subtitle mb-2 text-muted">Promedio Diario</h6>
                                                <h4 class="card-title text-primary mb-1">
                                                    $ {{ number_format($totalTotalDivisa / max(count($ventasDiarias), 1), 2, ',', '.') }}
                                                </h4>
                                                <small class="text-muted">
                                                    {{ number_format($totalCantidad / max(count($ventasDiarias), 1), 1) }} unidades/día
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-success">
                                            <div class="card-body text-center py-3">
                                                <h6 class="card-subtitle mb-2 text-muted">Ticket Promedio</h6>
                                                <h4 class="card-title text-success mb-1">
                                                    $ {{ number_format($totalTotalDivisa / max($totalCantidad, 1), 2, ',', '.') }}
                                                </h4>
                                                <small class="text-muted">
                                                    por unidad vendida
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-info">
                                            <div class="card-body text-center py-3">
                                                <h6 class="card-subtitle mb-2 text-muted">Eficiencia</h6>
                                                <h4 class="card-title text-info mb-1">
                                                    {{ number_format($margenTotal, 1) }}%
                                                </h4>
                                                <small class="text-muted">
                                                    margen promedio
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Modal para ver detalles de venta -->
                        <div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-cart-check me-2"></i>Detalle de Venta
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="detalleVentaContent">
                                        <!-- Contenido cargado dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 4: Gastos -->
                        <div class="tab-pane fade" id="sucursales" role="tabpanel" aria-labelledby="sucursales-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="bi bi-receipt-cutoff text-danger me-2"></i>
                                                Listado Detallado de Gastos
                                            </h5>
                                            <p class="text-muted mb-0 small">
                                                Registros ordenados por fecha
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-list-check me-1"></i>
                                                {{ count($ListadoGastosPeriodo ?? []) }} registros
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    @if(!empty($ListadoGastosPeriodo))
                                        @php
                                            // Ordenar por fecha descendente
                                            $gastosOrdenados = collect($ListadoGastosPeriodo)
                                                ->values()
                                                ->all();
                                            
                                            // Calcular totales
                                            $totalDivisa = collect($gastosOrdenados)->sum('MontoDivisaAbonado');
                                            $totalLocal = collect($gastosOrdenados)->sum('MontoAbonado');
                                        @endphp
                                        
                                        <!-- Filtros y búsqueda -->
                                        <div class="p-3 border-bottom bg-light">
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-search"></i>
                                                        </span>
                                                        <input type="text" class="form-control" placeholder="Buscar..." 
                                                            id="buscarGastos">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" id="filtroSucursal">
                                                        <option value="">Todas las sucursales</option>
                                                        @if(!empty($ListadoGastosPeriodo))
                                                            @php
                                                                // Agrupar sucursales únicas por SucursalId
                                                                $sucursalesUnicas = [];
                                                                
                                                                foreach ($ListadoGastosPeriodo as $gasto) {
                                                                    $sucursalId = $gasto->SucursalId ?? null;
                                                                    $sucursalNombre = $gasto->SucursalNombre ?? "Sucursal {$sucursalId}";
                                                                    
                                                                    if ($sucursalId && !isset($sucursalesUnicas[$sucursalId])) {
                                                                        $sucursalesUnicas[$sucursalId] = [
                                                                            'id' => $sucursalId,
                                                                            'nombre' => $sucursalNombre,
                                                                            'contador' => 0
                                                                        ];
                                                                    }
                                                                    
                                                                    if ($sucursalId) {
                                                                        $sucursalesUnicas[$sucursalId]['contador']++;
                                                                    }
                                                                }
                                                                
                                                                // Ordenar por nombre de sucursal
                                                                usort($sucursalesUnicas, function($a, $b) {
                                                                    return strcmp($a['nombre'], $b['nombre']);
                                                                });
                                                            @endphp
                                                            
                                                            @foreach($sucursalesUnicas as $sucursal)
                                                                <option value="{{ $sucursal['id'] }}">
                                                                    {{ $sucursal['nombre'] }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" id="filtroCategoria">
                                                        <option value="">Todas las categorías</option>
                                                        @foreach(collect($gastosOrdenados)->pluck('CategoriaId')->unique() as $categoriaId)
                                                            @php
                                                                $nombreCategoria = collect($gastosOrdenados)
                                                                    ->where('CategoriaId', $categoriaId)
                                                                    ->first()->Nombre ?? "Categoría {$categoriaId}";
                                                            @endphp
                                                            <option value="{{ $categoriaId }}">
                                                                {{ $nombreCategoria }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Botón de exportar PDF -->
                                                <div class="col-md-1"> <!-- Añadido: col-md-1 para PDF -->
                                                    <button class="btn btn-sm btn-outline-primary w-100" 
                                                            onclick="exportarPDFgastos()">
                                                        <i class="bi bi-file-pdf me-1"></i> PDF
                                                    </button>
                                                </div>

                                                <div class="col-md-2"> <!-- Cambiado: col-md-2 para Exportar -->
                                                    <button class="btn btn-sm btn-outline-primary w-100" 
                                                            onclick="exportarGastos()">
                                                        <i class="bi bi-download me-1"></i> Exportar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Tabla responsive -->
                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover mb-0" id="tablaGastos">
                                                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                                    <tr>
                                                        <th style="width: 50px;">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                            </div>
                                                        </th>
                                                        <th style="width: 100px;">Fecha</th>
                                                        <th style="width: 120px;">No. Operación</th>
                                                        <th>Descripción</th>
                                                        <th style="width: 150px;">Categoría</th>
                                                        <th style="width: 120px;">Monto ($)</th>
                                                        <th style="width: 120px;">Monto (Bs)</th>
                                                        <th style="width: 80px;">Sucursal</th>
                                                        <th style="width: 80px;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($gastosOrdenados as $gasto)
                                                        @php
                                                            $fechaFormateada = date('d/m/Y', strtotime($gasto->Fecha));
                                                            $horaFormateada = date('H:i', strtotime($gasto->Fecha));
                                                            $colorSucursal = match($gasto->SucursalId) {
                                                                '5' => 'primary',
                                                                '4' => 'success',
                                                                '8' => 'warning',
                                                                default => 'secondary'
                                                            };
                                                            
                                                            $tipoBadge = match($gasto->Tipo) {
                                                                '2' => ['color' => 'warning', 'text' => 'Gasto'],
                                                                '3' => ['color' => 'info', 'text' => 'Servicio'],
                                                                default => ['color' => 'secondary', 'text' => 'Otro']
                                                            };
                                                            
                                                            $estatusBadge = match($gasto->Estatus) {
                                                                '4' => ['color' => 'success', 'text' => 'Completado'],
                                                                '2' => ['color' => 'warning', 'text' => 'Pendiente'],
                                                                '1' => ['color' => 'danger', 'text' => 'Cancelado'],
                                                                default => ['color' => 'secondary', 'text' => 'Desconocido']
                                                            };
                                                        @endphp
                                                        
                                                        <tr class="align-middle" 
                                                            data-sucursal="{{ $gasto->SucursalId }}"
                                                            data-categoria="{{ $gasto->CategoriaId }}">
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input select-item" 
                                                                        type="checkbox" 
                                                                        value="{{ $gasto->ID }}">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <small class="text-muted">{{ $fechaFormateada }}</small>
                                                                    <small class="text-muted">{{ $horaFormateada }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <code class="small">{{ $gasto->NumeroOperacion }}</code>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div class="fw-medium">
                                                                        {{ $gasto->Descripcion }}
                                                                    </div>
                                                                    @if($gasto->Observacion)
                                                                        <small class="text-muted d-block">
                                                                            {{ $gasto->Observacion }}
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="badge bg-info mb-1">
                                                                        {{ $gasto->Nombre }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="text-danger fw-bold">
                                                                    ${{ number_format($gasto->MontoDivisaAbonado, 2) }}
                                                                </div>
                                                                <small class="text-muted">
                                                                    TC: {{ $gasto->TasaDeCambio }}
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="text-dark fw-medium">
                                                                    Bs {{ number_format($gasto->MontoAbonado, 2, ',', '.') }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <span class="badge bg-{{ $colorSucursal }} mb-1">
                                                                        S-{{ $gasto->SucursalId }}
                                                                    </span>
                                                                    <small class="text-muted">
                                                                        {{ $gasto->SucursalOrigenId }}
                                                                    </small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <button type="button" 
                                                                            class="btn btn-outline-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Ver detalles"
                                                                            onclick="verDetalleGasto('{{ $gasto->ID }}')">
                                                                        <i class="bi bi-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Resumen y paginación -->
                                        <div class="p-3 border-top bg-light">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="d-flex gap-3">
                                                        <div>
                                                            <small class="text-muted d-block">Total en Divisa:</small>
                                                            <strong class="text-danger">${{ number_format($totalDivisa, 2) }}</strong>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Total en Bs:</small>
                                                            <strong class="text-dark">Bs {{ number_format($totalLocal, 2, ',', '.') }}</strong>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Registros:</small>
                                                            <strong>{{ count($gastosOrdenados) }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        
                                    @else
                                        <!-- Estado vacío -->
                                        <div class="text-center py-5">
                                            <i class="bi bi-receipt display-4 text-muted mb-3"></i>
                                            <h5 class="text-muted">No hay gastos registrados</h5>
                                            <p class="text-muted small mb-4">
                                                No se encontraron gastos para el período seleccionado
                                            </p>
                                            <button class="btn btn-primary">
                                                <i class="bi bi-plus-circle me-1"></i> Registrar primer gasto
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Modal para ver detalles -->
                        <div class="modal fade" id="modalDetalleGasto" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-receipt me-2"></i>Detalle de Gasto
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="detalleGastoContent">
                                        <!-- Contenido cargado dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 5: Cuentas pro pagar -->
                        <div class="tab-pane fade" 
                            id="metodos" 
                            role="tabpanel" 
                            aria-labelledby="metodos-tab">
                            <div class="row">
                                <div class="col-md-5">
                                    <h5><i class="bi bi-credit-card-2-front text-danger me-2"></i>Distribución de Pagos</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Método</th>
                                                    <th class="text-end">Cantidad</th>
                                                    <th class="text-end">Monto</th>
                                                    <th class="text-end">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach([
                                                    ['método' => 'Tarjeta Crédito', 'color' => 'primary', 'porcentaje' => 45],
                                                    ['método' => 'Tarjeta Débito', 'color' => 'success', 'porcentaje' => 25],
                                                    ['método' => 'Efectivo', 'color' => 'warning', 'porcentaje' => 20],
                                                    ['método' => 'Transferencia', 'color' => 'info', 'porcentaje' => 8],
                                                    ['método' => 'QR/Zelle', 'color' => 'secondary', 'porcentaje' => 2]
                                                ] as $metodo)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-{{ $metodo['color'] }} me-2">●</span>
                                                        {{ $metodo['método'] }}
                                                    </td>
                                                    <td class="text-end">{{ rand(100, 500) }}</td>
                                                    <td class="text-end">$ {{ number_format(rand(10000, 50000), 2, ',', '.') }}</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-{{ $metodo['color'] }}">
                                                            {{ $metodo['porcentaje'] }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5><i class="bi bi-cash-stack text-success me-2"></i>Comparativa por Método</h5>
                                    <div class="mt-4">
                                        <!-- Gráfico simulado -->
                                        <div class="row align-items-end" style="height: 250px;">
                                            @foreach([45, 25, 20, 8, 2] as $index => $altura)
                                            <div class="col text-center">
                                                <div class="bg-{{ ['primary', 'success', 'warning', 'info', 'secondary'][$index] }} 
                                                            mx-auto rounded-top" 
                                                    style="height: {{ $altura * 2 }}px; width: 40px;">
                                                </div>
                                                <small class="d-block mt-2">
                                                    {{ ['Crédito', 'Débito', 'Efectivo', 'Transferencia', 'QR'][$index] }}
                                                </small>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="alert alert-warning mt-4">
                                        <i class="bi bi-lightbulb me-2"></i>
                                        <strong>Insight:</strong> Las ventas con tarjeta de crédito tienen un ticket promedio 35% más alto que las ventas en efectivo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card footer con resumen -->
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="bi bi-clock-history me-1"></i>
                                Última actualización: {{ now()->format('d/m/Y H:i:s') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    document.getElementById('form-periodo').addEventListener('submit', function(e) {
        const periodo = document.getElementById('periodoEstadisticas').value; // YYYY-MM

        if (periodo) {
            const partes = periodo.split('-'); 
            document.getElementById('anio').value = partes[0]; // YYYY
            document.getElementById('mes').value = partes[1];  // MM
        }
    });

    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Filtro de búsqueda
        const buscarInput = document.getElementById('buscarGastos');
        const filtroSucursal = document.getElementById('filtroSucursal');
        const filtroCategoria = document.getElementById('filtroCategoria');
        
        function filtrarTabla() {
            const busqueda = buscarInput.value.toLowerCase();
            const sucursal = filtroSucursal.value;
            const categoria = filtroCategoria.value;
            
            const filas = document.querySelectorAll('#tablaGastos tbody tr');
            
            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                const filaSucursal = fila.dataset.sucursal;
                const filaCategoria = fila.dataset.categoria;
                
                const coincideBusqueda = textoFila.includes(busqueda);
                const coincideSucursal = !sucursal || filaSucursal === sucursal;
                const coincideCategoria = !categoria || filaCategoria === categoria;
                
                fila.style.display = (coincideBusqueda && coincideSucursal && coincideCategoria) 
                    ? '' 
                    : 'none';
            });
        }
        
        buscarInput.addEventListener('input', filtrarTabla);
        filtroSucursal.addEventListener('change', filtrarTabla);
        filtroCategoria.addEventListener('change', filtrarTabla);
        
        // Seleccionar todos
        document.getElementById('selectAll').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });

        // Filtro Tab de Ventas Diarias
        // Capturar el select de sucursales
        const filtroSucursalDiario = document.getElementById('filtroSucursalVentasDiarias');
        const tablaVentas = document.getElementById('tablaVentasDiarias');
        const filas = tablaVentas.querySelectorAll('tbody tr.fila-venta');

        if(filtroSucursalDiario) {
            filtroSucursalDiario.addEventListener('change', function() {
                const sucursalSeleccionada = filtroSucursalDiario.value; // valor string

                filas.forEach(fila => {
                    const filaSucursal = fila.dataset.sucursal?.toString() || '';

                    // Mostrar fila si no hay sucursal seleccionada o si coincide
                    fila.style.display = (!sucursalSeleccionada || filaSucursal === sucursalSeleccionada) ? '' : 'none';
                });
            });
        }
    });

    // Funciones de acción
    function verDetalleGasto(id) {
        
        // Buscar el gasto en la lista (que ya está cargada en memoria)
        const gastos = @json($ListadoGastosPeriodo); // Pasar la lista a JavaScript
        
        // Encontrar el gasto por ID
        const gasto = gastos.find(g => g.ID === id.toString());
        
        if (!gasto) {
            alert('No se encontró el gasto con ID: ' + id);
            return;
        }
        
        // Formatear fecha
        const fecha = new Date(gasto.Fecha);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Formatear montos
        const montoDivisa = parseFloat(gasto.MontoDivisaAbonado).toFixed(2);
        const montoLocal = parseFloat(gasto.MontoAbonado).toFixed(2);
        
        // Determinar estado
        const estado = {
            '4': ['Completado', 'success'],
            '2': ['Pendiente', 'warning'],
            '1': ['Cancelado', 'danger']
        }[gasto.Estatus] || ['Desconocido', 'secondary'];
        
        // Determinar tipo
        const tipo = {
            '2': ['Gasto', 'warning'],
            '3': ['Servicio', 'info'],
            '0': ['Otro', 'secondary'],
            '5': ['Otro', 'secondary']
        }[gasto.Tipo] || ['No especificado', 'secondary'];
        
        // Generar HTML del detalle
        document.getElementById('detalleGastoContent').innerHTML = `
            <div class="row">
                <!-- Información General -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-info-circle me-2"></i>Información General
                    </h6>
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-dark">${gasto.ID}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Operación:</dt>
                        <dd class="col-sm-8">
                            <code>${gasto.NumeroOperacion}</code>
                        </dd>
                        
                        <dt class="col-sm-4">Fecha:</dt>
                        <dd class="col-sm-8">
                            <i class="bi bi-calendar3 me-1"></i>${fechaFormateada}
                        </dd>
                        
                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">${gasto.Descripcion}</dd>
                        
                        <dt class="col-sm-4">Tipo:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-${tipo[1]}">${tipo[0]}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-${estado[1]}">${estado[0]}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Forma de Pago:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">${gasto.FormaDePago}</span>
                        </dd>
                    </dl>
                </div>
                
                <!-- Detalles Financieros -->
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-cash-stack me-2"></i>Detalles Financieros
                    </h6>
                    <dl class="row">
                        <dt class="col-sm-4">Monto ($):</dt>
                        <dd class="col-sm-8 text-danger fw-bold">
                            $${montoDivisa}
                        </dd>
                        
                        <dt class="col-sm-4">Monto (Bs):</dt>
                        <dd class="col-sm-8 fw-bold">
                            Bs ${montoLocal}
                        </dd>
                        
                        <dt class="col-sm-4">Tasa de cambio:</dt>
                        <dd class="col-sm-8">${gasto.TasaDeCambio}</dd>
                        
                        <dt class="col-sm-4">Divisa:</dt>
                        <dd class="col-sm-8">
                            ${gasto.DivisaId === '0' ? 'Dólares' : 'Otra'}
                        </dd>
                    </dl>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-building me-2"></i>Información de Sucursal
                    </h6>
                    <dl class="row">
                        <dt class="col-sm-4">Sucursal ID:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary">${gasto.SucursalId}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Sucursal Origen:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">${gasto.SucursalNombre}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Categoría:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info text-truncate d-inline-block" 
                                style="max-width: 200px;"
                                data-bs-toggle="tooltip" 
                                title="${gasto.Nombre}">
                                ${gasto.Nombre}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Cédula/RIF:</dt>
                        <dd class="col-sm-8">${gasto.Cedula}</dd>
                    </dl>
                </div>
            </div>
            
            <!-- Observaciones -->
            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-chat-left-text me-2"></i>Observaciones
                    </h6>
                    <div class="alert alert-light">
                        ${gasto.Observacion || '<span class="text-muted">Sin observaciones</span>'}
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="row mt-4 pt-3 border-top">
                <div class="col-12 d-flex justify-content-end">            
                    <button type="button" class="btn btn-warning" onclick="imprimirGasto('${gasto.ID}')">
                        <i class="bi bi-printer me-1"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        `;
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalDetalleGasto'));
        modal.show();
    }

    function exportarGastos() {
        const tabla = document.getElementById('tablaGastos');
        
        // Obtener datos de la tabla
        const datos = [];
        
        // Encabezados (omitir checkbox)
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            if (!th.querySelector('input[type="checkbox"]')) {
                headers.push(th.textContent.trim());
            }
        });
        datos.push(headers);
        
        // Filas visibles
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display !== 'none') {
                const rowData = [];
                fila.querySelectorAll('td').forEach((td, index) => {
                    if (index !== 0) { // Omitir checkbox
                        rowData.push(td.textContent.trim().replace(/\n/g, ' '));
                    }
                });
                datos.push(rowData);
            }
        });
        
        // Crear workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);
        
        // Ajustar anchos de columna
        const colWidths = headers.map(() => ({ wch: 20 }));
        ws['!cols'] = colWidths;
        
        // Agregar hoja al workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Gastos');
        
        // Generar y descargar archivo
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `reporte_gastos_${fecha}.xlsx`);
    }

    function imprimirGasto(id) {
        // Obtener los datos del gasto directamente de la lista
        const gastos = @json($ListadoGastosPeriodo ?? []);
        const gasto = gastos.find(g => g.ID === id.toString());
        
        if (!gasto) {
            alert('Gasto no encontrado');
            return;
        }
        
        // Formatear datos
        const fecha = new Date(gasto.Fecha);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Crear iframe para impresión
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        
        document.body.appendChild(iframe);
        
        const doc = iframe.contentWindow.document;
        
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Comprobante de Gasto #${id}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        font-size: 12pt;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .title {
                        font-size: 18pt;
                        font-weight: bold;
                        color: #dc3545;
                        margin-bottom: 5px;
                    }
                    .subtitle {
                        font-size: 14pt;
                        color: #6c757d;
                        margin-bottom: 10px;
                    }
                    .info-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    .info-table th {
                        text-align: left;
                        padding: 8px;
                        background-color: #f8f9fa;
                        border: 1px solid #dee2e6;
                        width: 30%;
                    }
                    .info-table td {
                        padding: 8px;
                        border: 1px solid #dee2e6;
                    }
                    .monto-destacado {
                        font-size: 14pt;
                        font-weight: bold;
                    }
                    .monto-usd {
                        color: #dc3545;
                    }
                    .footer {
                        margin-top: 50px;
                        text-align: center;
                        font-size: 10pt;
                        color: #6c757d;
                    }
                    .firma {
                        margin-top: 100px;
                        border-top: 1px solid #000;
                        width: 300px;
                        margin-left: auto;
                        margin-right: auto;
                        padding-top: 10px;
                        text-align: center;
                    }
                    @media print {
                        @page { margin: 1cm; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="title">COMPROBANTE DE GASTO</div>
                    <div class="subtitle">N° ${gasto.NumeroOperacion}</div>
                    <div>Fecha: ${fechaFormateada}</div>
                </div>
                
                <table class="info-table">
                    <tr>
                        <th>ID del Gasto:</th>
                        <td><strong>${gasto.ID}</strong></td>
                    </tr>
                    <tr>
                        <th>Descripción:</th>
                        <td>${gasto.Descripcion}</td>
                    </tr>
                    <tr>
                        <th>Categoría:</th>
                        <td>${gasto.Nombre} (ID: ${gasto.CategoriaId})</td>
                    </tr>
                    <tr>
                        <th>Observación:</th>
                        <td>${gasto.Observacion || 'Ninguna'}</td>
                    </tr>
                    <tr>
                        <th>Monto en Dólares:</th>
                        <td class="monto-destacado monto-usd">$${parseFloat(gasto.MontoDivisaAbonado).toFixed(2)}</td>
                    </tr>
                    <tr>
                        <th>Monto en Bolívares:</th>
                        <td class="monto-destacado">Bs ${parseFloat(gasto.MontoAbonado).toFixed(2)}</td>
                    </tr>
                    <tr>
                        <th>Tasa de Cambio:</th>
                        <td>${gasto.TasaDeCambio}</td>
                    </tr>
                    <tr>
                        <th>Sucursal:</th>
                        <td>Sucursal ${gasto.SucursalId} (Origen: ${gasto.SucursalOrigenId})</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>${gasto.Estatus === '4' ? 'COMPLETADO' : 'PENDIENTE'}</td>
                    </tr>
                </table>
                
                <div class="firma">
                    <div>_________________________</div>
                    <div>Firma Autorizada</div>
                </div>
                
                <div class="footer">
                    <p>Comprobante generado automáticamente</p>
                    <p>Fecha de impresión: ${new Date().toLocaleString()}</p>
                </div>
                
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        🖨️ Imprimir
                    </button>
                </div>
            </body>
            </html>
        `);
        doc.close();
        
        // Esperar a que cargue y luego imprimir
        iframe.onload = function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            
            // Limpiar después de un tiempo
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        };
    }

    function exportarEDCOperaciones() {
        const tabla = document.getElementById('tablaEDC');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Obtener datos de la tabla
        const datos = [];
        
        // Encabezados
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            headers.push(th.textContent.trim());
        });
        datos.push(headers);
        
        // Filas del cuerpo de la tabla
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display !== 'none') {
                const rowData = [];
                fila.querySelectorAll('td').forEach((td, index) => {
                    // Obtener texto limpio
                    let texto = td.textContent.trim().replace(/\n/g, ' ');
                    
                    // Si tiene badge, tomar el texto del badge
                    const badge = td.querySelector('.badge');
                    if (badge) {
                        texto = badge.textContent.trim();
                    }
                    
                    rowData.push(texto);
                });
                datos.push(rowData);
            }
        });
        
        // Crear workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);
        
        // Ajustar anchos de columna
        const colWidths = headers.map(() => ({ wch: 20 }));
        ws['!cols'] = colWidths;
        
        // Agregar hoja al workbook
        XLSX.utils.book_append_sheet(wb, ws, 'EDC Operaciones');
        
        // Generar y descargar archivo
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `EDC_Operaciones_${fecha}.xlsx`);
    }

    // Filtro por tipo de movimiento
    document.getElementById('filtroTipoMovimiento').addEventListener('change', function() {
        let tipo = this.value;
        let filas = document.querySelectorAll('.fila-movimiento');
        
        filas.forEach(fila => {
            if (tipo === '' || fila.dataset.tipo === tipo) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

    function exportarVentasDiarias() {

        const tabla = document.getElementById('tablaVentasDiarias');

        if (!tabla) {
            alert('No se encontró la tabla de Ventas Diarias');
            return;
        }

        const datos = [];

        /* ==========================
        ENCABEZADOS
        ========================== */
        const headers = [];
        tabla.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });
        datos.push(headers);

        /* ==========================
        FILAS VISIBLES
        ========================== */
        tabla.querySelectorAll('tbody tr.fila-venta').forEach(fila => {

            // Exportar solo filas visibles (respeta filtro por sucursal)
            if (fila.style.display !== 'none') {

                const rowData = [];

                fila.querySelectorAll('td').forEach(td => {
                    let texto = td.textContent
                        .replace(/\n/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    // Si hay badge, usar su texto
                    const badge = td.querySelector('.badge');
                    if (badge) {
                        texto = badge.textContent.trim();
                    }

                    rowData.push(texto);
                });

                datos.push(rowData);
            }
        });

        if (datos.length <= 1) {
            alert('No hay datos visibles para exportar');
            return;
        }

        /* ==========================
        CREAR EXCEL
        ========================== */
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(datos);

        // Ajustar ancho de columnas automáticamente
        ws['!cols'] = headers.map(() => ({ wch: 18 }));

        XLSX.utils.book_append_sheet(wb, ws, 'Ventas Diarias');

        /* ==========================
        DESCARGA
        ========================== */
        const fecha = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Ventas_Diarias_${fecha}.xlsx`);
    }
    
    function verDetalleVenta(index) {
        // Mostrar alert con el índice recibido
        alert(`Índice de venta recibido: ${index}\n\nEsta función mostrará los detalles de la venta en la posición ${index} del array.`);
        
        // Opcional: Mostrar más información en la consola
        console.log(`Índice de venta: ${index}`);
        console.log(`Tipo de índice: ${typeof index}`);
        
        // Si necesitas acceder a los datos reales:
        // const ventas = @json($Ventas['listaVentasDiarias'] ?? []);
        // if (index >= 0 && index < ventas.length) {
        //     const venta = ventas[index];
        //     console.log('Venta encontrada:', venta);
        //     alert(`Venta ID: ${venta.id || 'N/A'}\nSucursal: ${venta.sucursalId}\nFecha: ${venta.fecha}`);
        // } else {
        //     alert(`Índice ${index} fuera de rango. Total ventas: ${ventas.length}`);
        // }
    }

    function exportarPDF() {
        const tabla = document.getElementById('tablaEDC');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Crear un canvas para la tabla
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const maxWidth = pageWidth - (margin * 2);
        
        // Configuración del documento
        const fechaActual = new Date().toLocaleDateString('es-ES');
        const titulo = 'Reporte de Movimientos EDC';
        
        // Título del documento
        pdf.setFontSize(16);
        pdf.text(titulo, margin, margin + 10);
        
        pdf.setFontSize(10);
        pdf.text(`Fecha: ${fechaActual}`, margin, margin + 18);
        
        // Preparar datos de la tabla
        const headers = [];
        const rows = [];
        
        // Obtener encabezados (excluir si es necesario)
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            const texto = th.textContent.trim();
            // Puedes excluir columnas específicas si lo necesitas
            headers.push(texto);
        });
        
        // Obtener filas visibles
        const filasVisibles = Array.from(tabla.querySelectorAll('tbody tr')).filter(fila => {
            return fila.style.display !== 'none' && 
                !fila.classList.contains('d-none') &&
                fila.textContent.trim() !== '';
        });
        
        // Procesar cada fila
        filasVisibles.forEach(fila => {
            const rowData = [];
            fila.querySelectorAll('td').forEach((td, index) => {
                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                
                // Si tiene badge, tomar el texto del badge
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }
                
                // Limpiar valores monetarios para mejor presentación
                if (index === 2 || index === 3 || index === 4) { // Columnas de montos
                    texto = texto.replace(/\$ /g, '');
                }
                
                rowData.push(texto);
            });
            rows.push(rowData);
        });
        
        // Configurar autoTable
        pdf.autoTable({
            startY: margin + 25,
            head: [headers],
            body: rows,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 3,
                overflow: 'linebreak',
                lineWidth: 0.1,
                lineColor: [200, 200, 200]
            },
            headStyles: {
                fillColor: [66, 133, 244],
                textColor: [255, 255, 255],
                fontSize: 9,
                fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [245, 247, 250]
            },
            columnStyles: {
                0: { cellWidth: 25 }, // Fecha
                1: { cellWidth: 'auto' }, // Descripción
                2: { cellWidth: 25, halign: 'right' }, // Ingreso
                3: { cellWidth: 25, halign: 'right' }, // Egreso
                4: { cellWidth: 25, halign: 'right' }  // Saldo
            },
            margin: { left: margin, right: margin },
            didDrawPage: function(data) {
                // Número de página
                const pageCount = pdf.internal.getNumberOfPages();
                pdf.setFontSize(8);
                pdf.text(
                    `Página ${data.pageNumber} de ${pageCount}`, 
                    pageWidth - margin, 
                    pdf.internal.pageSize.getHeight() - 10,
                    { align: 'right' }
                );
            }
        });
        
        // Calcular totales para el pie
        const totalIngreso = filasVisibles.reduce((total, fila) => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length > 2) {
                const ingresoTexto = celdas[2].textContent.trim()
                    .replace('$', '')
                    .replace('.', '')
                    .replace(',', '.')
                    .replace('-', '0');
                return total + parseFloat(ingresoTexto || 0);
            }
            return total;
        }, 0);
        
        const totalEgreso = filasVisibles.reduce((total, fila) => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length > 3) {
                const egresoTexto = celdas[3].textContent.trim()
                    .replace('$', '')
                    .replace('.', '')
                    .replace(',', '.')
                    .replace('-', '0');
                return total + parseFloat(egresoTexto || 0);
            }
            return total;
        }, 0);
        
        // Agregar totales al final si es la última fila
        const ultimaFila = filasVisibles[filasVisibles.length - 1];
        if (ultimaFila && ultimaFila.classList.contains('table-light')) {
            // Ya hay totales en la tabla
            pdf.setFontSize(10);
            pdf.text(
                `Total Ingresos: $${totalIngreso.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | ` +
                `Total Egresos: $${totalEgreso.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                margin,
                pdf.autoTable.previous.finalY + 10
            );
        }
        
        // Guardar el PDF
        const fecha = new Date().toISOString().split('T')[0];
        pdf.save(`Reporte_EDC_${fecha}.pdf`);
    }

    function exportarPDFventas() {
        const tabla = document.getElementById('tablaVentasDiarias');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Obtener datos de la tabla
        const datos = [];
        
        // Encabezados (excluir columna de acciones)
        const headers = [];
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            // Excluir la última columna si es "Acciones" o similar
            const texto = th.textContent.trim();
            if (!texto.toLowerCase().includes('accion') && 
                !texto.toLowerCase().includes('acción') &&
                texto !== '#') {
                headers.push(texto);
            }
        });
        
        // Si no hay encabezados, usar predeterminados
        if (headers.length === 0) {
            headers.push('Fecha', 'Sucursal', 'Cantidad', 'Costo Divisa', 'Total Divisa', 'Total Bs', 'Tasa Cambio');
        }
        
        datos.push(headers);
        
        // Variables para totales
        let totalCantidad = 0;
        let totalCostoDivisa = 0;
        let totalTotalDivisa = 0;
        let totalTotalBs = 0;
        
        // Filas del cuerpo de la tabla (solo las visibles)
        tabla.querySelectorAll('tbody tr').forEach(fila => {
            if (fila.style.display !== 'none' && 
                !fila.textContent.includes('No hay registros')) {
                const rowData = [];
                
                fila.querySelectorAll('td').forEach((td, index) => {
                    // Omitir la última columna (acciones)
                    const thCorrespondiente = tabla.querySelector(`thead th:nth-child(${index + 1})`);
                    if (thCorrespondiente) {
                        const textoTh = thCorrespondiente.textContent.trim();
                        if (!textoTh.toLowerCase().includes('accion') && 
                            !textoTh.toLowerCase().includes('acción') &&
                            textoTh !== '#') {
                            
                            // Obtener texto limpio
                            let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                            
                            // Si tiene badge, tomar el texto del badge
                            const badge = td.querySelector('.badge');
                            if (badge) {
                                texto = badge.textContent.trim();
                            }
                            
                            // Si tiene span, tomar el texto del span
                            const span = td.querySelector('span:not(.badge)');
                            if (span && !badge) {
                                texto = span.textContent.trim();
                            }
                            
                            // Si tiene small, tomar el texto del small
                            const small = td.querySelector('small');
                            if (small && !badge && !span) {
                                texto = small.textContent.trim();
                            }
                            
                            // Acumular totales basado en el encabezado
                            if (textoTh.includes('Cantidad') || textoTh.includes('Cant.')) {
                                const cantidad = parseFloat(texto.replace(/\./g, '').replace(',', '.'));
                                if (!isNaN(cantidad)) {
                                    totalCantidad += cantidad;
                                    // Mantener como número para cálculos
                                    texto = cantidad;
                                }
                            } 
                            else if (textoTh.includes('Costo') && textoTh.includes('Divisa')) {
                                const costo = parseFloat(texto.replace('$', '').replace(/\./g, '').replace(',', '.').trim());
                                if (!isNaN(costo)) {
                                    totalCostoDivisa += costo;
                                    // Mantener como número para cálculos
                                    texto = costo;
                                } else {
                                    texto = texto.replace('$', '').trim();
                                }
                            }
                            else if (textoTh.includes('Total') && textoTh.includes('Divisa')) {
                                const total = parseFloat(texto.replace('$', '').replace(/\./g, '').replace(',', '.').trim());
                                if (!isNaN(total)) {
                                    totalTotalDivisa += total;
                                    // Mantener como número para cálculos
                                    texto = total;
                                } else {
                                    texto = texto.replace('$', '').trim();
                                }
                            }
                            else if (textoTh.includes('Total') && textoTh.includes('Bs')) {
                                const totalBs = parseFloat(texto.replace('Bs', '').replace(/\./g, '').replace(',', '.').trim());
                                if (!isNaN(totalBs)) {
                                    totalTotalBs += totalBs;
                                    // Mantener como número para cálculos
                                    texto = totalBs;
                                } else {
                                    texto = texto.replace('Bs', '').trim();
                                }
                            }
                            else if (textoTh.includes('Tasa') || textoTh.includes('Cambio')) {
                                texto = texto.replace('Promedio:', '').trim();
                            }
                            
                            rowData.push(texto);
                        }
                    }
                });
                
                // Solo agregar si tiene datos
                if (rowData.length > 0) {
                    datos.push(rowData);
                }
            }
        });
        
        // Verificar que hay datos
        if (datos.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }
        
        // Crear el PDF
        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('l', 'mm', 'a4'); // Orientación landscape
            
            const pageWidth = pdf.internal.pageSize.getWidth();
            const margin = 10;
            
            // Título
            const fechaActual = new Date().toLocaleDateString('es-ES');
            const titulo = 'Reporte de Ventas Diarias';
            
            pdf.setFontSize(16);
            pdf.text(titulo, margin, margin + 10);
            
            pdf.setFontSize(10);
            pdf.text(`Fecha: ${fechaActual}`, margin, margin + 18);
            
            // Preparar datos para autoTable
            const headersPDF = datos[0];
            const rowsPDF = datos.slice(1);
            
            // Calcular anchos de columna proporcionales
            const availableWidth = pageWidth - (margin * 2);
            const columnCount = headersPDF.length;
            
            // Anchuras específicas basadas en el contenido
            let columnWidths = [];
            if (columnCount === 7) {
                // Para la tabla de ventas con 7 columnas
                columnWidths = [20, 25, 15, 25, 25, 25, 15];
            } else {
                // Distribución proporcional genérica
                const baseWidth = availableWidth / columnCount;
                for (let i = 0; i < columnCount; i++) {
                    columnWidths.push(baseWidth);
                }
            }
            
            // Configurar autoTable
            pdf.autoTable({
                startY: margin + 25,
                head: [headersPDF],
                body: rowsPDF,
                theme: 'grid',
                styles: {
                    fontSize: 8,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    lineWidth: 0.1,
                    lineColor: [200, 200, 200]
                },
                headStyles: {
                    fillColor: [58, 186, 244],
                    textColor: [255, 255, 255],
                    fontSize: 9,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    fontSize: 8
                },
                margin: { left: margin, right: margin },
                tableWidth: 'auto'
            });
            
            // Calcular utilidad
            const utilidadTotal = totalTotalDivisa - totalCostoDivisa;
            const margenTotal = totalCostoDivisa > 0 ? (utilidadTotal / totalCostoDivisa) * 100 : 0;
            
            // Agregar resumen después de la tabla
            const finalY = pdf.autoTable.previous.finalY + 10;
            
            if (finalY < pdf.internal.pageSize.getHeight() - 50) {
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'bold');
                pdf.text('RESUMEN FINANCIERO:', margin, finalY);
                
                pdf.setFont(undefined, 'normal');
                pdf.setFontSize(9);
                
                let yPos = finalY + 8;
                
                // Formatear números para display
                const formatNumber = (num, decimals = 2) => {
                    return num.toLocaleString('es-ES', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                };
                
                pdf.text(`• Total Cantidad: ${formatNumber(totalCantidad, 0)} unidades`, margin, yPos);
                yPos += 7;
                
                pdf.text(`• Total Costo Divisa: $ ${formatNumber(totalCostoDivisa)}`, margin, yPos);
                yPos += 7;
                
                pdf.text(`• Total Venta Divisa: $ ${formatNumber(totalTotalDivisa)}`, margin, yPos);
                yPos += 7;
                
                pdf.text(`• Total Venta Bs: Bs ${formatNumber(totalTotalBs)}`, margin, yPos);
                yPos += 7;
                
                // Utilidad
                pdf.setFont(undefined, 'bold');
                if (utilidadTotal >= 0) {
                    pdf.setTextColor(0, 128, 0); // Verde
                    pdf.text(`✓ Utilidad: $ ${formatNumber(utilidadTotal)}`, margin, yPos);
                } else {
                    pdf.setTextColor(220, 53, 69); // Rojo
                    pdf.text(`✗ Pérdida: $ ${formatNumber(Math.abs(utilidadTotal))}`, margin, yPos);
                }
                pdf.setTextColor(0, 0, 0); // Restaurar color
                yPos += 7;
                
                // Margen
                pdf.setFont(undefined, 'bold');
                if (margenTotal >= 20) {
                    pdf.setTextColor(0, 128, 0); // Verde
                } else if (margenTotal >= 10) {
                    pdf.setTextColor(255, 193, 7); // Amarillo/Naranja
                } else {
                    pdf.setTextColor(220, 53, 69); // Rojo
                }
                pdf.text(`• Margen: ${margenTotal.toFixed(1)}%`, margin, yPos);
                pdf.setTextColor(0, 0, 0); // Restaurar color
                
                // Promedio de tasa de cambio
                yPos += 7;
                pdf.setFont(undefined, 'normal');
                const promedioTasa = totalTotalDivisa > 0 ? totalTotalBs / totalTotalDivisa : 0;
                pdf.text(`• Tasa Promedio: ${formatNumber(promedioTasa)} Bs/$`, margin, yPos);
            }
            
            // Número de página
            const pageCount = pdf.internal.getNumberOfPages();
            pdf.setFontSize(8);
            pdf.text(
                `Página ${pageCount} de ${pageCount}`, 
                pageWidth - margin, 
                pdf.internal.pageSize.getHeight() - 5,
                { align: 'right' }
            );
            
            // Guardar el PDF
            const fecha = new Date().toISOString().split('T')[0];
            pdf.save(`Reporte_Ventas_${fecha}.pdf`);
            
        } catch (error) {
            console.error('Error al generar PDF:', error);
            alert('Error al generar el PDF. Verifique la consola para más detalles.');
        }
    }

    function exportarPDFgastos() {
        const tabla = document.getElementById('tablaGastos');
        
        if (!tabla) {
            alert('No se encontró la tabla para exportar');
            return;
        }
        
        // Crear un canvas para la tabla
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const maxWidth = pageWidth - (margin * 2);
        
        // Configuración del documento
        const fechaActual = new Date().toLocaleDateString('es-ES');
        const titulo = 'Reporte de Gastos';
        
        // Título del documento
        pdf.setFontSize(16);
        pdf.text(titulo, margin, margin + 10);
        
        pdf.setFontSize(10);
        pdf.text(`Fecha: ${fechaActual}`, margin, margin + 18);
        
        // Preparar datos de la tabla
        const headers = [];
        const rows = [];
        
        // Obtener encabezados (excluir primera columna de checkbox y última de acciones)
        tabla.querySelectorAll('thead th').forEach((th, index) => {
            const texto = th.textContent.trim();
            // Excluir primera columna (checkbox) y última columna (acciones)
            if (index > 0 && index < tabla.querySelectorAll('thead th').length - 1) {
                headers.push(texto);
            }
        });
        
        // Obtener filas visibles
        const filasVisibles = Array.from(tabla.querySelectorAll('tbody tr')).filter(fila => {
            return fila.style.display !== 'none' && 
                !fila.classList.contains('d-none') &&
                fila.textContent.trim() !== '';
        });
        
        // Variables para totales
        let totalDivisa = 0;
        let totalBs = 0;
        
        // Procesar cada fila
        filasVisibles.forEach(fila => {
            const rowData = [];
            let colIndex = 0;
            
            fila.querySelectorAll('td').forEach((td, index) => {
                // Saltar primera columna (checkbox) y última columna (acciones)
                if (index === 0 || index === tabla.querySelectorAll('thead th').length - 1) {
                    return;
                }
                
                let texto = td.textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                
                // Si tiene badge, tomar el texto del badge
                const badge = td.querySelector('.badge');
                if (badge) {
                    texto = badge.textContent.trim();
                }
                
                // Si tiene code, tomar el texto del code
                const code = td.querySelector('code');
                if (code && !badge) {
                    texto = code.textContent.trim();
                }
                
                // Limpiar valores monetarios para mejor presentación y cálculos
                if (colIndex === 4) { // Columna Monto ($)
                    texto = texto.replace(/\$ /g, '');
                    // Calcular total divisa
                    const montoDivisa = parseFloat(texto.replace(/\./g, '').replace(',', '.'));
                    if (!isNaN(montoDivisa)) {
                        totalDivisa += montoDivisa;
                    }
                } else if (colIndex === 5) { // Columna Monto (Bs)
                    texto = texto.replace(/Bs /g, '');
                    // Calcular total bolívares
                    const montoBs = parseFloat(texto.replace(/\./g, '').replace(',', '.'));
                    if (!isNaN(montoBs)) {
                        totalBs += montoBs;
                    }
                }
                
                // Para la columna de fecha, combinar fecha y hora
                if (colIndex === 0) {
                    const fechaElements = td.querySelectorAll('small');
                    if (fechaElements.length >= 2) {
                        texto = `${fechaElements[0].textContent.trim()} ${fechaElements[1].textContent.trim()}`;
                    }
                }
                
                // Para la columna de descripción, tomar solo el texto principal
                if (colIndex === 2) {
                    const descripcionPrincipal = td.querySelector('.fw-medium');
                    if (descripcionPrincipal) {
                        texto = descripcionPrincipal.textContent.trim();
                    }
                }
                
                rowData.push(texto);
                colIndex++;
            });
            
            rows.push(rowData);
        });
        
        // Configurar autoTable
        pdf.autoTable({
            startY: margin + 25,
            head: [headers],
            body: rows,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 3,
                overflow: 'linebreak',
                lineWidth: 0.1,
                lineColor: [200, 200, 200]
            },
            headStyles: {
                fillColor: [220, 53, 69], // Rojo para gastos
                textColor: [255, 255, 255],
                fontSize: 9,
                fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [245, 247, 250]
            },
            columnStyles: {
                0: { cellWidth: 25 }, // Fecha
                1: { cellWidth: 25 }, // No. Operación
                2: { cellWidth: 'auto' }, // Descripción
                3: { cellWidth: 25 }, // Categoría
                4: { cellWidth: 20, halign: 'right' }, // Monto ($)
                5: { cellWidth: 20, halign: 'right' }, // Monto (Bs)
                6: { cellWidth: 15 } // Sucursal
            },
            margin: { left: margin, right: margin },
            didDrawPage: function(data) {
                // Número de página
                const pageCount = pdf.internal.getNumberOfPages();
                pdf.setFontSize(8);
                pdf.text(
                    `Página ${data.pageNumber} de ${pageCount}`, 
                    pageWidth - margin, 
                    pdf.internal.pageSize.getHeight() - 10,
                    { align: 'right' }
                );
            }
        });
        
        // Agregar totales al final
        pdf.setFontSize(10);
        pdf.text(
            `Total Gastos en Divisa: $${totalDivisa.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 
            margin,
            pdf.autoTable.previous.finalY + 10
        );
        
        pdf.text(
            `Total Gastos en Bolívares: Bs ${totalBs.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 
            margin,
            pdf.autoTable.previous.finalY + 20
        );
        
        // Calcular y agregar promedio si hay registros
        if (filasVisibles.length > 0) {
            const promedioDivisa = totalDivisa / filasVisibles.length;
            const promedioBs = totalBs / filasVisibles.length;
            
            pdf.text(
                `Promedio por gasto: $${promedioDivisa.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | ` +
                `Bs ${promedioBs.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 
                margin,
                pdf.autoTable.previous.finalY + 30
            );
            
            pdf.text(
                `Total registros: ${filasVisibles.length} gastos`, 
                margin,
                pdf.autoTable.previous.finalY + 40
            );
        }
        
        // Guardar el PDF
        const fecha = new Date().toISOString().split('T')[0];
        pdf.save(`Reporte_Gastos_${fecha}.pdf`);
    }
</script>

@endsection