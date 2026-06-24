@extends('layout.layout_dashboard')

@section('title', 'Distribuciones')

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
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Distribuciones</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Gestión de distribuciones de mercancía</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Distribuciones</li>
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
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-check me-2"></i>Listado de Distribuciones
                    </h6>
                    <a href="{{ route('cpanel.distribuciones.create') }}" class="btn btn-sm fw-semibold" style="background:rgba(255,255,255,0.85);color:#7c3aed;border:1px solid rgba(255,255,255,0.4);font-size:0.8rem;">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Distribución
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaDistribuciones">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <!-- ✅ Agregar onclick para ordenar -->
                                <th class="ps-4 py-3 text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(0)">
                                    NÚMERO <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(1)">
                                    FECHA <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(2)">
                                    SUC. ORIGEN <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(3)">
                                    SUC. DESTINO <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(4)">
                                    ITEMS <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-end text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(5)">
                                    UND. ENVIADAS <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="py-3 text-center text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;cursor:pointer;"
                                    onclick="ordenarTabla(6)">
                                    ESTATUS <i class="bi bi-arrow-up-down ms-1"></i>
                                </th>
                                <th class="pe-4 py-3 text-center text-muted fw-semibold" 
                                    style="font-size:0.75rem;letter-spacing:.06em;width:100px;">
                                    ACCIONES
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distribuciones as $distribucion)
                            @php
                                $estatusMap = [
                                    1 => ['texto' => 'Nueva', 'clase' => 'badge bg-secondary text-white'],
                                    2 => ['texto' => 'En Edición', 'clase' => 'badge bg-warning text-dark'],
                                    3 => ['texto' => 'Registrada', 'clase' => 'badge bg-info text-white'],
                                    4 => ['texto' => 'Recibiendo', 'clase' => 'badge bg-primary text-white'],
                                    5 => ['texto' => 'Disponible', 'clase' => 'badge bg-success text-white'],
                                    6 => ['texto' => 'Procesada', 'clase' => 'badge bg-dark text-white'],
                                    9 => ['texto' => 'Anulada', 'clase' => 'badge bg-danger text-white']
                                ];
                                $estatus = $estatusMap[$distribucion->Estatus] ?? ['texto' => 'Desconocido', 'clase' => 'badge bg-secondary text-white'];
                                
                                $sucursalesDestino = $distribucion->sucursales_destino ?? collect();
                                $nombresDestino = $sucursalesDestino->pluck('Nombre')->implode(', ');
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $distribucion->Numero }}</span>
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ \Carbon\Carbon::parse($distribucion->Fecha)->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $distribucion->Origen ?? 'N/A' }}
                                </td>
                                <td class="text-muted" style="font-size:0.88rem;">
                                    {{ $nombresDestino ?: 'N/A' }}
                                </td>
                                <td class="text-center fw-semibold">{{ $distribucion->CantidadItems ?? 0 }}</td>
                                <td class="text-end fw-semibold">{{ number_format($distribucion->CantidadEmitida ?? 0, 0) }}</td>
                                <td class="text-center">
                                    <span class="{{ $estatus['clase'] }} rounded-pill px-2 py-1 fw-semibold" style="font-size:0.75rem;">
                                        {{ $estatus['texto'] }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('cpanel.distribuciones.edit', $distribucion->TransferenciaId) }}"
                                        class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                        style="width:30px;height:30px;background:rgba(59,130,246,0.1);color:#1d4ed8;border:1px solid rgba(59,130,246,0.25);"
                                        title="Editar distribución">
                                            <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.25);"
                                                onclick="finalizarDistribucion({{ $distribucion->TransferenciaId }})"
                                                title="Finalizar distribución">
                                            <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex align-items-center justify-content-center rounded-2 mx-auto mb-3"
                                         style="width:52px;height:52px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);opacity:0.5;">
                                        <i class="bi bi-diagram-3 text-white" style="font-size:1.4rem;"></i>
                                    </div>
                                    <p class="mb-0 text-muted fw-semibold">No hay distribuciones registradas</p>
                                    <small class="text-muted">Las nuevas distribuciones aparecerán aquí</small>
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

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para buscar en la tabla
    function buscarDistribucion() {
        const input = document.getElementById('buscarInput');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('tablaDistribuciones');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const textValue = cells[j].textContent || cells[j].innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    }

    // Función para ordenar la tabla
    function ordenarTabla(columna) {
        const table = document.getElementById('tablaDistribuciones');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));
        
        const isAscending = table.dataset.sortAsc === 'true';
        table.dataset.sortAsc = !isAscending;
        
        rows.sort((a, b) => {
            const aValue = a.getElementsByTagName('td')[columna].textContent.trim();
            const bValue = b.getElementsByTagName('td')[columna].textContent.trim();
            
            // Intentar convertir a número
            const aNum = parseFloat(aValue.replace(/,/g, ''));
            const bNum = parseFloat(bValue.replace(/,/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            return isAscending 
                ? aValue.localeCompare(bValue) 
                : bValue.localeCompare(aValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    function finalizarDistribucion(id) {
        // ✅ Obtener la URL desde la ruta con nombre
        const url = '{{ route("cpanel.distribuciones.cancelar", ":id") }}'.replace(':id', id);
        console.log('🌐 URL:', url);
        
        Swal.fire({
            title: '¿Eliminar distribución?',
            text: 'Esta acción devolverá la distribución',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Volver'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Cancelando distribución',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Distribución cancelada!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
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
    }
</script>
@endsection