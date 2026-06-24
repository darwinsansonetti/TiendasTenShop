@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Resumen de ventas')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

{{-- Page Header --}}
<div class="app-content-header border-bottom bg-white">
  <div class="container-fluid py-2">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h4 class="fw-bold mb-0 text-dark">
          <i class="bi bi-receipt text-primary me-2"></i>Resumen de Ventas
        </h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0" style="font-size:.8rem;">
            <li class="breadcrumb-item">
              <a href="{{ route('cpanel.dashboard') }}" class="text-decoration-none text-muted">Inicio</a>
            </li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Resumen de ventas</li>
          </ol>
        </nav>
      </div>
      @if(session('sucursal_nombre'))
      <div class="d-none d-md-block">
        <span class="badge bg-primary bg-opacity-10 text-primary fw-normal px-3 py-2 rounded-pill fs-6">
          <i class="bi bi-shop me-1"></i>{{ session('sucursal_nombre') }}
        </span>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    {{-- ===== KPI Cards ===== --}}
    <div class="row g-3 mt-1 mb-4">

      {{-- Unidades Vendidas --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size:.68rem;letter-spacing:.08em;">Unidades Vendidas</p>
                <h2 class="fw-bold mb-0">
                  {{ $ventas->listaVentasDiarias->sum('UnidadesVendidas') > 0
                      ? number_format($ventas->listaVentasDiarias->sum('UnidadesVendidas'), 0, ',', '.')
                      : '0' }}
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px;height:52px;background:rgba(13,110,253,.1);">
                <i class="bi bi-cart-fill fs-4 text-primary"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-primary me-1" style="font-size:8px;"></i>
                Total del período
              </small>
            </div>
          </div>
          <div style="height:4px;background:linear-gradient(90deg,#0d6efd,#6ea8fe);"></div>
        </div>
      </div>

      {{-- Producción Divisa --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size:.68rem;letter-spacing:.08em;">Producción Divisa</p>
                <h2 class="fw-bold mb-0">
                  {{ $ventas->listaVentasDiarias->sum('TotalDivisa') > 0
                      ? number_format($ventas->listaVentasDiarias->sum('TotalDivisa'), 2, ',', '.')
                      : '0' }}
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px;height:52px;background:rgba(220,53,69,.1);">
                <i class="bi bi-currency-exchange fs-4 text-danger"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-danger me-1" style="font-size:8px;"></i>
                Ventas totales en USD
              </small>
            </div>
          </div>
          <div style="height:4px;background:linear-gradient(90deg,#dc3545,#f1aeb5);"></div>
        </div>
      </div>

      {{-- Utilidad Divisa --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size:.68rem;letter-spacing:.08em;">Utilidad Divisa</p>
                <h2 class="fw-bold mb-0">
                  {{ $ventas->listaVentasDiarias->sum('UtilidadDivisaDiario') > 0
                      ? number_format($ventas->listaVentasDiarias->sum('UtilidadDivisaDiario'), 2, ',', '.')
                      : '0' }}
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px;height:52px;background:rgba(25,135,84,.1);">
                <i class="bi bi-clipboard-data fs-4 text-success"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-success me-1" style="font-size:8px;"></i>
                Ganancia neta en USD
              </small>
            </div>
          </div>
          <div style="height:4px;background:linear-gradient(90deg,#198754,#75b798);"></div>
        </div>
      </div>

      {{-- Margen Promedio --}}
      <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted mb-2"
                   style="font-size:.68rem;letter-spacing:.08em;">Margen Promedio</p>
                <h2 class="fw-bold mb-0">
                  {{ number_format($ventas->raw['MargenDivisasPeriodo'], 2, ',', '.') }}
                  <span class="fs-5 fw-normal text-muted">%</span>
                </h2>
              </div>
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:52px;height:52px;background:rgba(255,193,7,.15);">
                <i class="bi bi-arrow-up-right-square fs-4 text-warning"></i>
              </div>
            </div>
            <div class="mt-3 pt-2 border-top">
              <small class="text-muted">
                <i class="bi bi-circle-fill text-warning me-1" style="font-size:8px;"></i>
                Margen de utilidad del período
              </small>
            </div>
          </div>
          <div style="height:4px;background:linear-gradient(90deg,#ffc107,#ffe08a);"></div>
        </div>
      </div>

    </div>
    {{-- /KPI Cards --}}

    {{-- ===== Gráfica Principal ===== --}}
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
           style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
        <div class="d-flex align-items-center">
          <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
               style="width:36px;height:36px;background:rgba(255,255,255,.2);">
            <i class="bi bi-graph-up-arrow text-white"></i>
          </div>
          <div>
            <h6 class="fw-bold text-white mb-0">Evolución de Utilidad</h6>
            <small class="text-white-50">
              {{ \Carbon\Carbon::parse($ventas->raw['FechaInicio'])->format('d M Y') }}
              &mdash;
              {{ \Carbon\Carbon::parse($ventas->raw['FechaFin'])->format('d M Y') }}
            </small>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <form id="form-fechas" method="GET" action="{{ route('cpanel.resumen.ventas') }}"
                class="d-flex align-items-center gap-2">
            @php
              $fechaInicio = old('fecha_inicio', \Carbon\Carbon::parse($ventas->raw['FechaInicio'])->format('Y-m-d'));
              $fechaFin    = old('fecha_fin',    \Carbon\Carbon::parse($ventas->raw['FechaFin'])->format('Y-m-d'));
            @endphp
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                   class="form-control form-control-sm border-0"
                   style="width:135px;" value="{{ $fechaInicio }}">
            <input type="date" id="fecha_fin" name="fecha_fin"
                   class="form-control form-control-sm border-0"
                   style="width:135px;" value="{{ $fechaFin }}">
            <button type="submit"
                    class="btn btn-sm text-white d-flex align-items-center justify-content-center"
                    style="background:rgba(255,255,255,.2);width:32px;height:32px;padding:0;"
                    title="Filtrar">
              <i class="bi bi-funnel-fill"></i>
            </button>
          </form>
          <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                  data-lte-toggle="card-collapse">
            <i data-lte-icon="expand"  class="bi bi-chevron-down"></i>
            <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
          </button>
        </div>
      </div>

      <div class="card-body p-3">
        <div class="row g-3">
          <div class="col-md-8">
            <div id="sales-chart" style="height:300px;"></div>
          </div>
          <div class="col-md-4">
            <div id="grafico-detalle"></div>
          </div>
        </div>
      </div>
    </div>
    {{-- /Gráfica Principal --}}

    {{-- ===== Cards Inferiores ===== --}}
    @php
      $colores       = ['warning','success','danger','info'];
      $top4Vendidos  = array_slice($topTen ?? [], 0, 4);
      $top4Rentables = array_slice($ventasCompleto['TopProductosRentables'] ?? [], 0, 4);
    @endphp

    <div class="row g-3">

      {{-- Productos más vendidos --}}
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#198754 0%,#157347 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px;height:36px;background:rgba(255,255,255,.2);">
                <i class="bi bi-fire text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Más Vendidos</h6>
                <small class="text-white-50">Top 4 por unidades</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand"  class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>

          <div class="card-body p-0">
            @forelse($top4Vendidos as $index => $item)
              @php
                $urlImagen = FileHelper::getOrDownloadFile(
                    'images/items/thumbs/',
                    $item['Producto']['UrlFoto'] ?? '',
                    'assets/img/adminlte/img/produc_default.jfif'
                );
                $descripcion      = $item['Descripcion'] ?? 'Sin descripción';
                $descripcionCorta = strlen($descripcion) > 38 ? substr($descripcion, 0, 38).'...' : $descripcion;
                $productoId       = $item['Id'];
              @endphp
              <div class="d-flex align-items-center gap-3 px-3 py-2
                           {{ $index < count($top4Vendidos) - 1 ? 'border-bottom' : '' }}">
                <img src="{{ $urlImagen }}"
                     class="rounded img-zoomable flex-shrink-0"
                     style="width:46px;height:46px;object-fit:cover;cursor:zoom-in;border:2px solid #e2e8f0;"
                     alt="{{ $descripcion }}"
                     data-full-image="{{ $urlImagen }}"
                     data-description="{{ $descripcion }}">
                <div class="flex-grow-1" style="min-width:0;">
                  <a href="javascript:void(0);" onclick="verDetalleProducto({{ $productoId }})"
                     class="text-decoration-none text-dark">
                    <div class="fw-semibold text-truncate" style="font-size:13px;" title="{{ $descripcion }}">
                      {{ $descripcionCorta }}
                    </div>
                    <div class="mt-1">
                      <span class="badge bg-light text-dark border" style="font-size:10px;">
                        <i class="bi bi-upc me-1"></i>{{ $item['Codigo'] ?? 'N/A' }}
                      </span>
                    </div>
                  </a>
                </div>
                <span class="badge bg-success text-white fw-bold flex-shrink-0 px-2"
                      style="font-size:12px;min-width:34px;text-align:center;">
                  {{ $item['Cantidad'] ?? 0 }}
                </span>
              </div>
            @empty
              <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">
                <i class="bi bi-cart-x" style="font-size:2rem;opacity:.25;"></i>
                <p class="mt-2 mb-0 small">Sin datos disponibles</p>
              </div>
            @endforelse
          </div>

          <div class="card-footer text-center py-2 bg-white border-top">
            <a href="javascript:void(0)" class="text-decoration-none text-success small fw-semibold">
              <i class="bi bi-list-ul me-1"></i>Ver todos
            </a>
          </div>
        </div>
      </div>

      {{-- Productos más rentables --}}
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px;height:36px;background:rgba(255,255,255,.25);">
                <i class="bi bi-gem text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Más Rentables</h6>
                <small class="text-white-50">Top 4 por utilidad USD</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand"  class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>

          <div class="card-body p-0">
            @forelse($top4Rentables as $index => $item)
              @php
                $urlImagen = FileHelper::getOrDownloadFile(
                    'images/items/thumbs/',
                    $item['UrlFoto'] ?? '',
                    'assets/img/adminlte/img/default-150x150.png'
                );
                $descripcion      = $item['Descripcion'] ?? 'Sin descripción';
                $descripcionCorta = strlen($descripcion) > 38 ? substr($descripcion, 0, 38).'...' : $descripcion;
                $productoId       = $item['ProductoId'];
              @endphp
              <div class="d-flex align-items-center gap-3 px-3 py-2
                           {{ $index < count($top4Rentables) - 1 ? 'border-bottom' : '' }}">
                <img src="{{ $urlImagen }}"
                     class="rounded img-zoomable flex-shrink-0"
                     style="width:46px;height:46px;object-fit:cover;cursor:zoom-in;border:2px solid #e2e8f0;"
                     alt="{{ $descripcion }}"
                     data-full-image="{{ $urlImagen }}"
                     data-description="{{ $descripcion }}">
                <div class="flex-grow-1" style="min-width:0;">
                  <a href="javascript:void(0);" onclick="verDetalleProducto({{ $productoId }})"
                     class="text-decoration-none text-dark">
                    <div class="fw-semibold text-truncate" style="font-size:13px;" title="{{ $descripcion }}">
                      {{ $descripcionCorta }}
                    </div>
                    <div class="mt-1">
                      <span class="badge bg-light text-dark border" style="font-size:10px;">
                        <i class="bi bi-upc me-1"></i>{{ $item['Codigo'] ?? 'N/A' }}
                      </span>
                    </div>
                  </a>
                </div>
                <span class="badge bg-warning text-dark fw-bold flex-shrink-0 px-2"
                      style="font-size:11px;min-width:52px;text-align:right;">
                  ${{ number_format($item['UtilidadDivisa'], 2, ',', '.') }}
                </span>
              </div>
            @empty
              <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">
                <i class="bi bi-gem" style="font-size:2rem;opacity:.25;"></i>
                <p class="mt-2 mb-0 small">Sin datos disponibles</p>
              </div>
            @endforelse
          </div>

          <div class="card-footer text-center py-2 bg-white border-top">
            <a href="javascript:void(0)" class="text-decoration-none small fw-semibold"
               style="color:#d97706;">
              <i class="bi bi-list-ol me-1"></i>Ver ranking completo
            </a>
          </div>
        </div>
      </div>

      {{-- Ventas por día --}}
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-3 border-0"
               style="background:linear-gradient(135deg,#0891b2 0%,#0e7490 100%);">
            <div class="d-flex align-items-center">
              <div class="rounded-2 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                   style="width:36px;height:36px;background:rgba(255,255,255,.2);">
                <i class="bi bi-pie-chart-fill text-white"></i>
              </div>
              <div>
                <h6 class="fw-bold text-white mb-0">Ventas por Día</h6>
                <small class="text-white-50">Distribución semanal</small>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-1"
                    data-lte-toggle="card-collapse">
              <i data-lte-icon="expand"  class="bi bi-chevron-down"></i>
              <i data-lte-icon="collapse" class="bi bi-chevron-up"></i>
            </button>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-3">
            <canvas id="ventasPorDiaChart" height="250"></canvas>
          </div>
        </div>
      </div>

    </div>
    {{-- /Cards Inferiores --}}

    {{-- Image zoom overlay --}}
    <div id="imageZoomOverlay" class="image-zoom-overlay" style="display:none;">
      <div class="image-zoom-container">
        <span class="image-zoom-close" onclick="closeZoom()">&times;</span>
        <img id="zoomedImage" src="" alt="">
        <div id="imageDescription" class="image-description"></div>
      </div>
    </div>

  </div>
</div>

@endsection


@section('js')

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    document.addEventListener("DOMContentLoaded", function () {

        const listaOriginal = @json($ventas->listaVentasDiarias);
        const fechaInicio = "{{ $ventas->raw['FechaInicio'] }}";
        const fechaFin    = "{{ $ventas->raw['FechaFin'] }}";

        // =========================================
        // AGRUPACIÓN SOLO EN DOS MODOS: DIARIO / MENSUAL
        // =========================================
        function agrupar(lista, fechaInicio, fechaFin) {
            const inicio = moment(fechaInicio);
            const fin = moment(fechaFin);

            let resultado = [];
            let descripciones = [];

            const mismoMesYAnio = inicio.year() === fin.year() && inicio.month() === fin.month();

            // =====================================
            // 1) MISMO MES → AGRUPAR POR DÍA
            // =====================================
            if (mismoMesYAnio) {
                resultado = lista.map(d => ({
                    label: moment(d.Fecha).format("DD MMM"),
                    valor: parseFloat(d.UtilidadDivisaDiario ?? 0).toFixed(2)
                }));
                descripciones = resultado.map(r => r.label);
                return { resultado, descripciones };
            }

            // =====================================
            // 2) DIFERENTE MES → AGRUPAR POR MESES
            // =====================================
            let meses = {};
            lista.forEach(d => {
                const mesKey = moment(d.Fecha).format("YYYY-MM"); // clave por mes
                if (!meses[mesKey]) meses[mesKey] = [];
                meses[mesKey].push(d);
            });

            resultado = Object.keys(meses).map(m => {
                const elementos = meses[m];
                const fechaMes = moment(m + "-01", "YYYY-MM-DD");

                const total = elementos.reduce((t, x) =>
                    t + parseFloat(x.UtilidadDivisaDiario ?? 0), 0
                );

                descripciones.push(fechaMes.format("MMMM YYYY")); // Ej: noviembre 2025

                return {
                    label: fechaMes.format("MMM YYYY"), // eje X → Nov 2025
                    valor: total.toFixed(2)
                };
            });

            return { resultado, descripciones };
        }

        // =====================================
        // APLICAR AGRUPACIÓN
        // =====================================
        const { resultado, descripciones } = agrupar(listaOriginal, fechaInicio, fechaFin);

        const categories = resultado.map(x => x.label);
        const dataValues = resultado.map(x => parseFloat(x.valor));

        // =====================================
        // GRÁFICA APEX
        // =====================================
        const options = {
            chart: {
                type: 'line',
                height: 300
            },
            series: [{
                name: 'Utilidad (Divisa)',
                data: dataValues
            }],
            xaxis: {
                categories: categories,
                labels: {
                    rotate: -45,
                    trim: true
                }
            },
            yaxis: {
                labels: {
                    formatter: v => parseFloat(v).toFixed(2)
                }
            },
            stroke: {
                curve: 'smooth'
            }
        };

        const chart = new ApexCharts(document.querySelector("#sales-chart"), options);
        chart.render();

        const contenedor = document.getElementById("grafico-detalle");
        const colores = [
            "#4e79a7", "#f28e2b", "#e15759", "#76b7b2",
            "#59a14f", "#edc948", "#b07aa1", "#ff9da7"
        ];

        if (contenedor) {
            const maxValor = Math.max(...dataValues);

            // Creamos el contenido de los items
            const itemsHTML = resultado.map((r, i) => {
                const porcentaje = Math.min(100, (parseFloat(r.valor) / maxValor * 100));
                const color = colores[i % colores.length];

                return `
                    <div class="progress-group mb-2">
                        <div class="d-flex justify-content-between">
                            <span>${descripciones[i]}</span>
                            <span><b>${parseFloat(r.valor).toFixed(2)}</b></span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar"
                                style="width: ${porcentaje}%; background-color: ${color};">
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // Si hay más de 7 items, agregamos scroll vertical
            const alturaMax = resultado.length > 7 ? "292px" : "auto";

            contenedor.innerHTML = `
                <p class="text-center"><strong>Detalle de Periodos</strong></p>
                <div style="max-height: ${alturaMax}; overflow-y: auto; padding-right: 5px;">
                    ${itemsHTML}
                </div>
            `;
        }

        // =====================================
        // GRÁFICA Ventas por dia
        // =====================================
        const ctx = document.getElementById('ventasPorDiaChart').getContext('2d');

        const data = {
            labels: {!! json_encode($ventasPorDiaSemana->keys()) !!},  // nombres de días
            datasets: [{
                label: 'Ventas totales',
                data: {!! json_encode($ventasPorDiaSemana->values()) !!}, // totales por día
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56',
                    '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'
                ]
            }]
        };

        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return context.label + ': $' + value.toLocaleString('es-VE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        };

        new Chart(ctx, config);
    });

    document.getElementById('form-fechas').addEventListener('submit', function(e) {
        const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
        const fechaFin = new Date(document.getElementById('fecha_fin').value);

        if (fechaInicio > fechaFin) {
            e.preventDefault();
            showToast('La fecha de inicio no puede ser mayor a la fecha final.', 'danger');
            return;
        }

        const diffAnios = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24 * 365);
        if (diffAnios > 1) {
            e.preventDefault();
            showToast('El rango de fechas no puede ser mayor a 1 año.', 'danger');
            return;
        }
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

    function verDetalleProducto(id) {
        var ruta = '{{ url("/") }}' + '/productos/' + id;
        window.location.href = ruta;
    }

</script>

@endsection
