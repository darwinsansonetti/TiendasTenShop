<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - {{ $numeroOperacion }}</title>

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
        
        .container {
            max-width: 900px;
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
            padding: 20px 25px;
            text-align: center;
        }
        
        .logo-container {
            background: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .logo-container img {
            max-width: 150px;
            height: auto;
            display: block;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* Contenido */
        .content {
            padding: 25px;
        }
        
        /* Información del pago */
        .info-pago {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .info-pago h4 {
            color: #2b8c5e;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-pago table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-pago td {
            padding: 6px 0;
        }
        
        .info-pago td:first-child {
            width: 130px;
            font-weight: 600;
            color: #555;
        }
        
        /* Imagen del comprobante */
        .comprobante-container {
            text-align: center;
            margin: 20px 0;
            background: #fafafa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        
        .comprobante-container img {
            max-width: 100%;
            max-height: 600px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .comprobante-container .no-image {
            padding: 60px;
            color: #999;
            text-align: center;
        }
        
        .comprobante-container .no-image i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        /* Botones */
        .acciones {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #2b8c5e;
            color: white;
        }
        
        .btn-primary:hover {
            background: #236e4a;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        /* Pie */
        .footer {
            background: #f5f5f5;
            padding: 12px 25px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .acciones {
                display: none;
            }
            .btn-imprimir {
                display: none;
            }
            .header {
                background: #2b8c5e;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Asegurar que la imagen se muestre en impresión */
            .comprobante-container img {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 0 auto;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            /* Forzar que la imagen se cargue */
            img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
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
            <h1>COMPROBANTE DE PAGO</h1>
            <p>Documento escaneado / anexo</p>
        </div>
        
        <div class="content">
            <!-- Información del pago -->
            <div class="info-pago">
                <h4>📋 INFORMACIÓN DEL PAGO</h4>
                <table>
                    <tr><td><strong>N° de Pago:</strong></td><td>{{ $numeroOperacion }}</td>
                        <td style="width: 30px;"></td>
                        <td><strong>Fecha:</strong></td><td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                    </tr>
                    <tr><td><strong>Proveedor:</strong></td><td colspan="4">{{ $proveedor->Nombre ?? 'N/A' }}</td></tr>
                    <tr><td><strong>Monto Pagado:</strong></td><td colspan="4"><strong style="color: #2b8c5e;">$ {{ number_format($montoDivisa, 2) }}</strong></td></tr>
                </table>
            </div>
            
            <!-- Imagen del comprobante -->
            <div class="comprobante-container">
                @if($pago->UrlComprobante)
                    @php
                        // Obtener la URL completa de la imagen
                        $comprobanteUrl = asset('storage/images/comprobantes/' . $pago->UrlComprobante);
                        // También intentar con FileHelper si no existe
                        if (!file_exists(public_path('storage/images/comprobantes/' . $pago->UrlComprobante))) {
                            $comprobanteUrl = FileHelper::getOrDownloadFile(
                                'images/comprobantes/',
                                $pago->UrlComprobante,
                                null
                            );
                        }
                    @endphp
                    <img src="{{ $comprobanteUrl }}" 
                        alt="Comprobante de pago"
                        style="max-width: 100%; max-height: 600px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                @else
                    <div class="no-image">
                        <i class="bi bi-file-image"></i>
                        <p>No hay imagen de comprobante disponible</p>
                    </div>
                @endif
            </div>
            
            <!-- Botones de acción -->
            <div class="acciones">
                @if($comprobanteSrc && $comprobanteSrc != asset('assets/img/adminlte/img/no-image.png'))
                    <a href="{{ $comprobanteSrc }}" download class="btn btn-primary">
                        <i class="bi bi-download"></i> Descargar Comprobante
                    </a>
                @endif
                <button onclick="window.print();" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="{{ route('cpanel.pagos.detalle', $pago->ID) }}" class="btn btn-warning">
                    <i class="bi bi-arrow-left"></i> Volver al Detalle
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Este documento es una representación digital del comprobante de pago original.</p>
            <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>