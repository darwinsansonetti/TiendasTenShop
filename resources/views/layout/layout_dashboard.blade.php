@php
    use App\Helpers\FileHelper;

    $db = config('database.connections.' . config('database.default') . '.database');
@endphp

<!doctype html>
<html lang="es">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'TiendasTenShop | Dashboard')</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="TiensasTenShop | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content=""
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <!--<link rel="preload" href="{{ asset('assets/css/adminlte.css') }}" />-->  
    <!--end::Accessibility Features-->    

    <!-- Favicons-->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/x-icon">

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />

    <!-- LANDINGPAGE CSS -->
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">

    @yield('css')
  </head>
  <!--end::Head-->
  <!--begin::Body-->

  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

    <!--begin::App Wrapper-->
    <div class="app-wrapper">

      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
                <button class="nav-link border-0 bg-transparent" data-lte-toggle="sidebar" type="button" style="cursor: pointer;">
                    <i class="bi bi-list"></i>
                </button>
            </li>
            <li class="nav-item d-none d-md-block">
                <span id="navbar-sucursal" class="nav-link text-dark">
                    Sucursal
                    @if(session('sucursal_nombre'))
                        : {{ session('sucursal_nombre') }}
                    @endif
                </span>
            </li>
            <!--<li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>-->  
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Tasa del dia-->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi bi-coin"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-3">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Tasa del Día</h6>
                        <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
                    </div>

                    <!-- Tasa Actual -->
                    <div class="alert alert-light py-2 mb-3">
                        <div class="text-center">
                            <div id="tasa-actual-texto" class="h5 mb-1 text-success" data-tasa="{{ $tasa && $tasa['DivisaValor'] ? $tasa['DivisaValor']['Valor'] : 0 }}">
                                @if($tasa && $tasa['DivisaValor'])
                                    {{ number_format($tasa['DivisaValor']['Valor'], 2) }} Bs
                                @else
                                    0.00 Bs
                                @endif
                            </div>
                            <small class="text-muted">Dólar BCV</small>
                        </div>
                    </div>

                    <!-- Dólar Paralelo -->
                    <div class="alert alert-light py-2 mt-2">
                      <div class="text-center">
                        <div id="tasa-actual-texto-paralelo" class="h5 mb-1 text-warning" data-tasa="{{ $paralelo ?? 0 }}">
                            {{ number_format($paralelo ?? 0, 2) }} Bs
                        </div>
                        <small class="text-muted">Dólar Paralelo</small>
                      </div>
                    </div>

                    <!-- Formulario -->
                    <!-- Formulario para actualizar tasa -->
                    <form id="form-tasa-dia" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nueva-tasa" class="form-label small fw-semibold">
                                Ingresa Tasa BCV
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">1 USD =</span>
                                <input type="number" 
                                      class="form-control" 
                                      id="nueva-tasa" 
                                      name="valor"
                                      step="0.01" 
                                      min="0.01" 
                                      placeholder="0.00"
                                      value="{{ $tasa && $tasa['DivisaValor'] ? number_format($tasa['DivisaValor']['Valor'], 2) : '0.00' }}"
                                      required>
                                <span class="input-group-text">Bs</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nueva-tasa-paralelo" class="form-label small fw-semibold">
                                Ingresa Tasa Paralelo
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">1 USD =</span>
                                <input type="number" 
                                      class="form-control" 
                                      id="nueva-tasa-paralelo" 
                                      name="valor_paralelo"
                                      step="0.01" 
                                      min="0.01" 
                                      placeholder="0.00"
                                      value="{{ $paralelo ?? '0.00' }}"
                                      required>
                                <span class="input-group-text">Bs</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-circle me-1"></i>
                                Guardar
                            </button>
                        </div>
                    </form>

                    <!-- Loading -->
                    <div id="loading-tasa" class="text-center mt-2" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <small class="text-muted ms-2">Procesando...</small>
                    </div>
                </div>
            </li>
            <!--end::Notifications Dropdown Menu-->

            <!--begin::Messages Dropdown Menu-->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-chat-square-text"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-3" style="min-width: 350px;">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Publicar publicidad</h6>
                    </div>

                    <!-- Formulario para crear notificación -->
                    <form id="form-notificacion" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea 
                                class="form-control" 
                                id="texto-notificacion" 
                                name="mensaje"
                                rows="3" 
                                maxlength="100"
                                placeholder="Escribe tu publicidad aquí..."
                                oninput="actualizarContador()"
                                required
                            ></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">
                                    <span id="contador-caracteres">0</span>/100 caracteres
                                </small>
                                <small id="indicador-largo" class="text-success" style="display: none;">
                                    <i class="bi bi-check-circle me-1"></i>Bien
                                </small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send-check me-1"></i>Publicar
                            </button>
                        </div>
                    </form>

                    <!-- Loading -->
                    <div id="loading-notificacion" class="text-center mt-2" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <small class="text-muted ms-2">Publicando notificación...</small>
                    </div>
                </div>
            </li>
            <!--end::Messages Dropdown Menu-->

            <!--begin::Sucursales-->
            <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                  <i class="bi bi-building-check"></i>
                  <span class="navbar-badge badge text-bg-danger">
                      {{ $listaSucursales->count() }}
                  </span>
              </a>

              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" style="min-width: 320px;">

                  <!-- Header -->
                  <div class="bg-primary text-white p-3 rounded-top">
                      <div class="d-flex justify-content-between align-items-center">
                          <h6 class="mb-0 fw-bold">Sucursales</h6>
                          <span class="badge bg-white text-primary fs-7">
                              {{ $listaSucursales->where('EsActiva', true)->count() }}/{{ $listaSucursales->count() }} Activas
                          </span>
                      </div>
                  </div>

                  <!-- Lista con scroll -->
                  <div class="dropdown-body" style="max-height: 400px; overflow-y: auto;">

                    @foreach ($listaSucursales as $sucursal)
                    <a href="#" class="dropdown-item py-3 border-bottom seleccionar-sucursal"
                      data-id="{{ $sucursal->ID }}">
                        <div class="d-flex align-items-start">
                            <!-- Icono -->
                            <div class="flex-shrink-0 position-relative">
                                <div class="bg-{{ $sucursal->EsActiva ? 'primary' : 'secondary' }} bg-opacity-10 rounded-circle
                                            d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-shop-window fs-4 text-{{ $sucursal->EsActiva ? 'primary' : 'secondary' }}"></i>
                                </div>

                                <!-- Estado -->
                                <span class="position-absolute top-0 start-100 translate-middle p-1
                                            bg-{{ $sucursal->EsActiva ? 'success' : 'danger' }}
                                            border border-2 border-white rounded-circle"></span>
                            </div>

                            <!-- Información -->
                            <div class="flex-grow-1 ms-3" style="min-width: 0; width: 100%;">
                                <h6 class="fw-bold mb-1 text-dark text-truncate" title="{{ $sucursal->Nombre }}">
                                    {{ $sucursal->Nombre }}
                                </h6>

                                <div class="small text-muted mb-2 direccion-texto"
                                    title="{{ $sucursal->Direccion ?? 'Sin dirección' }}">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $sucursal->Direccion ?? 'Sin dirección' }}
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach

                    {{-- Opción Todas las sucursales --}}
                    <a href="#" class="dropdown-item py-3 seleccionar-sucursal" data-id="0">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-building fs-4 text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-bold mb-1 text-dark">Todas las sucursales</h6>
                                <div class="small text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Ver datos de todas las sucursales
                                </div>
                            </div>
                        </div>
                    </a>

                </div>

              </div>
            </li>

            <!--end::Messages Dropdown Menu-->

            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">

              @php

                  // Nombre del archivo (puede ser '' o null)
                  $fotoPerfil = $user->FotoPerfil ?? '';

                  // Usamos el helper genérico
                  $imgSrc = FileHelper::getOrDownloadFile(
                      'images/usuarios/',                          // Carpeta
                      $fotoPerfil,                                 // Archivo
                      'assets/img/adminlte/img/avatar4.png'        // Default
                  );
              @endphp

              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="{{ $imgSrc }}"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline">{{ $user->NombreCompleto }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="{{ $imgSrc }}"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    {{ $user->NombreCompleto }}
                    <small>{{ $user->Email }}</small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->
                  <div class="d-flex gap-2 justify-content-between">
                      <!-- Botón Perfil -->
                      <a href="#" class="btn btn-outline-primary btn-sm flex-fill">
                          <i class="fas fa-user me-1"></i>Perfil
                      </a>
                  </div>
                  <!--end::Row-->
                </li>
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer p-3">
                    <div class="d-flex gap-2 justify-content-between">
                        <!-- Botón Salir -->
                        <a href="#" 
                          class="btn btn-danger btn-sm flex-fill"
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-1"></i>Salir
                        </a>
                    </div>

                    <!-- Formulario oculto para cerrar sesión -->
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="{{ route('cpanel.dashboard') }}" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="{{ asset('assets/img/TSCirculo.png') }}" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">TiendasTenShop</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>        

        @if(Str::contains($db, 'db_a509ee_tenshop2026'))
          <span class="badge bg-warning text-dark"
                data-bs-toggle="tooltip"
                data-bs-placement="left"
                title="Base de datos activa: {{ $db }}">
              ⚠ DEVELOPER
          </span>
        @endif
        
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a href="{{ route('cpanel.dashboard') }}" class="nav-link {{ session('menu_active') == 'Inicio' ? 'active' : '' }}">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Inicio</p>
                </a>
              </li>
              <li class="nav-item {{ session('menu_active') == 'Informes - Resumen' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon  bi bi-clipboard-fill"></i>
                  <p>
                    Informes - Resumen
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.resumen.ventas') }}" class="nav-link {{ session('submenu_active') == 'Resumen de ventas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Resumen de ventas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.estado.cuentas') }}" class="nav-link {{ session('submenu_active') == 'Estado de cuentas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Estado de cuentas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.comparativa.sucursales') }}" class="nav-link {{ session('submenu_active') == 'Comparativa' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Comparativa Sucursales</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.indice.rotacion') }}" class="nav-link {{ session('submenu_active') == 'Indice de Rotación' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Indice de Rotación</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.baja.ventas') }}" class="nav-link {{ session('submenu_active') == 'Baja Demanda' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Baja Demanda</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item {{ session('menu_active') == 'Análisis de Ventas' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-shop"></i>
                  <p>
                    Análisis de Ventas
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.ventas.diarias') }}" class="nav-link {{ session('submenu_active') == 'Ventas Diarias' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Ventas Diarias</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.ventas.producto') }}" class="nav-link {{ session('submenu_active') == 'Ventas por producto' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Ventas por producto</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cargar.ventas.diarias') }}" class="nav-link {{ session('submenu_active') == 'Cargar Venta Diaria' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Cargar Ventas Diarias</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item {{ session('menu_active') == 'Cuadre de Caja' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-shop"></i>
                  <p>
                    Cuadre de Caja
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.resumen_diario') }}" class="nav-link {{ session('submenu_active') == 'Resumen Diario' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Resumen Diario</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.registrar_cierre') }}" class="nav-link {{ session('submenu_active') == 'Registrar Cierre' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Registrar Cierre</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-seam-fill"></i>
                  <p>
                    Widgets
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./widgets/small-box.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Small Box</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./widgets/info-box.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>info Box</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./widgets/cards.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Cards</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-fill"></i>
                  <p>
                    Layout Options
                    <span class="nav-badge badge text-bg-secondary me-3">6</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./layout/unfixed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Default Sidebar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Sidebar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-header.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Header</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-footer.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Footer</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-complete.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Complete</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/layout-custom-area.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Layout <small>+ Custom Area </small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/sidebar-mini.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/collapsed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini <small>+ Collapsed</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/logo-switch.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini <small>+ Logo Switch</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/layout-rtl.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Layout RTL</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-tree-fill"></i>
                  <p>
                    UI Elements
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./UI/general.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>General</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/icons.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Icons</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/timeline.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Timeline</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-pencil-square"></i>
                  <p>
                    Forms
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./forms/general.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>General Elements</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-table"></i>
                  <p>
                    Tables
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./tables/simple.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Simple Tables</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-header">EXAMPLES</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-arrow-in-right"></i>
                  <p>
                    Auth
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 1
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 2
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="./examples/lockscreen.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Lockscreen</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-header">DOCUMENTATIONS</li>
              <li class="nav-item">
                <a href="./docs/introduction.html" class="nav-link">
                  <i class="nav-icon bi bi-download"></i>
                  <p>Installation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docs/layout.html" class="nav-link">
                  <i class="nav-icon bi bi-grip-horizontal"></i>
                  <p>Layout</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docs/color-mode.html" class="nav-link">
                  <i class="nav-icon bi bi-star-half"></i>
                  <p>Color Mode</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-ui-checks-grid"></i>
                  <p>
                    Components
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./docs/components/main-header.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Main Header</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./docs/components/main-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Main Sidebar</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-filetype-js"></i>
                  <p>
                    Javascript
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./docs/javascript/treeview.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Treeview</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="./docs/browser-support.html" class="nav-link">
                  <i class="nav-icon bi bi-browser-edge"></i>
                  <p>Browser Support</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docs/how-to-contribute.html" class="nav-link">
                  <i class="nav-icon bi bi-hand-thumbs-up-fill"></i>
                  <p>How To Contribute</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docs/faq.html" class="nav-link">
                  <i class="nav-icon bi bi-question-circle-fill"></i>
                  <p>FAQ</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docs/license.html" class="nav-link">
                  <i class="nav-icon bi bi-patch-check-fill"></i>
                  <p>License</p>
                </a>
              </li>
              <li class="nav-header">MULTI LEVEL EXAMPLE</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>Level 1</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>
                    Level 1
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Level 2</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>
                        Level 2
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Level 2</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>Level 1</p>
                </a>
              </li>
              <li class="nav-header">LABELS</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-danger"></i>
                  <p class="text">Important</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-warning"></i>
                  <p>Warning</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-info"></i>
                  <p>Informational</p>
                </a>
              </li>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        
        @yield('content')

      </main>
      <!--end::App Main-->

      <div id="revenue-chart" style="display:none;"></div>
      <div id="world-map" style="display:none;"></div>
      <div id="sparkline-1" style="display:none;"></div>
      <div id="sparkline-2" style="display:none;"></div>
      <div id="sparkline-3" style="display:none;"></div>

      <!--begin::Footer-->
      <footer class="app-footer">
        
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2025&nbsp;
          TiendasTenShop
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!--Toast-->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
        <!-- Los toasts se insertarán aquí dinámicamente -->
    </div>

    @if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('warning') }}', 'warning');
        });
    </script>
    @endif

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
    @endif






    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('assets/js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->
    <script>
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <!-- ChartJS -->
    
    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>
    <!-- jsvectormap -->
    <script>
      // World map by jsVectorMap
      new jsVectorMap({
        selector: '#world-map',
        map: 'world',
      });

      // Sparkline charts
      const option_sparkline1 = {
        series: [
          {
            data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
      sparkline1.render();

      const option_sparkline2 = {
        series: [
          {
            data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
      sparkline2.render();

      const option_sparkline3 = {
        series: [
          {
            data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
      sparkline3.render();
    </script>
    <!--end::Script-->

    <script>
      
      // Función para mostrar toasts
      function showToast(message, type = 'success') {
          const toastId = 'toast-' + Date.now();
          const toastHtml = `
              <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" 
                  role="alert" aria-live="assertive" aria-atomic="true" 
                  data-bs-delay="5000" style="z-index: 99999;">
                  <div class="d-flex">
                      <div class="toast-body">
                          <i class="bi ${getToastIcon(type)} me-2"></i>
                          ${message}
                      </div>
                      <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                              data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
              </div>
          `;
          
          const toastContainer = document.getElementById('toast-container');
          toastContainer.insertAdjacentHTML('beforeend', toastHtml);
          
          const toastElement = document.getElementById(toastId);
          const toast = new bootstrap.Toast(toastElement);
          toast.show();
          
          // Remover del DOM cuando se oculte
          toastElement.addEventListener('hidden.bs.toast', function () {
              toastElement.remove();
          });
      }

      // Iconos para diferentes tipos de toast
      function getToastIcon(type) {
          const icons = {
              success: 'bi-check-circle-fill',
              danger: 'bi-exclamation-triangle-fill',
              warning: 'bi-exclamation-circle-fill',
              info: 'bi-info-circle-fill'
          };
          return icons[type] || 'bi-info-circle-fill';
      }

      // Guardar Tasa del Dia
      document.getElementById('form-tasa-dia').addEventListener('submit', function(e) {
        e.preventDefault();

        const valor = document.getElementById('nueva-tasa').value;
        const valorParalelo = document.getElementById('nueva-tasa-paralelo').value;
        const loading = document.getElementById('loading-tasa');

        loading.style.display = 'block';

        let formData = new FormData();
        formData.append("valor", valor);
        formData.append("valor_paralelo", valorParalelo);

        fetch("{{ route('divisas.guardarTasa') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
              // Actualizar tasa del dia
                document.querySelector('#tasa-actual-texto').innerText = parseFloat(valor).toFixed(2) + " Bs";
                // Actualizar el widget de tasa
                //document.getElementById('widget-tasa-dia').innerText = parseFloat(valor).toFixed(2) + " Bs";
                const widgetTasaDia = document.getElementById('widget-tasa-dia');
                if (widgetTasaDia) {
                    widgetTasaDia.innerText = parseFloat(valor).toFixed(2) + " Bs";
                }

                // Valor de dolar paralelo
                document.querySelector('#tasa-actual-texto-paralelo').innerText = parseFloat(valorParalelo).toFixed(2) + " Bs";
                //document.getElementById('widget-tasa-paralelo').innerText = parseFloat(valorParalelo).toFixed(2) + " Bs";
                const widgetTasaParalelo = document.getElementById('widget-tasa-paralelo');
                if (widgetTasaParalelo) {
                    widgetTasaParalelo.innerText = parseFloat(valorParalelo).toFixed(2) + " Bs";
                }

                // Valor de dolar BCV en el input de la Carga de Ventas Diarias
                const inputTasaCargaVentasDiarias = document.getElementById('exchangeRate');
                if (inputTasaCargaVentasDiarias) {
                    inputTasaCargaVentasDiarias.value = parseFloat(valor).toFixed(2);
                }
                
                showToast(res.message, "success");

                // 👉 SOLO recargar si está en índice de rotación
                if ((window.location.pathname.includes('indice/rotacion')) || (window.location.pathname.includes('baja/demanda')) || (window.location.pathname.includes('registrar/cierre'))) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                }
            } else {
                showToast("Ocurrió un problema al guardar la tasa", "danger");
            }
        })
        .catch(() => {
            showToast("Error de conexión con el servidor", "danger");
        })
        .finally(() => {
            loading.style.display = 'none';
        });
      });

      // Contador de caracteres
      function actualizarContador() {
          const textarea = document.getElementById('texto-notificacion');
          const contador = document.getElementById('contador-caracteres');
          const indicador = document.getElementById('indicador-largo');
          const caracteres = textarea.value.length;
          
          contador.textContent = caracteres;
          
          // Cambiar color según la cantidad de caracteres
          if (caracteres === 0) {
              contador.className = 'text-muted';
              indicador.style.display = 'none';
          } else if (caracteres < 50) {
              contador.className = 'text-success';
              indicador.style.display = 'none';
          } else if (caracteres < 90) {
              contador.className = 'text-warning';
              indicador.style.display = 'none';
          } else {
              contador.className = 'text-danger';
              indicador.style.display = 'inline';
              indicador.className = 'text-danger';
              indicador.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Cerca del límite';
          }
          
          // Mostrar indicador positivo cuando está entre 20-80 caracteres
          if (caracteres >= 20 && caracteres <= 80) {
              indicador.style.display = 'inline';
              indicador.className = 'text-success';
              indicador.innerHTML = '<i class="bi bi-check-circle me-1"></i>Longitud ideal';
          }
      }

      // Manejo del envío del formulario
      document.getElementById('form-notificacion').addEventListener('submit', async function(e) {
          e.preventDefault();

          const form = this;
          const formData = new FormData(form);
          const mensaje = document.getElementById('texto-notificacion').value.trim();

          const loading = document.getElementById('loading-notificacion');
          const submitBtn = form.querySelector('button[type="submit"]');

          // Asegurar que el form tenga acción
          const ruta = "{{ route('admin.publicidad.store') }}"; 
          form.action = ruta;

          // Validaciones
          if (mensaje.length === 0) {
              showToast('Por favor, escribe un mensaje', 'warning');
              return;
          }

          if (mensaje.length > 100) {
              showToast('El mensaje no puede exceder 100 caracteres', 'warning');
              return;
          }

          // Mostrar loading
          loading.style.display = 'block';
          submitBtn.disabled = true;

          try {
              const response = await fetch(ruta, {
                  method: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: formData
              });

              const data = await response.json();

              if (data.success) {
                  showToast('Publicidad publicada correctamente', 'success');

                  // Limpiar textarea
                  document.getElementById('texto-notificacion').value = '';
                  document.getElementById('contador-caracteres').innerText = '0';
                  document.getElementById('indicador-largo').style.display = 'none';

                  // Cerrar dropdown correctamente
                  setTimeout(() => {
                      const dropdownElement = form.closest('.dropdown-menu').previousElementSibling;
                      const dropInstance = bootstrap.Dropdown.getInstance(dropdownElement);
                      if (dropInstance) dropInstance.hide();
                  }, 1500);

              } else {
                  showToast(data.message || 'Error al publicar la publicidad', 'danger');
              }

          } catch (error) {
              console.error(error);
              showToast('Error en la conexión con el servidor.', 'danger');
          } finally {
              loading.style.display = 'none';
              submitBtn.disabled = false;
          }
      });

      // Inicializar contador al cargar
      document.addEventListener('DOMContentLoaded', function() {
          actualizarContador();

          document.querySelectorAll('.seleccionar-sucursal').forEach(function(el) {
            el.addEventListener('click', async function(e) {
                e.preventDefault();

                const sucursalId = this.dataset.id;

                const url = "{{ route('seleccionar.sucursal', ['id' => 'ID_TEMP']) }}".replace('ID_TEMP', sucursalId);

                // Rutas donde NO queremos recargar
                const rutasSinReload = ['/cpanel/dashboard', '/dashboard'];
                const rutaActual = window.location.pathname;
                const sinReload = rutasSinReload.some(r => rutaActual.endsWith(r));

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(data.message, 'success');

                        const navbarSucursal = document.getElementById('navbar-sucursal');
                        if (navbarSucursal) {
                            navbarSucursal.textContent = 'Sucursal: ' + data.sucursal_nombre;
                        }

                        if (!sinReload) {
                            setTimeout(() => location.reload(), 800);
                        }
                    } else {
                        showToast('Error al seleccionar la sucursal.', 'danger');
                    }

                } catch (error) {
                    showToast('Error en la conexión con el servidor.', 'danger');
                }
            });
          });

          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
          tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl)
          })
      });

      // Función para limpiar formulario
      function  limpiarFormulario() {
          document.getElementById('texto-notificacion').value = '';
          actualizarContador();
      }

    </script>

    @yield('js') 

  </body>
  <!--end::Body-->
</html>