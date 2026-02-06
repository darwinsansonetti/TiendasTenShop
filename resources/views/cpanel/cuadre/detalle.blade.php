@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Detalles del Cierre de Caja')

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
      <div class="col-sm-6"><h3 class="mb-0">Detalles del Cierre de Caja</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detalles del Cierre de Caja</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::Product Details-->
<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">
      <!-- Título -->
      <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="mb-0 text-primary fw-bold">
              Sucursal: {{ $cierreDiario->SucursalNombre }}
          </h4>

          <span class="badge bg-secondary fs-8">
              <i class="fas fa-calendar-alt me-1"></i>
              {{ $cierreDiario->Fecha->format('d/m/Y') }}
          </span>
      </div>

      <!-- Grid de métricas -->
      <div class="row g-3">

        <div class="col-6 col-md-3">
          <div class="spec-item border rounded p-2 text-center bg-light">
            <span class="spec-label d-block text-muted fw-medium">
              <i class="fas fa-dollar-sign me-1"></i>Total Ventas (Ingreso - Egreso Usd)
            </span>
            <span class="spec-value fw-bold d-block mt-1">
              ${{ number_format($totalGeneralDivisa, 2, ',', '.') }}
            </span>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="spec-item border rounded p-2 text-center bg-light">
            <span class="spec-label d-block text-muted fw-medium">
              <i class="fas fa-barcode me-1"></i>Total Egresos (Bsf)
            </span>
            <span class="spec-value fw-bold d-block mt-1">
              {{ number_format($cierreDiario->EgresoBs, 2, ',', '.') }} Bs
            </span>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="spec-item border rounded p-2 text-center bg-light">
            <span class="spec-label d-block text-muted fw-medium">
              <i class="fas fa-money-bill-wave me-1"></i>Total Ventas (Ingreso - Egreso Bsf)
            </span>
            <span class="spec-value fw-bold d-block mt-1">
              {{ number_format($totalBs, 2, ',', '.') }} Bs
            </span>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="spec-item border rounded p-2 text-center bg-light">
            <span class="spec-label d-block text-muted fw-medium">
              <i class="fas fa-cash-register me-1"></i>Ventas en Sistema (Bsf)
            </span>
            <span class="spec-value fw-bold d-block mt-1">
              {{ number_format($cierreDiario->VentaSistema, 2, ',', '.') }} Bs
            </span>
          </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="col-12 mt-2">
          <div class="border rounded p-3 bg-light">
            <label class="form-label fw-medium text-muted mb-2">
              <i class="fas fa-comment-dots me-1"></i>Observaciones
            </label>
            <textarea
              class="form-control"
              rows="2"
              readonly
            >{{ $cierreDiario->Observacion }}</textarea>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!--begin::Sucursal Products Info-->
  <div class="row mt-4">
      <div class="col-12 mb-4">
        <div class="card shadow-lg border-0 collapsed-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-store"></i>
                    <h4 class="card-title mb-0">
                        Detalles del Cierre de Caja 
                    </h4>
                </div>
                <div class="card-tools ms-auto">
                    <button type="button" class="btn btn-sm btn-light" data-lte-toggle="card-collapse">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-2">
                
              <!-- BLOQUE CON BORDE -->
              <div class="card border rounded-3 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: Información principal -->
                        <div class="col-md-6 border-end">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center" style="min-height: 31px;">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Ventas en Bolívares
                                </h6>
                                
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-exchange-alt text-info"></i>
                                        <span>Efectivo</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->EfectivoBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-mobile-alt text-primary"></i>
                                        <span>Pagos Moviles</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->PagoMovilBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-university text-secondary"></i>
                                        <span>Transferencias</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->TransferenciaBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- Punto de venta (ya existente) -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Punto de venta</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->PuntoDeVentaBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- Cashea -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Cashea</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->CasheaBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- Biopago -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Biopago</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->Biopago, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fw-semibold">
                                        <i class="fas fa-calculator text-primary"></i>
                                        <span>Subtotal</span>
                                    </div>
                                    <div class="info-value fw-bold text-primary">
                                        {{ number_format($totalIngresoBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- EGRESOS -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-danger">
                                        <i class="fas fa-arrow-down"></i>
                                        <span>Egresos</span>
                                    </div>
                                    <div class="info-value fw-semibold text-danger">
                                        - {{ number_format($cierreDiario->EgresoBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>

                                <!-- TOTAL FINAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-3 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fs-6 fw-bold">
                                        <i class="fas fa-wallet text-success"></i>
                                        <span>Total</span>
                                    </div>
                                    <div class="info-value fs-5 fw-bold text-success">
                                        {{ number_format($totalBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Columna derecha: Información de inventario -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2 flex-wrap">
                                    <i class="fas fa-warehouse"></i>
                                    <span>Equivalentes en Divisa</span>

                                    <span class="badge bg-light text-dark border">
                                        Tasa del día: <strong>{{ number_format($cierreDiario->DivisaValor, 2) }} Bsf</strong>
                                    </span>
                                </h6>
                                
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-exchange-alt text-info"></i>
                                        <span>Efectivo (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->EfectivoBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-mobile-alt text-primary"></i>
                                        <span>Pago Móvil (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->PagoMovilBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-university text-secondary"></i>
                                        <span>Transferencias (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->TransferenciaBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Punto de Venta (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->PuntoDeVentaBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Cashea (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->CasheaBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-credit-card text-dark"></i>
                                        <span>Biopago (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->Biopago / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fw-semibold">
                                        <i class="fas fa-calculator text-primary"></i>
                                        <span>Subtotal (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-bold text-primary">
                                        ${{ number_format($totalIngresoBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- EGRESOS -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-danger">
                                        <i class="fas fa-arrow-down"></i>
                                        <span>Egresos (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fw-semibold text-danger">
                                        - ${{ number_format($cierreDiario->EgresoBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- TOTAL FINAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-3 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fs-6 fw-bold">
                                        <i class="fas fa-wallet text-success"></i>
                                        <span>Total (Bsf → Usd)</span>
                                    </div>
                                    <div class="info-value fs-5 fw-bold text-success">
                                        ${{ number_format($totalIngresoBs / $cierreDiario->DivisaValor, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
              </div>

            </div>

            <div class="card-body p-2">
                
              <!-- BLOQUE CON BORDE -->
              <div class="card border rounded-3 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: Información principal -->
                        <div class="col-md-12">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center" style="min-height: 31px;">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Puntos de Venta 
                                </h6>

                                @foreach($cierreDiario->pagosPuntoDeVenta as $PagoPunto)
                                                                   
                                    <div class="info-item d-flex align-items-center justify-content-between py-2">
                                        <div class="info-label d-flex align-items-center gap-2 text-muted">

                                            <!-- LOGO DEL BANCO -->
                                            <img
                                                src="{{ asset('assets/img/bancos/' . $PagoPunto->puntoDeVenta->banco->Logo) }}"
                                                alt="{{ $PagoPunto->puntoDeVenta->banco->Nombre }}"
                                                class="bank-logo-md"
                                            >

                                            <span>
                                                {{ $PagoPunto->puntoDeVenta->banco->Nombre }}
                                                <small class="text-muted">
                                                    ({{ $PagoPunto->puntoDeVenta->Descripcion }})
                                                </small>
                                            </span>
                                        </div>

                                        <div class="info-value fw-semibold">
                                            {{ number_format($PagoPunto->Monto, 2, ',', '.') }} Bsf
                                        </div>
                                    </div>

                                @endforeach

                                <!-- TOTAL FINAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-3 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fs-6 fw-bold">
                                        <i class="fas fa-wallet text-success"></i>
                                        <span>Total</span>
                                    </div>
                                    <div class="info-value fs-5 fw-bold text-success">
                                        {{ number_format($cierreDiario->pagosPuntoDeVenta->sum('Monto'), 2, ',', '.') }} Bsf
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
              </div>
              
            </div>

            <div class="card-body p-2">
                
              <!-- BLOQUE CON BORDE -->
              <div class="card border rounded-3 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: Información principal -->
                        <div class="col-md-12">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center" style="min-height: 31px;">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Ventas en Divisa 
                                </h6>
                                
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-exchange-alt text-info"></i>
                                        <span>Efectivo</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        ${{ number_format($cierreDiario->EfectivoDivisas, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fw-semibold">
                                        <i class="fas fa-calculator text-primary"></i>
                                        <span>Subtotal</span>
                                    </div>
                                    <div class="info-value fw-bold text-primary">
                                        ${{ number_format($cierreDiario->EfectivoDivisas, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- EGRESOS -->
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-danger">
                                        <i class="fas fa-arrow-down"></i>
                                        <span>Egresos</span>
                                    </div>
                                    <div class="info-value fw-semibold text-danger">
                                        - ${{ number_format($cierreDiario->EgresoDivisas, 2, ',', '.') }}
                                    </div>
                                </div>

                                <!-- TOTAL FINAL -->
                                <div class="info-item d-flex align-items-center justify-content-between py-3 border-top mt-2">
                                    <div class="info-label d-flex align-items-center gap-2 fs-6 fw-bold">
                                        <i class="fas fa-wallet text-success"></i>
                                        <span>Total</span>
                                    </div>
                                    <div class="info-value fs-5 fw-bold text-success">
                                        ${{ number_format($totalGeneralDivisa, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
              </div>
              
            </div>

            @if($cierreDiario->CasheaBs > 0)
            <div class="card-body p-2">
                
              <!-- BLOQUE CON BORDE -->
              <div class="card border rounded-3 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: Información principal -->
                        <div class="col-md-12">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center" style="min-height: 31px;">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Ventas por Cashea
                                </h6>
                                
                                
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <!-- LOGO Cashea -->
                                        <img
                                            src="{{ asset('assets/img/bancos/cashea.png') }}"
                                            class="bank-logo-md"
                                        >
                                        <span>Cashea</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->CasheaBs, 2, ',', '.') }} Bsf
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                  </div>
              </div>
              
            </div>
            @endif

            @if($cierreDiario->Biopago > 0)
            <div class="card-body p-2">
                
              <!-- BLOQUE CON BORDE -->
              <div class="card border rounded-3 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="row">
                        <!-- Columna izquierda: Información principal -->
                        <div class="col-md-12">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3 d-flex align-items-center" style="min-height: 31px;">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Ventas por Biopago
                                </h6>
                                
                                
                                <div class="info-item d-flex align-items-center justify-content-between py-2">
                                    <div class="info-label d-flex align-items-center gap-2 text-muted">
                                        <!-- LOGO Biopago -->
                                        <img
                                            src="{{ asset('assets/img/bancos/biopago.png') }}"
                                            class="bank-logo-md"
                                        >
                                        <span>Biopago</span>
                                    </div>
                                    <div class="info-value fw-semibold">
                                        {{ number_format($cierreDiario->Biopago, 2, ',', '.') }} Bsf
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                  </div>
              </div>
              
            </div>
            @endif

        </div>
    </div>
  </div>
  <!--end::Sucursal Products Info-->

  <!-- Resumen Final -->
  <div class="card border-primary border-2 shadow-lg">
      <div class="card-header bg-primary text-white py-3">
          <h5 class="mb-0 fw-bold">
              <i class="fas fa-chart-line me-2"></i>
              Resumen Final del Cierre
          </h5>
      </div>
      <div class="card-body">
          <div class="row g-4">
              <div class="col-md-6">
                  <div class="card border-0 h-100">
                      <div class="card-body">
                          <h6 class="text-muted fw-semibold mb-3">
                              <i class="fas fa-calculator me-2"></i>
                              Cálculos del Día
                          </h6>
                          
                          <div class="list-group list-group-flush">
                              <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                  <span>Equivalencia de Ventas en Bolivares (Bsf → Usd)</span>
                                  <span class="fw-semibold">${{ number_format($totalIngresoBs / $cierreDiario->DivisaValor, 2, ',', '.') }}</span>
                              </div>
                              
                              <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                  <span>+ Ventas en Divisa (Venta - Egreso)</span>
                                  <span class="fw-semibold text-success">${{ number_format($totalGeneralDivisa, 2, ',', '.') }}</span>
                              </div>
                              
                              <div class="list-group-item bg-warning bg-opacity-10 d-flex justify-content-between align-items-center border-0 py-3">
                                  <div class="d-flex align-items-center">
                                      <i class="fas fa-calculator text-warning me-2"></i>
                                      <span class="fw-bold">Subtotal</span>
                                  </div>
                                  <span class="fw-bold text-warning fs-5">
                                      ${{ number_format((($totalIngresoBs / $cierreDiario->DivisaValor) + $totalGeneralDivisa), 2, ',', '.') }}
                                  </span>
                              </div>
                              
                              <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                  <span>- Ventas en Sistema (Bsf → Usd)</span>
                                  <span class="fw-semibold text-danger">- ${{ number_format($cierreDiario->VentaSistema / $cierreDiario->DivisaValor, 2, ',', '.') }}</span>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="col-md-6">
                  <div class="card border-success border-2 h-100">
                      <div class="card-body">
                          <h6 class="text-success fw-semibold mb-3">
                              <i class="fas fa-wallet me-2"></i>
                              Resultado Final
                          </h6>
                          
                          @php
                              $resultado = (
                                  (($totalIngresoBs / $cierreDiario->DivisaValor) + $totalGeneralDivisa)
                                  - ($cierreDiario->VentaSistema / $cierreDiario->DivisaValor)
                              );
                          @endphp
                          
                          <div class="d-flex flex-column align-items-center justify-content-center py-4">
                              <div class="display-5 fw-bold mb-2 {{ $resultado < 0 ? 'text-danger' : 'text-success' }}">
                                  {{ $resultado < 0 ? '-' : '+' }} ${{ number_format(abs($resultado), 2, ',', '.') }}
                              </div>
                              <div class="text-center">
                                  <span class="badge {{ $resultado < 0 ? 'bg-danger' : 'bg-success' }} py-2 px-3 fs-6">
                                      <i class="fas {{ $resultado < 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-1"></i>
                                      {{ $resultado < 0 ? 'Faltante en Caja' : 'Sobrante en Caja' }}
                                  </span>
                                  <p class="text-muted mt-2 mb-0 small">
                                      Diferencia entre caja física y sistema
                                  </p>
                              </div>
                          </div>
                          
                          <div class="mt-4">
                              <div class="progress" style="height: 10px;">
                                  @php
                                      $ventasSistema = $cierreDiario->VentaSistema / $cierreDiario->DivisaValor;
                                      $ventasReales = (($totalIngresoBs / $cierreDiario->DivisaValor) + $totalGeneralDivisa);
                                      $total = max($ventasSistema, $ventasReales);
                                      $porcentajeSistema = $total > 0 ? ($ventasSistema / $total) * 100 : 0;
                                      $porcentajeReales = $total > 0 ? ($ventasReales / $total) * 100 : 0;
                                  @endphp
                                  <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $porcentajeSistema }}%">
                                      Sistema
                                  </div>
                                  <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentajeReales }}%">
                                      Caja Física
                                  </div>
                              </div>
                              <div class="d-flex justify-content-between mt-2">
                                  <small class="text-danger">
                                      <i class="fas fa-cash-register me-1"></i>
                                      Sistema: ${{ number_format($ventasSistema, 2) }}
                                  </small>
                                  <small class="text-success">
                                      <i class="fas fa-wallet me-1"></i>
                                      Caja: ${{ number_format($ventasReales, 2) }}
                                  </small>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
<!--end::Product Details-->

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

    });

    

</script>

<style>
.bank-logo {
    width: 22px;
    height: 22px;
    object-fit: contain;
    border-radius: 4px;
}

.bank-logo-md {
    width: 40px;
    height: 32px;
    object-fit: contain;
    border-radius: 6px;
}

.bank-logo-lg {
    width: 48px;
    height: 38px;
    object-fit: contain;
    padding: 4px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.08);
}

.product-image-container {
    border-radius: 10px;
    overflow: hidden;
    background: #f8f9fa;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    object-fit: contain;
    max-height: 220px;
    width: auto;
    padding: 10px;
}

.product-title {
    font-size: 1.25rem;
    line-height: 1.4;
    color: #2c3e50;
}

.spec-item {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 8px;
    height: 100%;
    transition: background 0.2s ease;
}

.spec-item:hover {
    background: #e9ecef;
}

.spec-label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.spec-value {
    display: block;
    font-size: 1rem;
    color: #495057;
}

.additional-info {
    font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-card {
        padding: 1rem;
    }
    
    .product-image-container {
        min-height: 180px;
    }
    
    .product-image {
        max-height: 180px;
    }
    
    .product-title {
        font-size: 1.1rem;
    }
}

@media (max-width: 576px) {
    .product-card {
        padding: 0.75rem;
    }
    
    .spec-item {
        padding: 0.5rem;
    }
    
    .product-image-container {
        min-height: 150px;
    }
    
    .product-image {
        max-height: 150px;
    }
}

.card {
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: none;
}

.info-item {
    border-bottom: 1px dashed #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label span {
    font-size: 0.9rem;
}

.info-value {
    font-size: 0.95rem;
}

.stat-item {
    min-width: 120px;
}

.border-end {
    border-color: #e9ecef !important;
}

@media (max-width: 768px) {
    .col-md-6.border-end {
        border-right: none !important;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-value {
        text-align: left;
        width: 100%;
    }
    
    .stat-item {
        min-width: 100px;
    }
}
</style>

@endsection