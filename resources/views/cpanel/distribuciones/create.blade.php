@extends('layout.layout_dashboard')

@section('title', 'Crear Distribución')

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
                        <i class="bi bi-diagram-3 text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            {{ $transferencia->TransferenciaId > 0 ? 'Editar' : 'Nueva' }} Distribución
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $transferencia->TransferenciaId > 0 ? 'Editar distribución existente' : 'Crear una nueva distribución' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.distribucion.distribuciones') }}">Distribuciones</a></li>
                    <li class="breadcrumb-item active">{{ $transferencia->TransferenciaId > 0 ? 'Editar' : 'Nuevo' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Distribución de Mercancía
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $transferencia->TransferenciaId > 0 ? 'Edición' : 'Nuevo' }}
                    </span>
                </div>
            </div>
            <div class="card-body">

                {{-- ================================================ --}}
                {{-- PASO 1: SUCURSALES --}}
                {{-- ================================================ --}}
                <div class="accordion" id="accordionDistribucion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseSucursales" aria-expanded="true">
                                <i class="bi bi-shop me-2"></i>Sucursales
                                <small class="ms-2 text-muted">Seleccione las sucursales destino</small>
                            </button>
                        </h2>
                        <div id="collapseSucursales" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="tablaSucursales">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Dirección</th>
                                                        <th class="text-center" style="width:80px;">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sucursalesDestino as $sucursal)
                                                    <tr id="rowSucursal{{ $sucursal->ID }}">
                                                        <td>{{ $sucursal->Nombre }}</td>
                                                        <td>{{ $sucursal->Direccion ?? 'N/A' }}</td>
                                                        <td class="text-center">
                                                            @php
                                                                $esEdicion = $transferencia->TransferenciaId > 0;
                                                                $seleccionada = in_array($sucursal->ID, $transferencia->sucursales_destino_seleccionadas ?? []);
                                                                $btnClase = $seleccionada ? 'btn-success' : 'btn-primary';
                                                                $btnIcono = $seleccionada ? 'check' : 'plus';
                                                            @endphp
                                                            <button class="btn btn-sm {{ $btnClase }} btn-circle"
                                                                    onclick="asociarSucursal({{ $sucursal->ID }}, this)"
                                                                    title="{{ $seleccionada ? 'Quitar' : 'Agregar' }} sucursal"
                                                                    {{ $esEdicion ? 'disabled' : '' }}>
                                                                <i class="bi bi-{{ $btnIcono }}"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <form method="POST" action="{{ $transferencia->TransferenciaId > 0 ? route('cpanel.distribuciones.update', $transferencia->TransferenciaId) : route('cpanel.distribuciones.store') }}">
                                            @csrf
                                            @if($transferencia->TransferenciaId > 0)
                                                @method('PUT')
                                            @endif
                                            
                                            @php $esEdicion = $transferencia->TransferenciaId > 0; @endphp
                                            
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Origen:</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" value="{{ $transferencia->sucursal_origen ?? $sucursalAlmacen->Nombre }}" disabled>
                                                    <input type="hidden" name="sucursal_origen_id" value="{{ $transferencia->SucursalOrigenId ?? $sucursalAlmacen->ID }}">
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Fecha:</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="date" name="fecha" class="form-control"
                                                        value="{{ old('fecha', $transferencia->Fecha) }}" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Estatus:</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" value="NUEVO" disabled>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <label class="fw-bold">Observación:</label>
                                                </div>
                                                <div class="col-8">
                                                    <textarea name="observacion" class="form-control" rows="2"
                                                            placeholder="Escriba una observación...">{{ old('observacion', $transferencia->Observacion ?? '') }}</textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary" id="btnNuevaDistribucion">
                                                        <i class="bi bi-{{ $esEdicion ? 'save' : 'plus-circle' }} me-1"></i>
                                                        {{ $esEdicion ? 'Actualizar Distribución' : 'Nueva Distribución' }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- PASO 2: PRODUCTOS --}}
                    {{-- ================================================ --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseProductos" aria-expanded="false">
                                <i class="bi bi-box-seam me-2"></i>Productos
                                <small class="ms-2 text-muted">Cargue los productos de la distribución</small>
                            </button>
                        </h2>
                        <div id="collapseProductos" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                
                                {{-- ========================================== --}}
                                {{-- CARGAR DESDE EXCEL --}}
                                {{-- ========================================== --}}
                                <div class="card mb-3 border-info">
                                    <div class="card-header bg-info text-white">
                                        <strong><i class="bi bi-file-excel me-2"></i>Cargar Productos desde Excel</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Subir archivo de detalle</label>
                                                <div class="input-group">
                                                    <input type="file" name="transferencia_excel" id="transferencia_excel"
                                                        class="form-control" accept=".xlsx,.xls">
                                                    <button type="button" class="btn btn-primary" id="btnUploadExcel" disabled>
                                                        <i class="bi bi-upload me-1"></i>Cargar Transferencia
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-success" id="btnDescargarPlantilla">
                                                        <i class="bi bi-download me-1"></i>Descargar archivo detalle
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================== --}}
                                {{-- TABLA DE PRODUCTOS CON SUCURSALES DESTINO --}}
                                {{-- ========================================== --}}
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="bi bi-table me-2"></i>Listado de productos
                                                <span class="badge bg-light text-dark ms-2">{{ count($productos ?? []) }} productos</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-bordered table-striped mb-0" id="tablaProductosDistribucion">
                                                <thead class="table-dark sticky-top">
                                                    <tr>
                                                        <th style="width: 60px;">Foto</th>
                                                        <th>Código</th>
                                                        <th>Descripción</th>
                                                        <th class="text-end" style="width: 80px;">Exist.</th>
                                                        @php
                                                            // ✅ Obtener sucursales destino (desde BD o sesión)
                                                            $sucursalesDestinoList = collect();
                                                            
                                                            // Primero intentar desde la base de datos
                                                            if ($transferencia->TransferenciaId > 0) {
                                                                $sucursalesDestinoList = DB::connection('sqlsrv')
                                                                    ->table('TransferenciasSucursalesTmp as ts')
                                                                    ->leftJoin('Sucursales as s', 'ts.SucursalId', '=', 's.ID')
                                                                    ->where('ts.TransferenciaId', $transferencia->TransferenciaId)
                                                                    ->select('s.ID', 's.Nombre')
                                                                    ->orderBy('s.Nombre')
                                                                    ->get();
                                                            }
                                                            
                                                            // Si no hay en BD, usar las de sesión
                                                            if ($sucursalesDestinoList->isEmpty()) {
                                                                $sucursalesSeleccionadas = $transferencia->sucursales_destino_seleccionadas ?? [];
                                                                if (!empty($sucursalesSeleccionadas)) {
                                                                    $sucursalesDestinoList = DB::connection('sqlsrv')
                                                                        ->table('Sucursales')
                                                                        ->whereIn('ID', $sucursalesSeleccionadas)
                                                                        ->orderBy('Nombre')
                                                                        ->get();
                                                                }
                                                            }
                                                        @endphp
                                                        @foreach($sucursalesDestinoList as $sucursal)
                                                            <th class="text-center" style="min-width: 100px; font-size: 0.75rem;">
                                                                {{ $sucursal->Nombre }}
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($productos ?? [] as $producto)
                                                    @php
                                                        // ✅ Obtener la URL de la imagen usando FileHelper
                                                        $imgSrc = FileHelper::getOrDownloadFile(
                                                            'images/items/thumbs/',
                                                            $producto->UrlFoto ?? '',
                                                            'assets/img/adminlte/img/produc_default.jfif'
                                                        );
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <img src="{{ $imgSrc }}" 
                                                                loading="lazy" 
                                                                alt="{{ $producto->Codigo }}"
                                                                class="img-thumbnail img-zoomable"
                                                                style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                                                data-full-image="{{ $imgSrc }}"
                                                                data-description="{{ $producto->Descripcion }}"
                                                                onclick="zoomImagen(this)"
                                                                onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                                        </td>
                                                        <td><code>{{ $producto->Codigo }}</code></td>
                                                        <td>{{ $producto->Descripcion }}</td>
                                                        <td class="text-end">{{ number_format($producto->Existencia ?? 0, 0) }}</td>
                                                        @if($sucursalesDestinoList->isEmpty())
                                                            <td colspan="1" class="text-center text-muted">Sin sucursales destino</td>
                                                        @else
                                                            @foreach($sucursalesDestinoList as $sucursal)
                                                                <td>
                                                                    <input type="number" step="0.01" 
                                                                        class="form-control form-control-sm text-end cantidad-sucursal"
                                                                        name="cantidades[{{ $producto->ID }}][{{ $sucursal->ID }}]"
                                                                        data-producto-codigo="{{ $producto->Codigo }}"
                                                                        data-producto="{{ $producto->ID }}"
                                                                        data-sucursal="{{ $sucursal->ID }}"
                                                                        value="0"
                                                                        min="0"
                                                                        max="{{ $producto->Existencia ?? 0 }}">
                                                                </td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="{{ 5 + max(count($sucursalesDestinoList), 1) }}" class="text-center py-4">
                                                            <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                                            No hay productos disponibles en esta sucursal
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- PASO 3: TOTALES --}}
                    {{-- ================================================ --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTotales" aria-expanded="false">
                                <i class="bi bi-calculator me-2"></i>Totales
                            </button>
                        </h2>
                        <div id="collapseTotales" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4>Resumen de la Distribución</h4>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Items:</strong>
                                                        <h3 id="totalItems">0</h3>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Unidades:</strong>
                                                        <h3 id="totalUnidades">0</h3>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <button class="btn btn-success w-100" id="btnFinalizarDistribucion">
                                                            <i class="bi bi-check-circle me-1"></i>Finalizar Distribución
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- ✅ Solo jQuery (sin DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#transferencia_excel').on('change', function() {
            $('#btnUploadExcel').prop('disabled', this.value === '');
        });
    });

    // ============================================
    // ZOOM DE IMAGEN (igual que en otras vistas)
    // ============================================
    function zoomImagen(element) {
        const imgSrc = element.getAttribute('data-full-image') || element.src;
        const descripcion = element.getAttribute('data-description') || 'Producto';
        
        Swal.fire({
            title: descripcion,
            imageUrl: imgSrc,
            imageWidth: 400,
            imageHeight: 400,
            imageAlt: descripcion,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'rounded-3 shadow-lg'
            }
        });
    }

    // ============================================
    // ACTUALIZAR TOTALES (Items y Unidades)
    // ============================================
    function actualizarTotales() {
        let totalItems = 0;
        let totalUnidades = 0;
        
        // Recorrer todas las filas de productos
        document.querySelectorAll('#tablaProductosDistribucion tbody tr').forEach(row => {
            let tieneCantidad = false;
            let sumaProducto = 0;
            
            // Recorrer todos los inputs de cantidad por sucursal en esta fila
            row.querySelectorAll('.cantidad-sucursal').forEach(input => {
                const cantidad = parseFloat(input.value) || 0;
                if (cantidad > 0) {
                    tieneCantidad = true;
                    sumaProducto += cantidad;
                }
            });
            
            if (tieneCantidad) {
                totalItems++;
                totalUnidades += sumaProducto;
            }
        });
        
        // Actualizar los elementos HTML
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalUnidades').textContent = totalUnidades;
    }

    // ============================================
    // EVENTOS PARA ACTUALIZAR TOTALES
    // ============================================
    // Cuando cambia cualquier input de cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-sucursal')) {
            actualizarTotales();
        }
    });

    // Cuando se carga el Excel y se llenan los inputs
    document.addEventListener('DOMContentLoaded', function() {
        actualizarTotales();
    });

    // ============================================
    // ASOCIAR SUCURSAL DESTINO
    // ============================================
    function asociarSucursal(sucursalId, button) {
        const icono = button.querySelector('i');
        const esSeleccionada = icono.classList.contains('bi-check');

        const url = esSeleccionada
            ? '{{ route("cpanel.distribuciones.remover-sucursal") }}'
            : '{{ route("cpanel.distribuciones.asociar-sucursal") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ sucursal_id: sucursalId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (esSeleccionada) {
                    icono.className = 'bi bi-plus';
                    button.className = 'btn btn-sm btn-primary btn-circle';
                } else {
                    icono.className = 'bi bi-check';
                    button.className = 'btn btn-sm btn-success btn-circle';
                }
                Swal.fire({
                    icon: 'success',
                    title: esSeleccionada ? 'Sucursal eliminada' : 'Sucursal asociada',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar la solicitud', 'error');
        });
    }

    // ============================================
    // SUBIR EXCEL
    // ============================================
    document.getElementById('btnUploadExcel')?.addEventListener('click', function() {
        const fileInput = document.getElementById('transferencia_excel');
        const file = fileInput.files[0];

        if (!file) {
            Swal.fire('Error', 'Seleccione un archivo Excel', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', '{{ csrf_token() }}');

        Swal.fire({
            title: 'Cargando...',
            text: 'Procesando archivo Excel',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const url = '{{ route("cpanel.distribuciones.upload-excel") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ Llenar los inputs de la tabla con las cantidades del Excel
                data.productos.forEach(producto => {
                    // Buscar el input por código de producto
                    const inputs = document.querySelectorAll(`.cantidad-sucursal[data-producto-codigo="${producto.codigo}"]`);
                    inputs.forEach(input => {
                        const sucursalId = input.dataset.sucursal;
                        if (producto.cantidades[sucursalId]) {
                            input.value = producto.cantidades[sucursalId];
                        }
                    });
                });

                actualizarTotales();
                
                Swal.fire('Éxito', data.message, 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar el archivo', 'error');
        });
    });

    // ============================================
    // AGREGAR PRODUCTO INDIVIDUAL
    // ============================================
    document.querySelectorAll('.agregar-producto').forEach(button => {
        button.addEventListener('click', function() {
            const productoId = this.dataset.id;
            const codigo = this.dataset.codigo;
            const nombre = this.dataset.nombre;
            const row = this.closest('tr');
            const cantidadInput = row.querySelector('.cantidad-producto');
            const cantidad = parseFloat(cantidadInput.value) || 0;
            
            if (cantidad <= 0) {
                Swal.fire('Error', 'La cantidad debe ser mayor a 0', 'warning');
                return;
            }
            
            // Verificar que no exceda la existencia
            const existencia = parseFloat(cantidadInput.dataset.existencia) || 0;
            if (cantidad > existencia) {
                Swal.fire('Error', `La cantidad (${cantidad}) excede la existencia (${existencia})`, 'warning');
                return;
            }
            
            agregarProductoAsignado(productoId, codigo, nombre, cantidad);
        });
    });

    // ============================================
    // AGREGAR PRODUCTOS SELECCIONADOS
    // ============================================
    document.getElementById('btnAgregarProductosSeleccionados')?.addEventListener('click', function() {
        let productosAgregados = 0;
        document.querySelectorAll('.select-producto:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const cantidadInput = row.querySelector('.cantidad-producto');
            const cantidad = parseFloat(cantidadInput.value) || 0;
            
            if (cantidad > 0) {
                const productoId = checkbox.value;
                const codigo = row.querySelector('code')?.innerText || '';
                const nombre = row.querySelector('td:nth-child(3)')?.innerText || '';
                
                agregarProductoAsignado(productoId, codigo, nombre, cantidad);
                checkbox.checked = false;
                cantidadInput.value = 0;
                productosAgregados++;
            }
        });
        
        if (productosAgregados === 0) {
            Swal.fire('Info', 'Seleccione productos con cantidad mayor a 0', 'info');
        }
    });

    // ============================================
    // AGREGAR PRODUCTO ASIGNADO
    // ============================================
    function agregarProductoAsignado(productoId, codigo, nombre, cantidad) {
        // Verificar si ya existe
        const existente = document.querySelector(`#tablaProductosBody tr[data-id="${productoId}"]`);
        if (existente) {
            Swal.fire('Info', 'El producto ya está asignado', 'info');
            return;
        }
        
        const tbody = document.getElementById('tablaProductosBody');
        const row = document.createElement('tr');
        row.dataset.id = productoId;
        row.innerHTML = `
            <td><code>${codigo}</code></td>
            <td>${nombre}</td>
            <td class="text-end">${cantidad.toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger eliminar-producto-asignado" data-id="${productoId}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        // Eliminar mensaje "No hay productos asignados" si existe
        const emptyRow = tbody.querySelector('tr:only-child td[colspan="4"]');
        if (emptyRow) {
            tbody.innerHTML = '';
        }
        
        tbody.appendChild(row);
        
        // Actualizar contador de productos asignados
        actualizarContadorProductos();
    }

    // ============================================
    // ELIMINAR PRODUCTO ASIGNADO
    // ============================================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.eliminar-producto-asignado')) {
            const button = e.target.closest('.eliminar-producto-asignado');
            const row = button.closest('tr');
            
            Swal.fire({
                title: '¿Eliminar producto?',
                text: 'Este producto será removido de la distribución',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove();
                    actualizarContadorProductos();
                }
            });
        }
    });

    // ============================================
    // SELECCIONAR TODOS LOS PRODUCTOS
    // ============================================
    document.getElementById('seleccionarTodosProductos')?.addEventListener('change', function() {
        document.querySelectorAll('.select-producto').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // ============================================
    // LIMPIAR SELECCIÓN
    // ============================================
    document.getElementById('btnLimpiarSeleccion')?.addEventListener('click', function() {
        document.querySelectorAll('.select-producto').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.cantidad-producto').forEach(input => {
            input.value = 0;
        });
    });

    // ============================================
    // ACTUALIZAR CONTADOR DE PRODUCTOS
    // ============================================
    function actualizarContadorProductos() {
        const tbody = document.getElementById('tablaProductosBody');
        const count = tbody.querySelectorAll('tr:not(:only-child)').length;
        const badge = document.querySelector('.card-header.bg-info .badge');
        if (badge) {
            badge.textContent = count + ' productos';
        }
    }

    // ============================================
    // VALIDAR QUE LA CANTIDAD NO EXCEDA LA EXISTENCIA
    // ============================================
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-sucursal')) {
            const max = parseFloat(e.target.getAttribute('max')) || 0;
            const value = parseFloat(e.target.value) || 0;
            
            if (value > max) {
                e.target.value = max;
                Swal.fire({
                    icon: 'warning',
                    title: 'Cantidad excede existencia',
                    text: `La cantidad máxima disponible es ${max}`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });

    // ============================================
    // CALCULAR TOTALES POR SUCURSAL
    // ============================================
    function calcularTotalesPorSucursal() {
        const sucursales = {};
        
        document.querySelectorAll('.cantidad-sucursal').forEach(input => {
            const sucursalId = input.dataset.sucursal;
            const cantidad = parseFloat(input.value) || 0;
            
            if (!sucursales[sucursalId]) {
                sucursales[sucursalId] = 0;
            }
            sucursales[sucursalId] += cantidad;
        });
        
        // Actualizar totales en la vista (opcional)
        console.log('Totales por sucursal:', sucursales);
    }

    // ============================================
    // ACTUALIZAR DISTRIBUCIÓN (Guardar cantidades por sucursal)
    // ============================================
    document.getElementById('btnActualizarDistribucion')?.addEventListener('click', function() {
        const cantidades = [];
        let totalProductos = 0;
        
        document.querySelectorAll('.cantidad-sucursal').forEach(input => {
            const cantidad = parseFloat(input.value) || 0;
            if (cantidad > 0) {
                cantidades.push({
                    producto_id: input.dataset.producto,
                    sucursal_id: input.dataset.sucursal,
                    cantidad: cantidad
                });
                totalProductos++;
            }
        });
        
        if (totalProductos === 0) {
            Swal.fire('Info', 'No hay productos con cantidad asignada', 'info');
            return;
        }
        
        Swal.fire({
            title: 'Guardando...',
            text: 'Actualizando distribución',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        const url = '#';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cantidades: cantidades })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success');
                // Actualizar totales
                calcularTotalesPorSucursal();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al actualizar la distribución', 'error');
        });
    });

    // ============================================
    // FINALIZAR DISTRIBUCIÓN
    // ============================================
    document.getElementById('btnFinalizarDistribucion')?.addEventListener('click', function() {
        // 1. Recoger todas las cantidades de los inputs
        const detalles = [];
        const errores = [];
        let hayProductos = false;
        let hayExceso = false;
        
        document.querySelectorAll('#tablaProductosDistribucion tbody tr').forEach(row => {
            const productoId = row.querySelector('.cantidad-sucursal')?.dataset.producto;
            const codigo = row.querySelector('.cantidad-sucursal')?.dataset.productoCodigo;
            const existencia = parseFloat(row.querySelector('.cantidad-sucursal')?.getAttribute('max')) || 0;
            
            if (!productoId) return;
            
            const cantidades = {};
            let tieneCantidad = false;
            let totalAsignado = 0;
            
            row.querySelectorAll('.cantidad-sucursal').forEach(input => {
                const cantidad = parseFloat(input.value) || 0;
                if (cantidad > 0) {
                    const sucursalId = input.dataset.sucursal;
                    cantidades[sucursalId] = cantidad;
                    tieneCantidad = true;
                    totalAsignado += cantidad;
                }
            });
            
            if (tieneCantidad) {
                hayProductos = true;
                
                // ✅ Validar que no exceda la existencia disponible
                if (totalAsignado > existencia) {
                    hayExceso = true;
                    errores.push({
                        codigo: codigo || 'N/A',
                        existencia: existencia,
                        asignado: totalAsignado,
                        exceso: totalAsignado - existencia
                    });
                }
                
                detalles.push({
                    producto_id: productoId,
                    codigo: codigo,
                    existencia: existencia,
                    total_asignado: totalAsignado,
                    cantidades: cantidades
                });
            }
        });
        
        // 2. Si no hay productos, mostrar mensaje
        if (!hayProductos) {
            Swal.fire({
                icon: 'info',
                title: 'Sin productos',
                text: 'No hay productos con cantidades asignadas',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // 3. ✅ Si hay productos que exceden la existencia, mostrar error detallado
        if (hayExceso) {
            let mensaje = 'Los siguientes productos exceden la cantidad disponible:\n\n';
            errores.forEach(item => {
                mensaje += `• ${item.codigo}: disponible ${item.existencia}, asignado ${item.asignado} (exceso de ${item.exceso})\n`;
            });
            mensaje += '\nPor favor, revise las cantidades antes de finalizar.';
            
            Swal.fire({
                icon: 'warning',
                title: 'Cantidades excedidas',
                text: mensaje,
                confirmButtonText: 'Revisar',
                confirmButtonColor: '#d97706'
            });
            return;
        }
        
        // 4. Si todo está correcto, mostrar confirmación
        Swal.fire({
            title: '¿Finalizar distribución?',
            text: `Se enviarán ${detalles.length} productos a las sucursales destino`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Finalizando distribución',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const url = '{{ route("cpanel.distribuciones.finalizar", $transferencia->TransferenciaId) }}';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ detalles: detalles })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Distribución finalizada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("cpanel.distribucion.distribuciones") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al finalizar la distribución'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                });
            }
        });
    });

    // ============================================
    // DESCARGAR PLANTILLA EXCEL
    // ============================================
    document.getElementById('btnDescargarPlantilla')?.addEventListener('click', function() {
        // Mostrar loading
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Preparando plantilla de distribución',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Redirigir a la ruta de descarga
        window.location.href = '{{ route("cpanel.distribuciones.download-details") }}';
        
        // Cerrar el SweetAlert después de un momento
        setTimeout(() => {
            Swal.close();
        }, 2000);
    });
</script>
@endsection