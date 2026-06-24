@extends('layout.layout_dashboard')

@section('title', 'Detalle de Transferencia')

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
                         style="width:36px;height:36px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                        <i class="bi bi-info-circle text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Detalle de Transferencia</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Información de la transferencia</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="#">Listado de Transferencias</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-info-circle me-2"></i>Transferencia: {{ $transferencia->Numero }}
                    </h6>
                    <span class="{{ $estatus['clase'] }} rounded-pill px-3 py-1 fw-semibold" style="font-size:0.78rem;">
                        {{ $estatus['texto'] }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Información -->
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th style="width:150px;">N° Transferencia</th><td><strong>{{ $transferencia->Numero }}</strong></td></tr>
                            <tr><th>Fecha</th><td>{{ \Carbon\Carbon::parse($transferencia->Fecha)->format('d/m/Y H:i') }}</td></tr>
                            <tr><th>Sucursal Origen</th><td>{{ $transferencia->sucursal_origen ?? 'N/A' }}</td></tr>
                            <tr><th>Sucursal Destino</th><td>{{ $transferencia->sucursal_destino ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th style="width:150px;">Items</th><td><strong>{{ $totalItems }}</strong></td></tr>
                            <tr><th>Unidades Enviadas</th><td>{{ number_format($totalUnidades, 0) }}</td></tr>
                            <tr><th>Unidades Recibidas</th><td>{{ number_format($totalRecibido, 0) }}</td></tr>
                            <tr><th>Saldo</th><td class="fw-bold text-success">${{ number_format($transferencia->Saldo ?? 0, 2) }}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Productos -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tablaProductos">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px;">Foto</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-end">Cant. Emitida</th>
                                <th class="text-end">Cant. Recibida</th>
                                <th class="text-end">Disponible</th>
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
                            <tr>
                                <td class="text-center">
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
                                <td class="text-end">{{ number_format($detalle->CantidadRecibida ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($disponible, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                    No hay productos en esta transferencia
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @if(in_array($transferencia->Estatus, [1, 3, 4, 5]))
                    <a href="{{ route('cpanel.transferencias.recibir-productos', $transferencia->TransferenciaId) }}"
                    class="btn btn-warning">
                        <i class="bi bi-arrow-down-circle me-1"></i>Recibir
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tablaProductos').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[1, 'asc']],
            pageLength: 10
        });
    });

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
</script>
@endsection