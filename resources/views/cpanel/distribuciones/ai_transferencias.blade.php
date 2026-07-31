{{-- resources/views/cpanel/distribuciones/ai_transferencias.blade.php --}}

@extends('layout.layout_dashboard')

@section('title', 'Sugerencias de Transferencias (IA)')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-robot text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Sugerencias de Transferencias (IA)</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Sugerencias basadas en análisis de inventario
                            @if($estadisticas['sucursal_seleccionada_id'] > 0)
                                <span class="badge bg-primary ms-2">Sucursal: {{ $estadisticas['sucursal_seleccionada'] }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="#">Distribuciones</a></li>
                    <li class="breadcrumb-item active">Sugerencias IA</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Estadísticas --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-primary">{{ $estadisticas['total_sugerencias'] }}</h3>
                        <p class="text-muted mb-0">Total Sugerencias</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-success">{{ $estadisticas['productos_unicos'] }}</h3>
                        <p class="text-muted mb-0">Productos Únicos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-warning">{{ $estadisticas['sucursales_origen'] }}</h3>
                        <p class="text-muted mb-0">Sucursales Origen</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-info">{{ $estadisticas['sucursales_destino'] }}</h3>
                        <p class="text-muted mb-0">Sucursales Destino</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MENSAJE CUANDO NO HAY SUCURSAL SELECCIONADA --}}
        {{-- ========================================== --}}
        @if($estadisticas['sin_sucursal'] ?? false)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-building fs-1 text-muted"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Seleccione una Sucursal</h5>
                        <p class="text-muted">
                            Para ver las sugerencias de transferencia, debe seleccionar una sucursal específica 
                            desde el selector de sucursales en la parte superior.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-warning text-dark p-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Seleccione una sucursal para continuar
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else

        {{-- ========================================== --}}
        {{-- LISTADO DE SUGERENCIAS (solo cuando hay sucursal) --}}
        {{-- ========================================== --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-lightbulb me-2"></i>SUGERENCIAS DE TRANSFERENCIA
                        @if($estadisticas['sucursal_seleccionada_id'] > 0)
                            <span class="ms-2 badge bg-light text-dark">
                                {{ $estadisticas['sucursal_seleccionada'] }}
                            </span>
                        @endif
                    </h6>
                    <div class="d-flex gap-2 align-items-center">
                        {{-- Selector de tipo de sugerencia --}}
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-white" style="font-size:0.75rem;">Mostrar:</span>
                            <select id="tipoSugerencia" class="form-select form-select-sm bg-white" style="width:auto;font-size:0.75rem;padding:0.25rem 1.5rem 0.25rem 0.5rem;">
                                <option value="ventas" {{ $tipoSugerencia == 'ventas' ? 'selected' : '' }}>📈 Por Ventas</option>
                                <option value="existencia" {{ $tipoSugerencia == 'existencia' ? 'selected' : '' }}>📦 Por Existencia</option>
                            </select>
                        </div>

                        {{-- Botón Descargar Plantilla --}}
                        @if($estadisticas['sucursal_seleccionada_id'] > 0)
                        <a href="{{ route('cpanel.distribucion.ai-transferencias.descargar-plantilla') }}?tipo={{ $tipoSugerencia }}" 
                           class="btn btn-light btn-sm fw-semibold text-dark"
                           title="Descargar plantilla para {{ $estadisticas['sucursal_seleccionada'] }}"
                           style="font-size:0.78rem;">
                            <i class="bi bi-download me-1"></i>Descargar Plantilla
                        </a>
                        @else
                        <button type="button" 
                                class="btn btn-light btn-sm fw-semibold text-dark opacity-50"
                                style="font-size:0.78rem;cursor:not-allowed;"
                                title="Seleccione una sucursal específica para descargar la plantilla"
                                disabled>
                            <i class="bi bi-download me-1"></i>Descargar Plantilla
                        </button>
                        @endif

                        {{-- Buscador --}}
                        <div class="input-group input-group-sm" style="width:250px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0" 
                                   id="buscadorSugerencias" 
                                   placeholder="Buscar por código...">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tablaSugerencias">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:60px;cursor:pointer;" data-order="foto">Foto</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="codigo">
                                    Código <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="descripcion">
                                    Descripción <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="origen">
                                    Origen <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="stock_origen">
                                    Stock Origen <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="destino">
                                    Destino <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="stock_destino">
                                    Stock Destino <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="cantidad">
                                    Cantidad Sugerida <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="prioridad">
                                    Prioridad <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;" data-order="motivo">
                                    Motivo <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tablaSugerenciasBody">
                            @forelse($sugerencias as $sugerencia)
                            @php
                                $colorPrioridad = $sugerencia->Prioridad >= 80 ? 'danger' : ($sugerencia->Prioridad >= 60 ? 'warning' : ($sugerencia->Prioridad >= 40 ? 'info' : 'success'));
                                
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $sugerencia->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                            @endphp
                            <tr data-codigo="{{ strtolower($sugerencia->Codigo) }}" style="border-bottom:1px solid #f1f5f9;">
                                <td class="text-center" style="width: 60px; max-width: 60px; padding: 5px;">
                                    <img src="{{ $imgSrc }}" 
                                        alt="{{ $sugerencia->Codigo }}"
                                        loading="lazy"
                                        style="width: 45px; height: 45px; object-fit: cover; cursor: pointer; border-radius: 8px; border: 2px solid #e5e7eb; display: block; margin: 0 auto;"
                                        data-full-image="{{ $imgSrc }}"
                                        data-description="{{ $sugerencia->Descripcion }}"
                                        onclick="zoomImagen(this)"
                                        onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td class="codigo-cell">
                                    <span class="fw-bold text-dark" style="font-size:0.85rem;font-family:monospace;">
                                        {{ $sugerencia->Codigo }}
                                    </span>
                                </td>
                                <td class="descripcion-cell" style="font-size:0.85rem;">{{ $sugerencia->Descripcion }}</td>
                                <td class="origen-cell" style="font-size:0.85rem;">{{ $sugerencia->SucursalOrigen }}</td>
                                <td class="text-center stock-origen-cell fw-bold" style="font-size:0.85rem;">
                                    <span class="badge bg-success">{{ $sugerencia->StockOrigen }}</span>
                                </td>
                                <td class="destino-cell" style="font-size:0.85rem;">{{ $sugerencia->SucursalDestino }}</td>
                                <td class="text-center stock-destino-cell fw-bold" style="font-size:0.85rem;">
                                    <span class="badge bg-danger">{{ $sugerencia->StockDestino }}</span>
                                </td>
                                <td class="text-center cantidad-cell fw-bold text-primary" style="font-size:1rem;">
                                    {{ $sugerencia->CantidadSugerida }}
                                </td>
                                <td class="text-center prioridad-cell">
                                    <span class="badge bg-{{ $colorPrioridad }}">
                                        {{ $sugerencia->Prioridad }}%
                                    </span>
                                </td>
                                <td class="motivo-cell" style="font-size:0.8rem; max-width:300px;">
                                    {!! $sugerencia->Motivo !!}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-robot fs-1 d-block mb-2"></i>
                                    No hay sugerencias de transferencia en este momento.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sugerencias->count() > 0)
            <div class="card-footer border-0 py-3 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <small class="text-muted">
                        Mostrando <span id="sugerenciasVisibles">{{ $sugerencias->count() }}</span> de <span id="sugerenciasTotales">{{ $sugerencias->count() }}</span> sugerencias
                    </small>
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ==========================================
    // FUNCIÓN ZOOM IMAGEN
    // ==========================================
    function zoomImagen(elemento) {
        const imgSrc = elemento.getAttribute('data-full-image') || elemento.src;
        const descripcion = elemento.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            confirmButtonColor: '#7c3aed',
            confirmButtonText: 'Cerrar',
            showCloseButton: true
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // REFERENCIAS A ELEMENTOS
        // ==========================================
        const buscador = document.getElementById('buscadorSugerencias');
        const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
        const tablaBody = document.getElementById('tablaSugerenciasBody');
        const filas = tablaBody ? Array.from(tablaBody.querySelectorAll('tr:not(.no-result)')) : [];
        const contadorVisibles = document.getElementById('sugerenciasVisibles');
        const contadorTotales = document.getElementById('sugerenciasTotales');

        // ✅ SELECTOR DE TIPO DE SUGERENCIA
        const tipoSugerencia = document.getElementById('tipoSugerencia');
        
        if (tipoSugerencia) {
            tipoSugerencia.addEventListener('change', function() {
                const tipo = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('tipo', tipo);
                window.location.href = url.toString();
            });
        }

        // ==========================================
        // BUSCADOR POR CÓDIGO
        // ==========================================
        function filtrarSugerencias() {
            if (!buscador) return;
            
            const termino = buscador.value.toLowerCase().trim();
            let visibles = 0;

            filas.forEach(function(fila) {
                const codigo = fila.querySelector('.codigo-cell')?.textContent?.toLowerCase() || '';
                
                if (!termino || codigo.includes(termino)) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Actualizar contador
            if (contadorVisibles) contadorVisibles.textContent = visibles;
            if (contadorTotales) contadorTotales.textContent = filas.length;

            // Mostrar mensaje si no hay resultados
            let noResult = tablaBody?.querySelector('.no-result');
            if (visibles === 0 && filas.length > 0) {
                if (!noResult) {
                    noResult = document.createElement('tr');
                    noResult.className = 'no-result';
                    noResult.innerHTML = `
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-search fs-3 d-block mb-2"></i>
                            No se encontraron sugerencias que coincidan con "${termino}"
                        </td>
                    `;
                    if (tablaBody) tablaBody.appendChild(noResult);
                } else {
                    noResult.style.display = '';
                    noResult.querySelector('td').innerHTML = `
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No se encontraron sugerencias que coincidan con "${termino}"
                    `;
                }
            } else if (noResult) {
                noResult.style.display = 'none';
            }
        }

        // Evento del buscador
        if (buscador) {
            buscador.addEventListener('input', filtrarSugerencias);
            buscador.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    filtrarSugerencias();
                }
            });
        }

        // Botón limpiar búsqueda
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function() {
                if (buscador) {
                    buscador.value = '';
                    filtrarSugerencias();
                    buscador.focus();
                }
            });
        }

        // ==========================================
        // ORDENAMIENTO DE TABLA
        // ==========================================
        let ordenActual = { columna: null, direccion: 'asc' };

        document.querySelectorAll('th[data-order]').forEach(function(th) {
            th.style.cursor = 'pointer';
            
            th.addEventListener('click', function() {
                const columna = this.dataset.order;
                if (!columna || columna === 'foto') return;

                // Cambiar dirección
                if (ordenActual.columna === columna) {
                    ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
                } else {
                    ordenActual.columna = columna;
                    ordenActual.direccion = 'asc';
                }

                // Actualizar icono de orden
                const icono = this.querySelector('.sort-icon');
                if (icono) {
                    icono.className = ordenActual.direccion === 'asc' 
                        ? 'bi bi-arrow-up' 
                        : 'bi bi-arrow-down';
                }

                // Ordenar
                const filasArray = Array.from(tablaBody.querySelectorAll('tr:not(.no-result)'));
                if (filasArray.length === 0) return;

                filasArray.sort(function(a, b) {
                    let valorA, valorB;

                    if (columna === 'codigo') {
                        valorA = a.querySelector('.codigo-cell')?.textContent?.trim() || '';
                        valorB = b.querySelector('.codigo-cell')?.textContent?.trim() || '';
                    } else if (columna === 'descripcion') {
                        valorA = a.querySelector('.descripcion-cell')?.textContent?.trim() || '';
                        valorB = b.querySelector('.descripcion-cell')?.textContent?.trim() || '';
                    } else if (columna === 'origen') {
                        valorA = a.querySelector('.origen-cell')?.textContent?.trim() || '';
                        valorB = b.querySelector('.origen-cell')?.textContent?.trim() || '';
                    } else if (columna === 'stock_origen') {
                        const badgeA = a.querySelector('.stock-origen-cell .badge');
                        const badgeB = b.querySelector('.stock-origen-cell .badge');
                        valorA = parseInt(badgeA?.textContent?.trim() || 0);
                        valorB = parseInt(badgeB?.textContent?.trim() || 0);
                    } else if (columna === 'destino') {
                        valorA = a.querySelector('.destino-cell')?.textContent?.trim() || '';
                        valorB = b.querySelector('.destino-cell')?.textContent?.trim() || '';
                    } else if (columna === 'stock_destino') {
                        const badgeA = a.querySelector('.stock-destino-cell .badge');
                        const badgeB = b.querySelector('.stock-destino-cell .badge');
                        valorA = parseInt(badgeA?.textContent?.trim() || 0);
                        valorB = parseInt(badgeB?.textContent?.trim() || 0);
                    } else if (columna === 'cantidad') {
                        const textA = a.querySelector('.cantidad-cell')?.textContent?.trim() || '0';
                        const textB = b.querySelector('.cantidad-cell')?.textContent?.trim() || '0';
                        valorA = parseFloat(textA) || 0;
                        valorB = parseFloat(textB) || 0;
                    } else if (columna === 'prioridad') {
                        const badgeA = a.querySelector('.prioridad-cell .badge');
                        const badgeB = b.querySelector('.prioridad-cell .badge');
                        valorA = parseInt(badgeA?.textContent?.trim() || 0);
                        valorB = parseInt(badgeB?.textContent?.trim() || 0);
                    } else if (columna === 'motivo') {
                        valorA = a.querySelector('.motivo-cell')?.textContent?.trim() || '';
                        valorB = b.querySelector('.motivo-cell')?.textContent?.trim() || '';
                    } else {
                        return 0;
                    }

                    if (typeof valorA === 'string') {
                        valorA = valorA.toLowerCase();
                        valorB = valorB.toLowerCase();
                        return ordenActual.direccion === 'asc' 
                            ? valorA.localeCompare(valorB) 
                            : valorB.localeCompare(valorA);
                    } else {
                        return ordenActual.direccion === 'asc' 
                            ? valorA - valorB 
                            : valorB - valorA;
                    }
                });

                // Reordenar en el DOM
                filasArray.forEach(function(fila) {
                    if (tablaBody) tablaBody.appendChild(fila);
                });

                // Actualizar contador
                if (contadorVisibles) contadorVisibles.textContent = filasArray.length;
                if (contadorTotales) contadorTotales.textContent = filasArray.length;
            });
        });

        // ==========================================
        // INICIALIZAR CONTADOR
        // ==========================================
        if (contadorVisibles) contadorVisibles.textContent = filas.length;
        if (contadorTotales) contadorTotales.textContent = filas.length;

        console.log('✅ Sugerencias IA cargadas');
    });
</script>
@endsection

@push('styles')
<style>
    .img-zoomable {
        transition: transform 0.2s;
    }
    .img-zoomable:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .table td {
        vertical-align: middle;
    }
    th[data-order] {
        cursor: pointer;
        user-select: none;
    }
    th[data-order]:hover {
        background-color: #e9ecef;
    }
    .sort-icon {
        font-size: 0.65rem;
        opacity: 0.6;
        transition: all 0.2s;
    }
    th[data-order]:hover .sort-icon {
        opacity: 1;
    }
</style>
@endpush