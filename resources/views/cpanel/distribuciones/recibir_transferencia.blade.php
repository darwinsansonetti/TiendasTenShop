@extends('layout.layout_dashboard')

@section('title', 'Recibir Transferencia')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-arrow-down-circle text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Recibir Transferencia</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Recepción de mercancía desde sucursal</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.distribuciones.listado-transferencias') }}">Listado de Distribuciones y Transferencias</a>
                    </li>
                    <li class="breadcrumb-item active">Recibir</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD: INFORMACIÓN DE LA TRANSFERENCIA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Información de la Transferencia
                    </h6>
                    <span class="{{ $estatus['clase'] }} rounded-pill px-3 py-1 fw-semibold" style="font-size:0.78rem;">
                        {{ $estatus['texto'] }}
                    </span>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">N° Transferencia</p>
                        <p class="mb-0 fw-bold text-dark">{{ $transferencia->Numero }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Fecha</p>
                        <p class="mb-0 fw-semibold text-dark">{{ \Carbon\Carbon::parse($transferencia->Fecha)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal Origen</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $transferencia->sucursal_origen ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.7rem;letter-spacing:.05em;font-weight:600;">Sucursal Destino</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $transferencia->sucursal_destino ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD: PRODUCTOS --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Productos de la Transferencia
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        {{ $totalItems }} items | {{ $totalUnidades }} unidades
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaProductos">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">FOTO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CÓDIGO</th>
                                <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DESCRIPCIÓN</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. EMITIDA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">CANT. RECIBIDA</th>
                                <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.06em;">DISPONIBLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalles as $detalle)
                            @php
                                $imgSrc = FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $detalle->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                $disponible = ($detalle->CantidadEmitida ?? 0) - ($detalle->CantidadRecibida ?? 0);
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4 text-center">
                                    <img src="{{ $imgSrc }}" 
                                         alt="{{ $detalle->Codigo }}"
                                         class="img-thumbnail img-zoomable"
                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                         data-full-image="{{ $imgSrc }}"
                                         data-description="{{ $detalle->producto_nombre }}"
                                         onclick="zoomImagen(this)"
                                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td><code>{{ $detalle->Codigo }}</code></td>
                                <td>{{ $detalle->producto_nombre }}</td>
                                <td class="text-end">{{ number_format($detalle->CantidadEmitida ?? 0, 2) }}</td>
                                <td class="text-end">
                                    <input type="number" step="0.01" 
                                           class="form-control form-control-sm text-end cantidad-recibida"
                                           style="width: 100px; display: inline-block;"
                                           data-id="{{ $detalle->TransferenciaDetalleId }}"
                                           data-emitida="{{ $detalle->CantidadEmitida ?? 0 }}"
                                           value="{{ number_format($detalle->CantidadRecibida ?? 0, 2) }}"
                                           min="0"
                                           max="{{ $detalle->CantidadEmitida ?? 0 }}">
                                </td>
                                <td class="text-end fw-semibold disponible-cell">
                                    {{ number_format($disponible, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);opacity:0.5;">
                                        <i class="bi bi-box-seam text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay productos en esta transferencia</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="2" class="ps-4 fw-bold">TOTALES</td>
                                <td></td>
                                <td class="text-end fw-bold">{{ number_format($totalUnidades, 2) }}</td>
                                <td class="text-end fw-bold" id="totalRecibido">{{ number_format($totalRecibido, 2) }}</td>
                                <td class="text-end fw-bold" id="totalDisponible">{{ number_format($totalUnidades - $totalRecibido, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- BOTONES --}}
        {{-- ================================================ --}}
        <div class="row mt-3">
            <div class="col-12">
                <button class="btn btn-success" id="btnRecibirTransferencia">
                    <i class="bi bi-check-circle me-1"></i>Recibir / Finalizar
                </button>
                <a href="{{ route('cpanel.distribuciones.listado-transferencias') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ============================================
    // ACTUALIZAR TOTALES AL CAMBIAR CANTIDAD RECIBIDA
    // ============================================
    document.querySelectorAll('.cantidad-recibida').forEach(input => {
        input.addEventListener('change', function() {
            const emitida = parseFloat(this.dataset.emitida) || 0;
            const recibida = parseFloat(this.value) || 0;
            const disponible = emitida - recibida;
            
            // Actualizar disponible de la fila
            const row = this.closest('tr');
            const disponibleCell = row.querySelector('.disponible-cell');
            if (disponibleCell) {
                disponibleCell.textContent = disponible.toFixed(2);
            }
            
            // Recalcular totales
            recalcularTotales();
        });
    });

    function recalcularTotales() {
        let totalEmitida = 0;
        let totalRecibida = 0;
        
        document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
            const emitida = parseFloat(row.querySelector('.cantidad-recibida')?.dataset.emitida) || 0;
            const recibida = parseFloat(row.querySelector('.cantidad-recibida')?.value) || 0;
            totalEmitida += emitida;
            totalRecibida += recibida;
        });
        
        document.getElementById('totalRecibido').textContent = totalRecibida.toFixed(2);
        document.getElementById('totalDisponible').textContent = (totalEmitida - totalRecibida).toFixed(2);
    }

    // ============================================
    // ZOOM DE IMAGEN
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
    // RECIBIR / FINALIZAR TRANSFERENCIA
    // ============================================
    document.getElementById('btnRecibirTransferencia')?.addEventListener('click', function() {
        const detalles = [];
        let hayProductos = false;
        
        document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
            const input = row.querySelector('.cantidad-recibida');
            if (input) {
                const recibida = parseFloat(input.value) || 0;
                detalles.push({
                    id: input.dataset.id,
                    cantidad_recibida: recibida
                });
                if (recibida > 0) {
                    hayProductos = true;
                }
            }
        });
        
        if (!hayProductos) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'Debe recibir al menos un producto',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        Swal.fire({
            title: '¿Recibir transferencia?',
            text: 'Una vez recibida, no se podrán modificar las cantidades',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, recibir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Finalizando recepción',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const url = '{{ route("cpanel.transferencias.finalizar-recibir", $transferencia->TransferenciaId) }}';
                
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
                            title: '¡Transferencia recibida!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("cpanel.distribuciones.listado-transferencias") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al recibir la transferencia'
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
</script>
@endsection