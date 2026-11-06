<!DOCTYPE html>
<html>
<head>
    <title>Nuevo mensaje de contacto</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Nuevo mensaje desde la página web</h2>
    <p><strong>Nombre:</strong> {{ $datos['nombre'] }}</p>
    <p><strong>Email:</strong> {{ $datos['email'] }}</p>
    <p><strong>Teléfono:</strong> {{ $datos['telefono'] ?? 'No proporcionado' }}</p>
    <p><strong>Asunto:</strong> {{ $datos['asunto'] }}</p>
    <hr>
    <p><strong>Mensaje:</strong></p>
    <p style="background: #f4f4f4; padding: 15px; border-radius: 5px;">{{ $datos['mensaje'] }}</p>
</body>
</html>