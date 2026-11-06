@extends('layout.layout_landing')

@section('title', $producto->Descripcion . ' - Tiendas TenShop')

@section('content')
<main>
    <div class="container margin_30">
        <div class="row">
            <!-- Imagen del producto -->
            <div class="col-md-6">
                <div class="all">
                    <div class="slider">
                        <div class="owl-carousel owl-theme main">
                            @php
                                $urlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/',
                                    $producto->UrlFoto ?? '',
                                    'assets/img/products/product_placeholder_square_medium.jpg'
                                );
                            @endphp
                            <!-- Ajustamos el tamaño y la proporción de la imagen -->
                            <div class="item d-flex justify-content-center align-items-center" style="height: 400px; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                                <img src="{{ $urlImagen }}" 
                                    alt="{{ $producto->Descripcion }}" 
                                    class="img-fluid" 
                                    style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información del producto -->
            <div class="col-md-6">
                <div class="breadcrumbs">
                    <ul>
                        <li><a href="{{ route('landingpage.index') }}">Inicio</a></li>
                        <li><a href="#">{{ $producto->CategoriaDetectada }}</a></li>
                    </ul>
                </div>
                
                <div class="prod_info">
                    <h4>{{ $producto->Descripcion }}</h4>
                       Referencia: {{ $producto->Referencia ?? 'N/A' }}</p>
                    
                    <div class="prod_options">
                        <div class="row">
                            <label class="col-xl-5 col-lg-5 col-md-6 col-6 pt-0"><strong>Disponibilidad</strong></label>
                            <div class="col-xl-6 col-lg-5 col-md-6 col-6">
                                <span class="badge bg-success">En Stock ({{ $stockTotal }} unidades)</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-xl-5 col-lg-5 col-md-6 col-6"><strong>Sucursales con Stock</strong></label>
                            <div class="col-xl-7 col-lg-5 col-md-6 col-6">
                                @foreach($productosSucursal as $sucursal)
                                    <span class="badge bg-light text-dark border mb-1">
                                        {{ $sucursal->SucursalNombre }}: {{ $sucursal->Existencia }} un.
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /prod_info -->
            </div>
        </div>
    </div>
    
    <!-- Pestañas de Detalles -->
    <div class="tabs_product">
        <div class="container">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a id="tab-A" href="#pane-A" class="nav-link active" data-bs-toggle="tab" role="tab">Detalles y Precios</a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="tab_content_wrapper">
        <div class="container">
            <div class="tab-content" role="tablist">
                <div id="pane-A" class="card tab-pane fade active show" role="tabpanel">
                    <div class="card-header" role="tab">
                        <h5 class="mb-0">
                            <a class="collapsed" data-bs-toggle="collapse" href="#collapse-A" aria-expanded="false">
                                Información Adicional
                            </a>
                        </h5>
                    </div>
                    <div id="collapse-A" class="collapse" role="tabpanel">
                        <div class="card-body">
                            <div class="row justify-content-between">
                                <div class="col-lg-12">
                                    <h3>Precios por Sucursal</h3>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm sizes">
                                            <thead>
                                                <tr>
                                                    <th scope="row" style="width: 40%;">Sucursal</th>
                                                    <th style="width: 30%;">Disponibilidad</th>
                                                    <th class="text-end" style="width: 30%;">Precio (USD)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($productosSucursal as $suc)
                                                    <!-- Solo mostrar si hay stock -->
                                                    @if($suc->Existencia > 0)
                                                    <tr>
                                                        <td>
                                                            <i class="ti-location-pin text-muted me-1"></i> 
                                                            <strong>{{ $suc->SucursalNombre }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($suc->Existencia > 5)
                                                                <span class="badge bg-success">En Stock ({{ $suc->Existencia }})</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">Últimas ({{ $suc->Existencia }})</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end fw-bold" style="font-size: 1.1rem;">
                                                            ---
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Productos Relacionados -->
    @if($relacionados->count() > 0)
    <div class="container margin_60_35">
        <div class="main_title">
            <h2>También te puede interesar</h2>
            <span>Productos</span>
        </div>
        
        <div class="owl-carousel owl-theme products_carousel">
            @foreach($relacionados as $rel)
            <div class="item">
                <div class="grid_item">
                    <figure>
                        <a href="{{ route('landingpage.producto', $rel->ID) }}">
                            @php
                                $urlRel = \App\Helpers\FileHelper::getOrDownloadFile(
                                    'images/items/thumbs/', 
                                    $rel->UrlFoto ?? '', 
                                    'assets/img/products/product_placeholder_square_medium.jpg'
                                );
                            @endphp
                            <!-- Misma estructura de imagen que en el index -->
                            <img class="img-fluid lazy" 
                                src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" 
                                data-src="{{ $urlRel }}" 
                                alt="{{ $rel->Descripcion }}">
                        </a>
                    </figure>
                    <div class="rating">
                        <i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i>
                    </div>
                    <a href="{{ route('landingpage.producto', $rel->ID) }}">
                        <h3>{{ $rel->Descripcion }}</h3>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</main>
@endsection


@section('js')
<script>
// 1. Detectar imágenes rotas (usando delegación de eventos para atrapar todas)
$(document).on('error', 'img', function() {
    // Si la imagen falla y no es ya el placeholder por defecto
    if (!this.src.includes('product_placeholder_square_medium')) {
        this.src = "{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}";
    }
});

$(document).ready(function() {
    // 2. Carrusel de la imagen principal del producto
    $('.main').owlCarousel({
        items: 1,
        loop: false,
        margin: 10,
        nav: true,
        dots: false,
    });

    // 3. Carrusel de productos relacionados (Igual al Home)
    setTimeout(function() {
        var $carousel = $('.products_carousel');
        
        if ($carousel.hasClass('owl-loaded')) {
            $carousel.trigger('destroy.owl.carousel');
        }

        $carousel.owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 3000,
            autoplayHoverPause: true,
            smartSpeed: 800,
            autoplaySpeed: 800,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 3 },
                992: { items: 4 },
                1200: { items: 5 } 
            }
        });
    }, 500); 
});
</script>
@endsection