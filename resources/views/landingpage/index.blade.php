@extends('layout.layout_landing')

@section('title', 'TiensasTenShop')

@section('content')

<div id="carousel-home">
    <div class="owl-carousel owl-theme">
        @if(isset($bannersActivos) && $bannersActivos->count() > 0)
            @foreach($bannersActivos as $banner)
                <div class="owl-slide cover" style="background-image: url('{{ $banner->UrlImagen }}'); height: 500px; background-size: cover; background-position: center;">
                    
                    {{-- Capa oscura para que el texto resalte --}}
                    <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.3)">
                        <div class="container">
                            
                            {{-- Centrado si es la primera, a la izquierda si son las demás --}}
                            <div class="row {{ $loop->first ? 'justify-content-center' : 'justify-content-md-start' }}">
                                
                                {{-- Si es el primero, ocupa 12 columnas (centrado), si no, 6 columnas --}}
                                <div class="col-lg-{{ $loop->first ? '12' : '6' }} static">
                                    
                                    {{-- Texto centrado en la primera, alineado a la izquierda en las demás --}}
                                    <div class="slide-text white {{ $loop->first ? 'text-center' : 'text-start' }}">
                                        
                                        {{-- Validamos si es el primer banner --}}
                                        @if($loop->first)
                                            <p class="owl-slide-subtitle">
                                                ¡Bienvenido a Tiendas TenShop! Descubre las mejores ofertas en calzado.
                                            </p>
                                            <h2 class="owl-slide-title">Calzado de calidad para todos</h2>
                                        @else
                                            {{-- Textos aleatorios para los siguientes banners --}}
                                            @php
                                                $textos = [
                                                    ['titulo' => 'Renueva tu estilo', 'subtitulo' => 'Encuentra las últimas tendencias en calzado para toda la familia.'],
                                                    ['titulo' => 'Comodidad y Diseño', 'subtitulo' => 'Los mejores modelos para que camines con confianza todo el día.'],
                                                    ['titulo' => 'Ofertas Especiales', 'subtitulo' => 'Aprovecha nuestros descuentos exclusivos en calzado seleccionado.'],
                                                    ['titulo' => 'Camina con Nosotros', 'subtitulo' => 'Descubre por qué somos la tienda favorita de miles de clientes.'],
                                                    ['titulo' => 'Tu próximo par te espera', 'subtitulo' => 'Explora nuestro catálogo y encuentra el calzado perfecto para ti.']
                                                ];
                                                // Selecciona un texto aleatorio
                                                $textoAleatorio = $textos[array_rand($textos)];
                                            @endphp
                                            <h2 class="owl-slide-title">{{ $textoAleatorio['titulo'] }}</h2>
                                            <p class="owl-slide-subtitle">{{ $textoAleatorio['subtitulo'] }}</p>
                                        @endif

                                        <div class="owl-slide-cta">
                                            <a class="btn_1" href="{{ route('landingpage.productos') }}" role="button">Explorar colecciones</a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Carrusel por defecto si no hay banners --}}
            <div class="owl-slide cover" style="background-image: url('{{ asset('assets/img/slides/slide_home_2.jpg') }}');">
                <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.1)">
                    <div class="container">
                        <div class="row justify-content-center justify-content-md-end">
                            <div class="col-lg-12 static">
                                <div class="slide-text text-center white">
                                    <p class="owl-slide-subtitle">
                                        ¡Bienvenido a Tiendas TenShop! Descubre las mejores ofertas en calzado.
                                    </p>
                                    <h2 class="owl-slide-title">Calzado de calidad para todos</h2>
                                    <div class="owl-slide-cta">
                                        <a class="btn_1" href="{{ route('landingpage.productos') }}" role="button">Explorar colecciones</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div id="icon_drag_mobile"></div>
</div>
<!--/carousel-->

<ul id="banners_grid" class="clearfix">
	<li>
        <a href="{{ route('landingpage.productos', ['categoria' => 'Caballeros']) }}" class="img_container">
            <img src="{{ asset('assets/img/banners_cat_placeholder.jpg') }}" data-src="{{ asset('assets/img/banner_1.jpg') }}" alt="" class="lazy">
            <div class="short_info opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.7)">
                <h3>Caballeros</h3>
                <div><span class="btn_1">Ver</span></div>
            </div>
        </a>
    </li>
    <li>
        <a href="{{ route('landingpage.productos', ['categoria' => 'Damas']) }}" class="img_container">
            <img src="{{ asset('assets/img/banners_cat_placeholder.jpg') }}" data-src="{{ asset('assets/img/banner_2.jpg') }}" alt="" class="lazy">
            <div class="short_info opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.7)">
                <h3>Damas</h3>
                <div><span class="btn_1">Ver</span></div>
            </div>
        </a>
    </li>
    <li>
        <a href="{{ route('landingpage.productos', ['categoria' => 'Niños']) }}" class="img_container">
            <img src="{{ asset('assets/img/banners_cat_placeholder.jpg') }}" data-src="{{ asset('assets/img/banner_3.jpg') }}" alt="" class="lazy">
            <div class="short_info opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.7)">
                <h3>Niños</h3>
                <div><span class="btn_1">Ver</span></div>
            </div>
        </a>
    </li>
</ul>
<!--/banners_grid -->

<div class="container margin_60_35">
    <div class="main_title">
        <h2>Lo más vendido</h2>
        <span>Productos</span>
        <p>La tendencia y moda en calzados, lo consigues en Tiendas Ten Shop</p>
    </div>
    
    <div class="row small-gutters">
        @forelse($topTen as $item)
            @php
                // Usamos FileHelper igual que en el cPanel
                $urlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                    'images/items/thumbs/',
                    $item['Producto']['UrlFoto'] ?? '',
                    'assets/img/products/product_placeholder_square_medium.jpg'
                );
                $descripcion = $item['Descripcion'] ?? 'Sin descripción';
            @endphp
            
            <div class="col-6 col-md-4 col-xl-3">
                <div class="grid_item">
                    <span class="ribbon hot">Top</span>
                    <figure>
                        <a href="{{ route('landingpage.producto', $item['Id']) }}">
                            <img class="img-fluid lazy" 
                                 src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" 
                                 data-src="{{ $urlImagen }}" 
                                 alt="{{ $descripcion }}">
                        </a>
                    </figure>
                    <div class="rating">
                        <i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i>
                    </div>
                    <a href="{{ route('landingpage.producto', $item['Id']) }}">
                        <h3>{{ $descripcion }}</h3>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">No hay productos vendidos en los últimos 30 días.</p>
            </div>
        @endforelse
    </div>
</div>
<!-- /container -->

<div class="featured lazy" data-bg="url('{{ asset('assets/img/featured_home.jpg') }}')">
    <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.5)">
        <div class="container margin_60">
            <div class="row justify-content-center justify-content-md-start">
                <div class="col-lg-6 wow" data-wow-offset="150">
                    <h6 style="color: white;">La comodidad se une a la moda</h6>
                    <h2 style="color: white;">Descubre zapatos que lucen geniales y se sienten aún mejor.</h2>
                    <h6 style="color: white;">Nuestra colección incluye calzado cómodo y elegante, diseñado para que tus pies se sientan bien todo el día.</h6>
                    <div class="feat_text_block">
                        <a class="btn_1" href="{{ route('landingpage.productos') }}" role="button">Ver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /featured -->

<div class="container margin_60_35">
    <div class="main_title">
        <h2>Lo más reciente</h2>
        <span>Productos</span>
        <p>Lo último en la moda para caballeros, damas y niños.</p>
    </div>
        <div class="owl-carousel owl-theme products_carousel">
        @foreach($productosRecientes as $prod)
        <div class="item">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    {{-- CORREGIDO: Usar $prod->ID --}}
                    <a href="{{ route('landingpage.producto', $prod->ID) }}">
                        @php
                            $urlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                                'images/items/thumbs/',
                                $prod->UrlFoto ?? '',
                                'assets/img/products/product_placeholder_square_medium.jpg'
                            );
                        @endphp
                        <img class="img-fluid lazy" 
                            src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" 
                            data-src="{{ $urlImagen }}" 
                            alt="{{ $prod->Descripcion }}">
                    </a>
                </figure>
                <div class="rating">
                    <i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i>
                </div>
                {{-- CORREGIDO: Usar $prod->ID --}}
                <a href="{{ route('landingpage.producto', $prod->ID) }}">
                    <h3>{{ $prod->Descripcion }}</h3>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    <!-- /products_carousel -->
</div>
<!-- /container -->

<div class="top_line version_1 plus_select" style="background: linear-gradient(to right, #946cc1, #774474, #b73d46);">
    
</div>

<div class="container margin_60_35">
    <div class="main_title">
        <h2>Explora nuestras colecciones</h2>
        <p>Encuentra el calzado perfecto para cada miembro de la familia</p>
    </div>
    
    <div class="row">
        {{-- Colección 1: Damas --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('landingpage.productos', ['categoria' => 'Damas']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 ts-collection-card" style="border-radius: 10px; overflow: hidden;">
                    <div class="overflow-hidden">
                        <img src="{{ asset('assets/img/dama.jpg') }}" class="card-img-top w-100" alt="Damas" style="height: 220px; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h4 class="fw-bold mb-2 text-dark">Damas</h4>
                        <p class="text-muted small mb-0">Diseños modernos y cómodos para brillar en cada paso.</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Colección 2: Caballeros --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('landingpage.productos', ['categoria' => 'Caballeros']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 ts-collection-card" style="border-radius: 10px; overflow: hidden;">
                    <div class="overflow-hidden">
                        <img src="{{ asset('assets/img/blog-thumb-2.jpg') }}" class="card-img-top w-100" alt="Caballeros" style="height: 220px; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h4 class="fw-bold mb-2 text-dark">Caballeros</h4>
                        <p class="text-muted small mb-0">Estilo, elegancia y resistencia para el día a día.</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Colección 3: Niños --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('landingpage.productos', ['categoria' => 'Niños']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 ts-collection-card" style="border-radius: 10px; overflow: hidden;">
                    <div class="overflow-hidden">
                        <img src="{{ asset('assets/img/blog-thumb-3.jpg') }}" class="card-img-top w-100" alt="Niños" style="height: 220px; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h4 class="fw-bold mb-2 text-dark">Niños</h4>
                        <p class="text-muted small mb-0">Comodidad y resistencia para los más pequeños.</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Colección 4: Niñas --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('landingpage.productos', ['categoria' => 'Niñas']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 ts-collection-card" style="border-radius: 10px; overflow: hidden;">
                    <div class="overflow-hidden">
                        <img src="{{ asset('assets/img/ninas.jpg') }}" class="card-img-top w-100" alt="Niñas" style="height: 220px; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h4 class="fw-bold mb-2 text-dark">Niñas</h4>
                        <p class="text-muted small mb-0">Color y diversión en cada uno de sus pasos.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- Estilos para el efecto zoom y hover en la tarjeta --}}
@push('styles')
<style>
    /* Efecto zoom a la imagen */
    .ts-collection-card:hover .card-img-top {
        transform: scale(1.08);
    }
    
    /* Efecto de elevación al pasar el ratón por la tarjeta */
    .ts-collection-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .ts-collection-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
    }

    /* Efecto para que el título cambie de color al pasar el ratón */
    .ts-collection-card:hover h4 {
        color: #004dda !important; /* Cambia a tu color principal */
        transition: color 0.3s ease;
    }
</style>
@endpush
<!-- /container -->

@endsection

@section('js')

<script>

$(document).ready(function() {
    // 1. Carrusel de inicio (Banner principal)
    $('#carousel-home .owl-carousel').owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        smartSpeed: 800,
        nav: false,
        dots: true,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        lazyLoad: true
    });

    // 2. Carrusel de productos (Lo más reciente)
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

document.addEventListener('DOMContentLoaded', function() {
    
    // Esperamos 2 segundos para que el usuario vea la página primero y luego mostramos el modal
    setTimeout(function() {
        const popup = document.getElementById('popupAdvertise');
        if (popup) {
            // Forzamos la visibilidad por si el CSS de la plantilla interfiere
            popup.style.display = 'block';
            popup.style.opacity = '1';
            popup.style.visibility = 'visible';
            popup.classList.add('show'); 
        }
    }, 2000); // 2000ms = 2 segundos

    // Función para cerrar el popup
    window.cerrarPopupAdvertise = function() {
        const popup = document.getElementById('popupAdvertise');
        if (popup) {
            popup.classList.remove('show');
            // Volvemos a ocultarlo después de la animación
            setTimeout(() => { 
                popup.style.display = 'none'; 
                popup.style.opacity = '0';
                popup.style.visibility = 'hidden';
            }, 300); 
        }
    };
});
</script>
@endsection
