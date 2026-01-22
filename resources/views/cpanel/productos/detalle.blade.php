@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Ventas por producto')

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
      <div class="col-sm-6">
        <h3 class="mb-0">Detalles del producto</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detalles del producto</li>
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
  <div class="row product-card align-items-start mb-4">
      <!-- Image container -->
      @php
          $urlImagen = FileHelper::getOrDownloadFile(
              'images/items/thumbs/',
              $producto->UrlFoto ?? '',
              'assets/img/adminlte/img/produc_default.jfif'
          );
      @endphp
      
      <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
          <div class="product-image-container position-relative">
              <img src="{{ $urlImagen }}" 
                  alt="{{ $producto->Descripcion ?? 'Imagen del producto' }}" 
                  class="img-fluid rounded shadow-sm product-image"
                  loading="lazy">
              @if(!$producto->UrlFoto)
                  <div class="badge bg-secondary position-absolute top-0 start-0 m-2">
                      Sin imagen
                  </div>
              @endif
          </div>
      </div>

      <!-- Product Info -->
      <div class="col-lg-9 col-md-8">
          <div class="product-details">
              <!-- Title with badge -->
              <div class="d-flex align-items-start justify-content-between mb-2">
                  <h4 class="product-title mb-0 text-primary fw-bold">
                      {{ $producto->Descripcion ?? 'Sin descripción' }}
                  </h4>
                  @if($producto->FechaActualizacion)
                      <span class="badge bg-info ms-2">
                          <i class="fas fa-calendar-alt me-1"></i>
                          Actualizado
                      </span>
                  @endif
              </div>

              <!-- Product specs grid -->
              <div class="row g-2 mb-3">
                  <div class="col-sm-6 col-md-4">
                      <div class="spec-item">
                          <span class="spec-label">
                              <i class="fas fa-barcode me-1 text-muted"></i>
                              Código:
                          </span>
                          <span class="spec-value fw-medium">
                              {{ $producto->Codigo ?? 'N/A' }}
                          </span>
                      </div>
                  </div>
                  
                  <div class="col-sm-6 col-md-4">
                      <div class="spec-item">
                          <span class="spec-label">
                              <i class="fas fa-hashtag me-1 text-muted"></i>
                              Referencia:
                          </span>
                          <span class="spec-value fw-medium">
                              {{ $producto->Referencia ?? 'N/A' }}
                          </span>
                      </div>
                  </div>
                  
                  <div class="col-sm-6 col-md-4">
                      <div class="spec-item">
                          <span class="spec-label">
                              <i class="fas fa-calendar-day me-1 text-muted"></i>
                              Fecha de ingreso:
                          </span>
                          <span class="spec-value fw-medium">
                            @if($producto->FechaCreacion || $producto->FechaActualizacion)
                                @php
                                    $fechaReferencia = $producto->FechaActualizacion ?? $producto->FechaCreacion;
                                    $FechaCreacion = \Carbon\Carbon::parse($fechaReferencia);
                                    $diasTranscurridos = $FechaCreacion->diffInDays(now());
                                    
                                    // Configurar Carbon en español
                                    \Carbon\Carbon::setLocale('es');
                                    $fechaFormateada = $FechaCreacion->isoFormat('D [de] MMMM [de] YYYY');
                                    
                                    // Determinar el color y texto según los días
                                    if ($diasTranscurridos == 0) {
                                        $color = 'success';
                                        $bgColor = 'bg-success';
                                        $icono = 'fas fa-check-circle';
                                        $texto = 'Hoy';
                                    } elseif ($diasTranscurridos <= 3) {
                                        $color = 'success';
                                        $bgColor = 'bg-success';
                                        $icono = 'fas fa-bolt';
                                        $texto = "Hace {$diasTranscurridos} " . ($diasTranscurridos == 1 ? 'día' : 'días');
                                    } elseif ($diasTranscurridos <= 7) {
                                        $color = 'info';
                                        $bgColor = 'bg-info';
                                        $icono = 'fas fa-clock';
                                        $texto = "Hace {$diasTranscurridos} días";
                                    } elseif ($diasTranscurridos <= 15) {
                                        $color = 'warning';
                                        $bgColor = 'bg-warning';
                                        $icono = 'fas fa-calendar-week';
                                        $texto = "Hace " . floor($diasTranscurridos / 7) . " " . (floor($diasTranscurridos / 7) == 1 ? 'semana' : 'semanas');
                                    } elseif ($diasTranscurridos <= 30) {
                                        $color = 'warning';
                                        $bgColor = 'bg-warning';
                                        $icono = 'fas fa-exclamation-triangle';
                                        $texto = "Hace " . floor($diasTranscurridos / 7) . " semanas";
                                    } else {
                                        $meses = floor($diasTranscurridos / 30);
                                        $color = 'danger';
                                        $bgColor = 'bg-danger';
                                        $icono = 'fas fa-calendar-times';
                                        $texto = "Hace {$meses} " . ($meses == 1 ? 'mes' : 'meses');
                                    }
                                @endphp
                                

                                <div class="d-flex flex-column gap-1">
                                    <!-- Fecha en español -->
                                    <span class="text-muted small">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ $fechaFormateada }}
                                    </span>
                                  
                                    <!-- Indicador corregido -->
                                    <span class="badge {{ $bgColor }} text-white d-inline-flex align-items-center gap-2 px-3 py-2" 
                                          style="width: fit-content;">
                                        <i class="{{ $icono }}"></i>
                                        <span class="fw-medium">{{ $texto }}</span>
                                        <small class="opacity-90">({{ $diasTranscurridos }} días)</small>
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-times-circle me-1"></i>
                                    No disponible
                                </span>
                            @endif
                        </span>
                      </div>
                  </div>
              </div>

              <!-- Additional info (if exists) -->
              @if($producto->Observaciones || $producto->Categoria)
                <div class="additional-info mt-3 pt-3 border-top">
                    @if($producto->Categoria)
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-tag me-1"></i>
                                {{ $producto->Categoria }}
                            </span>
                        </div>
                    @endif
                    
                    @if($producto->Observaciones)
                        <div class="observations">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-sticky-note me-1"></i>Observaciones:
                            </small>
                            <p class="mb-0 small">{{ Str::limit($producto->Observaciones, 150) }}</p>
                        </div>
                    @endif
                </div>
              @endif
          </div>
      </div>
  </div>

  <!--begin::Sucursal Products Info-->
  <div class="row mt-4">
      @foreach ($productoSucursal as $item)
          @if ($item)
              @php
                  $productoView = $item['producto'];
                  
                  // Formatear valores
                  $costoDivisa = $productoView->CostoDivisa ?? 0;
                  $pvpBs = $productoView->PvpBs ?? 0;
                  $pvpDivisa = $productoView->PvpDivisa ?? 0;
                  $existencia = $productoView->Existencia ?? 0;
                  
                  // Formatear números
                  $costoDivisaFormatted = number_format($costoDivisa, 2, ',', '.');
                  $pvpBsFormatted = number_format($pvpBs, 2, ',', '.');
                  $pvpDivisaFormatted = number_format($pvpDivisa, 2, ',', '.');
                  $existenciaFormatted = number_format($existencia, 0, ',', '.');
                  
                  // Procesar fechas
                  $fechaActualizacion = $productoView->FechaActualizacion ?? null;
                  $fechaUltimaVenta = $productoView->FechaUltimaVenta ?? null;
                  
                  if ($fechaActualizacion) {
                      $fechaActualizacionCarbon = \Carbon\Carbon::parse($fechaActualizacion);
                      $fechaActualizacionFormatted = $fechaActualizacionCarbon->format('d/m/Y');
                      $diasDesdeActualizacion = $fechaActualizacionCarbon->diffInDays(now());
                  }
                  
                  if ($fechaUltimaVenta) {
                      $fechaUltimaVentaCarbon = \Carbon\Carbon::parse($fechaUltimaVenta);
                      $fechaUltimaVentaFormatted = $fechaUltimaVentaCarbon->format('d/m/Y');
                      $diasDesdeUltimaVenta = $fechaUltimaVentaCarbon->diffInDays(now());
                  }
                  
                  // Calcular margen si hay datos
                  $margen = null;
                  $porcentajeMargen = null;
                  if ($costoDivisa > 0 && $pvpDivisa > 0) {
                      $margen = $pvpDivisa - $costoDivisa;
                      $porcentajeMargen = ($margen / $costoDivisa) * 100;
                  }
                  
                  // Determinar color para existencia
                  $existenciaColor = 'success';
                  $existenciaBadge = '';
                  if ($existencia <= 0) {
                      $existenciaColor = 'danger';
                      $existenciaBadge = '<span class="badge bg-danger ms-2">Agotado</span>';
                  } elseif ($existencia <= 5) {
                      $existenciaColor = 'warning';
                      $existenciaBadge = '<span class="badge bg-warning ms-2">Bajo stock</span>';
                  } elseif ($existencia <= 10) {
                      $existenciaColor = 'info';
                  }
                  
                  // Determinar color para margen
                  $margenColor = 'success';
                  if ($porcentajeMargen !== null) {
                      if ($porcentajeMargen < 15) {
                          $margenColor = 'danger';
                      } elseif ($porcentajeMargen < 30) {
                          $margenColor = 'warning';
                      }
                  }
              @endphp
              
              <div class="col-12 mb-4">
                  <div class="card shadow-lg border-0 collapsed-card">
                      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                          <div class="d-flex align-items-center gap-2">
                              <i class="fas fa-store"></i>
                              <h4 class="card-title mb-0">
                                  Sucursal: {{ $item['sucursal_nombre'] }}
                              </h4>
                          </div>
                          <div class="card-tools ms-auto">
                              <button type="button" class="btn btn-sm btn-light" data-lte-toggle="card-collapse">
                                  <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                  <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                              </button>
                          </div>
                      </div>
                      
                      <div class="card-body p-4">
                          <div class="row">
                              <!-- Columna izquierda: Información principal -->
                              <div class="col-md-6 border-end">
                                  <div class="mb-4">
                                      <h6 class="text-primary border-bottom pb-2 mb-3">
                                          <i class="fas fa-dollar-sign me-2"></i>Información de Precios
                                      </h6>
                                      
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                              <strong>Costo Divisa:</strong>
                                          </div>
                                          <div class="info-value fw-bold text-success">
                                              ${{ $costoDivisaFormatted }}
                                          </div>
                                      </div>
                                      
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-bolivarsign me-2 text-primary"></i>
                                              <strong>Precio Bs:</strong>
                                          </div>
                                          <div class="info-value fw-bold text-primary">
                                              {{ $pvpBsFormatted }} Bs
                                          </div>
                                      </div>
                                      
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-dollar-sign me-2 text-info"></i>
                                              <strong>Precio Divisa:</strong>
                                          </div>
                                          <div class="info-value fw-bold text-info">
                                              ${{ $pvpDivisaFormatted }}
                                          </div>
                                      </div>
                                      
                                      @if ($margen !== null)
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-chart-line me-2 text-{{ $margenColor }}"></i>
                                              <strong>Margen:</strong>
                                          </div>
                                          <div class="info-value fw-bold text-{{ $margenColor }}">
                                              ${{ number_format($margen, 2, ',', '.') }}
                                              <small class="text-muted">({{ number_format($porcentajeMargen, 1, ',', '.') }}%)</small>
                                          </div>
                                      </div>
                                      @endif
                                  </div>
                              </div>
                              
                              <!-- Columna derecha: Información de inventario -->
                              <div class="col-md-6">
                                  <div class="mb-4">
                                      <h6 class="text-primary border-bottom pb-2 mb-3">
                                          <i class="fas fa-warehouse me-2"></i>Información de Inventario
                                      </h6>
                                      
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-boxes me-2 text-{{ $existenciaColor }}"></i>
                                              <strong>Existencia:</strong>
                                          </div>
                                          <div class="info-value fw-bold text-{{ $existenciaColor }}">
                                              {{ $existenciaFormatted }}
                                              {!! $existenciaBadge !!}
                                          </div>
                                      </div>
                                      
                                      <div class="info-item d-flex justify-content-between mb-3">
                                          <div class="info-label">
                                              <i class="fas fa-calendar-day me-2 text-info"></i>
                                              <strong>Última Actualización:</strong>
                                          </div>
                                          <div class="info-value">
                                              @if($fechaActualizacion)
                                                  <div class="d-flex flex-column">
                                                      <span class="fw-semibold">{{ $fechaActualizacionFormatted }}</span>
                                                      <small class="text-muted">
                                                          @if(isset($diasDesdeActualizacion))
                                                              Hace {{ $diasDesdeActualizacion }} {{ $diasDesdeActualizacion == 1 ? 'día' : 'días' }}
                                                          @endif
                                                      </small>
                                                  </div>
                                              @else
                                                  <span class="text-muted">N/A</span>
                                              @endif
                                          </div>
                                      </div>
                                      
                                      <div class="info-item d-flex justify-content-between">
                                          <div class="info-label">
                                              <i class="fas fa-shopping-cart me-2 {{ $fechaUltimaVenta ? 'text-success' : 'text-secondary' }}"></i>
                                              <strong>Fecha Última Venta:</strong>
                                          </div>
                                          <div class="info-value">
                                              @if($fechaUltimaVenta)
                                                  <div class="d-flex flex-column">
                                                      <span class="fw-semibold">{{ $fechaUltimaVentaFormatted }}</span>
                                                      <small class="text-muted">
                                                          @if(isset($diasDesdeUltimaVenta))
                                                              Hace {{ $diasDesdeUltimaVenta }} {{ $diasDesdeUltimaVenta == 1 ? 'día' : 'días' }}
                                                          @endif
                                                      </small>
                                                  </div>
                                              @else
                                                  <span class="text-muted">N/A</span>
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          @endif
      @endforeach
      
      @if(count($productoSucursal) === 0)
      <div class="col-12">
          <div class="alert alert-secondary text-center py-4">
              <i class="fas fa-store-slash fa-2x mb-3 text-muted"></i>
              <h5 class="mb-2">No hay información de sucursales</h5>
              <p class="mb-0">Este producto no está disponible en ninguna sucursal.</p>
          </div>
      </div>
      @endif
  </div>
  <!--end::Sucursal Products Info-->

</div>
<!--end::Product Details-->

@endsection

@section('js')

<style>
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
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    display: flex;
    align-items: center;
}

.info-value {
    text-align: right;
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