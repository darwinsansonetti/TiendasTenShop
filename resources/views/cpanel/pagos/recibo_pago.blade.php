<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - {{ $numeroComprobante }}</title>

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e0e0e0;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .recibo {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        /* Encabezado */
        .header {
            background: linear-gradient(135deg, #1e5799 0%, #2b8c5e 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Contenido */
        .content {
            padding: 30px;
        }
        
        /* Secciones */
        .seccion {
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .seccion-titulo {
            background: #f5f5f5;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 16px;
            color: #333;
            border-bottom: 2px solid #2b8c5e;
        }
        
        .seccion-titulo i {
            margin-right: 8px;
            color: #2b8c5e;
        }
        
        .seccion-cuerpo {
            padding: 15px;
        }
        
        /* Tablas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 140px;
            font-weight: 600;
            color: #555;
        }
        
        .info-table td:last-child {
            color: #333;
        }
        
        /* Montos */
        .montos {
            background: #f0f9f0;
        }
        
        .monto-principal {
            font-size: 24px;
            font-weight: bold;
            color: #2b8c5e;
        }
        
        /* Firma */
        .firma-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
        }
        
        .firma-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .firma-box {
            text-align: center;
            width: 45%;
        }
        
        .firma-linea {
            border-top: 1px solid #333;
            width: 100%;
            margin-top: 40px;
            margin-bottom: 8px;
        }
        
        .firma-label {
            font-size: 12px;
            color: #666;
        }
        
        .huella {
            font-size: 40px;
            text-align: center;
            margin: 20px 0;
        }
        
        /* Pie */
        .footer {
            background: #f5f5f5;
            padding: 15px 30px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        
        /* Botón imprimir */
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2b8c5e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-imprimir:hover {
            background: #236e4a;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .btn-imprimir {
                display: none;
            }
            .recibo {
                box-shadow: none;
                border-radius: 0;
            }
            .header {
                background: #2b8c5e;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .seccion-titulo {
                background: #f5f5f5;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <button class="btn-imprimir" onclick="window.print();">
        <i class="bi bi-printer"></i> Imprimir / Guardar PDF
    </button>
    
    <div class="recibo">
        <!-- Encabezado -->
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <!-- Logo izquierdo -->
                <div style="background: white; padding: 8px 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <img src="{{ asset('assets/img/calzatodo.png') }}" 
                        alt="Logo Calzatodo" 
                        style="height: 60px; width: auto; display: block;">
                </div>
                
                <!-- Logo derecho -->
                <div style="background: white; padding: 8px 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <img src="{{ asset('assets/img/LogoTenShop.jpg') }}" 
                        alt="Logo TenShop" 
                        style="height: 60px; width: auto; display: block;">
                </div>
            </div>
            <h1>RECIBO DE PAGO</h1>
            <p>Comprobante de pago a proveedor</p>
        </div>
        
        <div class="content">
            <!-- Información del pago -->
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📄</i> INFORMACIÓN DEL PAGO
                </div>
                <div class="seccion-cuerpo">
                    <table class="info-table">
                        <tr>
                            <td>Sucursal:</td>
                            <td><strong>{{ $sucursal->Nombre ?? 'OFICINA PRINCIPAL' }}</strong></td>
                        </tr>
                        <tr>
                            <td>N° de Pago:</td>
                            <td><strong>{{ $numeroComprobante }}</strong></td>
                        </tr>
                        <tr>
                            <td>Fecha:</td>
                            <td>{{ \Carbon\Carbon::parse($pago->Fecha)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Descripción:</td>
                            <td>{{ $pago->Descripcion ?? 'Pago registrado' }}</td>
                        </tr>
                        <tr>
                            <td>Forma de Pago:</td>
                            <td>{{ $formaPagoTexto }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Datos del Proveedor -->
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>🏢</i> DATOS DEL PROVEEDOR
                </div>
                <div class="seccion-cuerpo">
                    <table class="info-table">
                        <tr>
                            <td>Nombre / Razón Social:</td>
                            <td><strong>{{ $proveedor->Nombre ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td>RIF / Cédula:</td>
                            <td>{{ $proveedor->Rif_Cedula ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Dirección:</td>
                            <td>{{ $proveedor->Direccion ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Teléfono:</td>
                            <td>{{ $proveedor->TelefonoMovil ?? $proveedor->TelefonoFijo ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>{{ $proveedor->CorreoElectronico ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Montos Pagados -->
            <div class="seccion montos">
                <div class="seccion-titulo">
                    <i>💰</i> MONTO PAGADO
                </div>
                <div class="seccion-cuerpo">
                    <table class="info-table">
                        <tr>
                            <td>Monto en Divisas (USD):</td>
                            <td class="monto-principal">$ {{ number_format($montoDivisa, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Tasa de Cambio aplicada:</td>
                            <td>Bs {{ number_format($tasaCambio, 2) }} por USD</td>
                        </tr>
                        <tr>
                            <td>Monto en Bolívares (Bs):</td>
                            <td class="monto-principal">Bs {{ number_format($montoBs, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Información de la Factura -->
            @if($factura)
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📑</i> FACTURA ASOCIADA
                </div>
                <div class="seccion-cuerpo">
                    <table class="info-table">
                        <tr>
                            <td>N° Factura:</td>
                            <td><strong>{{ $factura->Numero }}</strong></td>
                        </tr>
                        <tr>
                            <td>Fecha Factura:</td>
                            <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
            
            <!-- Firma y Huella -->
            <div class="firma-section">
                <div class="huella">
                    <i class="bi bi-hand-index-thumb"></i> 
                    <span style="font-size: 14px;">Huella digital</span>
                </div>
                
                <div class="firma-container">
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                        <div class="firma-label">FIRMA DEL PROVEEDOR</div>
                    </div>
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                        <div class="firma-label">FIRMA DEL RECIBIDOR</div>
                    </div>
                </div>
                
                <div class="firma-container" style="margin-top: 20px;">
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                        <div class="firma-label">NOMBRE</div>
                    </div>
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                        <div class="firma-label">CÉDULA</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pie -->
        <div class="footer">
            <p>Este documento es un comprobante de pago válido para efectos contables.</p>
            <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    
    <!-- Bootstrap Icons para el botón (opcional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>