@extends('layout.layout_dashboard')

@section('title', 'Detalles de la Liberalidad')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Detalles de la Liberalidad</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detalles de la Liberalidad</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line me-2"></i>Detalle de Liberalidad
                            </h3>
                            <div>
                                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Volver
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Información del Empleado -->
                            <div class="col-md-6">
                                <div class="card mb-3 h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user me-2"></i>Información del Empleado
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                @php
                                                    $imgSrc = \App\Helpers\FileHelper::getOrDownloadFile(
                                                        'images/usuarios/',
                                                        $fotoPerfil,
                                                        'assets/img/adminlte/img/default.png'
                                                    );
                                                @endphp
                                                <img src="{{ $imgSrc }}" 
                                                    alt="{{ $nombreCompleto }}"
                                                    class="rounded-circle border border-success"
                                                    style="width: 120px; height: 120px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-8">
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <th width="120">Nombre:</th>
                                                        <td><strong>{{ $nombreCompleto }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email:</th>
                                                        <td style="word-break: break-all; overflow-wrap: break-word;">
                                                            {{ $email ?: 'N/A' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Sucursal:</th>
                                                        <td>{{ $sucursalNombre }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tipo:</th>
                                                        <td>
                                                            @if($detalle->EsVendedor)
                                                                <span class="badge bg-success">Vendedor</span>
                                                            @else
                                                                <span class="badge bg-info">Interno</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del Período -->
                            <div class="col-md-6">
                                <div class="card mb-3 h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>Período de Liberalidad
                                        </h5>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="150">Mes/Año:</th>
                                                <td>{{ $liberalidad->Mes }}/{{ $liberalidad->Anno }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Inicio:</th>
                                                <td>{{ \Carbon\Carbon::parse($liberalidad->FechaInicio)->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Final:</th>
                                                <td>{{ \Carbon\Carbon::parse($liberalidad->FechaFinal)->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Estatus:</th>
                                                <td>
                                                    @if($liberalidad->Estatus == 1)
                                                        <span class="badge bg-success">Cerrado</span>
                                                    @else
                                                        <span class="badge bg-warning">Abierto</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div></br>
                        
                        <!-- Resumen de Liberalidad -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Resumen de Liberalidad
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Concepto</th>
                                                <th></th>
                                                <th>Concepto</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Unidades Vendidas</td>
                                                <td class="text-end fw-bold">{{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}</td>
                                                <td>Ventas USD</td>
                                                <td class="text-end text-success fw-bold">$ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Liberalidad USD</td>
                                                <td class="text-end text-warning fw-bold">$ {{ number_format($detalle->MontoLiberalidad ?? 0, 2, ',', '.') }}</td>
                                                <td>Disponible</td>
                                                <td class="text-end text-info fw-bold">$ {{ number_format($disponible, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Otra Liberalidad</td>
                                                <td class="text-end">$ {{ number_format($detalle->OtraLiberalidad ?? 0, 2, ',', '.') }}</td>
                                                <td>Saldo a Favor</td>
                                                <td class="text-end text-success">$ {{ number_format($detalle->SaldoFavor ?? 0, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Abono a Préstamo</td>
                                                <td class="text-end text-info">$ {{ number_format($detalle->AbonoPrestamo ?? 0, 2, ',', '.') }}</td>
                                                <td>Deuda Préstamo</td>
                                                <td class="text-end text-danger">$ {{ number_format($detalle->DeudaPrestamo ?? 0, 2, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Total Pagado</td>
                                                <td class="text-end">$ {{ number_format($detalle->TotalPagado ?? 0, 2, ',', '.') }}</td>
                                                <td>Pago Realizado</td>
                                                <td class="text-end">$ {{ number_format($detalle->Pago ?? 0, 2, ',', '.') }}</td>
                                            </tr>
                                            @if($detalle->Motivo)
                                            <tr>
                                                <td colspan="4">
                                                    <strong>Motivo:</strong> {{ $detalle->Motivo }}
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detalle de Pagos si existen -->
                        @if(($detalle->Pago ?? 0) > 0 || ($detalle->AbonoPrestamo ?? 0) > 0)
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-money-bill-wave me-2"></i>Detalle de Pagos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if($detalle->Pago > 0)
                                    <div class="col-md-6">
                                        <div class="alert alert-success">
                                            <strong>Pago Realizado:</strong>
                                            <span class="float-end">$ {{ number_format($detalle->Pago, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    @if($detalle->AbonoPrestamo > 0)
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <strong>Abono a Préstamo:</strong>
                                            <span class="float-end">$ {{ number_format($detalle->AbonoPrestamo, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    @if($detalle->DeudaPrestamo > 0)
                                    <div class="col-md-6">
                                        <div class="alert alert-warning">
                                            <strong>Deuda Préstamo:</strong>
                                            <span class="float-end">$ {{ number_format($detalle->DeudaPrestamo, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @if($detalle->Motivo)
                                <div class="alert alert-secondary mt-2">
                                    <strong>Motivo:</strong>
                                    <p class="mb-0 mt-1">{{ $detalle->Motivo }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .info-box .info-box-number {
        margin: 10px 0 0 0;
        font-weight: bold;
    }
    .info-box .info-box-text {
        font-size: 14px;
        font-weight: 500;
        color: #666;
    }
</style>
@endpush