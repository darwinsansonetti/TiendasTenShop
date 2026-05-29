@extends('layout.layout_dashboard')

@section('title', 'Detalle del Contenedor')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>Detalle del Contenedor
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}">Contenedores</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $contenedor->Nombre }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Tarjeta de Información General -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>Información del Contenedor
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.contenedores.editar', $contenedor->Id) }}" 
                       class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 180px;">Nombre:</th>
                                <td><strong>{{ $contenedor->Nombre }}</strong></td>
                            </tr>
                            <tr>
                                <th>Número de Operación:</th>
                                <td>{{ $contenedor->NumeroOperacion ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación:</th>
                                <td>{{ \Carbon\Carbon::parse($contenedor->FechaCreacion)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Recepción:</th>
                                <td>{{ $contenedor->FechaRecepcion ? \Carbon\Carbon::parse($contenedor->FechaRecepcion)->format('d/m/Y') : 'Pendiente' }}</td>
                            </tr>
                            <tr>
                                <th>País de Origen:</th>
                                <td>{{ $contenedor->Origen ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Estatus:</th>
                                <td><span class="badge bg-{{ $estatusContenedor['clase'] }}">{{ $estatusContenedor['texto'] }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Flete:</th>
                                <td class="fw-bold">$ {{ number_format($contenedor->Flete ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Costo Aduana:</th>
                                <td class="fw-bold">$ {{ number_format($contenedor->Aduana ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Gastos:</th>
                                <td class="fw-bold text-primary">$ {{ number_format(($contenedor->Flete ?? 0) + ($contenedor->Aduana ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <th>Monto Total Facturas:</th>
                                <td class="fw-bold">$ {{ number_format($contenedor->MontoTotalFacturas ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Porcentaje de Gastos:</th>
                                <td class="fw-bold text-success">{{ number_format($contenedor->PorcentajeGastos ?? 0, 2) }} %</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Facturas Asociadas al Contenedor -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-file-text me-2"></i>Facturas Asociadas
                </h3>
                <div class="card-tools">
                    <span class="badge bg-primary">{{ $facturas->count() }} facturas</span>
                </div>
            </div>
            <div class="card-body">
                @if($facturas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>N° Factura</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto USD</th>
                                <th>Estatus</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturas as $factura)
                            @php
                                $estatusFactura = match($factura->Estatus) {
                                    1 => ['texto' => 'En Proceso', 'clase' => 'warning'],
                                    2 => ['texto' => 'Recibiendo', 'clase' => 'info'],
                                    4 => ['texto' => 'Recibida', 'clase' => 'success'],
                                    3 => ['texto' => 'Pagada', 'clase' => 'success'],
                                    0 => ['texto' => 'Anulada', 'clase' => 'danger'],
                                    default => ['texto' => 'Desconocido', 'clase' => 'secondary']
                                };
                            @endphp
                            <tr>
                                <td><strong>{{ $factura->Numero }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}</td>
                                <td class="text-end">$ {{ number_format($factura->MontoDivisa ?? 0, 2) }}</td>
                                <td><span class="badge bg-{{ $estatusFactura['clase'] }}">{{ $estatusFactura['texto'] }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('cpanel.facturas.detalle', $factura->ID) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       title="Ver detalle de factura">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">TOTAL:</td>
                                <td class="text-end fw-bold">$ {{ number_format($facturas->sum('MontoDivisa'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-info text-center mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    No hay facturas asociadas a este contenedor
                </div>
                @endif
            </div>
        </div>
        
    </div>
</div>

@endsection