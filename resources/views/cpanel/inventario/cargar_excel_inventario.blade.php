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

                    <!-- Información del archivo -->
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

                    <!-- Información adicional -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>Formato requerido:</strong> El archivo debe contener las columnas: 
                                    <span class="fw-semibold">CÓDIGO</span>, 
                                    <span class="fw-semibold">CANTIDAD</span> (opcional: 
                                    <span class="fw-semibold">REFERENCIA</span>, 
                                    <span class="fw-semibold">PRODUCTO</span>)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" id="btnLimpiar">
                            <i class="bi bi-eraser me-1"></i> Limpiar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnGuardar" {{ $sucursalId <= 0 ? 'disabled' : '' }}>
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
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressMessage = document.getElementById('progressMessage');
        const resultContainer = document.getElementById('resultContainer');
        const resultAlert = document.getElementById('resultAlert');
        const resultMessage = document.getElementById('resultMessage');
        const excelError = document.getElementById('excelError');

        // ==========================================
        // ACTUALIZAR ESTADO DEL BOTÓN
        // ==========================================
        function actualizarEstadoBoton() {
            if (SUCURSAL_ID <= 0) {
                btnGuardar.disabled = true;
                btnGuardar.style.opacity = '0.6';
                btnGuardar.style.cursor = 'not-allowed';
            } else {
                btnGuardar.disabled = false;
                btnGuardar.style.opacity = '1';
                btnGuardar.style.cursor = 'pointer';
            }
        }

        actualizarEstadoBoton();

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
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Guardar';
        });

        // ==========================================
        // ENVÍO DEL FORMULARIO
        // ==========================================
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validar que haya sucursal
            if (SUCURSAL_ID <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sucursal no seleccionada',
                    text: 'Debes seleccionar una sucursal para cargar el inventario',
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

            // Mostrar confirmación
            Swal.fire({
                icon: 'question',
                title: '¿Guardar inventario?',
                text: `Se procesará el archivo "${excelFile.files[0].name}" en la sucursal ${SUCURSAL_NOMBRE}`,
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarArchivo();
                }
            });
        });

        // ==========================================
        // ENVIAR ARCHIVO AL SERVIDOR
        // ==========================================
        function enviarArchivo() {
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
            formData.append('sucursal_id', SUCURSAL_ID);
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
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                progressMessage.textContent = 'Proceso completado';
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-success');

                if (data.success) {
                    // Mostrar los datos del Excel
                    let mensaje = `✅ ${data.message}\n\n`;
                    mensaje += `📊 Total de filas: ${data.total_filas}\n`;
                    mensaje += `📋 Encabezados: ${data.encabezados.join(', ')}\n\n`;
                    mensaje += `📄 Datos:\n`;
                    
                    // Mostrar primeras 5 filas como ejemplo
                    const preview = data.datos.slice(0, 5);
                    preview.forEach((fila, index) => {
                        mensaje += `\nFila ${index + 1}: `;
                        mensaje += Object.entries(fila)
                            .map(([key, value]) => `${key}: ${value}`)
                            .join(' | ');
                    });
                    
                    if (data.datos.length > 5) {
                        mensaje += `\n\n... y ${data.datos.length - 5} filas más`;
                    }
                    
                    mostrarResultado('success', mensaje);
                    
                    // También puedes mostrar en consola para depuración
                    console.log('Datos completos:', data.datos);
                    
                } else {
                    mostrarResultado('danger', data.message || 'Error al leer el archivo');
                }
            })
            .catch(error => {
                clearInterval(simulateProgress);
                console.error('Error:', error);
                mostrarResultado('danger', 'Error al enviar el archivo. Verifica la conexión.');
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Guardar';
            });
        }

        // ==========================================
        // MOSTRAR RESULTADO
        // ==========================================
        function mostrarResultado(tipo, mensaje) {
            resultContainer.classList.remove('d-none');
            resultAlert.className = `alert alert-${tipo}`;
            resultMessage.innerHTML = mensaje.replace(/\n/g, '<br>');
            
            // Scroll al resultado
            resultContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
</style>

@endsection