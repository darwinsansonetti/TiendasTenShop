@extends('layout.layout_dashboard')

@section('title', 'TiendasTenShop | Solicitar Préstamo')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 me-1"
                     style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);">
                  <i class="bi bi-file-earmark-plus text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                  <h4 class="mb-0 fw-bold text-dark" style="font-size:1.1rem;">Solicitar Préstamo</h4>
                  <p class="mb-0 text-muted" style="font-size:0.78rem;">
                    <i class="bi bi-person me-1"></i>{{ $empleado->NombreCompleto }}
                    &nbsp;·&nbsp;<i class="bi bi-shop me-1"></i>{{ $sucursal->Nombre ?? 'N/A' }}
                    &nbsp;·&nbsp;ID: {{ $empleado->VendedorId ?? 'N/A' }}
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.dashboard') }}">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('cpanel.empleados.lista_empleados_prestamos') }}">Préstamos</a>
                    </li>
                    <li class="breadcrumb-item active">Solicitar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <ul class="nav nav-tabs card-header-tabs" id="prestamoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dinero-tab" data-bs-toggle="tab" data-bs-target="#dinero" type="button" role="tab" style="color: #007bff; background-color: white; border-radius: 5px;">
                            <i class="fas fa-money-bill-wave me-1"></i>Solicitar Dinero
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="producto-tab" data-bs-toggle="tab" data-bs-target="#producto" type="button" role="tab" style="color: white;">
                            <i class="fas fa-box me-1"></i>Solicitar Producto
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                
                <div class="tab-content" id="prestamoTabsContent">
                    
                    <!-- TAB: SOLICITAR DINERO -->
                    <div class="tab-pane fade show active" id="dinero" role="tabpanel">
                        <form action="{{ route('cpanel.empleados.prestamos.solicitar.guardar') }}" method="POST" id="formDinero">
                            @csrf
                            <input type="hidden" name="usuario_id" value="{{ $empleado->Id }}">
                            <input type="hidden" name="sucursal_id" value="{{ $sucursal->ID ?? '' }}">
                            <input type="hidden" name="tipo_prestamo" value="dinero">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Monto en Divisas (USD) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="number" 
                                               name="monto_divisa" 
                                               id="monto_divisa"
                                               class="form-control form-control-lg" 
                                               step="0.01" 
                                               placeholder="0.00"
                                               required>
                                    </div>
                                    <small class="text-muted">Ingrese el monto en dólares americanos</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Equivalente en Bolívares (Bs)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Bs</span>
                                        <input type="text" 
                                               id="monto_bs" 
                                               class="form-control form-control-lg bg-light" 
                                               readonly
                                               placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Calculado automáticamente según la tasa de cambio</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tasa de Cambio</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Bs/USD</span>
                                        <input type="text" 
                                               id="tasa_cambio" 
                                               class="form-control bg-light" 
                                               value="{{ number_format($tasa->Valor ?? 475.01, 2) }}" 
                                               readonly>
                                    </div>
                                    <small class="text-muted">Tasa del día (no editable)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha del Préstamo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar"></i></span>
                                        <input type="date" 
                                               name="fecha_prestamo" 
                                               class="form-control bg-light" 
                                               value="{{ date('Y-m-d') }}"
                                               readonly>
                                    </div>
                                    <small class="text-muted">Fecha actual del sistema</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Motivo / Observación</label>
                                    <textarea name="observacion" class="form-control" rows="4" placeholder="Ej: Adelanto de sueldo, Emergencia, Compra de insumos, etc."></textarea>
                                    <small class="text-muted">Describa el motivo del préstamo (opcional)</small>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <a href="{{ route('cpanel.empleados.lista_empleados_prestamos') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Solicitar Préstamo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- TAB: SOLICITAR PRODUCTO -->
                    <div class="tab-pane fade" id="producto" role="tabpanel">
                        <form action="{{ route('cpanel.empleados.prestamos.solicitar.guardar') }}" method="POST" id="formProducto">
                            @csrf
                            <input type="hidden" name="usuario_id" value="{{ $empleado->Id }}">
                            <input type="hidden" name="sucursal_id" value="{{ $sucursal->ID ?? '' }}">
                            <input type="hidden" name="tipo_prestamo" value="producto">
                            
                            <!-- Búsqueda de producto -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Buscar Producto</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               id="codigo_producto" 
                                               class="form-control" 
                                               placeholder="Código de barras o código del producto">
                                        <button type="button" class="btn btn-primary" onclick="buscarProducto()">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                    </div>
                                    <small class="text-muted">Escanee o ingrese el código del producto</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Cantidad</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               id="cantidad_producto" 
                                               class="form-control" 
                                               value="1" 
                                               min="1">
                                        <button type="button" class="btn btn-success" onclick="agregarProducto()">
                                            <i class="fas fa-plus me-1"></i>Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del producto encontrado -->
                            <div id="info_producto" class="alert alert-info" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong id="producto_nombre"></strong><br>
                                        <small id="producto_codigo"></small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <strong>Precio: $<span id="producto_precio">0.00</span></strong>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lista de productos seleccionados -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Productos Seleccionados</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="tabla_productos">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio USD</th>
                                                    <th>Total USD</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="lista_productos">
                                                <tr><td colspan="6" class="text-center text-muted">No hay productos agregados</td></tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                                                    <td class="text-end fw-bold text-success" id="total_productos">$0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <a href="{{ route('cpanel.empleados.lista_empleados_prestamos') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </a>
                                    <button type="button" class="btn btn-primary" onclick="procesarPrestamoProducto()">
                                        <i class="fas fa-save me-1"></i>Solicitar Préstamo
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let productosSeleccionados = [];
    let tasaCambio = parseFloat('{{ number_format($tasa->Valor, 2, '.', '') }}');
    let empleadoData = @json($empleado);
    let sucursalData = @json($sucursal);
    
    // ============================================
    // FUNCIONES PARA PRÉSTAMO DE DINERO
    // ============================================
    const montoDivisa = document.getElementById('monto_divisa');
    const montoBsSpan = document.getElementById('monto_bs');
    
    function calcularMontoBs() {
        const monto = parseFloat(montoDivisa?.value) || 0;
        const montoBs = monto * tasaCambio;
        if (montoBsSpan) {
            montoBsSpan.value = montoBs.toFixed(2);
        }
    }
    
    if (montoDivisa) {
        montoDivisa.addEventListener('input', calcularMontoBs);
        montoDivisa.addEventListener('keyup', calcularMontoBs);
        calcularMontoBs();
    }
    
    // ============================================
    // MANEJAR SOLICITUD DE DINERO CON FETCH
    // ============================================
    const formDinero = document.getElementById('formDinero');
    if (formDinero) {
        formDinero.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const monto = parseFloat(document.getElementById('monto_divisa').value) || 0;
            
            if (monto <= 0) {
                Swal.fire('Error', 'Debe ingresar un monto válido mayor a cero', 'error');
                return;
            }
            
            Swal.fire({
                title: 'Procesando...',
                text: 'Creando préstamo en dinero',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData(this);
            
            try {
                const url = '{{ route("cpanel.empleados.prestamos.solicitar.guardar") }}';
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Generar PDF después del éxito
                    try {
                        const observacion = document.querySelector('#dinero textarea[name="observacion"]')?.value || 'Préstamo en dinero';
                        const datosPago = {
                            tipo: 'solicitud_dinero',
                            fecha: new Date().toISOString().split('T')[0],
                            monto: monto,
                            monto_bs: monto * tasaCambio,
                            tasa: tasaCambio,
                            descripcion: observacion
                        };
                        
                        setTimeout(() => {
                            generarComprobanteSolicitudPDF(datosPago, null, empleadoData);
                        }, 500);
                    } catch (pdfError) {
                        console.error('Error al generar PDF:', pdfError);
                    }
                    
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("cpanel.empleados.lista_empleados_prestamos") }}';
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al procesar el préstamo', 'error');
            }
        });
    }
    
    // ============================================
    // FUNCIONES PARA PRÉSTAMO DE PRODUCTO
    // ============================================
    
    // Buscar producto por código
    async function buscarProducto() {
        const codigo = document.getElementById('codigo_producto').value.trim();
        
        if (!codigo) {
            Swal.fire('Error', 'Ingrese un código de producto', 'warning');
            return;
        }
        
        try {
            const url = '{{ route("cpanel.empleados.prestamos.buscar_producto") }}?codigo=' + encodeURIComponent(codigo);
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                const infoDiv = document.getElementById('info_producto');
                document.getElementById('producto_nombre').textContent = data.producto.Descripcion;
                document.getElementById('producto_codigo').textContent = `Código: ${data.producto.Codigo} | Referencia: ${data.producto.Referencia || 'N/A'}`;
                
                let precio = parseFloat(data.producto.CostoDivisa) || 0;
                document.getElementById('producto_precio').textContent = precio.toFixed(2);
                infoDiv.style.display = 'block';
                
                window.productoActual = {
                    ...data.producto,
                    CostoDivisa: precio
                };
            } else {
                Swal.fire('Error', data.message, 'error');
                document.getElementById('info_producto').style.display = 'none';
                window.productoActual = null;
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al buscar el producto', 'error');
        }
    }
    
    // Agregar producto a la lista
    function agregarProducto() {
        if (!window.productoActual) {
            Swal.fire('Error', 'Primero busque un producto', 'warning');
            return;
        }
        
        const cantidad = parseInt(document.getElementById('cantidad_producto').value) || 1;
        if (cantidad <= 0) {
            Swal.fire('Error', 'La cantidad debe ser mayor a 0', 'warning');
            return;
        }
        
        const producto = window.productoActual;
        const precio = parseFloat(producto.CostoDivisa) || 0;
        
        const existente = productosSeleccionados.find(p => p.id === producto.ID);
        
        if (existente) {
            existente.cantidad += cantidad;
            existente.total = existente.cantidad * existente.precio;
        } else {
            productosSeleccionados.push({
                id: producto.ID,
                codigo: producto.Codigo,
                nombre: producto.Descripcion,
                precio: precio,
                cantidad: cantidad,
                total: cantidad * precio
            });
        }
        
        actualizarListaProductos();
        
        document.getElementById('codigo_producto').value = '';
        document.getElementById('cantidad_producto').value = '1';
        document.getElementById('info_producto').style.display = 'none';
        window.productoActual = null;
    }
    
    // Eliminar producto de la lista
    function eliminarProducto(index) {
        productosSeleccionados.splice(index, 1);
        actualizarListaProductos();
    }
    
    // Actualizar tabla de productos
    function actualizarListaProductos() {
        const tbody = document.getElementById('lista_productos');
        const totalSpan = document.getElementById('total_productos');
        let totalGeneral = 0;
        
        if (productosSeleccionados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay productos agregados</td></tr>';
            totalSpan.textContent = '$0.00';
            return;
        }
        
        let html = '';
        productosSeleccionados.forEach((p, index) => {
            totalGeneral += p.total;
            html += `
                <tr>
                    <td>${p.codigo}</td>
                    <td>${p.nombre}</td>
                    <td class="text-center">${p.cantidad}</td>
                    <td class="text-end">$${p.precio.toFixed(2)}</td>
                    <td class="text-end">$${p.total.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        totalSpan.textContent = `$${totalGeneral.toFixed(2)}`;
    }
    
    // Procesar préstamo de producto
    async function procesarPrestamoProducto() {
        if (productosSeleccionados.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos un producto', 'warning');
            return;
        }
        
        const observacion = document.getElementById('observacion_producto')?.value || '';
        
        const formData = new FormData();
        formData.append('usuario_id', '{{ $empleado->Id }}');
        formData.append('sucursal_id', '{{ $sucursal->ID ?? "" }}');
        formData.append('tipo_prestamo', 'producto');
        formData.append('observacion', observacion);
        formData.append('productos', JSON.stringify(productosSeleccionados));
        
        Swal.fire({
            title: 'Procesando...',
            text: 'Creando préstamo de productos',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        try {
            const url = '{{ route("cpanel.empleados.prestamos.solicitar.guardar") }}';
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const totalGeneral = productosSeleccionados.reduce((sum, p) => sum + p.total, 0);
                
                // Generar PDF después del éxito
                try {
                    const datosPago = {
                        tipo: 'solicitud_producto',
                        fecha: new Date().toISOString().split('T')[0],
                        monto: totalGeneral,
                        monto_bs: totalGeneral * tasaCambio,
                        tasa: tasaCambio,
                        descripcion: observacion || 'Préstamo de productos',
                        productos: productosSeleccionados
                    };
                    
                    setTimeout(() => {
                        generarComprobanteSolicitudPDF(datosPago, productosSeleccionados, empleadoData);
                    }, 500);
                } catch (pdfError) {
                    console.error('Error al generar PDF:', pdfError);
                }
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '{{ route("cpanel.empleados.lista_empleados_prestamos") }}';
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar el préstamo', 'error');
        }
    }
    
    // Enter para buscar producto
    document.getElementById('codigo_producto')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarProducto();
        }
    });
    
    // Manejar estilos de tabs
    document.querySelectorAll('#prestamoTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            document.querySelectorAll('#prestamoTabs .nav-link').forEach(btn => {
                btn.style.color = 'white';
                btn.style.backgroundColor = 'transparent';
                btn.style.borderRadius = '0';
            });
            
            const activeTab = document.querySelector('#prestamoTabs .nav-link.active');
            if (activeTab) {
                activeTab.style.color = '#007bff';
                activeTab.style.backgroundColor = 'white';
                activeTab.style.borderRadius = '5px';
            }
        });
    });
    
    // ============================================
    // FUNCIÓN PARA GENERAR PDF
    // ============================================
    function generarComprobanteSolicitudPDF(datosPago, productos, empleado, sucursal) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const pageWidth = doc.internal.pageSize.getWidth();
        let yPos = 15;
        
        // ============================================
        // ENCABEZADO CON LOGO (TEXTO ESTILIZADO)
        // ============================================
        // Logo o nombre de la empresa
        doc.setFillColor(41, 128, 185);
        doc.rect(0, 0, pageWidth, 45, 'F');
        
        doc.setFontSize(22);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text('TIENDAS TEN SHOP', pageWidth / 2, 20, { align: 'center' });
        
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.text('Comprobante de Solicitud de Préstamo', pageWidth / 2, 32, { align: 'center' });
        
        doc.setFontSize(9);
        doc.setTextColor(230, 230, 230);
        doc.text(`Generado: ${new Date().toLocaleString('es-VE')}`, pageWidth / 2, 40, { align: 'center' });
        
        yPos = 55;
        
        // ============================================
        // INFORMACIÓN DEL SOLICITANTE (TARJETA)
        // ============================================
        // Fondo gris claro
        doc.setFillColor(245, 245, 245);
        doc.roundedRect(14, yPos, pageWidth - 28, 65, 3, 3, 'F');
        
        doc.setFontSize(11);
        doc.setTextColor(41, 128, 185);
        doc.setFont('helvetica', 'bold');
        doc.text('INFORMACIÓN DEL SOLICITANTE', 20, yPos + 7);
        
        doc.setDrawColor(200, 200, 200);
        doc.line(20, yPos + 12, pageWidth - 20, yPos + 12);
        
        doc.setFontSize(9);
        doc.setTextColor(60, 60, 60);
        doc.setFont('helvetica', 'normal');
        
        // Columna izquierda
        doc.text('Nombre completo:', 20, yPos + 22);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.NombreCompleto || 'N/A', 70, yPos + 22);
        
        doc.setFont('helvetica', 'normal');
        doc.text('Vendedor ID:', 20, yPos + 32);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.VendedorId || 'N/A', 70, yPos + 32);
        
        doc.setFont('helvetica', 'normal');
        doc.text('Sucursal:', 20, yPos + 42);
        doc.setFont('helvetica', 'bold');
        doc.text(sucursal?.Nombre || 'N/A', 70, yPos + 42);
        
        // Columna derecha
        doc.setFont('helvetica', 'normal');
        doc.text('Teléfono:', pageWidth - 80, yPos + 22);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.PhoneNumber || empleado.telefono || 'N/A', pageWidth - 50, yPos + 22);
        
        doc.setFont('helvetica', 'normal');
        doc.text('Email:', pageWidth - 80, yPos + 32);
        doc.setFont('helvetica', 'bold');
        doc.text(empleado.Email || 'N/A', pageWidth - 50, yPos + 32);
        
        doc.setFont('helvetica', 'normal');
        doc.text('Fecha solicitud:', pageWidth - 80, yPos + 42);
        doc.setFont('helvetica', 'bold');
        doc.text(new Date().toLocaleDateString('es-VE'), pageWidth - 50, yPos + 42);
        
        yPos += 75;
        
        // ============================================
        // DETALLE DE LA SOLICITUD
        // ============================================
        doc.setFillColor(41, 128, 185);
        doc.roundedRect(14, yPos, pageWidth - 28, 10, 3, 3, 'F');
        doc.setFontSize(10);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text('DETALLE DE LA SOLICITUD', pageWidth / 2, yPos + 7, { align: 'center' });
        
        yPos += 15;
        
        doc.setFontSize(9);
        doc.setTextColor(60, 60, 60);
        doc.setFont('helvetica', 'normal');
        
        if (datosPago.tipo === 'solicitud_dinero') {
            // Tarjeta de resumen de dinero
            doc.setFillColor(240, 248, 255);
            doc.roundedRect(14, yPos, pageWidth - 28, 45, 3, 3, 'F');
            
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text('Tipo de préstamo:', 20, yPos + 10);
            doc.setTextColor(40, 167, 69);
            doc.text('DINERO', 80, yPos + 10);
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('Monto solicitado:', 20, yPos + 22);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(`$${datosPago.monto.toFixed(2)} USD`, 70, yPos + 22);
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            doc.text('Equivalente en Bs:', 20, yPos + 34);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(0, 0, 0);
            doc.text(`Bs ${datosPago.monto_bs.toFixed(2)}`, 70, yPos + 34);
            
            doc.setFont('helvetica', 'normal');
            doc.text('Tasa de cambio:', pageWidth - 70, yPos + 22);
            doc.setFont('helvetica', 'bold');
            doc.text(`Bs ${datosPago.tasa.toFixed(2)}`, pageWidth - 40, yPos + 22);
            
            yPos += 55;
            
        } else {
            // Tabla de productos mejorada
            if (productos && productos.length > 0) {
                const headers = [['Código', 'Producto', 'Cantidad', 'Precio USD', 'Total USD']];
                const body = productos.map(p => [
                    p.codigo || 'N/A',
                    p.nombre || 'N/A',
                    p.cantidad.toString(),
                    `$${p.precio.toFixed(2)}`,
                    `$${p.total.toFixed(2)}`
                ]);
                
                doc.autoTable({
                    head: headers,
                    body: body,
                    startY: yPos,
                    theme: 'grid',
                    headStyles: { 
                        fillColor: [41, 128, 185], 
                        textColor: 255, 
                        fontSize: 9, 
                        fontStyle: 'bold',
                        halign: 'center'
                    },
                    bodyStyles: { fontSize: 8, cellPadding: 4 },
                    alternateRowStyles: { fillColor: [245, 245, 245] },
                    columnStyles: {
                        0: { cellWidth: 25, halign: 'center' },
                        1: { cellWidth: 70 },
                        2: { cellWidth: 20, halign: 'center' },
                        3: { cellWidth: 30, halign: 'right' },
                        4: { cellWidth: 30, halign: 'right' }
                    },
                    margin: { left: 14, right: 14 }
                });
                
                yPos = doc.lastAutoTable.finalY + 10;
                
                // Resumen de totales
                doc.setFillColor(240, 248, 255);
                doc.roundedRect(14, yPos, pageWidth - 28, 35, 3, 3, 'F');
                
                doc.setFontSize(10);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(0, 0, 0);
                doc.text('RESUMEN DE LA SOLICITUD', pageWidth / 2, yPos + 7, { align: 'center' });
                
                doc.setFontSize(9);
                doc.setFont('helvetica', 'normal');
                doc.text('Total productos:', 20, yPos + 18);
                doc.setFont('helvetica', 'bold');
                doc.text(`${productos.length} producto(s)`, 70, yPos + 18);
                
                doc.setFont('helvetica', 'normal');
                doc.text('Total solicitado:', 20, yPos + 28);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(40, 167, 69);
                doc.text(`$${datosPago.monto.toFixed(2)} USD`, 70, yPos + 28);
                
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(60, 60, 60);
                doc.text('Equivalente en Bs:', pageWidth - 70, yPos + 18);
                doc.setFont('helvetica', 'bold');
                doc.text(`Bs ${datosPago.monto_bs.toFixed(2)}`, pageWidth - 45, yPos + 18);
                
                doc.setFont('helvetica', 'normal');
                doc.text('Tasa de cambio:', pageWidth - 70, yPos + 28);
                doc.setFont('helvetica', 'bold');
                doc.text(`Bs ${datosPago.tasa.toFixed(2)}`, pageWidth - 45, yPos + 28);
                
                yPos += 45;
            }
        }
        
        // ============================================
        // OBSERVACIÓN
        // ============================================
        if (datosPago.descripcion) {
            doc.setFillColor(255, 248, 225);
            doc.roundedRect(14, yPos, pageWidth - 28, 25, 3, 3, 'F');
            
            doc.setFontSize(9);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(200, 100, 0);
            doc.text('Observación:', 20, yPos + 7);
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(60, 60, 60);
            // Manejar texto largo con wrap
            const observacionLines = doc.splitTextToSize(datosPago.descripcion, pageWidth - 48);
            doc.text(observacionLines, 20, yPos + 15);
            
            yPos += 35;
        } else {
            yPos += 5;
        }
        
        // ============================================
        // FIRMAS
        // ============================================
        yPos += 10;
        
        doc.setDrawColor(150, 150, 150);
        doc.setLineWidth(0.5);
        
        // Línea firma solicitante
        doc.line(25, yPos, 85, yPos);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(100, 100, 100);
        doc.text('Firma del solicitante', 55, yPos + 4, { align: 'center' });
        
        // Línea firma autorizado
        doc.line(pageWidth - 85, yPos, pageWidth - 25, yPos);
        doc.text('Firma autorizado', pageWidth - 55, yPos + 4, { align: 'center' });
        
        yPos += 20;
        
        // ============================================
        // PIE DE PÁGINA
        // ============================================
        const añoActual = new Date().getFullYear();
        doc.setFillColor(41, 128, 185);
        doc.rect(0, doc.internal.pageSize.getHeight() - 20, pageWidth, 20, 'F');
        
        doc.setFontSize(7);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(255, 255, 255);
        doc.text('Este documento es un comprobante válido de solicitud de préstamo', pageWidth / 2, doc.internal.pageSize.getHeight() - 12, { align: 'center' });
        doc.text(`© ${añoActual} TiendasTenShop - Todos los derechos reservados`, pageWidth / 2, doc.internal.pageSize.getHeight() - 6, { align: 'center' });
        
        // ============================================
        // GUARDAR PDF
        // ============================================
        const nombreArchivo = `Solicitud_Prestamo_${(empleado.NombreCompleto || 'empleado').replace(/\s/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`;
        doc.save(nombreArchivo);
    }
</script>

<style>
    .card-header .nav-link.active {
        color: #007bff !important;
        background-color: white !important;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .card-header .nav-link {
        color: white !important;
    }
    
    .form-control-lg {
        font-size: 1.25rem;
        font-weight: bold;
    }
    
    .input-group-text {
        font-weight: bold;
    }
    
    #tabla_productos tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>
@endsection