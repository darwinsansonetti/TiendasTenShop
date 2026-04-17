@extends('layout.layout_dashboard')

@section('title', 'Detalles de la Liberalidad')

@php
    use App\Helpers\FileHelper;
    
    // Definir meses para mostrar
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    // Calcular deducciones
    $deducciones = max(0, ($detalle->MontoLiberalidad ?? 0) + ($detalle->OtraLiberalidad ?? 0) - ($detalle->TotalPagado ?? 0));
@endphp

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Detalles de la Liberalidad</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.dashboard') }}">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.empleados.lista_liberalidad') }}">Liberalidad</a>
                    </li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- ============================================ -->
        <!-- TARJETA DE INFORMACIÓN DEL EMPLEADO -->
        <!-- ============================================ -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-user-circle me-2"></i>
                                Información del Empleado
                            </h3>
                            <div>
                                <a href="{{ route('cpanel.empleados.lista_liberalidad') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Volver al Listado
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                @php
                                    $imgSrc = FileHelper::getOrDownloadFile(
                                        'images/usuarios/',
                                        $fotoPerfil,
                                        'assets/img/adminlte/img/default.png'
                                    );
                                @endphp
                                <img src="{{ $imgSrc }}" 
                                    alt="{{ $nombreCompleto }}"
                                    class="rounded-circle border border-3 border-success img-fluid"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="120">Nombre Completo:</th>
                                                <td><strong>{{ $nombreCompleto }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $email ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sucursal:</th>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-store me-1"></i>{{ $sucursalNombre }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="120">Tipo:</th>
                                                <td>
                                                    @if($detalle->EsVendedor)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-user-tie me-1"></i>Vendedor
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-user me-1"></i>Interno
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($vendedorId)
                                            <tr>
                                                <th>Vendedor ID:</th>
                                                <td><code>{{ $vendedorId }}</code></td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>Período:</th>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $meses[$liberalidad->Mes] ?? $liberalidad->Mes }} {{ $liberalidad->Anno }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>

        <!-- ============================================ -->
        <!-- TARJETAS DE RESUMEN (KPI CARDS) -->
        <!-- ============================================ -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3>{{ number_format($detalle->Unidades ?? 0, 0, ',', '.') }}</h3>
                        <p>Unidades Vendidas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3>$ {{ number_format($detalle->Venta ?? 0, 2, ',', '.') }}</h3>
                        <p>Ventas USD</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3>$ {{ number_format($detalle->MontoLiberalidad ?? 0, 2, ',', '.') }}</h3>
                        <p>Liberalidad USD</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-gradient-dark">
                    <div class="inner">
                        <h3>$ {{ number_format($detalle->TotalPagado ?? 0, 2, ',', '.') }}</h3>
                        <p>Total Pagado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TABLA DE DETALLES DE LIBERALIDAD -->
        <!-- ============================================ -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Desglose de Liberalidad
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="50%">Concepto</th>
                                <th width="50%" class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    <strong>Liberalidad por Ventas</strong>
                                </td>
                                <td class="text-end">
                                    $ {{ number_format($detalle->MontoLiberalidad ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas fa-gift text-info me-2"></i>
                                    <strong>Bonos (Otra Liberalidad)</strong>
                                </td>
                                <td class="text-end text-success">
                                    + $ {{ number_format($detalle->OtraLiberalidad ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="table-danger">
                                <td>
                                    <i class="fas fa-minus-circle text-danger me-2"></i>
                                    <strong>Deducciones</strong>
                                </td>
                                <td class="text-end text-danger">
                                    - $ {{ number_format($deducciones, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="table-success">
                                <td>
                                    <i class="fas fa-calculator me-2"></i>
                                    <strong>TOTAL PAGADO</strong>
                                </td>
                                <td class="text-end fw-bold">
                                    <strong>$ {{ number_format($detalle->TotalPagado ?? 0, 2, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- INFORMACIÓN DE PRÉSTAMOS -->
        <!-- ============================================ -->
        @if(($detalle->AbonoPrestamo ?? 0) > 0 || ($detalle->DeudaPrestamo ?? 0) > 0)
        <div class="card mb-4">
            <div class="card-header bg-gradient-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-hand-holding-usd me-2"></i>Información de Préstamos
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($detalle->AbonoPrestamo > 0)
                    <div class="col-md-6">
                        <div class="info-box bg-gradient-light">
                            <span class="info-box-icon">
                                <i class="fas fa-arrow-down text-success"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Abono a Préstamo</span>
                                <span class="info-box-number">$ {{ number_format($detalle->AbonoPrestamo, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($detalle->DeudaPrestamo > 0)
                    <div class="col-md-6">
                        <div class="info-box bg-gradient-light">
                            <span class="info-box-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Deuda Pendiente</span>
                                <span class="info-box-number">$ {{ number_format($detalle->DeudaPrestamo, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @if($detalle->SaldoFavor > 0)
                <div class="alert alert-success mt-2 mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Saldo a Favor:</strong> $ {{ number_format($detalle->SaldoFavor, 2, ',', '.') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- ============================================ -->
        <!-- DETALLE DE PAGOS -->
        <!-- ============================================ -->
        @if(($detalle->Pago ?? 0) > 0)
        <div class="card mb-4">
            <div class="card-header bg-gradient-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-receipt me-2"></i>Detalle de Pagos
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong><i class="fas fa-check-circle me-2"></i>Pago Realizado:</strong>
                    <span class="float-end">$ {{ number_format($detalle->Pago, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- ============================================ -->
        <!-- MOTIVO (SI EXISTE) -->
        <!-- ============================================ -->
        @if($detalle->Motivo)
        <div class="card mb-4">
            <div class="card-header bg-gradient-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-comment me-2"></i>Motivo
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary mb-0">
                    <i class="fas fa-quote-left me-2"></i>
                    {{ $detalle->Motivo }}
                </div>
            </div>
        </div>
        @endif

        <!-- ============================================ -->
        <!-- BOTONES DE ACCIÓN -->
        <!-- ============================================ -->
        <div class="row">
            <div class="col-md-12 text-end">
                <a href="{{ route('cpanel.empleados.lista_liberalidad') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver al Listado
                </a>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Small Box Cards */
    .small-box {
        border-radius: 12px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        overflow: hidden;
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
    .small-box .inner {
        padding: 15px 20px;
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: bold;
        margin: 0 0 8px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
        margin-bottom: 0;
        opacity: 0.9;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 0;
        font-size: 70px;
        color: rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }
    .small-box:hover .icon {
        transform: scale(1.1);
        color: rgba(255,255,255,0.3);
    }
    
    /* Info Box */
    .info-box {
        display: flex;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: all 0.3s ease;
    }
    .info-box:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    .info-box .info-box-icon {
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        text-align: center;
    }
    .info-box .info-box-content {
        flex: 1;
        padding-left: 15px;
    }
    .info-box .info-box-text {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-box .info-box-number {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0;
        color: #212529;
    }
    
    /* Gradientes */
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #0d6efd 100%) !important;
        color: white;
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white;
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
        color: white;
    }
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%) !important;
        color: white;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        color: white;
    }
    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
        color: white;
    }
    .bg-gradient-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }
    
    /* Tabla */
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
        padding: 12px 15px;
        vertical-align: middle;
    }
    .table-dark {
        background-color: #343a40;
        color: white;
    }
    
    /* Badges */
    .badge {
        padding: 6px 12px;
        font-weight: 500;
        border-radius: 8px;
    }
    
    /* Alertas */
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Animaciones */
    .card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }
    .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .card-header {
        border-bottom: none;
        padding: 15px 20px;
    }
    .card-body {
        padding: 20px;
    }
    
    /* Imagen de perfil */
    .border-3 {
        border-width: 3px !important;
    }
</style>
@endpush