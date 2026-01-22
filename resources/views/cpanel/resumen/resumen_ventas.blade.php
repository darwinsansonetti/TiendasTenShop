@extends('layout.layout_dashboard')

@section('title', 'TiensasTenShop | Resumen de ventas')

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
      <div class="col-sm-6"><h3 class="mb-0">Resumen de ventas</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Resumen de ventas</li>
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
            <span class="info-box-text">Unidades Vendidas</span>
            <span class="info-box-number">
                {{ $ventas->listaVentasDiarias->sum('UnidadesVendidas') > 0 ? number_format($ventas->listaVentasDiarias->sum('UnidadesVendidas'), 0, ',', '.') : '0' }}
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
            <span class="info-box-text">Producción Divisa</span>
            <span class="info-box-number">
                {{ $ventas->listaVentasDiarias->sum('TotalDivisa') > 0 ? number_format($ventas->listaVentasDiarias->sum('TotalDivisa'), 2, ',', '.') : '0'; }}
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
            <span class="info-box-text">Utilidad Divisa</span>
            <span class="info-box-number">
                {{ $ventas->listaVentasDiarias->sum('UtilidadDivisaDiario') > 0 ? number_format($ventas->listaVentasDiarias->sum('UtilidadDivisaDiario'), 2, ',', '.') : '0'; }}

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
            <span class="info-box-text">Promedio</span>
            <span class="info-box-number">
                {{ number_format($ventas->raw['MargenDivisasPeriodo'], 2, ',', '.') }}
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

    <!--begin::Row - Grafica Principal-->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <strong>Resumen de Ventas:</strong> {{ session('sucursal_nombre') }}
                    </h5>
                    <div class="card-tools">
                        <div class="btn-group">
                            <form id="form-fechas" method="GET" action="{{ route('cpanel.resumen.ventas') }}" class="d-flex align-items-center px-2">
                                @php
                                    $fechaInicio = old('fecha_inicio', \Carbon\Carbon::parse($ventas->raw['FechaInicio'])->format('Y-m-d'));
                                    $fechaFin = old('fecha_fin', \Carbon\Carbon::parse($ventas->raw['FechaFin'])->format('Y-m-d'));
                                @endphp

                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm me-1" value="{{ $fechaInicio }}">
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm me-1" value="{{ $fechaFin }}">

                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">

                            {{-- PERIODO --}}
                            <p class="text-center">
                                <strong>
                                    Periodo: {{ \Carbon\Carbon::parse($ventas->raw['FechaInicio'])->format('d M, Y') }} 
                                    - {{ \Carbon\Carbon::parse($ventas->raw['FechaFin'])->format('d M, Y') }}
                                </strong>
                            </p>

                            {{-- GRÁFICA INSERTADA JUSTO DEBAJO DEL TEXTO --}}
                            <div id="sales-chart" style="height: 300px;"></div>

                        </div>

                        <div class="col-md-4">
                            <div id="grafico-detalle"></div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <!--end::Row - Grafica Principal-->

    <!--begin::Row-->
    <div class="row">
        <!-- Start col -->
        <div class="col-md-8">
        <!--begin::Row-->
        @php
            $colores = ['warning', 'success', 'danger', 'info'];
            $top4Vendidos = array_slice($topTen ?? [], 0, 4);
            $top4Rentables = array_slice($ventasCompleto['TopProductosRentables'] ?? [], 0, 4);
        @endphp

        <div class="row g-4 mb-4">
            <!-- Productos más vendidos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Productos más vendidos</h3>                        
                        <div class="card-tools"> 
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"> 
                                <i data-lte-icon="expand" class="bi bi-plus-lg"></i> 
                                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i> 
                            </button> 
                        </div>
                    </div>
                    <div class="card-body p-0 px-2">
                        @forelse($top4Vendidos as $index => $item)
                            @php
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $item['Producto']['UrlFoto'] ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                $descripcion = $item['Descripcion'] ?? 'Sin descripción';
                                $descripcionCorta = strlen($descripcion) > 40 ? substr($descripcion, 0, 40) . '...' : $descripcion;
                                
                                $productoId = $item['Id'];
                            @endphp

                            <div class="d-flex border-top py-2 px-1 align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <!-- Imagen con click para zoom -->
                                    <img src="{{ $urlImagen }}" 
                                        class="rounded border img-zoomable" 
                                        style="width:50px; height:50px; object-fit:cover; cursor: zoom-in;" 
                                        alt="{{ $descripcion }}"
                                        data-full-image="{{ $urlImagen }}"
                                        data-description="{{ $descripcion }}">
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <a href="javascript:void(0);" onclick="verDetalleProducto({{ $productoId }})" class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-truncate" style="max-width: 80%;" title="{{ $descripcion }}">
                                                {{ $descripcion }}
                                            </span>
                                            <span class="badge bg-warning text-dark flex-shrink-0" style="font-size: 0.75rem;">
                                                {{ $item['Cantidad'] ?? 0 }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-barcode me-1"></i>{{ $item['Codigo'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">No hay productos vendidos</div>
                        @endforelse
                    </div>

                    <div class="card-footer text-center py-2">
                        <a href="javascript:void(0)" class="text-decoration-none small">
                            <i class="fas fa-list me-1"></i>Ver todos 
                        </a>
                    </div>
                </div>
            </div>

            <!-- Productos más rentables -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Productos más rentables</h3>
                        <div class="card-tools"> 
                            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"> 
                                <i data-lte-icon="expand" class="bi bi-plus-lg"></i> 
                                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i> 
                            </button> 
                        </div>
                    </div>
                    <div class="card-body p-0 px-2">
                        @forelse($top4Rentables as $index => $item)
                            @php
                                $urlImagen = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $item['UrlFoto'] ?? '',
                                    'assets/img/adminlte/img/default-150x150.png'
                                );
                                $descripcion = $item['Descripcion'] ?? 'Sin descripción';
                                $descripcionCorta = strlen($descripcion) > 40 ? substr($descripcion, 0, 40) . '...' : $descripcion;
                                
                                $productoId = $item['ProductoId'];
                            @endphp

                            <div class="d-flex border-top py-2 px-1 align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <img src="{{ $urlImagen }}" 
                                        class="rounded border img-zoomable" 
                                        style="width:50px; height:50px; object-fit:cover; cursor: zoom-in;" 
                                        alt="{{ $descripcion }}"
                                        data-full-image="{{ $urlImagen }}"
                                        data-description="{{ $descripcion }}">
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <a href="javascript:void(0);" onclick="verDetalleProducto({{ $productoId }})" class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-semibold text-truncate" style="max-width: 70%;" title="{{ $descripcion }}">
                                                {{ $descripcionCorta }}
                                            </span>
                                            <span class="badge bg-warning text-dark flex-shrink-0" style="font-size: 0.75rem;">
                                                ${{ number_format($item['UtilidadDivisa'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border"><i class="fas fa-barcode me-1"></i>{{ $item['Codigo'] ?? 'N/A' }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">No hay productos rentables</div>
                        @endforelse
                    </div>
                    <div class="card-footer text-center py-2">
                        <a href="javascript:void(0)" class="text-decoration-none small">
                            <i class="fas fa-list me-1"></i>Ver ranking completo
                        </a>
                    </div>
                </div>
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

        <!--end::Row-->
        </div>
        <!-- /.col -->
         
        <!-- Ventas por Dia -->
        <div class="col-md-4">        
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Ventas por día</h3>
                    <div class="card-tools"> 
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"> 
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i> 
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i> 
                        </button> 
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ventasPorDiaChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <!-- /.Ventas por Dia -->

    </div>
    <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content Header-->

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