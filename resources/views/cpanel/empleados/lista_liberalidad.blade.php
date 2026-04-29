@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Liberalidad')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Liberalidad</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liberalidad</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid"> 
        
        <!-- Card de filtros -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-body">
                <form action="{{ route('cpanel.empleados.lista_liberalidad') }}" method="GET" id="filtroForm">
                    <div class="row g-3 align-items-end">
                        <!-- Selector de Período (Mes/Año) -->
                        <div class="col-md-4">
                            <label for="periodo" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Período
                            </label>
                            <input type="month" 
                                name="periodo" 
                                id="periodo" 
                                class="form-control"
                                value="{{ request('periodo', sprintf('%04d-%02d', $filtroMesAnio['anio'], $filtroMesAnio['mes'])) }}">
                        </div>
                        
                        <!-- Botón Buscar -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                        
                        <!-- Botón Limpiar -->
                        <div class="col-md-2">
                            <a href="{{ route('cpanel.empleados.lista_liberalidad') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-undo me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($liberalidadDTO && $liberalidadDTO->detalles && $liberalidadDTO->detalles->count() > 0)  

            {{-- Mostrar datos desde el DTO guardado --}}
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <!-- Título y Buscador (Izquierda) -->
                        <div class="col-md-8 d-flex align-items-center gap-3">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>Reporte de Liberalidad
                                <small class="badge bg-danger text-white ms-2">
                                    <i class="fas fa-save me-1"></i>Cerrada
                                </small>
                            </h3>
                            
                            <!-- Buscador -->
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                    id="buscadorEmpleado" 
                                    class="form-control" 
                                    placeholder="Buscar empleado..."
                                    autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="limpiarBuscador">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Botones (Derecha) -->
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pdfTablaLiberalidad()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcelLiberalidad()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="tablaLiberalidad">
                            <thead class="table-light">
                                <tr>
                                    <th width="80" class="text-center">Foto</th>
                                    <th class="sortable" data-col="empleado">Empleado</th>
                                    <th width="200" class="sortable" data-col="sucursal">Sucursal</th>
                                    <th width="150" class="text-center sortable" data-col="unidades">Unidades</th>
                                    <th width="150" class="text-center sortable" data-col="ventas">Ventas</th>
                                    <th width="150" class="text-center sortable" data-col="bonos">Bonos</th>
                                    <th width="150" class="text-center sortable" data-col="deducciones">Deducciones</th>
                                    <th width="150" class="text-end sortable" data-col="liberalidad">Liberalidad</th>
                                    <th width="150" class="text-end sortable" data-col="neto">Neto</th>
                                    <th width="120" class="text-center">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($liberalidadDTO->detalles as $index => $detalle)
                                    @php
                                    // Priorizar Usuario sobre Empleado
                                    $usuario = $detalle->Usuario ?? null;
                                    $empleado = $detalle->Empleado ?? null;
                                    
                                    // Dar prioridad a Usuario si existe
                                    $entidad = $usuario ?: $empleado;
                                    
                                    // Obtener datos priorizando Usuario
                                    $usuarioId = $detalle->UsuarioId ?? $detalle->EmpleadoId ?? '';
                                    $nombre = 'Empleado';
                                    $vendedorId = '';
                                    $fotoPerfil = '';
                                    $sucursalId = null;
                                    
                                    if ($usuario) {
                                        $nombre = $usuario->NombreCompleto ?? 'Empleado';
                                        $vendedorId = $usuario->VendedorId ?? '';
                                        $fotoPerfil = $usuario->FotoPerfil ?? '';
                                        $sucursalId = $usuario->SucursalId ?? null;
                                    } elseif ($empleado) {
                                        $nombre = $empleado->NombreCompleto ?? 'Empleado';
                                        $vendedorId = $empleado->VendedorId ?? '';
                                        $fotoPerfil = $empleado->FotoPerfil ?? '';
                                        $sucursalId = $empleado->SucursalId ?? null;
                                    }
                                    
                                    // Obtener sucursal
                                    $sucursalNombre = 'N/A';
                                    
                                    if (isset($detalle->Sucursal) && $detalle->Sucursal && isset($detalle->Sucursal->Nombre)) {
                                        $sucursalNombre = $detalle->Sucursal->Nombre;
                                    } 
                                    elseif ($entidad && isset($entidad->Sucursal) && $entidad->Sucursal && isset($entidad->Sucursal->Nombre)) {
                                        $sucursalNombre = $entidad->Sucursal->Nombre;
                                    }
                                    elseif ($sucursalId) {
                                        $sucursalNombre = Cache::remember("sucursal_nombre_{$sucursalId}", 3600, function() use ($sucursalId) {
                                            $sucursal = \App\Models\Sucursal::find($sucursalId);
                                            return $sucursal ? $sucursal->Nombre : 'N/A';
                                        });
                                    }
                                    
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );

                                    // ============================================
                                    // PARA LIBERALIDAD CERRADA - USAR CAMPOS GUARDADOS
                                    // ============================================
                                    
                                    // Bonos = OtraLiberalidad (bonos del mes al cerrar)
                                    $bonosUSD = $detalle->OtraLiberalidad ?? 0;
                                    
                                    // Deducciones = TotalPagado? No, las deducciones se calculan como:
                                    // Deducciones = Liberalidad + Bonos - Neto
                                    // Pero como ya tenemos Neto (TotalPagado), mejor usamos la fórmula inversa
                                    $liberalidadUSD = $detalle->MontoLiberalidad ?? 0;
                                    $netoUSD = $detalle->TotalPagado ?? 0;
                                    
                                    // Calcular deducciones: Neto = Liberalidad + Bonos - Deducciones
                                    // Despejando: Deducciones = Liberalidad + Bonos - Neto
                                    $deduccionesUSD = max(0, $liberalidadUSD + $bonosUSD - $netoUSD);
                                    
                                    // Si hay AbonoPrestamo, se puede mostrar como tooltip
                                    $abonoPrestamo = $detalle->AbonoPrestamo ?? 0;
                                    $deudaPrestamo = $detalle->DeudaPrestamo ?? 0;
                                    @endphp

                                    <tr class="align-middle">
                                        <!-- Foto -->
                                        <td class="text-center">
                                            <img src="{{ $imgSrc }}" 
                                                alt="{{ $nombre }}"
                                                class="rounded-circle border border-success img-zoomable" 
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                                onclick="zoomImagen(this)"
                                                data-full-image="{{ $imgSrc }}"
                                                data-description="{{ $nombre }}">
                                        </td>
                                        
                                        <!-- Empleado -->
                                        <td data-order="{{ $nombre }}">
                                            <strong>{{ $nombre }}</strong>
                                            @if($vendedorId)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-badge me-1"></i>{{ $vendedorId }}
                                                </small>
                                            @endif
                                            @if($detalle->EsVendedor ?? false)
                                                <span class="badge bg-success ms-1">Vendedor</span>
                                            @else
                                                <span class="badge bg-info ms-1">Interno</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Sucursal -->
                                        <td data-order="{{ $sucursalNombre }}">
                                            <span class="badge bg-warning text-white p-2">
                                                <i class="fas fa-store me-1"></i>{{ $sucursalNombre }}
                                            </span>
                                        </td>
                                        
                                        <!-- Unidades -->
                                        <td class="text-center fw-bold" data-order="{{ $detalle->Unidades ?? 0 }}">
                                            <span class="badge bg-primary text-white p-2">
                                                {{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        
                                        <!-- Ventas USD -->
                                        <td class="text-center text-success fw-bold" data-order="{{ $detalle->Venta ?? 0 }}">
                                            $ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}
                                        </td>
                                        
                                        <!-- Bonos USD (usando OtraLiberalidad) -->
                                        <td class="text-center" data-order="{{ $bonosUSD }}">
                                            @if($bonosUSD > 0)
                                                <span class="text-success">
                                                    <i class="fas fa-gift me-1"></i>
                                                    $ {{ number_format($bonosUSD, 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">$ 0,00</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Deducciones USD (calculadas) -->
                                        <td class="text-center" data-order="{{ $deduccionesUSD }}">
                                            @if($deduccionesUSD > 0)
                                                <span class="text-danger">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    - $ {{ number_format($deduccionesUSD, 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">$ 0,00</span>
                                            @endif
                                            
                                            @if($abonoPrestamo > 0)
                                                <br>
                                                <small class="text-muted" title="Abono a préstamo">
                                                    <i class="fas fa-hand-holding-usd me-1"></i>Préstamo: ${{ number_format($abonoPrestamo, 2) }}
                                                </small>
                                            @endif
                                            @if($deudaPrestamo > 0)
                                                <br>
                                                <small class="text-muted" title="Deuda pendiente">
                                                    <i class="fas fa-clock me-1"></i>Deuda: ${{ number_format($deudaPrestamo, 2) }}
                                                </small>
                                            @endif
                                        </td>
                                        
                                        <!-- Liberalidad USD -->
                                        <td class="text-end fw-bold" data-order="{{ $liberalidadUSD }}">
                                            $ {{ number_format($liberalidadUSD, 2, ',', '.') }}
                                        </td>
                                        
                                        <!-- Neto USD (usando TotalPagado) -->
                                        <td class="text-end fw-bold text-primary" data-order="{{ $netoUSD }}">
                                            <span class="badge bg-dark p-2">
                                                <i class="fas fa-calculator me-1"></i>
                                                $ {{ number_format($netoUSD, 2, ',', '.') }}
                                            </span>
                                        </td>

                                        <!-- Detalle -->
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('liberalidad.detalle', ['id' => $detalle->LiberalidadDetalleId]) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Ver detalles del empleado"
                                                    data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Período: {{ $liberalidadDTO->FechaInicio->format('d/m/Y') }} - {{ $liberalidadDTO->FechaFinal->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                Total empleados: {{ $liberalidadDTO->detalles->count() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Tabla de resultados -->
            @if($liberalidad && $liberalidad->detalles && $liberalidad->detalles->count() > 0)

                {{-- NUEVO: Resumen por Sucursal --}}
                @if(isset($liberalidadPorSucursal) && $liberalidadPorSucursal->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-store me-2"></i> Resumen de Liberalidad por Sucursal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>Sucursal</th>
                                        <th>Cantidad de Vendedores</th>
                                        <th>Monto Liberalidad (USD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalGeneral = $liberalidadPorSucursal->sum('MontoLiberalidadSucursal');
                                        $totalVendedores = $liberalidadPorSucursal->sum('CantidadVendedores');
                                    @endphp
                                    @foreach($liberalidadPorSucursal as $sucursal)
                                    <tr>
                                        <td>
                                            <strong>
                                                <i class="fas fa-store me-2 text-warning"></i>
                                                {{ $sucursal['SucursalNombre'] }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">
                                                {{ $sucursal['CantidadVendedores'] }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            $ {{ number_format($sucursal['MontoLiberalidadSucursal'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr class="fw-bold">
                                        <td class="text-end">TOTAL GENERAL:</td>
                                        <td class="text-center">
                                            <span class="badge bg-dark">
                                                {{ $totalVendedores }} vendedores
                                            </span>
                                        </td>
                                        <td class="text-end text-success">
                                            $ {{ number_format($totalGeneral, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <!-- Título y Buscador (Izquierda) -->
                        <div class="col-md-8 d-flex align-items-center gap-3">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>Reporte de Liberalidad
                            </h3>
                            
                            <!-- Buscador -->
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                    id="buscadorEmpleado" 
                                    class="form-control" 
                                    placeholder="Buscar empleado..."
                                    autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="limpiarBuscador">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Botones (Derecha) -->
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <!-- Botón Cerrar Liberalidad (solo cuando NO está cerrada) -->
                                <button type="button" 
                                        class="btn btn-sm btn-success" 
                                        id="btnCerrarLiberalidad"
                                        onclick="cerrarLiberalidad()">
                                    <i class="fas fa-lock me-1"></i>Cerrar Liberalidad
                                </button>
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pdfTablaLiberalidad()">
                                    <i class="fas fa-print me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcelLiberalidad()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="tablaLiberalidad">
                            <thead class="table-light">
                                <th width="80" class="text-center">Foto</th>
                                <th class="sortable" data-col="empleado">Empleado</th>
                                <th width="200" class="sortable" data-col="sucursal">Sucursal</th>
                                <th width="150" class="text-center sortable" data-col="unidades">Unidades</th>
                                <th width="150" class="text-center sortable" data-col="ventas">Ventas</th>
                                <th width="150" class="text-center sortable" data-col="bonos">Bonos</th>
                                <th width="150" class="text-center sortable" data-col="deducciones">Deducciones</th>
                                <th width="150" class="text-end sortable" data-col="liberalidad">Liberalidad</th>
                                <th width="150" class="text-end sortable" data-col="neto">Neto</th>
                            </thead>
                            <tbody>
                                @foreach($liberalidad->detalles as $index => $detalle)
                                    @php
                                        // Obtener datos del usuario
                                        $usuario = $detalle->Usuario ?? null;
                                        $usuarioId = $detalle->UsuarioId ?? $detalle->EmpleadoId ?? '';
                                        $nombre = $usuario->NombreCompleto ?? 'Empleado';
                                        $vendedorId = $usuario->VendedorId ?? '';
                                        
                                        // Obtener nombre de sucursal
                                        $sucursalNombre = 'N/A';
                                        if ($usuario) {
                                            if (isset($usuario->Sucursal) && is_object($usuario->Sucursal)) {
                                                $sucursalNombre = $usuario->Sucursal->Nombre ?? 'N/A';
                                            } elseif (isset($usuario->sucursal) && is_object($usuario->sucursal)) {
                                                $sucursalNombre = $usuario->sucursal->Nombre ?? 'N/A';
                                            } elseif (isset($usuario->SucursalNombre)) {
                                                $sucursalNombre = $usuario->SucursalNombre;
                                            }
                                        }
                                        
                                        $fotoPerfil = $usuario->FotoPerfil ?? '';
                                        
                                        $imgSrc = FileHelper::getOrDownloadFile(
                                            'images/usuarios/',
                                            $fotoPerfil,
                                            'assets/img/adminlte/img/default.png'
                                        );
                                        
                                        // Calcular bonos (solo pendientes)
                                        $bonosPendientes = $detalle->bonos_pendientes ?? 0;
                                        $totalBonosUSD = 0;
                                        if (isset($detalle->bonos)) {
                                            $totalBonosUSD = $detalle->bonos->where('EsPagado', 0)->sum('MontoDivisa');
                                        }
                                        
                                        // Calcular deducciones (solo pendientes) - NUEVO
                                        $deduccionesPendientes = $detalle->deducciones_pendientes ?? 0;
                                        $totalDeduccionesUSD = 0;
                                        if (isset($detalle->deducciones)) {
                                            $totalDeduccionesUSD = $detalle->deducciones->where('EsPagado', 0)->sum('MontoDivisa');
                                        }
                                        
                                        $liberalidadUSD = $detalle->MontoLiberalidad ?? 0;
                                        $netoUSD = $liberalidadUSD + $totalBonosUSD - $totalDeduccionesUSD;
                                    @endphp
                                    <tr class="align-middle">                                        
                                        <!-- Foto -->
                                        <td class="text-center">
                                            <img src="{{ $imgSrc }}" 
                                                alt="{{ $nombre }}"
                                                class="rounded-circle border border-success img-zoomable" 
                                                style="width: 50px; height: 50px; object-fit: cover; cursor: zoom-in;"
                                                onclick="zoomImagen(this)"
                                                data-full-image="{{ $imgSrc }}"
                                                data-description="{{ $nombre }}">
                                        </td>
                                        
                                        <!-- Empleado -->
                                        <td data-order="{{ $nombre }}">
                                            <strong>{{ $nombre }}</strong>
                                            @if($vendedorId)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-badge me-1"></i>{{ $vendedorId }}
                                                </small>
                                            @endif
                                            @if($detalle->EsVendedor ?? false)
                                                <span class="badge bg-success ms-1">Vendedor</span>
                                            @else
                                                <span class="badge bg-info ms-1">Interno</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Sucursal -->
                                        <td data-order="{{ $sucursalNombre }}">
                                            <span class="badge bg-warning text-white p-2">
                                                <i class="fas fa-store me-1"></i>{{ $sucursalNombre }}
                                            </span>
                                        </td>
                                        
                                        <!-- Unidades -->
                                        <td class="text-center fw-bold" data-order="{{ $detalle->Unidades ?? 0 }}">
                                            <span class="badge bg-primary text-white p-2">
                                                {{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        
                                        <!-- Ventas USD -->
                                        <td class="text-center text-success fw-bold" data-order="{{ $detalle->Venta ?? 0 }}">
                                            $ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}
                                        </td>
                                        
                                        <!-- Bonos USD -->
                                        <td class="text-center">
                                            @if($totalBonosUSD > 0)
                                                <span class="text-success">
                                                    <i class="fas fa-gift me-1"></i>
                                                    $ {{ number_format($totalBonosUSD, 2, ',', '.') }}
                                                </span>
                                                @if($bonosPendientes > 0)
                                                    <br><small class="text-muted">({{ $bonosPendientes }} pendiente{{ $bonosPendientes != 1 ? 's' : '' }})</small>
                                                @endif
                                            @else
                                                <span class="text-muted">$ 0,00</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Deducciones USD (NUEVA) -->
                                        <td class="text-center">
                                            @if($totalDeduccionesUSD > 0)
                                                <span class="text-danger">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    - $ {{ number_format($totalDeduccionesUSD, 2, ',', '.') }}
                                                </span>
                                                @if($deduccionesPendientes > 0)
                                                    <br><small class="text-muted">({{ $deduccionesPendientes }} pendiente{{ $deduccionesPendientes != 1 ? 's' : '' }})</small>
                                                @endif
                                            @else
                                                <span class="text-muted">$ 0,00</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Liberalidad USD -->
                                        <td class="text-center">                                            
                                            <span class="text-success">
                                                <i class="fas fa-gift me-1"></i>
                                                $ {{ number_format($liberalidadUSD, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        
                                        <!-- Neto USD -->
                                        <td class="text-end fw-bold text-primary">
                                            <span class="badge bg-dark p-2">
                                                <i class="fas fa-calculator me-1"></i>
                                                $ {{ number_format($netoUSD, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Período: {{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                Total empleados: {{ $liberalidad->detalles->count() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-chart-line fa-4x text-muted"></i>
                        </div>
                        <h3 class="empty-state-title mt-3">No hay datos para mostrar</h3>
                        <p class="empty-state-subtitle">
                            No se encontraron ventas o empleados para el período seleccionado.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        @endif
        
    </div>
</div>

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    const liberalidadPorSucursal = @json($liberalidadPorSucursal ?? []);
    const periodoActual = '{{ $periodo ?? date("Y-m") }}';
    
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ==========================
        // BUSCADOR DE EMPLEADOS
        // ==========================
        const buscador = document.getElementById('buscadorEmpleado');
        const tabla = document.getElementById('tablaLiberalidad');
        const limpiarBtn = document.getElementById('limpiarBuscador');
        
        if (buscador && tabla) {
            function filtrarTabla() {
                const textoBusqueda = buscador.value.toLowerCase().trim();
                const filas = tabla.querySelectorAll('tbody tr');
                let filasVisibles = 0;
                
                filas.forEach(fila => {
                    const celdaEmpleado = fila.children[1];
                    if (celdaEmpleado) {
                        const textoEmpleado = celdaEmpleado.textContent.toLowerCase();
                        
                        if (textoBusqueda === '' || textoEmpleado.includes(textoBusqueda)) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });
                
                const tbody = tabla.querySelector('tbody');
                let mensajeNoResultados = document.getElementById('mensajeNoResultados');
                
                if (filasVisibles === 0 && textoBusqueda !== '') {
                    if (!mensajeNoResultados) {
                        mensajeNoResultados = document.createElement('tr');
                        mensajeNoResultados.id = 'mensajeNoResultados';
                        const colspan = tabla.querySelector('thead tr').children.length;
                        mensajeNoResultados.innerHTML = `
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                <i class="fas fa-search me-2"></i>
                                No se encontraron empleados con el nombre "${buscador.value}"
                            </td>
                        `;
                        tbody.appendChild(mensajeNoResultados);
                    }
                } else if (mensajeNoResultados) {
                    mensajeNoResultados.remove();
                }
            }
            
            buscador.addEventListener('input', filtrarTabla);
            
            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function() {
                    buscador.value = '';
                    filtrarTabla();
                    buscador.focus();
                });
            }
            
            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    buscador.value = '';
                    filtrarTabla();
                }
            });
        }

        // ==========================
        // ORDENAR TABLA POR CLIC EN TH
        // ==========================
        (function() {
            const tabla = document.getElementById('tablaLiberalidad');
            if (!tabla) return;

            const ths = tabla.querySelectorAll('thead th.sortable');
            const tbody = tabla.querySelector('tbody');
            let ordenAscendente = true;
            let columnaActual = null;

            ths.forEach(th => {
                th.style.cursor = 'pointer';
                
                th.addEventListener('click', () => {
                    const colIndex = Array.from(th.parentNode.children).indexOf(th);
                    
                    document.querySelectorAll('.sort-icon').forEach(icon => {
                        icon.innerHTML = '↕️';
                    });
                    
                    if (columnaActual === colIndex) {
                        ordenAscendente = !ordenAscendente;
                    } else {
                        ordenAscendente = true;
                        columnaActual = colIndex;
                    }
                    
                    const icono = th.querySelector('.sort-icon');
                    if (icono) {
                        icono.innerHTML = ordenAscendente ? '⬆️' : '⬇️';
                    } else {
                        ths.forEach(t => t.classList.remove('sort-asc', 'sort-desc'));
                        th.classList.add(ordenAscendente ? 'sort-asc' : 'sort-desc');
                    }
                    
                    ordenarTabla(tabla, colIndex, ordenAscendente);
                });
            });

            function ordenarTabla(tabla, index, asc = true) {
                const filas = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
                const filasReales = filas.filter(fila => fila.id !== 'mensajeNoResultados');

                filasReales.sort((a, b) => {
                    const tdA = a.children[index];
                    const tdB = b.children[index];
                    
                    if (!tdA || !tdB) return 0;

                    const valorA = tdA.dataset.order || extraerValorCelda(tdA);
                    const valorB = tdB.dataset.order || extraerValorCelda(tdB);

                    const numA = parseFloat(valorA);
                    const numB = parseFloat(valorB);

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    } else {
                        return asc 
                            ? valorA.toString().localeCompare(valorB.toString())
                            : valorB.toString().localeCompare(valorA.toString());
                    }
                });

                const filasOcultas = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));
                
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                
                filasReales.forEach(fila => tbody.appendChild(fila));
                filasOcultas.forEach(fila => tbody.appendChild(fila));
            }

            function extraerValorCelda(td) {
                const badge = td.querySelector('.badge');
                if (badge) {
                    return badge.textContent.trim().replace(/[$,]/g, '');
                }
                
                const strong = td.querySelector('strong');
                if (strong) {
                    return strong.textContent.trim();
                }
                
                return td.textContent.trim().replace(/[$,]/g, '');
            }
        })();
        
        if (sessionStorage.getItem('generarPDF') === 'true') {
            const periodo = sessionStorage.getItem('periodoPDF');
            sessionStorage.removeItem('generarPDF');
            sessionStorage.removeItem('periodoPDF');
            
            // Esperar a que la tabla se actualice completamente
            setTimeout(() => {
                generarPDFLiberalidadConDatos(periodo);
            }, 1000);
        }
    });
    
    function zoomImagen(img) {
        Swal.fire({
            imageUrl: img.src,
            imageAlt: img.alt,
            title: img.alt,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '2em',
            background: '#fff',
            customClass: {
                image: 'img-fluid rounded'
            }
        });
    }
    
    function pdfTablaLiberalidad() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        let yPosition = 20;
        
        // Título principal
        doc.setFontSize(16);
        doc.setTextColor(41, 128, 185);
        doc.text('Reporte de Liberalidad', 14, yPosition);
        yPosition += 8;
        
        // Información del período
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text(`Período: ${periodoActual}`, 14, yPosition);
        yPosition += 7;
        doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
        yPosition += 10;
        
        // ============================================
        // AGREGAR RESUMEN POR SUCURSAL SI EXISTE
        // ============================================
        if (liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            // Preparar datos del resumen
            const resumenHeaders = [['Sucursal', 'Cantidad Vendedores', 'Monto Liberalidad (USD)']];
            const resumenBody = [];
            let totalVendedores = 0;
            let totalMonto = 0;
            
            liberalidadPorSucursal.forEach(sucursal => {
                totalVendedores += sucursal.CantidadVendedores;
                totalMonto += sucursal.MontoLiberalidadSucursal;
                resumenBody.push([
                    sucursal.SucursalNombre,
                    sucursal.CantidadVendedores.toString(),
                    `$ ${sucursal.MontoLiberalidadSucursal.toFixed(2)}`
                ]);
            });
            
            // Agregar fila de totales
            resumenBody.push([
                'TOTAL GENERAL',
                totalVendedores.toString(),
                `$ ${totalMonto.toFixed(2)}`
            ]);
            
            // Título del resumen
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text('Resumen por Sucursal', 14, yPosition);
            yPosition += 5;
            
            // Tabla de resumen
            doc.autoTable({
                head: resumenHeaders,
                body: resumenBody,
                startY: yPosition,
                theme: 'grid',
                headStyles: {
                    fillColor: [52, 152, 219],
                    textColor: [255, 255, 255],
                    fontSize: 9,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    fontSize: 8,
                    cellPadding: 3
                },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { cellWidth: 40, halign: 'center' },
                    2: { cellWidth: 45, halign: 'right' }
                },
                didDrawPage: function(data) {
                    yPosition = data.cursor.y + 10;
                }
            });
            
            yPosition = doc.lastAutoTable.finalY + 10;
        }
        
        // ============================================
        // AGREGAR TABLA DE EMPLEADOS
        // ============================================
        const tabla = document.getElementById('tablaLiberalidad');
        if (tabla) {
            // Clonar la tabla para no modificar la original
            const tablaClone = tabla.cloneNode(true);
            
            // OBTENER TODAS LAS FILAS (incluyendo thead y tbody)
            const todasLasFilas = tablaClone.querySelectorAll('tr');
            
            // Ocultar la primera columna (Foto) en TODAS las filas
            todasLasFilas.forEach(row => {
                const primeraCelda = row.querySelector('th:first-child, td:first-child');
                if (primeraCelda) {
                    primeraCelda.style.display = 'none';
                }
            });
            
            // También eliminar específicamente el texto "Foto" del encabezado si quedara
            const thead = tablaClone.querySelector('thead');
            if (thead) {
                const thumbs = thead.querySelectorAll('th');
                if (thumbs.length > 0 && thumbs[0].innerText.trim() === 'Foto') {
                    thumbs[0].style.display = 'none';
                }
            }
            
            // Opcional: Ocultar columna de Detalle si existe (última columna)
            const headers = thead ? thead.querySelectorAll('th') : [];
            if (headers.length > 0) {
                const lastHeader = headers[headers.length - 1];
                if (lastHeader && lastHeader.innerText.includes('Detalle')) {
                    const lastColIndex = headers.length - 1;
                    tablaClone.querySelectorAll('tr').forEach(row => {
                        const celdas = row.children;
                        if (celdas[lastColIndex]) {
                            celdas[lastColIndex].style.display = 'none';
                        }
                    });
                }
            }
            
            // Generar tabla de empleados
            doc.autoTable({
                html: tablaClone,
                startY: yPosition,
                theme: 'grid',
                styles: { 
                    fontSize: 7,
                    cellPadding: 2
                },
                headStyles: { 
                    fillColor: [41, 128, 185],
                    textColor: [255, 255, 255],
                    fontSize: 8,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                didDrawPage: function(data) {
                    // Número de página
                    const pageCount = doc.internal.getNumberOfPages();
                    doc.setFontSize(8);
                    doc.setTextColor(150, 150, 150);
                    doc.text(
                        `Página ${data.pageNumber} de ${pageCount}`,
                        doc.internal.pageSize.getWidth() - 30,
                        doc.internal.pageSize.getHeight() - 10
                    );
                }
            });
        } else {
            // Si no hay tabla, mostrar mensaje
            doc.setFontSize(10);
            doc.setTextColor(150, 150, 150);
            doc.text('No hay datos de empleados para mostrar', 14, yPosition);
        }
        
        // Guardar PDF
        const fecha = new Date().toISOString().slice(0, 10);
        doc.save(`Liberalidad_${periodoActual}_${fecha}.pdf`);
    }
    
    function exportarExcelLiberalidad() {
        const tabla = document.getElementById('tablaLiberalidad');
        if (!tabla) return;
        
        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Liberalidad"});
        XLSX.writeFile(wb, `Liberalidad_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    let pdfEnMemoria = null;
    let nombreArchivo = null;

    function generarPDFLiberalidadConDatos(periodo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        let yPosition = 20;
        
        // ============================================
        // FUNCIÓN AUXILIAR PARA LIMPIAR NÚMEROS
        // ============================================
        const limpiarNumero = (texto) => {
            if (!texto) return 0;
            let limpio = texto.replace('$', '').replace('-', '').trim();
            if (limpio.includes('(')) limpio = limpio.split('(')[0].trim();
            limpio = limpio.replace(/\./g, '');
            limpio = limpio.replace(',', '.');
            return parseFloat(limpio) || 0;
        };
        
        const limpiarEntero = (texto) => {
            if (!texto) return 0;
            return parseInt(texto.replace(/\./g, '')) || 0;
        };
        
        const limpiarNombre = (texto) => {
            if (!texto) return '';
            return texto.replace('Vendedor', '').replace('Interno', '').trim();
        };
        
        // ============================================
        // OBTENER RESUMEN POR SUCURSAL
        // ============================================
        let resumenData = null;
        
        // Intentar obtener de la variable global
        if (typeof liberalidadPorSucursal !== 'undefined' && liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            resumenData = liberalidadPorSucursal;
        } 
        // Intentar obtener del sessionStorage (para después del reload)
        else if (sessionStorage.getItem('resumenPorSucursal')) {
            resumenData = JSON.parse(sessionStorage.getItem('resumenPorSucursal'));
            // Limpiar después de usar
            sessionStorage.removeItem('resumenPorSucursal');
        }
        // Intentar obtener del DOM
        else {
            const resumenCard = document.querySelector('.card.mb-4:has(.bg-gradient-info)');
            if (resumenCard) {
                const tablaResumen = resumenCard.querySelector('table');
                if (tablaResumen) {
                    resumenData = [];
                    const filasResumen = tablaResumen.querySelectorAll('tbody tr');
                    filasResumen.forEach(fila => {
                        const celdas = fila.querySelectorAll('td');
                        if (celdas.length >= 3) {
                            resumenData.push({
                                SucursalNombre: celdas[0]?.innerText.trim() || '',
                                CantidadVendedores: parseInt(celdas[1]?.innerText.replace(/[^0-9]/g, '')) || 0,
                                MontoLiberalidadSucursal: parseFloat(celdas[2]?.innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0
                            });
                        }
                    });
                }
            }
        }
        
        // ============================================
        // AGREGAR RESUMEN POR SUCURSAL SI EXISTE
        // ============================================
        if (resumenData && resumenData.length > 0) {
            // Título principal
            doc.setFontSize(16);
            doc.setTextColor(41, 128, 185);
            doc.text('Reporte de Liberalidad', 14, yPosition);
            yPosition += 8;
            
            // Información del período
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Período: ${periodo}`, 14, yPosition);
            yPosition += 7;
            doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
            yPosition += 10;
            
            // Preparar datos del resumen
            const resumenHeaders = [['Sucursal', 'Cantidad Vendedores', 'Monto Liberalidad (USD)']];
            const resumenBody = [];
            let totalVendedores = 0;
            let totalMonto = 0;
            
            resumenData.forEach(sucursal => {
                totalVendedores += sucursal.CantidadVendedores;
                totalMonto += sucursal.MontoLiberalidadSucursal;
                resumenBody.push([
                    sucursal.SucursalNombre,
                    sucursal.CantidadVendedores.toString(),
                    `$ ${sucursal.MontoLiberalidadSucursal.toFixed(2)}`
                ]);
            });
            
            // Agregar fila de totales
            resumenBody.push([
                'TOTAL GENERAL',
                totalVendedores.toString(),
                `$ ${totalMonto.toFixed(2)}`
            ]);
            
            // Título del resumen
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text('Resumen por Sucursal', 14, yPosition);
            yPosition += 5;
            
            // Tabla de resumen
            doc.autoTable({
                head: resumenHeaders,
                body: resumenBody,
                startY: yPosition,
                theme: 'grid',
                headStyles: {
                    fillColor: [52, 152, 219],
                    textColor: [255, 255, 255],
                    fontSize: 9,
                    fontStyle: 'bold',
                    halign: 'center'
                },
                bodyStyles: {
                    fontSize: 8,
                    cellPadding: 3
                },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { cellWidth: 40, halign: 'center' },
                    2: { cellWidth: 45, halign: 'right' }
                }
            });
            
            yPosition = doc.lastAutoTable.finalY + 15;
        } else {
            // Si no hay resumen, mostrar solo el encabezado normal
            doc.setFontSize(16);
            doc.setTextColor(41, 128, 185);
            doc.text('Reporte de Liberalidad', 14, yPosition);
            yPosition += 8;
            
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Período: ${periodo}`, 14, yPosition);
            yPosition += 7;
            doc.text(`Fecha de generación: ${new Date().toLocaleString('es-VE')}`, 14, yPosition);
            yPosition += 10;
        }
        
        // ============================================
        // EXTRAER DATOS DE LA TABLA
        // ============================================
        const tabla = document.getElementById('tablaLiberalidad');
        if (!tabla) {
            console.error('No se encontró la tabla');
            return;
        }
        
        const headers = [
            ['Empleado', 'Sucursal', 'Unidades', 'Ventas USD', 'Bonos USD', 'Deducciones USD', 'Liberalidad USD', 'Neto USD']
        ];
        const rows = [];
        
        const filas = tabla.querySelectorAll('tbody tr');
        
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 9) {
                rows.push([
                    limpiarNombre(celdas[1]?.innerText || ''),
                    celdas[2]?.innerText.trim() || '',
                    limpiarEntero(celdas[3]?.innerText),
                    limpiarNumero(celdas[4]?.innerText),
                    limpiarNumero(celdas[5]?.innerText),
                    limpiarNumero(celdas[6]?.innerText),
                    limpiarNumero(celdas[7]?.innerText),
                    limpiarNumero(celdas[8]?.innerText)
                ]);
            }
        });
        
        // ============================================
        // CALCULAR TOTALES
        // ============================================
        let totales = {
            unidades: 0,
            ventas: 0,
            bonos: 0,
            deducciones: 0,
            liberalidad: 0,
            neto: 0
        };
        
        rows.forEach(row => {
            totales.unidades += row[2];
            totales.ventas += row[3];
            totales.bonos += row[4];
            totales.deducciones += row[5];
            totales.liberalidad += row[6];
            totales.neto += row[7];
        });
        
        // Agregar fila de totales
        rows.push([
            'TOTALES',
            '',
            totales.unidades.toLocaleString('es-VE', { minimumFractionDigits: 0 }),
            `$ ${totales.ventas.toFixed(2)}`,
            `$ ${totales.bonos.toFixed(2)}`,
            `$ ${totales.deducciones.toFixed(2)}`,
            `$ ${totales.liberalidad.toFixed(2)}`,
            `$ ${totales.neto.toFixed(2)}`
        ]);
        
        // Formatear filas para visualización
        const filasFormateadas = rows.map((row, index) => {
            if (index === rows.length - 1) return row; // Totales
            return [
                row[0],
                row[1],
                row[2].toLocaleString('es-VE', { minimumFractionDigits: 0 }),
                `$ ${row[3].toFixed(2)}`,
                `$ ${row[4].toFixed(2)}`,
                `$ ${row[5].toFixed(2)}`,
                `$ ${row[6].toFixed(2)}`,
                `$ ${row[7].toFixed(2)}`
            ];
        });
        
        // ============================================
        // GENERAR TABLA DE EMPLEADOS
        // ============================================
        doc.autoTable({
            head: headers,
            body: filasFormateadas,
            startY: yPosition,
            theme: 'grid',
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: [255, 255, 255],
                fontSize: 9,
                fontStyle: 'bold',
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 8,
                cellPadding: 3
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            columnStyles: {
                0: { cellWidth: 35 },  // Empleado
                1: { cellWidth: 30 },  // Sucursal
                2: { cellWidth: 20, halign: 'center' },  // Unidades
                3: { cellWidth: 25, halign: 'right' },   // Ventas
                4: { cellWidth: 25, halign: 'right' },   // Bonos
                5: { cellWidth: 25, halign: 'right' },   // Deducciones
                6: { cellWidth: 25, halign: 'right' },   // Liberalidad
                7: { cellWidth: 25, halign: 'right' }    // Neto
            },
            didDrawPage: function(data) {
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(150, 150, 150);
                doc.text(
                    `Página ${data.pageNumber} de ${pageCount}`,
                    doc.internal.pageSize.getWidth() - 30,
                    doc.internal.pageSize.getHeight() - 10
                );
            }
        });
        
        // Guardar PDF
        doc.save(`Liberalidad_${periodo}_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    function exportarPDFMemoria() {
        if (pdfEnMemoria) {
            const link = document.createElement('a');
            const url = URL.createObjectURL(pdfEnMemoria);
            link.href = url;
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            // Limpiar memoria
            pdfEnMemoria = null;
            nombreArchivo = null;
        }
    }

    function cerrarLiberalidad() {
        const periodo = document.getElementById('periodo').value;
        
        // Guardar el resumen por sucursal si existe antes de cerrar
        if (typeof liberalidadPorSucursal !== 'undefined' && liberalidadPorSucursal && liberalidadPorSucursal.length > 0) {
            sessionStorage.setItem('resumenPorSucursal', JSON.stringify(liberalidadPorSucursal));
        } else {
            // Intentar extraer el resumen del DOM si no está en la variable
            const resumenCard = document.querySelector('.card.mb-4:has(.bg-gradient-info)');
            if (resumenCard) {
                const tablaResumen = resumenCard.querySelector('table');
                if (tablaResumen) {
                    const resumenData = [];
                    const filasResumen = tablaResumen.querySelectorAll('tbody tr');
                    filasResumen.forEach(fila => {
                        const celdas = fila.querySelectorAll('td');
                        if (celdas.length >= 3) {
                            resumenData.push({
                                SucursalNombre: celdas[0]?.innerText.trim() || '',
                                CantidadVendedores: parseInt(celdas[1]?.innerText.replace(/[^0-9]/g, '')) || 0,
                                MontoLiberalidadSucursal: parseFloat(celdas[2]?.innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0
                            });
                        }
                    });
                    if (resumenData.length > 0) {
                        sessionStorage.setItem('resumenPorSucursal', JSON.stringify(resumenData));
                    }
                }
            }
        }
        
        Swal.fire({
            title: '¿Cerrar Liberalidad?',
            html: `Esta acción no se puede deshacer.<br>
                La liberalidad del período <strong>${periodo}</strong> quedará registrada.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando liberalidad...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch('{{ route("cpanel.empleados.liberalidad.cerrar") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        periodo: periodo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Guardar flag para generar PDF después de la recarga
                        sessionStorage.setItem('generarPDF', 'true');
                        sessionStorage.setItem('periodoPDF', periodo);
                        // Recargar la página
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al cerrar la liberalidad', 'error');
                });
            }
        });
    }
</script>

<style>
    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem !important;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .btn-group .btn i {
        font-size: 0.9rem;
    }
    
    .empty-state {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .empty-state-icon {
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .img-zoomable {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: zoom-in;
    }
    
    .img-zoomable:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Estilo para el scroll de la tabla de empleados */
    .card-body.p-0::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .card-body.p-0::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .card-body.p-0::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .card-body.p-0::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Sticky header para la tabla dentro del scroll */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Mejorar visualización de la tabla */
    #tablaEmpleadosSeleccionados {
        margin-bottom: 0;
    }

    #tablaEmpleadosSeleccionados thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endsection