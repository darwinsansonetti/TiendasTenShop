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

    <!-- Sidebar custom theme -->
    <style>
      /* Background */
      .app-sidebar {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
        border-right: 1px solid rgba(255,255,255,.05) !important;
      }
      /* Brand */
      .sidebar-brand {
        background: rgba(0,0,0,.25) !important;
        border-bottom: 1px solid rgba(255,255,255,.08) !important;
      }
      .sidebar-brand .brand-text {
        color: #f1f5f9 !important;
        font-weight: 600 !important;
        font-size: .94rem !important;
        letter-spacing: .01em !important;
      }
      .sidebar-brand .brand-image { opacity: .92 !important; }

      /* Top-level nav links */
      .sidebar-wrapper .nav-sidebar > .nav-item > .nav-link {
        margin: 2px 8px !important;
        border-radius: 7px !important;
        color: rgba(255,255,255,.6) !important;
        transition: background .18s, color .18s, border-color .18s !important;
        border-left: 3px solid transparent !important;
      }
      .sidebar-wrapper .nav-sidebar > .nav-item > .nav-link:hover {
        background: rgba(255,255,255,.07) !important;
        color: #fff !important;
      }
      .sidebar-wrapper .nav-sidebar > .nav-item > .nav-link.active {
        background: rgba(59,130,246,.18) !important;
        color: #fff !important;
        border-left-color: #3b82f6 !important;
      }
      .sidebar-wrapper .nav-sidebar > .nav-item.menu-open > .nav-link {
        background: rgba(255,255,255,.06) !important;
        color: #e2e8f0 !important;
        border-left-color: rgba(59,130,246,.4) !important;
      }

      /* Submenu links */
      .sidebar-wrapper .nav-treeview .nav-link {
        color: rgba(255,255,255,.48) !important;
        border-radius: 5px !important;
        margin: 1px 4px !important;
        transition: background .15s, color .15s !important;
        border-left: 3px solid transparent !important;
      }
      .sidebar-wrapper .nav-treeview .nav-link:hover {
        background: rgba(255,255,255,.06) !important;
        color: rgba(255,255,255,.85) !important;
      }
      .sidebar-wrapper .nav-treeview .nav-link.active {
        background: rgba(59,130,246,.15) !important;
        color: #93c5fd !important;
        border-left-color: #3b82f6 !important;
      }

      /* Icons */
      .sidebar-wrapper .nav-icon {
        color: rgba(255,255,255,.35) !important;
        width: 1.6rem !important;
        font-size: .95rem !important;
        text-align: center !important;
        transition: color .18s !important;
      }
      .sidebar-wrapper .nav-link:hover  .nav-icon,
      .sidebar-wrapper .nav-link.active .nav-icon,
      .sidebar-wrapper .menu-open > .nav-link .nav-icon {
        color: rgba(255,255,255,.85) !important;
      }
      .sidebar-wrapper .nav-treeview .nav-icon {
        font-size: .82rem !important;
        width: 1.4rem !important;
      }
      .sidebar-wrapper .nav-treeview .nav-link.active .nav-icon {
        color: #93c5fd !important;
      }

      /* Section headers */
      .sidebar-wrapper .nav-header {
        color: rgba(255,255,255,.25) !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: .12em !important;
        padding: 16px 18px 5px !important;
      }

      /* Arrow */
      .sidebar-wrapper .nav-arrow {
        color: rgba(255,255,255,.25) !important;
        font-size: .7rem !important;
        transition: color .18s !important;
      }
      .sidebar-wrapper .nav-link:hover .nav-arrow,
      .sidebar-wrapper .menu-open > .nav-link .nav-arrow {
        color: rgba(255,255,255,.6) !important;
      }

      /* Scrollbar */
      .sidebar-wrapper .os-scrollbar-track  { background: rgba(255,255,255,.04) !important; }
      .sidebar-wrapper .os-scrollbar-handle { background: rgba(255,255,255,.14) !important; }
    </style>

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
                <span id="navbar-sucursal" class="nav-link text-dark fw-semibold">
                    <i class="bi bi-geo-alt-fill text-primary me-1" style="font-size: 0.85rem;"></i>
                    @if(session('sucursal_nombre'))
                        {{ session('sucursal_nombre') }}
                    @else
                        Todas las Sucursales
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
      <aside class="app-sidebar shadow" data-bs-theme="dark">

        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <a href="{{ route('cpanel.dashboard') }}" class="brand-link" style="border-bottom:none;">
            <img src="{{ asset('assets/img/TSCirculo.png') }}"
                 alt="TiendasTenShop"
                 class="brand-image shadow" />
            <span class="brand-text">TiendasTenShop</span>
          </a>
        </div>

        @if(Str::contains($db, 'db_a509ee_tenshop2026'))
          <div class="px-3 pt-1 pb-2">
            <span class="badge bg-warning text-dark w-100 py-1"
                  data-bs-toggle="tooltip" data-bs-placement="right"
                  title="Base de datos activa: {{ $db }}">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> DEVELOPER
            </span>
          </div>
        @endif
        <!--end::Sidebar Brand-->

        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-1 pb-4">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="navigation"
                aria-label="Main navigation"
                data-accordion="false"
                id="navigation">

              {{-- Inicio --}}
              <li class="nav-item">
                <a href="{{ route('cpanel.dashboard') }}"
                   class="nav-link {{ session('menu_active') == 'Inicio' ? 'active' : '' }}">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Inicio</p>
                </a>
              </li>

              {{-- ── REPORTES ─────────────────────────────── --}}
              <li class="nav-header">REPORTES</li>

              <li class="nav-item {{ session('menu_active') == 'Informes - Resumen' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-data-fill"></i>
                  <p>Informes - Resumen <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.resumen.ventas') }}"
                       class="nav-link {{ session('submenu_active') == 'Resumen de ventas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-receipt"></i><p>Resumen de ventas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.estado.cuentas') }}"
                       class="nav-link {{ session('submenu_active') == 'Estado de cuentas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-wallet2"></i><p>Estado de cuentas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.comparativa.sucursales') }}"
                       class="nav-link {{ session('submenu_active') == 'Comparativa' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-bar-chart-line"></i><p>Comparativa Sucursales</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.indice.rotacion') }}"
                       class="nav-link {{ session('submenu_active') == 'Indice de Rotación' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-arrow-repeat"></i><p>Indice de Rotación</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.baja.ventas') }}"
                       class="nav-link {{ session('submenu_active') == 'Baja Demanda' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-arrow-down-circle"></i><p>Baja Demanda</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.alta.ventas') }}"
                       class="nav-link {{ session('submenu_active') == 'Alta Demanda' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-arrow-up-circle"></i><p>Alta Demanda</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item {{ session('menu_active') == 'Análisis de Ventas' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-shop"></i>
                  <p>Análisis de Ventas <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.ventas.diarias') }}"
                       class="nav-link {{ session('submenu_active') == 'Ventas Diarias' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-calendar-day"></i><p>Ventas Diarias</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.ventas.producto') }}"
                       class="nav-link {{ session('submenu_active') == 'Ventas por producto' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-tag"></i><p>Ventas por producto</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cargar.ventas.diarias') }}"
                       class="nav-link {{ session('submenu_active') == 'Cargar Venta Diaria' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-upload"></i><p>Cargar Ventas Diarias</p>
                    </a>
                  </li>
                </ul>
              </li>

              {{-- ── OPERACIONES ──────────────────────────── --}}
              <li class="nav-header">OPERACIONES</li>

              <li class="nav-item {{ session('menu_active') == 'Cuadre de Caja' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-calculator"></i>
                  <p>Cuadre de Caja <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.resumen_diario') }}"
                       class="nav-link {{ session('submenu_active') == 'Resumen Diario' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-journal-text"></i><p>Resumen Diario</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.registrar_cierre') }}"
                       class="nav-link {{ session('submenu_active') == 'Registrar Cierre' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-lock"></i><p>Registrar Cierre</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.auditar_cierre') }}"
                       class="nav-link {{ session('submenu_active') == 'Auditar Cierre' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-search"></i><p>Auditar Cierre</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.cuadre.consolidado') }}"
                       class="nav-link {{ session('submenu_active') == 'Consolidado Financiero' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-file-earmark-bar-graph"></i><p>Consolidado Financiero</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item {{ session('menu_active') == 'Contabilidad' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-cash-stack"></i>
                  <p>Contabilidad <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.contabilidad.balance_general') }}"
                       class="nav-link {{ session('submenu_active') == 'Balance General' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-journal-bookmark"></i><p>Balance General</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.contabilidad.show_cerrar_dia') }}"
                       class="nav-link {{ session('submenu_active') == 'Cerrar Día' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-calendar-check"></i><p>Cerrar Día</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.contabilidad.probar_cerrar_dia') }}"
                       class="nav-link {{ session('submenu_active') == 'Probar Cerrar Dia' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-calendar-x"></i><p>Probar Cerrar Dia</p>
                    </a>
                  </li>
                </ul>
              </li>

              {{-- ── EQUIPO ───────────────────────────────── --}}
              <li class="nav-header">EQUIPO</li>

              <li class="nav-item {{ session('menu_active') == 'Empleados' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-person-badge"></i>
                  <p>Empleados <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.ventas_diarias') }}"
                       class="nav-link {{ session('submenu_active') == 'Ventas Diarias' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-calendar-day"></i><p>Ventas Diarias</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.ranking') }}"
                       class="nav-link {{ session('submenu_active') == 'Ranking General' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-trophy"></i><p>Ranking General</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.vendedores') }}"
                       class="nav-link {{ session('submenu_active') == 'Vendedores' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-people"></i><p>Vendedores</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.personal') }}"
                       class="nav-link {{ session('submenu_active') == 'Personal Interno' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-person-lines-fill"></i><p>Empleado Interno</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.agregar') }}"
                       class="nav-link {{ session('submenu_active') == 'Agregar Empleado' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-person-plus"></i><p>Agregar Empleado</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.lista_empleados_bonos') }}"
                       class="nav-link {{ session('submenu_active') == 'Bonos' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-gift"></i><p>Bonos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.lista_empleados_deducciones') }}"
                       class="nav-link {{ session('submenu_active') == 'Deducciones' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-dash-circle"></i><p>Deducciones</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.lista_empleados_prestamos') }}"
                       class="nav-link {{ session('submenu_active') == 'Prestamos' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-cash-coin"></i><p>Prestamos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.empleados.lista_liberalidad') }}"
                       class="nav-link {{ session('submenu_active') == 'Liberalidad' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-heart"></i><p>Liberalidad</p>
                    </a>
                  </li>
                </ul>
              </li>

              {{-- ── LOGÍSTICA ────────────────────────────── --}}
              <li class="nav-header">LOGÍSTICA</li>

              <li class="nav-item {{ session('menu_active') == 'Proveedor Mercancía' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-truck"></i>
                  <p>Proveedor Mercancía <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.proveedor.mercancia.listado') }}"
                       class="nav-link {{ session('submenu_active') == 'Listado Proveedores' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-list-ul"></i><p>Listado Proveedores</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.proveedor.mercancia.registrar_pagos') }}"
                       class="nav-link {{ session('submenu_active') == 'Registrar Pagos' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-credit-card"></i><p>Registrar Pagos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.proveedor.mercancia.registrar_facturas') }}"
                       class="nav-link {{ session('submenu_active') == 'Registrar Facturas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-file-text"></i><p>Registrar Facturas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.proveedor.mercancia.contenedores') }}"
                       class="nav-link {{ session('submenu_active') == 'Contenedores' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-archive"></i><p>Contenedores</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item {{ session('menu_active') == 'Inventario' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-data"></i>
                  <p>Inventario <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.inventario.cargar.excel') }}"
                       class="nav-link {{ session('submenu_active') == 'Cargar Inventario' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-table"></i><p>Cargar Inventario</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item {{ session('menu_active') == 'Recepciones' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-seam"></i>
                  <p>Recepciones <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.recepciones.proveedor') }}"
                       class="nav-link {{ session('submenu_active') == 'Recibir de proveedor' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-inbox-fill"></i><p>Recibir de proveedor</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.recepciones.sucursal') }}"
                       class="nav-link {{ session('submenu_active') == 'Recibir de Sucursal' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-inbox-fill"></i><p>Recibir de Sucursal</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.recepciones.finalizadas') }}"
                       class="nav-link {{ session('submenu_active') == 'Recepciones Finalizadas' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-inbox-fill"></i><p>Recepciones Finalizadas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('cpanel.recepciones.auditorias') }}"
                       class="nav-link {{ session('submenu_active') == 'Auditar Recepciones' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-inbox-fill"></i><p>Auditar Recepciones</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item {{ session('menu_active') == 'Distribuciones' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-seam"></i>
                  <p>Distribuciones <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('cpanel.distribucion.listado') }}"
                        class="nav-link {{ session('submenu_active') == 'Listado Dist. / Trans.' ? 'active' : '' }}">
                          <i class="nav-icon bi bi-list-ul"></i>
                          <p>Listado Dist. / Trans.</p>
                      </a>
                  </li>

                  <li class="nav-item">
                      <a href="{{ route('cpanel.distribucion.distribuciones') }}"
                        class="nav-link {{ session('submenu_active') == 'Nueva Distribución' ? 'active' : '' }}">
                          <i class="nav-icon bi bi-plus-circle"></i>
                          <p>Nueva Distribución</p>
                      </a>
                  </li>

                  <li class="nav-item">
                      <a href="{{ route('cpanel.distribucion.inventario') }}"
                        class="nav-link {{ session('submenu_active') == 'Inventario de almacen' ? 'active' : '' }}">
                          <i class="nav-icon bi bi-box-seam"></i>
                          <p>Inventario de almacen</p>
                      </a>
                  </li>
                </ul>
              </li>

              {{-- ── PRODUCTOS ────────────────────────────── --}}
              <li class="nav-header">PRODUCTOS</li>

              <li class="nav-item {{ session('menu_active') == 'Productos' ? 'menu-open' : '' }}">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-truck"></i>
                  <p>Productos <i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('cpanel.productos.cambiar.pvp') }}"
                       class="nav-link {{ session('submenu_active') == 'Gestión de Precios' ? 'active' : '' }}">
                      <i class="nav-icon bi bi-list-ul"></i><p>Gestión de Precios</p>
                    </a>
                  </li>
                </ul>
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
        <strong>
          <i class="bi bi-c-circle me-1"></i> 2025 TiendasTenShop
        </strong>
        <span class="ms-1 text-muted">— Todos los derechos reservados.</span>
        <div class="float-end d-none d-sm-inline text-muted small">
          <i class="bi bi-server me-1"></i>{{ $db }}
        </div>
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