@extends('adminlte::page')

@section('title', 'Detalle de Cuenta por Cobrar')

@section('content_header')
    <h1>Detalle de la Cuenta</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h4><strong>Cliente:</strong> {{ $cuenta->cliente->nombre_cliente }}</h4>
        <p><strong>Total Factura:</strong> {{ number_format($cuenta->total_factura, 2) }}</p>
        <p><strong>Saldo Restante:</strong> {{ number_format($cuenta->saldo_restante, 2) }}</p>
        <p><strong>Fecha de Emisión:</strong> {{ $cuenta->fecha_emision }}</p>
        <p><strong>Fecha de Vencimiento:</strong> {{ $cuenta->fecha_vencimiento }}</p>
        <p><strong>Estado:</strong> 
            @if($cuenta->estado == 'pagada')
                <span class="badge bg-success">Pagada</span>
            @elseif($cuenta->estado == 'vencida')
                <span class="badge bg-danger">Vencida</span>
            @else
                <span class="badge bg-warning text-dark">Pendiente</span>
            @endif
        </p>

        <hr>

        <h5>Historial de abonos</h5>
        <table class="table table-sm table-bordered">
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

        <a href="{{ route('admin.cuentas.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('admin.cuentas.print', $cuenta->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Imprimir
        </a>
    </div>
</div>
@stop
