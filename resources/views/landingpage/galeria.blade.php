@extends('layout.layout_landing')

@section('title', 'Galería - Tiendas TenShop')

@section('css')
{{-- CSS Oficial de Magnific Popup --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">

<style>
    /* =========================================
       ESTILOS DE LA GALERÍA (Cuadrícula Moderna)
       ========================================= */
    .galeria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 0;
        list-style: none;
        margin: 0;
    }

    .galeria-item {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        background: #f8f9fa;
        height: 250px;
        display: block;
    }

    .galeria-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
        display: block;
    }

    .galeria-item a {
        display: block;
        width: 100%;
        height: 100%;
        position: relative;
        text-decoration: none;
    }

    .galeria-item:hover img {
        transform: scale(1.08);
    }

    .galeria-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .galeria-item:hover .galeria-overlay {
        opacity: 1;
    }

    .galeria-overlay i {
        color: #fff;
        font-size: 2.5rem;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        transition: transform 0.3s ease;
    }

    .galeria-item:hover .galeria-overlay i {
        transform: scale(1.2);
    }

    /* Ajuste para móviles */
    @media (max-width: 576px) {
        .galeria-grid {
            grid-template-columns: 1fr;
        }
        .galeria-item {
            height: 220px;
        }
    }

    /* =========================================
       ARREGLO DE LAS FLECHAS DUPLICADAS (SOLUCIÓN)
       ========================================= */
    
    /* 1. Ocultamos los íconos originales de Magnific Popup para que no se dupliquen */
    .mfp-arrow-left::before, 
    .mfp-arrow-left::after, 
    .mfp-arrow-right::before, 
    .mfp-arrow-right::after {
        display: none !important; 
    }

    /* 2. Creamos una sola flecha personalizada usando bordes CSS */
    .mfp-arrow-left, 
    .mfp-arrow-right {
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    
    .mfp-arrow-left:hover, 
    .mfp-arrow-right:hover {
        opacity: 1;
    }

    /* Flecha Izquierda */
    .mfp-arrow-left {
        border-left: 3px solid #fff !important;
        border-bottom: 3px solid #fff !important;
        width: 30px !important;
        height: 30px !important;
        transform: rotate(45deg) !important;
        left: 40px !important;
        margin-top: -15px !important;
    }

    /* Flecha Derecha */
    .mfp-arrow-right {
        border-right: 3px solid #fff !important;
        border-bottom: 3px solid #fff !important;
        width: 30px !important;
        height: 30px !important;
        transform: rotate(-45deg) !important;
        right: 40px !important;
        margin-top: -15px !important;
    }

    /* Ocultar flechas de Owl Carousel si se llegan a colar */
    .mfp-container .owl-nav {
        display: none !important;
    }
</style>
@endsection

@section('content')
<main class="bg_gray">
    
    <div class="bg_white">
        <div class="container margin_90_65">
            <div class="main_title">
                <h2>Nuestras Fotos</h2>
                <p>Un vistazo a lo que tenemos para ti en Tiendas TenShop</p>
            </div>
            
            <ul class="galeria-grid">
                @forelse($galerias as $foto)
                    <li class="galeria-item">
                        {{-- El enlace envuelve la imagen y activa el Magnific Popup --}}
                        <a href="{{ $foto->UrlImagen }}" title="Foto TenShop">
                            <img src="{{ $foto->UrlImagen }}" alt="Foto Galería">
                            <div class="galeria-overlay">
                                <i class="bi bi-arrows-angle-expand"></i>
                            </div>
                        </a>
                    </li>
                @empty
                    <div class="col-12 text-center py-5 w-100" style="grid-column: 1 / -1;">
                        <i class="ti-gallery" style="font-size: 3rem; color: #ccc;"></i>
                        <h4 class="mt-3">Aún no hay imágenes en la galería.</h4>
                        <p class="text-muted">¡Vuelve pronto para ver nuestras novedades!</p>
                    </div>
                @endforelse
            </ul>
            
        </div>
    </div>
    
</main>
@endsection

@section('js')
{{-- Script de Magnific Popup --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

<script>
    jQuery(document).ready(function($) {
        $('.galeria-grid').magnificPopup({
            delegate: 'a',
            type: 'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            },
            image: {
                titleSrc: 'title',
                verticalFit: true
            },
            removalDelay: 300,
            mainClass: 'mfp-fade'
        });
    });
</script>
@endsection