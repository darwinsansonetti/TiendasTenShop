@extends('layout.layout_landing')

@section('title', 'Contáctanos - Tiendas TenShop')

@section('content')
<main class="bg_gray">
    {{-- Banner Superior --}}
    <div class="top_banner general">
        <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.4)">
            <div class="container">
                <div class="row justify-content-end">
                    <div class="col-lg-6 col-md-8 text-end">
                        <h1 class="text-white fw-bold">Contáctanos</h1>
                        <p class="text-white lead mb-0">¿Tienes alguna duda? Estamos aquí para ayudarte.</p>
                    </div>
                </div>
            </div>
        </div>
        <img src="{{ asset('assets/img/slides/banner-2.jpg') }}" class="img-fluid" alt="">
    </div>
    <!--/top_banner-->
    
    <div class="bg_white">
        <div class="container margin_60_35">
            <div class="row justify-content-between">
                
                {{-- Columna Izquierda: Información de Contacto --}}
                <div class="col-lg-4">
                    <div class="box_contacts">
                        <h3>Información de Contacto</h3>
                        <ul>
                            <li>
                                <i class="ti-location-pin"></i>
                                <strong>Ubicación</strong>
                                <p>Visítanos en nuestras sucursales.<br>
                                <a href="{{ route('landingpage.nosotros') }}">Ver todas las tiendas</a></p>
                            </li>
                            <li>
                                <i class="ti-headphone-alt"></i>
                                <strong>Teléfono / WhatsApp</strong>
                                <p><a href="https://wa.me/584120000000">+58 412-000-0000</a></p>
                            </li>
                            <li>
                                <i class="ti-time"></i>
                                <strong>Horario de Atención</strong>
                                <p>Lunes a Sábado<br>8:30 AM - 5:00 PM</p>
                            </li>
                        </ul>
                    </div>
                </div>
                
                {{-- Columna Derecha: Formulario --}}
                <div class="col-lg-7">
                    <div class="box_contacts" style="padding: 30px; background: #fff; border-radius: 10px; border: 1px solid #e9ecef;">
                        <h3>Envíanos un mensaje</h3>
                        
                        {{-- Mensajes de éxito o error --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ti-check-box me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ti-alert me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('landingpage.contacto.enviar') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">Nombre completo *</label>
                                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required placeholder="Tu nombre">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">Correo electrónico *</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="tu@correo.com">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">Teléfono (Opcional)</label>
                                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej: 0414-1234567">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="fw-semibold">Asunto *</label>
                                        <input type="text" name="asunto" class="form-control" value="{{ old('asunto') }}" required placeholder="¿En qué te podemos ayudar?">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="fw-semibold">Mensaje *</label>
                                <textarea name="mensaje" class="form-control" rows="5" required placeholder="Escribe tu mensaje aquí...">{{ old('mensaje') }}</textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn_1" style="padding: 10px 30px; border-radius: 6px;">
                                    <i class="ti-email me-2"></i> Enviar Mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>
            <!--/row-->
        </div>
        <!--/container-->
    </div>
    <!--/bg_white-->
</main>
@endsection