@extends('layout.layout_dashboard')

@section('title', 'Detalle de Pago')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-cash-stack me-2"></i>Detalle de Pago
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedor.mercancia.listado') }}">Proveedores</a>
                    </li>
                    @if($proveedor)
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId) }}">
                            {{ $proveedor->Nombre }}
                        </a>
                    </li>
                    @endif
                    <li class="breadcrumb-item active">Pago #{{ $pago->NumeroOperacion }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle-fill me-2"></i>Información del Pago
                </h3>
                <div class="card-tools">
                    <a href="{{ route('cpanel.proveedores.detalle', $proveedor->ProveedorId ?? 0) }}" 
                       class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th style="width: 140px;">N° Operación:</th>
                                <td><strong>{{ $pago->NumeroOperacion }}</strong></td>
                            </tr>
                            <tr><th>Fecha:</th>
                                <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr><th>Descripción:</th>
                                <td>{{ $pago->Descripcion ?? 'Sin descripción' }}</td>
                            </tr>
                            <tr><th>Forma de Pago:</th>
                                <td>{{ $formaPagoTexto }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th style="width: 140px;">Monto USD:</th>
                                <td class="fw-bold text-success">$ {{ number_format($pago->MontoDivisaAbonado ?? 0, 2) }}</td>
                            </tr>
                            <tr><th>Monto Bs:</th>
                                <td class="fw-bold">Bs {{ number_format($pago->MontoAbonado ?? 0, 2) }}</td>
                            </tr>
                            <tr><th>Tasa de Cambio:</th>
                                <td>{{ number_format($pago->TasaDeCambio ?? 0, 2) }}</td>
                            </tr>
                            <tr><th>Estatus:</th>
                                <td><span class="badge bg-{{ $estatusPago['clase'] }}">{{ $estatusPago['texto'] }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($factura)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-file-text me-2"></i>
                            <strong>Factura asociada:</strong> {{ $factura->Numero }}
                            <br>
                            <small>Este pago se aplicó a la factura #{{ $factura->Numero }}</small>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($pago->UrlComprobante)
                <div class="row mt-3">
                    <div class="col-12">
                        @php
                            // Usar FileHelper para obtener el comprobante (como en productos y proveedores)
                            $comprobanteSrc = FileHelper::getOrDownloadFile(
                                'images/comprobantes/',
                                $pago->UrlComprobante,
                                'assets/img/adminlte/img/no-image.png'
                            );
                        @endphp
                        <div class="alert alert-secondary">
                            <i class="bi bi-file-earmark-image me-2"></i>
                            <strong>Comprobante:</strong>
                            <a href="{{ route('cpanel.pagos.ver-comprobante', $pago->ID) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-info ms-2">
                                <i class="bi bi-eye me-1"></i>Ver Comprobante
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
    </div>
</div>

@endsection