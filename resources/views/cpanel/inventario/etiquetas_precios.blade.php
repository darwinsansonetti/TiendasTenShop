<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etiquetas de Cambio de Precio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            padding: 0;
            margin: 0;
        }
        
        .etiqueta {
            display: inline-block;
            width: 165mm;
            height: 92mm;
            text-align: center;
            padding: 5mm 3mm;
            margin: 0;
            page-break-after: always;
            page-break-inside: avoid;
            box-sizing: border-box;
            overflow: hidden;
            border: 1px dashed #ccc;
        }
        
        .etiqueta .codigo {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin-top: 5mm;
            margin-bottom: 2mm;
            letter-spacing: 0.5px;
        }
        
        .etiqueta .descripcion {
            font-size: 10pt;
            text-align: center;
            margin-bottom: 5mm;
        }
        
        .etiqueta .precio {
            font-family: 'Impact', 'Arial Black', sans-serif;
            font-size: 42pt;
            font-weight: bold;
            text-align: center;
            margin-top: 5mm;
        }
        
        .etiqueta .ref {
            font-size: 8pt;
            text-align: left;
            display: block;
            margin-top: 10mm;
        }
        
        @page {
            size: 165mm 92mm;
            margin: 0;
            padding: 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .etiqueta {
                border: none !important;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @foreach($productos as $producto)
        <div class="etiqueta">
            <div class="codigo">{{ $producto->Codigo ?? 'N/A' }}</div>
            <div class="descripcion">{{ Str::limit($producto->Descripcion ?? '', 40) }}</div>
            <div class="precio">$ {{ number_format($producto->NuevoPvp ?? 0, 2) }}</div>
            <div class="ref">Ref.</div>
        </div>
    @endforeach
</body>
</html>