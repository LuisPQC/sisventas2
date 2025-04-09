@extends('adminlte::page')

@section('title', 'Registrar Abono')

@section('content_header')
    <h1>Registrar Abono a Factura</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.abonos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="cuenta_id" value="{{ $cuenta->id }}">
                <div class="mb-3">
                    <label>Cliente:</label>
                    <input type="text" class="form-control" value="{{ $cuenta->cliente->nombre_cliente }}" disabled>
                </div>

                <div class="mb-3">
                    <label>Total Factura:</label>
                    <input type="text" class="form-control" value="{{ $cuenta->total_factura }}" disabled>
                </div>

                <div class="mb-3">
                    <label>Saldo Restante:</label>
                    <input type="text" class="form-control" value="{{ $cuenta->saldo_restante }}" disabled>
                </div>

                <div class="mb-3">
                    <label>Monto del Abono:</label>
                    <input type="number" step="0.01" class="form-control" name="monto" required>
                </div>

                <div class="mb-3">
                    <label>Observaciones:</label>
                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Registrar Abono</button>
                <a href="{{ route('admin.cuentas.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
    </div>
</div>
    
@stop
