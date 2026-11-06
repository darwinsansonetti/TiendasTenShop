@extends('layout.layout_landing')

@section('title', 'Nosotros - Tiendas TenShop')

@section('content')
<main class="bg_gray">
    {{-- Banner Superior --}}
    <div class="top_banner general">
        <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.4)">
            <div class="container">
                <div class="row justify-content-end">
                    <div class="col-lg-6 col-md-8 text-end">
                        <h1 class="text-white fw-bold">Conoce Nuestra Historia</h1>
                        <p class="text-white lead mb-0">Calzado de alta calidad para toda la familia.</p>
                    </div>
                </div>
            </div>
        </div>
        {{-- Puedes cambiar esta imagen por una de la fachada de tu tienda principal --}}
        <img src="{{ asset('assets/img/slides/banner-1.jpg') }}" class="img-fluid" alt="">
    </div>
    <!--/top_banner-->
    
    <div class="bg_white">
        <div class="container margin_90_0">
            
            {{-- Sección 1: Imagen Izquierda, Texto Derecha --}}
            <div class="row justify-content-between align-items-center content_general_row">
                <div class="col-lg-5 text-center">
                    <figure>
                        <img src="{{ asset('assets/img/blog-3.jpg') }}" alt="" class="img-fluid lazy rounded shadow-sm">
                    </figure>
                </div>
                <div class="col-lg-6">
                    <h2>Más que una tienda, una pasión</h2>
                    <p class="lead">En TenShop nos dedicamos a ofrecer el mejor calzado.</p>
                    <p>Desde nuestros inicios, nos hemos comprometido a brindar calidad, comodidad y estilo a cada uno de nuestros clientes. Contamos con una amplia variedad de marcas y estilos para caballeros, damas y niños, asegurando que encuentres el par perfecto para cada ocasión.</p>
                </div>
            </div>

            {{-- Sección 2: Texto Izquierda, Imagen Derecha --}}
            <div class="row justify-content-between align-items-center flex-lg-row-reverse content_general_row">
                <div class="col-lg-5 text-center">
                    <figure>
                        <img src="{{ asset('assets/img/blog-5.jpg') }}" alt="" class="img-fluid lazy rounded shadow-sm">
                    </figure>
                </div>
                <div class="col-lg-6">
                    <h2>Calidad que se siente en cada paso</h2>
                    <p class="lead">Seleccionamos cuidadosamente cada producto.</p>
                    <p>Nuestro equipo trabaja incansablemente para traer las últimas tendencias y los modelos más cómodos. Creemos que un buen par de zapatos no solo completa un look, sino que también mejora tu día a día.</p>
                    <ul class="list_ok">
                        <li>Materiales de primera calidad</li>
                        <li>Atención personalizada en cada sucursal</li>
                        <li>Variedad para toda la familia</li>
                    </ul>
                </div>
            </div>
            
        </div>
        <!--/container-->
    </div>
    <!--/bg_white-->

    {{-- Sección 3: Nuestras Sucursales --}}
    <div class="container margin_60_35">
        <div class="main_title">
            <h2>Nuestras Sucursales</h2>
            <p>Visítanos en cualquiera de nuestras tiendas a nivel nacional</p>
        </div>
        
        <div class="row">
            @forelse($sucursales as $sucursal)
                <div class="col-lg-4 col-md-6 mb-4">
                    {{-- Envolvemos toda la tarjeta en un enlace --}}
                    <a href="{{ route('landingpage.productos', ['sucursal_id' => $sucursal->ID]) }}" class="text-decoration-none d-block h-100 ts-sucursal-card">
                        <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 10px; overflow: hidden;">
                            
                            {{-- Imagen de la sucursal con animación --}}
                            <div class="overflow-hidden" style="height: 200px;">
                                @if(!empty($sucursal->UrlFoto))
                                    <img src="{{ asset('assets/img/' . $sucursal->UrlFoto) }}" 
                                        class="card-img-top w-100 h-100" 
                                        alt="{{ $sucursal->Nombre }}"
                                        style="object-fit: cover; transition: transform 0.3s ease;">
                                @else
                                    {{-- Imagen por defecto si no hay foto en la BD --}}
                                    <img src="{{ asset('assets/img/blog-1.jpg') }}" 
                                        class="card-img-top w-100 h-100" 
                                        alt="Sucursal"
                                        style="object-fit: cover; transition: transform 0.3s ease;">
                                @endif
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-uppercase mb-3 text-dark">{{ $sucursal->Nombre }}</h5>
                                
                                <p class="card-text text-muted small mb-2">
                                    <i class="ti-location-pin me-1 text-primary"></i> 
                                    {{ $sucursal->Direccion }}
                                </p>
                                
                                <p class="card-text text-muted small mb-0 mt-auto">
                                    <i class="ti-time me-1 text-primary"></i> 
                                    Lun - Sab: 8:30 AM - 5:00 PM
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
            <div class="col-12 text-center py-5">
                <h4>No hay sucursales disponibles por los momentos.</h4>
            </div>
            @endforelse
        </div>
    </div>

</main>
@endsection

@section('js')
{{-- Si usas las animaciones del template original (Lazy Load) --}}
<script>
    // Inicializar cualquier efecto que tengas en tu layout principal
</script>
@endsection