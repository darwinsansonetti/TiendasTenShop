@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Dashboard')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Dashboard</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
    <!--begin::Row-->
    <div class="row">
      <!--begin::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 1-->
        <div class="small-box text-bg-primary">
          <div class="inner">
            <h3>{{ $productos->where('Estatus', true)->count() }}</h3>
            <p>Productos activos</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <path
              d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"
            ></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
          >
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 1-->
      </div>
      <!--end::Col-->
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 4-->
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3>{{ $productos->where('Estatus', false)->count() }}</h3>
            <p>Productos Inactivos</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"
            ></path>
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"
            ></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
          >
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 4-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 2-->
        <div class="small-box text-bg-success">
          <div class="inner">                    
            <h3>{{ $listaSucursales->where('EsActiva', true)->count() }}</h3>
            <p>Sucursales</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <path
              d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"
            ></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
          >
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 2-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 3-->
        <div class="small-box text-bg-warning">
          <div class="inner">
            <h3 id="widget-tasa-dia">
              @if($tasa && $tasa['DivisaValor'])
                  {{ number_format($tasa['DivisaValor']['Valor'], 2) }} Bs
              @else
                  0.00 Bs
              @endif
            </h3>
            <p>Tasa del Día</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <path
              d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"
            ></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover"
          >
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 3-->
      </div>
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row">

        <!-- Start col -->
        <div class="col-lg-7 connectedSortable">

            <!-- Ventas últimos 7 meses -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="fas fa-chart-line me-2 fs-6"></i>
                        <h3 class="card-title mb-0 fs-5">Ventas por sucursal (Últimos 7 meses)</h3>
                    </div>

                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-light" data-lte-toggle="card-collapse">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div id="revenue-chart" style="height: 300px;"></div>
                </div>
            </div>

            <!-- Producción mensual por sucursal -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">

                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="fas fa-store me-2 fs-6"></i>
                        <h3 class="card-title mb-0 fs-5">Producción mensual por Sucursal</h3>
                    </div>

                    <div class="d-flex align-items-center me-3">
                        <input type="month" id="month-year-picker"
                            class="form-control form-control-sm"
                            style="width: 150px;"
                            value="{{ date('Y-m') }}">
                        <button id="update-chart-btn" class="btn btn-sm btn-outline-light ms-2">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>

                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-light" data-lte-toggle="card-collapse">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div id="sales-by-store-chart" style="height: 300px;"></div>
                </div>
            </div>

        </div>
        <!-- End col -->

        <!-- Ranking -->
        <div class="col-lg-5 connectedSortable">

            <!-- Ranking Sucursales -->
            <div class="card border-0 shadow-lg mb-4">

                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="fas fa-trophy me-2 fs-6"></i>
                        <h3 class="card-title mb-0 fs-5">Ranking de Sucursales</h3>
                    </div>

                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-light" data-lte-toggle="card-collapse">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-0" style="min-height: 344px;">

                    <!-- Filtro -->
                    <div class="bg-light p-2 border-bottom">
                        <form id="filter-form" class="d-flex align-items-center flex-nowrap gap-1">

                            <div class="d-flex align-items-center" style="flex: 1;">
                                <span class="small text-muted me-1" style="min-width: 38px; font-size: 12px;">Inicio:</span>
                                <input type="date" id="fecha_inicio" name="fecha_inicio"
                                    value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                                    class="form-control form-control-sm border-primary"
                                    style="width: 110px; font-size: 12px; padding: .2rem .4rem;">
                            </div>

                            <div class="d-flex align-items-center" style="flex: 1;">
                                <span class="small text-muted me-1" style="min-width: 28px; font-size: 12px;">Fin:</span>
                                <input type="date" id="fecha_fin" name="fecha_fin"
                                    value="{{ request('fecha_fin', now()->endOfMonth()->format('Y-m-d')) }}"
                                    class="form-control form-control-sm border-primary"
                                    style="width: 110px; font-size: 12px; padding: .2rem .4rem;">
                            </div>

                            <div style="flex-shrink: 0;">
                                <button type="button" id="filter-button" class="btn btn-primary btn-sm px-2 py-1" style="font-size: 12px;">
                                    <i class="fas fa-filter me-1"></i>Filtrar
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- Contenedor dinámico -->
                    <div id="ranking-sucursales-container">
                        @include('cpanel.partials.ranking_sucursales', ['rankingSucursales' => $rankingSucursales])
                    </div>

                </div>

            </div>
            <!-- Ranking Sucursales -->
            
            <!-- Ranking Vendedores -->
            <div class="card border-0 shadow-lg mb-4">

                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="fas fa-user-tie me-2 fs-6"></i>
                        <h3 class="card-title mb-0 fs-5">Ranking de Vendedores</h3>
                    </div>

                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-light" data-lte-toggle="card-collapse">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-0" style="min-height: 250px;">
                    
                    <!-- Filtro (reutilizamos las mismas fechas) -->
                    <div class="bg-light p-2 border-bottom">
                        <form id="filter-vendedores-form" class="d-flex align-items-center flex-nowrap gap-1">

                            <div class="d-flex align-items-center" style="flex: 1;">
                                <span class="small text-muted me-1" style="min-width: 38px; font-size: 12px;">Inicio:</span>
                                <input type="date" id="fecha_inicio_vendedores" name="fecha_inicio"
                                    value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}"
                                    class="form-control form-control-sm border-success"
                                    style="width: 110px; font-size: 12px; padding: .2rem .4rem;">
                            </div>

                            <div class="d-flex align-items-center" style="flex: 1;">
                                <span class="small text-muted me-1" style="min-width: 28px; font-size: 12px;">Fin:</span>
                                <input type="date" id="fecha_fin_vendedores" name="fecha_fin"
                                    value="{{ request('fecha_fin', now()->endOfMonth()->format('Y-m-d')) }}"
                                    class="form-control form-control-sm border-success"
                                    style="width: 110px; font-size: 12px; padding: .2rem .4rem;">
                            </div>

                            <div style="flex-shrink: 0;">
                                <button type="button" id="filter-vendedores-button" class="btn btn-success btn-sm px-2 py-1" style="font-size: 12px;">
                                    <i class="fas fa-filter me-1"></i>Filtrar
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- Contenedor dinámico -->
                    <div id="ranking-vendedores-container">
                        @include('cpanel.partials.ranking_vendedores', ['rankingVendedores' => $rankingVendedor->take(3)])
                    </div>

                </div>

            </div>
            <!-- /Ranking Vendedores -->


        </div>
        <!-- /Ranking -->

    </div>
    <!-- /.row -->

  <!--end::Container-->
</div>
<!--end::App Content-->

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
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: true,
                formatter: val => "$" + Number(val).toLocaleString(),
                offsetY: -20
            },
            xaxis: {
                categories: [''], // eje X vacío
                labels: { show: false }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center'
            },
            yaxis: {
                labels: {
                    formatter: val => "$" + Number(val).toLocaleString()
                }
            },
            colors: ['#2E93FA','#66DA26','#546E7A','#E91E63','#FF9800','#8e44ad','#00bcd4'],
            tooltip: {
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
            chart: { height: 300, type: 'area', toolbar: { show: false } },
            xaxis: {
                categories: months
            },
            colors: ['#0d6efd', '#20c997', '#ff5733', '#ff6347', '#3f8b7f'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            tooltip: {
                x: { formatter: val => val }
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


</script>

@endsection