@extends('layout.layout_landing')

@section('title', 'Catálogo de Productos - Tiendas TenShop')

@section('css')
<style>
    .ts-catalogo {
        --ts-primary: #004dda;      /* cambia aquí el color principal si usas otro */
        --ts-border: #e9ecef;
        --ts-muted: #6c757d;
        --ts-radius: 10px;
    }

    /* ---------- Banner ---------- */
    .ts-catalogo .ts-banner {
        position: relative;
        border-radius: var(--ts-radius);
        overflow: hidden;
        min-height: 150px;
        display: flex;
        align-items: center;
        padding: 24px 28px;
        color: #fff;
        background: #1f2937 center / cover no-repeat;
    }
    .ts-catalogo .ts-banner::before {
        content: "";
        position: absolute; inset: 0;
        background: rgba(0, 0, 0, .45);
    }
    .ts-catalogo .ts-banner > div { position: relative; }
    .ts-catalogo .ts-banner h1 { color: #fff; font-size: 1.75rem; margin: 6px 0 0; }
    .ts-catalogo .ts-breadcrumb { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; font-size: .85rem; }
    .ts-catalogo .ts-breadcrumb li + li::before { content: "/"; margin-right: 6px; opacity: .7; }
    .ts-catalogo .ts-breadcrumb a { color: #fff; opacity: .85; text-decoration: none; }
    .ts-catalogo .ts-breadcrumb a:hover { opacity: 1; text-decoration: underline; }

    /* ---------- Sidebar ---------- */
    .ts-catalogo .ts-filters {
        background: #fff;
        border: 1px solid var(--ts-border);
        border-radius: var(--ts-radius);
        padding: 18px;
    }
    .ts-catalogo .ts-filters h4 { font-size: 1rem; margin: 0 0 12px; font-weight: 600; }
    .ts-catalogo .ts-cat-list { list-style: none; margin: 0; padding: 0; }
    .ts-catalogo .ts-cat-list a {
        display: flex; align-items: center; justify-content: space-between;
        padding: 9px 12px;
        border-radius: 8px;
        color: #333;
        text-decoration: none;
        font-size: .95rem;
        transition: background .15s;
    }
    .ts-catalogo .ts-cat-list a:hover { background: #f4f6f9; }
    .ts-catalogo .ts-cat-list a.active {
        background: var(--ts-primary);
        color: #fff;
        font-weight: 500;
    }
    @media (min-width: 992px) {
        .ts-catalogo .ts-sticky { position: sticky; top: 90px; }
    }

    /* ---------- Barra de herramientas ---------- */
    .ts-catalogo .ts-toolbar {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        margin: 16px 0 20px;
    }
    .ts-catalogo .ts-toolbar .ts-search { flex: 1 1 240px; position: relative; min-width: 0; }
    .ts-catalogo .ts-toolbar .ts-search input {
        width: 100%; height: 44px;
        border: 1px solid var(--ts-border);
        border-radius: 8px;
        padding: 0 44px 0 14px;
        font-size: .95rem;
    }
    .ts-catalogo .ts-toolbar .ts-search input:focus { outline: none; border-color: var(--ts-primary); }
    .ts-catalogo .ts-toolbar .ts-search button {
        position: absolute; right: 0; top: 0; height: 44px; width: 44px;
        border: 0; background: transparent; color: var(--ts-muted); cursor: pointer;
    }
    .ts-catalogo .ts-toolbar select {
        height: 44px;
        border: 1px solid var(--ts-border);
        border-radius: 8px;
        padding: 0 12px;
        background: #fff;
        font-size: .95rem;
        flex: 0 1 220px;
    }
    .ts-catalogo .ts-btn-filters {
        height: 44px;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 0 14px;
        border: 1px solid var(--ts-border);
        border-radius: 8px;
        background: #fff;
        color: #333;
        cursor: pointer;
    }
    .ts-catalogo .ts-view { display: none; gap: 6px; }
    @media (min-width: 768px) { .ts-catalogo .ts-view { display: inline-flex; } }
    .ts-catalogo .ts-view button {
        width: 44px; height: 44px;
        border: 1px solid var(--ts-border);
        border-radius: 8px;
        background: #fff;
        color: var(--ts-muted);
        cursor: pointer;
    }
    .ts-catalogo .ts-view button.active { color: #fff; background: var(--ts-primary); border-color: var(--ts-primary); }
    .ts-catalogo .ts-count { color: var(--ts-muted); font-size: .9rem; margin: 0 0 14px; }

    /* ---------- Tarjetas ---------- */
    .ts-catalogo .ts-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }
    @media (min-width: 576px) { .ts-catalogo .ts-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1200px) { .ts-catalogo .ts-grid { grid-template-columns: repeat(3, 1fr); } }

    .ts-catalogo .ts-card {
        position: relative;
        display: flex; flex-direction: column;
        background: #fff;
        border: 1px solid var(--ts-border);
        border-radius: var(--ts-radius);
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
    }
    .ts-catalogo .ts-card:hover { box-shadow: 0 8px 22px rgba(0, 0, 0, .09); transform: translateY(-2px); }
    .ts-catalogo .ts-card .ts-img { display: block; background: #f8f9fa; }
    .ts-catalogo .ts-card .ts-img img {
        width: 100%; height: 230px;
        object-fit: contain;
        display: block;
    }
    .ts-catalogo .ts-badge {
        position: absolute; top: 10px; left: 10px; z-index: 1;
        background: #e53935; color: #fff;
        font-size: .75rem; font-weight: 600;
        padding: 3px 9px; border-radius: 20px;
    }
    .ts-catalogo .ts-fav {
        position: absolute; top: 10px; right: 10px; z-index: 1;
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: #fff;
        color: var(--ts-muted);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        text-decoration: none;
    }
    .ts-catalogo .ts-fav:hover { color: #e53935; }
    .ts-catalogo .ts-body { padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; }
    .ts-catalogo .ts-code { font-size: .78rem; color: var(--ts-muted); margin-bottom: 4px; }
    .ts-catalogo .ts-title {
        font-size: 1rem; font-weight: 500; line-height: 1.35;
        margin: 0 0 10px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        min-height: 2.7em;
    }
    .ts-catalogo .ts-title a { color: #222; text-decoration: none; }
    .ts-catalogo .ts-title a:hover { color: var(--ts-primary); }
    .ts-catalogo .ts-desc { display: none; color: var(--ts-muted); font-size: .9rem; margin: 0 0 10px; }
    .ts-catalogo .ts-price { margin-top: auto; margin-bottom: 12px; }
    .ts-catalogo .ts-price .new { font-size: 1.25rem; font-weight: 700; color: #222; }
    .ts-catalogo .ts-price .old { margin-left: 8px; color: var(--ts-muted); text-decoration: line-through; font-size: .9rem; }
    .ts-catalogo .ts-btn {
        display: block; text-align: center;
        background: var(--ts-primary); color: #fff;
        padding: 10px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: opacity .15s;
    }
    .ts-catalogo .ts-btn:hover { opacity: .9; color: #fff; }

    /* ---------- Vista lista (solo escritorio/tablet) ---------- */
    @media (min-width: 768px) {
        /* 1. Forzar a que el contenedor principal sea de 1 sola columna */
        .ts-catalogo .ts-grid.is-list {
            grid-template-columns: 1fr !important;
        }

        /* 2. La tarjeta se vuelve una fila (imagen izq, texto der) */
        .ts-catalogo .ts-grid.is-list .ts-card {
            flex-direction: row !important;
            min-height: 220px;
        }

        /* 3. El enlace interno también debe ser una fila */
        .ts-catalogo .ts-grid.is-list .ts-card-link {
            flex-direction: row !important;
            width: 100%;
        }

        /* 4. Columna de la Imagen: Ancho fijo, alineada a la izquierda */
        .ts-catalogo .ts-grid.is-list .ts-img {
            flex: 0 0 280px !important; /* 280px de ancho fijo */
            width: 280px;
            height: 100%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            border-right: 1px solid var(--ts-border);
            padding: 15px;
        }

        /* 5. La imagen en sí */
        .ts-catalogo .ts-grid.is-list .ts-img img {
            width: 100%;
            height: 100%;
            max-height: 200px;
            object-fit: contain;
        }

        /* 6. Columna de Texto: Ocupa el resto del espacio */
        .ts-catalogo .ts-grid.is-list .ts-body {
            flex: 1;
            padding: 24px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ts-catalogo .ts-grid.is-list .ts-title {
            min-height: 0;
            font-size: 1.2rem;
            margin-bottom: 10px;
            -webkit-line-clamp: 3;
        }

        .ts-catalogo .ts-grid.is-list .ts-desc {
            display: block;
            margin-bottom: 15px;
        }
    }

    /* ---------- Vacío y paginación ---------- */
    .ts-catalogo .ts-empty { text-align: center; padding: 60px 16px; color: var(--ts-muted); }
    .ts-catalogo .ts-empty i { font-size: 3rem; color: #ccc; }
    .ts-catalogo .ts-pagination { display: flex; justify-content: center; margin-top: 30px; }
    .ts-catalogo .ts-pagination .pagination { flex-wrap: wrap; justify-content: center; gap: 4px; margin: 0; }
    .ts-catalogo .ts-pagination .page-link { border-radius: 8px !important; color: var(--ts-primary); border-color: var(--ts-border); }
    .ts-catalogo .ts-pagination .page-item.active .page-link { background: var(--ts-primary); border-color: var(--ts-primary); color: #fff; }

    /* Estilos para que el enlace cubra toda la tarjeta */
    .ts-catalogo .ts-card-link {
        display: flex;
        flex-direction: column;
        flex: 1;
        text-decoration: none;
        color: inherit;
    }
    /* Ajustamos el hover del título para que se vea el efecto de enlace */
    .ts-catalogo .ts-card:hover .ts-title {
        color: var(--ts-primary);
    }
    /* Quitamos la altura mínima del título para que no queden espacios vacíos sin el botón */
    .ts-catalogo .ts-title {
        min-height: auto; 
        margin-bottom: 12px;
    }

    /* --- Paginación Estilo Allaia --- */
    .ts-catalogo .ts-pagination {
        display: flex;
        justify-content: center;
        margin-top: 40px;
    }
    .ts-catalogo .ts-pagination ul.pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 8px;
        align-items: center;
    }
    .ts-catalogo .ts-pagination ul.pagination li a, 
    .ts-catalogo .ts-pagination ul.pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%; /* Hace los botones redondos */
        font-size: 0.95rem;
        color: #444;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .ts-catalogo .ts-pagination ul.pagination li a:hover {
        background-color: #f4f4f4;
        color: var(--ts-primary);
    }
    .ts-catalogo .ts-pagination ul.pagination li.active span,
    .ts-catalogo .ts-pagination ul.pagination li a.active {
        background-color: var(--ts-primary);
        color: #fff;
        font-weight: 500;
    }
    .ts-catalogo .ts-pagination ul.pagination li.disabled span {
        color: #ccc;
        cursor: not-allowed;
    }
    /* Ajuste específico para las flechas (prev/next) */
    .ts-catalogo .ts-pagination ul.pagination li a.prev,
    .ts-catalogo .ts-pagination ul.pagination li a.next {
        font-size: 0.75rem;
        background-color: #fff;
        border: 1px solid var(--ts-border);
    }
</style>
@endsection

@section('content')
@php
    $placeholder = asset('assets/img/products/product_placeholder_square_medium.jpg');
    $categorias  = ['Damas', 'Caballeros', 'Niños', 'Niñas'];
    $ordenActual = request('orden');
@endphp

<div class="container margin_30 ts-catalogo">

    {{-- Banner --}}
    <div class="ts-banner" style="background-image: url('{{ asset('assets/img/banner_2.jpg') }}');">
        <div>
            <ul class="ts-breadcrumb">
                <li><a href="{{ route('landingpage.index') }}">Inicio</a></li>
                <li><a href="{{ route('landingpage.productos') }}">Catálogo</a></li>
                <li>
                    {{ request('categoria') ?? 'Todos' }}
                    @if(request('sucursal_id')) (Filtrado por Sucursal) @endif
                </li>
            </ul>
            <h1>{{ request('categoria') ?? 'Todos los productos' }}</h1>
        </div>
    </div>

    <div class="row mt-4">

        {{-- ===== Sidebar de filtros ===== --}}
        <aside class="col-lg-3 mb-3 mb-lg-0">
            <div class="collapse d-lg-block" id="tsFiltros">
                <div class="ts-filters ts-sticky">

                    {{-- Filtro por Sucursal --}}
                    <h4 style="font-size: 0.95rem; margin-bottom: 12px; font-weight: 600;">Tienda / Sucursal</h4>
                    <form action="{{ route('landingpage.productos') }}" method="GET" class="mb-4 pb-3 border-bottom">
                        {{-- Mantener otros filtros ocultos --}}
                        @if(request('categoria')) <input type="hidden" name="categoria" value="{{ request('categoria') }}"> @endif
                        @if(request('buscar')) <input type="hidden" name="buscar" value="{{ request('buscar') }}"> @endif
                        @if(request('precio_min')) <input type="hidden" name="precio_min" value="{{ request('precio_min') }}"> @endif
                        @if(request('precio_max')) <input type="hidden" name="precio_max" value="{{ request('precio_max') }}"> @endif
                        @if(request('orden')) <input type="hidden" name="orden" value="{{ request('orden') }}"> @endif

                        <select name="sucursal_id" class="form-control form-control-sm" onchange="this.form.submit()" style="font-size: 0.9rem; border-radius: 6px;">
                            <option value="">Todas las tiendas</option>
                            <option value="3" {{ request('sucursal_id') == 3 ? 'selected' : '' }}>Calzatodo Boulevard</option>
                            <option value="4" {{ request('sucursal_id') == 4 ? 'selected' : '' }}>Ten Shop CA</option>
                            <option value="5" {{ request('sucursal_id') == 5 ? 'selected' : '' }}>10&10 CA</option>
                            <option value="7" {{ request('sucursal_id') == 7 ? 'selected' : '' }}>Grupo 1091</option>
                        </select>
                    </form>

                    <h4>Categorías</h4>
                    <ul class="ts-cat-list">
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['categoria' => null, 'page' => null]) }}"
                            class="{{ request('categoria') ? '' : 'active' }}">Todas</a>
                        </li>
                        @foreach($categorias as $cat)
                            <li>
                                <a href="{{ request()->fullUrlWithQuery(['categoria' => $cat, 'page' => null]) }}"
                                class="{{ request('categoria') == $cat ? 'active' : '' }}">{{ $cat }}</a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Filtro de Precio --}}
                    <div class="mt-4 pt-3 border-top">
                        <form action="{{ route('landingpage.productos') }}" method="GET">
                            @if(request('categoria'))
                                <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                            @endif
                            @if(request('buscar'))
                                <input type="hidden" name="buscar" value="{{ request('buscar') }}">
                            @endif
                            @if(request('orden'))
                                <input type="hidden" name="orden" value="{{ request('orden') }}">
                            @endif
                            {{-- IMPORTANTE: Mantener el filtro de sucursal al filtrar por precio --}}
                            @if(request('sucursal_id'))
                                <input type="hidden" name="sucursal_id" value="{{ request('sucursal_id') }}">
                            @endif

                            <div class="d-flex gap-2 mb-2">
                                <input type="hidden" name="precio_min" value="{{ request('precio_min') }}" 
                                    placeholder="Mín" min="0" step="0.01" 
                                    class="form-control form-control-sm" style="font-size: 0.85rem;">
                                <input type="hidden" name="precio_max" value="{{ request('precio_max') }}" 
                                    placeholder="Máx" min="0" step="0.01" 
                                    class="form-control form-control-sm" style="font-size: 0.85rem;">
                            </div>
                            <button type="submit" class="ts-btn" style="padding: 6px 10px; font-size: 0.85rem; width: 100%;">
                                Aplicar
                            </button>
                            
                            @if(request('precio_min') || request('precio_max'))
                                <a href="{{ request()->fullUrlWithQuery(['precio_min' => null, 'precio_max' => null]) }}" 
                                class="btn_1 gray full-width mt-2" style="padding: 6px 10px; font-size: 0.85rem;">
                                    Limpiar Precio
                                </a>
                            @endif
                        </form>
                    </div>

                    {{-- Botón Limpiar Todo --}}
                    @if(request()->hasAny(['categoria', 'buscar', 'orden', 'precio_min', 'precio_max', 'sucursal_id']))
                        <div class="mt-3">
                            <a href="{{ route('landingpage.productos') }}" class="btn_1 gray full-width w-100" style="padding: 8px; font-size: 0.85rem;">Limpiar todo</a>
                        </div>
                    @endif
                </div>
            </div>
        </aside>

        {{-- ===== Listado ===== --}}
        <div class="col-lg-9">

            {{-- Barra: buscador + orden + vista --}}
            <form action="{{ route('landingpage.productos') }}" method="GET" class="ts-toolbar">
                @if(request('categoria'))
                    <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                @endif
                
                {{-- Mantener filtros de precio al buscar --}}
                @if(request('precio_min'))
                    <input type="hidden" name="precio_min" value="{{ request('precio_min') }}">
                @endif
                @if(request('precio_max'))
                    <input type="hidden" name="precio_max" value="{{ request('precio_max') }}">
                @endif

                {{-- IMPORTANTE: Mantener el filtro de sucursal al buscar --}}
                @if(request('sucursal_id'))
                    <input type="hidden" name="sucursal_id" value="{{ request('sucursal_id') }}">
                @endif

                {{-- Forzar búsqueda desde la página 1 --}}
                <input type="hidden" name="page" value="1">

                <div class="ts-search">
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por código o nombre...">
                    <button type="submit" aria-label="Buscar"><i class="ti-search"></i></button>
                </div>

                <select name="orden" onchange="this.form.submit()" aria-label="Ordenar">
                    <option value="" @selected(!$ordenActual)>Ordenar por defecto</option>
                    <option value="precio_asc" @selected($ordenActual == 'precio_asc')>Precio: menor a mayor</option>
                    <option value="precio_desc" @selected($ordenActual == 'precio_desc')>Precio: mayor a menor</option>
                    <option value="nombre" @selected($ordenActual == 'nombre')>Nombre A-Z</option>
                </select>

                <button type="button" class="ts-btn-filters d-lg-none" data-bs-toggle="collapse" data-bs-target="#tsFiltros">
                    <i class="ti-filter"></i> Filtros
                </button>

                <div class="ts-view">
                    <button type="button" id="tsVistaGrid" class="active" title="Cuadrícula"><i class="ti-view-grid"></i></button>
                    <button type="button" id="tsVistaLista" title="Lista"><i class="ti-view-list"></i></button>
                </div>
            </form>

            <p class="ts-count">
                {{ $productos->total() }} {{ $productos->total() == 1 ? 'producto' : 'productos' }}
                @if(request('buscar')) para "<strong>{{ request('buscar') }}</strong>" @endif
            </p>

            @if($productos->count())
                <div class="ts-grid" id="tsGrid">
                    @foreach($productos as $prod)
                        @php
                            $urlImagen = \App\Helpers\FileHelper::getOrDownloadFile(
                                'images/items/thumbs/',
                                $prod->UrlFoto ?? '',
                                'assets/img/products/product_placeholder_square_medium.jpg'
                            );
                            
                            // Verificamos si viene del filtro de sucursal o de la consulta agrupada general
                            if (isset($prod->PrecioMostrar)) {
                                // Viene de la consulta agrupada (Todas las tiendas)
                                $precioFinal = $prod->PrecioMostrar;
                                $precioBase  = $prod->PvpAnterior ?? 0; // Si usamos PvpAnterior para el tachado
                                $enOferta    = false; // En la agrupación general no calculamos el % de descuento exacto
                                $descuento   = 0;
                            } else {
                                // Viene del filtro de sucursal (Consulta directa)
                                $precioFinal = ($prod->NuevoPvp > 0) ? $prod->NuevoPvp : $prod->PvpDivisa;
                                $precioBase  = $prod->PvpDivisa;
                                $enOferta    = $prod->NuevoPvp > 0 && $prod->PvpDivisa > $prod->NuevoPvp;
                                $descuento   = $enOferta ? round((1 - $prod->NuevoPvp / $prod->PvpDivisa) * 100) : 0;
                            }
                            
                            $urlProducto = route('landingpage.producto', $prod->ID);
                        @endphp

                        <article class="ts-card">
                            @if($enOferta && $descuento > 0)
                                <span class="ts-badge">-{{ $descuento }}%</span>
                            @endif

                            <!-- Toda la tarjeta es un enlace -->
                            <a href="{{ $urlProducto }}" class="ts-card-link">
                                <div class="ts-img">
                                    <img src="{{ $urlImagen ?: $placeholder }}" 
                                        alt="{{ $prod->Descripcion }}" 
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                </div>
                                
                                <div class="ts-body">
                                    <h3 class="ts-title">{{ $prod->Descripcion }}</h3>
                                    <p class="ts-desc">Calzado de alta calidad disponible en nuestras tiendas.</p>

                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                <div class="ts-pagination">
                    <ul class="pagination">
                        {{-- Botón Anterior --}}
                        @if ($productos->onFirstPage())
                            <li class="disabled"><span class="prev">&#10094;</span></li>
                        @else
                            <li><a href="{{ $productos->appends(request()->query())->previousPageUrl() }}" class="prev" title="Anterior">&#10094;</a></li>
                        @endif

                        {{-- Números de Página --}}
                        @foreach ($productos->appends(request()->query())->getUrlRange(max(1, $productos->currentPage() - 2), min($productos->lastPage(), $productos->currentPage() + 2)) as $page => $url)
                            @if ($page == $productos->currentPage())
                                <li><span class="active">{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Botón Siguiente --}}
                        @if ($productos->hasMorePages())
                            <li><a href="{{ $productos->appends(request()->query())->nextPageUrl() }}" class="next" title="Siguiente">&#10095;</a></li>
                        @else
                            <li class="disabled"><span class="next">&#10095;</span></li>
                        @endif
                    </ul>
                </div>
            @else
                <div class="ts-empty">
                    <i class="ti-face-sad"></i>
                    <h4 class="mt-3">No encontramos productos</h4>
                    <p>Intenta con otra palabra clave o categoría.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const grid = document.getElementById('tsGrid');
        const btnGrid = document.getElementById('tsVistaGrid');
        const btnLista = document.getElementById('tsVistaLista');
        
        // Si no estamos en la página de productos, no hacer nada
        if (!grid || !btnGrid || !btnLista) return;

        function setVista(lista) {
            grid.classList.toggle('is-list', lista);
            btnLista.classList.toggle('active', lista);
            btnGrid.classList.toggle('active', !lista);
            
            // 🚀 GUARDAR LA PREFERENCIA EN EL NAVEGADOR
            localStorage.setItem('ts_vista_catalogo', lista ? 'lista' : 'grid');
        }

        // 🚀 LEER LA PREFERENCIA AL CARGAR LA PÁGINA (Esto soluciona el problema)
        const vistaGuardada = localStorage.getItem('ts_vista_catalogo');
        if (vistaGuardada === 'lista') {
            setVista(true);
        } else {
            setVista(false);
        }

        // Eventos de clic
        btnGrid.addEventListener('click', () => setVista(false));
        btnLista.addEventListener('click', () => setVista(true));
    })();
</script>
@endsection