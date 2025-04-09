<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta por Cobrar</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #aaa; padding: 6px; text-align: left; }
        .header { margin-bottom: 15px; }
        .titulo { font-size: 18px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <p><strong>Cliente:</strong> {{ $cuenta->cliente->nombre_cliente }}</p>
        <p><strong>Fecha Emisión:</strong> {{ $cuenta->fecha_emision }}</p>
        <p><strong>Fecha Vencimiento:</strong> {{ $cuenta->fecha_vencimiento }}</p>
        <p><strong>Total Factura:</strong> {{ number_format($cuenta->total_factura, 2) }}</p>
        <p><strong>Saldo Restante:</strong> {{ number_format($cuenta->saldo_restante, 2) }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($cuenta->estado) }}</p>
    </div>

    <div class="titulo">Historial de Abonos</div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Usuario</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($abonos as $abono)
            <tr>
                <td>{{ $abono->fecha_abono }}</td>
                <td>{{ number_format($abono->monto, 2) }}</td>
                <td>{{ $abono->usuario->name ?? 'Desconocido' }}</td>
                <td>{{ $abono->observaciones }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
