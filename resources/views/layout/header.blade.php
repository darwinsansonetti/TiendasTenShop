<header class="version_2">
    <!-- Mobile menu overlay mask -->
    <div class="layer"></div>

    <!-- Marquesina dinamica -->
    <div class="top_line version_1 plus_select" style="background: linear-gradient(to right, #946cc1, #774474, #b73d46);">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-12">
                    <div class="marquee d-flex justify-content-center">
                        <span>{{ $mensaje }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main_header Sticky">
        <div class="container">
            <div class="row small-gutters">
                <div class="col-xl-3 col-lg-3 d-lg-flex align-items-center">
                    <div id="logo">
                        <a href="{{ route('landingpage.index') }}"><img src="{{ asset('assets/img/LogoTenShop.jpg') }}" alt="" width="100" height="35"></a>
                    </div>
                </div>
                <nav class="col-xl-6 col-lg-7">
                    <a class="open_close" href="javascript:void(0);">
                        <div class="hamburger hamburger--spin">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                    <!-- Mobile menu button -->
                    <div class="main-menu">
                        <div id="header_menu">
                            <a href="{{ route('landingpage.index') }}"><img src="{{ asset('assets/img/LogoTenShop.jpg') }}" alt="" width="100" height="35"></a>
                            <a href="#" class="open_close" id="close_in"><i class="ti-close"></i></a>
                        </div>
                        <ul>                            
                            <li>
                                <a href="{{ route('landingpage.index') }}">Inicio</a>
                            </li>
                            <li class="submenu">
                                <a href="javascript:void(0);" class="show-submenu">Productos</a>
                                <ul>
                                    <li><a href="{{ route('landingpage.productos', ['categoria' => 'Damas']) }}">Damas</a></li>
                                    <li><a href="{{ route('landingpage.productos', ['categoria' => 'Caballeros']) }}">Caballeros</a></li>
                                    <li><a href="{{ route('landingpage.productos', ['categoria' => 'Niños']) }}">Niños</a></li>
                                </ul>
                            </li>
                            <li><a href="{{ route('landingpage.nosotros') }}">Nosotros</a></li>
                            <li><a href="{{ route('landingpage.contactanos') }}">Contactanos</a></li>
                            <li><a href="{{ route('landingpage.galeria') }}">Galeria</a></li>
                        </ul>
                    </div>
                    <!--/main-menu -->
                </nav>
                <div class="col-xl-3 col-lg-2 d-lg-flex align-items-center justify-content-end text-end">
                    <ul class="top_tools">
                        <li>
                            <a href="#sign-in-dialog" id="sign-in" class="access_link"><span>Inicio Sesion</span></a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="search_panel"><span>Buscar</span></a>
                        </li>						
                    </ul>
                </div>
            </div>
            <!-- /row -->
        </div>
    </div>
    <!-- /main_header -->
</header>
<!-- /header -->

<!-- Modal Login -->
<div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">
    <div class="modal_header">
        <h3 id="modal-title">Iniciar sesión</h3>
    </div>

    <!-- Formulario Login -->
    <form id="loginForm">
        <div class="sign-in-wrapper login-wrapper">
            <div id="login-error" style="display:none; color:red; margin-bottom:10px;"></div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" id="email">
                <i class="ti-email"></i>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" id="password">
                <i class="ti-lock"></i>
            </div>
            <div class="form-group clearfix">
                <label class="container_check">Recordarme
                    <input type="checkbox" id="remember">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="text-center">
                <input type="submit" value="Iniciar" class="btn_1 full-width">
            </div>
            <div class="text-center mt-2">
                <a href="javascript:void(0);" id="forgot">¿Olvidó su contraseña?</a>
            </div>
        </div>
    </form>

    <div id="login-loading" style="display:none; text-align:center; margin:10px 0;">
        <img src="{{ asset('assets/img/loader.gif') }}" alt="Cargando..." style="width:40px; height:40px;">
        <p>Procesando...</p>
    </div>

    <!-- Formulario Recuperar Contraseña -->
    <form id="forgotForm">
        <div class="sign-in-wrapper forgot-wrapper" style="display:none;">
            <div id="forgot-msg" style="display:none; margin-bottom:10px;"></div>
            <div class="form-group">
                <label>Ingresa el email</label>
                <input type="email" class="form-control" id="email_forgot">
                <i class="ti-email"></i>
            </div>
            <p>Recibirás un correo electrónico con una contraseña genérica para acceder.</p>
            <div class="text-center">
                <input type="submit" value="Recuperar" class="btn_1 full-width">
            </div>
            <div class="text-center mt-2">
                <a href="javascript:void(0);" id="back-login">Volver al login</a>
            </div>
        </div>
    </form>
</div>


<!-- Agrega jQuery si aún no lo tienes -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    // Mostrar recuperar contraseña
    $('#forgot').on('click', function(){
        $('.login-wrapper').hide();
        $('.forgot-wrapper').fadeIn();
        $('#modal-title').text('Recuperar contraseña');
    });

    // Volver al login
    $('#back-login').on('click', function(){
        $('.forgot-wrapper').hide();
        $('.login-wrapper').fadeIn();
        $('#modal-title').text('Iniciar sesión');
    });

    // Cada vez que se abre el modal Login
    $('#sign-in').on('click', function() {
        $('.forgot-wrapper').hide();
        $('.login-wrapper').show();
        $('#modal-title').text('Iniciar sesión'); // Asegurar texto correcto
        $('#email_forgot').val('');
        $('#forgot-msg').hide().text('');
    });

    // Enviar formulario de recuperación de password
    $('#forgotForm').on('submit', function(e){
        e.preventDefault(); // Evitar recarga de página

        const email = $('#email_forgot').val();
        const msgDiv = $('#forgot-msg');

        // Ocultar mensaje previo
        msgDiv.hide().text('');

        $.ajax({
            url: '{{ url("/auth/recover-password") }}',
            method: 'POST',
            data: {
                email: email,
                _token: '{{ csrf_token() }}' // Importante en Laravel
            },
            success: function(response) {
                msgDiv.show();
                if (response.success) {
                    //msgDiv.css('color', 'green').text(response.message);
                    showToastWithIcon(response.message, 'success');
                } else {
                    //msgDiv.css('color', 'red').text(response.message);
                    showToastWithIcon(response.message, 'danger');
                }
            },
            error: function(xhr) {
                // msgDiv.show().css('color', 'red').text('Ocurrió un error, inténtalo de nuevo.');
                showToastWithIcon('Ocurrió un error, inténtalo de nuevo.', 'danger');
            }
        });

    });

});

// Cargar datos guardados si existen
document.addEventListener("DOMContentLoaded", function () {
    const savedEmail = localStorage.getItem("login_email");
    const savedPassword = localStorage.getItem("login_password");
    const savedRemember = localStorage.getItem("login_remember");

    if (savedRemember === "true") {
        document.getElementById("remember").checked = true;
        if (savedEmail) document.getElementById("email").value = savedEmail;
        if (savedPassword) document.getElementById("password").value = savedPassword;
    }
});

// Función específica para mostrar toasts con íconos
function showToastWithIcon(message, type = 'success') {
    const icons = {
        success: '✓',
        danger: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <span class="me-2" style="font-size: 1.2em;">${icons[type]}</span>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.getElementById('toast-container');
    toastContainer.innerHTML = toastHtml;
    
    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

// Tu código de login modificado
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const remember = document.getElementById("remember").checked;
    if (remember) {
        localStorage.setItem("login_email", document.getElementById("email").value);
        localStorage.setItem("login_password", document.getElementById("password").value);
        localStorage.setItem("login_remember", "true");
    } else {
        localStorage.removeItem("login_email");
        localStorage.removeItem("login_password");
        localStorage.removeItem("login_remember");
    }

    const errorBox = document.getElementById('login-error');
    const loader = document.getElementById('login-loading');

    // Limpiar errores previos
    errorBox.style.display = "none";
    loader.style.display = "block";

    const formData = new FormData(this);

    try {
        const response = await fetch("{{ route('login') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: formData
        });

        const data = await response.json();
        loader.style.display = "none";

        // Mostrar toast en lugar del errorBox
        if (!data.success) {
            showToastWithIcon(data.message, 'danger');
            return;
        }

        // Login exitoso
        showToastWithIcon(data.message, 'success');

        // Redirigir después de 2 segundos
        const redirectUrl = data.redirect || "{{ route('cpanel.dashboard') }}";

        setTimeout(() => { 
            window.location.href = redirectUrl;
        }, 500);

    } catch (err) {
        loader.style.display = "none";
        showToastWithIcon("Error de conexión, intente nuevamente.", 'danger');
    }
});
</script>