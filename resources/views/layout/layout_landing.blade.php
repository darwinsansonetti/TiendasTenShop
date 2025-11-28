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
        <small>¿Que producto estas buscando?</small>
		</div>
		<!-- /header_panel -->
		
		<div class="container">
			<div class="search-input">
					<input type="text" placeholder="Buscar en más de 1.000 productos...">
					<button type="submit"><i class="ti-search"></i></button>
				</div>
		</div>
		<!-- /related -->
	</div>
	<!-- Buscar producto -->

    <!-- Contenido -->
    <main>

        @yield('content')
        
    </main>

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
</body>
</html>
