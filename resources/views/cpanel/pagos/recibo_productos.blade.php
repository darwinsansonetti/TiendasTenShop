<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Productos - Factura {{ $factura->Numero }}</title>

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
            max-width: 1100px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1e5799 0%, #2b8c5e 100%);
            padding: 20px 25px;
            text-align: center;
        }
        
        .logo-container {
            background: white;
            padding: 8px 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .logo-container img {
            height: 60px;
            width: auto;
            display: block;
        }
        
        .header h1 {
            color: white;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
        }
        
        .content {
            padding: 25px;
        }
        
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
            font-size: 14px;
            border-bottom: 2px solid #2b8c5e;
        }
        
        .seccion-titulo i {
            margin-right: 8px;
            color: #2b8c5e;
        }
        
        .seccion-cuerpo {
            padding: 15px;
        }
        
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
        
        .resumen {
            background: #f0f9f0;
        }
        
        .monto-principal {
            font-size: 22px;
            font-weight: bold;
            color: #2b8c5e;
        }
        
        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .tabla-productos th,
        .tabla-productos td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .tabla-productos th {
            background: #2b8c5e;
            color: white;
            font-weight: 600;
        }
        
        .tabla-productos tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .tabla-productos td.text-end {
            text-align: right;
        }
        
        .tabla-productos td.text-center {
            text-align: center;
        }
        
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
            font-size: 11px;
            color: #666;
        }
        
        .footer {
            background: #f5f5f5;
            padding: 12px 25px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn-acciones {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
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
        
        .btn-print {
            background: #2b8c5e;
            color: white;
        }
        
        .btn-close {
            background: #6c757d;
            color: white;
        }
        
        .btn-print:hover {
            background: #236e4a;
        }
        
        .btn-close:hover {
            background: #5a6268;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .btn-acciones {
                display: none;
            }
            .recibo {
                box-shadow: none;
                border-radius: 0;
            }
            .header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="btn-acciones">
        <button class="btn btn-print" onclick="window.print();">
            <i class="bi bi-printer"></i> Imprimir / Guardar PDF
        </button>
        <button class="btn btn-close" onclick="window.close();">
            <i class="bi bi-x-circle"></i> Cerrar
        </button>
    </div>
    
    <div class="recibo">
        <!-- Encabezado -->
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="background: white; padding: 8px 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <img src="{{ asset('assets/img/calzatodo.png') }}" 
                         alt="Logo Calzatodo" 
                         style="height: 60px; width: auto; display: block;">
                </div>
                <div style="background: white; padding: 8px 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <img src="{{ asset('assets/img/LogoTenShop.jpg') }}" 
                         alt="Logo TenShop" 
                         style="height: 60px; width: auto; display: block;">
                </div>
            </div>
            <h1>DETALLE DE PRODUCTOS</h1>
            <p>Productos incluidos en la factura</p>
        </div>
        
        <div class="content">
            <!-- Información de la Factura -->
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📄</i> INFORMACIÓN DE LA FACTURA
                </div>
                <div class="seccion-cuerpo">
                    <table class="info-table">
                        <tr>
                            <td>N° Factura:</td>
                            <td><strong>{{ $factura->Numero }}</strong></td>
                        </tr>
                        <tr>
                            <td>Fecha de Emisión:</td>
                            <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Proveedor:</td>
                            <td><strong>{{ $proveedor->Nombre ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Sucursal:</td>
                            <td>{{ $sucursal->Nombre ?? 'OFICINA PRINCIPAL' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Tabla de Productos -->
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📦</i> LISTA DE PRODUCTOS
                </div>
                <div class="seccion-cuerpo">
                    <table class="tabla-productos">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Referencia</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo USD</th>
                                <th class="text-end">Subtotal USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalles as $detalle)
                            <tr>
                                <td><code>{{ $detalle->Codigo ?? 'N/A' }}</code></td>
                                <td>{{ $detalle->Referencia ?? 'N/A' }}</td>
                                <td>{{ $detalle->producto_nombre ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($detalle->CantidadEmitida ?? 0, 2) }}</td>
                                <td class="text-end">$ {{ number_format($detalle->CostoDivisa ?? 0, 2) }}</td>
                                <td class="text-end">$ {{ number_format(($detalle->CantidadEmitida ?? 0) * ($detalle->CostoDivisa ?? 0), 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay productos registrados en esta factura</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Observaciones -->
            @if($factura->Descripcion)
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📝</i> OBSERVACIONES
                </div>
                <div class="seccion-cuerpo">
                    <p>{{ $factura->Descripcion }}</p>
                </div>
            </div>
            @endif
            
        </div>
        
        <!-- Pie -->
        <div class="footer">
            <p>Este documento es un listado detallado de los productos incluidos en la factura.</p>
            <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>