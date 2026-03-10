@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Pendientes por Cerrar')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Información a enviar para el Proximo Cierre</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pendientes por Cerrar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <div class="card-body">
                
            {{-- Iterar sobre cada fecha --}}
            @foreach($ventasAgrupadasPorFecha as $fecha => $datos)
                <div class="card mb-5 fecha-card" data-fecha="{{ $fecha }}">
                    {{-- Cabecera de la fecha --}}
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> 
                            Resumen del día: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </h4>
                    </div>
                    
                    <div class="card-body">
                        {{-- SECCIÓN 1: VENTAS DEL DÍA --}}
                        <h5 class="text-primary">
                            <i class="fas fa-shopping-cart"></i> Ventas del Día Pendientes por Totalizar
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sucursal</th>
                                        <th>Fecha</th>
                                        <th>Unidades</th>
                                        <th>Total USD</th>
                                        <th>Total Bs</th>
                                        <th>Utilidad USD</th>
                                        <th>Margen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datos['ventas'] as $venta)
                                    <tr>
                                        <td>{{ $venta->nombreSucursalOrigen }}</td>
                                        <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $venta->cantidad }}</td>
                                        <td>${{ number_format($venta->totalDivisa, 2) }}</td>
                                        <td>Bs. {{ number_format($venta->totalBs, 2) }}</td>
                                        <td>${{ number_format($venta->utilidadDivisaDiario ?? 0, 2) }}</td>
                                        <td>{{ number_format($venta->margenDivisaDiario ?? 0, 2) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold">
                                    <tr>
                                        <td colspan="3" class="text-right">Totales:</td>
                                        <td>${{ number_format($datos['total_divisa_dia'], 2) }}</td>
                                        <td>Bs. {{ number_format($datos['total_bs_dia'], 2) }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- SECCIÓN 1.5: CIERRES DIARIOS --}}
                        <h5 class="text-primary mt-4">
                            <i class="fas fa-clipboard-check"></i> Cierres Diarios
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Sucursal</th>
                                        <th>Ventas POS (VentaSistema)</th>
                                        <th>Total USD</th>
                                        <th>Total Bs</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalVentasSistema = 0;
                                        $totalUsd = 0;
                                        $totalBsGeneral = 0;
                                    @endphp
                                    
                                    @foreach($datos['ventas'] as $venta)
                                        @if($venta->cierreDiario && $venta->cierreDiario->isNotEmpty())
                                            @php
                                                // Solo hay UN cierre por sucursal
                                                $cierre = $venta->cierreDiario->first();
                                                
                                                // Total USD (EfectivoDivisas)
                                                $usd = (float)($cierre->EfectivoDivisas ?? 0);
                                                
                                                // Total Bs = suma de EfectivoBs + PagoMovilBs + TransferenciaBs + CasheaBs + Biopago + PuntoDeVentaBs
                                                $totalBsCierre = (float)($cierre->EfectivoBs ?? 0) +
                                                                (float)($cierre->PagoMovilBs ?? 0) +
                                                                (float)($cierre->TransferenciaBs ?? 0) +
                                                                (float)($cierre->CasheaBs ?? 0) +
                                                                (float)($cierre->Biopago ?? 0) +
                                                                (float)($cierre->PuntoDeVentaBs ?? 0);
                                                
                                                // Ventas POS (VentaSistema)
                                                $ventasSistema = (float)($cierre->VentaSistema ?? 0);
                                                
                                                // Acumular totales
                                                $totalVentasSistema += $ventasSistema;
                                                $totalUsd += $usd;
                                                $totalBsGeneral += $totalBsCierre;
                                                
                                                // Mapeo de estatus (0=Recibida, 1=Verificada, 2=Contabilizada, 3=Cerrada)
                                                $estatusTexto = '';
                                                $estatusClass = '';
                                                switch((int)($cierre->Estatus ?? 0)) {
                                                    case 0:
                                                        $estatusTexto = 'En Edicion';
                                                        $badgeClass = 'badge bg-secondary text-white';
                                                        break;
                                                    case 1:
                                                        $estatusTexto = 'Nuevo';
                                                        $badgeClass = 'badge bg-info text-white';
                                                        break;
                                                    case 2:
                                                        $estatusTexto = 'Auditoria';
                                                        $badgeClass = 'badge bg-primary text-white';
                                                        break;
                                                    case 3:
                                                        $estatusTexto = 'Contabilizado';
                                                        $badgeClass = 'badge bg-success text-white';  // Fondo verde, texto blanco forzado
                                                        break;
                                                    case 4:
                                                        $estatusTexto = 'Cerrada';
                                                        $badgeClass = 'badge bg-success text-white';  // Fondo verde, texto blanco forzado
                                                        break;
                                                    default:
                                                        $estatusTexto = 'Desconocido';
                                                        $badgeClass = 'badge bg-dark text-white';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $venta->nombreSucursalOrigen }}</td>
                                                <td class="text-right">Bs. {{ number_format($ventasSistema, 2) }}</td>
                                                <td class="text-right">${{ number_format($usd, 2) }}</td>
                                                <td class="text-right">Bs. {{ number_format($totalBsCierre, 2) }}</td>
                                                <td>
                                                    <span>{{ $estatusTexto }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- SECCIÓN 2: FACTURAS --}}
                        @if($datos['Facturas']['Cantidad'] > 0)
                        <h5 class="text-success mt-5">
                            <i class="fas fa-file-invoice"></i> Facturas Pendientes
                        </h5>
                        
                        {{-- Facturas de Mercancía --}}
                        @if($datos['resumen_facturas']['mercancia']['cantidad'] > 0)
                        <div class="card mt-2">
                            <div class="card-header bg-info text-white">
                                <strong>Mercancía</strong> 
                                <span class="badge badge-light">{{ $datos['resumen_facturas']['mercancia']['cantidad'] }} facturas</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th># Factura</th>
                                            <th>Proveedor</th>
                                            <th>Fecha</th>
                                            <th>Total $</th>
                                            <th>Abonado $</th>
                                            <th>Pendiente $</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($datos['resumen_facturas']['mercancia']['facturas'] as $factura)
                                        <tr>
                                            <td>{{ $factura['Factura']['Numero'] ?? 'N/A' }}</td>
                                            <td>{{ $factura['Factura']['Proveedor']['Nombre'] ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($factura['Factura']['Fecha'])->format('d/m/Y') }}</td>
                                            <td>${{ number_format($factura['TotalDivisa'] ?? 0, 2) }}</td>
                                            <td>${{ number_format($factura['TotalAbonadoDivisa'] ?? 0, 2) }}</td>
                                            <td class="font-weight-bold {{ ($factura['TotalSaldoDivisa'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                ${{ number_format($factura['TotalSaldoDivisa'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="3" class="text-right">Totales Mercancía:</th>
                                            <th>${{ number_format($datos['resumen_facturas']['mercancia']['total'], 2) }}</th>
                                            <th>${{ number_format($datos['resumen_facturas']['mercancia']['pagado'], 2) }}</th>
                                            <th>${{ number_format($datos['resumen_facturas']['mercancia']['pendiente'], 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Facturas de Servicios --}}
                        @if($datos['resumen_facturas']['servicios']['cantidad'] > 0)
                        <div class="card mt-3">
                            <div class="card-header bg-warning text-white">
                                <strong>Servicios</strong>
                                <span class="badge badge-light">{{ $datos['resumen_facturas']['servicios']['cantidad'] }} facturas</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th># Factura</th>
                                            <th>Proveedor</th>
                                            <th>Fecha</th>
                                            <th>Total $</th>
                                            <th>Abonado $</th>
                                            <th>Pendiente $</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($datos['resumen_facturas']['servicios']['facturas'] as $factura)
                                        <tr>
                                            <td>{{ $factura['Factura']['Numero'] ?? 'N/A' }}</td>
                                            <td>{{ $factura['Factura']['Proveedor']['Nombre'] ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($factura['Factura']['Fecha'])->format('d/m/Y') }}</td>
                                            <td>${{ number_format($factura['Factura']['MontoDivisa'] ?? 0, 2) }}</td>
                                            <td>${{ number_format($factura['TotalAbonadoDivisa'] ?? 0, 2) }}</td>
                                            <td class="font-weight-bold {{ ($factura['TotalSaldoDivisa'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                ${{ number_format($factura['TotalSaldoDivisa'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="3" class="text-right">Totales Servicios:</th>
                                            <th>${{ number_format($datos['resumen_facturas']['servicios']['total'], 2) }}</th>
                                            <th>${{ number_format($datos['resumen_facturas']['servicios']['pagado'], 2) }}</th>
                                            <th>${{ number_format($datos['resumen_facturas']['servicios']['pendiente'], 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Total General Facturas --}}
                        <div class="alert alert-info mt-2">
                            <strong>Total General Facturas Pendientes:</strong> 
                            ${{ number_format($datos['Facturas']['Monto'], 2) }}
                        </div>
                        @endif
                        
                        {{-- SECCIÓN 3: PAGOS --}}
                        @if(($datos['listado_pagos']['pagos_mercancia']['Monto'] ?? 0) > 0 || ($datos['listado_pagos']['pagos_servicios']['Monto'] ?? 0) > 0)
                        <h5 class="text-primary mt-4">
                            <i class="fas fa-money-bill-wave"></i> Pagos Efectuados
                        </h5>

                        {{-- Pagos Mercancía (cuando SOLO hay mercancía) --}}
                        @if(($datos['listado_pagos']['pagos_mercancia']['Monto'] ?? 0) > 0 && ($datos['listado_pagos']['pagos_servicios']['Monto'] ?? 0) == 0)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    Pagos de Mercancía
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Descripción</th>
                                                <th>Monto $</th>
                                                <th>Monto Bs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($datos['listado_pagos']['pagos_mercancia']['Detalle'] ?? [] as $pago)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                                <td>{{ $pago->Descripcion ?? 'Pago mercancía' }}</td>
                                                <td>${{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                                <td>Bs. {{ number_format($pago->MontoAbonado, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Pagos Servicios (cuando SOLO hay servicios) --}}
                        @elseif(($datos['listado_pagos']['pagos_servicios']['Monto'] ?? 0) > 0 && ($datos['listado_pagos']['pagos_mercancia']['Monto'] ?? 0) == 0)
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    Pagos de Servicios
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Descripción</th>
                                                <th>Monto $</th>
                                                <th>Monto Bs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($datos['listado_pagos']['pagos_servicios']['Detalle'] ?? [] as $pago)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                                <td>{{ $pago->Descripcion ?? 'Pago servicio' }}</td>
                                                <td>${{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                                <td>Bs. {{ number_format($pago->MontoAbonado, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Ambos tipos de pago (mitad y mitad) --}}
                        @else
                        <div class="row">
                            {{-- Pagos Mercancía --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        Pagos a Mercancía
                                        <span class="badge badge-light float-right">
                                            ${{ number_format($datos['listado_pagos']['pagos_mercancia']['Monto'], 2) }}
                                        </span>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Descripción</th>
                                                    <th>Monto $</th>
                                                    <th>Monto Bs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($datos['listado_pagos']['pagos_mercancia']['Detalle'] ?? [] as $pago)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                                    <td>{{ $pago->Descripcion ?? 'Pago mercancía' }}</td>
                                                    <td>${{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                                    <td>Bs. {{ number_format($pago->MontoAbonado, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Pagos Servicios --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        Pagos a Servicios
                                        <span class="badge badge-light float-right">
                                            ${{ number_format($datos['listado_pagos']['pagos_servicios']['Monto'], 2) }}
                                        </span>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Descripción</th>
                                                    <th>Monto $</th>
                                                    <th>Monto Bs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($datos['listado_pagos']['pagos_servicios']['Detalle'] ?? [] as $pago)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                                                    <td>{{ $pago->Descripcion ?? 'Pago servicio' }}</td>
                                                    <td>${{ number_format($pago->MontoDivisaAbonado, 2) }}</td>
                                                    <td>Bs. {{ number_format($pago->MontoAbonado, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        
                        {{-- SECCIÓN 4: PRÉSTAMOS --}}
                        @if(($datos['prestamos'] ?? collect())->isNotEmpty())
                        <h5 class="text-warning mt-4">
                            <i class="fas fa-hand-holding-usd"></i> Préstamos Activos
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Monto $</th>
                                        <th>Monto Bs</th>
                                        <th>Pagado $</th>
                                        <th>Saldo $</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datos['prestamos'] as $prestamo)
                                    <tr>
                                        <td>{{ $prestamo->prestamoId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($prestamo->fecha)->format('d/m/Y') }}</td>
                                        <td>${{ number_format($prestamo->montoDivisa, 2) }}</td>
                                        <td>Bs. {{ number_format($prestamo->montoBs, 2) }}</td>
                                        <td>${{ number_format($prestamo->totalAbonadoDivisa ?? 0, 2) }}</td>
                                        <td class="font-weight-bold {{ ($prestamo->saldoDivisa ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($prestamo->saldoDivisa ?? 0, 2) }}
                                        </td>
                                        <td>
                                            @if($prestamo->estatus == 1)
                                                <span class="badge badge-warning">Nuevo</span>
                                            @elseif($prestamo->estatus == 2)
                                                <span class="badge badge-info">En Proceso</span>
                                            @elseif($prestamo->estatus == 3)
                                                <span class="badge badge-success">Pagado</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                        
                        {{-- SECCIÓN 5: RESUMEN FINANCIERO --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-header bg-dark text-white">
                                        <strong>Resumen Financiero del Día</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card text-white bg-primary mb-3">
                                                    <div class="card-header">Ventas</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">${{ number_format($datos['total_divisa_dia'], 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-white bg-success mb-3">
                                                    <div class="card-header">Pagos</div>
                                                    <div class="card-body">
                                                        @php
                                                            $totalPagos = ($datos['listado_pagos']['pagos_mercancia']['Monto'] ?? 0) + 
                                                                        ($datos['listado_pagos']['pagos_servicios']['Monto'] ?? 0);
                                                        @endphp
                                                        <h5 class="card-title">${{ number_format($totalPagos, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-white bg-warning mb-3">
                                                    <div class="card-header">Préstamos</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">${{ number_format($datos['prestamos']->sum('montoDivisa'), 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-white bg-danger mb-3">
                                                    <div class="card-header">Facturas Pend.</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">${{ number_format($datos['Facturas']['Monto'] ?? 0, 2) }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            @endforeach
            
            {{-- Mensaje si no hay datos --}}
            @if($ventasAgrupadasPorFecha->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No hay ventas pendientes para mostrar.
            </div>
            @endif
            
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Scripts para exportar Excel y PDF -->
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    

</script>
@endsection