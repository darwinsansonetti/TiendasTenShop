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
                                    
                                    // Obtener sucursal - Prioridad: 1. Sucursal del DTO, 2. Sucursal de la entidad
                                    $sucursalNombre = 'N/A';
                                    
                                    // Primero, verificar si el DTO ya tiene la sucursal cargada
                                    if (isset($detalle->Sucursal) && $detalle->Sucursal && isset($detalle->Sucursal->Nombre)) {
                                        $sucursalNombre = $detalle->Sucursal->Nombre;
                                    } 
                                    // Si no, buscar en la entidad (usuario o empleado)
                                    elseif ($entidad && isset($entidad->Sucursal) && $entidad->Sucursal && isset($entidad->Sucursal->Nombre)) {
                                        $sucursalNombre = $entidad->Sucursal->Nombre;
                                    }
                                    // Si la entidad tiene SucursalId pero no la relación cargada, intentar obtenerla
                                    elseif ($sucursalId) {
                                        // Usar cache para evitar múltiples consultas
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

                                    // Calcular bonos
                                    $bonosPendientes = $detalle->bonos_pendientes ?? 0;
                                    $bonosPagados = $detalle->bonos_pagados ?? 0;
                                    $totalBonosUSD = 0;
                                    if (isset($detalle->bonos)) {
                                        $totalBonosUSD = $detalle->bonos->where('EsPagado', 0)->sum('MontoDivisa');
                                    }
                                    
                                    // Calcular deducciones (NUEVO)
                                    $deduccionesPendientes = $detalle->deducciones_pendientes ?? 0;
                                    $deduccionesPagadas = $detalle->deducciones_pagadas ?? 0;
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
                                    <td class="text-end fw-bold">
                                        $ {{ number_format($liberalidadUSD, 2, ',', '.') }}
                                    </td>
                                    
                                    <!-- Neto USD (NUEVO) -->
                                    <td class="text-end fw-bold text-primary">
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
                                        <td class="text-end fw-bold">
                                            $ {{ number_format($liberalidadUSD, 2, ',', '.') }}
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
    
    document.addEventListener("DOMContentLoaded", function() {
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // // ==========================
        // // CHECK ALL / UNCHECK ALL
        // // ==========================
        // const checkAll = document.getElementById('checkAll');
        // if (checkAll) {
        //     checkAll.addEventListener('change', function() {
        //         const checkboxes = document.querySelectorAll('.detalle-checkbox');
        //         checkboxes.forEach(cb => cb.checked = this.checked);
        //         updateSelectAllState();
        //     });
        // }

        // // ==========================
        // // ACTUALIZAR ESTADO DEL CHECK ALL
        // // ==========================
        // function updateSelectAllState() {
        //     const checkAll = document.getElementById('checkAll');
        //     const checkboxes = document.querySelectorAll('.detalle-checkbox');
        //     if (!checkAll) return;
            
        //     const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);
        //     const ningunoSeleccionado = Array.from(checkboxes).every(cb => !cb.checked);
            
        //     if (todosSeleccionados) {
        //         checkAll.checked = true;
        //         checkAll.indeterminate = false;
        //     } else if (ningunoSeleccionado) {
        //         checkAll.checked = false;
        //         checkAll.indeterminate = false;
        //     } else {
        //         checkAll.indeterminate = true;
        //     }
        // }

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
        
        // // ==========================
        // // EVENTOS PARA CHECKBOXES INDIVIDUALES
        // // ==========================
        // const checkboxes = document.querySelectorAll('.detalle-checkbox');
        // checkboxes.forEach(checkbox => {
        //     checkbox.addEventListener('change', updateSelectAllState);
        // });
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
        
        doc.autoTable({ 
            html: '#tablaLiberalidad',
            startY: 20,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [41, 128, 185] }
        });
        
        doc.save(`Liberalidad_${new Date().toISOString().slice(0,10)}.pdf`);
    }
    
    function exportarExcelLiberalidad() {
        const tabla = document.getElementById('tablaLiberalidad');
        if (!tabla) return;
        
        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Liberalidad"});
        XLSX.writeFile(wb, `Liberalidad_${new Date().toISOString().slice(0,10)}.xlsx`);
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