<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Facturas - {{ $proveedor->Nombre }}</title>

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
            max-width: 1200px;
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
        
        .logos-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo-box {
            background: white;
            padding: 8px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .logo-box img {
            height: 60px;
            width: auto;
            display: block;
        }
        
        .proveedor-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        .proveedor-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .proveedor-datos {
            text-align: left;
            color: white;
        }
        
        .proveedor-datos h3 {
            margin-bottom: 5px;
        }
        
        .proveedor-datos p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .header h1 {
            color: white;
            font-size: 24px;
            margin: 15px 0 5px;
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
        
        .resumen {
            background: #f0f9f0;
        }
        
        .resumen-grid {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .resumen-item {
            flex: 1;
        }
        
        .resumen-label {
            font-size: 12px;
            color: #555;
            margin-bottom: 5px;
        }
        
        .resumen-valor {
            font-size: 20px;
            font-weight: bold;
        }
        
        .resumen-valor.total {
            color: #2b8c5e;
        }
        
        .resumen-valor.pagado {
            color: #28a745;
        }
        
        .resumen-valor.saldo {
            color: #dc3545;
        }
        
        .tabla-facturas {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .tabla-facturas th,
        .tabla-facturas td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .tabla-facturas th {
            background: #2b8c5e;
            color: white;
            font-weight: 600;
        }
        
        .tabla-facturas tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .tabla-facturas td.text-end {
            text-align: right;
        }
        
        .tabla-facturas td.text-center {
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
            <div class="logos-container">
                <div class="logo-box">
                    <img src="{{ asset('assets/img/calzatodo.png') }}" alt="Logo Calzatodo">
                </div>
                <div class="logo-box">
                    <img src="{{ asset('assets/img/LogoTenShop.jpg') }}" alt="Logo TenShop">
                </div>
            </div>
            
            <div class="proveedor-info">
                <img src="{{ $imgSrc }}" alt="{{ $proveedor->Nombre }}" class="proveedor-img">
                <div class="proveedor-datos">
                    <h3>{{ $proveedor->Nombre }}</h3>
                    <p>RIF/Cédula: {{ $proveedor->Rif_Cedula ?? 'N/A' }}</p>
                    <p>Teléfono: {{ $proveedor->TelefonoMovil ?? $proveedor->TelefonoFijo ?? 'N/A' }}</p>
                    <p>Email: {{ $proveedor->CorreoElectronico ?? 'N/A' }}</p>
                </div>
            </div>
            
            <h1>LISTADO DE FACTURAS</h1>
            <p>Facturas vigentes del proveedor</p>
        </div>
        
        <div class="content">
            <!-- Resumen -->
            <div class="seccion resumen">
                <div class="seccion-titulo">
                    <i>💰</i> RESUMEN FINANCIERO
                </div>
                <div class="seccion-cuerpo">
                    <div class="resumen-grid">
                        <div class="resumen-item">
                            <div class="resumen-label">TOTAL FACTURAS</div>
                            <div class="resumen-valor total">$ {{ number_format($totalFacturas, 2) }}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">TOTAL PAGADO</div>
                            <div class="resumen-valor pagado">$ {{ number_format($totalPagado, 2) }}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">SALDO PENDIENTE</div>
                            <div class="resumen-valor saldo">$ {{ number_format($saldoPendiente, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Facturas -->
            <div class="seccion">
                <div class="seccion-titulo">
                    <i>📄</i> FACTURAS VIGENTES
                </div>
                <div class="seccion-cuerpo">
                    <table class="tabla-facturas">
                        <thead>
                            <tr>
                                <th>N° Factura</th>
                                <th>Fecha Emisión</th>
                                <th class="text-end">Monto USD</th>
                                <th class="text-end">Pagado USD</th>
                                <th class="text-end">Saldo USD</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facturas as $factura)
                            <tr>
                                <td>{{ $factura->Numero }}贩
                                <td>{{ \Carbon\Carbon::parse($factura->FechaCreacion)->format('d/m/Y') }}贩
                                <td class="text-end">$ {{ number_format($factura->MontoDivisa, 2) }}贩
                                <td class="text-end">$ {{ number_format($factura->total_pagado, 2) }}贩
                                <td class="text-end fw-bold">$ {{ number_format($factura->saldo_pendiente, 2) }}贩
                                <td class="text-center">
                                    @php
                                        $estatusTexto = match($factura->Estatus) {
                                            1 => 'En Proceso',
                                            2 => 'Recibiendo',
                                            4 => 'Recibida',
                                            default => 'Desconocido'
                                        };
                                        $estatusColor = match($factura->Estatus) {
                                            1 => '#ffc107',
                                            2 => '#17a2b8',
                                            4 => '#28a745',
                                            default => '#6c757d'
                                        };
                                    @endphp
                                    <span style="background: {{ $estatusColor }}; color: white; padding: 3px 8px; border-radius: 12px; font-size: 10px;">
                                        {{ $estatusTexto }}
                                    </span>
                                贩
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay facturas registradas para este proveedor贩
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot style="background: #f5f5f5; font-weight: bold;">
                            <tr>
                                <td colspan="2" class="text-end">TOTALES:贩
                                <td class="text-end">$ {{ number_format($totalFacturas, 2) }}贩
                                <td class="text-end">$ {{ number_format($totalPagado, 2) }}贩
                                <td class="text-end">$ {{ number_format($saldoPendiente, 2) }}贩
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Firmas -->
            <div class="firma-section">
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
            <p>Este documento es un listado de las facturas vigentes del proveedor.</p>
            <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>