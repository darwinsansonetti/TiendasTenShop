@extends('layout.layout_dashboard')

@section('title', 'Detalle de diferencia - Proveedores')

@php
    use Carbon\Carbon;
    
    $hdrBg = 'linear-gradient(135deg,#f59e0b,#d97706)';
    $hdrIcon = 'arrow-left-right';
    $hdrTitle = 'Detalle de diferencia';
    $hdrSubtitle = 'Factura: ' . trim($factura->Numero ?? 'N/A');
    
    $estatusTexto = $factura->Estatus == 3 ? 'Pagada' : ($factura->Estatus == 4 ? 'Recibida' : 'Desconocido');
    $estatusColor = $factura->Estatus == 3 ? 'success' : ($factura->Estatus == 4 ? 'info' : 'secondary');
@endphp

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:{{ $hdrBg }};">
                        <i class="bi bi-{{ $hdrIcon }} text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">{{ $hdrTitle }}</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">{{ $hdrSubtitle }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.proveedor.mercancia.diferencia') }}">Diferencia</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Botón Volver --}}
        <div class="mb-3">
            <a href="{{ route('cpanel.proveedor.mercancia.diferencia') }}" 
               class="btn btn-light border fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver a la lista
            </a>
        </div>

        {{-- Información de la factura --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #0d6efd;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">PROVEEDOR</small>
                        <span class="fw-bold text-dark">{{ $factura->proveedor_nombre ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #6c757d;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">FACTURA</small>
                        <code class="fw-bold" style="color:#d97706;">{{ trim($factura->Numero ?? 'N/A') }}</code>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid #0dcaf0;">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">FECHA</small>
                        <span class="fw-bold text-dark">{{ $factura->FechaCreacion ? Carbon::parse($factura->FechaCreacion)->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left:4px solid {{ $estatusColor == 'success' ? '#198754' : '#0dcaf0' }};">
                    <div class="card-body py-2">
                        <small class="text-muted d-block" style="font-size:0.7rem;">ESTATUS</small>
                        <span class="badge bg-{{ $estatusColor }}">{{ $estatusTexto }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errores de recepción --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3" style="background:{{ $hdrBg }};">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                     Detalles de la factura 
                    @if($erroresRecepcion && $erroresRecepcion->count() > 0)
                        <span class="badge bg-white ms-2 fw-semibold" style="color:#d97706;">
                            {{ $erroresRecepcion->count() }}
                        </span>
                    @endif
                </h6>
            </div>
            <div class="card-body p-0">
                @if($erroresRecepcion && $erroresRecepcion->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th class="ps-4 py-3 text-muted fw-semibold" style="font-size:0.75rem;">CÓDIGO</th>
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">PRODUCTO</th>
                                    <th class="py-3 text-muted fw-semibold" style="font-size:0.75rem;">REFERENCIA</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PEDIDO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">RECIBIDO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">CAJA VACÍA</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE INV.</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">PIE SOLO</th>
                                    <th class="py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">DAÑADO</th>
                                    <th class="pe-4 py-3 text-end text-muted fw-semibold" style="font-size:0.75rem;">DIFERENCIA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalDiferencia = 0; @endphp
                                @foreach($erroresRecepcion as $error)
                                    @php $totalDiferencia += $error->Diferencia ?? 0; @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td class="ps-4">
                                            <code class="px-2 py-1 rounded-2" style="background:#f1f5f9;font-size:0.8rem;color:#d97706;font-weight:bold;">
                                                {{ $error->ProductoCodigo ?? 'N/A' }}
                                            </code>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $error->ProductoDescripcion ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted" style="font-size:0.8rem;">{{ $error->ProductoReferencia ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($error->CantidadPedida ?? 0, 0) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($error->CantidadRecibida ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadCajaVacia ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPieInvertido ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPieSolo ?? 0, 0) }}</td>
                                        <td class="text-end">{{ number_format($error->CantidadPiezaDanada ?? 0, 0) }}</td>
                                        <td class="pe-4 text-end fw-bold text-danger">{{ number_format($error->Diferencia ?? 0, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2">No hay errores de recepción para esta factura</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .card-header { border-radius: 8px 8px 0 0; }
    .table-hover tbody tr:hover { background-color: #f8fafc; }
</style>
@endpush