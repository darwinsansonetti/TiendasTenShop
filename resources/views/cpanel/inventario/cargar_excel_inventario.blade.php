{{-- resources/views/cpanel/inventario/cargar_excel_inventario.blade.php --}}

@extends('layout.layout_dashboard')

@section('title', 'Cargar Inventario desde Excel')

@section('content')

@php
    $sucursalId = (int) session('sucursal_id', 0);
    $sucursalNombre = session('sucursal_nombre', 'Sin sucursal');
@endphp

<!-- Campos ocultos para JavaScript -->
<input type="hidden" id="sucursalIdHidden" value="{{ $sucursalId }}">
<input type="hidden" id="sucursalNombreHidden" value="{{ $sucursalNombre }}">

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                         style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="bi bi-file-earmark-excel text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Cargar Inventario desde Excel</h4>
                        <p class="mb-0 text-muted" style="font-size:0.78rem;">Actualiza el inventario de productos mediante archivo Excel</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('cpanel.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Cargar Inventario</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- ================================================ --}}
        {{-- CARD: INFORMACIÓN DE SUCURSAL --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-building me-2"></i>Sucursal Seleccionada
                    </h6>
                </div>
            </div>
            <div class="card-body py-3">
                <div id="infoSucursal">
                    @if($sucursalId > 0)
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span class="fw-semibold">Sucursal: {{ $sucursalNombre }}</span>
                        <span class="badge bg-success">Activa</span>
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        <span class="fw-semibold text-warning">No hay sucursal seleccionada</span>
                        <span class="badge bg-danger">Inactiva</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================================================ --}}
        {{-- CARD: FORMULARIO DE CARGA --}}
        {{-- ================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 py-3"
                 style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="bi bi-upload me-2"></i>Cargar Archivo Excel
                    </h6>
                    <span class="badge rounded-pill"
                          style="background:rgba(255,255,255,0.25);color:#fff;font-size:0.78rem;">
                        Inventario
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Campo oculto para sucursal_id -->
                    <input type="hidden" name="sucursal_id" value="{{ $sucursalId }}">
                    <input type="hidden" name="sucursal_nombre" value="{{ $sucursalNombre }}">

                    {{-- ================================================ --}}
                    {{-- NUEVO: SELECCIÓN DE TIPO DE INVENTARIO --}}
                    {{-- ================================================ --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-archive me-1"></i> Tipo de Inventario
                            <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Seleccione el tipo de inventario que desea cargar</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="tipo_inventario" 
                                           id="tipo_sucursal" 
                                           value="sucursal" 
                                           checked>
                                    <label class="form-check-label" for="tipo_sucursal">
                                        <i class="bi bi-building text-primary me-1"></i>
                                        <span class="fw-semibold">Inventario de Sucursal</span>
                                        <br>
                                        <small class="text-muted">Productos físicos en la sucursal</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="tipo_inventario" 
                                           id="tipo_almacen" 
                                           value="almacen">
                                    <label class="form-check-label" for="tipo_almacen">
                                        <i class="bi bi-archive text-warning me-1"></i>
                                        <span class="fw-semibold">Inventario de Almacén</span>
                                        <br>
                                        <small class="text-muted">Productos en bodega/almacén central</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- NUEVO: BOTÓN DE DESCARGA DE PLANTILLA --}}
                    {{-- ================================================ --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="btnDescargarPlantilla"
                                    disabled>
                                <i class="bi bi-download me-1"></i> Descargar Plantilla Excel
                            </button>
                            <span id="plantillaInfo" class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Seleccione "Inventario de Almacén" para descargar la plantilla
                            </span>
                        </div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- ARCHIVO EXCEL --}}
                    {{-- ================================================ --}}
                    <div class="mb-4">
                        <label for="excelFile" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-excel me-1"></i> Archivo Excel
                            <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Seleccione el archivo Excel con el inventario a cargar</p>
                        
                        <div class="file-input-container">
                            <div class="input-group">
                                <input type="file" 
                                       class="form-control" 
                                       id="excelFile" 
                                       name="excel_file"
                                       accept=".xlsx, .xls"
                                       required>
                            </div>
                            <div class="invalid-feedback" id="excelError" style="display: none;">
                                <i class="bi bi-exclamation-circle me-1"></i> Por favor seleccione el archivo Excel.
                            </div>
                        </div>
                        <div id="fileNameDisplay" class="mt-2 text-muted small"></div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- INFORMACIÓN ADICIONAL SEGÚN TIPO --}}
                    {{-- ================================================ --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div id="infoSucursalDetalle" class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>Inventario de Sucursal:</strong> El archivo debe contener las columnas: 
                                    <span class="fw-semibold">CÓDIGO</span>, 
                                    <span class="fw-semibold">CANTIDAD</span> (opcional: 
                                    <span class="fw-semibold">REFERENCIA</span>, 
                                    <span class="fw-semibold">PRODUCTO</span>)
                                </div>
                            </div>
                            <div id="infoAlmacenDetalle" class="alert alert-warning d-flex align-items-center d-none">
                                <i class="bi bi-archive me-2"></i>
                                <div>
                                    <strong>Inventario de Almacén:</strong> El archivo debe contener las columnas: 
                                    <span class="fw-semibold">CÓDIGO</span>, 
                                    <span class="fw-semibold">CANTIDAD</span>, 
                                    <span class="fw-semibold">UBICACIÓN</span> (opcional: 
                                    <span class="fw-semibold">REFERENCIA</span>, 
                                    <span class="fw-semibold">PRODUCTO</span>)
                                    <br>
                                    <small class="text-muted">La ubicación es obligatoria para inventario de almacén</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================================================ --}}
                    {{-- BOTONES DE ACCIÓN --}}
                    {{-- ================================================ --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" id="btnLimpiar">
                            <i class="bi bi-eraser me-1"></i> Limpiar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnGuardar">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Guardar
                        </button>
                    </div>
                </form>

                <!-- Contenedor de progreso -->
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
                        <i class="bi bi-hourglass-split me-1"></i> Procesando archivo, por favor espere...
                    </div>
                </div>

                <!-- Contenedor de resultados -->
                <div id="resultContainer" class="mt-4 d-none">
                    <div class="alert" id="resultAlert" role="alert">
                        <span id="resultMessage"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')

<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ==========================================
    // OBTENER SUCURSAL DESDE CAMPOS OCULTOS
    // ==========================================
    const SUCURSAL_ID = parseInt(document.getElementById('sucursalIdHidden').value) || 0;
    const SUCURSAL_NOMBRE = document.getElementById('sucursalNombreHidden').value || 'Sin sucursal';

    document.addEventListener('DOMContentLoaded', function () {

        // ==========================================
        // REFERENCIAS A ELEMENTOS
        // ==========================================
        const excelFile = document.getElementById('excelFile');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const uploadForm = document.getElementById('uploadForm');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnLimpiar = document.getElementById('btnLimpiar');
        const btnDescargarPlantilla = document.getElementById('btnDescargarPlantilla');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressMessage = document.getElementById('progressMessage');
        const resultContainer = document.getElementById('resultContainer');
        const resultAlert = document.getElementById('resultAlert');
        const resultMessage = document.getElementById('resultMessage');
        const excelError = document.getElementById('excelError');
        
        // Nuevos elementos
        const tipoSucursal = document.getElementById('tipo_sucursal');
        const tipoAlmacen = document.getElementById('tipo_almacen');
        const infoSucursalDetalle = document.getElementById('infoSucursalDetalle');
        const infoAlmacenDetalle = document.getElementById('infoAlmacenDetalle');
        const plantillaInfo = document.getElementById('plantillaInfo');

        // ==========================================
        // ACTUALIZAR ESTADO DEL BOTÓN GUARDAR
        // ==========================================
        function actualizarEstadoBoton() {
            const tieneSucursal = SUCURSAL_ID > 0;
            const esAlmacen = tipoAlmacen.checked;
            
            // ✅ El botón se activa si: hay sucursal O está seleccionado Almacén
            if (tieneSucursal || esAlmacen) {
                btnGuardar.disabled = false;
                btnGuardar.style.opacity = '1';
                btnGuardar.style.cursor = 'pointer';
            } else {
                btnGuardar.disabled = true;
                btnGuardar.style.opacity = '0.6';
                btnGuardar.style.cursor = 'not-allowed';
            }
        }

        // ==========================================
        // MANEJAR CAMBIO DE TIPO DE INVENTARIO
        // ==========================================
        function actualizarTipoInventario() {
            // ✅ Limpiar el archivo seleccionado al cambiar de tipo
            if (excelFile.files.length > 0) {
                excelFile.value = '';
                fileNameDisplay.innerHTML = '';
                excelFile.classList.remove('is-invalid');
                excelError.style.display = 'none';
                
                // Mostrar mensaje informativo (opcional)
                // Descomentar si quieres mostrar el mensaje
                /*
                Swal.fire({
                    icon: 'info',
                    title: 'Tipo de inventario cambiado',
                    text: 'El archivo seleccionado ha sido limpiado. Seleccione el archivo correspondiente al nuevo tipo de inventario.',
                    confirmButtonColor: '#10b981',
                    timer: 3000,
                    showConfirmButton: true
                });
                */
            }

            if (tipoAlmacen.checked) {
                // ✅ Seleccionado Almacén
                btnDescargarPlantilla.disabled = false;
                btnDescargarPlantilla.classList.remove('btn-outline-secondary');
                btnDescargarPlantilla.classList.add('btn-warning');
                btnDescargarPlantilla.innerHTML = '<i class="bi bi-download me-1"></i> Descargar Plantilla Almacén';
                
                plantillaInfo.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Plantilla disponible para descarga';
                
                infoSucursalDetalle.classList.add('d-none');
                infoAlmacenDetalle.classList.remove('d-none');
                
                // Actualizar texto del botón guardar
                btnGuardar.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Guardar Inventario de Almacén';
                
                // Actualizar el accept del input file para el tipo de archivo
                excelFile.setAttribute('accept', '.xlsx, .xls');
                
            } else {
                // ✅ Seleccionado Sucursal
                btnDescargarPlantilla.disabled = true;
                btnDescargarPlantilla.classList.remove('btn-warning');
                btnDescargarPlantilla.classList.add('btn-outline-secondary');
                btnDescargarPlantilla.innerHTML = '<i class="bi bi-download me-1"></i> Descargar Plantilla Excel';
                
                plantillaInfo.innerHTML = '<i class="bi bi-info-circle me-1"></i> Seleccione "Inventario de Almacén" para descargar la plantilla';
                
                infoSucursalDetalle.classList.remove('d-none');
                infoAlmacenDetalle.classList.add('d-none');
                
                // Actualizar texto del botón guardar
                btnGuardar.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Guardar Inventario de Sucursal';
                
                // Actualizar el accept del input file para el tipo de archivo
                excelFile.setAttribute('accept', '.xlsx, .xls');
            }
            
            // ✅ ACTUALIZAR EL ESTADO DEL BOTÓN GUARDAR
            actualizarEstadoBoton();
        }

        // Eventos para los radios
        tipoSucursal.addEventListener('change', actualizarTipoInventario);
        tipoAlmacen.addEventListener('change', actualizarTipoInventario);

        // Ejecutar al cargar para estado inicial
        actualizarTipoInventario();

        // ==========================================
        // DESCARGAR PLANTILLA
        // ==========================================
        btnDescargarPlantilla.addEventListener('click', function() {
            if (!tipoAlmacen.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione Almacén',
                    text: 'Debe seleccionar "Inventario de Almacén" para descargar la plantilla',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Generando plantilla...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // 👇 Crear FormData en lugar de JSON
            const formData = new FormData();
            formData.append('tipo', 'almacen');
            formData.append('sucursal_id', SUCURSAL_ID);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            // Descargar plantilla
            fetch('{{ route("cpanel.inventario.descargar-plantilla") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al generar la plantilla');
                }
                return response.blob();
            })
            .then(blob => {
                // Crear link de descarga
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `plantilla_almacen_${SUCURSAL_NOMBRE || 'general'}.xlsx`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                Swal.fire({
                    icon: 'success',
                    title: 'Plantilla descargada',
                    text: 'La plantilla se ha descargado correctamente',
                    confirmButtonColor: '#10b981',
                    timer: 3000
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo descargar la plantilla: ' + error.message,
                    confirmButtonColor: '#dc2626'
                });
            });
        });

        // ==========================================
        // MOSTRAR NOMBRE DEL ARCHIVO
        // ==========================================
        excelFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileNameDisplay.innerHTML = `
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
                this.classList.remove('is-invalid');
                excelError.style.display = 'none';
            } else {
                fileNameDisplay.innerHTML = '';
            }
        });

        // ==========================================
        // LIMPIAR FORMULARIO
        // ==========================================
        btnLimpiar.addEventListener('click', function() {
            excelFile.value = '';
            fileNameDisplay.innerHTML = '';
            excelFile.classList.remove('is-invalid');
            excelError.style.display = 'none';
            progressContainer.classList.add('d-none');
            resultContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-info');
            
            // Resetear selección a Sucursal
            tipoSucursal.checked = true;
            actualizarTipoInventario();
        });

        // ==========================================
        // ENVÍO DEL FORMULARIO
        // ==========================================
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // ✅ Validar que haya sucursal O sea inventario de almacén
            const tieneSucursal = SUCURSAL_ID > 0;
            const esAlmacen = tipoAlmacen.checked;
            
            if (!tieneSucursal && !esAlmacen) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione una opción',
                    text: 'Debe seleccionar una sucursal o activar "Inventario de Almacén"',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            // Validar tipo de inventario seleccionado
            const tipoInventario = document.querySelector('input[name="tipo_inventario"]:checked');
            if (!tipoInventario) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione tipo de inventario',
                    text: 'Debe seleccionar "Sucursal" o "Almacén"',
                    confirmButtonColor: '#d97706'
                });
                return;
            }

            // Validar que el archivo esté seleccionado
            if (!excelFile.files.length) {
                excelFile.classList.add('is-invalid');
                excelError.style.display = 'block';
                excelFile.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Validar extensión del archivo
            const fileName = excelFile.files[0].name;
            const extension = fileName.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls'].includes(extension)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato no válido',
                    text: 'Solo se permiten archivos .xlsx o .xls',
                    confirmButtonColor: '#dc2626'
                });
                excelFile.value = '';
                fileNameDisplay.innerHTML = '';
                return;
            }

            const tipoTexto = tipoInventario.value === 'almacen' ? 'Almacén' : 'Sucursal';
            
            // ✅ Mostrar mensaje según el tipo
            let mensajeConfirmacion;
            if (esAlmacen) {
                mensajeConfirmacion = `Se procesará el archivo "${excelFile.files[0].name}" como inventario de ${tipoTexto}`;
            } else {
                mensajeConfirmacion = `Se procesará el archivo "${excelFile.files[0].name}" como inventario de ${tipoTexto} en ${SUCURSAL_NOMBRE}`;
            }

            // Mostrar confirmación
            Swal.fire({
                icon: 'question',
                title: '¿Guardar inventario?',
                text: mensajeConfirmacion,
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarArchivo(tipoInventario.value);
                }
            });
        });

        // ==========================================
        // ENVIAR ARCHIVO AL SERVIDOR
        // ==========================================
        function enviarArchivo(tipoInventario) {
            // Deshabilitar botón
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';

            // Mostrar progreso
            progressContainer.classList.remove('d-none');
            resultContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            progressMessage.textContent = 'Iniciando carga...';
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-info');

            // Crear FormData
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            
            // ✅ Si es almacén, enviar sucursal_id = 0
            if (tipoInventario === 'almacen') {
                formData.append('sucursal_id', 6);
                formData.append('sucursal_nombre', 'ALMACEN');
            } else {
                formData.append('sucursal_id', SUCURSAL_ID);
                formData.append('sucursal_nombre', SUCURSAL_NOMBRE);
            }
            
            formData.append('tipo_inventario', tipoInventario);
            formData.append('excel_file', excelFile.files[0]);

            // Simular progreso
            let progress = 0;
            const simulateProgress = setInterval(() => {
                if (progress < 90) {
                    progress += Math.random() * 3 + 1;
                    progress = Math.min(progress, 90);
                    progressBar.style.width = `${Math.round(progress)}%`;
                    progressText.textContent = `${Math.round(progress)}%`;

                    if (progress < 30) {
                        progressMessage.textContent = 'Validando archivo...';
                    } else if (progress < 60) {
                        progressMessage.textContent = 'Procesando productos...';
                    } else if (progress < 90) {
                        progressMessage.textContent = 'Actualizando inventario...';
                    }
                }
            }, 500);

            // Enviar al servidor
            fetch('{{ route("cpanel.inventario.cargar-excel") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(simulateProgress);
                console.log('📦 Data recibida:', data);
                
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                progressMessage.textContent = 'Proceso completado';
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-success');

                setTimeout(() => {
                    progressContainer.classList.add('d-none');
                }, 500);

                if (data.success) {
                    let mensaje = '';
                    let icono = 'success';
                    let titulo = '✅ Proceso completado';
                    
                    mensaje += `<div style="text-align:left; font-size: 0.95rem; line-height: 1.8;">`;
                    mensaje += `<strong>📊 Productos procesados:</strong> ${data.total_filas || 0}<br>`;
                    mensaje += `<strong>✅ Productos actualizados:</strong> ${data.actualizados || 0}<br>`;
                    
                    if (data.productos_ingresados > 0) {
                        mensaje += `<strong>🆕 Productos ingresados:</strong> ${data.productos_ingresados}<br>`;
                    }
                    
                    if (data.productos_auditoria > 0) {
                        mensaje += `<strong>📋 Productos en auditoría:</strong> ${data.productos_auditoria}<br>`;
                    }
                    
                    if (data.no_encontrados && data.no_encontrados.length > 0) {
                        mensaje += `<strong>⚠️ Productos sin código:</strong> ${data.no_encontrados.length}<br>`;
                    }
                    
                    if (data.errores && data.errores.length > 0) {
                        mensaje += `<br><strong>❌ Errores:</strong> ${data.errores.length}`;
                        const erroresMostrar = data.errores.slice(0, 3);
                        erroresMostrar.forEach(err => {
                            mensaje += `<br><span style="color: #dc2626; font-size: 0.85rem;">• ${err}</span>`;
                        });
                        if (data.errores.length > 3) {
                            mensaje += `<br><span style="color: #6b7280; font-size: 0.85rem;">... y ${data.errores.length - 3} errores más</span>`;
                        }
                    }
                    
                    mensaje += `</div>`;
                    
                    Swal.fire({
                        icon: icono,
                        title: titulo,
                        html: mensaje,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Aceptar',
                        width: 500,
                        padding: '1.5rem',
                        showCloseButton: true
                    });
                    
                    console.log('✅ Procesamiento exitoso:', data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al leer el archivo',
                        confirmButtonColor: '#dc2626'
                    });
                    console.error('❌ Error en el procesamiento:', data);
                }
            })
            .catch(error => {
                clearInterval(simulateProgress);
                console.error('💥 Error en fetch:', error);
                
                setTimeout(() => {
                    progressContainer.classList.add('d-none');
                }, 500);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar el archivo: ' + (error.message || 'Verifica la conexión'),
                    confirmButtonColor: '#dc2626'
                });
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Guardar';
            });
        }

        // ==========================================
        // ESCUCHAR CAMBIOS DE SUCURSAL
        // ==========================================
        document.addEventListener('sucursalActualizada', function(e) {
            const nuevaSucursalId = e.detail.sucursalId || 0;
            const nuevaSucursalNombre = e.detail.sucursalNombre || 'Sin sucursal';
            
            // Actualizar variables
            window.SUCURSAL_ID = nuevaSucursalId;
            window.SUCURSAL_NOMBRE = nuevaSucursalNombre;
            
            // Actualizar campos ocultos
            document.getElementById('sucursalIdHidden').value = nuevaSucursalId;
            document.getElementById('sucursalNombreHidden').value = nuevaSucursalNombre;
            document.querySelector('input[name="sucursal_id"]').value = nuevaSucursalId;
            document.querySelector('input[name="sucursal_nombre"]').value = nuevaSucursalNombre;
            
            // Actualizar información de sucursal
            const infoSucursal = document.getElementById('infoSucursal');
            if (infoSucursal) {
                if (nuevaSucursalId > 0) {
                    infoSucursal.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="fw-semibold">Sucursal: ${nuevaSucursalNombre}</span>
                            <span class="badge bg-success">Activa</span>
                        </div>
                    `;
                } else {
                    infoSucursal.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <span class="fw-semibold text-warning">No hay sucursal seleccionada</span>
                            <span class="badge bg-danger">Inactiva</span>
                        </div>
                    `;
                }
            }
            
            // Actualizar estado del botón
            actualizarEstadoBoton();
        });
    });
</script>

<style>
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
    
    .file-input-container {
        position: relative;
    }
    
    .input-group .form-control {
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
    }
    
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
    
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    .progress-bar {
        transition: width 0.3s ease-in-out;
    }

    .form-check-inline {
        margin-right: 1.5rem;
    }

    .form-check-label {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .form-check-label:hover {
        background-color: #f8f9fa;
    }

    .form-check-input:checked + .form-check-label {
        background-color: #e8f5e9;
        border-radius: 0.5rem;
    }

    #btnDescargarPlantilla:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

@endsection