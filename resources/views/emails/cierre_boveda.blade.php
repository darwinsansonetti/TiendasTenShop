<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cierre de Bóveda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .header h1 { margin: 0 0 5px 0; font-size: 22px; }
        .header p { margin: 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 25px; }
        h2 {
            color: #7c3aed;
            font-size: 16px;
            border-bottom: 2px solid #e9d5ff;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f8f5ff;
            color: #5b21b6;
            font-weight: bold;
        }
        td.text-end { text-align: right; }
        td.text-center { text-align: center; }
        .badge-success { color: #059669; font-weight: bold; }
        .badge-danger { color: #dc2626; font-weight: bold; }
        .total-row {
            background: #f9f9f9;
            font-weight: bold;
        }
        .info-box {
            background: #f8f5ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #8b5cf6;
        }
        .info-box p { margin: 5px 0; font-size: 13px; }
        .footer {
            background: #f9f9f9;
            padding: 15px 25px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Cierre Diario de Bóveda</h1>
            <p>Bóveda #{{ $boveda->BovedaId }} - {{ \Carbon\Carbon::parse($boveda->Fecha)->format('d/m/Y') }}</p>
        </div>

        <div class="content">

            {{-- INFORMACIÓN GENERAL --}}
            <div class="info-box">
                <p><strong>ID:</strong> #{{ $boveda->BovedaId }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($boveda->Fecha)->format('d/m/Y') }}</p>
                <p><strong>Estatus:</strong> {{ $boveda->Estatus == 0 ? 'Abierto' : 'Cerrado' }}</p>
                <p><strong>Tasa:</strong> Bs. {{ number_format($boveda->tasa_cambio ?? 0, 2) }}</p>
                <p><strong>Observación:</strong> {{ $boveda->Observacion ?? 'Sin observación' }}</p>
            </div>

            {{-- TOTALES --}}
            <h2>Totales Generales</h2>
            <table>
                <tr>
                    <th style="width: 50%;">Total Divisas</th>
                    <td class="text-end">$ {{ number_format($totales['divisas'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total Bolívares</th>
                    <td class="text-end">Bs. {{ number_format($totales['bs'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total PDV Sistema</th>
                    <td class="text-end">VES {{ number_format($totales['pdv_sistema'], 2) }}</td>
                </tr>
                <tr>
                    <th>Total PDV Depositado</th>
                    <td class="text-end">VES {{ number_format($totales['pdv_depositado'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <th>Diferencia PDV</th>
                    <td class="text-end {{ abs($totales['pdv_diferencia']) < 0.01 ? 'badge-success' : 'badge-danger' }}">
                        VES {{ number_format($totales['pdv_diferencia'], 2) }}
                    </td>
                </tr>
            </table>

            {{-- CONCILIACIÓN EFECTIVO --}}
            @if($conciliacionEfectivo->count() > 0)
            <h2>Conciliación de Efectivo</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Tipo</th>
                        <th class="text-end">Sistema</th>
                        <th class="text-end">Contado</th>
                        <th class="text-center">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conciliacionEfectivo as $item)
                    <tr>
                        <td>{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                        <td>{{ $item->Tipo == 1 ? 'Divisas' : 'Bolívares' }}</td>
                        <td class="text-end">
                            {{ $item->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($item->MontoSistema, 2) }}
                        </td>
                        <td class="text-end">
                            {{ $item->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($item->MontoDepositado, 2) }}
                        </td>
                        <td class="text-center {{ abs($item->Diferencia) < 0.01 ? 'badge-success' : 'badge-danger' }}">
                            {{ $item->Tipo == 1 ? '$' : 'Bs.' }} {{ number_format($item->Diferencia, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- CONCILIACIÓN PDV --}}
            @if($conciliacionPDV->count() > 0)
            <h2>Conciliación Puntos de Venta</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Punto</th>
                        <th>Banco</th>
                        <th class="text-end">Sistema</th>
                        <th class="text-end">Depositado</th>
                        <th class="text-center">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conciliacionPDV as $item)
                    <tr>
                        <td>{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                        <td>{{ $item->pdv_descripcion ?? 'N/A' }}</td>
                        <td>{{ $item->banco_nombre ?? 'N/A' }}</td>
                        <td class="text-end">VES {{ number_format($item->MontoSistema, 2) }}</td>
                        <td class="text-end">VES {{ number_format($item->MontoDepositado, 2) }}</td>
                        <td class="text-center {{ abs($item->Diferencia) < 0.01 ? 'badge-success' : 'badge-danger' }}">
                            VES {{ number_format($item->Diferencia, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- CONCILIACIÓN OTROS --}}
            @if($conciliacionOtros->count() > 0)
            <h2>Conciliación Otros Conceptos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Tipo</th>
                        <th class="text-end">Sistema</th>
                        <th class="text-end">Depositado</th>
                        <th class="text-center">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conciliacionOtros as $item)
                    <tr>
                        <td>{{ $item->sucursal_nombre ?? 'N/A' }}</td>
                        <td>{{ $item->TipoNombre ?? 'N/A' }}</td>
                        <td class="text-end">Bs. {{ number_format($item->MontoSistema, 2) }}</td>
                        <td class="text-end">Bs. {{ number_format($item->MontoDepositado, 2) }}</td>
                        <td class="text-center {{ abs($item->Diferencia) < 0.01 ? 'badge-success' : 'badge-danger' }}">
                            Bs. {{ number_format($item->Diferencia, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

        </div>

        <div class="footer">
            <p>Este correo fue generado automáticamente por el sistema <strong>Tiendas Ten Shop</strong>.</p>
            <p>Fecha de envío: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>