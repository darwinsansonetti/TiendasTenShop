@extends('layout.layout_dashboard')

@section('title', 'Auditoría de Conteo')

@php
    use App\Helpers\FileHelper;

    $tipoLabels = [
        'diferencias' => 'Con Diferencias',
        'exactos' => 'Exactos',
        'novendible' => 'No Vendibles'
    ];

    $tipoColors = [
        'diferencias' => 'orange',
        'exactos' => 'green',
        'novendible' => 'red'
    ];
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-clipboard-check text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">
                            Auditoría de Conteo
                        </h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">
                            {{ $tipoLabels[$tipo] ?? 'Auditoría' }} - Inventario: {{ $inventario->Codigo ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.inventario.listado') }}">Inventarios</a></li>
                    <li class="breadcrumb-item active">Auditoría</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            {{-- Header --}}
            <div class="card-header border-0 py-3"
                style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="mb-0 fw-bold text-white" style="font-size:1.15rem;">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Auditoría: {{ $tipoLabels[$tipo] ?? 'Auditoría' }}
                            <small class="d-block text-white-50" style="font-size:0.7rem;font-weight:400;">
                                {{ $inventario->Codigo ?? 'N/A' }} - {{ $inventario->Descripcion ?? '' }}
                            </small>
                        </h2>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        {{-- Botón Finalizar (solo si el estatus es EnConteo o EnAuditoria) --}}
                        @if(isset($inventario->Estatus) && ($inventario->Estatus == 1 || $inventario->Estatus == 2))
                            <a href="#" 
                            class="btn btn-sm text-white" 
                            style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;"
                            onclick="confirmarFinalizar(event, {{ $inventario->InventarioId ?? 0 }})">
                                <i class="bi bi-stop-circle me-1"></i> Finalizar
                            </a>
                        @endif

                        {{-- Botón Listado --}}
                        <a href="{{ route('cpanel.inventario.listado') }}" 
                        class="btn btn-sm text-white" 
                        style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);border-radius:6px;padding:0.25rem 0.7rem;">
                            <i class="bi bi-list me-1"></i> LISTADO
                        </a>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="card-body p-4">
                {{-- Resumen --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                            <div class="card-body p-3">
                                <small class="text-muted d-block">Total Productos</small>
                                <h4 class="mb-0">{{ $totalProductos }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                            <div class="card-body p-3">
                                <small class="text-muted d-block">Total Existencia</small>
                                <h4 class="mb-0">{{ number_format($totalExistencia, 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                            <div class="card-body p-3">
                                <small class="text-muted d-block">Total Contado</small>
                                <h4 class="mb-0">{{ number_format($totalContado, 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                            <div class="card-body p-3">
                                <small class="text-muted d-block">Diferencia</small>
                                @php
                                    $diferencia = $totalContado - $totalExistencia;
                                    $color = $diferencia < 0 ? '#ef4444' : ($diferencia > 0 ? '#22c55e' : '#000');
                                @endphp
                                <h4 class="mb-0" style="color:{{ $color }};">
                                    {{ $diferencia >= 0 ? '+' : '' }}{{ number_format($diferencia, 0) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de productos con inputs --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tablaAuditoria">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th style="width:60px;">Foto</th>
                                <th>Código</th>
                                <th>Referencia</th>
                                <th>Costo</th>
                                <th>Existencia</th>
                                <th style="min-width:80px;">Contado</th>
                                <th style="min-width:70px;">Pie Solo</th>
                                <th style="min-width:70px;">Pie Inv.</th>
                                <th style="min-width:70px;">Dañado</th>
                                <th style="min-width:80px;">Total</th>
                                <th style="min-width:80px;">Diferencia</th>
                                <th style="min-width:100px;">Acción</th>
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
                                $imgFull = FileHelper::getOrDownloadFile(
                                    'images/items/',
                                    $detalle->UrlFoto ?? '',
                                    'assets/img/adminlte/img/produc_default.jfif'
                                );
                                $diferencia = ($detalle->CantidadContada ?? 0) - ($detalle->Existencia ?? 0);
                                $color = $diferencia < 0 ? '#ef4444' : ($diferencia > 0 ? '#22c55e' : '#000');
                                $texto = $diferencia < 0 ? 'Falta ' . abs($diferencia) : ($diferencia > 0 ? 'Sobra ' . $diferencia : 'Exacto');
                                $totalUnidades = ($detalle->CantidadContada ?? 0) - ($detalle->CantidadPieSolo ?? 0) - ($detalle->CantidadPieInvertido ?? 0) - ($detalle->CantidadPiezaDanada ?? 0);
                                $totalCosto = $totalUnidades * ($detalle->CostoDivisa ?? 0);
                                $productoId = $detalle->ProductoId;
                                $detalleId = $detalle->InventarioDetalleId;
                            @endphp
                            <tr id="fila-{{ $detalleId }}">
                                <td class="text-center">
                                    <img src="{{ $imgSrc }}" 
                                         loading="lazy" 
                                         alt="{{ $detalle->Codigo }}"
                                         class="img-thumbnail img-zoomable"
                                         style="width:40px;height:40px;object-fit:cover;cursor:pointer;"
                                         data-full-image="{{ $imgFull }}"
                                         data-description="{{ $detalle->Codigo }} - {{ $detalle->Referencia }}"
                                         onclick="zoomImagen(this)"
                                         onerror="this.src='{{ asset('assets/img/adminlte/img/produc_default.jfif') }}'">
                                </td>
                                <td><strong>{{ $detalle->Codigo ?? 'N/A' }}</strong></td>
                                <td>{{ $detalle->Referencia ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($detalle->CostoDivisa ?? 0, 2) }}</td>
                                <td class="text-center" id="existencia_{{ $detalleId }}">{{ $detalle->Existencia ?? 0 }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm input-auditoria" 
                                           id="contado_{{ $detalleId }}"
                                           style="width:75px;display:inline-block;font-size:0.8rem;text-align:center;"
                                           value="{{ $detalle->CantidadContada ?? 0 }}" min="0"
                                           onchange="calcularTotalesFila({{ $detalleId }})">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm input-auditoria" 
                                           id="pie_solo_{{ $detalleId }}"
                                           style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                           value="{{ $detalle->CantidadPieSolo ?? 0 }}" min="0"
                                           onchange="calcularTotalesFila({{ $detalleId }})">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm input-auditoria" 
                                           id="pie_invertido_{{ $detalleId }}"
                                           style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                           value="{{ $detalle->CantidadPieInvertido ?? 0 }}" min="0"
                                           onchange="calcularTotalesFila({{ $detalleId }})">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm input-auditoria" 
                                           id="danado_{{ $detalleId }}"
                                           style="width:65px;display:inline-block;font-size:0.8rem;text-align:center;"
                                           value="{{ $detalle->CantidadPiezaDanada ?? 0 }}" min="0"
                                           onchange="calcularTotalesFila({{ $detalleId }})">
                                </td>
                                <td class="text-center" id="total_{{ $detalleId }}">${{ number_format($totalCosto, 2) }}</td>
                                <td class="text-center" id="diferencia_{{ $detalleId }}">
                                    <span style="color:{{ $color }};font-weight:bold;">
                                        {{ $texto }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Botón Guardar (siempre habilitado) --}}
                                        <button type="button" class="btn btn-sm btn-success" 
                                                style="width:28px;height:28px;padding:0;border-radius:4px;font-size:0.7rem;"
                                                id="btnAdd_{{ $detalleId }}"
                                                onclick="ContarProductoSeleccionado('btnAdd_{{ $detalleId }}', {{ $productoId }}, {{ $detalleId }})">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        
                                        {{-- Botón Eliminar (deshabilitado si CantidadContada == 0) --}}
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                style="width:28px;height:28px;padding:0;border-radius:4px;font-size:0.7rem;"
                                                id="btnRem_{{ $detalleId }}"
                                                {{ ($detalle->CantidadContada ?? 0) > 0 ? '' : 'disabled' }}
                                                onclick="RemoverConteo('btnRem_{{ $detalleId }}', {{ $productoId }}, {{ $detalleId }})">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    No hay productos para mostrar en esta auditoría
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Botones --}}
                <div class="mt-4">
                    <a href="{{ route('cpanel.inventario.iniciar-conteo', ['id' => $inventario->InventarioId, 'iniciar' => 0]) }}" 
                       class="btn" style="background:#f59e0b;color:#fff;border-radius:6px;padding:0.5rem 1.5rem;">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Conteo
                    </a>
                    <a href="{{ route('cpanel.inventario.listado') }}" 
                       class="btn btn-secondary" style="border-radius:6px;padding:0.5rem 1.5rem;">
                        <i class="bi bi-list me-1"></i> Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================
// CONFIRMAR FINALIZAR CONTEO
// ============================================
function confirmarFinalizar(event, inventarioId) {
    event.preventDefault();
    
    if (!inventarioId || inventarioId === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el inventario',
            confirmButtonColor: '#dc2626'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Finalizar inventario?',
        text: 'Esta acción cambiará el estatus a "Cerrado" y no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("cpanel.inventario.finalizar-conteo", "") }}/' + inventarioId;
        }
    });
}

// ============================================
// CALCULAR TOTALES POR FILA
// ============================================
function calcularTotalesFila(detalleId) {
    var contado = parseInt(document.getElementById('contado_' + detalleId).value) || 0;
    var pieSolo = parseInt(document.getElementById('pie_solo_' + detalleId).value) || 0;
    var pieInvertido = parseInt(document.getElementById('pie_invertido_' + detalleId).value) || 0;
    var danado = parseInt(document.getElementById('danado_' + detalleId).value) || 0;
    
    var existencia = parseInt(document.getElementById('existencia_' + detalleId).textContent) || 0;
    
    // Calcular total
    var totalUnidades = contado - pieSolo - pieInvertido - danado;
    
    // Obtener costo (de la celda)
    var row = document.getElementById('fila-' + detalleId);
    var costoCell = row.querySelector('td:nth-child(4)');
    var costo = parseFloat(costoCell.textContent.replace('$', '').replace(',', '')) || 0;
    
    var totalCosto = totalUnidades * costo;
    document.getElementById('total_' + detalleId).textContent = '$' + totalCosto.toFixed(2);
    
    // Calcular diferencia
    var diferencia = contado - existencia;
    var color = diferencia < 0 ? '#ef4444' : (diferencia > 0 ? '#22c55e' : '#000');
    var texto = diferencia < 0 ? 'Falta ' + Math.abs(diferencia) : (diferencia > 0 ? 'Sobra ' + diferencia : 'Exacto');
    
    document.getElementById('diferencia_' + detalleId).innerHTML = '<span style="color:' + color + ';font-weight:bold;">' + texto + '</span>';
}

// ============================================
// CONTAR PRODUCTO EN AUDITORÍA (Guardar)
// ============================================
function ContarProductoSeleccionado(buttonId, productoId, detalleId) {
    var contado = parseInt(document.getElementById('contado_' + detalleId).value) || 0;
    var pieSolo = parseInt(document.getElementById('pie_solo_' + detalleId).value) || 0;
    var pieInvertido = parseInt(document.getElementById('pie_invertido_' + detalleId).value) || 0;
    var danado = parseInt(document.getElementById('danado_' + detalleId).value) || 0;
    var sucursalId = {{ $inventario->SucursalId ?? 0 }};
    
    // Obtener existencia y costo de la fila
    var row = document.getElementById('fila-' + detalleId);
    var existencia = parseInt(document.getElementById('existencia_' + detalleId).textContent) || 0;
    var costoCell = row.querySelector('td:nth-child(4)');
    var costoDivisa = parseFloat(costoCell.textContent.replace('$', '').replace(',', '')) || 0;
    
    var diferencia = contado - existencia;
    var totalCosto = (contado - pieSolo - pieInvertido - danado) * costoDivisa;

    // Validar que haya al menos una cantidad
    if (contado <= 0 && pieSolo <= 0 && pieInvertido <= 0 && danado <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'Ingrese al menos una cantidad para contar',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    Swal.fire({
        title: 'Guardando cambios...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    // 🔥 Datos como en .NET (usando Id = detalleId)
    var data = {
        Id: detalleId,
        CantidadContada: contado,
        CantidadPieSolo: pieSolo,
        CantidadPieInvertido: pieInvertido,
        CantidadDanado: danado,
        SucursalId: sucursalId
    };

    var URL = "{{ route('cpanel.inventario.guardar-conteo-producto') }}";

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(resultado => {
        Swal.close();
        
        if (resultado.success) {
            // ✅ Habilitar botón de eliminar
            var btnRem = document.getElementById('btnRem_' + detalleId);
            if (btnRem) btnRem.disabled = false;
            
            // Actualizar Total y Diferencia en la fila
            document.getElementById('total_' + detalleId).textContent = '$' + totalCosto.toFixed(2);
            
            var color = diferencia < 0 ? '#ef4444' : (diferencia > 0 ? '#22c55e' : '#000');
            var texto = diferencia < 0 ? 'Falta ' + Math.abs(diferencia) : (diferencia > 0 ? 'Sobra ' + diferencia : 'Exacto');
            document.getElementById('diferencia_' + detalleId).innerHTML = '<span style="color:' + color + ';font-weight:bold;">' + texto + '</span>';
            
            Swal.fire({
                icon: 'success',
                title: '¡Conteo actualizado!',
                text: 'El conteo se ha actualizado correctamente',
                timer: 1500,
                showConfirmButton: false
            });
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message || 'No se pudo actualizar el conteo',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar los cambios',
            confirmButtonColor: '#dc2626'
        });
    });
}

// ============================================
// REMOVER CONTEO EN AUDITORÍA (Eliminar)
// ============================================
function RemoverConteo(buttonId, productoId, detalleId) {
    // Preguntar confirmación
    Swal.fire({
        title: '¿Eliminar conteo?',
        text: 'Esta acción eliminará el conteo de este producto (se pondrá en 0)',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Resetear inputs a 0
            document.getElementById('contado_' + detalleId).value = 0;
            document.getElementById('pie_solo_' + detalleId).value = 0;
            document.getElementById('pie_invertido_' + detalleId).value = 0;
            document.getElementById('danado_' + detalleId).value = 0;
            
            var sucursalId = {{ $inventario->SucursalId ?? 0 }};

            Swal.fire({
                title: 'Eliminando conteo...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // 🔥 Misma ruta que Guardar, pero con todos los valores en 0
            var data = {
                Id: detalleId,
                CantidadContada: 0,
                CantidadPieSolo: 0,
                CantidadPieInvertido: 0,
                CantidadDanado: 0,
                SucursalId: sucursalId
            };

            var URL = "{{ route('cpanel.inventario.guardar-conteo-producto') }}";

            fetch(URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(resultado => {
                Swal.close();
                
                if (resultado.success) {
                    // ✅ Deshabilitar el botón de eliminar (igual que en .NET)
                    var btnRem = document.getElementById(buttonId);
                    if (btnRem) btnRem.disabled = true;
                    
                    // ✅ Actualizar Total a 0
                    document.getElementById('total_' + detalleId).textContent = '$0.00';
                    
                    // ✅ Actualizar Diferencia a "Exacto 0"
                    document.getElementById('diferencia_' + detalleId).innerHTML = '<span style="color:#000;font-weight:bold;">Exacto 0</span>';
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Conteo eliminado',
                        text: 'El conteo se ha eliminado correctamente',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resultado.message || 'No se pudo eliminar el conteo',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el conteo',
                    confirmButtonColor: '#dc2626'
                });
            });
        }
    });
}

// ============================================
// ENTER PARA GUARDAR
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-auditoria').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var row = this.closest('tr');
                var detalleId = row.id.replace('fila-', '');
                var btnGuardar = row.querySelector('.btn-success');
                if (btnGuardar) {
                    btnGuardar.click();
                }
            }
        });
    });
});
</script>
@endsection

@push('styles')
<style>
    .btn-orange {
        background: #f59e0b;
        border-color: #f59e0b;
        color: #fff;
    }
    .btn-orange:hover {
        background: #d97706;
        border-color: #d97706;
        color: #fff;
    }
    .img-thumbnail {
        padding: 2px;
        border-radius: 4px;
    }
    .img-zoomable:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
        cursor: pointer;
    }
    .input-auditoria {
        transition: border-color 0.2s ease;
    }
    .input-auditoria:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
    }
</style>
@endpush