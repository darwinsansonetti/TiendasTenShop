@extends('layout.layout_landing')

@section('title', 'TiensasTenShop')

@section('content')

<div id="carousel-home">
    <div class="owl-carousel owl-theme">
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
                                    <a class="btn_1" href="#" role="button">Explorar colecciones</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/owl-slide-->
        <div class="owl-slide cover" style="background-image: url('{{ asset('assets/img/slides/slide_home_1.jpg') }}');">
            <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.1)">
                <div class="container">
                    <div class="row justify-content-center justify-content-md-start">
                        <div class="col-lg-12 static">
                            <div class="slide-text text-center white">
                                <h2 class="owl-slide-title">
                                    Tiendas Ten Shop
                                </h2>
                                <p class="owl-slide-subtitle">
                                    Descubre los nuevos modelos de calzado que son tendencia
                                </p>
                                <div class="owl-slide-cta">
                                    <a class="btn_1" href="listing-grid-1-full.html" role="button">Lo más nuevo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="icon_drag_mobile"></div>
</div>
<!--/carousel-->

<ul id="banners_grid" class="clearfix">
	<li>
        <a href="#0" class="img_container">
            <img src="{{ asset('assets/img/banners_cat_placeholder.jpg') }}" data-src="{{ asset('assets/img/banner_1.jpg') }}" alt="" class="lazy">
            <div class="short_info opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.7)">
                <h3>Caballeros</h3>
                <div><span class="btn_1">Ver</span></div>
            </div>
        </a>
    </li>
    <li>
        <a href="#0" class="img_container">
            <img src="{{ asset('assets/img/banners_cat_placeholder.jpg') }}" data-src="{{ asset('assets/img/banner_2.jpg') }}" alt="" class="lazy">
            <div class="short_info opacity-mask" data-opacity-mask="rgba(0, 0, 0, 0.7)">
                <h3>Damas</h3>
                <div><span class="btn_1">Ver</span></div>
            </div>
        </a>
    </li>
    <li>
        <a href="#0" class="img_container">
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
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <figure>
                    <span class="ribbon off">-30%</span>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/1.jpg') }}" alt="">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/2.jpg') }}" alt="">
                    </a>
                    <div data-countdown="2019/05/15" class="countdown"></div>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air x Fear</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$48.00</span>
                    <span class="old_price">$60.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon off">-30%</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/2.jpg') }}" alt="">
                    </a>
                    <div data-countdown="2019/05/10" class="countdown"></div>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Okwahn II</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$90.00</span>
                    <span class="old_price">$170.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon off">-50%</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/3.jpg') }}" alt="">
                    </a>
                    <div data-countdown="2019/05/21" class="countdown"></div>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air Wildwood ACG</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$75.00</span>
                    <span class="old_price">$155.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/4.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor ACG React Terra</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$110.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/5.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air Zoom Alpha</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$140.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/6.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air Alpha</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$130.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon hot">Hot</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/7.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air Max 98</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$115.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="grid_item">
                <span class="ribbon hot">Hot</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="img-fluid lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/8.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Armor Air Max 720</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$120.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /col -->
    </div>
    <!-- /row -->
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
                        <a class="btn_1" href="listing-grid-1-full.html" role="button">Ver</a>
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
        <div class="item">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="owl-lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/4.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>ACG React Terra</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$110.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /item -->
        <div class="item">
            <div class="grid_item">
                <span class="ribbon new">New</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="owl-lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/5.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Air Zoom Alpha</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$140.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /item -->
        <div class="item">
            <div class="grid_item">
                <span class="ribbon hot">Hot</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="owl-lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/8.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Air Color 720</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$120.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /item -->
        <div class="item">
            <div class="grid_item">
                <span class="ribbon off">-30%</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="owl-lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/2.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Okwahn II</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$90.00</span>
                    <span class="old_price">$170.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /item -->
        <div class="item">
            <div class="grid_item">
                <span class="ribbon off">-50%</span>
                <figure>
                    <a href="product-detail-1.html">
                        <img class="owl-lazy" src="{{ asset('assets/img/products/product_placeholder_square_medium.jpg') }}" data-src="{{ asset('assets/img/products/shoes/3.jpg') }}" alt="">
                    </a>
                </figure>
                <div class="rating"><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star voted"></i><i class="icon-star"></i></div>
                <a href="product-detail-1.html">
                    <h3>Air Wildwood ACG</h3>
                </a>
                <div class="price_box">
                    <span class="new_price">$75.00</span>
                    <span class="old_price">$155.00</span>
                </div>
                <ul>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to favorites"><i class="ti-heart"></i><span>Add to favorites</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to compare"><i class="ti-control-shuffle"></i><span>Add to compare</span></a></li>
                    <li><a href="#0" class="tooltip-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Add to cart"><i class="ti-shopping-cart"></i><span>Add to cart</span></a></li>
                </ul>
            </div>
            <!-- /grid_item -->
        </div>
        <!-- /item -->
    </div>
    <!-- /products_carousel -->
</div>
<!-- /container -->

<div class="top_line version_1 plus_select" style="background: linear-gradient(to right, #946cc1, #774474, #b73d46);">
    
</div>

<div class="container margin_60_35">
    <div class="main_title">
        <h2>Noticias</h2>
        <span>Blog</span>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <a class="box_news" href="#">
                <figure>
                    <img src="{{ asset('assets/img/blog-thumb-placeholder.jpg') }}" data-src="{{ asset('assets/img/blog-thumb-1.jpg') }}" alt="" width="400" height="266" class="lazy">
                    <figcaption><strong>15</strong>Dic</figcaption>
                </figure>
                <ul>
                    <li>Proximo</li>
                    <li>15.12.2025</li>
                </ul>
                <h4>Stephen Curry 12</h4>
                <p>Parte superior de malla transpirable con revestimientos resistentes para mayor comodidad y control en movimientos dinámicos.....</p>
            </a>
        </div>
        <!-- /box_news -->
        <div class="col-lg-6">
            <a class="box_news" href="blog.html">
                <figure>
                    <img src="{{ asset('assets/img/blog-thumb-placeholder.jpg') }}" data-src="{{ asset('assets/img/blog-thumb-2.jpg') }}" alt="" width="400" height="266" class="lazy">
                    <figcaption><strong>15</strong>Dic</figcaption>
                </figure>
                <ul>
                    <li>Proximo</li>
                    <li>15.12.2025</li>
                </ul>
                <h4>LeBron XXIII "Bubble Boy"</h4>
                <p>Ofrece una responsividad ligera máxima gracias a la espuma ZoomX Foam elástica de largo completo, perfecta para las exigencias de alta velocidad del juego moderno.</p>
            </a>
        </div>
        <!-- /box_news -->
        <div class="col-lg-6">
            <a class="box_news" href="blog.html">
                <figure>
                    <img src="{{ asset('assets/img/blog-thumb-placeholder.jpg') }}" data-src="{{ asset('assets/img/blog-thumb-3.jpg') }}" alt="" width="400" height="266" class="lazy">
                    <figcaption><strong>28</strong>Dec</figcaption>
                </figure>
                <ul>
                    <li>By Luca Robinson</li>
                    <li>20.11.2017</li>
                </ul>
                <h4>Elitr mandamus cu has</h4>
                <p>Cu eum alia elit, usu in eius appareat, deleniti sapientem honestatis eos ex. In ius esse ullum vidisse....</p>
            </a>
        </div>
        <!-- /box_news -->
        <div class="col-lg-6">
            <a class="box_news" href="blog.html">
                <figure>
                    <img src="{{ asset('assets/img/blog-thumb-placeholder.jpg') }}" data-src="{{ asset('assets/img/blog-thumb-4.jpg') }}" alt="" width="400" height="266" class="lazy">
                    <figcaption><strong>28</strong>Dec</figcaption>
                </figure>
                <ul>
                    <li>By Paula Rodrigez</li>
                    <li>20.11.2017</li>
                </ul>
                <h4>Id est adhuc ignota delenit</h4>
                <p>Cu eum alia elit, usu in eius appareat, deleniti sapientem honestatis eos ex. In ius esse ullum vidisse....</p>
            </a>
        </div>
        <!-- /box_news -->
    </div>
    <!-- /row -->
</div>
<!-- /container -->

@endsection

@section('js')

<script>

$(document).ready(function() {
    // Home
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

    // Productos
    $('.products_carousel').owlCarousel({
        loop: true,
        margin: 10,
        nav: true,
        dots: false,
        lazyLoad: true,
        autoplay: true,
        autoplayTimeout: 5000,
        smartSpeed: 800,
        responsive: {
            0: { items: 1 },
            576: { items: 2 },
            768: { items: 3 },
            992: { items: 4 },
            1200: { items: 5 }
        }
    });
});
</script>
@endsection
