@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Cargar Venta Diaria')

@php
    use App\Helpers\FileHelper;
@endphp

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6"><h3 class="mb-0">Cargar Venta Diaria</h3></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Cargar Venta Diaria</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-12">
                <!--begin::Card-->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Carga de Archivos Excel</h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Campo oculto para sucursal_id -->
                            <input type="hidden" name="sucursal_id" value="{{ session('sucursal_id', 0) }}">

                            <!-- Campo oculto para sucursal_id -->
                            <input type="hidden" name="nombre_sucursal" value="{{ session('sucursal_nombre') }}">
                            
                            <div class="row mb-4">
                                <!-- Fecha de Venta -->
                                <div class="col-md-6">
                                    <label for="saleDate" class="form-label">
                                        <strong>Fecha de Venta</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar"></i>
                                        </span>
                                        <input type="date" class="form-control" id="saleDate" name="sale_date" required>
                                    </div>
                                    <small class="text-muted">Fecha correspondiente a las ventas que se están cargando</small>
                                </div>
                                
                                <!-- Tasa de cambio -->
                                <div class="col-md-6">
                                    <label for="exchangeRate" class="form-label">
                                        <strong>Tasa de Cambio BCV</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-currency-exchange"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="exchangeRate" 
                                               name="exchange_rate"
                                               step="0.000001"
                                               min="0"
                                               value="{{ $tasa['DivisaValor']['Valor'] ?? 0 }}"
                                               placeholder="Ej: 36.123456"
                                               required>
                                        <span class="input-group-text">
                                            Bs/USD
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Tasa oficial del Banco Central de Venezuela</small>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Archivo de Ventas Diarias -->
                            <div class="mb-4">
                                <label for="dailySalesFile" class="form-label">
                                    <strong>Venta Diaria</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Seleccione el archivo Excel de ventas diarias</p>
                                
                                <!-- Contenedor principal del campo -->
                                <div class="file-input-container">
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control" 
                                               id="dailySalesFile" 
                                               name="daily_sales_file"
                                               accept=".xlsx, .xls, .csv"
                                               required>
                                    </div>
                                    <!-- Mensaje de error separado -->
                                    <div class="invalid-feedback" id="dailySalesError" style="display: none;">
                                        <i class="bi bi-exclamation-circle me-1"></i> Por favor seleccione el archivo de ventas diarias.
                                    </div>
                                </div>
                                <div id="dailySalesFileName" class="mt-2 text-muted small"></div>
                            </div>

                            <!-- Archivo de Ventas por Vendedores -->
                            <div class="mb-4">
                                <label for="salesBySellerFile" class="form-label">
                                    <strong>Ventas por Vendedores</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <p class="text-muted small mb-2">Seleccione el archivo Excel de ventas por vendedores</p>
                                
                                <!-- Contenedor principal del campo -->
                                <div class="file-input-container">
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control" 
                                               id="salesBySellerFile" 
                                               name="sales_by_seller_file"
                                               accept=".xlsx, .xls, .csv"
                                               required>
                                    </div>
                                    <!-- Mensaje de error separado -->
                                    <div class="invalid-feedback" id="salesBySellerError" style="display: none;">
                                        <i class="bi bi-exclamation-circle me-1"></i> Por favor seleccione el archivo de ventas por vendedores.
                                    </div>
                                </div>
                                <div id="salesBySellerFileName" class="mt-2 text-muted small"></div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <!-- Información adicional -->
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <div>
                                            <strong>Nota:</strong> Ambos archivos son requeridos. Asegúrese de que los archivos sean en formato Excel (.xlsx, .xls) o CSV.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Cargar -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="button" class="btn btn-secondary me-md-2" id="resetForm">
                                    <i class="bi bi-eraser me-1"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary" id="loadButton">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos
                                </button>
                            </div>
                        </form>

                        <!-- Contenedor de progreso (oculto inicialmente) -->
                        <div id="progressContainer" class="mt-4 d-none">
                            <div class="progress" style="height: 25px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%" 
                                     aria-valuenow="0" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span id="progressText">0%</span>
                                </div>
                            </div>
                            <div id="progressMessage" class="text-center mt-2 text-muted">
                                <i class="bi bi-hourglass-split me-1"></i> Procesando archivos, por favor espere...
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@section('js')

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<!-- jsPDF y autoTable para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM
        const dailySalesFile = document.getElementById('dailySalesFile');
        const salesBySellerFile = document.getElementById('salesBySellerFile');
        const dailySalesFileName = document.getElementById('dailySalesFileName');
        const salesBySellerFileName = document.getElementById('salesBySellerFileName');
        const resetForm = document.getElementById('resetForm');
        const uploadForm = document.getElementById('uploadForm');
        const loadButton = document.getElementById('loadButton');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressMessage = document.getElementById('progressMessage');
        const dailySalesError = document.getElementById('dailySalesError');
        const salesBySellerError = document.getElementById('salesBySellerError');
        const saleDate = document.getElementById('saleDate');
        const exchangeRate = document.getElementById('exchangeRate');

        // Establecer fecha máxima como hoy
        const today = new Date().toISOString().split('T')[0];
        saleDate.max = today;
        saleDate.value = today;

        // Mantener la funcionalidad original para los archivos
        dailySalesFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                dailySalesFileName.innerHTML = `
                    <div class="file-info">
                        <i class="bi bi-file-earmark-excel"></i>
                        <div>
                            <strong>${file.name}</strong><br>
                            <small class="text-muted">
                                <i class="bi bi-hdd me-1"></i> ${(file.size / 1024).toFixed(2)} KB • 
                                <i class="bi bi-clock me-1 ms-2"></i> ${new Date(file.lastModified).toLocaleDateString('es-ES', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </small>
                        </div>
                    </div>
                `;
                // Quitar error si existe
                this.classList.remove('is-invalid');
                dailySalesError.style.display = 'none';
            } else {
                dailySalesFileName.innerHTML = '';
            }
        });

        salesBySellerFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                salesBySellerFileName.innerHTML = `
                    <div class="file-info">
                        <i class="bi bi-file-earmark-excel"></i>
                        <div>
                            <strong>${file.name}</strong><br>
                            <small class="text-muted">
                                <i class="bi bi-hdd me-1"></i> ${(file.size / 1024).toFixed(2)} KB • 
                                <i class="bi bi-clock me-1 ms-2"></i> ${new Date(file.lastModified).toLocaleDateString('es-ES', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </small>
                        </div>
                    </div>
                `;
                // Quitar error si existe
                this.classList.remove('is-invalid');
                salesBySellerError.style.display = 'none';
            } else {
                salesBySellerFileName.innerHTML = '';
            }
        });

        // Limpiar todo el formulario
        resetForm.addEventListener('click', function() {
            // Limpiar archivos
            dailySalesFile.value = '';
            salesBySellerFile.value = '';
            dailySalesFileName.innerHTML = '';
            salesBySellerFileName.innerHTML = '';
            
            // Limpiar errores
            dailySalesFile.classList.remove('is-invalid');
            salesBySellerFile.classList.remove('is-invalid');
            dailySalesError.style.display = 'none';
            salesBySellerError.style.display = 'none';
            
            // Resetear nuevos campos
            // saleDate.value = "{{ date('Y-m-d') }}";
            saleDate.value = today;
            exchangeRate.value = "{{ $tasaParalelo ?? 0 }}";
            
            // Resetear progreso
            progressContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-info');
        });

        // Simular carga de archivos
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar que ambos archivos estén seleccionados
            let hasError = false;
            
            // Validar nuevos campos primero
            if (!saleDate.value) {
                saleDate.classList.add('is-invalid');
                hasError = true;
            } else {
                saleDate.classList.remove('is-invalid');
            }
            
            if (!exchangeRate.value || parseFloat(exchangeRate.value) <= 0) {
                exchangeRate.classList.add('is-invalid');
                hasError = true;
            } else {
                exchangeRate.classList.remove('is-invalid');
            }
            
            // Validar archivos (funcionalidad original)
            if (!dailySalesFile.files.length) {
                dailySalesFile.classList.add('is-invalid');
                dailySalesError.style.display = 'block';
                hasError = true;
            } else {
                dailySalesFile.classList.remove('is-invalid');
                dailySalesError.style.display = 'none';
            }
            
            if (!salesBySellerFile.files.length) {
                salesBySellerFile.classList.add('is-invalid');
                salesBySellerError.style.display = 'block';
                hasError = true;
            } else {
                salesBySellerFile.classList.remove('is-invalid');
                salesBySellerError.style.display = 'none';
            }
            
            if (hasError) {
                // Scroll suave al primer error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                return;
            }

            // Si todo está bien, proceder con la carga
            startUploadProcess();
        });

        // Función para iniciar el proceso de carga
        function startUploadProcess() {
            // 1. Obtener valores de los campos
            const sucursalId = document.querySelector('input[name="sucursal_id"]').value;
            const sucursalNombre = document.querySelector('input[name="nombre_sucursal"]').value;
            const saleDate = document.getElementById('saleDate').value;
            const exchangeRate = parseFloat(document.getElementById('exchangeRate').value);
            const dailySalesFile = document.getElementById('dailySalesFile').files[0];
            const salesBySellerFile = document.getElementById('salesBySellerFile').files[0];
            
            // 2. Validaciones
            let errors = [];
            
            // Validar fecha
            if (!saleDate) {
                errors.push('Debe seleccionar una fecha de venta');
            } else {
                const selectedDate = new Date(saleDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Para comparar solo la fecha
                
                if (selectedDate > today) {
                    errors.push('La fecha de venta no puede ser mayor a la fecha actual');
                }
            }
            
            // Validar tasa BCV
            if (!exchangeRate || exchangeRate <= 0 || isNaN(exchangeRate)) {
                errors.push('La tasa BCV debe ser un número mayor a 0');
            }
            
            // Validar sucursal
            if (!sucursalId || parseInt(sucursalId) <= 0) {
                errors.push('Debe estar asociado a una sucursal válida');
            }
            
            // Validar archivos
            if (!dailySalesFile) {
                errors.push('Debe seleccionar el archivo de ventas diarias');
            }
            
            if (!salesBySellerFile) {
                errors.push('Debe seleccionar el archivo de ventas por vendedores');
            }
            
            // 3. Si hay errores, mostrarlos y salir
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: `<div class="text-start">
                            <p>Por favor corrija los siguientes errores:</p>
                            <ul class="mb-0">
                                ${errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>`,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            // 4. Formatear fecha para mostrar
            // const fechaFormateada = new Date(saleDate).toLocaleDateString('es-ES', {
            //     weekday: 'long',
            //     year: 'numeric',
            //     month: 'long',
            //     day: 'numeric'
            // });

            const [y, m, d] = saleDate.split('-').map(Number);
            const selectedDate = new Date(y, m - 1, d);

            const fechaFormateada = selectedDate.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // 5. Mostrar confirmación antes de enviar
            Swal.fire({
                icon: 'question',
                title: 'Confirmar envío',
                html: `<div class="text-start">
                        <p class="mb-3">¿Está seguro que desea enviar los siguientes datos?</p>
                        
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-shop text-primary me-2"></i>
                                <strong class="me-2">Sucursal:</strong>
                                <span>${sucursalNombre}</span>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar text-primary me-2"></i>
                                <strong class="me-2">Fecha de Venta:</strong>
                                <span>${fechaFormateada}</span>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-currency-exchange text-primary me-2"></i>
                                <strong class="me-2">Tasa BCV:</strong>
                                <span>${exchangeRate.toFixed(2)} Bs</span>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                <strong class="me-2">Ventas Diarias:</strong>
                                <span class="text-truncate">${dailySalesFile.name} - ${(dailySalesFile.size / 1024).toFixed(2)} KB</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                <strong class="me-2">Ventas por Vendedores:</strong>
                                <span class="text-truncate">${salesBySellerFile.name} - ${(salesBySellerFile.size / 1024).toFixed(2)} KB</span>
                            </div>
                        </div>
                        
                        <p class="text-muted border-top pt-2 mb-0"><small><i class="bi bi-info-circle me-1"></i> Los datos serán procesados por el sistema.</small></p>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Sí, enviar',
                cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                reverseButtons: true,
                focusConfirm: false,
                customClass: {
                    popup: 'animate__animated animate__fadeIn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // 6. Si confirma, proceder con el envío real
                    proceedWithRealUpload();
                } else {
                    // Si cancela, mantener el botón habilitado
                    loadButton.disabled = false;
                    loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
                }
            });
        }

        // Función para proceder con el envío real (simulación)
        // function proceedWithRealUpload() {
        //     // Deshabilitar botón de carga
        //     loadButton.disabled = true;
        //     loadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';

        //     // Mostrar contenedor de progreso
        //     progressContainer.classList.remove('d-none');
            
        //     // Simular progreso de carga (esto sería reemplazado por el fetch real)
        //     simulateUploadProgress();
        // }

        // Función para proceder con el envío real (cargar los archivos al servidor)
        // function proceedWithRealUpload() {
        //     // Deshabilitar botón de carga
        //     loadButton.disabled = true;
        //     loadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';

        //     // Mostrar contenedor de progreso
        //     progressContainer.classList.remove('d-none');
            
        //     // Crear objeto FormData con todos los datos
        //     const formData = new FormData();
        //     formData.append('_token', document.querySelector('input[name="_token"]').value);
        //     formData.append('sucursal_id', document.querySelector('input[name="sucursal_id"]').value);
        //     formData.append('sale_date', saleDate.value);
        //     formData.append('exchange_rate', exchangeRate.value);
        //     formData.append('daily_sales_file', dailySalesFile.files[0]);
        //     formData.append('sales_by_seller_file', salesBySellerFile.files[0]);

        //     // Realizar el envío al servidor con fetch
        //     fetch('{{ route("ventas.store") }}', { // Asegúrate de que esta ruta esté definida en tus rutas de Laravel
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             // Aquí ya se ha procesado todo correctamente en el backend
        //             Swal.fire({
        //                 icon: 'success',
        //                 title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Envío Exitoso!',
        //                 html: `
        //                     <div class="text-start">
        //                         <p>Los datos han sido enviados correctamente al servidor.</p>
        //                         <div class="alert alert-success mb-0">
        //                             <i class="bi bi-check-circle me-1"></i> Los archivos están siendo procesados en segundo plano.
        //                         </div>
        //                     </div>`,
        //                 confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Aceptar',
        //                 confirmButtonColor: '#3085d6'
        //             }).then(() => {
        //                 // Resetear formulario
        //                 uploadForm.reset();
        //                 document.getElementById('saleDate').value = "{{ date('Y-m-d') }}";
        //                 document.getElementById('exchangeRate').value = "{{ $tasa['DivisaValor']['Valor'] ?? 0 }}";
        //                 document.getElementById('dailySalesFileName').innerHTML = '';
        //                 document.getElementById('salesBySellerFileName').innerHTML = '';
        //                 progressContainer.classList.add('d-none');
        //                 progressBar.style.width = '0%';
        //                 progressText.textContent = '0%';
        //                 progressBar.classList.remove('bg-success');
        //                 progressBar.classList.add('bg-info');

        //                 // Habilitar botón de carga
        //                 loadButton.disabled = false;
        //                 loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
        //             });
        //         } else {
        //             // Si el backend responde con error
        //             Swal.fire({
        //                 icon: 'error',
        //                 title: '¡Error!',
        //                 text: data.message || 'Hubo un problema al procesar los archivos. Por favor, intente nuevamente.',
        //                 confirmButtonColor: '#dc3545'
        //             });
        //             loadButton.disabled = false;
        //             loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         Swal.fire({
        //             icon: 'error',
        //             title: '¡Error!',
        //             text: 'Hubo un problema al procesar la solicitud. Por favor, intente nuevamente.',
        //             confirmButtonColor: '#dc3545'
        //         });
        //         loadButton.disabled = false;
        //         loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
        //     });
        // }

        function proceedWithRealUpload() {
            // Deshabilitar botón de carga
            loadButton.disabled = true;
            loadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';

            // Mostrar contenedor de progreso
            progressContainer.classList.remove('d-none');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressMessage.textContent = 'Iniciando carga...';

            // Crear FormData
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('sucursal_id', document.querySelector('input[name="sucursal_id"]').value);
            formData.append('sale_date', saleDate.value);
            formData.append('exchange_rate', exchangeRate.value);
            formData.append('daily_sales_file', dailySalesFile.files[0]);
            formData.append('sales_by_seller_file', salesBySellerFile.files[0]);

            // Variable de progreso
            let progress = 0;

            // Intervalo de simulación
            const simulateProgress = setInterval(() => {
                if (progress < 90) { // Límite de la simulación (no 100%)
                    progress += Math.random() * 3 + 1; // Incrementos pequeños y constantes
                    progress = Math.min(progress, 90); // Limitar al 90%
                    progressBar.style.width = `${Math.round(progress)}%`;
                    progressText.textContent = `${Math.round(progress)}%`;

                    // Mensajes dinámicos
                    if (progress < 30) {
                        progressMessage.textContent = 'Iniciando carga...';
                    } else if (progress < 60) {
                        progressMessage.textContent = 'Procesando archivo de ventas...';
                    } else if (progress < 90) {
                        progressMessage.textContent = 'Procesando archivo por vendedores...';
                    }
                }
            }, 500);

            // Enviar al servidor
            fetch('{{ route("ventas.store") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Detener simulación
                clearInterval(simulateProgress);

                // Completar la barra al 100%
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                progressMessage.textContent = 'Proceso completado';
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-success');

                // Mostrar modal de éxito o error
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Envío Exitoso!',
                        html: `<div class="text-start">
                                <p>Los datos han sido enviados correctamente al servidor.</p>
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle me-1"></i> Los archivos están siendo procesados en segundo plano.
                                </div>
                            </div>`,
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Aceptar',
                        confirmButtonColor: '#3085d6'
                    }).then(() => resetFormAndProgress());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: data.message || 'Hubo un problema al procesar los archivos. Por favor, intente nuevamente.',
                        confirmButtonColor: '#dc3545'
                    }).then(() => {
                        // Resetear barra y formulario tras error
                        resetFormAndProgress();
                    });
                }
            })
            .catch(error => {
                clearInterval(simulateProgress);

                // Mostrar mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'Hubo un problema al procesar la solicitud. Por favor, intente nuevamente.',
                    confirmButtonColor: '#dc3545'
                }).then(() => {
                    // Resetear barra y formulario aunque haya fallado
                    resetFormAndProgress();
                });
            });
        }

        // Función para resetear el formulario y la barra de progreso
        function resetFormAndProgress() {
            uploadForm.reset();
            document.getElementById('saleDate').value = "{{ date('Y-m-d') }}";
            document.getElementById('exchangeRate').value = "{{ $tasa['DivisaValor']['Valor'] ?? 0 }}";
            document.getElementById('dailySalesFileName').innerHTML = '';
            document.getElementById('salesBySellerFileName').innerHTML = '';
            progressContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-info');
            loadButton.disabled = false;
            loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
        }

        // Función para simular el progreso de carga (mantener tu función existente)
        function simulateUploadProgress() {
            let progress = 0;
            const steps = [10, 25, 40, 60, 75, 85, 95, 100];
            const messages = [
                '<i class="bi bi-calendar-check me-1"></i> Validando fecha de venta...',
                '<i class="bi bi-calculator me-1"></i> Aplicando tasa de cambio...',
                '<i class="bi bi-search me-1"></i> Validando estructura de archivos...',
                '<i class="bi bi-file-earmark-excel me-1"></i> Leyendo archivo de ventas diarias...',
                '<i class="bi bi-gear me-1"></i> Procesando datos de ventas diarias...',
                '<i class="bi bi-file-earmark-excel me-1"></i> Leyendo archivo de ventas por vendedores...',
                '<i class="bi bi-people me-1"></i> Procesando datos por vendedores...',
                '<i class="bi bi-puzzle me-1"></i> Integrando información...',
                '<i class="bi bi-check-circle me-1"></i> ¡Proceso completado exitosamente!'
            ];

            const interval = setInterval(() => {
                if (progress < steps.length) {
                    const currentStep = steps[progress];
                    progressBar.style.width = `${currentStep}%`;
                    progressText.textContent = `${currentStep}%`;
                    progressMessage.innerHTML = messages[progress];
                    
                    if (currentStep === 100) {
                        progressBar.classList.remove('bg-info');
                        progressBar.classList.add('bg-success');
                    }
                    
                    progress++;
                } else {
                    clearInterval(interval);
                    
                    setTimeout(() => {
                        // Mostrar mensaje de éxito final
                        Swal.fire({
                            icon: 'success',
                            title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Envío Exitoso!',
                            html: `<div class="text-start">
                                    <p>Los datos han sido enviados correctamente al servidor.</p>
                                    <div class="alert alert-success mb-0">
                                        <i class="bi bi-check-circle me-1"></i> Los archivos están siendo procesados en segundo plano.
                                    </div>
                                </div>`,
                            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Aceptar',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Resetear formulario
                            uploadForm.reset();
                            document.getElementById('saleDate').value = "{{ date('Y-m-d') }}";
                            document.getElementById('exchangeRate').value = "{{ $tasa['DivisaValor']['Valor'] ?? 0 }}";
                            document.getElementById('dailySalesFileName').innerHTML = '';
                            document.getElementById('salesBySellerFileName').innerHTML = '';
                            progressContainer.classList.add('d-none');
                            progressBar.style.width = '0%';
                            progressText.textContent = '0%';
                            progressBar.classList.remove('bg-success');
                            progressBar.classList.add('bg-info');
                            
                            // Habilitar botón de carga
                            loadButton.disabled = false;
                            loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
                        });
                    }, 1000);
                }
            }, 800);
        }

        // Función para mostrar mensaje de éxito
        function showSuccessMessage() {
            const dailyFile = dailySalesFile.files[0];
            const sellerFile = salesBySellerFile.files[0];
            
            // Crear objeto FormData con todos los datos
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('sucursal_id', "{{ session('sucursal_id', 0) }}");
            formData.append('sale_date', saleDate.value);
            formData.append('exchange_rate', exchangeRate.value);
            formData.append('daily_sales_file', dailyFile);
            formData.append('sales_by_seller_file', sellerFile);
            
            Swal.fire({
                icon: 'success',
                title: '<i class="bi bi-check-circle-fill text-success me-2"></i> ¡Carga Exitosa!',
                html: `
                    <div class="text-start">
                        <p>Los datos se han procesado correctamente:</p>
                        <div class="mb-2">
                            <strong><i class="bi bi-calendar me-1"></i> Fecha de Venta:</strong><br>
                            <small class="text-muted">${saleDate.value}</small>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-currency-exchange me-1"></i> Tasa de Cambio:</strong><br>
                            <small class="text-muted">${parseFloat(exchangeRate.value).toFixed(6)} Bs/USD</small>
                        </div>
                        <div class="mb-2">
                            <strong><i class="bi bi-file-earmark-excel me-1"></i> Venta Diaria:</strong><br>
                            <small class="text-muted">${dailyFile.name}</small>
                        </div>
                        <div class="mb-3">
                            <strong><i class="bi bi-file-earmark-excel me-1"></i> Ventas por Vendedores:</strong><br>
                            <small class="text-muted">${sellerFile.name}</small>
                        </div>
                        <div class="alert alert-info d-flex align-items-start mb-0">
                            <i class="bi bi-shop me-2 mt-1"></i>
                            <div>
                                <strong>Sucursal ID:</strong><br>
                                <small class="text-muted">{{ session('sucursal_id', 0) }}</small>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Continuar',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                // En un caso real, aquí enviarías el formData al servidor
                console.log('Datos a enviar:', {
                    sucursal_id: "{{ session('sucursal_id', 0) }}",
                    sale_date: saleDate.value,
                    exchange_rate: exchangeRate.value,
                    files: [dailyFile.name, sellerFile.name]
                });
                
                // Resetear formulario manteniendo valores por defecto
                uploadForm.reset();
                saleDate.value = "{{ date('Y-m-d') }}";
                exchangeRate.value = "{{ $tasaParalelo ?? 0 }}";
                
                dailySalesFileName.innerHTML = '';
                salesBySellerFileName.innerHTML = '';
                progressContainer.classList.add('d-none');
                progressBar.style.width = '0%';
                progressText.textContent = '0%';
                progressBar.classList.remove('bg-success');
                progressBar.classList.add('bg-info');
                
                // Habilitar botón de carga
                loadButton.disabled = false;
                loadButton.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Cargar Archivos';
            });
        }
    });
</script>

<style>
    .progress-bar {
        transition: width 0.3s ease-in-out;
    }
    
    .file-info {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
        margin-top: 5px;
        border-left: 4px solid #28a745;
        display: flex;
        align-items: center;
    }
    
    .file-info i {
        margin-right: 8px;
        color: #28a745;
        font-size: 1.1em;
    }
    
    /* Contenedor para el input file con botón */
    .file-input-container {
        position: relative;
    }
    
    /* Estilo para el grupo de input */
    .input-group {
        width: 100%;
    }
    
    /* Estilo para los botones de limpiar - MANTENIENDO LA ESTRUCTURA ORIGINAL */
    .input-group .btn-outline-secondary {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
        padding: 0.375rem 0.75rem;
        height: calc(2.25rem + 2px);
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
    }
    
    .input-group .btn-outline-secondary i {
        font-size: 1.1em;
        line-height: 1;
    }
    
    /* Asegurar que el input file ocupe todo el espacio */
    .input-group .form-control {
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
    }
    
    /* Estilo para mensajes de error - SEPARADO DEL INPUT GROUP */
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
        padding: 0.5rem;
        background-color: rgba(220, 53, 69, 0.05);
        border: 1px solid rgba(220, 53, 69, 0.2);
        border-radius: 0.25rem;
    }
    
    .invalid-feedback i {
        font-size: 1em;
    }
    
    /* Estilo cuando hay error - SIN AFECTAR EL BOTÓN */
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    /* Para los botones principales */
    .btn {
        min-width: 120px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn i {
        font-size: 1em;
    }
    
    /* Estilo para el alert con ícono */
    .alert-info i {
        font-size: 1.2em;
        color: #0dcaf0;
    }
    
    /* Estilo para el spinner del botón de carga */
    .spinner-border {
        width: 1em;
        height: 1em;
        border-width: 0.15em;
    }
    
    /* Estilo para los campos de fecha y tasa */
    .input-group-text {
        background-color: #f8f9fa;
    }
</style>

@endsection