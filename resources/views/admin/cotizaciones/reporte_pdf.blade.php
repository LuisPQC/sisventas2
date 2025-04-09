<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: center; }
    </style>
</head>
<body>

    <h2>Cotización #{{ $cotizacion->id }}</h2>
    <p><strong>Fecha:</strong> {{ $cotizacion->fecha }}</p>

    <p><strong>Cliente:</strong>
        {{ $mostrarCliente ? ($cotizacion->cliente->nombre_cliente ?? 'N/A') : 'Sin nombre' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($cotizacion->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td>${{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @php $total += $detalle->subtotal; @endphp
            @endforeach
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>${{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
