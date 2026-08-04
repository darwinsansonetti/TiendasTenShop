@extends('layout.layout_dashboard')

@section('title', 'Consolidación de Productos')

@php
    use App\Helpers\FileHelper;
    
    // Calcular estadísticas directamente en la vista
    $totalProductos = $productos->count();
    $totalStock = $productos->sum('Existencia');
    $productosConStock = $productos->filter(fn($p) => ($p->Existencia ?? 0) > 0)->count();
    
    // Obtener nombre de la sucursal seleccionada
    $sucursalNombre = $sucursales->firstWhere('ID', $sucursalId)?->Nombre ?? 'Sucursal';
    $esAlmacen = $sucursales->firstWhere('ID', $sucursalId)?->Tipo == 2;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="bi bi-merge text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Consolidación de Productos</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            Identifica productos para consolidar en <strong>{{ $sucursalNombre }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="#">Distribuciones</a></li>
                    <li class="breadcrumb-item active">Consolidación</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Filtros con Selector de Sucursal --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('cpanel.distribucion.consolidar') }}" class="row g-3 align-items-end">
                    {{-- Selector de Sucursal --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-muted small">
                            <i class="bi bi-building me-1"></i> Sucursal / Almacén
                        </label>
                        <select class="form-select" name="sucursal_id" onchange="this.form.submit()">
                            <option value="0">-- Seleccionar --</option>
                            
                            @php
                                $almacenes = $sucursales->where('Tipo', 2);
                                $sucursalesVentas = $sucursales->where('Tipo', 1);
                            @endphp

                            @if($almacenes->count() > 0)
                                <optgroup label="ALMACENES">
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->ID }}" 
                                            {{ $sucursalId == $almacen->ID ? 'selected' : '' }}>
                                            📦 {{ $almacen->Nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($sucursalesVentas->count() > 0)
                                <optgroup label="SUCURSALES">
                                    @foreach($sucursalesVentas as $sucursal)
                                        <option value="{{ $sucursal->ID }}" 
                                            {{ $sucursalId == $sucursal->ID ? 'selected' : '' }}>
                                            🏪 {{ $sucursal->Nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    {{-- Búsqueda en tiempo real --}}
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-muted small">
                            <i class="bi bi-search me-1"></i> Buscar en tabla
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background:#f1f5f9;">
                                <i class="bi bi-search text-muted" style="font-size:0.75rem;"></i>
                            </span>
                            <input type="text" 
                                   id="buscadorProductos" 
                                   class="form-control form-control-sm border-0" 
                                   placeholder="Buscar por código..."
                                   style="background:#f1f5f9;font-size:0.78rem;"
                                   oninput="filtrarTabla()">
                        </div>
                    </div>

                    {{-- Sucursal activa --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Sucursal Activa
                        </label>
                        <div class="mt-1">
                            <span class="badge bg-primary p-2" style="font-size:0.85rem;">
                                <i class="bi {{ $esAlmacen ? 'bi-archive' : 'bi-shop' }} me-1"></i>
                                {{ $sucursalNombre }}
                                @if($esAlmacen)
                                    <span class="badge bg-warning text-dark ms-1">ALMACÉN</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Botón para consolidar seleccionados --}}
        <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-success" onclick="consolidarSeleccionados()" id="btnConsolidarSeleccionados" disabled>
                <i class="bi bi-merge me-1"></i> Consolidar Seleccionados (<span id="contadorSeleccionados">0</span>)
            </button>
            <button class="btn btn-secondary" onclick="limpiarSeleccion()">
                <i class="bi bi-x-circle me-1"></i> Limpiar Selección
            </button>
            <span class="text-muted small ms-2">
                <i class="bi bi-info-circle me-1"></i> Haz clic en <strong>➕</strong> para agregar productos a consolidar (mínimo 2)
            </span>
        </div>

        {{-- Lista de productos --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list me-2"></i>PRODUCTOS EN {{ strtoupper($sucursalNombre) }}
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill"
                              style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                            <span id="totalProductosVisibles">{{ $totalProductos }}</span> / {{ $totalProductos }} productos
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaConsolidacion">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:70px;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">REFERENCIA</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">EXISTENCIA</th>
                                <th class="py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">COSTO USD</th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;width:100px;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            @forelse($productos ?? [] as $producto)
                            @php
                                $existencia = (float)($producto->Existencia ?? 0);
                                $tieneStock = $existencia > 0;
                                $costo = (float)($producto->CostoDivisa ?? 0);
                                
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $producto->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;" data-codigo="{{ strtoupper($producto->Codigo ?? '') }}" data-producto-id="{{ $producto->ID }}">
                                <td class="ps-4 text-center">
                                    <img src="{{ $imgSrc }}" 
                                         loading="lazy"
                                         alt="{{ $producto->Codigo ?? 'Producto' }}"
                                         class="img-thumbnail img-zoomable"
                                         style="width: 45px; height: 45px; object-fit: cover; cursor: pointer; border-radius: 6px;"
                                         data-full-image="{{ $imgSrc }}"
                                         data-description="{{ $producto->Descripcion ?? 'Producto' }}"
                                         onclick="zoomImagen(this)"
                                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td>
                                    <span class="badge rounded-2 fw-semibold"
                                          style="background:rgba(139,92,246,0.1);color:#7c3aed;border:1px solid rgba(139,92,246,0.25);font-size:0.78rem;font-family:monospace;">
                                        {{ $producto->Codigo ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.85rem;">{{ $producto->Referencia ?? 'N/A' }}</td>
                                <td class="fw-semibold text-dark" style="font-size:0.85rem;">
                                    {{ $producto->Descripcion ?? 'Sin descripción' }}
                                </td>
                                <td class="text-center fw-semibold">
                                    <span class="badge {{ $existencia > 0 ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2 py-1">
                                        {{ number_format($existencia, 0) }}
                                    </span>
                                </td>
                                <td class="text-center fw-semibold" style="color:#059669;">
                                    $ {{ number_format($costo, 2) }}
                                </td>
                                <td class="pe-4 text-center">
                                    <button class="btn btn-sm btn-success btn-agregar-consolidacion" 
                                            onclick="toggleSeleccion(this, '{{ $producto->ID }}')"
                                            title="Agregar a consolidación"
                                            data-seleccionado="false"
                                            data-producto-id="{{ $producto->ID }}">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-merge text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en esta sucursal</p>
                                    <small class="text-muted">Selecciona otra sucursal o almacén para ver productos</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($totalProductos > 0)
            <div class="card-footer border-0 py-3 px-4"
                 style="background:#f8fafc;border-top:1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <span class="text-muted" style="font-size:0.85rem;">
                            Mostrando <span id="totalProductosVisiblesFooter">{{ $totalProductos }}</span> de {{ $totalProductos }} productos
                            <span class="badge bg-success ms-2">
                                {{ $productosConStock }} con stock
                            </span>
                            <span class="badge bg-secondary ms-1">
                                {{ $totalProductos - $productosConStock }} sin stock
                            </span>
                        </span>
                    </div>
                    <div>
                        <span class="text-muted" style="font-size:0.85rem;">
                            Total unidades: 
                            <strong>{{ number_format($totalStock, 0) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de consolidación --}}
<div class="modal fade" id="modalConsolidacion" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-merge me-2"></i>Consolidar Productos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalConsolidacionBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Obtener sucursal actual
const sucursalActual = {{ $sucursalId ?? 0 }};

// Array para almacenar productos seleccionados
let productosSeleccionados = [];

// ============================================
// FILTRAR TABLA POR CÓDIGO (en tiempo real)
// ============================================
function filtrarTabla() {
    const input = document.getElementById('buscadorProductos');
    const filter = input.value.toUpperCase().trim();
    const tbody = document.getElementById('tablaBody');
    const rows = tbody.querySelectorAll('tr');
    
    const noDataRow = rows.length === 1 && rows[0].querySelector('td[colspan]');
    if (noDataRow) {
        return;
    }
    
    let visibles = 0;
    
    rows.forEach(row => {
        const codigo = (row.getAttribute('data-codigo') || '').toUpperCase();
        
        if (filter === '' || codigo.includes(filter)) {
            row.style.display = '';
            visibles++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('totalProductosVisibles').textContent = visibles;
    document.getElementById('totalProductosVisiblesFooter').textContent = visibles;
}

// ============================================
// FUNCIÓN PARA ZOOM DE IMAGEN
// ============================================
function zoomImagen(element) {
    const imgSrc = element.getAttribute('data-full-image') || element.src;
    const description = element.getAttribute('data-description') || 'Producto';
    
    Swal.fire({
        imageUrl: imgSrc,
        imageAlt: description,
        title: description,
        imageWidth: 400,
        imageHeight: 400,
        imageClass: 'rounded-3 shadow-lg',
        showCloseButton: true,
        showConfirmButton: false,
        confirmButtonColor: '#7c3aed'
    });
}

// ============================================
// FUNCIÓN PARA AGREGAR/QUITAR DE SELECCIÓN
// ============================================
function toggleSeleccion(button, productoId) {
    const isSeleccionado = button.dataset.seleccionado === 'true';
    const icon = button.querySelector('i');
    const row = button.closest('tr');
    
    if (isSeleccionado) {
        button.dataset.seleccionado = 'false';
        icon.className = 'bi bi-plus-circle';
        button.classList.remove('btn-danger');
        button.classList.add('btn-success');
        button.title = 'Agregar a consolidación';
        row.style.backgroundColor = '';
        productosSeleccionados = productosSeleccionados.filter(id => id !== productoId);
    } else {
        button.dataset.seleccionado = 'true';
        icon.className = 'bi bi-dash-circle';
        button.classList.remove('btn-success');
        button.classList.add('btn-danger');
        button.title = 'Quitar de consolidación';
        row.style.backgroundColor = '#f0fdf4';
        productosSeleccionados.push(productoId);
    }
    
    actualizarContador();
}

// ============================================
// ACTUALIZAR CONTADOR DE SELECCIONADOS
// ============================================
function actualizarContador() {
    const contador = document.getElementById('contadorSeleccionados');
    const btnConsolidar = document.getElementById('btnConsolidarSeleccionados');
    
    contador.textContent = productosSeleccionados.length;
    btnConsolidar.disabled = productosSeleccionados.length < 2;
}

// ============================================
// LIMPIAR SELECCIÓN
// ============================================
function limpiarSeleccion() {
    document.querySelectorAll('.btn-agregar-consolidacion').forEach(btn => {
        btn.dataset.seleccionado = 'false';
        btn.querySelector('i').className = 'bi bi-plus-circle';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-success');
        btn.title = 'Agregar a consolidación';
        btn.closest('tr').style.backgroundColor = '';
    });
    
    productosSeleccionados = [];
    actualizarContador();
    $('#modalConsolidacion').modal('hide');
}

// ============================================
// CONSOLIDAR SELECCIONADOS - MODAL CON RESÚMEN
// ============================================
function consolidarSeleccionados() {
    const totalSeleccionados = productosSeleccionados.length;
    
    if (totalSeleccionados < 2) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona más productos',
            text: 'Debes seleccionar al menos 2 productos para consolidar',
            confirmButtonColor: '#7c3aed'
        });
        return;
    }
    
    const productosData = [];
    let totalExistencia = 0;
    let totalCosto = 0;
    let countConCosto = 0;
    
    productosSeleccionados.forEach(id => {
        const row = document.querySelector(`tr[data-producto-id="${id}"]`);
        if (row) {
            const codigo = row.querySelector('td:nth-child(2) .badge')?.textContent?.trim() || 'N/A';
            const referencia = row.querySelector('td:nth-child(3)')?.textContent?.trim() || 'N/A';
            const descripcion = row.querySelector('td:nth-child(4)')?.textContent?.trim() || 'Sin descripción';
            
            const existenciaText = row.querySelector('td:nth-child(5)')?.textContent?.trim() || '0';
            const existencia = parseFloat(existenciaText.replace(/,/g, '')) || 0;
            
            const costoText = row.querySelector('td:nth-child(6)')?.textContent?.trim() || '$ 0.00';
            const costo = parseFloat(costoText.replace('$', '').replace(/,/g, '')) || 0;
            
            const img = row.querySelector('td:first-child img');
            const imgSrc = img ? img.src : '';
            
            productosData.push({
                id: id,
                codigo: codigo,
                referencia: referencia,
                descripcion: descripcion,
                existencia: existencia,
                costo: costo,
                imgSrc: imgSrc
            });
            
            totalExistencia += existencia;
            if (costo > 0) {
                totalCosto += costo;
                countConCosto++;
            }
        }
    });
    
    // ✅ Promedio Simple (como en .NET)
    const costoPromedio = countConCosto > 0 ? (totalCosto / countConCosto) : 0;
    
    let html = `
        {{-- Resumen de totales --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center py-2">
                        <small class="text-muted">Productos</small>
                        <h5 class="mb-0 text-primary">${productosData.length}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center py-2">
                        <small class="text-muted">Existencia Total</small>
                        <h5 class="mb-0 text-success">${totalExistencia.toFixed(0)}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center py-2">
                        <small class="text-muted">Costo Promedio</small>
                        <h5 class="mb-0 text-info">$${costoPromedio.toFixed(2)}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Tabla de productos seleccionados --}}
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 10;">
                    <tr>
                        <th style="width:50px;">FOTO</th>
                        <th>CÓDIGO</th>
                        <th>REFERENCIA</th>
                        <th>DESCRIPCIÓN</th>
                        <th class="text-center">EXISTENCIA</th>
                        <th class="text-center">COSTO</th>
                        <th style="width:60px; text-align:center;">ACCION</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    productosData.forEach((p, index) => {
        html += `
            <tr id="fila-seleccion-${p.id}">
                <td class="text-center">
                    <img src="${p.imgSrc}" 
                         style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px;"
                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                </td>
                <td><span class="fw-bold" style="font-family:monospace;">${p.codigo}</span></td>
                <td>${p.referencia}</td>
                <td>${p.descripcion}</td>
                <td class="text-center"><span class="badge bg-success">${p.existencia.toFixed(0)}</span></td>
                <td class="text-center" style="color:#059669;">$${p.costo.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger" onclick="quitarDeSeleccion('${p.id}')" title="Quitar de selección">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        
        <hr>
        
        {{-- ⭐ PRODUCTO DESTINO - Más visible --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-primary" style="border-width: 2px;">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0 fw-bold" style="font-size:0.85rem;">
                            <i class="bi bi-arrow-right-circle me-2"></i> PRODUCTO DESTINO (Mantener)
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <label class="fw-semibold text-muted small mb-1">
                                    <i class="bi bi-box me-1"></i> Seleccione el producto que desea mantener:
                                </label>
                                <select class="form-select" id="productoDestino" style="border-color: #7c3aed; font-size:0.85rem;">
    `;
    
    productosData.forEach(p => {
        const descCorta = p.descripcion.length > 35 ? p.descripcion.substring(0, 35) + '...' : p.descripcion;
        const selected = p === productosData[0] ? 'selected' : '';
        html += `<option value="${p.id}" ${selected}>${p.codigo} - ${descCorta}</option>`;
    });
    
    html += `
                                </select>
                                <small class="text-muted" style="font-size:0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Los productos seleccionados se consolidarán en este producto
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Botones de acción --}}
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" style="font-size:0.85rem;">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button class="btn btn-success" onclick="ejecutarConsolidacion()" style="font-size:0.85rem;">
                        <i class="bi bi-merge me-1"></i> Consolidar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#modalConsolidacionBody').html(html);
    $('#modalConsolidacion').modal('show');
}

// ============================================
// QUITAR PRODUCTO DE LA SELECCIÓN
// ============================================
function quitarDeSeleccion(productoId) {
    productosSeleccionados = productosSeleccionados.filter(id => id !== productoId);
    actualizarContador();
    
    const fila = document.getElementById(`fila-seleccion-${productoId}`);
    if (fila) {
        fila.style.display = 'none';
    }
    
    const btn = document.querySelector(`.btn-agregar-consolidacion[data-producto-id="${productoId}"]`);
    if (btn) {
        btn.dataset.seleccionado = 'false';
        btn.querySelector('i').className = 'bi bi-plus-circle';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-success');
        btn.title = 'Agregar a consolidación';
        btn.closest('tr').style.backgroundColor = '';
    }
    
    if (productosSeleccionados.length < 2) {
        Swal.fire({
            icon: 'info',
            title: 'Selecciona más productos',
            text: 'Debes seleccionar al menos 2 productos para consolidar',
            confirmButtonColor: '#7c3aed'
        });
        setTimeout(() => {
            $('#modalConsolidacion').modal('hide');
        }, 1500);
    } else {
        actualizarResumenModal();
    }
}

// ============================================
// ACTUALIZAR RESUMEN DEL MODAL
// ============================================
function actualizarResumenModal() {
    let totalExistencia = 0;
    let totalCosto = 0;
    let countConCosto = 0;
    let totalProductos = 0;
    
    document.querySelectorAll('#modalConsolidacionBody tbody tr:not([style*="display: none"])').forEach(row => {
        const existenciaText = row.querySelector('td:nth-child(5) .badge')?.textContent?.trim() || '0';
        const existencia = parseFloat(existenciaText.replace(/,/g, '')) || 0;
        const costoText = row.querySelector('td:nth-child(6)')?.textContent?.trim() || '$ 0.00';
        const costo = parseFloat(costoText.replace('$', '').replace(/,/g, '')) || 0;
        
        totalExistencia += existencia;
        if (costo > 0) {
            totalCosto += costo;
            countConCosto++;
        }
        totalProductos++;
    });
    
    const costoPromedio = countConCosto > 0 ? (totalCosto / countConCosto) : 0;
    
    const cards = document.querySelectorAll('#modalConsolidacionBody .col-md-4 .card .card-body');
    if (cards.length >= 3) {
        const h5s = cards.forEach((card, index) => {
            const h5 = card.querySelector('h5');
            if (h5) {
                if (index === 0) h5.textContent = totalProductos;
                else if (index === 1) h5.textContent = totalExistencia.toFixed(0);
                else if (index === 2) h5.textContent = '$' + costoPromedio.toFixed(2);
            }
        });
    }
    
    const select = document.getElementById('productoDestino');
    if (select) {
        const currentVal = select.value;
        select.innerHTML = '';
        document.querySelectorAll('#modalConsolidacionBody tbody tr:not([style*="display: none"])').forEach(row => {
            const id = row.id.replace('fila-seleccion-', '');
            const codigo = row.querySelector('td:nth-child(2)')?.textContent?.trim() || 'N/A';
            const descripcion = row.querySelector('td:nth-child(4)')?.textContent?.trim() || 'Sin descripción';
            const descCorta = descripcion.length > 30 ? descripcion.substring(0, 30) + '...' : descripcion;
            const option = document.createElement('option');
            option.value = id;
            option.textContent = `${codigo} - ${descCorta}`;
            if (id === currentVal) option.selected = true;
            select.appendChild(option);
        });
    }
}

// ============================================
// EJECUTAR CONSOLIDACIÓN
// ============================================
function ejecutarConsolidacion() {
    const productoDestino = document.getElementById('productoDestino')?.value;
    
    // ✅ Convertir a número para comparar correctamente
    const destinoId = parseInt(productoDestino);
    
    // ✅ Filtrar correctamente (comparar como números)
    const productosOrigen = productosSeleccionados.filter(id => parseInt(id) !== destinoId);
    
    if (!productoDestino || productosOrigen.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona un producto destino',
            text: 'Debes seleccionar un producto destino para la consolidación',
            confirmButtonColor: '#7c3aed'
        });
        return;
    }
    
    const select = document.getElementById('productoDestino');
    const destinoText = select?.selectedOptions[0]?.text || 'N/A';
    
    let totalExistenciaOrigen = 0;
    
    productosOrigen.forEach(id => {
        const row = document.querySelector(`tr[data-producto-id="${id}"]`);
        if (row) {
            const existenciaBadge = row.querySelector('td:nth-child(5) .badge');
            const existenciaText = existenciaBadge?.textContent?.trim() || 
                                  row.querySelector('td:nth-child(5)')?.textContent?.trim() || 
                                  '0';
            const existencia = parseFloat(existenciaText.replace(/,/g, '')) || 0;
            
            totalExistenciaOrigen += existencia;
        }
    });
    
    Swal.fire({
        title: 'Confirmar consolidación',
        html: `
            <div class="text-start">
                <p><strong>Producto destino:</strong> ${destinoText}</p>
                <p><strong>Productos a consolidar:</strong> ${productosOrigen.length}</p>
                <p><strong>Total existencias a mover:</strong> ${totalExistenciaOrigen.toFixed(0)} unidades</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, consolidar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#modalConsolidacionBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Consolidando productos...</p>
                </div>
            `);
            
            $.ajax({
                url: "{{ route('cpanel.consolidacion.guardar') }}",
                type: 'POST',
                data: {
                    producto_destino_id: productoDestino,
                    productos_origen: productosOrigen,
                    sucursal_id: sucursalActual,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Consolidación exitosa!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        $('#modalConsolidacion').modal('hide');
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    $('#modalConsolidacionBody').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error: ${xhr.responseJSON?.message || 'Error al consolidar productos'}
                        </div>
                    `);
                }
            });
        }
    });
}
</script>
@endsection

@push('styles')
<style>
    #tablaConsolidacion tbody tr:hover { background: #f8fafc; }
    
    .img-zoomable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid #e5e7eb;
    }
    
    .img-zoomable:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
        position: relative;
    }
    
    .btn-agregar-consolidacion {
        transition: all 0.3s ease;
    }
    
    .btn-agregar-consolidacion:hover {
        transform: scale(1.1);
    }
    
    #buscadorProductos::placeholder {
        color: #94a3b8;
    }
    
    #buscadorProductos:focus {
        background: #ffffff !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
    }
    
    .badge {
        font-weight: 500;
    }
    
    select.form-select {
        cursor: pointer;
    }
    
    select.form-select:hover {
        border-color: #8b5cf6;
    }
    
    .modal-header {
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    #modalConsolidacionBody .table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        z-index: 10;
    }
    
    #modalConsolidacionBody .table tbody tr:hover {
        background: #f8fafc;
    }
</style>
@endpush