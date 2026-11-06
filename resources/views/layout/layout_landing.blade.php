<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Timbrok">

    <title>@yield('title', 'TiendasTenShop')</title>

    <!-- Favicons-->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/x-icon">

    <!-- Favicons-->
    <link rel="apple-touch-icon" type="image/x-icon" href="{{ asset('assets/img/apple-touch-icon-57x57-precomposed.png') }}">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="{{ asset('assets/img/apple-touch-icon-72x72-precomposed.png') }}">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="{{ asset('assets/img/apple-touch-icon-114x114-precomposed.png') }}">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="{{ asset('assets/img/apple-touch-icon-144x144-precomposed.png') }}">
	
    <!-- GOOGLE WEB FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
	<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&amp;display=swap" rel="stylesheet">

    <!-- BASE CSS -->
    <link rel="preload" href="{{ asset('assets/css/bootstrap.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

	<!-- SPECIFIC CSS -->
    <link href="{{ asset('assets/css/home_1.css') }}" rel="stylesheet">

    <!-- YOUR CUSTOM CSS -->
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

    <!-- LANDINGPAGE CSS -->
    <link href="{{ asset('assets/css/landingpage.css') }}" rel="stylesheet">

    @yield('css')

</head>
<body>

    <div id="page">

    <!-- Header -->
    @include('layout.header', ['mensaje' => $mensaje])

    <!-- Buscar producto -->
    <div class="top_panel">
        <div class="container header_panel">
            <a href="#0" class="btn_close_top_panel"><i class="ti-close"></i></a>
            <small>¿Qué producto estás buscando?</small>
        </div>
        <!-- /header_panel -->
        
        <div class="container">
            <form action="{{ route('landingpage.productos') }}" method="GET">
                {{-- Mantener filtros actuales si el usuario está en la página de productos --}}
                @if(request('categoria'))
                    <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                @endif
                @if(request('sucursal_id'))
                    <input type="hidden" name="sucursal_id" value="{{ request('sucursal_id') }}">
                @endif
                @if(request('precio_min'))
                    <input type="hidden" name="precio_min" value="{{ request('precio_min') }}">
                @endif
                @if(request('precio_max'))
                    <input type="hidden" name="precio_max" value="{{ request('precio_max') }}">
                @endif
                @if(request('orden'))
                    <input type="hidden" name="orden" value="{{ request('orden') }}">
                @endif

                <div class="search-input">
                    <input type="text" name="buscar" placeholder="Buscar en más de 1.000 productos..." value="{{ request('buscar') }}" required>
                    <button type="submit"><i class="ti-search"></i></button>
                </div>
            </form>
        </div>
        <!-- /related -->
    </div>
    <!-- Buscar producto -->

    <!-- Contenido -->
    <main>

        @yield('content')
        
    </main>

    {{-- MODAL DE PUBLICIDAD (Solo se pinta si hay una publicidad activa) --}}
    @if(isset($urlPublicidad) && $urlPublicidad)
        <div class="popup_wrapper" id="popupAdvertise" style="display: none;">
            {{-- Mantenemos la clase popup_content para que la plantilla lo centre --}}
            <div class="popup_content" style="background: transparent !important; padding: 0 !important; box-shadow: none !important; max-width: 497px;">
                <span class="popup_close" onclick="cerrarPopupAdvertise()" style="background: #fff; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; top: -10px; right: -10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">X</span>
                <a href="{{ route('landingpage.productos') }}">
                    <img class="img-fluid" src="{{ $urlPublicidad }}" alt="Promoción" width="497" height="495" style="border-radius: 8px; display: block; width: 100%;">
                </a>
            </div>
        </div>
    @endif

    <!-- Footer -->
    @include('layout.footer')

    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Owl Carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!-- COMMON SCRIPTS -->
    <script src="{{ asset('assets/js/common_scripts.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
	
	<!-- SPECIFIC SCRIPTS -->    
    <!-- <script src="{{ asset('assets/js/carousel-home.min.js') }}"></script> -->

    @yield('js')    

    <!-- Contenedor para toasts con z-index más alto -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
        <!-- Los toasts se insertarán aquí dinámicamente -->
    </div>

    <!-- Botón Flotante de WhatsApp -->
    <a href="https://wa.me/584120000000?text=Hola,%20estoy%20interesado%20en%20un%20producto%20de%20Tiendas%20TenShop" 
    class="btn-whatsapp-float" 
    target="_blank" 
    rel="noopener noreferrer"
    aria-label="Contactar por WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <style>
        /* Botón Flotante de WhatsApp */
        .btn-whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 30px;
            right: 30px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 32px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            animation: pulse-whatsapp 2s infinite;
        }

        .btn-whatsapp-float:hover {
            background-color: #128c7e;
            color: #FFF;
            transform: scale(1.1);
            animation: none;
        }

        @keyframes pulse-whatsapp {
            0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
        }

        @media (max-width: 768px) {
            .btn-whatsapp-float {
                width: 50px;
                height: 50px;
                font-size: 26px;
                bottom: 20px;
                right: 20px;
            }
        }

        #toTop {
            display: none !important;
        }
    </style>
</body>
</html>
