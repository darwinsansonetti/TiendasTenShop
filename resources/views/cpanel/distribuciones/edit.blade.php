@extends('layout.layout_dashboard')

@section('title', 'Editar Distribución')

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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Editar Distribución</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Agregue productos a la distribución</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.distribuciones.index') }}">Distribuciones</a></li>
                    <li class="breadcrumb-item active">Editar</li>
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
                        <i class="bi bi-info-circle me-2"></i>Distribución: {{ $transferencia->Numero }}
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        Paso 2: Productos
                    </span>
                </div>
            </div>
            <div class="card-body">

                <!-- Información de la distribución -->
                <div class="row">
                    <div class="col-md-3">
                        <strong>N° Distribución:</strong> {{ $transferencia->Numero }}
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($transferencia->Fecha)->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Origen:</strong> {{ $transferencia->sucursal_origen }}
                    </div>
                    <div class="col-md-3">
                        <strong>Destino:</strong> {{ $transferencia->sucursales_destino_nombres ?? 'N/A' }}
                    </div>
                </div>

                <hr>

                <!-- Productos disponibles -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Productos Disponibles en Origen</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaProductos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th class="text-end">Existencia</th>
                                        <th class="text-end">Cantidad a Enviar</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productos as $producto)
                                    <tr>
                                        <td><code>{{ $producto->Codigo }}</code></td>
                                        <td>{{ $producto->Descripcion }}</td>
                                        <td class="text-end">{{ number_format($producto->Existencia, 2) }}</td>
                                        <td class="text-end">
                                            <input type="number" step="0.01" class="form-control form-control-sm text-end"
                                                   style="width: 120px; display: inline-block;"
                                                   value="0">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
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

                <!-- Productos ya asignados -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Productos Asignados a la Distribución</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($detalles ?? [] as $detalle)
                                    <tr>
                                        <td><code>{{ $detalle->Codigo }}</code></td>
                                        <td>{{ $detalle->producto_nombre }}</td>
                                        <td class="text-end">{{ number_format($detalle->CantidadEmitida ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i><br>
                                            No hay productos asignados
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-success" id="btnFinalizarDistribucion">
                            <i class="bi bi-check-circle me-1"></i>Finalizar Distribución
                        </button>
                        <a href="{{ route('cpanel.distribuciones.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
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
            pageLength: 10,
            order: [[0, 'asc']]
        });
    });

    // Botón Finalizar Distribución
    document.getElementById('btnFinalizarDistribucion')?.addEventListener('click', function() {
        Swal.fire({
            title: '¿Finalizar distribución?',
            text: 'Una vez finalizada, no se podrán modificar los productos',
            icon: 'warning',
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
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => {
                            window.location.href = '{{ route("cpanel.distribuciones.index") }}';
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error al finalizar la distribución', 'error');
                });
            }
        });
    });
</script>
@endsection