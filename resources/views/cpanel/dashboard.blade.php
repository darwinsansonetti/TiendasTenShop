@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Dashboard')

@section('content')

{{-- Page Header --}}
<div class="app-content-header border-bottom bg-white">
  <div class="container-fluid py-2">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h4 class="fw-bold mb-0 text-dark">
          <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard
        </h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
            <li class="breadcrumb-item">
              <a href="{{ route('cpanel.dashboard') }}" class="text-decoration-none text-muted">Inicio</a>
            </li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
      <div class="d-none d-md-block">
        <span class="badge bg-primary bg-opacity-10 text-primary fw-normal px-3 py-2 rounded-pill fs-6">
          <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d/m/Y') }}
        </span>
      </div>
    </div>
  </div>
</div>

{{-- Main Content --}}
<div class="app-content">
  <div class="container-fluid">

    {{-- ===== KPI Cards ===== --}}
    <div class="row g-3 mt-1 mb-4">

      {{-- Productos Activos --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size: 0.68rem; letter-spacing: .08em;">Productos Activos</p>
                <h2 class="fw-bold mb-0">{{ $productos->where('Estatus', true)->count() }}</h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px; height:52px; background:rgba(13,110,253,0.1);">
                <i class="bi bi-bag-check-fill fs-4 text-primary"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-success me-1" style="font-size:8px;"></i>
                En catálogo activo
              </small>
            </div>
          </div>
          <div style="height:4px; background:linear-gradient(90deg,#0d6efd,#6ea8fe);"></div>
        </div>
      </div>

      {{-- Productos Inactivos --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size: 0.68rem; letter-spacing: .08em;">Productos Inactivos</p>
                <h2 class="fw-bold mb-0">{{ $productos->where('Estatus', false)->count() }}</h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px; height:52px; background:rgba(220,53,69,0.1);">
                <i class="bi bi-bag-x-fill fs-4 text-danger"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-danger me-1" style="font-size:8px;"></i>
                Fuera de catálogo
              </small>
            </div>
          </div>
          <div style="height:4px; background:linear-gradient(90deg,#dc3545,#f1aeb5);"></div>
        </div>
      </div>

      {{-- Tasa Paralelo --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size: 0.68rem; letter-spacing: .08em;">Tasa Paralelo</p>
                <h2 class="fw-bold mb-0" id="widget-tasa-paralelo">
                  @if($paralelo)
                    {{ number_format($paralelo, 2) }} Bs
                  @else
                    0.00 Bs
                  @endif
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px; height:52px; background:rgba(25,135,84,0.1);">
                <i class="bi bi-currency-dollar fs-4 text-success"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-warning me-1" style="font-size:8px;"></i>
                1 USD — Mercado paralelo
              </small>
            </div>
          </div>
          <div style="height:4px; background:linear-gradient(90deg,#198754,#75b798);"></div>
        </div>
      </div>

      {{-- Tasa BCV --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size: 0.68rem; letter-spacing: .08em;">Tasa BCV</p>
                <h2 class="fw-bold mb-0" id="widget-tasa-dia">
                  @if($tasa && $tasa['DivisaValor'])
                    {{ number_format($tasa['DivisaValor']['Valor'], 2) }} Bs
                  @else
                    0.00 Bs
                  @endif
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px; height:52px; background:rgba(255,193,7,0.15);">
                <i class="bi bi-bank2 fs-4 text-warning"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-primary me-1" style="font-size:8px;"></i>
                1 USD — Banco Central de Venezuela
              </small>
            </div>
          </div>
          <div style="height:4px; background:linear-gradient(90deg,#ffc107,#ffe08a);"></div>
        </div>
      </div>

    </div>
    {{-- /KPI Cards --}}

    {{-- ===== Charts + Rankings ===== --}}
    <div class="row g-3">

      {{-- LEFT: Gráficas --}}
      <div class="col-lg-7 connectedSortable">

        {{-- Ventas últimos 7 meses --}}
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px; height:36px; background:rgba(255,255,255,0.2);">
                <i class="bi bi-graph-up-arrow text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Ventas por Sucursal</h6>
                <small class="text-white-50">Últimos 7 meses</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand" class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>
          <div class="card-body p-3">
            <div id="revenue-chart" style="height:300px;"></div>
          </div>
        </div>

        {{-- Producción mensual por sucursal --}}
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#198754 0%,#157347 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px; height:36px; background:rgba(255,255,255,0.2);">
                <i class="bi bi-bar-chart-line-fill text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Producción por Sucursal</h6>
                <small class="text-white-50">Mensual</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <input type="month" id="month-year-picker"
                     class="form-control form-control-sm border-0"
                     style="width:140px;"
                     value="{{ date('Y-m') }}">
              <button id="update-chart-btn"
                      class="btn btn-sm text-white d-flex align-items-center justify-content-center"
                      style="background:rgba(255,255,255,0.2); width:32px; height:32px; padding:0;"
                      title="Actualizar gráfica">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
              <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                      data-lte-toggle="card-collapse">
                <i data-lte-icon="expand" class="bi bi-chevron-down"></i>
                <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-3">
            <div id="sales-by-store-chart" style="height:300px;"></div>
          </div>
        </div>

      </div>
      {{-- /LEFT --}}

      {{-- RIGHT: Rankings --}}
      <div class="col-lg-5 connectedSortable">

        {{-- Ranking Sucursales --}}
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px; height:36px; background:rgba(255,255,255,0.2);">
                <i class="bi bi-trophy-fill text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Ranking de Sucursales</h6>
                <small class="text-white-50">Por volumen de ventas</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand" class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>

          {{-- Barra de filtro --}}
          <div class="px-3 py-2 border-bottom bg-light">
            <form id="filter-form" class="d-flex align-items-center gap-2 flex-nowrap">
              <div class="input-group input-group-sm flex-nowrap">
                <span class="input-group-text bg-white text-muted border-end-0"
                      style="font-size:11px;">Desde</span>
                <input type="date" id="fecha_inicio" name="fecha_inicio"
                       value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="form-control border-start-0 ps-0"
                       style="font-size:12px; max-width:120px;">
              </div>
              <div class="input-group input-group-sm flex-nowrap">
                <span class="input-group-text bg-white text-muted border-end-0"
                      style="font-size:11px;">Hasta</span>
                <input type="date" id="fecha_fin" name="fecha_fin"
                       value="{{ request('fecha_fin', now()->endOfMonth()->format('Y-m-d')) }}"
                       class="form-control border-start-0 ps-0"
                       style="font-size:12px; max-width:120px;">
              </div>
              <button type="button" id="filter-button"
                      class="btn btn-primary btn-sm flex-shrink-0" title="Filtrar">
                <i class="bi bi-funnel-fill"></i>
              </button>
            </form>
          </div>

          <div class="card-body p-0" style="min-height:300px;">
            <div id="ranking-sucursales-container">
              @include('cpanel.partials.ranking_sucursales', ['rankingSucursales' => $rankingSucursales])
            </div>
          </div>
        </div>

        {{-- Ranking Vendedores --}}
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#198754 0%,#157347 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px; height:36px; background:rgba(255,255,255,0.2);">
                <i class="bi bi-person-badge-fill text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Ranking de Vendedores</h6>
                <small class="text-white-50">Top rendimiento del período</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand" class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>

          {{-- Barra de filtro --}}
          <div class="px-3 py-2 border-bottom bg-light">
            <form id="filter-vendedores-form" class="d-flex align-items-center gap-2 flex-nowrap">
              <div class="input-group input-group-sm flex-nowrap">
                <span class="input-group-text bg-white text-muted border-end-0"
                      style="font-size:11px;">Desde</span>
                <input type="date" id="fecha_inicio_vendedores" name="fecha_inicio"
                       value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="form-control border-start-0 ps-0"
                       style="font-size:12px; max-width:120px;">
              </div>
              <div class="input-group input-group-sm flex-nowrap">
                <span class="input-group-text bg-white text-muted border-end-0"
                      style="font-size:11px;">Hasta</span>
                <input type="date" id="fecha_fin_vendedores" name="fecha_fin"
                       value="{{ request('fecha_fin', now()->endOfMonth()->format('Y-m-d')) }}"
                       class="form-control border-start-0 ps-0"
                       style="font-size:12px; max-width:120px;">
              </div>
              <button type="button" id="filter-vendedores-button"
                      class="btn btn-success btn-sm flex-shrink-0" title="Filtrar">
                <i class="bi bi-funnel-fill"></i>
              </button>
            </form>
          </div>

          <div class="card-body p-0" style="min-height:250px;">
            <div id="ranking-vendedores-container">
              @include('cpanel.partials.ranking_vendedores', ['rankingVendedores' => $rankingVendedor->take(3)])
            </div>
          </div>
        </div>

      </div>
      {{-- /RIGHT --}}

    </div>
    {{-- /Charts + Rankings --}}

  </div>
</div>
{{-- /App Content --}}

@endsection

@section('js')

<script>

    /* ============================================================
       1) GRÁFICA: Producción por Sucursal (BarChart)
    ============================================================ */

    document.addEventListener('DOMContentLoaded', function() {

        // Datos desde PHP
        const graficaCategorias = @json($graficaProduccionMes->pluck('sucursal'));
        const graficaValores     = @json($graficaProduccionMes->pluck('produccion'));

        // Crear una serie por tienda para que la leyenda funcione
        const seriesPorTienda = graficaCategorias.map((nombre, index) => ({
            name: nombre,
            data: [graficaValores[index]] // cada barra es una serie
        }));

        const salesByStoreOptions = {
            series: seriesPorTienda,
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: "'Source Sans 3', 'Helvetica Neue', sans-serif",
                dropShadow: {
                    enabled: true,
                    top: 4,
                    left: 0,
                    blur: 6,
                    opacity: 0.08
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '48%',
                    borderRadius: 6,
                    borderRadiusApplication: 'end'
                }
            },
            dataLabels: {
                enabled: true,
                formatter: val => "$" + Number(val).toLocaleString(),
                offsetY: -22,
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    colors: ['#374151']
                },
                background: { enabled: false }
            },
            xaxis: {
                categories: [''],
                labels: { show: false },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { top: 16, right: 8, bottom: 0, left: 8 }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                fontWeight: 500,
                itemMargin: { horizontal: 10 },
                markers: { width: 10, height: 10, radius: 3 }
            },
            yaxis: {
                labels: {
                    formatter: val => "$" + Number(val).toLocaleString(),
                    style: { fontSize: '11px', colors: '#94a3b8' }
                }
            },
            colors: ['#3b82f6','#10b981','#6366f1','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],
            tooltip: {
                theme: 'light',
                style: { fontSize: '12px' },
                y: { formatter: val => "$" + Number(val).toLocaleString() },
                x: { formatter: function(val, opts) {
                    return graficaCategorias[opts.seriesIndex];
                }}
            }
        };

        // Crear la gráfica
        const produccionChart = new ApexCharts(
            document.querySelector('#sales-by-store-chart'),
            salesByStoreOptions
        );
        produccionChart.render();

        // === ACTUALIZAR AL SELECCIONAR MES ===
        document.getElementById('update-chart-btn').addEventListener('click', function() {

            const monthYear = document.getElementById('month-year-picker').value;

            const url = "{{ route('cpanel.dashboard.produccion') }}" + "?monthYear=" + monthYear;

            fetch(url)
            .then(res => {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(data => {

                if (!data.categorias || !data.valores) {
                    console.error("Datos incompletos", data);
                    return;
                }

                // Reconstruir series por tienda (IMPORTANTE para que funcione tu leyenda actual)
                const nuevasSeries = data.categorias.map((nombre, index) => ({
                    name: nombre,
                    data: [data.valores[index] ?? 0]
                }));

                // Actualizar gráfica manteniendo el mismo estilo
                produccionChart.updateSeries(nuevasSeries);

                // El eje X siempre queda vacío como en tu diseño original
                produccionChart.updateOptions({
                    xaxis: { categories: [''] }
                });

            })
            .catch(err => {
                console.error("Error al actualizar la gráfica:", err);
            });
        });

    });

    /* ============================================================
       2) GRÁFICA: Ventas Mensuales por Sucursales (AreaChart)
    ============================================================ */
    const ventasData = @json($graficaSucursalesMeses);

    // Verifica que existan datos válidos
    if (ventasData && Array.isArray(ventasData.categories) && Array.isArray(ventasData.series)) {

        // Convertir "YYYY-MM" a "Mes"
        const months = ventasData.categories.map(str => {

            if (!str || !str.includes("-")) return str;

            const parts = str.split('-');
            const monthIndex = parseInt(parts[1], 10) - 1;

            const monthNames = [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ];

            return monthNames[monthIndex] ?? str;

        });

        const salesChartOptions = {
            series: ventasData.series,
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false },
                fontFamily: "'Source Sans 3', 'Helvetica Neue', sans-serif",
                zoom: { enabled: false }
            },
            xaxis: {
                categories: months,
                labels: {
                    style: { fontSize: '11px', colors: '#94a3b8', fontWeight: 500 }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: val => "$" + Number(val).toLocaleString(),
                    style: { fontSize: '11px', colors: '#94a3b8' }
                }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.03,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { top: 8, right: 8, bottom: 0, left: 8 }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                fontWeight: 500,
                itemMargin: { horizontal: 10 },
                markers: { width: 10, height: 10, radius: 3 }
            },
            tooltip: {
                theme: 'light',
                style: { fontSize: '12px' },
                x: { formatter: val => val },
                y: { formatter: val => "$" + Number(val).toLocaleString() }
            }
        };

        // ⚠️ NOMBRE DIFERENTE PARA EVITAR COLISIÓN
        const ventasChart = new ApexCharts(
            document.querySelector('#revenue-chart'),
            salesChartOptions
        );

        ventasChart.render();

    } else {
        console.error('ventasData no válido:', ventasData);
    }

    /* ============================================================
       3) Script AJAX para Actualizar el Ranking de Sucursales
    ============================================================ */
    document.getElementById('filter-button').addEventListener('click', function(e) {
        e.preventDefault(); // evitar que un form se envíe si el botón está dentro de uno

        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        // Validar que ambas fechas estén seleccionadas
        if (!fechaInicio || !fechaFin) {
            showToast('Debe seleccionar ambas fechas.', 'danger');
            return;
        }

        // Convertir strings a objetos Date
        const fechaInicioV = new Date(fechaInicio);
        const fechaFinV = new Date(fechaFin);

        if (fechaInicio > fechaFin) {
            //e.preventDefault();
            showToast('La fecha de inicio no puede ser mayor a la fecha final.', 'danger');
            return;
        }

        const diffAnios = (fechaFinV - fechaInicioV) / (1000 * 60 * 60 * 24 * 365);
        if (diffAnios > 1) {
            //e.preventDefault();
            showToast('El rango de fechas no puede ser mayor a 1 año.', 'danger');
            return;
        }

        const url = "{{ route('cpanel.dashboard.ranking') }}?fecha_inicio=" + fechaInicio + "&fecha_fin=" + fechaFin;
        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error("HTTP status " + res.status);
                }
                return res.json();
            })
            .then(data => {

                if (data.html) {
                    document.getElementById('ranking-sucursales-container').innerHTML = data.html;
                } else {
                    console.warn("No se recibió HTML en la respuesta", data);
                }
            })
            .catch(err => {
                console.error("Error al obtener el ranking:", err);
            });
    });


    /* ============================================================
       4) Script AJAX para Actualizar el Ranking de Vendedores
    ============================================================ */
    document.getElementById('filter-vendedores-button').addEventListener('click', function() {
        const fechaInicio = document.getElementById('fecha_inicio_vendedores').value;
        const fechaFin = document.getElementById('fecha_fin_vendedores').value;

        // Validar que ambas fechas estén seleccionadas
        if (!fechaInicio || !fechaFin) {
            showToast('Debe seleccionar ambas fechas.', 'danger');
            return;
        }

        // Convertir strings a objetos Date
        const fechaInicioV = new Date(fechaInicio);
        const fechaFinV = new Date(fechaFin);

        if (fechaInicio > fechaFin) {
            //e.preventDefault();
            showToast('La fecha de inicio no puede ser mayor a la fecha final.', 'danger');
            return;
        }

        const diffAnios = (fechaFinV - fechaInicioV) / (1000 * 60 * 60 * 24 * 365);
        if (diffAnios > 1) {
            //e.preventDefault();
            showToast('El rango de fechas no puede ser mayor a 1 año.', 'danger');
            return;
        }

        fetch("{{ route('cpanel.ranking-vendedores') }}?fecha_inicio=" + fechaInicio + "&fecha_fin=" + fechaFin)
            .then(res => {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(data => {
                if (data.html) {
                    // Insertamos el ranking de vendedores debajo del ranking de sucursales
                    const container = document.getElementById('ranking-vendedores-container');
                    if (container) {
                        container.innerHTML = data.html;
                    } else {
                        // Si no existe, lo creamos debajo del ranking de sucursales
                        const sucursalesContainer = document.getElementById('ranking-sucursales-container');
                        const wrapper = document.createElement('div');
                        wrapper.id = 'ranking-vendedores-container';
                        wrapper.innerHTML = data.html;
                        sucursalesContainer.parentNode.insertBefore(wrapper, sucursalesContainer.nextSibling);
                    }
                }
            })
            .catch(err => console.error('Error al actualizar ranking de vendedores:', err));
    });


    /* ============================================================
       Validaciones de filtro de fechas
    ============================================================ */


</script>

@endsection
